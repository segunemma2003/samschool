<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBookCategories extends Model
{
    protected $guarded = ['id'];
    public function books(): HasMany
    {
        return $this->hasMany(LibraryBook::class, 'library_category_id');
    }
}
