<?php

namespace App\Filament\Ourparent\Resources\FundraisingProgramResource\Pages;

use App\Filament\Ourparent\Resources\FundraisingProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFundraisingPrograms extends ListRecords
{
    protected static string $resource = FundraisingProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
