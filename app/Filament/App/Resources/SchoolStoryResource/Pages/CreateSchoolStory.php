<?php

namespace App\Filament\App\Resources\SchoolStoryResource\Pages;

use App\Filament\App\Resources\SchoolStoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSchoolStory extends CreateRecord
{
    protected static string $resource = SchoolStoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['admin_id'] = Auth::id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
