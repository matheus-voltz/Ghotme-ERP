<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiNotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Pega as notificações. Para o mobile vamos retornar os dados formatados
        $notifications = $user->notifications()->latest()->get()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'data' => $notification->data,
                'is_read' => $notification->read_at !== null,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'unread_count' => $user->unreadNotifications->count(),
            'notifications' => $notifications
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();

        if ($id === 'all') {
            $user->unreadNotifications->markAsRead();
        } else {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }

        return response()->json(['success' => true]);
    }

    public function preferences()
    {
        $user = Auth::user();

        // Deafult settings if null
        $prefs = $user->notification_preferences ?? [
            'new_os' => true,
            'chat_messages' => true,
            'system_alerts' => true,
            'budget_updates' => true,
        ];

        return response()->json($prefs);
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $prefs = $user->notification_preferences ?? [];
        $key = $request->input('key');
        $value = $request->input('value');

        if ($key !== null && $value !== null) {
            $prefs[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            $user->notification_preferences = $prefs;
            $user->save();
        }

        return response()->json(['success' => true, 'preferences' => $user->notification_preferences]);
    }
}
