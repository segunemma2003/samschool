<?php

namespace App\Filament\Ourparent\Resources\ComplainResource\Pages;

use App\Filament\Ourparent\Resources\ComplainResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplain extends EditRecord
{
    protected static string $resource = ComplainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
