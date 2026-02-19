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
     * Get list of contacts (team members)
     */
    public function contacts()
    {
        $user = Auth::user();

        // Get team members (same company, excluding self)
        $contacts = User::where('id', '!=', $user->id)
            ->where('company_id', $user->company_id)
            ->select('id', 'name', 'email', 'profile_photo_path', 'status')
            ->get()
            ->map(function ($contact) {
                // Append full profile photo URL if needed
                $contact->profile_photo_url = $contact->profile_photo_url;
                return $contact;
            });

        return response()->json($contacts);
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

        broadcast(new \App\Events\MessageReceived($message))->toOthers();

        return response()->json($message, 201);
    }
}
