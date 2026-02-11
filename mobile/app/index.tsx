import React, { useState } from 'react';
import {
    View,
    Text,
    TextInput,
    TouchableOpacity,
    StyleSheet,
    Alert,
    KeyboardAvoidingView,
    Platform
} from 'react-native';
import { Image } from 'expo-image';
import { useAuth } from '../context/AuthContext';
import { router } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';

// Replace with your actual logo URL served by your Laravel backend
// For Android Emulator: http://10.0.2.2:8000
// For iOS Simulator: http://localhost:8000
// For Physical Device: http://YOUR_PC_IP:8000
const LOGO_URL = 'http://10.0.0.118:8000/assets/img/front-pages/branding/logo-1.png';

export default function LoginScreen() {
    const { signIn } = useAuth();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);

    async function handleLogin() {
        if (!email || !password) {
            Alert.alert('Erro', 'Por favor, preencha todos os campos.');
            return;
        }

        try {
            setLoading(true);
            await signIn({ email, password });
            // Router replacement is handled inside signIn now, but to be safe:
            // router.replace('/(tabs)'); 
        } catch (error) {
            Alert.alert('Falha no Login', 'Verifique suas credenciais e tente novamente.');
            console.log(error);
        } finally {
            setLoading(false);
        }
    }

    return (
        <LinearGradient
            colors={['#f8f9fa', '#eef1f6']}
            style={styles.container}
        >
            <KeyboardAvoidingView
                behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                style={styles.keyboardView}
            >
                <View style={styles.formWrapper}>
                    <View style={styles.contentContainer}>
                        {/* Logo Section */}
                        <View style={styles.logoContainer}>
                            <Image
                                source={{ uri: LOGO_URL }}
                                style={styles.logo}
                                contentFit="contain"
                            />
                            <Text style={styles.subtitle}>Bem-vindo ao Ghotme 👋</Text>
                            <Text style={styles.description}>Faça login e comece a gerenciar</Text>
                        </View>

                        {/* Form Section */}
                        <View style={styles.inputWrapper}>
                            <Text style={styles.label}>Email ou Usuário</Text>
                            <View style={styles.inputContainer}>
                                <Ionicons name="mail-outline" size={20} color="#666" style={styles.inputIcon} />
                                <TextInput
                                    style={styles.input}
                                    placeholder="Seu email"
                                    placeholderTextColor="#aaa"
                                    value={email}
                                    onChangeText={setEmail}
                                    autoCapitalize="none"
                                    keyboardType="email-address"
                                />
                            </View>
                        </View>

                        <View style={styles.inputWrapper}>
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
                                <Text style={styles.label}>Senha</Text>
                                <TouchableOpacity onPress={() => Alert.alert('Ops', 'Funcionalidade em desenvolvimento!')}>
                                    <Text style={styles.forgotPassword}>Esqueceu a senha?</Text>
                                </TouchableOpacity>
                            </View>
                            <View style={styles.inputContainer}>
                                <Ionicons name="lock-closed-outline" size={20} color="#666" style={styles.inputIcon} />
                                <TextInput
                                    style={styles.input}
                                    placeholder="Sua senha"
                                    placeholderTextColor="#aaa"
                                    value={password}
                                    onChangeText={setPassword}
                                    secureTextEntry={!showPassword}
                                />
                                <TouchableOpacity onPress={() => setShowPassword(!showPassword)} style={{ padding: 5 }}>
                                    <Ionicons name={showPassword ? "eye-off-outline" : "eye-outline"} size={20} color="#666" />
                                </TouchableOpacity>
                            </View>
                        </View>

                        <TouchableOpacity
                            style={[styles.button, loading && styles.buttonDisabled]}
                            onPress={handleLogin}
                            disabled={loading}
                        >
                            {loading ? (
                                <Text style={styles.buttonText}>Entrando...</Text>
                            ) : (
                                <Text style={styles.buttonText}>Entrar</Text>
                            )}
                        </TouchableOpacity>

                        <View style={styles.footer}>
                            <Text style={styles.footerText}>Novo na plataforma? </Text>
                            <TouchableOpacity onPress={() => Alert.alert('Cadastro', 'Entre em contato com o administrador.')}>
                                <Text style={styles.linkText}>Crie uma conta</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </KeyboardAvoidingView>
        </LinearGradient>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
    },
    keyboardView: {
        flex: 1,
        justifyContent: 'center',
        padding: 20,
    },
    formWrapper: {
        width: '100%',
        maxWidth: 400,
        alignSelf: 'center',
    },
    contentContainer: {
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 24,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 10,
        elevation: 5,
    },
    logoContainer: {
        alignItems: 'center',
        marginBottom: 30,
    },
    logo: {
        width: 180,
        height: 60,
        marginBottom: 10,
    },
    subtitle: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#333',
        marginBottom: 5,
        textAlign: 'center',
    },
    description: {
        fontSize: 14,
        color: '#666',
        textAlign: 'center',
        marginBottom: 10,
    },
    inputWrapper: {
        marginBottom: 16,
    },
    label: {
        fontSize: 14,
        fontWeight: '600',
        color: '#333',
        marginBottom: 0,
    },
    inputContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#ddd',
        borderRadius: 8,
        paddingHorizontal: 12,
        backgroundColor: '#fff',
        height: 48,
    },
    inputIcon: {
        marginRight: 10,
    },
    input: {
        flex: 1,
        height: 48,
        color: '#333',
    },
    forgotPassword: {
        color: '#7367F0',
        fontSize: 12,
        fontWeight: '500',
    },
    button: {
        backgroundColor: '#7367F0', // Vuexy Primary Color
        borderRadius: 8,
        height: 48,
        justifyContent: 'center',
        alignItems: 'center',
        shadowColor: '#7367F0',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 4,
        marginTop: 10,
    },
    buttonDisabled: {
        opacity: 0.7,
    },
    buttonText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: 'bold',
    },
    footer: {
        flexDirection: 'row',
        justifyContent: 'center',
        marginTop: 20,
    },
    footerText: {
        color: '#666',
        fontSize: 14,
    },
    linkText: {
        color: '#7367F0',
        fontSize: 14,
        fontWeight: 'bold',
    },
});
