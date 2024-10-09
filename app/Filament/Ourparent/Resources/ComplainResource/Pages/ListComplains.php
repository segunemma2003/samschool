<?php

namespace App\Filament\Ourparent\Resources\ComplainResource\Pages;

use App\Filament\Ourparent\Resources\ComplainResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComplains extends ListRecords
{
    protected static string $resource = ComplainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
