<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.connections.mysql.database') === ':memory:') {
            config(['database.connections.mysql.database' => 'deen_electronics_dev']);
        }
        config(['database.default' => 'mysql']);
        DB::purge('mysql');

        try {
            DB::connection('mysql')->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('MySQL connection not available: ' . $e->getMessage());
        }

        // Ensure roles and permissions are seeded
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function tearDown(): void
    {
        try {
            // Clean up test admins and test users created during test runs
            $conn = DB::connection('mysql');
            $conn->table('admins')->where('username', 'LIKE', 'test_%')->delete();
            $conn->table('users')->where('email', 'LIKE', 'test_%')->delete();
            $conn->table('audit_logs')->where('entity', 'AuditTest')->delete();
        } catch (\Throwable $e) {
            // Ignore
        }

        parent::tearDown();
    }

    public function test_admin_login_screen_renders_successfully(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
        $response->assertSee('Sign In to Admin Console');
    }

    public function test_admin_can_login_with_valid_credentials_and_regenerates_session(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'test_superadmin_' . uniqid(),
            'email'     => 'test_superadmin_' . uniqid() . '@example.com',
            'password'  => Hash::make('Secret123!'),
            'full_name' => 'Test Super Admin',
            'is_active' => true,
        ]);

        $initialSessionId = Session::getId();

        $response = $this->post(route('admin.login.submit'), [
            'login'    => $admin->username,
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');

        // Verify session was regenerated
        $newSessionId = Session::getId();
        $this->assertNotEquals($initialSessionId, $newSessionId, 'Session ID must regenerate on login');

        // Verify last_login_at was updated
        $admin->refresh();
        $this->assertNotNull($admin->last_login_at);
    }

    public function test_admin_login_fails_with_invalid_credentials_and_generic_error(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'test_invalid_' . uniqid(),
            'email'     => 'test_invalid_' . uniqid() . '@example.com',
            'password'  => Hash::make('CorrectPassword'),
            'is_active' => true,
        ]);

        // Wrong password for existing user
        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'login'    => $admin->username,
            'password' => 'WrongPassword',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors([
            'login' => 'The provided credentials do not match our records or the account is inactive.',
        ]);
        $this->assertGuest('admin');

        // Non-existent user must return the exact same generic error message
        $responseNonExistent = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'login'    => 'test_nonexistent_' . uniqid(),
            'password' => 'SomePassword',
        ]);

        $responseNonExistent->assertRedirect(route('admin.login'));
        $responseNonExistent->assertSessionHasErrors([
            'login' => 'The provided credentials do not match our records or the account is inactive.',
        ]);
    }

    public function test_admin_login_rate_limiting_and_http_429(): void
    {
        $loginAttemptUser = 'test_throttle_' . uniqid();
        $normalizedLogin  = Str::transliterate(Str::lower($loginAttemptUser));
        $throttleKey      = 'admin_login:' . $normalizedLogin . '|127.0.0.1';

        RateLimiter::clear($throttleKey);

        // 5 consecutive failed attempts
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
                'login'    => $loginAttemptUser,
                'password' => 'WrongPassword',
            ]);
            $response->assertRedirect(route('admin.login'));
        }

        // 6th attempt must trigger HTTP 429 Too Many Requests
        $response429 = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'login'    => $loginAttemptUser,
            'password' => 'WrongPassword',
        ]);

        $response429->assertStatus(429);
        $response429->assertHeader('Retry-After');
        $response429->assertSee('Too many login attempts');

        // Verify throttled audit event was recorded
        $auditThrottled = DB::table('audit_logs')
            ->where('action', 'admin.login_throttled')
            ->where('changes', 'LIKE', '%' . $normalizedLogin . '%')
            ->first();
        $this->assertNotNull($auditThrottled, 'Throttled login event must be recorded in audit log');

        // Clearing rate limiter restores login access
        RateLimiter::clear($throttleKey);

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $activeAdmin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => $loginAttemptUser,
            'email'     => $loginAttemptUser . '@example.com',
            'password'  => Hash::make('ValidP@ss123!'),
            'is_active' => true,
        ]);

        $responseValid = $this->post(route('admin.login.submit'), [
            'login'    => $loginAttemptUser,
            'password' => 'ValidP@ss123!',
        ]);

        $responseValid->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($activeAdmin, 'admin');
    }

    public function test_deactivated_admin_cannot_login(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'test_deactivated_' . uniqid(),
            'email'     => 'test_deactivated_' . uniqid() . '@example.com',
            'password'  => Hash::make('Secret123!'),
            'is_active' => false,
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'login'    => $admin->username,
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_admin_can_logout_and_session_is_invalidated(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'test_logout_' . uniqid(),
            'email'     => 'test_logout_' . uniqid() . '@example.com',
            'password'  => Hash::make('Secret123!'),
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin');
        $this->assertAuthenticatedAs($admin, 'admin');

        $response = $this->post(route('admin.logout'));
        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_guard_and_admin_guard_isolation(): void
    {
        // 1. Create a storefront customer user
        $customer = User::create([
            'name'     => 'Customer User',
            'email'    => 'test_customer_' . uniqid() . '@example.com',
            'password' => Hash::make('CustomerPass123!'),
        ]);

        // Authenticate customer on the 'web' guard
        $this->actingAs($customer, 'web');
        $this->assertTrue(Auth::guard('web')->check());
        $this->assertFalse(Auth::guard('admin')->check(), 'Customer must not be authenticated on admin guard');

        // Customer attempts to access admin dashboard -> redirected to admin login
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));

        // Log out of customer guard
        Auth::guard('web')->logout();
        $this->assertFalse(Auth::guard('web')->check());

        // 2. Create an admin user
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'test_guard_admin_' . uniqid(),
            'email'     => 'test_guard_' . uniqid() . '@example.com',
            'password'  => Hash::make('AdminPass123!'),
            'is_active' => true,
        ]);

        // Authenticate on 'admin' guard
        $this->actingAs($admin, 'admin');
        $this->assertTrue(Auth::guard('admin')->check());

        // Admin session must not satisfy customer 'web' guard
        $this->assertFalse(Auth::guard('web')->check(), 'Admin session must not authenticate web guard');
    }

    public function test_super_admin_has_full_access_to_all_protected_routes(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $admin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'test_root_' . uniqid(),
            'email'     => 'test_root_' . uniqid() . '@example.com',
            'password'  => Hash::make('Pass123!'),
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.dashboard'))->assertStatus(200);
        $this->get(route('admin.test.superadmin'))->assertStatus(200);
        $this->get(route('admin.test.products.view'))->assertStatus(200);
        $this->get(route('admin.test.settings.view'))->assertStatus(200);
        $this->get(route('admin.test.admins.manage'))->assertStatus(200);
    }

    public function test_manager_permissions_and_role_access_boundaries(): void
    {
        $managerRole = Role::where('name', 'Manager')->firstOrFail();

        $manager = Admin::create([
            'role_id'   => $managerRole->id,
            'username'  => 'test_mgr_' . uniqid(),
            'email'     => 'test_mgr_' . uniqid() . '@example.com',
            'password'  => Hash::make('Pass123!'),
            'is_active' => true,
        ]);

        $this->actingAs($manager, 'admin');

        // Dashboard accessible
        $this->get(route('admin.dashboard'))->assertStatus(200);

        // Allowed: products.view (Manager has this permission)
        $this->get(route('admin.test.products.view'))->assertStatus(200);

        // Allowed: settings.view (Manager has this permission)
        $this->get(route('admin.test.settings.view'))->assertStatus(200);

        // FORBIDDEN: Super Admin role required
        $this->get(route('admin.test.superadmin'))->assertStatus(403);

        // FORBIDDEN: admins.manage (Manager does NOT possess this permission)
        $this->get(route('admin.test.admins.manage'))->assertStatus(403);
    }

    public function test_editor_permissions_and_access_boundaries(): void
    {
        $editorRole = Role::where('name', 'Editor')->firstOrFail();

        $editor = Admin::create([
            'role_id'   => $editorRole->id,
            'username'  => 'test_ed_' . uniqid(),
            'email'     => 'test_ed_' . uniqid() . '@example.com',
            'password'  => Hash::make('Pass123!'),
            'is_active' => true,
        ]);

        $this->actingAs($editor, 'admin');

        // Dashboard accessible
        $this->get(route('admin.dashboard'))->assertStatus(200);

        // Allowed: products.view (Editor has this permission)
        $this->get(route('admin.test.products.view'))->assertStatus(200);

        // FORBIDDEN: settings.view (Editor does NOT possess this permission)
        $this->get(route('admin.test.settings.view'))->assertStatus(403);

        // FORBIDDEN: admins.manage (Editor does NOT possess this permission)
        $this->get(route('admin.test.admins.manage'))->assertStatus(403);

        // FORBIDDEN: Super Admin role required
        $this->get(route('admin.test.superadmin'))->assertStatus(403);
    }

    public function test_secure_super_admin_provisioning_and_duplicate_protection(): void
    {
        $username = 'test_cli_admin_' . uniqid();
        $email    = 'test_cli_' . uniqid() . '@example.com';
        $password = 'StrongP@ssw0rd99';

        // 1. Initial provisioning with --force or initial account
        $this->artisan('admin:create-super-admin', [
            '--username' => $username,
            '--email'    => $email,
            '--password' => $password,
            '--name'     => 'CLI Provisioned Super Admin',
            '--force'    => true,
        ])
        ->assertSuccessful()
        ->expectsOutputToContain('created successfully');

        // Verify in database
        $admin = Admin::where('username', $username)->first();
        $this->assertNotNull($admin);
        $this->assertEquals($email, $admin->email);
        $this->assertEquals('CLI Provisioned Super Admin', $admin->full_name);
        $this->assertTrue(Hash::check($password, $admin->password));
        $this->assertEquals('Super Admin', $admin->role->name);

        // 2. Attempting to provision a SECOND Super Admin without --force must fail
        $secondUsername = 'test_cli_second_' . uniqid();
        $this->artisan('admin:create-super-admin', [
            '--username' => $secondUsername,
            '--email'    => 'test_sec_' . uniqid() . '@example.com',
            '--password' => 'AnotherStrongP@ss99',
            '--name'     => 'Second Super Admin',
        ])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutputToContain('already exists in the system');

        // Verify second admin was NOT created
        $this->assertNull(Admin::where('username', $secondUsername)->first());

        // 3. Provisioning with DEEN_SUPER_ADMIN_PASSWORD environment variable
        $envUsername = 'test_cli_env_' . uniqid();
        $envEmail    = 'test_env_' . uniqid() . '@example.com';
        putenv('DEEN_SUPER_ADMIN_PASSWORD=EnvPasswordSecure123!');

        $this->artisan('admin:create-super-admin', [
            '--username' => $envUsername,
            '--email'    => $envEmail,
            '--name'     => 'Env Provisioned Admin',
            '--force'    => true,
        ])
        ->assertSuccessful()
        ->expectsOutputToContain('created successfully');

        $envAdmin = Admin::where('username', $envUsername)->first();
        $this->assertNotNull($envAdmin);
        $this->assertTrue(Hash::check('EnvPasswordSecure123!', $envAdmin->password));

        putenv('DEEN_SUPER_ADMIN_PASSWORD');
    }

    public function test_audit_log_redacts_sensitive_credentials(): void
    {
        $auditService = app(AuditLogService::class);

        $log = $auditService->log(
            action: 'test.credential_check',
            entity: 'AuditTest',
            entityId: 999,
            changes: [
                'username'              => 'admin_user',
                'password'              => 'SecretPlainTextPassword123!',
                'password_confirmation' => 'SecretPlainTextPassword123!',
                'token'                 => 'bearer_token_xyz_987',
                'nested'                => [
                    'credit_card' => '4111222233334444',
                    'normal_key'  => 'normal_value',
                ],
            ]
        );

        $dbLog = DB::table('audit_logs')->where('id', $log->id)->first();
        $this->assertNotNull($dbLog);

        $decodedChanges = json_decode($dbLog->changes, true);

        // Sensitive credentials must be strictly redacted
        $this->assertEquals('[REDACTED]', $decodedChanges['password']);
        $this->assertEquals('[REDACTED]', $decodedChanges['password_confirmation']);
        $this->assertEquals('[REDACTED]', $decodedChanges['token']);
        $this->assertEquals('[REDACTED]', $decodedChanges['nested']['credit_card']);

        // Non-sensitive data must be intact
        $this->assertEquals('admin_user', $decodedChanges['username']);
        $this->assertEquals('normal_value', $decodedChanges['nested']['normal_key']);

        // Clean up
        DB::table('audit_logs')->where('id', $log->id)->delete();
    }

    public function test_diagnostic_routes_are_disabled_outside_local_and_testing(): void
    {
        // When environment is not local or testing, the test prefix routes are omitted
        // Verify that in testing environment, the route is registered
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.test.superadmin'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.test.products.view'));

        // Verify the environment guard logic in routes/web.php
        $isLocalOrTesting = app()->environment('local', 'testing');
        $this->assertTrue($isLocalOrTesting, 'Test suite must run in testing environment');

        // Direct check: if app environment were production, Route::has returns false for isolated route file
        $routesInWeb = file_get_contents(base_path('routes/web.php'));
        $this->assertStringContainsString("if (app()->environment('local', 'testing'))", $routesInWeb);
    }
}
