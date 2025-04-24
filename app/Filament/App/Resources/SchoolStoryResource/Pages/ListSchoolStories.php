<?php

namespace App\Filament\App\Resources\SchoolStoryResource\Pages;

use App\Filament\App\Resources\SchoolStoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolStories extends ListRecords
{
    protected static string $resource = SchoolStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
