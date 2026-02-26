<div class="chat-widget">
    <div class="chat-window" id="chatWindow">
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($responsible->name ?? 'Suporte') }}&color=7367f0&background=fff" class="rounded-circle" width="30">
                </div>
                <div>
                    <h6 class="mb-0 text-white">{{ $responsible->name ?? 'Suporte' }}</h6>
                    <small style="font-size: 0.7rem; opacity: 0.8;">Atendimento Online</small>
                </div>
            </div>
            <button class="btn-close btn-close-white" onclick="toggleChat()"></button>
        </div>
        <div class="chat-body" id="chatBody">
            <div class="chat-msg chat-msg-received">
                Olá! Como podemos ajudar você hoje?
            </div>
            @foreach($messages as $msg)
            <div class="chat-msg {{ $msg->sender_id ? 'chat-msg-received' : 'chat-msg-sent' }}">
                {{ $msg->message }}
            </div>
            @endforeach
        </div>
        <div class="chat-footer">
            <input type="text" id="portalChatMessage" class="form-control border-0 bg-light" placeholder="Digite sua mensagem...">
            <button class="btn btn-primary btn-icon rounded-circle" id="btnPortalSend">
                <i class="ti tabler-send"></i>
            </button>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $client->company->phone ?? '') }}" target="_blank" class="chat-button bg-success shadow-none" style="width: 50px; height: 50px;">
            <i class="ti tabler-brand-whatsapp fs-3"></i>
        </a>
        <button class="chat-button" onclick="toggleChat()">
            <div class="position-relative">
                <i class="ti tabler-message fs-2" id="chatIcon"></i>
                <span id="chatBadge" class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle p-1 d-none" style="border: 2px solid white;">
                    <span class="visually-hidden">Nova mensagem</span>
                </span>
            </div>
        </button>
    </div>
</div>

<audio id="chatSound" src="https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3" preload="auto"></audio>

<script>
    let lastMsgCount = 0;

    function toggleChat() {
        const win = document.getElementById('chatWindow');
        const icon = document.getElementById('chatIcon');
        const badge = document.getElementById('chatBadge');

        if (win.style.display === 'flex') {
            win.style.display = 'none';
            icon.className = 'ti tabler-message fs-2';
        } else {
            win.style.display = 'flex';
            icon.className = 'ti tabler-x fs-2';
            badge.classList.add('d-none'); // Limpa badge ao abrir
            const body = document.getElementById('chatBody');
            body.scrollTop = body.scrollHeight;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnSend = document.getElementById('btnPortalSend');
        const input = document.getElementById('portalChatMessage');
        const chatBody = document.getElementById('chatBody');
        const chatWindow = document.getElementById('chatWindow');
        const badge = document.getElementById('chatBadge');
        const sound = document.getElementById('chatSound');

        lastMsgCount = chatBody.querySelectorAll('.chat-msg').length - 1;

        function appendMessage(msg, type) {
            const div = document.createElement('div');
            div.className = `chat-msg chat-msg-${type}`;
            div.textContent = msg;
            chatBody.appendChild(div);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        async function send() {
            const message = input.value.trim();
            if (!message) return;

            input.value = '';
            appendMessage(message, 'sent');
            lastMsgCount++;

            try {
                const response = await fetch("{{ route('customer.portal.send-message', $client->uuid) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: message
                    })
                });
            } catch (err) {
                console.error('Erro ao enviar mensagem');
            }
        }

        btnSend.onclick = send;
        input.onkeypress = (e) => {
            if (e.key === 'Enter') send();
        };

        // Polling de mensagens a cada 5 segundos
        async function fetchMessages() {
            try {
                const response = await fetch("{{ route('customer.portal.fetch-messages', $client->uuid) }}");
                const messages = await response.json();

                const serverCount = messages.length;
                console.log('Messages from server:', serverCount, 'Current local count:', lastMsgCount);

                if (serverCount > lastMsgCount) {
                    const welcomeMsgHtml = chatBody.querySelector('.chat-msg-received').outerHTML;
                    chatBody.innerHTML = welcomeMsgHtml;

                    messages.forEach(msg => {
                        const div = document.createElement('div');
                        div.className = `chat-msg ${msg.sender_id ? 'chat-msg-received' : 'chat-msg-sent'}`;
                        div.textContent = msg.message;
                        chatBody.appendChild(div);
                    });

                    const lastMsg = messages[messages.length - 1];
                    if (lastMsg.sender_id) {
                        console.log('New message from staff! Playing sound...');
                        sound.play().catch(e => console.error('Sound error:', e));

                        if (chatWindow.style.display !== 'flex') {
                            badge.classList.remove('d-none');
                        }
                    }

                    chatBody.scrollTop = chatBody.scrollHeight;
                    lastMsgCount = serverCount;
                }
            } catch (err) {
                console.error('Erro ao buscar mensagens:', err);
            }
        }

        setInterval(fetchMessages, 5000);
        chatBody.scrollTop = chatBody.scrollHeight;
    });
</script>