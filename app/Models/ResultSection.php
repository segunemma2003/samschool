<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultSection extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function group()
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }
}
