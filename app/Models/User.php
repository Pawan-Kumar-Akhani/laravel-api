<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 🔹 Auto UUID generation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (!$user->uuid) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    // 🔹 JWT Required Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // 🔹 RELATIONSHIPS

    // user has many products (admin side)
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // user has many cart items
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // user has many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}