import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, TextInput, ActivityIndicator } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import { useAuth } from '../../../context/AuthContext';

export default function InventoryScreen() {
    const router = useRouter();
    const { colors } = useTheme();
    const { labels } = useNiche();
    const { user } = useAuth();

    const [items, setItems] = useState<any[]>([]);
    const [filteredItems, setFilteredItems] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');

    useEffect(() => {
        fetchInventory();
    }, []);

    const fetchInventory = async () => {
        setLoading(true);
        try {
            // Ajustar rota conforme a API do Laravel
            const response = await api.get('/inventory/items-list');
            const data = response.data.data ? response.data.data : response.data;
            setItems(data);
            setFilteredItems(data);
        } catch (error) {
            console.error("Erro ao buscar estoque:", error);
        } finally {
            setLoading(false);
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

    const renderItem = ({ item }: { item: any }) => (
        <TouchableOpacity
            style={[styles.card, { backgroundColor: colors.card }]}
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
                }
            })}
        >
            <View style={[styles.iconContainer, { backgroundColor: colors.iconBg }]}>
                <Ionicons name="cube-outline" size={24} color="#7367F0" />
            </View>
            <View style={styles.info}>
                <Text style={[styles.name, { color: colors.text }]}>{item.name}</Text>
                <Text style={[styles.sku, { color: colors.subText }]}>SKU: {item.sku || 'N/A'}</Text>
                <Text style={styles.price}>R$ {parseFloat(item.selling_price || 0).toFixed(2)}</Text>
            </View>
            <View style={[styles.badge, item.quantity < 5 ? styles.lowStock : styles.goodStock]}>
                <Text style={[styles.badgeText, item.quantity < 5 ? styles.lowStockText : styles.goodStockText]}>
                    {item.quantity} un
                </Text>
            </View>
        </TouchableOpacity>
    );
    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <View style={[styles.header, { backgroundColor: colors.background }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>Estoque de {labels.inventory_items?.split('/')[0] || 'Peças'}</Text>
                <TouchableOpacity onPress={fetchInventory}>
                    <Ionicons name="refresh" size={24} color="#7367F0" />
                </TouchableOpacity>
            </View>

            <View style={styles.searchRow}>
                <View style={[styles.searchContainer, { backgroundColor: colors.card, flex: 1 }]}>
                    <Ionicons name="search" size={20} color={colors.subText} />
                    <TextInput
                        style={[styles.searchInput, { color: colors.text }]}
                        placeholder={`Buscar ${labels.inventory_items?.split('/')[0] || 'peça'}...`}
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
    fab: { position: 'absolute', bottom: 30, right: 20, width: 60, height: 60, borderRadius: 30, backgroundColor: '#7367F0', justifyContent: 'center', alignItems: 'center', elevation: 5 }
});
