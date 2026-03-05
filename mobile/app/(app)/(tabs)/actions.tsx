import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, TouchableOpacity, StyleSheet, ScrollView, Modal,
    FlatList, RefreshControl, ActivityIndicator, Alert, Image
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import Animated, { FadeInUp, FadeInDown, ZoomIn } from 'react-native-reanimated';
import * as Haptics from 'expo-haptics';
import { Swipeable } from 'react-native-gesture-handler';

import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import { useAuth } from '../../../context/AuthContext';
import api from '../../../services/api';

// ─── Tipagens ───────────────────────────────────────────────────────────────
interface OrderItem {
    id: number;
    client_name: string;
    vehicle: string;
    plate: string;
    status: string;
    total: number;
    created_at: string;
    items_count?: number;
    description?: string;
    client?: { name?: string; company_name?: string };
}

// ─── Helpers ────────────────────────────────────────────────────────────────
const statusLabels: Record<string, string> = {
    pending: 'Pendente',
    approved: 'Em Preparo',
    running: 'Em Preparo',
    finalized: 'Pronto',
    canceled: 'Cancelado',
};

const statusColors: Record<string, string> = {
    pending: '#FF9F43',
    approved: '#00CFE8',
    running: '#00CFE8',
    finalized: '#28C76F',
    canceled: '#EA5455',
};

const paymentMethods = [
    { id: 'cash', label: 'Dinheiro', icon: 'cash-outline', color: '#28C76F' },
    { id: 'pix', label: 'PIX', icon: 'qr-code-outline', color: '#7367F0' },
    { id: 'debit', label: 'Débito', icon: 'card-outline', color: '#00CFE8' },
    { id: 'credit', label: 'Crédito', icon: 'card-outline', color: '#FF9F43' },
];

const numberFormat = (value: any) =>
    parseFloat(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const getTimeSince = (dateStr: string) => {
    const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 60000);
    if (diff < 1) return 'agora';
    if (diff < 60) return `${diff}min`;
    return `${Math.floor(diff / 60)}h${diff % 60}min`;
};

// ─── Componente Principal ───────────────────────────────────────────────────
export default function ActionsScreen() {
    const { colors } = useTheme();
    const { labels, niche } = useNiche();
    const { user } = useAuth();

    // Se NÃO for food_service, renderiza as Ações Rápidas originais
    if (niche !== 'food_service') {
        return <OriginalActionsScreen colors={colors} labels={labels} niche={niche} />;
    }

    // ─── PDV Food Service ───────────────────────────────────────────────────
    return <PDVScreen colors={colors} user={user} />;
}

// ═══════════════════════════════════════════════════════════════════════════
// AÇÕES RÁPIDAS ORIGINAIS (Nichos não-food)
// ═══════════════════════════════════════════════════════════════════════════
function OriginalActionsScreen({ colors, labels, niche }: any) {
    const getEntityIcon = () => {
        switch (niche) {
            case 'pet': return 'paw-outline';
            case 'electronics': return 'laptop-outline';
            default: return 'car-sport-outline';
        }
    };

    const actions = [
        { id: 'new', title: 'Nova OS', icon: 'document-text-outline', color: '#7367F0', desc: 'Abrir ordem de serviço' },
        { id: 'new-client', title: 'Novo Cliente', icon: 'person-add-outline', color: '#28C76F', desc: 'Cadastrar cliente' },
        { id: 'new-vehicle', title: labels.new_entity, icon: getEntityIcon(), color: '#00CFE8', desc: `Cadastrar ${labels.entity.toLowerCase()}` },
        { id: 'scan', title: 'Ler QR Code', icon: 'scan-outline', color: '#4B4B4B', desc: 'Buscar por etiqueta' },
        { id: 'calendar', title: 'Agenda', icon: 'calendar-outline', color: '#FF9F43', desc: 'Ver agendamentos' },
        { id: 'parts', title: labels.inventory_items?.split('/')[0] || 'Peças/Produtos', icon: 'cube-outline', color: '#EA5455', desc: 'Consultar estoque' },
        { id: 'ai', title: 'Ghotme IA', icon: 'sparkles-outline', color: '#CE9FFC', desc: 'Consultor inteligente' },
    ];

    const handlePress = (id: string) => {
        Haptics.selectionAsync();
        const routes: Record<string, string> = {
            'new': '/os/create',
            'new-client': '/clients/create',
            'new-vehicle': '/vehicles/create',
            'scan': '/screens/qr_scanner',
            'calendar': '/calendar',
            'parts': '/inventory',
            'ai': '/ai-consultant',
        };
        if (routes[id]) router.push(routes[id] as any);
    };

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.header}>
                <Animated.View entering={FadeInUp.duration(600).springify()}>
                    <Text style={styles.headerTitle}>Ações Rápidas</Text>
                    <Text style={styles.headerSubtitle}>O que você deseja fazer hoje?</Text>
                </Animated.View>
            </LinearGradient>

            <ScrollView contentContainerStyle={styles.gridContainer} showsVerticalScrollIndicator={false}>
                <View style={styles.grid}>
                    {actions.map((item, index) => (
                        <Animated.View key={item.id} style={{ width: '47%', marginBottom: 20 }} entering={ZoomIn.delay(index * 100).duration(500).springify()}>
                            <TouchableOpacity
                                style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}
                                activeOpacity={0.8}
                                onPress={() => handlePress(item.id)}
                            >
                                <View style={[styles.iconContainer, { backgroundColor: item.color + '20' }]}>
                                    <Ionicons name={item.icon as any} size={28} color={item.color} />
                                </View>
                                <Text style={[styles.cardTitle, { color: colors.text }]}>{item.title}</Text>
                                <Text style={[styles.cardDesc, { color: colors.subText }]}>{item.desc}</Text>
                            </TouchableOpacity>
                        </Animated.View>
                    ))}
                </View>
            </ScrollView>
        </View>
    );
}

// ═══════════════════════════════════════════════════════════════════════════
// PDV FOOD SERVICE
// ═══════════════════════════════════════════════════════════════════════════
function PDVScreen({ colors, user }: any) {
    const [orders, setOrders] = useState<OrderItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [activeFilter, setActiveFilter] = useState('all');
    const [paymentModal, setPaymentModal] = useState(false);
    const [selectedOrder, setSelectedOrder] = useState<OrderItem | null>(null);
    const [selectedPayment, setSelectedPayment] = useState<string | null>(null);
    const [processing, setProcessing] = useState(false);
    const [pixModalVisible, setPixModalVisible] = useState(false);
    const [pixQrCode, setPixQrCode] = useState<any>(null);
    const [pixPolling, setPixPolling] = useState<any>(null);

    const fetchOrders = async () => {
        try {
            const res = await api.get('/os');
            setOrders(res.data?.data || res.data || []);
        } catch (e) {
            console.error('PDV fetch error:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        fetchOrders();
        const interval = setInterval(fetchOrders, 15000); // Auto-refresh a cada 15s
        return () => clearInterval(interval);
    }, []);

    const onRefresh = useCallback(() => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        setRefreshing(true);
        fetchOrders();
    }, []);

    const filteredOrders = orders.filter(o => {
        if (activeFilter === 'all') return o.status !== 'canceled';
        if (activeFilter === 'pending') return o.status === 'pending';
        if (activeFilter === 'running') return o.status === 'approved' || o.status === 'running';
        if (activeFilter === 'finalized') return o.status === 'finalized';
        return true;
    });

    const todaySales = orders
        .filter(o => o.status === 'finalized' && new Date(o.created_at).toDateString() === new Date().toDateString())
        .reduce((sum, o) => sum + (parseFloat(String(o.total)) || 0), 0);

    const openPayment = (order: OrderItem) => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
        setSelectedOrder(order);
        setSelectedPayment(null);
        setPaymentModal(true);
    };

    const confirmPayment = async () => {
        if (!selectedPayment || !selectedOrder) return;
        setProcessing(true);
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);

        try {
            // Se for PIX, tenta gerar cobrança no gateway
            if (selectedPayment === 'pix') {
                const pixResponse = await api.post(`/os/${selectedOrder.id}/pix/generate`);

                // Se gateway está configurado
                if (pixResponse.data?.gateway === true) {
                    setPixQrCode(pixResponse.data);
                    setPixModalVisible(true);
                    setPaymentModal(false);

                    // Iniciar polling para verificar pagamento
                    const pollInterval = setInterval(async () => {
                        try {
                            const statusResponse = await api.get(`/os/${selectedOrder.id}/pix/status`);

                            if (statusResponse.data?.is_paid) {
                                clearInterval(pollInterval);
                                setPixModalVisible(false);
                                setPixQrCode(null);
                                Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
                                Alert.alert('Sucesso!', 'Pagamento PIX confirmado! Pedido finalizado.');
                                setSelectedOrder(null);
                                fetchOrders();
                            }
                        } catch (e) {
                            console.error('Erro ao verificar status PIX:', e);
                        }
                    }, 3000);

                    // Limpar polling após 5 minutos
                    setPixPolling(pollInterval);
                    setTimeout(() => clearInterval(pollInterval), 300000);
                    setProcessing(false);
                    return;
                }
                // Se sem gateway, continua com pagamento normal
            }

            // Finalizar pedido com o método de pagamento
            await api.patch(`/os/${selectedOrder.id}/status`, {
                status: 'finalized',
                payment_method: selectedPayment
            });

            setPaymentModal(false);
            setSelectedOrder(null);
            fetchOrders();
        } catch (e: any) {
            const msg = e?.response?.data?.message || e?.message || 'Não foi possível finalizar o pedido.';
            Alert.alert('Erro', msg);
        } finally {
            setProcessing(false);
        }
    };

    const handleDeleteOrder = (orderId: number) => {
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Warning);
        Alert.alert(
            'Excluir Pedido',
            'Gostaria de excluir o pedido?',
            [
                { text: 'Cancelar', style: 'cancel' },
                {
                    text: 'Excluir',
                    style: 'destructive',
                    onPress: async () => {
                        try {
                            setLoading(true);
                            await api.delete(`/os/${orderId}`);
                            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
                            fetchOrders();
                        } catch (e) {
                            Alert.alert('Erro', 'Não foi possível excluir o pedido.');
                        } finally {
                            setLoading(false);
                        }
                    }
                }
            ]
        );
    };

    const renderRightActions = (id: number) => {
        return (
            <TouchableOpacity
                style={pdvStyles.deleteAction}
                onPress={() => handleDeleteOrder(id)}
                activeOpacity={0.7}
            >
                <View style={pdvStyles.deleteActionContent}>
                    <Ionicons name="trash-outline" size={24} color="#fff" />
                    <Text style={pdvStyles.deleteActionText}>Excluir</Text>
                </View>
            </TouchableOpacity>
        );
    };

    const filterTabs = [
        { key: 'all', label: 'Todos', icon: 'grid-outline' },
        { key: 'pending', label: 'Pendentes', icon: 'hourglass-outline' },
        { key: 'running', label: 'Preparo', icon: 'flame-outline' },
        { key: 'finalized', label: 'Prontos', icon: 'checkmark-done-outline' },
    ];

    const extractDeliveryAddress = (description?: string): string | null => {
        if (!description) return null;
        const lines = description.split('\n');
        // Linha com endereço: "🏠 Rua tal, 123"
        const addrLine = lines.find(l => l.startsWith('🏠'));
        if (addrLine) return addrLine.replace('🏠', '').trim();
        // Fallback formato antigo: "📍 ENTREGA: Rua tal"
        const fallback = lines.find(l => l.includes('📍 ENTREGA:'));
        if (fallback) return fallback.split(':').slice(1).join(':').trim();
        return null;
    };

    const renderOrderCard = ({ item, index }: { item: OrderItem; index: number }) => {
        const statusColor = statusColors[item.status] || '#7367F0';
        const isDelivery = item.description?.includes('📍 ENTREGA');
        const typeColor = isDelivery ? '#EA5455' : '#7367F0';
        const typeText = isDelivery ? 'Entrega' : 'Balcão';

        // Nome do cliente — mostra qualquer nome disponível
        const clientName = item.client?.name || item.client?.company_name || item.client_name || null;

        const deliveryAddress = isDelivery ? extractDeliveryAddress(item.description) : null;

        return (
            <Animated.View entering={FadeInDown.delay(index * 80).duration(400).springify()}>
                <Swipeable
                    renderRightActions={() => renderRightActions(item.id)}
                    friction={2}
                    rightThreshold={40}
                >
                    <TouchableOpacity
                        style={[pdvStyles.orderCard, { backgroundColor: colors.card, borderColor: colors.border }]}
                        activeOpacity={0.85}
                        onPress={() => item.status !== 'finalized' && item.status !== 'canceled' ? openPayment(item) : router.push(`/os/${item.id}`)}
                    >
                        <View style={pdvStyles.orderHeader}>
                            <View style={{ flexDirection: 'row', gap: 6 }}>
                                <View style={[pdvStyles.orderBadge, { backgroundColor: statusColor + '20' }]}>
                                    <Text style={[pdvStyles.orderBadgeText, { color: statusColor }]}>#{item.id}</Text>
                                </View>
                                <View style={[pdvStyles.orderBadge, { backgroundColor: typeColor + '20' }]}>
                                    <Text style={[pdvStyles.orderBadgeText, { color: typeColor }]}>{typeText}</Text>
                                </View>
                            </View>
                            <View style={[pdvStyles.statusChip, { backgroundColor: statusColor + '15' }]}>
                                <View style={[pdvStyles.statusDot, { backgroundColor: statusColor }]} />
                                <Text style={[pdvStyles.statusText, { color: statusColor }]}>
                                    {statusLabels[item.status] || item.status}
                                </Text>
                            </View>
                        </View>

                        <View style={pdvStyles.orderBody}>
                            {clientName && (
                                <View style={pdvStyles.orderInfoRow}>
                                    <Ionicons name="person-outline" size={14} color={colors.subText} />
                                    <Text style={[pdvStyles.orderInfoText, { color: colors.text }]} numberOfLines={1}>
                                        {clientName}
                                    </Text>
                                </View>
                            )}
                            {deliveryAddress && (
                                <View style={pdvStyles.orderInfoRow}>
                                    <Ionicons name="location-outline" size={14} color="#EA5455" />
                                    <Text style={[pdvStyles.orderInfoText, { color: colors.subText }]} numberOfLines={2}>
                                        {deliveryAddress}
                                    </Text>
                                </View>
                            )}
                            <View style={pdvStyles.orderInfoRow}>
                                <Ionicons name="time-outline" size={14} color={colors.subText} />
                                <Text style={[pdvStyles.orderInfoText, { color: colors.subText }]}>
                                    {getTimeSince(item.created_at)}
                                </Text>
                            </View>
                        </View>

                        <View style={pdvStyles.orderFooter}>
                            <Text style={[pdvStyles.orderTotal, { color: colors.text }]}>R$ {numberFormat(item.total)}</Text>
                            {item.status !== 'finalized' && item.status !== 'canceled' && (
                                <View style={pdvStyles.payBtn}>
                                    <Ionicons name="wallet-outline" size={16} color="#7367F0" />
                                    <Text style={pdvStyles.payBtnText}>Receber</Text>
                                </View>
                            )}
                        </View>
                    </TouchableOpacity>
                </Swipeable>
            </Animated.View>
        );
    };

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            {/* Header PDV */}
            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.header}>
                <Animated.View entering={FadeInUp.duration(600).springify()}>
                    <Text style={styles.headerTitle}>Balcão PDV</Text>
                    <Text style={styles.headerSubtitle}>Vendas de Hoje: R$ {numberFormat(todaySales)}</Text>
                </Animated.View>
            </LinearGradient>

            {/* Filtros */}
            <View style={[pdvStyles.filterBar, { backgroundColor: colors.card }]}>
                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 15 }}>
                    {filterTabs.map(tab => (
                        <TouchableOpacity
                            key={tab.key}
                            style={[pdvStyles.filterTab, activeFilter === tab.key && pdvStyles.filterTabActive]}
                            onPress={() => { Haptics.selectionAsync(); setActiveFilter(tab.key); }}
                        >
                            <Ionicons name={tab.icon as any} size={16} color={activeFilter === tab.key ? '#7367F0' : colors.subText} />
                            <Text style={[pdvStyles.filterTabText, { color: activeFilter === tab.key ? '#7367F0' : colors.subText }]}>
                                {tab.label}
                            </Text>
                        </TouchableOpacity>
                    ))}
                </ScrollView>
            </View>

            {/* Lista de Pedidos */}
            {loading ? (
                <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
                    <ActivityIndicator size="large" color="#7367F0" />
                </View>
            ) : (
                <FlatList
                    data={filteredOrders}
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={renderOrderCard}
                    contentContainerStyle={{ padding: 15, paddingBottom: 120 }}
                    showsVerticalScrollIndicator={false}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#7367F0']} tintColor="#7367F0" />}
                    ListEmptyComponent={
                        <View style={pdvStyles.emptyState}>
                            <Ionicons name="receipt-outline" size={48} color={colors.subText + '44'} />
                            <Text style={[pdvStyles.emptyText, { color: colors.subText }]}>Nenhum pedido encontrado</Text>
                        </View>
                    }
                />
            )}

            {/* FAB Novo Pedido */}
            <Animated.View entering={FadeInUp.delay(300).springify()} style={pdvStyles.fabContainer}>
                <TouchableOpacity
                    style={pdvStyles.fab}
                    onPress={() => { Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium); router.push('/os/create'); }}
                    activeOpacity={0.8}
                >
                    <LinearGradient colors={['#7367F0', '#CE9FFC']} style={pdvStyles.fabGradient}>
                        <Ionicons name="add" size={28} color="#fff" />
                    </LinearGradient>
                </TouchableOpacity>
            </Animated.View>

            {/* Modal de Pagamento */}
            <Modal visible={paymentModal} animationType="slide" transparent>
                <View style={pdvStyles.modalOverlay}>
                    <View style={[pdvStyles.modalContent, { backgroundColor: colors.card }]}>
                        <View style={pdvStyles.modalHandle} />

                        <Text style={[pdvStyles.modalTitle, { color: colors.text }]}>Receber Pagamento</Text>

                        {selectedOrder && (
                            <View style={[pdvStyles.modalOrderSummary, { backgroundColor: colors.background }]}>
                                <Text style={[pdvStyles.modalOrderId, { color: colors.subText }]}>Pedido #{selectedOrder.id}</Text>
                                <Text style={[pdvStyles.modalOrderName, { color: colors.text }]}>
                                    {selectedOrder.client_name || selectedOrder.plate || 'Balcão'}
                                </Text>
                                <Text style={[pdvStyles.modalOrderTotal, { color: '#7367F0' }]}>
                                    R$ {numberFormat(selectedOrder.total)}
                                </Text>
                            </View>
                        )}

                        <Text style={[pdvStyles.modalSectionTitle, { color: colors.text }]}>Método de Pagamento</Text>

                        <View style={pdvStyles.paymentGrid}>
                            {paymentMethods.map(method => (
                                <TouchableOpacity
                                    key={method.id}
                                    style={[
                                        pdvStyles.paymentCard,
                                        { backgroundColor: colors.background, borderColor: selectedPayment === method.id ? method.color : colors.border },
                                        selectedPayment === method.id && { borderWidth: 2 }
                                    ]}
                                    onPress={() => { Haptics.selectionAsync(); setSelectedPayment(method.id); }}
                                >
                                    <View style={[pdvStyles.paymentIconWrap, { backgroundColor: method.color + '15' }]}>
                                        <Ionicons name={method.icon as any} size={32} color={method.color} />
                                    </View>
                                    <Text style={[pdvStyles.paymentLabel, { color: colors.text }]}>{method.label}</Text>
                                </TouchableOpacity>
                            ))}
                        </View>

                        <TouchableOpacity
                            style={[pdvStyles.confirmBtn, { opacity: selectedPayment ? 1 : 0.5 }]}
                            onPress={confirmPayment}
                            disabled={!selectedPayment || processing}
                        >
                            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={pdvStyles.confirmBtnGradient}>
                                {processing ? (
                                    <ActivityIndicator color="#fff" />
                                ) : (
                                    <>
                                        <Ionicons name="checkmark-circle" size={22} color="#fff" style={{ marginRight: 8 }} />
                                        <Text style={pdvStyles.confirmBtnText}>Finalizar e Receber</Text>
                                    </>
                                )}
                            </LinearGradient>
                        </TouchableOpacity>

                        <TouchableOpacity style={pdvStyles.cancelBtn} onPress={() => setPaymentModal(false)}>
                            <Text style={[pdvStyles.cancelBtnText, { color: colors.subText }]}>Cancelar</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>

            {/* Modal PIX QR Code */}
            <Modal visible={pixModalVisible} animationType="slide" transparent>
                <View style={pdvStyles.modalOverlay}>
                    <View style={[pdvStyles.modalContent, { backgroundColor: colors.card }]}>
                        <View style={pdvStyles.modalHandle} />

                        <Text style={[pdvStyles.modalTitle, { color: colors.text }]}>Aguardando Pagamento PIX</Text>

                        {pixQrCode && (
                            <>
                                <View style={[pdvStyles.pixQrContainer, { backgroundColor: colors.background }]}>
                                    {pixQrCode.qr_code_image && (
                                        <Image
                                            source={{ uri: `data:image/png;base64,${pixQrCode.qr_code_image}` }}
                                            style={pdvStyles.pixImage}
                                        />
                                    )}
                                </View>

                                {pixQrCode.qr_code_text && (
                                    <View style={[pdvStyles.pixCopyContainer, { backgroundColor: colors.background, borderColor: colors.border }]}>
                                        <Text style={[pdvStyles.pixCopyLabel, { color: colors.subText }]}>Copia e Cola:</Text>
                                        <Text style={[pdvStyles.pixCopyText, { color: colors.text }]} selectable>
                                            {pixQrCode.qr_code_text}
                                        </Text>
                                    </View>
                                )}

                                <View style={pdvStyles.pixInfo}>
                                    <Ionicons name="hourglass-outline" size={20} color="#7367F0" />
                                    <Text style={[pdvStyles.pixInfoText, { color: colors.text }]}>
                                        Escaneie o QR Code ou copie o código acima
                                    </Text>
                                </View>
                            </>
                        )}

                        <TouchableOpacity
                            style={pdvStyles.cancelBtn}
                            onPress={() => {
                                if (pixPolling) clearInterval(pixPolling);
                                setPixModalVisible(false);
                                setPixQrCode(null);
                            }}
                        >
                            <Text style={[pdvStyles.cancelBtnText, { color: colors.subText }]}>Cancelar</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>
        </View>
    );
}

// ═══════════════════════════════════════════════════════════════════════════
// ESTILOS
// ═══════════════════════════════════════════════════════════════════════════
const styles = StyleSheet.create({
    container: { flex: 1 },
    header: {
        paddingTop: 60, paddingBottom: 30, paddingHorizontal: 24,
        borderBottomLeftRadius: 30, borderBottomRightRadius: 30, alignItems: 'center',
    },
    headerTitle: { fontSize: 24, fontWeight: 'bold', color: '#fff', textAlign: 'center' },
    headerSubtitle: { fontSize: 14, color: 'rgba(255,255,255,0.9)', marginTop: 5, textAlign: 'center' },
    gridContainer: { padding: 20, paddingBottom: 100 },
    grid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
    card: {
        width: '100%', borderRadius: 16, padding: 20, alignItems: 'center',
        borderWidth: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05, shadowRadius: 10, elevation: 2,
    },
    iconContainer: {
        width: 60, height: 60, borderRadius: 30,
        justifyContent: 'center', alignItems: 'center', marginBottom: 12,
    },
    cardTitle: { fontSize: 16, fontWeight: 'bold', marginBottom: 4, textAlign: 'center' },
    cardDesc: { fontSize: 12, textAlign: 'center' },
});

const pdvStyles = StyleSheet.create({
    filterBar: { paddingVertical: 12 },
    filterTab: {
        flexDirection: 'row', alignItems: 'center', gap: 6,
        paddingHorizontal: 16, paddingVertical: 8, borderRadius: 20, marginRight: 10,
    },
    filterTabActive: { backgroundColor: '#7367F015', borderWidth: 1, borderColor: '#7367F040' },
    filterTabText: { fontSize: 13, fontWeight: '600' },

    orderCard: {
        borderRadius: 16, padding: 16, marginBottom: 12,
        borderWidth: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05, shadowRadius: 8, elevation: 2,
    },
    orderHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    orderBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
    orderBadgeText: { fontWeight: '800', fontSize: 14 },
    statusChip: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12, gap: 5 },
    statusDot: { width: 6, height: 6, borderRadius: 3 },
    statusText: { fontSize: 12, fontWeight: '700' },

    orderBody: { gap: 6, marginBottom: 12 },
    orderInfoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    orderInfoText: { fontSize: 14, fontWeight: '500' },

    orderFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderTopWidth: 1, borderTopColor: '#f0f0f0', paddingTop: 12 },
    orderTotal: { fontSize: 18, fontWeight: 'bold' },
    payBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: '#7367F015', paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20 },
    payBtnText: { color: '#7367F0', fontWeight: '700', fontSize: 13 },

    emptyState: { alignItems: 'center', justifyContent: 'center', marginTop: 60 },
    emptyText: { marginTop: 10, fontSize: 14, fontWeight: '500' },

    fabContainer: { position: 'absolute', bottom: 100, right: 25, zIndex: 999 },
    fab: { shadowColor: '#7367F0', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.35, shadowRadius: 10, elevation: 8 },
    fabGradient: { width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center' },

    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalContent: { borderTopLeftRadius: 30, borderTopRightRadius: 30, padding: 25, paddingBottom: 40 },
    modalHandle: { width: 40, height: 4, borderRadius: 2, backgroundColor: '#ddd', alignSelf: 'center', marginBottom: 20 },
    modalTitle: { fontSize: 22, fontWeight: 'bold', textAlign: 'center', marginBottom: 20 },
    modalOrderSummary: { borderRadius: 16, padding: 16, alignItems: 'center', marginBottom: 20 },
    modalOrderId: { fontSize: 12, fontWeight: '600', marginBottom: 4 },
    modalOrderName: { fontSize: 16, fontWeight: '700', marginBottom: 6 },
    modalOrderTotal: { fontSize: 28, fontWeight: '900' },
    modalSectionTitle: { fontSize: 14, fontWeight: '700', marginBottom: 15, textTransform: 'uppercase', letterSpacing: 1 },
    paymentGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', marginBottom: 25 },
    paymentCard: { width: '48%', borderRadius: 20, paddingVertical: 22, paddingHorizontal: 14, alignItems: 'center', borderWidth: 1.5, marginBottom: 12 },
    paymentIconWrap: { width: 64, height: 64, borderRadius: 32, justifyContent: 'center', alignItems: 'center', marginBottom: 12 },
    paymentLabel: { fontSize: 15, fontWeight: '700' },
    confirmBtn: { marginBottom: 12 },
    confirmBtnGradient: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 16, borderRadius: 16 },
    confirmBtnText: { color: '#fff', fontSize: 16, fontWeight: 'bold' },
    cancelBtn: { alignItems: 'center', paddingVertical: 12 },
    cancelBtnText: { fontSize: 15, fontWeight: 'bold' },
    deleteAction: {
        backgroundColor: '#EA5455',
        justifyContent: 'center',
        alignItems: 'flex-end',
        width: 100,
        height: '88%',
        marginTop: 6,
        borderRadius: 16,
    },
    deleteActionContent: {
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        width: 100,
    },
    deleteActionText: {
        color: '#fff',
        fontSize: 12,
        fontWeight: 'bold',
        marginTop: 4,
    },

    pixQrContainer: { borderRadius: 16, padding: 20, alignItems: 'center', justifyContent: 'center', marginBottom: 20 },
    pixImage: { width: 280, height: 280, borderRadius: 12 },
    pixCopyContainer: { borderRadius: 12, padding: 16, marginBottom: 20, borderWidth: 1, borderColor: '#ddd' },
    pixCopyLabel: { fontSize: 12, fontWeight: '600', marginBottom: 8 },
    pixCopyText: { fontSize: 13, fontWeight: '500', lineHeight: 20 },
    pixInfo: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10, marginBottom: 20, paddingVertical: 12 },
    pixInfoText: { fontSize: 13, fontWeight: '500', flex: 1 },
});
