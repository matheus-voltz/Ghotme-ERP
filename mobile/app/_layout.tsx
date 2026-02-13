import { Slot, Stack } from 'expo-router';
import { AuthProvider } from '../context/AuthContext';
import { ThemeProvider } from '../context/ThemeContext';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useEffect } from 'react';
import { registerForPushNotificationsAsync } from '../services/notifications';
import * as Notifications from 'expo-notifications';

export default function Layout() {
  
  useEffect(() => {
    registerForPushNotificationsAsync().then(token => {
        if(token) console.log("Token Push:", token);
        // Aqui você faria: api.post('/update-push-token', { token })
    });

    const subscription = Notifications.addNotificationReceivedListener(notification => {
      // O que fazer quando receber notificação com app aberto
      console.log(notification);
    });

    return () => subscription.remove();
  }, []);

  return (
    <SafeAreaProvider>
      <ThemeProvider>
        <AuthProvider>
          <Slot />
        </AuthProvider>
      </ThemeProvider>
    </SafeAreaProvider>
  );
}
