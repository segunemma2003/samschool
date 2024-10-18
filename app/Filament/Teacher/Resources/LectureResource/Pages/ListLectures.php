<?php

namespace App\Filament\Teacher\Resources\LectureResource\Pages;

use App\Filament\Teacher\Resources\LectureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLectures extends ListRecords
{
    protected static string $resource = LectureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
