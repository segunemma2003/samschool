<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SchoolPayment extends Model
{
    protected $guarded = ['id'];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SchoolInvoice::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
