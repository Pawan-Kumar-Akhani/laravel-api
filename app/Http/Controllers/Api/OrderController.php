<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    // 🔹 Get authenticated user (clean reusable method)
    private function user()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    // 🔹 PLACE ORDER (Checkout)
    public function placeOrder(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'address' => 'required'
        ]);

        $user = $this->user();
        $userId = $user->id;

        $cartItems = Cart::with('product')
                        ->where('user_id', $userId)
                        ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Cart is empty'
            ]);
        }

        DB::beginTransaction();

        try {
            $total = 0;

            // 🔹 Calculate total price
            foreach ($cartItems as $item) {

                if (!$item->product) {
                    throw new \Exception('Product not found');
                }

                $price = $item->product->discount_price ?? $item->product->price;
                $total += $price * $item->quantity;
            }

            // 🔹 Create Order
            $order = Order::create([
                'user_id' => $userId,
                'total_price' => $total,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => 'pending'
            ]);

            // 🔹 Create Order Items + reduce stock
            foreach ($cartItems as $item) {

                $price = $item->product->discount_price ?? $item->product->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $price
                ]);

                // 🔥 Reduce stock (VERY IMPORTANT)
                $item->product->decrement('stock', $item->quantity);
            }

            // 🔹 Clear Cart
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Order failed',
                'error' => $e->getMessage()
            ]);
        }
    }

    // 🔹 MY ORDERS (User)
    public function myOrders()
    {
        $user = $this->user();

        $orders = Order::with('items.product')
                    ->where('user_id', $user->id)
                    ->latest()
                    ->get();

        return response()->json([
            'status' => true,
            'orders' => $orders
        ]);
    }

    // 🔹 ORDER DETAILS (User safe)
    public function orderDetails($id)
    {
        $user = $this->user();

        $order = Order::with('items.product')
                    ->where('user_id', $user->id)
                    ->where('id', $id)
                    ->firstOrFail();

        return response()->json([
            'status' => true,
            'order' => $order
        ]);
    }

    // 🔹 ADMIN: UPDATE ORDER STATUS
    public function updateStatus(Request $request, $id)
    {
        $user = $this->user();

        // 🔥 ADMIN CHECK
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);

        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order status updated',
            'order' => $order
        ]);
    }
}