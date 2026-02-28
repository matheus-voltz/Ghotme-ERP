import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator } from 'react-native';
import { useLocalSearchParams, useRouter, Stack } from 'expo-router';
import { useTheme } from '../../../context/ThemeContext';
import Animated, { FadeInDown } from 'react-native-reanimated';
import api from '../../../services/api';

export default function ReportScreen() {
    const { type } = useLocalSearchParams();
    const { colors } = useTheme();

    const [data, setData] = useState<any>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchData();
    }, [type]);

    const fetchData = async () => {
        try {
            const resp = await api.get(`/reports/${type}`);
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

    return (
        <ScrollView
            style={[styles.container, { backgroundColor: colors.background }]}
            contentContainerStyle={{ padding: 20, paddingBottom: 60 }}
            contentInsetAdjustmentBehavior="automatic"
        >
            <Stack.Screen options={{
                title: 'Inteligência',
                headerBackTitle: 'Voltar',
                headerLargeTitle: true,
                headerShadowVisible: false,
                headerStyle: { backgroundColor: colors.background }
            }} />

            <Animated.View entering={FadeInDown.duration(400)}>
                <Text style={[styles.title, { color: colors.text }]}>{data.title}</Text>
                <Text style={[styles.description, { color: colors.subText }]}>{data.description}</Text>

                <View style={[styles.heroCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
                    <Text style={[styles.mainMetric, { color: colors.text }]}>{data.main_metric}</Text>
                    <Text style={[styles.subMetric, { color: colors.subText }]}>{data.secondary_metric}</Text>
                </View>
            </Animated.View>

            {data.charts && data.charts.length > 0 && (
                <Animated.View entering={FadeInDown.duration(500).delay(200)} style={styles.chartSection}>
                    <Text style={[styles.sectionTitle, { color: colors.text }]}>Detalhamento</Text>
                    <View style={[styles.chartContainer, { backgroundColor: colors.card, borderColor: colors.border }]}>
                        {data.charts.map((chart: any, i: number) => {
                            const maxVal = Math.max(...data.charts.map((c: any) => c.value), 1);
                            const widthPct = (chart.value / maxVal) * 100;

                            return (
                                <View key={i} style={styles.chartRow}>
                                    <View style={styles.chartLabelRow}>
                                        <Text style={[styles.chartLabel, { color: colors.text }]}>{chart.label}</Text>
                                        <Text style={[styles.chartLabelValue, { color: colors.text }]}>
                                            {chart.value > 100 ? `R$ ${chart.value.toFixed(2)}` : chart.value}
                                        </Text>
                                    </View>
                                    <View style={[styles.barBg, { backgroundColor: colors.border + '30' }]}>
                                        <View style={[styles.barFill, { backgroundColor: chart.color || '#7367F0', width: `${widthPct}%` }]} />
                                    </View>
                                </View>
                            );
                        })}
                    </View>
                </Animated.View>
            )}

            {data.insights && data.insights.length > 0 && (
                <Animated.View entering={FadeInDown.duration(500).delay(400)} style={styles.insightSection}>
                    <Text style={[styles.sectionTitle, { color: colors.text }]}>🤖 Análise Ghotme IA</Text>
                    {data.insights.map((insight: string, i: number) => (
                        <View key={i} style={[styles.insightCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <Text style={[styles.insightHeader, { color: colors.text }]}>Ghotme IA diz:</Text>
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
    title: { fontSize: 24, fontWeight: 'bold', marginBottom: 8, letterSpacing: -0.5 },
    description: { fontSize: 13, marginBottom: 24, opacity: 0.8 },
    heroCard: {
        padding: 24,
        borderRadius: 20,
        borderWidth: 1,
        marginBottom: 30,
        alignItems: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 10,
        elevation: 3,
    },
    mainMetric: { fontSize: 32, fontWeight: '900', marginBottom: 8 },
    subMetric: { fontSize: 13, fontWeight: '500', opacity: 0.8 },
    sectionTitle: { fontSize: 14, fontWeight: '600', marginBottom: 15, textTransform: 'uppercase', letterSpacing: 1 },
    chartSection: { marginBottom: 30 },
    chartContainer: {
        padding: 20,
        borderRadius: 16,
        borderWidth: 1,
    },
    chartRow: { marginBottom: 15 },
    chartLabelRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 },
    chartLabel: { fontSize: 13, fontWeight: '500' },
    chartLabelValue: { fontSize: 13, fontWeight: 'bold' },
    barBg: { height: 8, borderRadius: 4, overflow: 'hidden' },
    barFill: { height: '100%', borderRadius: 4 },
    insightSection: { marginBottom: 40 },
    insightCard: { padding: 16, borderRadius: 16, borderWidth: 1, marginBottom: 12, borderLeftWidth: 4, borderLeftColor: '#7367F0' },
    insightHeader: { fontSize: 13, fontWeight: '700', marginBottom: 6, color: '#7367F0' },
    insightText: { fontSize: 13, lineHeight: 20 },
});
