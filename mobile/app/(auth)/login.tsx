import React, { useState, useEffect } from 'react';
import {
    View,
    Text,
    TextInput,
    Pressable,
    StyleSheet,
    Alert,
    KeyboardAvoidingView,
    Platform,
    StatusBar,
    ActivityIndicator
} from 'react-native';
import { useRouter } from 'expo-router';
import { useAuth } from '../../context/AuthContext';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import * as LocalAuthentication from 'expo-local-authentication';
import * as SecureStore from 'expo-secure-store';
import Animated, {
    useSharedValue,
    useAnimatedStyle,
    withSpring,
    withTiming,
    withDelay,
    withSequence
} from 'react-native-reanimated';
import Svg, { Path, Defs, LinearGradient as SvgLinearGradient, Stop } from 'react-native-svg';

export default function LoginScreen() {
    const { signIn, verify2FA } = useAuth();
    const router = useRouter();

    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [twoFactorCode, setTwoFactorCode] = useState('');
    const [show2FA, setShow2FA] = useState(false);
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const [biometricAvailable, setBiometricAvailable] = useState(false);
    const [biometricEnabled, setBiometricEnabled] = useState(false);
    const [biometricType, setBiometricType] = useState<'faceid' | 'fingerprint' | null>(null);
    const [biometricLoading, setBiometricLoading] = useState(false);

    // Animações
    const formOpacity = useSharedValue(0);
    const formTranslateY = useSharedValue(50);
    const logoScale = useSharedValue(1);
    const logoTranslateY = useSharedValue(0);
    const bioBtnScale = useSharedValue(1);

    useEffect(() => {
        formOpacity.value = withDelay(400, withTiming(1, { duration: 800 }));
        formTranslateY.value = withDelay(400, withSpring(0));
        checkBiometricAvailability();
    }, []);

    // Verifica hardware e preferência do usuário
    const checkBiometricAvailability = async () => {
        try {
            const hasHardware = await LocalAuthentication.hasHardwareAsync();
            const isEnrolled = await LocalAuthentication.isEnrolledAsync();
            const pref = await SecureStore.getItemAsync('useBiometrics');
            const token = await SecureStore.getItemAsync('userToken');

            if (hasHardware && isEnrolled) {
                setBiometricAvailable(true);
                const types = await LocalAuthentication.supportedAuthenticationTypesAsync();
                if (types.includes(LocalAuthentication.AuthenticationType.FACIAL_RECOGNITION)) {
                    setBiometricType('faceid');
                } else if (types.includes(LocalAuthentication.AuthenticationType.FINGERPRINT)) {
                    setBiometricType('fingerprint');
                }
            }

            const enabled = pref === 'true';
            setBiometricEnabled(enabled);

            // Disparo automático apenas se há token E biometria ativada
            if (enabled && hasHardware && isEnrolled && token) {
                setTimeout(() => triggerBiometricAuth(), 600);
            }
        } catch (e) {
            console.log('Biometric check error:', e);
        }
    };

    // Versão aprimorada com feedback visual
    const triggerBiometricAuth = async () => {
        if (biometricLoading) return;
        try {
            setBiometricLoading(true);
            bioBtnScale.value = withSequence(
                withSpring(0.93),
                withSpring(1.0)
            );

            const token = await SecureStore.getItemAsync('userToken');
            if (!token) {
                Alert.alert('Biometria', 'Faça login manualmente primeiro para ativar a biometria.');
                return;
            }

            const result = await LocalAuthentication.authenticateAsync({
                promptMessage: biometricType === 'faceid' ? 'Entrar com Face ID' : 'Entrar com sua digital',
                fallbackLabel: 'Usar senha',
                disableDeviceFallback: false,
            });

            if (result.success) {
                triggerSuccess();
            } else if (result.error && result.error !== 'user_cancel' && result.error !== 'system_cancel') {
                Alert.alert('Falha na biometria', 'Tente novamente ou use email e senha.');
            }
        } catch (e: any) {
            Alert.alert('Erro', e?.message ?? 'Falha ao acessar biometria.');
        } finally {
            setBiometricLoading(false);
        }
    };

    const animatedFormStyle = useAnimatedStyle(() => ({
        opacity: formOpacity.value,
        transform: [{ translateY: formTranslateY.value }]
    }));

    const animatedLogoStyle = useAnimatedStyle(() => ({
        transform: [
            { scale: logoScale.value },
            { translateY: logoTranslateY.value }
        ]
    }));

    const animatedBioStyle = useAnimatedStyle(() => ({
        transform: [{ scale: bioBtnScale.value }]
    }));

    async function handleLogin() {
        if (!email || !password) {
            Alert.alert('Campos vazios', 'Por favor, preencha seu email e senha.');
            return;
        }

        try {
            setLoading(true);
            const response = await signIn({
                email: email.trim(),
                password: password.trim()
            });

            if (response?.two_factor) {
                setShow2FA(true);
                setLoading(false);
                formTranslateY.value = withSpring(10);
                return;
            }

            triggerSuccess();

        } catch (error: any) {
            console.error("Login Error:", error);
            Alert.alert('Falha no Login', 'Verifique suas credenciais.');
            setLoading(false);
        }
    }

    async function handleVerify2FA() {
        if (twoFactorCode.length !== 6) {
            Alert.alert("Atenção", "O código deve ter 6 dígitos.");
            return;
        }

        try {
            setLoading(true);
            await verify2FA(email, twoFactorCode);
            triggerSuccess();
        } catch (error) {
            Alert.alert("Erro", "Código inválido ou expirado.");
            setLoading(false);
        }
    }

    function triggerSuccess() {
        setSuccess(true);
        formOpacity.value = withTiming(0, { duration: 400 });
        formTranslateY.value = withTiming(-20, { duration: 400 });
        logoTranslateY.value = withSpring(50);
        logoScale.value = withSpring(1.5);
    }

    return (
        <LinearGradient
            colors={['#ffffff', '#f2f2f7']}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.container}
        >
            <StatusBar barStyle="dark-content" />
            <KeyboardAvoidingView
                behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                style={styles.keyboardView}
            >
                <View style={styles.contentContainer}>
                    <View style={styles.headerContainer}>
                        <View style={styles.logoPlaceholder}>
                            {success ? (
                                <Animated.View style={animatedLogoStyle}>
                                    <Ionicons name="checkmark-circle" size={90} color="#7367F0" />
                                </Animated.View>
                            ) : (
                                <Svg width="120" height="120" viewBox="0 0 32 24" fill="none">
                                    <Defs>
                                        <SvgLinearGradient id="grad" x1="0" y1="0" x2="1" y2="1">
                                            <Stop offset="0" stopColor="#7367F0" stopOpacity="1" />
                                            <Stop offset="1" stopColor="#CE9FFC" stopOpacity="1" />
                                        </SvgLinearGradient>
                                    </Defs>
                                    <Path
                                        fillRule="evenodd"
                                        clipRule="evenodd"
                                        d="M16 0L4 6V18L16 24L28 18V10H22V15L16 18L10 15V9L16 6L22 9V3L16 0Z"
                                        fill="url(#grad)"
                                    />
                                    <Path opacity="0.1" d="M16 0L22 3V9L16 6V0Z" fill="#000" />
                                    <Path opacity="0.1" d="M16 24L16 18L22 15V10H28V18L16 24Z" fill="#000" />
                                </Svg>
                            )}
                        </View>
                        <Text style={styles.appName}>Ghotme ERP</Text>
                        {!success && (
                            <Text style={styles.tagline}>
                                {show2FA ? 'Segurança em Duas Etapas' : 'Gestão inteligente para sua empresa'}
                            </Text>
                        )}
                    </View>

                    {!success && (
                        <Animated.View style={[styles.formContainer, animatedFormStyle]}>
                            {!show2FA ? (
                                <>
                                    <View style={styles.inputWrapper}>
                                        <Text style={styles.label}>Email</Text>
                                        <View style={styles.inputContainer}>
                                            <Ionicons name="mail-outline" size={20} color="#7367F0" style={styles.inputIcon} />
                                            <TextInput
                                                style={styles.input}
                                                placeholder="admin@admin.com"
                                                placeholderTextColor="#999"
                                                value={email}
                                                onChangeText={setEmail}
                                                autoCapitalize="none"
                                                keyboardType="email-address"
                                            />
                                        </View>
                                    </View>

                                    <View style={styles.inputWrapper}>
                                        <Text style={styles.label}>Senha</Text>
                                        <View style={styles.inputContainer}>
                                            <Ionicons name="lock-closed-outline" size={20} color="#7367F0" style={styles.inputIcon} />
                                            <TextInput
                                                style={styles.input}
                                                placeholder="••••••••"
                                                placeholderTextColor="#999"
                                                value={password}
                                                onChangeText={setPassword}
                                                secureTextEntry={!showPassword}
                                            />
                                            <Pressable 
                                                onPress={() => setShowPassword(!showPassword)} 
                                                style={({ pressed }) => [{ opacity: pressed ? 0.5 : 1, padding: 5 }]}
                                            >
                                                <Ionicons name={showPassword ? "eye-off-outline" : "eye-outline"} size={20} color="#7367F0" />
                                            </Pressable>
                                        </View>
                                    </View>

                                    <Pressable 
                                        style={({ pressed }) => [
                                            styles.loginButton,
                                            { opacity: pressed || loading ? 0.85 : 1, transform: [{ scale: pressed ? 0.98 : 1 }] }
                                        ]} 
                                        onPress={handleLogin} 
                                        disabled={loading}
                                    >
                                        {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.loginButtonText}>Entrar agora</Text>}
                                    </Pressable>

                                    {/* Botão de Biometria Premium */}
                                    {biometricAvailable && biometricEnabled && (
                                        <Animated.View style={[animatedBioStyle, { marginTop: 16 }]}>
                                            <Pressable
                                                style={({ pressed }) => [
                                                    styles.biometricButton,
                                                    { opacity: pressed || biometricLoading ? 0.8 : 1 }
                                                ]}
                                                onPress={triggerBiometricAuth}
                                                disabled={biometricLoading}
                                            >
                                                {biometricLoading ? (
                                                    <ActivityIndicator size="small" color="#7367F0" />
                                                ) : (
                                                    <Ionicons
                                                        name={biometricType === 'faceid' ? 'scan-outline' : 'finger-print-outline'}
                                                        size={24}
                                                        color="#7367F0"
                                                    />
                                                )}
                                                <Text style={styles.biometricButtonText}>
                                                    {biometricType === 'faceid' ? 'Face ID' : 'Digital'}
                                                </Text>
                                            </Pressable>
                                        </Animated.View>
                                    )}

                                    {/* Link para ativar biometria caso disponível mas não configurado */}
                                    {biometricAvailable && !biometricEnabled && (
                                        <Pressable
                                            style={({ pressed }) => [{ marginTop: 24, alignItems: 'center', flexDirection: 'row', justifyContent: 'center', opacity: pressed ? 0.5 : 1 }]}
                                            onPress={() => Alert.alert(
                                                biometricType === 'faceid' ? '🔒 Face ID' : '🔒 Biometria',
                                                'Para usar biometria: faça login com email/senha, depois acesse Perfil → Segurança e ative a opção de biometria.',
                                                [{ text: 'Entendi' }]
                                            )}
                                        >
                                            <Ionicons name={biometricType === 'faceid' ? 'scan-outline' : 'finger-print-outline'} size={14} color="#aaa" style={{ marginRight: 6 }} />
                                            <Text style={{ color: '#aaa', fontSize: 13, fontWeight: '500' }}>
                                                {biometricType === 'faceid' ? 'Face ID disponível' : 'Digital disponível'} — ative nas configurações
                                            </Text>
                                        </Pressable>
                                    )}
                                </>
                            ) : (
                                <View>
                                    <View style={styles.inputWrapper}>
                                        <Text style={styles.label}>Código de 6 dígitos</Text>
                                        <View style={styles.inputContainer}>
                                            <Ionicons name="keypad-outline" size={20} color="#7367F0" style={styles.inputIcon} />
                                            <TextInput
                                                style={[styles.input, { letterSpacing: 10, textAlign: 'center', fontWeight: 'bold' }]}
                                                placeholder="000000"
                                                placeholderTextColor="#ccc"
                                                value={twoFactorCode}
                                                onChangeText={setTwoFactorCode}
                                                keyboardType="numeric"
                                                maxLength={6}
                                                autoFocus
                                            />
                                        </View>
                                        <Text style={{ color: '#8e8e93', fontSize: 13, textAlign: 'center', marginTop: 12 }}>
                                            Abra seu app de autenticação e digite o código.
                                        </Text>
                                    </View>

                                    <Pressable 
                                        style={({ pressed }) => [
                                            styles.loginButton,
                                            { opacity: pressed || loading ? 0.85 : 1, transform: [{ scale: pressed ? 0.98 : 1 }] }
                                        ]} 
                                        onPress={handleVerify2FA} 
                                        disabled={loading}
                                    >
                                        {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.loginButtonText}>Verificar Código</Text>}
                                    </Pressable>

                                    <Pressable
                                        style={({ pressed }) => [{ marginTop: 20, alignItems: 'center', opacity: pressed ? 0.6 : 1 }]}
                                        onPress={() => setShow2FA(false)}
                                    >
                                        <Text style={{ color: '#7367F0', fontSize: 15, fontWeight: '600' }}>Voltar ao Login</Text>
                                    </Pressable>
                                </View>
                            )}
                        </Animated.View>
                    )}
                </View>
            </KeyboardAvoidingView>
        </LinearGradient>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    keyboardView: { flex: 1, justifyContent: 'center' },
    contentContainer: { paddingHorizontal: 24, width: '100%', maxWidth: 400, alignSelf: 'center' },
    headerContainer: { alignItems: 'center', marginBottom: 40 },
    logoPlaceholder: {
        width: 120, height: 120,
        justifyContent: 'center', alignItems: 'center',
        marginBottom: 16,
    },
    appName: { fontSize: 34, fontWeight: '800', color: '#1c1c1e', letterSpacing: -0.5 },
    tagline: { fontSize: 17, color: '#8e8e93', marginTop: 8, textAlign: 'center', paddingHorizontal: 20, lineHeight: 22 },
    formContainer: { width: '100%' },
    inputWrapper: { marginBottom: 16 },
    label: { fontSize: 13, fontWeight: '600', color: '#1c1c1e', marginBottom: 8, marginLeft: 2, opacity: 0.8 },
    inputContainer: {
        flexDirection: 'row', alignItems: 'center',
        backgroundColor: '#f2f2f7', borderRadius: 12,
        paddingHorizontal: 16, height: 56,
        borderWidth: 1, borderColor: '#e5e5ea',
    },
    inputIcon: { marginRight: 12, opacity: 0.7 },
    input: { flex: 1, height: 56, color: '#000', fontSize: 17 },
    loginButton: {
        backgroundColor: '#7367F0', borderRadius: 12,
        height: 56, justifyContent: 'center', alignItems: 'center',
        marginTop: 12,
    },
    loginButtonText: { color: '#fff', fontSize: 17, fontWeight: '700', letterSpacing: -0.4 },
    biometricButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 8,
        backgroundColor: '#fff',
        borderRadius: 12,
        height: 52,
        borderWidth: 1,
        borderColor: '#7367F030',
    },
    biometricButtonText: {
        color: '#7367F0',
        fontSize: 16,
        fontWeight: '600',
    },
});
