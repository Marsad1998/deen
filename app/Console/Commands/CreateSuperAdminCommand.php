<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CreateSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-super-admin 
                            {--username= : Unique username for the Super Admin}
                            {--email= : Unique email address for the Super Admin}
                            {--name= : Full name of the Super Admin}
                            {--password= : [DEPRECATED] Plaintext password option. Prefer interactive prompt or DEEN_SUPER_ADMIN_PASSWORD env}
                            {--force : Force creation even if a Super Admin account already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provision a secure initial Super Admin account for DEEN Electronics';

    /**
     * Execute the console command.
     */
    public function handle(AuditLogService $auditLogService): int
    {
        $this->newLine();
        $this->alert('DEEN ELECTRONICS — SECURE SUPER ADMIN PROVISIONING');

        // Verify Super Admin role exists
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if (! $superAdminRole) {
            $this->error("The 'Super Admin' role was not found in the database. Please ensure seeders have been executed.");
            return Command::FAILURE;
        }

        // Security safeguard: Prevent accidental multiple initial Super Admin accounts
        $existingCount = Admin::where('role_id', $superAdminRole->id)->count();
        if ($existingCount > 0 && ! $this->option('force')) {
            $this->error("A Super Admin account already exists in the system ({$existingCount} found).");
            $this->line("To prevent accidental duplicate provisioning of root accounts, specify the <comment>--force</comment> flag or manage administrators via the admin portal.");
            return Command::FAILURE;
        }

        // Retrieve or prompt for inputs
        $username = $this->option('username') ?: $this->ask('Enter Administrator Username');
        $email    = $this->option('email') ?: $this->ask('Enter Administrator Email Address');
        $name     = $this->option('name') ?: $this->ask('Enter Full Name (optional)', 'System Administrator');

        $password = null;

        // Safer non-interactive secret mechanism (e.g., CI/CD / containerized deployment)
        if ($envSecret = getenv('DEEN_SUPER_ADMIN_PASSWORD')) {
            $password = $envSecret;
        } elseif ($cliPassword = $this->option('password')) {
            $this->warn('SECURITY WARNING: Supplying passwords via the --password CLI option exposes plaintext secrets in shell history and process tables (ps aux).');
            $this->line('  Recommendation: Use the interactive hidden prompt or set the DEEN_SUPER_ADMIN_PASSWORD environment variable instead.');
            $password = $cliPassword;
        }

        if (! $password) {
            if ($this->input->isInteractive()) {
                $password = $this->secret('Enter Secure Password (minimum 8 characters)');
                $confirmPassword = $this->secret('Confirm Secure Password');

                if ($password !== $confirmPassword) {
                    $this->error('Password confirmation does not match.');
                    return Command::FAILURE;
                }
            } else {
                $this->error('Password is required. Provide the DEEN_SUPER_ADMIN_PASSWORD environment variable for non-interactive execution.');
                return Command::FAILURE;
            }
        }

        // Validate inputs
        $validator = Validator::make([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'name'     => $name,
        ], [
            'username' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-zA-Z0-9_\-\.]+$/', 'unique:admins,username'],
            'email'    => ['required', 'string', 'email', 'max:150', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
            'name'     => ['nullable', 'string', 'max:150'],
        ]);

        if ($validator->fails()) {
            $this->newLine();
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line(" - {$error}");
            }
            return Command::FAILURE;
        }

        try {
            $admin = Admin::create([
                'role_id'   => $superAdminRole->id,
                'username'  => $username,
                'email'     => $email,
                'password'  => Hash::make($password),
                'full_name' => $name,
                'is_active' => true,
            ]);

            // Record audit log without credential payload
            $auditLogService->log(
                action: 'admin.provisioned',
                entity: 'Admin',
                entityId: $admin->id,
                changes: [
                    'username' => $admin->username,
                    'email'    => $admin->email,
                    'role'     => $superAdminRole->name,
                ]
            );

            $this->newLine();
            $this->info("✓ Super Admin [{$admin->username}] created successfully with ID #{$admin->id}.");
            $this->line("  Assigned Role: <comment>{$superAdminRole->name}</comment>");
            $this->line("  Email:         <comment>{$admin->email}</comment>");
            $this->line("  Audit Trail:   <info>Logged in audit ledger</info>");
            $this->newLine();

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to create Super Admin: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

