<?php

namespace App\Filament\Teacher\Resources\ClassStoryResource\Pages;

use App\Filament\Teacher\Resources\ClassStoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClassStory extends ViewRecord
{
    protected static string $resource = ClassStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
