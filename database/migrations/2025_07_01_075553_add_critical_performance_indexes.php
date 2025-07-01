<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Critical indexes for sub-300ms queries

        // Domains table - most critical for tenant resolution
        $this->addIndexSafely('domains', function (Blueprint $table) {
            $table->index('domain', 'domains_domain_idx'); // Primary lookup
            $table->index(['domain', 'tenant_id'], 'domains_domain_tenant_idx'); // Join optimization
        });

        // Tenants table - critical for logo and settings lookup
        $this->addIndexSafely('tenants', function (Blueprint $table) {
            $table->index(['id', 'logo'], 'tenants_id_logo_idx'); // Logo lookup optimization
            $table->index(['id', 'is_active'], 'tenants_id_active_idx'); // Active tenant check
        });

        // Schools table - domain lookup
        $this->addIndexSafely('schools', function (Blueprint $table) {
            $table->index('domain', 'schools_domain_idx'); // Primary domain lookup
        });

        // Student scores - most frequent query
        $this->addIndexSafely('student_scores', function (Blueprint $table) {
            $table->index(['course_id', 'result_section_id', 'student_id'], 'student_scores_lookup_idx');
            $table->index(['student_id', 'course_id'], 'student_scores_student_course_idx');
        });

        // General settings - frequently accessed
        $this->addIndexSafely('general_settings', function (Blueprint $table) {
            $table->index(['site_name', 'email_from_address'], 'general_settings_common_idx');
        });

        // Users table - auth optimization
        $this->addIndexSafely('users', function (Blueprint $table) {
            $table->index(['email', 'password'], 'users_auth_idx');
            $table->index(['id', 'email_verified_at'], 'users_verified_idx');
        });

        // Sessions - frequent cleanup and lookup
        $this->addIndexSafely('sessions', function (Blueprint $table) {
            $table->index(['user_id', 'ip_address'], 'sessions_user_ip_idx');
            $table->index('last_activity', 'sessions_activity_cleanup_idx');
        });

        // MySQL specific optimizations
        if (DB::getDriverName() === 'mysql') {
            // Add covering indexes for most common queries
            try {
                DB::statement('ALTER TABLE `domains` ADD INDEX `domains_covering_idx` (`domain`, `tenant_id`, `created_at`)');
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                DB::statement('ALTER TABLE `tenants` ADD INDEX `tenants_covering_idx` (`id`, `logo`, `email`, `is_active`)');
            } catch (\Exception $e) {
                // Index might already exist
            }

            // Optimize for tenant database name generation
            try {
                DB::statement('ALTER TABLE `tenants` ADD INDEX `tenants_id_prefix_idx` (`id`)');
            } catch (\Exception $e) {
                // Index might already exist
            }
        }
    }

    /**
     * Helper method to add indexes safely
     */
    private function addIndexSafely(string $tableName, callable $callback): void
    {
        try {
            Schema::table($tableName, $callback);
        } catch (\Exception $e) {
            // Skip if index already exists
            if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                // Log the error but don't fail the migration
                Log::warning("Could not add index to {$tableName}: " . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafely('domains', [
            'domains_domain_idx',
            'domains_domain_tenant_idx',
            'domains_covering_idx'
        ]);

        $this->dropIndexSafely('tenants', [
            'tenants_id_logo_idx',
            'tenants_id_active_idx',
            'tenants_covering_idx',
            'tenants_id_prefix_idx'
        ]);

        $this->dropIndexSafely('schools', [
            'schools_domain_idx'
        ]);

        $this->dropIndexSafely('student_scores', [
            'student_scores_lookup_idx',
            'student_scores_student_course_idx'
        ]);

        $this->dropIndexSafely('general_settings', [
            'general_settings_common_idx'
        ]);

        $this->dropIndexSafely('users', [
            'users_auth_idx',
            'users_verified_idx'
        ]);

        $this->dropIndexSafely('sessions', [
            'sessions_user_ip_idx',
            'sessions_activity_cleanup_idx'
        ]);
    }

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
