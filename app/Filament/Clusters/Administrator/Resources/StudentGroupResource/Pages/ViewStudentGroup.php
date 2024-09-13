<?php

namespace App\Filament\Clusters\Administrator\Resources\StudentGroupResource\Pages;

use App\Filament\Clusters\Administrator\Resources\StudentGroupResource;
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
