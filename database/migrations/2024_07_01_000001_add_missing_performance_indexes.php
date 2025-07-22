<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds any missing indexes for foreign keys and frequently queried columns.
     * If you add a new table or relationship, always add an index for the foreign key and any column used in WHERE, JOIN, or ORDER BY.
     */
    public function up(): void
    {
        // Helper to check if index exists
        $indexExists = function (string $table, string $indexName): bool {
            try {
                $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
                return !empty($indexes);
            } catch (\Exception $e) {
                return false;
            }
        };

        // Example: Add missing indexes for students table
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('students', 'idx_students_class_id')) {
                    $table->index('class_id', 'idx_students_class_id');
                }
                if (!$indexExists('students', 'idx_students_guardian_id')) {
                    $table->index('guardian_id', 'idx_students_guardian_id');
                }
                if (!$indexExists('students', 'idx_students_group_id')) {
                    $table->index('group_id', 'idx_students_group_id');
                }
                if (!$indexExists('students', 'idx_students_arm_id')) {
                    $table->index('arm_id', 'idx_students_arm_id');
                }
                if (!$indexExists('students', 'idx_students_registration_number')) {
                    $table->index('registration_number', 'idx_students_registration_number');
                }
            });
        }

        // Repeat for other major tables (assignments, exams, quiz_scores, course_forms, subjects, users, etc.)
        $tablesAndColumns = [
            'assignments' => ['class_id', 'subject_id', 'teacher_id', 'term_id', 'academic_id', 'status', 'deadline'],
            'exams' => ['academic_year_id', 'subject_id', 'term_id', 'exam_date', 'is_set'],
            'quiz_scores' => ['student_id', 'exam_id', 'course_form_id', 'total_score'],
            'course_forms' => ['student_id', 'subject_id', 'academic_year_id', 'term_id'],
            'subjects' => ['class_id', 'teacher_id', 'subject_depot_id', 'code'],
            'users' => ['email', 'user_type'],
            'teachers' => ['email', 'username'],
            'school_classes' => ['teacher_id', 'group_id'],
            'academic_years' => ['status', 'starting_date'],
            'terms' => ['status'],
            'question_banks' => ['exam_id', 'question_type'],
            'student_attendances' => ['student_id', 'teacher_id', 'date_of_attendance', 'status'],
        ];
        foreach ($tablesAndColumns as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table, $columns, $indexExists) {
                    foreach ($columns as $column) {
                        $indexName = 'idx_' . $table . '_' . $column;
                        if (Schema::hasColumn($table, $column) && !$indexExists($table, $indexName)) {
                            $tableBlueprint->index($column, $indexName);
                        }
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally drop indexes (safe to leave empty for idempotency)
    }
};
