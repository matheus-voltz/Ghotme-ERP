import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, Alert, ActivityIndicator, Linking } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import * as Haptics from 'expo-haptics';

export default function PendingBudgetsScreen() {
    const router = useRouter();
    const { colors } = useTheme();
    const { labels, niche } = useNiche();
    const [budgets, setBudgets] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchBudgets();
    }, []);

    const fetchBudgets = async () => {
        try {
            const response = await api.get('/budgets/pending');
            setBudgets(response.data);
        } catch (error) {
            Alert.alert('Erro', 'Não foi possível carregar os orçamentos.');
        } finally {
            setLoading(false);
        }
    };

    const handleWhatsApp = (budget: any) => {
        const phone = budget.client?.phone || budget.client?.whatsapp;
        if (!phone) {
            Alert.alert("Erro", "Cliente sem telefone cadastrado.");
            return;
        }

        const cleanPhone = phone.replace(/\D/g, '');
        const baseUrl = api.defaults.baseURL?.replace('/api', '') || 'https://ghotme.com.br';
        const link = `${baseUrl}/view-budget/${budget.uuid}`;

        const message = `Olá, ${budget.client?.name || 'Cliente'}! Tudo bem?\nSegue o link do seu orçamento detalhado para aprovação do ${labels.entity.toLowerCase()} ${budget.veiculo?.modelo || ''}:\n\n🔗 ${link}\n\nVocê pode aprovar direto pelo link!`;

        const url = `whatsapp://send?phone=55${cleanPhone}&text=${encodeURIComponent(message)}`;
        Linking.openURL(url).catch(() => Alert.alert('Erro', 'WhatsApp não está instalado.'));
    };

    const handleApprove = async (id: number) => {
        Alert.alert(
            "Aprovar Orçamento",
            "Deseja aprovar manualmente este orçamento?",
            [
                { text: 'Cancelar', style: 'cancel' },
                {
                    text: 'Aprovar',
                    style: 'default',
                    onPress: async () => {
                        try {
                            await api.post(`/budgets/${id}/approve`);
                            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
                            fetchBudgets();
                        } catch (e) {
                            Alert.alert("Erro", "Falha ao aprovar.");
                        }
                    }
                }
            ]
        );
    };

    const renderItem = ({ item }: { item: any }) => {
        const isLate = item.created_at && new Date(item.created_at).getTime() < new Date().getTime() - 5 * 24 * 60 * 60 * 1000;

        return (
            <View style={[styles.card, { backgroundColor: colors.card, borderColor: isLate ? '#EA5455' : colors.border, borderWidth: isLate ? 1.5 : 1 }]}>
                <View style={styles.cardHeader}>
                    <Text style={[styles.clientId, { color: colors.text }]}>Orçamento #{item.id}</Text>
                    <View style={[styles.badge, isLate && { backgroundColor: '#EA545520' }]}>
                        <Text style={[styles.badgeText, isLate && { color: '#EA5455' }]}>{isLate ? 'Atrasado > 5d' : 'Pendente'}</Text>
                    </View>
                </View>

                <Text style={[styles.clientName, { color: colors.text }]}>
                    {item.client?.name || item.client?.company_name || 'N/A'}
                </Text>
                <Text style={[styles.vehicle, { color: colors.subText }]}>
                    {item.veiculo?.marca} {item.veiculo?.modelo} - {item.veiculo?.placa}
                </Text>

                <View style={styles.actions}>
                    <TouchableOpacity
                        style={[styles.btn, { backgroundColor: '#28C76F' }]}
                        onPress={() => handleWhatsApp(item)}
                    >
                        <Ionicons name="logo-whatsapp" size={16} color="#fff" />
                        <Text style={styles.btnText}>Cobrar no Zap</Text>
                    </TouchableOpacity>

                    <TouchableOpacity
                        style={[styles.btn, { backgroundColor: '#7367F0' }]}
                        onPress={() => handleApprove(item.id)}
                    >
                        <Ionicons name="checkmark-done" size={16} color="#fff" />
                        <Text style={styles.btnText}>Aprovar</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    };

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <View style={styles.header}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color="#fff" />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>Orçamentos Pendentes</Text>
                <View style={{ width: 40 }} />
            </View>

            {loading ? (
                <View style={styles.center}>
                    <ActivityIndicator size="large" color="#7367F0" />
                </View>
            ) : budgets.length === 0 ? (
                <View style={styles.center}>
                    <Ionicons name="document-text-outline" size={48} color={colors.subText} />
                    <Text style={[styles.emptyText, { color: colors.subText }]}>Nenhum orçamento pendente.</Text>
                </View>
            ) : (
                <FlatList
                    data={budgets}
                    keyExtractor={(item) => item.id.toString()}
                    contentContainerStyle={{ padding: 16, paddingBottom: 40 }}
                    renderItem={renderItem}
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: {
        flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
        backgroundColor: '#7367F0', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 15
    },
    backBtn: { width: 40, height: 40, justifyContent: 'center' },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#fff' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    emptyText: { marginTop: 10, fontSize: 16 },
    card: {
        borderRadius: 12, padding: 16, marginBottom: 16, borderWidth: 1,
        elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4
    },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    clientId: { fontSize: 16, fontWeight: 'bold' },
    badge: { backgroundColor: '#FF9F4320', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 12 },
    badgeText: { color: '#FF9F43', fontSize: 12, fontWeight: 'bold' },
    clientName: { fontSize: 18, fontWeight: 'bold', marginBottom: 4 },
    vehicle: { fontSize: 14, marginBottom: 16 },
    actions: { flexDirection: 'row', gap: 10 },
    btn: {
        flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
        paddingVertical: 12, borderRadius: 8, gap: 6
    },
    btnText: { color: '#fff', fontWeight: 'bold', fontSize: 14 }
});
