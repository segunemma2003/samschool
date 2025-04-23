<?php

namespace App\Filament\Teacher\Resources\ClassStoryResource\Pages;

use App\Filament\Teacher\Resources\ClassStoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassStory extends EditRecord
{
    protected static string $resource = ClassStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
