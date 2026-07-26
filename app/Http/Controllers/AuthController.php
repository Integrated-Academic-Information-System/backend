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
        // 1. Validate incoming request
        $request->validate([
            'user_name' => 'required|string',
            'password'  => 'required|string',
        ]);

        $input_username = $request->user_name; // Keep original input for database query
        $lower_username = strtolower($input_username); // Lowercase for pattern matching

        $user = null;
        $outGoingUser = '';
        $teacher_status = 100;

        if (str_contains($lower_username, 'admin')) {
            $user = Admin::where('user_name', $input_username)->first();
        } elseif (str_contains($lower_username, 'reg')) {
            $user = Student::where('reg_no', $request->user_name)->first();
        } else if (str_contains($lower_username, 'teacher')) {
            $user = Teacher::where('user_name', $request->user_name)->first();
            if ($user->role_status === 0) {
                $teacher_status = 0;
            } else if ($user->role_status === 1) {
                $teacher_status = 1;
            }
        } else {
            // Default check for Teachers (Handles 'ct_' and any other teacher patterns)
            $user = Teacher::where('user_name', $input_username)->first();
        }

        // 3. Verify user existence and password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid username or password'
            ], 401);
        }

        // 4. Set response details based on user type
        if ($user instanceof Admin) {
            $outGoingUser = $user->user_name;
        } else if ($user instanceof Student) {
            $outGoingUser = $user->reg_no;
        } else if ($user instanceof Teacher) {
            $outGoingUser = $user->user_name;
            $teacher_status = $user->role_status;
        }

        // 5. Generate JWT token
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }

        // 6. Prepare and send response
        $responseData = [
            'token'          => $token,
            'token_type'     => 'bearer',
            'expires_in'     => config('jwt.ttl') * 60 * 6,
            'user_name'      => $outGoingUser,
            'teacher_status' => $teacher_status, // 0 = Subject, 1 = Class Incharge
        ];

        // Include teacher_id for teachers to facilitate filtering
        if ($user instanceof Teacher) {
            $responseData['teacher_id'] = $user->id;
            $responseData['name']           = $user->name;
            $responseData['email']          = $user->email;
            $responseData['mobile_number']  = $user->mobile_number;
        }

        return response()->json($responseData);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            return response()->json(['message' => 'Failed to logout'], 500);
        }

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Validate the stored token and return the current user's identity.
     * Called by the app on startup to restore the session without re-login.
     */
    public function me(Request $request)
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 401);
            }

            // Try each guard in order — the token payload encodes which model was used
            foreach (['admin', 'student', 'teacher'] as $guard) {
                try {
                    $user = auth($guard)->setToken($token)->authenticate();
                    if ($user) {
                        $role = $guard; // 'admin' | 'student' | 'teacher'

                        $responseData = [
                            'success'      => true,
                            'role'         => $role,
                            'user_name'    => $user instanceof Admin
                                ? $user->user_name
                                : ($user instanceof Student ? $user->reg_no : $user->user_name),
                            'teacher_status' => $user instanceof Teacher ? $user->role_status : null,
                        ];

                        if ($user instanceof Teacher) {
                            $responseData['teacher_id']     = $user->id;
                            $responseData['name']           = $user->name;
                            $responseData['email']          = $user->email;
                            $responseData['mobile_number']  = $user->mobile_number;
                        }

                        return response()->json($responseData);
                    }
                } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                    return response()->json(['message' => 'Token expired'], 401);
                } catch (\Throwable $e) {
                    // This guard did not match — try the next one
                    continue;
                }
            }

            return response()->json(['message' => 'Unauthenticated'], 401);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
    }
}
