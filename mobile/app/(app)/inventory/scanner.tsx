import React, { useState } from 'react';
import { Text, View, StyleSheet, TouchableOpacity, Alert, Platform } from 'react-native';
import { CameraView, useCameraPermissions } from 'expo-camera';
import { useRouter, Stack } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import * as Haptics from 'expo-haptics';
import api from '../../../services/api';

export default function InventoryScanner() {
    const [permission, requestPermission] = useCameraPermissions();
    const [scanned, setScanned] = useState(false);
    const [notFoundData, setNotFoundData] = useState<string | null>(null);
    const { colors } = useTheme();
    const { labels } = useNiche();
    const router = useRouter();

    const entityName = labels.inventory_items?.split('/')[0]?.toLowerCase() || 'peça';

    if (!permission) {
        return <View />;
    }

    if (!permission.granted) {
        return (
            <View style={[styles.container, { backgroundColor: colors.background, justifyContent: 'center', alignItems: 'center' }]}>
                <Text style={{ textAlign: 'center', color: colors.text, marginBottom: 20 }}>
                    Precisamos de permissão para usar a câmera
                </Text>
                <TouchableOpacity style={styles.button} onPress={requestPermission}>
                    <Text style={styles.buttonText}>Conceder Permissão</Text>
                </TouchableOpacity>
            </View>
        );
    }

    const handleBarCodeScanned = async ({ type, data }: { type: string, data: string }) => {
        if (scanned) return;
        setScanned(true);
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);

        try {
            const response = await api.get(`/inventory/items-list?search=${data}`);
            const items = response.data.data ? response.data.data : response.data;

            const foundItem = items.find((i: any) => i.sku === data || (i.name && i.name.includes(data)));

            if (foundItem) {
                router.replace({
                    pathname: '/inventory/[id]',
                    params: {
                        id: foundItem.id,
                        name: foundItem.name,
                        sku: foundItem.sku || '',
                        cost_price: String(foundItem.cost_price || 0),
                        selling_price: String(foundItem.selling_price || 0),
                        quantity: String(foundItem.quantity || 0),
                        min_quantity: String(foundItem.min_quantity || 5),
                        location: foundItem.location || '',
                        unit: foundItem.unit || 'un',
                        supplier_name: foundItem.supplier?.name || '',
                    }
                });
            } else {
                setNotFoundData(data);
            }
        } catch (error) {
            console.error("Scanner search error:", error);
            setNotFoundData(data); // Em caso de erro, também mostra a tela de não encontrado ou erro.
        }
    };

    return (
        <View style={styles.container}>
            <Stack.Screen options={{ title: 'Escanear Peça', headerTransparent: true, headerTintColor: '#fff' }} />
            {!notFoundData && (
                <CameraView
                    style={StyleSheet.absoluteFillObject}
                    onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
                    barcodeScannerSettings={{
                        barcodeTypes: ["qr", "ean13", "ean8", "code128", "pdf417", "upc_a", "upc_e"],
                    }}
                />
            )}

            {!notFoundData && (
                <View style={styles.overlay}>
                    <View style={styles.unfocusedContainer}></View>
                    <View style={styles.middleContainer}>
                        <View style={styles.unfocusedContainer}></View>
                        <View style={styles.focusedContainer}>
                            <Ionicons name="scan-outline" size={250} color="rgba(255,255,255,0.3)" />
                        </View>
                        <View style={styles.unfocusedContainer}></View>
                    </View>
                    <View style={styles.unfocusedContainer}>
                        <Text style={styles.hintText}>Aponte para o código de barras do(a) {entityName}</Text>
                    </View>
                </View>
            )}

            {notFoundData && (
                <View style={[styles.overlay, { backgroundColor: 'rgba(0,0,0,0.85)', justifyContent: 'center', alignItems: 'center', padding: 24 }]}>
                    <View style={[styles.notFoundCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
                        <View style={styles.notFoundIconBg}>
                            <Ionicons name="search-outline" size={32} color="#EA5455" />
                        </View>
                        <Text style={[styles.notFoundTitle, { color: colors.text }]}>Item não encontrado</Text>
                        <Text style={[styles.notFoundMessage, { color: colors.subText }]}>
                            Nenhum item com o código <Text style={{ fontWeight: 'bold', color: colors.text }}>{notFoundData}</Text> foi localizado no estoque.
                        </Text>

                        <View style={{ width: '100%', marginTop: 24, gap: 12 }}>
                            <TouchableOpacity
                                style={[styles.actionButton, { backgroundColor: '#7367F0' }]}
                                onPress={() => {
                                    setNotFoundData(null);
                                    setScanned(false);
                                    router.replace('/inventory/create');
                                }}
                            >
                                <Ionicons name="add-circle-outline" size={20} color="#fff" />
                                <Text style={styles.actionButtonText}>Cadastrar Novo</Text>
                            </TouchableOpacity>

                            <TouchableOpacity
                                style={[styles.actionButton, { backgroundColor: 'transparent', borderWidth: 1, borderColor: colors.border }]}
                                onPress={() => {
                                    setNotFoundData(null);
                                    setScanned(false);
                                }}
                            >
                                <Ionicons name="refresh-outline" size={20} color={colors.text} />
                                <Text style={[styles.actionButtonText, { color: colors.text }]}>Tentar Novamente</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#000' },
    overlay: { position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 },
    unfocusedContainer: { flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'center', alignItems: 'center' },
    middleContainer: { flexDirection: 'row', flex: 3 },
    focusedContainer: { flex: 6, borderWidth: 2, borderColor: '#7367F0', borderRadius: 20, justifyContent: 'center', alignItems: 'center' },
    hintText: { color: '#fff', fontSize: 14, fontWeight: '600', textAlign: 'center', paddingHorizontal: 40 },
    button: { backgroundColor: '#7367F0', paddingHorizontal: 20, paddingVertical: 12, borderRadius: 12 },
    buttonText: { color: '#fff', fontWeight: 'bold' },

    // Not Found Card
    notFoundCard: { width: '100%', padding: 24, borderRadius: 20, alignItems: 'center', borderWidth: 1, elevation: 5, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 10 },
    notFoundIconBg: { width: 64, height: 64, borderRadius: 32, backgroundColor: '#EA545515', alignItems: 'center', justifyContent: 'center', marginBottom: 16 },
    notFoundTitle: { fontSize: 20, fontWeight: 'bold', marginBottom: 8 },
    notFoundMessage: { fontSize: 14, textAlign: 'center', lineHeight: 20 },
    actionButton: { width: '100%', flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 14, borderRadius: 12, gap: 8 },
    actionButtonText: { fontSize: 15, fontWeight: 'bold', color: '#fff' }
});
