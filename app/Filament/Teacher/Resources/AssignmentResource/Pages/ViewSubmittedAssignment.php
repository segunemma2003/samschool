<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use App\Filament\Teacher\Resources\AssignmentResource;
use Filament\Resources\Pages\Page;

class ViewSubmittedAssignment extends Page
{
    protected static string $resource = AssignmentResource::class;

    protected static string $view = 'filament.teacher.resources.assignment-resource.pages.view-submitted-assignment';
}
