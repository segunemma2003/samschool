<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use App\Filament\Teacher\Resources\AssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSubmittedAssignmentTeacher extends ViewRecord
{
    protected static string $resource = AssignmentResource::class;
    protected static string $view = 'filament.teacher.resources.assignment-resource.pages.view-submitted-assignment-teacher';
}
