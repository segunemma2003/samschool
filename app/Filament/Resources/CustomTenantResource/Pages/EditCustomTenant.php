<?php

namespace App\Filament\Resources\CustomTenantResource\Pages;

use App\Filament\Resources\CustomTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\EditTenant;

class EditCustomTenant extends EditTenant
{
    protected static string $resource = CustomTenantResource::class;



}
