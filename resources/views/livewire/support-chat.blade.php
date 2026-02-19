<div>
    <div class="app-chat card overflow-hidden">
        <div class="row g-0">
            <!-- Sidebar Left -->
            <div class="col app-chat-sidebar-left app-sidebar overflow-hidden" id="app-chat-sidebar-left">
                <div class="chat-sidebar-left-user sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap px-6 pt-12">
                    <div class="avatar avatar-xl avatar-{{ $userStatus }} chat-sidebar-avatar">
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="Avatar" class="rounded-circle" />
                    </div>
                    <h5 class="mt-4 mb-0">{{ auth()->user()->name }}</h5>
                    <span>{{ auth()->user()->email }}</span>
                    <i class="icon-base ti tabler-x icon-lg cursor-pointer close-sidebar" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-left"></i>
                </div>
                <div class="sidebar-body px-6 pb-6">
                    <div class="my-6">
                        <p class="text-uppercase text-body-secondary mb-1">Status</p>
                        <div class="d-grid gap-2 pt-2 text-heading ms-2">
                            <div class="form-check form-check-success">
                                <input name="chat-user-status" class="form-check-input" type="radio" value="online" id="user-online" wire:click="updateStatus('online')" {{ $userStatus == 'online' ? 'checked' : '' }} />
                                <label class="form-check-label" for="user-online">Online</label>
                            </div>
                            <div class="form-check form-check-warning">
                                <input name="chat-user-status" class="form-check-input" type="radio" value="away" id="user-away" wire:click="updateStatus('away')" {{ $userStatus == 'away' ? 'checked' : '' }} />
                                <label class="form-check-label" for="user-away">Ausente</label>
                            </div>
                            <div class="form-check form-check-danger">
                                <input name="chat-user-status" class="form-check-input" type="radio" value="busy" id="user-busy" wire:click="updateStatus('busy')" {{ $userStatus == 'busy' ? 'checked' : '' }} />
                                <label class="form-check-label" for="user-busy">Não Perturbe</label>
                            </div>
                            <div class="form-check form-check-secondary">
                                <input name="chat-user-status" class="form-check-input" type="radio" value="offline" id="user-offline" wire:click="updateStatus('offline')" {{ $userStatus == 'offline' ? 'checked' : '' }} />
                                <label class="form-check-label" for="user-offline">Offline</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Sidebar Left-->

            <!-- Chat & Contacts -->
            <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts">
                <div class="sidebar-header h-px-75 px-5 border-bottom d-flex align-items-center">
                    <div class="d-flex align-items-center me-6 me-lg-0">
                        <div class="flex-shrink-0 avatar avatar-{{ $userStatus }} me-4" data-bs-toggle="sidebar" data-overlay="app-overlay-ex" data-target="#app-chat-sidebar-left">
                            <img class="user-avatar rounded-circle cursor-pointer" src="{{ auth()->user()->profile_photo_url }}" alt="Avatar" />
                        </div>
                        <div class="flex-grow-1 input-group input-group-merge">
                            <span class="input-group-text" id="basic-addon-search31"><i class="icon-base ti tabler-search icon-xs"></i></span>
                            <input type="text" class="form-control chat-search-input" wire:model.live="search" placeholder="Buscar usuário..." />
                        </div>
                    </div>
                    <i class="icon-base ti tabler-x icon-lg cursor-pointer position-absolute top-50 end-0 translate-middle d-lg-none d-block" data-overlay data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>
                </div>
                <div class="sidebar-body">
                    <!-- Tabs -->
                    <div class="d-flex justify-content-between px-3 py-2 border-bottom">
                        <button class="btn btn-sm btn-icon rounded-pill {{ $activeTab == 'team' ? 'btn-primary' : 'btn-text-secondary' }}" wire:click="setTab('team')" title="Equipe Interna">
                            <i class="ti tabler-users"></i>
                        </button>
                        <button class="btn btn-sm btn-icon rounded-pill {{ $activeTab == 'support' ? 'btn-primary' : 'btn-text-secondary' }}" wire:click="setTab('support')" title="Suporte Ghotme">
                            <i class="ti tabler-headset"></i>
                        </button>
                        <button class="btn btn-sm btn-icon rounded-pill {{ $activeTab == 'clients' ? 'btn-primary' : 'btn-text-secondary' }}" wire:click="setTab('clients')" title="Clientes (CRM)">
                            <i class="ti tabler-brand-whatsapp"></i>
                        </button>
                    </div>

                    <!-- Chats -->
                    <ul class="list-unstyled chat-contact-list py-2 mb-0" id="chat-list">
                        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
                            <h5 class="text-primary mb-0">
                                @if($activeTab == 'team') Minha Equipe ({{ $teamCount }})
                                @elseif($activeTab == 'support') Suporte Ghotme ({{ $supportCount }})
                                @else Clientes ({{ $clientCount }}) @endif
                            </h5>
                        </li>

                        @foreach($contacts as $contact)
                        <li class="chat-contact-list-item {{ $activeUserId == $contact->id ? 'active' : '' }} mb-1" wire:key="contact-{{ $contact->id }}">
                            <a class="d-flex align-items-center" wire:click.prevent="selectUser({{ $contact->id }})" style="cursor: pointer;">
                                <div class="flex-shrink-0 avatar avatar-{{ $contact->chat_status }}">
                                    <img src="{{ $contact->profile_photo_url }}" alt="Avatar" class="rounded-circle" />
                                </div>
                                <div class="chat-contact-info flex-grow-1 ms-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="chat-contact-name text-truncate m-0 fw-normal">{{ $contact->name }}</h6>
                                    </div>
                                    <small class="chat-contact-status text-truncate">{{ $contact->email }}</small>
                                </div>
                            </a>
                        </li>
                        @endforeach

                        @if($contacts->isEmpty())
                        <li class="chat-contact-list-item chat-list-item-0">
                            <h6 class="text-body-secondary mb-0">Nenhum contato encontrado</h6>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            <!-- /Chat contacts -->

            <!-- Chat conversation -->
            @if(!$activeUser)
            <div class="col app-chat-conversation d-flex align-items-center justify-content-center flex-column" id="app-chat-conversation">
                <div class="bg-label-primary p-8 rounded-circle">
                    <i class="icon-base ti tabler-message-2 icon-50px"></i>
                </div>
                <p class="my-4">Selecione um contato para iniciar.</p>
            </div>
            @else
            <!-- Chat History -->
            <div class="col app-chat-history" id="app-chat-history">
                <div class="chat-history-wrapper">
                    <div class="chat-history-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex overflow-hidden align-items-center">
                                <i class="icon-base ti tabler-menu-2 icon-lg cursor-pointer d-lg-none d-block me-4" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-contacts"></i>
                                <div class="flex-shrink-0 avatar avatar-{{ $activeUser->chat_status }}">
                                    <img src="{{ $activeUser->profile_photo_url }}" alt="Avatar" class="rounded-circle" />
                                </div>
                                <div class="chat-contact-info flex-grow-1 ms-4">
                                    <h6 class="m-0 fw-normal">{{ $activeUser->name }}</h6>
                                    <small class="user-status text-body">{{ ucfirst($activeUser->chat_status == 'online' ? 'online' : ($activeUser->chat_status == 'away' ? 'ausente' : ($activeUser->chat_status == 'busy' ? 'não perturbe' : 'offline'))) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="chat-history-body" id="chat-history-container" style="height: 500px; overflow-y: auto;" wire:poll.2000ms>
                        <ul class="list-unstyled chat-history">
                            @foreach($messages as $msg)
                            <li class="chat-message {{ $msg->sender_id == auth()->id() ? 'chat-message-right' : '' }}" wire:key="msg-{{ $msg->id }}">
                                <div class="d-flex overflow-hidden">
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            @if($msg->attachment_path)
                                            <div class="mb-2 position-relative">
                                                <img src="{{ asset('storage/' . $msg->attachment_path) }}" class="img-fluid rounded" style="max-width: 200px;" alt="Anexo" />
                                            </div>
                                            @endif
                                            @if($msg->message)
                                            <p class="mb-0">{{ $msg->message }}</p>
                                            @endif
                                        </div>
                                        <div class="text-end text-body-secondary mt-1">
                                            <small>{{ $msg->created_at->format('H:i') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Chat message form -->
                    <div class="chat-history-footer shadow-xs position-relative">
                        @if ($attachment)
                        <div class="position-absolute bottom-100 start-0 mb-3 ms-3 p-2 bg-white rounded shadow-sm border d-flex align-items-center gap-2">
                            <img src="{{ $attachment->temporaryUrl() }}" width="60" height="60" class="rounded object-fit-cover">
                            <button type="button" class="btn-close btn-sm" wire:click="$set('attachment', null)"></button>
                        </div>
                        @endif

                        <form wire:submit.prevent="sendMessage" class="form-send-message d-flex justify-content-between align-items-center ">
                            <div class="me-2">
                                <label for="chat-attachment" class="btn btn-icon btn-text-secondary rounded-pill cursor-pointer" title="Anexar Imagem">
                                    <i class="ti tabler-paperclip"></i>
                                </label>
                                <input type="file" id="chat-attachment" wire:model="attachment" class="d-none" accept="image/*">
                            </div>

                            <input wire:model="message" class="form-control message-input border-0 me-4 shadow-none" placeholder="Digite sua mensagem..." />

                            <div class="message-actions d-flex align-items-center">
                                <div class="position-relative">
                                    <button type="button" class="btn btn-icon btn-text-secondary rounded-pill me-2" id="emoji-picker-btn" title="Emojis">
                                        <i class="ti tabler-mood-smile"></i>
                                    </button>
                                    <div id="emoji-picker-container" class="position-absolute bottom-100 start-0 mb-3 d-none shadow-lg border rounded" style="z-index: 1000;">
                                        <emoji-picker></emoji-picker>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary d-flex send-msg-btn" wire:loading.attr="disabled">
                                    <span class="align-middle d-md-inline-block d-none">Enviar</span>
                                    <i class="icon-base ti tabler-send icon-16px ms-md-2 ms-0"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
            <!-- /Chat History -->

            <div class="app-overlay"></div>
        </div>
    </div>

    @script
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1/index.js"></script>
    <script>
        const el = document.getElementById('chat-history-container');

        const scrollDown = () => {
            if (el) el.scrollTop = el.scrollHeight;
        };

        function initEmojiPicker() {
            const emojiBtn = document.getElementById('emoji-picker-btn');
            const emojiContainer = document.getElementById('emoji-picker-container');
            const messageInput = document.querySelector('.message-input');
            const picker = document.querySelector('emoji-picker');

            if (emojiBtn && !emojiBtn.dataset.initialized) {
                emojiBtn.dataset.initialized = "true";
                emojiBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    emojiContainer.classList.toggle('d-none');
                });
                document.addEventListener('click', (e) => {
                    if (emojiContainer && !emojiContainer.contains(e.target) && e.target !== emojiBtn) {
                        emojiContainer.classList.add('d-none');
                    }
                });
                picker?.addEventListener('emoji-click', event => {
                    const emoji = event.detail.unicode;
                    const start = messageInput.selectionStart;
                    const end = messageInput.selectionEnd;
                    const text = messageInput.value;
                    const before = text.substring(0, start);
                    const after = text.substring(end, text.length);
                    messageInput.value = before + emoji + after;
                    messageInput.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    emojiContainer.classList.add('d-none');
                    messageInput.focus();
                });
            }
        }

        setTimeout(() => {
            scrollDown();
            initEmojiPicker();
        }, 100);

        $wire.on('message-sent', () => {
            setTimeout(scrollDown, 50);
        });

        setInterval(() => {
            if (el) {
                const isNearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 100;
                if (isNearBottom) scrollDown();
            }
        }, 2000);

        Livewire.hook('morph.updated', ({
            el: element,
            component
        }) => {
            if (component.name === 'support-chat') {
                initEmojiPicker();
            }
        });
    </script>
    @endscript
</div>
