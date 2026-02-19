import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, TextInput, ActivityIndicator } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';

import { useNiche } from '../../../context/NicheContext';

export default function TechnicalChecklistScreen() {
    const { osId } = useLocalSearchParams();
    const router = useRouter();
    const { colors } = useTheme();
    const { labels } = useNiche();
    const [loading, setLoading] = useState(false);
    const [items, setItems] = useState<any[]>([]);

    // Get categories from niche labels or use empty default
    const categories = labels.checklist_categories || {};

    useEffect(() => {
        // Inicializa o estado com os itens padrão
        const initialItems: any[] = [];
        Object.entries(categories).forEach(([category, list]) => {
            (list as string[]).forEach(item => {
                initialItems.push({ category, item, status: 'ok', observation: '' });
            });
        });
        setItems(initialItems);
        fetchExistingChecklist();
    }, []);

    const fetchExistingChecklist = async () => {
        try {
            const response = await api.get(`/os/${osId}/technical-checklist`);
            if (response.data.length > 0) {
                // Merge com os existentes
                setItems(prev => prev.map(p => {
                    const existing = response.data.find((e: any) => e.item === p.item && e.category === p.category);
                    return existing ? { ...p, status: existing.status, observation: existing.observation } : p;
                }));
            }
        } catch (error) {
            console.log("Nenhum checklist anterior encontrado.");
        }
    };

    const updateItem = (category: string, item: string, field: string, value: any) => {
        setItems(prev => prev.map(p =>
            (p.category === category && p.item === item) ? { ...p, [field]: value } : p
        ));
    };

    const handleSave = async () => {
        setLoading(true);
        try {
            await api.post('/os/technical-checklist', {
                ordem_servico_id: osId,
                items: items
            });
            Alert.alert("Sucesso", "Checklist técnico salvo!");
            router.back();
        } catch (error) {
            Alert.alert("Erro", "Falha ao salvar checklist.");
        } finally {
            setLoading(false);
        }
    };

    const renderStatusButton = (currentStatus: string, targetStatus: string, icon: any, color: string, onPress: () => void) => (
        <TouchableOpacity
            style={[
                styles.statusBtn,
                currentStatus === targetStatus && { backgroundColor: color + '20', borderColor: color }
            ]}
            onPress={onPress}
        >
            <Ionicons name={icon} size={20} color={currentStatus === targetStatus ? color : '#ccc'} />
        </TouchableOpacity>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <View style={[styles.header, { backgroundColor: colors.card }]}>
                <TouchableOpacity onPress={() => router.back()}>
                    <Ionicons name="close" size={24} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>Checklist Técnico</Text>
                <TouchableOpacity onPress={handleSave} disabled={loading}>
                    {loading ? <ActivityIndicator color={colors.primary} /> : <Text style={{ color: colors.primary, fontWeight: 'bold' }}>Salvar</Text>}
                </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.content}>
                {Object.keys(CATEGORIES).map(category => (
                    <View key={category} style={[styles.section, { backgroundColor: colors.card }]}>
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>{category}</Text>

                        {items.filter(i => i.category === category).map((item, index) => (
                            <View key={index} style={[styles.row, { borderBottomColor: colors.border }]}>
                                <View style={styles.rowTop}>
                                    <Text style={[styles.itemText, { color: colors.text }]}>{item.item}</Text>
                                    <View style={styles.actions}>
                                        {renderStatusButton(item.status, 'ok', 'checkmark-circle', '#28C76F', () => updateItem(category, item.item, 'status', 'ok'))}
                                        {renderStatusButton(item.status, 'warning', 'alert-circle', '#FF9F43', () => updateItem(category, item.item, 'status', 'warning'))}
                                        {renderStatusButton(item.status, 'danger', 'close-circle', '#EA5455', () => updateItem(category, item.item, 'status', 'danger'))}
                                    </View>
                                </View>

                                {item.status !== 'ok' && (
                                    <TextInput
                                        style={[styles.obsInput, { color: colors.text, borderColor: colors.border }]}
                                        placeholder="Descreva o problema..."
                                        placeholderTextColor={colors.subText}
                                        value={item.observation}
                                        onChangeText={(text) => updateItem(category, item.item, 'observation', text)}
                                    />
                                )}
                            </View>
                        ))}
                    </View>
                ))}
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 50, paddingBottom: 15, paddingHorizontal: 20, elevation: 4 },
    headerTitle: { fontSize: 18, fontWeight: 'bold' },
    content: { padding: 15, paddingBottom: 50 },
    section: { borderRadius: 12, padding: 15, marginBottom: 20 },
    sectionTitle: { fontSize: 16, fontWeight: 'bold', marginBottom: 15, textTransform: 'uppercase' },
    row: { marginBottom: 15, borderBottomWidth: 1, paddingBottom: 15 },
    rowTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    itemText: { fontSize: 15, fontWeight: '500', flex: 1 },
    actions: { flexDirection: 'row', gap: 10 },
    statusBtn: { padding: 5, borderRadius: 20, borderWidth: 1, borderColor: 'transparent' },
    obsInput: { marginTop: 10, borderWidth: 1, borderRadius: 8, padding: 8, fontSize: 14 }
});
