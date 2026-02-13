import React, { useState } from 'react';
import { View, Text, TextInput, StyleSheet, ScrollView, TouchableOpacity, Alert, ActivityIndicator, KeyboardAvoidingView, Platform } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';

// Componente CustomInput (reutilizado)
const CustomInput = ({ label, icon, value, onChangeText, placeholder, keyboardType = 'default', flex = 1 }: any) => (
    <View style={[styles.inputGroup, { flex }]}>
        <Text style={styles.label}>{label}</Text>
        <View style={styles.inputWrapper}>
            <Ionicons name={icon} size={18} color="#7367F0" style={styles.inputIcon} />
            <TextInput
                style={styles.input}
                value={value} onChangeText={onChangeText}
                placeholder={placeholder} placeholderTextColor="#9CA3AF"
                keyboardType={keyboardType}
            />
        </View>
    </View>
);

export default function CreateInventoryScreen() {
    const router = useRouter();
    const { colors } = useTheme();
    const [loading, setLoading] = useState(false);

    const [form, setForm] = useState({
        name: '',
        sku: '',
        selling_price: '',
        cost_price: '',
        quantity: '',
        min_quantity: '5'
    });

    const updateForm = (key: string, value: string) => setForm(prev => ({ ...prev, [key]: value }));

    const handleSubmit = async () => {
        if (!form.name || !form.selling_price || !form.quantity) {
            Alert.alert("Atenção", "Preencha Nome, Preço de Venda e Quantidade.");
            return;
        }

        setLoading(true);
        try {
            await api.post('/inventory/items', form);
            Alert.alert("Sucesso", "Peça adicionada ao estoque!", [
                { text: "OK", onPress: () => router.back() }
            ]);
        } catch (error: any) {
            console.error("Erro ao salvar peça:", error);
            Alert.alert("Erro", "Não foi possível salvar a peça.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <KeyboardAvoidingView behavior={Platform.OS === "ios" ? "padding" : "height"} style={{ flex: 1, backgroundColor: '#F3F4F6' }}>
            <View style={styles.header}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color="#333" />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>Nova Peça</Text>
                <View style={{ width: 40 }} />
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent}>
                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <Ionicons name="cube-outline" size={20} color="#7367F0" />
                        <Text style={styles.cardTitle}>Dados do Item</Text>
                    </View>
                    
                    <CustomInput label="Nome da Peça *" icon="pricetag-outline" value={form.name} onChangeText={(v:any) => updateForm('name', v)} placeholder="Ex: Filtro de Óleo" />
                    <CustomInput label="SKU / Código" icon="barcode-outline" value={form.sku} onChangeText={(v:any) => updateForm('sku', v)} placeholder="FIL-1234" />
                    
                    <View style={styles.row}>
                        <CustomInput label="Quantidade *" icon="layers-outline" value={form.quantity} onChangeText={(v:any) => updateForm('quantity', v)} placeholder="10" keyboardType="numeric" />
                        <CustomInput label="Estoque Mín." icon="alert-circle-outline" value={form.min_quantity} onChangeText={(v:any) => updateForm('min_quantity', v)} placeholder="5" keyboardType="numeric" />
                    </View>
                </View>

                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <Ionicons name="cash-outline" size={20} color="#7367F0" />
                        <Text style={styles.cardTitle}>Valores (R$)</Text>
                    </View>
                    <View style={styles.row}>
                        <CustomInput label="Custo (Compra)" icon="trending-down-outline" value={form.cost_price} onChangeText={(v:any) => updateForm('cost_price', v)} placeholder="0.00" keyboardType="numeric" />
                        <CustomInput label="Venda (Cliente) *" icon="trending-up-outline" value={form.selling_price} onChangeText={(v:any) => updateForm('selling_price', v)} placeholder="0.00" keyboardType="numeric" />
                    </View>
                </View>
            </ScrollView>

            <View style={styles.footer}>
                <TouchableOpacity activeOpacity={0.8} onPress={handleSubmit} disabled={loading}>
                    <LinearGradient colors={['#7367F0', '#CE9FFC']} start={{x:0, y:0}} end={{x:1, y:0}} style={styles.submitBtn}>
                        {loading ? <ActivityIndicator color="#fff" /> : (
                            <>
                                <Ionicons name="save-outline" size={22} color="#fff" />
                                <Text style={styles.submitBtnText}>Salvar no Estoque</Text>
                            </>
                        )}
                    </LinearGradient>
                </TouchableOpacity>
            </View>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 15, backgroundColor: '#fff' },
    backBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#1F2937' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    card: { backgroundColor: '#fff', borderRadius: 16, padding: 16, marginBottom: 16, elevation: 2 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 15, borderBottomWidth: 1, borderBottomColor: '#F3F4F6', paddingBottom: 10 },
    cardTitle: { fontSize: 15, fontWeight: 'bold', color: '#374151', marginLeft: 8 },
    inputGroup: { marginBottom: 15 },
    label: { fontSize: 12, fontWeight: '700', marginBottom: 6, marginLeft: 4, textTransform: 'uppercase', color: '#6B7280' },
    inputWrapper: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F9FAFB', borderWidth: 1, borderColor: '#E5E7EB', borderRadius: 12, height: 52 },
    inputIcon: { paddingHorizontal: 12 },
    input: { flex: 1, fontSize: 15, paddingRight: 12, color: '#1F2937' },
    row: { flexDirection: 'row', gap: 12 },
    footer: { padding: 20, paddingBottom: Platform.OS === 'ios' ? 40 : 20, backgroundColor: '#fff', borderTopWidth: 1, borderTopColor: '#E5E7EB' },
    submitBtn: { height: 56, borderRadius: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
    submitBtnText: { color: '#fff', fontSize: 17, fontWeight: 'bold' }
});
