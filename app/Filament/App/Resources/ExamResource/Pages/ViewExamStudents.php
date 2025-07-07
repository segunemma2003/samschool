<?php

namespace App\Filament\App\Resources\ExamResource\Pages;

use App\Filament\App\Resources\ExamResource;
use App\Models\QuizScore;
use App\Models\Student;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\Alignment;

class ViewExamStudents extends Page implements HasTable
{
    use InteractsWithRecord, InteractsWithTable;

    protected static string $resource = ExamResource::class;
    protected static string $view = 'filament.app.resources.exam-resource.pages.view-exam-students';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                QuizScore::query()
                    ->where('exam_id', $this->record->id)
                    ->with(['student', 'exam'])
            )
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.registration_number')
                    ->label('Registration Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.class.name')
                    ->label('Class')
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_score')
                    ->label('Score')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(function ($state, $record) {
                        $percentage = $record->exam->total_score > 0
                            ? round(($state / $record->exam->total_score) * 100, 1)
                            : 0;
                        return "{$state}/{$record->exam->total_score} ({$percentage}%)";
                    }),

                TextColumn::make('approved')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Approved' : 'Pending'),

                TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => static::getResource()::getUrl('exam-student-details', ['quizScoreId' => $record->id]))
                    ->openUrlInNewTab(false),
            ])
            ->defaultSort('total_score', 'desc')
            ->striped();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Back to Exams')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function getTitle(): string
    {
        $subjectName = $this->record->subject->subjectDepot->name ?? $this->record->subject->code;
        return "Students - {$subjectName} ({$this->record->assessment_type})";
    }
}
