<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Order;
use App\Models\Product;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price'
    ];

    // 🔹 belongs to order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // 🔹 belongs to product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}