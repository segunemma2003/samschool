<?php

namespace App\Filament\App\Resources\StudentGroupResource\Pages;

use App\Filament\App\Resources\StudentGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentGroup extends EditRecord
{
    protected static string $resource = StudentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
