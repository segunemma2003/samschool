<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table optimizations
        $this->addIndexSafely('users', function (Blueprint $table) {
            $table->index(['email', 'email_verified_at'], 'users_email_verified_idx');
            $table->index(['created_at', 'email_verified_at'], 'users_active_search_idx');
        });

        // Tenants table optimizations
        $this->addIndexSafely('tenants', function (Blueprint $table) {
            $table->index('status', 'tenants_status_idx');
            $table->index('is_active', 'tenants_is_active_idx');
            $table->index(['is_active', 'status'], 'tenants_active_status_idx');
            $table->index('email', 'tenants_email_idx');
            $table->index(['otp_code', 'otp_code_active_at'], 'tenants_otp_verification_idx');
            $table->index('created_at', 'tenants_created_at_idx');
        });

        // Domains table optimizations
        $this->addIndexSafely('domains', function (Blueprint $table) {
            $table->index(['tenant_id', 'created_at'], 'domains_tenant_created_idx');
        });

        // Sessions table optimizations
        $this->addIndexSafely('sessions', function (Blueprint $table) {
            $table->index(['user_id', 'last_activity'], 'sessions_user_activity_idx');
            $table->index('last_activity', 'sessions_last_activity_idx');
        });

        // Cache table optimizations
        $this->addIndexSafely('cache', function (Blueprint $table) {
            $table->index('expiration', 'cache_expiration_idx');
        });

        // Cache locks optimizations
        $this->addIndexSafely('cache_locks', function (Blueprint $table) {
            $table->index('expiration', 'cache_locks_expiration_idx');
            $table->index(['owner', 'expiration'], 'cache_locks_owner_expiration_idx');
        });

        // Jobs table optimizations
        $this->addIndexSafely('jobs', function (Blueprint $table) {
            $table->index(['queue', 'available_at'], 'jobs_queue_available_idx');
            $table->index(['attempts', 'created_at'], 'jobs_attempts_created_idx');
            $table->index('reserved_at', 'jobs_reserved_at_idx');
        });

        // Job batches optimizations
        $this->addIndexSafely('job_batches', function (Blueprint $table) {
            $table->index(['pending_jobs', 'failed_jobs'], 'job_batches_status_idx');
            $table->index(['finished_at', 'created_at'], 'job_batches_finished_created_idx');
            $table->index('cancelled_at', 'job_batches_cancelled_at_idx');
        });

        // Failed jobs optimizations
        $this->addIndexSafely('failed_jobs', function (Blueprint $table) {
            // Index for failed_at (queue and connection are TEXT, handled separately)
            $table->index('failed_at', 'failed_jobs_failed_at_idx');
        });

        // Settings table optimizations
        $this->addIndexSafely('settings', function (Blueprint $table) {
            $table->index('group', 'settings_group_idx');
            $table->index('locked', 'settings_locked_idx');
            $table->index(['group', 'locked'], 'settings_group_locked_idx');
        });

        // General settings optimizations
        $this->addIndexSafely('general_settings', function (Blueprint $table) {
            $table->index('site_name', 'general_settings_site_name_idx');
            $table->index('email_from_address', 'general_settings_email_from_idx');
        });

        // Filament email log optimizations
        $this->addIndexSafely('filament_email_log', function (Blueprint $table) {
            $table->index('to', 'filament_email_log_to_idx');
            $table->index('from', 'filament_email_log_from_idx');
            $table->index(['team_id', 'created_at'], 'filament_email_log_team_created_idx');
            $table->index('subject', 'filament_email_log_subject_idx');
            $table->index('created_at', 'filament_email_log_created_at_idx');
        });

        // Password reset tokens optimizations
        $this->addIndexSafely('password_reset_tokens', function (Blueprint $table) {
            $table->index('created_at', 'password_reset_tokens_created_at_idx');
        });

        // Tenant user impersonation tokens optimizations
        $this->addIndexSafely('tenant_user_impersonation_tokens', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'impersonation_tenant_user_idx');
            $table->index('created_at', 'impersonation_tokens_created_at_idx');
            $table->index('auth_guard', 'impersonation_tokens_auth_guard_idx');
        });

        // Handle TEXT columns with raw SQL for MySQL
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE `failed_jobs` ADD INDEX `failed_jobs_queue_prefix_idx` (`queue`(100))');
            } catch (\Exception $e) {
                // Index might already exist, skip
            }

            try {
                DB::statement('ALTER TABLE `failed_jobs` ADD INDEX `failed_jobs_connection_prefix_idx` (`connection`(50))');
            } catch (\Exception $e) {
                // Index might already exist, skip
            }
        }
    }

    /**
     * Helper method to add indexes safely (skip if already exists)
     */
    private function addIndexSafely(string $tableName, callable $callback): void
    {
        try {
            Schema::table($tableName, $callback);
        } catch (\Exception $e) {
            // Skip if index already exists or other errors
            if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafely('users', [
            'users_email_verified_idx',
            'users_active_search_idx'
        ]);

        $this->dropIndexSafely('tenants', [
            'tenants_status_idx',
            'tenants_is_active_idx',
            'tenants_active_status_idx',
            'tenants_email_idx',
            'tenants_otp_verification_idx',
            'tenants_created_at_idx'
        ]);

        $this->dropIndexSafely('domains', [
            'domains_tenant_created_idx'
        ]);

        $this->dropIndexSafely('sessions', [
            'sessions_user_activity_idx',
            'sessions_last_activity_idx'
        ]);

        $this->dropIndexSafely('cache', [
            'cache_expiration_idx'
        ]);

        $this->dropIndexSafely('cache_locks', [
            'cache_locks_expiration_idx',
            'cache_locks_owner_expiration_idx'
        ]);

        $this->dropIndexSafely('jobs', [
            'jobs_queue_available_idx',
            'jobs_attempts_created_idx',
            'jobs_reserved_at_idx'
        ]);

        $this->dropIndexSafely('job_batches', [
            'job_batches_status_idx',
            'job_batches_finished_created_idx',
            'job_batches_cancelled_at_idx'
        ]);

        $this->dropIndexSafely('failed_jobs', [
            'failed_jobs_failed_at_idx'
        ]);

        $this->dropIndexSafely('settings', [
            'settings_group_idx',
            'settings_locked_idx',
            'settings_group_locked_idx'
        ]);

        $this->dropIndexSafely('general_settings', [
            'general_settings_site_name_idx',
            'general_settings_email_from_idx'
        ]);

        $this->dropIndexSafely('filament_email_log', [
            'filament_email_log_to_idx',
            'filament_email_log_from_idx',
            'filament_email_log_team_created_idx',
            'filament_email_log_subject_idx',
            'filament_email_log_created_at_idx'
        ]);

        $this->dropIndexSafely('password_reset_tokens', [
            'password_reset_tokens_created_at_idx'
        ]);

        $this->dropIndexSafely('tenant_user_impersonation_tokens', [
            'impersonation_tenant_user_idx',
            'impersonation_tokens_created_at_idx',
            'impersonation_tokens_auth_guard_idx'
        ]);

        // Drop TEXT column indexes
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE `failed_jobs` DROP INDEX `failed_jobs_queue_prefix_idx`');
            } catch (\Exception $e) {
                // Index might not exist, skip
            }

            try {
                DB::statement('ALTER TABLE `failed_jobs` DROP INDEX `failed_jobs_connection_prefix_idx`');
            } catch (\Exception $e) {
                // Index might not exist, skip
            }
        }
    }

    /**
     * Helper method to drop indexes safely
     */
    private function dropIndexSafely(string $tableName, array $indexes): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($indexes) {
            foreach ($indexes as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Exception $e) {
                    // Skip if index doesn't exist
                }
            }
        });
    }
};
