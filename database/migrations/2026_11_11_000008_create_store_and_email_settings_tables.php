<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Store Settings table
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_group', 50)->default('general');
            $table->string('setting_key', 100)->unique();
            $table->longText('setting_value')->nullable();
            $table->timestamps();

            $table->index('setting_group');
        });

        // 2. Email Settings table
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('smtp_enabled')->default(false);
            $table->string('smtp_host', 255)->nullable();
            $table->integer('smtp_port')->nullable();
            $table->text('smtp_user')->nullable();
            $table->text('smtp_pass')->nullable();
            $table->string('smtp_encryption', 20)->nullable();
            $table->string('from_email', 255)->nullable();
            $table->string('from_name', 255)->nullable();
            $table->string('admin_notification_email', 255)->nullable();
            $table->boolean('notify_admin_on_order')->default(true);
            $table->string('email_subject', 255)->nullable();
            $table->longText('email_template_html')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_settings');
        Schema::dropIfExists('store_settings');
    }
};
