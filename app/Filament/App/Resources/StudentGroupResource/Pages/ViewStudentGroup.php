<?php

namespace App\Filament\App\Resources\StudentGroupResource\Pages;

use App\Filament\App\Resources\StudentGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentGroup extends ViewRecord
{
    protected static string $resource = StudentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
