<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserCareer;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'clerk_id',
        'firebase_uid',
        'clerkfire_id',
        'first_name',
        'last_name',
        'user_since',
        'is_premium',
        'premium_expires_at',
    ];

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_premium' => 'boolean', 
        'premium_expires_at' => 'datetime',
    ];

    public function userCareer()
    {
        return $this->hasOne(UserCareer::class, 'user_id', 'firebase_uid');
    }

    // Método para verificar se o usuário é admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
