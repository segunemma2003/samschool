<?php

use Illuminate\Support\Facades\DB;

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
