<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineLibraryMaterial extends Model
{
    protected $guarded = ['id'];

    public function type()
    {
        return $this->belongsTo(OnlineLibraryType::class, 'type_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(OnlineLibrarySubject::class, 'online_library_material_subject', 'material_id', 'subject_id')
            ->withTimestamps();
    }

    public function readingProgress(): HasMany
    {
        return $this->hasMany(OnlineLibraryReadingProgress::class, 'material_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(OnlineLibraryReview::class, 'material_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(OnlineLibraryFavorite::class, 'material_id');
    }
}
