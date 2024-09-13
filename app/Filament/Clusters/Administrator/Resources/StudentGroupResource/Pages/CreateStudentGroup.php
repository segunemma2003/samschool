<?php

namespace App\Filament\Clusters\Administrator\Resources\StudentGroupResource\Pages;

use App\Filament\Clusters\Administrator\Resources\StudentGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentGroup extends CreateRecord
{
    protected static string $resource = StudentGroupResource::class;
}
