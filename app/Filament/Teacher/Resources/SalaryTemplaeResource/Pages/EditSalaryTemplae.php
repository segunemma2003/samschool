<?php

namespace App\Filament\Teacher\Resources\SalaryTemplaeResource\Pages;

use App\Filament\Teacher\Resources\SalaryTemplaeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalaryTemplae extends EditRecord
{
    protected static string $resource = SalaryTemplaeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
