<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\AiSupportService;
use Illuminate\Support\Facades\Auth;

class AiSupportChat extends Component
{
    public $isOpen = false;
    public $messages = [];
    public $messageText = '';
    public $isTyping = false;

    public function mount()
    {
        // Mensagem de boas-vindas inicial (teste de deploy)
        $this->messages = [
            [
                'role' => 'assistant', 
                'content' => "Olá, " . Auth::user()->name . "! 🚀 Sou o Ghotme AI (v2.0). Em que posso te ajudar agora?"
            ]
        ];
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage()
    {
        if (trim($this->messageText) === '') return;

        // 1. Adicionar mensagem do usuário ao histórico local
        $this->messages[] = ['role' => 'user', 'content' => $this->messageText];
        
        $userQuestion = $this->messageText;
        $this->messageText = '';
        $this->isTyping = true;

        // 2. Chamar a IA (Livewire 3 permite usar o dispatch para rodar em background se quiser, 
        // mas aqui vamos rodar direto para simplificar a demo)
        $aiService = new AiSupportService();
        
        $userContext = [
            'name' => Auth::user()->name,
            'company_name' => Auth::user()->company->name ?? 'Sua Oficina',
            'niche' => Auth::user()->company->niche ?? 'automotive'
        ];

        // Filtramos apenas as últimas 5 mensagens para o histórico da IA (limite de contexto/tokens)
        $recentMessages = array_slice($this->messages, -6);

        $aiResponse = $aiService->ask($recentMessages, $userContext);

        // 3. Adicionar resposta da IA
        $this->messages[] = ['role' => 'assistant', 'content' => $aiResponse];
        $this->isTyping = false;

        $this->dispatch('message-sent-ai');
    }

    public function clearChat()
    {
        $this->mount();
    }

    public function render()
    {
        return view('livewire.ai-support-chat');
    }
}
