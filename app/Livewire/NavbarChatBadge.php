<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class NavbarChatBadge extends Component
{
    public $unreadCount = 0;

    public function mount()
    {
        $this->updateCount();
    }

    /**
     * Atualiza o contador de mensagens não lidas.
     * Este método será chamado automaticamente pelo Livewire via polling.
     */
    public function updateCount()
    {
        if (Auth::check()) {
            $this->unreadCount = ChatMessage::withoutGlobalScopes()
                ->where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->count();
        }
    }

    public function render()
    {
        return view('livewire.navbar-chat-badge');
    }
}
