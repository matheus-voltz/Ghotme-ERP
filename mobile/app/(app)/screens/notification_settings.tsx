import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Switch, ActivityIndicator, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { useTheme } from '../../../context/ThemeContext';
import api from '../../../services/api';

export default function NotificationSettingsScreen() {
    const { colors } = useTheme();
    const router = useRouter();

    const [loading, setLoading] = useState(true);
    const [preferences, setPreferences] = useState({
        new_os: true,
        chat_messages: true,
        system_alerts: true,
        budget_updates: true,
    });

    useEffect(() => {
        fetchPreferences();
    }, []);

    const fetchPreferences = async () => {
        try {
            const response = await api.get('/notifications/preferences');
            if (response.data) {
                setPreferences({
                    new_os: response.data.new_os ?? true,
                    chat_messages: response.data.chat_messages ?? true,
                    system_alerts: response.data.system_alerts ?? true,
                    budget_updates: response.data.budget_updates ?? true,
                });
            }
        } catch (error) {
            console.error('Erro ao buscar preferências:', error);
            Alert.alert('Erro', 'Não foi possível carregar as preferências.');
        } finally {
            setLoading(false);
        }
    };

    const togglePreference = async (key: string, value: boolean) => {
        // Optimistic UI update
        setPreferences(prev => ({ ...prev, [key]: value }));

        try {
            await api.post('/notifications/preferences', { key, value });
        } catch (error) {
            console.error('Erro ao salvar preferência:', error);
            Alert.alert('Erro', 'Falha ao salvar configuração. Tente novamente.');
            // Revert if error
            setPreferences(prev => ({ ...prev, [key]: !value }));
        }
    };

    const renderToggle = (key: keyof typeof preferences, title: string, subtitle: string, icon: any) => (
        <View style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <View style={styles.iconContainer}>
                <Ionicons name={icon} size={24} color="#7367F0" />
            </View>
            <View style={styles.textContainer}>
                <Text style={[styles.title, { color: colors.text }]}>{title}</Text>
                <Text style={[styles.subtitle, { color: colors.subText }]}>{subtitle}</Text>
            </View>
            <Switch
                trackColor={{ false: "#d1d1d1", true: "#7367F0" }}
                thumbColor={"#fff"}
                ios_backgroundColor="#d1d1d1"
                onValueChange={(val) => togglePreference(key, val)}
                value={preferences[key]}
            />
        </View>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <View style={[styles.header, { backgroundColor: colors.card, borderBottomColor: colors.border }]}>
                <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
                    <Ionicons name="arrow-back" size={24} color={colors.text} />
                </TouchableOpacity>
                <Text style={[styles.headerTitle, { color: colors.text }]}>Gerenciar Alertas</Text>
                <View style={{ width: 34 }} />
            </View>

            {loading ? (
                <View style={styles.loadingContainer}>
                    <ActivityIndicator size="large" color="#7367F0" />
                </View>
            ) : (
                <View style={styles.content}>
                    <Text style={[styles.sectionHeader, { color: colors.text }]}>Quais notificações deseja receber?</Text>

                    {renderToggle(
                        'new_os',
                        'Novas Ordens de Serviço',
                        'Avisar quando uma nova O.S for criada ou atribuída a você.',
                        'clipboard-outline'
                    )}

                    {renderToggle(
                        'chat_messages',
                        'Mensagens no Chat',
                        'Alertas de novas mensagens diretas ou em grupo.',
                        'chatbubbles-outline'
                    )}

                    {renderToggle(
                        'budget_updates',
                        'Aprovações de Orçamento',
                        'Quando um cliente aprova ou recusa um orçamento pelo link.',
                        'cash-outline'
                    )}

                    {renderToggle(
                        'system_alerts',
                        'Alertas do Sistema',
                        'Avisos de sistema, relatórios diários de fechamento e dicas Ghotme.',
                        'information-circle-outline'
                    )}

                    <Text style={[styles.footerText, { color: colors.subText }]}>
                        Desativar os alertas não apaga o histórico de notificações e mensagens de dentro do aplicativo.
                    </Text>
                </View>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: 20,
        paddingTop: 50,
        borderBottomWidth: 1
    },
    backBtn: { padding: 5 },
    headerTitle: { fontSize: 20, fontWeight: 'bold' },
    content: { padding: 20 },
    sectionHeader: {
        fontSize: 16,
        fontWeight: '600',
        marginBottom: 20,
        marginLeft: 5
    },
    card: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 15,
        borderRadius: 12,
        marginBottom: 15,
        borderWidth: 1,
    },
    iconContainer: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: 'rgba(115, 103, 240, 0.1)',
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 15
    },
    textContainer: { flex: 1, paddingRight: 10 },
    title: { fontSize: 16, fontWeight: '500', marginBottom: 4 },
    subtitle: { fontSize: 13, lineHeight: 18 },
    loadingContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    footerText: {
        fontSize: 12,
        textAlign: 'center',
        marginTop: 20,
        paddingHorizontal: 10,
        opacity: 0.8
    }
});
