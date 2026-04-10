<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    // 🔹 CREATE PRODUCT (Admin)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
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
            'user_id' => Auth::id(),
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
            'message' => 'Product created',
            'product' => $product
        ]);
    }

    // 🔹 GET ALL PRODUCTS
    public function index()
    {
        $products = Product::with('category')->latest()->get();

        return response()->json($products);
    }

    // 🔹 GET SINGLE PRODUCT
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json($product);
    }

    // 🔹 UPDATE PRODUCT (Admin)
    public function update(Request $request, $id)
    {
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
            'message' => 'Product updated',
            'product' => $product
        ]);
    }

    // 🔹 DELETE PRODUCT (Admin)
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted'
        ]);
    }
}