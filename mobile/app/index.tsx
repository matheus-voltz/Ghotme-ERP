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
import { useAuth } from '../context/AuthContext';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import Animated, {
    useSharedValue,
    useAnimatedStyle,
    withSpring,
    withTiming,
    withDelay
} from 'react-native-reanimated';

export default function LoginScreen() {
    const { signIn, verify2FA, user } = useAuth();
    const router = useRouter();
    
    // States
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
    const logoScale = useSharedValue(0.5);
    const logoTranslateY = useSharedValue(0);

    useEffect(() => {
        formOpacity.value = withDelay(300, withTiming(1, { duration: 800 }));
        formTranslateY.value = withDelay(300, withSpring(0));
        logoScale.value = withSpring(1, { damping: 12 });
    }, []);

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
            const response = await signIn({ email, password });

            if (response?.two_factor) {
                // Ativar modo 2FA
                setShow2FA(true);
                setLoading(false);
                // Animação sutil para avisar a mudança
                formTranslateY.value = withSpring(10);
                return;
            }

            // Sucesso direto (sem 2FA)
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

        setTimeout(() => {
            router.replace('/(tabs)');
        }, 800);
    }

    return (
        <LinearGradient
            colors={['#7367F0', '#CE9FFC']}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.container}
        >
            <StatusBar barStyle="light-content" />
            <KeyboardAvoidingView
                behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                style={styles.keyboardView}
            >
                <View style={styles.contentContainer}>
                    {/* Header Animado */}
                    <Animated.View style={[styles.headerContainer, animatedLogoStyle]}>
                        <View style={styles.logoPlaceholder}>
                            {success ? (
                                <Ionicons name="checkmark-circle" size={70} color="#fff" />
                            ) : (
                                <Ionicons name={show2FA ? "shield-checkmark" : "cube-outline"} size={60} color="#fff" />
                            )}
                        </View>
                        <Text style={styles.appName}>Ghotme ERP</Text>
                        {!success && (
                            <Text style={styles.tagline}>
                                {show2FA ? 'Segurança em Duas Etapas' : 'Sincronizando sua oficina'}
                            </Text>
                        )}
                    </Animated.View>

                    {/* Formulário de Login ou 2FA */}
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
                                        {loading ? <ActivityIndicator color="#7367F0" /> : <Text style={styles.loginButtonText}>ENTRAR AGORA</Text>}
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
                                        <Text style={{color: '#fff', fontSize: 12, textAlign: 'center', marginTop: 10, opacity: 0.8}}>
                                            Abra seu app de autenticação e digite o código.
                                        </Text>
                                    </View>

                                    <TouchableOpacity style={styles.loginButton} onPress={handleVerify2FA} disabled={loading}>
                                        {loading ? <ActivityIndicator color="#7367F0" /> : <Text style={styles.loginButtonText}>VERIFICAR CÓDIGO</Text>}
                                    </TouchableOpacity>

                                    <TouchableOpacity 
                                        style={{marginTop: 20, alignItems: 'center'}} 
                                        onPress={() => setShow2FA(false)}
                                    >
                                        <Text style={{color: '#fff', fontSize: 14, fontWeight: '600'}}>Voltar ao Login</Text>
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
        width: 110, height: 110,
        backgroundColor: 'rgba(255,255,255,0.25)',
        borderRadius: 30,
        justifyContent: 'center', alignItems: 'center',
        marginBottom: 15,
        borderWidth: 2, borderColor: 'rgba(255,255,255,0.4)',
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.2, shadowRadius: 15,
    },
    appName: { fontSize: 34, fontWeight: 'bold', color: '#fff', letterSpacing: 1 },
    tagline: { fontSize: 16, color: 'rgba(255,255,255,0.9)', marginTop: 5 },
    formContainer: { width: '100%' },
    inputWrapper: { marginBottom: 20 },
    label: { fontSize: 12, fontWeight: 'bold', color: '#fff', marginBottom: 8, marginLeft: 4, textTransform: 'uppercase', opacity: 0.9 },
    inputContainer: {
        flexDirection: 'row', alignItems: 'center',
        backgroundColor: '#fff', borderRadius: 16,
        paddingHorizontal: 18, height: 60,
        shadowColor: '#000', shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1, shadowRadius: 10, elevation: 5,
    },
    inputIcon: { marginRight: 12 },
    input: { flex: 1, height: 60, color: '#1F2937', fontSize: 16 },
    loginButton: {
        backgroundColor: '#fff', borderRadius: 16,
        height: 60, justifyContent: 'center', alignItems: 'center',
        marginTop: 10,
        shadowColor: '#000', shadowOffset: { width: 0, height: 8 },
        shadowOpacity: 0.2, shadowRadius: 12, elevation: 8,
    },
    loginButtonText: { color: '#7367F0', fontSize: 16, fontWeight: 'bold', letterSpacing: 1.5 },
});
