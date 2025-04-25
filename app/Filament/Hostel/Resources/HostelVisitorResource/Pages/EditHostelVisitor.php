<?php

namespace App\Filament\Hostel\Resources\HostelVisitorResource\Pages;

use App\Filament\Hostel\Resources\HostelVisitorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelVisitor extends EditRecord
{
    protected static string $resource = HostelVisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
