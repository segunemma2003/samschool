<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Teacher;
use Illuminate\Support\Facades\Cache;

class AssignmentCacheService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const STATS_CACHE_TTL = 600; // 10 minutes

    public static function getTeacherAssignments(int $teacherId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $cacheKey = self::getTeacherAssignmentsCacheKey($teacherId, $filters);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($teacherId, $filters) {
            $query = Assignment::where('teacher_id', $teacherId)
                ->with(['class:id,name', 'subject:id,name,code', 'term:id,name', 'academy:id,title'])
                ->latest();

            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['class_id'])) {
                $query->where('class_id', $filters['class_id']);
            }

            if (!empty($filters['subject_id'])) {
                $query->where('subject_id', $filters['subject_id']);
            }

            if (!empty($filters['overdue'])) {
                $query->where('deadline', '<', now());
            }

            return $query->paginate(25);
        });
    }

    public static function getAssignmentStats(int $teacherId): array
    {
        $cacheKey = "assignment_stats_teacher_{$teacherId}";

        return Cache::remember($cacheKey, self::STATS_CACHE_TTL, function () use ($teacherId) {
            return Assignment::getStatsForTeacher($teacherId);
        });
    }

    public static function getTeacherData(int $userId): ?Teacher
    {
        $cacheKey = "teacher_data_{$userId}";

        return Cache::remember($cacheKey, self::STATS_CACHE_TTL, function () use ($userId) {
            $user = \App\Models\User::find($userId);
            return $user ? Teacher::where('email', $user->email)->first() : null;
        });
    }

    public static function invalidateTeacherCache(int $teacherId): void
    {
        $pattern = "assignment_teacher_{$teacherId}_*";

        // Clear specific caches
        Cache::forget("assignment_stats_teacher_{$teacherId}");
        Cache::forget("teacher_{$teacherId}_classes");
        Cache::forget("teacher_{$teacherId}_subjects");
        Cache::forget("teacher_{$teacherId}_pending_assignments");

        // Clear assignment caches for this teacher
        Cache::tags(["teacher_{$teacherId}"])->flush();
    }

    public static function invalidateAssignmentCache(int $assignmentId): void
    {
        Cache::forget("assignment_{$assignmentId}_submitted_count");
        Cache::forget("assignment_{$assignmentId}_class_total");
        Cache::forget("assignment_{$assignmentId}_submission_stats");
    }

    private static function getTeacherAssignmentsCacheKey(int $teacherId, array $filters): string
    {
        $filterHash = md5(serialize($filters));
        return "assignment_teacher_{$teacherId}_list_{$filterHash}";
    }
}
