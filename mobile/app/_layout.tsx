import { Slot, useRouter, useSegments } from 'expo-router';
import { AuthProvider, useAuth } from '../context/AuthContext';
import { ThemeProvider } from '../context/ThemeContext';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useEffect, useState } from 'react';
import { View, ActivityIndicator } from 'react-native';
import { useFonts } from 'expo-font';
import { Ionicons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { NicheProvider } from '../context/NicheContext';
import { ChatProvider } from '../context/ChatContext';
import { LanguageProvider } from '../context/LanguageContext';

function RootLayoutNav() {
  const { user, loading } = useAuth();
  const segments = useSegments();
  const router = useRouter();
  const [fontsLoaded] = useFonts({ ...Ionicons.font });
  const [appReady, setAppReady] = useState(false);

  useEffect(() => {
    const checkNavigation = async () => {
      if (loading || !fontsLoaded) return;

      const completed = await AsyncStorage.getItem('onboarding_completed');
      const needsOnboarding = completed !== 'true';
      const inAuthGroup = segments[0] === '(auth)';
      const isExpiredPage = segments[0] === 'expired';
      const isOnboarding = segments[0] === 'onboarding';

      // 1. Onboarding Priority
      if (needsOnboarding) {
        if (!isOnboarding) {
          router.replace('/onboarding');
        }
        setAppReady(true);
        return;
      }

      // 1.5 Prevent being on onboarding page if already done
      if (isOnboarding && !needsOnboarding) {
        router.replace(user ? '/(app)/(tabs)' : '/(auth)/login');
        setAppReady(true);
        return;
      }

      // 2. Auth flows
      if (!user) {
        if (!inAuthGroup) {
          router.replace('/(auth)/login');
        }
      } else {
        if (user.is_expired) {
          if (!isExpiredPage) {
            router.replace('/expired');
          }
        } else {
          if (inAuthGroup || isExpiredPage || !segments[0]) {
            router.replace('/(app)/(tabs)');
          }
        }
      }
      setAppReady(true);
    };

    checkNavigation();
  }, [user, loading, segments, fontsLoaded]);

  if (!appReady || !fontsLoaded) {
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
          <LanguageProvider>
            <NicheProvider>
              <ChatProvider>
                <RootLayoutNav />
              </ChatProvider>
            </NicheProvider>
          </LanguageProvider>
        </AuthProvider>
      </ThemeProvider>
    </SafeAreaProvider>
  );
}
