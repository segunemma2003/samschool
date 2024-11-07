<?php

namespace App\Filament\App\Resources\ArmResource\Pages;

use App\Filament\App\Resources\ArmResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageArms extends ManageRecords
{
    protected static string $resource = ArmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
