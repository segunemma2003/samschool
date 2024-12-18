<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

if (!function_exists('getGeneralSettings')) {
    function getGeneralSettings()
    {
        $tenantId = tenant('id'); // Retrieve the tenant's ID using tenancy

        if (is_null($tenantId)) {
            // If no tenant, query the central database's general_settings
            return DB::table('general_settings')->first();
        } else {

            // If tenant exists, dynamically set the tenant's database and query the general_settings
            $tenantDatabase = 'tomatophp_' . $tenantId . '_db';
            // dd($tenantDatabase);
            return DB::connection($tenantDatabase)->table('general_settings')->first();
        }
    }
}






if (!function_exists('getStudentScore')) {
    /**
     * Fetch the score for a specific course and result section for a tenant.
     *
     * @param int $courseId
     * @param int $resultSectionId
     * @param int $studentId
     * @return mixed
     */
    function getStudentScore(int $courseId, int $resultSectionId, int $studentId)
    {
        // Retrieve the tenant ID (assuming tenant ID is available in the context)
        $tenantId = tenant('id');

        // If no tenant is found, fallback to the central database
        if (is_null($tenantId)) {
            // Query the central database for student score (example)
            return DB::table('student_scores')
                ->where('course_id', $courseId)
                ->where('result_section_id', $resultSectionId)
                ->where('student_id', $studentId)
                ->value('score');
        }

        try {
            // Construct the tenant-specific database name
            $tenantDatabase = 'tomatophp_' . $tenantId . '_db';

            // Dynamically set the tenant's database connection
            return DB::connection($tenantDatabase)
                ->table('student_scores') // Assuming you have a table like student_scores
                ->where('course_id', $courseId)
                ->where('result_section_id', $resultSectionId)
                ->where('student_id', $studentId)
                ->value('score');
        } catch (\Exception $e) {
            // Log the error if there's an issue with the tenant's database
            Log::error("Error fetching score for student {$studentId} in course {$courseId} and result section {$resultSectionId}: " . $e->getMessage());

            // Fallback to central database or return null
            return DB::table('student_scores')
                ->where('course_id', $courseId)
                ->where('result_section_id', $resultSectionId)
                ->where('student_id', $studentId)
                ->value('score');
        }
    }
}
