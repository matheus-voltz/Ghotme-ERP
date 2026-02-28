@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Consultor IA - Ghotme')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('page-style')
<style>
    .chat-container {
        height: calc(100vh - 250px);
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        overflow: hidden;
    }

    .chat-history {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        background: #f8f9fa;
    }

    .chat-message {
        margin-bottom: 1rem;
        max-width: 80%;
        padding: 1rem;
        border-radius: 1rem;
        position: relative;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .chat-message.user {
        align-self: flex-end;
        background-color: var(--bs-primary);
        color: #fff;
        border-bottom-right-radius: 0.25rem;
        margin-left: auto;
    }

    .chat-message.assistant {
        align-self: flex-start;
        background-color: #fff;
        color: #333;
        border-bottom-left-radius: 0.25rem;
        border: 1px solid #e0e0e0;
    }

    .chat-footer {
        padding: 1rem;
        background: #fff;
        border-top: 1px solid #e0e0e0;
    }

    .typing-indicator {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        color: #666;
        display: none;
    }

    .sidebar-chats {
        border-right: 1px solid #e0e0e0;
        height: calc(100vh - 250px);
        overflow-y: auto;
    }

    .chat-item {
        cursor: pointer;
        transition: background 0.2s;
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .chat-item:hover,
    .chat-item.active {
        background: #f0f2f5;
    }

    .chat-item.active {
        border-left: 4px solid var(--bs-primary);
    }
</style>
@endsection

@section('content')
<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Consultas</h5>
                <a href="{{ route('ai-consultant.create') }}" class="btn btn-sm btn-primary">
                    <i class="ti tabler-plus me-1"></i> Nova
                </a>
            </div>
            <div class="sidebar-chats">
                @foreach($chats as $c)
                <div onclick="window.location='{{ route('ai-consultant.show', $c->id) }}'" class="chat-item {{ $c->id == $chat->id ? 'active' : '' }}">
                    <div class="fw-bold text-truncate">{{ $c->title }}</div>
                    <small class="text-muted">{{ $c->updated_at->diffForHumans() }}</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="col-md-9">
        <div class="chat-container">
            <div class="card-header border-bottom bg-white d-flex align-items-center p-3">
                <div class="avatar avatar-md me-3">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                        <i class="ti tabler-robot fs-3"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0">Consultor Estratégico Ghotme</h6>
                    <small class="text-success">Online agora</small>
                </div>
            </div>

            <div class="chat-history d-flex flex-column" id="chat-history">
                @foreach($messages as $msg)
                <div class="chat-message {{ $msg->role }}">
                    {!! $msg->content !!}
                </div>
                @endforeach
            </div>

            <div id="typing-indicator" class="typing-indicator animate__animated animate__fadeIn">
                <span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
                O Consultor está analisando seus dados...
            </div>

            <div class="chat-footer">
                <form id="chat-form" class="d-flex align-items-center">
                    @csrf
                    <input type="text" id="chat-input" class="form-control message-input border-0 shadow-none me-3" placeholder="Digite sua dúvida sobre o negócio..." autocomplete="off">
                    <button type="submit" class="btn btn-primary d-flex align-items-center" id="btn-send">
                        <i class="ti tabler-send"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const history = document.getElementById('chat-history');
        const form = document.getElementById('chat-form');
        const input = document.getElementById('chat-input');
        const btnSend = document.getElementById('btn-send');
        const typing = document.getElementById('typing-indicator');

        const scrollToBottom = () => {
            history.scrollTop = history.scrollHeight;
        };

        scrollToBottom();

        const formatContent = (text) => {
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
        };

        // Formatar mensagens existentes
        document.querySelectorAll('.chat-message.assistant').forEach(msg => {
            // Já vem como HTML do backend ou a gente formata aqui se for raw
            // msg.innerHTML = formatContent(msg.innerText);
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = input.value.trim();
            if (!message) return;

            // Add user message to UI
            const userDiv = document.createElement('div');
            userDiv.className = 'chat-message user animate__animated animate__fadeInRight';
            userDiv.textContent = message;
            history.appendChild(userDiv);

            input.value = '';
            scrollToBottom();

            // Show typing
            typing.style.display = 'block';
            btnSend.disabled = true;

            fetch('{{ route("ai-consultant.send", $chat->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: message
                    })
                })
                .then(res => res.json())
                .then(data => {
                    typing.style.display = 'none';
                    btnSend.disabled = false;

                    if (data.success) {
                        const aiDiv = document.createElement('div');
                        aiDiv.className = 'chat-message assistant animate__animated animate__fadeInLeft';
                        aiDiv.innerHTML = formatContent(data.message);
                        history.appendChild(aiDiv);
                        scrollToBottom();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ops!',
                            text: data.message || 'Erro ao processar sua mensagem.'
                        });
                    }
                })
                .catch(err => {
                    typing.style.display = 'none';
                    btnSend.disabled = false;
                    console.error(err);
                });
        });
    });
</script>
@endsection