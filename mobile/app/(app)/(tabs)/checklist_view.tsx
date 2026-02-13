import React, { useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../context/ThemeContext';

export default function ChecklistViewRedirect() {
    const { colors } = useTheme();

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <Ionicons name="car-sport" size={64} color="#7367F0" />
            <Text style={[styles.title, { color: colors.text }]}>Vistoria Visual</Text>
            <Text style={[styles.subtitle, { color: colors.subText }]}>
                Para realizar uma vistoria, selecione uma Ordem de Serviço em execução na tela inicial.
            </Text>

            <TouchableOpacity
                style={styles.button}
                onPress={() => router.replace('/(tabs)')}
            >
                <Text style={styles.buttonText}>Ir para Início</Text>
            </TouchableOpacity>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        padding: 30,
        textAlign: 'center'
    },
    title: {
        fontSize: 24,
        fontWeight: 'bold',
        marginTop: 20,
        marginBottom: 10
    },
    subtitle: {
        fontSize: 16,
        textAlign: 'center',
        marginBottom: 30,
        lineHeight: 24
    },
    button: {
        backgroundColor: '#7367F0',
        paddingHorizontal: 30,
        paddingVertical: 15,
        borderRadius: 12
    },
    buttonText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 16
    }
});
