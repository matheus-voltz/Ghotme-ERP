import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
    View, Text, FlatList, TextInput, TouchableOpacity,
    StyleSheet, KeyboardAvoidingView, Platform, Alert, ActivityIndicator
} from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import Animated, { FadeInDown, FadeIn } from 'react-native-reanimated';
import * as Haptics from 'expo-haptics';
import api from '../../services/api';
import { useTheme } from '../../context/ThemeContext';
import { useAuth } from '../../context/AuthContext';

interface ChatItem {
    id: number;
    title: string;
    updated_at: string;
}

interface Message {
    id: number;
    role: 'user' | 'assistant';
    content: string;
    created_at: string;
}

export default function AiConsultantScreen() {
    const router = useRouter();
    const { colors } = useTheme();
    const { user } = useAuth();
    const insets = useSafeAreaInsets();
    const flatListRef = useRef<FlatList>(null);

    const [chats, setChats] = useState<ChatItem[]>([]);
    const [activeChatId, setActiveChatId] = useState<number | null>(null);
    const [messages, setMessages] = useState<Message[]>([]);
    const [text, setText] = useState('');
    const [sending, setSending] = useState(false);
    const [aiThinking, setAiThinking] = useState(false);
    const [loadingChats, setLoadingChats] = useState(true);
    const pollingRef = useRef<ReturnType<typeof setInterval> | null>(null);

    // Carrega lista de chats ao montar
    useEffect(() => {
        fetchChats();
        return () => stopPolling();
    }, []);

    const fetchChats = async () => {
        try {
            const res = await api.get('/ai-consultant/chats');
            setChats(res.data);
            // Se tiver chats, abre o mais recente
            if (res.data.length > 0 && !activeChatId) {
                openChat(res.data[0].id);
            }
        } catch (e) {
            console.error('Erro ao carregar chats IA:', e);
        } finally {
            setLoadingChats(false);
        }
    };

    const openChat = async (chatId: number) => {
        setActiveChatId(chatId);
        try {
            const res = await api.get(`/ai-consultant/chats/${chatId}/messages`);
            setMessages(res.data);
            // Verifica se a última mensagem é do user (aguardando resposta)
            if (res.data.length > 0 && res.data[res.data.length - 1].role === 'user') {
                setAiThinking(true);
                startPolling(chatId);
            }
        } catch (e) {
            console.error('Erro ao carregar mensagens:', e);
        }
    };

    const createNewChat = async () => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
        try {
            const res = await api.post('/ai-consultant/chats');
            setChats(prev => [res.data, ...prev]);
            setActiveChatId(res.data.id);
            setMessages([]);
            setAiThinking(false);
            stopPolling();
        } catch (e) {
            Alert.alert('Erro', 'Não foi possível criar uma nova conversa.');
        }
    };

    const sendMessage = async () => {
        if (!text.trim() || sending || !activeChatId) return;

        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        setSending(true);

        const tempMsg: Message = {
            id: Date.now(),
            role: 'user',
            content: text.trim(),
            created_at: new Date().toISOString(),
        };

        setMessages(prev => [...prev, tempMsg]);
        setText('');
        setAiThinking(true);

        try {
            await api.post(`/ai-consultant/chats/${activeChatId}/send`, {
                message: tempMsg.content,
            });
            startPolling(activeChatId);
        } catch (e: any) {
            const errorMsg = e?.response?.data?.error || 'Falha ao enviar mensagem.';
            Alert.alert('Erro', errorMsg);
            setAiThinking(false);
        } finally {
            setSending(false);
        }
    };

    const startPolling = (chatId: number) => {
        stopPolling();
        pollingRef.current = setInterval(async () => {
            try {
                const res = await api.get(`/ai-consultant/chats/${chatId}/messages`);
                setMessages(res.data);
                // Se a última mensagem é da IA, para o polling
                if (res.data.length > 0 && res.data[res.data.length - 1].role === 'assistant') {
                    setAiThinking(false);
                    stopPolling();
                    Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
                }
            } catch (e) {
                console.log('Polling error:', e);
            }
        }, 3000);
    };

    const stopPolling = () => {
        if (pollingRef.current) {
            clearInterval(pollingRef.current);
            pollingRef.current = null;
        }
    };

    const renderMessage = useCallback(({ item }: { item: Message }) => {
        const isUser = item.role === 'user';
        return (
            <Animated.View entering={FadeIn.duration(300)}>
                <View style={[
                    styles.bubble,
                    isUser ? styles.bubbleUser : styles.bubbleAi,
                    { backgroundColor: isUser ? colors.primary : colors.card }
                ]}>
                    {!isUser && (
                        <View style={styles.aiLabel}>
                            <Ionicons name="sparkles" size={12} color="#CE9FFC" />
                            <Text style={styles.aiLabelText}>Ghotme IA</Text>
                        </View>
                    )}
                    <Text style={[styles.msgText, { color: isUser ? '#fff' : colors.text }]}>
                        {item.content}
                    </Text>
                    <Text style={[styles.timeText, { color: isUser ? 'rgba(255,255,255,0.6)' : colors.subText }]}>
                        {new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </Text>
                </View>
            </Animated.View>
        );
    }, [colors]);

    const renderThinkingBubble = () => (
        <Animated.View entering={FadeInDown.duration(400).springify()}>
            <View style={[styles.bubble, styles.bubbleAi, { backgroundColor: colors.card }]}>
                <View style={styles.aiLabel}>
                    <Ionicons name="sparkles" size={12} color="#CE9FFC" />
                    <Text style={styles.aiLabelText}>Ghotme IA</Text>
                </View>
                <View style={styles.thinkingRow}>
                    <ActivityIndicator size="small" color="#7367F0" />
                    <Text style={[styles.thinkingText, { color: colors.subText }]}>
                        Analisando seus dados...
                    </Text>
                </View>
            </View>
        </Animated.View>
    );

    // Tela de boas-vindas se não há chats
    if (loadingChats) {
        return (
            <View style={[styles.container, { backgroundColor: colors.background, justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#7367F0" />
            </View>
        );
    }

    return (
        <KeyboardAvoidingView
            behavior={Platform.OS === 'ios' ? 'padding' : undefined}
            style={[styles.container, { backgroundColor: colors.background }]}
        >
            {/* Header */}
            <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={[styles.header, { paddingTop: insets.top + 10 }]}
            >
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color="#fff" />
                </TouchableOpacity>
                <View style={styles.headerCenter}>
                    <Ionicons name="sparkles" size={20} color="#fff" style={{ marginRight: 8 }} />
                    <Text style={styles.headerTitle}>Ghotme IA</Text>
                </View>
                <TouchableOpacity onPress={createNewChat} style={styles.newChatBtn}>
                    <Ionicons name="add-circle-outline" size={28} color="#fff" />
                </TouchableOpacity>
            </LinearGradient>

            {/* Chat Tabs */}
            {chats.length > 1 && (
                <View style={[styles.chatTabs, { backgroundColor: colors.card }]}>
                    <FlatList
                        horizontal
                        data={chats}
                        showsHorizontalScrollIndicator={false}
                        keyExtractor={(item) => item.id.toString()}
                        contentContainerStyle={{ paddingHorizontal: 15 }}
                        renderItem={({ item }) => (
                            <TouchableOpacity
                                style={[
                                    styles.chatTab,
                                    item.id === activeChatId && styles.chatTabActive
                                ]}
                                onPress={() => openChat(item.id)}
                            >
                                <Text style={[
                                    styles.chatTabText,
                                    { color: item.id === activeChatId ? '#7367F0' : colors.subText }
                                ]} numberOfLines={1}>
                                    {item.title}
                                </Text>
                            </TouchableOpacity>
                        )}
                    />
                </View>
            )}

            {/* Mensagens */}
            {messages.length === 0 && !aiThinking ? (
                <View style={styles.emptyState}>
                    <View style={styles.emptyIconWrapper}>
                        <Ionicons name="sparkles" size={48} color="#CE9FFC" />
                    </View>
                    <Text style={[styles.emptyTitle, { color: colors.text }]}>Consultor Inteligente</Text>
                    <Text style={[styles.emptySubtitle, { color: colors.subText }]}>
                        Faça perguntas sobre o seu negócio.{'\n'}
                        A IA analisa seus dados reais de vendas, clientes e estoque.
                    </Text>
                    <View style={styles.suggestionsContainer}>
                        {[
                            'Como está meu faturamento este mês?',
                            'Quais clientes compraram mais?',
                            'Resumo de produtividade da equipe',
                        ].map((suggestion, idx) => (
                            <TouchableOpacity
                                key={idx}
                                style={[styles.suggestionChip, { backgroundColor: colors.card, borderColor: colors.border }]}
                                onPress={() => {
                                    setText(suggestion);
                                    Haptics.selectionAsync();
                                }}
                            >
                                <Ionicons name="chatbubble-outline" size={14} color="#7367F0" style={{ marginRight: 8 }} />
                                <Text style={[styles.suggestionText, { color: colors.text }]}>{suggestion}</Text>
                            </TouchableOpacity>
                        ))}
                    </View>
                </View>
            ) : (
                <FlatList
                    ref={flatListRef}
                    data={messages}
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={renderMessage}
                    contentContainerStyle={styles.messageList}
                    onContentSizeChange={() => flatListRef.current?.scrollToEnd({ animated: true })}
                    onLayout={() => flatListRef.current?.scrollToEnd({ animated: true })}
                    ListFooterComponent={aiThinking ? renderThinkingBubble : null}
                />
            )}

            {/* Input */}
            <View style={[styles.inputWrapper, { backgroundColor: colors.card, paddingBottom: insets.bottom + 10 }]}>
                <View style={styles.inputRow}>
                    <TextInput
                        style={[styles.input, { color: colors.text, backgroundColor: colors.background }]}
                        value={text}
                        onChangeText={setText}
                        placeholder="Pergunte algo à IA..."
                        placeholderTextColor={colors.subText}
                        multiline
                        editable={!sending}
                    />
                    <TouchableOpacity
                        onPress={activeChatId ? sendMessage : async () => { await createNewChat(); }}
                        style={[styles.sendBtn, { opacity: !text.trim() ? 0.5 : 1 }]}
                        disabled={!text.trim() || sending}
                    >
                        {sending ? (
                            <ActivityIndicator size="small" color="#fff" />
                        ) : (
                            <LinearGradient
                                colors={['#7367F0', '#CE9FFC']}
                                style={styles.sendGradient}
                            >
                                <Ionicons name="send" size={18} color="#fff" />
                            </LinearGradient>
                        )}
                    </TouchableOpacity>
                </View>
            </View>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingBottom: 15,
        paddingHorizontal: 15,
    },
    backBtn: { padding: 5 },
    headerCenter: { flexDirection: 'row', alignItems: 'center' },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#fff' },
    newChatBtn: { padding: 5 },

    chatTabs: { paddingVertical: 10 },
    chatTab: {
        paddingHorizontal: 16,
        paddingVertical: 8,
        borderRadius: 20,
        marginRight: 8,
        backgroundColor: 'transparent',
    },
    chatTabActive: {
        backgroundColor: '#7367F015',
        borderWidth: 1,
        borderColor: '#7367F040',
    },
    chatTabText: { fontSize: 13, fontWeight: '600', maxWidth: 120 },

    messageList: { padding: 15, paddingBottom: 20 },
    bubble: { maxWidth: '85%', padding: 14, borderRadius: 18, marginBottom: 10 },
    bubbleUser: { alignSelf: 'flex-end', borderBottomRightRadius: 4 },
    bubbleAi: { alignSelf: 'flex-start', borderBottomLeftRadius: 4 },
    aiLabel: { flexDirection: 'row', alignItems: 'center', marginBottom: 6 },
    aiLabelText: { fontSize: 11, fontWeight: '700', color: '#CE9FFC', marginLeft: 4 },
    msgText: { fontSize: 15, lineHeight: 22 },
    timeText: { fontSize: 10, alignSelf: 'flex-end', marginTop: 6 },

    thinkingRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    thinkingText: { fontSize: 14, fontStyle: 'italic' },

    inputWrapper: { borderTopWidth: 1, borderTopColor: '#eee' },
    inputRow: { flexDirection: 'row', padding: 10, alignItems: 'center' },
    input: {
        flex: 1, minHeight: 45, maxHeight: 100,
        borderRadius: 22, paddingHorizontal: 18, paddingVertical: 10, marginRight: 10,
    },
    sendBtn: {},
    sendGradient: {
        width: 45, height: 45, borderRadius: 22.5,
        justifyContent: 'center', alignItems: 'center',
    },

    emptyState: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 30 },
    emptyIconWrapper: {
        width: 90, height: 90, borderRadius: 45,
        backgroundColor: '#7367F015',
        justifyContent: 'center', alignItems: 'center',
        marginBottom: 20,
    },
    emptyTitle: { fontSize: 22, fontWeight: 'bold', marginBottom: 10 },
    emptySubtitle: { fontSize: 14, textAlign: 'center', lineHeight: 22, marginBottom: 25 },
    suggestionsContainer: { width: '100%' },
    suggestionChip: {
        flexDirection: 'row', alignItems: 'center',
        paddingHorizontal: 16, paddingVertical: 14,
        borderRadius: 14, borderWidth: 1, marginBottom: 10,
    },
    suggestionText: { fontSize: 14, flex: 1 },
});
