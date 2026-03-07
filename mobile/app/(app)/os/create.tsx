import React, { useEffect, useState, useCallback } from 'react';
import {
    View, Text, TextInput, StyleSheet, ScrollView, TouchableOpacity,
    Alert, ActivityIndicator, KeyboardAvoidingView, Platform, FlatList, Linking, Modal, SectionList
} from 'react-native';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import Animated, { FadeInDown, FadeIn, ZoomIn } from 'react-native-reanimated';
import * as Haptics from 'expo-haptics';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import paymentService from '../../../services/payment';
import { useDevices } from '../../../context/DeviceContext';

// ─── Tipagens ───────────────────────────────────────────────────────────────
interface IngredientInfo {
    name: string;
    qty: number;
}

interface MenuItem {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    selling_price: number;
    quantity: number;
    image_url: string | null;
    ingredients: IngredientInfo[];
}

interface MenuCategory {
    id: number;
    name: string;
    icon: string;
    items: MenuItem[];
}

interface CartItem extends MenuItem {
    qty: number;
    notes: string;
}

const PAYMENT_METHODS = [
    { id: 'cash', label: 'Dinheiro', icon: 'cash-outline', color: '#28C76F' },
    { id: 'pix', label: 'PIX', icon: 'qr-code-outline', color: '#7367F0' },
    { id: 'card_reader', label: 'Maquininha', icon: 'bluetooth-outline', color: '#7367F0' },
    { id: 'debit', label: 'Débito (Manual)', icon: 'card-outline', color: '#00CFE8' },
    { id: 'credit', label: 'Crédito (Manual)', icon: 'card-outline', color: '#FF9F43' },
    { id: 'ifood', label: 'iFood', icon: 'bicycle-outline', color: '#EA1D2C' },
];

const fmt = (v: number) => v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

// ═══════════════════════════════════════════════════════════════════════════
// COMPONENTE PRINCIPAL
// ═══════════════════════════════════════════════════════════════════════════
export default function CreateOrderScreen() {
    const { niche } = useNiche();

    if (niche === 'food_service') {
        return <FoodServicePDV />;
    }
    return <OriginalCreateOrder />;
}

// Componente Auxiliar para Item de Produto (para reutilizar na SectionList)
const ProductItem = ({ item, index, cart, addToCart, setDetailItem, colors }: any) => {
    const inCart = cart.find((c: any) => c.id === item.id);
    return (
        <Animated.View entering={FadeInDown.delay(index * 60).duration(300)} style={s.productCardWrapper}>
            <TouchableOpacity
                style={[s.productCard, { backgroundColor: colors.card, borderColor: colors.border }]}
                activeOpacity={0.85}
                onPress={() => setDetailItem(item)}
            >
                {item.image_url ? (
                    <Image source={{ uri: item.image_url }} style={s.productImage} contentFit="cover" transition={200} />
                ) : (
                    <View style={[s.productImagePlaceholder, { backgroundColor: '#7367F015' }]}>
                        <Ionicons name="fast-food-outline" size={36} color="#7367F0" />
                    </View>
                )}
                <View style={s.productInfo}>
                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                        {item.sku ? (
                            <View style={{ backgroundColor: '#7367F015', paddingHorizontal: 4, paddingVertical: 1, borderRadius: 4 }}>
                                <Text style={{ fontSize: 10, fontWeight: 'bold', color: '#7367F0' }}>#{item.sku}</Text>
                            </View>
                        ) : null}
                        <Text style={[s.productName, { color: colors.text, flex: 1 }]} numberOfLines={1}>{item.name}</Text>
                    </View>
                    <Text style={s.productPrice}>R$ {item.selling_price ? item.selling_price.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) : '0,00'}</Text>
                </View>
                <TouchableOpacity style={s.addBtn} onPress={() => addToCart(item)}>
                    <LinearGradient colors={['#7367F0', '#CE9FFC']} style={s.addBtnGradient}>
                        <Ionicons name="add" size={20} color="#fff" />
                    </LinearGradient>
                </TouchableOpacity>
                {inCart && (
                    <View style={s.qtyBadge}>
                        <Text style={s.qtyBadgeText}>{inCart.qty}</Text>
                    </View>
                )}
            </TouchableOpacity>
        </Animated.View>
    );
};

// ═══════════════════════════════════════════════════════════════════════════
// PDV FOOD SERVICE — Cardápio Visual com Carrinho
// ═══════════════════════════════════════════════════════════════════════════
function FoodServicePDV() {
    const router = useRouter();
    const { colors } = useTheme();
    const insets = useSafeAreaInsets();
    const { pairedDevices } = useDevices();

    // Estado
    const [categories, setCategories] = useState<MenuCategory[]>([]);
    const [loading, setLoading] = useState(true);
    const [activeCatId, setActiveCatId] = useState<number | null>(null);
    const [cart, setCart] = useState<CartItem[]>([]);
    const [step, setStep] = useState<'menu' | 'cart' | 'payment' | 'pix'>('menu');
    const [customerName, setCustomerName] = useState('');
    const [customerPhone, setCustomerPhone] = useState(''); // 📱 Telefone opcional
    const [selectedPayment, setSelectedPayment] = useState<string | null>(null);
    const [submitting, setSubmitting] = useState(false);
    const [notesModal, setNotesModal] = useState<{ itemId: number; notes: string } | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [detailItem, setDetailItem] = useState<MenuItem | null>(null);
    const [sortBy, setSortBy] = useState<'default' | 'az' | 'za' | 'expensive' | 'cheap'>('default');
    const [lastCreatedOS, setLastCreatedOS] = useState<any>(null); // Para impressão pós-pedido

    // Tipo de pedido: Balcão ou Entrega
    const [orderType, setOrderType] = useState<'counter' | 'delivery'>('counter');
    const [deliveryAddress, setDeliveryAddress] = useState('');
    const [deliveryPhone, setDeliveryPhone] = useState('');
    const [deliveryRef, setDeliveryRef] = useState('');

    // PIX
    const [pixData, setPixData] = useState<{ paymentId: string; qrCodeImage: string; qrCodeText: string } | null>(null);
    const [pixStatus, setPixStatus] = useState<'loading' | 'waiting' | 'paid' | 'error'>('loading');
    const pixPollingRef = React.useRef<ReturnType<typeof setInterval> | null>(null);

    useEffect(() => { fetchMenu(); }, []);

    const fetchMenu = async () => {
        try {
            const res = await api.get('/inventory/menu');
            const data = res.data || [];

            // Garantir que as categorias tenham o formato para SectionList
            const sections = data.map((cat: any) => ({
                id: cat.id,
                title: cat.name,
                data: cat.items || []
            })).filter((s: any) => s.data.length > 0);

            setCategories(data);
            if (sections.length > 0) setActiveCatId(sections[0].id);
        } catch (e) {
            // Fallback para a lista simples se o endpoint não existir
            try {
                const res = await api.get('/inventory/items-list');
                const items = Array.isArray(res.data) ? res.data : (res.data.data || []);
                const filtered = items.filter((i: any) => !i.is_ingredient);
                setCategories([{
                    id: 0, name: 'Cardápio', icon: 'restaurant-outline', items: filtered.map((i: any) => ({
                        id: i.id, name: i.name, description: null, selling_price: parseFloat(i.selling_price), quantity: i.quantity, image_url: null, ingredients: []
                    }))
                }]);
                setActiveCatId(0);
            } catch (e2) { console.error(e2); }
        } finally { setLoading(false); }
    };

    // Itens filtrados por categoria + pesquisa
    const allItems = categories.flatMap(c => c.items);
    const isSearching = searchQuery.trim().length > 0;
    const activeSections = React.useMemo(() => {
        if (isSearching) return [];
        return categories.map(cat => ({
            id: cat.id,
            title: cat.name,
            data: cat.items
        })).filter(s => s.data.length > 0);
    }, [categories, isSearching]);

    const activeItems = React.useMemo(() => {
        if (!isSearching) return [];
        const lowerQuery = searchQuery.toLowerCase();
        const items = [...allItems.filter(i =>
            i.name.toLowerCase().includes(lowerQuery) ||
            (i.sku && i.sku.toLowerCase().includes(lowerQuery))
        )];
        switch (sortBy) {
            case 'az': return items.sort((a, b) => a.name.localeCompare(b.name));
            case 'za': return items.sort((a, b) => b.name.localeCompare(a.name));
            case 'expensive': return items.sort((a, b) => b.selling_price - a.selling_price);
            case 'cheap': return items.sort((a, b) => a.selling_price - b.selling_price);
            default: return items;
        }
    }, [allItems, searchQuery, isSearching, sortBy]);

    const sectionListRef = React.useRef<SectionList>(null);

    const scrollToCategory = (id: number) => {
        const index = activeSections.findIndex(s => s.id === id);
        if (index !== -1 && sectionListRef.current) {
            sectionListRef.current.scrollToLocation({
                sectionIndex: index,
                itemIndex: 0,
                animated: true,
                viewOffset: 0
            });
            setActiveCatId(id);
        }
    };

    const cartTotal = cart.reduce((sum, i) => sum + i.selling_price * i.qty, 0);
    const cartCount = cart.reduce((sum, i) => sum + i.qty, 0);

    const addToCart = (item: MenuItem) => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        setCart(prev => {
            const existing = prev.find(c => c.id === item.id);
            if (existing) {
                return prev.map(c => c.id === item.id ? { ...c, qty: c.qty + 1 } : c);
            }
            return [...prev, { ...item, qty: 1, notes: '' }];
        });
    };

    const updateQty = (itemId: number, delta: number) => {
        Haptics.selectionAsync();
        setCart(prev => prev.map(c => {
            if (c.id === itemId) {
                const newQty = c.qty + delta;
                return newQty > 0 ? { ...c, qty: newQty } : c;
            }
            return c;
        }).filter(c => c.qty > 0 || delta >= 0));
    };

    const removeFromCart = (itemId: number) => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
        setCart(prev => prev.filter(c => c.id !== itemId));
    };

    const updateNotes = (itemId: number, notes: string) => {
        setCart(prev => prev.map(c => c.id === itemId ? { ...c, notes } : c));
    };

    const handleSubmit = async () => {
        if (!selectedPayment) {
            Alert.alert('Atenção', 'Selecione o método de pagamento.');
            return;
        }

        // Validar campos de entrega
        if (orderType === 'delivery') {
            if (!deliveryPhone.trim()) {
                Alert.alert('Atenção', 'Informe o telefone para entrega.');
                return;
            }
            if (!deliveryAddress.trim()) {
                Alert.alert('Atenção', 'Informe o endereço de entrega.');
                return;
            }
        }

        let description = cart.map(c => {
            let line = `${c.qty}x ${c.name}`;
            if (c.notes) line += ` (${c.notes})`;
            return line;
        }).join('\n');

        // Adicionar dados de entrega ou de balcão na descrição
        if (orderType === 'delivery') {
            description += `\n\n📍 ENTREGA`;
            description += `\n📞 ${deliveryPhone}`;
            description += `\n🏠 ${deliveryAddress}`;
            if (deliveryRef.trim()) description += `\n📌 Ref: ${deliveryRef}`;
        } else {
            description += `\n\n🏪 BALCÃO`;
            // Inclui telefone de balcão se informado
            if (customerPhone.trim()) description += `\n📞 ${customerPhone}`;
        }

        // Se for Maquininha Bluetooth
        if (selectedPayment === 'card_reader') {
            const hasReader = pairedDevices.some(d => d.type === 'card_reader');
            if (!hasReader) {
                Alert.alert('Dispositivo não encontrado', 'Vá em Perfil > Dispositivos e conecte sua maquininha primeiro.');
                return;
            }

            try {
                setSubmitting(true);
                // 1000 = R$ 10,00 (O PlugPag geralmente usa centavos)
                const paymentResult: any = await paymentService.processPayment(cartTotal * 100);
                if (!paymentResult.success) {
                    Alert.alert('Erro no Cartão', paymentResult.message || 'O pagamento foi recusado ou cancelado.');
                    setSubmitting(false);
                    return;
                }
                // Se pagou, segue para criar a OS
            } catch (e) {
                Alert.alert('Erro de Conexão', 'Não foi possível comunicar com a maquininha.');
                setSubmitting(false);
                return;
            }
        }

        // Se for PIX, gerar QR Code primeiro
        if (selectedPayment === 'pix') {
            setStep('pix');
            setPixStatus('loading');
            try {
                const pixRes = await api.post('/pix/generate', {
                    amount: cartTotal,
                    description: `Pedido: ${description}`,
                    customer_name: customerName || 'Balcão',
                });

                if (pixRes.data.success) {
                    setPixData({
                        paymentId: pixRes.data.payment_id,
                        qrCodeImage: pixRes.data.qr_code_image,
                        qrCodeText: pixRes.data.qr_code_text,
                    });
                    setPixStatus('waiting');
                    startPixPolling(pixRes.data.payment_id, description);
                } else {
                    setPixStatus('error');
                    Alert.alert('Erro', pixRes.data.message || 'Falha ao gerar PIX.');
                }
            } catch (e: any) {
                setPixStatus('error');
                Alert.alert('Erro', e.response?.data?.message || 'Falha ao gerar cobrança PIX.');
            }
            return;
        }

        // Construir objeto 'parts' para o serviço do Laravel
        const parts: any = {};
        cart.forEach(item => {
            parts[item.id] = {
                selected: true,
                price: item.selling_price,
                quantity: item.qty
            };
        });

        // Outros métodos: criar pedido direto
        setSubmitting(true);
        try {
            const payload = {
                customer_name: customerName || 'Balcão',
                parts: parts,
                description,
                status: 'running', // 🍳 Vai direto para a cozinha!
                payment_method: selectedPayment,
            };

            const response = await api.post('/os', payload);
            setLastCreatedOS(response.data);
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);

            const resetState = () => {
                setCart([]);
                setStep('menu');
                setCustomerName('');
                setCustomerPhone('');
                setSelectedPayment(null);
                setOrderType('counter');
                setDeliveryAddress('');
                setDeliveryPhone('');
                setDeliveryRef('');
                setLastCreatedOS(null);
            };

            // Tentar imprimir automaticamente se houver impressora
            const hasPrinter = pairedDevices.some(d => d.type === 'printer');
            if (hasPrinter) {
                paymentService.printReceipt(`PEDIDO #${response.data.id}\n----------------\n${description}\n----------------\nTotal: R$ ${cartTotal.toFixed(2)}`);
            }

            Alert.alert(
                '🍳 Na Cozinha!',
                `Pedido #${response.data.id} enviado direto para a cozinha.\nTotal: R$ ${fmt(cartTotal)}\n\nDeseja imprimir a comanda?`,
                [
                    {
                        text: '🖨️ Imprimir',
                        onPress: () => {
                            router.push(`/os/${response.data.id}` as any);
                        }
                    },
                    {
                        text: '+ Novo Pedido',
                        onPress: resetState
                    },
                    {
                        text: 'Fechar',
                        onPress: () => router.back(),
                        style: 'cancel'
                    },
                ]
            );
        } catch (e: any) {
            Alert.alert('Erro', e.response?.data?.message || 'Falha ao criar pedido.');
        } finally { setSubmitting(false); }
    };

    const startPixPolling = (paymentId: string, description: string) => {
        if (pixPollingRef.current) clearInterval(pixPollingRef.current);
        pixPollingRef.current = setInterval(async () => {
            try {
                const res = await api.get(`/pix/status/${paymentId}`);
                if (res.data.is_paid) {
                    if (pixPollingRef.current) clearInterval(pixPollingRef.current);
                    setPixStatus('paid');
                    Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);

                    // Criar pedido automaticamente
                    const parts: any = {};
                    cart.forEach(item => {
                        parts[item.id] = {
                            selected: true,
                            price: item.selling_price,
                            quantity: item.qty
                        };
                    });

                    try {
                        await api.post('/os', {
                            customer_name: customerName || 'Balcão',
                            parts: parts,
                            description,
                            status: 'running', // 🍳 PIX pago → direto para a cozinha!
                            payment_method: 'pix',
                        });
                    } catch (e) { console.error('Erro ao criar OS após PIX:', e); }
                }
            } catch (e) { console.error('Erro polling PIX:', e); }
        }, 3000);
    };

    // Limpar polling ao desmontar
    useEffect(() => { return () => { if (pixPollingRef.current) clearInterval(pixPollingRef.current); }; }, []);

    if (loading) {
        return (
            <View style={[s.container, { backgroundColor: colors.background, justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#7367F0" />
                <Text style={[s.loadingText, { color: colors.subText }]}>Carregando cardápio...</Text>
            </View>
        );
    }

    return (
        <View style={[s.container, { backgroundColor: colors.background }]}>
            {/* ── Header ── */}
            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={[s.header, { paddingTop: insets.top + 10 }]}>
                <TouchableOpacity onPress={() => {
                    if (step === 'menu') router.back();
                    else if (step === 'cart') setStep('menu');
                    else if (step === 'payment') setStep('cart');
                    else if (step === 'pix') { if (pixPollingRef.current) clearInterval(pixPollingRef.current); setStep('payment'); setPixData(null); }
                }}>
                    <Ionicons name="chevron-back" size={28} color="#fff" />
                </TouchableOpacity>
                <Text style={s.headerTitle}>
                    {step === 'menu' ? 'Cardápio' : step === 'cart' ? 'Carrinho' : step === 'payment' ? 'Pagamento' : 'PIX'}
                </Text>
                {step === 'menu' && cartCount > 0 ? (
                    <TouchableOpacity onPress={() => setStep('cart')} style={s.cartBadge}>
                        <Ionicons name="cart" size={22} color="#fff" />
                        <View style={s.badgeCircle}>
                            <Text style={s.badgeText}>{cartCount}</Text>
                        </View>
                    </TouchableOpacity>
                ) : <View style={{ width: 40 }} />}
            </LinearGradient>

            {/* ═══════════════════ ETAPA 1: CARDÁPIO ═══════════════════ */}
            {step === 'menu' && (
                <>
                    {/* Barra de Pesquisa */}
                    <View style={[s.searchBar, { backgroundColor: colors.card }]}>
                        <View style={[s.searchInput, { backgroundColor: colors.background, borderColor: colors.border }]}>
                            <Ionicons name="search" size={18} color={colors.subText} />
                            <TextInput
                                style={[s.searchInputText, { color: colors.text }]}
                                placeholder="Nome ou código do item..."
                                placeholderTextColor={colors.subText}
                                value={searchQuery}
                                onChangeText={setSearchQuery}
                            />
                            {searchQuery.length > 0 && (
                                <TouchableOpacity onPress={() => setSearchQuery('')}>
                                    <Ionicons name="close-circle" size={18} color={colors.subText} />
                                </TouchableOpacity>
                            )}
                        </View>
                    </View>

                    {/* Categorias (ocultas quando pesquisando) */}
                    {!isSearching && (
                        <View style={[s.categoryBar, { backgroundColor: colors.card }]}>
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 15 }}>
                                {activeSections.map(cat => (
                                    <TouchableOpacity
                                        key={cat.id}
                                        style={[s.categoryTab, activeCatId === cat.id && s.categoryTabActive]}
                                        onPress={() => { Haptics.selectionAsync(); scrollToCategory(cat.id); }}
                                    >
                                        <Text style={[s.categoryTabText, { color: activeCatId === cat.id ? '#7367F0' : colors.subText }]}>
                                            {cat.title}
                                        </Text>
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>
                        </View>
                    )}

                    {/* Filtros de Ordenação */}
                    <View style={s.sortBar}>
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 15 }}>
                            {[
                                { id: 'default' as const, label: 'Padrão', icon: 'grid-outline' as const },
                                { id: 'az' as const, label: 'A → Z', icon: 'text-outline' as const },
                                { id: 'za' as const, label: 'Z → A', icon: 'text-outline' as const },
                                { id: 'expensive' as const, label: 'Mais Caro', icon: 'arrow-up-outline' as const },
                                { id: 'cheap' as const, label: 'Mais Barato', icon: 'arrow-down-outline' as const },
                            ].map(opt => (
                                <TouchableOpacity
                                    key={opt.id}
                                    style={[s.sortChip, sortBy === opt.id && s.sortChipActive]}
                                    onPress={() => { Haptics.selectionAsync(); setSortBy(opt.id); }}
                                >
                                    <Ionicons name={opt.icon} size={13} color={sortBy === opt.id ? '#fff' : colors.subText} style={{ marginRight: 4 }} />
                                    <Text style={[s.sortChipText, { color: sortBy === opt.id ? '#fff' : colors.subText }]}>{opt.label}</Text>
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>

                    {/* Grid de Produtos ou Seções */}
                    {isSearching ? (
                        <FlatList
                            data={activeItems}
                            keyExtractor={item => item.id.toString()}
                            numColumns={2}
                            columnWrapperStyle={s.gridRow}
                            contentContainerStyle={{ padding: 12, paddingBottom: 120 }}
                            showsVerticalScrollIndicator={false}
                            renderItem={({ item, index }) => <ProductItem item={item} index={index} cart={cart} addToCart={addToCart} setDetailItem={setDetailItem} colors={colors} />}
                            ListEmptyComponent={
                                <View style={s.emptyState}>
                                    <Ionicons name="search-outline" size={48} color={colors.subText + '44'} />
                                    <Text style={[s.emptyText, { color: colors.subText }]}>Nenhum item encontrado na busca</Text>
                                </View>
                            }
                        />
                    ) : (
                        <SectionList
                            ref={sectionListRef}
                            sections={activeSections}
                            keyExtractor={item => item.id.toString()}
                            stickySectionHeadersEnabled={false}
                            showsVerticalScrollIndicator={false}
                            contentContainerStyle={{ paddingBottom: 120 }}
                            renderSectionHeader={({ section: { title } }) => (
                                <View style={[s.sectionHeader, { backgroundColor: colors.background }]}>
                                    <View style={s.sectionHeaderLine} />
                                    <Text style={[s.sectionHeaderText, { color: colors.text }]}>{title}</Text>
                                    <View style={s.sectionHeaderLine} />
                                </View>
                            )}
                            renderSectionFooter={() => <View style={{ height: 20 }} />}
                            // Para manter o visual de GRID 2 Colunas em SectionList:
                            renderItem={({ item, index, section }) => {
                                if (index % 2 !== 0) return null;
                                const nextItem = section.data[index + 1];
                                return (
                                    <View style={s.gridRow}>
                                        <View style={{ flex: 1 }}>
                                            <ProductItem item={item} index={index} cart={cart} addToCart={addToCart} setDetailItem={setDetailItem} colors={colors} />
                                        </View>
                                        <View style={{ flex: 1 }}>
                                            {nextItem ? (
                                                <ProductItem item={nextItem} index={index + 1} cart={cart} addToCart={addToCart} setDetailItem={setDetailItem} colors={colors} />
                                            ) : <View style={s.productCardWrapper} />}
                                        </View>
                                    </View>
                                );
                            }}
                            onViewableItemsChanged={({ viewableItems }) => {
                                if (viewableItems.length > 0) {
                                    const firstVisible = viewableItems[0];
                                    if (firstVisible.section) {
                                        setActiveCatId((firstVisible.section as any).id);
                                    }
                                }
                            }}
                            viewabilityConfig={{ itemVisiblePercentThreshold: 50 }}
                        />
                    )}

                    {/* Barra inferior do carrinho */}
                    {cartCount > 0 && (
                        <Animated.View entering={FadeInDown.springify()} style={[s.bottomBar, { backgroundColor: colors.card, paddingBottom: insets.bottom + 10 }]}>
                            <View style={s.bottomBarInfo}>
                                <Text style={[s.bottomBarCount, { color: colors.subText }]}>{cartCount} {cartCount === 1 ? 'item' : 'itens'}</Text>
                                <Text style={[s.bottomBarTotal, { color: colors.text }]}>R$ {fmt(cartTotal)}</Text>
                            </View>
                            <TouchableOpacity onPress={() => setStep('cart')}>
                                <LinearGradient colors={['#7367F0', '#CE9FFC']} style={s.bottomBarBtn}>
                                    <Ionicons name="cart" size={18} color="#fff" style={{ marginRight: 6 }} />
                                    <Text style={s.bottomBarBtnText}>Ver Carrinho</Text>
                                </LinearGradient>
                            </TouchableOpacity>
                        </Animated.View>
                    )}

                    {/* Modal de Detalhes do Produto */}
                    <Modal visible={!!detailItem} animationType="slide" transparent>
                        <View style={s.detailOverlay}>
                            <View style={[s.detailContent, { backgroundColor: colors.card }]}>
                                <View style={s.detailHandle} />
                                <TouchableOpacity style={s.detailClose} onPress={() => setDetailItem(null)}>
                                    <Ionicons name="close-circle" size={28} color={colors.subText} />
                                </TouchableOpacity>

                                {detailItem && (
                                    <ScrollView showsVerticalScrollIndicator={false}>
                                        {detailItem.image_url ? (
                                            <Image source={{ uri: detailItem.image_url }} style={s.detailImage} contentFit="cover" transition={200} />
                                        ) : (
                                            <View style={[s.detailImagePlaceholder, { backgroundColor: '#7367F015' }]}>
                                                <Ionicons name="fast-food-outline" size={56} color="#7367F0" />
                                            </View>
                                        )}

                                        <View style={s.detailBody}>
                                            <Text style={[s.detailName, { color: colors.text }]}>{detailItem.name}</Text>
                                            <Text style={s.detailPrice}>R$ {fmt(detailItem.selling_price)}</Text>

                                            {detailItem.description && (
                                                <Text style={[s.detailDesc, { color: colors.subText }]}>{detailItem.description}</Text>
                                            )}

                                            {detailItem.ingredients && detailItem.ingredients.length > 0 && (
                                                <View style={s.ingredientsSection}>
                                                    <Text style={[s.ingredientsTitle, { color: colors.text }]}>🧾 O que vai neste produto:</Text>
                                                    {detailItem.ingredients.map((ing, idx) => (
                                                        <View key={idx} style={[s.ingredientRow, { borderBottomColor: colors.border }]}>
                                                            <View style={s.ingredientDot} />
                                                            <Text style={[s.ingredientName, { color: colors.text }]}>{ing.name}</Text>
                                                            <Text style={[s.ingredientQty, { color: colors.subText }]}>x{ing.qty}</Text>
                                                        </View>
                                                    ))}
                                                </View>
                                            )}

                                            {detailItem.quantity !== undefined && (
                                                <View style={[s.stockBadge, { backgroundColor: detailItem.quantity > 0 ? '#28C76F15' : '#EA545515' }]}>
                                                    <Ionicons name={detailItem.quantity > 0 ? 'checkmark-circle' : 'alert-circle'} size={16} color={detailItem.quantity > 0 ? '#28C76F' : '#EA5455'} />
                                                    <Text style={{ color: detailItem.quantity > 0 ? '#28C76F' : '#EA5455', fontWeight: '600', fontSize: 13, marginLeft: 6 }}>
                                                        {detailItem.quantity > 0 ? `${detailItem.quantity} disponíveis` : 'Esgotado'}
                                                    </Text>
                                                </View>
                                            )}
                                        </View>

                                        {/* Botão Adicionar */}
                                        <View style={{ padding: 20 }}>
                                            <TouchableOpacity
                                                onPress={() => { addToCart(detailItem); setDetailItem(null); }}
                                                disabled={detailItem.quantity <= 0}
                                                style={{ opacity: detailItem.quantity > 0 ? 1 : 0.4 }}
                                            >
                                                <LinearGradient colors={['#7367F0', '#CE9FFC']} style={s.detailAddBtn}>
                                                    <Ionicons name="cart" size={20} color="#fff" style={{ marginRight: 8 }} />
                                                    <Text style={s.detailAddBtnText}>Adicionar ao Pedido — R$ {fmt(detailItem.selling_price)}</Text>
                                                </LinearGradient>
                                            </TouchableOpacity>
                                        </View>
                                    </ScrollView>
                                )}
                            </View>
                        </View>
                    </Modal>
                </>
            )}

            {/* ═══════════════════ ETAPA 2: CARRINHO ═══════════════════ */}
            {step === 'cart' && (
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
                    <ScrollView contentContainerStyle={{ padding: 20, paddingBottom: 150 }} showsVerticalScrollIndicator={false}>

                        {/* Tipo de Pedido: Balcão ou Entrega */}
                        <View style={[s.cartSection, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <View style={s.cartSectionHeader}>
                                <Ionicons name="location-outline" size={18} color="#7367F0" />
                                <Text style={[s.cartSectionTitle, { color: colors.text }]}>Tipo do Pedido</Text>
                            </View>
                            <View style={s.orderTypeRow}>
                                <TouchableOpacity
                                    style={[s.orderTypeBtn, orderType === 'counter' && s.orderTypeBtnActive, { borderColor: orderType === 'counter' ? '#7367F0' : colors.border }]}
                                    onPress={() => { Haptics.selectionAsync(); setOrderType('counter'); }}
                                >
                                    <Ionicons name="storefront-outline" size={24} color={orderType === 'counter' ? '#7367F0' : colors.subText} />
                                    <Text style={[s.orderTypeBtnText, { color: orderType === 'counter' ? '#7367F0' : colors.subText }]}>Balcão</Text>
                                </TouchableOpacity>
                                <TouchableOpacity
                                    style={[s.orderTypeBtn, orderType === 'delivery' && s.orderTypeBtnActive, { borderColor: orderType === 'delivery' ? '#FF9F43' : colors.border }]}
                                    onPress={() => { Haptics.selectionAsync(); setOrderType('delivery'); }}
                                >
                                    <Ionicons name="bicycle-outline" size={24} color={orderType === 'delivery' ? '#FF9F43' : colors.subText} />
                                    <Text style={[s.orderTypeBtnText, { color: orderType === 'delivery' ? '#FF9F43' : colors.subText }]}>Entrega</Text>
                                </TouchableOpacity>
                            </View>
                        </View>

                        {/* Nome do Cliente + Telefone */}
                        <View style={[s.cartSection, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <View style={s.cartSectionHeader}>
                                <Ionicons name="person-outline" size={18} color="#7367F0" />
                                <Text style={[s.cartSectionTitle, { color: colors.text }]}>Cliente</Text>
                            </View>
                            {/* Campo Nome */}
                            <View style={[s.cartInputWrapper, { backgroundColor: colors.background, borderColor: colors.border, marginBottom: 10 }]}>
                                <Ionicons name="person-outline" size={16} color={colors.subText} style={{ marginRight: 10 }} />
                                <TextInput
                                    style={[s.cartInputInner, { color: colors.text }]}
                                    placeholder="Nome do cliente (opcional)"
                                    placeholderTextColor={colors.subText}
                                    value={customerName}
                                    onChangeText={setCustomerName}
                                />
                            </View>
                            {/* Campo Telefone */}
                            <View style={[s.cartInputWrapper, { backgroundColor: colors.background, borderColor: colors.border }]}>
                                <Ionicons name="call-outline" size={16} color={colors.subText} style={{ marginRight: 10 }} />
                                <TextInput
                                    style={[s.cartInputInner, { color: colors.text }]}
                                    placeholder="Telefone (opcional)"
                                    placeholderTextColor={colors.subText}
                                    value={customerPhone}
                                    onChangeText={setCustomerPhone}
                                    keyboardType="phone-pad"
                                />
                            </View>
                        </View>

                        {/* Dados de Entrega (condicional) */}
                        {orderType === 'delivery' && (
                            <Animated.View entering={FadeInDown.duration(300)}>
                                <View style={[s.cartSection, { backgroundColor: colors.card, borderColor: colors.border }]}>
                                    <View style={s.cartSectionHeader}>
                                        <Ionicons name="navigate-outline" size={18} color="#FF9F43" />
                                        <Text style={[s.cartSectionTitle, { color: colors.text }]}>Dados da Entrega</Text>
                                    </View>
                                    <TextInput
                                        style={[s.cartInput, { color: colors.text, backgroundColor: colors.background, borderColor: colors.border, marginBottom: 10 }]}
                                        placeholder="Telefone / WhatsApp *"
                                        placeholderTextColor={colors.subText}
                                        value={deliveryPhone}
                                        onChangeText={setDeliveryPhone}
                                        keyboardType="phone-pad"
                                    />
                                    <TextInput
                                        style={[s.cartInput, { color: colors.text, backgroundColor: colors.background, borderColor: colors.border, marginBottom: 10 }]}
                                        placeholder="Endereço completo *"
                                        placeholderTextColor={colors.subText}
                                        value={deliveryAddress}
                                        onChangeText={setDeliveryAddress}
                                    />
                                    <TextInput
                                        style={[s.cartInput, { color: colors.text, backgroundColor: colors.background, borderColor: colors.border }]}
                                        placeholder="Ponto de referência (opcional)"
                                        placeholderTextColor={colors.subText}
                                        value={deliveryRef}
                                        onChangeText={setDeliveryRef}
                                    />
                                </View>
                            </Animated.View>
                        )}

                        {/* Itens */}
                        <View style={[s.cartSection, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <View style={s.cartSectionHeader}>
                                <Ionicons name="receipt-outline" size={18} color="#7367F0" />
                                <Text style={[s.cartSectionTitle, { color: colors.text }]}>Itens do Pedido</Text>
                            </View>

                            {cart.map((item, idx) => (
                                <Animated.View key={item.id} entering={FadeIn.delay(idx * 80)}>
                                    <View style={[s.cartItem, idx < cart.length - 1 && { borderBottomWidth: 1, borderBottomColor: colors.border }]}>
                                        <View style={s.cartItemTop}>
                                            {item.image_url ? (
                                                <Image source={{ uri: item.image_url }} style={s.cartItemImage} contentFit="cover" />
                                            ) : (
                                                <View style={[s.cartItemImagePlaceholder, { backgroundColor: '#7367F015' }]}>
                                                    <Ionicons name="fast-food" size={20} color="#7367F0" />
                                                </View>
                                            )}
                                            <View style={s.cartItemInfo}>
                                                <Text style={[s.cartItemName, { color: colors.text }]} numberOfLines={1}>{item.name}</Text>
                                                <Text style={s.cartItemPrice}>R$ {fmt(item.selling_price)}</Text>
                                            </View>
                                            <View style={s.qtyControls}>
                                                <TouchableOpacity onPress={() => item.qty === 1 ? removeFromCart(item.id) : updateQty(item.id, -1)} style={[s.qtyBtn, { borderColor: colors.border }]}>
                                                    <Ionicons name={item.qty === 1 ? "trash-outline" : "remove"} size={16} color={item.qty === 1 ? '#EA5455' : colors.text} />
                                                </TouchableOpacity>
                                                <Text style={[s.qtyNumber, { color: colors.text }]}>{item.qty}</Text>
                                                <TouchableOpacity onPress={() => updateQty(item.id, 1)} style={[s.qtyBtn, { borderColor: '#7367F0', backgroundColor: '#7367F010' }]}>
                                                    <Ionicons name="add" size={16} color="#7367F0" />
                                                </TouchableOpacity>
                                            </View>
                                        </View>

                                        {/* Observações */}
                                        <TouchableOpacity
                                            style={[s.notesBtn, { backgroundColor: colors.background }]}
                                            onPress={() => setNotesModal({ itemId: item.id, notes: item.notes })}
                                        >
                                            <Ionicons name="chatbubble-ellipses-outline" size={14} color={item.notes ? '#7367F0' : colors.subText} />
                                            <Text style={[s.notesBtnText, { color: item.notes ? '#7367F0' : colors.subText }]} numberOfLines={1}>
                                                {item.notes || 'Adicionar observação (sem cebola, extra queijo...)'}
                                            </Text>
                                        </TouchableOpacity>

                                        <Text style={[s.cartItemSubtotal, { color: colors.text }]}>
                                            Subtotal: R$ {fmt(item.selling_price * item.qty)}
                                        </Text>
                                    </View>
                                </Animated.View>
                            ))}
                        </View>
                    </ScrollView>

                    {/* Footer Carrinho */}
                    <View style={[s.bottomBar, { backgroundColor: colors.card, paddingBottom: insets.bottom + 10 }]}>
                        <View style={s.bottomBarInfo}>
                            <Text style={[s.bottomBarCount, { color: colors.subText }]}>{cartCount} {cartCount === 1 ? 'item' : 'itens'}</Text>
                            <Text style={[s.bottomBarTotal, { color: colors.text }]}>R$ {fmt(cartTotal)}</Text>
                        </View>
                        <TouchableOpacity onPress={() => setStep('payment')}>
                            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={s.bottomBarBtn}>
                                <Text style={s.bottomBarBtnText}>Pagamento</Text>
                                <Ionicons name="chevron-forward" size={18} color="#fff" style={{ marginLeft: 4 }} />
                            </LinearGradient>
                        </TouchableOpacity>
                    </View>

                </KeyboardAvoidingView>
            )}

            {/* ═══════════════════ ETAPA 3: PAGAMENTO ═══════════════════ */}
            {step === 'payment' && (
                <>
                    <ScrollView contentContainerStyle={{ padding: 20, paddingBottom: 150 }} showsVerticalScrollIndicator={false}>
                        {/* Resumo */}
                        <View style={[s.cartSection, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <View style={s.cartSectionHeader}>
                                <Ionicons name="receipt-outline" size={18} color="#7367F0" />
                                <Text style={[s.cartSectionTitle, { color: colors.text }]}>Resumo</Text>
                            </View>
                            {cart.map(item => (
                                <View key={item.id} style={s.summaryRow}>
                                    <Text style={[s.summaryItemName, { color: colors.text }]}>{item.qty}x {item.name}</Text>
                                    <Text style={[s.summaryItemPrice, { color: colors.text }]}>R$ {fmt(item.selling_price * item.qty)}</Text>
                                </View>
                            ))}
                            <View style={[s.summaryTotalRow, { borderTopColor: colors.border }]}>
                                <Text style={[s.summaryTotalLabel, { color: colors.text }]}>Total</Text>
                                <Text style={s.summaryTotalValue}>R$ {fmt(cartTotal)}</Text>
                            </View>
                        </View>

                        {/* Método de Pagamento */}
                        <View style={[s.cartSection, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <View style={s.cartSectionHeader}>
                                <Ionicons name="wallet-outline" size={18} color="#7367F0" />
                                <Text style={[s.cartSectionTitle, { color: colors.text }]}>Método de Pagamento</Text>
                            </View>
                            <View style={s.paymentGrid}>
                                {PAYMENT_METHODS.map((method, idx) => (
                                    <Animated.View key={method.id} entering={ZoomIn.delay(idx * 80).duration(300)} style={s.paymentCardWrapper}>
                                        <TouchableOpacity
                                            style={[
                                                s.paymentCard,
                                                { backgroundColor: colors.background, borderColor: selectedPayment === method.id ? method.color : colors.border },
                                                selectedPayment === method.id && { borderWidth: 2.5 }
                                            ]}
                                            onPress={() => { Haptics.selectionAsync(); setSelectedPayment(method.id); }}
                                        >
                                            <View style={[s.paymentIconWrap, { backgroundColor: method.color + '15' }]}>
                                                <Ionicons name={method.icon as any} size={28} color={method.color} />
                                            </View>
                                            <Text style={[s.paymentLabel, { color: colors.text }]}>{method.label}</Text>
                                            {selectedPayment === method.id && (
                                                <Ionicons name="checkmark-circle" size={18} color={method.color} style={s.paymentCheck} />
                                            )}
                                        </TouchableOpacity>
                                    </Animated.View>
                                ))}
                            </View>
                        </View>
                    </ScrollView>

                    {/* Botão Confirmar */}
                    <View style={[s.bottomBar, { backgroundColor: colors.card, paddingBottom: insets.bottom + 10 }]}>
                        <TouchableOpacity
                            style={[s.confirmFullBtn, { opacity: selectedPayment && !submitting ? 1 : 0.5 }]}
                            onPress={handleSubmit}
                            disabled={!selectedPayment || submitting}
                        >
                            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={s.confirmFullBtnGradient}>
                                {submitting ? (
                                    <ActivityIndicator color="#fff" />
                                ) : (
                                    <>
                                        <Ionicons name="flame" size={22} color="#fff" style={{ marginRight: 8 }} />
                                        <Text style={s.confirmFullBtnText}>🍳 Enviar para Cozinha — R$ {fmt(cartTotal)}</Text>
                                    </>
                                )}
                            </LinearGradient>
                        </TouchableOpacity>
                    </View>
                </>
            )}

            {/* ═══════════════════ ETAPA 4: PIX ═══════════════════ */}
            {step === 'pix' && (
                <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 }}>
                    {/* Loading - Gerando QR Code */}
                    {pixStatus === 'loading' && (
                        <Animated.View entering={FadeIn} style={{ alignItems: 'center' }}>
                            <ActivityIndicator size="large" color="#7367F0" />
                            <Text style={[s.pixStatusText, { color: colors.text }]}>Gerando QR Code PIX...</Text>
                            <Text style={[s.pixSubText, { color: colors.subText }]}>Aguarde um momento</Text>
                        </Animated.View>
                    )}

                    {/* QR Code - Aguardando pagamento */}
                    {pixStatus === 'waiting' && pixData && (
                        <Animated.View entering={FadeInDown.duration(400)} style={{ alignItems: 'center', width: '100%' }}>
                            <View style={[s.pixQrContainer, { backgroundColor: colors.card, borderColor: colors.border }]}>
                                <Text style={[s.pixTitle, { color: colors.text }]}>Escaneie o QR Code</Text>
                                <Text style={[s.pixAmount, { color: '#7367F0' }]}>R$ {fmt(cartTotal)}</Text>

                                <View style={s.pixQrImageWrap}>
                                    <Image
                                        source={{ uri: `data:image/png;base64,${pixData.qrCodeImage}` }}
                                        style={s.pixQrImage}
                                        contentFit="contain"
                                    />
                                </View>

                                {/* Copia e Cola */}
                                <TouchableOpacity
                                    style={[s.pixCopyBtn, { backgroundColor: colors.background, borderColor: colors.border }]}
                                    onPress={() => {
                                        try {
                                            const Clipboard = require('react-native').Clipboard;
                                            Clipboard.setString(pixData.qrCodeText);
                                            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
                                            Alert.alert('Copiado! 📋', 'Código PIX copiado para a área de transferência.');
                                        } catch {
                                            Alert.alert('PIX Copia e Cola', pixData.qrCodeText);
                                        }
                                    }}
                                >
                                    <Ionicons name="copy-outline" size={18} color="#7367F0" />
                                    <Text style={[s.pixCopyBtnText, { color: '#7367F0' }]}>Copiar código PIX</Text>
                                </TouchableOpacity>
                            </View>

                            {/* Indicador de aguardando */}
                            <Animated.View entering={FadeIn.delay(500)} style={s.pixWaitingBar}>
                                <ActivityIndicator size="small" color="#7367F0" />
                                <Text style={[s.pixWaitingText, { color: colors.subText }]}>Aguardando pagamento...</Text>
                            </Animated.View>
                        </Animated.View>
                    )}

                    {/* Sucesso! */}
                    {pixStatus === 'paid' && (
                        <Animated.View entering={ZoomIn.springify()} style={{ alignItems: 'center' }}>
                            <View style={s.pixSuccessCircle}>
                                <Ionicons name="checkmark" size={56} color="#fff" />
                            </View>
                            <Text style={[s.pixSuccessTitle, { color: colors.text }]}>Pagamento Confirmado! 🎉</Text>
                            <Text style={[s.pixSuccessAmount, { color: '#28C76F' }]}>R$ {fmt(cartTotal)}</Text>
                            <Text style={[s.pixSubText, { color: colors.subText }]}>Pedido enviado para a cozinha</Text>

                            <View style={{ flexDirection: 'row', marginTop: 30, width: '100%' }}>
                                <TouchableOpacity
                                    style={{ flex: 1, marginRight: 8 }}
                                    onPress={() => { setCart([]); setStep('menu'); setCustomerName(''); setCustomerPhone(''); setSelectedPayment(null); setPixData(null); setPixStatus('loading'); setOrderType('counter'); setDeliveryAddress(''); setDeliveryPhone(''); setDeliveryRef(''); }}
                                >
                                    <LinearGradient colors={['#7367F0', '#CE9FFC']} style={s.pixActionBtn}>
                                        <Text style={s.pixActionBtnText}>Novo Pedido</Text>
                                    </LinearGradient>
                                </TouchableOpacity>
                                <TouchableOpacity
                                    style={[s.pixActionBtnOutline, { flex: 1, marginLeft: 8, borderColor: colors.border }]}
                                    onPress={() => router.back()}
                                >
                                    <Text style={[s.pixActionBtnOutlineText, { color: colors.text }]}>Voltar</Text>
                                </TouchableOpacity>
                            </View>
                        </Animated.View>
                    )}

                    {/* Erro */}
                    {pixStatus === 'error' && (
                        <Animated.View entering={FadeIn} style={{ alignItems: 'center' }}>
                            <View style={[s.pixSuccessCircle, { backgroundColor: '#EA5455' }]}>
                                <Ionicons name="close" size={56} color="#fff" />
                            </View>
                            <Text style={[s.pixSuccessTitle, { color: colors.text }]}>Erro ao gerar PIX</Text>
                            <Text style={[s.pixSubText, { color: colors.subText }]}>Tente novamente ou escolha outro método</Text>

                            <TouchableOpacity
                                style={{ marginTop: 30, width: '100%' }}
                                onPress={() => { setStep('payment'); setPixData(null); setPixStatus('loading'); }}
                            >
                                <LinearGradient colors={['#7367F0', '#CE9FFC']} style={s.pixActionBtn}>
                                    <Text style={s.pixActionBtnText}>Voltar ao Pagamento</Text>
                                </LinearGradient>
                            </TouchableOpacity>
                        </Animated.View>
                    )}
                </View>
            )}

            {/* ═══════════════════ MODAL DE OBSERVAÇÕES ═══════════════════ */}
            <Modal
                visible={!!notesModal}
                animationType="slide"
                transparent
                onRequestClose={() => setNotesModal(null)}
            >
                <TouchableOpacity
                    style={s.notesModalOverlay}
                    activeOpacity={1}
                    onPress={() => setNotesModal(null)}
                >
                    <KeyboardAvoidingView
                        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
                        style={{ width: '100%' }}
                    >
                        <TouchableOpacity
                            activeOpacity={1}
                            style={[s.notesModalContent, { backgroundColor: colors.card }]}
                            onPress={(e) => e.stopPropagation()}
                        >
                            <View style={s.notesModalHandle} />
                            <TouchableOpacity
                                style={s.notesModalClose}
                                onPress={() => setNotesModal(null)}
                            >
                                <Ionicons name="close" size={24} color={colors.subText} />
                            </TouchableOpacity>

                            <Text style={[s.notesModalTitle, { color: colors.text }]}>Observações do Item</Text>

                            <TextInput
                                style={[s.notesModalInput, { color: colors.text, backgroundColor: colors.background, borderColor: colors.border }]}
                                placeholder="Ex: Sem cebola, extra queijo, molho à parte..."
                                placeholderTextColor={colors.subText}
                                multiline
                                numberOfLines={4}
                                value={notesModal?.notes || ''}
                                onChangeText={(t) => setNotesModal(prev => prev ? { ...prev, notes: t } : null)}
                                autoFocus
                            />

                            <TouchableOpacity
                                style={s.notesModalSave}
                                onPress={() => {
                                    if (notesModal) { updateNotes(notesModal.itemId, notesModal.notes); }
                                    setNotesModal(null);
                                }}
                            >
                                <LinearGradient colors={['#7367F0', '#CE9FFC']} style={s.notesModalSaveGradient}>
                                    <Text style={s.notesModalSaveText}>Salvar Observação</Text>
                                </LinearGradient>
                            </TouchableOpacity>
                        </TouchableOpacity>
                    </KeyboardAvoidingView>
                </TouchableOpacity>
            </Modal>
        </View>
    );
}

// ═══════════════════════════════════════════════════════════════════════════
// FORMULÁRIO ORIGINAL (Outros nichos)
// ═══════════════════════════════════════════════════════════════════════════
function OriginalCreateOrder() {
    const router = useRouter();
    const { colors } = useTheme();
    const { labels } = useNiche();

    const [loading, setLoading] = useState(false);
    const [productsLoading, setProductsLoading] = useState(false);
    const [products, setProducts] = useState<any[]>([]);
    const [customerName, setCustomerName] = useState('');
    const [productId, setProductId] = useState('');
    const [observations, setObservations] = useState('');
    const [showProductModal, setShowProductModal] = useState(false);
    const [productSearch, setProductSearch] = useState('');
    const [filteredProducts, setFilteredProducts] = useState<any[]>([]);

    useEffect(() => { fetchProducts(); }, []);

    const fetchProducts = async () => {
        setProductsLoading(true);
        try {
            const response = await api.get('/inventory/items-list');
            const data = Array.isArray(response.data) ? response.data : (response.data.data || []);
            const filtered = data.filter((item: any) => !item.is_ingredient);
            setProducts(filtered);
            setFilteredProducts(filtered);
        } catch (error) { console.error(error); }
        finally { setProductsLoading(false); }
    };

    useEffect(() => {
        const lowerQuery = productSearch.toLowerCase();
        setFilteredProducts(products.filter(p =>
            p.name.toLowerCase().includes(lowerQuery) ||
            (p.sku && p.sku.toLowerCase().includes(lowerQuery))
        ));
    }, [productSearch, products]);

    const handleSelectProduct = (item: any) => { setProductId(item.id.toString()); setShowProductModal(false); setProductSearch(''); };
    const getSelectedProductName = () => { const item = products.find(p => p.id.toString() === productId); return item ? item.name : 'Clique para selecionar...'; };

    const handleSubmit = async () => {
        if (!customerName || !productId) { Alert.alert("Atenção", "Preencha o Nome do Cliente e escolha o Pedido."); return; }
        setLoading(true);
        try {
            const payload = { customer_name: customerName, product_id: productId, description: observations, status: 'pending' };
            const response = await api.post('/os', payload);
            const sendWhatsApp = () => {
                const message = response.data.share_message;
                const phone = response.data.client_phone || '';
                const url = `whatsapp://send?text=${encodeURIComponent(message)}&phone=${phone}`;
                Linking.openURL(url).catch(() => Alert.alert("Erro", "Não foi possível abrir o WhatsApp."));
            };
            Alert.alert("Sucesso", "Criado com sucesso!", [
                { text: "Vistoria/Fotos", onPress: () => router.push({ pathname: '/os/checklist', params: { osId: response.data.id } }) },
                { text: "WhatsApp 💬", onPress: () => { sendWhatsApp(); router.back(); } },
                { text: "Concluído", onPress: () => router.back(), style: 'cancel' }
            ]);
        } catch (error: any) { Alert.alert("Erro", error.response?.data?.message || "Erro ao criar."); }
        finally { setLoading(false); }
    };

    return (
        <KeyboardAvoidingView behavior={Platform.OS === "ios" ? "padding" : "height"} style={[orig.container, { backgroundColor: colors.background }]}>
            <View style={[orig.header, { backgroundColor: colors.card }]}>
                <TouchableOpacity onPress={() => router.back()} style={orig.backButton}><Ionicons name="arrow-back" size={24} color={colors.text} /></TouchableOpacity>
                <Text style={[orig.headerTitle, { color: colors.text }]}>{labels?.new_entity || 'Novo Registro'}</Text>
                <View style={{ width: 32 }} />
            </View>
            <ScrollView contentContainerStyle={orig.scrollContent} showsVerticalScrollIndicator={false}>
                <View style={orig.formSection}>
                    <View style={orig.sectionHeaderRow}><Ionicons name="fast-food-outline" size={20} color={colors.primary} /><Text style={[orig.sectionTitle, { color: colors.primary }]}>Detalhes do Atendimento</Text></View>
                    <View style={orig.inputGroup}>
                        <View style={orig.labelRow}><Ionicons name="person-outline" size={16} color={colors.subText} /><Text style={[orig.label, { color: colors.subText }]}>Nome do Cliente</Text></View>
                        <View style={[orig.inputContainer, { borderColor: colors.border, backgroundColor: colors.background }]}><TextInput style={[orig.input, { color: colors.text }]} placeholder="Ex: João da Silva" placeholderTextColor={colors.subText} value={customerName} onChangeText={setCustomerName} /></View>
                    </View>
                    <View style={orig.inputGroup}>
                        <View style={orig.labelRow}><Ionicons name="cube-outline" size={16} color={colors.subText} /><Text style={[orig.label, { color: colors.subText }]}>Serviço / Produto</Text></View>
                        <TouchableOpacity style={[orig.pickerWrapper, { borderColor: colors.border, backgroundColor: colors.background, paddingHorizontal: 15 }]} onPress={() => setShowProductModal(true)}>
                            <Text style={{ fontSize: 16, color: productId ? colors.text : colors.subText }}>{getSelectedProductName()}</Text>
                            <Ionicons name="search" size={20} color={colors.subText} style={{ position: 'absolute', right: 15 }} />
                        </TouchableOpacity>
                    </View>
                    <View style={orig.inputGroup}>
                        <View style={orig.labelRow}><Ionicons name="chatbox-ellipses-outline" size={16} color={colors.subText} /><Text style={[orig.label, { color: colors.subText }]}>Observações (Ex: Sem cebola)</Text></View>
                        <View style={[orig.textAreaContainer, { borderColor: colors.border, backgroundColor: colors.background }]}><TextInput style={[orig.textArea, { color: colors.text }]} placeholder="Caprichar no molho, tirar o milho..." placeholderTextColor={colors.subText} multiline numberOfLines={3} value={observations} onChangeText={setObservations} /></View>
                    </View>
                </View>
            </ScrollView>
            <View style={[orig.footer, { backgroundColor: colors.card, borderTopColor: colors.border }]}>
                <TouchableOpacity style={[orig.submitButton, { backgroundColor: colors.primary, opacity: (loading || productsLoading) ? 0.7 : 1 }]} onPress={handleSubmit} disabled={loading || productsLoading}>
                    {loading ? <ActivityIndicator color="#fff" /> : <><Ionicons name="checkmark-circle-outline" size={22} color="#fff" /><Text style={orig.submitButtonText}>Confirmar</Text></>}
                </TouchableOpacity>
            </View>
            {showProductModal && (
                <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', padding: 20, zIndex: 100 }]}>
                    <View style={{ backgroundColor: colors.card, borderRadius: 16, height: '80%', padding: 20 }}>
                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
                            <Text style={{ fontSize: 18, fontWeight: 'bold', color: colors.text }}>Escolher Item</Text>
                            <TouchableOpacity onPress={() => setShowProductModal(false)}><Ionicons name="close" size={24} color={colors.text} /></TouchableOpacity>
                        </View>
                        <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: colors.background, borderRadius: 10, paddingHorizontal: 10, marginBottom: 15, borderWidth: 1, borderColor: colors.border }}>
                            <Ionicons name="search" size={20} color={colors.subText} />
                            <TextInput style={{ flex: 1, padding: 12, fontSize: 16, color: colors.text }} placeholder="Pesquisar..." placeholderTextColor={colors.subText} value={productSearch} onChangeText={setProductSearch} autoFocus />
                        </View>
                        <FlatList data={filteredProducts} keyExtractor={item => item.id.toString()} renderItem={({ item }) => (
                            <TouchableOpacity style={{ paddingVertical: 15, borderBottomWidth: 1, borderBottomColor: colors.border }} onPress={() => handleSelectProduct(item)}>
                                <View>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                                        {item.sku ? (
                                            <View style={{ backgroundColor: colors.primary + '15', paddingHorizontal: 6, paddingVertical: 2, borderRadius: 4 }}>
                                                <Text style={{ fontSize: 11, fontWeight: 'bold', color: colors.primary }}>#{item.sku}</Text>
                                            </View>
                                        ) : null}
                                        <Text style={{ fontSize: 16, fontWeight: '600', color: colors.text }}>{item.name}</Text>
                                    </View>
                                    <Text style={{ fontSize: 13, color: colors.subText }}>Disponível: {item.quantity}</Text>
                                </View>
                            </TouchableOpacity>
                        )} ListEmptyComponent={<Text style={{ textAlign: 'center', marginTop: 20, color: colors.subText }}>Nenhum item encontrado.</Text>} />
                    </View>
                </View>
            )}
        </KeyboardAvoidingView>
    );
}

// ═══════════════════════════════════════════════════════════════════════════
// ESTILOS — PDV Food Service
// ═══════════════════════════════════════════════════════════════════════════
const s = StyleSheet.create({
    container: { flex: 1 },
    loadingText: { marginTop: 10, fontSize: 14 },

    sectionHeader: { flexDirection: 'row', alignItems: 'center', paddingVertical: 15, paddingHorizontal: 20, gap: 10 },
    sectionHeaderLine: { flex: 1, height: 1.5, backgroundColor: 'rgba(115, 103, 240, 0.15)' },
    sectionHeaderText: { fontSize: 13, fontWeight: '900', textTransform: 'uppercase', letterSpacing: 1.5, color: '#7367F0' },


    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingBottom: 15, paddingHorizontal: 15 },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#fff' },
    cartBadge: { position: 'relative', padding: 5 },
    badgeCircle: { position: 'absolute', top: 0, right: 0, backgroundColor: '#EA5455', width: 18, height: 18, borderRadius: 9, justifyContent: 'center', alignItems: 'center' },
    badgeText: { color: '#fff', fontSize: 10, fontWeight: 'bold' },

    categoryBar: { paddingVertical: 12 },
    categoryTab: { paddingHorizontal: 18, paddingVertical: 10, borderRadius: 20, marginRight: 8 },
    categoryTabActive: { backgroundColor: '#7367F015', borderWidth: 1, borderColor: '#7367F040' },
    categoryTabText: { fontSize: 14, fontWeight: '700' },

    gridRow: { justifyContent: 'space-between', paddingHorizontal: 4 },
    productCardWrapper: { width: '48%', marginBottom: 14 },
    productCard: { borderRadius: 16, borderWidth: 1, overflow: 'hidden', position: 'relative' },
    productImage: { width: '100%', height: 120, borderTopLeftRadius: 16, borderTopRightRadius: 16 },
    productImagePlaceholder: { width: '100%', height: 120, justifyContent: 'center', alignItems: 'center' },
    productInfo: { padding: 12 },
    productName: { fontSize: 14, fontWeight: '700', marginBottom: 4 },
    productPrice: { fontSize: 16, fontWeight: '900', color: '#7367F0' },
    addBtn: { position: 'absolute', bottom: 10, right: 10 },
    addBtnGradient: { width: 32, height: 32, borderRadius: 16, justifyContent: 'center', alignItems: 'center' },
    qtyBadge: { position: 'absolute', top: 8, right: 8, backgroundColor: '#7367F0', width: 24, height: 24, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
    qtyBadgeText: { color: '#fff', fontSize: 12, fontWeight: 'bold' },

    emptyState: { alignItems: 'center', marginTop: 60 },
    emptyText: { marginTop: 10, fontSize: 14 },

    bottomBar: { position: 'absolute', bottom: 0, left: 0, right: 0, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 20, paddingTop: 15, borderTopWidth: 1, borderTopColor: '#eee' },
    bottomBarInfo: {},
    bottomBarCount: { fontSize: 12, fontWeight: '600' },
    bottomBarTotal: { fontSize: 20, fontWeight: '900' },
    bottomBarBtn: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 20, paddingVertical: 14, borderRadius: 14 },
    bottomBarBtnText: { color: '#fff', fontWeight: 'bold', fontSize: 15 },

    cartSection: { borderRadius: 16, borderWidth: 1, padding: 16, marginBottom: 16 },
    cartSectionHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 14, gap: 8 },
    cartSectionTitle: { fontSize: 16, fontWeight: '700' },
    cartInput: { borderWidth: 1, borderRadius: 12, paddingHorizontal: 16, height: 50, fontSize: 16 },
    // Novo: input com ícone integrado como prefixo
    cartInputWrapper: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderRadius: 12, paddingHorizontal: 14, height: 50 },
    cartInputInner: { flex: 1, fontSize: 16 },

    cartItem: { paddingVertical: 14 },
    cartItemTop: { flexDirection: 'row', alignItems: 'center' },
    cartItemImage: { width: 50, height: 50, borderRadius: 10 },
    cartItemImagePlaceholder: { width: 50, height: 50, borderRadius: 10, justifyContent: 'center', alignItems: 'center' },
    cartItemInfo: { flex: 1, marginLeft: 12 },
    cartItemName: { fontSize: 15, fontWeight: '700' },
    cartItemPrice: { fontSize: 13, color: '#7367F0', fontWeight: '600', marginTop: 2 },
    cartItemSubtotal: { fontSize: 13, fontWeight: '700', textAlign: 'right', marginTop: 6 },

    qtyControls: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    qtyBtn: { width: 32, height: 32, borderRadius: 10, borderWidth: 1, justifyContent: 'center', alignItems: 'center' },
    qtyNumber: { fontSize: 16, fontWeight: 'bold', minWidth: 20, textAlign: 'center' },

    notesBtn: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, gap: 6, marginTop: 8 },
    notesBtnText: { fontSize: 12, flex: 1 },

    notesModalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    notesModalContent: { borderTopLeftRadius: 30, borderTopRightRadius: 30, padding: 25, paddingBottom: 40 },
    notesModalHandle: { width: 40, height: 4, borderRadius: 2, backgroundColor: '#ddd', alignSelf: 'center', marginBottom: 20 },
    notesModalClose: { position: 'absolute', top: 20, right: 20, zIndex: 10 },
    notesModalTitle: { fontSize: 18, fontWeight: 'bold', marginBottom: 15 },
    notesModalInput: { borderWidth: 1, borderRadius: 12, padding: 15, fontSize: 15, minHeight: 80, textAlignVertical: 'top' },
    notesModalSave: { marginTop: 15 },
    notesModalSaveGradient: { paddingVertical: 14, borderRadius: 14, alignItems: 'center' },
    notesModalSaveText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },

    summaryRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8 },
    summaryItemName: { fontSize: 14, fontWeight: '500' },
    summaryItemPrice: { fontSize: 14, fontWeight: '600' },
    summaryTotalRow: { flexDirection: 'row', justifyContent: 'space-between', paddingTop: 12, marginTop: 8, borderTopWidth: 1 },
    summaryTotalLabel: { fontSize: 16, fontWeight: '700' },
    summaryTotalValue: { fontSize: 20, fontWeight: '900', color: '#7367F0' },

    paymentGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', width: '100%' },
    paymentCardWrapper: { width: '48%', marginBottom: 12, minWidth: '45%' },
    paymentCard: { width: '100%', borderRadius: 20, paddingVertical: 16, paddingHorizontal: 4, alignItems: 'center', borderWidth: 1.5, position: 'relative', backgroundColor: '#fff', minHeight: 100, justifyContent: 'center' },
    paymentIconWrap: { width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center', marginBottom: 10 },
    paymentLabel: { fontSize: 15, fontWeight: '700', textAlign: 'center', width: '100%' },
    paymentCheck: { position: 'absolute', top: 10, right: 10 },

    confirmFullBtn: { width: '100%' },
    confirmFullBtnGradient: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 16, borderRadius: 16 },
    confirmFullBtnText: { color: '#fff', fontSize: 16, fontWeight: 'bold' },

    // Barra de pesquisa
    searchBar: { paddingHorizontal: 15, paddingTop: 12, paddingBottom: 4 },

    // Filtros de ordena\u00e7\u00e3o
    sortBar: { paddingVertical: 8 },
    sortChip: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 20, backgroundColor: 'transparent', marginRight: 8, borderWidth: 1, borderColor: '#ddd' },
    sortChipActive: { backgroundColor: '#7367F0', borderColor: '#7367F0' },
    sortChipText: { fontSize: 12, fontWeight: '600' },

    // Tipo de Pedido (Balcão / Entrega)
    orderTypeRow: { flexDirection: 'row', justifyContent: 'space-between' },
    orderTypeBtn: { flex: 1, alignItems: 'center', paddingVertical: 16, borderRadius: 14, borderWidth: 1.5, marginHorizontal: 4 },
    orderTypeBtnActive: { backgroundColor: '#7367F008' },
    orderTypeBtnText: { fontSize: 14, fontWeight: '700', marginTop: 6 },
    searchInput: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderRadius: 12, paddingHorizontal: 14, height: 44, gap: 8 },
    searchInputText: { flex: 1, fontSize: 15 },

    // Modal de Detalhes do Produto
    detailOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    detailContent: { borderTopLeftRadius: 30, borderTopRightRadius: 30, maxHeight: '90%', overflow: 'hidden' },
    detailHandle: { width: 40, height: 4, borderRadius: 2, backgroundColor: 'rgba(0,0,0,0.1)', alignSelf: 'center', marginTop: 12, marginBottom: 8, zIndex: 11 },
    detailClose: { position: 'absolute', top: 12, right: 16, zIndex: 12, backgroundColor: 'rgba(255,255,255,0.7)', borderRadius: 20 },
    detailImage: { width: '100%', height: 260, borderTopLeftRadius: 30, borderTopRightRadius: 30 },
    detailImagePlaceholder: { width: '100%', height: 260, justifyContent: 'center', alignItems: 'center', borderTopLeftRadius: 30, borderTopRightRadius: 30 },
    detailBody: { padding: 20 },
    detailName: { fontSize: 22, fontWeight: 'bold', marginBottom: 6 },
    detailPrice: { fontSize: 24, fontWeight: '900', color: '#7367F0', marginBottom: 12 },
    detailDesc: { fontSize: 14, lineHeight: 22, marginBottom: 16 },
    detailAddBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 16, borderRadius: 16 },
    detailAddBtnText: { color: '#fff', fontSize: 16, fontWeight: 'bold' },

    // Ingredientes
    ingredientsSection: { marginBottom: 16 },
    ingredientsTitle: { fontSize: 15, fontWeight: '700', marginBottom: 10 },
    ingredientRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1 },
    ingredientDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: '#7367F0', marginRight: 10 },
    ingredientName: { flex: 1, fontSize: 14, fontWeight: '500' },
    ingredientQty: { fontSize: 13, fontWeight: '600' },

    // Estoque
    stockBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, alignSelf: 'flex-start' },

    // PIX
    pixStatusText: { fontSize: 18, fontWeight: 'bold', marginTop: 20 },
    pixSubText: { fontSize: 14, marginTop: 6 },
    pixQrContainer: { borderRadius: 24, borderWidth: 1, padding: 24, alignItems: 'center', width: '100%' },
    pixTitle: { fontSize: 16, fontWeight: '600', marginBottom: 4 },
    pixAmount: { fontSize: 32, fontWeight: '900', marginBottom: 20 },
    pixQrImageWrap: { backgroundColor: '#fff', borderRadius: 16, padding: 16, marginBottom: 20 },
    pixQrImage: { width: 220, height: 220 },
    pixCopyBtn: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderRadius: 12, paddingHorizontal: 20, paddingVertical: 12 },
    pixCopyBtnText: { fontWeight: '700', fontSize: 14, marginLeft: 8 },
    pixWaitingBar: { flexDirection: 'row', alignItems: 'center', marginTop: 20 },
    pixWaitingText: { marginLeft: 10, fontSize: 14, fontWeight: '600' },
    pixSuccessCircle: { width: 100, height: 100, borderRadius: 50, backgroundColor: '#28C76F', justifyContent: 'center', alignItems: 'center', marginBottom: 20 },
    pixSuccessTitle: { fontSize: 22, fontWeight: 'bold', marginBottom: 6 },
    pixSuccessAmount: { fontSize: 28, fontWeight: '900', marginBottom: 6 },
    pixActionBtn: { paddingVertical: 16, borderRadius: 16, alignItems: 'center' },
    pixActionBtnText: { color: '#fff', fontSize: 16, fontWeight: 'bold' },
    pixActionBtnOutline: { paddingVertical: 16, borderRadius: 16, alignItems: 'center', borderWidth: 1 },
    pixActionBtnOutlineText: { fontSize: 16, fontWeight: 'bold' },
});

// Estilos originais (outros nichos)
const orig = StyleSheet.create({
    container: { flex: 1 },
    header: { flexDirection: 'row', alignItems: 'center', paddingTop: 50, paddingBottom: 20, paddingHorizontal: 20, borderBottomLeftRadius: 20, borderBottomRightRadius: 20, elevation: 5, zIndex: 10 },
    backButton: { padding: 8, marginRight: 10 },
    headerTitle: { fontSize: 20, fontWeight: 'bold' },
    scrollContent: { padding: 20, paddingBottom: 100 },
    formSection: { marginBottom: 20 },
    sectionHeaderRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 15 },
    sectionTitle: { fontSize: 16, fontWeight: '700', marginLeft: 8 },
    inputGroup: { marginBottom: 20 },
    labelRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
    label: { fontSize: 14, fontWeight: '600', marginLeft: 6 },
    inputContainer: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderRadius: 12, paddingHorizontal: 15, height: 55 },
    input: { flex: 1, fontSize: 16 },
    pickerWrapper: { borderWidth: 1, borderRadius: 12, overflow: 'hidden', height: 55, justifyContent: 'center' },
    textAreaContainer: { borderWidth: 1, borderRadius: 12, padding: 15, height: 100 },
    textArea: { flex: 1, fontSize: 16, textAlignVertical: 'top' },
    footer: { padding: 20, paddingBottom: Platform.OS === 'ios' ? 40 : 20, borderTopWidth: 1 },
    submitButton: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 16, borderRadius: 12, elevation: 6 },
    submitButtonText: { color: '#fff', fontSize: 18, fontWeight: 'bold', marginLeft: 8 },
});
