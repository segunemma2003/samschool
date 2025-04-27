<?php

namespace App\Filament\Finance\Resources\FundRaisingProgramResource\Pages;

use App\Filament\Finance\Resources\FundRaisingProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundRaisingProgram extends EditRecord
{
    protected static string $resource = FundRaisingProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
