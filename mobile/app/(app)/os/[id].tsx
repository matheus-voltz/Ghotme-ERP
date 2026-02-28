import React, { useEffect, useState, useRef } from 'react';
import {
    View,
    Text,
    StyleSheet,
    ScrollView,
    TouchableOpacity,
    ActivityIndicator,
    Alert,
    StatusBar,
    Linking,
    Animated as RNAnimated,
} from 'react-native';
import { useLocalSearchParams, router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import * as Haptics from 'expo-haptics';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import { LinearGradient } from 'expo-linear-gradient';
import Animated, { FadeInUp, FadeInDown } from 'react-native-reanimated';

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

import { SuccessAnimation } from '../../../components/SuccessAnimation';

export default function OSDetailScreen() {
    const { id } = useLocalSearchParams();
    const { colors } = useTheme();
    const { labels, niche } = useNiche();
    const [os, setOs] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [updating, setUpdating] = useState(false);
    const [timers, setTimers] = useState<{ [key: number]: number }>({});
    const [showSuccess, setShowSuccess] = useState(false);
    const [togglingItem, setTogglingItem] = useState<number | null>(null);

    // Animação de pulso para itens em progresso
    const pulseAnim = useRef(new RNAnimated.Value(1)).current;

    useEffect(() => {
        const pulse = RNAnimated.loop(
            RNAnimated.sequence([
                RNAnimated.timing(pulseAnim, { toValue: 1.06, duration: 700, useNativeDriver: true }),
                RNAnimated.timing(pulseAnim, { toValue: 1.0, duration: 700, useNativeDriver: true }),
            ])
        );
        pulse.start();
        return () => pulse.stop();
    }, []);

    const fetchOSDetails = async () => {
        try {
            const response = await api.get(`/os/${id}`);
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

    // Live timer update effect
    useEffect(() => {
        let interval: any;

        if (os && os.items) {
            // Setup initial elapsed times coming from secure backend calculation
            const initialTimers: { [key: number]: number } = {};
            os.items.forEach((item: any) => {
                initialTimers[item.id] = item.elapsed_time || item.duration_seconds || 0;
            });
            setTimers(initialTimers);

            // Just safely increment (+1 second) bypassing nasty timezone math constraints
            interval = setInterval(() => {
                setTimers(prevTimers => {
                    const newTimers: { [key: number]: number } = { ...prevTimers };
                    os.items.forEach((item: any) => {
                        if (item.status === 'in_progress') {
                            newTimers[item.id] = (newTimers[item.id] !== undefined ? newTimers[item.id] : (item.elapsed_time || 0)) + 1;
                        } else {
                            newTimers[item.id] = item.elapsed_time || item.duration_seconds || 0;
                        }
                    });
                    return newTimers;
                });
            }, 1000);
        }

        return () => {
            if (interval) clearInterval(interval);
        };
    }, [os]);

    const formatTime = (seconds: number) => {
        if (!seconds || seconds <= 0) return "00:00:00";
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.floor(seconds % 60);
        return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    };

    const toggleItemTimer = async (itemId: number) => {
        try {
            setTogglingItem(itemId);
            const item = os.items?.find((i: any) => i.id === itemId);
            const isRunning = item?.status === 'in_progress';
            if (isRunning) {
                Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
            } else {
                Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
            }
            const response = await api.post(`/os/items/${itemId}/toggle-timer`);
            if (response.data.success) {
                fetchOSDetails();
            }
        } catch (error) {
            Alert.alert('Erro', 'Não foi possível alterar o timer.');
        } finally {
            setTogglingItem(null);
        }
    };

    const completeItem = async (itemId: number) => {
        try {
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
            const response = await api.post(`/os/items/${itemId}/complete`);
            if (response.data.success) {
                fetchOSDetails();
            }
        } catch (error) {
            Alert.alert('Erro', 'Não foi possível concluir o serviço.');
        }
    };

    const handleFinalizeOS = async () => {
        Alert.alert(
            "Finalizar Ordem",
            "Deseja concluir este serviço e notificar o cliente?",
            [
                { text: "Cancelar", style: "cancel" },
                {
                    text: "Sim, Notificar WhatsApp",
                    onPress: () => processFinalization(true)
                },
                {
                    text: "Apenas Finalizar",
                    onPress: () => processFinalization(false)
                }
            ]
        );
    };

    const processFinalization = async (notify: boolean) => {
        try {
            setUpdating(true);
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
            await api.patch(`/os/${id}/status`, { status: 'finalized' });
            setOs({ ...os, status: 'finalized' });
            setShowSuccess(true);

            if (notify) {
                const phone = os.client?.whatsapp || os.client?.phone;
                const emoji = niche === 'pet' ? '🐾' : (niche === 'electronics' ? '💻' : '🚗');
                const establishment = niche === 'pet' ? 'Pet Shop' : (niche === 'beauty_clinic' ? 'Clínica' : 'Oficina');

                const message = `Olá ${os.client?.name || 'Cliente'}! ${emoji}\nSeu ${labels.entity.toLowerCase()} ${os.veiculo?.marca} ${os.veiculo?.modelo} já está pronto no ${establishment} Ghotme!\n\nOrdem de Serviço: #${os.id}\n\nJá pode vir retirá-lo!`;

                if (phone) {
                    const cleanPhone = phone.replace(/\D/g, '');
                    const url = `whatsapp://send?phone=55${cleanPhone}&text=${encodeURIComponent(message)}`;
                    Linking.openURL(url).catch(() => {
                        Alert.alert("Erro", "Não foi possível abrir o WhatsApp.");
                    });
                } else {
                    Alert.alert("Aviso", "Cliente não possui telefone cadastrado.");
                }
            }

            Alert.alert("Sucesso", "Ordem de Serviço finalizada!");
        } catch (error) {
            Alert.alert("Erro", "Falha ao finalizar OS.");
        } finally {
            setUpdating(false);
        }
    };

    const handleUpdateStatus = async (newStatus: string) => {
        try {
            setUpdating(true);
            await api.patch(`/os/${id}/status`, { status: newStatus });
            setOs({ ...os, status: newStatus });
            Alert.alert('Sucesso', `Status atualizado para ${statusTranslations[newStatus]}`);
        } catch (error) {
            Alert.alert('Erro', 'Falha ao atualizar status.');
        } finally {
            setUpdating(false);
        }
    };

    if (loading || !os) {
        return (
            <View style={[styles.loadingContainer, { backgroundColor: colors.background }]}>
                <ActivityIndicator size="large" color={colors.primary} />
                <Text style={{ marginTop: 10, color: colors.subText }}>Carregando ordem...</Text>
            </View>
        );
    }

    const renderTimeline = () => {
        const steps = ['pending', 'approved', 'running', 'finalized'];
        const labels = ['Entrada', 'Aprovado', 'Execução', 'Pronto'];
        const currentIndex = steps.indexOf(os.status === 'canceled' ? 'pending' : os.status);

        return (
            <View style={styles.timelineContainer}>
                <View style={styles.timelineLine} />
                <View style={styles.timelineSteps}>
                    {steps.map((step, index) => {
                        const isActive = index <= currentIndex;
                        const isCurrent = index === currentIndex;
                        return (
                            <View key={step} style={styles.stepWrapper}>
                                <View style={[
                                    styles.stepDot,
                                    isActive && { backgroundColor: '#7367F0', borderColor: '#7367F0' },
                                    isCurrent && { borderWidth: 4, borderColor: '#CE9FFC' }
                                ]}>
                                    {isActive && <Ionicons name="checkmark" size={12} color="#fff" />}
                                </View>
                                <Text style={[
                                    styles.stepLabel,
                                    isActive && { color: '#7367F0', fontWeight: 'bold' }
                                ]}>{labels[index]}</Text>
                            </View>
                        );
                    })}
                </View>
            </View>
        );
    };

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
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
                {/* Timeline Substitui o Status Badge Simples */}
                {renderTimeline()}
            </LinearGradient>

            <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
                {/* Client Info */}
                <Animated.View
                    style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                    entering={FadeInDown.delay(100).duration(500).springify()}
                >
                    <View style={styles.sectionHeader}>
                        <Ionicons name="person" size={20} color={colors.primary} />
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>Cliente</Text>
                    </View>
                    <Text style={[styles.infoText, { color: colors.text }]}>{os.client?.name || os.client?.company_name}</Text>
                    <Text style={[styles.subInfoText, { color: colors.subText }]}>{os.client?.phone || 'Sem telefone'}</Text>
                </Animated.View>

                {/* Vehicle Info */}
                <Animated.View
                    style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                    entering={FadeInDown.delay(200).duration(500).springify()}
                >
                    <View style={styles.sectionHeader}>
                        <Ionicons name={niche === 'pet' ? "paw" : (niche === 'electronics' ? "laptop" : "car")} size={20} color={colors.primary} />
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>{labels.entity}</Text>
                    </View>
                    <Text style={[styles.infoText, { color: colors.text }]}>{os.veiculo?.marca} {os.veiculo?.modelo}</Text>

                    {/* Identifier Badge (Plate/Serial/Name) */}
                    {os.veiculo?.placa ? (
                        <View style={[
                            styles.plateBadge,
                            { borderColor: colors.border, backgroundColor: colors.background },
                            niche === 'automotive' ? { borderWidth: 2, borderRadius: 4 } : { borderWidth: 0, paddingHorizontal: 0 }
                        ]}>
                            <Text style={[
                                styles.plateText,
                                { color: colors.text },
                                niche === 'automotive' ? { letterSpacing: 2, textTransform: 'uppercase' } : { fontSize: 16 }
                            ]}>{niche === 'automotive' ? os.veiculo.placa : `Mod: ${os.veiculo.placa}`}</Text>
                        </View>
                    ) : null}
                    <Text style={[styles.subInfoText, { color: colors.subText }]}>{labels.color}: {os.veiculo?.cor} {labels.metric ? `| ${labels.metric}: ${os.km_entry}` : ''}</Text>
                </Animated.View>

                {/* Description / Problem */}
                <Animated.View
                    style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                    entering={FadeInDown.delay(300).duration(500).springify()}
                >
                    <View style={styles.sectionHeader}>
                        <Ionicons name="alert-circle" size={20} color={colors.primary} />
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>Relato do Problema</Text>
                    </View>
                    <Text style={[styles.descriptionText, { color: colors.subText }]}>{os.description || 'Nenhuma descrição fornecida.'}</Text>
                </Animated.View>

                {/* Action Buttons for Mechanic */}
                <Animated.View
                    style={styles.actionContainer}
                    entering={FadeInDown.delay(400).duration(500).springify()}
                >
                    <Text style={[styles.actionTitle, { color: colors.text }]}>Ações Rápidas</Text>
                    <View style={styles.buttonRow}>
                        {os.status === 'pending' && (
                            <TouchableOpacity
                                style={[styles.actionButton, { backgroundColor: '#00CFE8' }]}
                                onPress={() => handleUpdateStatus('running')}
                                disabled={updating}
                            >
                                <Ionicons name="play" size={18} color="#fff" />
                                <Text style={styles.buttonText}>Iniciar OS</Text>
                            </TouchableOpacity>
                        )}

                        {os.status === 'running' && (
                            <TouchableOpacity
                                style={[styles.actionButton, { backgroundColor: '#28C76F' }]}
                                onPress={() => handleUpdateStatus('finalized')}
                                disabled={updating}
                            >
                                <Ionicons name="checkmark-done" size={18} color="#fff" />
                                <Text style={styles.buttonText}>Finalizar OS</Text>
                            </TouchableOpacity>
                        )}

                        <TouchableOpacity
                            style={[styles.actionButton, { backgroundColor: '#7367F0' }]}
                            onPress={() => router.push({ pathname: '/os/checklist', params: { osId: os.id } })}
                        >
                            <Ionicons name="camera" size={18} color="#fff" />
                            <Text style={styles.buttonText}>Fotos</Text>
                        </TouchableOpacity>

                        <TouchableOpacity
                            style={[styles.actionButton, { backgroundColor: '#FF9F43' }]}
                            onPress={() => router.push({ pathname: '/os/technical_checklist', params: { osId: os.id } })}
                        >
                            <Ionicons name="clipboard" size={18} color="#fff" />
                            <Text style={styles.buttonText}>Checklist</Text>
                        </TouchableOpacity>
                    </View>
                </Animated.View>

                {/* Services Timer Section */}
                <Animated.View
                    style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                    entering={FadeInDown.delay(500).duration(500).springify()}
                >
                    <View style={styles.sectionHeader}>
                        <Ionicons name="timer" size={20} color={colors.primary} />
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>Cronômetro de Serviços</Text>
                        {/* Tempo total */}
                        {os.items?.some((i: any) => (timers[i.id] ?? 0) > 0) && (
                            <View style={styles.totalTimeBadge}>
                                <Text style={styles.totalTimeText}>
                                    ⏱ {formatTime(os.items?.reduce((acc: number, i: any) => acc + (timers[i.id] ?? 0), 0))}
                                </Text>
                            </View>
                        )}
                    </View>

                    {os.items?.map((item: any) => {
                        const elapsed = timers[item.id] ?? 0;
                        const isRunning = item.status === 'in_progress';
                        const isDone = item.status === 'completed';
                        const isToggling = togglingItem === item.id;

                        return (
                            <View
                                key={`item-${item.id}`}
                                style={[
                                    styles.timerCard,
                                    { borderColor: isRunning ? '#00CFE8' : (isDone ? '#28C76F' : colors.border) },
                                    isDone && { opacity: 0.75 },
                                ]}
                            >
                                {/* Header do item */}
                                <View style={styles.timerCardHeader}>
                                    <View style={[
                                        styles.timerStatusDot,
                                        { backgroundColor: isRunning ? '#00CFE8' : (isDone ? '#28C76F' : colors.border) }
                                    ]} />
                                    <Text style={[styles.timerItemName, { color: colors.text }]} numberOfLines={1}>
                                        {item.service?.name || 'Serviço'}
                                    </Text>
                                    {isDone && (
                                        <View style={styles.donePill}>
                                            <Ionicons name="checkmark-circle" size={14} color="#28C76F" />
                                            <Text style={styles.donePillText}>Concluído</Text>
                                        </View>
                                    )}
                                </View>

                                {/* Display digital do tempo */}
                                <View style={styles.timerDisplayRow}>
                                    {isRunning ? (
                                        <RNAnimated.View style={{ transform: [{ scale: pulseAnim }] }}>
                                            <View style={[styles.timerDisplay, { backgroundColor: '#00CFE820', borderColor: '#00CFE8' }]}>
                                                <Text style={[styles.timerDigits, { color: '#00CFE8' }]}>
                                                    {formatTime(elapsed)}
                                                </Text>
                                                <View style={styles.runningDot} />
                                            </View>
                                        </RNAnimated.View>
                                    ) : (
                                        <View style={[styles.timerDisplay, {
                                            backgroundColor: isDone ? '#28C76F15' : colors.background,
                                            borderColor: isDone ? '#28C76F' : colors.border,
                                        }]}>
                                            <Text style={[styles.timerDigits, {
                                                color: isDone ? '#28C76F' : colors.subText,
                                            }]}>
                                                {formatTime(elapsed)}
                                            </Text>
                                        </View>
                                    )}

                                    {/* Botões de ação */}
                                    {!isDone && (
                                        <View style={styles.timerBtns}>
                                            <TouchableOpacity
                                                style={[
                                                    styles.timerMainBtn,
                                                    { backgroundColor: isRunning ? '#FF9F43' : '#00CFE8' }
                                                ]}
                                                onPress={() => toggleItemTimer(item.id)}
                                                disabled={isToggling}
                                                activeOpacity={0.85}
                                            >
                                                {isToggling ? (
                                                    <ActivityIndicator size="small" color="#fff" />
                                                ) : (
                                                    <Ionicons
                                                        name={isRunning ? 'pause' : 'play'}
                                                        size={20}
                                                        color="#fff"
                                                    />
                                                )}
                                            </TouchableOpacity>

                                            <TouchableOpacity
                                                style={[styles.timerCompleteBtn, { borderColor: '#28C76F' }]}
                                                onPress={() => completeItem(item.id)}
                                                disabled={isToggling}
                                                activeOpacity={0.85}
                                            >
                                                <Ionicons name="checkmark" size={20} color="#28C76F" />
                                            </TouchableOpacity>
                                        </View>
                                    )}
                                </View>

                                {/* Barra de progresso visual */}
                                {elapsed > 0 && (
                                    <View style={styles.progressBarBg}>
                                        <View style={[
                                            styles.progressBarFill,
                                            {
                                                width: `${Math.min(100, (elapsed / 3600) * 100)}%`,
                                                backgroundColor: isRunning ? '#00CFE8' : (isDone ? '#28C76F' : '#7367F0'),
                                            }
                                        ]} />
                                    </View>
                                )}
                            </View>
                        );
                    })}

                    {(!os.items || os.items.length === 0) && (
                        <View style={styles.emptyTimer}>
                            <Ionicons name="timer-outline" size={32} color={colors.subText} style={{ opacity: 0.5 }} />
                            <Text style={[{ color: colors.subText, marginTop: 8, fontSize: 13 }]}>Nenhum serviço para cronometrar.</Text>
                        </View>
                    )}
                </Animated.View>

                {/* Parts Section */}
                {os.parts?.length > 0 && (
                    <Animated.View
                        style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                        entering={FadeInDown.delay(600).duration(500).springify()}
                    >
                        <View style={styles.sectionHeader}>
                            <Ionicons name="build" size={20} color={colors.primary} />
                            <Text style={[styles.sectionTitle, { color: colors.text }]}>{labels.inventory_items?.split('/')[0] + ' Utilizados' || 'Peças Utilizadas'}</Text>
                        </View>
                        {os.parts.map((part: any) => (
                            <View key={`part-${part.id}`} style={[styles.itemRow, { borderBottomColor: colors.border }]}>
                                <Text style={[styles.itemName, { color: colors.text }]}>{part.inventoryItem?.name || 'Peça'}</Text>
                                <Text style={[styles.itemQty, { color: colors.primary }]}>x{part.quantity}</Text>
                            </View>
                        ))}
                    </Animated.View>
                )}

                {/* Financial Summary */}
                <Animated.View
                    style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                    entering={FadeInDown.delay(700).duration(500).springify()}
                >
                    <View style={styles.sectionHeader}>
                        <Ionicons name="wallet" size={20} color={'#28C76F'} />
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>Resumo Financeiro</Text>
                    </View>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 8 }}>
                        <Text style={[styles.itemName, { color: colors.text, fontSize: 16 }]}>Total da Ordem</Text>
                        <Text style={[{ color: '#28C76F', fontSize: 22, fontWeight: '900' }]}>
                            {os.total ? `R$ ${parseFloat(os.total).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}` : 'R$ 0,00'}
                        </Text>
                    </View>
                </Animated.View>

                {/* BOTÃO FINALIZAR GERAL */}
                {os.status !== 'finalized' && os.status !== 'canceled' && (
                    <Animated.View entering={FadeInUp.delay(800).duration(500).springify()}>
                        <TouchableOpacity
                            style={[styles.finalizeButton, { backgroundColor: '#28C76F' }]}
                            onPress={handleFinalizeOS}
                            disabled={updating}
                        >
                            {updating ? <ActivityIndicator color="#fff" /> : (
                                <>
                                    <Ionicons name="checkmark-done-circle" size={24} color="#fff" />
                                    <Text style={styles.finalizeButtonText}>FINALIZAR E ENTREGAR</Text>
                                </>
                            )}
                        </TouchableOpacity>
                    </Animated.View>
                )}

                <View style={{ height: 100 }} />
            </ScrollView>

            <SuccessAnimation
                visible={showSuccess}
                onFinish={() => setShowSuccess(false)}
                message="Ordem Finalizada!"
            />
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
    // Timeline Styles
    timelineContainer: {
        marginTop: 20,
        marginBottom: 10,
        paddingHorizontal: 10,
    },
    timelineLine: {
        position: 'absolute',
        top: 12,
        left: 25,
        right: 25,
        height: 2,
        backgroundColor: 'rgba(255,255,255,0.3)',
        zIndex: -1,
    },
    timelineSteps: {
        flexDirection: 'row',
        justifyContent: 'space-between',
    },
    stepWrapper: {
        alignItems: 'center',
        width: 60,
    },
    stepDot: {
        width: 24,
        height: 24,
        borderRadius: 12,
        backgroundColor: '#fff',
        borderWidth: 2,
        borderColor: '#fff',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 6,
    },
    stepLabel: {
        fontSize: 10,
        color: 'rgba(255,255,255,0.8)',
        textAlign: 'center',
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
        borderWidth: 1, // Added
        borderColor: '#f0f0f0', // Added
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05, // Refined
        shadowRadius: 10,   // Refined
        elevation: 2,       // Refined
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
        flexWrap: 'wrap',
        justifyContent: 'space-between',
        gap: 10,
    },
    actionButton: {
        width: '48%', // Allow wrapping nicely
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
        marginBottom: 5,
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
        fontWeight: '500',
        color: '#333',
        flex: 1,
        marginBottom: 4,
    },
    itemQty: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#7367F0',
        marginLeft: 10,
    },
    timerCard: {
        borderRadius: 16,
        borderWidth: 1.5,
        padding: 14,
        marginBottom: 12,
        overflow: 'hidden',
    },
    timerCardHeader: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 10,
        gap: 8,
    },
    timerStatusDot: {
        width: 8,
        height: 8,
        borderRadius: 4,
    },
    timerItemName: {
        flex: 1,
        fontSize: 14,
        fontWeight: '600',
    },
    donePill: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#28C76F15',
        paddingHorizontal: 8,
        paddingVertical: 3,
        borderRadius: 50,
        gap: 4,
    },
    donePillText: {
        color: '#28C76F',
        fontSize: 11,
        fontWeight: '700',
    },
    timerDisplayRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        gap: 10,
    },
    timerDisplay: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 12,
        borderRadius: 12,
        borderWidth: 1,
        gap: 8,
    },
    timerDigits: {
        fontSize: 22,
        fontWeight: '800',
        fontVariant: ['tabular-nums'],
        letterSpacing: 2,
    },
    runningDot: {
        width: 8,
        height: 8,
        borderRadius: 4,
        backgroundColor: '#00CFE8',
    },
    timerBtns: {
        flexDirection: 'row',
        gap: 8,
    },
    timerMainBtn: {
        width: 48,
        height: 48,
        borderRadius: 14,
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.15,
        shadowRadius: 4,
        elevation: 3,
    },
    timerCompleteBtn: {
        width: 48,
        height: 48,
        borderRadius: 14,
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 2,
    },
    progressBarBg: {
        height: 4,
        backgroundColor: 'rgba(0,0,0,0.06)',
        borderRadius: 2,
        marginTop: 12,
        overflow: 'hidden',
    },
    progressBarFill: {
        height: '100%',
        borderRadius: 2,
    },
    totalTimeBadge: {
        marginLeft: 'auto',
        backgroundColor: '#7367F015',
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 50,
    },
    totalTimeText: {
        fontSize: 12,
        fontWeight: '700',
        color: '#7367F0',
    },
    emptyTimer: {
        alignItems: 'center',
        paddingVertical: 20,
    },
    finalizeButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 18,
        borderRadius: 16,
        marginTop: 20,
        marginHorizontal: 5,
        shadowColor: '#28C76F',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 6,
    },
    finalizeButtonText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: 'bold',
        marginLeft: 10,
        letterSpacing: 1,
    }
});
