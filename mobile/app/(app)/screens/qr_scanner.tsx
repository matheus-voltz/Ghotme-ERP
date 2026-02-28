import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  StatusBar,
  Dimensions,
  Alert,
  ActivityIndicator,
  Animated as RNAnimated,
} from 'react-native';
import { CameraView, useCameraPermissions, BarcodeScanningResult } from 'expo-camera';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';

const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');
const SCAN_FRAME_SIZE = SCREEN_WIDTH * 0.72;

export default function QRScannerScreen() {
  const router = useRouter();
  const { colors } = useTheme();
  const [permission, requestPermission] = useCameraPermissions();
  const [scanned, setScanned] = useState(false);
  const [loading, setLoading] = useState(false);
  const [flashOn, setFlashOn] = useState(false);

  // Animação da linha de scan
  const scanLineAnim = useRef(new RNAnimated.Value(0)).current;

  const startScanAnimation = useCallback(() => {
    RNAnimated.loop(
      RNAnimated.sequence([
        RNAnimated.timing(scanLineAnim, {
          toValue: 1,
          duration: 2000,
          useNativeDriver: true,
        }),
        RNAnimated.timing(scanLineAnim, {
          toValue: 0,
          duration: 2000,
          useNativeDriver: true,
        }),
      ])
    ).start();
  }, [scanLineAnim]);

  useEffect(() => {
    startScanAnimation();
  }, [startScanAnimation]);

  const scanLineTranslateY = scanLineAnim.interpolate({
    inputRange: [0, 1],
    outputRange: [0, SCAN_FRAME_SIZE - 4],
  });

  // Handler de barcode detectado
  const handleBarcodeScanned = useCallback(
    async ({ data }: BarcodeScanningResult) => {
      if (scanned || loading) return;
      setScanned(true);
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);

      // Tenta identificar se é um ID de OS ou uma URL do sistema
      let osId: string | null = null;

      // Padrão 1: URL direta — ex: https://ghotme.com.br/os/123
      const urlMatch = data.match(/\/os\/(\d+)/);
      if (urlMatch) {
        osId = urlMatch[1];
      }
      // Padrão 2: Valor numérico simples (ID da OS)
      else if (/^\d+$/.test(data.trim())) {
        osId = data.trim();
      }
      // Padrão 3: JSON com id
      else {
        try {
          const parsed = JSON.parse(data);
          if (parsed.os_id) osId = String(parsed.os_id);
          else if (parsed.id) osId = String(parsed.id);
        } catch (_) {}
      }

      if (osId) {
        setLoading(true);
        try {
          // Valida se a OS existe no sistema
          await api.get(`/os/${osId}`);
          router.replace(`/os/${osId}` as any);
        } catch (error: any) {
          setLoading(false);
          const status = error?.response?.status;
          Alert.alert(
            'OS não encontrada',
            status === 404
              ? `A Ordem de Serviço #${osId} não existe ou não pertence à sua empresa.`
              : 'Não foi possível buscar a OS. Verifique sua conexão.',
            [{ text: 'Escanear novamente', onPress: () => setScanned(false) }]
          );
        }
      } else {
        // QR Code genérico — mostra o conteúdo e dá opção
        Alert.alert(
          'QR Code lido',
          `Conteúdo: ${data.length > 100 ? data.substring(0, 100) + '...' : data}\n\nNão foi possível identificar uma OS neste QR Code.`,
          [
            { text: 'Escanear novamente', onPress: () => setScanned(false) },
            { text: 'Fechar', style: 'cancel', onPress: () => router.back() },
          ]
        );
      }
    },
    [scanned, loading, router]
  );

  // --- Estados de permissão ---

  if (!permission) {
    return (
      <View style={[styles.centerContainer, { backgroundColor: colors.background }]}>
        <ActivityIndicator size="large" color="#7367F0" />
      </View>
    );
  }

  if (!permission.granted) {
    return (
      <View style={[styles.centerContainer, { backgroundColor: colors.background }]}>
        <StatusBar barStyle="light-content" />
        <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.permissionIcon}>
          <Ionicons name="camera-outline" size={48} color="#fff" />
        </LinearGradient>
        <Text style={[styles.permissionTitle, { color: colors.text }]}>
          Permissão de Câmera
        </Text>
        <Text style={[styles.permissionSubtitle, { color: colors.subText }]}>
          Para escanear QR Codes das Ordens de Serviço, precisamos de acesso à sua câmera.
        </Text>
        <TouchableOpacity style={styles.permissionButton} onPress={requestPermission}>
          <Text style={styles.permissionButtonText}>Conceder Permissão</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.backButtonText} onPress={() => router.back()}>
          <Text style={{ color: colors.subText, marginTop: 16 }}>Voltar</Text>
        </TouchableOpacity>
      </View>
    );
  }

  // --- Câmera ativa ---
  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="transparent" translucent />

      <CameraView
        style={StyleSheet.absoluteFill}
        facing="back"
        enableTorch={flashOn}
        barcodeScannerSettings={{ barcodeTypes: ['qr', 'code128', 'code39'] }}
        onBarcodeScanned={scanned ? undefined : handleBarcodeScanned}
      />

      {/* Overlay escuro com recorte */}
      <View style={styles.overlay}>
        {/* Topo escuro */}
        <View style={styles.topOverlay}>
          {/* Botão voltar e título */}
          <View style={styles.header}>
            <TouchableOpacity style={styles.headerBtn} onPress={() => router.back()}>
              <Ionicons name="arrow-back" size={24} color="#fff" />
            </TouchableOpacity>
            <Text style={styles.headerTitle}>Escanear QR Code</Text>
            <TouchableOpacity
              style={[styles.headerBtn, flashOn && styles.headerBtnActive]}
              onPress={() => {
                Haptics.selectionAsync();
                setFlashOn((v) => !v);
              }}
            >
              <Ionicons name={flashOn ? 'flash' : 'flash-outline'} size={24} color="#fff" />
            </TouchableOpacity>
          </View>

          <Text style={styles.instructionText}>
            Aponte a câmera para o QR Code da OS
          </Text>
        </View>

        {/* Linha horizontal com o frame transparente */}
        <View style={styles.middleRow}>
          <View style={styles.sideOverlay} />

          {/* Frame de scan */}
          <View style={styles.scanFrame}>
            {/* Cantos do frame */}
            <View style={[styles.corner, styles.cornerTL]} />
            <View style={[styles.corner, styles.cornerTR]} />
            <View style={[styles.corner, styles.cornerBL]} />
            <View style={[styles.corner, styles.cornerBR]} />

            {/* Linha animada de scan */}
            {!scanned && !loading && (
              <RNAnimated.View
                style={[
                  styles.scanLine,
                  { transform: [{ translateY: scanLineTranslateY }] },
                ]}
              />
            )}

            {/* Loading overlay dentro do frame */}
            {loading && (
              <View style={styles.loadingOverlay}>
                <ActivityIndicator size="large" color="#7367F0" />
                <Text style={styles.loadingText}>Buscando OS...</Text>
              </View>
            )}

            {/* Ícone de sucesso */}
            {scanned && !loading && (
              <View style={styles.successOverlay}>
                <Ionicons name="checkmark-circle" size={64} color="#28C76F" />
              </View>
            )}
          </View>

          <View style={styles.sideOverlay} />
        </View>

        {/* Rodapé escuro */}
        <View style={styles.bottomOverlay}>
          <Text style={styles.hintText}>
            Escaneie o QR Code impresso na OS ou na etiqueta do veículo/pet
          </Text>

          {scanned && !loading && (
            <TouchableOpacity
              style={styles.rescanButton}
              onPress={() => {
                setScanned(false);
                Haptics.selectionAsync();
              }}
            >
              <Ionicons name="refresh" size={18} color="#7367F0" style={{ marginRight: 8 }} />
              <Text style={styles.rescanText}>Escanear novamente</Text>
            </TouchableOpacity>
          )}
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  centerContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 32,
  },
  // --- Overlay ---
  overlay: {
    ...StyleSheet.absoluteFillObject,
    flexDirection: 'column',
  },
  topOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
    paddingTop: 56,
  },
  middleRow: {
    flexDirection: 'row',
    height: SCAN_FRAME_SIZE,
  },
  sideOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
  },
  bottomOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
    alignItems: 'center',
    paddingTop: 30,
    paddingHorizontal: 32,
  },
  // --- Header ---
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingBottom: 16,
  },
  headerBtn: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: 'rgba(255,255,255,0.15)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerBtnActive: {
    backgroundColor: '#FF9F43',
  },
  headerTitle: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  instructionText: {
    color: 'rgba(255,255,255,0.8)',
    fontSize: 14,
    textAlign: 'center',
    paddingHorizontal: 32,
    marginTop: 8,
  },
  // --- Frame de scan ---
  scanFrame: {
    width: SCAN_FRAME_SIZE,
    height: SCAN_FRAME_SIZE,
    position: 'relative',
    overflow: 'hidden',
  },
  corner: {
    position: 'absolute',
    width: 28,
    height: 28,
    borderColor: '#7367F0',
    borderWidth: 4,
  },
  cornerTL: {
    top: 0,
    left: 0,
    borderRightWidth: 0,
    borderBottomWidth: 0,
    borderTopLeftRadius: 8,
  },
  cornerTR: {
    top: 0,
    right: 0,
    borderLeftWidth: 0,
    borderBottomWidth: 0,
    borderTopRightRadius: 8,
  },
  cornerBL: {
    bottom: 0,
    left: 0,
    borderRightWidth: 0,
    borderTopWidth: 0,
    borderBottomLeftRadius: 8,
  },
  cornerBR: {
    bottom: 0,
    right: 0,
    borderLeftWidth: 0,
    borderTopWidth: 0,
    borderBottomRightRadius: 8,
  },
  scanLine: {
    position: 'absolute',
    left: 4,
    right: 4,
    height: 2,
    backgroundColor: '#7367F0',
    shadowColor: '#7367F0',
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.8,
    shadowRadius: 6,
  },
  loadingOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.6)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingText: {
    color: '#fff',
    marginTop: 12,
    fontSize: 14,
    fontWeight: '600',
  },
  successOverlay: {
    ...StyleSheet.absoluteFillObject,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(40,199,111,0.2)',
  },
  // --- Rodapé ---
  hintText: {
    color: 'rgba(255,255,255,0.6)',
    fontSize: 13,
    textAlign: 'center',
    lineHeight: 20,
  },
  rescanButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 50,
    marginTop: 20,
  },
  rescanText: {
    color: '#7367F0',
    fontWeight: 'bold',
    fontSize: 15,
  },
  // --- Permissão ---
  permissionIcon: {
    width: 100,
    height: 100,
    borderRadius: 50,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 24,
  },
  permissionTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    marginBottom: 12,
    textAlign: 'center',
  },
  permissionSubtitle: {
    fontSize: 15,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 32,
  },
  permissionButton: {
    backgroundColor: '#7367F0',
    paddingHorizontal: 32,
    paddingVertical: 14,
    borderRadius: 50,
  },
  permissionButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  backButtonText: {
    alignItems: 'center',
  },
});
