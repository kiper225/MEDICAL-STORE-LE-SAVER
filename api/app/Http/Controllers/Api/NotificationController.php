<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'notifications' => $request->user()->notifications()->paginate(10),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(null, 204);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(null, 204);
    }
}