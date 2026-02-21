import React, { useState, useEffect } from 'react';
import {
    View,
    Text,
    TextInput,
    TouchableOpacity,
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
    withRepeat,
    withSequence,
    Easing
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

    // Animações
    const formOpacity = useSharedValue(0);
    const formTranslateY = useSharedValue(50);
    const logoScale = useSharedValue(0);
    const logoTranslateY = useSharedValue(20);

    useEffect(() => {
        formOpacity.value = withDelay(400, withTiming(1, { duration: 800 }));
        formTranslateY.value = withDelay(400, withSpring(0));

        // Entrada Suave (Saindo do "trampolim")
        logoScale.value = withTiming(1, {
            duration: 1000,
            easing: Easing.bezier(0.25, 0.1, 0.25, 1)
        });
        logoTranslateY.value = withTiming(0, {
            duration: 1000,
            easing: Easing.out(Easing.exp)
        });

        // Flutuação sutil (Parallax) em vez de pulso
        logoTranslateY.value = withDelay(1000, withRepeat(
            withSequence(
                withTiming(-6, { duration: 2500, easing: Easing.inOut(Easing.ease) }),
                withTiming(0, { duration: 2500, easing: Easing.inOut(Easing.ease) })
            ),
            -1,
            true
        ));

        // Tenta biometria automática após carregar
        setTimeout(autoBiometrics, 1000);
    }, []);

    const autoBiometrics = async () => {
        const useBiometrics = await SecureStore.getItemAsync('useBiometrics');
        if (useBiometrics === 'true') {
            checkBiometrics();
        }
    };

    const checkBiometrics = async () => {
        try {
            const hasHardware = await LocalAuthentication.hasHardwareAsync();
            const isEnrolled = await LocalAuthentication.isEnrolledAsync();

            if (!hasHardware) {
                Alert.alert("Erro", "Este dispositivo não suporta biometria.");
                return;
            }
            if (!isEnrolled) {
                Alert.alert("Biometria", "Nenhuma digital ou rosto cadastrado no sistema do celular.");
                return;
            }

            const useBiometrics = await SecureStore.getItemAsync('useBiometrics');
            const token = await SecureStore.getItemAsync('userToken');

            if (useBiometrics === 'true' && token) {
                const result = await LocalAuthentication.authenticateAsync({
                    promptMessage: 'Entrar com Biometria',
                    fallbackLabel: 'Usar Senha',
                    disableDeviceFallback: false
                });

                if (result.success) {
                    triggerSuccess();
                }
            } else if (useBiometrics !== 'true') {
                Alert.alert("Aviso", "Ative a biometria na tela de Segurança dentro do seu perfil primeiro.");
            }
        } catch (error) {
            console.error(error);
            Alert.alert("Erro", "Falha ao processar biometria.");
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
            colors={['#ffffff', '#f8f9fa']}
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
                    <Animated.View style={[styles.headerContainer, animatedLogoStyle]}>
                        <View style={styles.logoPlaceholder}>
                            {success ? (
                                <Ionicons name="checkmark-circle" size={90} color="#7367F0" />
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
                    </Animated.View>

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
                                            <TouchableOpacity onPress={() => setShowPassword(!showPassword)} style={{ padding: 5 }}>
                                                <Ionicons name={showPassword ? "eye-off-outline" : "eye-outline"} size={20} color="#7367F0" />
                                            </TouchableOpacity>
                                        </View>
                                    </View>

                                    <TouchableOpacity style={styles.loginButton} onPress={handleLogin} disabled={loading}>
                                        {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.loginButtonText}>ENTRAR AGORA</Text>}
                                    </TouchableOpacity>

                                    <TouchableOpacity
                                        style={{ marginTop: 20, alignItems: 'center', flexDirection: 'row', justifyContent: 'center' }}
                                        onPress={checkBiometrics}
                                    >
                                        <Ionicons name="finger-print-outline" size={20} color="#7367F0" style={{ marginRight: 8 }} />
                                        <Text style={{ color: '#7367F0', fontSize: 14, fontWeight: '600' }}>Usar Biometria</Text>
                                    </TouchableOpacity>
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
                                        <Text style={{ color: '#666', fontSize: 12, textAlign: 'center', marginTop: 10, opacity: 0.8 }}>
                                            Abra seu app de autenticação e digite o código.
                                        </Text>
                                    </View>

                                    <TouchableOpacity style={styles.loginButton} onPress={handleVerify2FA} disabled={loading}>
                                        {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.loginButtonText}>VERIFICAR CÓDIGO</Text>}
                                    </TouchableOpacity>

                                    <TouchableOpacity
                                        style={{ marginTop: 20, alignItems: 'center' }}
                                        onPress={() => setShow2FA(false)}
                                    >
                                        <Text style={{ color: '#7367F0', fontSize: 14, fontWeight: '600' }}>Voltar ao Login</Text>
                                    </TouchableOpacity>
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
    contentContainer: { paddingHorizontal: 30, width: '100%', maxWidth: 500, alignSelf: 'center' },
    headerContainer: { alignItems: 'center', marginBottom: 40 },
    logoPlaceholder: {
        width: 120, height: 120,
        justifyContent: 'center', alignItems: 'center',
        marginBottom: 10,
    },
    appName: { fontSize: 32, fontWeight: 'bold', color: '#333', letterSpacing: 1 },
    tagline: { fontSize: 16, color: '#666', marginTop: 5, textAlign: 'center' },
    formContainer: { width: '100%' },
    inputWrapper: { marginBottom: 20 },
    label: { fontSize: 12, fontWeight: 'bold', color: '#555', marginBottom: 8, marginLeft: 4, textTransform: 'uppercase', opacity: 0.9 },
    inputContainer: {
        flexDirection: 'row', alignItems: 'center',
        backgroundColor: '#fff', borderRadius: 16,
        paddingHorizontal: 18, height: 60,
        borderWidth: 1, borderColor: '#E0E0E0',
        shadowColor: '#000', shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05, shadowRadius: 5, elevation: 2,
    },
    inputIcon: { marginRight: 12 },
    input: { flex: 1, height: 60, color: '#333', fontSize: 16 },
    loginButton: {
        backgroundColor: '#7367F0', borderRadius: 16,
        height: 60, justifyContent: 'center', alignItems: 'center',
        marginTop: 10,
        shadowColor: '#7367F0', shadowOffset: { width: 0, height: 8 },
        shadowOpacity: 0.3, shadowRadius: 12, elevation: 6,
    },
    loginButtonText: { color: '#fff', fontSize: 16, fontWeight: 'bold', letterSpacing: 1.5 },
});
