<?php

namespace App\Filament\Ourparent\Resources\StudentFeeResource\Pages;

use App\Filament\Ourparent\Resources\StudentFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentFee extends EditRecord
{
    protected static string $resource = StudentFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
