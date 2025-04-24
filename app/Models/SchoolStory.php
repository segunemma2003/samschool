<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolStory extends Model
{
    protected $guarded = ['id'];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'commentable_id')
        ->where('commentable_type', self::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
