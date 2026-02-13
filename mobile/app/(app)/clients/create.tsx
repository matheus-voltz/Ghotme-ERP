import React, { useState } from 'react';
import { View, Text, TextInput, StyleSheet, ScrollView, TouchableOpacity, Alert, ActivityIndicator, KeyboardAvoidingView, Platform } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';

// Componente CustomInput fora para evitar perda de foco
const CustomInput = ({ label, icon, value, onChangeText, placeholder, keyboardType = 'default', flex = 1, colors }: any) => (
    <View style={[styles.inputGroup, { flex }]}>
        <Text style={[styles.label, { color: colors.subText }]}>{label}</Text>
        <View style={[styles.inputWrapper, { borderColor: colors.border, backgroundColor: colors.card }]}>
            <Ionicons name={icon} size={18} color={colors.primary} style={styles.inputIcon} />
            <TextInput
                style={[styles.input, { color: colors.text }]}
                value={value}
                onChangeText={onChangeText}
                placeholder={placeholder}
                placeholderTextColor={colors.subText}
                keyboardType={keyboardType}
            />
        </View>
    </View>
);

export default function CreateClientScreen() {
    const router = useRouter();
    const { colors } = useTheme();

    const [loading, setLoading] = useState(false);
    const [type, setType] = useState<'PF' | 'PJ'>('PF');

    const [form, setForm] = useState({
        name: '', cpf: '', rg: '', birth_date: '', company_name: '', trade_name: '',
        cnpj: '', email: '', phone: '', whatsapp: '', cep: '', rua: '',
        numero: '', complemento: '', bairro: '', cidade: '', estado: ''
    });

    const updateForm = (key: string, value: string) => setForm(prev => ({ ...prev, [key]: value }));

    const handleSubmit = async () => {
        const requiredName = type === 'PF' ? form.name : form.company_name;
        if (!requiredName || !form.email) {
            Alert.alert("Campos Obrigatórios", "Por favor, preencha o nome e o e-mail.");
            return;
        }
        setLoading(true);
        try {
            await api.post('/clients-list', { ...form, type });
            Alert.alert("Sucesso! 🎉", "Cliente cadastrado com sucesso!", [{ text: "Ótimo", onPress: () => router.back() }]);
        } catch (error: any) {
            Alert.alert("Erro", "Não foi possível salvar o cliente.");
        } finally { setLoading(false); }
    };

    return (
        <KeyboardAvoidingView behavior={Platform.OS === "ios" ? "padding" : "height"} style={{ flex: 1, backgroundColor: colors.background }}>
            <View style={[styles.header, { backgroundColor: colors.card }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>Novo Cliente</Text>
                <View style={{ width: 40 }} />
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled">
                
                <View style={[styles.segmentedContainer, { backgroundColor: colors.border }]}>
                    <TouchableOpacity style={[styles.segment, type === 'PF' && { backgroundColor: colors.card }]} onPress={() => setType('PF')}>
                        <Text style={[styles.segmentText, type === 'PF' && { color: colors.primary }]}>Pessoa Física</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={[styles.segment, type === 'PJ' && { backgroundColor: colors.card }]} onPress={() => setType('PJ')}>
                        <Text style={[styles.segmentText, type === 'PJ' && { color: colors.primary }]}>Pessoa Jurídica</Text>
                    </TouchableOpacity>
                </View>

                <View style={[styles.card, { backgroundColor: colors.card }]}>
                    <View style={[styles.cardHeader, { borderBottomColor: colors.border }]}>
                        <Ionicons name="id-card-outline" size={20} color={colors.primary} />
                        <Text style={[styles.cardTitle, { color: colors.text }]}>Identificação</Text>
                    </View>
                    {type === 'PF' ? (
                        <>
                            <CustomInput colors={colors} label="Nome Completo *" icon="person-outline" value={form.name} onChangeText={(v:any) => updateForm('name', v)} placeholder="João Silva" />
                            <View style={styles.row}>
                                <CustomInput colors={colors} label="CPF" icon="document-text-outline" value={form.cpf} onChangeText={(v:any) => updateForm('cpf', v)} placeholder="000.000..." keyboardType="numeric" />
                                <CustomInput colors={colors} label="RG" icon="browsers-outline" value={form.rg} onChangeText={(v:any) => updateForm('rg', v)} placeholder="00.000..." />
                            </View>
                        </>
                    ) : (
                        <>
                            <CustomInput colors={colors} label="Razão Social *" icon="business-outline" value={form.company_name} onChangeText={(v:any) => updateForm('company_name', v)} placeholder="Empresa LTDA" />
                            <CustomInput colors={colors} label="CNPJ" icon="copy-outline" value={form.cnpj} onChangeText={(v:any) => updateForm('cnpj', v)} placeholder="00.000.000/0001-00" keyboardType="numeric" />
                        </>
                    )}
                </View>

                <View style={[styles.card, { backgroundColor: colors.card }]}>
                    <View style={[styles.cardHeader, { borderBottomColor: colors.border }]}>
                        <Ionicons name="call-outline" size={20} color={colors.primary} />
                        <Text style={[styles.cardTitle, { color: colors.text }]}>Contato</Text>
                    </View>
                    <CustomInput colors={colors} label="E-mail Principal *" icon="mail-outline" value={form.email} onChangeText={(v:any) => updateForm('email', v)} placeholder="exemplo@email.com" keyboardType="email-address" />
                    <View style={styles.row}>
                        <CustomInput colors={colors} label="WhatsApp" icon="logo-whatsapp" value={form.whatsapp} onChangeText={(v:any) => updateForm('whatsapp', v)} placeholder="(11) 9..." keyboardType="phone-pad" />
                        <CustomInput colors={colors} label="Telefone" icon="call-outline" value={form.phone} onChangeText={(v:any) => updateForm('phone', v)} placeholder="(11) 4..." keyboardType="phone-pad" />
                    </View>
                </View>

                <View style={[styles.card, { backgroundColor: colors.card }]}>
                    <View style={[styles.cardHeader, { borderBottomColor: colors.border }]}>
                        <Ionicons name="map-outline" size={20} color={colors.primary} />
                        <Text style={[styles.cardTitle, { color: colors.text }]}>Endereço</Text>
                    </View>
                    <View style={styles.row}>
                        <CustomInput colors={colors} label="CEP" icon="pin-outline" value={form.cep} onChangeText={(v:any) => updateForm('cep', v)} placeholder="00000-000" keyboardType="numeric" flex={0.4} />
                        <CustomInput colors={colors} label="Cidade" icon="business-outline" value={form.cidade} onChangeText={(v:any) => updateForm('cidade', v)} placeholder="São Paulo" flex={0.6} />
                    </View>
                    <CustomInput colors={colors} label="Rua / Logradouro" icon="navigate-outline" value={form.rua} onChangeText={(v:any) => updateForm('rua', v)} placeholder="Av. Paulista" />
                    <View style={styles.row}>
                        <CustomInput colors={colors} label="Nº" icon="home-outline" value={form.numero} onChangeText={(v:any) => updateForm('numero', v)} placeholder="123" flex={0.3} />
                        <CustomInput colors={colors} label="Bairro" icon="trail-sign-outline" value={form.bairro} onChangeText={(v:any) => updateForm('bairro', v)} placeholder="Centro" flex={0.7} />
                    </View>
                </View>

            </ScrollView>

            <View style={[styles.footer, { backgroundColor: colors.card, borderTopColor: colors.border }]}>
                <TouchableOpacity activeOpacity={0.8} onPress={handleSubmit} disabled={loading}>
                    <LinearGradient colors={['#7367F0', '#CE9FFC']} start={{x:0, y:0}} end={{x:1, y:0}} style={styles.submitBtn}>
                        {loading ? <ActivityIndicator color="#fff" /> : (
                            <>
                                <Ionicons name="checkmark-circle-outline" size={22} color="#fff" />
                                <Text style={styles.submitBtnText}>Cadastrar Cliente</Text>
                            </>
                        )}
                    </LinearGradient>
                </TouchableOpacity>
            </View>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 15 },
    backBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
    headerTitle: { fontSize: 20, fontWeight: 'bold' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    segmentedContainer: { flexDirection: 'row', borderRadius: 12, padding: 4, marginBottom: 20 },
    segment: { flex: 1, paddingVertical: 10, alignItems: 'center', borderRadius: 10 },
    segmentText: { fontSize: 14, fontWeight: '600' },
    card: { borderRadius: 16, padding: 16, marginBottom: 16, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 15, borderBottomWidth: 1, paddingBottom: 10 },
    cardTitle: { fontSize: 15, fontWeight: 'bold', marginLeft: 8 },
    inputGroup: { marginBottom: 15 },
    label: { fontSize: 12, fontWeight: '700', marginBottom: 6, marginLeft: 4, textTransform: 'uppercase' },
    inputWrapper: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderRadius: 12, height: 52 },
    inputIcon: { paddingHorizontal: 12 },
    input: { flex: 1, fontSize: 15, paddingRight: 12 },
    row: { flexDirection: 'row', gap: 12 },
    footer: { padding: 20, paddingBottom: Platform.OS === 'ios' ? 40 : 20, borderTopWidth: 1 },
    submitBtn: { height: 56, borderRadius: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
    submitBtnText: { color: '#fff', fontSize: 17, fontWeight: 'bold' }
});