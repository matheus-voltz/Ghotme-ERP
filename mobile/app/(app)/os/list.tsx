import React, { useEffect, useState, useCallback } from 'react';
import { View, Text, FlatList, TouchableOpacity, StyleSheet, Alert, StatusBar, RefreshControl } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import Animated, { FadeInDown } from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';
import { Skeleton } from '../../../components/Skeleton';

const statusTranslations: { [key: string]: string } = {
    'pending': 'Pendentes',
    'running': 'Em Execução',
    'finalized': 'Finalizadas',
    'canceled': 'Canceladas',
};

export default function OSListScreen() {
    const { status, title } = useLocalSearchParams();
    const router = useRouter();
    const { colors, activeTheme } = useTheme();
    const { labels, niche } = useNiche();
    const [data, setData] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchData = async () => {
        try {
            const response = await api.get('/os', { params: { status } });
            const list = response.data.data || response.data;
            setData(Array.isArray(list) ? list : []);
        } catch (error) {
            console.error("List fetch error:", error);
            Alert.alert("Erro", "Não foi possível carregar a lista.");
        } finally {
            // Pequeno delay para percepção do skeleton
            setTimeout(() => {
                setLoading(false);
                setRefreshing(false);
            }, 600);
        }
    };

    useEffect(() => {
        fetchData();
    }, [status]);

    const onRefresh = useCallback(() => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        setRefreshing(true);
        fetchData();
    }, [status]);

    const renderSkeleton = () => (
        <View style={styles.list}>
            {[1, 2, 3, 4, 5].map(i => (
                <View key={i} style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
                    <View style={styles.cardHeader}>
                        <Skeleton width={50} height={20} borderRadius={6} />
                        <Skeleton width={80} height={14} />
                    </View>
                    <Skeleton width="60%" height={18} style={{ marginBottom: 10 }} />
                    <Skeleton width="40%" height={14} style={{ marginBottom: 15 }} />
                    <View style={[styles.footer, { borderTopColor: colors.border }]}>
                        <Skeleton width={100} height={20} />
                        <Skeleton width={80} height={24} borderRadius={20} />
                    </View>
                </View>
            ))}
        </View>
    );

    const renderItem = ({ item, index }: { item: any, index: number }) => (
        <Animated.View entering={FadeInDown.delay(index * 50).duration(400).springify()}>
            <TouchableOpacity
                style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}
                onPress={() => {
                    Haptics.selectionAsync();
                    router.push(`/os/${item.id}`);
                }}
                activeOpacity={0.9}
            >
                <View style={styles.cardHeader}>
                    <View style={styles.idBadge}>
                        <Text style={styles.idText}>#{item.id}</Text>
                    </View>
                    <Text style={[styles.date, { color: colors.subText }]}>{new Date(item.created_at).toLocaleDateString('pt-BR')}</Text>
                </View>

                <Text style={[styles.clientName, { color: colors.text }]}>{item.client?.name || item.client?.company_name || 'N/A'}</Text>

                <View style={styles.infoRow}>
                    <Ionicons name={niche === 'pet' ? "paw-outline" : (niche === 'electronics' ? "laptop-outline" : "car-sport-outline")} size={14} color={colors.subText} style={{ marginRight: 6 }} />
                    <Text style={[styles.vehicle, { color: colors.subText }]}>
                        {item.veiculo?.marca || 'N/A'} {item.veiculo?.modelo || ''}
                        {niche === 'automotive' && item.veiculo?.placa ? ` - ${item.veiculo.placa}` : ''}
                        {niche !== 'automotive' && niche !== 'pet' && item.veiculo?.placa ? ` - Mod: ${item.veiculo.placa}` : ''}
                    </Text>
                </View>

                <View style={[styles.footer, { borderTopColor: colors.border }]}>
                    <Text style={[styles.total, { color: colors.primary }]}>
                        R$ {(Number(item.total) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </Text>
                    <View style={styles.actionBtn}>
                        <Text style={styles.actionText}>Ver Detalhes</Text>
                        <Ionicons name="chevron-forward" size={16} color={colors.primary} />
                    </View>
                </View>
            </TouchableOpacity>
        </Animated.View>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <StatusBar barStyle="light-content" backgroundColor="#7367F0" />

            <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.header}
            >
                <View style={styles.headerContent}>
                    <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={24} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.headerTitle}>
                        {title || statusTranslations[status as string] || 'Lista'}
                    </Text>
                    <View style={{ width: 24 }} />
                </View>
            </LinearGradient>

            {loading ? renderSkeleton() : (
                <FlatList
                    data={data}
                    refreshControl={
                        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#7367F0" />
                    }
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={renderItem}
                    contentContainerStyle={styles.list}
                    showsVerticalScrollIndicator={false}
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Ionicons name="folder-open-outline" size={48} color={colors.subText} />
                            <Text style={[styles.emptyText, { color: colors.subText }]}>Nenhuma ordem encontrada.</Text>
                        </View>
                    }
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: {
        paddingTop: 60,
        paddingBottom: 25,
        paddingHorizontal: 20,
        borderBottomLeftRadius: 30,
        borderBottomRightRadius: 30,
        zIndex: 10,
        elevation: 5,
    },
    headerContent: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
    },
    backBtn: {
        padding: 4,
        backgroundColor: 'rgba(255,255,255,0.2)',
        borderRadius: 12,
    },
    headerTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        color: '#fff',
    },
    list: {
        paddingTop: 20,
        paddingHorizontal: 20,
        paddingBottom: 40,
    },
    card: {
        borderRadius: 16,
        padding: 16,
        marginBottom: 16,
        borderWidth: 1,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 2,
    },
    cardHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 10,
    },
    idBadge: {
        backgroundColor: '#7367F015',
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 6,
    },
    idText: {
        color: '#7367F0',
        fontWeight: '700',
        fontSize: 12,
    },
    date: {
        fontSize: 12,
    },
    clientName: {
        fontSize: 16,
        fontWeight: 'bold',
        marginBottom: 6,
    },
    infoRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 12,
    },
    vehicle: {
        fontSize: 13,
    },
    footer: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        borderTopWidth: 1,
        paddingTop: 12,
        marginTop: 4,
    },
    total: {
        fontWeight: 'bold',
        fontSize: 16,
    },
    actionBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#7367F010',
        paddingHorizontal: 10,
        paddingVertical: 6,
        borderRadius: 20,
    },
    actionText: {
        color: '#7367F0',
        fontSize: 11,
        fontWeight: '600',
        marginRight: 4,
    },
    emptyContainer: {
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: 60,
        opacity: 0.7,
    },
    emptyText: {
        marginTop: 12,
        fontSize: 14,
    },
});
