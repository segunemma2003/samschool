<?php

namespace App\Filament\App\Resources\PsychomotorResource\Pages;

use App\Filament\App\Resources\PsychomotorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPsychomotors extends ListRecords
{
    protected static string $resource = PsychomotorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
