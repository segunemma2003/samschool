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
        // Only add columns that don't exist to prevent errors
        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                // Add priority column if it doesn't exist
                if (!Schema::hasColumn('announcements', 'priority')) {
                    $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                          ->default('medium')
                          ->after('type_of_user_sent_to');
                }

                // Add status column if it doesn't exist
                if (!Schema::hasColumn('announcements', 'status')) {
                    $table->enum('status', ['draft', 'published', 'archived'])
                          ->default('published')
                          ->after('priority');
                }

                // Add expires_at column if it doesn't exist
                if (!Schema::hasColumn('announcements', 'expires_at')) {
                    $table->timestamp('expires_at')
                          ->nullable()
                          ->after('status');
                }

                // Add views_count column if it doesn't exist
                if (!Schema::hasColumn('announcements', 'views_count')) {
                    $table->unsignedInteger('views_count')
                          ->default(0)
                          ->after('expires_at');
                }
            });
        }

        // Add performance indexes safely
        $this->addIndexesSafely();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Drop columns in reverse order
            if (Schema::hasColumn('announcements', 'views_count')) {
                $table->dropColumn('views_count');
            }
            if (Schema::hasColumn('announcements', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
            if (Schema::hasColumn('announcements', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('announcements', 'priority')) {
                $table->dropColumn('priority');
            }
        });

        // Drop indexes
        $this->dropIndexesSafely();
    }

    /**
     * Add database indexes safely
     */
    private function addIndexesSafely(): void
    {
        try {
            // Check if indexes exist before creating them
            if (!$this->indexExists('announcements', 'idx_announcements_performance')) {
                DB::statement('CREATE INDEX idx_announcements_performance ON announcements (type_of_user_sent_to, from_id, created_at)');
            }

            if (!$this->indexExists('announcements', 'idx_announcements_search')) {
                DB::statement('CREATE INDEX idx_announcements_search ON announcements (title(50))');
            }

            // Add priority index only if column exists
            if (Schema::hasColumn('announcements', 'priority') && !$this->indexExists('announcements', 'idx_announcements_priority')) {
                DB::statement('CREATE INDEX idx_announcements_priority ON announcements (priority, created_at)');
            }

            // Add expires_at index only if column exists
            if (Schema::hasColumn('announcements', 'expires_at') && !$this->indexExists('announcements', 'idx_announcements_expires')) {
                DB::statement('CREATE INDEX idx_announcements_expires ON announcements (expires_at)');
            }

        } catch (\Exception $e) {
            // Log the error but don't fail the migration
            Log::warning('Could not create some announcement indexes: ' . $e->getMessage());
        }
    }

    /**
     * Drop indexes safely
     */
    private function dropIndexesSafely(): void
    {
        $indexes = [
            'idx_announcements_performance',
            'idx_announcements_search',
            'idx_announcements_priority',
            'idx_announcements_expires'
        ];

        foreach ($indexes as $index) {
            try {
                if ($this->indexExists('announcements', $index)) {
                    DB::statement("DROP INDEX {$index} ON announcements");
                }
            } catch (\Exception $e) {
                // Continue if index doesn't exist
            }
        }
    }

    /**
     * Check if an index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
};
