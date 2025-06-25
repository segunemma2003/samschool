<?php

// app/Filament/Teacher/Widgets/AssignmentStatsWidget.php

namespace App\Filament\Teacher\Widgets;

use App\Models\Assignment;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AssignmentStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $teacher = $this->getCurrentTeacher();

        if (!$teacher) {
            return [];
        }

        // Cache stats for 5 minutes
        $stats = Cache::remember("assignment_stats_{$teacher->id}", 300, function () use ($teacher) {
            return Assignment::getStatsForTeacher($teacher->id);
        });

        return [
            Stat::make('Total Assignments', $stats['total'])
                ->description('All your assignments')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5])
                ->url(route('filament.teacher.resources.assignments.index')),

            Stat::make('Active Assignments', $stats['active'])
                ->description('Currently available to students')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([3, 1, 2, 4, 3, 2, 1])
                ->url(route('filament.teacher.resources.assignments.index', ['tableFilters[status][value]' => 'available'])),

            Stat::make('Overdue Assignments', $stats['overdue'])
                ->description('Past deadline but still open')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stats['overdue'] > 0 ? 'danger' : 'success')
                ->chart([0, 1, 0, 2, 1, 0, 0])
                ->url(route('filament.teacher.resources.assignments.index', ['tableFilters[overdue][value]' => true])),

            Stat::make('With Submissions', $stats['with_submissions'])
                ->description('Assignments receiving student work')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning')
                ->chart([1, 2, 1, 3, 2, 1, 2])
                ->url(route('filament.teacher.resources.assignments.index', ['tableFilters[with_submissions][value]' => true])),
        ];
    }

    private function getCurrentTeacher(): ?Teacher
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return Cache::remember(
            "teacher_data_{$user->id}",
            600,
            fn() => Teacher::where('email', $user->email)->first()
        );
    }

    public static function canView(): bool
    {
        return Auth::check();
    }
}
