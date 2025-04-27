<?php

namespace App\Filament\Ourparent\Resources\StudentFeeResource\Pages;

use App\Filament\Ourparent\Resources\StudentFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentFee extends ViewRecord
{
    protected static string $resource = StudentFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
