<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * CRITICAL INDEXES STILL NEEDED WITH TOMATOPHP/FILAMENT-TENANCY
     *
     * The tenancy package handles tenant isolation, but you still need
     * these performance optimizations within each tenant database.
     */
    public function up(): void
    {
        // ===========================================
        // 1. FOREIGN KEY INDEXES (STILL CRITICAL!)
        // ===========================================

        // Even with tenancy, your relationships need proper indexing
        $foreignKeyTables = [
            'students' => ['class_id', 'guardian_id', 'section_id', 'group_id', 'arm_id'],
            'assignments' => ['class_id', 'subject_id', 'teacher_id', 'term_id', 'academic_id'],
            'exams' => ['academic_year_id', 'subject_id', 'term_id'],
            'student_attendances' => ['student_id', 'teacher_id'],
            'quiz_scores' => ['student_id', 'exam_id', 'course_form_id'],
            'course_forms' => ['student_id', 'subject_id', 'academic_year_id', 'term_id'],
            'school_sections' => ['class_id', 'teacher_id'],
            'subjects' => ['class_id', 'teacher_id', 'subject_depot_id'],
            'messages' => ['conversation_id', 'sender_id'],
            'invoice_students' => ['student_id', 'term_id', 'academic_id'],
        ];

        foreach ($foreignKeyTables as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table, $columns) {
                    foreach ($columns as $column) {
                        if (Schema::hasColumn($table, $column)) {
                            $indexName = 'idx_' . $table . '_' . $column;
                            if (!$this->indexExists($table, $indexName)) {
                                $tableBlueprint->index($column, $indexName);
                            }
                        }
                    }
                });
            }
        }

        // ===========================================
        // 2. FILAMENT RESOURCE OPTIMIZATION
        // ===========================================

        // StudentResource - STILL needs these indexes for fast listing
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                // Class + Section filtering (very common in school systems)
                if (!$this->indexExists('students', 'idx_students_class_section_list')) {
                    $table->index(['class_id', 'section_id', 'created_at'], 'idx_students_class_section_list');
                }

                // Search optimization (name + registration number)
                if (!$this->indexExists('students', 'idx_students_search')) {
                    $table->index(['name', 'registration_number'], 'idx_students_search');
                }

                // Academic grouping filters
                if (!$this->indexExists('students', 'idx_students_academic_filters')) {
                    $table->index(['class_id', 'arm_id', 'group_id'], 'idx_students_academic_filters');
                }
            });
        }

        // AssignmentResource - Teacher workload queries
        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                // Teacher assignment listing
                if (!$this->indexExists('assignments', 'idx_assignments_teacher_list')) {
                    $table->index(['teacher_id', 'status', 'deadline'], 'idx_assignments_teacher_list');
                }

                // Class subject filtering
                if (!$this->indexExists('assignments', 'idx_assignments_class_subject')) {
                    $table->index(['class_id', 'subject_id', 'deadline'], 'idx_assignments_class_subject');
                }

                // Academic term filtering
                if (Schema::hasColumn('assignments', 'term_id') && Schema::hasColumn('assignments', 'academic_id')) {
                    if (!$this->indexExists('assignments', 'idx_assignments_academic_term')) {
                        $table->index(['academic_id', 'term_id', 'status'], 'idx_assignments_academic_term');
                    }
                }
            });
        }

        // ===========================================
        // 3. ATTENDANCE OPTIMIZATION (HIGH VOLUME!)
        // ===========================================

        // This will grow HUGE in each tenant - needs proper indexing
        if (Schema::hasTable('student_attendances')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                // Daily attendance marking (most frequent operation)
                if (!$this->indexExists('student_attendances', 'idx_attendance_daily')) {
                    $table->index(['date_of_attendance', 'teacher_id', 'status'], 'idx_attendance_daily');
                }

                // Student attendance history
                if (!$this->indexExists('student_attendances', 'idx_attendance_student_history')) {
                    $table->index(['student_id', 'date_of_attendance'], 'idx_attendance_student_history');
                }

                // Attendance reports (date range queries)
                if (!$this->indexExists('student_attendances', 'idx_attendance_reports')) {
                    $table->index(['date_of_attendance', 'status'], 'idx_attendance_reports');
                }
            });
        }

        // ===========================================
        // 4. RESULT SYSTEM OPTIMIZATION
        // ===========================================

        // Course Forms - Student-Subject enrollment
        if (Schema::hasTable('course_forms')) {
            Schema::table('course_forms', function (Blueprint $table) {
                // Student result compilation
                if (!$this->indexExists('course_forms', 'idx_course_forms_student_results')) {
                    $table->index(['student_id', 'academic_year_id', 'term_id'], 'idx_course_forms_student_results');
                }

                // Subject enrollment tracking
                if (!$this->indexExists('course_forms', 'idx_course_forms_subject_enrollment')) {
                    $table->index(['subject_id', 'term_id', 'academic_year_id'], 'idx_course_forms_subject_enrollment');
                }
            });
        }

        // Quiz Scores - Performance analytics
        if (Schema::hasTable('quiz_scores')) {
            Schema::table('quiz_scores', function (Blueprint $table) {
                // Student performance tracking
                if (!$this->indexExists('quiz_scores', 'idx_quiz_scores_student_performance')) {
                    $table->index(['student_id', 'exam_id', 'total_score'], 'idx_quiz_scores_student_performance');
                }

                // Exam analysis
                if (!$this->indexExists('quiz_scores', 'idx_quiz_scores_exam_analysis')) {
                    $table->index(['exam_id', 'approved', 'total_score'], 'idx_quiz_scores_exam_analysis');
                }
            });
        }

        // ===========================================
        // 5. COMMUNICATION OPTIMIZATION
        // ===========================================

        // Messages - Chat performance
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                // Conversation message timeline
                if (!$this->indexExists('messages', 'idx_messages_conversation_timeline')) {
                    $table->index(['conversation_id', 'created_at'], 'idx_messages_conversation_timeline');
                }

                // Unread message tracking
                if (!$this->indexExists('messages', 'idx_messages_read_tracking')) {
                    $table->index(['conversation_id', 'is_read'], 'idx_messages_read_tracking');
                }
            });
        }

        // ===========================================
        // 6. FINANCIAL SYSTEM OPTIMIZATION
        // ===========================================

        // Invoice Students - Payment tracking
        if (Schema::hasTable('invoice_students')) {
            Schema::table('invoice_students', function (Blueprint $table) {
                // Outstanding payments
                if (!$this->indexExists('invoice_students', 'idx_invoice_outstanding')) {
                    $table->index(['status', 'academic_id', 'term_id'], 'idx_invoice_outstanding');
                }

                // Student payment history
                if (!$this->indexExists('invoice_students', 'idx_invoice_student_history')) {
                    $table->index(['student_id', 'academic_id', 'status'], 'idx_invoice_student_history');
                }
            });
        }

        // ===========================================
        // 7. MYSQL SPECIFIC OPTIMIZATIONS
        // ===========================================

        if (DB::getDriverName() === 'mysql') {
            // Full-text search for tenant-specific content
            try {
                DB::statement('ALTER TABLE students ADD FULLTEXT(name, email) WITH PARSER ngram');
                DB::statement('ALTER TABLE teachers ADD FULLTEXT(name, email) WITH PARSER ngram');
                DB::statement('ALTER TABLE assignments ADD FULLTEXT(title, description) WITH PARSER ngram');
                DB::statement('ALTER TABLE announcements ADD FULLTEXT(title, text) WITH PARSER ngram');
            } catch (\Exception $e) {
                // Continue if indexes already exist
            }
        }

        // ===========================================
        // 8. TABLE PARTITIONING FOR LARGE TABLES
        // ===========================================

        // Even with tenancy, these tables grow large within each tenant
        if (DB::getDriverName() === 'mysql') {
            try {
                // Partition messages by month (chat grows fast)
                DB::statement("
                    ALTER TABLE messages
                    PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
                        PARTITION p202401 VALUES LESS THAN (202402),
                        PARTITION p202402 VALUES LESS THAN (202403),
                        PARTITION p202403 VALUES LESS THAN (202404),
                        PARTITION p202404 VALUES LESS THAN (202405),
                        PARTITION p202405 VALUES LESS THAN (202406),
                        PARTITION p202406 VALUES LESS THAN (202407),
                        PARTITION p_future VALUES LESS THAN MAXVALUE
                    )
                ");

                // Partition student_attendances by month
                DB::statement("
                    ALTER TABLE student_attendances
                    PARTITION BY RANGE (YEAR(date_of_attendance) * 100 + MONTH(date_of_attendance)) (
                        PARTITION p202401 VALUES LESS THAN (202402),
                        PARTITION p202402 VALUES LESS THAN (202403),
                        PARTITION p202403 VALUES LESS THAN (202404),
                        PARTITION p202404 VALUES LESS THAN (202405),
                        PARTITION p202405 VALUES LESS THAN (202406),
                        PARTITION p202406 VALUES LESS THAN (202407),
                        PARTITION p_future VALUES LESS THAN MAXVALUE
                    )
                ");
            } catch (\Exception $e) {
                // Tables might already be partitioned
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

    public function down(): void
    {
        // Rollback implementation...
    }
};
