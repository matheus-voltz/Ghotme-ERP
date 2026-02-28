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
     * Get unread message count for the authenticated user
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        // Se for Master, conta para ele e suporte oficial
        if ($user->is_master) {
            $supportIds = [7, 14];
            $unreadCount = ChatMessage::withoutGlobalScopes()
                ->whereIn('receiver_id', $supportIds)
                ->where('is_read', false)
                ->count();
            
            $lastMessage = ChatMessage::withoutGlobalScopes()
                ->whereIn('receiver_id', $supportIds)
                ->where('is_read', false)
                ->latest()
                ->first();
        } else {
            $unreadCount = ChatMessage::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
            
            $lastMessage = ChatMessage::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->latest()
                ->first();
        }

        return response()->json([
            'unread_count' => $unreadCount,
            'last_message' => $lastMessage
        ]);
    }

    /**
     * Get list of contacts (team members) with unread counts
     */
    public function contacts()
    {
        $user = Auth::user();

        // Equipe
        $team = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($user) {
                $q->where('company_id', $user->company_id)
                    ->orWhere('role', 'super_admin');
            })
            ->get()
            ->map(function ($contact) use ($user) {
                $contact->unread_count = ChatMessage::where('sender_id', $contact->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                $contact->is_client = false;
                return $contact;
            });

        // Clientes (que já mandaram mensagem ou estão vinculados à empresa)
        $clients = \App\Models\Clients::where('company_id', $user->company_id)
            ->get()
            ->map(function ($client) use ($user) {
                $client->unread_count = ChatMessage::where('client_id', $client->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                $client->is_client = true;
                return $client;
            });

        return response()->json([
            'team' => $team,
            'clients' => $clients
        ]);
    }

    /**
     * Get messages between current user and specified user or client
     */
    public function messages(Request $request, $id)
    {
        $authUserId = Auth::id();
        $isClient = $request->query('is_client') === 'true';

        if ($isClient) {
            $messages = ChatMessage::where('client_id', $id)
                ->where(function($q) use ($authUserId) {
                    $q->where('receiver_id', $authUserId)->orWhere('sender_id', $authUserId);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            ChatMessage::where('client_id', $id)
                ->where('receiver_id', $authUserId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } else {
            $messages = ChatMessage::where(function ($q) use ($authUserId, $id) {
                $q->where('sender_id', $authUserId)->where('receiver_id', $id);
            })
            ->orWhere(function ($q) use ($authUserId, $id) {
                $q->where('sender_id', $id)->where('receiver_id', $authUserId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

            ChatMessage::where('sender_id', $id)
                ->where('receiver_id', $authUserId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json($messages);
    }

    /**
     * Send a new message
     */
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'nullable|exists:users,id',
            'client_id' => 'nullable|exists:clients,id',
            'message' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:10240',
        ]);

        if (!$request->message && !$request->hasFile('image')) {
            return response()->json(['error' => 'Mensagem ou imagem é obrigatória.'], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('image')) {
            $attachmentPath = $request->file('image')->store('chat_attachments', 'public');
        }

        $message = ChatMessage::create([
            'company_id' => Auth::user()->company_id,
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'client_id' => $request->client_id,
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
        ]);

        $message->load('sender:id,name');

        // Notificar Master se for uma mensagem de suporte ou para um usuário Master
        $master = \App\Models\User::where('is_master', true)->first();
        if ($master && $message->receiver_id == $master->id) {
            $master->notify(new \App\Notifications\SystemAlertNotification(
                "💬 Suporte (App): " . ($message->sender->name ?? 'Usuário'),
                \Illuminate\Support\Str::limit($message->message, 50),
                url('/support/chat')
            ));
        }

        try {
            broadcast(new \App\Events\MessageReceived($message));
        } catch (\Exception $e) {}

        return response()->json($message, 201);
    }
}
