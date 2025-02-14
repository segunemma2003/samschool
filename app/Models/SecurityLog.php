<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'details' => 'array',
    ];

    public static function logIncident($type, $details, $severity = 'medium'): void
    {
        self::create([
            'incident_type' => $type,
            'details' => $details,
            'severity_level' => $severity,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
