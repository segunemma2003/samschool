<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryShelf extends Model
{
    protected $guarded  = ['id'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(LibraryLocation::class, 'location_id');
    }

    public function books(): HasMany
    {
        return $this->hasMany(LibraryBook::class, 'shelf_id');
    }

    public function getCapacityAttribute(): int
    {
        return $this->row_count * $this->position_count;
    }

    public function getAvailablePositions(): array
    {
        $occupied = $this->books()
            ->select('row_number', 'position_number')
            ->get()
            ->map(fn($book) => "{$book->row_number}-{$book->position_number}")
            ->toArray();

        $allPositions = [];
        for ($row = 1; $row <= $this->row_count; $row++) {
            for ($pos = 1; $pos <= $this->position_count; $pos++) {
                $key = "{$row}-{$pos}";
                if (!in_array($key, $occupied)) {
                    $allPositions[$key] = "Row {$row}, Position {$pos}";
                }
            }
        }

        return $allPositions;
    }
}
