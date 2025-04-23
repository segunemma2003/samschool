<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Teacher extends Model
{
    use HasFactory, HasRoles;
    protected $guarded = ['id'];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'teacher_id');
    }

    public function arm()
    {
        return $this->belongsTo(ArmsTeacher::class, 'id','teacher_id');
    }

    public function subject()
    {
        return $this->hasMany(Subject::class, 'teacher_id');
    }
}
