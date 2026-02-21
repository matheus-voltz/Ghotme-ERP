import { Stack } from 'expo-router';
import { useEffect } from 'react';
import { View, Text, StyleSheet } from 'react-native';
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
    <View style={{ flex: 1 }}>
      {user?.is_overdue && !user?.is_locked && (
        <View style={styles.overdueBanner}>
          <Text style={styles.overdueTitle}>Atraso de Pagamento Detectado</Text>
          <Text style={styles.overdueText}>
            Seu pagamento está atrasado há {user.overdue_days} dia(s).
            Em {3 - user.overdue_days} dia(s) o acesso será bloqueado.
          </Text>
        </View>
      )}
      <Stack screenOptions={{ headerShown: false }}>
        <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
        <Stack.Screen name="os/checklist" options={{ presentation: 'modal', title: 'Vistoria', headerShown: true }} />
        <Stack.Screen name="os/technical_checklist" options={{ presentation: 'modal', title: 'Checklist Técnico', headerShown: false }} />
        <Stack.Screen name="os/list" options={{ title: 'Lista de Ordens', headerShown: false }} />
        <Stack.Screen name="calendar/create" options={{ presentation: 'modal', title: 'Novo Agendamento', headerShown: true }} />
        <Stack.Screen name="chat/contacts" options={{ headerShown: false }} />
        <Stack.Screen name="chat/messages" options={{ headerShown: false }} />
      </Stack>
    </View>
  );
}

const styles = StyleSheet.create({
  overdueBanner: {
    backgroundColor: '#ffdbdb',
    padding: 15,
    paddingTop: 50, // SafeArea spacing
    borderBottomWidth: 1,
    borderBottomColor: '#ea5455',
  },
  overdueTitle: {
    color: '#ea5455',
    fontWeight: 'bold',
    fontSize: 16,
    marginBottom: 4,
  },
  overdueText: {
    color: '#ea5455',
    fontSize: 13,
  }
});
