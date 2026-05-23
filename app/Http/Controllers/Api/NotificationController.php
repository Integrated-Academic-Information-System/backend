<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller {

    public function index(Request $request) {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()->get();
        return response()->json($notifications);
    }

    public function markAsRead($id) {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);
        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllRead(Request $request) {
        Notification::where('user_id', $request->user()->id)
            ->update(['is_read' => true]);
        return response()->json(['message' => 'All notifications marked as read']);
    }
}