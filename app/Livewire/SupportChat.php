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
    public $message = '';
    public $attachment;
    public $search = '';
    public $activeTab = 'team'; // team, support, clients
    public $userStatus;

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
        $this->activeUserId = null; 
    }

    public function selectUser($userId)
    {
        $this->activeUserId = (int) $userId;
        
        // Log para debug no terminal (verifique o log do laravel)
        \Illuminate\Support\Facades\Log::info("Usuário selecionado no Chat: " . $this->activeUserId);

        // Marcar mensagens como lidas
        ChatMessage::withoutGlobalScopes()
            ->where('sender_id', $this->activeUserId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        $this->dispatch('message-sent'); 
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|image|max:10240', // 10MB
        ]);

        if (!$this->message && !$this->attachment) {
            return;
        }

        if (!$this->activeUserId) {
            return;
        }

        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('chat_attachments', 'public');
        }

        // Criar a mensagem
        ChatMessage::create([
            'company_id' => Auth::user()->company_id,
            'sender_id' => Auth::id(),
            'receiver_id' => $this->activeUserId,
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

        // 1. Equipe Interna
        $teamContacts = User::where('id', '!=', $user->id)
            ->where('company_id', $user->company_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->get();

        // 2. Suporte Ghotme (Ignora escopo de empresa)
        $supportContacts = User::withoutGlobalScopes()
            ->where('id', '!=', $user->id)
            ->where(function ($q) {
                $q->where('role', 'super_admin')
                  ->orWhere('email', 'suporte@ghotme.com.br');
            })
            ->where('name', 'like', '%' . $this->search . '%')
            ->get();

        $contacts = match ($this->activeTab) {
            'team' => $teamContacts,
            'support' => $supportContacts,
            'clients' => collect(),
            default => $teamContacts
        };

        // Adicionar contagem de não lidas para cada contato
        $contacts->map(function ($contact) use ($user) {
            $contact->unread_count = ChatMessage::withoutGlobalScopes()
                ->where('sender_id', $contact->id)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
            return $contact;
        });

        // Buscar usuário ativo e mensagens (Ignorando escopo)
        $activeUser = null;
        $messages = [];

        if ($this->activeUserId) {
            $activeUser = User::withoutGlobalScopes()->find($this->activeUserId);
            
            $messages = ChatMessage::withoutGlobalScopes()
                ->with('sender')
                ->where(function ($q) {
                    $q->where('sender_id', Auth::id())->where('receiver_id', $this->activeUserId);
                })
                ->orWhere(function ($q) {
                    $q->where('sender_id', $this->activeUserId)->where('receiver_id', Auth::id());
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
            'clientCount' => 0,
        ]);
    }
}
