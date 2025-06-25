<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class Announcement extends Model
{
    use HasFactory;

    // Keep your existing guarded approach but make it more specific for security
    protected $guarded = ['id'];

    // Add fillable for better security (optional - you can keep guarded)
    protected $fillable = [
        'title',
        'sub',
        'text',
        'file',
        'link',
        'from_id',
        'type_of_user_sent_to',
        'priority',
        'status',
        'expires_at',
        'views_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'views_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Default values for new columns (backward compatible)
    protected $attributes = [
        'type_of_user_sent_to' => 'teacher',
        'priority' => 'medium',
        'status' => 'published',
        'views_count' => 0,
    ];

    // OPTIMIZED: Your existing relationship but with performance boost
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_id')
            ->select(['id', 'name', 'email', 'avatar', 'user_type']); // Only select needed fields
    }

    // PERFORMANCE: Query Scopes for faster database queries
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function ($query) use ($userId) {
            $query->where('type_of_user_sent_to', 'teacher')
                  ->orWhere('from_id', $userId)
                  ->orWhere('type_of_user_sent_to', 'all');
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where(function ($query) {
            // Handle cases where status column might not exist yet
            if (Schema::hasColumn('announcements', 'status')) {
                $query->where('status', 'published');
            }

            // Handle cases where expires_at column might not exist yet
            if (Schema::hasColumn('announcements', 'expires_at')) {
                $query->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                });
            }
        });
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        // Only filter by priority if the column exists
        if (Schema::hasColumn('announcements', 'priority')) {
            return $query->where('priority', $priority);
        }
        return $query;
    }

    public function scopeWithAttachments(Builder $query): Builder
    {
        return $query->whereNotNull('file');
    }

    public function scopeWithLinks(Builder $query): Builder
    {
        return $query->whereNotNull('link');
    }

    // BACKWARD COMPATIBLE: Accessors that work with or without new columns
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file) {
            return null;
        }

        // Handle both old and new file storage methods
        if (filter_var($this->file, FILTER_VALIDATE_URL)) {
            return $this->file; // Already a full URL
        }

        return Storage::disk('s3')->url($this->file);
    }

    public function getIsExpiredAttribute(): bool
    {
        // Backward compatible - return false if expires_at doesn't exist
        if (!isset($this->attributes['expires_at']) || !$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function getIsRecentAttribute(): bool
    {
        return $this->created_at->isAfter(now()->subDays(7));
    }

    public function getPriorityAttribute($value): string
    {
        // Backward compatible - return 'medium' if priority column doesn't exist
        return $value ?? 'medium';
    }

    public function getStatusAttribute($value): string
    {
        // Backward compatible - return 'published' if status column doesn't exist
        return $value ?? 'published';
    }

    public function getViewsCountAttribute($value): int
    {
        // Backward compatible - return 0 if views_count column doesn't exist
        return $value ?? 0;
    }

    public function getPriorityColorAttribute(): string
    {
        $priority = $this->priority ?? 'medium';

        return match ($priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'medium' => 'primary',
            'low' => 'success',
            default => 'gray',
        };
    }

    public function getPriorityIconAttribute(): string
    {
        $priority = $this->priority ?? 'medium';

        return match ($priority) {
            'urgent' => 'heroicon-m-exclamation-triangle',
            'high' => 'heroicon-m-exclamation-circle',
            'medium' => 'heroicon-m-information-circle',
            'low' => 'heroicon-m-chat-bubble-bottom-center-text',
            default => 'heroicon-m-bell',
        };
    }

    public function getAudienceIconAttribute(): string
    {
        $audience = $this->type_of_user_sent_to ?? 'teacher';

        return match ($audience) {
            'all' => 'heroicon-m-globe-alt',
            'teacher' => 'heroicon-m-academic-cap',
            'student' => 'heroicon-m-user-group',
            'admin' => 'heroicon-m-shield-check',
            'parent' => 'heroicon-m-home',
            default => 'heroicon-m-users',
        };
    }

    public function getAudienceLabelAttribute(): string
    {
        $audience = $this->type_of_user_sent_to ?? 'teacher';

        return match ($audience) {
            'all' => 'Everyone',
            'teacher' => 'Teachers',
            'student' => 'Students',
            'admin' => 'Administrators',
            'parent' => 'Parents/Guardians',
            default => ucfirst($audience),
        };
    }

    public function getExcerptAttribute(): string
    {
        if (!empty($this->sub)) {
            return $this->sub;
        }

        if (!empty($this->text)) {
            return Str::limit(strip_tags($this->text), 100);
        }

        return 'No content available';
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    // UTILITY METHODS
    public function incrementViews(): void
    {
        // Only increment if views_count column exists
        if (Schema::hasColumn('announcements', 'views_count')) {
            $this->increment('views_count');
        }
    }

    public function isVisibleTo(\App\Models\User $user): bool
    {
        // Check status if column exists
        if (Schema::hasColumn('announcements', 'status')) {
            if ($this->status !== 'published') {
                return false;
            }
        }

        // Check expiration if column exists
        if ($this->is_expired) {
            return false;
        }

        // Own announcements are always visible
        if ($this->from_id === $user->id) {
            return true;
        }

        // Check audience targeting
        $userType = $user->user_type ?? 'teacher'; // Backward compatible
        $targetAudience = $this->type_of_user_sent_to ?? 'teacher';

        return in_array($targetAudience, ['all', $userType]);
    }

    public function canBeEditedBy(\App\Models\User $user): bool
    {
        // Only owner or admin can edit
        if ($this->from_id === $user->id) {
            return true;
        }

        // Check if user has admin role (backward compatible)
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin');
        }

        // Fallback: check user_type
        return ($user->user_type ?? '') === 'admin';
    }

    // SEARCH FUNCTIONALITY (BACKWARD COMPATIBLE)
    public static function search(string $query): Builder
    {
        return static::query()
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('title', 'LIKE', "%{$query}%");

                // Only search in sub and text if they exist
                if (Schema::hasColumn('announcements', 'sub')) {
                    $queryBuilder->orWhere('sub', 'LIKE', "%{$query}%");
                }

                if (Schema::hasColumn('announcements', 'text')) {
                    $queryBuilder->orWhere('text', 'LIKE', "%{$query}%");
                }
            });
    }

    // STATISTICS (BACKWARD COMPATIBLE)
    public static function getStatsForUser(int $userId): array
    {
        $baseQuery = static::forUser($userId);

        $stats = [
            'total' => $baseQuery->count(),
            'recent' => $baseQuery->recent()->count(),
            'my_announcements' => static::where('from_id', $userId)->count(),
        ];

        // Only get urgent count if priority column exists
        if (Schema::hasColumn('announcements', 'priority')) {
            $stats['urgent'] = $baseQuery->where('priority', 'urgent')->count();
        } else {
            $stats['urgent'] = 0;
        }

        return $stats;
    }

    // MODEL EVENTS (BACKWARD COMPATIBLE)
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($announcement) {
            if (!$announcement->from_id) {
                $announcement->from_id = Auth::id();
            }
        });

        static::deleting(function ($announcement) {
            // Clean up file when announcement is deleted
            if ($announcement->file && Storage::disk('s3')->exists($announcement->file)) {
                Storage::disk('s3')->delete($announcement->file);
            }
        });
    }
}
