<?php

namespace App\Filament\Library\Resources\HostelApplicationResource\Pages;

use App\Filament\Library\Resources\HostelApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelApplication extends EditRecord
{
    protected static string $resource = HostelApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
