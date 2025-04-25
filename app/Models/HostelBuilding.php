<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelBuilding extends Model
{
    protected $guarded = ['id'];
    public function floors()
    {
        return $this->hasMany(HostelFloor::class);
    }
}
