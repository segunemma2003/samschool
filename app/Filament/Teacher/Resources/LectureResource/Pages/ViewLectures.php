<?php

namespace App\Filament\Teacher\Resources\LectureResource\Pages;

use App\Filament\Teacher\Resources\LectureResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewLectures extends ViewRecord
{
    protected static string $resource = LectureResource::class;

    protected static ?string $title = '';
    protected ?string $heading = '';
    protected static string $view = 'filament.teacher.resources.lectures.pages.view_lectures';



    public function lecturesInfolist(Infolist $infolist): Infolist
    {
        return $infolist
                ->record($this->record)
                ->schema([

                    TextEntry::make('subject.name'),
                    TextEntry::make('teacher.name'),
                    TextEntry::make('meeting_link'),
                    TextEntry::make('date_of_meeting'),
                    TextEntry::make('time_of_meeting'),
                    TextEntry::make('created_at')->since(),
                    TextEntry::make('title')->label('Topic'),

                    TextEntry::make('other_materials_links'),
                    TextEntry::make('description')->label('Details')->columnSpanFull(),
                    TextEntry::make('note')->html()->columnSpanFull(),

                ]);
    }
}
