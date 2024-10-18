<?php

namespace App\Filament\Teacher\Resources\UserAttendanceResource\Pages;

use App\Filament\Teacher\Resources\UserAttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserAttendance extends EditRecord
{
    protected static string $resource = UserAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
