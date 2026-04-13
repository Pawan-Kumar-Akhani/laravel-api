<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CartController extends Controller
{
    // 🔹 Get authenticated user (clean reusable method)
    private function user()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    // 🔹 ADD TO CART
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = $this->user();

        $cart = Cart::where('user_id', $user->id)
                    ->where('product_id', $request->product_id)
                    ->first();

        if ($cart) {
            $cart->increment('quantity');
        } else {
            $cart = Cart::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'quantity' => 1
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart',
            'cart' => $cart
        ]);
    }

    // 🔹 GET USER CART
    public function getCart()
    {
        $user = $this->user();

        $cart = Cart::with('product')
                    ->where('user_id', $user->id)
                    ->latest()
                    ->get();

        return response()->json([
            'status' => true,
            'cart' => $cart
        ]);
    }

    // 🔹 UPDATE QUANTITY
    public function updateQuantity(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $user = $this->user();

        $cart = Cart::where('user_id', $user->id)
                    ->where('id', $id)
                    ->firstOrFail();

        $cart->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Quantity updated',
            'cart' => $cart
        ]);
    }

    // 🔹 REMOVE SINGLE ITEM
    public function removeItem($id)
    {
        $user = $this->user();

        $cart = Cart::where('user_id', $user->id)
                    ->where('id', $id)
                    ->firstOrFail();

        $cart->delete();

        return response()->json([
            'status' => true,
            'message' => 'Item removed from cart'
        ]);
    }

    // 🔹 CLEAR FULL CART
    public function clearCart()
    {
        $user = $this->user();

        Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
}