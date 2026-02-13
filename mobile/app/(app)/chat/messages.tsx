import React, { useEffect, useState, useRef } from 'react';
import { View, Text, TextInput, FlatList, TouchableOpacity, StyleSheet, ActivityIndicator, KeyboardAvoidingView, Platform, SafeAreaView } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '@/services/api';
import { useAuth } from '@/context/AuthContext';

export default function ChatMessagesScreen() {
    const { userId, userName } = useLocalSearchParams();
    const { user } = useAuth();
    const [messages, setMessages] = useState<any[]>([]);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(true);
    const flatListRef = useRef<FlatList>(null);
    const router = useRouter();

    useEffect(() => {
        fetchMessages();
        const interval = setInterval(fetchMessages, 3000); // Simple polling every 3s
        return () => clearInterval(interval);
    }, [userId]);

    const fetchMessages = async () => {
        try {
            const response = await api.get(`/chat/messages/${userId}`);
            setMessages(response.data);
            setLoading(false);
        } catch (error) {
            console.error("Error fetching messages:", error);
        }
    };

    const sendMessage = async () => {
        if (!newMessage.trim()) return;

        try {
            await api.post('/chat/messages', {
                receiver_id: userId,
                message: newMessage
            });
            setNewMessage('');
            fetchMessages(); // Refresh immediately
        } catch (error) {
            console.error("Error sending message:", error);
        }
    };

    const renderMessage = ({ item }: { item: any }) => {
        const isMe = item.sender_id === user?.id;
        return (
            <View style={[
                styles.messageBubble,
                isMe ? styles.myMessage : styles.otherMessage
            ]}>
                <Text style={[styles.messageText, isMe ? styles.myMessageText : styles.otherMessageText]}>
                    {item.message}
                </Text>
                <Text style={[styles.timestamp, isMe ? styles.myTimestamp : styles.otherTimestamp]}>
                    {new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </Text>
            </View>
        );
    };

    return (
        <SafeAreaView style={styles.container}>
            <View style={styles.header}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
                    <Ionicons name="arrow-back" size={24} color="#333" />
                </TouchableOpacity>
                <View style={styles.headerInfo}>
                    <Text style={styles.headerTitle}>{userName}</Text>
                    <Text style={styles.headerStatus}>Online</Text>
                </View>
            </View>

            {loading ? (
                <View style={styles.centered}>
                    <ActivityIndicator size="large" color="#7367F0" />
                </View>
            ) : (
                <FlatList
                    ref={flatListRef}
                    data={messages}
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={renderMessage}
                    contentContainerStyle={styles.messagesList}
                    onContentSizeChange={() => flatListRef.current?.scrollToEnd({ animated: true })}
                />
            )}

            <KeyboardAvoidingView
                behavior={Platform.OS === "ios" ? "padding" : "height"}
                keyboardVerticalOffset={Platform.OS === "ios" ? 100 : 0}
                style={styles.keyboardView}
            >
                <View style={styles.inputContainer}>
                    <TextInput
                        style={styles.input}
                        value={newMessage}
                        onChangeText={setNewMessage}
                        placeholder="Digite sua mensagem..."
                        placeholderTextColor="#999"
                        multiline
                    />
                    <TouchableOpacity style={styles.sendButton} onPress={sendMessage}>
                        <Ionicons name="send" size={20} color="#fff" />
                    </TouchableOpacity>
                </View>
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#fff',
    },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingVertical: 12,
        borderBottomWidth: 1,
        borderBottomColor: '#f0f0f0',
        backgroundColor: '#fff',
    },
    backButton: {
        marginRight: 16,
    },
    headerInfo: {
        flex: 1,
    },
    headerTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#333',
    },
    headerStatus: {
        fontSize: 12,
        color: '#28C76F',
    },
    centered: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
    messagesList: {
        padding: 16,
        paddingBottom: 20,
    },
    messageBubble: {
        maxWidth: '80%',
        padding: 12,
        borderRadius: 20,
        marginBottom: 8,
    },
    myMessage: {
        alignSelf: 'flex-end',
        backgroundColor: '#7367F0',
        borderBottomRightRadius: 4,
    },
    otherMessage: {
        alignSelf: 'flex-start',
        backgroundColor: '#f0f0f0',
        borderBottomLeftRadius: 4,
    },
    messageText: {
        fontSize: 16,
    },
    myMessageText: {
        color: '#fff',
    },
    otherMessageText: {
        color: '#333',
    },
    timestamp: {
        fontSize: 10,
        marginTop: 4,
        alignSelf: 'flex-end',
    },
    myTimestamp: {
        color: 'rgba(255,255,255,0.7)',
    },
    otherTimestamp: {
        color: '#999',
    },
    keyboardView: {
        borderTopWidth: 1,
        borderTopColor: '#f0f0f0',
    },
    inputContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 12,
        backgroundColor: '#fff',
    },
    input: {
        flex: 1,
        backgroundColor: '#f8f9fa',
        borderRadius: 20,
        paddingHorizontal: 16,
        paddingVertical: 10,
        maxHeight: 100,
        marginRight: 12,
        color: '#333',
    },
    sendButton: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: '#7367F0',
        justifyContent: 'center',
        alignItems: 'center',
        shadowColor: '#7367F0',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.3,
        shadowRadius: 4,
        elevation: 4,
    },
});
