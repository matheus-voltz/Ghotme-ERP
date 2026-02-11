import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { useAuth } from '../../context/AuthContext';

export default function ProfileScreen() {
    const { user, signOut } = useAuth();

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <View style={styles.avatar}>
                    <Text style={styles.avatarText}>{user?.name?.charAt(0).toUpperCase()}</Text>
                </View>
                <Text style={styles.name}>{user?.name}</Text>
                <Text style={styles.email}>{user?.email}</Text>
            </View>

            <View style={styles.content}>
                <TouchableOpacity style={styles.item}>
                    <Text>Minha Conta</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.item}>
                    <Text>Configurações</Text>
                </TouchableOpacity>
                <TouchableOpacity style={[styles.item, styles.logoutButton]} onPress={signOut}>
                    <Text style={styles.logoutText}>Sair</Text>
                </TouchableOpacity>
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#fff',
    },
    header: {
        padding: 30,
        alignItems: 'center',
        backgroundColor: '#007bff',
    },
    avatar: {
        width: 80,
        height: 80,
        borderRadius: 40,
        backgroundColor: '#fff',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 10,
    },
    avatarText: {
        fontSize: 32,
        fontWeight: 'bold',
        color: '#007bff',
    },
    name: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#fff',
    },
    email: {
        color: '#eee',
        fontSize: 16,
    },
    content: {
        padding: 20,
    },
    item: {
        padding: 15,
        borderBottomWidth: 1,
        borderColor: '#eee',
        marginBottom: 10,
    },
    logoutButton: {
        marginTop: 20,
        backgroundColor: '#ffebee',
        borderWidth: 0,
        alignItems: 'center',
        borderRadius: 8,
    },
    logoutText: {
        color: '#d32f2f',
        fontWeight: 'bold',
    },
});
