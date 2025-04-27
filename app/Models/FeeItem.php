<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeItem extends Model
{
    protected $guarded = ['id'];

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }
}
