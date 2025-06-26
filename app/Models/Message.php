<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'read_at' => 'datetime',
    ];


    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment) return null;

        return cache()->remember(
            "message_attachment_{$this->id}",
            3600, // 1 hour
            fn() => Storage::disk('s3')->url($this->attachment)
        );
    }

    public function getIsImageAttribute()
    {
        if (!$this->attachment) return false;

        return Str::contains($this->attachment, ['.jpg', '.jpeg', '.png', '.gif']);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class)
            ->select(['id', 'title', 'last_message_at']);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')
            ->select(['id', 'name', 'avatar', 'user_type']);
    }

public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeInConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId)
                    ->orderBy('created_at', 'desc');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true]);
        }
    }
}
