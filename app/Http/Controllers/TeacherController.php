<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherProfileUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class TeacherController extends Controller
{
    // ─────────────────────────────────────────
    // GET /api/teacher/profile
    // ─────────────────────────────────────────
    public function profile()
    {
        try {
            $teacher = JWTAuth::parseToken()->authenticate();

            if (!$teacher) {
                return response()->json(['message' => 'Teacher not found'], 404);
            }

            return response()->json([
                'id'            => $teacher->id,
                'name'          => $teacher->name,
                'user_name'     => $teacher->user_name,
                'email'         => $teacher->email,
                'mobile_number' => $teacher->mobile_number,
                'role'          => $teacher->role_status == 1 ? 'Class Teacher' : 'Subject Teacher',
                'access_status' => $teacher->access_status,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized', 'error' => $e->getMessage()], 401);
        }
    }

    // ─────────────────────────────────────────
    // PUT /api/teacher/profile/update
    // ─────────────────────────────────────────
    public function updateProfile(TeacherProfileUpdateRequest $request)
    {
        try {
            $teacher = JWTAuth::parseToken()->authenticate();

            if (!$teacher) {
                return response()->json(['message' => 'Teacher not found'], 404);
            }

            $data = [];

            // Update email if provided
            if ($request->filled('email')) {
                $data['email'] = $request->email;
            }

            // Update mobile number if provided
            if ($request->filled('mobile_number')) {
                $data['mobile_number'] = $request->mobile_number;
            }

            // Update password if provided
            if ($request->filled('new_password')) {
                // Verify current password first
                if (!Hash::check($request->current_password, $teacher->password)) {
                    return response()->json([
                        'message' => 'Current password is incorrect.',
                    ], 422);
                }

                $data['password'] = Hash::make($request->new_password);
            }

            // Nothing was sent to update
            if (empty($data)) {
                return response()->json([
                    'message' => 'No fields provided to update.',
                ], 422);
            }

            $teacher->update($data);

            return response()->json([
                'message'       => 'Profile updated successfully.',
                'name'          => $teacher->name,
                'email'         => $teacher->email,
                'mobile_number' => $teacher->mobile_number,
                'role'          => $teacher->role_status == 1 ? 'Class Teacher' : 'Subject Teacher',
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized', 'error' => $e->getMessage()], 401);
        }
    }
}