<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
{
    // 🔹 Get authenticated user (clean reusable method)
    private function user()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    // 🔹 CREATE PRODUCT (Admin only)
    public function store(Request $request)
    {
        $user = $this->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = 'uploads/products/' . $filename;
        }

        $product = Product::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock' => $request->stock,
            'image' => $imagePath,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'product' => $product
        ]);
    }

    // 🔹 GET ALL PRODUCTS (Public)
    public function index(Request $request)
    {
    $query = Product::with('category')->latest();

    // 🔍 SEARCH by name
    if ($request->has('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    // 📂 FILTER by category
    if ($request->has('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    // 💰 PRICE FILTER (min - max)
    if ($request->has('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }

    if ($request->has('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    // 📄 PAGINATION
    $perPage = $request->get('per_page', 10);

    $products = $query->paginate($perPage);

    return response()->json([
        'status' => true,
        'message' => 'Products fetched successfully',
        'data' => $products
    ]);
    }

    // 🔹 GET SINGLE PRODUCT (Public)
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json([
            'status' => true,
            'product' => $product
        ]);
    }

    // 🔹 UPDATE PRODUCT (Admin only)
    public function update(Request $request, $id)
    {
        $user = $this->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $product = Product::findOrFail($id);

        $imagePath = $product->image;

        if ($request->hasFile('image')) {

            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }

            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);

            $imagePath = 'uploads/products/' . $filename;
        }

        $product->update([
            'name' => $request->name ?? $product->name,
            'description' => $request->description ?? $product->description,
            'price' => $request->price ?? $product->price,
            'discount_price' => $request->discount_price ?? $product->discount_price,
            'stock' => $request->stock ?? $product->stock,
            'image' => $imagePath,
            'category_id' => $request->category_id ?? $product->category_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }

    // 🔹 DELETE PRODUCT (Admin only)
    public function destroy($id)
    {
        $user = $this->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $product = Product::findOrFail($id);

        if ($product->image && file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}