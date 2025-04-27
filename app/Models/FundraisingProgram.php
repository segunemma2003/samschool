<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundraisingProgram extends Model
{
    protected $guarded = ['id'];

    public function contributions(): HasMany
    {
        return $this->hasMany(ProgramContribution::class);
    }
}
