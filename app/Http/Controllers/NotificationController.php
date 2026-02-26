<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        return view('content.pages.notifications.index', compact('notifications'));
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json([
            'success' => true,
            'read_at' => $notification->read_at->format('d/m/Y H:i')
        ]);
    }

    public function unreadCount()
    {
        $unread = Auth::user()->unreadNotifications;
        return response()->json([
            'count' => $unread->count(),
            'latest' => $unread->first()
        ]);
    }
}
