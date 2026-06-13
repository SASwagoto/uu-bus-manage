<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'student_id',
        'phone'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->username)) {
                $user->username = strstr($user->email, '@', true) ?: 'user_' . uniqid();
            }
            if (\App\Models\User::count() === 0) {
                $user->role = 'admin';
            }
        });
    }

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
        ];
    }
    public function canAccessPanel(Panel $panel): bool
    {
        // লাইভ সার্ভারে আপাতত সব রেজিস্টার্ড ইউজারকে ড্যাশবোর্ডে অ্যাক্সেস দেওয়ার জন্য true করে দেওয়া হলো
        return true; 
        
        // পরবর্তীতে আপনি চাইলে এখানে নির্দিষ্ট ইমেইল লক করতে পারেন, যেমন:
        // return str_ends_with($this->email, '@uubus.edu.bd');
    }
}
