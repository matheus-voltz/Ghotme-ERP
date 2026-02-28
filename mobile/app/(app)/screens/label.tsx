import React, { useRef } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Share, Platform } from 'react-native';
import { useLocalSearchParams, useRouter, Stack } from 'expo-router';
import { useTheme } from '../../../context/ThemeContext';
import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';

export default function LabelGeneratorScreen() {
    const { id, type, title } = useLocalSearchParams();
    const { colors } = useTheme();
    const router = useRouter();

    // Usando API pública para gerar o QR Code
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${id}`;

    const handleShare = async () => {
        try {
            await Share.share({
                message: `Identificação ${type === 'os' ? 'Ordem de Serviço' : 'Item de Estoque'}: ${title}\nCódigo: ${id}\nLink: ${qrUrl}`,
                url: qrUrl,
            });
        } catch (error) {
            console.error(error);
        }
    };

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <Stack.Screen options={{
                title: 'Etiqueta Inteligente',
                headerShadowVisible: false,
                headerStyle: { backgroundColor: colors.background }
            }} />

            <View style={styles.content}>
                <View style={[styles.labelCard, { backgroundColor: '#fff', shadowColor: colors.primary }]}>
                    <View style={styles.labelHeader}>
                        <Ionicons name="flash" size={24} color="#7367F0" />
                        <Text style={styles.brandName}>GHOTME ERP</Text>
                    </View>

                    <View style={styles.qrContainer}>
                        <Image
                            source={{ uri: qrUrl }}
                            style={styles.qrCode}
                            contentFit="contain"
                        />
                    </View>

                    <View style={styles.infoContainer}>
                        <Text style={styles.itemTitle}>{title || 'Identificação'}</Text>
                        <Text style={styles.itemId}>#{id}</Text>
                        <Text style={styles.itemType}>{type === 'os' ? 'ORDEM DE SERVIÇO' : 'ITEM DE ESTOQUE'}</Text>
                    </View>

                    <View style={styles.labelFooter}>
                        <Text style={styles.footerText}>Bipe para ver detalhes no App</Text>
                    </View>
                </View>

                <View style={styles.actionsContainer}>
                    <Text style={[styles.hintText, { color: colors.subText }]}>
                        Você pode imprimir esta etiqueta em uma impressora térmica Bluetooth ou Wi-Fi através do compartilhamento.
                    </Text>

                    <TouchableOpacity style={[styles.primaryButton, { backgroundColor: colors.primary }]} onPress={handleShare}>
                        <Ionicons name="share-social-outline" size={24} color="#fff" />
                        <Text style={styles.buttonText}>Compartilhar / Imprimir</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={styles.secondaryButton} onPress={() => router.back()}>
                        <Text style={[styles.secondaryButtonText, { color: colors.subText }]}>Voltar para o Início</Text>
                    </TouchableOpacity>
                </View>
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    content: { flex: 1, padding: 30, alignItems: 'center', justifyContent: 'center' },
    labelCard: {
        width: '100%',
        padding: 24,
        borderRadius: 20,
        backgroundColor: '#fff',
        alignItems: 'center',
        elevation: 8,
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.1,
        shadowRadius: 20,
        borderWidth: 1,
        borderColor: '#eee',
    },
    labelHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 20, gap: 8 },
    brandName: { fontSize: 18, fontWeight: '900', color: '#7367F0', letterSpacing: 1 },
    qrContainer: {
        width: 200,
        height: 200,
        backgroundColor: '#f8f8f8',
        borderRadius: 16,
        padding: 10,
        justifyContent: 'center',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#f0f0f0',
    },
    qrCode: { width: '100%', height: '100%' },
    infoContainer: { marginTop: 20, alignItems: 'center' },
    itemTitle: { fontSize: 20, fontWeight: 'bold', color: '#333', textAlign: 'center' },
    itemId: { fontSize: 16, fontWeight: '600', color: '#666', marginTop: 4 },
    itemType: { fontSize: 12, fontWeight: 'bold', color: '#999', marginTop: 8, letterSpacing: 2 },
    labelFooter: { marginTop: 24, paddingTop: 16, borderTopWidth: 1, borderTopColor: '#eee', width: '100%', alignItems: 'center' },
    footerText: { fontSize: 11, color: '#aaa', fontWeight: '600', textTransform: 'uppercase' },
    actionsContainer: { width: '100%', marginTop: 40, gap: 15 },
    hintText: { textAlign: 'center', fontSize: 13, lineHeight: 20, marginBottom: 10, paddingHorizontal: 20 },
    primaryButton: {
        height: 60,
        borderRadius: 16,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 12,
        shadowColor: '#7367F0',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 4,
    },
    buttonText: { color: '#fff', fontSize: 16, fontWeight: 'bold' },
    secondaryButton: { height: 50, alignItems: 'center', justifyContent: 'center' },
    secondaryButtonText: { fontSize: 15, fontWeight: '600' },
});
