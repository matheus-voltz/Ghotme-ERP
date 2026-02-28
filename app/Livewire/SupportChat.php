<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

        // LÓGICA DE MARCAR COMO LIDO (Suporte/Master)
        $supportUser = User::withoutGlobalScopes()->where('email', 'suporte@ghotme.com.br')->first();
        
        $query = ChatMessage::withoutGlobalScopes()
            ->where('is_read', false);

        if (Auth::user()->is_master) {
            // Se eu sou MASTER, marco como lido o que o CLIENTE (sender) me enviou
            $query->where('sender_id', $this->activeUserId)
                  ->where(function($q) use ($supportUser) {
                      $q->where('receiver_id', Auth::id());
                      if ($supportUser) $q->orWhere('receiver_id', $supportUser->id);
                  });
        } else {
            // Se eu sou CLIENTE, marco como lido o que o SUPORTE/MASTER (sender) me enviou
            $query->where('receiver_id', Auth::id())
                  ->where(function($q) use ($supportUser, $userId) {
                      $q->where('sender_id', $userId)
                        ->orWhereHas('sender', function($sq) { $sq->where('is_master', true); });
                  });
        }

        $query->update(['is_read' => true]);

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
            $recipient = User::withoutGlobalScopes()->find($this->activeUserId);
            
            // LÓGICA DE IDENTIDADE MASTER:
            // Se eu sou MASTER, eu assumo a identidade do Suporte Oficial (ID 14)
            // para que o cliente veja a resposta no mesmo chat que ele abriu.
            $supportUser = User::withoutGlobalScopes()->where('email', 'suporte@ghotme.com.br')->first();
            $mySenderId = (Auth::user()->is_master && $supportUser) ? $supportUser->id : Auth::id();

            // LÓGICA DE EMPRESA:
            $targetCompanyId = (Auth::user()->is_master && $recipient) 
                ? $recipient->company_id 
                : (Auth::user()->company_id ?? $recipient->company_id ?? null);

            $newMessage = ChatMessage::create([
                'company_id' => $targetCompanyId,
                'sender_id' => $mySenderId,
                'receiver_id' => $this->activeUserId,
                'message' => $this->message ?? '',
                'attachment_path' => $attachmentPath,
                'is_read' => false
            ]);

            Log::info("ENVIO MASTER DEBUG: Salvo com Sucesso! ID: " . $newMessage->id . " | Company: " . $newMessage->company_id . " | Sender: " . $newMessage->sender_id . " | Receiver: " . $newMessage->receiver_id);

            // SE EU SOU MASTER ENVIANDO: Notifico o cliente para ele ver o badge/alerta
            if (Auth::user()->is_master && $recipient) {
                $recipient->notify(new \App\Notifications\SystemAlertNotification(
                    "🎧 Suporte Ghotme",
                    "Você recebeu uma nova resposta do suporte.",
                    url('/support/chat')
                ));
            }

            // SE EU SOU CLIENTE ENVIANDO PARA O SUPORTE: Notifico o MASTER
            if ($recipient && ($recipient->role === 'super_admin' || $recipient->email === 'suporte@ghotme.com.br')) {
                $master = User::where('is_master', true)->first();
                if ($master && $master->id !== Auth::id()) {
                    // 1. Notificação no sininho
                    $master->notify(new \App\Notifications\SystemAlertNotification(
                        "💬 Nova Mensagem no Suporte",
                        "De: " . Auth::user()->name . " (" . (Auth::user()->company->name ?? 'Empresa N/A') . ")",
                        url('/support/chat')
                    ));

                    // 2. Cria uma mensagem espelhada para o Master ver no chat dele
                    ChatMessage::create([
                        'company_id' => $master->company_id,
                        'sender_id' => Auth::id(),
                        'receiver_id' => $master->id,
                        'message' => "[SUPORTE]: " . ($this->message ?? 'Anexo enviado'),
                        'attachment_path' => $attachmentPath,
                        'is_read' => false
                    ]);
                }
            }
        }

        $this->reset(['message', 'attachment']);
        $this->dispatch('message-sent');
    }

    public function render()
    {
        $user = Auth::user();
        $isMaster = $user->is_master;

        if ($isMaster) {
            // Master só vê quem mandou mensagem pra ele (Tickets de Suporte)
            $this->activeTab = 'support';
            $contacts = User::withoutGlobalScopes()
                ->where('users.id', '!=', $user->id)
                ->join('chat_messages', 'users.id', '=', 'chat_messages.sender_id')
                ->where('chat_messages.receiver_id', $user->id)
                ->select('users.id', 'users.name', 'users.email', 'users.profile_photo_path', 'users.company_id', DB::raw('MAX(chat_messages.created_at) as last_message_at'))
                ->groupBy('users.id', 'users.name', 'users.email', 'users.profile_photo_path', 'users.company_id')
                ->orderBy('last_message_at', 'desc')
                ->get();
            
            $teamContacts = collect();
            $clientContacts = collect();
            $supportContacts = $contacts;
        } else {
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
        }

        // Unread counts
        $supportUser = User::withoutGlobalScopes()->where('email', 'suporte@ghotme.com.br')->first();

        $contacts->map(function ($contact) use ($user, $isMaster, $supportUser) {
            if (!$isMaster && $this->activeTab === 'clients') {
                $contact->unread_count = ChatMessage::withoutGlobalScopes()
                    ->where('client_id', $contact->id)
                    ->whereNull('sender_id')
                    ->where('is_read', false)
                    ->count();
            } else {
                $query = ChatMessage::withoutGlobalScopes()
                    ->where('sender_id', $contact->id)
                    ->where('is_read', false);
                
                if ($isMaster) {
                    $query->where(function($q) use ($user, $supportUser) {
                        $q->where('receiver_id', $user->id);
                        if ($supportUser) $q->orWhere('receiver_id', $supportUser->id);
                    });
                } else {
                    $query->where('receiver_id', $user->id);
                }

                $contact->unread_count = $query->count();
            }
            return $contact;
        });

        $activeContact = null;
        $messages = [];

        if ($this->chatType === 'user' && $this->activeUserId) {
            $activeContact = User::withoutGlobalScopes()->find($this->activeUserId);
            $supportUser = User::withoutGlobalScopes()->where('email', 'suporte@ghotme.com.br')->first();
            $supportId = $supportUser->id ?? Auth::id();
            
            // LÓGICA DE MENSAGENS MASTER: Considera meu ID e o ID do Suporte Oficial
            $messages = ChatMessage::withoutGlobalScopes()
                ->where(function ($q) use ($supportId) {
                    $q->whereIn('sender_id', [Auth::id(), $supportId])->where('receiver_id', $this->activeUserId);
                })
                ->orWhere(function ($q) use ($supportId) {
                    $q->where('sender_id', $this->activeUserId)->whereIn('receiver_id', [Auth::id(), $supportId]);
                })
                ->orderBy('created_at', 'asc')->get();
            
            Log::info("Chat Master-Cliente DEBUG: " . $messages->count() . " mensagens encontradas.");
        } elseif ($this->chatType === 'client' && $this->activeClientId) {
            $activeContact = \App\Models\Clients::withoutGlobalScopes()->find($this->activeClientId);
            $messages = ChatMessage::withoutGlobalScopes()
                ->where('client_id', $this->activeClientId)
                ->orderBy('created_at', 'asc')->get();
        }

        // Counts para as abas
        $unreadTeam = $isMaster ? 0 : ChatMessage::withoutGlobalScopes()->where('receiver_id', $user->id)->where('is_read', false)->whereIn('sender_id', $teamContacts->pluck('id'))->count();
        
        // Unread Suporte para Clientes: conta mensagens de quem é super_admin ou master
        $unreadSupport = ChatMessage::withoutGlobalScopes()
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->where(function($q) use ($supportContacts) {
                $q->whereIn('sender_id', $supportContacts->pluck('id'))
                  ->orWhereHas('sender', function($sq) { $sq->where('is_master', true); });
            })
            ->count();

        $unreadClients = ($isMaster || !$clientContacts->count()) ? 0 : ChatMessage::withoutGlobalScopes()->whereNull('sender_id')->where('is_read', false)->whereIn('client_id', $clientContacts->pluck('id'))->count();

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
