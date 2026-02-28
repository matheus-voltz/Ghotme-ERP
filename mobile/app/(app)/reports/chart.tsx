import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, Dimensions, TouchableOpacity } from 'react-native';
import { Stack } from 'expo-router';
import { useTheme } from '../../../context/ThemeContext';
import Animated, { FadeInDown, FadeIn, FadeOut } from 'react-native-reanimated';
import { Ionicons } from '@expo/vector-icons';
import * as Haptics from 'expo-haptics';
import api from '../../../services/api';

const screenWidth = Dimensions.get('window').width;

export default function ChartDetailScreen() {
    const { colors } = useTheme();
    const [data, setData] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [selectedBar, setSelectedBar] = useState<number | null>(null);

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            const resp = await api.get('/reports/chart');
            setData(resp.data);
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <View style={[styles.center, { backgroundColor: colors.background }]}>
                <Stack.Screen options={{ title: 'Carregando...' }} />
                <ActivityIndicator size="large" color="#7367F0" />
            </View>
        );
    }

    if (!data) {
        return (
            <View style={[styles.center, { backgroundColor: colors.background }]}>
                <Stack.Screen options={{ title: 'Erro' }} />
                <Text style={{ color: colors.text }}>Nenhum dado encontrado.</Text>
            </View>
        );
    }

    const chartData = data.chart_data || [];
    const maxVal = Math.max(...chartData.map((d: any) => d.value), 1);

    return (
        <ScrollView
            style={[styles.container, { backgroundColor: colors.background }]}
            contentContainerStyle={{ padding: 20, paddingBottom: 60 }}
            contentInsetAdjustmentBehavior="automatic"
        >
            <Stack.Screen options={{
                title: 'Fluxo de Receita',
                headerBackTitle: 'Voltar',
                headerShadowVisible: false,
                headerStyle: { backgroundColor: colors.background },
            }} />

            {/* Hero Metric */}
            <Animated.View entering={FadeInDown.duration(400)}>
                <Text style={[styles.title, { color: colors.text }]}>{data.title}</Text>
                <Text style={[styles.description, { color: colors.subText }]}>{data.description}</Text>

                <View style={[styles.heroCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
                    <Text style={[styles.mainMetric, { color: colors.text }]}>{data.main_metric}</Text>
                    <Text style={[styles.subMetric, { color: colors.subText }]}>{data.secondary_metric}</Text>
                </View>
            </Animated.View>

            {/* Highlights Row */}
            {data.highlights && (
                <Animated.View entering={FadeInDown.duration(500).delay(100)} style={styles.highlightsRow}>
                    {data.highlights.map((h: any, i: number) => (
                        <View key={i} style={[styles.highlightCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <Text style={[styles.highlightLabel, { color: colors.subText }]}>{h.label}</Text>
                            <Text style={[styles.highlightValue, { color: colors.text }]}>{h.value}</Text>
                        </View>
                    ))}
                </Animated.View>
            )}

            {/* Full 30-Day Chart */}
            <Animated.View entering={FadeInDown.duration(500).delay(200)}>
                <Text style={[styles.sectionTitle, { color: colors.text }]}>Gráfico Diário</Text>
                <View style={[styles.chartBox, { backgroundColor: colors.card, borderColor: colors.border }]}>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 4 }}>
                        <View style={styles.chartBars}>
                            {chartData.map((day: any, idx: number) => {
                                const height = (day.value / maxVal) * 120;
                                const isSelected = selectedBar === idx;
                                return (
                                    <TouchableOpacity
                                        key={idx}
                                        style={styles.chartColumn}
                                        activeOpacity={0.7}
                                        onPress={() => {
                                            Haptics.selectionAsync();
                                            setSelectedBar(isSelected ? null : idx);
                                        }}
                                    >
                                        {isSelected && day.value > 0 && (
                                            <Animated.View entering={FadeIn.duration(200)} exiting={FadeOut.duration(200)} style={styles.tooltip}>
                                                <Text style={styles.tooltipText}>
                                                    R$ {day.value > 1000 ? (day.value / 1000).toFixed(1) + 'k' : day.value.toFixed(0)}
                                                </Text>
                                            </Animated.View>
                                        )}
                                        <View style={[
                                            styles.bar,
                                            {
                                                height: Math.max(3, height),
                                                backgroundColor: isSelected ? '#CE9FFC' : (day.value === 0 ? '#EA545530' : '#7367F0'),
                                                width: isSelected ? 12 : 8,
                                            }
                                        ]} />
                                        <Text style={[styles.dayLabel, { color: colors.subText, fontSize: 8 }]}>
                                            {idx % 3 === 0 ? day.day : ''}
                                        </Text>
                                    </TouchableOpacity>
                                );
                            })}
                        </View>
                    </ScrollView>
                </View>
            </Animated.View>

            {/* Top Services */}
            {data.top_services && data.top_services.length > 0 && (
                <Animated.View entering={FadeInDown.duration(500).delay(350)}>
                    <Text style={[styles.sectionTitle, { color: colors.text, marginTop: 24 }]}>
                        🏆 Top Serviços do Mês
                    </Text>
                    <View style={[styles.topServicesCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
                        {data.top_services.map((svc: any, i: number) => {
                            const svcMax = Math.max(...data.top_services.map((s: any) => s.earned), 1);
                            const pct = (svc.earned / svcMax) * 100;
                            const medal = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `${i + 1}.`;
                            return (
                                <View key={i} style={styles.svcRow}>
                                    <View style={styles.svcLabelRow}>
                                        <Text style={[styles.svcName, { color: colors.text }]}>
                                            {medal} {svc.name}
                                        </Text>
                                        <Text style={[styles.svcEarned, { color: colors.text }]}>
                                            R$ {svc.earned.toFixed(2)} ({svc.qty}x)
                                        </Text>
                                    </View>
                                    <View style={[styles.svcBarBg, { backgroundColor: colors.border + '25' }]}>
                                        <View style={[styles.svcBarFill, { width: `${pct}%`, backgroundColor: i === 0 ? '#7367F0' : i === 1 ? '#28C76F' : '#FF9F43' }]} />
                                    </View>
                                </View>
                            );
                        })}
                    </View>
                </Animated.View>
            )}

            {/* IA Insights */}
            {data.insights && data.insights.length > 0 && (
                <Animated.View entering={FadeInDown.duration(500).delay(500)} style={{ marginTop: 24 }}>
                    <Text style={[styles.sectionTitle, { color: colors.text }]}>🤖 Análise Ghotme IA</Text>
                    {data.insights.map((insight: string, i: number) => (
                        <View key={i} style={[styles.insightCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <Text style={[styles.insightHeader, { color: '#7367F0' }]}>Ghotme IA diz:</Text>
                            <Text style={[styles.insightText, { color: colors.text, opacity: 0.9 }]}>{insight}</Text>
                        </View>
                    ))}
                </Animated.View>
            )}
        </ScrollView>
    );
}

const styles = StyleSheet.create({
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    container: { flex: 1 },
    title: { fontSize: 22, fontWeight: 'bold', marginBottom: 6, letterSpacing: -0.5 },
    description: { fontSize: 13, marginBottom: 20, opacity: 0.8 },
    heroCard: {
        padding: 24, borderRadius: 20, borderWidth: 1, marginBottom: 20,
        alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.08, shadowRadius: 10, elevation: 3,
    },
    mainMetric: { fontSize: 30, fontWeight: '900', marginBottom: 6 },
    subMetric: { fontSize: 13, fontWeight: '500', opacity: 0.8 },
    highlightsRow: { flexDirection: 'column', gap: 8, marginBottom: 24 },
    highlightCard: {
        padding: 14, borderRadius: 14, borderWidth: 1,
        flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    },
    highlightLabel: { fontSize: 13, fontWeight: '500' },
    highlightValue: { fontSize: 13, fontWeight: 'bold' },
    sectionTitle: { fontSize: 14, fontWeight: '600', marginBottom: 12, textTransform: 'uppercase', letterSpacing: 1 },
    chartBox: { padding: 16, borderRadius: 16, borderWidth: 1 },
    chartBars: { flexDirection: 'row', alignItems: 'flex-end', height: 160, paddingTop: 30 },
    chartColumn: { alignItems: 'center', marginHorizontal: 3, justifyContent: 'flex-end' },
    bar: { borderRadius: 4 },
    dayLabel: { marginTop: 4 },
    tooltip: {
        backgroundColor: '#7367F0', paddingHorizontal: 6, paddingVertical: 2,
        borderRadius: 6, marginBottom: 4,
    },
    tooltipText: { color: '#fff', fontSize: 9, fontWeight: 'bold' },
    topServicesCard: { padding: 16, borderRadius: 16, borderWidth: 1 },
    svcRow: { marginBottom: 14 },
    svcLabelRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 5 },
    svcName: { fontSize: 13, fontWeight: '500', flex: 1 },
    svcEarned: { fontSize: 12, fontWeight: 'bold' },
    svcBarBg: { height: 6, borderRadius: 3, overflow: 'hidden' },
    svcBarFill: { height: '100%', borderRadius: 3 },
    insightCard: { padding: 16, borderRadius: 16, borderWidth: 1, marginBottom: 12, borderLeftWidth: 4, borderLeftColor: '#7367F0' },
    insightHeader: { fontSize: 13, fontWeight: '700', marginBottom: 6 },
    insightText: { fontSize: 13, lineHeight: 20 },
});
