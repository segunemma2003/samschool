<?php

namespace App\Filament\Teacher\Resources\QuestionBankResource\Pages;

use App\Filament\Teacher\Resources\QuestionBankResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;

class ViewQuestionBank extends ViewRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Question Details')
                    ->icon('heroicon-m-question-mark-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('exam.subject.code')
                                    ->label('Subject')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('exam.assessment_type')
                                    ->label('Assessment Type')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('question_type')
                                    ->label('Question Type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'multiple_choice' => 'success',
                                        'true_false' => 'warning',
                                        'open_ended' => 'info',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string =>
                                        match ($state) {
                                            'multiple_choice' => 'Multiple Choice',
                                            'true_false' => 'True/False',
                                            'open_ended' => 'Open Ended',
                                            default => $state,
                                        }
                                    ),

                                TextEntry::make('marks')
                                    ->label('Points')
                                    ->badge()
                                    ->suffix(' pts')
                                    ->color('success'),
                            ]),

                        TextEntry::make('question')
                            ->label('Question Text')
                            ->html()
                            ->columnSpanFull()
                            ->weight(FontWeight::Medium),

                        ImageEntry::make('image')
                            ->label('Question Image')
                            ->disk('s3')
                            ->visible(fn ($record) => !empty($record->image))
                            ->columnSpanFull(),
                    ]),

                Section::make('Answer Options')
                    ->icon('heroicon-m-check-circle')
                    ->schema([
                        RepeatableEntry::make('options')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('option')
                                            ->label('Option')
                                            ->weight(fn ($state, $record) =>
                                                ($state['is_correct'] ?? false) ? FontWeight::Bold : FontWeight::Normal
                                            )
                                            ->color(fn ($state, $record) =>
                                                ($state['is_correct'] ?? false) ? 'success' : 'gray'
                                            )
                                            ->icon(fn ($state, $record) =>
                                                ($state['is_correct'] ?? false) ? 'heroicon-m-check-circle' : null
                                            )
                                            ->columnSpan(2),

                                        TextEntry::make('is_correct')
                                            ->label('Correct')
                                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'success' : 'gray')
                                            ->columnSpan(1),
                                    ]),

                                ImageEntry::make('image')
                                    ->label('Option Image')
                                    ->disk('s3')
                                    ->visible(fn ($state) => !empty($state['image']))
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn ($record) =>
                                in_array($record->question_type, ['multiple_choice', 'true_false']) &&
                                !empty($record->options)
                            ),
                    ])
                    ->visible(fn ($record) =>
                        in_array($record->question_type, ['multiple_choice', 'true_false'])
                    ),

                Section::make('Model Answer')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        TextEntry::make('answer')
                            ->label('Expected Answer')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) =>
                        $record->question_type === 'open_ended' && !empty($record->answer)
                    ),

                Section::make('Additional Information')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        TextEntry::make('hint')
                            ->label('Hint')
                            ->html()
                            ->visible(fn ($record) => !empty($record->hint)),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('exam.term.name')
                                    ->label('Term')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('exam.academic.title')
                                    ->label('Academic Year')
                                    ->badge()
                                    ->color('primary'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('M j, Y g:i A'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('M j, Y g:i A'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
