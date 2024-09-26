<?php

namespace App\Filament\App\Resources\GuardiansResource\Pages;

use App\Filament\App\Resources\GuardiansResource;
use App\Filament\Imports\GuardiansImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuardians extends ListRecords
{
    protected static string $resource = GuardiansResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(GuardiansImporter::class),
        ];
    }

    // protected function getActions(): array
    // {
    //     return [

    //         Actions\CreateAction::make(),
    //     ];
    // }
}
