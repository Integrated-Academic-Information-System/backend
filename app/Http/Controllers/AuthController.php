<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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

        // if (str_contains(strtolower($requested_user_name), strtolower("admin"))) {
        //     $user = Admin::where('user_name', $request->user_name)->first();
        // }else{
        //     return response()->json([
        //         'message' => 'Can not login! Try again.'
        //     ], 401);
        // }

        $teacher_status = 100;

        if (str_contains(strtolower($requested_user_name), 'admin')) {
            $user = Admin::where('user_name', $request->user_name)->first();
            $outGoingUser = $user->user_name;
        } else if (str_contains(strtolower($requested_user_name), 'reg')) {
            $user = Student::where('reg_no', $request->user_name)->first();
            $outGoingUser = $user->reg_no;
        } else if (str_contains(strtolower($requested_user_name), 'teacher')) {
            $user = Teacher::where('user_name', $request->user_name)->first();
            $outGoingUser = $user->user_name;
            if ($user->role_status === 0) {
                $teacher_status = 0;
            } else if($user->role_status === 1) {
               $teacher_status = 1;
            }
        } else {
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

        // Generate JWT token directly from the user object
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Could not create token'
            ], 500);
        }

        return response()->json([
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60 * 6, // seconds
            'user_name'  => $outGoingUser,
            'teacher_status' => $teacher_status,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            return response()->json(['message' => 'Failed to logout'], 500);
        }

        return response()->json(['message' => 'Logged out']);
    }

}
