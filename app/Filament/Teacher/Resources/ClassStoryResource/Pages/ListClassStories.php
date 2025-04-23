<?php

namespace App\Filament\Teacher\Resources\ClassStoryResource\Pages;

use App\Filament\Teacher\Resources\ClassStoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassStories extends ListRecords
{
    protected static string $resource = ClassStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
