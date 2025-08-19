<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychomotorCategory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function psychomotors()
    {
        return $this->hasMany(Psychomotor::class, 'psychomotor_category_id');
    }
}
