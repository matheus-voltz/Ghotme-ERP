import { Slot, useRouter, useSegments } from 'expo-router';
import { AuthProvider, useAuth } from '../context/AuthContext';
import { ThemeProvider } from '../context/ThemeContext';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useEffect } from 'react';
import * as SplashScreen from 'expo-splash-screen';
import { View, ActivityIndicator } from 'react-native';
import { useFonts } from 'expo-font';
import { Ionicons } from '@expo/vector-icons';

SplashScreen.preventAutoHideAsync();

function RootLayoutNav() {
  const { user, loading } = useAuth();
  const segments = useSegments();
  const router = useRouter();
  const [fontsLoaded] = useFonts({ ...Ionicons.font });

  useEffect(() => {
    if (loading || !fontsLoaded) return;

    const inAuthGroup = segments[0] === '(auth)';

    if (!user && !inAuthGroup) {
      // Se não tem usuário e não está na tela de login -> Manda pro Login
      router.replace('/(auth)/login');
    } else if (user && (inAuthGroup || segments.length === 0)) {
      // Se tem usuário e está no login ou na raiz -> Manda pro App
      router.replace('/(app)/(tabs)');
    }

    SplashScreen.hideAsync();
  }, [user, loading, segments, fontsLoaded]);

  if (loading || !fontsLoaded) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#7367F0' }}>
        <ActivityIndicator size="large" color="#ffffff" />
      </View>
    );
  }

  return <Slot />;
}

export default function Layout() {
  return (
    <SafeAreaProvider>
      <ThemeProvider>
        <AuthProvider>
          <RootLayoutNav />
        </AuthProvider>
      </ThemeProvider>
    </SafeAreaProvider>
  );
}
