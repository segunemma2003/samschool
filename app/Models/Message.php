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
    ];


    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment) return null;

        return Storage::disk('s3')->url($this->attachment);
    }

    public function getIsImageAttribute()
    {
        if (!$this->attachment) return false;

        return Str::contains($this->attachment, ['.jpg', '.jpeg', '.png', '.gif']);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
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
