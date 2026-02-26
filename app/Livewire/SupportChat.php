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
    public $onlyMyClients = false; // Novo filtro

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

        // Marcar TODAS as mensagens deste cliente enviadas para a empresa como lidas
        ChatMessage::withoutGlobalScopes()
            ->where('client_id', $this->activeClientId)
            ->whereNull('sender_id')
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

        if ($this->chatType === 'client') {
            // Mensagem para o cliente
            ChatMessage::create([
                'company_id' => Auth::user()->company_id,
                'client_id' => $this->activeClientId,
                'sender_id' => Auth::id(),
                'receiver_id' => null, // O "receptor" é o cliente (identificado pelo client_id)
                'message' => $this->message ?? '',
                'attachment_path' => $attachmentPath,
                'is_read' => false
            ]);
        } else {
            // Mensagem interna (equipe ou suporte)
            ChatMessage::create([
                'company_id' => Auth::user()->company_id,
                'sender_id' => Auth::id(),
                'receiver_id' => $this->activeUserId,
                'message' => $this->message ?? '',
                'attachment_path' => $attachmentPath,
                'is_read' => false
            ]);
        }

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

        // 3. Clientes - Mostra APENAS os do atendente e prioriza os recentes
        $clientQuery = \App\Models\Clients::where('clients.company_id', $user->company_id)
            ->select('clients.*')
            ->leftJoin('chat_messages', function ($join) {
                $join->on('clients.id', '=', 'chat_messages.client_id');
            })
            ->selectRaw('MAX(chat_messages.created_at) as last_message_at')
            ->whereHas('attendants', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->groupBy('clients.id');

        $clientContacts = $clientQuery->where('clients.name', 'like', '%' . $this->search . '%')
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC')
            ->orderBy('clients.name', 'asc')
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
                // Conta mensagens não lidas desse cliente para QUALQUER UM da empresa
                $contact->unread_count = ChatMessage::withoutGlobalScopes()
                    ->where('client_id', $contact->id)
                    ->whereNull('sender_id')
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
            $activeContact = \App\Models\Clients::withoutGlobalScopes()->find($this->activeClientId);
            // Mostra todas as mensagens do cliente para a empresa, não apenas para um atendente
            $messages = ChatMessage::withoutGlobalScopes()
                ->where('client_id', $this->activeClientId)
                ->orderBy('created_at', 'asc')->get();
        }

        // Calcular totais de não lidas para as abas (chamativo)
        $unreadTeam = ChatMessage::withoutGlobalScopes()
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->whereIn('sender_id', $teamContacts->pluck('id'))
            ->count();

        $unreadSupport = ChatMessage::withoutGlobalScopes()
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->whereIn('sender_id', $supportContacts->pluck('id'))
            ->count();

        $unreadClients = ChatMessage::withoutGlobalScopes()
            ->whereNull('sender_id')
            ->where('is_read', false)
            ->where('company_id', $user->company_id)
            ->whereIn('client_id', $clientContacts->pluck('id')) // Apenas clientes visíveis para este atendente
            ->count();

        return view('livewire.support-chat', [
            'contacts' => $contacts,
            'messages' => $messages,
            'activeContact' => $activeContact,
            'teamCount' => $teamContacts->count(),
            'supportCount' => $supportContacts->count(),
            'clientCount' => $clientContacts->count(),
            'unreadTeam' => $unreadTeam,
            'unreadSupport' => $unreadSupport,
            'unreadClients' => $unreadClients,
        ]);
    }
}
