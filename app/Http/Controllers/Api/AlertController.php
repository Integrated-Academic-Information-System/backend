<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller {

    public function index(Request $request) {
        $role = $request->user()->role;
        $alerts = Alert::with('creator')
            ->where(fn($q) => $q->where('role_target', 'all')->orWhere('role_target', $role))
            ->latest()->get();

        return response()->json($alerts);
    }

    public function store(Request $request) {
        $request->validate([
            'title'       => 'required|string',
            'message'     => 'required|string',
            'role_target' => 'required|in:all,admin,student,subject_teacher,class_incharge',
        ]);

        $alert = Alert::create([
            'title'       => $request->title,
            'message'     => $request->message,
            'role_target' => $request->role_target,
            'created_by'  => $request->user()->id,
        ]);

        return response()->json(['message' => 'Alert created', 'alert' => $alert], 201);
    }
}