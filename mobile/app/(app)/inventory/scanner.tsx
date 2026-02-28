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
                Alert.alert(
                    "Item não encontrado",
                    `Nenhum item com o código ${data} foi localizado no estoque.`,
                    [{ text: "OK", onPress: () => setScanned(false) }]
                );
            }
        } catch (error) {
            console.error("Scanner search error:", error);
            Alert.alert("Erro", "Não foi possível buscar o item no estoque.");
            setScanned(false);
        }
    };

    return (
        <View style={styles.container}>
            <Stack.Screen options={{ title: 'Escanear Peça', headerTransparent: true, headerTintColor: '#fff' }} />
            <CameraView
                style={StyleSheet.absoluteFillObject}
                onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
                barcodeScannerSettings={{
                    barcodeTypes: ["qr", "ean13", "ean8", "code128", "pdf417", "upc_a", "upc_e"],
                }}
            />

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
                    {scanned && (
                        <TouchableOpacity style={styles.rescanButton} onPress={() => setScanned(false)}>
                            <Text style={styles.rescanText}>Escanear Novamente</Text>
                        </TouchableOpacity>
                    )}
                </View>
            </View>
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
    rescanButton: { marginTop: 20, backgroundColor: '#fff', paddingHorizontal: 20, paddingVertical: 12, borderRadius: 12 },
    rescanText: { color: '#7367F0', fontWeight: 'bold' }
});
