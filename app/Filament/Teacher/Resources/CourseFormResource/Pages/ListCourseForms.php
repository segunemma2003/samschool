<?php

namespace App\Filament\Teacher\Resources\CourseFormResource\Pages;

use App\Filament\Teacher\Resources\CourseFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseForms extends ListRecords
{
    protected static string $resource = CourseFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public function getView(): string
    {
        // Point to your custom Blade view
        return 'filament.forms.custom-subjects-form';
    }
}
