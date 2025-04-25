<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LibraryBookRequest extends Model
{
    protected $guarded = ['id'];
    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class);
    }

    public function requester(): MorphTo
    {
        return $this->morphTo();
    }

    public function approve(): void
    {
        $this->update(['status' => 'approved']);

        // You might want to automatically create a loan here
        LibraryBookLoan::create([
            'library_book_id' => $this->library_book_id,
            'borrower_type' => $this->requester_type,
            'borrower_id' => $this->requester_id,
            'loan_date' => now(),
            'due_date' => now()->addWeeks(2), // Adjust as needed
            'status' => 'borrowed',
        ]);

        $this->book->decrement('quantity');
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }
}
