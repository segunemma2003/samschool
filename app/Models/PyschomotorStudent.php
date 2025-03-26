<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PyschomotorStudent extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function psychomotor()
    {
        return $this->belongsTo(Psychomotor::class, 'psychomotor_id');
    }


}
