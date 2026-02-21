import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { useTheme } from '../../../context/ThemeContext';
import api from '../../../services/api';

export default function NotificationsScreen() {
    const { colors } = useTheme();
    const router = useRouter();

    const [notifications, setNotifications] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchNotifications();
    }, []);

    const fetchNotifications = async () => {
        try {
            const response = await api.get('/notifications');
            setNotifications(response.data.notifications || []);
        } catch (error) {
            console.error('Erro ao buscar notificações:', error);
            Alert.alert('Erro', 'Não foi possível carregar as notificações.');
        } finally {
            setLoading(false);
        }
    };

    const markAsRead = async (id: string | number) => {
        try {
            await api.post(`/notifications/${id}/read`);
            if (id === 'all') {
                const marked = notifications.map(n => ({ ...n, is_read: true }));
                setNotifications(marked);
            } else {
                const marked = notifications.map(n => n.id === id ? { ...n, is_read: true } : n);
                setNotifications(marked);
            }
        } catch (error) {
            console.error('Erro ao marcar notificação como lida:', error);
        }
    };

    const renderItem = ({ item }: { item: any }) => {
        const isUnread = !item.is_read;
        const title = item.data?.title || 'Notificação';
        const message = item.data?.message || item.data?.body || 'Você tem uma nova mensagem.';

        return (
            <TouchableOpacity
                style={[
                    styles.card,
                    { backgroundColor: colors.card, borderColor: colors.border },
                    isUnread && { borderLeftColor: '#7367F0', borderLeftWidth: 4 }
                ]}
                onPress={() => isUnread && markAsRead(item.id)}
                activeOpacity={0.8}
            >
                <View style={styles.iconContainer}>
                    <Ionicons name="notifications" size={24} color={isUnread ? "#7367F0" : colors.subText} />
                </View>
                <View style={styles.content}>
                    <Text style={[styles.title, { color: colors.text, fontWeight: isUnread ? 'bold' : 'normal' }]}>{title}</Text>
                    <Text style={[styles.message, { color: colors.subText }]} numberOfLines={2}>{message}</Text>
                    <Text style={styles.date}>{item.created_at}</Text>
                </View>
                {isUnread && <View style={styles.unreadDot} />}
            </TouchableOpacity>
        );
    };

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <View style={[styles.header, { backgroundColor: colors.card, borderBottomColor: colors.border }]}>
                <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
                    <Ionicons name="arrow-back" size={24} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>Notificações</Text>
                <TouchableOpacity style={styles.readAllBtn} onPress={() => markAsRead('all')}>
                    <Ionicons name="checkmark-done-circle-outline" size={24} color="#7367F0" />
                </TouchableOpacity>
            </View>

            {loading ? (
                <View style={styles.loadingContainer}>
                    <ActivityIndicator size="large" color="#7367F0" />
                </View>
            ) : notifications.length > 0 ? (
                <FlatList
                    data={notifications}
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={renderItem}
                    contentContainerStyle={styles.list}
                    showsVerticalScrollIndicator={false}
                />
            ) : (
                <View style={styles.emptyContainer}>
                    <Ionicons name="notifications-off-outline" size={60} color={colors.subText} style={{ opacity: 0.5 }} />
                    <Text style={[styles.emptyText, { color: colors.subText }]}>Nenhuma notificação por aqui.</Text>
                </View>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: 20,
        paddingTop: 50,
        borderBottomWidth: 1
    },
    backBtn: { padding: 5 },
    headerTitle: { fontSize: 20, fontWeight: 'bold' },
    readAllBtn: { padding: 5 },
    list: { padding: 15, paddingBottom: 40 },
    card: {
        flexDirection: 'row',
        padding: 15,
        borderRadius: 12,
        marginBottom: 10,
        borderWidth: 1,
        alignItems: 'center'
    },
    iconContainer: {
        width: 40,
        height: 40,
        borderRadius: 20,
        backgroundColor: 'rgba(115, 103, 240, 0.1)',
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 15
    },
    content: { flex: 1 },
    title: { fontSize: 16, marginBottom: 4 },
    message: { fontSize: 14, marginBottom: 6 },
    date: { fontSize: 12, color: '#aaa' },
    unreadDot: {
        width: 10,
        height: 10,
        borderRadius: 5,
        backgroundColor: '#7367F0',
        marginLeft: 10
    },
    loadingContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    emptyContainer: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
    emptyText: { fontSize: 16, marginTop: 15, textAlign: 'center' }
});
