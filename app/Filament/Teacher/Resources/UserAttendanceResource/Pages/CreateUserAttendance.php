<?php

namespace App\Filament\Teacher\Resources\UserAttendanceResource\Pages;

use App\Filament\Teacher\Resources\UserAttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserAttendance extends CreateRecord
{
    protected static string $resource = UserAttendanceResource::class;
}
