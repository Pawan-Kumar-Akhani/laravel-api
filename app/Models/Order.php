<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\OrderItem;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'phone',
        'address'
    ];

    // 🔹 Order belongs to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 Order has many items
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}