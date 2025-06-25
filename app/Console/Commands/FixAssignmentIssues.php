<?php

// Create this file as: app/Console/Commands/FixAssignmentIssues.php
// Run with: php artisan assignment:fix

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Assignment;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use App\Models\AcademicYear;

class FixAssignmentIssues extends Command
{
    protected $signature = 'assignment:fix {--test : Test queries without fixing}';

    protected $description = 'Fix common assignment-related database issues';

    public function handle()
    {
        $test = $this->option('test');

        $this->info('🔧 Assignment Issue Fixer');
        $this->newLine();

        if ($test) {
            $this->warn('🧪 TEST MODE - No changes will be made');
            $this->newLine();
        }

        // Step 1: Check database structure
        $this->checkDatabaseStructure();

        // Step 2: Fix null values
        $this->fixNullValues($test);

        // Step 3: Test critical queries
        $this->testCriticalQueries();

        // Step 4: Clear caches
        $this->clearCaches($test);

        $this->newLine();
        $this->info('✅ Assignment fix completed!');

        return 0;
    }

    private function checkDatabaseStructure()
    {
        $this->info('📊 Checking database structure...');

        // Check if assignment_student table exists
        if (!DB::getSchemaBuilder()->hasTable('assignment_student')) {
            $this->error('❌ assignment_student table not found!');
            $this->warn('   Create it with: php artisan make:migration create_assignment_student_table');
            return;
        }

        // Check required columns
        $columns = DB::getSchemaBuilder()->getColumnListing('assignment_student');
        $requiredColumns = ['assignment_id', 'student_id', 'status', 'total_score', 'answer', 'comments_score'];

        foreach ($requiredColumns as $column) {
            if (in_array($column, $columns)) {
                $this->line("  ✅ {$column} column exists");
            } else {
                $this->error("  ❌ {$column} column missing");
            }
        }

        // Check assignments table columns
        if (DB::getSchemaBuilder()->hasTable('assignments')) {
            $assignmentColumns = DB::getSchemaBuilder()->getColumnListing('assignments');
            $requiredAssignmentColumns = ['teacher_id', 'status', 'weight_mark'];

            foreach ($requiredAssignmentColumns as $column) {
                if (in_array($column, $assignmentColumns)) {
                    $this->line("  ✅ assignments.{$column} exists");
                } else {
                    $this->warn("  ⚠️  assignments.{$column} missing (will use defaults)");
                }
            }
        }

        $this->newLine();
    }

    private function fixNullValues($test = false)
    {
        $this->info('🧹 Fixing null/empty values...');

        // Fix SchoolClass names
        $nullClasses = SchoolClass::whereNull('name')->orWhere('name', '')->count();
        if ($nullClasses > 0) {
            $this->warn("  Found {$nullClasses} classes with null/empty names");
            if (!$test) {
                SchoolClass::whereNull('name')->orWhere('name', '')->get()->each(function ($class) {
                    $class->update(['name' => "Class {$class->id}"]);
                });
                $this->info("  ✅ Fixed {$nullClasses} class names");
            }
        } else {
            $this->line("  ✅ All class names are valid");
        }

        // Fix Subject names
        $nullSubjects = Subject::where(function($q) {
            $q->whereNull('name')->orWhere('name', '');
        })->where(function($q) {
            $q->whereNull('code')->orWhere('code', '');
        })->count();

        if ($nullSubjects > 0) {
            $this->warn("  Found {$nullSubjects} subjects with null/empty names and codes");
            if (!$test) {
                Subject::where(function($q) {
                    $q->whereNull('name')->orWhere('name', '');
                })->where(function($q) {
                    $q->whereNull('code')->orWhere('code', '');
                })->get()->each(function ($subject) {
                    $subject->update([
                        'name' => "Subject {$subject->id}",
                        'code' => "SUBJ{$subject->id}"
                    ]);
                });
                $this->info("  ✅ Fixed {$nullSubjects} subject names");
            }
        } else {
            $this->line("  ✅ All subject names are valid");
        }

        // Fix Term names
        $nullTerms = Term::whereNull('name')->orWhere('name', '')->count();
        if ($nullTerms > 0) {
            $this->warn("  Found {$nullTerms} terms with null/empty names");
            if (!$test) {
                Term::whereNull('name')->orWhere('name', '')->get()->each(function ($term) {
                    $term->update(['name' => "Term {$term->id}"]);
                });
                $this->info("  ✅ Fixed {$nullTerms} term names");
            }
        } else {
            $this->line("  ✅ All term names are valid");
        }

        // Fix AcademicYear titles
        $nullAcademicYears = AcademicYear::whereNull('title')->orWhere('title', '')->count();
        if ($nullAcademicYears > 0) {
            $this->warn("  Found {$nullAcademicYears} academic years with null/empty titles");
            if (!$test) {
                AcademicYear::whereNull('title')->orWhere('title', '')->get()->each(function ($year) {
                    $currentYear = date('Y');
                    $nextYear = $currentYear + 1;
                    $year->update(['title' => "{$currentYear}/{$nextYear} Academic Year"]);
                });
                $this->info("  ✅ Fixed {$nullAcademicYears} academic year titles");
            }
        } else {
            $this->line("  ✅ All academic year titles are valid");
        }

        $this->newLine();
    }

    private function testCriticalQueries()
    {
        $this->info('🧪 Testing critical queries...');

        try {
            // Test 1: Basic assignment count
            $assignmentCount = Assignment::count();
            $this->line("  ✅ Total assignments: {$assignmentCount}");

            // Test 2: Teacher-specific queries
            $teacherCount = Teacher::count();
            $this->line("  ✅ Total teachers: {$teacherCount}");

            if ($teacherCount > 0) {
                $firstTeacher = Teacher::first();
                $teacherAssignments = Assignment::where('teacher_id', $firstTeacher->id)->count();
                $this->line("  ✅ Teacher {$firstTeacher->id} has {$teacherAssignments} assignments");
            }

            // Test 3: Pivot table queries
            if (DB::getSchemaBuilder()->hasTable('assignment_student')) {
                $submissionCount = DB::table('assignment_student')->count();
                $this->line("  ✅ Total submissions: {$submissionCount}");

                // Test the problematic query
                try {
                    $withSubmissions = Assignment::whereHas('students', function ($q) {
                        $q->where('assignment_student.status', 'submitted');
                    })->count();
                    $this->line("  ✅ Assignments with submissions: {$withSubmissions}");
                } catch (\Exception $e) {
                    $this->error("  ❌ Pivot query failed: " . $e->getMessage());
                }
            }

            // Test 4: Stats query
            if ($teacherCount > 0) {
                try {
                    $firstTeacher = Teacher::first();
                    $stats = Assignment::getStatsForTeacher($firstTeacher->id);
                    $this->line("  ✅ Stats query successful: " . json_encode($stats));
                } catch (\Exception $e) {
                    $this->error("  ❌ Stats query failed: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            $this->error("  ❌ Query test failed: " . $e->getMessage());
        }

        $this->newLine();
    }

    private function clearCaches($test = false)
    {
        $this->info('🧽 Clearing caches...');

        if (!$test) {
            Cache::flush();
            $this->line("  ✅ All caches cleared");

            // Clear specific assignment caches
            $teachers = Teacher::all();
            foreach ($teachers as $teacher) {
                Cache::forget("teacher_data_{$teacher->id}");
                Cache::forget("teacher_{$teacher->id}_classes_safe");
                Cache::forget("teacher_{$teacher->id}_subjects_safe");
                Cache::forget("assignment_stats_teacher_{$teacher->id}");
            }
            $this->line("  ✅ Teacher-specific caches cleared");
        } else {
            $this->line("  ⏭️  Would clear all caches");
        }

        $this->newLine();
    }
}
