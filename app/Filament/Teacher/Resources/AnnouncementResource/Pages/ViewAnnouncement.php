<?php

namespace App\Filament\Teacher\Resources\AnnouncementResource\Pages;

use App\Filament\Teacher\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;

class ViewAnnouncement extends ViewRecord
{
    protected static string $resource = AnnouncementResource::class;

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::FourExtraLarge;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn (Model $record): bool => $record->from_id === Auth::id())
                ->icon('heroicon-m-pencil-square')
                ->color('warning'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('title')
                                        ->size(TextEntry\TextEntrySize::Large)
                                        ->weight('bold')
                                        ->color('primary'),

                                    TextEntry::make('type_of_user_sent_to')
                                        ->label('Target Audience')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'all' => 'success',
                                            'teacher' => 'primary',
                                            'student' => 'warning',
                                            'admin' => 'danger',
                                            'parent' => 'info',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn (string $state): string => match ($state) {
                                            'all' => 'Everyone',
                                            'teacher' => 'Teachers',
                                            'student' => 'Students',
                                            'admin' => 'Administrators',
                                            'parent' => 'Parents',
                                            default => ucfirst($state),
                                        }),
                                ]),
                        ]),

                        TextEntry::make('sub')
                            ->label('Subtitle')
                            ->visible(fn ($state): bool => !empty($state)),

                        TextEntry::make('text')
                            ->label('Content')
                            ->html()
                            ->visible(fn ($state): bool => !empty($state)),

                        TextEntry::make('link')
                            ->label('Additional Link')
                            ->url('link')
                            ->openUrlInNewTab()
                            ->visible(fn ($state): bool => !empty($state)),

                        ImageEntry::make('file')
                            ->label('Attachment')
                            ->visible(fn ($state): bool => !empty($state))
                            ->disk('public')
                            ->height(400),

                        // Only show priority if column exists
                        ...(Schema::hasColumn('announcements', 'priority') ? [
                            TextEntry::make('priority')
                                ->label('Priority')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'urgent' => 'danger',
                                    'high' => 'warning',
                                    'medium' => 'primary',
                                    'low' => 'success',
                                    default => 'gray',
                                })
                                ->icon(fn (string $state): string => match ($state) {
                                    'urgent' => 'heroicon-m-exclamation-triangle',
                                    'high' => 'heroicon-m-exclamation-circle',
                                    'medium' => 'heroicon-m-information-circle',
                                    'low' => 'heroicon-m-chat-bubble-bottom-center-text',
                                    default => 'heroicon-m-bell',
                                }),
                        ] : []),

                        // Only show views count if column exists
                        ...(Schema::hasColumn('announcements', 'views_count') ? [
                            TextEntry::make('views_count')
                                ->label('Views')
                                ->icon('heroicon-m-eye')
                                ->numeric(),
                        ] : []),
                    ]),

                Section::make('Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('owner.name')
                                    ->label('Posted by')
                                    ->icon('heroicon-m-user'),

                                TextEntry::make('created_at')
                                    ->label('Published')
                                    ->since()
                                    ->icon('heroicon-m-clock'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->since()
                                    ->icon('heroicon-m-pencil-square')
                                    ->visible(fn ($record): bool => $record->created_at != $record->updated_at),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Increment views count if column exists
        if (Schema::hasColumn('announcements', 'views_count')) {
            $record->increment('views_count');
        }

        return parent::handleRecordUpdate($record, $data);
    }
}
