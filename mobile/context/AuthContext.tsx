import React, { createContext, useState, useEffect, useContext } from 'react';
import * as SecureStore from 'expo-secure-store';
import api from '../services/api';
import { router } from 'expo-router';

type AuthContextType = {
    user: any;
    loading: boolean;
    signIn: (data: any) => Promise<void>;
    signOut: () => Promise<void>;
};

const AuthContext = createContext<AuthContextType>({} as AuthContextType);

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
    const [user, setUser] = useState<any>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadStorageData();
    }, []);

    async function loadStorageData() {
        try {
            const userToken = await SecureStore.getItemAsync('userToken');
            const userData = await SecureStore.getItemAsync('userData');

            if (userToken && userData) {
                setUser(JSON.parse(userData));
                api.defaults.headers.common['Authorization'] = `Bearer ${userToken}`;
            }
        } catch (error) {
            console.log('Error loading data', error);
        } finally {
            setLoading(false);
        }
    }

    async function signIn({ email, password }: any) {
        try {
            const response = await api.post('/login', { email, password });

            const { user, token } = response.data;

            api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            await SecureStore.setItemAsync('userToken', token);
            await SecureStore.setItemAsync('userData', JSON.stringify(user));

            setUser(user);
            router.replace('/(tabs)');
        } catch (error) {
            throw error;
        }
    }

    async function signOut() {
        setUser(null);
        await SecureStore.deleteItemAsync('userToken');
        await SecureStore.deleteItemAsync('userData');
        router.replace('/');
    }

    return (
        <AuthContext.Provider value={{ user, loading, signIn, signOut }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) throw new Error('useAuth must be used within an AuthProvider');
    return context;
};
