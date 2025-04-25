<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelInventory extends Model
{
    protected $guarded = ['id'];

    public function room()
    {
        return $this->belongsTo(HostelRoom::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(HostelMaintenanceRequest::class);
    }

    public function damageReports()
    {
        return $this->hasMany(HostelDamageReport::class);
    }
}
