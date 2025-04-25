<?php

namespace App\Filament\Hostel\Resources\HostelRuleViolationResource\Pages;

use App\Filament\Hostel\Resources\HostelRuleViolationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostelRuleViolation extends EditRecord
{
    protected static string $resource = HostelRuleViolationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
