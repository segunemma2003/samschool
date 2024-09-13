<?php

namespace App\Filament\Resources\CustomTenantResource\Pages;

use App\Filament\Resources\CustomTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\ViewTenant;

class ViewCustomTenant extends ViewTenant
{
    protected static string $resource = CustomTenantResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\EditAction::make(),
    //     ];
    // }
}
