<?php

namespace App\Filament\App\Resources\ExamResource\Pages;

use App\Filament\App\Resources\ExamResource;
use App\Models\QuizScore;
use App\Models\QuizSubmission;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ViewExamQuestions extends Page implements HasTable
{
    use InteractsWithRecord, InteractsWithTable;

    protected static string $resource = ExamResource::class;
    protected static string $view = 'filament.app.resources.exam-resource.pages.view-exam-questions';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->record->questions()->getQuery()
            )
            ->columns([
                TextColumn::make('question')
                    ->label('Question')
                    ->searchable()
                    ->limit(100)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 100) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('question_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'multiple_choice' => 'success',
                        'true_false' => 'warning',
                        'open_ended' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True/False',
                        'open_ended' => 'Open Ended',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),

                TextColumn::make('answer')
                    ->label('Correct Answer')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('marks')
                    ->label('Marks')
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->color('primary'),

                TextColumn::make('hint')
                    ->label('Hint')
                    ->limit(50)
                    ->placeholder('No hint')
                    ->toggleable(isToggledHiddenByDefault: true),

                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('s3')
                    ->visibility('private')
                    ->size(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Question Details')
                    ->modalContent(function ($record) {
                        return view('filament.app.resources.exam-resource.modals.question-details', [
                            'question' => $record
                        ]);
                    })
                    ->modalWidth('3xl'),

                Action::make('view_responses')
                    ->label('View Responses')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->modalHeading('Student Responses')
                    ->modalContent(function ($record) {
                        $responses = QuizSubmission::where('question_id', $record->id)
                            ->with(['quizScore.student'])
                            ->get();

                        return view('filament.app.resources.exam-resource.modals.question-responses', [
                            'responses' => $responses,
                            'question' => $record
                        ]);
                    })
                    ->modalWidth('4xl'),

                Action::make('statistics')
                    ->label('Statistics')
                    ->icon('heroicon-o-chart-bar')
                    ->color('warning')
                    ->modalHeading('Question Statistics')
                    ->modalContent(function ($record) {
                        $total = QuizSubmission::where('question_id', $record->id)->count();
                        $correct = QuizSubmission::where('question_id', $record->id)
                            ->where('answer', $record->answer)
                            ->count();
                        $incorrect = $total - $correct;
                        $percentage = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

                        return view('filament.app.resources.exam-resource.modals.question-statistics', [
                            'total' => $total,
                            'correct' => $correct,
                            'incorrect' => $incorrect,
                            'percentage' => $percentage,
                            'question' => $record
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Back to Exam')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('view', ['record' => $this->record]))
                ->color('gray'),

            \Filament\Actions\Action::make('view_students')
                ->label('View Students')
                ->icon('heroicon-o-user-group')
                ->url($this->getResource()::getUrl('view-students', ['record' => $this->record]))
                ->color('info'),

            \Filament\Actions\Action::make('export_questions')
                ->label('Export Questions')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    // You can implement export functionality here
                    Notification::make()
                        ->title('Export functionality')
                        ->body('Export feature can be implemented here')
                        ->info()
                        ->send();
                })
                ->color('success'),
        ];
    }

    public function getTitle(): string
    {
        $subjectName = $this->record->subject->subjectDepot->name ?? $this->record->subject->code;
        return "Exam Questions - {$subjectName} ({$this->record->assessment_type})";
    }
}
