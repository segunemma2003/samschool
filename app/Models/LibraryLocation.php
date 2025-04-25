<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class LibraryLocation extends Model
{
    protected $guarded = ['id'];

    public function shelves(): HasMany
    {
        return $this->hasMany(LibraryShelf::class, 'location_id');
    }

    public function books(): HasManyThrough
    {
        return $this->hasManyThrough(
            LibraryBook::class,
            LibraryShelf::class,
            'location_id',
            'shelf_id'
        );
    }
}
