<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\Category;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CategoryController extends Controller
{
    // 🔹 Get authenticated user (for admin check)
    private function user()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    // 🔹 GET ALL CATEGORIES (Public)
    public function index()
    {
        return response()->json([
            'status' => true,
            'categories' => Category::all()
        ]);
    }

    // 🔹 CREATE CATEGORY (ADMIN ONLY)
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
            'name' => 'required|string'
        ]);

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Category created',
            'category' => $category
        ]);
    }

    // 🔹 GET SINGLE CATEGORY
    public function show($id)
    {
        return response()->json([
            'status' => true,
            'category' => Category::findOrFail($id)
        ]);
    }

    // 🔹 UPDATE CATEGORY (ADMIN ONLY)
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
            'name' => 'required|string'
        ]);

        $category = Category::findOrFail($id);

        $category->update([
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Category updated',
            'category' => $category
        ]);
    }

    // 🔹 DELETE CATEGORY (ADMIN ONLY)
    public function destroy($id)
    {
        $user = $this->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}