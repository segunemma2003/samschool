<?php

namespace App\Filament\App\Resources\ResultSectionResource\Pages;

use App\Filament\App\Resources\ResultSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResultSection extends EditRecord
{
    protected static string $resource = ResultSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
