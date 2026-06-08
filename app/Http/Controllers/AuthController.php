<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string',
            'password'  => 'required|string',
        ]);

        $requested_user_name = $request->user_name;
        // Find user by username

        if (str_contains(strtolower($requested_user_name), strtolower("admin"))) {
            $user = Admin::where('user_name', $request->user_name)->first();
        }else{
            return response()->json([
                'message' => 'Can not login! Try again.'
            ], 401);
        }

        // Check if user exists and password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid username or password'
            ], 401);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token'     => $token,
            'user_name' => $user->user_name,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
        return response()->json(['message' => 'Logged out']);
    }
}
