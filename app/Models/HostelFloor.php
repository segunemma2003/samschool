<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelFloor extends Model
{
    protected $guarded = ['id'];

    public function building()
    {
        return $this->belongsTo(HostelBuilding::class);
    }

    public function rooms()
    {
        return $this->hasMany(HostelRoom::class);
    }
}
