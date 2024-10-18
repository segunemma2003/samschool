<?php

namespace App\Filament\Teacher\Resources\StudentResource\Pages;

use App\Filament\Teacher\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;
}
