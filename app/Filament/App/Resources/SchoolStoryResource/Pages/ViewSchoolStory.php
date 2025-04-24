<?php

namespace App\Filament\App\Resources\SchoolStoryResource\Pages;

use App\Filament\App\Resources\SchoolStoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSchoolStory extends ViewRecord
{
    protected static string $resource = SchoolStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
