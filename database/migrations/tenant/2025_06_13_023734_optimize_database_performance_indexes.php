<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.

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
     * Additional performance optimizations including full-text search,
     * partial indexes, and specialized query optimizations.
     */
    public function up(): void
    {
        // Full-text search indexes for searchable content
        if (DB::getDriverName() === 'mysql') {
            // Add full-text search indexes for MySQL (only if tables exist)
            if (Schema::hasTable('students')) {
                try {
                    DB::statement('ALTER TABLE students ADD FULLTEXT(name, email, address) WITH PARSER ngram');
                } catch (\Exception $e) {
                    // Index may already exist or column may not exist
                }
            }
            if (Schema::hasTable('teachers')) {
                try {
                    DB::statement('ALTER TABLE teachers ADD FULLTEXT(name, email, address) WITH PARSER ngram');
                } catch (\Exception $e) {
                    // Index may already exist or column may not exist
                }
            }
            if (Schema::hasTable('guardians')) {
                try {
                    DB::statement('ALTER TABLE guardians ADD FULLTEXT(name, email, address) WITH PARSER ngram');
                } catch (\Exception $e) {
                    // Index may already exist or column may not exist
                }
            }
            if (Schema::hasTable('library_books')) {
                try {
                    DB::statement('ALTER TABLE library_books ADD FULLTEXT(title, author, description) WITH PARSER ngram');
                } catch (\Exception $e) {
                    // Index may already exist or column may not exist
                }
            }
            if (Schema::hasTable('online_library_materials')) {
                try {
                    DB::statement('ALTER TABLE online_library_materials ADD FULLTEXT(title, description, author) WITH PARSER ngram');
                } catch (\Exception $e) {
                    // Index may already exist or column may not exist
                }
            }
            if (Schema::hasTable('announcements')) {
                try {
                    DB::statement('ALTER TABLE announcements ADD FULLTEXT(title, text) WITH PARSER ngram');
                } catch (\Exception $e) {
                    // Index may already exist or column may not exist
                }
            }
            if (Schema::hasTable('blogs')) {
                try {
                    DB::statement('ALTER TABLE blogs ADD FULLTEXT(title, content, description) WITH PARSER ngram');
                } catch (\Exception $e) {
                    // Index may already exist or column may not exist
                }
            }
        }

        // Partial indexes for commonly filtered active records
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Index only active users for faster user listings
                if (!$this->indexExists('users', 'idx_active_users_type_created')) {
                    $table->index(['user_type', 'created_at'], 'idx_active_users_type_created');
                }
            });
        }

        // Optimizations for date-range queries (very common in school systems)
        if (Schema::hasTable('student_attendances')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                if (!$this->indexExists('student_attendances', 'idx_attendance_date_status_student')) {
                    $table->index(['date_of_attendance', 'status', 'student_id'], 'idx_attendance_date_status_student');
                }
            });
        }

        if (Schema::hasTable('exam_schedules')) {
            Schema::table('exam_schedules', function (Blueprint $table) {
                if (!$this->indexExists('exam_schedules', 'idx_exam_schedules_date_status')) {
                    $table->index(['date_of_exam', 'status'], 'idx_exam_schedules_date_status');
                }
            });
        }

        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                if (!$this->indexExists('assignments', 'idx_assignments_deadline_status_class')) {
                    $table->index(['deadline', 'status', 'class_id'], 'idx_assignments_deadline_status_class');
                }
            });
        }

        // Optimize polymorphic relationships
        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                if (!$this->indexExists('comments', 'idx_comments_polymorphic_created')) {
                    $table->index(['commentable_type', 'commentable_id', 'created_at'], 'idx_comments_polymorphic_created');
                }
            });
        }

        if (Schema::hasTable('library_book_loans')) {
            Schema::table('library_book_loans', function (Blueprint $table) {
                if (!$this->indexExists('library_book_loans', 'idx_book_loans_borrower_status')) {
                    $table->index(['borrower_type', 'borrower_id', 'status'], 'idx_book_loans_borrower_status');
                }
            });
        }

        // Communication optimizations
        if (Schema::hasTable('ch_messages')) {
            Schema::table('ch_messages', function (Blueprint $table) {
                if (!$this->indexExists('ch_messages', 'idx_ch_messages_from_to_seen')) {
                    $table->index(['from_id', 'to_id', 'seen'], 'idx_ch_messages_from_to_seen');
                }
                if (!$this->indexExists('ch_messages', 'idx_ch_messages_to_seen_created')) {
                    $table->index(['to_id', 'seen', 'created_at'], 'idx_ch_messages_to_seen_created');
                }
            });
        }

        // Financial reporting optimizations
        if (Schema::hasTable('school_invoices')) {
            Schema::table('school_invoices', function (Blueprint $table) {
                if (!$this->indexExists('school_invoices', 'idx_invoices_status_due_academic')) {
                    $table->index(['status', 'due_date', 'academic_year_id'], 'idx_invoices_status_due_academic');
                }
                if (!$this->indexExists('school_invoices', 'idx_invoices_student_term_status')) {
                    $table->index(['student_id', 'term_id', 'status'], 'idx_invoices_student_term_status');
                }
            });
        }

        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (!$this->indexExists('payrolls', 'idx_payrolls_year_month_status')) {
                    $table->index(['year', 'month', 'status'], 'idx_payrolls_year_month_status');
                }
            });
        }

        // Hostel management optimizations
        if (Schema::hasTable('hostel_assignments')) {
            Schema::table('hostel_assignments', function (Blueprint $table) {
                if (!$this->indexExists('hostel_assignments', 'idx_hostel_room_dates')) {
                    $table->index(['hostel_room_id', 'assignment_date', 'release_date'], 'idx_hostel_room_dates');
                }
            });
        }

        if (Schema::hasTable('hostel_applications')) {
            Schema::table('hostel_applications', function (Blueprint $table) {
                if (!$this->indexExists('hostel_applications', 'idx_hostel_apps_status_term_academic')) {
                    $table->index(['status', 'term_id', 'academic_id'], 'idx_hostel_apps_status_term_academic');
                }
            });
        }

        // Library advanced search optimizations
        if (Schema::hasTable('online_library_materials')) {
            Schema::table('online_library_materials', function (Blueprint $table) {
                if (!$this->indexExists('online_library_materials', 'idx_materials_year_type_views')) {
                    $table->index(['publication_year', 'type_id', 'view_count'], 'idx_materials_year_type_views');
                }
            });
        }

        if (Schema::hasTable('online_library_reading_progress')) {
            Schema::table('online_library_reading_progress', function (Blueprint $table) {
                if (!$this->indexExists('online_library_reading_progress', 'idx_reading_progress_user_finished')) {
                    $table->index(['user_id', 'is_finished', 'updated_at'], 'idx_reading_progress_user_finished');
                }
            });
        }

        // Event and calendar optimizations
        if (Schema::hasTable('routines')) {
            Schema::table('routines', function (Blueprint $table) {
                if (!$this->indexExists('routines', 'idx_routines_day_time_class_section')) {
                    $table->index(['day', 'start_time', 'class_id', 'section_id'], 'idx_routines_day_time_class_section');
                }
            });
        }

        if (Schema::hasTable('live_meetings')) {
            Schema::table('live_meetings', function (Blueprint $table) {
                if (!$this->indexExists('live_meetings', 'idx_live_meetings_datetime')) {
                    $table->index(['date_of_meeting', 'time_of_meeting'], 'idx_live_meetings_datetime');
                }
            });
        }

        // Advanced academic performance tracking
        if (Schema::hasTable('quiz_submissions')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                if (!$this->indexExists('quiz_submissions', 'idx_quiz_submissions_exam_student_correct')) {
                    $table->index(['exam_id', 'student_id', 'correct'], 'idx_quiz_submissions_exam_student_correct');
                }
            });
        }

        if (Schema::hasTable('question_analytics')) {
            Schema::table('question_analytics', function (Blueprint $table) {
                if (!$this->indexExists('question_analytics', 'idx_question_analytics_exam_question_correct')) {
                    $table->index(['exam_id', 'question_bank_id', 'is_correct'], 'idx_question_analytics_exam_question_correct');
                }
            });
        }

        // Security and monitoring
        if (Schema::hasTable('security_logs')) {
            Schema::table('security_logs', function (Blueprint $table) {
                if (!$this->indexExists('security_logs', 'idx_security_logs_exam_student_incident')) {
                    $table->index(['exam_id', 'student_id', 'incident_type'], 'idx_security_logs_exam_student_incident');
                }
            });
        }

        if (Schema::hasTable('exam_recordings')) {
            Schema::table('exam_recordings', function (Blueprint $table) {
                if (!$this->indexExists('exam_recordings', 'idx_exam_recordings_exam_recorded')) {
                    $table->index(['exam_id', 'recorded_at'], 'idx_exam_recordings_exam_recorded');
                }
            });
        }

        // Mail system optimization
        if (Schema::hasTable('mails')) {
            Schema::table('mails', function (Blueprint $table) {
                if (!$this->indexExists('mails', 'idx_mails_sent_delivered')) {
                    $table->index(['sent_at', 'delivered_at'], 'idx_mails_sent_delivered');
                }
                if (!$this->indexExists('mails', 'idx_mails_class_sent')) {
                    $table->index(['mail_class', 'sent_at'], 'idx_mails_class_sent');
                }
            });
        }

        if (Schema::hasTable('mail_events')) {
            Schema::table('mail_events', function (Blueprint $table) {
                if (!$this->indexExists('mail_events', 'idx_mail_events_mail_type_occurred')) {
                    $table->index(['mail_id', 'type', 'occurred_at'], 'idx_mail_events_mail_type_occurred');
                }
            });
        }

        // Result computation optimizations
        if (Schema::hasTable('result_section_student_types')) {
            Schema::table('result_section_student_types', function (Blueprint $table) {
                if (!$this->indexExists('result_section_student_types', 'idx_result_section_type_score')) {
                    $table->index(['result_section_type_id', 'score'], 'idx_result_section_type_score');
                }
            });
        }

        // Document and template system
        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table) {
                if (!$this->indexExists('documents', 'idx_documents_model_sent')) {
                    $table->index(['model_type', 'model_id', 'is_send'], 'idx_documents_model_sent');
                }
            });
        }

        if (Schema::hasTable('document_templates')) {
            Schema::table('document_templates', function (Blueprint $table) {
                if (!$this->indexExists('document_templates', 'idx_doc_templates_active_created')) {
                    $table->index(['is_active', 'created_at'], 'idx_doc_templates_active_created');
                }
            });
        }

        // Transport and logistics
        if (Schema::hasTable('transports')) {
            Schema::table('transports', function (Blueprint $table) {
                if (!$this->indexExists('transports', 'idx_transports_status_route')) {
                    $table->index(['status', 'route'], 'idx_transports_status_route');
                }
            });
        }

        // Advanced search for complaints and issues
        if (Schema::hasTable('complaint_replies')) {
            Schema::table('complaint_replies', function (Blueprint $table) {
                if (!$this->indexExists('complaint_replies', 'idx_complaint_replies_complaint_admin_created')) {
                    $table->index(['complaint_id', 'is_admin', 'created_at'], 'idx_complaint_replies_complaint_admin_created');
                }
            });
        }

        // Performance indexes for dashboard queries
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                // For dashboard student count by class
                if (!$this->indexExists('students', 'idx_students_class_created')) {
                    $table->index(['class_id', 'created_at'], 'idx_students_class_created');
                }
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                // For teacher workload analysis
                if (!$this->indexExists('teachers', 'idx_teachers_designation_joining')) {
                    $table->index(['designation', 'joining_date'], 'idx_teachers_designation_joining');
                }
            });
        }

        // Fee collection performance
        if (Schema::hasTable('class_fees')) {
            Schema::table('class_fees', function (Blueprint $table) {
                if (!$this->indexExists('class_fees', 'idx_class_fees_academic_term_active')) {
                    $table->index(['academic_year_id', 'term_id', 'is_active'], 'idx_class_fees_academic_term_active');
                }
            });
        }

        if (Schema::hasTable('student_fees')) {
            Schema::table('student_fees', function (Blueprint $table) {
                if (!$this->indexExists('student_fees', 'idx_student_fees_status_due')) {
                    $table->index(['status', 'due_date'], 'idx_student_fees_status_due');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop full-text indexes safely
        if (DB::getDriverName() === 'mysql') {
            $fullTextTables = [
                'students' => 'name',
                'teachers' => 'name',
                'guardians' => 'name',
                'library_books' => 'title',
                'online_library_materials' => 'title',
                'announcements' => 'title',
                'blogs' => 'title'
            ];

            foreach ($fullTextTables as $tableName => $indexName) {
                if (Schema::hasTable($tableName)) {
                    try {
                        DB::statement("ALTER TABLE {$tableName} DROP INDEX {$indexName}");
                    } catch (\Exception $e) {
                        // Silently continue if index doesn't exist
                    }
                }
            }
        }

        // Drop all other indexes safely using the same pattern
        $indexesToDrop = [
            'users' => ['idx_active_users_type_created'],
            'student_attendances' => ['idx_attendance_date_status_student'],
            'exam_schedules' => ['idx_exam_schedules_date_status'],
            'assignments' => ['idx_assignments_deadline_status_class'],
            'comments' => ['idx_comments_polymorphic_created'],
            'library_book_loans' => ['idx_book_loans_borrower_status'],
            'ch_messages' => ['idx_ch_messages_from_to_seen', 'idx_ch_messages_to_seen_created'],
            'school_invoices' => ['idx_invoices_status_due_academic', 'idx_invoices_student_term_status'],
            'payrolls' => ['idx_payrolls_year_month_status'],
            'hostel_assignments' => ['idx_hostel_room_dates'],
            'hostel_applications' => ['idx_hostel_apps_status_term_academic'],
            'online_library_materials' => ['idx_materials_year_type_views'],
            'online_library_reading_progress' => ['idx_reading_progress_user_finished'],
            'routines' => ['idx_routines_day_time_class_section'],
            'live_meetings' => ['idx_live_meetings_datetime'],
            'quiz_submissions' => ['idx_quiz_submissions_exam_student_correct'],
            'question_analytics' => ['idx_question_analytics_exam_question_correct'],
            'security_logs' => ['idx_security_logs_exam_student_incident'],
            'exam_recordings' => ['idx_exam_recordings_exam_recorded'],
            'mails' => ['idx_mails_sent_delivered', 'idx_mails_class_sent'],
            'mail_events' => ['idx_mail_events_mail_type_occurred'],
            'result_section_student_types' => ['idx_result_section_type_score'],
            'documents' => ['idx_documents_model_sent'],
            'document_templates' => ['idx_doc_templates_active_created'],
            'transports' => ['idx_transports_status_route'],
            'complaint_replies' => ['idx_complaint_replies_complaint_admin_created'],
            'students' => ['idx_students_class_created'],
            'teachers' => ['idx_teachers_designation_joining'],
            'class_fees' => ['idx_class_fees_academic_term_active'],
            'student_fees' => ['idx_student_fees_status_due'],
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
                            // Silently continue if index doesn't exist or can't be dropped
                        }
                    }
                });
            }
        }
    }
};
