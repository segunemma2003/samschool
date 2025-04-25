<?php

namespace App\Filament\Hostel\Resources\HostelRuleViolationResource\Pages;

use App\Filament\Hostel\Resources\HostelRuleViolationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHostelRuleViolation extends ViewRecord
{
    protected static string $resource = HostelRuleViolationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
