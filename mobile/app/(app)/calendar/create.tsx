import React, { useState } from 'react';
import { View, Text, TextInput, StyleSheet, TouchableOpacity, Alert, ActivityIndicator, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useTheme } from '../../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';

export default function CreateEventScreen() {
    const router = useRouter();
    const { colors } = useTheme();
    const params = useLocalSearchParams();

    // Se o usuário veio da agenda, pegamos a data que ele clicou
    const initialDate = params.date as string || new Date().toISOString().split('T')[0];

    const [loading, setLoading] = useState(false);
    const [title, setTitle] = useState('');
    const [date, setDate] = useState(initialDate);
    const [time, setTime] = useState('08:00');
    const [description, setDescription] = useState('');

    const handleSubmit = async () => {
        if (!title || !date || !time) {
            Alert.alert("Atenção", "Preencha o título, data e hora.");
            return;
        }

        setLoading(true);
        try {
            const startDateTime = `${date} ${time}:00`;

            await api.post('/calendar/events', {
                title: title,
                start: startDateTime,
                end: startDateTime,
                allDay: false,
                extendedProps: {
                    calendar: 'Business', // Valor padrão exigido pela sua validação
                    description: description
                }
            });

            Alert.alert("Sucesso! 📅", "Serviço agendado com sucesso!", [
                { text: "Ótimo", onPress: () => router.back() }
            ]);
        } catch (error: any) {
            console.error("Error creating event:", error);
            Alert.alert("Erro", "Não foi possível salvar o agendamento.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <KeyboardAvoidingView behavior={Platform.OS === "ios" ? "padding" : "height"} style={{ flex: 1, backgroundColor: colors.background }}>
            <View style={[styles.header, { backgroundColor: colors.background }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>Novo Agendamento</Text>
                <View style={{ width: 40 }} />
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent}>
                <View style={[styles.card, { backgroundColor: colors.card }]}>
                    <View style={styles.inputGroup}>
                        <Text style={[styles.label, { color: colors.subText }]}>O que será feito? *</Text>
                        <View style={[styles.inputWrapper, { backgroundColor: colors.iconBg, borderColor: colors.border }]}>
                            <Ionicons name="construct-outline" size={18} color="#7367F0" style={styles.inputIcon} />
                            <TextInput
                                style={[styles.input, { color: colors.text }]}
                                value={title} onChangeText={setTitle}
                                placeholder="Ex: Troca de Óleo - Honda Civic"
                                placeholderTextColor={colors.subText}
                            />
                        </View>
                    </View>

                    <View style={styles.row}>
                        <View style={[styles.inputGroup, { flex: 1, marginRight: 10 }]}>
                            <Text style={[styles.label, { color: colors.subText }]}>Data *</Text>
                            <View style={[styles.inputWrapper, { backgroundColor: colors.iconBg, borderColor: colors.border }]}>
                                <Ionicons name="calendar-outline" size={18} color="#7367F0" style={styles.inputIcon} />
                                <TextInput
                                    style={[styles.input, { color: colors.text }]}
                                    value={date} onChangeText={setDate}
                                    placeholder="YYYY-MM-DD"
                                    placeholderTextColor={colors.subText}
                                />
                            </View>
                        </View>
                        <View style={[styles.inputGroup, { flex: 1 }]}>
                            <Text style={[styles.label, { color: colors.subText }]}>Hora *</Text>
                            <View style={[styles.inputWrapper, { backgroundColor: colors.iconBg, borderColor: colors.border }]}>
                                <Ionicons name="time-outline" size={18} color="#7367F0" style={styles.inputIcon} />
                                <TextInput
                                    style={[styles.input, { color: colors.text }]}
                                    value={time} onChangeText={setTime}
                                    placeholder="08:00"
                                    placeholderTextColor={colors.subText}
                                />
                            </View>
                        </View>
                    </View>

                    <View style={styles.inputGroup}>
                        <Text style={[styles.label, { color: colors.subText }]}>Observações</Text>
                        <View style={[styles.inputWrapper, { height: 100, alignItems: 'flex-start', paddingTop: 10, backgroundColor: colors.iconBg, borderColor: colors.border }]}>
                            <Ionicons name="document-text-outline" size={18} color="#7367F0" style={styles.inputIcon} />
                            <TextInput
                                style={[styles.input, { height: '100%', color: colors.text }]}
                                value={description} onChangeText={setDescription}
                                placeholder="Detalhes adicionais..."
                                placeholderTextColor={colors.subText}
                                multiline
                            />
                        </View>
                    </View>
                </View>
            </ScrollView>

            <View style={[styles.footer, { backgroundColor: colors.card, borderTopColor: colors.border }]}>
                <TouchableOpacity activeOpacity={0.8} onPress={handleSubmit} disabled={loading}>
                    <LinearGradient colors={['#7367F0', '#CE9FFC']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }} style={styles.submitBtn}>
                        {loading ? <ActivityIndicator color="#fff" /> : (
                            <>
                                <Ionicons name="calendar-outline" size={22} color="#fff" />
                                <Text style={styles.submitBtnText}>Confirmar Agendamento</Text>
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
    scrollContent: { padding: 16 },
    card: { borderRadius: 16, padding: 16, elevation: 2 },
    inputGroup: { marginBottom: 20 },
    label: { fontSize: 13, fontWeight: '700', marginBottom: 8, textTransform: 'uppercase' },
    inputWrapper: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderRadius: 12, height: 55 },
    inputIcon: { paddingHorizontal: 12 },
    input: { flex: 1, fontSize: 16 },
    row: { flexDirection: 'row' },
    footer: { padding: 20, paddingBottom: Platform.OS === 'ios' ? 40 : 20, borderTopWidth: 1 },
    submitBtn: { height: 56, borderRadius: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
    submitBtnText: { color: '#fff', fontSize: 17, fontWeight: 'bold' }
});
