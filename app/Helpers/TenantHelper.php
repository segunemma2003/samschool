<?php

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

if(!function_exists('getAuthName')){
    function getAuthName(){
        $auth = Auth::user();
        return $auth->name ?? "User";
    }
}

function getCurrentTenant()
{
    // Skip tenant resolution in testing/CI environment
    if (app()->environment(['testing']) || env('TENANCY_SKIP_DOMAIN_CHECK', false)) {
        return null;
    }

    try {
        $domain = request()->getHost();

        // Aggressive caching - 6 hours
        return Cache::remember("tenant_domain_{$domain}", 21600, function () use ($domain) {
            // Use select to limit columns and add index hint
            $tenant = DB::select(
                "SELECT * FROM schools USE INDEX (schools_domain_idx) WHERE domain = ? LIMIT 1",
                [$domain]
            );
            return $tenant ? (object)$tenant[0] : null;
        });
    } catch (\Exception $e) {
        Log::error("Error fetching tenant for domain: " . $e->getMessage());

        if (app()->environment(['local', 'testing'])) {
            return null;
        }
        throw $e;
    }
}

if(!function_exists('getTenantLogo')){
    function getTenantLogo()
    {
        try {
            $domain = request()->getHost();
            $subdomain = explode('.', $domain)[0];

            // Try Redis first for ultra-fast access
            $cacheKey = "tenant_logo_{$subdomain}";

            if (Redis::exists($cacheKey)) {
                return Redis::get($cacheKey);
            }

            // Database query with strict timeout and optimized query
            $logo = Cache::remember($cacheKey, 3600, function () use ($subdomain) {
                try {
                    // Single optimized query with joins
                    $result = DB::select("
                        SELECT t.logo
                        FROM domains d
                        USE INDEX (domains_domain_idx)
                        INNER JOIN tenants t ON t.id = d.tenant_id
                        WHERE d.domain = ?
                        AND t.logo IS NOT NULL
                        LIMIT 1
                    ", [$subdomain]);

                    if (!empty($result) && $result[0]->logo) {
                        return Storage::disk('s3')->url($result[0]->logo);
                    }
                } catch (\Exception $e) {
                    Log::error("Database error in getTenantLogo: " . $e->getMessage());
                }

                return asset("images/2023-08-Compasse-Network-Limited.png");
            });

            // Store in Redis for next request
            Redis::setex($cacheKey, 3600, $logo);

            return $logo;

        } catch (\Exception $e) {
            Log::error("Error in getTenantLogo: " . $e->getMessage());
            return asset("images/2023-08-Compasse-Network-Limited.png");
        }
    }
}

if (!function_exists('getGeneralSettings')) {
    function getGeneralSettings()
    {
        try {
            $tenantId = tenant('id');
            $cacheKey = "general_settings_{$tenantId}";

            // Try Redis first
            if (Redis::exists($cacheKey)) {
                return json_decode(Redis::get($cacheKey));
            }

            $settings = Cache::remember($cacheKey, 7200, function () use ($tenantId) {
                if (is_null($tenantId)) {
                    // Optimized query with specific columns
                    $result = DB::select("
                        SELECT site_name, email_from_address, logo, timezone
                        FROM general_settings
                        LIMIT 1
                    ");
                    return $result ? (object)$result[0] : null;
                } else {
                    $tenantDatabase = 'tomatophp_' . $tenantId . '_db';
                    $result = DB::connection($tenantDatabase)->select("
                        SELECT site_name, email_from_address, logo, timezone
                        FROM general_settings
                        LIMIT 1
                    ");
                    return $result ? (object)$result[0] : null;
                }
            });

            // Store in Redis
            if ($settings) {
                Redis::setex($cacheKey, 7200, json_encode($settings));
            }

            return $settings ?: (object) [
                'site_name' => env('APP_NAME', 'Application'),
                'email_from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            ];

        } catch (\Exception $e) {
            Log::error("Error in getGeneralSettings: " . $e->getMessage());

            return (object) [
                'site_name' => env('APP_NAME', 'Application'),
                'email_from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            ];
        }
    }
}

if (!function_exists('getStudentScore')) {
    function getStudentScore(int $courseId, int $resultSectionId, int $studentId)
    {
        try {
            $tenantId = tenant('id');
            $cacheKey = "student_score_{$tenantId}_{$courseId}_{$resultSectionId}_{$studentId}";

            // Try Redis first
            if (Redis::exists($cacheKey)) {
                return Redis::get($cacheKey);
            }

            $score = Cache::remember($cacheKey, 1800, function () use ($courseId, $resultSectionId, $studentId, $tenantId) {
                if (is_null($tenantId)) {
                    $result = DB::select("
                        SELECT score
                        FROM student_scores
                        USE INDEX (student_scores_lookup_idx)
                        WHERE course_id = ? AND result_section_id = ? AND student_id = ?
                        LIMIT 1
                    ", [$courseId, $resultSectionId, $studentId]);

                    return $result ? $result[0]->score : null;
                }

                try {
                    $tenantDatabase = 'tomatophp_' . $tenantId . '_db';

                    $result = DB::connection($tenantDatabase)->select("
                        SELECT score
                        FROM student_scores
                        USE INDEX (student_scores_lookup_idx)
                        WHERE course_id = ? AND result_section_id = ? AND student_id = ?
                        LIMIT 1
                    ", [$courseId, $resultSectionId, $studentId]);

                    return $result ? $result[0]->score : null;

                } catch (\Exception $e) {
                    Log::error("Error fetching tenant score: " . $e->getMessage());
                    return null;
                }
            });

            // Store in Redis
            if ($score !== null) {
                Redis::setex($cacheKey, 1800, $score);
            }

            return $score;

        } catch (\Exception $e) {
            Log::error("Error in getStudentScore: " . $e->getMessage());
            return null;
        }
    }
}

// Fast database health check
if (!function_exists('checkDatabaseConnection')) {
    function checkDatabaseConnection($connection = null): bool
    {
        try {
            $db = $connection ? DB::connection($connection) : DB::connection();

            // Quick ping query
            $result = $db->select('SELECT 1 as ping');
            return !empty($result);
        } catch (\Exception $e) {
            Log::error("Database connection check failed: " . $e->getMessage());
            return false;
        }
    }
}

// Batch query helper to minimize database round trips
if (!function_exists('batchQuery')) {
    function batchQuery(array $queries, $connection = null): array
    {
        try {
            $db = $connection ? DB::connection($connection) : DB::connection();
            $results = [];

            foreach ($queries as $key => $query) {
                $results[$key] = $db->select($query['sql'], $query['bindings'] ?? []);
            }

            return $results;
        } catch (\Exception $e) {
            Log::error("Batch query failed: " . $e->getMessage());
            return [];
        }
    }
}
