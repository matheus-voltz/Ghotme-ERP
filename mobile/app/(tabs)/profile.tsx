import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ScrollView, Alert, StatusBar } from 'react-native';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';

export default function ProfileScreen() {
    const { user, signOut } = useAuth();
    const { theme, setTheme, colors, activeTheme } = useTheme();
    const router = useRouter();

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
        </TouchableOpacity>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <StatusBar barStyle={activeTheme === 'dark' ? "light-content" : "light-content"} backgroundColor="#7367F0" />

            <ScrollView contentContainerStyle={{ paddingBottom: 100 }} showsVerticalScrollIndicator={false}>
                {/* Header Section */}
                <LinearGradient
                    colors={['#7367F0', '#CE9FFC']}
                    start={{ x: 0, y: 0 }}
                    end={{ x: 1, y: 1 }}
                    style={styles.header}
                >
                    <View style={styles.profileHeaderContent}>
                        <View style={styles.avatarContainer}>
                            <Text style={styles.avatarText}>
                                {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
                            </Text>
                            <View style={styles.editBadge}>
                                <Ionicons name="camera" size={12} color="#fff" />
                            </View>
                        </View>
                        <Text style={styles.name}>{user?.name || 'Usuário'}</Text>
                        <Text style={styles.email}>{user?.email || 'email@exemplo.com'}</Text>

                        <View style={styles.statsContainer}>
                            <View style={styles.statItem}>
                                <Text style={styles.statNumber}>0</Text>
                                <Text style={styles.statLabel}>Ordens</Text>
                            </View>
                            <View style={styles.statDivider} />
                            <View style={styles.statItem}>
                                <Text style={styles.statNumber}>0</Text>
                                <Text style={styles.statLabel}>Pendentes</Text>
                            </View>
                            <View style={styles.statDivider} />
                            <View style={styles.statItem}>
                                <Text style={styles.statNumber}>0</Text>
                                <Text style={styles.statLabel}>Concluídas</Text>
                            </View>
                        </View>
                    </View>
                </LinearGradient>

                {/* Settings Section */}
                <View style={styles.sectionContainer}>
                    <Text style={[styles.sectionHeader, { color: colors.text }]}>Conta</Text>
                    <View style={[styles.sectionCard, { backgroundColor: colors.card, shadowColor: colors.text }]}>
                        {renderSettingItem("person-outline", "Dados Pessoais", "Alterar nome, email", () => Alert.alert('Em breve', 'Edição de perfil será implementada.'))}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem("lock-closed-outline", "Segurança", "Alterar senha, biometria", () => Alert.alert('Em breve', 'Segurança será implementada.'))}
                    </View>

                    <Text style={[styles.sectionHeader, { color: colors.text }]}>Preferências</Text>
                    <View style={[styles.sectionCard, { backgroundColor: colors.card, shadowColor: colors.text }]}>
                        {renderSettingItem("notifications-outline", "Notificações", "Gerenciar alertas")}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem("moon-outline", "Tema", getCurrentThemeLabel(theme), handleChangeTheme)}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem("language-outline", "Idioma", "Português (Brasil)")}
                    </View>

                    <Text style={[styles.sectionHeader, { color: colors.text }]}>Suporte</Text>
                    <View style={[styles.sectionCard, { backgroundColor: colors.card, shadowColor: colors.text }]}>
                        {renderSettingItem("help-circle-outline", "Ajuda", "Perguntas frequentes")}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem("chatbox-ellipses-outline", "Chat da Equipe", "Falar com o time", () => router.push('/chat/contacts'))}
                        <View style={[styles.separator, { backgroundColor: colors.border }]} />
                        {renderSettingItem("mail-outline", "Fale Conosco", "Enviar mensagem")}
                    </View>

                    <TouchableOpacity style={[styles.logoutButton, { backgroundColor: activeTheme === 'dark' ? '#2f2b3a' : '#ffebee' }]} onPress={handleSignOut}>
                        <Ionicons name="log-out-outline" size={20} color="#EA5455" style={{ marginRight: 8 }} />
                        <Text style={styles.logoutText}>Sair da Conta</Text>
                    </TouchableOpacity>

                    <Text style={styles.versionText}>Versão 1.0.0</Text>
                </View>
            </ScrollView>
        </View>
    );
}

function getCurrentThemeLabel(theme: string) {
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
        marginBottom: 4,
    },
    email: {
        fontSize: 14,
        color: 'rgba(255,255,255,0.8)',
        marginBottom: 24,
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
});
