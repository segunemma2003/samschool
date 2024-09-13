<?php

namespace App\Filament\Resources\CustomTenantResource\Pages;

use App\Filament\Resources\CustomTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages\CreateTenant;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TomatoPHP\FilamentTenancy\Models\Tenant;
use Throwable;
use function Filament\Support\is_app_url;

class CreateCustomTenant extends CreateTenant
{
    protected static string $resource = CustomTenantResource::class;


     /**
     * @throws Throwable
     */

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        $this->callHook('beforeValidate');

        $data = $this->form->getState();

        $this->callHook('afterValidate');

        $data = $this->mutateFormDataBeforeCreate($data);

        $this->callHook('beforeCreate');

        $this->record = $this->handleRecordCreation($data);

        $this->form->model($this->getRecord())->saveRelationships();

        $this->callHook('afterCreate');

        $this->rememberData();

        $this->getCreatedNotification()?->send();



        $redirectUrl = $this->getRedirectUrl();

        $record = $this->record;

        config(['database.connections.dynamic.database' => config('tenancy.database.prefix').$record->id. config('tenancy.database.suffix')]);
        $user = DB::connection('dynamic')
            ->table('users')
            ->where('email', $record->email)
            ->first();
        if($user){
            DB::connection('dynamic')
                ->table('users')
                ->where('email', $record->email)
                ->update([
                    "name" => $record->name,
                    "email" => $record->email,
                    "password" => $record->password,
                ]);
        }
        else {
            DB::connection('dynamic')
                ->table('users')
                ->insert([
                    "name" => $record->name,
                    "email" => $record->email,
                    "password" => $record->password,
                ]);
        }

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }





}
