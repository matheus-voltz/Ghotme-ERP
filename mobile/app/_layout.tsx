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

SplashScreen.preventAutoHideAsync();

function RootLayoutNav() {
  const { user, loading: authLoading } = useAuth();
  const segments = useSegments();
  const router = useRouter();

  const [fontsLoaded] = useFonts({
    ...Ionicons.font,
  });

  useEffect(() => {
    if (fontsLoaded) {
      SplashScreen.hideAsync();
    }
  }, [fontsLoaded]);

  // Se estiver carregando, mostramos o Splash/Loading
  if (authLoading || !fontsLoaded) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#7367F0' }}>
        <ActivityIndicator size="large" color="#ffffff" />
      </View>
    );
  }

  return (
    <Stack screenOptions={{ headerShown: false }}>
      {/* Se não houver usuário, a rota inicial é o Login (index) */}
      {!user ? (
        <Stack.Screen name="index" options={{ headerShown: false, animation: 'fade' }} />
      ) : (
        <Stack.Screen name="(tabs)" options={{ headerShown: false, animation: 'fade' }} />
      )}
      
      {/* Telas auxiliares */}
      <Stack.Screen name="os/checklist" options={{ presentation: 'modal', title: 'Vistoria Visual', headerShown: true }} />
      <Stack.Screen name="calendar/create" options={{ presentation: 'modal', title: 'Agendar Serviço', headerShown: true }} />
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