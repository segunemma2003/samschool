<?php

namespace App\Filament\Resources\CustomTenantResource\Pages;

use App\Filament\Resources\CustomTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\ListTenants;

class ListCustomTenants extends ListTenants
{
    protected static string $resource = CustomTenantResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
