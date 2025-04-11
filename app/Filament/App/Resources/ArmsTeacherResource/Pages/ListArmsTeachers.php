<?php

namespace App\Filament\App\Resources\ArmsTeacherResource\Pages;

use App\Filament\App\Resources\ArmsTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArmsTeachers extends ListRecords
{
    protected static string $resource = ArmsTeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
