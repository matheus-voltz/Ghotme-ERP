import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { useLocalSearchParams, useRouter, Stack } from 'expo-router';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import { useAuth } from '../../../context/AuthContext';
import Animated, { FadeInDown } from 'react-native-reanimated';
import { Ionicons } from '@expo/vector-icons';

export default function InventoryDetailScreen() {
    const params = useLocalSearchParams();
    const { colors } = useTheme();
    const { labels } = useNiche();
    const { user } = useAuth();
    const router = useRouter();

    const item = {
        id: params.id,
        name: params.name as string || 'Item',
        sku: params.sku as string || 'N/A',
        cost_price: parseFloat(params.cost_price as string || '0'),
        selling_price: parseFloat(params.selling_price as string || '0'),
        quantity: parseInt(params.quantity as string || '0', 10),
        min_quantity: parseInt(params.min_quantity as string || '5', 10),
        location: params.location as string || '',
        unit: params.unit as string || 'un',
        supplier_name: params.supplier_name as string || '',
    };

    const margin = item.selling_price > 0 && item.cost_price > 0
        ? ((item.selling_price - item.cost_price) / item.selling_price) * 100
        : 0;
    const profit = item.selling_price - item.cost_price;
    const stockPercent = item.min_quantity > 0
        ? Math.min((item.quantity / (item.min_quantity * 3)) * 100, 100)
        : 100;
    const isLowStock = item.quantity <= item.min_quantity;
    const stockColor = isLowStock ? '#EA5455' : item.quantity <= item.min_quantity * 2 ? '#FF9F43' : '#28C76F';
    const potentialRevenue = item.quantity * item.selling_price;
    const potentialProfit = item.quantity * profit;

    const itemLabel = labels.inventory_items?.split('/')[0] || 'Peça';

    // Ghotme IA insights
    const insights: string[] = [];
    if (isLowStock) {
        insights.push(`⚠️ Estoque crítico! Restam apenas ${item.quantity} ${item.unit}. Faça um pedido ao fornecedor o quanto antes.`);
    }
    if (margin > 50) {
        insights.push(`🤑 Margem excelente de ${margin.toFixed(0)}%! Esse item é um dos seus melhores em rentabilidade.`);
    } else if (margin > 0 && margin < 20) {
        insights.push(`📉 Margem baixa de ${margin.toFixed(0)}%. Considere renegociar o preço de compra com o fornecedor ou aumentar o valor de venda.`);
    }
    if (potentialRevenue > 0) {
        insights.push(`💰 Com o estoque atual, você tem R$ ${potentialRevenue.toFixed(2)} de potencial em vendas e R$ ${potentialProfit.toFixed(2)} de lucro potencial.`);
    }
    if (!item.sku || item.sku === 'N/A') {
        insights.push('🏷️ Esse item não tem SKU cadastrado. Adicionar um código facilita a busca e organização do estoque.');
    }

    return (
        <ScrollView
            style={[styles.container, { backgroundColor: colors.background }]}
            contentContainerStyle={{ padding: 20, paddingBottom: 60 }}
            contentInsetAdjustmentBehavior="automatic"
        >
            <Stack.Screen options={{
                title: 'Detalhe',
                headerBackTitle: 'Voltar',
                headerShadowVisible: false,
                headerStyle: { backgroundColor: colors.background },
            }} />

            {/* Hero Card */}
            <Animated.View entering={FadeInDown.duration(400)}>
                <View style={[styles.heroCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
                    <View style={[styles.heroIcon, { backgroundColor: stockColor + '20' }]}>
                        <Ionicons name="cube" size={32} color={stockColor} />
                    </View>
                    <Text style={[styles.heroName, { color: colors.text }]}>{item.name}</Text>
                    <Text style={[styles.heroSku, { color: colors.subText }]}>SKU: {item.sku}</Text>
                    <View style={[styles.stockBadge, { backgroundColor: stockColor + '15' }]}>
                        <Text style={[styles.stockBadgeText, { color: stockColor }]}>
                            {isLowStock ? '⚠️ Estoque Baixo' : '✅ Estoque OK'} — {item.quantity} {item.unit}
                        </Text>
                    </View>
                </View>
            </Animated.View>

            {/* Pricing & Margin - Only for Admin */}
            {user?.role === 'admin' ? (
                <Animated.View entering={FadeInDown.duration(500).delay(100)}>
                    <Text style={[styles.sectionTitle, { color: colors.text }]}>💰 Preços & Margem</Text>
                    <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
                        <View style={styles.priceRow}>
                            <View style={styles.priceItem}>
                                <Text style={[styles.priceLabel, { color: colors.subText }]}>Custo</Text>
                                <Text style={[styles.priceValue, { color: '#EA5455' }]}>R$ {item.cost_price.toFixed(2)}</Text>
                            </View>
                            <View style={styles.priceItem}>
                                <Text style={[styles.priceLabel, { color: colors.subText }]}>Venda</Text>
                                <Text style={[styles.priceValue, { color: '#28C76F' }]}>R$ {item.selling_price.toFixed(2)}</Text>
                            </View>
                            <View style={styles.priceItem}>
                                <Text style={[styles.priceLabel, { color: colors.subText }]}>Lucro</Text>
                                <Text style={[styles.priceValue, { color: '#7367F0' }]}>R$ {profit.toFixed(2)}</Text>
                            </View>
                        </View>

                        <View style={{ marginTop: 16 }}>
                            <View style={styles.marginLabelRow}>
                                <Text style={[styles.marginLabel, { color: colors.subText }]}>Margem de Lucro</Text>
                                <Text style={[styles.marginPercent, { color: margin > 30 ? '#28C76F' : '#FF9F43' }]}>{margin.toFixed(1)}%</Text>
                            </View>
                            <View style={[styles.marginBarBg, { backgroundColor: colors.border + '40' }]}>
                                <View style={[styles.marginBarFill, {
                                    width: `${Math.min(margin, 100)}%`,
                                    backgroundColor: margin > 50 ? '#28C76F' : margin > 20 ? '#FF9F43' : '#EA5455',
                                }]} />
                            </View>
                        </View>
                    </View>
                </Animated.View>
            ) : (
                <Animated.View entering={FadeInDown.duration(500).delay(100)}>
                    <Text style={[styles.sectionTitle, { color: colors.text }]}>💰 Valor de Venda</Text>
                    <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border, alignItems: 'center' }]}>
                        <Text style={[styles.priceLabel, { color: colors.subText, fontSize: 13 }]}>Preço ao Consumidor</Text>
                        <Text style={[styles.priceValue, { color: '#28C76F', fontSize: 28, marginTop: 4 }]}>R$ {item.selling_price.toFixed(2)}</Text>
                    </View>
                </Animated.View>
            )}

            {/* Stock Level Gauge */}
            <Animated.View entering={FadeInDown.duration(500).delay(200)}>
                <Text style={[styles.sectionTitle, { color: colors.text }]}>📦 Nível de Estoque</Text>
                <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
                    <View style={styles.stockRow}>
                        <View style={styles.stockInfo}>
                            <Text style={[styles.stockLabel, { color: colors.subText }]}>Atual</Text>
                            <Text style={[styles.stockValue, { color: colors.text }]}>{item.quantity} {item.unit}</Text>
                        </View>
                        <View style={styles.stockInfo}>
                            <Text style={[styles.stockLabel, { color: colors.subText }]}>Mínimo</Text>
                            <Text style={[styles.stockValue, { color: colors.text }]}>{item.min_quantity} {item.unit}</Text>
                        </View>
                    </View>
                    <View style={[styles.gaugeBarBg, { backgroundColor: colors.border + '40' }]}>
                        <View style={[styles.gaugeBarFill, { width: `${stockPercent}%`, backgroundColor: stockColor }]} />
                    </View>
                    <Text style={[styles.gaugeHint, { color: colors.subText }]}>
                        {isLowStock
                            ? `Faltam ${item.min_quantity - item.quantity >= 0 ? item.min_quantity - item.quantity : 0} para atingir o mínimo seguro`
                            : `${item.quantity - item.min_quantity} acima do mínimo seguro`
                        }
                    </Text>
                </View>
            </Animated.View>

            {/* Extra Info */}
            {(item.location || item.supplier_name || item.unit) && (
                <Animated.View entering={FadeInDown.duration(500).delay(300)}>
                    <Text style={[styles.sectionTitle, { color: colors.text }]}>📍 Info Rápida</Text>
                    <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
                        {item.location ? (
                            <View style={styles.infoRow}>
                                <Ionicons name="location-outline" size={18} color="#7367F0" />
                                <Text style={[styles.infoLabel, { color: colors.subText }]}>Localização</Text>
                                <Text style={[styles.infoValue, { color: colors.text }]}>{item.location}</Text>
                            </View>
                        ) : null}
                        {item.supplier_name ? (
                            <View style={styles.infoRow}>
                                <Ionicons name="business-outline" size={18} color="#7367F0" />
                                <Text style={[styles.infoLabel, { color: colors.subText }]}>Fornecedor</Text>
                                <Text style={[styles.infoValue, { color: colors.text }]}>{item.supplier_name}</Text>
                            </View>
                        ) : null}
                        <View style={styles.infoRow}>
                            <Ionicons name="scale-outline" size={18} color="#7367F0" />
                            <Text style={[styles.infoLabel, { color: colors.subText }]}>Unidade</Text>
                            <Text style={[styles.infoValue, { color: colors.text }]}>{item.unit}</Text>
                        </View>
                    </View>
                </Animated.View>
            )}

            {/* Ghotme IA - Filtered based on role */}
            {insights.length > 0 && (
                <Animated.View entering={FadeInDown.duration(500).delay(400)}>
                    <Text style={[styles.sectionTitle, { color: colors.text }]}>🤖 Análise Ghotme IA</Text>
                    {insights.filter(tip => {
                        if (user?.role !== 'admin') {
                            // Hide tips that mention profit or margin for employees
                            return !tip.toLowerCase().includes('margem') && !tip.toLowerCase().includes('lucro');
                        }
                        return true;
                    }).map((tip, i) => (
                        <View key={i} style={[styles.insightCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <Text style={[styles.insightHeader, { color: '#7367F0' }]}>Ghotme IA diz:</Text>
                            <Text style={[styles.insightText, { color: colors.text, opacity: 0.9 }]}>{tip}</Text>
                        </View>
                    ))}
                </Animated.View>
            )}
        </ScrollView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    heroCard: {
        padding: 24, borderRadius: 20, borderWidth: 1, marginBottom: 24,
        alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.08, shadowRadius: 10, elevation: 3,
    },
    heroIcon: { width: 60, height: 60, borderRadius: 16, alignItems: 'center', justifyContent: 'center', marginBottom: 12 },
    heroName: { fontSize: 22, fontWeight: 'bold', marginBottom: 4, textAlign: 'center' },
    heroSku: { fontSize: 13, marginBottom: 12 },
    stockBadge: { paddingHorizontal: 14, paddingVertical: 6, borderRadius: 20 },
    stockBadgeText: { fontSize: 13, fontWeight: '700' },
    sectionTitle: { fontSize: 14, fontWeight: '600', marginBottom: 12, textTransform: 'uppercase', letterSpacing: 1 },
    card: { padding: 18, borderRadius: 16, borderWidth: 1, marginBottom: 20 },
    priceRow: { flexDirection: 'row', justifyContent: 'space-between' },
    priceItem: { alignItems: 'center', flex: 1 },
    priceLabel: { fontSize: 11, fontWeight: '600', textTransform: 'uppercase', marginBottom: 4 },
    priceValue: { fontSize: 18, fontWeight: '900' },
    marginLabelRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 },
    marginLabel: { fontSize: 12, fontWeight: '500' },
    marginPercent: { fontSize: 14, fontWeight: '900' },
    marginBarBg: { height: 8, borderRadius: 4, overflow: 'hidden' },
    marginBarFill: { height: '100%', borderRadius: 4 },
    stockRow: { flexDirection: 'row', justifyContent: 'space-around', marginBottom: 16 },
    stockInfo: { alignItems: 'center' },
    stockLabel: { fontSize: 11, fontWeight: '600', textTransform: 'uppercase', marginBottom: 4 },
    stockValue: { fontSize: 22, fontWeight: '900' },
    gaugeBarBg: { height: 10, borderRadius: 5, overflow: 'hidden' },
    gaugeBarFill: { height: '100%', borderRadius: 5 },
    gaugeHint: { fontSize: 12, marginTop: 8, textAlign: 'center' },
    infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 10, borderBottomWidth: 0.5, borderBottomColor: '#eee', gap: 10 },
    infoLabel: { fontSize: 13, fontWeight: '500', flex: 1 },
    infoValue: { fontSize: 14, fontWeight: '700' },
    insightCard: { padding: 16, borderRadius: 16, borderWidth: 1, marginBottom: 12, borderLeftWidth: 4, borderLeftColor: '#7367F0' },
    insightHeader: { fontSize: 13, fontWeight: '700', marginBottom: 6 },
    insightText: { fontSize: 13, lineHeight: 20 },
});
