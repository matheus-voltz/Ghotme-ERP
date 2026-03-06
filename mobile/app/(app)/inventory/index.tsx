import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, TextInput, ActivityIndicator, Image, Alert, Modal, Animated as RNAnimated } from 'react-native';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import { useAuth } from '../../../context/AuthContext';
import { Swipeable } from 'react-native-gesture-handler';
import * as Haptics from 'expo-haptics';

export default function InventoryScreen() {
    const router = useRouter();
    const params = useLocalSearchParams();
    const { colors } = useTheme();
    const { labels } = useNiche();
    const { user } = useAuth();

    const [items, setItems] = useState<any[]>([]);
    const [filteredItems, setFilteredItems] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [showCategoryModal, setShowCategoryModal] = useState(false);
    const [newCatName, setNewCatName] = useState('');
    const [savingCat, setSavingCat] = useState(false);

    useEffect(() => {
        fetchInventory();
    }, []);

    useEffect(() => {
        if (params.filter === 'low_stock' && items.length > 0) {
            const filtered = items.filter(i => Number(i.quantity) <= Number(i.min_quantity));
            setFilteredItems(filtered);
            setSearch('');
        }
    }, [params.filter, items]);

    const fetchInventory = async () => {
        setLoading(true);
        try {
            const response = await api.get('/inventory/items-list');
            const data = response.data.data ? response.data.data : response.data;
            setItems(data);

            if (params.filter === 'low_stock') {
                const filtered = data.filter((i: any) => Number(i.quantity) <= Number(i.min_quantity));
                setFilteredItems(filtered);
            } else {
                setFilteredItems(data);
            }
        } catch (error) {
            console.error("Erro ao buscar estoque:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleDeleteItem = (id: number, name: string) => {
        Alert.alert(
            "Excluir Item",
            `Deseja realmente excluir "${name}"? Esta ação não pode ser desfeita.`,
            [
                { text: "Cancelar", style: "cancel" },
                {
                    text: "Excluir",
                    style: "destructive",
                    onPress: async () => {
                        try {
                            await api.delete(`/inventory/items/${id}`);
                            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
                            fetchInventory();
                        } catch (err) {
                            Alert.alert("Erro", "Não foi possível excluir o item.");
                        }
                    }
                }
            ]
        );
    };

    const handleManageCategories = () => {
        setShowCategoryModal(true);
    };

    const handleSaveCategory = async () => {
        if (!newCatName.trim()) return;
        setSavingCat(true);
        try {
            await api.post('/categories', { name: newCatName.trim() });
            Alert.alert("Sucesso", "Categoria adicionada!");
            setShowCategoryModal(false);
            setNewCatName('');
        } catch (err) {
            Alert.alert("Erro", "Não foi possível adicionar a categoria.");
        } finally {
            setSavingCat(false);
        }
    };

    const handleSearch = (text: string) => {
        setSearch(text);
        if (text) {
            const lower = text.toLowerCase();
            const filtered = items.filter(item =>
                item.name.toLowerCase().includes(lower) ||
                (item.sku && item.sku.toLowerCase().includes(lower))
            );
            setFilteredItems(filtered);
        } else {
            setFilteredItems(items);
        }
    };

    const renderRightActions = (progress: any, dragX: any, item: any) => {
        const trans = dragX.interpolate({
            inputRange: [-160, -80, 0],
            outputRange: [0, 0, 80],
        });

        return (
            <View style={{ flexDirection: 'row', width: 160, marginBottom: 12 }}>
                <TouchableOpacity
                    style={[styles.actionButton, { backgroundColor: '#7367F0' }]}
                    onPress={() => router.push(`/inventory/edit/${item.id}`)}
                >
                    <Ionicons name="pencil-outline" size={22} color="#fff" />
                    <Text style={styles.actionText}>Editar</Text>
                </TouchableOpacity>
                <TouchableOpacity
                    style={[styles.actionButton, { backgroundColor: '#EA5455' }]}
                    onPress={() => handleDeleteItem(item.id, item.name)}
                >
                    <Ionicons name="trash-outline" size={22} color="#fff" />
                    <Text style={styles.actionText}>Apagar</Text>
                </TouchableOpacity>
            </View>
        );
    };

    const renderItem = ({ item }: { item: any }) => (
        <Swipeable
            renderRightActions={(progress, dragX) => renderRightActions(progress, dragX, item)}
            overshootRight={false}
        >
            <TouchableOpacity
                style={[styles.card, { backgroundColor: colors.card, marginBottom: 0 }]}
                activeOpacity={0.7}
                onPress={() => router.push({
                    pathname: '/inventory/[id]',
                    params: {
                        id: item.id,
                        name: item.name,
                        sku: item.sku || '',
                        cost_price: String(item.cost_price || 0),
                        selling_price: String(item.selling_price || 0),
                        quantity: String(item.quantity || 0),
                        min_quantity: String(item.min_quantity || 5),
                        location: item.location || '',
                        unit: item.unit || 'un',
                        supplier_name: item.supplier?.name || '',
                        image_url: item.image_url || '',
                        category_name: item.category?.name || 'Sem Categoria',
                    }
                })}
            >
                <View style={[styles.iconContainer, { backgroundColor: colors.iconBg, padding: item.image_url ? 0 : undefined, overflow: 'hidden' }]}>
                    {item.image_url ? (
                        <Image source={{ uri: item.image_url }} style={{ width: '100%', height: '100%', resizeMode: 'cover' }} />
                    ) : (
                        <Ionicons name="cube-outline" size={24} color="#7367F0" />
                    )}
                </View>
                <View style={styles.info}>
                    <Text style={[styles.name, { color: colors.text }]}>{item.name}</Text>
                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                        <Text style={[styles.sku, { color: '#7367F0', fontWeight: 'bold' }]}>{item.category?.name || 'Geral'}</Text>
                        {item.sku ? <Text style={[styles.sku, { color: colors.subText }]}>• SKU: {item.sku}</Text> : null}
                    </View>
                </View>
                <View style={[styles.badge, (Number(item.quantity) <= Number(item.min_quantity)) ? styles.lowStock : styles.goodStock]}>
                    <Text style={[styles.badgeText, (Number(item.quantity) <= Number(item.min_quantity)) ? styles.lowStockText : styles.goodStockText]}>
                        {item.quantity} {item.unit || 'un'}
                    </Text>
                </View>
            </TouchableOpacity>
            <View style={{ height: 12 }} />
        </Swipeable>
    );
    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <View style={[styles.header, { backgroundColor: colors.background }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color={colors.text} />
                </TouchableOpacity>
                <View style={{ flex: 1, alignItems: 'center' }}>
                    <Text style={[styles.headerTitle, { color: colors.text }]} numberOfLines={1}>Estoque</Text>
                </View>
                <View style={{ flexDirection: 'row', gap: 12, width: 80, justifyContent: 'flex-end' }}>
                    {user?.role === 'admin' && (
                        <TouchableOpacity onPress={handleManageCategories} style={styles.headerActionBtn}>
                            <Ionicons name="apps-outline" size={22} color="#7367F0" />
                        </TouchableOpacity>
                    )}
                    <TouchableOpacity onPress={fetchInventory} style={styles.headerActionBtn}>
                        <Ionicons name="refresh" size={22} color="#7367F0" />
                    </TouchableOpacity>
                </View>
            </View>

            <View style={styles.searchRow}>
                <View style={[styles.searchContainer, { backgroundColor: colors.card, flex: 1 }]}>
                    <Ionicons name="search" size={20} color={colors.subText} />
                    <TextInput
                        style={[styles.searchInput, { color: colors.text }]}
                        placeholder={`Buscar ${labels.inventory_items?.split('/')[0].toLowerCase() || 'item'}...`}
                        placeholderTextColor={colors.subText}
                        value={search}
                        onChangeText={handleSearch}
                    />
                </View>
                <TouchableOpacity
                    style={[styles.scannerButton, { backgroundColor: colors.primary }]}
                    onPress={() => router.push('/inventory/scanner')}
                >
                    <Ionicons name="barcode-outline" size={24} color="#fff" />
                </TouchableOpacity>
            </View>

            {params.filter === 'low_stock' && (
                <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: '#EA545515', marginHorizontal: 20, marginBottom: 15, padding: 12, borderRadius: 12, gap: 8 }}>
                    <Ionicons name="filter" size={18} color="#EA5455" />
                    <Text style={{ flex: 1, color: '#EA5455', fontWeight: 'bold', fontSize: 13 }}>Exibindo apenas itens com baixo estoque</Text>
                    <TouchableOpacity onPress={() => { router.setParams({ filter: '' }); fetchInventory(); }}>
                        <Text style={{ color: '#7367F0', fontWeight: 'bold', fontSize: 13 }}>Ver Tudo</Text>
                    </TouchableOpacity>
                </View>
            )}

            {loading ? (
                <ActivityIndicator size="large" color="#7367F0" style={{ marginTop: 50 }} />
            ) : (
                <FlatList
                    data={filteredItems}
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={renderItem}
                    contentContainerStyle={styles.listContent}
                    ListEmptyComponent={
                        <View style={styles.emptyState}>
                            <Ionicons name="search-outline" size={50} color={colors.subText} />
                            <Text style={[styles.emptyText, { color: colors.subText }]}>Nenhum item encontrado.</Text>
                        </View>
                    }
                />
            )}

            {/* Modal de Nova Categoria */}
            <Modal visible={showCategoryModal} transparent animationType="fade">
                <View style={styles.modalOverlay}>
                    <View style={[styles.modalContent, { backgroundColor: colors.card, padding: 24 }]}>
                        <Text style={[styles.modalTitle, { color: colors.text, marginBottom: 20 }]}>Nova Categoria</Text>

                        <View style={[styles.inputWrapper, { backgroundColor: colors.iconBg, borderColor: colors.border }]}>
                            <Ionicons name="pricetag-outline" size={18} color="#7367F0" style={{ marginLeft: 15 }} />
                            <TextInput
                                style={[styles.searchInput, { color: colors.text, flex: 1, marginLeft: 10 }]}
                                placeholder="Nome da Categoria"
                                placeholderTextColor={colors.subText}
                                value={newCatName}
                                onChangeText={setNewCatName}
                            />
                        </View>

                        <View style={{ flexDirection: 'row', gap: 12, marginTop: 24 }}>
                            <TouchableOpacity
                                style={{ flex: 1, height: 48, borderRadius: 12, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.iconBg }}
                                onPress={() => { setShowCategoryModal(false); setNewCatName(''); }}
                            >
                                <Text style={{ color: colors.text }}>Cancelar</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={{ flex: 1, height: 48, borderRadius: 12, alignItems: 'center', justifyContent: 'center', backgroundColor: '#7367F0' }}
                                onPress={handleSaveCategory}
                                disabled={savingCat}
                            >
                                {savingCat ? <ActivityIndicator color="#fff" /> : <Text style={{ color: '#fff', fontWeight: 'bold' }}>Adicionar</Text>}
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>

            {user?.role === 'admin' && (
                <TouchableOpacity
                    style={styles.fab}
                    onPress={() => router.push('/inventory/create')}
                >
                    <Ionicons name="add" size={30} color="#fff" />
                </TouchableOpacity>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 20 },
    backBtn: { width: 40 },
    headerTitle: { fontSize: 20, fontWeight: 'bold' },
    headerActionBtn: { width: 36, height: 36, borderRadius: 10, backgroundColor: 'rgba(115, 103, 240, 0.1)', alignItems: 'center', justifyContent: 'center' },
    searchRow: { flexDirection: 'row', alignItems: 'center', marginHorizontal: 20, marginBottom: 20, gap: 10 },
    searchContainer: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 15, borderRadius: 12, height: 50, elevation: 2 },
    scannerButton: { width: 50, height: 50, borderRadius: 12, justifyContent: 'center', alignItems: 'center', elevation: 2 },
    searchInput: { flex: 1, fontSize: 16, marginLeft: 10 },
    listContent: { paddingHorizontal: 20, paddingBottom: 80 },
    card: { flexDirection: 'row', alignItems: 'center', padding: 15, borderRadius: 16, marginBottom: 12, elevation: 2 },
    iconContainer: { width: 50, height: 50, borderRadius: 12, alignItems: 'center', justifyContent: 'center', marginRight: 15 },
    info: { flex: 1 },
    name: { fontSize: 16, fontWeight: 'bold' },
    sku: { fontSize: 12, marginTop: 2 },
    price: { fontSize: 14, fontWeight: '600', color: '#7367F0', marginTop: 4 },
    badge: { paddingHorizontal: 10, paddingVertical: 5, borderRadius: 8 },
    goodStock: { backgroundColor: '#E8FDF3' },
    lowStock: { backgroundColor: '#FCEAEA' },
    badgeText: { fontSize: 12, fontWeight: 'bold' },
    goodStockText: { color: '#28C76F' },
    lowStockText: { color: '#EA5455' },
    emptyState: { alignItems: 'center', marginTop: 50 },
    emptyText: { marginTop: 10 },
    fab: { position: 'absolute', bottom: 30, right: 20, width: 60, height: 60, borderRadius: 30, backgroundColor: '#7367F0', justifyContent: 'center', alignItems: 'center', elevation: 5 },

    actionButton: { width: 80, height: 75, justifyContent: 'center', alignItems: 'center', borderRadius: 16, marginLeft: 8 },
    actionText: { color: '#fff', fontSize: 12, fontWeight: 'bold', marginTop: 4 },

    // Modal Styles
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', padding: 20 },
    modalContent: { borderRadius: 20, padding: 20, elevation: 5 },
    modalTitle: { fontSize: 18, fontWeight: 'bold', textAlign: 'center' },
    inputWrapper: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderRadius: 12, height: 52 },
});
