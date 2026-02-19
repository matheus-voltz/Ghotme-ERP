import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ScrollView } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';

export default function ActionsScreen() {
    const { colors } = useTheme();
    const { labels, niche } = useNiche();

    const getEntityIcon = () => {
        switch (niche) {
            case 'pet': return 'paw-outline';
            case 'electronics': return 'laptop-outline';
            default: return 'car-sport-outline';
        }
    };

    const actions = [
        { id: 'new', title: 'Nova OS', icon: 'document-text-outline', color: '#7367F0', desc: 'Abrir ordem de serviço' },
        { id: 'new-client', title: 'Novo Cliente', icon: 'person-add-outline', color: '#28C76F', desc: 'Cadastrar cliente' },
        { id: 'new-vehicle', title: labels.new_entity, icon: getEntityIcon(), color: '#00CFE8', desc: `Cadastrar ${labels.entity.toLowerCase()}` },
        { id: 'scan', title: 'Ler QR Code', icon: 'scan-outline', color: '#4B4B4B', desc: 'Buscar por etiqueta' },
        { id: 'calendar', title: 'Agenda', icon: 'calendar-outline', color: '#FF9F43', desc: 'Ver agendamentos' },
        { id: 'parts', title: labels.inventory_items?.split('/')[0] || 'Peças/Produtos', icon: 'cube-outline', color: '#EA5455', desc: 'Consultar estoque' },
    ];

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                style={styles.header}
            >
                <View>
                    <Text style={styles.headerTitle}>Ações Rápidas</Text>
                    <Text style={styles.headerSubtitle}>O que você deseja fazer hoje?</Text>
                </View>
            </LinearGradient>

            <ScrollView contentContainerStyle={styles.gridContainer} showsVerticalScrollIndicator={false}>
                <View style={styles.grid}>
                    {actions.map((item) => (
                        <TouchableOpacity
                            key={item.id}
                            style={[
                                styles.card,
                                {
                                    width: '47%',
                                    backgroundColor: colors.card,
                                    marginBottom: 20
                                }
                            ]}
                            activeOpacity={0.8}
                            onPress={() => {
                                if (item.id === 'new') {
                                    router.push('/os/create');
                                } else if (item.id === 'new-client') {
                                    router.push('/clients/create');
                                } else if (item.id === 'new-vehicle') {
                                    router.push('/vehicles/create');
                                } else if (item.id === 'calendar') {
                                    router.push('/calendar');
                                } else if (item.id === 'parts') {
                                    router.push('/inventory');
                                } else {
                                    alert(`Ação: ${item.title} (Em breve)`);
                                }
                            }}
                        >
                            <View style={[styles.iconContainer, { backgroundColor: item.color + '20' }]}>
                                <Ionicons name={item.icon as any} size={28} color={item.color} />
                            </View>
                            <Text style={[styles.cardTitle, { color: colors.text }]}>{item.title}</Text>
                            <Text style={[styles.cardDesc, { color: colors.subText }]}>{item.desc}</Text>
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
