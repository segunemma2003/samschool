<?php

namespace App\Filament\App\Resources\SyllabusResource\Pages;

use App\Filament\App\Resources\SyllabusResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSyllabus extends ViewRecord
{
    protected static string $resource = SyllabusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
        {
            return $infolist
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('class.name'),
                    TextEntry::make('subject.name'),
                    Section::make('Details')
    ->description('This is an outline for the syllabus')
    ->schema([
        TextEntry::make('description')->label('')->html()->columnSpanFull(),
    ])

                ]);
        }


}
