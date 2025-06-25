<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use App\Filament\Teacher\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Student;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms;

class SubmittedStudents extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = AssignmentResource::class;

    protected static string $view = 'filament.teacher.pages.submitted-students';

    public Assignment $record;

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getTitle(): string
    {
        return "Submissions: {$this->record->title}";
    }

    public function getSubheading(): ?string
    {
        $stats = $this->record->getSubmissionStats();

        return "Total Submissions: {$stats['submitted']} | Average Score: {$stats['avg_score']}% | Deadline: {$this->record->deadline->format('M j, Y g:i A')}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_submissions')
                ->label('Download All Submissions')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    // Implementation for downloading all submissions
                    $this->downloadAllSubmissions();
                }),

            Actions\Action::make('export_grades')
                ->label('Export Grades')
                ->icon('heroicon-m-table-cells')
                ->color('success')
                ->action(function () {
                    // Implementation for exporting grades
                    $this->exportGrades();
                }),

            Actions\Action::make('back_to_assignments')
                ->label('Back to Assignments')
                ->icon('heroicon-m-arrow-left')
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('index')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->whereHas('assignments', function (Builder $query) {
                        $query->where('assignment_id', $this->record->id);
                    })
                    ->with([
                        'assignments' => function ($query) {
                            $query->where('assignment_id', $this->record->id);
                        }
                    ])
            )
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=Student&color=7c3aed&background=ede9fe')
                    ->size(40),

                TextColumn::make('name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('registration_number')
                    ->label('Reg. Number')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Registration number copied'),

                BadgeColumn::make('submission_status')
                    ->label('Status')
                    ->getStateUsing(function (Student $record): string {
                        $submission = $record->assignments->first()?->pivot;
                        return $submission?->status ?? 'not_submitted';
                    })
                    ->colors([
                        'danger' => 'not_submitted',
                        'warning' => 'draft',
                        'success' => 'submitted',
                        'info' => 'graded',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'not_submitted' => 'Not Submitted',
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'graded' => 'Graded',
                        default => ucfirst($state),
                    }),

                TextColumn::make('submission_date')
                    ->label('Submitted At')
                    ->getStateUsing(function (Student $record): ?string {
                        $submission = $record->assignments->first()?->pivot;
                        return $submission?->updated_at?->format('M j, Y g:i A');
                    })
                    ->placeholder('Not submitted'),

                TextColumn::make('total_score')
                    ->label('Score')
                    ->getStateUsing(function (Student $record): string {
                        $submission = $record->assignments->first()?->pivot;
                        $score = $submission?->total_score;

                        if ($score === null) {
                            return 'Not graded';
                        }

                        return "{$score}/{$this->record->weight_mark}";
                    })
                    ->badge()
                    ->color(function (Student $record): string {
                        $submission = $record->assignments->first()?->pivot;
                        $score = $submission?->total_score;

                        if ($score === null) {
                            return 'gray';
                        }

                        $percentage = ($score / $this->record->weight_mark) * 100;

                        return match (true) {
                            $percentage >= 80 => 'success',
                            $percentage >= 60 => 'warning',
                            default => 'danger',
                        };
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'draft' => 'Draft',
                        'not_submitted' => 'Not Submitted',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['value']) {
                            return $query;
                        }

                        return $query->whereHas('assignments', function (Builder $q) use ($data) {
                            $q->where('assignment_id', $this->record->id)
                              ->where('status', $data['value']);
                        });
                    }),
            ])
            ->actions([
                Action::make('view_submission')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->modalHeading('Student Submission')
                    ->modalContent(function (Student $record) {
                        $submission = $record->assignments->first()?->pivot;

                        if (!$submission) {
                            return view('filament.teacher.modals.no-submission');
                        }

                        return view('filament.teacher.modals.submission-details', [
                            'student' => $record,
                            'submission' => $submission,
                            'assignment' => $this->record,
                        ]);
                    })
                    ->modalWidth('4xl'),

                Action::make('grade')
                    ->label('Grade')
                    ->icon('heroicon-m-academic-cap')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('total_score')
                            ->label('Score')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue($this->record->weight_mark)
                            ->suffix("/ {$this->record->weight_mark}"),

                        Forms\Components\Textarea::make('comments_score')
                            ->label('Comments')
                            ->placeholder('Provide feedback for the student...')
                            ->rows(4),
                    ])
                    ->fillForm(function (Student $record): array {
                        $submission = $record->assignments->first()?->pivot;

                        return [
                            'total_score' => $submission?->total_score,
                            'comments_score' => $submission?->comments_score,
                        ];
                    })
                    ->action(function (Student $record, array $data): void {
                        $record->assignments()->updateExistingPivot($this->record->id, [
                            'total_score' => $data['total_score'],
                            'comments_score' => $data['comments_score'] ?? null,
                            'status' => 'submitted', // Update status to submitted if graded
                        ]);

                        $this->notify('success', 'Grade updated successfully!');
                    })
                    ->visible(fn (Student $record): bool =>
                        $record->assignments->first()?->pivot !== null
                    ),

                Action::make('download_file')
                    ->label('Download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->action(function (Student $record) {
                        $submission = $record->assignments->first()?->pivot;

                        if ($submission?->file) {
                            return Storage::disk('s3')->download($submission->file);
                        }

                        $this->notify('warning', 'No file submitted by this student.');
                    })
                    ->visible(fn (Student $record): bool =>
                        $record->assignments->first()?->pivot?->file !== null
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_grade')
                    ->label('Bulk Grade')
                    ->icon('heroicon-m-academic-cap')
                    ->form([
                        Forms\Components\TextInput::make('total_score')
                            ->label('Score for all selected')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue($this->record->weight_mark),

                        Forms\Components\Textarea::make('comments_score')
                            ->label('Comments for all selected')
                            ->placeholder('This comment will be applied to all selected students...'),
                    ])
                    ->action(function (array $data, $records): void {
                        foreach ($records as $student) {
                            $student->assignments()->updateExistingPivot($this->record->id, [
                                'total_score' => $data['total_score'],
                                'comments_score' => $data['comments_score'] ?? null,
                                'status' => 'submitted',
                            ]);
                        }

                        $this->notify('success', 'Bulk grading completed successfully!');
                    }),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('No submissions yet')
            ->emptyStateDescription('Students haven\'t submitted their assignments yet.')
            ->striped()
            ->paginated();
    }

    private function downloadAllSubmissions(): void
    {
        // Implementation for downloading all submissions as a ZIP file
        $this->notify('info', 'Download functionality will be implemented.');
    }

    private function exportGrades(): void
    {
        // Implementation for exporting grades to Excel/CSV
        $this->notify('info', 'Export functionality will be implemented.');
    }

    private function notify(string $type, string $message): void
    {
        \Filament\Notifications\Notification::make()
            ->title($message)
            ->{$type}()
            ->send();
    }
}
