import React, { useEffect, useState } from 'react';
import { View, Text, TextInput, StyleSheet, ScrollView, TouchableOpacity, Alert, ActivityIndicator, KeyboardAvoidingView, Platform, FlatList } from 'react-native';
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
    const [vehiclesLoading, setVehiclesLoading] = useState(false); // Novo estado
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
        setVehiclesLoading(true);
        try {
            console.log("Buscando veículos para cliente ID:", cid);
            const response = await api.get(`/clients/${cid}/vehicles`);
            console.log("Resposta do servidor (veículos):", response.data);

            if (Array.isArray(response.data)) {
                setVehicles(response.data);
                // Se só houver um veículo, já seleciona ele automaticamente
                if (response.data.length === 1) {
                    setVehicleId(response.data[0].id.toString());
                }
            } else {
                setVehicles([]);
            }
        } catch (error) {
            console.error("Erro ao buscar veículos:", error);
            setVehicles([]);
        } finally {
            setVehiclesLoading(false);
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

            const response = await api.post('/os', payload);

            Alert.alert("Sucesso", "Ordem de Serviço criada com sucesso!", [
                {
                    text: "Ir para Checklist",
                    onPress: () => router.push({
                        pathname: '/os/checklist',
                        params: { osId: response.data.id }
                    })
                },
                { text: "Depois", onPress: () => router.back() }
            ]);
        } catch (error: any) {
            console.error("Error creating OS:", error);
            Alert.alert("Erro", error.response?.data?.message || "Erro ao criar OS.");
        } finally {
            setLoading(false);
        }
    };

    const [showClientModal, setShowClientModal] = useState(false);
    const [clientSearch, setClientSearch] = useState('');
    const [filteredClients, setFilteredClients] = useState<any[]>([]);

    useEffect(() => {
        setFilteredClients(
            clients.filter(c =>
                (c.name && c.name.toLowerCase().includes(clientSearch.toLowerCase())) ||
                (c.company_name && c.company_name.toLowerCase().includes(clientSearch.toLowerCase()))
            )
        );
    }, [clientSearch, clients]);

    const handleSelectClient = (client: any) => {
        setClientId(client.id);
        setShowClientModal(false);
        setClientSearch('');
    };

    const getSelectedClientName = () => {
        const client = clients.find(c => c.id === clientId);
        return client ? (client.name || client.company_name) : 'Selecione o Cliente';
    };

    return (
        <KeyboardAvoidingView
            behavior={Platform.OS === "ios" ? "padding" : "height"}
            style={[styles.container, { backgroundColor: colors.background }]}
        >
            <View style={[styles.header, { backgroundColor: colors.card }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
                    <Ionicons name="arrow-back" size={24} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>Nova Ordem de Serviço</Text>
                <View style={{ width: 32 }} />
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

                {/* Section: Informações Básicas */}
                <View style={styles.formSection}>
                    <View style={styles.sectionHeaderRow}>
                        <Ionicons name="document-text-outline" size={20} color={colors.primary} />
                        <Text style={[styles.sectionTitle, { color: colors.primary }]}>Informações Básicas</Text>
                    </View>

                    {/* Cliente (Searchable) */}
                    <View style={styles.inputGroup}>
                        <View style={styles.labelRow}>
                            <Ionicons name="person-outline" size={16} color={colors.subText} />
                            <Text style={[styles.label, { color: colors.subText }]}>Cliente *</Text>
                        </View>

                        <TouchableOpacity
                            style={[styles.pickerWrapper, { borderColor: colors.border, backgroundColor: colors.background, paddingHorizontal: 15 }]}
                            onPress={() => setShowClientModal(true)}
                        >
                            <Text style={{ fontSize: 16, color: clientId ? colors.text : colors.subText }}>
                                {getSelectedClientName()}
                            </Text>
                            <Ionicons name="chevron-down" size={20} color={colors.subText} style={{ position: 'absolute', right: 15 }} />
                        </TouchableOpacity>
                    </View>

                    {/* Veículo */}
                    <View style={styles.inputGroup}>
                        <View style={styles.labelRow}>
                            <Ionicons name="car-sport-outline" size={16} color={colors.subText} />
                            <Text style={[styles.label, { color: colors.subText }]}>Veículo *</Text>
                        </View>
                        <View style={[styles.pickerWrapper, { borderColor: colors.border, backgroundColor: colors.background, opacity: (clientId && !vehiclesLoading) ? 1 : 0.6 }]}>
                            <Picker
                                selectedValue={vehicleId ? vehicleId.toString() : ''}
                                onValueChange={(itemValue) => setVehicleId(itemValue)}
                                enabled={clientId !== '' && !vehiclesLoading}
                                dropdownIconColor={colors.text}
                            >
                                <Picker.Item 
                                    label={vehiclesLoading ? "Carregando veículos..." : (clientId ? "Selecione o Veículo" : "Selecione o Cliente Primeiro")} 
                                    value="" 
                                    color={colors.subText} 
                                />
                                {vehicles.map((v) => (
                                    <Picker.Item
                                        key={v.id.toString()}
                                        label={`${v.marca || ''} ${v.modelo} - ${v.placa}`}
                                        value={v.id.toString()}
                                        color={colors.text}
                                    />
                                ))}
                            </Picker>
                        </View>
                    </View>

                    {/* Status Inicial (Chips) */}
                    <View style={styles.inputGroup}>
                        <View style={styles.labelRow}>
                            <Ionicons name="flag-outline" size={16} color={colors.subText} />
                            <Text style={[styles.label, { color: colors.subText }]}>Status Inicial *</Text>
                        </View>
                        <View style={styles.statusContainer}>
                            <TouchableOpacity
                                style={[
                                    styles.statusChip,
                                    {
                                        backgroundColor: status === 'pending' ? colors.primary + '20' : colors.card,
                                        borderColor: status === 'pending' ? colors.primary : colors.border
                                    }
                                ]}
                                onPress={() => setStatus('pending')}
                            >
                                <Text style={[styles.statusText, { color: status === 'pending' ? colors.primary : colors.subText }]}>Aguardando</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={[
                                    styles.statusChip,
                                    {
                                        backgroundColor: status === 'running' ? '#00CFE820' : colors.card,
                                        borderColor: status === 'running' ? '#00CFE8' : colors.border
                                    }
                                ]}
                                onPress={() => setStatus('running')}
                            >
                                <Text style={[styles.statusText, { color: status === 'running' ? '#00CFE8' : colors.subText }]}>Em Execução</Text>
                            </TouchableOpacity>
                        </View>
                    </View>

                    {/* KM Entrada */}
                    <View style={styles.inputGroup}>
                        <View style={styles.labelRow}>
                            <Ionicons name="speedometer-outline" size={16} color={colors.subText} />
                            <Text style={[styles.label, { color: colors.subText }]}>KM na Entrada</Text>
                        </View>
                        <View style={[styles.inputContainer, { borderColor: colors.border, backgroundColor: colors.background }]}>
                            <TextInput
                                style={[styles.input, { color: colors.text }]}
                                placeholder="0"
                                placeholderTextColor={colors.subText}
                                keyboardType="numeric"
                                value={kmEntry}
                                onChangeText={setKmEntry}
                            />
                            <Text style={[styles.inputSuffix, { color: colors.subText }]}>km</Text>
                        </View>
                    </View>

                    {/* Descrição */}
                    <View style={styles.inputGroup}>
                        <View style={styles.labelRow}>
                            <Ionicons name="create-outline" size={16} color={colors.subText} />
                            <Text style={[styles.label, { color: colors.subText }]}>Relato do Cliente / Problema</Text>
                        </View>
                        <View style={[styles.textAreaContainer, { borderColor: colors.border, backgroundColor: colors.background }]}>
                            <TextInput
                                style={[styles.textArea, { color: colors.text }]}
                                placeholder="Descreva o problema relatado..."
                                placeholderTextColor={colors.subText}
                                multiline
                                numberOfLines={4}
                                value={description}
                                onChangeText={setDescription}
                            />
                        </View>
                    </View>
                </View>

                {/* Info Box */}
                <View style={[styles.infoBox, { backgroundColor: colors.primary + '15' }]}>
                    <Ionicons name="information-circle-outline" size={24} color={colors.primary} />
                    <Text style={[styles.infoText, { color: colors.text }]}>
                        A adição de serviços e peças poderá ser feita na tela de detalhes da OS após a criação.
                    </Text>
                </View>

            </ScrollView>

            {/* Fixed Bottom Action Bar */}
            <View style={[styles.footer, { backgroundColor: colors.card, borderTopColor: colors.border }]}>
                <TouchableOpacity
                    style={[styles.submitButton, { backgroundColor: colors.primary, opacity: loading ? 0.7 : 1 }]}
                    onPress={handleSubmit}
                    disabled={loading}
                >
                    {loading ? (
                        <ActivityIndicator color="#fff" />
                    ) : (
                        <>
                            <Ionicons name="save-outline" size={20} color="#fff" />
                            <Text style={styles.submitButtonText}>Criar Ordem de Serviço</Text>
                        </>
                    )}
                </TouchableOpacity>
            </View>

            {/* Client Search Modal */}
            {showClientModal && (
                <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', padding: 20, zIndex: 100 }]}>
                    <View style={{ backgroundColor: colors.card, borderRadius: 16, height: '80%', padding: 20 }}>
                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
                            <Text style={{ fontSize: 18, fontWeight: 'bold', color: colors.text }}>Selecionar Cliente</Text>
                            <TouchableOpacity onPress={() => setShowClientModal(false)}>
                                <Ionicons name="close" size={24} color={colors.text} />
                            </TouchableOpacity>
                        </View>

                        <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: colors.background, borderRadius: 10, paddingHorizontal: 10, marginBottom: 15, borderWidth: 1, borderColor: colors.border }}>
                            <Ionicons name="search" size={20} color={colors.subText} />
                            <TextInput
                                style={{ flex: 1, padding: 12, fontSize: 16, color: colors.text }}
                                placeholder="Buscar cliente..."
                                placeholderTextColor={colors.subText}
                                value={clientSearch}
                                onChangeText={setClientSearch}
                                autoFocus
                            />
                        </View>

                        <FlatList
                            data={filteredClients}
                            keyExtractor={item => item.id.toString()}
                            showsVerticalScrollIndicator={false}
                            renderItem={({ item }) => (
                                <TouchableOpacity
                                    style={{ paddingVertical: 15, borderBottomWidth: 1, borderBottomColor: colors.border }}
                                    onPress={() => handleSelectClient(item)}
                                >
                                    <Text style={{ fontSize: 16, fontWeight: '600', color: colors.text }}>{item.name || item.company_name}</Text>
                                    <Text style={{ fontSize: 14, color: colors.subText }}>ID: {item.id}</Text>
                                </TouchableOpacity>
                            )}
                            ListEmptyComponent={
                                <Text style={{ textAlign: 'center', marginTop: 20, color: colors.subText }}>Nenhum cliente encontrado.</Text>
                            }
                        />
                    </View>
                </View>
            )}
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
        paddingTop: 50,
        paddingBottom: 20,
        paddingHorizontal: 20,
        backgroundColor: '#fff',
        borderBottomLeftRadius: 20,
        borderBottomRightRadius: 20,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 5,
        elevation: 5,
        zIndex: 10,
    },
    backButton: {
        padding: 8,
        marginRight: 10,
    },
    headerTitle: {
        fontSize: 20,
        fontWeight: 'bold',
    },
    scrollContent: {
        padding: 20,
        paddingBottom: 100,
    },
    formSection: {
        marginBottom: 20,
    },
    sectionHeaderRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 15,
    },
    sectionTitle: {
        fontSize: 16,
        fontWeight: '700',
        marginLeft: 8,
    },
    inputGroup: {
        marginBottom: 20,
    },
    labelRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 8,
    },
    label: {
        fontSize: 14,
        fontWeight: '600',
        marginLeft: 6,
    },
    pickerWrapper: {
        borderWidth: 1,
        borderRadius: 12,
        overflow: 'hidden',
        height: 55, // Fixed height for consistency
        justifyContent: 'center',
    },
    inputContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        borderWidth: 1,
        borderRadius: 12,
        paddingHorizontal: 15,
        height: 55,
    },
    input: {
        flex: 1,
        fontSize: 16,
    },
    inputSuffix: {
        fontSize: 14,
        fontWeight: '600',
        marginLeft: 5,
    },
    textAreaContainer: {
        borderWidth: 1,
        borderRadius: 12,
        padding: 15,
        minHeight: 120,
    },
    textArea: {
        fontSize: 16,
        textAlignVertical: 'top',
        height: '100%',
    },
    // Status Chips
    statusContainer: {
        flexDirection: 'row',
        gap: 10,
    },
    statusChip: {
        flex: 1,
        paddingVertical: 12,
        borderRadius: 12,
        borderWidth: 1,
        alignItems: 'center',
        justifyContent: 'center',
    },
    statusText: {
        fontWeight: '600',
        fontSize: 14,
    },
    // Info Box
    infoBox: {
        flexDirection: 'row',
        alignItems: 'flex-start',
        padding: 16,
        borderRadius: 12,
        marginBottom: 30,
    },
    infoText: {
        flex: 1,
        fontSize: 13,
        marginLeft: 10,
        lineHeight: 20,
    },
    footer: {
        padding: 20,
        paddingBottom: Platform.OS === 'ios' ? 40 : 20,
        borderTopWidth: 1,
        borderTopColor: '#f0f0f0',
    },
    // Submit Button
    submitButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 16,
        borderRadius: 12,
        shadowColor: '#7367F0',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 10,
        elevation: 6,
    },
    submitButtonText: {
        color: '#fff',
        fontSize: 18,
        fontWeight: 'bold',
        marginLeft: 8,
    },
});
