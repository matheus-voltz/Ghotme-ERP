import React, { useEffect, useState, useRef, useMemo, memo } from 'react';
import {
    View,
    Text,
    StyleSheet,
    ScrollView,
    Pressable,
    ActivityIndicator,
    Alert,
    StatusBar,
    Linking,
    Animated as RNAnimated,
    Platform
} from 'react-native';
import { useLocalSearchParams, router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import * as Haptics from 'expo-haptics';
import * as Print from 'expo-print';
import * as Sharing from 'expo-sharing';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import { useAuth } from '../../../context/AuthContext';
import { LinearGradient } from 'expo-linear-gradient';
import Animated, { FadeInUp, FadeInDown } from 'react-native-reanimated';
import DevicePassword from './components/DevicePassword';
import { SuccessAnimation } from '../../../components/SuccessAnimation';

const getStatusTranslations = (niche: string) => {
    const isFood = niche === 'food_service';
    return {
        'pending': 'Pendente',
        'approved': 'Aprovada',
        'running': isFood ? 'Em Cozinha' : 'Em Execução',
        'finalized': isFood ? 'Pronto' : 'Finalizada',
        'canceled': 'Cancelada',
    };
};

// Componente de Item do Cronômetro Memoizado para Performance
const TimerItem = memo(function TimerItem({ item, elapsed, isToggling, onToggle, onComplete, colors, pulseAnim, formatTime }: any) {
    const isRunning = item.status === 'in_progress';
    const isDone = item.status === 'completed';

    return (
        <View
            style={[
                styles.timerCard,
                { borderColor: isRunning ? '#00CFE8' : (isDone ? '#28C76F' : colors.border) },
                isDone && { opacity: 0.75 },
            ]}
        >
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

            <View style={styles.timerDisplayRow}>
                {isRunning ? (
                    <RNAnimated.View style={{ transform: [{ scale: pulseAnim }] }}>
                        <View style={[styles.timerDisplay, { backgroundColor: '#00CFE815', borderColor: '#00CFE8' }]}>
                            <Text style={[styles.timerDigits, { color: '#00CFE8' }]}>
                                {formatTime(elapsed)}
                            </Text>
                            <View style={styles.runningDot} />
                        </View>
                    </RNAnimated.View>
                ) : (
                    <View style={[styles.timerDisplay, {
                        backgroundColor: isDone ? '#28C76F10' : colors.background,
                        borderColor: isDone ? '#28C76F' : colors.border,
                    }]}>
                        <Text style={[styles.timerDigits, {
                            color: isDone ? '#28C76F' : colors.subText,
                        }]}>
                            {formatTime(elapsed)}
                        </Text>
                    </View>
                )}

                {!isDone && (
                    <View style={styles.timerBtns}>
                        <Pressable
                            style={({ pressed }) => [
                                styles.timerMainBtn,
                                { backgroundColor: isRunning ? '#FF9F43' : '#00CFE8', opacity: pressed ? 0.8 : 1 }
                            ]}
                            onPress={() => onToggle(item.id)}
                            disabled={isToggling}
                        >
                            {isToggling ? (
                                <ActivityIndicator size="small" color="#fff" />
                            ) : (
                                <Ionicons name={isRunning ? 'pause' : 'play'} size={20} color="#fff" />
                            )}
                        </Pressable>

                        <Pressable
                            style={({ pressed }) => [
                                styles.timerCompleteBtn,
                                { borderColor: '#28C76F', opacity: pressed ? 0.6 : 1 }
                            ]}
                            onPress={() => onComplete(item.id)}
                            disabled={isToggling}
                        >
                            <Ionicons name="checkmark" size={20} color="#28C76F" />
                        </Pressable>
                    </View>
                )}
            </View>

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
});

export default function OSDetailScreen() {
    const { id } = useLocalSearchParams();
    const { colors } = useTheme();
    const { labels, niche } = useNiche();
    const { user } = useAuth();

    const isAdmin = user?.role === 'admin' || user?.is_master === true;

    const [os, setOs] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [updating, setUpdating] = useState(false);
    const [timers, setTimers] = useState<{ [key: number]: number }>({});
    const [showSuccess, setShowSuccess] = useState(false);
    const [togglingItem, setTogglingItem] = useState<number | null>(null);
    const [printing, setPrinting] = useState(false);

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

    useEffect(() => {
        let interval: any;

        if (os && os.items) {
            const initialTimers: { [key: number]: number } = {};
            os.items.forEach((item: any) => {
                initialTimers[item.id] = item.elapsed_time || item.duration_seconds || 0;
            });
            setTimers(initialTimers);

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
                const isFood = niche === 'food_service';
                const emoji = isFood ? '🍔' : (niche === 'pet' ? '🐾' : (niche === 'electronics' ? '💻' : '🚗'));
                const establishment = isFood ? 'Food Truck' : (niche === 'pet' ? 'Pet Shop' : (niche === 'beauty_clinic' ? 'Clínica' : 'Oficina'));

                let message = "";
                if (isFood) {
                    message = `Olá ${os.client?.name || 'Cliente'}! ${emoji}\nSeu pedido #${os.id} já está pronto no ${establishment} Ghotme!\n\nJá pode vir retirá-lo ou aguardar a entrega!`;
                } else {
                    message = `Olá ${os.client?.name || 'Cliente'}! ${emoji}\nSeu ${labels.entity.toLowerCase()} ${os.veiculo?.marca} ${os.veiculo?.modelo} já está pronto no ${establishment} Ghotme!\n\nOrdem de Serviço: #${os.id}\n\nJá pode vir retirá-lo!`;
                }

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
        } catch (error) {
            Alert.alert("Erro", "Falha ao finalizar OS.");
        } finally {
            setUpdating(false);
        }
    };

    const generateReceiptHTML = () => {
        if (!os) return '';
        const isFood = niche === 'food_service';
        const isDelivery = (os.description ?? '').includes('ENTREGA') || os.payment_method === 'ifood';
        const clientName = os.client?.name || os.customer_name || (isFood ? 'Balcão' : 'Cliente');
        const paymentLabels: any = { cash: 'Dinheiro', pix: 'PIX', debit: 'Débito', credit: 'Crédito', ifood: 'iFood' };
        const paymentLabel = paymentLabels[os.payment_method] || os.payment_method || '---';
        const date = new Date(os.created_at).toLocaleString('pt-BR');
        const total = parseFloat(os.total || 0).toFixed(2).replace('.', ',');

        // Extrair telefone da descri\u00e7\u00e3o se n\u00e3o houver no cadastro
        const clientPhoneFromDB = os.client?.phone || os.client?.whatsapp || '';
        let clientPhoneFromDesc = '';
        if (!clientPhoneFromDB && os.description) {
            const phoneMatch = os.description.match(/\ud83d\udcde\s*([+\d\s\(\)\-]{7,20})/);
            if (phoneMatch) clientPhoneFromDesc = phoneMatch[1].trim();
        }
        const clientPhone = clientPhoneFromDB || clientPhoneFromDesc;

        let itemsHTML = '';
        if (os.parts && os.parts.length > 0) {
            os.parts.forEach((p: any) => {
                const name = p.inventory_item?.name || p.part?.name || p.inventoryItem?.name || 'Item';
                const qty = p.quantity || 1;
                const price = parseFloat(p.price || 0).toFixed(2).replace('.', ',');
                const subtotal = (qty * parseFloat(p.price || 0)).toFixed(2).replace('.', ',');
                itemsHTML += `
                    <tr>
                        <td style="text-align:left;padding:2px 0;">${qty}x ${name}</td>
                        <td style="text-align:right;padding:2px 0;">R$ ${subtotal}</td>
                    </tr>`;
            });
        }
        if (os.items && os.items.length > 0) {
            os.items.forEach((item: any) => {
                const name = item.service?.name || 'Serviço';
                const qty = item.quantity || 1;
                const subtotal = (qty * parseFloat(item.price || 0)).toFixed(2).replace('.', ',');
                itemsHTML += `
                    <tr>
                        <td style="text-align:left;padding:2px 0;">${qty}x ${name}</td>
                        <td style="text-align:right;padding:2px 0;">R$ ${subtotal}</td>
                    </tr>`;
            });
        }

        // Extrair dados de entrega da descrição
        let deliveryInfo = '';
        if (isDelivery && os.description) {
            const lines = os.description.split('\n');
            const deliveryLines = lines.filter((l: string) => l.startsWith('📞') || l.startsWith('🏠') || l.startsWith('📌'));
            if (deliveryLines.length > 0) {
                deliveryInfo = `
                    <div style="border:1px dashed #000;padding:6px;margin:8px 0;">
                        <div style="font-weight:bold;text-align:center;margin-bottom:4px;">🛵 ENTREGA</div>
                        ${deliveryLines.map((l: string) => `<div style="font-size:11px;">${l}</div>`).join('')}
                    </div>`;
            }
        }

        return `
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                @page { margin: 0; size: 80mm auto; }
                body {
                    font-family: 'Courier New', Courier, monospace;
                    font-size: 12px;
                    width: 72mm;
                    margin: 4mm auto;
                    padding: 0;
                    color: #000;
                }
                .center { text-align: center; }
                .bold { font-weight: bold; }
                .divider {
                    border-top: 1px dashed #000;
                    margin: 6px 0;
                }
                .double-divider {
                    border-top: 2px solid #000;
                    margin: 6px 0;
                }
                table { width: 100%; border-collapse: collapse; }
                .total-row td {
                    font-size: 16px;
                    font-weight: bold;
                    padding-top: 6px;
                }
                .header-logo {
                    font-size: 18px;
                    font-weight: bold;
                    letter-spacing: 2px;
                }
                .order-badge {
                    font-size: 22px;
                    font-weight: bold;
                    border: 2px solid #000;
                    display: inline-block;
                    padding: 4px 16px;
                    margin: 6px 0;
                }
                .delivery-badge {
                    background: #000;
                    color: #fff;
                    padding: 4px 12px;
                    font-weight: bold;
                    font-size: 13px;
                    display: inline-block;
                    margin: 4px 0;
                }
            </style>
        </head>
        <body>
            <div class="center">
                <div class="header-logo">GHOTME</div>
                <div style="font-size:10px;margin-top:2px;">${isFood ? 'Food Service' : 'Ordem de Serviço'}</div>
                <div class="divider"></div>
                <div class="order-badge">#${os.id}</div>
                ${isDelivery ? '<div><span class="delivery-badge">🛵 ENTREGA</span></div>' : '<div style="font-size:11px;">🏪 BALCÃO</div>'}
            </div>

            <div class="divider"></div>

            <table>
                <tr>
                    <td class="bold">Cliente:</td>
                    <td style="text-align:right;">${clientName}</td>
                </tr>
                ${clientPhone ? `<tr><td class="bold">Tel:</td><td style="text-align:right;">${clientPhone}</td></tr>` : ''}
                <tr>
                    <td class="bold">Data:</td>
                    <td style="text-align:right;">${date}</td>
                </tr>
                <tr>
                    <td class="bold">Pagamento:</td>
                    <td style="text-align:right;">${paymentLabel}</td>
                </tr>
            </table>

            ${deliveryInfo}

            <div class="double-divider"></div>
            <div class="center bold" style="font-size:13px;">ITENS DO PEDIDO</div>
            <div class="divider"></div>

            <table>
                ${itemsHTML || '<tr><td colspan="2" class="center">Nenhum item</td></tr>'}
            </table>

            <div class="double-divider"></div>
            <table>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td style="text-align:right;">R$ ${total}</td>
                </tr>
            </table>
            <div class="divider"></div>

            <div class="center" style="font-size:10px;margin-top:8px;">
                <div>Obrigado pela preferência!</div>
                <div style="margin-top:4px;">Ghotme ERP • ghotme.com</div>
                <div style="margin-top:2px;font-size:9px;">${date}</div>
            </div>

            <div style="height:20px;"></div>
        </body>
        </html>`;
    };

    const handlePrintReceipt = async () => {
        try {
            setPrinting(true);
            Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
            const html = generateReceiptHTML();
            await Print.printAsync({ html });
        } catch (error) {
            console.error('Print error:', error);
            // Fallback: gerar PDF e compartilhar
            try {
                const html = generateReceiptHTML();
                const { uri } = await Print.printToFileAsync({ html, width: 302, height: 792 });
                await Sharing.shareAsync(uri, { UTI: '.pdf', mimeType: 'application/pdf' });
            } catch (e) {
                Alert.alert('Erro', 'Não foi possível imprimir o recibo.');
            }
        } finally {
            setPrinting(false);
        }
    };

    const handleUpdateStatus = async (newStatus: string) => {
        try {
            setUpdating(true);
            await api.patch(`/os/${id}/status`, { status: newStatus });
            setOs({ ...os, status: newStatus });
            const trans = getStatusTranslations(niche);
            Alert.alert('Sucesso', `Status atualizado para ${trans[newStatus as keyof typeof trans]}`);
        } catch (error) {
            Alert.alert('Erro', 'Falha ao atualizar status.');
        } finally {
            setUpdating(false);
        }
    };

    const timeline = useMemo(() => {
        if (!os) return null;
        const steps = ['pending', 'approved', 'running', 'finalized'];
        const isFood = niche === 'food_service';
        const timelineLabels = isFood ? ['Pedido', 'Aceito', 'Cozinha', 'Pronto'] : ['Entrada', 'Aprovado', 'Execução', 'Pronto'];
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
                                ]}>{timelineLabels[index]}</Text>
                            </View>
                        );
                    })}
                </View>
            </View>
        );
    }, [os?.status, niche]);

    if (loading || !os) {
        return (
            <View style={[styles.loadingContainer, { backgroundColor: colors.background }]}>
                <ActivityIndicator size="large" color={colors.primary} />
                <Text style={{ marginTop: 10, color: colors.subText }}>Carregando ordem...</Text>
            </View>
        );
    }

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <StatusBar barStyle="light-content" />

            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.header}>
                <View style={styles.headerTop}>
                    <Pressable onPress={() => router.back()} style={({ pressed }) => [{ opacity: pressed ? 0.6 : 1 }]}>
                        <Ionicons name="arrow-back" size={24} color="#fff" />
                    </Pressable>
                    <Text style={styles.headerTitle}>
                        {niche === 'food_service' ? `Pedido #${os.id}` : `Ordem de Serviço #${os.id}`}
                    </Text>
                    <Pressable
                        onPress={handlePrintReceipt}
                        style={({ pressed }) => [{ opacity: pressed ? 0.6 : 1 }]}
                        disabled={printing}
                    >
                        {printing
                            ? <ActivityIndicator size="small" color="#fff" />
                            : <Ionicons name="print-outline" size={22} color="#fff" />}
                    </Pressable>
                </View>
                {timeline}
            </LinearGradient>

            <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
                <Animated.View
                    style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                    entering={FadeInDown.delay(100).duration(500).springify()}
                >
                    <View style={styles.sectionHeader}>
                        <Ionicons name="person" size={20} color={colors.primary} />
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>Cliente</Text>
                    </View>
                    <Text style={[styles.infoText, { color: colors.text }]}>
                        {os.client?.name || os.client?.company_name || os.customer_name || (niche === 'food_service' ? 'Balcão' : 'Cliente não informado')}
                    </Text>
                    {(() => {
                        // Prioridade: telefone do cadastro > telefone extraído da descrição
                        const clientPhone = os.client?.phone || os.client?.whatsapp;
                        let phoneFromDesc = '';
                        if (!clientPhone && os.description) {
                            const phoneMatch = os.description.match(/📞\s*([+\d\s\(\)\-]{7,20})/);
                            if (phoneMatch) phoneFromDesc = phoneMatch[1].trim();
                        }
                        const phone = clientPhone || phoneFromDesc;
                        if (phone) {
                            return (
                                <Pressable
                                    style={{ flexDirection: 'row', alignItems: 'center', marginTop: 6 }}
                                    onPress={() => {
                                        const cleanPhone = phone.replace(/\D/g, '');
                                        Linking.openURL(`whatsapp://send?phone=55${cleanPhone}`);
                                    }}
                                >
                                    <Ionicons name="logo-whatsapp" size={16} color="#28C76F" style={{ marginRight: 6 }} />
                                    <Text style={[styles.subInfoText, { color: '#28C76F', fontWeight: '700' }]}>{phone}</Text>
                                </Pressable>
                            );
                        }
                        return (
                            <Text style={[styles.subInfoText, { color: colors.subText }]}>
                                {niche === 'food_service' ? 'Sem telefone informado' : 'Sem telefone'}
                            </Text>
                        );
                    })()}
                </Animated.View>

                {niche !== 'food_service' && (
                    <Animated.View
                        style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                        entering={FadeInDown.delay(200).duration(500).springify()}
                    >
                        <View style={styles.sectionHeader}>
                            <Ionicons name={niche === 'pet' ? "paw" : (niche === 'electronics' ? "laptop" : "car")} size={20} color={colors.primary} />
                            <Text style={[styles.sectionTitle, { color: colors.text }]}>{labels.entity}</Text>
                        </View>
                        <Text style={[styles.infoText, { color: colors.text }]}>{os.veiculo?.marca} {os.veiculo?.modelo}</Text>

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
                )}

                <Animated.View
                    style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                    entering={FadeInDown.delay(300).duration(500).springify()}
                >
                    <View style={styles.sectionHeader}>
                        <Ionicons name={niche === 'food_service' ? "receipt-outline" : "alert-circle"} size={20} color={colors.primary} />
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>
                            {niche === 'food_service' ? 'Detalhes do Pedido' : 'Relato do Problema'}
                        </Text>
                    </View>
                    <Text style={[styles.descriptionText, { color: colors.subText }]}>
                        {os.description || (niche === 'food_service' ? 'Nenhuma observação no pedido.' : 'Nenhuma descrição fornecida.')}
                    </Text>
                </Animated.View>

                {niche === 'electronics' && (
                    <Animated.View entering={FadeInDown.delay(350).duration(500).springify()}>
                        <DevicePassword
                            osId={os.id}
                            initialPassword={os.device_password}
                            initialPattern={os.device_pattern_lock}
                            onUpdate={(newPwd, newPat) => setOs({ ...os, device_password: newPwd, device_pattern_lock: newPat })}
                        />
                    </Animated.View>
                )}

                {os.status === 'canceled' && (
                    <Animated.View
                        entering={FadeInDown.delay(400).duration(400).springify()}
                        style={[styles.readOnlyBanner, { backgroundColor: '#EA545515', borderColor: '#EA5455' }]}
                    >
                        <Ionicons name="close-circle-outline" size={22} color="#EA5455" />
                        <View style={{ flex: 1 }}>
                            <Text style={[styles.readOnlyTitle, { color: '#EA5455' }]}>
                                {niche === 'food_service' ? 'Pedido Cancelado' : 'Ordem Cancelada'}
                            </Text>
                            <Text style={[styles.readOnlySubtitle, { color: colors.subText }]}>
                                {niche === 'food_service' ? 'Este pedido foi cancelado.' : 'Esta OS foi cancelada e não pode ser editada.'}
                            </Text>
                        </View>
                    </Animated.View>
                )}

                {os.status === 'pending' && niche !== 'food_service' && (
                    <Animated.View
                        entering={FadeInDown.delay(400).duration(400).springify()}
                        style={[styles.readOnlyBanner, { backgroundColor: '#FF9F4315', borderColor: '#FF9F43' }]}
                    >
                        <Ionicons name="time-outline" size={22} color="#FF9F43" />
                        <View style={{ flex: 1 }}>
                            <Text style={[styles.readOnlyTitle, { color: '#FF9F43' }]}>Aguardando Aprovação</Text>
                            <Text style={[styles.readOnlySubtitle, { color: colors.subText }]}>
                                Somente visualização. As ações serão liberadas após a aprovação pelo gestor no painel web.
                            </Text>
                        </View>
                    </Animated.View>
                )}

                {os.status === 'pending' && niche === 'food_service' && (
                    <Animated.View
                        entering={FadeInDown.delay(400).duration(400).springify()}
                        style={styles.actionContainer}
                    >
                        <Text style={[styles.actionTitle, { color: colors.text }]}>Ações do Pedido</Text>
                        <View style={styles.buttonRow}>
                            <Pressable
                                style={({ pressed }) => [
                                    styles.actionButton,
                                    { backgroundColor: '#28C76F', opacity: pressed || updating ? 0.7 : 1 }
                                ]}
                                onPress={() => handleUpdateStatus('approved')}
                                disabled={updating}
                            >
                                {updating
                                    ? <ActivityIndicator size="small" color="#fff" />
                                    : <Ionicons name="checkmark-circle" size={18} color="#fff" />}
                                <Text style={styles.buttonText}>Aceitar Pedido</Text>
                            </Pressable>
                            <Pressable
                                style={({ pressed }) => [
                                    styles.actionButton,
                                    { backgroundColor: '#EA5455', opacity: pressed || updating ? 0.7 : 1 }
                                ]}
                                onPress={() => handleUpdateStatus('canceled')}
                                disabled={updating}
                            >
                                <Ionicons name="close-circle" size={18} color="#fff" />
                                <Text style={styles.buttonText}>Recusar</Text>
                            </Pressable>
                        </View>
                    </Animated.View>
                )}

                {(os.status === 'approved' || os.status === 'running') && (
                    <Animated.View
                        style={styles.actionContainer}
                        entering={FadeInDown.delay(400).duration(500).springify()}
                    >
                        <Text style={[styles.actionTitle, { color: colors.text }]}>Ações Rápidas</Text>
                        <View style={styles.buttonRow}>
                            {os.status === 'approved' && (
                                <Pressable
                                    style={({ pressed }) => [
                                        styles.actionButton,
                                        { backgroundColor: '#00CFE8', opacity: pressed || updating ? 0.7 : 1 }
                                    ]}
                                    onPress={() => handleUpdateStatus('running')}
                                    disabled={updating}
                                >
                                    {updating
                                        ? <ActivityIndicator size="small" color="#fff" />
                                        : <Ionicons name="play" size={18} color="#fff" />}
                                    <Text style={styles.buttonText}>{niche === 'food_service' ? 'Preparar' : 'Iniciar OS'}</Text>
                                </Pressable>
                            )}

                            {niche !== 'food_service' && (
                                <>
                                    <Pressable
                                        style={({ pressed }) => [
                                            styles.actionButton,
                                            { backgroundColor: '#7367F0', opacity: pressed ? 0.7 : 1 }
                                        ]}
                                        onPress={() => router.push({ pathname: '/os/checklist', params: { osId: os.id } })}
                                    >
                                        <Ionicons name="camera" size={18} color="#fff" />
                                        <Text style={styles.buttonText}>Fotos</Text>
                                    </Pressable>

                                    <Pressable
                                        style={({ pressed }) => [
                                            styles.actionButton,
                                            { backgroundColor: '#FF9F43', opacity: pressed ? 0.7 : 1 }
                                        ]}
                                        onPress={() => router.push({ pathname: '/os/technical_checklist', params: { osId: os.id } })}
                                    >
                                        <Ionicons name="clipboard" size={18} color="#fff" />
                                        <Text style={styles.buttonText}>Checklist</Text>
                                    </Pressable>
                                </>
                            )}
                        </View>
                    </Animated.View>
                )}

                {(os.status === 'running' || os.status === 'finalized') && niche !== 'food_service' && (
                    <Animated.View
                        style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                        entering={FadeInDown.delay(500).duration(500).springify()}
                    >
                        <View style={styles.sectionHeader}>
                            <Ionicons name="timer" size={20} color={colors.primary} />
                            <Text style={[styles.sectionTitle, { color: colors.text }]}>Cronômetro de Serviços</Text>
                            {os.items?.some((i: any) => (timers[i.id] ?? 0) > 0) && (
                                <View style={styles.totalTimeBadge}>
                                    <Text style={styles.totalTimeText}>
                                        ⏱ {formatTime(os.items?.reduce((acc: number, i: any) => acc + (timers[i.id] ?? 0), 0))}
                                    </Text>
                                </View>
                            )}
                        </View>

                        {os.items?.map((item: any) => (
                            <TimerItem
                                key={`item-${item.id}`}
                                item={item}
                                elapsed={timers[item.id] ?? 0}
                                isToggling={togglingItem === item.id}
                                onToggle={toggleItemTimer}
                                onComplete={completeItem}
                                colors={colors}
                                pulseAnim={pulseAnim}
                                formatTime={formatTime}
                            />
                        ))}

                        {(!os.items || os.items.length === 0) && (
                            <View style={styles.emptyTimer}>
                                <Ionicons name="timer-outline" size={32} color={colors.subText} style={{ opacity: 0.5 }} />
                                <Text style={[{ color: colors.subText, marginTop: 8, fontSize: 13 }]}>Nenhum serviço para cronometrar.</Text>
                            </View>
                        )}
                    </Animated.View>
                )}

                {os.parts?.length > 0 && (
                    <Animated.View
                        style={[styles.section, { backgroundColor: colors.card, borderColor: colors.border }]}
                        entering={FadeInDown.delay(600).duration(500).springify()}
                    >
                        <View style={styles.sectionHeader}>
                            <Ionicons name={niche === 'food_service' ? 'fast-food' : 'build'} size={20} color={colors.primary} />
                            <Text style={[styles.sectionTitle, { color: colors.text }]}>
                                {niche === 'food_service' ? 'Itens do Pedido' : (labels.inventory_items?.split('/')[0] + ' Utilizados' || 'Peças Utilizadas')}
                            </Text>
                        </View>
                        {os.parts.map((part: any) => (
                            <View key={`part-${part.id}`} style={[styles.itemRow, { borderBottomColor: colors.border }]}>
                                <Text style={[styles.itemName, { color: colors.text }]}>
                                    {part.inventory_item?.name || part.part?.name || part.inventoryItem?.name || (niche === 'food_service' ? 'Produto' : 'Peça')}
                                </Text>
                                <Text style={[styles.itemQty, { color: colors.primary }]}>x{part.quantity}</Text>
                            </View>
                        ))}
                    </Animated.View>
                )}

                {isAdmin && (
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
                )}

                {os.status === 'running' && (
                    <Animated.View entering={FadeInUp.delay(800).duration(500).springify()}>
                        <Pressable
                            style={({ pressed }) => [
                                styles.finalizeButton,
                                { backgroundColor: '#28C76F', opacity: pressed || updating ? 0.8 : 1 }
                            ]}
                            onPress={handleFinalizeOS}
                            disabled={updating}
                        >
                            {updating ? <ActivityIndicator color="#fff" /> : (
                                <>
                                    <Ionicons name="checkmark-done-circle" size={24} color="#fff" />
                                    <Text style={styles.finalizeButtonText}>{niche === 'food_service' ? 'MARCAR COMO PRONTO' : 'FINALIZAR E ENTREGAR'}</Text>
                                </>
                            )}
                        </Pressable>
                    </Animated.View>
                )}

                <View style={{ height: 100 }} />
            </ScrollView>

            <SuccessAnimation
                visible={showSuccess}
                onFinish={() => {
                    setShowSuccess(false);
                    router.back();
                }}
                message={niche === 'food_service' ? 'Pedido Concluído!' : 'Ordem Finalizada!'}
                emoji={niche === 'food_service' ? '🍽️' : (niche === 'pet' ? '🐾' : (niche === 'electronics' ? '💻' : (niche === 'beauty_clinic' ? '💅' : '🔧')))}
            />
        </View >
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#f8f9fa' },
    loadingContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { paddingTop: 60, paddingBottom: 25, paddingHorizontal: 20, borderBottomLeftRadius: 30, borderBottomRightRadius: 30 },
    headerTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    headerTitle: { fontSize: 19, fontWeight: '800', color: '#fff', letterSpacing: -0.5 },
    timelineContainer: { marginTop: 24, marginBottom: 8, paddingHorizontal: 8 },
    timelineLine: { position: 'absolute', top: 12, left: 25, right: 25, height: 2, backgroundColor: 'rgba(255,255,255,0.25)', zIndex: -1 },
    timelineSteps: { flexDirection: 'row', justifyContent: 'space-between' },
    stepWrapper: { alignItems: 'center', width: 64 },
    stepDot: { width: 24, height: 24, borderRadius: 12, backgroundColor: '#fff', borderWidth: 2, borderColor: '#fff', alignItems: 'center', justifyContent: 'center', marginBottom: 8 },
    stepLabel: { fontSize: 11, color: 'rgba(255,255,255,0.7)', textAlign: 'center', fontWeight: '500' },
    content: { flex: 1, padding: 16 },
    section: { backgroundColor: '#fff', borderRadius: 16, padding: 18, marginBottom: 16, borderWidth: 1, borderColor: '#e5e5ea' },
    sectionHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 14 },
    sectionTitle: { fontSize: 15, fontWeight: '700', color: '#1c1c1e', marginLeft: 8, textTransform: 'uppercase', letterSpacing: 0.5 },
    infoText: { fontSize: 19, fontWeight: '800', color: '#1c1c1e' },
    subInfoText: { fontSize: 15, color: '#8e8e93', marginTop: 4, fontWeight: '500' },
    plateBadge: { backgroundColor: '#f2f2f7', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8, alignSelf: 'flex-start', marginVertical: 10, borderWidth: 1, borderColor: '#e5e5ea' },
    plateText: { fontSize: 15, fontWeight: '800', color: '#1c1c1e' },
    descriptionText: { fontSize: 15, color: '#3a3a3c', lineHeight: 22 },
    readOnlyBanner: { flexDirection: 'row', alignItems: 'flex-start', gap: 12, borderWidth: 1, borderRadius: 16, padding: 16, marginBottom: 20 },
    readOnlyTitle: { fontSize: 14, fontWeight: '700', marginBottom: 4 },
    readOnlySubtitle: { fontSize: 12, lineHeight: 18 },
    actionContainer: { marginBottom: 24, paddingHorizontal: 4 },
    actionTitle: { fontSize: 17, fontWeight: '800', color: '#1c1c1e', marginBottom: 16 },
    buttonRow: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', gap: 12 },
    actionButton: { width: '48%', height: 54, borderRadius: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingHorizontal: 10 },
    buttonText: { color: '#fff', fontWeight: '700', fontSize: 14, marginLeft: 8 },
    itemRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#f2f2f7' },
    itemName: { fontSize: 15, fontWeight: '600', color: '#1c1c1e', flex: 1 },
    itemQty: { fontSize: 15, fontWeight: '800', color: '#7367F0', marginLeft: 12 },
    timerCard: { borderRadius: 16, borderWidth: 1.5, padding: 16, marginBottom: 14, backgroundColor: '#fff' },
    timerCardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 12, gap: 8 },
    timerStatusDot: { width: 8, height: 8, borderRadius: 4 },
    timerItemName: { flex: 1, fontSize: 15, fontWeight: '700' },
    donePill: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#28C76F10', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 10, gap: 4 },
    donePillText: { color: '#28C76F', fontSize: 11, fontWeight: '800' },
    timerDisplayRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12 },
    timerDisplay: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 14, borderRadius: 14, borderWidth: 1, gap: 10 },
    timerDigits: { fontSize: 24, fontWeight: '800', fontVariant: ['tabular-nums'] },
    runningDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: '#00CFE8' },
    timerBtns: { flexDirection: 'row', gap: 10 },
    timerMainBtn: { width: 52, height: 52, borderRadius: 16, alignItems: 'center', justifyContent: 'center' },
    timerCompleteBtn: { width: 52, height: 52, borderRadius: 16, alignItems: 'center', justifyContent: 'center', borderWidth: 2 },
    progressBarBg: { height: 5, backgroundColor: '#f2f2f7', borderRadius: 3, marginTop: 14, overflow: 'hidden' },
    progressBarFill: { height: '100%', borderRadius: 3 },
    totalTimeBadge: { marginLeft: 'auto', backgroundColor: '#7367F010', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 12 },
    totalTimeText: { fontSize: 13, fontWeight: '800', color: '#7367F0' },
    emptyTimer: { alignItems: 'center', paddingVertical: 24 },
    finalizeButton: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 20, borderRadius: 18, marginTop: 24, marginHorizontal: 4 },
    finalizeButtonText: { color: '#fff', fontSize: 17, fontWeight: '800', marginLeft: 12, letterSpacing: -0.2 }
});


