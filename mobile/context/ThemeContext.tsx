import React, { createContext, useContext, useState, useEffect } from 'react';
import { useColorScheme as useDeviceColorScheme } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

type Theme = 'light' | 'dark' | 'system';

interface ThemeContextType {
    theme: Theme;
    activeTheme: 'light' | 'dark';
    setTheme: (theme: Theme) => void;
    colors: typeof lightColors;
}

const lightColors = {
    background: '#f8f9fa',
    card: '#ffffff',
    text: '#333333',
    subText: '#888888',
    border: '#f0f0f0',
    primary: '#7367F0',
    iconBg: '#F3F4F6',
    tabBar: '#ffffff',
};

const darkColors = {
    background: '#161d31', // Darker blue/grey background
    card: '#283046', // Slightly lighter card bg
    text: '#d0d2d6', // Light grey text
    subText: '#b4b7bd', // Dimmer text
    border: '#3b4253', // Dark border
    primary: '#7367F0', // Keep primary brand color or adjust slightly
    iconBg: '#3b4253', // Dark icon background
    tabBar: '#283046',
};

const ThemeContext = createContext<ThemeContextType>({} as ThemeContextType);

export const ThemeProvider = ({ children }: { children: React.ReactNode }) => {
    const systemScheme = useDeviceColorScheme();
    const [theme, setThemeState] = useState<Theme>('system');

    useEffect(() => {
        loadTheme();
    }, []);

    const loadTheme = async () => {
        try {
            const savedTheme = await AsyncStorage.getItem('user-theme');
            if (savedTheme) {
                setThemeState(savedTheme as Theme);
            }
        } catch (e) {
            console.log('Failed to load theme preference', e);
        }
    };

    const setTheme = async (newTheme: Theme) => {
        setThemeState(newTheme);
        try {
            await AsyncStorage.setItem('user-theme', newTheme);
        } catch (e) {
            console.log('Failed to save theme preference', e);
        }
    };

    const activeTheme = theme === 'system' ? (systemScheme || 'light') : theme;
    const colors = activeTheme === 'dark' ? darkColors : lightColors;

    return (
        <ThemeContext.Provider value={{ theme, activeTheme, setTheme, colors }}>
            {children}
        </ThemeContext.Provider>
    );
};

export const useTheme = () => useContext(ThemeContext);
