import React, { useState, useEffect } from 'react';
import { View, Text, TouchableOpacity, Image, StyleSheet, ScrollView, Alert, Dimensions, ActivityIndicator } from 'react-native';
import { CameraView, useCameraPermissions } from 'expo-camera';
import * as ImagePicker from 'expo-image-picker';
import { Ionicons } from '@expo/vector-icons';
import Svg, { Path, G, Rect } from 'react-native-svg';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { useTheme } from '../../../context/ThemeContext';
import { useAuth } from '../../../context/AuthContext';
import api from '../../../services/api';

const { width } = Dimensions.get('window');

export default function ChecklistVisual() {
  const router = useRouter();
  const { user } = useAuth();
  const { osId } = useLocalSearchParams();
  const { colors } = useTheme();

  const logoUrl = user?.company?.logo_url || (user?.company?.logo_path ? `${api.defaults.baseURL?.replace('/api', '')}/storage/${user?.company?.logo_path}` : null);

  const [permission, requestPermission] = useCameraPermissions();
  const [cameraVisible, setCameraVisible] = useState(false);
  const [selectedPart, setSelectedPart] = useState<string | null>(null);
  const [damages, setDamages] = useState<any[]>([]);
  const [isProcessing, setIsProcessing] = useState(false);
  const cameraRef = React.useRef<any>(null);

  useEffect(() => {
    if (!permission?.granted) {
      requestPermission();
    }
  }, []);

  const handlePartPress = (partName: string) => {
    setSelectedPart(partName);
    Alert.alert(
      `Vistoria: ${partName}`,
      "Como deseja registrar a avaria?",
      [
        { text: "Cancelar", style: "cancel" },
        { text: "Tirar Foto", onPress: () => setCameraVisible(true) },
        { text: "Galeria", onPress: pickImage }
      ]
    );
  };

  const pickImage = async () => {
    let result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      quality: 0.5,
    });

    if (!result.canceled) {
      addDamage(result.assets[0].uri);
    }
  };

  const takePicture = async () => {
    if (cameraRef.current && !isProcessing) {
      try {
        setIsProcessing(true);
        const photo = await cameraRef.current.takePictureAsync({ quality: 0.5 });
        if (photo) {
          addDamage(photo.uri);
          setCameraVisible(false);
        }
      } catch (error) {
        console.error("Erro ao tirar foto:", error);
        Alert.alert("Erro", "Não foi possível capturar a foto.");
      } finally {
        setIsProcessing(false);
      }
    }
  };

  const addDamage = (photoUri: string) => {
    const newDamage = {
      id: Date.now(),
      part: selectedPart,
      photo: photoUri,
      date: new Date().toLocaleTimeString('pt-BR')
    };
    setDamages([...damages, newDamage]);
  };

  const hasDamage = (part: string) => damages.some(d => d.part === part);

  const handleSaveChecklist = async () => {
    if (damages.length === 0) {
      Alert.alert("Aviso", "Registre pelo menos uma avaria antes de salvar.");
      return;
    }

    try {
      setIsProcessing(true);
      const formData = new FormData();
      formData.append('ordem_servico_id', osId as string);

      damages.forEach((damage, index) => {
        formData.append(`parts[${index}]`, damage.part);
        // @ts-ignore
        formData.append(`photos[${index}]`, {
          uri: damage.photo,
          name: `damage_${index}.jpg`,
          type: 'image/jpeg',
        });
      });

      const response = await api.post('/checklist/visual', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (response.data.success) {
        Alert.alert("Sucesso", "Vistoria enviada com sucesso!");
        router.back();
      }
    } catch (error) {
      console.error("Erro ao salvar vistoria:", error);
      Alert.alert("Erro", "Não foi possível enviar a vistoria para o servidor.");
    } finally {
      setIsProcessing(false);
    }
  };

  if (cameraVisible) {
    return (
      <View style={{ flex: 1, backgroundColor: '#000' }}>
        <CameraView
          style={{ flex: 1 }}
          ref={cameraRef}
          onMountError={(error) => Alert.alert("Erro de Câmera", error.message)}
        />

        {/* Controles da Câmera (Agora FORA do CameraView com posicionamento absoluto) */}
        <View style={[StyleSheet.absoluteFill, styles.cameraOverlay]}>
          <View style={styles.cameraControls}>
            <TouchableOpacity
              onPress={() => !isProcessing && setCameraVisible(false)}
              style={styles.closeButton}
              disabled={isProcessing}
            >
              <Ionicons name="close-circle" size={40} color="white" />
            </TouchableOpacity>

            <TouchableOpacity
              onPress={takePicture}
              style={[styles.captureButton, isProcessing && { opacity: 0.5 }]}
              disabled={isProcessing}
            >
              <View style={styles.captureInner}>
                {isProcessing && <ActivityIndicator color="#7367F0" />}
              </View>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    );
  }

  return (
    <ScrollView style={[styles.container, { backgroundColor: colors.background }]} showsVerticalScrollIndicator={false}>
      <View style={[styles.header, { backgroundColor: colors.card, borderBottomColor: colors.border, paddingTop: 60 }]}>
        <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 10 }}>
          <View style={{ flexDirection: 'row', alignItems: 'center' }}>
            <TouchableOpacity onPress={() => router.back()} style={{ marginRight: 15 }}>
              <Ionicons name="arrow-back" size={24} color={colors.text} />
            </TouchableOpacity>
            <Text style={[styles.title, { color: colors.text }]}>Vistoria Visual</Text>
          </View>
          {logoUrl && (
            <Image
              source={{ uri: logoUrl }}
              style={{ width: 100, height: 40 }}
              contentFit="contain"
            />
          )}
        </View>
        <Text style={[styles.subtitle, { color: colors.subText }]}>OS: #{osId || 'N/A'}</Text>
        <Text style={[styles.subtitle, { color: colors.subText }]}>Toque nas partes do {labels.entity.toLowerCase()} para registrar avarias</Text>
      </View>

      <View style={styles.carWrapper}>
        <Svg width="300" height="600" viewBox="0 0 300 600">
          {/* FRENTE */}
          <G onPress={() => handlePartPress('Frente')}>
            <Path d="M100,20 C120,10 180,10 200,20 L220,100 L80,100 Z"
              fill={hasDamage('Frente') ? "#EA5455" : "#eee"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* CAPO */}
          <G onPress={() => handlePartPress('Capô')}>
            <Path d="M80,100 L220,100 L230,180 L70,180 Z"
              fill={hasDamage('Capô') ? "#EA5455" : "#fff"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* PARA-BRISA */}
          <G onPress={() => handlePartPress('Para-brisa')}>
            <Path d="M75,185 L225,185 L235,220 L65,220 Z"
              fill={hasDamage('Para-brisa') ? "#EA5455" : "#dbeafe"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* TETO */}
          <G onPress={() => handlePartPress('Teto')}>
            <Rect x="70" y="225" width="160" height="200"
              fill={hasDamage('Teto') ? "#EA5455" : "#fff"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* PORTA ESQUERDA DIANTEIRA */}
          <G onPress={() => handlePartPress('Porta Esq. Diant.')}>
            <Path d="M50,225 L70,225 L70,325 L50,325 Z"
              fill={hasDamage('Porta Esq. Diant.') ? "#EA5455" : "#eee"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* PORTA DIREITA DIANTEIRA */}
          <G onPress={() => handlePartPress('Porta Dir. Diant.')}>
            <Path d="M230,225 L250,225 L250,325 L230,325 Z"
              fill={hasDamage('Porta Dir. Diant.') ? "#EA5455" : "#eee"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* PORTA ESQUERDA TRASEIRA */}
          <G onPress={() => handlePartPress('Porta Esq. Tras.')}>
            <Path d="M50,325 L70,325 L70,425 L50,425 Z"
              fill={hasDamage('Porta Esq. Tras.') ? "#EA5455" : "#eee"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* PORTA DIREITA TRASEIRA */}
          <G onPress={() => handlePartPress('Porta Dir. Tras.')}>
            <Path d="M230,325 L250,325 L250,425 L230,425 Z"
              fill={hasDamage('Porta Dir. Tras.') ? "#EA5455" : "#eee"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* VIDRO TRASEIRO */}
          <G onPress={() => handlePartPress('Vidro Traseiro')}>
            <Path d="M75,430 L225,430 L235,480 L65,480 Z"
              fill={hasDamage('Vidro Traseiro') ? "#EA5455" : "#dbeafe"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* PORTA MALAS */}
          <G onPress={() => handlePartPress('Porta Malas')}>
            <Path d="M60,480 L240,480 L250,510 C250,530 200,545 150,545 C100,545 50,530 50,510 L60,480"
              fill={hasDamage('Porta Malas') ? "#EA5455" : "#eee"} stroke="#333" strokeWidth="1.5" />
          </G>

          {/* RODAS */}
          <G onPress={() => handlePartPress('Roda Diant. Esq.')}>
            <Rect x="30" y="130" width="20" height="40" rx="5" fill={hasDamage('Roda Diant. Esq.') ? "red" : "#333"} />
          </G>
          <G onPress={() => handlePartPress('Roda Diant. Dir.')}>
            <Rect x="250" y="130" width="20" height="40" rx="5" fill={hasDamage('Roda Diant. Dir.') ? "red" : "#333"} />
          </G>
          <G onPress={() => handlePartPress('Roda Tras. Esq.')}>
            <Rect x="30" y="430" width="20" height="40" rx="5" fill={hasDamage('Roda Tras. Esq.') ? "red" : "#333"} />
          </G>
          <G onPress={() => handlePartPress('Roda Tras. Dir.')}>
            <Rect x="250" y="430" width="20" height="40" rx="5" fill={hasDamage('Roda Tras. Dir.') ? "red" : "#333"} />
          </G>
        </Svg>
      </View>

      <View style={styles.listContainer}>
        <Text style={[styles.sectionTitle, { color: colors.text }]}>Fotos e Avarias ({damages.length})</Text>
        {damages.length === 0 && (
          <View style={[styles.emptyState, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <Ionicons name="camera-outline" size={40} color="#ccc" />
            <Text style={{ color: '#999' }}>Nenhuma avaria registrada</Text>
          </View>
        )}
        {damages.map((item) => (
          <View key={item.id} style={[styles.damageCard, { backgroundColor: colors.card }]}>
            <Image source={{ uri: item.photo }} style={styles.thumb} />
            <View style={styles.damageInfo}>
              <Text style={[styles.damagePart, { color: colors.text }]}>{item.part}</Text>
              <Text style={styles.damageDate}>Registrado às {item.date}</Text>
            </View>
            <TouchableOpacity onPress={() => !isProcessing && setDamages(damages.filter(d => d.id !== item.id))} disabled={isProcessing}>
              <Ionicons name="trash" size={22} color="#EA5455" />
            </TouchableOpacity>
          </View>
        ))}

        {damages.length > 0 && (
          <TouchableOpacity
            style={[styles.saveButton, isProcessing && { opacity: 0.7 }]}
            onPress={handleSaveChecklist}
            disabled={isProcessing}
          >
            {isProcessing ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <Text style={styles.saveButtonText}>Finalizar e Sincronizar</Text>
            )}
          </TouchableOpacity>
        )}
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  header: { padding: 24, paddingBottom: 20, borderBottomWidth: 1 },
  title: { fontSize: 22, fontWeight: 'bold' },
  subtitle: { fontSize: 13, marginTop: 2 },
  carWrapper: { alignItems: 'center', paddingVertical: 20 },
  cameraOverlay: { backgroundColor: 'transparent', justifyContent: 'flex-end' },
  cameraControls: { justifyContent: 'center', alignItems: 'flex-end', flexDirection: 'row', paddingBottom: 50 },
  captureButton: { width: 80, height: 80, borderRadius: 40, backgroundColor: '#fff', padding: 5 },
  captureInner: { flex: 1, borderRadius: 40, backgroundColor: '#fff', borderWidth: 2, borderColor: '#7367F0', justifyContent: 'center', alignItems: 'center' },
  closeButton: { position: 'absolute', bottom: 70, right: 30, zIndex: 10 },
  listContainer: { padding: 20 },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', marginBottom: 15 },
  emptyState: { alignItems: 'center', padding: 40, borderRadius: 15, borderStyle: 'dashed', borderWidth: 1 },
  damageCard: { flexDirection: 'row', alignItems: 'center', padding: 12, borderRadius: 12, marginBottom: 12, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4 },
  thumb: { width: 60, height: 60, borderRadius: 8 },
  damageInfo: { flex: 1, marginLeft: 15 },
  damagePart: { fontSize: 16, fontWeight: 'bold' },
  damageDate: { fontSize: 12, color: '#999', marginTop: 2 },
  saveButton: { backgroundColor: '#28C76F', padding: 16, borderRadius: 12, alignItems: 'center', marginTop: 10, marginBottom: 40 },
  saveButtonText: { color: '#fff', fontSize: 16, fontWeight: 'bold' }
});