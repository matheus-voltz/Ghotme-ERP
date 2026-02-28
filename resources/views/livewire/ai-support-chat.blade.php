<div>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <!-- Floating Help Button -->
    <button wire:click="toggleChat" class="btn btn-primary rounded-pill btn-icon shadow-lg" style="position: fixed; bottom: 25px; right: 25px; z-index: 9999; width: 50px; height: 50px;">
        @if($isOpen)
            <i class="ti tabler-x icon-28px"></i>
        @else
            <i class="ti tabler-robot icon-28px"></i>
        @endif
    </button>

    <!-- Chat Window -->
    <div x-cloak x-data="{ open: @entangle('isOpen') }" x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="card shadow-lg chat-window-ai" 
         style="position: fixed; bottom: 85px; right: 25px; z-index: 9999; width: 380px; max-height: 600px; display: flex; flex-direction: column;">
        
        <!-- Header -->
        <div class="card-header bg-primary text-white p-3 d-flex justify-content-between align-items-center rounded-top">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                    <span class="avatar-initial rounded-circle bg-label-white text-primary">AI</span>
                </div>
                <div>
                    <h6 class="text-white mb-0">Suporte Inteligente</h6>
                    <small class="text-white opacity-75">Online • Ghotme AI</small>
                </div>
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-icon text-white p-0" type="button" data-bs-toggle="dropdown">
                    <i class="ti tabler-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="javascript:void(0);" wire:click="clearChat">Limpar Conversa</a></li>
                </ul>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="card-body p-3 overflow-auto" id="chat-messages-container" style="flex-grow: 1; min-height: 300px; max-height: 450px; background-color: #f8f7fa;">
            @foreach($messages as $msg)
                <div class="d-flex {{ $msg['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' }} mb-4">
                    <div class="chat-message-content {{ $msg['role'] === 'user' ? 'bg-primary text-white' : 'bg-white border text-body' }} p-3 rounded" 
                         style="max-width: 85%; {{ $msg['role'] === 'user' ? 'border-bottom-right-radius: 0 !important;' : 'border-bottom-left-radius: 0 !important;' }}">
                        @php
                            $content = e($msg['content']);
                            // Converte ## Título em um H6 estilizado
                            $content = preg_replace('/## (.*?)\n/', '<h6 class="fw-bold mt-2 mb-1 text-primary">$1</h6>', $content);
                            // Converte **Negrito** em <b>
                            $content = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $content);
                        @endphp
                        <p class="mb-0 small">{!! nl2br($content) !!}</p>
                    </div>
                </div>
            @endforeach

            @if($isTyping)
                <div class="d-flex justify-content-start mb-4">
                    <div class="bg-white border p-3 rounded text-muted small" style="border-bottom-left-radius: 0 !important;">
                        <span class="spinner-grow spinner-grow-sm me-1"></span> Digitando...
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer / Input -->
        <div class="card-footer p-3 border-top">
            <form wire:submit.prevent="sendMessage" class="d-flex gap-2">
                <input type="text" wire:model="messageText" class="form-control" placeholder="Pergunte qualquer coisa..." autocomplete="off">
                <button type="submit" class="btn btn-primary btn-icon" wire:loading.attr="disabled">
                    <i class="ti tabler-send"></i>
                </button>
            </form>
            <div class="text-center mt-2">
                <small class="text-muted" style="font-size: 10px;">Ghotme AI pode cometer erros. Verifique informações importantes.</small>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        const container = document.getElementById('chat-messages-container');
        
        @this.on('message-sent-ai', () => {
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 100);
        });
    });
</script>
