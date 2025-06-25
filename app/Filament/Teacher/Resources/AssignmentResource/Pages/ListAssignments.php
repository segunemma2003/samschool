<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use App\Filament\Teacher\Resources\AssignmentResource;
use App\Filament\Teacher\Widgets\AssignmentStatsWidget;
use App\Models\Assignment;
use App\Models\Teacher;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ListAssignments extends ListRecords
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Assignment')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->size('lg'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssignmentStatsWidget::class,
        ];
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getTitle(): string
    {
        return 'My Assignments';
    }

    public function getSubheading(): ?string
    {
        $teacher = $this->getCurrentTeacher();

        if (!$teacher) {
            return null;
        }

        $stats = Assignment::getStatsForTeacher($teacher->id);

        return "Total: {$stats['total']} | Active: {$stats['active']} | Overdue: {$stats['overdue']}";
    }

    private function getCurrentTeacher(): ?Teacher
    {
        $user = Auth::user();

        return Cache::remember(
            "teacher_data_{$user->id}",
            600,
            fn() => Teacher::where('email', $user->email)->first()
        );
    }
}
