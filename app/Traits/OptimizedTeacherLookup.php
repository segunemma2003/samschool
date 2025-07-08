<?php

namespace App\Traits;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait OptimizedTeacherLookup
{

    protected static function getCurrentTeacher(): ?Teacher
    {
        $userId = auth()->id();

        if (!$userId) {
            return null;
        }

        return Cache::remember(
            "current_teacher_{$userId}",
            1800, // 10 minutes
            fn() => Teacher::where('email', auth()->user()->email)->first()
        );
    }

    /**
     * Clear the teacher cache for the current user
     */
    protected function clearTeacherCache(?int $userId = null): void
    {
        try {
            $userId = $userId ?? Auth::id();
            if ($userId) {
                Cache::forget("teacher_for_user_{$userId}");
            }
        } catch (\Exception $e) {
            Log::error("Error clearing teacher cache: " . $e->getMessage());
        }
    }

    /**
     * Get teacher with additional relationships loaded
     */
    protected function getCurrentTeacherWithRelations(array $relations = []): ?Teacher
    {
        try {
            $teacher = $this->getCurrentTeacher();

            if ($teacher && !empty($relations)) {
                return Teacher::with($relations)->find($teacher->id);
            }

            return $teacher;
        } catch (\Exception $e) {
            Log::error("Error in getCurrentTeacherWithRelations: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if current user is a teacher
     */
    protected function isCurrentUserTeacher(): bool
    {
        try {
            return $this->getCurrentTeacher() !== null;
        } catch (\Exception $e) {
            Log::error("Error in isCurrentUserTeacher: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get teacher subjects with caching
     */
    protected function getCurrentTeacherSubjects()
    {
        try {
            $teacher = $this->getCurrentTeacher();

            if (!$teacher) {
                return collect();
            }

            return Cache::remember("teacher_subjects_{$teacher->id}", 1800, function() use ($teacher) {
                return $teacher->subjects()
                    ->with(['subjectDepot:id,name,code', 'class:id,name'])
                    ->select('id', 'code', 'class_id', 'teacher_id', 'subject_depot_id')
                    ->get();
            });
        } catch (\Exception $e) {
            Log::error("Error in getCurrentTeacherSubjects: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get teacher classes with caching
     */
    protected function getCurrentTeacherClasses()
    {
        try {
            $teacher = $this->getCurrentTeacher();

            if (!$teacher) {
                return collect();
            }

            return Cache::remember("teacher_classes_{$teacher->id}", 1800, function() use ($teacher) {
                return $teacher->classes()
                    ->select('id', 'name', 'class_numeric', 'teacher_id')
                    ->get();
            });
        } catch (\Exception $e) {
            Log::error("Error in getCurrentTeacherClasses: " . $e->getMessage());
            return collect();
        }
    }
}
