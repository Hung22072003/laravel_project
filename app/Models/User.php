<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, HasUuids;
    use Notifiable;
    use SoftDeletes;

    const EMAIL = 'email';
    const FULL_NAME = 'full_name';
    const PASSWORD = 'password';
    const ROLE = 'role';

    protected $fillable = [
        self::EMAIL,
        self::FULL_NAME,
        self::PASSWORD,
        self::ROLE,
    ];
    
    protected $hidden = [
        self::PASSWORD
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
    
    public function isAdmin()
    {
        return $this->role === 'ADMIN';
    }

    public function isUser()
    {
        return $this->role === 'USER';
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'full_name' => $this->full_name,
            'role' => $this->role,
        ];
    }
}

