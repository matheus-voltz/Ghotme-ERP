import React, { useState } from 'react';
import {
    View, Text, TextInput, StyleSheet, ScrollView, TouchableOpacity,
    Alert, KeyboardAvoidingView, Platform
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import Animated, { FadeInDown, FadeInUp } from 'react-native-reanimated';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import * as Haptics from 'expo-haptics';
import { useTheme } from '../../../context/ThemeContext';

interface Ingredient {
    id: string;
    name: string;
    pricePerKg: string;
    gramsPerPortion: string;
}

const fmt = (v: number) => v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export default function CostCalculatorScreen() {
    const { colors } = useTheme();
    const insets = useSafeAreaInsets();
    const router = useRouter();

    const [ingredients, setIngredients] = useState<Ingredient[]>([]);

    // Formulário de novo ingrediente
    const [name, setName] = useState('');
    const [pricePerKg, setPricePerKg] = useState('');
    const [gramsPerPortion, setGramsPerPortion] = useState('');

    // Simulador de precificação
    const [simulateType, setSimulateType] = useState<'price' | 'margin'>('margin');
    const [desiredMargin, setDesiredMargin] = useState('50');
    const [desiredPrice, setDesiredPrice] = useState('');

    const addIngredient = () => {
        if (!name.trim() || !pricePerKg.trim() || !gramsPerPortion.trim()) {
            Alert.alert('Atenção', 'Preencha todos os campos do ingrediente.');
            return;
        }

        const priceNum = parseFloat(pricePerKg.replace(',', '.'));
        const gramsNum = parseFloat(gramsPerPortion.replace(',', '.'));

        if (isNaN(priceNum) || isNaN(gramsNum)) {
            Alert.alert('Erro', 'Valores numéricos inválidos.');
            return;
        }

        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        setIngredients([...ingredients, {
            id: Date.now().toString(),
            name: name.trim(),
            pricePerKg: pricePerKg.replace(',', '.'),
            gramsPerPortion: gramsPerPortion.replace(',', '.')
        }]);

        // Limpar inputs de ingrediente
        setName('');
        setPricePerKg('');
        setGramsPerPortion('');
    };

    const removeIngredient = (id: string) => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
        setIngredients(ingredients.filter(ig => ig.id !== id));
    };

    // Cálculos Gerais
    const calcCost = (ig: Ingredient) => {
        const kgPrice = parseFloat(ig.pricePerKg) || 0;
        const gPortion = parseFloat(ig.gramsPerPortion) || 0;
        // Preço por grama = Kg / 1000
        return (kgPrice / 1000) * gPortion;
    };

    const totalCost = ingredients.reduce((sum, ig) => sum + calcCost(ig), 0);

    // Precificação
    let suggestedPrice = 0;
    let computedMargin = 0;
    let computedProfit = 0;

    if (simulateType === 'margin') {
        const marginPerc = parseFloat(desiredMargin.replace(',', '.')) || 0;
        if (marginPerc < 100 && marginPerc > 0) {
            // formula: preço = custo / (1 - margem%)
            suggestedPrice = totalCost / (1 - (marginPerc / 100));
            computedProfit = suggestedPrice - totalCost;
            computedMargin = marginPerc;
        }
    } else {
        suggestedPrice = parseFloat(desiredPrice.replace(',', '.')) || 0;
        computedProfit = suggestedPrice - totalCost;
        if (suggestedPrice > 0) {
            computedMargin = (computedProfit / suggestedPrice) * 100;
        }
    }

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            {/* ── Header ── */}
            <LinearGradient colors={['#16a085', '#2ecc71']} style={[styles.header, { paddingTop: insets.top + 10 }]}>
                <TouchableOpacity onPress={() => router.back()}>
                    <Ionicons name="chevron-back" size={28} color="#fff" />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>Calculadora de Custos</Text>
                <View style={{ width: 40 }} />
            </LinearGradient>

            <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
                <ScrollView contentContainerStyle={{ padding: 20, paddingBottom: 100 }} showsVerticalScrollIndicator={false}>

                    {/* ADICIONAR INGREDIENTE */}
                    <Animated.View entering={FadeInDown.duration(400).springify()}>
                        <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
                            <View style={styles.cardHeader}>
                                <Ionicons name="basket-outline" size={20} color="#16a085" />
                                <Text style={[styles.cardTitle, { color: colors.text }]}>Novo Ingrediente</Text>
                            </View>

                            <View style={styles.inputRow}>
                                <TextInput
                                    style={[styles.input, { flex: 1, backgroundColor: colors.background, color: colors.text, borderColor: colors.border }]}
                                    placeholder="Nome (Ex: Queijo)"
                                    placeholderTextColor={colors.subText}
                                    value={name}
                                    onChangeText={setName}
                                />
                            </View>

                            <View style={styles.inputRow}>
                                <View style={[styles.inputGroup, { flex: 1, marginRight: 8 }]}>
                                    <Text style={[styles.label, { color: colors.subText }]}>Preço por Kg (R$)</Text>
                                    <View style={[styles.inputWrapper, { backgroundColor: colors.background, borderColor: colors.border }]}>
                                        <Text style={{ color: colors.subText, marginLeft: 10 }}>R$</Text>
                                        <TextInput
                                            style={[styles.inputInner, { color: colors.text }]}
                                            placeholder="0,00"
                                            placeholderTextColor={colors.subText}
                                            value={pricePerKg}
                                            onChangeText={setPricePerKg}
                                            keyboardType="numeric"
                                        />
                                    </View>
                                </View>

                                <View style={[styles.inputGroup, { flex: 1, marginLeft: 8 }]}>
                                    <Text style={[styles.label, { color: colors.subText }]}>Quantidade / Porção</Text>
                                    <View style={[styles.inputWrapper, { backgroundColor: colors.background, borderColor: colors.border }]}>
                                        <TextInput
                                            style={[styles.inputInner, { color: colors.text }]}
                                            placeholder="0"
                                            placeholderTextColor={colors.subText}
                                            value={gramsPerPortion}
                                            onChangeText={setGramsPerPortion}
                                            keyboardType="numeric"
                                        />
                                        <Text style={{ color: colors.subText, marginRight: 10 }}>g</Text>
                                    </View>
                                </View>
                            </View>

                            <TouchableOpacity style={styles.addBtn} onPress={addIngredient}>
                                <LinearGradient colors={['#16a085', '#2ecc71']} style={styles.addBtnGradient}>
                                    <Ionicons name="add-circle-outline" size={20} color="#fff" style={{ marginRight: 6 }} />
                                    <Text style={styles.addBtnText}>Adicionar Ingrediente</Text>
                                </LinearGradient>
                            </TouchableOpacity>
                        </View>
                    </Animated.View>

                    {/* LISTA DE INGREDIENTES */}
                    {ingredients.length > 0 && (
                        <View style={styles.listSection}>
                            <Text style={[styles.sectionTitle, { color: colors.text }]}>Ingredientes no Prato</Text>
                            {ingredients.map((ig, idx) => {
                                const cost = calcCost(ig);
                                return (
                                    <Animated.View key={ig.id} entering={FadeInDown.delay(idx * 50).duration(300)}>
                                        <View style={[styles.ingredientItem, { backgroundColor: colors.card, borderColor: colors.border }]}>
                                            <View style={{ flex: 1 }}>
                                                <Text style={[styles.igName, { color: colors.text }]}>{ig.name}</Text>
                                                <Text style={[styles.igDetails, { color: colors.subText }]}>
                                                    R$ {fmt(parseFloat(ig.pricePerKg))} /kg  •  {ig.gramsPerPortion}g na porção
                                                </Text>
                                            </View>
                                            <View style={{ alignItems: 'flex-end', marginLeft: 10 }}>
                                                <Text style={[styles.igCost, { color: '#EA5455' }]}>
                                                    R$ {fmt(cost)}
                                                </Text>
                                                <TouchableOpacity onPress={() => removeIngredient(ig.id)} style={styles.removeBtn}>
                                                    <Ionicons name="trash-outline" size={16} color="#EA5455" />
                                                </TouchableOpacity>
                                            </View>
                                        </View>
                                    </Animated.View>
                                );
                            })}

                            <View style={[styles.totalRow, { backgroundColor: '#EA545515', borderColor: '#EA545530' }]}>
                                <Text style={[styles.totalLabel, { color: '#EA5455' }]}>Custo Total</Text>
                                <Text style={[styles.totalValue, { color: '#EA5455' }]}>R$ {fmt(totalCost)}</Text>
                            </View>
                        </View>
                    )}

                    {/* PRECIFICADOR */}
                    {totalCost > 0 && (
                        <Animated.View entering={FadeInUp.duration(500).springify()}>
                            <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border, marginTop: 20 }]}>
                                <View style={styles.cardHeader}>
                                    <Ionicons name="cash-outline" size={20} color="#7367F0" />
                                    <Text style={[styles.cardTitle, { color: colors.text }]}>Simulador de Lucro</Text>
                                </View>

                                <View style={styles.simulatorTabs}>
                                    <TouchableOpacity
                                        style={[styles.simTab, simulateType === 'margin' && styles.simTabActive, { borderColor: simulateType === 'margin' ? '#7367F0' : colors.border }]}
                                        onPress={() => { Haptics.selectionAsync(); setSimulateType('margin'); }}
                                    >
                                        <Text style={[styles.simTabText, { color: simulateType === 'margin' ? '#7367F0' : colors.subText }]}>Definir Margem %</Text>
                                    </TouchableOpacity>

                                    <TouchableOpacity
                                        style={[styles.simTab, simulateType === 'price' && styles.simTabActive, { borderColor: simulateType === 'price' ? '#7367F0' : colors.border, marginLeft: 10 }]}
                                        onPress={() => { Haptics.selectionAsync(); setSimulateType('price'); }}
                                    >
                                        <Text style={[styles.simTabText, { color: simulateType === 'price' ? '#7367F0' : colors.subText }]}>Definir Preço (R$)</Text>
                                    </TouchableOpacity>
                                </View>

                                {simulateType === 'margin' ? (
                                    <View style={styles.inputGroup}>
                                        <Text style={[styles.label, { color: colors.subText }]}>Margem de Lucro Desejada (%)</Text>
                                        <View style={[styles.inputWrapper, { backgroundColor: colors.background, borderColor: colors.border }]}>
                                            <TextInput
                                                style={[styles.inputInner, { color: colors.text }]}
                                                placeholder="Ex: 50"
                                                placeholderTextColor={colors.subText}
                                                value={desiredMargin}
                                                onChangeText={setDesiredMargin}
                                                keyboardType="numeric"
                                            />
                                            <Text style={{ color: colors.subText, marginRight: 15 }}>%</Text>
                                        </View>
                                    </View>
                                ) : (
                                    <View style={styles.inputGroup}>
                                        <Text style={[styles.label, { color: colors.subText }]}>Preço de Venda Final (R$)</Text>
                                        <View style={[styles.inputWrapper, { backgroundColor: colors.background, borderColor: colors.border }]}>
                                            <Text style={{ color: colors.subText, marginLeft: 15 }}>R$</Text>
                                            <TextInput
                                                style={[styles.inputInner, { color: colors.text }]}
                                                placeholder="Ex: 25,00"
                                                placeholderTextColor={colors.subText}
                                                value={desiredPrice}
                                                onChangeText={setDesiredPrice}
                                                keyboardType="numeric"
                                            />
                                        </View>
                                    </View>
                                )}

                                {/* RESULTADOS */}
                                <View style={styles.resultBox}>
                                    <View style={styles.resultRow}>
                                        <Text style={styles.resultLabel}>Custo do Produto:</Text>
                                        <Text style={styles.resultValueCost}>R$ {fmt(totalCost)}</Text>
                                    </View>

                                    <View style={styles.resultRow}>
                                        <Text style={styles.resultLabel}>Preço de Venda Sugerido:</Text>
                                        <Text style={styles.resultValueSales}>R$ {fmt(suggestedPrice)}</Text>
                                    </View>

                                    <View style={[styles.resultDivider, { borderBottomColor: colors.border }]} />

                                    <View style={styles.resultRow}>
                                        <Text style={styles.resultLabel}>Lucro Líquido:</Text>
                                        <Text style={[styles.resultValueProfit, { color: computedProfit >= 0 ? '#28C76F' : '#EA5455' }]}>
                                            R$ {fmt(computedProfit)}
                                        </Text>
                                    </View>
                                    <View style={styles.resultRow}>
                                        <Text style={styles.resultLabel}>Margem do Produto:</Text>
                                        <Text style={[styles.resultValueProfit, { color: computedProfit >= 0 ? '#28C76F' : '#EA5455' }]}>
                                            {fmt(computedMargin)}%
                                        </Text>
                                    </View>
                                </View>

                            </View>
                        </Animated.View>
                    )}

                </ScrollView>
            </KeyboardAvoidingView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingBottom: 15, paddingHorizontal: 15 },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#fff' },

    card: { borderRadius: 16, borderWidth: 1, padding: 16 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 15, gap: 8 },
    cardTitle: { fontSize: 16, fontWeight: '700' },

    inputRow: { flexDirection: 'row', marginBottom: 12 },
    inputGroup: { flex: 1 },
    label: { fontSize: 13, fontWeight: '600', marginBottom: 6 },

    input: { height: 48, borderWidth: 1, borderRadius: 12, paddingHorizontal: 15, fontSize: 15 },
    inputWrapper: { height: 48, borderWidth: 1, borderRadius: 12, flexDirection: 'row', alignItems: 'center' },
    inputInner: { flex: 1, height: '100%', paddingHorizontal: 15, fontSize: 15 },

    addBtn: { marginTop: 10 },
    addBtnGradient: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', height: 48, borderRadius: 12 },
    addBtnText: { color: '#fff', fontSize: 15, fontWeight: '700' },

    listSection: { marginTop: 25 },
    sectionTitle: { fontSize: 16, fontWeight: '700', marginBottom: 15 },
    ingredientItem: { flexDirection: 'row', padding: 15, borderRadius: 12, borderWidth: 1, marginBottom: 10 },
    igName: { fontSize: 15, fontWeight: '700', marginBottom: 4 },
    igDetails: { fontSize: 13 },
    igCost: { fontSize: 15, fontWeight: '800', marginBottom: 6 },
    removeBtn: { padding: 4 },

    totalRow: { flexDirection: 'row', justifyContent: 'space-between', padding: 15, borderRadius: 12, borderWidth: 1, marginTop: 5 },
    totalLabel: { fontSize: 16, fontWeight: 'bold' },
    totalValue: { fontSize: 18, fontWeight: '900' },

    simulatorTabs: { flexDirection: 'row', marginBottom: 20 },
    simTab: { flex: 1, paddingVertical: 12, alignItems: 'center', borderWidth: 1, borderRadius: 12 },
    simTabActive: { backgroundColor: '#7367F010' },
    simTabText: { fontSize: 13, fontWeight: '700' },

    resultBox: { marginTop: 20, backgroundColor: '#f8f9fa', padding: 15, borderRadius: 12, borderWidth: 1, borderColor: '#e9ecef' },
    resultRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    resultLabel: { fontSize: 14, color: '#495057', fontWeight: '500' },
    resultValueCost: { fontSize: 15, fontWeight: '700', color: '#EA5455' },
    resultValueSales: { fontSize: 16, fontWeight: '800', color: '#16a085' },
    resultDivider: { borderBottomWidth: 1, marginVertical: 10 },
    resultValueProfit: { fontSize: 18, fontWeight: '900' },
});
