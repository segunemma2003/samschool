<?php

namespace App\Filament\Finance\Resources\FundRaisingProgramResource\Pages;

use App\Filament\Finance\Resources\FundRaisingProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFundRaisingPrograms extends ListRecords
{
    protected static string $resource = FundRaisingProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
