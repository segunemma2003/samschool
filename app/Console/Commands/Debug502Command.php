<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Debug502Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:502 {--user-id= : Specific user ID to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug 502 gateway errors in QuestionBank resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting 502 Error Debug...');

        // If user ID is provided, simulate that user
        if ($userId = $this->option('user-id')) {
            $this->info("Testing with user ID: {$userId}");
            $this->debugWithSpecificUser($userId);
        } else {
            $this->info("Testing system without authentication...");
            $this->debugQuestionBankResource();
        }

        $this->info('Debug completed. Check logs for details.');
        return 0;
    }

    public function debugWithSpecificUser($userId)
    {
        try {
            $this->info("=== Testing with User ID: {$userId} ===");

            // 1. Check user exists
            $user = User::find($userId);
            $this->info("User found: " . ($user ? "YES" : "NO"));
            if (!$user) {
                $this->error("User with ID {$userId} not found!");
                return;
            }

            $this->info("User email: " . ($user->email ?? 'NO EMAIL'));
            $this->info("User type: " . ($user->user_type ?? 'NO TYPE'));

            // 2. Check teacher lookup
            $teacher = Teacher::where('email', $user->email)->first();
            $this->info("Teacher found: " . ($teacher ? "YES (ID: {$teacher->id})" : "NO"));

            if ($teacher) {
                $this->info("Teacher name: {$teacher->name}");
                $this->info("Teacher designation: " . ($teacher->designation ?? 'None'));
            }

            // 3. Test the OptimizedTeacherLookup logic
            $this->testTeacherLookupLogic($userId, $user, $teacher);

            // 4. Test QuestionBank specific queries
            $this->testQuestionBankQueries($teacher);

        } catch (\Exception $e) {
            $this->error("Error in debugWithSpecificUser: " . $e->getMessage());
            Log::error("Debug with user error: " . $e->getMessage());
        }
    }

    public function debugQuestionBankResource()
    {
        try {
            $this->info("=== System Information ===");
            $this->checkSystemResources();

            $this->info("\n=== Database Connectivity ===");
            // Check database connection
            $dbCheck = DB::select('SELECT 1 as test');
            $this->info("Database connection: " . ($dbCheck ? "OK" : "FAILED"));

            $this->info("\n=== Academic Data ===");
            // Check academic year and term
            $academy = AcademicYear::where('status', 'true')->first();
            $term = Term::where('status', 'true')->first();
            $this->info("Active academy: " . ($academy ? "YES (ID: {$academy->id}, Title: {$academy->title})" : "NO"));
            $this->info("Active term: " . ($term ? "YES (ID: {$term->id}, Name: {$term->name})" : "NO"));

            if (!$academy || !$term) {
                $this->warn("Missing active academic year or term - this will cause issues!");
            }

            $this->info("\n=== Teacher/User Data ===");
            // Check some users and teachers
            $totalUsers = User::count();
            $totalTeachers = Teacher::count();
            $this->info("Total users: {$totalUsers}");
            $this->info("Total teachers: {$totalTeachers}");

            // Find teachers with matching emails
            $teachersWithEmails = Teacher::whereNotNull('email')
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.email', 'teachers.email');
                })
                ->count();
            $this->info("Teachers with matching user emails: {$teachersWithEmails}");

            // Test with a random teacher-user pair
            $sampleTeacher = Teacher::whereNotNull('email')
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.email', 'teachers.email');
                })
                ->first();

            if ($sampleTeacher) {
                $sampleUser = User::where('email', $sampleTeacher->email)->first();
                $this->info("Testing with sample teacher: {$sampleTeacher->name} (ID: {$sampleTeacher->id})");
                $this->info("Corresponding user: {$sampleUser->name} (ID: {$sampleUser->id})");

                $this->debugWithSpecificUser($sampleUser->id);
            }

        } catch (\Exception $e) {
            $this->error("Debug error: " . $e->getMessage());
            Log::error("Debug error: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }

    private function testTeacherLookupLogic($userId, $user, $teacher)
    {
        $this->info("\n=== Testing Teacher Lookup Logic ===");

        try {
            // Test caching
            $cacheKey = "teacher_for_user_{$userId}";
            Cache::forget($cacheKey); // Clear first

            $this->info("Testing cache mechanism...");
            $cachedResult = Cache::remember($cacheKey, 60, function() use ($userId) {
                $user = User::select('id', 'email')->whereId($userId)->first();
                if (!$user || !$user->email) {
                    return null;
                }
                return Teacher::select('id', 'name', 'email', 'designation')
                    ->where('email', $user->email)
                    ->first();
            });

            $this->info("Cache result: " . ($cachedResult ? "SUCCESS" : "NULL"));

            // Test the actual queries that might be causing issues
            $this->info("Testing subject queries...");
            if ($teacher) {
                $subjectsCount = DB::table('subjects')->where('teacher_id', $teacher->id)->count();
                $this->info("Teacher's subjects: {$subjectsCount}");

                $classesCount = DB::table('school_classes')->where('teacher_id', $teacher->id)->count();
                $this->info("Teacher's classes: {$classesCount}");
            }

        } catch (\Exception $e) {
            $this->error("Teacher lookup test failed: " . $e->getMessage());
        }
    }

    private function testQuestionBankQueries($teacher)
    {
        $this->info("\n=== Testing QuestionBank Queries ===");

        try {
            if (!$teacher) {
                $this->warn("No teacher to test with");
                return;
            }

            // Test the main query that might be causing 502
            $this->info("Testing exam queries...");

            $academy = AcademicYear::where('status', 'true')->first();
            $term = Term::where('status', 'true')->first();

            if ($academy && $term) {
                $examsCount = Exam::where('term_id', $term->id)
                    ->where('academic_year_id', $academy->id)
                    ->whereHas('subject', function($q) use ($teacher) {
                        $q->where('teacher_id', $teacher->id);
                    })
                    ->count();
                $this->info("Teacher's exams count: {$examsCount}");

                // Test the complex query from QuestionBankResource
                $questionBankCount = DB::table('question_banks')
                    ->join('exams', 'question_banks.exam_id', '=', 'exams.id')
                    ->join('subjects', 'exams.subject_id', '=', 'subjects.id')
                    ->where('subjects.teacher_id', $teacher->id)
                    ->count();
                $this->info("Teacher's question banks: {$questionBankCount}");
            }

        } catch (\Exception $e) {
            $this->error("QuestionBank query test failed: " . $e->getMessage());
            Log::error("QuestionBank query error: " . $e->getMessage());
        }
    }

    public function checkSystemResources()
    {
        try {
            $info = [
                'PHP Version' => PHP_VERSION,
                'Memory Limit' => ini_get('memory_limit'),
                'Memory Usage' => $this->formatBytes(memory_get_usage(true)),
                'Peak Memory' => $this->formatBytes(memory_get_peak_usage(true)),
                'Max Execution Time' => ini_get('max_execution_time'),
                'Laravel Version' => app()->version(),
                'Cache Driver' => config('cache.default'),
                'Database Driver' => config('database.default'),
            ];

            foreach ($info as $key => $value) {
                $this->info("{$key}: {$value}");
                Log::info("{$key}: {$value}");
            }

        } catch (\Exception $e) {
            $this->error("System check error: " . $e->getMessage());
            Log::error("System check error: " . $e->getMessage());
        }
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }
}
