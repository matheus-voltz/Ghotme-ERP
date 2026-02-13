import React, { useState, useEffect } from 'react';
import { View, Text, TouchableOpacity, Image, StyleSheet, Modal, ScrollView, Alert } from 'react-native';
import { Camera, CameraView, useCameraPermissions } from 'expo-camera';
import * as ImagePicker from 'expo-image-picker';
import { Ionicons } from '@expo/vector-icons';
import Svg, { Path, G, Rect } from 'react-native-svg';

export default function ChecklistVisual() {
  const [permission, requestPermission] = useCameraPermissions();
  const [cameraVisible, setCameraVisible] = useState(false);
  const [selectedPart, setSelectedPart] = useState(null);
  const [damages, setDamages] = useState([]); // { part: 'capo', photo: uri, description: '' }
  const [cameraRef, setCameraRef] = useState(null);

  useEffect(() => {
    (async () => {
      // Request camera permissions on mount if not granted
      if (!permission?.granted) {
        await requestPermission();
      }
    })();
  }, []);

  const handlePartPress = (partName) => {
    setSelectedPart(partName);
    Alert.alert(
      `Avaria em: ${partName}`,
      "O que deseja fazer?",
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
    if (cameraRef) {
      const photo = await cameraRef.takePictureAsync({ quality: 0.5 });
      setCameraVisible(false);
      addDamage(photo.uri);
    }
  };

  const addDamage = (photoUri) => {
    const newDamage = {
      id: Date.now(),
      part: selectedPart,
      photo: photoUri,
      date: new Date().toLocaleTimeString()
    };
    setDamages([...damages, newDamage]);
  };

  if (cameraVisible) {
    return (
      <View style={{ flex: 1 }}>
        <CameraView style={{ flex: 1 }} ref={(ref) => setCameraRef(ref)}>
          <View style={styles.cameraControls}>
            <TouchableOpacity onPress={() => setCameraVisible(false)} style={styles.closeButton}>
              <Ionicons name="close" size={30} color="white" />
            </TouchableOpacity>
            <TouchableOpacity onPress={takePicture} style={styles.captureButton}>
              <View style={styles.captureInner} />
            </TouchableOpacity>
          </View>
        </CameraView>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Checklist Visual</Text>
      <Text style={styles.subtitle}>Toque na área danificada para adicionar foto</Text>

      <View style={styles.carContainer}>
        {/* SVG Interativo do Carro - Simplificado para Mobile */}
        <Svg height="400" width="300" viewBox="0 0 300 600">
            {/* Capô - Área Interativa */}
            <G onPress={() => handlePartPress('Capô')}>
                <Path d="M60,150 L240,150 L250,120 C250,90 200,60 150,60 C100,60 50,90 50,120 L60,150" fill="#f0f2f5" stroke="#333" strokeWidth="2"/>
                {/* Highlight se tiver dano */}
                {damages.some(d => d.part === 'Capô') && <Rect x="140" y="100" width="20" height="20" rx="10" fill="red" />}
            </G>
            
            {/* Teto */}
            <G onPress={() => handlePartPress('Teto')}>
                <Rect x="65" y="225" width="170" height="200" fill="#fff" stroke="#333" strokeWidth="2"/>
                {damages.some(d => d.part === 'Teto') && <Rect x="140" y="300" width="20" height="20" rx="10" fill="red" />}
            </G>

            {/* Porta Malas */}
            <G onPress={() => handlePartPress('Porta Malas')}>
                <Path d="M60,480 L240,480 L250,510 C250,530 200,545 150,545 C100,545 50,530 50,510 L60,480" fill="#f0f2f5" stroke="#333" strokeWidth="2"/>
                {damages.some(d => d.part === 'Porta Malas') && <Rect x="140" y="500" width="20" height="20" rx="10" fill="red" />}
            </G>

            {/* Para-brisa (Apenas Visual) */}
            <Path d="M60,150 L240,150 L230,220 L70,220 Z" fill="#dbeafe" stroke="#333" strokeWidth="2"/>
        </Svg>
      </View>

      <View style={styles.listContainer}>
        <Text style={styles.sectionTitle}>Avarias Registradas ({damages.length})</Text>
        {damages.map((item) => (
          <View key={item.id} style={styles.damageItem}>
            <Image source={{ uri: item.photo }} style={styles.thumb} />
            <View style={{marginLeft: 10}}>
                <Text style={styles.damagePart}>{item.part}</Text>
                <Text style={styles.damageDate}>{item.date}</Text>
            </View>
            <TouchableOpacity onPress={() => {
                setDamages(damages.filter(d => d.id !== item.id));
            }}>
                <Ionicons name="trash-outline" size={24} color="red" />
            </TouchableOpacity>
          </View>
        ))}
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff', padding: 20 },
  title: { fontSize: 24, fontWeight: 'bold', marginBottom: 5, color: '#333' },
  subtitle: { fontSize: 14, color: '#666', marginBottom: 20 },
  carContainer: { alignItems: 'center', marginVertical: 20 },
  cameraControls: { flex: 1, backgroundColor: 'transparent', flexDirection: 'row', justifyContent: 'center', alignItems: 'flex-end', paddingBottom: 40 },
  captureButton: { width: 70, height: 70, borderRadius: 35, backgroundColor: 'white', justifyContent: 'center', alignItems: 'center' },
  captureInner: { width: 60, height: 60, borderRadius: 30, backgroundColor: 'white', borderWidth: 2, borderColor: 'black' },
  closeButton: { position: 'absolute', top: 50, right: 20 },
  listContainer: { marginTop: 20, paddingBottom: 50 },
  sectionTitle: { fontSize: 18, fontWeight: '600', marginBottom: 10 },
  damageItem: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#f8f8f8', padding: 10, borderRadius: 8, marginBottom: 10 },
  thumb: { width: 50, height: 50, borderRadius: 5, backgroundColor: '#ddd' },
  damagePart: { fontWeight: 'bold', fontSize: 16 },
  damageDate: { color: '#888', fontSize: 12 }
});
