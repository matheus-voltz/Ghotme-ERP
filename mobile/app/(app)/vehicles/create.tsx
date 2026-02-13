import React, { useState, useEffect } from 'react';
import { View, Text, TextInput, StyleSheet, ScrollView, TouchableOpacity, Alert, ActivityIndicator, KeyboardAvoidingView, Platform, FlatList } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';

// Componente CustomInput movido para fora para evitar perda de foco
const CustomInput = ({ label, icon, value, onChangeText, placeholder, keyboardType = 'default', flex = 1, autoCapitalize = 'none' }: any) => (
    <View style={[styles.inputGroup, { flex }]}>
        <Text style={styles.label}>{label}</Text>
        <View style={styles.inputWrapper}>
            <Ionicons name={icon} size={18} color="#7367F0" style={styles.inputIcon} />
            <TextInput
                style={styles.input}
                value={value} onChangeText={onChangeText}
                placeholder={placeholder} placeholderTextColor="#9CA3AF"
                keyboardType={keyboardType} autoCapitalize={autoCapitalize}
            />
        </View>
    </View>
);

export default function CreateVehicleScreen() {
    const router = useRouter();
    const { colors } = useTheme();

    const [loading, setLoading] = useState(false);
    const [lookupLoading, setLookupLoading] = useState(false);
    const [clients, setClients] = useState<any[]>([]);
    
    const [clienteId, setClienteId] = useState('');
    const [placa, setPlaca] = useState('');
    const [marca, setMarca] = useState('');
    const [modelo, setModelo] = useState('');
    const [anoFabricacao, setAnoFabricacao] = useState('');
    const [cor, setCor] = useState('');
    const [renavam, setRenavam] = useState('');
    const [chassi, setChassi] = useState('');

    const [showClientModal, setShowClientModal] = useState(false);
    const [clientSearch, setClientSearch] = useState('');
    const [filteredClients, setFilteredClients] = useState<any[]>([]);

    useEffect(() => { fetchClients(); }, []);

    const fetchClients = async () => {
        try {
            const response = await api.get('/clients');
            setClients(response.data);
        } catch (error) { console.error(error); }
    };

    useEffect(() => {
        setFilteredClients(
            clients.filter(c =>
                (c.name && c.name.toLowerCase().includes(clientSearch.toLowerCase())) ||
                (c.company_name && c.company_name.toLowerCase().includes(clientSearch.toLowerCase()))
            )
        );
    }, [clientSearch, clients]);

    const handleLookupPlaca = async () => {
        if (placa.length < 7) { Alert.alert("Ops", "Digite os 7 caracteres da placa."); return; }
        setLookupLoading(true);
        try {
            const response = await api.get(`/api/vehicle-lookup/${placa}`);
            const data = response.data;
            setMarca(data.brand || data.marca || '');
            setModelo(data.model || data.modelo || '');
            setAnoFabricacao(data.year?.toString() || data.ano_fabricacao?.toString() || '');
            setCor(data.color || data.cor || '');
            Alert.alert("Sucesso! ✨", "Dados importados com sucesso.");
        } catch (error) {
            Alert.alert("Manual", "Placa não encontrada. Preencha os campos abaixo.");
        } finally { setLookupLoading(false); }
    };

    const handleSubmit = async () => {
        if (!clienteId || !placa || !marca || !modelo) {
            Alert.alert("Atenção", "Preencha o proprietário, placa, marca e modelo.");
            return;
        }
        setLoading(true);
        try {
            await api.post('/vehicles-list', { cliente_id: clienteId, placa: placa.toUpperCase(), marca, modelo, ano_fabricacao: anoFabricacao, cor, renavam, chassi });
            Alert.alert("Veículo Pronto! 🚗", "Cadastro realizado com sucesso.", [{ text: "OK", onPress: () => router.back() }]);
        } catch (error: any) {
            Alert.alert("Erro", "Não foi possível salvar o veículo.");
        } finally { setLoading(false); }
    };

    return (
        <KeyboardAvoidingView behavior={Platform.OS === "ios" ? "padding" : "height"} style={{ flex: 1, backgroundColor: '#F3F4F6' }}>
            <View style={styles.header}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color="#333" />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>Novo Veículo</Text>
                <View style={{ width: 40 }} />
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled">
                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <Ionicons name="person-outline" size={20} color="#7367F0" />
                        <Text style={styles.cardTitle}>Proprietário</Text>
                    </View>
                    <TouchableOpacity style={styles.clientSelector} onPress={() => setShowClientModal(true)}>
                        <Text style={[styles.clientSelectorText, { color: clienteId ? '#333' : '#9CA3AF' }]}>
                            {clienteId ? clients.find(c => c.id === clienteId)?.name : 'Selecionar Proprietário'}
                        </Text>
                        <Ionicons name="search" size={20} color="#7367F0" />
                    </TouchableOpacity>
                </View>

                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <Ionicons name="barcode-outline" size={20} color="#7367F0" />
                        <Text style={styles.cardTitle}>Identificação por Placa</Text>
                    </View>
                    <View style={styles.row}>
                        <View style={{ flex: 1 }}>
                            <Text style={styles.label}>Placa *</Text>
                            <View style={styles.inputWrapper}>
                                <Ionicons name="car-sport-outline" size={18} color="#7367F0" style={styles.inputIcon} />
                                <TextInput 
                                    style={[styles.input, { textTransform: 'uppercase' }]}
                                    value={placa} onChangeText={setPlaca}
                                    placeholder="ABC1234" maxLength={7}
                                />
                            </View>
                        </View>
                        <TouchableOpacity style={styles.lookupButton} onPress={handleLookupPlaca} disabled={lookupLoading}>
                            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.lookupGradient}>
                                {lookupLoading ? <ActivityIndicator color="#fff" /> : <Ionicons name="flash" size={22} color="#fff" />}
                            </LinearGradient>
                        </TouchableOpacity>
                    </View>
                </View>

                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <Ionicons name="settings-outline" size={20} color="#7367F0" />
                        <Text style={styles.cardTitle}>Dados Técnicos</Text>
                    </View>
                    <View style={styles.row}>
                        <CustomInput label="Marca *" icon="ribbon-outline" value={marca} onChangeText={setMarca} placeholder="Honda" />
                        <CustomInput label="Modelo *" icon="car-outline" value={modelo} onChangeText={setModelo} placeholder="Civic" />
                    </View>
                    <View style={styles.row}>
                        <CustomInput label="Ano" icon="calendar-outline" value={anoFabricacao} onChangeText={setAnoFabricacao} placeholder="2024" keyboardType="numeric" />
                        <CustomInput label="Cor" icon="color-palette-outline" value={cor} onChangeText={setCor} placeholder="Prata" />
                    </View>
                </View>

                <View style={styles.card}>
                    <View style={styles.cardHeader}>
                        <Ionicons name="document-outline" size={20} color="#7367F0" />
                        <Text style={styles.cardTitle}>Documentos</Text>
                    </View>
                    <CustomInput label="Renavam" icon="finger-print-outline" value={renavam} onChangeText={setRenavam} keyboardType="numeric" />
                    <CustomInput label="Chassi" icon="qr-code-outline" value={chassi} onChangeText={setChassi} autoCapitalize="characters" />
                </View>
            </ScrollView>

            <View style={styles.footer}>
                <TouchableOpacity activeOpacity={0.8} onPress={handleSubmit} disabled={loading}>
                    <LinearGradient colors={['#7367F0', '#CE9FFC']} start={{x:0, y:0}} end={{x:1, y:0}} style={styles.submitBtn}>
                        {loading ? <ActivityIndicator color="#fff" /> : (
                            <>
                                <Ionicons name="save-outline" size={22} color="#fff" />
                                <Text style={styles.submitBtnText}>Salvar Veículo</Text>
                            </>
                        )}
                    </LinearGradient>
                </TouchableOpacity>
            </View>

            {showClientModal && (
                <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'center', padding: 20, zIndex: 100 }]}>
                    <View style={{ backgroundColor: '#fff', borderRadius: 20, height: '80%', padding: 20 }}>
                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
                            <Text style={{ fontSize: 18, fontWeight: 'bold' }}>Proprietário</Text>
                            <TouchableOpacity onPress={() => setShowClientModal(false)}><Ionicons name="close-circle" size={28} color="#999" /></TouchableOpacity>
                        </View>
                        <TextInput
                            style={{ backgroundColor: '#F3F4F6', padding: 15, borderRadius: 12, marginBottom: 15 }}
                            placeholder="Buscar cliente..."
                            value={clientSearch} onChangeText={setClientSearch}
                        />
                        <FlatList
                            data={filteredClients}
                            keyExtractor={item => item.id.toString()}
                            renderItem={({ item }) => (
                                <TouchableOpacity style={{ paddingVertical: 15, borderBottomWidth: 1, borderBottomColor: '#F3F4F6' }} onPress={() => { setClienteId(item.id); setShowClientModal(false); }}>
                                    <Text style={{ fontSize: 16, fontWeight: '600' }}>{item.name || item.company_name}</Text>
                                </TouchableOpacity>
                            )}
                        />
                    </View>
                </View>
            )}
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 15, backgroundColor: '#fff' },
    backBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#1F2937' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    card: { backgroundColor: '#fff', borderRadius: 16, padding: 16, marginBottom: 16, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 15, borderBottomWidth: 1, borderBottomColor: '#F3F4F6', paddingBottom: 10 },
    cardTitle: { fontSize: 15, fontWeight: 'bold', color: '#374151', marginLeft: 8 },
    clientSelector: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F9FAFB', padding: 15, borderRadius: 12, borderWidth: 1, borderColor: '#E5E7EB' },
    clientSelectorText: { flex: 1, fontSize: 16, marginLeft: 10 },
    inputGroup: { marginBottom: 15 },
    label: { fontSize: 12, fontWeight: '700', marginBottom: 6, marginLeft: 4, textTransform: 'uppercase', color: '#6B7280' },
    inputWrapper: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F9FAFB', borderWidth: 1, borderColor: '#E5E7EB', borderRadius: 12, height: 52 },
    inputIcon: { paddingHorizontal: 12 },
    input: { flex: 1, fontSize: 15, paddingRight: 12, color: '#1F2937' },
    row: { flexDirection: 'row', gap: 12, alignItems: 'flex-end' },
    lookupButton: { width: 52, height: 52, borderRadius: 12, overflow: 'hidden' },
    lookupGradient: { flex: 1, alignItems: 'center', justifyContent: 'center' },
    helperText: { fontSize: 11, color: '#9CA3AF', marginTop: 8, marginLeft: 4 },
    footer: { padding: 20, paddingBottom: Platform.OS === 'ios' ? 40 : 20, backgroundColor: '#fff', borderTopWidth: 1, borderTopColor: '#E5E7EB' },
    submitBtn: { height: 56, borderRadius: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
    submitBtnText: { color: '#fff', fontSize: 17, fontWeight: 'bold' }
});
