<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if an index exists on a table
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

    /**
     * Run the migrations.
     *
     * Core database performance optimizations that were missing from the previous migration.
     * These are the fundamental indexes that handle the most frequent queries.
     */
    public function up(): void
    {
        // ========================================
        // CORE PERFORMANCE OPTIMIZATIONS
        // (Missing from previous migration)
        // ========================================

        // Users and Authentication Optimization
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!$this->indexExists('users', 'idx_users_user_type')) {
                    $table->index('user_type', 'idx_users_user_type');
                }
                if (!$this->indexExists('users', 'idx_users_email_verified')) {
                    $table->index('email_verified_at', 'idx_users_email_verified');
                }
                if (!$this->indexExists('users', 'idx_users_type_created')) {
                    $table->index(['user_type', 'created_at'], 'idx_users_type_created');
                }
            });
        }

        // Students Core Optimization (Most Important!)
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (!$this->indexExists('students', 'idx_students_class_section_group')) {
                    $table->index(['class_id', 'section_id', 'group_id'], 'idx_students_class_section_group');
                }
                if (!$this->indexExists('students', 'idx_students_class_arm')) {
                    $table->index(['class_id', 'arm_id'], 'idx_students_class_arm');
                }
                if (!$this->indexExists('students', 'idx_students_blood_group')) {
                    $table->index('blood_group', 'idx_students_blood_group');
                }
                if (!$this->indexExists('students', 'idx_students_reg_number')) {
                    $table->index('registration_number', 'idx_students_reg_number');
                }
                if (!$this->indexExists('students', 'idx_students_guardian_class')) {
                    $table->index(['guardian_id', 'class_id'], 'idx_students_guardian_class');
                }
            });
        }

        // Teachers Core Optimization
        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (!$this->indexExists('teachers', 'idx_teachers_designation')) {
                    $table->index('designation', 'idx_teachers_designation');
                }
                if (!$this->indexExists('teachers', 'idx_teachers_gender_joining')) {
                    $table->index(['gender', 'joining_date'], 'idx_teachers_gender_joining');
                }
            });
        }

        // Academic Years and Terms (Critical for filtering)
        if (Schema::hasTable('academic_years')) {
            Schema::table('academic_years', function (Blueprint $table) {
                if (!$this->indexExists('academic_years', 'idx_academic_status_start')) {
                    $table->index(['status', 'starting_date'], 'idx_academic_status_start');
                }
            });
        }

        if (Schema::hasTable('terms')) {
            Schema::table('terms', function (Blueprint $table) {
                if (!$this->indexExists('terms', 'idx_terms_status')) {
                    $table->index('status', 'idx_terms_status');
                }
            });
        }

        // Classes and Sections (Very Important!)
        if (Schema::hasTable('school_classes')) {
            Schema::table('school_classes', function (Blueprint $table) {
                if (!$this->indexExists('school_classes', 'idx_classes_teacher_group')) {
                    $table->index(['teacher_id', 'group_id'], 'idx_classes_teacher_group');
                }
                if (!$this->indexExists('school_classes', 'idx_classes_numeric')) {
                    $table->index('class_numeric', 'idx_classes_numeric');
                }
            });
        }

        if (Schema::hasTable('school_sections')) {
            Schema::table('school_sections', function (Blueprint $table) {
                if (!$this->indexExists('school_sections', 'idx_sections_class_teacher')) {
                    $table->index(['class_id', 'teacher_id'], 'idx_sections_class_teacher');
                }
                if (!$this->indexExists('school_sections', 'idx_sections_category')) {
                    $table->index('category', 'idx_sections_category');
                }
            });
        }

        // Subjects Optimization (Critical for academic queries)
        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (!$this->indexExists('subjects', 'idx_subjects_class_teacher')) {
                    $table->index(['class_id', 'teacher_id'], 'idx_subjects_class_teacher');
                }
                if (!$this->indexExists('subjects', 'idx_subjects_type_class')) {
                    $table->index(['type', 'class_id'], 'idx_subjects_type_class');
                }
                if (!$this->indexExists('subjects', 'idx_subjects_depot')) {
                    $table->index('subject_depot_id', 'idx_subjects_depot');
                }
                if (!$this->indexExists('subjects', 'idx_subjects_code')) {
                    $table->index('code', 'idx_subjects_code');
                }
            });
        }

        // Assignments Core Optimization (High Impact!)
        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                if (!$this->indexExists('assignments', 'idx_assignments_class_subject_deadline')) {
                    $table->index(['class_id', 'subject_id', 'deadline'], 'idx_assignments_class_subject_deadline');
                }
                if (!$this->indexExists('assignments', 'idx_assignments_teacher_status')) {
                    $table->index(['teacher_id', 'status'], 'idx_assignments_teacher_status');
                }
                if (Schema::hasColumn('assignments', 'term_id') && Schema::hasColumn('assignments', 'academic_id')) {
                    if (!$this->indexExists('assignments', 'idx_assignments_term_academic')) {
                        $table->index(['term_id', 'academic_id'], 'idx_assignments_term_academic');
                    }
                }
            });
        }

        // Exams Core Optimization (Very Important!)
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (!$this->indexExists('exams', 'idx_exams_academic_term_subject')) {
                    $table->index(['academic_year_id', 'term_id', 'subject_id'], 'idx_exams_academic_term_subject');
                }
                if (!$this->indexExists('exams', 'idx_exams_date_isset')) {
                    $table->index(['exam_date', 'is_set'], 'idx_exams_date_isset');
                }
                if (!$this->indexExists('exams', 'idx_exams_assessment_type')) {
                    $table->index('assessment_type', 'idx_exams_assessment_type');
                }
            });
        }

        // Basic Attendance Optimization (High Impact!)
        if (Schema::hasTable('student_attendances')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                if (!$this->indexExists('student_attendances', 'idx_student_attendance_date')) {
                    $table->index(['student_id', 'date_of_attendance'], 'idx_student_attendance_date');
                }
                if (!$this->indexExists('student_attendances', 'idx_student_attendance_teacher_date')) {
                    $table->index(['teacher_id', 'date_of_attendance'], 'idx_student_attendance_teacher_date');
                }
                if (!$this->indexExists('student_attendances', 'idx_student_attendance_status_date')) {
                    $table->index(['status', 'date_of_attendance'], 'idx_student_attendance_status_date');
                }
            });
        }

        if (Schema::hasTable('teacher_attendances')) {
            Schema::table('teacher_attendances', function (Blueprint $table) {
                if (!$this->indexExists('teacher_attendances', 'idx_teacher_attendance_date')) {
                    $table->index(['teacher_id', 'date_of_attendance'], 'idx_teacher_attendance_date');
                }
                if (!$this->indexExists('teacher_attendances', 'idx_teacher_attendance_status_date')) {
                    $table->index(['status', 'date_of_attendance'], 'idx_teacher_attendance_status_date');
                }
            });
        }

        // Results and Marks Core Optimization (Critical!)
        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                if (!$this->indexExists('results', 'idx_results_student_subject')) {
                    $table->index(['student_id', 'subject_id'], 'idx_results_student_subject');
                }
                if (!$this->indexExists('results', 'idx_results_teacher_student')) {
                    $table->index(['teacher_id', 'student_id'], 'idx_results_teacher_student');
                }
            });
        }

        if (Schema::hasTable('marks')) {
            Schema::table('marks', function (Blueprint $table) {
                if (!$this->indexExists('marks', 'idx_marks_student_subject')) {
                    $table->index(['student_id', 'subject_id'], 'idx_marks_student_subject');
                }
                if (!$this->indexExists('marks', 'idx_marks_teacher_subject')) {
                    $table->index(['teacher_id', 'subject_id'], 'idx_marks_teacher_subject');
                }
            });
        }

        // Course Forms and Quiz Core Optimization
        if (Schema::hasTable('course_forms')) {
            Schema::table('course_forms', function (Blueprint $table) {
                if (!$this->indexExists('course_forms', 'idx_course_forms_student_academic_term')) {
                    $table->index(['student_id', 'academic_year_id', 'term_id'], 'idx_course_forms_student_academic_term');
                }
                if (!$this->indexExists('course_forms', 'idx_course_forms_subject_academic')) {
                    $table->index(['subject_id', 'academic_year_id'], 'idx_course_forms_subject_academic');
                }
            });
        }

        if (Schema::hasTable('quiz_scores')) {
            Schema::table('quiz_scores', function (Blueprint $table) {
                if (!$this->indexExists('quiz_scores', 'idx_quiz_scores_student_exam')) {
                    $table->index(['student_id', 'exam_id'], 'idx_quiz_scores_student_exam');
                }
                if (!$this->indexExists('quiz_scores', 'idx_quiz_scores_course_approved')) {
                    $table->index(['course_form_id', 'approved'], 'idx_quiz_scores_course_approved');
                }
            });
        }

        // Question Banks and Online Exams Core
        if (Schema::hasTable('question_banks')) {
            Schema::table('question_banks', function (Blueprint $table) {
                if (!$this->indexExists('question_banks', 'idx_question_banks_exam_type')) {
                    $table->index(['exam_id', 'question_type'], 'idx_question_banks_exam_type');
                }
                if (!$this->indexExists('question_banks', 'idx_question_banks_type')) {
                    $table->index('question_type', 'idx_question_banks_type');
                }
            });
        }

        if (Schema::hasTable('take_exams')) {
            Schema::table('take_exams', function (Blueprint $table) {
                if (!$this->indexExists('take_exams', 'idx_take_exams_student_question')) {
                    $table->index(['student_id', 'question_id'], 'idx_take_exams_student_question');
                }
                if (!$this->indexExists('take_exams', 'idx_take_exams_status_approval')) {
                    $table->index(['status', 'approval'], 'idx_take_exams_status_approval');
                }
            });
        }

        // Financial Management Core (Important!)
        if (Schema::hasTable('invoice_students')) {
            Schema::table('invoice_students', function (Blueprint $table) {
                if (!$this->indexExists('invoice_students', 'idx_invoice_student_academic_term')) {
                    $table->index(['student_id', 'academic_id', 'term_id'], 'idx_invoice_student_academic_term');
                }
                if (!$this->indexExists('invoice_students', 'idx_invoice_status_academic')) {
                    $table->index(['status', 'academic_id'], 'idx_invoice_status_academic');
                }
                if (!$this->indexExists('invoice_students', 'idx_invoice_order_code')) {
                    $table->index('order_code', 'idx_invoice_order_code');
                }
            });
        }

        if (Schema::hasTable('school_payments')) {
            Schema::table('school_payments', function (Blueprint $table) {
                if (!$this->indexExists('school_payments', 'idx_payments_payable')) {
                    $table->index(['payable_type', 'payable_id'], 'idx_payments_payable');
                }
                if (!$this->indexExists('school_payments', 'idx_payments_status_created')) {
                    $table->index(['status', 'created_at'], 'idx_payments_status_created');
                }
                if (!$this->indexExists('school_payments', 'idx_payments_transaction_ref')) {
                    $table->index('transaction_reference', 'idx_payments_transaction_ref');
                }
            });
        }

        // Communication and Messages Core
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (!$this->indexExists('messages', 'idx_messages_conversation_created')) {
                    $table->index(['conversation_id', 'created_at'], 'idx_messages_conversation_created');
                }
                if (!$this->indexExists('messages', 'idx_messages_sender_created')) {
                    $table->index(['sender_id', 'created_at'], 'idx_messages_sender_created');
                }
                if (!$this->indexExists('messages', 'idx_messages_read_status')) {
                    $table->index('is_read', 'idx_messages_read_status');
                }
            });
        }

        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                if (!$this->indexExists('conversations', 'idx_conversations_last_message')) {
                    $table->index('last_message_at', 'idx_conversations_last_message');
                }
            });
        }

        // Notifications Core
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (!$this->indexExists('notifications', 'idx_notifications_notifiable')) {
                    $table->index(['notifiable_type', 'notifiable_id'], 'idx_notifications_notifiable');
                }
                if (!$this->indexExists('notifications', 'idx_notifications_read_created')) {
                    $table->index(['read_at', 'created_at'], 'idx_notifications_read_created');
                }
            });
        }

        // Library Management Core
        if (Schema::hasTable('library_books')) {
            Schema::table('library_books', function (Blueprint $table) {
                if (!$this->indexExists('library_books', 'idx_library_books_category_author')) {
                    $table->index(['library_category_id', 'author'], 'idx_library_books_category_author');
                }
                if (Schema::hasColumn('library_books', 'shelf_id')) {
                    if (!$this->indexExists('library_books', 'idx_library_books_location')) {
                        $table->index(['shelf_id', 'row_number', 'position_number'], 'idx_library_books_location');
                    }
                }
            });
        }

        if (Schema::hasTable('library_book_loans')) {
            Schema::table('library_book_loans', function (Blueprint $table) {
                if (!$this->indexExists('library_book_loans', 'idx_book_loans_book_status')) {
                    $table->index(['library_book_id', 'status'], 'idx_book_loans_book_status');
                }
                if (!$this->indexExists('library_book_loans', 'idx_book_loans_borrower')) {
                    $table->index(['borrower_type', 'borrower_id'], 'idx_book_loans_borrower');
                }
                if (!$this->indexExists('library_book_loans', 'idx_book_loans_due_status')) {
                    $table->index(['due_date', 'status'], 'idx_book_loans_due_status');
                }
            });
        }

        // Routines and Schedules Core (Important for timetables!)
        if (Schema::hasTable('routines')) {
            Schema::table('routines', function (Blueprint $table) {
                if (!$this->indexExists('routines', 'idx_routines_class_section_day')) {
                    $table->index(['class_id', 'section_id', 'day'], 'idx_routines_class_section_day');
                }
                if (!$this->indexExists('routines', 'idx_routines_teacher_day')) {
                    $table->index(['teacher_id', 'day'], 'idx_routines_teacher_day');
                }
                if (!$this->indexExists('routines', 'idx_routines_academic_day')) {
                    $table->index(['academic_id', 'day'], 'idx_routines_academic_day');
                }
            });
        }

        // Events and Announcements Core
        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                if (!$this->indexExists('announcements', 'idx_announcements_from_type')) {
                    $table->index(['from_id', 'type_of_user_sent_to'], 'idx_announcements_from_type');
                }
                if (!$this->indexExists('announcements', 'idx_announcements_created')) {
                    $table->index('created_at', 'idx_announcements_created');
                }
            });
        }

        if (Schema::hasTable('my_events')) {
            Schema::table('my_events', function (Blueprint $table) {
                if (!$this->indexExists('my_events', 'idx_events_datetime')) {
                    $table->index('date_time', 'idx_events_datetime');
                }
            });
        }

        // Live Classes and Lectures Core
        if (Schema::hasTable('live_classes')) {
            Schema::table('live_classes', function (Blueprint $table) {
                if (!$this->indexExists('live_classes', 'idx_live_classes_subject_teacher')) {
                    $table->index(['subject_id', 'teacher_id'], 'idx_live_classes_subject_teacher');
                }
                if (!$this->indexExists('live_classes', 'idx_live_classes_date')) {
                    $table->index('date_of_meeting', 'idx_live_classes_date');
                }
            });
        }

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

        // Media and Files Core
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
                if (!$this->indexExists('media', 'idx_media_model')) {
                    $table->index(['model_type', 'model_id'], 'idx_media_model');
                }
                if (!$this->indexExists('media', 'idx_media_collection_type')) {
                    $table->index(['collection_name', 'model_type'], 'idx_media_collection_type');
                }
            });
        }

        // Settings and Configuration Core
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!$this->indexExists('settings', 'idx_settings_group_locked')) {
                    $table->index(['group', 'locked'], 'idx_settings_group_locked');
                }
            });
        }

        // Import/Export Tracking Core
        if (Schema::hasTable('imports')) {
            Schema::table('imports', function (Blueprint $table) {
                if (!$this->indexExists('imports', 'idx_imports_user_completed')) {
                    $table->index(['user_id', 'completed_at'], 'idx_imports_user_completed');
                }
                if (!$this->indexExists('imports', 'idx_imports_importer')) {
                    $table->index('importer', 'idx_imports_importer');
                }
            });
        }

        if (Schema::hasTable('exports')) {
            Schema::table('exports', function (Blueprint $table) {
                if (!$this->indexExists('exports', 'idx_exports_user_completed')) {
                    $table->index(['user_id', 'completed_at'], 'idx_exports_user_completed');
                }
                if (!$this->indexExists('exports', 'idx_exports_exporter')) {
                    $table->index('exporter', 'idx_exports_exporter');
                }
            });
        }

        // Audit Logs Core
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (!$this->indexExists('audit_logs', 'idx_audit_logs_model')) {
                    $table->index(['model_type', 'model_id'], 'idx_audit_logs_model');
                }
                if (!$this->indexExists('audit_logs', 'idx_audit_logs_user_created')) {
                    $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_created');
                }
                if (!$this->indexExists('audit_logs', 'idx_audit_logs_action')) {
                    $table->index('action', 'idx_audit_logs_action');
                }
            });
        }

        // Complaints and Communication Core
        if (Schema::hasTable('complaints')) {
            Schema::table('complaints', function (Blueprint $table) {
                if (!$this->indexExists('complaints', 'idx_complaints_user_status')) {
                    $table->index(['user_id', 'status'], 'idx_complaints_user_status');
                }
                if (!$this->indexExists('complaints', 'idx_complaints_status_created')) {
                    $table->index(['status', 'created_at'], 'idx_complaints_status_created');
                }
            });
        }

        // Psychomotor Assessment Core
        if (Schema::hasTable('psychomotors')) {
            Schema::table('psychomotors', function (Blueprint $table) {
                if (!$this->indexExists('psychomotors', 'idx_psychomotors_group_term_academic')) {
                    $table->index(['group_id', 'term_id', 'academic_id'], 'idx_psychomotors_group_term_academic');
                }
                if (!$this->indexExists('psychomotors', 'idx_psychomotors_type_category')) {
                    $table->index(['type', 'psychomotor_category_id'], 'idx_psychomotors_type_category');
                }
            });
        }

        if (Schema::hasTable('pyschomotor_students')) {
            Schema::table('pyschomotor_students', function (Blueprint $table) {
                if (!$this->indexExists('pyschomotor_students', 'idx_psychomotor_student_assessment')) {
                    $table->index(['student_id', 'psychomotor_id'], 'idx_psychomotor_student_assessment');
                }
            });
        }

        // Result Section Core
        if (Schema::hasTable('result_section_student_types')) {
            Schema::table('result_section_student_types', function (Blueprint $table) {
                if (!$this->indexExists('result_section_student_types', 'idx_result_section_course_type')) {
                    $table->index(['course_form_id', 'result_section_type_id'], 'idx_result_section_course_type');
                }
            });
        }

        // Hostel Management Core (if not already covered)
        if (Schema::hasTable('hostel_assignments')) {
            Schema::table('hostel_assignments', function (Blueprint $table) {
                if (!$this->indexExists('hostel_assignments', 'idx_hostel_assign_student_academic')) {
                    $table->index(['student_id', 'academic_id', 'term_id'], 'idx_hostel_assign_student_academic');
                }
                if (!$this->indexExists('hostel_assignments', 'idx_hostel_assign_room_academic')) {
                    $table->index(['hostel_room_id', 'academic_id'], 'idx_hostel_assign_room_academic');
                }
            });
        }

        if (Schema::hasTable('hostel_attendances')) {
            Schema::table('hostel_attendances', function (Blueprint $table) {
                if (!$this->indexExists('hostel_attendances', 'idx_hostel_attendance_student_date')) {
                    $table->index(['student_id', 'date'], 'idx_hostel_attendance_student_date');
                }
                if (!$this->indexExists('hostel_attendances', 'idx_hostel_attendance_room_date')) {
                    $table->index(['hostel_room_id', 'date'], 'idx_hostel_attendance_room_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Core indexes rollback - only drop the indexes we added in this migration
        $coreIndexesToDrop = [
            'users' => ['idx_users_user_type', 'idx_users_email_verified', 'idx_users_type_created'],
            'students' => ['idx_students_class_section_group', 'idx_students_class_arm', 'idx_students_blood_group', 'idx_students_reg_number', 'idx_students_guardian_class'],
            'teachers' => ['idx_teachers_designation', 'idx_teachers_gender_joining'],
            'academic_years' => ['idx_academic_status_start'],
            'terms' => ['idx_terms_status'],
            'school_classes' => ['idx_classes_teacher_group', 'idx_classes_numeric'],
            'school_sections' => ['idx_sections_class_teacher', 'idx_sections_category'],
            'subjects' => ['idx_subjects_class_teacher', 'idx_subjects_type_class', 'idx_subjects_depot', 'idx_subjects_code'],
            'assignments' => ['idx_assignments_class_subject_deadline', 'idx_assignments_teacher_status', 'idx_assignments_term_academic'],
            'exams' => ['idx_exams_academic_term_subject', 'idx_exams_date_isset', 'idx_exams_assessment_type'],
            'student_attendances' => ['idx_student_attendance_date', 'idx_student_attendance_teacher_date', 'idx_student_attendance_status_date'],
            'teacher_attendances' => ['idx_teacher_attendance_date', 'idx_teacher_attendance_status_date'],
            'results' => ['idx_results_student_subject', 'idx_results_teacher_student'],
            'marks' => ['idx_marks_student_subject', 'idx_marks_teacher_subject'],
            'course_forms' => ['idx_course_forms_student_academic_term', 'idx_course_forms_subject_academic'],
            'quiz_scores' => ['idx_quiz_scores_student_exam', 'idx_quiz_scores_course_approved'],
            'question_banks' => ['idx_question_banks_exam_type', 'idx_question_banks_type'],
            'take_exams' => ['idx_take_exams_student_question', 'idx_take_exams_status_approval'],
            'invoice_students' => ['idx_invoice_student_academic_term', 'idx_invoice_status_academic', 'idx_invoice_order_code'],
            'school_payments' => ['idx_payments_payable', 'idx_payments_status_created', 'idx_payments_transaction_ref'],
            'messages' => ['idx_messages_conversation_created', 'idx_messages_sender_created', 'idx_messages_read_status'],
            'conversations' => ['idx_conversations_last_message'],
            'notifications' => ['idx_notifications_notifiable', 'idx_notifications_read_created'],
            'library_books' => ['idx_library_books_category_author', 'idx_library_books_location'],
            'library_book_loans' => ['idx_book_loans_book_status', 'idx_book_loans_borrower', 'idx_book_loans_due_status'],
            'routines' => ['idx_routines_class_section_day', 'idx_routines_teacher_day', 'idx_routines_academic_day'],
            'announcements' => ['idx_announcements_from_type', 'idx_announcements_created'],
            'my_events' => ['idx_events_datetime'],
            'live_classes' => ['idx_live_classes_subject_teacher', 'idx_live_classes_date'],
            'lectures' => ['idx_lectures_subject_teacher', 'idx_lectures_date'],
            'media' => ['idx_media_model', 'idx_media_collection_type'],
            'settings' => ['idx_settings_group_locked'],
            'imports' => ['idx_imports_user_completed', 'idx_imports_importer'],
            'exports' => ['idx_exports_user_completed', 'idx_exports_exporter'],
            'audit_logs' => ['idx_audit_logs_model', 'idx_audit_logs_user_created', 'idx_audit_logs_action'],
            'complaints' => ['idx_complaints_user_status', 'idx_complaints_status_created'],
            'psychomotors' => ['idx_psychomotors_group_term_academic', 'idx_psychomotors_type_category'],
            'pyschomotor_students' => ['idx_psychomotor_student_assessment'],
            'result_section_student_types' => ['idx_result_section_course_type'],
            'hostel_assignments' => ['idx_hostel_assign_student_academic', 'idx_hostel_assign_room_academic'],
            'hostel_attendances' => ['idx_hostel_attendance_student_date', 'idx_hostel_attendance_room_date'],
        ];

        // Drop all regular indexes
        foreach ($coreIndexesToDrop as $tableName => $indexes) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexes, $tableName) {
                    foreach ($indexes as $indexName) {
                        try {
                            if ($this->indexExists($tableName, $indexName)) {
                                $table->dropIndex($indexName);
                            }
                        } catch (\Exception $e) {
                            // Silently continue if index doesn't exist or can't be dropped
                        }
                    }
                });
            }
        }
    }
};
