import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, TextInput, ActivityIndicator } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import * as Haptics from 'expo-haptics';

import { useNiche } from '../../../context/NicheContext';

export default function TechnicalChecklistScreen() {
    const { osId } = useLocalSearchParams();
    const router = useRouter();
    const { colors } = useTheme();
    const { labels, niche } = useNiche();
    const [loading, setLoading] = useState(false);
    const [items, setItems] = useState<any[]>([]);

    const categories = labels.checklist_categories || {};

    useEffect(() => {
        const initialItems: any[] = [];
        Object.entries(categories).forEach(([category, list]) => {
            (list as string[]).forEach(item => {
                initialItems.push({ category, item, status: 'ok', observation: '' });
            });
        });
        setItems(initialItems);
        fetchExistingChecklist(initialItems);
    }, []);

    const fetchExistingChecklist = async (initialItems: any[]) => {
        try {
            const response = await api.get(`/os/${osId}/technical-checklist`);
            if (response.data.length > 0) {
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

    const cycleStatus = (category: string, item: string) => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        setItems(prev => prev.map(p => {
            if (p.category === category && p.item === item) {
                let nextStatus = 'ok';
                if (p.status === 'ok') nextStatus = 'warning';
                else if (p.status === 'warning') nextStatus = 'danger';
                return { ...p, status: nextStatus };
            }
            return p;
        }));
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

    const getStatusColor = (status: string) => {
        if (status === 'ok') return '#28C76F';
        if (status === 'warning') return '#FF9F43';
        return '#EA5455';
    };

    const getStatusIcon = (status: string) => {
        if (status === 'ok') return 'checkmark-circle';
        if (status === 'warning') return 'alert-circle';
        return 'close-circle';
    };

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
                {niche === 'electronics' && (
                    <View style={styles.tipBox}>
                        <Ionicons name="bulb-outline" size={20} color="#00CFE8" />
                        <Text style={styles.tipText}>Toque no item para alterar o status rapidamente durante os testes na bancada.</Text>
                    </View>
                )}

                {Object.keys(categories).map(category => (
                    <View key={category} style={[styles.section, { backgroundColor: colors.card }]}>
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>{category}</Text>

                        {niche === 'electronics' ? (
                            <View style={styles.gridContainer}>
                                {items.filter(i => i.category === category).map((item, index) => {
                                    const itemColor = getStatusColor(item.status);
                                    return (
                                        <TouchableOpacity
                                            key={index}
                                            activeOpacity={0.7}
                                            style={[
                                                styles.gridItem,
                                                { backgroundColor: itemColor + '15', borderColor: itemColor }
                                            ]}
                                            onPress={() => cycleStatus(category, item.item)}
                                        >
                                            <Ionicons name={getStatusIcon(item.status)} size={22} color={itemColor} style={{ marginBottom: 4 }} />
                                            <Text style={[styles.gridItemText, { color: colors.text }]} numberOfLines={2} adjustsFontSizeToFit>
                                                {item.item}
                                            </Text>
                                        </TouchableOpacity>
                                    );
                                })}

                                {/* Render observations inputs below grid for warned/danger items */}
                                {items.filter(i => i.category === category && i.status !== 'ok').map((item, index) => (
                                    <View key={`obs-${index}`} style={styles.gridObsWrapper}>
                                        <Text style={[styles.gridObsLabel, { color: getStatusColor(item.status) }]}>{item.item}:</Text>
                                        <TextInput
                                            style={[styles.obsInput, { color: colors.text, borderColor: colors.border, marginTop: 4 }]}
                                            placeholder="Descrever problema..."
                                            placeholderTextColor={colors.subText}
                                            value={item.observation}
                                            onChangeText={(text) => updateItem(category, item.item, 'observation', text)}
                                        />
                                    </View>
                                ))}
                            </View>
                        ) : (
                            <View>
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
                        )}
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
    tipBox: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#00CFE815', padding: 12, borderRadius: 8, marginBottom: 20 },
    tipText: { color: '#00CFE8', fontSize: 13, flex: 1, marginLeft: 10, lineHeight: 18 },
    section: { borderRadius: 12, padding: 15, marginBottom: 20 },
    sectionTitle: { fontSize: 16, fontWeight: 'bold', marginBottom: 15, textTransform: 'uppercase' },
    row: { marginBottom: 15, borderBottomWidth: 1, paddingBottom: 15 },
    rowTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    itemText: { fontSize: 15, fontWeight: '500', flex: 1 },
    actions: { flexDirection: 'row', gap: 10 },
    statusBtn: { padding: 5, borderRadius: 20, borderWidth: 1, borderColor: 'transparent' },
    obsInput: { marginTop: 10, borderWidth: 1, borderRadius: 8, padding: 8, fontSize: 14 },
    // Grid Styles
    gridContainer: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, justifyContent: 'flex-start' },
    gridItem: { width: '31%', aspectRatio: 1, borderRadius: 12, borderWidth: 1, padding: 8, alignItems: 'center', justifyContent: 'center' },
    gridItemText: { fontSize: 11, fontWeight: '600', textAlign: 'center', marginTop: 4 },
    gridObsWrapper: { width: '100%', marginTop: 8 },
    gridObsLabel: { fontSize: 12, fontWeight: 'bold' }
});
