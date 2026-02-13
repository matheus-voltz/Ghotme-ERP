import React, { useState, useEffect, useRef } from 'react';
import { View, Text, FlatList, TextInput, TouchableOpacity, StyleSheet, KeyboardAvoidingView, Platform, Image } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useAuth } from '../../../context/AuthContext';
import { useTheme } from '../../../context/ThemeContext';

export default function ChatScreen() {
    const router = useRouter();
    const { userId, name, photo } = useLocalSearchParams();
    const { user } = useAuth();
    const { colors } = useTheme();
    
    const [messages, setMessages] = useState<any[]>([]);
    const [text, setText] = useState('');
    const flatListRef = useRef<FlatList>(null);

    useEffect(() => {
        fetchMessages();
        const interval = setInterval(fetchMessages, 5000); // Polling simples
        return () => clearInterval(interval);
    }, []);

    const fetchMessages = async () => {
        try {
            const response = await api.get(`/chat/messages/${userId}`);
            setMessages(response.data);
        } catch (error) {
            console.log(error);
        }
    };

    const sendMessage = async () => {
        if (!text.trim()) return;
        
        const tempMsg = {
            id: Date.now(),
            sender_id: user.id,
            message: text,
            created_at: new Date().toISOString()
        };

        setMessages(prev => [...prev, tempMsg]);
        setText('');
        
        try {
            await api.post('/chat/messages', { receiver_id: userId, message: tempMsg.message });
            fetchMessages(); // Sincroniza ID real
        } catch (error) {
            console.error("Erro ao enviar", error);
        }
    };

    const renderMessage = ({ item }: { item: any }) => {
        const isMe = item.sender_id === user.id;
        return (
            <View style={[
                styles.bubble, 
                isMe ? styles.bubbleMe : styles.bubbleOther,
                { backgroundColor: isMe ? colors.primary : colors.card }
            ]}>
                <Text style={[styles.msgText, { color: isMe ? '#fff' : colors.text }]}>{item.message}</Text>
                <Text style={[styles.timeText, { color: isMe ? 'rgba(255,255,255,0.7)' : colors.subText }]}>
                    {new Date(item.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </Text>
            </View>
        );
    };

    return (
        <KeyboardAvoidingView 
            behavior={Platform.OS === "ios" ? "padding" : "height"}
            style={[styles.container, { backgroundColor: colors.background }]}
        >
            <View style={[styles.header, { backgroundColor: colors.card }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={24} color={colors.text} />
                </TouchableOpacity>
                {photo ? (
                    <Image source={{ uri: photo as string }} style={styles.avatar} />
                ) : (
                    <View style={[styles.avatarPlaceholder, { backgroundColor: colors.primary }]}>
                        <Text style={styles.avatarText}>{(name as string)?.charAt(0)}</Text>
                    </View>
                )}
                <Text style={[styles.headerName, { color: colors.text }]}>{name}</Text>
            </View>

            <FlatList
                ref={flatListRef}
                data={messages}
                keyExtractor={(item) => item.id.toString()}
                renderItem={renderMessage}
                contentContainerStyle={styles.list}
                onContentSizeChange={() => flatListRef.current?.scrollToEnd()}
            />

            <View style={[styles.inputContainer, { backgroundColor: colors.card }]}>
                <TextInput
                    style={[styles.input, { color: colors.text, backgroundColor: colors.background }]}
                    value={text}
                    onChangeText={setText}
                    placeholder="Digite sua mensagem..."
                    placeholderTextColor={colors.subText}
                />
                <TouchableOpacity onPress={sendMessage} style={[styles.sendBtn, { backgroundColor: colors.primary }]}>
                    <Ionicons name="send" size={20} color="#fff" />
                </TouchableOpacity>
            </View>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: { flexDirection: 'row', alignItems: 'center', paddingTop: 50, paddingBottom: 15, paddingHorizontal: 15, elevation: 4 },
    backBtn: { marginRight: 10 },
    avatar: { width: 40, height: 40, borderRadius: 20, marginRight: 10 },
    avatarPlaceholder: { width: 40, height: 40, borderRadius: 20, justifyContent: 'center', alignItems: 'center', marginRight: 10 },
    avatarText: { color: '#fff', fontWeight: 'bold', fontSize: 18 },
    headerName: { fontSize: 18, fontWeight: 'bold' },
    list: { padding: 15, paddingBottom: 20 },
    bubble: { maxWidth: '80%', padding: 12, borderRadius: 16, marginBottom: 10 },
    bubbleMe: { alignSelf: 'flex-end', borderBottomRightRadius: 2 },
    bubbleOther: { alignSelf: 'flex-start', borderBottomLeftRadius: 2 },
    msgText: { fontSize: 16 },
    timeText: { fontSize: 10, alignSelf: 'flex-end', marginTop: 4 },
    inputContainer: { flexDirection: 'row', padding: 10, alignItems: 'center', borderTopWidth: 1, borderTopColor: '#eee' },
    input: { flex: 1, height: 45, borderRadius: 22, paddingHorizontal: 15, marginRight: 10 },
    sendBtn: { width: 45, height: 45, borderRadius: 22.5, justifyContent: 'center', alignItems: 'center' }
});