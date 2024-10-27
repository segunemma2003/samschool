<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use App\Filament\Teacher\Resources\AssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class SubmittedStudents extends ViewRecord
{
    protected static string $resource = AssignmentResource::class;
    protected static ?string $title = '';
    protected ?string $heading = '';
    protected static string $view = 'filament.teacher.resources.assignment.pages.submitted_students';
}
