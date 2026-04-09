<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    // REGISTER
   public function register(Request $request)
{
    $request->validate([
    'name' => 'required',
    'email' => 'required|email|unique:users',
    'password' => 'required|min:6|confirmed',
    'role' => 'nullable'
    ]);

    $user = User::create([
    'uuid' => Str::uuid(), // extra safety
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'role' => $request->role ?? 'customer', // default
    ]);
    $token = JWTAuth::fromUser($user);

    return response()->json([
        'status' => true,
        'user' => $user,
        'token' => $token
    ], 201);
}

    // LOGIN
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    return response()->json([
        'user' => JWTAuth::user(),
        'token' => $token
    ]);
    }

    // PROFILE
    public function profile()
    {
        return response()->json(auth()->user());
    }

    // LOGOUT
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email'
    ]);

    $token = Str::random(60);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]
    );

    // For now just return token (instead of email)
    return response()->json([
        'status' => true,
        'message' => 'Reset token generated',
        'token' => $token
    ]);
    }
    public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'token' => 'required',
        'password' => 'required|min:6|confirmed'
    ]);

    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid token'
        ]);
    }

    User::where('email', $request->email)->update([
        'password' => Hash::make($request->password)
    ]);

    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    return response()->json([
        'status' => true,
        'message' => 'Password reset successfully'
    ]);
    } 
    public function getProfile()
    {
    return response()->json([
        'status' => true,
        'user' => auth()->user()
    ]);
    }
 public function updateProfile(Request $request)
{
    $user = JWTAuth::parseToken()->authenticate();

    $request->validate([
        'name' => 'nullable|string',
        'phone' => 'nullable|string',
        'address' => 'nullable|string',
    ]);

    $user->update([
        'name' => $request->name ?? $user->name,
        'phone' => $request->phone ?? $user->phone,
        'address' => $request->address ?? $user->address,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Profile updated successfully',
        'user' => $user->fresh()
    ]);
}
public function updateProfileImage(Request $request)
{
    $user = JWTAuth::parseToken()->authenticate();

    $request->validate([
        'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    // delete old image
    if ($user->image && file_exists(public_path($user->image))) {
        unlink(public_path($user->image));
    }

    $file = $request->file('image');
    $filename = uniqid() . '.' . $file->getClientOriginalExtension();
    $file->move(public_path('uploads/users'), $filename);

    $imagePath = 'uploads/users/' . $filename;

    $user->update([
        'image' => $imagePath
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Profile image updated successfully',
        'image' => $imagePath,
        'user' => $user->fresh()
    ]);
}
    }
 