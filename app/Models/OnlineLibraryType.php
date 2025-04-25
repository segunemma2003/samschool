<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineLibraryType extends Model
{
    protected $guarded = ['id'];

    public function materials(): HasMany
    {
        return $this->hasMany(OnlineLibraryMaterial::class, 'type_id');
    }
}
