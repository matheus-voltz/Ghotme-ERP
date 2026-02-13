import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, TextInput, ActivityIndicator } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';

export default function InventoryScreen() {
    const router = useRouter();
    const { colors } = useTheme();
    
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
        <View style={styles.card}>
            <View style={styles.iconContainer}>
                <Ionicons name="cube-outline" size={24} color="#7367F0" />
            </View>
            <View style={styles.info}>
                <Text style={styles.name}>{item.name}</Text>
                <Text style={styles.sku}>SKU: {item.sku || 'N/A'}</Text>
                <Text style={styles.price}>R$ {parseFloat(item.selling_price || 0).toFixed(2)}</Text>
            </View>
            <View style={[styles.badge, item.quantity < 5 ? styles.lowStock : styles.goodStock]}>
                <Text style={[styles.badgeText, item.quantity < 5 ? styles.lowStockText : styles.goodStockText]}>
                    {item.quantity} un
                </Text>
            </View>
        </View>
    );

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color="#333" />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>Estoque de Peças</Text>
                <TouchableOpacity onPress={fetchInventory}>
                    <Ionicons name="refresh" size={24} color="#7367F0" />
                </TouchableOpacity>
            </View>

            <View style={styles.searchContainer}>
                <Ionicons name="search" size={20} color="#999" style={{ marginRight: 10 }} />
                <TextInput 
                    style={styles.searchInput}
                    placeholder="Buscar peça por nome ou SKU..."
                    value={search}
                    onChangeText={handleSearch}
                />
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
                            <Ionicons name="search-outline" size={50} color="#ccc" />
                            <Text style={styles.emptyText}>Nenhuma peça encontrada.</Text>
                        </View>
                    }
                />
            )}

            <TouchableOpacity 
                style={styles.fab}
                onPress={() => router.push('/inventory/create')}
            >
                <Ionicons name="add" size={30} color="#fff" />
            </TouchableOpacity>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F3F4F6' },
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 20, backgroundColor: '#fff' },
    backBtn: { width: 40 },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#1F2937' },
    searchContainer: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', margin: 20, paddingHorizontal: 15, borderRadius: 12, height: 50, elevation: 2 },
    searchInput: { flex: 1, fontSize: 16 },
    listContent: { paddingHorizontal: 20, paddingBottom: 80 },
    card: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', padding: 15, borderRadius: 16, marginBottom: 12, elevation: 2 },
    iconContainer: { width: 50, height: 50, borderRadius: 12, backgroundColor: '#F3F4F6', alignItems: 'center', justifyContent: 'center', marginRight: 15 },
    info: { flex: 1 },
    name: { fontSize: 16, fontWeight: 'bold', color: '#333' },
    sku: { fontSize: 12, color: '#888', marginTop: 2 },
    price: { fontSize: 14, fontWeight: '600', color: '#7367F0', marginTop: 4 },
    badge: { paddingHorizontal: 10, paddingVertical: 5, borderRadius: 8 },
    goodStock: { backgroundColor: '#E8FDF3' },
    lowStock: { backgroundColor: '#FCEAEA' },
    badgeText: { fontSize: 12, fontWeight: 'bold' },
    goodStockText: { color: '#28C76F' },
    lowStockText: { color: '#EA5455' },
    emptyState: { alignItems: 'center', marginTop: 50 },
    emptyText: { color: '#999', marginTop: 10 },
    fab: { position: 'absolute', bottom: 30, right: 20, width: 60, height: 60, borderRadius: 30, backgroundColor: '#7367F0', justifyContent: 'center', alignItems: 'center', elevation: 5 }
});
