<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Sevenspan\LaravelChat\Traits\HasConversations;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;

// use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $guarded = ['id'];

    public function canAccessPanel(Panel $panel): bool
{
    // Use static cache to avoid repeated DB calls
    static $accessCache = [];

    $key = "{$this->id}_{$panel->getId()}";

    if (!isset($accessCache[$key])) {
        $accessCache[$key] = $this->calculatePanelAccess($panel);
    }

    return $accessCache[$key];
}

private function calculatePanelAccess(Panel $panel): bool
{
    $panelId = $panel->getId();
    $userType = $this->user_type;

    return match($panelId) {
        'admin' => $this->email === "myadmin@admin.com",
        'app' => in_array($userType, [null, 'admin']) ||
                $this->email === "admin@admin.com",
        'parent' => $userType === "parent",
        'student' => $userType === "student",
        'teacher' => $userType === "teacher",
        'finance' => $userType === "admin",
        default => true
    };
}

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($user) {
            // Clear only relevant caches
            Cache::forget("user_panel_access_{$user->id}_admin");
            Cache::forget("user_panel_access_{$user->id}_app");
            Cache::forget("user_admin_check_{$user->id}");
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active_status' => 'boolean',
        'dark_mode' => 'boolean',
        ];
    }

    public function scopeWithProfile($query)
{
    return $query->with([
        'teacher:id,name,email,designation',
        'student:id,name,email,class_id,registration_number'
    ]);
}


     protected $with = [];

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'email', 'email')
            ->select(['id', 'name', 'email', 'designation', 'avatar', 'signature']);
    }

      public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'email', 'email')
            ->select(['id', 'name', 'email', 'class_id', 'registration_number', 'avatar']);
    }

    // PERFORMANCE: Add scopes for common queries
    public function scopeByType($query, string $type)
    {
        return $query->where('user_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('active_status', true);
    }



    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)
            ->withPivot('last_read_at')
            ->orderByDesc('last_message_at');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function isAdmin(): bool
    {
        return cache()->remember(
            "user_admin_check_{$this->id}",
            1800, // 30 minutes
            fn() => $this->user_type === 'admin'
        );
    }

    public function readingProgress(): HasMany
    {
        return $this->hasMany(OnlineLibraryReadingProgress::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(OnlineLibraryReview::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(OnlineLibraryFavorite::class);
    }
}
