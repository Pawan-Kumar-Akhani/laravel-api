<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
{
    // 🔐 check admin
    private function checkAdmin()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return false;
        }

        return true;
    }

    // 👥 TOTAL USERS
    public function totalUsers()
    {
        if (!$this->checkAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => true,
            'total_users' => User::count()
        ]);
    }

    // 📦 TOTAL ORDERS
    public function totalOrders()
    {
        if (!$this->checkAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => true,
            'total_orders' => Order::count()
        ]);
    }

    // 💰 TOTAL REVENUE
    public function totalRevenue()
    {
        if (!$this->checkAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $revenue = Order::where('status', '!=', 'cancelled')->sum('total_price');

        return response()->json([
            'status' => true,
            'total_revenue' => $revenue
        ]);
    }

    // 🔥 TOP PRODUCTS
    public function topProducts()
    {
        if (!$this->checkAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $topProducts = OrderItem::select('product_id')
            ->selectRaw('SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->take(5)
            ->get();

        return response()->json([
            'status' => true,
            'top_products' => $topProducts
        ]);
    }
}