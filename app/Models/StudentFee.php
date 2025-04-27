<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFee extends Model
{
    protected $guarded = ['id'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classFee(): BelongsTo
    {
        return $this->belongsTo(ClassFee::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SchoolPayment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SchoolInvoice::class);
    }
}
