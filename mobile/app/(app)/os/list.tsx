import React, { useEffect, useState, useCallback, useMemo, useRef } from 'react';
import {
    View,
    Text,
    FlatList,
    TouchableOpacity,
    StyleSheet,
    Alert,
    StatusBar,
    RefreshControl,
    TextInput,
    ScrollView,
    Keyboard,
    Animated as RNAnimated,
} from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import Animated, { FadeInDown, FadeIn } from 'react-native-reanimated';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';
import { Skeleton } from '../../../components/Skeleton';

// ─── Tipos ───────────────────────────────────────────────────────────────────

type SortOption = 'date_desc' | 'date_asc' | 'value_desc' | 'value_asc';

const STATUS_FILTERS = [
    { key: 'all', label: 'Todas', color: '#7367F0' },
    { key: 'pending', label: 'Pendente', color: '#FF9F43' },
    { key: 'approved', label: 'Aprovada', color: '#00CFE8' },
    { key: 'running', label: 'Execução', color: '#00CFE8' },
    { key: 'finalized', label: 'Finalizada', color: '#28C76F' },
    { key: 'canceled', label: 'Cancelada', color: '#EA5455' },
];

const SORT_OPTIONS: { key: SortOption; label: string; icon: string }[] = [
    { key: 'date_desc', label: 'Mais recente', icon: 'arrow-down' },
    { key: 'date_asc', label: 'Mais antigo', icon: 'arrow-up' },
    { key: 'value_desc', label: 'Maior valor', icon: 'trending-up' },
    { key: 'value_asc', label: 'Menor valor', icon: 'trending-down' },
];

const getStatusColor = (status: string) => {
    const found = STATUS_FILTERS.find(f => f.key === status);
    return found?.color ?? '#7367F0';
};

const statusTranslations: { [key: string]: string } = {
    pending: 'Pendente',
    approved: 'Aprovada',
    running: 'Em Execução',
    finalized: 'Finalizada',
    canceled: 'Cancelada',
};

// ─── Componente Principal ────────────────────────────────────────────────────

export default function OSListScreen() {
    const { status: initialStatus, title } = useLocalSearchParams();
    const router = useRouter();
    const { colors, activeTheme } = useTheme();
    const { niche } = useNiche();

    const [allData, setAllData] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [searchText, setSearchText] = useState('');
    const [activeStatus, setActiveStatus] = useState<string>(
        (initialStatus as string) ?? 'all'
    );
    const [sortBy, setSortBy] = useState<SortOption>('date_desc');
    const [showSort, setShowSort] = useState(false);

    // Animação da barra de busca
    const searchBarAnim = useRef(new RNAnimated.Value(0)).current;
    const [searchFocused, setSearchFocused] = useState(false);

    const onSearchFocus = () => {
        setSearchFocused(true);
        RNAnimated.spring(searchBarAnim, { toValue: 1, useNativeDriver: false, speed: 20 }).start();
    };
    const onSearchBlur = () => {
        setSearchFocused(false);
        RNAnimated.spring(searchBarAnim, { toValue: 0, useNativeDriver: false, speed: 20 }).start();
    };

    const searchBorderColor = searchBarAnim.interpolate({
        inputRange: [0, 1],
        outputRange: [colors.border, '#7367F0'],
    });

    // ─── Fetch ────────────────────────────────────────────────────────────────

    const fetchData = async () => {
        try {
            // Busca TODAS as OS (sem filtro de status no servidor)
            // para poder filtrar localmente com rapidez
            const response = await api.get('/os');
            const list = response.data.data || response.data;
            setAllData(Array.isArray(list) ? list : []);
        } catch (error) {
            console.error('List fetch error:', error);
            Alert.alert('Erro', 'Não foi possível carregar a lista.');
        } finally {
            setTimeout(() => {
                setLoading(false);
                setRefreshing(false);
            }, 500);
        }
    };

    useEffect(() => { fetchData(); }, []);

    const onRefresh = useCallback(() => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        setRefreshing(true);
        fetchData();
    }, []);

    // ─── Filtragem e Ordenação (memo → performance) ───────────────────────────

    const filteredData = useMemo(() => {
        let result = [...allData];

        // 1. Filtro de status
        if (activeStatus !== 'all') {
            result = result.filter(item => item.status === activeStatus);
        }

        // 2. Busca textual (cliente, nº OS, placa/modelo)
        const query = searchText.trim().toLowerCase();
        if (query.length > 0) {
            result = result.filter(item => {
                const clientName = (item.client?.name ?? item.client?.company_name ?? '').toLowerCase();
                const osId = String(item.id);
                const plate = (item.veiculo?.placa ?? '').toLowerCase();
                const model = `${item.veiculo?.marca ?? ''} ${item.veiculo?.modelo ?? ''}`.toLowerCase();
                return (
                    clientName.includes(query) ||
                    osId.includes(query) ||
                    plate.includes(query) ||
                    model.includes(query)
                );
            });
        }

        // 3. Ordenação
        result.sort((a, b) => {
            switch (sortBy) {
                case 'date_asc':
                    return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
                case 'value_desc':
                    return (Number(b.total) || 0) - (Number(a.total) || 0);
                case 'value_asc':
                    return (Number(a.total) || 0) - (Number(b.total) || 0);
                default: // date_desc
                    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
            }
        });

        return result;
    }, [allData, activeStatus, searchText, sortBy]);

    // ─── Contagens por status ─────────────────────────────────────────────────

    const countByStatus = useMemo(() => {
        const counts: Record<string, number> = { all: allData.length };
        allData.forEach(item => {
            counts[item.status] = (counts[item.status] ?? 0) + 1;
        });
        return counts;
    }, [allData]);

    // ─── Renderização ─────────────────────────────────────────────────────────

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

    const renderItem = ({ item, index }: { item: any; index: number }) => (
        <Animated.View entering={FadeInDown.delay(index * 40).duration(350).springify()}>
            <TouchableOpacity
                style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}
                onPress={() => { Haptics.selectionAsync(); router.push(`/os/${item.id}`); }}
                activeOpacity={0.88}
            >
                <View style={styles.cardHeader}>
                    <View style={styles.idBadge}>
                        <Text style={styles.idText}>#{item.id}</Text>
                    </View>
                    <View style={[styles.statusPill, { backgroundColor: getStatusColor(item.status) + '20' }]}>
                        <View style={[styles.statusDot, { backgroundColor: getStatusColor(item.status) }]} />
                        <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
                            {statusTranslations[item.status?.toLowerCase()] ?? item.status}
                        </Text>
                    </View>
                </View>

                <Text style={[styles.clientName, { color: colors.text }]} numberOfLines={1}>
                    {item.client?.name ?? item.client?.company_name ?? 'Consumidor'}
                </Text>

                <View style={styles.infoRow}>
                    <Ionicons
                        name={niche === 'pet' ? 'paw-outline' : niche === 'electronics' ? 'laptop-outline' : 'car-sport-outline'}
                        size={13}
                        color={colors.subText}
                        style={{ marginRight: 5 }}
                    />
                    <Text style={[styles.vehicle, { color: colors.subText }]} numberOfLines={1}>
                        {item.veiculo?.marca ?? ''} {item.veiculo?.modelo ?? ''}
                        {item.veiculo?.placa ? ` · ${item.veiculo.placa}` : ''}
                    </Text>
                </View>

                <View style={[styles.footer, { borderTopColor: colors.border }]}>
                    <View style={styles.dateBox}>
                        <Ionicons name="calendar-outline" size={12} color={colors.subText} />
                        <Text style={[styles.date, { color: colors.subText }]}>
                            {new Date(item.created_at).toLocaleDateString('pt-BR')}
                        </Text>
                    </View>
                    <Text style={[styles.total, { color: colors.primary }]}>
                        R$ {(Number(item.total) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}
                    </Text>
                </View>
            </TouchableOpacity>
        </Animated.View>
    );

    const currentSortLabel = SORT_OPTIONS.find(s => s.key === sortBy)?.label ?? 'Ordenar';

    // ─── JSX ──────────────────────────────────────────────────────────────────

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <StatusBar barStyle="light-content" backgroundColor="#7367F0" />

            {/* ── Header Gradiente ─────────────────────────────────────────────── */}
            <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.header}
            >
                <View style={styles.headerContent}>
                    <TouchableOpacity onPress={() => { Haptics.selectionAsync(); router.back(); }} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={22} color="#fff" />
                    </TouchableOpacity>
                    <View style={{ flex: 1, marginHorizontal: 12 }}>
                        <Text style={styles.headerTitle} numberOfLines={1}>
                            {title ? String(title) : 'Ordens de Serviço'}
                        </Text>
                        {!loading && (
                            <Text style={styles.headerCount}>
                                {filteredData.length} {filteredData.length === 1 ? 'ordem' : 'ordens'}
                                {searchText || activeStatus !== 'all' ? ' encontradas' : ' no total'}
                            </Text>
                        )}
                    </View>
                    {/* Botão de ordenação */}
                    <TouchableOpacity
                        style={[styles.sortBtn, showSort && { backgroundColor: 'rgba(255,255,255,0.35)' }]}
                        onPress={() => { Haptics.selectionAsync(); setShowSort(v => !v); Keyboard.dismiss(); }}
                    >
                        <Ionicons name="funnel-outline" size={18} color="#fff" />
                    </TouchableOpacity>
                </View>

                {/* ── Barra de busca ─────────────────────────────────────────────── */}
                <RNAnimated.View style={[styles.searchWrapper, { borderColor: searchBorderColor }]}>
                    <Ionicons name="search-outline" size={18} color={searchFocused ? '#7367F0' : colors.subText} style={{ marginRight: 8 }} />
                    <TextInput
                        style={[styles.searchInput, { color: colors.text }]}
                        placeholder="Buscar por cliente, nº OS ou placa..."
                        placeholderTextColor={colors.subText}
                        value={searchText}
                        onChangeText={setSearchText}
                        onFocus={onSearchFocus}
                        onBlur={onSearchBlur}
                        returnKeyType="search"
                        autoCorrect={false}
                        autoCapitalize="none"
                    />
                    {searchText.length > 0 && (
                        <TouchableOpacity onPress={() => { setSearchText(''); Haptics.selectionAsync(); }}>
                            <Ionicons name="close-circle" size={18} color={colors.subText} />
                        </TouchableOpacity>
                    )}
                </RNAnimated.View>
            </LinearGradient>

            {/* ── Painel de ordenação (dropdown) ───────────────────────────────── */}
            {showSort && (
                <Animated.View
                    entering={FadeIn.duration(200)}
                    style={[styles.sortPanel, { backgroundColor: colors.card, borderColor: colors.border }]}
                >
                    <Text style={[styles.sortPanelTitle, { color: colors.subText }]}>ORDENAR POR</Text>
                    {SORT_OPTIONS.map(opt => (
                        <TouchableOpacity
                            key={opt.key}
                            style={[styles.sortRow, sortBy === opt.key && { backgroundColor: '#7367F010' }]}
                            onPress={() => {
                                setSortBy(opt.key);
                                setShowSort(false);
                                Haptics.selectionAsync();
                            }}
                        >
                            <Ionicons name={opt.icon as any} size={16} color={sortBy === opt.key ? '#7367F0' : colors.subText} />
                            <Text style={[styles.sortRowText, { color: sortBy === opt.key ? '#7367F0' : colors.text }]}>
                                {opt.label}
                            </Text>
                            {sortBy === opt.key && <Ionicons name="checkmark" size={16} color="#7367F0" />}
                        </TouchableOpacity>
                    ))}
                </Animated.View>
            )}

            {/* ── Chips de filtro de status ─────────────────────────────────────── */}
            <View style={styles.chipsWrapper}>
                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chips}>
                    {STATUS_FILTERS.map(f => {
                        const isActive = activeStatus === f.key;
                        const count = countByStatus[f.key] ?? 0;
                        return (
                            <TouchableOpacity
                                key={f.key}
                                style={[
                                    styles.chip,
                                    { borderColor: isActive ? f.color : colors.border, backgroundColor: isActive ? f.color : colors.card },
                                ]}
                                onPress={() => {
                                    Haptics.selectionAsync();
                                    setActiveStatus(f.key);
                                    setShowSort(false);
                                }}
                                activeOpacity={0.8}
                            >
                                {isActive && <View style={styles.chipDot} />}
                                <Text style={[styles.chipText, { color: isActive ? '#fff' : colors.subText }]}>
                                    {f.label}
                                </Text>
                                {!loading && (
                                    <View style={[styles.chipBadge, { backgroundColor: isActive ? 'rgba(255,255,255,0.25)' : colors.border }]}>
                                        <Text style={[styles.chipBadgeText, { color: isActive ? '#fff' : colors.subText }]}>{count}</Text>
                                    </View>
                                )}
                            </TouchableOpacity>
                        );
                    })}
                </ScrollView>
            </View>

            {/* ── Lista ─────────────────────────────────────────────────────────── */}
            {loading ? (
                renderSkeleton()
            ) : (
                <FlatList
                    data={filteredData}
                    keyExtractor={item => item.id.toString()}
                    renderItem={renderItem}
                    contentContainerStyle={styles.list}
                    showsVerticalScrollIndicator={false}
                    keyboardShouldPersistTaps="handled"
                    onScrollBeginDrag={() => { setShowSort(false); Keyboard.dismiss(); }}
                    refreshControl={
                        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#7367F0" colors={['#7367F0']} />
                    }
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Ionicons name="search-outline" size={52} color={colors.subText} style={{ opacity: 0.5 }} />
                            <Text style={[styles.emptyTitle, { color: colors.text }]}>
                                {searchText ? 'Nenhuma OS encontrada' : 'Sem ordens nesta categoria'}
                            </Text>
                            <Text style={[styles.emptySubtitle, { color: colors.subText }]}>
                                {searchText
                                    ? `Nenhuma OS corresponde a "${searchText}"`
                                    : 'Tente selecionar outro filtro de status'}
                            </Text>
                            {searchText.length > 0 && (
                                <TouchableOpacity style={styles.clearSearch} onPress={() => setSearchText('')}>
                                    <Text style={{ color: '#7367F0', fontWeight: '600' }}>Limpar busca</Text>
                                </TouchableOpacity>
                            )}
                        </View>
                    }
                />
            )}
        </View>
    );
}

// ─── Estilos ──────────────────────────────────────────────────────────────────

const styles = StyleSheet.create({
    container: { flex: 1 },

    // Header
    header: {
        paddingTop: 56,
        paddingBottom: 16,
        paddingHorizontal: 16,
        borderBottomLeftRadius: 24,
        borderBottomRightRadius: 24,
        zIndex: 10,
        elevation: 5,
    },
    headerContent: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 14,
    },
    backBtn: {
        width: 38,
        height: 38,
        borderRadius: 12,
        backgroundColor: 'rgba(255,255,255,0.2)',
        alignItems: 'center',
        justifyContent: 'center',
    },
    headerTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#fff',
    },
    headerCount: {
        fontSize: 11,
        color: 'rgba(255,255,255,0.75)',
        marginTop: 2,
    },
    sortBtn: {
        width: 38,
        height: 38,
        borderRadius: 12,
        backgroundColor: 'rgba(255,255,255,0.2)',
        alignItems: 'center',
        justifyContent: 'center',
    },

    // Search
    searchWrapper: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#fff',
        borderRadius: 14,
        paddingHorizontal: 14,
        paddingVertical: 10,
        borderWidth: 1.5,
    },
    searchInput: {
        flex: 1,
        fontSize: 14,
        padding: 0,
    },

    // Sort Panel
    sortPanel: {
        position: 'absolute',
        top: 175,
        right: 16,
        width: 200,
        borderRadius: 16,
        borderWidth: 1,
        zIndex: 100,
        elevation: 20,
        paddingVertical: 8,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.12,
        shadowRadius: 12,
    },
    sortPanelTitle: {
        fontSize: 10,
        fontWeight: '700',
        letterSpacing: 1,
        paddingHorizontal: 16,
        paddingBottom: 8,
        paddingTop: 4,
    },
    sortRow: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingVertical: 12,
        gap: 10,
        borderRadius: 10,
        marginHorizontal: 6,
    },
    sortRowText: { flex: 1, fontSize: 14 },

    // Chips
    chipsWrapper: {
        paddingTop: 14,
        paddingBottom: 4,
    },
    chips: {
        paddingHorizontal: 16,
        gap: 8,
        paddingRight: 20,
    },
    chip: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 12,
        paddingVertical: 7,
        borderRadius: 50,
        borderWidth: 1.5,
        gap: 5,
    },
    chipDot: {
        width: 6,
        height: 6,
        borderRadius: 3,
        backgroundColor: '#fff',
    },
    chipText: {
        fontSize: 13,
        fontWeight: '600',
    },
    chipBadge: {
        paddingHorizontal: 6,
        paddingVertical: 1,
        borderRadius: 10,
        minWidth: 20,
        alignItems: 'center',
    },
    chipBadgeText: {
        fontSize: 11,
        fontWeight: '700',
    },

    // List & Cards
    list: {
        paddingHorizontal: 16,
        paddingTop: 12,
        paddingBottom: 120,
    },
    card: {
        borderRadius: 18,
        padding: 16,
        marginBottom: 12,
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
        borderRadius: 8,
    },
    idText: {
        color: '#7367F0',
        fontWeight: '700',
        fontSize: 12,
    },
    statusPill: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 50,
        gap: 5,
    },
    statusDot: {
        width: 6,
        height: 6,
        borderRadius: 3,
    },
    statusText: {
        fontSize: 12,
        fontWeight: '600',
    },
    clientName: {
        fontSize: 16,
        fontWeight: 'bold',
        marginBottom: 5,
    },
    infoRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 12,
    },
    vehicle: {
        fontSize: 13,
        flex: 1,
    },
    footer: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        borderTopWidth: 1,
        paddingTop: 10,
        marginTop: 2,
    },
    dateBox: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
    },
    date: {
        fontSize: 12,
    },
    total: {
        fontWeight: 'bold',
        fontSize: 16,
    },

    // Empty State
    emptyContainer: {
        alignItems: 'center',
        justifyContent: 'center',
        paddingTop: 60,
        paddingHorizontal: 32,
    },
    emptyTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        marginTop: 16,
        marginBottom: 8,
        textAlign: 'center',
    },
    emptySubtitle: {
        fontSize: 14,
        textAlign: 'center',
        lineHeight: 20,
    },
    clearSearch: {
        marginTop: 20,
        paddingHorizontal: 24,
        paddingVertical: 10,
        backgroundColor: '#7367F010',
        borderRadius: 50,
        borderWidth: 1,
        borderColor: '#7367F030',
    },
});
