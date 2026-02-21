import React, { useState, useEffect } from 'react';
import { View, Text, ScrollView, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native';
import { Image } from 'expo-image';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../../services/api';
import { useAuth } from '../../../context/AuthContext';
import { useTheme } from '../../../context/ThemeContext';

export default function ContactsScreen() {
    const router = useRouter();
    const { type } = useLocalSearchParams<{ type: 'support' | 'team' }>();
    const { user } = useAuth();
    const { colors } = useTheme();
    const [contacts, setContacts] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchContacts();
    }, []);

    const fetchContacts = async () => {
        try {
            const response = await api.get('/chat/contacts');
            setContacts(response.data);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const isSupportType = type === 'support';
    const filteredContacts = contacts.filter(c => {
        const isSuper = c.role === 'super_admin' || c.is_support;
        return isSupportType ? isSuper : !isSuper;
    });

    const renderContact = (item: any) => (
        <TouchableOpacity
            key={item.id}
            style={[styles.contactCard, { backgroundColor: colors.card }]}
            onPress={() => router.push({
                pathname: '/chat/messages',
                params: {
                    userId: item.id,
                    name: item.name,
                    photo: item.profile_photo_url
                }
            })}
        >
            <View style={styles.avatarContainer}>
                {item.profile_photo_url ? (
                    <Image
                        source={{ uri: item.profile_photo_url }}
                        style={styles.avatar}
                        contentFit="cover"
                        transition={200}
                    />
                ) : (
                    <View style={[styles.avatarPlaceholder, { backgroundColor: isSupportType ? '#7367F020' : colors.primary + '20' }]}>
                        <Text style={[styles.avatarText, { color: isSupportType ? '#7367F0' : colors.primary }]}>
                            {item.name.charAt(0).toUpperCase()}
                        </Text>
                    </View>
                )}
                {item.is_online && <View style={styles.onlineBadge} />}
            </View>
            <View style={styles.info}>
                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                    <Text style={[styles.name, { color: colors.text }]}>{item.name}</Text>
                    {isSupportType && (
                        <View style={styles.verifiedBadge}>
                            <Ionicons name="checkmark-circle" size={14} color="#7367F0" />
                        </View>
                    )}
                </View>
                <Text style={[styles.role, { color: isSupportType ? '#7367F0' : colors.subText, fontWeight: isSupportType ? '600' : '400' }]}>
                    {isSupportType ? 'Suporte Especializado' : (item.role || 'Colaborador')}
                </Text>
            </View>
            {item.unread_count > 0 && (
                <View style={styles.unreadBadge}>
                    <Text style={styles.unreadText}>{item.unread_count}</Text>
                </View>
            )}
            <Ionicons name="chevron-forward" size={20} color={colors.subText} style={{ marginLeft: 10 }} />
        </TouchableOpacity>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <View style={[styles.header, { backgroundColor: colors.card }]}>
                <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                    <Ionicons name="chevron-back" size={28} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>
                    {isSupportType ? 'Suporte Ghotme' : 'Equipe da Empresa'}
                </Text>
                <View style={{ width: 40 }} />
            </View>

            {loading ? (
                <View style={styles.center}>
                    <ActivityIndicator size="large" color={colors.primary} />
                </View>
            ) : (
                <ScrollView contentContainerStyle={styles.list} showsVerticalScrollIndicator={false}>
                    <View style={styles.sectionHeader}>
                        <Ionicons
                            name={isSupportType ? "headset-outline" : "people-outline"}
                            size={18}
                            color={isSupportType ? "#7367F0" : colors.primary}
                        />
                        <Text style={[styles.sectionTitle, { color: isSupportType ? "#7367F0" : colors.primary }]}>
                            {isSupportType ? 'FALE COM UM ESPECIALISTA' : 'MEMBROS DA EQUIPE'}
                        </Text>
                    </View>

                    {filteredContacts.length > 0 ? (
                        filteredContacts.map(c => renderContact(c))
                    ) : (
                        <View style={styles.empty}>
                            <Ionicons
                                name={isSupportType ? "headset-outline" : "people-outline"}
                                size={48}
                                color={colors.subText + '44'}
                            />
                            <Text style={[styles.emptyText, { color: colors.subText, marginTop: 10 }]}>
                                {isSupportType ? 'Nenhum atendente disponível.' : 'Nenhum membro na equipe.'}
                            </Text>
                        </View>
                    )}
                </ScrollView>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 15, elevation: 2 },
    backBtn: { width: 40 },
    headerTitle: { fontSize: 20, fontWeight: 'bold' },
    list: { padding: 20 },
    sectionHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 15, marginLeft: 5 },
    sectionTitle: { fontSize: 12, fontWeight: 'bold', marginLeft: 8, letterSpacing: 1, color: '#7367F0' },
    contactCard: { flexDirection: 'row', alignItems: 'center', padding: 15, borderRadius: 16, marginBottom: 12, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 5, elevation: 1 },
    avatarContainer: { position: 'relative' },
    avatar: { width: 52, height: 52, borderRadius: 26 },
    avatarPlaceholder: { width: 52, height: 52, borderRadius: 26, justifyContent: 'center', alignItems: 'center' },
    avatarText: { fontSize: 22, fontWeight: 'bold' },
    onlineBadge: { position: 'absolute', bottom: 2, right: 2, width: 12, height: 12, borderRadius: 6, backgroundColor: '#28C76F', borderWidth: 2, borderColor: '#fff' },
    info: { flex: 1, marginLeft: 15 },
    name: { fontSize: 16, fontWeight: '700' },
    role: { fontSize: 12, marginTop: 2 },
    verifiedBadge: { marginLeft: 5 },
    unreadBadge: { backgroundColor: '#EA5455', minWidth: 20, height: 20, borderRadius: 10, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 6 },
    unreadText: { color: '#fff', fontSize: 10, fontWeight: 'bold' },
    empty: { alignItems: 'center', justifyContent: 'center', marginTop: 40, paddingVertical: 20 },
    emptyText: { textAlign: 'center', fontSize: 14, marginVertical: 10, opacity: 0.6 }
});