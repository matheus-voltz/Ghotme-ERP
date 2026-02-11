import React, { useState } from 'react';
import {
    View,
    Text,
    TextInput,
    TouchableOpacity,
    StyleSheet,
    Alert,
    KeyboardAvoidingView,
    Platform,
    StatusBar
} from 'react-native';
import { Image } from 'expo-image';
import { useAuth } from '../context/AuthContext';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';

// Ensure this matches your computer's local IP
const LOGO_URL = 'http://10.0.0.118:8000/assets/img/front-pages/branding/logo-1.png';

export default function LoginScreen() {
    const { signIn } = useAuth();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);

    async function handleLogin() {
        if (!email || !password) {
            Alert.alert('Campos vazios', 'Por favor, preencha seu email e senha.');
            return;
        }

        try {
            setLoading(true);
            await signIn({ email, password });
        } catch (error: any) {
            console.error("Login Error details:", error);
            let errorMessage = 'Verifique suas credenciais e tente novamente.';

            if (error.message === 'Network Error') {
                errorMessage = 'Erro de conexão. Verifique se o celular está na mesma rede Wi-Fi do computador e se o IP está correto.';
            } else if (error.response) {
                // Server responded with a status code outside the 2xx range
                errorMessage = error.response.data.message || 'Erro no servidor.';
            }

            Alert.alert('Falha no Login', errorMessage);
        } finally {
            setLoading(false);
        }
    }

    return (
        <LinearGradient
            colors={['#7367F0', '#CE9FFC']} // Vuexy-like purple gradient
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
                    {/* Header Section */}
                    <View style={styles.headerContainer}>
                        {/* Fallback icon if image fails or while loading */}
                        <View style={styles.logoPlaceholder}>
                            <Ionicons name="cube-outline" size={60} color="#fff" />
                        </View>
                        <Text style={styles.appName}>Ghotme ERP</Text>
                        <Text style={styles.tagline}>Gerencie seu negócio com estilo</Text>
                    </View>

                    {/* Form Section */}
                    <View style={styles.formContainer}>
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

                        <TouchableOpacity
                            style={styles.forgotPasswordButton}
                            onPress={() => Alert.alert('Ops', 'Entre em contato com o suporte para redefinir.')}
                        >
                            <Text style={styles.forgotPasswordText}>Esqueceu a senha?</Text>
                        </TouchableOpacity>

                        <TouchableOpacity
                            style={[styles.loginButton, loading && styles.buttonDisabled]}
                            onPress={handleLogin}
                            disabled={loading}
                        >
                            {loading ? (
                                <Text style={styles.loginButtonText}>Entrando...</Text>
                            ) : (
                                <Text style={styles.loginButtonText}>ACESSAR CONTA</Text>
                            )}
                        </TouchableOpacity>
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
    },
    contentContainer: {
        paddingHorizontal: 30,
        width: '100%',
        maxWidth: 500,
        alignSelf: 'center',
    },
    headerContainer: {
        alignItems: 'center',
        marginBottom: 40,
    },
    logoPlaceholder: {
        width: 100,
        height: 100,
        backgroundColor: 'rgba(255,255,255,0.2)',
        borderRadius: 25,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 15,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.3)',
    },
    appName: {
        fontSize: 32,
        fontWeight: 'bold',
        color: '#fff',
        letterSpacing: 1,
    },
    tagline: {
        fontSize: 16,
        color: 'rgba(255,255,255,0.8)',
        marginTop: 5,
    },
    formContainer: {
        width: '100%',
    },
    inputWrapper: {
        marginBottom: 20,
    },
    label: {
        fontSize: 14,
        fontWeight: '600',
        color: '#fff',
        marginBottom: 8,
        marginLeft: 4,
    },
    inputContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#fff',
        borderRadius: 12,
        paddingHorizontal: 15,
        height: 55,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 4,
        elevation: 3,
    },
    inputIcon: {
        marginRight: 10,
    },
    input: {
        flex: 1,
        height: 55,
        color: '#333',
        fontSize: 16,
    },
    forgotPasswordButton: {
        alignSelf: 'flex-end',
        marginBottom: 30,
    },
    forgotPasswordText: {
        color: '#fff',
        fontWeight: '600',
        fontSize: 14,
    },
    loginButton: {
        backgroundColor: '#fff',
        borderRadius: 12,
        height: 55,
        justifyContent: 'center',
        alignItems: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 5,
        elevation: 5,
    },
    buttonDisabled: {
        opacity: 0.7,
    },
    loginButtonText: {
        color: '#7367F0',
        fontSize: 16,
        fontWeight: 'bold',
        letterSpacing: 1,
    },
});
