<div>
    <div class="app-chat card overflow-hidden">
        <div class="row g-0">
            <!-- Contacts (Esquerda) -->
            <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" style="width: 350px;">
                <div class="sidebar-header h-px-75 px-5 border-bottom d-flex align-items-center">
                    <div class="input-group input-group-merge w-100">
                        <span class="input-group-text"><i class="ti tabler-search"></i></span>
                        <input type="text" class="form-control" wire:model.live="search" placeholder="Buscar..." />
                    </div>
                </div>
                <div class="sidebar-body" wire:poll.5s>
                    <div class="d-flex justify-content-between px-3 py-2 border-bottom bg-light">
                        <button class="btn btn-sm {{ $activeTab == 'team' ? 'btn-primary' : 'btn-label-secondary' }}" wire:click="setTab('team')">Equipe</button>
                        <button class="btn btn-sm {{ $activeTab == 'support' ? 'btn-primary' : 'btn-label-secondary' }}" wire:click="setTab('support')">Suporte</button>
                        <button class="btn btn-sm {{ $activeTab == 'clients' ? 'btn-primary' : 'btn-label-secondary' }}" wire:click="setTab('clients')">Clientes</button>
                    </div>
                    <ul class="list-unstyled chat-contact-list py-2 mb-0" style="max-height: 500px; overflow-y: auto;">
                        @foreach($contacts as $contact)
                        @php
                            $isSelected = ($chatType === 'user' && $activeUserId == $contact->id) || ($chatType === 'client' && $activeClientId == $contact->id);
                            $clickAction = ($activeTab === 'clients') ? "selectClient({$contact->id})" : "selectUser({$contact->id})";
                            $avatar = ($activeTab === 'clients') ? "https://ui-avatars.com/api/?name=" . urlencode($contact->name) . "&color=7367f0&background=f8f7fa" : $contact->profile_photo_url;
                        @endphp
                        <li class="chat-contact-list-item {{ $isSelected ? 'active' : '' }} p-3 border-bottom" wire:click="{{ $clickAction }}" wire:key="contact-{{ $activeTab }}-{{ $contact->id }}" style="cursor: pointer;">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle" width="40">
                                </div>
                                <div class="chat-contact-info flex-grow-1">
                                    <h6 class="m-0">{{ $contact->name }}</h6>
                                    <small class="text-muted text-truncate d-block" style="max-width: 180px;">{{ $contact->email ?? $contact->whatsapp ?? 'Cliente' }}</small>
                                </div>
                                @if($contact->unread_count > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $contact->unread_count }}</span>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Chat Window (Direita) -->
            <div class="col app-chat-history bg-white d-flex flex-column" style="height: 600px;">
                @if($activeContact)
                    <!-- Header -->
                    <div class="chat-history-header border-bottom p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    @php
                                        $activeAvatar = ($chatType === 'client') ? "https://ui-avatars.com/api/?name=" . urlencode($activeContact->name) . "&color=7367f0&background=f8f7fa" : $activeContact->profile_photo_url;
                                    @endphp
                                    <img src="{{ $activeAvatar }}" alt="Avatar" class="rounded-circle" width="40">
                                </div>
                                <div>
                                    <h6 class="m-0">{{ $activeContact->name }}</h6>
                                    <span class="badge bg-label-{{ $chatType === 'client' ? 'primary' : 'success' }} small">{{ $chatType === 'client' ? 'Portal do Cliente' : 'Equipe' }}</span>
                                </div>
                            </div>
                            @if($chatType === 'user' && $activeContact->email == 'suporte@ghotme.com.br')
                                <a href="https://api.whatsapp.com/send?phone=5541991391687&text=Olá! Preciso de ajuda." target="_blank" class="btn btn-success btn-sm">
                                    <i class="ti tabler-brand-whatsapp me-1"></i> WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="chat-history-body flex-grow-1 p-4" id="chat-history-container" style="overflow-y: auto;" wire:poll.4s>
                        <ul class="list-unstyled chat-history">
                            @foreach($messages as $msg)
                            <li class="chat-message {{ ($msg->sender_id == auth()->id()) ? 'chat-message-right' : '' }} mb-4 d-flex {{ ($msg->sender_id == auth()->id()) ? 'justify-content-end' : '' }}">
                                <div class="chat-message-wrapper" style="max-width: 70%;">
                                    <div class="chat-message-text p-3 rounded shadow-sm {{ ($msg->sender_id == auth()->id()) ? 'bg-primary text-white' : 'bg-light text-dark' }}">
                                        @if($msg->attachment_path)
                                            <div class="mb-2">
                                                <a href="{{ asset('storage/' . $msg->attachment_path) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $msg->attachment_path) }}" class="img-fluid rounded" style="max-height: 250px;">
                                                </a>
                                            </div>
                                        @endif
                                        @if($msg->message)
                                            <p class="mb-0">{{ $msg->message }}</p>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1 {{ ($msg->sender_id == auth()->id()) ? 'text-end' : '' }}">
                                        {{ $msg->created_at->format('H:i') }}
                                    </small>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Footer / Input -->
                    <div class="chat-history-footer border-top p-4 bg-white position-relative">
                        @if ($attachment)
                        <div class="position-absolute bottom-100 start-0 mb-3 ms-3 p-2 bg-white rounded shadow border d-flex align-items-center gap-2" style="z-index: 10;">
                            <img src="{{ $attachment->temporaryUrl() }}" width="60" height="60" class="rounded object-fit-cover">
                            <button type="button" class="btn-close btn-sm" wire:click="$set('attachment', null)"></button>
                        </div>
                        @endif

                        <form wire:submit.prevent="sendMessage" class="d-flex align-items-center">
                            <div class="d-flex align-items-center">
                                <label for="chat-attachment" class="btn btn-icon btn-text-secondary rounded-pill me-1 cursor-pointer" title="Enviar Foto">
                                    <i class="ti tabler-paperclip"></i>
                                    <input type="file" id="chat-attachment" wire:model="attachment" class="d-none" accept="image/*">
                                </label>
                                
                                <button type="button" class="btn btn-icon btn-text-secondary rounded-pill me-2" id="emoji-btn" title="Emojis">
                                    <i class="ti tabler-mood-smile"></i>
                                </button>
                            </div>

                            <input type="text" id="message-input" wire:model="message" class="form-control border-0 shadow-none me-3" placeholder="Digite sua mensagem..." />
                            
                            <button type="submit" class="btn btn-primary d-flex align-items-center" wire:loading.attr="disabled">
                                <span class="d-none d-sm-inline">Enviar</span>
                                <i class="ti tabler-send ms-sm-2"></i>
                            </button>
                        </form>
                        
                        <!-- Emoji Picker Container -->
                        <div id="emoji-picker-container" class="position-absolute bottom-100 start-0 mb-2 d-none shadow-lg border rounded" style="z-index: 1000;">
                            <emoji-picker></emoji-picker>
                        </div>
                    </div>
                @else
                    <div class="d-flex h-100 align-items-center justify-content-center flex-column text-center p-5">
                        <div class="avatar avatar-xl bg-label-primary mb-4" style="width: 80px; height: 80px;">
                            <span class="avatar-initial rounded-circle"><i class="ti tabler-message-2 fs-1"></i></span>
                        </div>
                        <h4>Chat Ghotme</h4>
                        <p class="text-muted">Selecione alguém para conversar com segurança e rapidez.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @script
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1/index.js"></script>
    <script>
        const el = document.getElementById('chat-history-container');
        const scrollDown = () => { if (el) el.scrollTop = el.scrollHeight; };
        
        $wire.on('message-sent', () => { 
            setTimeout(scrollDown, 100); 
        });

        // Emoji Logic
        document.addEventListener('click', (e) => {
            const picker = document.getElementById('emoji-picker-container');
            const btn = document.getElementById('emoji-btn');
            const input = document.getElementById('message-input');
            const emojiElement = document.querySelector('emoji-picker');

            if (e.target.closest('#emoji-btn')) {
                picker.classList.toggle('d-none');
            } else if (picker && !picker.contains(e.target)) {
                picker.classList.add('d-none');
            }

            if (!emojiElement.dataset.initialized) {
                emojiElement.dataset.initialized = "true";
                emojiElement.addEventListener('emoji-click', event => {
                    const emoji = event.detail.unicode;
                    input.value += emoji;
                    input.dispatchEvent(new Event('input')); // Update Livewire model
                    picker.classList.add('d-none');
                    input.focus();
                });
            }
        });

        window.addEventListener('load', scrollDown);
    </script>
    @endscript
</div>
