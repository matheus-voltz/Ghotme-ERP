import React, { useEffect, useState } from 'react';
import { View, Text, TextInput, StyleSheet, ScrollView, TouchableOpacity, Alert, ActivityIndicator, KeyboardAvoidingView, Platform } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { Picker } from '@react-native-picker/picker'; // You might need to install this or use a custom implementation
import api from '../../services/api';
import { useTheme } from '../../context/ThemeContext';

// IMPORTANT: Install @react-native-picker/picker if not already installed
// expo install @react-native-picker/picker

export default function CreateOrderScreen() {
    const router = useRouter();
    const { colors } = useTheme();

    const [loading, setLoading] = useState(false);
    const [clients, setClients] = useState<any[]>([]);
    const [vehicles, setVehicles] = useState<any[]>([]);

    // Form State
    const [clientId, setClientId] = useState('');
    const [vehicleId, setVehicleId] = useState('');
    const [status, setStatus] = useState('pending');
    const [kmEntry, setKmEntry] = useState('');
    const [description, setDescription] = useState('');

    useEffect(() => {
        fetchClients();
    }, []);

    useEffect(() => {
        if (clientId) {
            fetchVehicles(clientId);
        } else {
            setVehicles([]);
            setVehicleId('');
        }
    }, [clientId]);

    const fetchClients = async () => {
        try {
            const response = await api.get('/clients');
            setClients(response.data);
        } catch (error) {
            console.error("Error fetching clients:", error);
            Alert.alert("Erro", "Não foi possível carregar os clientes.");
        }
    };

    const fetchVehicles = async (cid: string) => {
        try {
            const response = await api.get(`/clients/${cid}/vehicles`);
            setVehicles(response.data);
        } catch (error) {
            console.error("Error fetching vehicles:", error);
        }
    };

    const handleSubmit = async () => {
        if (!clientId || !vehicleId || !status) {
            Alert.alert("Atenção", "Preencha os campos obrigatórios (Cliente, Veículo, Status).");
            return;
        }

        setLoading(true);
        try {
            const payload = {
                client_id: clientId,
                veiculo_id: vehicleId,
                status: status,
                km_entry: kmEntry,
                description: description
            };

            await api.post('/os', payload);

            Alert.alert("Sucesso", "Ordem de Serviço criada com sucesso!", [
                { text: "OK", onPress: () => router.back() }
            ]);
        } catch (error: any) {
            console.error("Error creating OS:", error);
            Alert.alert("Erro", error.response?.data?.message || "Erro ao criar OS.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <KeyboardAvoidingView
            behavior={Platform.OS === "ios" ? "padding" : "height"}
            style={[styles.container, { backgroundColor: colors.background }]}
        >
            <View style={[styles.header, { backgroundColor: colors.card, borderBottomColor: colors.border }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
                    <Ionicons name="arrow-back" size={24} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>Nova Ordem de Serviço</Text>
                <View style={{ width: 24 }} />
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

                {/* Section: Informações Básicas */}
                <Text style={[styles.sectionTitle, { color: colors.primary }]}>Informações Básicas</Text>

                <View style={[styles.card, { backgroundColor: colors.card, shadowColor: colors.text }]}>

                    {/* Cliente */}
                    <Text style={[styles.label, { color: colors.subText }]}>Cliente *</Text>
                    <View style={[styles.pickerContainer, { borderColor: colors.border, backgroundColor: colors.background }]}>
                        <Picker
                            selectedValue={clientId}
                            onValueChange={(itemValue) => setClientId(itemValue)}
                            style={[styles.picker, { color: colors.text }]}
                            dropdownIconColor={colors.text}
                        >
                            <Picker.Item label="Selecione o Cliente" value="" color={colors.subText} />
                            {clients.map((client) => (
                                <Picker.Item
                                    key={client.id}
                                    label={client.name || client.company_name}
                                    value={client.id}
                                    color={colors.text}
                                />
                            ))}
                        </Picker>
                    </View>

                    {/* Veículo */}
                    <Text style={[styles.label, { color: colors.subText }]}>Veículo *</Text>
                    <View style={[styles.pickerContainer, { borderColor: colors.border, backgroundColor: colors.background }]}>
                        <Picker
                            selectedValue={vehicleId}
                            onValueChange={(itemValue) => setVehicleId(itemValue)}
                            enabled={clients.length > 0 && clientId !== ''}
                            style={[styles.picker, { color: colors.text }]}
                            dropdownIconColor={colors.text}
                        >
                            <Picker.Item label={clientId ? "Selecione o Veículo" : "Selecione o Cliente Primeiro"} value="" color={colors.subText} />
                            {vehicles.map((vehicle) => (
                                <Picker.Item
                                    key={vehicle.id}
                                    label={`${vehicle.modelo} - ${vehicle.placa}`}
                                    value={vehicle.id}
                                    color={colors.text}
                                />
                            ))}
                        </Picker>
                    </View>

                    {/* Status Inicial */}
                    <Text style={[styles.label, { color: colors.subText }]}>Status Inicial *</Text>
                    <View style={[styles.pickerContainer, { borderColor: colors.border, backgroundColor: colors.background }]}>
                        <Picker
                            selectedValue={status}
                            onValueChange={(itemValue) => setStatus(itemValue)}
                            style={[styles.picker, { color: colors.text }]}
                            dropdownIconColor={colors.text}
                        >
                            <Picker.Item label="Aguardando Início" value="pending" color={colors.text} />
                            <Picker.Item label="Em Execução" value="running" color={colors.text} />
                        </Picker>
                    </View>

                    {/* KM Entrada */}
                    <Text style={[styles.label, { color: colors.subText }]}>KM na Entrada</Text>
                    <TextInput
                        style={[styles.input, { borderColor: colors.border, backgroundColor: colors.background, color: colors.text }]}
                        placeholder="0"
                        placeholderTextColor={colors.subText}
                        keyboardType="numeric"
                        value={kmEntry}
                        onChangeText={setKmEntry}
                    />

                    {/* Descrição */}
                    <Text style={[styles.label, { color: colors.subText }]}>Relato do Cliente / Problema</Text>
                    <TextInput
                        style={[styles.textArea, { borderColor: colors.border, backgroundColor: colors.background, color: colors.text }]}
                        placeholder="Descreva o problema relatado..."
                        placeholderTextColor={colors.subText}
                        multiline
                        numberOfLines={4}
                        value={description}
                        onChangeText={setDescription}
                    />
                </View>

                {/* Section: Serviços e Peças (Placeholder) */}
                <View style={[styles.infoBox, { backgroundColor: '#eef2ff' }]}>
                    <Ionicons name="information-circle-outline" size={20} color="#7367F0" />
                    <Text style={styles.infoText}>
                        A adição de serviços e peças será feita na tela de detalhes da OS após a criação.
                    </Text>
                </View>

                {/* Submit Button */}
                <TouchableOpacity
                    style={[styles.submitButton, { backgroundColor: colors.primary, opacity: loading ? 0.7 : 1 }]}
                    onPress={handleSubmit}
                    disabled={loading}
                >
                    {loading ? (
                        <ActivityIndicator color="#fff" />
                    ) : (
                        <Text style={styles.submitButtonText}>Criar Ordem de Serviço</Text>
                    )}
                </TouchableOpacity>

            </ScrollView>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
    },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingTop: 50,
        paddingBottom: 15,
        paddingHorizontal: 20,
        borderBottomWidth: 1,
    },
    backButton: {
        padding: 5,
    },
    headerTitle: {
        fontSize: 18,
        fontWeight: 'bold',
    },
    scrollContent: {
        padding: 20,
        paddingBottom: 50,
    },
    sectionTitle: {
        fontSize: 16,
        fontWeight: '600',
        marginBottom: 10,
        marginTop: 5,
        textTransform: 'uppercase',
        letterSpacing: 1,
    },
    card: {
        borderRadius: 12,
        padding: 20,
        marginBottom: 20,
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 5,
        elevation: 2,
    },
    label: {
        fontSize: 14,
        fontWeight: '500',
        marginBottom: 8,
        marginTop: 12,
    },
    input: {
        borderWidth: 1,
        borderRadius: 8,
        padding: 12,
        fontSize: 16,
    },
    textArea: {
        borderWidth: 1,
        borderRadius: 8,
        padding: 12,
        fontSize: 16,
        textAlignVertical: 'top',
        minHeight: 100,
    },
    pickerContainer: {
        borderWidth: 1,
        borderRadius: 8,
        overflow: 'hidden',
        marginBottom: 5,
    },
    picker: {
        // height: 50, // On Android picker height is fixed usually
    },
    infoBox: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 15,
        borderRadius: 8,
        marginBottom: 20,
        gap: 10,
    },
    infoText: {
        color: '#7367F0',
        flex: 1,
        fontSize: 13,
    },
    submitButton: {
        paddingVertical: 16,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#7367F0',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 8,
        elevation: 4,
        marginBottom: 30,
    },
    submitButtonText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: 'bold',
    },
});
