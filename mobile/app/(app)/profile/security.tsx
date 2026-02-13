import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, Switch, ActivityIndicator } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../../context/AuthContext';
import { useTheme } from '../../../context/ThemeContext';
import { LinearGradient } from 'expo-linear-gradient';

export default function SecurityScreen() {
    const { user } = useAuth();
    const { colors, activeTheme } = useTheme();
    const router = useRouter();
    const [loading, setLoading] = useState(false);

    const twoFactorEnabled = !!user?.two_factor_enabled;

    const handleToggle2FA = () => {
        Alert.alert(
            "Autenticação de Dois Fatores",
            "Para sua maior segurança, a configuração inicial do 2FA (QR Code e códigos de recuperação) deve ser realizada através do nosso painel Web no computador.",
            [
                { text: "Entendi", style: "default" },
                { text: "Ir para o Web", onPress: () => Alert.alert("Link", "Acesse: https://Ghotme-ERP.com/user/profile") }
            ]
        );
    };

    const renderSecurityItem = (icon: any, title: string, subtitle: string, rightElement?: any, onPress?: () => void) => (
        <TouchableOpacity
            style={[styles.securityItem, { backgroundColor: colors.card, borderColor: colors.border }]}
            onPress={onPress}
            disabled={!onPress}
        >
            <View style={[styles.iconWrapper, { backgroundColor: colors.iconBg }]}>
                <Ionicons name={icon} size={22} color={colors.primary} />
            </View>
            <View style={styles.textContainer}>
                <Text style={[styles.itemTitle, { color: colors.text }]}>{title}</Text>
                <Text style={[styles.itemSubtitle, { color: colors.subText }]}>{subtitle}</Text>
            </View>
            {rightElement}
        </TouchableOpacity>
    );

    return (
        <View style={[styles.container, { backgroundColor: colors.background }]}>
            <LinearGradient colors={['#7367F0', '#CE9FFC']} style={styles.header}>
                <View style={styles.headerTop}>
                    <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={24} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.headerTitle}>Segurança</Text>
                    <View style={{ width: 24 }} />
                </View>
            </LinearGradient>

            <ScrollView contentContainerStyle={styles.content}>
                <Text style={[styles.sectionTitle, { color: colors.text }]}>Acesso e Proteção</Text>

                {renderSecurityItem(
                    "shield-checkmark-outline",
                    "Autenticação 2FA",
                    twoFactorEnabled ? "Ativado - Código de segurança ativo" : "Desativado - Aumente sua segurança",
                    <View style={styles.badgeContainer}>
                        <Text style={[styles.badgeText, { color: twoFactorEnabled ? '#28C76F' : '#EA5455' }]}>
                            {twoFactorEnabled ? "ATIVO" : "INATIVO"}
                        </Text>
                        <Switch
                            value={twoFactorEnabled}
                            onValueChange={handleToggle2FA}
                            trackColor={{ false: '#767577', true: '#7367F0' }}
                        />
                    </View>
                )}

                {renderSecurityItem(
                    "lock-closed-outline",
                    "Alterar Senha",
                    "Troque sua senha periodicamente",
                    <Ionicons name="chevron-forward" size={20} color={colors.subText} />,
                    () => Alert.alert("Aviso", "Por questões de segurança, a alteração de senha deve ser feita via painel Web ou pela opção 'Esqueci minha senha' no login.")
                )}

                {renderSecurityItem(
                    "finger-print-outline",
                    "Biometria",
                    "Acessar o app usando digital/face",
                    <Switch value={false} disabled />, // Mocked for now
                )}

                <View style={[styles.infoCard, { backgroundColor: activeTheme === 'dark' ? 'rgba(115,103,240,0.1)' : '#F0EFFF' }]}>
                    <Ionicons name="information-circle-outline" size={24} color="#7367F0" />
                    <Text style={[styles.infoText, { color: colors.text }]}>
                        A autenticação de dois fatores adiciona uma camada extra de segurança. Você precisará de um aplicativo como o Google Authenticator para entrar.
                    </Text>
                </View>
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: { paddingTop: 60, paddingBottom: 25, paddingHorizontal: 20, borderBottomLeftRadius: 30, borderBottomRightRadius: 30 },
    headerTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#fff' },
    backBtn: { padding: 4 },
    content: { padding: 20 },
    sectionTitle: { fontSize: 16, fontWeight: 'bold', marginBottom: 15, marginLeft: 5 },
    securityItem: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 15,
        borderRadius: 16,
        borderWidth: 1,
        marginBottom: 15,
        elevation: 2,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 5
    },
    iconWrapper: { width: 45, height: 45, borderRadius: 12, justifyContent: 'center', alignItems: 'center', marginRight: 15 },
    textContainer: { flex: 1 },
    itemTitle: { fontSize: 16, fontWeight: '600', marginBottom: 2 },
    itemSubtitle: { fontSize: 12 },
    badgeContainer: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    badgeText: { fontSize: 10, fontWeight: 'bold' },
    infoCard: { flexDirection: 'row', padding: 20, borderRadius: 16, marginTop: 20, gap: 12, alignItems: 'flex-start' },
    infoText: { flex: 1, fontSize: 13, lineHeight: 20, opacity: 0.8 }
});
