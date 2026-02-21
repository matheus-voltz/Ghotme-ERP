import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ScrollView, Alert, StatusBar, ActivityIndicator, Modal, RefreshControl } from 'react-native';
import { useAuth } from '../../../context/AuthContext';
import { useTheme } from '../../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import * as ImagePicker from 'expo-image-picker';
import api from '../../../services/api';
import { useLanguage } from '../../../context/LanguageContext';

export default function ProfileScreen() {
    const { user, signOut, updateUser, refreshUser } = useAuth();
    const { theme, setTheme, colors, activeTheme } = useTheme();
    const router = useRouter();
    const [uploading, setUploading] = useState(false);

    // Language translations mapped via Context
    const { language, setLanguage, t } = useLanguage();
    const [languageModalVisible, setLanguageModalVisible] = useState(false);

    const availableLanguages: { code: 'pt-BR' | 'en' | 'es' | 'fr', name: string }[] = [
        { code: 'pt-BR', name: 'Português (Brasil)' },
        { code: 'en', name: 'English' },
        { code: 'es', name: 'Español' },
        { code: 'fr', name: 'Français' },
    ];

    const currentLanguageName = availableLanguages.find(l => l.code === language)?.name || 'Português (Brasil)';

    const handleUpdatePhoto = async () => {
        try {
            const result = await ImagePicker.launchImageLibraryAsync({
                mediaTypes: ImagePicker.MediaTypeOptions.Images,
                allowsEditing: true,
                aspect: [1, 1],
                quality: 0.5,
            });

            if (!result.canceled) {
                setUploading(true);
                const formData = new FormData();

                // @ts-ignore
                formData.append('photo', {
                    uri: result.assets[0].uri,
                    name: 'profile.jpg',
                    type: 'image/jpeg',
                });

                const response = await api.post('/user/profile-photo', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });

                if (response.data.success) {
                    await updateUser(response.data.user || { profile_photo_url: response.data.profile_photo_url });
                    Alert.alert("Sucesso", "Foto de perfil atualizada!");
                }
            }
        } catch (error) {
            console.error(error);
            Alert.alert("Erro", "Falha ao enviar a foto.");
        } finally {
            setUploading(false);
        }
    };

    const handleSignOut = () => {
        Alert.alert(
            "Sair",
            "Tem certeza que deseja sair da sua conta?",
            [
                { text: "Cancelar", style: "cancel" },
                { text: "Sair", style: "destructive", onPress: signOut }
            ]
        );
    };

    const handleChangeTheme = () => {
        Alert.alert(
            "Escolha o Tema",
            "Selecione a aparência do aplicativo",
            [
                { text: "Claro", onPress: () => setTheme('light') },
                { text: "Escuro", onPress: () => setTheme('dark') },
                { text: "Sistema", onPress: () => setTheme('system') },
                { text: "Cancelar", style: "cancel" }
            ]
        );
    };

    const renderSettingItem = (icon: any, title: string, subtitle?: string, onPress?: () => void) => (
        <TouchableOpacity style={styles.settingItem} onPress={onPress}>
            <View style={[styles.iconContainer, { backgroundColor: colors.iconBg }]}>
                <Ionicons name={icon} size={22} color={colors.primary} />
            </View>
            <View style={styles.settingTextContainer}>
                <Text style={[styles.settingTitle, { color: colors.text }]}>{title}</Text>
                {subtitle && <Text style={[styles.settingSubtitle, { color: colors.subText }]}>{subtitle}</Text>}
            </View>
            <Ionicons name="chevron-forward" size={20} color={colors.subText} />
        </TouchableOpacity >
    );

    const [stats, setStats] = useState<any>(null);

    useEffect(() => {
        fetchStats();
    }, []);

    const fetchStats = async () => {
        try {
            const response = await api.get('/dashboard/stats');
            setStats(response.data);
        } catch (error) {
            console.log("Error fetching profile stats:", error);
        }
    };

    const [refreshing, setRefreshing] = useState(false);
    const onRefresh = useCallback(async () => {
        setRefreshing(true);
        if (refreshUser) {
            await refreshUser();
        }
        await fetchStats();
        setRefreshing(false);
    }, [refreshUser]);

    const getCompletedToday = () => {
        if (!stats) return 0;
        return user?.role === 'admin'
            ? (stats.osStats?.finalized_today || 0)
            : (stats.stats?.completedToday || 0);
    };

    const getPending = () => {
        if (!stats) return 0;
        return user?.role === 'admin'
            ? (stats.osStats?.pending || 0)
            : (stats.stats?.pendingBudgets || 0);
    };

    const getEfficiency = () => {
        if (!stats) return '0%';
        if (user?.role === 'admin') {
            return (stats.monthlyProfitability || 0) + '%';
        }
        // Para mecânicos, podemos calcular eficiência baseada em ordens finalizadas vs total?
        // Por enquanto, vamos retornar um placeholder ou remover
        return '-';
    };

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <StatusBar barStyle={activeTheme === 'dark' ? "light-content" : "light-content"} backgroundColor="#7367F0" />

            <ScrollView
                contentContainerStyle={{ paddingBottom: 100 }}
                showsVerticalScrollIndicator={false}
                refreshControl={
                    <RefreshControl
                        refreshing={refreshing}
                        onRefresh={onRefresh}
                        colors={['#7367F0']}
                        tintColor="#7367F0"
                        progressViewOffset={10}
                    />
                }
            >
                {/* Header Section */}
                <LinearGradient
                    colors={['#7367F0', '#CE9FFC']}
                    start={{ x: 0, y: 0 }}
                    end={{ x: 1, y: 1 }}
                    style={styles.header}
                >
                    <View style={styles.profileHeaderContent}>
                        <View style={styles.avatarContainer}>
                            {user?.profile_photo_url ? (
                                <Image
                                    source={{ uri: user.profile_photo_url }}
                                    style={{ width: 100, height: 100, borderRadius: 50 }}
                                    contentFit="cover"
                                />
                            ) : (
                                <Text style={styles.avatarText}>
                                    {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
                                </Text>
                            )}
                            <TouchableOpacity style={styles.editBadge} onPress={handleUpdatePhoto} disabled={uploading}>
                                {uploading ? <ActivityIndicator size="small" color="#fff" /> : <Ionicons name="camera" size={16} color="#fff" />}
                            </TouchableOpacity>
                        </View>
                        <Text style={styles.name}>{user?.name || 'Usuário'}</Text>
                        <View style={styles.roleBadge}>
                            <Text style={styles.roleText}>{user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'Mecânico'}</Text>
                        </View>

                        <View style={styles.contactInfoRow}>
                            <Ionicons name="mail-outline" size={14} color="rgba(255,255,255,0.9)" />
                            <Text style={styles.email}>{user?.email || 'email@exemplo.com'}</Text>
                            <View style={{ width: 15 }} />
                            <Ionicons name="call-outline" size={14} color="rgba(255,255,255,0.9)" />
                            <Text style={styles.email}>{user?.contact_number || '(00) 00000-0000'}</Text>
                        </View>

                        <View style={styles.statsContainer}>
                            <View style={styles.statItem}>
                                <Text style={styles.statNumber}>{getCompletedToday()}</Text>
                                <Text style={styles.statLabel}>Hoje</Text>
                            </View>
                            <View style={styles.statDivider} />
                            <View style={styles.statItem}>
                                <Text style={styles.statNumber}>{getPending()}</Text>
                                <Text style={styles.statLabel}>Pendentes</Text>
                            </View>
                            <View style={styles.statDivider} />
                            <View style={styles.statItem}>
                                <Text style={styles.statNumber}>{getEfficiency()}</Text>
                                <Text style={styles.statLabel}>{user?.role === 'admin' ? 'Lucratividade' : 'Eficiência'}</Text>
                            </View>
                        </View>
                    </View>
                </LinearGradient>

                {/* Settings Section */}
                <View style={styles.sectionContainer}>
                    <Text style={[styles.sectionHeader, { color: colors.text }]}>Conta</Text>
                    <View style={[styles.sectionCard, { backgroundColor: colors.card, shadowColor: colors.text }]}>
                        {renderSettingItem("person-outline", t('personal_data'), "Alterar nome, telefone, cidade", () => router.push('/profile/details'))}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem(
                            "lock-closed-outline",
                            t('security'),
                            user?.two_factor_enabled ? "2FA Ativado" : "2FA Desativado",
                            () => router.push('/profile/security')
                        )}
                    </View>

                    <Text style={[styles.sectionHeader, { color: colors.text }]}>{t('preferences')}</Text>
                    <View style={[styles.sectionCard, { backgroundColor: colors.card, shadowColor: colors.text }]}>
                        {renderSettingItem("notifications-outline", t('notifications'), t('manage_alerts'), () => router.push('/screens/notification_settings'))}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem("moon-outline", t('theme'), getCurrentThemeLabel(theme, t), handleChangeTheme)}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem("language-outline", t('language'), currentLanguageName, () => setLanguageModalVisible(true))}
                    </View>

                    <Text style={[styles.sectionHeader, { color: colors.text }]}>{t('support')}</Text>
                    <View style={[styles.sectionCard, { backgroundColor: colors.card, shadowColor: colors.text }]}>
                        {renderSettingItem("help-circle-outline", t('help'), "Perguntas frequentes", () => router.push('/screens/faq'))}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem("chatbox-ellipses-outline", t('team_chat'), "Falar com o time", () => router.push({ pathname: '/chat/contacts', params: { type: 'team' } }))}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem("mail-outline", t('contact_us'), "Falar com o suporte", () => router.push({ pathname: '/chat/contacts', params: { type: 'support' } }))}
                    </View>

                    <TouchableOpacity style={[styles.logoutButton, { backgroundColor: activeTheme === 'dark' ? '#2f2b3a' : '#ffebee' }]} onPress={handleSignOut}>
                        <Ionicons name="log-out-outline" size={20} color="#EA5455" style={{ marginRight: 8 }} />
                        <Text style={styles.logoutText}>{t('logout')}</Text>
                    </TouchableOpacity>

                    <Text style={styles.versionText}>Versão 1.0.0</Text>
                </View>
            </ScrollView>

            <Modal
                visible={languageModalVisible}
                animationType="fade"
                transparent={true}
                onRequestClose={() => setLanguageModalVisible(false)}
            >
                <TouchableOpacity
                    style={styles.modalOverlay}
                    activeOpacity={1}
                    onPress={() => setLanguageModalVisible(false)}
                >
                    <View style={[styles.modalContent, { backgroundColor: colors.card }]}>
                        <Text style={[styles.modalTitle, { color: colors.text }]}>Selecione o Idioma</Text>
                        {availableLanguages.map((lang) => (
                            <TouchableOpacity
                                key={lang.code}
                                style={styles.languageOption}
                                onPress={() => {
                                    setLanguage(lang.code);
                                    setLanguageModalVisible(false);
                                }}
                            >
                                <Text style={[styles.languageText, { color: colors.text }]}>{lang.name}</Text>
                                {language === lang.code && (
                                    <Ionicons name="checkmark-circle" size={24} color="#28C76F" />
                                )}
                            </TouchableOpacity>
                        ))}
                    </View>
                </TouchableOpacity>
            </Modal>
        </View>
    );
}

function getCurrentThemeLabel(theme: string, t: any) {
    if (theme === 'light') return 'Claro';
    if (theme === 'dark') return 'Escuro';
    return 'Sistema';
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
    },
    header: {
        paddingTop: 60,
        paddingBottom: 30,
        borderBottomLeftRadius: 30,
        borderBottomRightRadius: 30,
        alignItems: 'center',
    },
    profileHeaderContent: {
        alignItems: 'center',
        width: '100%',
    },
    avatarContainer: {
        width: 100,
        height: 100,
        borderRadius: 50,
        backgroundColor: '#fff',
        justifyContent: 'center',
        alignItems: 'center',
        alignSelf: 'center',
        marginBottom: 16,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 5,
        elevation: 8,
        position: 'relative',
    },
    avatarText: {
        fontSize: 40,
        fontWeight: 'bold',
        color: '#7367F0',
    },
    editBadge: {
        position: 'absolute',
        bottom: 0,
        right: 0,
        backgroundColor: '#FF9F43',
        width: 32,
        height: 32,
        borderRadius: 16,
        justifyContent: 'center',
        alignItems: 'center',
        borderWidth: 3,
        borderColor: '#7367F0', // Matches header bg roughly
    },
    name: {
        fontSize: 24,
        fontWeight: 'bold',
        color: '#fff',
        marginBottom: 8,
    },
    roleBadge: {
        backgroundColor: 'rgba(255,255,255,0.2)',
        paddingHorizontal: 12,
        paddingVertical: 4,
        borderRadius: 20,
        marginBottom: 16,
    },
    roleText: {
        color: '#fff',
        fontSize: 12,
        fontWeight: 'bold',
        textTransform: 'uppercase',
    },
    contactInfoRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 24,
    },
    email: {
        fontSize: 13,
        color: 'rgba(255,255,255,0.8)',
        marginLeft: 5,
    },
    statsContainer: {
        flexDirection: 'row',
        backgroundColor: 'rgba(255,255,255,0.15)',
        borderRadius: 16,
        paddingVertical: 12,
        paddingHorizontal: 20,
        width: '85%',
        justifyContent: 'space-between',
    },
    statItem: {
        alignItems: 'center',
        flex: 1,
    },
    statNumber: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#fff',
    },
    statLabel: {
        fontSize: 12,
        color: 'rgba(255,255,255,0.8)',
        marginTop: 2,
    },
    statDivider: {
        width: 1,
        backgroundColor: 'rgba(255,255,255,0.3)',
        height: '80%',
        alignSelf: 'center',
    },
    sectionContainer: {
        padding: 24,
        marginTop: -10,
    },
    sectionHeader: {
        fontSize: 18,
        fontWeight: 'bold',
        marginBottom: 12,
        marginTop: 12,
        marginLeft: 4,
    },
    sectionCard: {
        borderRadius: 16,
        padding: 8, // Padding around items
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 2,
    },
    settingItem: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: 12,
        paddingHorizontal: 12,
    },
    iconContainer: {
        width: 40,
        height: 40,
        borderRadius: 12,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 16,
    },
    settingTextContainer: {
        flex: 1,
    },
    settingTitle: {
        fontSize: 16,
        fontWeight: '600',
    },
    settingSubtitle: {
        fontSize: 12,
        marginTop: 2,
    },
    separator: {
        height: 1,
        marginLeft: 68, // Align with text start
    },
    logoutButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        borderRadius: 16,
        paddingVertical: 16,
        marginTop: 30,
    },
    logoutText: {
        color: '#EA5455',
        fontWeight: 'bold',
        fontSize: 16,
    },
    versionText: {
        textAlign: 'center',
        color: '#ccc',
        fontSize: 12,
        marginTop: 20,
    },
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0, 0, 0, 0.5)',
        justifyContent: 'flex-end',
    },
    modalContent: {
        borderTopLeftRadius: 20,
        borderTopRightRadius: 20,
        padding: 24,
        paddingBottom: 40,
    },
    modalTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        marginBottom: 20,
        textAlign: 'center',
    },
    languageOption: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingVertical: 16,
        borderBottomWidth: 1,
        borderBottomColor: 'rgba(150, 150, 150, 0.1)',
    },
    languageText: {
        fontSize: 16,
    }
});
