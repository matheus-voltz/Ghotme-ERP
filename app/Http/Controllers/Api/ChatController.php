<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    /**
     * Get list of contacts (team members) with unread counts
     */
    public function contacts()
    {
        $user = Auth::user();

        // Get team members (same company) + Support Ghotme (Super Admins/NULL company)
        $contacts = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($user) {
                $q->where('company_id', $user->company_id)
                  ->orWhereNull('company_id')
                  ->orWhere('role', 'super_admin')
                  ->orWhere('role', 'admin');
            })
            ->get()
            ->map(function ($contact) use ($user) {
                $contact->unread_count = ChatMessage::where('sender_id', $contact->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                
                return $contact;
            });

        return response()->json($contacts);
    }

    /**
     * Get total unread count for the authenticated user
     */
    public function unreadCount()
    {
        $count = ChatMessage::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get messages between current user and specified user
     */
    public function messages($userId)
    {
        $authUserId = Auth::id();

        $messages = ChatMessage::where(function ($q) use ($authUserId, $userId) {
            $q->where('sender_id', $authUserId)
                ->where('receiver_id', $userId);
        })
            ->orWhere(function ($q) use ($authUserId, $userId) {
                $q->where('sender_id', $userId)
                    ->where('receiver_id', $authUserId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', $authUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    /**
     * Send a new message
     */
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:10240', // Max 10MB
        ]);

        if (!$request->message && !$request->hasFile('image')) {
            return response()->json(['error' => 'Mensagem ou imagem é obrigatória.'], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat_attachments', 'public');
            $attachmentPath = $path;
        }

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
        ]);

        // Load relationships for response if needed
        $message->load('sender:id,name,profile_photo_path');

        try {
            broadcast(new \App\Events\MessageReceived($message));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Broadcasting failed: " . $e->getMessage());
        }

        // Send Database Notification
        $receiver = User::find($request->receiver_id);
        $receiver->notify(new \App\Notifications\ChatMessageNotification($message));

        // Send Push Notification
        $receiver = User::find($request->receiver_id);
        if ($receiver && $receiver->expo_push_token) {
            $senderName = Auth::user()->name;
            $body = $message->message ? $message->message : '📷 Imagem';
            $title = "Nova mensagem de {$senderName}";

            \App\Helpers\Helpers::sendExpoNotification(
                $receiver->expo_push_token,
                $title,
                $body,
                ['type' => 'chat_message', 'sender_id' => Auth::id()]
            );
        }

        return response()->json($message, 201);
    }
}
