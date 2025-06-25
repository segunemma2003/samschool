<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use App\Filament\Teacher\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Student;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ViewAssignmentSubmission extends Page
{
    protected static string $resource = AssignmentResource::class;

    protected static string $view = 'filament.teacher.pages.view-assignment-submission';
    protected static bool $shouldRegisterNavigation = false;
    public Assignment $assignment;
    public Student $student;
    public $submission;

    public function mount(): void
    {
        // Get parameters from the route
        $assignmentId = request()->route('assignment');
        $studentId = request()->route('student');

        $this->assignment = Assignment::findOrFail($assignmentId);
        $this->student = Student::with(['class', 'arm'])->findOrFail($studentId);

        // Get the submission data
        $this->submission = $this->student->assignments()
            ->where('assignment_id', $this->assignment->id)
            ->withPivot([
                'file',
                'status',
                'total_score',
                'answer',
                'comments_score',
                'created_at',
                'updated_at'
            ])
            ->first()?->pivot;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getTitle(): string
    {
        return "Assignment Submission";
    }

    public function getSubheading(): ?string
    {
        return "Student: {$this->student->name} | Assignment: {$this->assignment->title}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('grade_assignment')
                ->label('Grade Assignment')
                ->icon('heroicon-m-academic-cap')
                ->color('success')
                ->visible(fn () => $this->submission !== null)
                ->form([
                    Forms\Components\TextInput::make('total_score')
                        ->label('Score')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue($this->assignment->weight_mark)
                        ->suffix("/ {$this->assignment->weight_mark}")
                        ->default($this->submission?->total_score),

                    Forms\Components\Textarea::make('comments_score')
                        ->label('Teacher Comments')
                        ->placeholder('Provide detailed feedback for the student...')
                        ->rows(4)
                        ->default($this->submission?->comments_score),
                ])
                ->action(function (array $data): void {
                    $this->student->assignments()->updateExistingPivot($this->assignment->id, [
                        'total_score' => $data['total_score'],
                        'comments_score' => $data['comments_score'] ?? null,
                        'status' => 'submitted',
                    ]);

                    // Refresh the submission data
                    $this->submission = $this->student->assignments()
                        ->where('assignment_id', $this->assignment->id)
                        ->withPivot([
                            'file',
                            'status',
                            'total_score',
                            'answer',
                            'comments_score',
                            'created_at',
                            'updated_at'
                        ])
                        ->first()?->pivot;

                    Notification::make()
                        ->title('Assignment graded successfully!')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('download_file')
                ->label('Download Submission')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => $this->submission?->file !== null)
                ->url(function () {
                    if ($this->submission?->file) {
                        return route('secure.download.submission', [
                            'assignment' => $this->assignment->id,
                            'student' => $this->student->id
                        ]);
                    }
                    return null;
                })
                ->openUrlInNewTab(true),

            Actions\Action::make('back_to_submissions')
                ->label('Back to Submissions')
                ->icon('heroicon-m-arrow-left')
                ->color('gray')
                ->url(fn (): string => AssignmentResource::getUrl('view', ['record' => $this->assignment])),
        ];
    }

    public function getSubmissionStatus(): string
    {
        return match ($this->submission?->status ?? 'not_submitted') {
            'submitted' => 'Submitted',
            'draft' => 'Draft',
            'graded' => 'Graded',
            default => 'Not Submitted',
        };
    }

    public function getSubmissionStatusColor(): string
    {
        return match ($this->submission?->status ?? 'not_submitted') {
            'submitted' => 'success',
            'draft' => 'warning',
            'graded' => 'info',
            default => 'danger',
        };
    }

    public function getPercentageScore(): ?string
    {
        if (!$this->submission?->total_score || $this->assignment->weight_mark == 0) {
            return null;
        }

        $percentage = ($this->submission->total_score / $this->assignment->weight_mark) * 100;
        return number_format($percentage, 1) . '%';
    }

    public function getGradeColor(): string
    {
        if (!$this->submission?->total_score) {
            return 'gray';
        }

        $percentage = ($this->submission->total_score / $this->assignment->weight_mark) * 100;

        return match (true) {
            $percentage >= 80 => 'success',
            $percentage >= 60 => 'warning',
            default => 'danger',
        };
    }


}
