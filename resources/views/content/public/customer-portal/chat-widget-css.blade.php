<style>
    /* Chat Widget Styles */
    .chat-widget {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .chat-button {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--portal-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px rgba(115, 103, 240, 0.4);
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }

    .chat-window {
        width: 350px;
        height: 450px;
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        margin-bottom: 1rem;
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .chat-header {
        background: var(--portal-primary);
        color: white;
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-body {
        flex-grow: 1;
        padding: 1rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        background: #fdfdff;
    }

    .chat-msg {
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        max-width: 85%;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .chat-msg-received {
        background: #f1f1f2;
        color: #444;
        align-self: flex-start;
        border-bottom-left-radius: 0.2rem;
    }

    .chat-msg-sent {
        background: var(--portal-primary);
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 0.2rem;
    }

    .chat-footer {
        padding: 1rem;
        border-top: 1px solid #eee;
        display: flex;
        gap: 0.5rem;
    }
</style>