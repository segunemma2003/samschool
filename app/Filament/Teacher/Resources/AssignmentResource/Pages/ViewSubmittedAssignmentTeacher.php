<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use App\Filament\Teacher\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Student;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Resources\Pages\ViewRecord;

class ViewSubmittedAssignmentTeacher extends Page
{
    protected static string $resource = AssignmentResource::class;
    protected static string $view = 'filament.teacher.resources.assignment-resource.pages.view-submitted-assignment-teacher';

    // protected static ?string $title = '';
    protected ?string $heading = '';


    // protected static ?string $panel = "teacher";

    public  $assignment;
    public  $student;
    public function mount()
    {


        $this->assignment = $_GET['assignment'];;
        $this->student = $_GET['student'];;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }





}
