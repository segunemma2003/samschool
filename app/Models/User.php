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
// use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $guarded = ['id'];

    public function canAccessPanel(Panel $panel): bool
    {
         return cache()->remember(
            "user_panel_access_{$this->id}_{$panel->getId()}",
            600, // 10 minutes
            function () use ($panel) {
        if ($panel->getId() === 'admin') {
            return $this->email == "myadmin@admin.com";
        }else if($panel->getId() === 'app'){
            return  is_null($this->user_type) || $this->user_type=="admin" ||  $this->email == "admin@admin.com" ||  $this->email == "myadmin@admin.com";
        }else if($panel->getId() === 'parent'){
            return $this->user_type=="parent";
        }else if($panel->getId() === 'student'){
            return $this->user_type=="student";
        }else if($panel->getId() === 'teacher'){
            return $this->user_type=="teacher";
        }else if($panel->getId()=="finance"){
            if($this->user_type =="admin"){
                return true;
            }
        }
        return true;
        }
        );

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

    public function isAdmin()
    {
        return $this->user_type == 'admin';
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
