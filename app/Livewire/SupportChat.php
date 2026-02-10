<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;

class SupportChat extends Component
{
    public $activeUserId;
    public $message = '';
    public $search = '';
    public $activeTab = 'team'; // team, support, clients
    public $userStatus;
    public $lastMessageId;

    public function mount()
    {
        $this->userStatus = Auth::user()->status ?? 'active';
        $this->lastMessageId = ChatMessage::max('id');
        
        // Ao iniciar, tenta selecionar o primeiro colega de equipe
        $firstContact = User::where('id', '!=', Auth::id())
            ->where('company_id', Auth::user()->company_id)
            ->first();
            
        if ($firstContact) {
            $this->activeUserId = $firstContact->id;
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->activeUserId = null; // Limpa a seleção ao trocar de aba
    }

    public function changeStatus($status)
    {
        $this->userStatus = $status;
        Auth::user()->update(['status' => $status]);
    }

    public function checkNewMessages()
    {
        $latestMessage = ChatMessage::where('receiver_id', Auth::id())
            ->orderBy('id', 'desc')
            ->first();

        if ($latestMessage && $latestMessage->id > $this->lastMessageId) {
            $this->lastMessageId = $latestMessage->id;
            $senderName = $latestMessage->sender->name;
            $this->dispatch('new-chat-message', [
                'sender' => $senderName,
                'message' => substr($latestMessage->message, 0, 50) . (strlen($latestMessage->message) > 50 ? '...' : '')
            ]);
        }
    }

    public function selectUser($userId)
    {
        $this->activeUserId = $userId;
        // Marcar mensagens como lidas
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', Auth::id())
            ->update(['is_read' => true]);
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'required|string|max:1000',
        ]);

        if (!$this->activeUserId) {
            return;
        }

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->activeUserId,
            'message' => $this->message,
        ]);

        // Enviar para Telegram se o usuário estiver conectado
        $receiver = User::find($this->activeUserId);
        if ($receiver && $receiver->telegram_chat_id) {
            try {
                // Usando o helper app() para instanciar o Nutgram sem injeção direta no método
                app(Nutgram::class)->sendMessage($this->message, ['chat_id' => $receiver->telegram_chat_id]);
            } catch (\Exception $e) {
                // Logar erro mas não parar o chat
                \Log::error("Erro ao enviar Telegram: " . $e->getMessage());
            }
        }

        $this->message = '';
        // Scroll to bottom event could be dispatched here
    }

    public function render()
    {
        $user = Auth::user();

        // 1. Equipe Interna (Mesma Empresa)
        $teamContacts = User::where('id', '!=', $user->id)
            ->where('company_id', $user->company_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->get();

        // 2. Suporte Ghotme (Super Admins - Usuários sem company_id ou role específica)
        // Assumindo que admins do sistema não têm company_id ou têm uma flag
        $supportContacts = User::where('id', '!=', $user->id)
            ->where(function($q) {
                $q->whereNull('company_id')
                  ->orWhere('role', 'super_admin'); 
            })
            ->where('name', 'like', '%' . $this->search . '%')
            ->get();

        // 3. Clientes (CRM) - Placeholder
        $clientContacts = collect(); 

        // Seleciona a lista baseada na aba ativa
        $contacts = match($this->activeTab) {
            'team' => $teamContacts,
            'support' => $supportContacts,
            'clients' => $clientContacts,
            default => $teamContacts
        };

        $messages = [];
        $activeUser = null;

        if ($this->activeUserId) {
            $activeUser = User::find($this->activeUserId);
            // Se for User
            if ($activeUser) {
                $messages = ChatMessage::where(function($q) {
                        $q->where('sender_id', Auth::id())
                        ->where('receiver_id', $this->activeUserId);
                    })
                    ->orWhere(function($q) {
                        $q->where('sender_id', $this->activeUserId)
                        ->where('receiver_id', Auth::id());
                    })
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
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
