import React, { useState, useEffect } from 'react';
import { View, Text, TextInput, StyleSheet, ScrollView, TouchableOpacity, Alert, ActivityIndicator, KeyboardAvoidingView, Platform, Switch, Modal, FlatList } from 'react-native';

import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';

import { CameraView, useCameraPermissions } from 'expo-camera';
import * as Haptics from 'expo-haptics';
import * as ImagePicker from 'expo-image-picker';
import Animated, { FadeInDown } from 'react-native-reanimated';
import { Image } from 'react-native';

// Componente CustomInput (reutilizado)
const CustomInput = ({ label, icon, value, onChangeText, placeholder, keyboardType = 'default', flex = 1, colors, actionIcon, onActionPress }: any) => (
    <View style={[styles.inputGroup, { flex }]}>
        <Text style={[styles.label, { color: colors.subText }]}>{label}</Text>
        <View style={[styles.inputWrapper, { backgroundColor: colors.iconBg, borderColor: colors.border }]}>
            <Ionicons name={icon} size={18} color="#7367F0" style={styles.inputIcon} />
            <TextInput
                style={[styles.input, { color: colors.text }]}
                value={value} onChangeText={onChangeText}
                placeholder={placeholder} placeholderTextColor={colors.subText}
                keyboardType={keyboardType}
            />
            {actionIcon && (
                <TouchableOpacity onPress={onActionPress} style={styles.actionBtn}>
                    <Ionicons name={actionIcon} size={22} color="#7367F0" />
                </TouchableOpacity>
            )}
        </View>
    </View>
);

import { useNiche } from '../../../context/NicheContext';

// ...

export default function CreateInventoryScreen() {
    const router = useRouter();
    const { colors } = useTheme();
    const { labels } = useNiche();
    const [loading, setLoading] = useState(false);
    const [controlStock, setControlStock] = useState(true);
    const [image, setImage] = useState<string | null>(null);
    const [categories, setCategories] = useState<any[]>([]);
    const [fetchingCategories, setFetchingCategories] = useState(false);
    const [showCategoryModal, setShowCategoryModal] = useState(false);
    const [showNewCategoryModal, setShowNewCategoryModal] = useState(false);
    const [newCategoryName, setNewCategoryName] = useState('');
    const [savingCategory, setSavingCategory] = useState(false);

    const [form, setForm] = useState({
        name: '',
        sku: '',
        selling_price: '',
        cost_price: '',
        quantity: '',
        min_quantity: '5',
        menu_category_id: ''
    });

    useEffect(() => {
        fetchCategories();
    }, []);

    const fetchCategories = async () => {
        setFetchingCategories(true);
        try {
            const response = await api.get('/categories');
            setCategories(response.data);
        } catch (error) {
            console.error("Erro ao buscar categorias:", error);
        } finally {
            setFetchingCategories(false);
        }
    };

    const handleAddNewCategory = async () => {
        if (!newCategoryName || newCategoryName.trim() === '') return;
        setSavingCategory(true);
        try {
            const res = await api.post('/categories', { name: newCategoryName.trim() });
            setCategories(prev => [...prev, res.data]);
            updateForm('menu_category_id', res.data.id.toString());
            setShowNewCategoryModal(false);
            setNewCategoryName('');
            Alert.alert("Sucesso", "Categoria criada!");
        } catch (err) {
            Alert.alert("Erro", "Não foi possível criar a categoria.");
        } finally {
            setSavingCategory(false);
        }
    };

    const [showScanner, setShowScanner] = useState(false);
    const [scanned, setScanned] = useState(false);
    const [permission, requestPermission] = useCameraPermissions();

    const updateForm = (key: string, value: string) => setForm(prev => ({ ...prev, [key]: value }));

    const handleOpenScanner = async () => {
        if (!permission) {
            await requestPermission();
        } else if (!permission.granted) {
            const result = await requestPermission();
            if (!result.granted) {
                Alert.alert("Permissão", "É necessário dar permissão da câmera para usar o scanner.");
                return;
            }
        }
        setScanned(false);
        setShowScanner(true);
    };

    const handleBarCodeScanned = ({ type, data }: { type: string, data: string }) => {
        if (scanned) return;
        setScanned(true);
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
        updateForm('sku', data);
        setShowScanner(false);
    };

    const handlePickImage = async () => {
        Alert.alert(
            "Foto do Produto",
            "Escolha uma opção:",
            [
                {
                    text: "Tirar Foto",
                    onPress: async () => {
                        const permissionRole = await ImagePicker.requestCameraPermissionsAsync();
                        if (permissionRole.granted === false) {
                            Alert.alert('Ops', 'Você negou acesso à câmera!');
                            return;
                        }
                        const result = await ImagePicker.launchCameraAsync({
                            mediaTypes: ImagePicker.MediaTypeOptions.Images,
                            allowsEditing: true,
                            aspect: [1, 1],
                            quality: 0.6,
                            base64: true,
                        });
                        if (!result.canceled && result.assets[0].base64) {
                            setImage(`data:image/jpeg;base64,${result.assets[0].base64}`);
                        }
                    }
                },
                {
                    text: "Escolher da Galeria",
                    onPress: async () => {
                        const permissionRole = await ImagePicker.requestMediaLibraryPermissionsAsync();
                        if (permissionRole.granted === false) {
                            Alert.alert('Ops', 'Você negou acesso às fotos!');
                            return;
                        }
                        const result = await ImagePicker.launchImageLibraryAsync({
                            mediaTypes: ImagePicker.MediaTypeOptions.Images,
                            allowsEditing: true,
                            aspect: [1, 1],
                            quality: 0.6,
                            base64: true,
                        });
                        if (!result.canceled && result.assets[0].base64) {
                            setImage(`data:image/jpeg;base64,${result.assets[0].base64}`);
                        }
                    }
                },
                {
                    text: "Cancelar",
                    style: "cancel"
                }
            ]
        );
    };

    const handleSubmit = async () => {
        const name = String(form.name || '').trim();
        const priceStr = String(form.selling_price || '').trim().replace(',', '.');
        const qtyStr = controlStock ? String(form.quantity || '').trim() : '0';

        if (name === '' || priceStr === '' || (controlStock && qtyStr === '')) {
            Alert.alert("Atenção", "Preencha Nome, Preço e Quantidade.");
            return;
        }

        const payload = {
            ...form,
            name,
            selling_price: parseFloat(priceStr) || 0,
            cost_price: form.cost_price ? parseFloat(String(form.cost_price).replace(',', '.')) : null,
            quantity: controlStock ? (parseInt(qtyStr, 10) || 0) : 0,
            min_quantity: controlStock ? (parseInt(String(form.min_quantity || '5'), 10) || 0) : 0,
            menu_category_id: form.menu_category_id ? parseInt(form.menu_category_id) : null,
            image_base64: image
        };

        setLoading(true);
        try {
            await api.post('/inventory/items', payload);
            Alert.alert("Sucesso", `${labels.inventory_items?.split('/')[0] || 'Item'} adicionado! Deseja gerar o QR Code para etiqueta?`, [
                {
                    text: "Sim, Gerar QR",
                    onPress: () => router.push({
                        pathname: '/inventory/label',
                        params: { id: form.sku || form.name, type: 'inventory', title: form.name }
                    })
                },
                { text: "Agora não", onPress: () => router.back() }
            ]);
        } catch (error: any) {
            console.error("Erro ao salvar item:", error.response?.data || error.message);

            if (error.response?.status === 422) {
                const data = error.response.data;
                const msg = data.errors ? Object.values(data.errors).flat()[0] as string : data.message;
                Alert.alert("Dados Inválidos", msg || "Verifique os dados informados.");
            } else {
                Alert.alert("Erro", "Não foi possível salvar o item.");
            }
        } finally {
            setLoading(false);
        }
    };

    const itemLabel = labels.inventory_items?.split('/')[0] || 'Peça';

    if (showScanner) {
        return (
            <View style={styles.scannerContainer}>
                <CameraView
                    style={StyleSheet.absoluteFillObject}
                    onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
                    barcodeScannerSettings={{
                        barcodeTypes: ["qr", "ean13", "ean8", "code128", "pdf417", "upc_a", "upc_e"],
                    }}
                />
                <View style={styles.scannerOverlay}>
                    <View style={styles.scannerUnfocused}></View>
                    <View style={styles.scannerRow}>
                        <View style={styles.scannerUnfocused}></View>
                        <View style={styles.scannerFocused}>
                            <Ionicons name="scan-outline" size={200} color="rgba(255,255,255,0.4)" />
                        </View>
                        <View style={styles.scannerUnfocused}></View>
                    </View>
                    <View style={styles.scannerUnfocused}>
                        <Text style={styles.scannerHint}>Aponte para o código de barras</Text>
                        <TouchableOpacity style={styles.scannerCloseBtn} onPress={() => setShowScanner(false)}>
                            <Text style={styles.scannerCloseText}>Cancelar</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </View>
        );
    }

    return (
        <KeyboardAvoidingView behavior={Platform.OS === "ios" ? "padding" : "height"} style={{ flex: 1, backgroundColor: colors.background }}>
            <View style={[styles.header, { backgroundColor: colors.background }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>Novo {itemLabel}</Text>
                <View style={{ width: 40 }} />
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent}>
                <View style={[styles.card, { backgroundColor: colors.card, alignItems: 'center' }]}>
                    <TouchableOpacity style={[styles.imageContainer, { borderColor: colors.border }]} onPress={handlePickImage}>
                        {image ? (
                            <Image source={{ uri: image }} style={styles.productImage} />
                        ) : (
                            <View style={[styles.imagePlaceholder, { backgroundColor: colors.iconBg }]}>
                                <Ionicons name="camera" size={36} color="#7367F0" />
                                <Text style={[styles.imageText, { color: colors.subText }]}>Tirar Foto</Text>
                            </View>
                        )}
                        {image && (
                            <View style={styles.editImageBadge}>
                                <Ionicons name="pencil" size={14} color="#fff" />
                            </View>
                        )}
                    </TouchableOpacity>
                </View>

                <View style={[styles.card, { backgroundColor: colors.card }]}>
                    <View style={[styles.cardHeader, { borderBottomColor: colors.border }]}>
                        <Ionicons name="cube-outline" size={20} color="#7367F0" />
                        <Text style={[styles.cardTitle, { color: colors.text }]}>Dados do {itemLabel}</Text>
                    </View>

                    <CustomInput colors={colors} label={`Nome do ${itemLabel} *`} icon="pricetag-outline" value={form.name} onChangeText={(v: any) => updateForm('name', v)} placeholder="Ex: Filtro de Óleo" />
                    <CustomInput
                        colors={colors}
                        label="SKU / Código"
                        icon="barcode-outline"
                        value={form.sku}
                        onChangeText={(v: any) => updateForm('sku', v)}
                        placeholder="FIL-1234"
                        actionIcon="scan"
                        onActionPress={handleOpenScanner}
                    />

                    <View style={styles.inputGroup}>
                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
                            <Text style={[styles.label, { color: colors.subText, marginBottom: 0 }]}>Categoria</Text>
                            <TouchableOpacity onPress={() => setShowNewCategoryModal(true)}>
                                <Text style={{ color: '#7367F0', fontSize: 13, fontWeight: 'bold' }}>+ Nova Categoria</Text>
                            </TouchableOpacity>
                        </View>
                        <TouchableOpacity
                            style={[styles.inputWrapper, { backgroundColor: colors.iconBg, borderColor: colors.border, height: 52, paddingLeft: 10 }]}
                            onPress={() => setShowCategoryModal(true)}
                        >
                            <Ionicons name="apps-outline" size={18} color="#7367F0" />
                            <Text style={{ flex: 1, color: form.menu_category_id ? colors.text : colors.subText, marginLeft: 10, fontSize: 15 }}>
                                {form.menu_category_id ? (categories.find(c => c.id.toString() === form.menu_category_id)?.name || 'Selecionada') : 'Selecionar Categoria...'}
                            </Text>
                            <Ionicons name="chevron-down" size={18} color={colors.subText} style={{ marginRight: 15 }} />
                        </TouchableOpacity>
                    </View>

                    <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: controlStock ? 15 : 0, marginTop: 5 }}>
                        <Text style={[styles.label, { color: colors.subText, marginBottom: 0 }]}>Controlar Estoque e Quantidades?</Text>
                        <Switch
                            value={controlStock}
                            onValueChange={setControlStock}
                            trackColor={{ true: '#7367F0', false: colors.border }}
                        />
                    </View>

                    {controlStock && (
                        <Animated.View style={styles.row} entering={FadeInDown.duration(300)}>
                            <CustomInput colors={colors} label="Quantidade *" icon="layers-outline" value={form.quantity} onChangeText={(v: any) => updateForm('quantity', v)} placeholder="10" keyboardType="numeric" />
                            <CustomInput colors={colors} label="Estoque Mín." icon="alert-circle-outline" value={form.min_quantity} onChangeText={(v: any) => updateForm('min_quantity', v)} placeholder="5" keyboardType="numeric" />
                        </Animated.View>
                    )}
                </View>

                <View style={[styles.card, { backgroundColor: colors.card }]}>
                    <View style={[styles.cardHeader, { borderBottomColor: colors.border }]}>
                        <Ionicons name="cash-outline" size={20} color="#7367F0" />
                        <Text style={[styles.cardTitle, { color: colors.text }]}>Valores (R$)</Text>
                    </View>
                    <View style={styles.row}>
                        <CustomInput colors={colors} label="Custo (Compra)" icon="trending-down-outline" value={form.cost_price} onChangeText={(v: any) => updateForm('cost_price', v)} placeholder="0.00" keyboardType="numeric" />
                        <CustomInput colors={colors} label="Venda (Cliente) *" icon="trending-up-outline" value={form.selling_price} onChangeText={(v: any) => updateForm('selling_price', v)} placeholder="0.00" keyboardType="numeric" />
                    </View>
                </View>
            </ScrollView>

            {/* Modal de Seleção de Categoria */}
            <Modal visible={showCategoryModal} transparent animationType="slide">
                <View style={styles.modalOverlay}>
                    <View style={[styles.modalContent, { backgroundColor: colors.card }]}>
                        <View style={styles.modalHeader}>
                            <Text style={[styles.modalTitle, { color: colors.text }]}>Selecionar Categoria</Text>
                            <TouchableOpacity onPress={() => setShowCategoryModal(false)}>
                                <Ionicons name="close" size={24} color={colors.text} />
                            </TouchableOpacity>
                        </View>
                        <FlatList
                            data={[{ id: '', name: 'Sem Categoria' }, ...categories]}
                            keyExtractor={(item) => item.id.toString()}
                            renderItem={({ item }) => (
                                <TouchableOpacity
                                    style={[styles.categoryItem, { borderBottomColor: colors.border }]}
                                    onPress={() => {
                                        updateForm('menu_category_id', item.id.toString());
                                        setShowCategoryModal(false);
                                    }}
                                >
                                    <Text style={{ color: item.id.toString() === form.menu_category_id ? '#7367F0' : colors.text, fontWeight: item.id.toString() === form.menu_category_id ? '700' : '400' }}>
                                        {item.name}
                                    </Text>
                                    {item.id.toString() === form.menu_category_id && <Ionicons name="checkmark" size={18} color="#7367F0" />}
                                </TouchableOpacity>
                            )}
                        />
                    </View>
                </View>
            </Modal>

            {/* Modal de Nova Categoria */}
            <Modal visible={showNewCategoryModal} transparent animationType="fade">
                <View style={styles.modalOverlay}>
                    <View style={[styles.modalContent, { backgroundColor: colors.card, padding: 24, height: 'auto', maxHeight: 300 }]}>
                        <Text style={[styles.modalTitle, { color: colors.text, marginBottom: 20 }]}>Nova Categoria</Text>
                        <CustomInput
                            colors={colors}
                            label="Nome da Categoria"
                            icon="pricetag-outline"
                            value={newCategoryName}
                            onChangeText={setNewCategoryName}
                            placeholder="Ex: Bebidas"
                        />
                        <View style={{ flexDirection: 'row', gap: 12, marginTop: 20 }}>
                            <TouchableOpacity
                                style={{ flex: 1, height: 48, borderRadius: 12, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.iconBg }}
                                onPress={() => { setShowNewCategoryModal(false); setNewCategoryName(''); }}
                            >
                                <Text style={{ color: colors.text }}>Cancelar</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={{ flex: 1, height: 48, borderRadius: 12, alignItems: 'center', justifyContent: 'center', backgroundColor: '#7367F0' }}
                                onPress={handleAddNewCategory}
                                disabled={savingCategory}
                            >
                                {savingCategory ? <ActivityIndicator color="#fff" /> : <Text style={{ color: '#fff', fontWeight: 'bold' }}>Salvar</Text>}
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>

            <View style={[styles.footer, { backgroundColor: colors.card, borderTopColor: colors.border }]}>
                <TouchableOpacity activeOpacity={0.8} onPress={handleSubmit} disabled={loading}>
                    <LinearGradient colors={['#7367F0', '#CE9FFC']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }} style={styles.submitBtn}>
                        {loading ? <ActivityIndicator color="#fff" /> : (
                            <>
                                <Ionicons name="save-outline" size={22} color="#fff" />
                                <Text style={styles.submitBtnText}>Salvar no Estoque</Text>
                            </>
                        )}
                    </LinearGradient>
                </TouchableOpacity>
            </View>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 15 },
    backBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
    headerTitle: { fontSize: 20, fontWeight: 'bold' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    card: { borderRadius: 16, padding: 16, marginBottom: 16, elevation: 2 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 15, borderBottomWidth: 1, paddingBottom: 10 },
    cardTitle: { fontSize: 15, fontWeight: 'bold', marginLeft: 8 },
    inputGroup: { marginBottom: 15 },
    label: { fontSize: 12, fontWeight: '700', marginBottom: 6, marginLeft: 4, textTransform: 'uppercase' },
    inputWrapper: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderRadius: 12, height: 52 },
    inputIcon: { paddingHorizontal: 12 },
    input: { flex: 1, fontSize: 15, paddingRight: 12 },
    actionBtn: { paddingHorizontal: 15, height: '100%', justifyContent: 'center' },
    row: { flexDirection: 'row', gap: 12 },
    footer: { padding: 20, paddingBottom: Platform.OS === 'ios' ? 40 : 20, borderTopWidth: 1 },
    submitBtn: { height: 56, borderRadius: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
    submitBtnText: { color: '#fff', fontSize: 17, fontWeight: 'bold' },

    imageContainer: { width: 120, height: 120, borderRadius: 60, borderWidth: 2, borderStyle: 'dashed', overflow: 'hidden', justifyContent: 'center', alignItems: 'center', alignSelf: 'center', marginBottom: 10 },
    imagePlaceholder: { width: '100%', height: '100%', justifyContent: 'center', alignItems: 'center' },
    imageText: { fontSize: 12, marginTop: 4, fontWeight: '600' },
    productImage: { width: '100%', height: '100%', resizeMode: 'cover' },
    editImageBadge: { position: 'absolute', bottom: 10, right: 10, backgroundColor: 'rgba(0,0,0,0.6)', padding: 6, borderRadius: 15 },

    // Scanner Styles
    scannerContainer: { flex: 1, backgroundColor: '#000' },
    scannerOverlay: { position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 },
    scannerUnfocused: { flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'center', alignItems: 'center' },
    scannerRow: { flexDirection: 'row', flex: 3 },
    scannerFocused: { flex: 6, borderWidth: 2, borderColor: '#7367F0', borderRadius: 20, justifyContent: 'center', alignItems: 'center' },
    scannerHint: { color: '#fff', fontSize: 14, fontWeight: '600', textAlign: 'center', paddingHorizontal: 40, marginBottom: 20 },
    scannerCloseBtn: { backgroundColor: '#fff', paddingHorizontal: 20, paddingVertical: 12, borderRadius: 12 },
    scannerCloseText: { color: '#7367F0', fontWeight: 'bold' },

    // Modal Styles
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalContent: { borderTopLeftRadius: 25, borderTopRightRadius: 25, height: '60%', padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20, paddingHorizontal: 5 },
    modalTitle: { fontSize: 18, fontWeight: 'bold' },
    categoryItem: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 18, borderBottomWidth: 1 },
});
