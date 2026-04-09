<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
    'uuid',
    'name',
    'email',
    'password',
    'phone',
    'address',
    'image',
    'role'   

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function getJWTIdentifier()
    {
    return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
    return [];
    }
    }