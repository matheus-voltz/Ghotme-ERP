import { Slot, Stack } from 'expo-router';
import { AuthProvider } from '../context/AuthContext';
import { ThemeProvider } from '../context/ThemeContext';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useEffect } from 'react';
import { registerForPushNotificationsAsync } from '../services/notifications';
import * as Notifications from 'expo-notifications';
import { Alert } from 'react-native';

import api from '../services/api';

export default function Layout() {
  
  useEffect(() => {
    registerForPushNotificationsAsync().then(token => {
        if(token && token.startsWith('ExponentPushToken')) {
            console.log("Token Push:", token);
            // Envia para a API se o usuário estiver logado
            api.post('/update-push-token', { token })
               .then(() => console.log("Token salvo no servidor"))
               .catch(err => console.log("Erro ao salvar token:", err));
        }
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
