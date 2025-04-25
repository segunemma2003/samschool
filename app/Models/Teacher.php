<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function bookLoans(): HasMany
    {
        return $this->hasMany(LibraryBookLoan::class, 'borrower_id')
            ->where('borrower_type', self::class);
    }

    public function currentLoans(): HasMany
    {
        return $this->bookLoans()->where('status', 'borrowed');
    }

    public function bookRequests(): HasMany
    {
        return $this->hasMany(LibraryBookRequest::class, 'requester_id')
            ->where('requester_type', self::class);
    }
}
