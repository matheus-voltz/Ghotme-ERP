import React, { useState } from 'react';
import { View, Text, TextInput, StyleSheet, ScrollView, TouchableOpacity, Alert, ActivityIndicator, KeyboardAvoidingView, Platform } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../services/api';
import { useTheme } from '../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';

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

    const CustomInput = ({ label, icon, value, onChangeText, placeholder, keyboardType = 'default', flex = 1 }: any) => (
        <View style={[styles.inputGroup, { flex }]}>
            <Text style={[styles.label, { color: colors.subText }]}>{label}</Text>
            <View style={[styles.inputWrapper, { borderColor: colors.border, backgroundColor: '#F9FAFB' }]}>
                <Ionicons name={icon} size={18} color={colors.primary} style={styles.inputIcon} />
                <TextInput
                    style={[styles.input, { color: colors.text }]}
                    value={value}
                    onChangeText={onChangeText}
                    placeholder={placeholder}
                    placeholderTextColor="#9CA3AF"
                    keyboardType={keyboardType}
                />
            </View>
        </View>
    );

    return (
        <KeyboardAvoidingView behavior={Platform.OS === "ios" ? "padding" : "height"} style={{ flex: 1, backgroundColor: '#F3F4F6' }}>
            <View style={styles.header}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color="#333" />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>Novo Cliente</Text>
                <View style={{ width: 40 }} />
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
                
                {/* Segmented Control para Tipo */}
                <View style={styles.segmentedContainer}>
                    <TouchableOpacity 
                        style={[styles.segment, type === 'PF' && styles.segmentActive]} 
                        onPress={() => setType('PF')}
                    >
                        <Text style={[styles.segmentText, type === 'PF' && styles.segmentTextActive]}>Pessoa Física</Text>
                    </TouchableOpacity>
                    <TouchableOpacity 
                        style={[styles.segment, type === 'PJ' && styles.segmentActive]} 
                        onPress={() => setType('PJ')}
                    >
                        <Text style={[styles.segmentText, type === 'PJ' && styles.segmentTextActive]}>Pessoa Jurídica</Text>
                    </TouchableOpacity>
                </View>

                {/* Seção: Identificação */}
                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <Ionicons name="id-card-outline" size={20} color={colors.primary} />
                        <Text style={styles.cardTitle}>Identificação</Text>
                    </View>
                    
                    {type === 'PF' ? (
                        <>
                            <CustomInput label="Nome Completo *" icon="person-outline" value={form.name} onChangeText={(v:any) => updateForm('name', v)} placeholder="João Silva" />
                            <View style={styles.row}>
                                <CustomInput label="CPF" icon="document-text-outline" value={form.cpf} onChangeText={(v:any) => updateForm('cpf', v)} placeholder="000.000..." keyboardType="numeric" />
                                <CustomInput label="RG" icon="browsers-outline" value={form.rg} onChangeText={(v:any) => updateForm('rg', v)} placeholder="00.000..." />
                            </View>
                        </>
                    ) : (
                        <>
                            <CustomInput label="Razão Social *" icon="business-outline" value={form.company_name} onChangeText={(v:any) => updateForm('company_name', v)} placeholder="Empresa LTDA" />
                            <CustomInput label="CNPJ" icon="copy-outline" value={form.cnpj} onChangeText={(v:any) => updateForm('cnpj', v)} placeholder="00.000.000/0001-00" keyboardType="numeric" />
                        </>
                    )}
                </View>

                {/* Seção: Contato */}
                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <Ionicons name="call-outline" size={20} color={colors.primary} />
                        <Text style={styles.cardTitle}>Contato</Text>
                    </View>
                    <CustomInput label="E-mail Principal *" icon="mail-outline" value={form.email} onChangeText={(v:any) => updateForm('email', v)} placeholder="exemplo@email.com" keyboardType="email-address" />
                    <View style={styles.row}>
                        <CustomInput label="WhatsApp" icon="logo-whatsapp" value={form.whatsapp} onChangeText={(v:any) => updateForm('whatsapp', v)} placeholder="(11) 9..." keyboardType="phone-pad" />
                        <CustomInput label="Telefone" icon="call-outline" value={form.phone} onChangeText={(v:any) => updateForm('phone', v)} placeholder="(11) 4..." keyboardType="phone-pad" />
                    </View>
                </View>

                {/* Seção: Endereço */}
                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <Ionicons name="map-outline" size={20} color={colors.primary} />
                        <Text style={styles.cardTitle}>Endereço</Text>
                    </View>
                    <View style={styles.row}>
                        <CustomInput label="CEP" icon="pin-outline" value={form.cep} onChangeText={(v:any) => updateForm('cep', v)} placeholder="00000-000" keyboardType="numeric" flex={0.4} />
                        <CustomInput label="Cidade" icon="business-outline" value={form.cidade} onChangeText={(v:any) => updateForm('cidade', v)} placeholder="São Paulo" flex={0.6} />
                    </View>
                    <CustomInput label="Rua / Logradouro" icon="navigate-outline" value={form.rua} onChangeText={(v:any) => updateForm('rua', v)} placeholder="Av. Paulista" />
                    <View style={styles.row}>
                        <CustomInput label="Nº" icon="home-outline" value={form.numero} onChangeText={(v:any) => updateForm('numero', v)} placeholder="123" flex={0.3} />
                        <CustomInput label="Bairro" icon="trail-sign-outline" value={form.bairro} onChangeText={(v:any) => updateForm('bairro', v)} placeholder="Centro" flex={0.7} />
                    </View>
                </View>

            </ScrollView>

            <View style={styles.footer}>
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
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 15, backgroundColor: '#fff' },
    backBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#1F2937' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    segmentedContainer: { flexDirection: 'row', backgroundColor: '#E5E7EB', borderRadius: 12, padding: 4, marginBottom: 20 },
    segment: { flex: 1, paddingVertical: 10, alignItems: 'center', borderRadius: 10 },
    segmentActive: { backgroundColor: '#fff', elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4 },
    segmentText: { fontSize: 14, fontWeight: '600', color: '#6B7280' },
    segmentTextActive: { color: '#7367F0' },
    card: { backgroundColor: '#fff', borderRadius: 16, padding: 16, marginBottom: 16, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 15, borderBottomWidth: 1, borderBottomColor: '#F3F4F6', paddingBottom: 10 },
    cardTitle: { fontSize: 15, fontWeight: 'bold', color: '#374151', marginLeft: 8 },
    inputGroup: { marginBottom: 15 },
    label: { fontSize: 12, fontWeight: '700', marginBottom: 6, marginLeft: 4, textTransform: 'uppercase' },
    inputWrapper: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderRadius: 12, height: 52 },
    inputIcon: { paddingHorizontal: 12 },
    input: { flex: 1, fontSize: 15, paddingRight: 12 },
    row: { flexDirection: 'row', gap: 12 },
    footer: { padding: 20, paddingBottom: Platform.OS === 'ios' ? 40 : 20, backgroundColor: '#fff', borderTopWidth: 1, borderTopColor: '#E5E7EB' },
    submitBtn: { height: 56, borderRadius: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
    submitBtnText: { color: '#fff', fontSize: 17, fontWeight: 'bold' }
});