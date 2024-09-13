<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TomatoPHP\FilamentTenancy\Models\Tenant;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;


class CustomTenant extends Tenant
// implements HasMedia
{
    use HasDatabase, HasDomains;
    use HasPlanSubscriptions;
    // use InteractsWithMedia;
  protected $fillable = [
    'name',
    'email',
    'phone',
    'password',
    'otp_code',
    'otp_code_active_at',
    'is_active',
    'data',
    'address',
    'logo'
    ];


    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'password',
            'otp_code',
            'otp_code_active_at',
            'is_active',
            'data',
            'address',
            'logo'
        ];
    }


}
