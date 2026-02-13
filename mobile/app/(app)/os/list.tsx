import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, TouchableOpacity, StyleSheet, ActivityIndicator, Alert } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';

const statusTranslations: { [key: string]: string } = {
    'pending': 'Pendentes',
    'running': 'Em Execução',
    'finalized': 'Finalizadas',
    'canceled': 'Canceladas',
};

export default function OSListScreen() {
    const { status, title } = useLocalSearchParams();
    const router = useRouter();
    const { colors } = useTheme();
    const [data, setData] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchData();
    }, [status]);

    const fetchData = async () => {
        try {
            const response = await api.get('/os', { params: { status } });
            // Se for paginado, os dados estão em .data.data, senão em .data
            const list = response.data.data || response.data;
            setData(Array.isArray(list) ? list : []);
        } catch (error) {
            console.error("List fetch error:", error);
            Alert.alert("Erro", "Não foi possível carregar a lista.");
        } finally {
            setLoading(false);
        }
    };

    const renderItem = ({ item }: { item: any }) => (
        <TouchableOpacity
            style={[styles.card, { backgroundColor: colors.card }]}
            onPress={() => router.push(`/os/${item.id}`)}
        >
            <View style={styles.cardHeader}>
                <Text style={[styles.osId, { color: colors.primary }]}>#{item.id}</Text>
                <Text style={[styles.date, { color: colors.subText }]}>{new Date(item.created_at).toLocaleDateString('pt-BR')}</Text>
            </View>
            <Text style={[styles.clientName, { color: colors.text }]}>{item.client?.name || item.client?.company_name || 'N/A'}</Text>
            <Text style={[styles.vehicle, { color: colors.subText }]}>{item.veiculo?.marca || 'N/A'} {item.veiculo?.modelo || ''} - {item.veiculo?.placa || ''}</Text>
            <View style={styles.footer}>
                <Text style={[styles.total, { color: colors.primary }]}>
                    R$ {(Number(item.total) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </Text>
                <Ionicons name="chevron-forward" size={20} color={colors.subText} />
            </View>
        </TouchableOpacity>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <View style={[styles.header, { backgroundColor: colors.card }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={24} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>{title || statusTranslations[status as string] || 'Lista'}</Text>
                <View style={{ width: 40 }} />
            </View>

            {loading ? (
                <ActivityIndicator size="large" color={colors.primary} style={{ marginTop: 50 }} />
            ) : (
                <FlatList
                    data={data}
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={renderItem}
                    contentContainerStyle={styles.list}
                    ListEmptyComponent={
                        <Text style={{ textAlign: 'center', marginTop: 50, color: colors.subText }}>Nenhuma ordem encontrada.</Text>
                    }
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 50, paddingBottom: 15, paddingHorizontal: 15, elevation: 4 },
    backBtn: { padding: 5 },
    headerTitle: { fontSize: 18, fontWeight: 'bold' },
    list: { padding: 15 },
    card: { padding: 15, borderRadius: 16, marginBottom: 15, elevation: 2 },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8 },
    osId: { fontWeight: 'bold', fontSize: 16 },
    date: { fontSize: 12 },
    clientName: { fontSize: 16, fontWeight: 'bold', marginBottom: 4 },
    vehicle: { fontSize: 14, marginBottom: 10 },
    footer: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderTopWidth: 1, borderTopColor: '#f0f0f0', paddingTop: 10 },
    total: { fontWeight: 'bold', fontSize: 15 }
});
