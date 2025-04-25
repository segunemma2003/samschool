<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OnlineLibrarySubject extends Model
{
    protected $guarded = ['id'];

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(OnlineLibraryMaterial::class, 'online_library_material_subject','subject_id', 'material_id')
            ->withTimestamps();
    }
}
