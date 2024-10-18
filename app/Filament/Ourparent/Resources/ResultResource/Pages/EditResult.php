<?php

namespace App\Filament\Ourparent\Resources\ResultResource\Pages;

use App\Filament\Ourparent\Resources\ResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResult extends EditRecord
{
    protected static string $resource = ResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
