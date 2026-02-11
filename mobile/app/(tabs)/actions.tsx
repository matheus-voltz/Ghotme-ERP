import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ScrollView } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

export default function ActionsScreen() {
    const actions = [
        { id: 'new-os', title: 'Nova OS', icon: 'document-text-outline', color: '#7367F0', desc: 'Abrir ordem de serviço' },
        { id: 'new-client', title: 'Novo Cliente', icon: 'person-add-outline', color: '#28C76F', desc: 'Cadastrar cliente' },
        { id: 'new-vehicle', title: 'Novo Veículo', icon: 'car-sport-outline', color: '#00CFE8', desc: 'Cadastrar veículo' },
        { id: 'scan', title: 'Ler QR Code', icon: 'scan-outline', color: '#4B4B4B', desc: 'Buscar por etiqueta' },
        { id: 'calendar', title: 'Agenda', icon: 'calendar-outline', color: '#FF9F43', desc: 'Ver agendamentos' },
        { id: 'parts', title: 'Peças', icon: 'construct-outline', color: '#EA5455', desc: 'Consultar estoque' },
    ];

    return (
        <View style={styles.container}>
            <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                style={styles.header}
            >
                <Text style={styles.headerTitle}>Ações Rápidas</Text>
                <Text style={styles.headerSubtitle}>O que você deseja fazer hoje?</Text>
            </LinearGradient>

            <ScrollView contentContainerStyle={styles.gridContainer} showsVerticalScrollIndicator={false}>
                <View style={styles.grid}>
                    {actions.map((item) => (
                        <TouchableOpacity
                            key={item.id}
                            style={styles.card}
                            activeOpacity={0.9}
                            onPress={() => alert(`Ação: ${item.title}`)}
                        >
                            <View style={[styles.iconContainer, { backgroundColor: item.color + '20' }]}>
                                <Ionicons name={item.icon as any} size={28} color={item.color} />
                            </View>
                            <Text style={styles.cardTitle}>{item.title}</Text>
                            <Text style={styles.cardDesc}>{item.desc}</Text>
                        </TouchableOpacity>
                    ))}
                </View>
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#f8f9fa',
    },
    header: {
        paddingTop: 60,
        paddingBottom: 30,
        paddingHorizontal: 24,
        borderBottomLeftRadius: 30,
        borderBottomRightRadius: 30,
        alignItems: 'center',
    },
    headerTitle: {
        fontSize: 24,
        fontWeight: 'bold',
        color: '#fff',
    },
    headerSubtitle: {
        fontSize: 14,
        color: 'rgba(255,255,255,0.9)',
        marginTop: 5,
    },
    gridContainer: {
        padding: 20,
        paddingBottom: 100, // Space for tab bar
    },
    grid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        justifyContent: 'space-between',
    },
    card: {
        width: '48%', // 2 columns
        backgroundColor: '#fff',
        borderRadius: 20,
        padding: 20,
        marginBottom: 16,
        alignItems: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 3,
    },
    iconContainer: {
        width: 60,
        height: 60,
        borderRadius: 30,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 12,
    },
    cardTitle: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#333',
        marginBottom: 4,
        textAlign: 'center',
    },
    cardDesc: {
        fontSize: 12,
        color: '#888',
        textAlign: 'center',
    },
});
