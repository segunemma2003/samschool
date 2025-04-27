<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeStructure extends Model
{
    protected $guarded = ['id'];


    public function feeItems(): HasMany
    {
        return $this->hasMany(FeeItem::class);
    }
}
