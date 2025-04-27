<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramContribution extends Model
{
    protected $guarded = ['id'];

    public function fundraisingProgram(): BelongsTo
    {
        return $this->belongsTo(FundraisingProgram::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Guardians::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SchoolPayment::class);
    }
}
