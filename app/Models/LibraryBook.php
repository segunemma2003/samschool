<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBook extends Model
{
    protected $guarded = ['id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryBookCategories::class, 'library_category_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(LibraryBookLoan::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->loans()->where('status', 'borrowed');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(LibraryBookRequest::class);
    }

    public function getAvailableCopiesAttribute(): int
    {
        return $this->quantity - $this->activeLoans()->count();
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(LibraryShelf::class);
    }

    public function getFullLocationAttribute(): ?string
    {
        if (!$this->shelf) return null;

        return "{$this->shelf->location->name}, Shelf {$this->shelf->name}, " .
               "Row {$this->row_number}, Position {$this->position_number}";
    }

    public function getLocationCodeAttribute(): ?string
    {
        if (!$this->shelf) return null;

        return "{$this->shelf->location->code}-{$this->shelf->code}-" .
               "{$this->row_number}-{$this->position_number}";
    }
}
