import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Image } from 'react-native';
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
        unit: (params.unit as string) || 'un',
        supplier_name: (params.supplier_name as string) || '',
        category_name: (params.category_name as string) || '',
    };

    const margin = item.selling_price > 0 && item.cost_price > 0
        ? ((item.selling_price - item.cost_price) / item.selling_price) * 100
        : 0;
    const profit = item.selling_price - item.cost_price;
    const stockPercent = item.min_quantity > 0
        ? Math.min((item.quantity / (item.min_quantity * 3)) * 100, 100)
        : 100;

    const tracksStock = item.min_quantity > 0 || item.quantity > 0;
    const isLowStock = tracksStock && item.quantity <= item.min_quantity;

    // Se não controla estoque ou estoque ok, verde. Se isLowStock -> Vermelho. 
    const stockColor = !tracksStock ? '#7367F0' : (isLowStock ? '#EA5455' : (item.quantity <= item.min_quantity * 2 ? '#FF9F43' : '#28C76F'));
    const potentialRevenue = tracksStock ? item.quantity * item.selling_price : 0;
    const potentialProfit = tracksStock ? item.quantity * profit : 0;

    const imageUrl = params.image_url as string;

    const itemLabel = labels.inventory_items?.split('/')[0] || 'Peça';

    // Ghotme IA insights
    const insights: { text: string; type: 'warning' | 'success' | 'info' | 'error' }[] = [];

    if (isLowStock) {
        insights.push({ type: 'error', text: `Estoque crítico! Restam apenas ${item.quantity} ${item.unit}. Faça pedido urgente ao fornecedor.` });
    } else if (tracksStock && item.quantity > item.min_quantity * 3) {
        insights.push({ type: 'warning', text: `Estoque alto! Você tem ${item.quantity} ${item.unit}, o que representa um capital parado. Considere frear os próximos pedidos.` });
    }

    if (item.cost_price > 0 && item.selling_price > 0) {
        if (margin >= 50) {
            insights.push({ type: 'success', text: `Margem excelente de ${margin.toFixed(0)}%! Este item é um grande gerador de lucro.` });
        } else if (margin > 0 && margin <= 25) {
            insights.push({ type: 'warning', text: `Margem apertada de ${margin.toFixed(0)}%. Fique de olho e tente negociar com fornecedores.` });
        }
    } else if (item.selling_price > 0 && item.cost_price === 0) {
        // Se caso não colocou preço de custo
        insights.push({ type: 'info', text: `Sem preço de custo! Adicione o custo de compra para a IA poder medir sua lucratividade.` });
    }

    if (potentialRevenue > 0) {
        insights.push({ type: 'success', text: `Valuation: Este lote atual representa R$ ${potentialRevenue.toFixed(2)} engatilhados em vendas.` });
    }

    if (!item.sku || item.sku === 'N/A') {
        insights.push({ type: 'info', text: `Item sem SKU. Cadastrar SKU (código de barras) ajuda na agilidade do lançamento no sistema.` });
    }

    if (insights.length === 0) {
        insights.push({ type: 'success', text: `O banco de dados confirmou, tudo OK com o cadastro deste item por enquanto!` });
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
                    <View style={[styles.heroIcon, { backgroundColor: imageUrl ? 'transparent' : stockColor + '20' }]}>
                        {imageUrl ? (
                            <Image source={{ uri: imageUrl }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
                        ) : (
                            <Ionicons name="cube" size={32} color={stockColor} />
                        )}
                    </View>
                    <Text style={[styles.heroName, { color: colors.text }]}>{item.name}</Text>
                    {item.sku ? <Text style={[styles.heroSku, { color: colors.subText }]}>SKU: {item.sku}</Text> : null}
                    {tracksStock && (
                        <View style={[styles.stockBadge, { backgroundColor: stockColor + '15' }]}>
                            <Text style={[styles.stockBadgeText, { color: stockColor }]}>
                                {isLowStock ? '⚠️ Estoque Baixo' : '✅ Estoque OK'}
                                {` — ${item.quantity} ${item.unit}`}
                            </Text>
                        </View>
                    )}
                </View>
            </Animated.View>
            {/* Agente de Precificação Ghotme (Somente Admin) */}
            {user?.role === 'admin' && (
                <Animated.View entering={FadeInDown.duration(500).delay(100)}>
                    <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 12, gap: 8 }}>
                        <Ionicons name="sparkles" size={20} color="#FF9F43" />
                        <Text style={[styles.sectionTitle, { color: colors.text, marginBottom: 0 }]}>Agente de Precificação</Text>
                    </View>
                    <View style={[styles.card, { backgroundColor: colors.card, borderColor: '#FF9F4330', borderWidth: 1.5, shadowColor: '#FF9F43', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 10 }]}>

                        <View style={styles.priceRow}>
                            <View style={[styles.priceItem, { backgroundColor: '#EA545515', padding: 12, borderRadius: 12 }]}>
                                <Text style={[styles.priceLabel, { color: '#EA5455' }]}>Custo</Text>
                                <Text style={[styles.priceValue, { color: '#EA5455', fontSize: 14 }]}>R$ {item.cost_price.toFixed(2)}</Text>
                            </View>
                            <View style={[styles.priceItem, { backgroundColor: '#28C76F15', padding: 12, borderRadius: 12, marginHorizontal: 8 }]}>
                                <Text style={[styles.priceLabel, { color: '#28C76F' }]}>Venda</Text>
                                <Text style={[styles.priceValue, { color: '#28C76F', fontSize: 14 }]}>R$ {item.selling_price.toFixed(2)}</Text>
                            </View>
                            <View style={[styles.priceItem, { backgroundColor: '#7367F015', padding: 12, borderRadius: 12 }]}>
                                <Text style={[styles.priceLabel, { color: '#7367F0' }]}>Lucro / Un</Text>
                                <Text style={[styles.priceValue, { color: '#7367F0', fontSize: 14 }]}>R$ {profit.toFixed(2)}</Text>
                            </View>
                        </View>

                        <View style={{ marginTop: 24 }}>
                            <View style={styles.marginLabelRow}>
                                <Text style={[styles.marginLabel, { color: colors.subText, fontWeight: '600' }]}>Performance da Margem</Text>
                                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                                    <View style={{ width: 8, height: 8, borderRadius: 4, backgroundColor: margin > 50 ? '#28C76F' : margin > 20 ? '#FF9F43' : '#EA5455' }} />
                                    <Text style={[styles.marginPercent, { color: colors.text, fontSize: 13 }]}>
                                        {margin.toFixed(1)}% ({margin > 50 ? 'Excelente' : margin > 20 ? 'Razoável' : 'Crítica'})
                                    </Text>
                                </View>
                            </View>

                            <View style={[styles.marginBarBg, { backgroundColor: colors.border + '60', height: 10, borderRadius: 5 }]}>
                                <View style={[styles.marginBarFill, {
                                    width: `${Math.min(Math.max(margin, 0), 100)}%`,
                                    backgroundColor: margin > 50 ? '#28C76F' : margin > 20 ? '#FF9F43' : '#EA5455',
                                    height: '100%', borderRadius: 5,
                                }]} />
                            </View>

                            <Text style={{ fontSize: 13, color: colors.subText, marginTop: 12, lineHeight: 20 }}>
                                {margin > 50
                                    ? 'A margem está ótima! Este item é altamente lucrativo.'
                                    : margin > 20
                                        ? 'Margem razoável. Está bom para atrair clientes, mas fique de olho no custo.'
                                        : 'Atenção. Margem muito baixa! Considere rever o preço de venda ou comprar num fornecedor mais barato.'}
                            </Text>

                            {tracksStock && potentialRevenue > 0 && (
                                <View style={{ marginTop: 16, paddingTop: 16, borderTopWidth: 1, borderTopColor: colors.border }}>
                                    <Text style={{ fontSize: 13, color: colors.subText }}>Simulação com estoque atual ({item.quantity} un):</Text>
                                    <Text style={{ fontSize: 14, color: colors.text, fontWeight: '700', marginTop: 8 }}>
                                        💸 Faturamento: <Text style={{ color: '#28C76F' }}>R$ {potentialRevenue.toFixed(2)}</Text>
                                    </Text>
                                    <Text style={{ fontSize: 14, color: colors.text, fontWeight: '700', marginTop: 4 }}>
                                        💎 Lucro Estimado: <Text style={{ color: '#7367F0' }}>R$ {potentialProfit.toFixed(2)}</Text>
                                    </Text>
                                </View>
                            )}
                        </View>
                    </View>
                </Animated.View>
            )}
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
                            <Ionicons name="apps-outline" size={18} color="#7367F0" />
                            <Text style={[styles.infoLabel, { color: colors.subText }]}>Categoria</Text>
                            <Text style={[styles.infoValue, { color: colors.text }]}>{item.category_name || 'Sem Categoria'}</Text>
                        </View>
                        <View style={styles.infoRow}>
                            <Ionicons name="scale-outline" size={18} color="#7367F0" />
                            <Text style={[styles.infoLabel, { color: colors.subText }]}>Unidade</Text>
                            <Text style={[styles.infoValue, { color: colors.text }]}>{item.unit}</Text>
                        </View>
                    </View>
                </Animated.View>
            )}

            {/* Ghotme IA - Filtered based on role */}
            {(() => {
                const filteredInsights = insights.filter((tip) => {
                    if (user?.role !== 'admin') {
                        // Hide tips that mention profit or margin for employees
                        return !tip.text.toLowerCase().includes('margem') && !tip.text.toLowerCase().includes('lucro') && !tip.text.toLowerCase().includes('valuation');
                    }
                    return true;
                });

                if (filteredInsights.length === 0) return null;

                return (
                    <Animated.View entering={FadeInDown.duration(500).delay(400)}>
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>🤖 Análise Ghotme IA</Text>
                        {filteredInsights.map((tip, i) => {
                            let iconColor = '#7367F0';
                            let iconName: keyof typeof Ionicons.glyphMap = 'information-circle';

                            if (tip.type === 'error') { iconColor = '#EA5455'; iconName = 'alert-circle'; }
                            else if (tip.type === 'warning') { iconColor = '#FF9F43'; iconName = 'warning'; }
                            else if (tip.type === 'success') { iconColor = '#28C76F'; iconName = 'checkmark-circle'; }

                            return (
                                <View key={i} style={[styles.insightCard, { backgroundColor: colors.card, borderColor: colors.border, borderLeftColor: iconColor }]}>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 6, gap: 6 }}>
                                        <Ionicons name={iconName} size={16} color={iconColor} />
                                        <Text style={[styles.insightHeader, { color: iconColor, marginBottom: 0 }]}>Ghotme IA diz:</Text>
                                    </View>
                                    <Text style={[styles.insightText, { color: colors.text, opacity: 0.9 }]}>{tip.text}</Text>
                                </View>
                            );
                        })}
                    </Animated.View>
                );
            })()}
        </ScrollView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    heroCard: {
        padding: 24, borderRadius: 20, borderWidth: 1, marginBottom: 24,
        alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.08, shadowRadius: 10, elevation: 3, marginTop: 10
    },
    heroIcon: { width: 140, height: 140, borderRadius: 70, alignItems: 'center', justifyContent: 'center', marginBottom: 24, overflow: 'hidden' },
    heroName: { fontSize: 24, fontWeight: 'bold', marginBottom: 8, textAlign: 'center' },
    heroSku: { fontSize: 13, marginBottom: 16 },
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
