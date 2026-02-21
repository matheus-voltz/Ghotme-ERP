import React, { createContext, useState, useEffect, useContext } from 'react';
import * as SecureStore from 'expo-secure-store';
import api from '../services/api';

type AuthContextType = {
    user: any;
    loading: boolean;
    signIn: (data: any) => Promise<any>;
    signOut: () => Promise<void>;
    verify2FA: (email: string, code: string) => Promise<void>;
    updateUser: (newData: any) => Promise<void>;
    refreshUser: () => Promise<void>;
};

const AuthContext = createContext<AuthContextType>({} as AuthContextType);

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
    const [user, setUser] = useState<any>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadStorageData();
    }, []);

    const fixPhotoUrl = (userObj: any) => {
        if (!userObj) return userObj;
        if (!userObj.profile_photo_url) {
            userObj.profile_photo_url = `https://ui-avatars.com/api/?name=${encodeURIComponent(userObj.name || 'U')}&color=7367F0&background=F3F2FF`;
        } else if (userObj.profile_photo_url.includes('localhost') || userObj.profile_photo_url.includes('127.0.0.1')) {
            const baseUrl = api.defaults.baseURL?.replace('/api', '');
            if (baseUrl) {
                userObj.profile_photo_url = userObj.profile_photo_url.replace(/^http:\/\/(localhost|127\.0\.0\.1)(:\d+)?/, baseUrl);
            }
        }
        return userObj;
    };

    async function loadStorageData() {
        try {
            const userToken = await SecureStore.getItemAsync('userToken');
            const userData = await SecureStore.getItemAsync('userData');

            if (userToken) {
                api.defaults.headers.common['Authorization'] = `Bearer ${userToken}`;

                // Tenta buscar dados atualizados da API para verificar expiração/plano
                try {
                    const response = await api.get('/user');
                    const updatedUser = fixPhotoUrl(response.data);

                    await SecureStore.setItemAsync('userData', JSON.stringify(updatedUser));
                    setUser(updatedUser);
                } catch (apiError) {
                    // Se a API falhar (ex: sem internet), usa os dados do cache
                    if (userData) {
                        setUser(JSON.parse(userData));
                    }
                }
            }
        } catch (error) {
            console.log('Error loading auth data', error);
        } finally {
            setLoading(false);
        }
    }

    async function saveAuthData(rawUser: any, token: string) {
        const user = fixPhotoUrl(rawUser);

        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        await SecureStore.setItemAsync('userToken', token);
        await SecureStore.setItemAsync('userData', JSON.stringify(user));
        setUser(user);
    }

    async function updateUser(newData: any) {
        try {
            const updatedUser = fixPhotoUrl({ ...user, ...newData });
            await SecureStore.setItemAsync('userData', JSON.stringify(updatedUser));
            setUser(updatedUser);
        } catch (error) {
            console.error("Error updating user:", error);
        }
    }

    async function refreshUser() {
        try {
            const response = await api.get('/user');
            const updatedUser = fixPhotoUrl(response.data);
            await SecureStore.setItemAsync('userData', JSON.stringify(updatedUser));
            setUser(updatedUser);
        } catch (error) {
            console.error('Error in refreshUser:', error);
        }
    }

    async function signIn({ email, password }: any) {
        try {
            const response = await api.post('/login', { email, password });
            if (response.data.two_factor) return response.data;
            const { user, token } = response.data;
            await saveAuthData(user, token);
            return { success: true };
        } catch (error) { throw error; }
    }

    async function verify2FA(email: string, code: string) {
        try {
            const response = await api.post('/login/two-factor', { email, code });
            const { user, token } = response.data;
            await saveAuthData(user, token);
        } catch (error) { throw error; }
    }

    async function signOut() {
        try {
            await SecureStore.deleteItemAsync('userToken');
            await SecureStore.deleteItemAsync('userData');
            delete api.defaults.headers.common['Authorization'];
            setUser(null);
        } catch (error) { console.error('Error during signOut:', error); }
    }

    return (
        <AuthContext.Provider value={{ user, loading, signIn, signOut, verify2FA, updateUser, refreshUser }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);