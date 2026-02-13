import React, { useEffect, useState } from 'react';
import {
    View,
    Text,
    StyleSheet,
    ScrollView,
    TouchableOpacity,
    ActivityIndicator,
    Alert,
    StatusBar
} from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../services/api';
import { LinearGradient } from 'expo-linear-gradient';

const statusTranslations: { [key: string]: string } = {
    'pending': 'Pendente',
    'running': 'Em Execução',
    'finalized': 'Finalizada',
    'canceled': 'Cancelada',
};

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'pending': return '#FF9F43';
        case 'running': return '#00CFE8';
        case 'finalized': return '#28C76F';
        case 'canceled': return '#EA5455';
        default: return '#7367F0';
    }
};

export default function OSDetailScreen() {
    const { id } = useLocalSearchParams();
    const router = useRouter();
    const [os, setOs] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [updating, setUpdating] = useState(false);

    const fetchOSDetails = async () => {
        try {
            const response = await api.get(`/ordens-servico/${id}`);
            setOs(response.data);
        } catch (error) {
            console.error("Error fetching OS:", error);
            Alert.alert('Erro', 'Não foi possível carregar os detalhes da ordem.');
            router.back();
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchOSDetails();
    }, [id]);

    const handleUpdateStatus = async (newStatus: string) => {
        try {
            setUpdating(true);
            await api.patch(`/ordens-servico/${id}/status`, { status: newStatus });
            setOs({ ...os, status: newStatus });
            Alert.alert('Sucesso', `Status atualizado para ${statusTranslations[newStatus]}`);
        } catch (error) {
            Alert.alert('Erro', 'Falha ao atualizar status.');
        } finally {
            setUpdating(false);
        }
    };

    if (loading) {
        return (
            <View style={styles.loadingContainer}>
                <ActivityIndicator size="large" color="#7367F0" />
            </View>
        );
    }

    return (
        <View style={styles.container}>
            <StatusBar barStyle="light-content" />

            {/* Header */}
            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.header}>
                <View style={styles.headerTop}>
                    <TouchableOpacity onPress={() => router.back()}>
                        <Ionicons name="arrow-back" size={24} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.headerTitle}>Ordem de Serviço #{os.id}</Text>
                    <View style={{ width: 24 }} />
                </View>

                <View style={styles.statusContainer}>
                    <View style={[styles.statusBadge, { backgroundColor: getStatusColor(os.status) }]}>
                        <Text style={styles.statusText}>{statusTranslations[os.status] || os.status}</Text>
                    </View>
                </View>
            </LinearGradient>

            <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
                {/* Client Info */}
                <View style={styles.section}>
                    <View style={styles.sectionHeader}>
                        <Ionicons name="person" size={20} color="#7367F0" />
                        <Text style={styles.sectionTitle}>Cliente</Text>
                    </View>
                    <Text style={styles.infoText}>{os.client?.name || os.client?.company_name}</Text>
                    <Text style={styles.subInfoText}>{os.client?.phone || 'Sem telefone'}</Text>
                </View>

                {/* Vehicle Info */}
                <View style={styles.section}>
                    <View style={styles.sectionHeader}>
                        <Ionicons name="car" size={20} color="#7367F0" />
                        <Text style={styles.sectionTitle}>Veículo</Text>
                    </View>
                    <Text style={styles.infoText}>{os.veiculo?.marca} {os.veiculo?.modelo}</Text>
                    <View style={styles.plateBadge}>
                        <Text style={styles.plateText}>{os.veiculo?.placa}</Text>
                    </View>
                    <Text style={styles.subInfoText}>Cor: {os.veiculo?.cor} | KM: {os.km_entry}</Text>
                </View>

                {/* Description / Problem */}
                <View style={styles.section}>
                    <View style={styles.sectionHeader}>
                        <Ionicons name="alert-circle" size={20} color="#7367F0" />
                        <Text style={styles.sectionTitle}>Relato do Problema</Text>
                    </View>
                    <Text style={styles.descriptionText}>{os.description || 'Nenhuma descrição fornecida.'}</Text>
                </View>

                {/* Action Buttons for Mechanic */}
                <View style={styles.actionContainer}>
                    <Text style={styles.actionTitle}>Gerenciar Trabalho</Text>
                    <View style={styles.buttonRow}>
                        {os.status === 'pending' && (
                            <TouchableOpacity
                                style={[styles.actionButton, { backgroundColor: '#00CFE8' }]}
                                onPress={() => handleUpdateStatus('running')}
                                disabled={updating}
                            >
                                <Ionicons name="play" size={18} color="#fff" />
                                <Text style={styles.buttonText}>Iniciar Serviço</Text>
                            </TouchableOpacity>
                        )}

                        {os.status === 'running' && (
                            <TouchableOpacity
                                style={[styles.actionButton, { backgroundColor: '#28C76F' }]}
                                onPress={() => handleUpdateStatus('finalized')}
                                disabled={updating}
                            >
                                <Ionicons name="checkmark-done" size={18} color="#fff" />
                                <Text style={styles.buttonText}>Finalizar Serviço</Text>
                            </TouchableOpacity>
                        )}

                        <TouchableOpacity
                            style={[styles.actionButton, { backgroundColor: '#7367F0' }]}
                            onPress={() => router.push({ pathname: '/os/checklist', params: { osId: os.id } })}
                        >
                            <Ionicons name="list" size={18} color="#fff" />
                            <Text style={styles.buttonText}>Checklist / Fotos</Text>
                        </TouchableOpacity>
                    </View>
                </View>

                {/* Items / Services */}
                <View style={styles.section}>
                    <View style={styles.sectionHeader}>
                        <Ionicons name="build" size={20} color="#7367F0" />
                        <Text style={styles.sectionTitle}>Serviços & Peças</Text>
                    </View>
                    {os.items?.map((item: any) => (
                        <View key={`item-${item.id}`} style={styles.itemRow}>
                            <Text style={styles.itemName}>{item.name}</Text>
                            <Text style={styles.itemQty}>x{item.quantity}</Text>
                        </View>
                    ))}
                    {os.parts?.map((part: any) => (
                        <View key={`part-${part.id}`} style={styles.itemRow}>
                            <Text style={styles.itemName}>{part.name}</Text>
                            <Text style={styles.itemQty}>x{part.quantity}</Text>
                        </View>
                    ))}
                    {(!os.items?.length && !os.parts?.length) && (
                        <Text style={styles.subInfoText}>Nenhum item adicionado.</Text>
                    )}
                </View>

                <View style={{ height: 40 }} />
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#f8f9fa',
    },
    loadingContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
    header: {
        paddingTop: 60,
        paddingBottom: 25,
        paddingHorizontal: 20,
        borderBottomLeftRadius: 30,
        borderBottomRightRadius: 30,
    },
    headerTop: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    headerTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#fff',
    },
    statusContainer: {
        alignItems: 'center',
        marginTop: 15,
    },
    statusBadge: {
        paddingHorizontal: 15,
        paddingVertical: 5,
        borderRadius: 20,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.3)',
    },
    statusText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 12,
        textTransform: 'uppercase',
    },
    content: {
        flex: 1,
        padding: 20,
    },
    section: {
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 16,
        marginBottom: 20,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 8,
        elevation: 3,
    },
    sectionHeader: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 12,
    },
    sectionTitle: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#333',
        marginLeft: 8,
    },
    infoText: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#333',
    },
    subInfoText: {
        fontSize: 14,
        color: '#666',
        marginTop: 4,
    },
    plateBadge: {
        backgroundColor: '#f1f1f1',
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 6,
        alignSelf: 'flex-start',
        marginVertical: 8,
        borderWidth: 1,
        borderColor: '#ddd',
    },
    plateText: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#333',
        letterSpacing: 1,
    },
    descriptionText: {
        fontSize: 14,
        color: '#666',
        lineHeight: 20,
    },
    actionContainer: {
        marginBottom: 25,
    },
    actionTitle: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#333',
        marginBottom: 15,
    },
    buttonRow: {
        flexDirection: 'row',
        gap: 10,
    },
    actionButton: {
        flex: 1,
        height: 50,
        borderRadius: 12,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingHorizontal: 10,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 4,
        elevation: 3,
    },
    buttonText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 13,
        marginLeft: 6,
    },
    itemRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        paddingVertical: 8,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f1f1',
    },
    itemName: {
        fontSize: 14,
        color: '#333',
        flex: 1,
    },
    itemQty: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#7367F0',
        marginLeft: 10,
    }
});
