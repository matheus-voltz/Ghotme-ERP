<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupportChat extends Component
{
    use WithFileUploads;

    public $activeUserId;
    public $activeClientId; // Novo
    public $chatType = 'user'; // user ou client
    public $message = '';
    public $attachment;
    public $search = '';
    public $activeTab = 'team'; 
    public $userStatus;

    public function mount()
    {
        $this->userStatus = Auth::user()->chat_status ?? 'online';
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->activeUserId = null; 
        $this->activeClientId = null;
    }

    public function selectUser($userId)
    {
        $this->activeUserId = (int) $userId;
        $this->activeClientId = null;
        $this->chatType = 'user';

        ChatMessage::withoutGlobalScopes()
            ->where('sender_id', $this->activeUserId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        $this->dispatch('message-sent'); 
    }

    public function selectClient($clientId)
    {
        $this->activeClientId = (int) $clientId;
        $this->activeUserId = null;
        $this->chatType = 'client';

        ChatMessage::withoutGlobalScopes()
            ->where('client_id', $this->activeClientId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        $this->dispatch('message-sent');
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|image|max:10240',
        ]);

        if (!$this->message && !$this->attachment) return;
        if (!$this->activeUserId && !$this->activeClientId) return;

        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('chat_attachments', 'public');
        }

        ChatMessage::create([
            'company_id' => Auth::user()->company_id,
            'client_id' => $this->activeClientId,
            'sender_id' => Auth::id(),
            'receiver_id' => $this->activeUserId, // Pode ser null se for para cliente
            'message' => $this->message ?? '',
            'attachment_path' => $attachmentPath,
            'is_read' => false
        ]);

        $this->reset(['message', 'attachment']);
        $this->dispatch('message-sent');
    }

    public function render()
    {
        $user = Auth::user();

        // 1. Equipe
        $teamContacts = User::where('id', '!=', $user->id)
            ->where('company_id', $user->company_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->get();

        // 2. Suporte
        $supportContacts = User::withoutGlobalScopes()
            ->where('id', '!=', $user->id)
            ->where(function ($q) {
                $q->where('role', 'super_admin')->orWhere('email', 'suporte@ghotme.com.br');
            })
            ->where('name', 'like', '%' . $this->search . '%')
            ->get();

        // 3. Clientes
        $clientContacts = \App\Models\Clients::where('company_id', $user->company_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->get();

        $contacts = match ($this->activeTab) {
            'team' => $teamContacts,
            'support' => $supportContacts,
            'clients' => $clientContacts,
            default => $teamContacts
        };

        // Unread counts
        $contacts->map(function ($contact) use ($user) {
            if ($this->activeTab === 'clients') {
                $contact->unread_count = ChatMessage::withoutGlobalScopes()
                    ->where('client_id', $contact->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            } else {
                $contact->unread_count = ChatMessage::withoutGlobalScopes()
                    ->where('sender_id', $contact->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            }
            return $contact;
        });

        $activeContact = null;
        $messages = [];

        if ($this->chatType === 'user' && $this->activeUserId) {
            $activeContact = User::withoutGlobalScopes()->find($this->activeUserId);
            $messages = ChatMessage::withoutGlobalScopes()
                ->where(function ($q) {
                    $q->where('sender_id', Auth::id())->where('receiver_id', $this->activeUserId);
                })
                ->orWhere(function ($q) {
                    $q->where('sender_id', $this->activeUserId)->where('receiver_id', Auth::id());
                })
                ->orderBy('created_at', 'asc')->get();
        } elseif ($this->chatType === 'client' && $this->activeClientId) {
            $activeContact = \App\Models\Clients::find($this->activeClientId);
            // Mostra todas as mensagens do cliente para a empresa, não apenas para um atendente
            $messages = ChatMessage::withoutGlobalScopes()
                ->where('client_id', $this->activeClientId)
                ->orderBy('created_at', 'asc')->get();
        }

        return view('livewire.support-chat', [
            'contacts' => $contacts,
            'messages' => $messages,
            'activeContact' => $activeContact,
            'teamCount' => $teamContacts->count(),
            'supportCount' => $supportContacts->count(),
            'clientCount' => $clientContacts->count(),
        ]);
    }
}
