<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LibraryBookLoan extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['loan_date', 'due_date', 'return_date'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'library_book_id');
    }

    // Polymorphic relationship with borrower (student/teacher)
    public function borrower(): MorphTo
    {
        return $this->morphTo();
    }

    // Scope for borrowed books
    public function scopeBorrowed($query)
    {
        return $query->where('status', 'borrowed');
    }

    // Scope for overdue books
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'returned');
    }

    // Method to mark as returned
    public function markAsReturned(): void
    {
        $this->update([
            'return_date' => now(),
            'status' => 'returned',
        ]);

        $this->book->increment('quantity');
    }
}
