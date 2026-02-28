import React, { useState, useEffect, useCallback } from 'react';
import {
    View,
    Text,
    StyleSheet,
    FlatList,
    TouchableOpacity,
    ActivityIndicator,
    Alert,
    RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { useTheme } from '../../../context/ThemeContext';
import api from '../../../services/api';
import { getNotificationIcon, getNotificationColor, getRouteFromNotification } from '../../../services/notifications';
import Animated, { FadeInDown } from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';

// ─── Tipos ───────────────────────────────────────────────────────────────────

type NotificationType = {
    id: string | number;
    data: Record<string, any>;
    is_read: boolean;
    created_at: string;
};

// ─── Helper: formatar data relativa ──────────────────────────────────────────
function formatRelativeDate(dateStr: string): string {
    try {
        const date = new Date(dateStr);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffMin = Math.floor(diffMs / 60000);
        const diffH = Math.floor(diffMin / 60);
        const diffD = Math.floor(diffH / 24);

        if (diffMin < 1) return 'agora mesmo';
        if (diffMin < 60) return `há ${diffMin} min`;
        if (diffH < 24) return `há ${diffH}h`;
        if (diffD === 1) return 'ontem';
        if (diffD < 7) return `há ${diffD} dias`;
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    } catch {
        return dateStr;
    }
}

// ─── Componente Principal ────────────────────────────────────────────────────

export default function NotificationsScreen() {
    const { colors, activeTheme } = useTheme();
    const router = useRouter();

    const [notifications, setNotifications] = useState<NotificationType[]>([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    useEffect(() => { fetchNotifications(); }, []);

    const fetchNotifications = async () => {
        try {
            const response = await api.get('/notifications');
            setNotifications(response.data.notifications || []);
        } catch (error) {
            console.error('Erro ao buscar notificações:', error);
            Alert.alert('Erro', 'Não foi possível carregar as notificações.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const onRefresh = useCallback(() => {
        setRefreshing(true);
        fetchNotifications();
    }, []);

    const markAsRead = async (id: string | number) => {
        try {
            await api.post(`/notifications/${id}/read`);
            if (id === 'all') {
                setNotifications(prev => prev.map(n => ({ ...n, is_read: true })));
            } else {
                setNotifications(prev => prev.map(n => n.id === id ? { ...n, is_read: true } : n));
            }
        } catch (error) {
            console.error('Erro ao marcar notificação como lida:', error);
        }
    };

    const handlePressNotification = (item: NotificationType) => {
        Haptics.selectionAsync();

        // Marca como lida
        if (!item.is_read) markAsRead(item.id);

        // Navega para a tela correspondente
        const route = getRouteFromNotification(item.data);
        if (route) {
            router.push(route as any);
        }
    };

    const unreadCount = notifications.filter(n => !n.is_read).length;

    // ─── Render item ────────────────────────────────────────────────────────────
    const renderItem = ({ item, index }: { item: NotificationType; index: number }) => {
        const isUnread = !item.is_read;
        const title = item.data?.title || 'Notificação';
        const message = item.data?.message || item.data?.body || 'Você tem uma nova mensagem.';
        const route = getRouteFromNotification(item.data);
        const iconName = getNotificationIcon(item.data) as any;
        const iconColor = getNotificationColor(item.data);
        const hasRoute = !!route;

        return (
            <Animated.View entering={FadeInDown.delay(index * 40).duration(300).springify()}>
                <TouchableOpacity
                    style={[
                        styles.card,
                        { backgroundColor: colors.card, borderColor: colors.border },
                        isUnread && { borderLeftColor: iconColor, borderLeftWidth: 3.5 },
                        isUnread && activeTheme === 'dark' && { backgroundColor: iconColor + '12' },
                    ]}
                    onPress={() => handlePressNotification(item)}
                    activeOpacity={0.8}
                >
                    {/* Ícone colorido */}
                    <View style={[styles.iconCircle, { backgroundColor: iconColor + '18' }]}>
                        <Ionicons name={iconName} size={22} color={iconColor} />
                    </View>

                    {/* Conteúdo */}
                    <View style={styles.cardContent}>
                        <View style={styles.cardTopRow}>
                            <Text
                                style={[styles.cardTitle, { color: colors.text, fontWeight: isUnread ? '700' : '500' }]}
                                numberOfLines={1}
                            >
                                {title}
                            </Text>
                            <Text style={styles.cardDate}>{formatRelativeDate(item.created_at)}</Text>
                        </View>

                        <Text style={[styles.cardMessage, { color: colors.subText }]} numberOfLines={2}>
                            {message}
                        </Text>

                        {/* CTA de navegação */}
                        {hasRoute && (
                            <View style={[styles.ctaRow, { borderTopColor: colors.border }]}>
                                <Ionicons name="arrow-forward-circle" size={14} color={iconColor} />
                                <Text style={[styles.ctaText, { color: iconColor }]}>
                                    {item.data?.os_id || item.data?.ordem_servico_id
                                        ? `Ver OS #${item.data?.os_id ?? item.data?.ordem_servico_id}`
                                        : item.data?.chat_id
                                            ? 'Abrir conversa'
                                            : 'Ver detalhes'}
                                </Text>
                            </View>
                        )}
                    </View>

                    {/* Ponto de não-lido */}
                    {isUnread && <View style={[styles.unreadDot, { backgroundColor: iconColor }]} />}
                </TouchableOpacity>
            </Animated.View>
        );
    };

    // ─── JSX ────────────────────────────────────────────────────────────────────
    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            {/* Header gradiente */}
            <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.header}
            >
                <TouchableOpacity style={styles.backBtn} onPress={() => { Haptics.selectionAsync(); router.back(); }}>
                    <Ionicons name="arrow-back" size={22} color="#fff" />
                </TouchableOpacity>

                <View style={{ flex: 1, marginHorizontal: 12 }}>
                    <Text style={styles.headerTitle}>Notificações</Text>
                    {!loading && (
                        <Text style={styles.headerSub}>
                            {unreadCount > 0 ? `${unreadCount} não lida${unreadCount > 1 ? 's' : ''}` : 'Tudo lido ✓'}
                        </Text>
                    )}
                </View>

                {unreadCount > 0 && (
                    <TouchableOpacity
                        style={styles.markAllBtn}
                        onPress={() => { Haptics.selectionAsync(); markAsRead('all'); }}
                    >
                        <Ionicons name="checkmark-done" size={18} color="#fff" />
                        <Text style={styles.markAllText}>Ler tudo</Text>
                    </TouchableOpacity>
                )}
            </LinearGradient>

            {/* Lista */}
            {loading ? (
                <View style={styles.centered}>
                    <ActivityIndicator size="large" color="#7367F0" />
                </View>
            ) : notifications.length > 0 ? (
                <FlatList
                    data={notifications}
                    keyExtractor={item => item.id.toString()}
                    renderItem={renderItem}
                    contentContainerStyle={styles.list}
                    showsVerticalScrollIndicator={false}
                    refreshControl={
                        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#7367F0" colors={['#7367F0']} />
                    }
                />
            ) : (
                <View style={styles.centered}>
                    <View style={styles.emptyIcon}>
                        <Ionicons name="notifications-off-outline" size={44} color="#7367F0" />
                    </View>
                    <Text style={[styles.emptyTitle, { color: colors.text }]}>Nenhuma notificação</Text>
                    <Text style={[styles.emptySubtitle, { color: colors.subText }]}>
                        Quando houver atualizações de OS{'\n'}ou mensagens, elas aparecerão aqui.
                    </Text>
                </View>
            )}
        </View>
    );
}

// ─── Estilos ──────────────────────────────────────────────────────────────────

const styles = StyleSheet.create({
    container: { flex: 1 },

    header: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingTop: 56,
        paddingBottom: 18,
        paddingHorizontal: 16,
        borderBottomLeftRadius: 24,
        borderBottomRightRadius: 24,
    },
    backBtn: {
        width: 36,
        height: 36,
        borderRadius: 12,
        backgroundColor: 'rgba(255,255,255,0.2)',
        alignItems: 'center',
        justifyContent: 'center',
    },
    headerTitle: { fontSize: 18, fontWeight: 'bold', color: '#fff' },
    headerSub: { fontSize: 11, color: 'rgba(255,255,255,0.75)', marginTop: 2 },
    markAllBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 5,
        backgroundColor: 'rgba(255,255,255,0.2)',
        paddingHorizontal: 12,
        paddingVertical: 7,
        borderRadius: 50,
    },
    markAllText: { color: '#fff', fontSize: 12, fontWeight: '600' },

    list: { padding: 16, paddingBottom: 60 },

    card: {
        flexDirection: 'row',
        borderRadius: 16,
        marginBottom: 10,
        borderWidth: 1,
        padding: 14,
        alignItems: 'flex-start',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.04,
        shadowRadius: 8,
        elevation: 1,
    },
    iconCircle: {
        width: 44,
        height: 44,
        borderRadius: 14,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: 12,
        flexShrink: 0,
    },
    cardContent: { flex: 1 },
    cardTopRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: 4,
        gap: 8,
    },
    cardTitle: { fontSize: 14, flex: 1 },
    cardDate: { fontSize: 11, color: '#aaa', flexShrink: 0 },
    cardMessage: { fontSize: 13, lineHeight: 18 },
    ctaRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
        marginTop: 8,
        paddingTop: 8,
        borderTopWidth: 1,
    },
    ctaText: { fontSize: 12, fontWeight: '600' },
    unreadDot: {
        width: 8,
        height: 8,
        borderRadius: 4,
        marginLeft: 8,
        alignSelf: 'center',
        flexShrink: 0,
    },

    centered: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 30 },
    emptyIcon: {
        width: 80,
        height: 80,
        borderRadius: 24,
        backgroundColor: '#7367F015',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 16,
    },
    emptyTitle: { fontSize: 18, fontWeight: 'bold', marginBottom: 8, textAlign: 'center' },
    emptySubtitle: { fontSize: 14, textAlign: 'center', lineHeight: 20, opacity: 0.7 },
});
