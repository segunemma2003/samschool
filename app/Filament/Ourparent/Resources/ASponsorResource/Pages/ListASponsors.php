<?php

namespace App\Filament\Ourparent\Resources\ASponsorResource\Pages;

use App\Filament\Ourparent\Resources\ASponsorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListASponsors extends ListRecords
{
    protected static string $resource = ASponsorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
