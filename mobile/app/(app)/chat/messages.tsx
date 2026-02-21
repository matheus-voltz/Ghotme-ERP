import React, { useState, useEffect, useRef } from 'react';
import { View, Text, FlatList, TextInput, TouchableOpacity, StyleSheet, KeyboardAvoidingView, Platform, Alert, ActivityIndicator } from 'react-native';
import LottieView from 'lottie-react-native';
import { Image } from 'expo-image';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useAuth } from '../../../context/AuthContext';
import { useTheme } from '../../../context/ThemeContext';
import { useChat } from '../../../context/ChatContext';
import * as ImagePicker from 'expo-image-picker';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

export default function ChatScreen() {
    const router = useRouter();
    const { userId, name, photo } = useLocalSearchParams();
    const { user } = useAuth();
    const { colors } = useTheme();
    const { refreshUnreadCount } = useChat();
    const insets = useSafeAreaInsets();

    const [messages, setMessages] = useState<any[]>([]);
    const [text, setText] = useState('');
    const [selectedImage, setSelectedImage] = useState<string | null>(null);
    const [sending, setSending] = useState(false);
    const [showAirplane, setShowAirplane] = useState(false);
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
            refreshUnreadCount();
        } catch (error) {
            console.log(error);
        }
    };

    const pickImage = async () => {
        try {
            const result = await ImagePicker.launchImageLibraryAsync({
                mediaTypes: ImagePicker.MediaTypeOptions.Images,
                allowsEditing: true,
                quality: 0.7,
            });

            if (!result.canceled) {
                setSelectedImage(result.assets[0].uri);
            }
        } catch (error) {
            Alert.alert('Erro', 'Não foi possível selecionar a imagem.');
        }
    };

    const sendMessage = async () => {
        if ((!text.trim() && !selectedImage) || sending) return;

        setSending(true);
        setShowAirplane(true);
        setTimeout(() => setShowAirplane(false), 1500);

        const tempMsg = {
            id: Date.now(),
            sender_id: user.id,
            message: text,
            attachment_path: selectedImage ? 'temp' : null, // Marker for UI
            temp_image: selectedImage,
            created_at: new Date().toISOString()
        };

        setMessages(prev => [...prev, tempMsg]);
        setText('');
        setSelectedImage(null);

        try {
            const formData = new FormData();
            formData.append('receiver_id', userId as string);
            formData.append('message', text); // Send empty string if just image

            if (selectedImage) {
                const filename = selectedImage.split('/').pop() || 'image.jpg';
                const match = /\.(\w+)$/.exec(filename);
                const type = match ? `image/${match[1]}` : 'image/jpeg';

                // @ts-ignore
                formData.append('image', { uri: selectedImage, name: filename, type });
            }

            // We need to remove the 'Content-Type' header so axios/browser can set the boundary
            // This is a bit tricky with configured instance. 
            // In RN usually passing form data works.
            await api.post('/chat/messages', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                }
            });
            fetchMessages(); // Sincroniza ID real e imagem real
        } catch (error) {
            console.error("Erro ao enviar", error);
            Alert.alert('Erro', 'Falha ao enviar mensagem.');
        } finally {
            setSending(false);
        }
    };

    const renderMessage = ({ item }: { item: any }) => {
        const isMe = item.sender_id === user.id;

        // Resolve attachment URL
        let attachmentUrl = null;
        if (item.temp_image) {
            attachmentUrl = item.temp_image;
        } else if (item.attachment_path) {
            // Get the base URL from the API instance dynamically
            const baseUrl = api.defaults.baseURL?.split('/api')[0];
            attachmentUrl = `${baseUrl}/storage/${item.attachment_path}`;
        }

        return (
            <View style={[
                styles.bubble,
                isMe ? styles.bubbleMe : styles.bubbleOther,
                { backgroundColor: isMe ? colors.primary : colors.card }
            ]}>
                {attachmentUrl && (
                    <Image
                        source={{ uri: attachmentUrl }}
                        style={styles.messageImage}
                        contentFit="cover"
                    />
                )}
                {!!item.message && (
                    <Text style={[styles.msgText, { color: isMe ? '#fff' : colors.text }]}>{item.message}</Text>
                )}
                <Text style={[styles.timeText, { color: isMe ? 'rgba(255,255,255,0.7)' : colors.subText }]}>
                    {new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </Text>
            </View>
        );
    };

    return (
        <KeyboardAvoidingView
            behavior={Platform.OS === "ios" ? "padding" : undefined}
            style={[styles.container, { backgroundColor: colors.background }]}
            keyboardVerticalOffset={Platform.OS === "ios" ? 0 : 0}
        >
            <View style={[styles.header, { backgroundColor: colors.card, paddingTop: insets.top + 10 }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={24} color={colors.text} />
                </TouchableOpacity>
                {photo ? (
                    <Image
                        source={{ uri: photo as string }}
                        style={styles.avatar}
                        contentFit="cover"
                    />
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
                onContentSizeChange={() => flatListRef.current?.scrollToEnd({ animated: true })}
                onLayout={() => flatListRef.current?.scrollToEnd({ animated: true })}
            />

            <View style={[styles.inputWrapper, { backgroundColor: colors.card, paddingBottom: insets.bottom + 10 }]}>
                {selectedImage && (
                    <View style={styles.previewContainer}>
                        <Image
                            source={{ uri: selectedImage }}
                            style={styles.previewImage}
                            contentFit="cover"
                        />
                        <TouchableOpacity style={styles.removePreviewBtn} onPress={() => setSelectedImage(null)}>
                            <Ionicons name="close-circle" size={24} color="#fff" />
                        </TouchableOpacity>
                    </View>
                )}

                <View style={styles.inputRow}>
                    <TouchableOpacity style={styles.iconBtn} onPress={pickImage}>
                        <Ionicons name="images-outline" size={24} color={colors.primary} />
                    </TouchableOpacity>

                    <TouchableOpacity style={styles.iconBtn} onPress={() => { /* Abre teclado emoji nativo? O input ja suporta. */ }}>
                        <Ionicons name="happy-outline" size={24} color={colors.primary} />
                    </TouchableOpacity>

                    <TextInput
                        style={[styles.input, { color: colors.text, backgroundColor: colors.background }]}
                        value={text}
                        onChangeText={setText}
                        placeholder="Digite sua mensagem..."
                        placeholderTextColor={colors.subText}
                        multiline
                    />

                    <TouchableOpacity
                        onPress={sendMessage}
                        style={[styles.sendBtn, { backgroundColor: colors.primary, opacity: (!text.trim() && !selectedImage) ? 0.5 : 1 }]}
                        disabled={!text.trim() && !selectedImage}
                    >
                        {sending ? (
                            <ActivityIndicator size="small" color="#fff" />
                        ) : (
                            <Ionicons name="send" size={20} color="#fff" />
                        )}
                    </TouchableOpacity>
                </View>
            </View>

            {showAirplane && (
                <View style={styles.airplaneOverlay} pointerEvents="none">
                    <LottieView
                        source={{ uri: 'https://assets9.lottiefiles.com/packages/lf20_mkmfclal.json' }}
                        autoPlay
                        loop={false}
                        style={styles.airplaneLottie}
                    />
                </View>
            )}
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: { flexDirection: 'row', alignItems: 'center', paddingBottom: 15, paddingHorizontal: 15, elevation: 4 },
    backBtn: { marginRight: 10 },
    avatar: { width: 40, height: 40, borderRadius: 20, marginRight: 10, alignSelf: 'center' },
    avatarPlaceholder: { width: 40, height: 40, borderRadius: 20, justifyContent: 'center', alignItems: 'center', marginRight: 10, alignSelf: 'center' },
    avatarText: { color: '#fff', fontWeight: 'bold', fontSize: 18 },
    headerName: { fontSize: 18, fontWeight: 'bold' },
    list: { padding: 15, paddingBottom: 20 },
    bubble: { maxWidth: '80%', padding: 12, borderRadius: 16, marginBottom: 10 },
    bubbleMe: { alignSelf: 'flex-end', borderBottomRightRadius: 2 },
    bubbleOther: { alignSelf: 'flex-start', borderBottomLeftRadius: 2 },
    msgText: { fontSize: 16 },
    timeText: { fontSize: 10, alignSelf: 'flex-end', marginTop: 4 },
    messageImage: { width: 200, height: 150, borderRadius: 10, marginBottom: 5 },

    inputWrapper: { borderTopWidth: 1, borderTopColor: '#eee' },
    inputRow: { flexDirection: 'row', padding: 10, alignItems: 'center' },
    input: { flex: 1, minHeight: 45, maxHeight: 100, borderRadius: 22, paddingHorizontal: 15, paddingVertical: 10, marginRight: 10 },
    sendBtn: { width: 45, height: 45, borderRadius: 22.5, justifyContent: 'center', alignItems: 'center' },
    iconBtn: { padding: 8, marginRight: 5 },

    previewContainer: { padding: 10, flexDirection: 'row', alignItems: 'center' },
    previewImage: { width: 80, height: 80, borderRadius: 10, marginRight: 10 },
    removePreviewBtn: { position: 'absolute', top: 5, left: 80, backgroundColor: 'rgba(0,0,0,0.5)', borderRadius: 12 },

    airplaneOverlay: {
        ...StyleSheet.absoluteFillObject,
        justifyContent: 'center',
        alignItems: 'center',
        zIndex: 999,
        backgroundColor: 'transparent'
    },
    airplaneLottie: {
        width: 300,
        height: 300
    }
});