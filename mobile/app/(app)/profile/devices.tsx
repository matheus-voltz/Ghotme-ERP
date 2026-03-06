import React, { useState, useEffect } from 'react';
import {
    View,
    Text,
    StyleSheet,
    ScrollView,
    TouchableOpacity,
    Alert,
    ActivityIndicator,
    Platform,
    FlatList
} from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../context/ThemeContext';
import { useDevices } from '../../../context/DeviceContext';
import { LinearGradient } from 'expo-linear-gradient';
import Animated, { FadeInDown, ZoomIn } from 'react-native-reanimated';

export default function DevicesScreen() {
    const { colors } = useTheme();
    const { pairedDevices, removeDevice, addDevice } = useDevices();
    const router = useRouter();
    const [isScanning, setIsScanning] = useState(false);
    const [availableDevices, setAvailableDevices] = useState<any[]>([]);

    const startDiscovery = async () => {
        setIsScanning(true);
        // Simulação de descoberta para UI
        // Na prática aqui entraríamos com BLEManager.scan()
        setTimeout(() => {
            setAvailableDevices([
                { id: '00:11:22:33:44:55', name: 'Moderninha Plus 2', type: 'card_reader', brand: 'pagseguro' },
                { id: '66:77:88:99:AA:BB', name: 'Impressora Térmica 58mm', type: 'printer' }
            ]);
            setIsScanning(false);
        }, 3000);
    };

    const handlePair = (device: any) => {
        Alert.alert(
            "Conectar Dispositivo",
            `Deseja parear com ${device.name}?`,
            [
                { text: "Cancelar", style: "cancel" },
                {
                    text: "Conectar",
                    onPress: () => {
                        addDevice(device);
                        setAvailableDevices(prev => prev.filter(d => d.id !== device.id));
                        Alert.alert("Sucesso", "Dispositivo pareado com sucesso!");
                    }
                }
            ]
        );
    };

    const renderDeviceItem = (item: any, isPaired: boolean) => (
        <Animated.View
            key={item.id}
            entering={FadeInDown.duration(400)}
            style={[styles.deviceCard, { backgroundColor: colors.card, borderColor: colors.border }]}
        >
            <View style={[styles.iconWrapper, { backgroundColor: item.type === 'card_reader' ? '#7367F015' : '#28C76F15' }]}>
                <Ionicons
                    name={item.type === 'card_reader' ? "card-outline" : "print-outline"}
                    size={24}
                    color={item.type === 'card_reader' ? "#7367F0" : "#28C76F"}
                />
            </View>
            <View style={styles.deviceInfo}>
                <Text style={[styles.deviceName, { color: colors.text }]}>{item.name}</Text>
                <Text style={[styles.deviceSub, { color: colors.subText }]}>
                    {item.brand === 'pagseguro' ? 'PagSeguro • ' : ''}
                    {item.id}
                </Text>
            </View>

            {isPaired ? (
                <TouchableOpacity onPress={() => removeDevice(item.id)}>
                    <Text style={{ color: '#EA5455', fontWeight: 'bold' }}>Remover</Text>
                </TouchableOpacity>
            ) : (
                <TouchableOpacity
                    style={[styles.pairBtn, { backgroundColor: colors.primary }]}
                    onPress={() => handlePair(item)}
                >
                    <Text style={styles.pairBtnText}>Parear</Text>
                </TouchableOpacity>
            )}
        </Animated.View>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.header}>
                <View style={styles.headerTop}>
                    <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={24} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.headerTitle}>Dispositivos Bluetooth</Text>
                    <View style={{ width: 24 }} />
                </View>
            </LinearGradient>

            <ScrollView contentContainerStyle={styles.content}>
                <View style={styles.section}>
                    <Text style={[styles.sectionTitle, { color: colors.text }]}>Meus Dispositivos</Text>
                    {pairedDevices.length > 0 ? (
                        pairedDevices.map(d => renderDeviceItem(d, true))
                    ) : (
                        <View style={styles.emptyCard}>
                            <Text style={{ color: colors.subText, textAlign: 'center' }}>Nenhum dispositivo salvo</Text>
                        </View>
                    )}
                </View>

                <View style={[styles.scanSection, { marginTop: 20 }]}>
                    <View style={styles.scanHeader}>
                        <Text style={[styles.sectionTitle, { color: colors.text }]}>Disponíveis para Parear</Text>
                        {isScanning && <ActivityIndicator size="small" color={colors.primary} />}
                    </View>

                    {availableDevices.length > 0 ? (
                        availableDevices.map(d => renderDeviceItem(d, false))
                    ) : !isScanning && (
                        <TouchableOpacity
                            style={[styles.scanBtn, { borderColor: colors.primary }]}
                            onPress={startDiscovery}
                        >
                            <Ionicons name="search" size={20} color={colors.primary} />
                            <Text style={[styles.scanBtnText, { color: colors.primary }]}>Procurar Dispositivos</Text>
                        </TouchableOpacity>
                    )}
                </View>

                <View style={styles.warningCard}>
                    <Ionicons name="alert-circle" size={20} color="#FF9F43" />
                    <Text style={styles.warningText}>
                        Certifique-se que o dispositivo Bluetooth está ligado e no modo de pareamento.
                        No caso de maquininhas, algumas exigem pareamento prévio nas configurações do Android/iOS.
                    </Text>
                </View>
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: { paddingTop: 60, paddingBottom: 25, paddingHorizontal: 20, borderBottomLeftRadius: 30, borderBottomRightRadius: 30 },
    headerTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#fff' },
    backBtn: { padding: 4 },
    content: { padding: 20 },
    section: { marginBottom: 10 },
    sectionTitle: { fontSize: 16, fontWeight: 'bold', marginBottom: 15 },
    scanSection: { marginBottom: 20 },
    scanHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 15 },
    deviceCard: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 15,
        borderRadius: 16,
        borderWidth: 1,
        marginBottom: 12
    },
    iconWrapper: { width: 45, height: 45, borderRadius: 12, justifyContent: 'center', alignItems: 'center', marginRight: 15 },
    deviceInfo: { flex: 1 },
    deviceName: { fontSize: 15, fontWeight: '600', marginBottom: 2 },
    deviceSub: { fontSize: 12 },
    pairBtn: { paddingHorizontal: 15, paddingVertical: 8, borderRadius: 10 },
    pairBtnText: { color: '#fff', fontSize: 13, fontWeight: 'bold' },
    emptyCard: { padding: 30, borderRadius: 16, borderStyle: 'dashed', borderWidth: 1, borderColor: '#ccc' },
    scanBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 15,
        borderRadius: 16,
        borderWidth: 1,
        borderStyle: 'dashed',
        gap: 10
    },
    scanBtnText: { fontWeight: 'bold' },
    warningCard: { flexDirection: 'row', backgroundColor: '#FFF9F2', padding: 15, borderRadius: 12, marginTop: 30, gap: 10 },
    warningText: { flex: 1, fontSize: 12, color: '#C18446', lineHeight: 18 }
});
