<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function up(): void
    {
        // ===========================================
        // CRITICAL AUTHENTICATION & LOGIN OPTIMIZATION
        // ===========================================

        // Users - Core authentication
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!$this->indexExists('users', 'idx_users_auth_combo')) {
                    $table->index(['email', 'user_type'], 'idx_users_auth_combo');
                }
                if (Schema::hasColumn('users', 'remember_token') && !$this->indexExists('users', 'idx_users_remember_token')) {
                    $table->index('remember_token', 'idx_users_remember_token');
                }
                if (!$this->indexExists('users', 'idx_users_type_status')) {
                    $table->index(['user_type', 'created_at'], 'idx_users_type_status');
                }
            });
        }

        // Teachers - Email lookup optimization (CRITICAL for your Filament resources)
        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (!$this->indexExists('teachers', 'idx_teachers_email_fast')) {
                    $table->index('email', 'idx_teachers_email_fast');
                }
                if (Schema::hasColumn('teachers', 'username') && !$this->indexExists('teachers', 'idx_teachers_username')) {
                    $table->index('username', 'idx_teachers_username');
                }
            });
        }

        // Students - Registration and class lookups
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (!$this->indexExists('students', 'idx_students_class_arm_combo')) {
                    $table->index(['class_id', 'arm_id', 'group_id'], 'idx_students_class_arm_combo');
                }
                if (Schema::hasColumn('students', 'email') && !$this->indexExists('students', 'idx_students_email')) {
                    $table->index('email', 'idx_students_email');
                }
                if (!$this->indexExists('students', 'idx_students_guardian_lookup')) {
                    $table->index(['guardian_id', 'class_id'], 'idx_students_guardian_lookup');
                }
            });
        }

        // ===========================================
        // ACADEMIC STRUCTURE OPTIMIZATION
        // ===========================================

        // School Classes - Teacher relationship (fixes your StudentResource slow query)
        if (Schema::hasTable('school_classes')) {
            Schema::table('school_classes', function (Blueprint $table) {
                if (!$this->indexExists('school_classes', 'idx_classes_teacher_fast')) {
                    $table->index(['teacher_id', 'group_id'], 'idx_classes_teacher_fast');
                }
            });
        }

        // Subjects - Class and teacher relationships
        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (!$this->indexExists('subjects', 'idx_subjects_teacher_class')) {
                    $table->index(['teacher_id', 'class_id'], 'idx_subjects_teacher_class');
                }
                if (!$this->indexExists('subjects', 'idx_subjects_code_fast')) {
                    $table->index('code', 'idx_subjects_code_fast');
                }
            });
        }

        // Academic Years - Status lookup (fixes your ExamResource slow query)
        if (Schema::hasTable('academic_years')) {
            Schema::table('academic_years', function (Blueprint $table) {
                if (!$this->indexExists('academic_years', 'idx_academic_status_fast')) {
                    $table->index(['status', 'starting_date'], 'idx_academic_status_fast');
                }
            });
        }

        // Terms - Status lookup
        if (Schema::hasTable('terms')) {
            Schema::table('terms', function (Blueprint $table) {
                if (!$this->indexExists('terms', 'idx_terms_status_fast')) {
                    $table->index('status', 'idx_terms_status_fast');
                }
            });
        }

        // ===========================================
        // ASSIGNMENTS & EXAMS OPTIMIZATION
        // ===========================================

        // Assignments - Teacher workload (fixes your AssignmentResource slow queries)
        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                if (!$this->indexExists('assignments', 'idx_assignments_teacher_workload')) {
                    $table->index(['teacher_id', 'status', 'deadline'], 'idx_assignments_teacher_workload');
                }
                if (!$this->indexExists('assignments', 'idx_assignments_class_subject')) {
                    $table->index(['class_id', 'subject_id', 'deadline'], 'idx_assignments_class_subject');
                }
                if (Schema::hasColumn('assignments', 'term_id') && Schema::hasColumn('assignments', 'academic_id')) {
                    if (!$this->indexExists('assignments', 'idx_assignments_term_academic')) {
                        $table->index(['term_id', 'academic_id'], 'idx_assignments_term_academic');
                    }
                }
            });
        }

        // Exams - Subject and academic filtering
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (!$this->indexExists('exams', 'idx_exams_academic_term_subject')) {
                    $table->index(['academic_year_id', 'term_id', 'subject_id'], 'idx_exams_academic_term_subject');
                }
                if (!$this->indexExists('exams', 'idx_exams_date_status')) {
                    $table->index(['exam_date', 'is_set'], 'idx_exams_date_status');
                }
            });
        }

        // Question Banks - Exam relationship
        if (Schema::hasTable('question_banks')) {
            Schema::table('question_banks', function (Blueprint $table) {
                if (!$this->indexExists('question_banks', 'idx_questions_exam_type')) {
                    $table->index(['exam_id', 'question_type'], 'idx_questions_exam_type');
                }
            });
        }

        // ===========================================
        // RESULTS & PERFORMANCE OPTIMIZATION
        // ===========================================

        // Course Forms - Student academic records (CRITICAL for result generation)
        if (Schema::hasTable('course_forms')) {
            Schema::table('course_forms', function (Blueprint $table) {
                if (!$this->indexExists('course_forms', 'idx_course_forms_student_academic')) {
                    $table->index(['student_id', 'academic_year_id', 'term_id'], 'idx_course_forms_student_academic');
                }
                if (!$this->indexExists('course_forms', 'idx_course_forms_subject_term')) {
                    $table->index(['subject_id', 'term_id'], 'idx_course_forms_subject_term');
                }
            });
        }

        // Quiz Scores - Result calculation
        if (Schema::hasTable('quiz_scores')) {
            Schema::table('quiz_scores', function (Blueprint $table) {
                if (!$this->indexExists('quiz_scores', 'idx_quiz_scores_student_exam')) {
                    $table->index(['student_id', 'exam_id'], 'idx_quiz_scores_student_exam');
                }
                if (!$this->indexExists('quiz_scores', 'idx_quiz_scores_exam_total')) {
                    $table->index(['exam_id', 'total_score'], 'idx_quiz_scores_exam_total');
                }
            });
        }

        // Quiz Submissions - Analytics
        if (Schema::hasTable('quiz_submissions')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                if (!$this->indexExists('quiz_submissions', 'idx_quiz_submissions_exam_student')) {
                    $table->index(['exam_id', 'student_id', 'correct'], 'idx_quiz_submissions_exam_student');
                }
            });
        }

        // Result Section Student Types - Score calculation
        if (Schema::hasTable('result_section_student_types')) {
            Schema::table('result_section_student_types', function (Blueprint $table) {
                if (!$this->indexExists('result_section_student_types', 'idx_result_section_course')) {
                    $table->index(['course_form_id', 'result_section_type_id'], 'idx_result_section_course');
                }
            });
        }

        // ===========================================
        // ATTENDANCE OPTIMIZATION
        // ===========================================

        // Student Attendance - Daily operations
        if (Schema::hasTable('student_attendances')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                if (!$this->indexExists('student_attendances', 'idx_attendance_date_student')) {
                    $table->index(['date_of_attendance', 'student_id', 'status'], 'idx_attendance_date_student');
                }
                if (!$this->indexExists('student_attendances', 'idx_attendance_teacher_date')) {
                    $table->index(['teacher_id', 'date_of_attendance'], 'idx_attendance_teacher_date');
                }
            });
        }

        // Student Attendance Summary - Reports
        if (Schema::hasTable('student_attendance_summaries')) {
            Schema::table('student_attendance_summaries', function (Blueprint $table) {
                if (!$this->indexExists('student_attendance_summaries', 'idx_attendance_summary')) {
                    $table->index(['student_id', 'term_id', 'academic_id'], 'idx_attendance_summary');
                }
            });
        }

        // ===========================================
        // COMMUNICATION OPTIMIZATION
        // ===========================================

        // Messages - Conversation threading (fixes your Chat.php slow queries)
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (!$this->indexExists('messages', 'idx_messages_conversation_time')) {
                    $table->index(['conversation_id', 'created_at'], 'idx_messages_conversation_time');
                }
                if (!$this->indexExists('messages', 'idx_messages_sender_time')) {
                    $table->index(['sender_id', 'created_at'], 'idx_messages_sender_time');
                }
                if (!$this->indexExists('messages', 'idx_messages_read_status')) {
                    $table->index(['is_read', 'created_at'], 'idx_messages_read_status');
                }
            });
        }

        // Conversations - Last message ordering
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                if (!$this->indexExists('conversations', 'idx_conversations_last_message')) {
                    $table->index('last_message_at', 'idx_conversations_last_message');
                }
            });
        }

        // Conversation User - User conversations
        if (Schema::hasTable('conversation_user')) {
            Schema::table('conversation_user', function (Blueprint $table) {
                if (!$this->indexExists('conversation_user', 'idx_conversation_user_read')) {
                    $table->index(['user_id', 'last_read_at'], 'idx_conversation_user_read');
                }
            });
        }

        // ===========================================
        // FINANCIAL SYSTEM OPTIMIZATION
        // ===========================================

        // Invoice Students - Payment tracking
        if (Schema::hasTable('invoice_students')) {
            Schema::table('invoice_students', function (Blueprint $table) {
                if (!$this->indexExists('invoice_students', 'idx_invoice_student_status')) {
                    $table->index(['student_id', 'status', 'term_id'], 'idx_invoice_student_status');
                }
                if (!$this->indexExists('invoice_students', 'idx_invoice_order_code')) {
                    $table->index('order_code', 'idx_invoice_order_code');
                }
            });
        }

        // School Payments - Transaction processing
        if (Schema::hasTable('school_payments')) {
            Schema::table('school_payments', function (Blueprint $table) {
                if (!$this->indexExists('school_payments', 'idx_payments_status_date')) {
                    $table->index(['status', 'created_at'], 'idx_payments_status_date');
                }
                if (!$this->indexExists('school_payments', 'idx_payments_payable')) {
                    $table->index(['payable_type', 'payable_id'], 'idx_payments_payable');
                }
            });
        }

        // ===========================================
        // NOTIFICATION SYSTEM OPTIMIZATION
        // ===========================================

        // Notifications - User notification feeds
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (!$this->indexExists('notifications', 'idx_notifications_user_read')) {
                    $table->index(['notifiable_id', 'notifiable_type', 'read_at'], 'idx_notifications_user_read');
                }
                if (!$this->indexExists('notifications', 'idx_notifications_created')) {
                    $table->index('created_at', 'idx_notifications_created');
                }
            });
        }

        // ===========================================
        // LIBRARY SYSTEM OPTIMIZATION
        // ===========================================

        // Library Books - Search and availability
        if (Schema::hasTable('library_books')) {
            Schema::table('library_books', function (Blueprint $table) {
                if (!$this->indexExists('library_books', 'idx_library_books_category')) {
                    $table->index(['library_category_id', 'author'], 'idx_library_books_category');
                }
                if (!$this->indexExists('library_books', 'idx_library_books_isbn')) {
                    $table->index('isbn', 'idx_library_books_isbn');
                }
            });
        }

        // Library Book Loans - Borrowing status
        if (Schema::hasTable('library_book_loans')) {
            Schema::table('library_book_loans', function (Blueprint $table) {
                if (!$this->indexExists('library_book_loans', 'idx_book_loans_borrower_status')) {
                    $table->index(['borrower_type', 'borrower_id', 'status'], 'idx_book_loans_borrower_status');
                }
                if (!$this->indexExists('library_book_loans', 'idx_book_loans_due_date')) {
                    $table->index(['due_date', 'status'], 'idx_book_loans_due_date');
                }
            });
        }

        // ===========================================
        // HOSTEL MANAGEMENT OPTIMIZATION
        // ===========================================

        // Hostel Assignments - Room and student tracking
        if (Schema::hasTable('hostel_assignments')) {
            Schema::table('hostel_assignments', function (Blueprint $table) {
                if (!$this->indexExists('hostel_assignments', 'idx_hostel_student_academic')) {
                    $table->index(['student_id', 'academic_id', 'term_id'], 'idx_hostel_student_academic');
                }
                if (!$this->indexExists('hostel_assignments', 'idx_hostel_room_dates')) {
                    $table->index(['hostel_room_id', 'assignment_date', 'release_date'], 'idx_hostel_room_dates');
                }
            });
        }

        // ===========================================
        // ANNOUNCEMENTS & EVENTS OPTIMIZATION
        // ===========================================

        // Announcements - User type filtering
        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                if (!$this->indexExists('announcements', 'idx_announcements_type_date')) {
                    $table->index(['type_of_user_sent_to', 'created_at'], 'idx_announcements_type_date');
                }
                if (!$this->indexExists('announcements', 'idx_announcements_from')) {
                    $table->index('from_id', 'idx_announcements_from');
                }
            });
        }

        // ===========================================
        // TIMETABLE & SCHEDULING OPTIMIZATION
        // ===========================================

        // Routines - Timetable queries
        if (Schema::hasTable('routines')) {
            Schema::table('routines', function (Blueprint $table) {
                if (!$this->indexExists('routines', 'idx_routines_class_day')) {
                    $table->index(['class_id', 'day', 'start_time'], 'idx_routines_class_day');
                }
                if (!$this->indexExists('routines', 'idx_routines_teacher_day')) {
                    $table->index(['teacher_id', 'day'], 'idx_routines_teacher_day');
                }
            });
        }

        // Lectures - Class scheduling
        if (Schema::hasTable('lectures')) {
            Schema::table('lectures', function (Blueprint $table) {
                if (!$this->indexExists('lectures', 'idx_lectures_subject_teacher')) {
                    $table->index(['subject_id', 'teacher_id'], 'idx_lectures_subject_teacher');
                }
                if (!$this->indexExists('lectures', 'idx_lectures_date')) {
                    $table->index('date_of_meeting', 'idx_lectures_date');
                }
            });
        }

        // ===========================================
        // PSYCHOMOTOR ASSESSMENT OPTIMIZATION
        // ===========================================

        // Psychomotor Students - Assessment tracking
        if (Schema::hasTable('pyschomotor_students')) {
            Schema::table('pyschomotor_students', function (Blueprint $table) {
                if (!$this->indexExists('pyschomotor_students', 'idx_psychomotor_student')) {
                    $table->index(['student_id', 'psychomotor_id'], 'idx_psychomotor_student');
                }
            });
        }

        // ===========================================
        // SYSTEM ADMINISTRATION OPTIMIZATION
        // ===========================================

        // Audit Logs - System monitoring
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (!$this->indexExists('audit_logs', 'idx_audit_logs_user_date')) {
                    $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_date');
                }
                if (!$this->indexExists('audit_logs', 'idx_audit_logs_model')) {
                    $table->index(['model_type', 'model_id'], 'idx_audit_logs_model');
                }
            });
        }

        // Media - File management
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
                if (!$this->indexExists('media', 'idx_media_model_collection')) {
                    $table->index(['model_type', 'model_id', 'collection_name'], 'idx_media_model_collection');
                }
            });
        }

        // ===========================================
        // TABLE PARTITIONING FOR SCALE
        // ===========================================

        // Partition large tables by year for better performance
        if (DB::getDriverName() === 'mysql') {
            try {
                // Partition messages table by year (will grow exponentially)
                DB::statement("
                    ALTER TABLE messages
                    PARTITION BY RANGE (YEAR(created_at)) (
                        PARTITION p2024 VALUES LESS THAN (2025),
                        PARTITION p2025 VALUES LESS THAN (2026),
                        PARTITION p2026 VALUES LESS THAN (2027),
                        PARTITION p_future VALUES LESS THAN MAXVALUE
                    )
                ");
            } catch (\Exception $e) {
                // Table might already be partitioned or not support it
            }

            try {
                // Partition notifications table by year
                DB::statement("
                    ALTER TABLE notifications
                    PARTITION BY RANGE (YEAR(created_at)) (
                        PARTITION p2024 VALUES LESS THAN (2025),
                        PARTITION p2025 VALUES LESS THAN (2026),
                        PARTITION p2026 VALUES LESS THAN (2027),
                        PARTITION p_future VALUES LESS THAN MAXVALUE
                    )
                ");
            } catch (\Exception $e) {
                // Table might already be partitioned or not support it
            }
        }

        // ===========================================
        // MYSQL CONFIGURATION OPTIMIZATION
        // ===========================================

        // Set optimal MySQL settings for million users
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement("SET GLOBAL innodb_buffer_pool_size = '4G'");
                DB::statement("SET GLOBAL query_cache_size = '256M'");
                DB::statement("SET GLOBAL tmp_table_size = '64M'");
                DB::statement("SET GLOBAL max_heap_table_size = '64M'");
            } catch (\Exception $e) {
                // These might require admin privileges
            }
        }
    }

    public function down(): void
    {
        // Drop all indexes created in up() method
        $indexesToDrop = [
            'users' => ['idx_users_auth_combo', 'idx_users_remember_token', 'idx_users_type_status'],
            'teachers' => ['idx_teachers_email_fast', 'idx_teachers_username'],
            'students' => ['idx_students_class_arm_combo', 'idx_students_email', 'idx_students_guardian_lookup'],
            'school_classes' => ['idx_classes_teacher_fast'],
            'subjects' => ['idx_subjects_teacher_class', 'idx_subjects_code_fast'],
            'academic_years' => ['idx_academic_status_fast'],
            'terms' => ['idx_terms_status_fast'],
            'assignments' => ['idx_assignments_teacher_workload', 'idx_assignments_class_subject', 'idx_assignments_term_academic'],
            'exams' => ['idx_exams_academic_term_subject', 'idx_exams_date_status'],
            'question_banks' => ['idx_questions_exam_type'],
            'course_forms' => ['idx_course_forms_student_academic', 'idx_course_forms_subject_term'],
            'quiz_scores' => ['idx_quiz_scores_student_exam', 'idx_quiz_scores_exam_total'],
            'quiz_submissions' => ['idx_quiz_submissions_exam_student'],
            'result_section_student_types' => ['idx_result_section_course'],
            'student_attendances' => ['idx_attendance_date_student', 'idx_attendance_teacher_date'],
            'student_attendance_summaries' => ['idx_attendance_summary'],
            'messages' => ['idx_messages_conversation_time', 'idx_messages_sender_time', 'idx_messages_read_status'],
            'conversations' => ['idx_conversations_last_message'],
            'conversation_user' => ['idx_conversation_user_read'],
            'invoice_students' => ['idx_invoice_student_status', 'idx_invoice_order_code'],
            'school_payments' => ['idx_payments_status_date', 'idx_payments_payable'],
            'notifications' => ['idx_notifications_user_read', 'idx_notifications_created'],
            'library_books' => ['idx_library_books_category', 'idx_library_books_isbn'],
            'library_book_loans' => ['idx_book_loans_borrower_status', 'idx_book_loans_due_date'],
            'hostel_assignments' => ['idx_hostel_student_academic', 'idx_hostel_room_dates'],
            'announcements' => ['idx_announcements_type_date', 'idx_announcements_from'],
            'routines' => ['idx_routines_class_day', 'idx_routines_teacher_day'],
            'lectures' => ['idx_lectures_subject_teacher', 'idx_lectures_date'],
            'pyschomotor_students' => ['idx_psychomotor_student'],
            'audit_logs' => ['idx_audit_logs_user_date', 'idx_audit_logs_model'],
            'media' => ['idx_media_model_collection'],
        ];

        foreach ($indexesToDrop as $tableName => $indexes) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexes, $tableName) {
                    foreach ($indexes as $indexName) {
                        try {
                            if ($this->indexExists($tableName, $indexName)) {
                                $table->dropIndex($indexName);
                            }
                        } catch (\Exception $e) {
                            // Continue if index doesn't exist
                        }
                    }
                });
            }
        }
    }
};
