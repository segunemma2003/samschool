<?php

namespace App\Filament\App\Resources\PsychomotorCategoryResource\Pages;

use App\Filament\App\Resources\PsychomotorCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPsychomotorCategories extends ListRecords
{
    protected static string $resource = PsychomotorCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
