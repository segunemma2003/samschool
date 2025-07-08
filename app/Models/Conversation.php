<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['other_user'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];


    public function otherUser()
    {
        return $this->belongsToMany(User::class)
            ->where('users.id', '!=', Auth::id());
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('last_read_at');
    }

    public function getUnreadMessagesCountForUser($userId)
    {
        $lastReadAt = $this->users()->where('user_id', $userId)->first()->pivot->last_read_at;

        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where(function ($query) use ($lastReadAt) {
                if ($lastReadAt) {
                    $query->where('created_at', '>', $lastReadAt);
                }
            })
            ->count();
    }

    public function getOtherUserAttribute()
    {
        return cache()->remember(
            "conversation_other_user_{$this->id}_" . Auth::id(),
            600,
            fn() => $this->users->where('id', '!=', Auth::id())->first()
        );
    }

    // Add this method to update last_message_at
    public function touchLastMessage()
    {
        $this->update(['last_message_at' => now()]);
    }
}
