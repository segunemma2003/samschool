<?php

namespace App\Filament\App\Resources\ArmsTeacherResource\Pages;

use App\Filament\App\Resources\ArmsTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArmsTeacher extends EditRecord
{
    protected static string $resource = ArmsTeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
