<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];
}
