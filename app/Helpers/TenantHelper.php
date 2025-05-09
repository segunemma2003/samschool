<?php

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


if(!function_exists('getAuthName')){
    function getAuthName(){
        $auth = Auth::user();
        return $auth->name?? "User";
    }

}



if(!function_exists('getTenantLogo')){
     function getTenantLogo()  // Replace School with your actual tenant model
    {
        $domain = request()->getHost();
        $subdomain = explode('.', $domain)[0];


        // 🔹 Query the `domains` table directly
        $domainEntry = DB::table('domains')->where('domain', $subdomain)->first();

        if ($domainEntry && $domainEntry->tenant_id) {
            $tenant = DB::table('tenants')->where('id', $domainEntry->tenant_id)->first();
            if ($tenant && $tenant->logo) {
                return Storage::disk('s3')->url($tenant->logo);
            }
        }
        return asset("images/2023-08-Compasse-Network-Limited.png");
    }

    //  function getTenantLogo(): string
    // {
    //     $tenant = $this->getTenantFromDomain(); // 🔹 Retrieve tenant (Replace this with your actual method)
    //     return $tenant;
    // }
}

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
