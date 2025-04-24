<?php

namespace App\Filament\App\Resources\SchoolStoryResource\Pages;

use App\Filament\App\Resources\SchoolStoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolStory extends EditRecord
{
    protected static string $resource = SchoolStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
