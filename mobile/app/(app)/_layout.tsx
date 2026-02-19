import { Stack } from 'expo-router';
import { useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { registerForPushNotificationsAsync } from '../../services/notifications';
import api from '../../services/api';

export default function AppLayout() {
  const { user } = useAuth();

  useEffect(() => {
    if (user) {
      registerForPushNotificationsAsync().then(token => {
        if (token && !token.startsWith('Erro')) {
          api.post('/user/push-token', { token }).catch(e => console.error("Error updating push token:", e));
        }
      });
    }
  }, [user]);

  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
      <Stack.Screen name="os/checklist" options={{ presentation: 'modal', title: 'Vistoria', headerShown: true }} />
      <Stack.Screen name="os/technical_checklist" options={{ presentation: 'modal', title: 'Checklist Técnico', headerShown: false }} />
      <Stack.Screen name="os/list" options={{ title: 'Lista de Ordens', headerShown: false }} />
      <Stack.Screen name="calendar/create" options={{ presentation: 'modal', title: 'Novo Agendamento', headerShown: true }} />
      <Stack.Screen name="chat/contacts" options={{ headerShown: false }} />
      <Stack.Screen name="chat/messages" options={{ headerShown: false }} />
    </Stack>
  );
}
