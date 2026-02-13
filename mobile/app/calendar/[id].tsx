import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, Alert } from 'react-native';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useTheme } from '../../context/ThemeContext';

export default function EventDetailsScreen() {
    const router = useRouter();
    const { colors } = useTheme();
    const { id, title, start, description, color } = useLocalSearchParams();

    const formattedTime = (start as string)?.split(' ')[1]?.substring(0, 5) || '08:00';
    const formattedDate = (start as string)?.split(' ')[0]?.split('-').reverse().join('/') || '--/--/----';

    const handleDelete = () => {
        Alert.alert(
            "Excluir Agendamento",
            "Tem certeza que deseja remover este compromisso?",
            [
                { text: "Cancelar", style: "cancel" },
                { 
                    text: "Sim, Excluir", 
                    style: "destructive", 
                    onPress: async () => {
                        try {
                            // api.delete(`/calendar/events/${id}`);
                            Alert.alert("Sucesso", "Agendamento removido.");
                            router.back();
                        } catch (e) {
                            Alert.alert("Erro", "Falha ao excluir.");
                        }
                    } 
                }
            ]
        );
    };

    return (
        <View style={{ flex: 1, backgroundColor: '#F3F4F6' }}>
            {/* Header com Gradiente */}
            <LinearGradient
                colors={[(color as string) || '#7367F0', '#CE9FFC']}
                style={styles.header}
            >
                <View style={styles.headerTop}>
                    <TouchableOpacity onPress={() => router.back()} style={styles.iconBtn}>
                        <Ionicons name="close" size={28} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.headerTitle}>Detalhes do Serviço</Text>
                    <TouchableOpacity onPress={handleDelete} style={styles.iconBtn}>
                        <Ionicons name="trash-outline" size={24} color="#fff" />
                    </TouchableOpacity>
                </View>

                <View style={styles.headerContent}>
                    <View style={styles.timeBadge}>
                        <Text style={styles.timeText}>{formattedTime}</Text>
                    </View>
                    <Text style={styles.eventTitle}>{title}</Text>
                </View>
            </LinearGradient>

            <ScrollView contentContainerStyle={styles.content}>
                {/* Card de Informações */}
                <View style={styles.infoCard}>
                    <View style={styles.infoRow}>
                        <View style={styles.infoIconWrapper}>
                            <Ionicons name="calendar" size={22} color="#7367F0" />
                        </View>
                        <View>
                            <Text style={styles.infoLabel}>Data Agendada</Text>
                            <Text style={styles.infoValue}>{formattedDate}</Text>
                        </View>
                    </View>

                    <View style={styles.divider} />

                    <View style={styles.infoRow}>
                        <View style={styles.infoIconWrapper}>
                            <Ionicons name="time" size={22} color="#7367F0" />
                        </View>
                        <View>
                            <Text style={styles.infoLabel}>Horário de Início</Text>
                            <Text style={styles.infoValue}>{formattedTime}</Text>
                        </View>
                    </View>
                </View>

                {/* Card de Descrição */}
                <View style={[styles.infoCard, { marginTop: 20 }]}>
                    <Text style={styles.infoLabel}>Observações e Detalhes</Text>
                    <Text style={styles.descriptionText}>
                        {description || "Nenhuma observação adicional foi registrada para este agendamento."}
                    </Text>
                </View>

                {/* Botão de Ação */}
                <TouchableOpacity style={styles.mainBtn} onPress={() => Alert.alert("Em breve", "Funcionalidade de check-in em breve!")}>
                    <Text style={styles.mainBtnText}>Iniciar Atendimento</Text>
                </TouchableOpacity>
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    header: { paddingTop: 60, paddingBottom: 40, paddingHorizontal: 20, borderBottomLeftRadius: 40, borderBottomRightRadius: 40 },
    headerTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 30 },
    headerTitle: { fontSize: 18, fontWeight: 'bold', color: '#fff' },
    iconBtn: { width: 40, height: 40, borderRadius: 20, backgroundColor: 'rgba(255,255,255,0.2)', alignItems: 'center', justifyContent: 'center' },
    headerContent: { alignItems: 'center' },
    timeBadge: { backgroundColor: '#fff', paddingHorizontal: 15, paddingVertical: 5, borderRadius: 20, marginBottom: 15 },
    timeText: { color: '#7367F0', fontWeight: 'bold', fontSize: 16 },
    eventTitle: { fontSize: 24, fontWeight: 'bold', color: '#fff', textAlign: 'center' },
    content: { padding: 20 },
    infoCard: { backgroundColor: '#fff', borderRadius: 20, padding: 20, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 10 },
    infoRow: { flexDirection: 'row', alignItems: 'center' },
    infoIconWrapper: { width: 45, height: 45, borderRadius: 12, backgroundColor: '#F3F4F6', alignItems: 'center', justifyContent: 'center', marginRight: 15 },
    infoLabel: { fontSize: 12, color: '#999', fontWeight: 'bold', textTransform: 'uppercase', marginBottom: 2 },
    infoValue: { fontSize: 16, color: '#333', fontWeight: '600' },
    divider: { height: 1, backgroundColor: '#F3F4F6', marginVertical: 15 },
    descriptionText: { fontSize: 15, color: '#666', lineHeight: 22, marginTop: 10 },
    mainBtn: { backgroundColor: '#7367F0', height: 60, borderRadius: 16, alignItems: 'center', justifyContent: 'center', marginTop: 30, marginBottom: 50 },
    mainBtnText: { color: '#fff', fontSize: 18, fontWeight: 'bold' }
});
