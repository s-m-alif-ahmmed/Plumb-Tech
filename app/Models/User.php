<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser,HasAvatar
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'service',
        'about',
        'avatar',
        'address',
        'email_verified_at',
        'role',
        'is_otp_verified',
        'remember_token',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'email_verified_at',
        'deleted_at',
        'created_at',
        'updated_at',
        'status',
        'remember_token',
        'is_otp_verified',
        'pivot'
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
            'is_otp_verified' => 'boolean'
        ];
    }

    protected $appends = ['name'];

    public function getAvatarAttribute($value): string | null
    {

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        if (request()->is('api/*') && !empty($value)) {
            if (Str::contains($value, 'public/')) {
                $value = str_replace('public/', '', $value);
            }
            return url($value);
        }
        return $value;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->role === 'customer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function skills(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'skill_user');
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(ProfileImage::class);
    }

    public function getNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }



    //reviews
    public function reviews(): User|HasMany
    {
        // reviews given by this user
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedReviews(): User|HasMany
    {
        // reviews received by this user
        return $this->hasMany(Review::class, 'user_id');
    }

    // User sent discussion requests
    public function sentRequests(): User|HasMany
    {
        return $this->hasMany(DiscussionRequest::class, 'user_id');
    }

    // Engineer received discussion requests
    public function receivedRequests(): User|HasMany
    {
        return $this->hasMany(DiscussionRequest::class, 'engineer_id');
    }

    // **Check if User is Engineer**
    public function isEngineer(): bool
    {
        return $this->role === 'engineer';
    }


    public function firebaseTokens(): User|HasMany
    {
        return $this->hasMany(FirebaseTokens::class);
    }


    // An engineer can have many services
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'engineer_id');
    }

    // An engineer can receive many payment
    // User sent discussion requests
    public function discussionRequests(): User|HasMany
    {
        return $this->hasMany(DiscussionRequest::class);
    }

    // Engineer as engineer_id relation
    public function engineerRequests(): User|HasMany
    {
        return $this->hasMany(DiscussionRequest::class, 'engineer_id');
    }

    // RequestAcceptDenies relation (as Engineer)
    public function requestAcceptDenies(): User|HasMany
    {
        return $this->hasMany(RequestAcceptDenied::class, 'engineer_id');
    }

    public function wallet(): \Illuminate\Database\Eloquent\Relations\HasOne|User
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }


    // User can have many bank details
    public function bankDetails(): User|HasMany
    {
        return $this->hasMany(BankDetails::class);
    }

    // A user can have multiple withdrawal requests
    public function withdrawalRequests(): User|HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }

}
