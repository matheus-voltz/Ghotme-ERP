import { Stack, useRouter, useSegments } from 'expo-router';
import { AuthProvider, useAuth } from '../context/AuthContext';
import { ThemeProvider } from '../context/ThemeContext';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useEffect } from 'react';
import { registerForPushNotificationsAsync } from '../services/notifications';
import * as Notifications from 'expo-notifications';
import api from '../services/api';

function RootLayoutNav() {
  const { user, loading } = useAuth();
  const segments = useSegments();
  const router = useRouter();

  useEffect(() => {
    const subscription = Notifications.addNotificationReceivedListener(notification => {
      console.log(notification);
    });
    return () => subscription.remove();
  }, []);

  useEffect(() => {
    if (!loading && user) {
      registerForPushNotificationsAsync().then(token => {
        if (token && token.startsWith('ExponentPushToken')) {
          api.post('/update-push-token', { token }).catch(err => {
            // Silencia erro 401 se for apenas delay de auth
            if (err.response?.status !== 401) {
              console.log("Erro ao salvar token no servidor:", err);
            }
          });
        }
      });
    }
  }, [user, loading]);

  useEffect(() => {
    if (loading) return;

    const inAuthGroup = segments[0] === '(tabs)';

    if (!user && inAuthGroup) {
      router.replace('/');
    } else if (user && (segments[0] === undefined || segments[0] === 'index' || segments[0] as string === '')) {
      router.replace('/(tabs)');
    }
  }, [user, loading, segments]);

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