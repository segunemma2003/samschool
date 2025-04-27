<?php

namespace App\Filament\Finance\Resources\FundRaisingProgramResource\Pages;

use App\Filament\Finance\Resources\FundRaisingProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFundRaisingProgram extends ViewRecord
{
    protected static string $resource = FundRaisingProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
