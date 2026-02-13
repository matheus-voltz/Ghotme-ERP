import { Stack, useRouter, useSegments } from 'expo-router';
import { AuthProvider, useAuth } from '../context/AuthContext';
import { ThemeProvider } from '../context/ThemeContext';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useEffect } from 'react';
import { registerForPushNotificationsAsync } from '../services/notifications';
import * as Notifications from 'expo-notifications';
import api from '../services/api';
import { useFonts } from 'expo-font';
import { Ionicons } from '@expo/vector-icons';
import * as SplashScreen from 'expo-splash-screen';
import { View, ActivityIndicator } from 'react-native';

// Impede que a tela de splash suma automaticamente antes das fontes carregarem
SplashScreen.preventAutoHideAsync();

function RootLayoutNav() {
  const { user, loading: authLoading } = useAuth();
  const segments = useSegments();
  const router = useRouter();

  // Carregar fontes
  const [fontsLoaded] = useFonts({
    ...Ionicons.font,
  });

  useEffect(() => {
    const subscription = Notifications.addNotificationReceivedListener(notification => {
      console.log(notification);
    });
    return () => subscription.remove();
  }, []);

  useEffect(() => {
    if (!authLoading && user) {
      registerForPushNotificationsAsync().then(token => {
        if (token && token.startsWith('ExponentPushToken')) {
          api.post('/update-push-token', { token }).catch(err => {
            if (err.response?.status !== 401) {
              console.log("Erro ao salvar token no servidor:", err);
            }
          });
        }
      });
    }
  }, [user, authLoading]);

  useEffect(() => {
    if (authLoading || !fontsLoaded) return;

    const inAuthGroup = segments[0] === '(tabs)';

    if (!user && inAuthGroup) {
      router.replace('/');
    } else if (user && (segments[0] === undefined || segments[0] === 'index' || segments[0] as any === '' || segments.length === 0)) {
      router.replace('/(tabs)');
    }

    // Ocultar splash screen apenas no final
    setTimeout(() => {
        SplashScreen.hideAsync();
    }, 500);
  }, [user, authLoading, segments, fontsLoaded]);

  // Mostrar loading enquanto fontes ou auth inicializam
  if (!fontsLoaded || authLoading) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#7367F0' }}>
        <ActivityIndicator size="large" color="#ffffff" />
      </View>
    );
  }

  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="index" options={{ headerShown: false }} />
      <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
      <Stack.Screen name="os/checklist" options={{ presentation: 'modal', title: 'Vistoria Visual', headerShown: true }} />
    </Stack>
  );
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