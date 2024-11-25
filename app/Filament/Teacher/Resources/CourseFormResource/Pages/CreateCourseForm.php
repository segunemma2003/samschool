<?php

namespace App\Filament\Teacher\Resources\CourseFormResource\Pages;

use App\Filament\Teacher\Resources\CourseFormResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourseForm extends CreateRecord
{
    protected static string $resource = CourseFormResource::class;

    public function mount(): void
    {
        parent::mount();

        // Additional logic if needed during mounting
    }

    public function getView(): string
    {
        // Point to your custom Blade view
        return 'filament.forms.custom-subjects-form';
    }


    // public function getViewData(): array
    // {
    //     $user = Auth::user();
    //     $student = Student::where('email', $user->email)->first();
    //     $subjects = collect();
    //     $terms = Term::all();

    //     if ($student) {
    //         $subjects = Subject::where('class_id', $student->class->id)->get();
    //     }

    //     return [
    //         'subjects' => $subjects,
    //         'terms' => $terms,
    //     ];
    // }
}
