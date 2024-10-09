<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Complain extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        // Generate a unique 8-character alphanumeric code when a new company is created
        static::creating(function ($company) {

            $company->user_id = Auth::id();
        });
    }


    public function complainer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'to_id');
    }

}
