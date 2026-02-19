<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;

class SupportChat extends Component
{
    use WithFileUploads;

    public $activeUserId;
    public $message = '';
    public $attachment;
    public $search = '';
    public $activeTab = 'team'; // team, support, clients
    public $userStatus;
    public $lastMessageId;

    public function mount()
    {
        $this->userStatus = Auth::user()->chat_status ?? 'online';
    }

    public function updateStatus($newStatus)
    {
        $validStatuses = ['online', 'away', 'busy', 'offline'];
        if (in_array($newStatus, $validStatuses)) {
            $this->userStatus = $newStatus;
            Auth::user()->update(['chat_status' => $newStatus]);
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->activeUserId = null; // Limpa a seleção ao trocar de aba
    }

    public function selectUser($userId)
    {
        $this->activeUserId = $userId;
        // Marcar mensagens como lidas
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Verifica se há novas mensagens para o chat atual.
     * Chamado via wire:poll na view.
     */
    public function checkNewMessages()
    {
        // O poll apenas força o re-render
        return;
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|image|max:10240', // 10MB
        ]);

        if (!$this->message && !$this->attachment) {
            $this->addError('message', 'Mensagem ou anexo é obrigatório.');
            return;
        }

        if (!$this->activeUserId) {
            return;
        }

        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('chat_attachments', 'public');
        }

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->activeUserId,
            'message' => $this->message ?? '',
            'attachment_path' => $attachmentPath,
        ]);

        // Broadcast Message
        $message->load('sender:id,name,profile_photo_path');
        try {
            broadcast(new \App\Events\MessageReceived($message))->toOthers();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Broadcasting failed: " . $e->getMessage());
        }

        // Send Database Notification
        $receiver = User::find($this->activeUserId);
        $receiver->notify(new \App\Notifications\ChatMessageNotification($message));

        // Send Notification if User has Push Token
        $receiver = User::find($this->activeUserId);
        if ($receiver && $receiver->expo_push_token) {
            $senderName = Auth::user()->name;
            $body = $message->message ? $message->message : '📷 Imagem';
            $title = "Nova mensagem de {$senderName}";

            $res = \App\Helpers\Helpers::sendExpoNotification(
                $receiver->expo_push_token,
                $title,
                $body,
                ['type' => 'chat_message', 'sender_id' => Auth::id()]
            );
            
            \Illuminate\Support\Facades\Log::info("Chat Notification Sent to User {$receiver->id}", [
                'token' => $receiver->expo_push_token,
                'response' => $res
            ]);
        } else {
            \Illuminate\Support\Facades\Log::warning("Chat Notification NOT Sent: User {$this->activeUserId} has no push token.");
        }

        // Enviar para Telegram se o usuário estiver conectado
        $this->reset(['message', 'attachment']);
        $this->dispatch('message-sent');
    }

    public function render()
    {
        $user = Auth::user();

        // 1. Equipe Interna (Mesma Empresa)
        $teamContacts = User::where('id', '!=', $user->id)
            ->where('company_id', $user->company_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->get()
            ->map(function ($contact) use ($user) {
                $contact->unread_count = ChatMessage::where('sender_id', $contact->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                return $contact;
            });

        // 2. Suporte Ghotme (Super Admins)
        $supportContacts = User::where('id', '!=', $user->id)
            ->where(function ($q) {
                $q->whereNull('company_id')
                    ->orWhere('role', 'super_admin');
            })
            ->where('name', 'like', '%' . $this->search . '%')
            ->get()
            ->map(function ($contact) use ($user) {
                $contact->unread_count = ChatMessage::where('sender_id', $contact->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                return $contact;
            });

        // 3. Clientes (CRM)
        $clientContacts = collect();

        $contacts = match ($this->activeTab) {
            'team' => $teamContacts,
            'support' => $supportContacts,
            'clients' => $clientContacts,
            default => $teamContacts
        };

        $activeUser = $this->activeUserId ? User::find($this->activeUserId) : null;
        $messages = [];

        if ($this->activeUserId) {
            $messages = ChatMessage::with('sender')->where(function ($q) {
                $q->where('sender_id', Auth::id())
                    ->where('receiver_id', $this->activeUserId);
            })
                ->orWhere(function ($q) {
                    $q->where('sender_id', $this->activeUserId)
                        ->where('receiver_id', Auth::id());
                })
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('livewire.support-chat', [
            'contacts' => $contacts,
            'messages' => $messages,
            'activeUser' => $activeUser,
            'teamCount' => $teamContacts->count(),
            'supportCount' => $supportContacts->count(),
            'clientCount' => $clientContacts->count(),
        ]);
    }
}
