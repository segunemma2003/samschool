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
        // Add missing columns to assignments table if they don't exist
        Schema::table('assignments', function (Blueprint $table) {
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('assignments', 'status')) {
                $table->enum('status', ['draft', 'available', 'closed'])
                      ->default('available')
                      ->after('weight_mark');
            }

            // Add allow_late_submission column if it doesn't exist
            if (!Schema::hasColumn('assignments', 'allow_late_submission')) {
                $table->tinyInteger('allow_late_submission')
                      ->default(0)
                      ->after('status')
                      ->comment('0=Not allowed, 1=Allowed with penalty, 2=Allowed without penalty');
            }

            // Make sure teacher_id exists and is properly indexed
            if (!Schema::hasColumn('assignments', 'teacher_id')) {
                $table->foreignId('teacher_id')
                      ->after('subject_id')
                      ->constrained('teachers')
                      ->onDelete('cascade');
            }
        });

        // Add critical performance indexes for assignments
        $this->addIndexesSafely();

        // Optimize assignment_student pivot table
        if (Schema::hasTable('assignment_student')) {
            Schema::table('assignment_student', function (Blueprint $table) {
                // Add indexes for better performance
                if (!$this->indexExists('assignment_student', 'idx_assignment_student_status')) {
                    $table->index(['assignment_id', 'status'], 'idx_assignment_student_status');
                }

                if (!$this->indexExists('assignment_student', 'idx_assignment_student_score')) {
                    $table->index(['assignment_id', 'total_score'], 'idx_assignment_student_score');
                }

                if (!$this->indexExists('assignment_student', 'idx_assignment_student_updated')) {
                    $table->index(['assignment_id', 'updated_at'], 'idx_assignment_student_updated');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'allow_late_submission')) {
                $table->dropColumn('allow_late_submission');
            }
            if (Schema::hasColumn('assignments', 'status')) {
                $table->dropColumn('status');
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
            // Assignment performance indexes
            if (!$this->indexExists('assignments', 'idx_assignments_teacher_performance')) {
                DB::statement('CREATE INDEX idx_assignments_teacher_performance ON assignments (teacher_id, status, created_at DESC)');
            }

            if (!$this->indexExists('assignments', 'idx_assignments_deadline_status')) {
                DB::statement('CREATE INDEX idx_assignments_deadline_status ON assignments (deadline, status)');
            }

            if (!$this->indexExists('assignments', 'idx_assignments_class_subject')) {
                DB::statement('CREATE INDEX idx_assignments_class_subject ON assignments (class_id, subject_id, deadline)');
            }

            if (!$this->indexExists('assignments', 'idx_assignments_term_academic')) {
                DB::statement('CREATE INDEX idx_assignments_term_academic ON assignments (term_id, academic_id, created_at)');
            }

            if (!$this->indexExists('assignments', 'idx_assignments_search')) {
                DB::statement('CREATE INDEX idx_assignments_search ON assignments (title(50), status)');
            }

            // Teacher optimization indexes
            if (!$this->indexExists('teachers', 'idx_teachers_email_fast')) {
                DB::statement('CREATE INDEX idx_teachers_email_fast ON teachers (email)');
            }

            // Class and subject optimization
            if (!$this->indexExists('school_classes', 'idx_classes_teacher_fast')) {
                DB::statement('CREATE INDEX idx_classes_teacher_fast ON school_classes (teacher_id, id, name)');
            }

            if (!$this->indexExists('subjects', 'idx_subjects_teacher_fast')) {
                DB::statement('CREATE INDEX idx_subjects_teacher_fast ON subjects (teacher_id, id, name, code)');
            }

            // Student assignments optimization
            if (!$this->indexExists('assignment_student', 'idx_assignment_student_composite')) {
                DB::statement('CREATE INDEX idx_assignment_student_composite ON assignment_student (assignment_id, student_id, status, updated_at)');
            }

        } catch (\Exception $e) {
            Log::warning('Could not create some assignment indexes: ' . $e->getMessage());
        }
    }

    /**
     * Drop indexes safely
     */
    private function dropIndexesSafely(): void
    {
        $indexes = [
            'assignments' => [
                'idx_assignments_teacher_performance',
                'idx_assignments_deadline_status',
                'idx_assignments_class_subject',
                'idx_assignments_term_academic',
                'idx_assignments_search'
            ],
            'teachers' => [
                'idx_teachers_email_fast'
            ],
            'school_classes' => [
                'idx_classes_teacher_fast'
            ],
            'subjects' => [
                'idx_subjects_teacher_fast'
            ],
            'assignment_student' => [
                'idx_assignment_student_status',
                'idx_assignment_student_score',
                'idx_assignment_student_updated',
                'idx_assignment_student_composite'
            ]
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                try {
                    if ($this->indexExists($table, $index)) {
                        DB::statement("DROP INDEX {$index} ON {$table}");
                    }
                } catch (\Exception $e) {
                    // Continue if index doesn't exist
                }
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
