import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, FlatList, ActivityIndicator, StatusBar, Modal
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter, Stack } from 'expo-router';
import { useTheme } from '../../../context/ThemeContext';
import { useLanguage } from '../../../context/LanguageContext';
import { useNiche } from '../../../context/NicheContext';
import { LinearGradient } from 'expo-linear-gradient';
import Animated, { FadeInDown, FadeInUp, ZoomIn } from 'react-native-reanimated';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import api from '../../../services/api';

const numberFormat = (value: any) =>
    parseFloat(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export default function AccountsPayableScreen() {
    const { colors } = useTheme();
    const { t, language } = useLanguage();
    const { niche } = useNiche();
    const router = useRouter();
    const insets = useSafeAreaInsets();

    const [loading, setLoading] = useState(true);
    const [bills, setBills] = useState<any[]>([]);
    const [stats, setStats] = useState({ pending: 0, paid_today: 0, due_soon: 0 });
    const [search, setSearch] = useState('');
    const [filter, setFilter] = useState('pending'); // pending, paid, overdue
    const [selectedBill, setSelectedBill] = useState<any>(null);

    useEffect(() => {
        fetchBills();
    }, [filter]);

    const fetchBills = async () => {
        setLoading(true);
        try {
            // Mock de dados para visualização enquanto a API é ajustada
            setTimeout(() => {
                const mockBills = [
                    { id: 1, title: 'Aluguel Março', supplier: 'Imobiliária Central', amount: 2500, due_date: '2026-03-10', status: 'pending', category: 'bills' },
                    { id: 2, title: 'Fornecedor de Bebidas', supplier: 'Ambev S.A.', amount: 1250.50, due_date: '2026-03-08', status: 'overdue', category: 'suppliers' },
                    { id: 3, title: 'Energia Elétrica', supplier: 'Enel', amount: 450.20, due_date: '2026-03-07', status: 'paid', category: 'bills' },
                    { id: 4, title: 'Salário João Silva', supplier: 'Funcionário', amount: 1800, due_date: '2026-03-05', status: 'paid', category: 'employees' },
                    { id: 5, title: 'Internet Fibra', supplier: 'Vivo Empresas', amount: 199.90, due_date: '2026-03-15', status: 'pending', category: 'bills' },
                ];
                
                setBills(mockBills.filter(b => filter === 'all' || b.status === filter));
                setStats({ pending: 3750.50, paid_today: 2250.20, due_soon: 2500 });
                setLoading(false);
            }, 800);
        } catch (e) {
            console.error(e);
            setLoading(false);
        }
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'paid': return '#28C76F';
            case 'pending': return '#FF9F43';
            case 'overdue': return '#EA5455';
            default: return '#7367F0';
        }
    };

    const getCategoryIcon = (cat: string) => {
        switch (cat) {
            case 'suppliers': return 'cart-outline';
            case 'employees': return 'people-outline';
            default: return 'document-text-outline';
        }
    };

    const renderBillItem = ({ item, index }: { item: any, index: number }) => (
        <Animated.View entering={FadeInDown.delay(index * 100).duration(500)}>
            <TouchableOpacity 
                style={[styles.billCard, { backgroundColor: colors.card, borderColor: colors.border }]}
                onPress={() => setSelectedBill(item)}
            >
                <View style={[styles.iconBox, { backgroundColor: getStatusColor(item.status) + '15' }]}>
                    <Ionicons name={getCategoryIcon(item.category)} size={22} color={getStatusColor(item.status)} />
                </View>
                
                <View style={styles.billInfo}>
                    <Text style={[styles.billTitle, { color: colors.text }]} numberOfLines={1}>{item.title}</Text>
                    <Text style={[styles.billSupplier, { color: colors.subText }]} numberOfLines={1}>{item.supplier}</Text>
                </View>

                <View style={styles.billAmountContainer}>
                    <Text style={[styles.billAmount, { color: colors.text }]}>R$ {numberFormat(item.amount)}</Text>
                    <Text style={[styles.billDate, { color: item.status === 'overdue' ? '#EA5455' : colors.subText }]}>
                        {new Date(item.due_date).toLocaleDateString(language)}
                    </Text>
                </View>
            </TouchableOpacity>
        </Animated.View>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <StatusBar barStyle="light-content" backgroundColor="#7367F0" />
            
            {/* Header */}
            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={[styles.header, { paddingTop: insets.top + 10 }]}>
                <View style={styles.headerTop}>
                    <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                        <Ionicons name="chevron-back" size={28} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.headerTitle}>{t('accounts_payable')}</Text>
                    <TouchableOpacity style={styles.addBtn}>
                        <Ionicons name="add-circle" size={28} color="#fff" />
                    </TouchableOpacity>
                </View>

                <View style={styles.statsRow}>
                    <View style={styles.statItem}>
                        <Text style={styles.statLabel}>{t('total_pending')}</Text>
                        <Text style={styles.statValue}>R$ {numberFormat(stats.pending)}</Text>
                    </View>
                    <View style={styles.statDivider} />
                    <View style={styles.statItem}>
                        <Text style={styles.statLabel}>{t('paid_today')}</Text>
                        <Text style={styles.statValue}>R$ {numberFormat(stats.paid_today)}</Text>
                    </View>
                </View>
            </LinearGradient>

            {/* Content */}
            <View style={styles.content}>
                {/* Search & Filter */}
                <View style={styles.filterSection}>
                    <View style={[styles.searchBar, { backgroundColor: colors.card, borderColor: colors.border }]}>
                        <Ionicons name="search-outline" size={20} color={colors.subText} />
                        <TextInput 
                            placeholder={t('search_bill')}
                            placeholderTextColor={colors.subText}
                            style={[styles.searchInput, { color: colors.text }]}
                            value={search}
                            onChangeText={setSearch}
                        />
                    </View>

                    <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.tabsScroll}>
                        {[
                            { id: 'pending', label: t('status_unpaid') },
                            { id: 'overdue', label: t('status_overdue') },
                            { id: 'paid', label: t('status_paid') },
                            { id: 'all', label: t('all') },
                        ].map((tab) => (
                            <TouchableOpacity 
                                key={tab.id}
                                style={[
                                    styles.tabBtn, 
                                    filter === tab.id && { backgroundColor: '#7367F0' }
                                ]}
                                onPress={() => setFilter(tab.id)}
                            >
                                <Text style={[
                                    styles.tabText, 
                                    { color: filter === tab.id ? '#fff' : colors.subText }
                                ]}>{tab.label}</Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>
                </View>

                {loading ? (
                    <View style={styles.loadingBox}>
                        <ActivityIndicator size="large" color="#7367F0" />
                    </View>
                ) : (
                    <FlatList
                        data={bills}
                        keyExtractor={(item) => item.id.toString()}
                        renderItem={renderBillItem}
                        contentContainerStyle={{ paddingBottom: 100 }}
                        showsVerticalScrollIndicator={false}
                        ListEmptyComponent={
                            <View style={styles.emptyBox}>
                                <Ionicons name="document-text-outline" size={48} color={colors.subText + '44'} />
                                <Text style={{ color: colors.subText, marginTop: 10 }}>Nenhuma conta encontrada</Text>
                            </View>
                        }
                    />
                )}
            </View>

            {/* FAB Nova Conta */}
            <Animated.View entering={FadeInUp.delay(600)} style={styles.fabContainer}>
                <TouchableOpacity style={styles.fab} activeOpacity={0.8}>
                    <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.fabGradient}>
                        <Ionicons name="add" size={30} color="#fff" />
                    </LinearGradient>
                </TouchableOpacity>
            </Animated.View>

            {/* Modal de Detalhes */}
            <Modal visible={!!selectedBill} animationType="slide" transparent>
                <View style={styles.modalOverlay}>
                    <View style={[styles.modalContent, { backgroundColor: colors.card }]}>
                        <View style={styles.modalHandle} />
                        {selectedBill && (
                            <>
                                <View style={styles.modalHeader}>
                                    <View style={[styles.modalIcon, { backgroundColor: getStatusColor(selectedBill.status) + '15' }]}>
                                        <Ionicons name={getCategoryIcon(selectedBill.category)} size={32} color={getStatusColor(selectedBill.status)} />
                                    </View>
                                    <Text style={[styles.modalTitle, { color: colors.text }]}>{selectedBill.title}</Text>
                                    <Text style={[styles.modalSupplier, { color: colors.subText }]}>{selectedBill.supplier}</Text>
                                </View>

                                <View style={styles.modalDetails}>
                                    <View style={styles.detailRow}>
                                        <Text style={[styles.detailLabel, { color: colors.subText }]}>{t('amount')}</Text>
                                        <Text style={[styles.detailValue, { color: '#7367F0' }]}>R$ {numberFormat(selectedBill.amount)}</Text>
                                    </View>
                                    <View style={styles.detailRow}>
                                        <Text style={[styles.detailLabel, { color: colors.subText }]}>{t('due_date')}</Text>
                                        <Text style={[styles.detailValue, { color: colors.text }]}>{new Date(selectedBill.due_date).toLocaleDateString(language)}</Text>
                                    </View>
                                </View>

                                <View style={styles.modalActions}>
                                    {selectedBill.status !== 'paid' && (
                                        <TouchableOpacity style={styles.payBtn}>
                                            <Text style={styles.payBtnText}>{t('status_paid').toUpperCase()}</Text>
                                        </TouchableOpacity>
                                    )}
                                    <TouchableOpacity style={styles.closeBtn} onPress={() => setSelectedBill(null)}>
                                        <Text style={[styles.closeBtnText, { color: colors.subText }]}>Fechar</Text>
                                    </TouchableOpacity>
                                </View>
                            </>
                        )}
                    </View>
                </View>
            </Modal>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: {
        paddingHorizontal: 20,
        paddingBottom: 25,
        borderBottomLeftRadius: 30,
        borderBottomRightRadius: 30,
    },
    headerTop: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 20 },
    backBtn: { padding: 5 },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#fff' },
    addBtn: { padding: 5 },
    statsRow: { flexDirection: 'row', justifyContent: 'space-around', alignItems: 'center' },
    statItem: { alignItems: 'center' },
    statLabel: { color: 'rgba(255,255,255,0.7)', fontSize: 12, marginBottom: 4 },
    statValue: { color: '#fff', fontSize: 18, fontWeight: 'bold' },
    statDivider: { width: 1, height: 30, backgroundColor: 'rgba(255,255,255,0.2)' },
    
    content: { flex: 1, paddingHorizontal: 20 },
    filterSection: { marginTop: 20, marginBottom: 15 },
    searchBar: {
        flexDirection: 'row', alignItems: 'center', paddingHorizontal: 15,
        height: 50, borderRadius: 12, borderWidth: 1, marginBottom: 15
    },
    searchInput: { flex: 1, marginLeft: 10, fontSize: 14 },
    tabsScroll: { marginBottom: 10 },
    tabBtn: {
        paddingHorizontal: 20, height: 36, borderRadius: 18,
        justifyContent: 'center', alignItems: 'center', marginRight: 10,
        borderWidth: 1, borderColor: '#7367F030'
    },
    tabText: { fontSize: 13, fontWeight: '600' },

    billCard: {
        flexDirection: 'row', alignItems: 'center', padding: 15,
        borderRadius: 16, borderWidth: 1, marginBottom: 12,
    },
    iconBox: { width: 44, height: 44, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
    billInfo: { flex: 1, marginLeft: 15 },
    billTitle: { fontSize: 15, fontWeight: 'bold', marginBottom: 2 },
    billSupplier: { fontSize: 12 },
    billAmountContainer: { alignItems: 'flex-end' },
    billAmount: { fontSize: 15, fontWeight: 'bold', marginBottom: 2 },
    billDate: { fontSize: 11, fontWeight: '500' },

    loadingBox: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    emptyBox: { flex: 1, justifyContent: 'center', alignItems: 'center', marginTop: 50 },

    fabContainer: { position: 'absolute', bottom: 30, right: 20 },
    fab: { width: 56, height: 56, borderRadius: 28, elevation: 5, shadowColor: '#7367F0', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 8 },
    fabGradient: { width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center' },

    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalContent: { borderTopLeftRadius: 30, borderTopRightRadius: 30, padding: 25, paddingBottom: 40 },
    modalHandle: { width: 40, height: 5, backgroundColor: '#e5e5ea', borderRadius: 3, alignSelf: 'center', marginBottom: 20 },
    modalHeader: { alignItems: 'center', marginBottom: 25 },
    modalIcon: { width: 70, height: 70, borderRadius: 20, justifyContent: 'center', alignItems: 'center', marginBottom: 15 },
    modalTitle: { fontSize: 20, fontWeight: 'bold', marginBottom: 5 },
    modalSupplier: { fontSize: 14 },
    modalDetails: { gap: 15, marginBottom: 30 },
    detailRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#f2f2f7' },
    detailLabel: { fontSize: 14, fontWeight: '500' },
    detailValue: { fontSize: 16, fontWeight: 'bold' },
    modalActions: { gap: 10 },
    payBtn: { backgroundColor: '#28C76F', height: 50, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
    payBtnText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },
    closeBtn: { height: 50, justifyContent: 'center', alignItems: 'center' },
    closeBtnText: { fontWeight: '600' },
});
