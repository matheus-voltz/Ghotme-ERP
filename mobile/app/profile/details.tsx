import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, TextInput, Alert, ActivityIndicator } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';

export default function PersonalDataScreen() {
    const { user } = useAuth();
    const { colors } = useTheme();
    const router = useRouter();
    const [loading, setLoading] = useState(false);

    // Form states
    const [name, setName] = useState(user?.name || '');
    const [email, setEmail] = useState(user?.email || '');
    const [phone, setPhone] = useState(user?.contact_number || '');
    const [document, setDocument] = useState(user?.cpf_cnpj || '');
    const [city, setCity] = useState(user?.city || '');
    const [role, setRole] = useState(user?.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1)) : 'Funcionário');

    const handleSave = () => {
        setLoading(true);
        // Simulate API call
        setTimeout(() => {
            setLoading(false);
            Alert.alert("Sucesso", "Seus dados foram atualizados localmente.");
            router.back();
        }, 1500);
    };

    const renderInput = (label: string, value: string, setValue: (v: string) => void, icon: any, placeholder: string, keyboardType: any = "default") => (
        <View style={styles.inputGroup}>
            <Text style={[styles.label, { color: colors.subText }]}>{label}</Text>
            <View style={[styles.inputWrapper, { backgroundColor: colors.card, borderColor: colors.border }]}>
                <Ionicons name={icon} size={20} color={colors.primary} style={styles.inputIcon} />
                <TextInput
                    style={[styles.input, { color: colors.text }]}
                    value={value}
                    onChangeText={setValue}
                    placeholder={placeholder}
                    placeholderTextColor="#999"
                    keyboardType={keyboardType}
                />
            </View>
        </View>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.header}>
                <View style={styles.headerTop}>
                    <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={24} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.headerTitle}>Dados Pessoais</Text>
                    <View style={{ width: 24 }} />
                </View>
            </LinearGradient>

            <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
                <View style={styles.formCard}>
                    {renderInput("Nome Completo", name, setName, "person-outline", "Seu nome")}
                    {renderInput("E-mail", email, setEmail, "mail-outline", "seu@email.com", "email-address")}
                    {renderInput("Telefone / WhatsApp", phone, setPhone, "call-outline", "(00) 00000-0000", "phone-pad")}
                    {renderInput("CPF / CNPJ", document, setDocument, "document-text-outline", "000.000.000-00")}
                    {renderInput("Cidade", city, setCity, "location-outline", "Sua cidade")}
                    {renderInput("Cargo / Função", role, setRole, "briefcase-outline", "Ex: Mecânico Chefe")}
                </View>

                <TouchableOpacity
                    style={[styles.saveButton, { opacity: loading ? 0.7 : 1 }]}
                    onPress={handleSave}
                    disabled={loading}
                >
                    {loading ? (
                        <ActivityIndicator color="#fff" />
                    ) : (
                        <>
                            <Ionicons name="save-outline" size={20} color="#fff" style={{ marginRight: 8 }} />
                            <Text style={styles.saveButtonText}>Salvar Alterações</Text>
                        </>
                    )}
                </TouchableOpacity>
                <View style={{ height: 40 }} />
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
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
        fontSize: 20,
        fontWeight: 'bold',
        color: '#fff',
    },
    backBtn: {
        padding: 4,
    },
    content: {
        padding: 20,
    },
    formCard: {
        marginBottom: 20,
    },
    inputGroup: {
        marginBottom: 20,
    },
    label: {
        fontSize: 14,
        fontWeight: '600',
        marginBottom: 8,
        marginLeft: 4,
    },
    inputWrapper: {
        flexDirection: 'row',
        alignItems: 'center',
        borderWidth: 1,
        borderRadius: 12,
        height: 55,
        paddingHorizontal: 15,
    },
    inputIcon: {
        marginRight: 12,
    },
    input: {
        flex: 1,
        fontSize: 16,
    },
    saveButton: {
        backgroundColor: '#7367F0',
        height: 55,
        borderRadius: 14,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#7367F0',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 8,
        elevation: 4,
    },
    saveButtonText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: 'bold',
    },
});
