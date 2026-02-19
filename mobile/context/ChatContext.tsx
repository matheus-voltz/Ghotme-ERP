import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import api from '../services/api';
import { useAuth } from './AuthContext';

interface ChatContextData {
    unreadCount: number;
    refreshUnreadCount: () => Promise<void>;
}

const ChatContext = createContext<ChatContextData>({} as ChatContextData);

export const ChatProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const { user } = useAuth();
    const [unreadCount, setUnreadCount] = useState(0);

    const refreshUnreadCount = useCallback(async () => {
        if (!user) return;
        try {
            // Tenta buscar contagem real
            // Assumindo que o endpoint retorna { count: number }
            const response = await api.get('/chat/unread-count');
            setUnreadCount(response.data.count || 0);
        } catch (error) {
            // Fallback: tentar pegar dos contatos se o endpoint específico não existir
            try {
                const response = await api.get('/chat/contacts');
                if (Array.isArray(response.data)) {
                    const count = response.data.reduce((acc: number, contact: any) => acc + (contact.unread_count || 0), 0);
                    setUnreadCount(count);
                }
            } catch (e) {
                console.log('Chat stats error', e);
            }
        }
    }, [user]);

    useEffect(() => {
        if (user) {
            refreshUnreadCount();
            const interval = setInterval(refreshUnreadCount, 15000); // Poll every 15s
            return () => clearInterval(interval);
        }
    }, [user]);

    return (
        <ChatContext.Provider value={{ unreadCount, refreshUnreadCount }}>
            {children}
        </ChatContext.Provider>
    );
};

export const useChat = () => useContext(ChatContext);
