<?php

namespace App\Filament\Ourparent\Resources\FundraisingProgramResource\Pages;

use App\Filament\Ourparent\Resources\FundraisingProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundraisingProgram extends EditRecord
{
    protected static string $resource = FundraisingProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make(),
            // Actions\DeleteAction::make(),
        ];
    }
}
