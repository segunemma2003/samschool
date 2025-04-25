<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelMeal extends Model
{
    protected $guarded = ['id'];

    public function attendances()
    {
        return $this->hasMany(HostelMealAttendance::class);
    }
}
