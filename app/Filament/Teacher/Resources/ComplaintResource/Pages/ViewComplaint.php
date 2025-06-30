<?php

namespace App\Filament\Teacher\Resources\ComplaintResource\Pages;

use App\Filament\Teacher\Resources\ComplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

class ViewComplaint extends ViewRecord
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->color('warning'),
            Actions\Action::make('resolve')
                ->label('Mark as Resolved')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => 'resolved']))
                ->visible(fn (): bool => $this->record->status !== 'resolved'),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Complaint Overview')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Title')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->columnSpanFull(),

                                TextEntry::make('student.name')
                                    ->label('Student')
                                    ->icon('heroicon-m-user')
                                    ->color('primary'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->colors([
                                        'gray' => 'pending',
                                        'warning' => 'investigating',
                                        'success' => 'resolved',
                                        'danger' => 'closed',
                                    ]),

                                TextEntry::make('priority')
                                    ->label('Priority')
                                    ->badge()
                                    ->colors([
                                        'success' => 'low',
                                        'warning' => 'medium',
                                        'danger' => 'high',
                                        'red' => 'urgent',
                                    ]),

                                TextEntry::make('category')
                                    ->label('Category')
                                    ->badge()
                                    ->colors([
                                        'primary' => 'academic',
                                        'warning' => 'behavioral',
                                        'danger' => 'bullying',
                                        'success' => 'facilities',
                                        'secondary' => 'attendance',
                                        'gray' => 'other',
                                    ])
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'academic' => 'Academic Issues',
                                        'behavioral' => 'Behavioral Problems',
                                        'attendance' => 'Attendance Issues',
                                        'bullying' => 'Bullying/Harassment',
                                        'facilities' => 'Facilities/Infrastructure',
                                        'other' => 'Other',
                                        default => $state,
                                    }),
                                TextEntry::make('incident_date')
                                    ->label('Incident Date')
                                    ->date('F j, Y')
                                    ->icon('heroicon-m-calendar-days'),
                            ]),
                    ]),

                Section::make('Description')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->prose()
                            ->html(),
                    ]),

                Section::make('Resolution Notes')
                    ->icon('heroicon-m-check-circle')
                    ->schema([
                        TextEntry::make('resolution_notes')
                            ->hiddenLabel()
                            ->prose()
                            ->html()
                            ->placeholder('No resolution notes yet...'),
                    ])
                    ->visible(fn (): bool => !empty($this->record->resolution_notes)),

                Section::make('Timeline')
                    ->icon('heroicon-m-clock')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Reported')
                                    ->dateTime('F j, Y \a\t g:i A')
                                    ->icon('heroicon-m-plus-circle')
                                    ->color('primary'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('F j, Y \a\t g:i A')
                                    ->icon('heroicon-m-pencil-square')
                                    ->color('gray'),
                            ]),
                    ]),
            ]);
    }
}
