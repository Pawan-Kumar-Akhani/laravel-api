<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Category;
use App\Models\Cart;
use App\Models\OrderItem;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'discount_price',
        'stock',
        'image',
        'category_id'
    ];

    // 🔹 Product belongs to admin/user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 Product belongs to category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // 🔹 Product has many cart entries
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // 🔹 Product has many order items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}