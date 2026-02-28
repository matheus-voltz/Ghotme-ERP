import { Stack, useRouter } from 'expo-router';
import { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, AppState } from 'react-native';
import * as Notifications from 'expo-notifications';
import { useAuth } from '../../context/AuthContext';
import { registerForPushNotificationsAsync, getRouteFromNotification } from '../../services/notifications';
import api from '../../services/api';

export default function AppLayout() {
  const { user } = useAuth();
  const router = useRouter();
  const notificationListener = useRef<any>(null);
  const responseListener = useRef<any>(null);

  useEffect(() => {
    // ── Registro do token ao fazer login ────────────────────────────────────
    if (user) {
      registerForPushNotificationsAsync().then(token => {
        if (token && !token.startsWith('Erro')) {
          api.post('/user/push-token', { token }).catch(e =>
            console.error('Error updating push token:', e)
          );
        }
      });
    }

    // ── Listener: notificação recebida com app ABERTO ────────────────────────
    // (apenas mostra o banner nativo — nenhuma ação extra necessária)
    notificationListener.current = Notifications.addNotificationReceivedListener(notification => {
      console.log('[Push] Recebida:', notification.request.content.title);
    });

    // ── Listener: usuário TOCOU em uma notificação ───────────────────────────
    responseListener.current = Notifications.addNotificationResponseReceivedListener(response => {
      const data = response.notification.request.content.data as Record<string, any>;
      const route = getRouteFromNotification(data);

      console.log('[Push] Tocado — data:', data, '→ rota:', route);

      if (route) {
        // Pequeno delay para garantir que a navegação ocorra após o app estar pronto
        setTimeout(() => {
          try {
            router.push(route as any);
          } catch (e) {
            console.error('[Push] Erro ao navegar:', e);
          }
        }, 300);
      }
    });

    // ── Checa notificação que abriu o app do zero (app estava fechado) ──────
    Notifications.getLastNotificationResponseAsync().then(response => {
      if (!response) return;
      const data = response.notification.request.content.data as Record<string, any>;
      const route = getRouteFromNotification(data);
      if (route) {
        setTimeout(() => {
          try {
            router.push(route as any);
          } catch (e) {
            console.error('[Push] Erro ao navegar (cold start):', e);
          }
        }, 800);
      }
    });

    return () => {
      if (notificationListener.current) {
        notificationListener.current.remove();
      }
      if (responseListener.current) {
        responseListener.current.remove();
      }
    };
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
        {/* As demais telas são registradas automaticamente pelo expo-router */}
        {/* Configuramos apenas as que precisam de apresentação Modal ou comportamentos específicos do Stack */}
        <Stack.Screen name="os/checklist" options={{ presentation: 'modal', title: 'Vistoria', headerShown: true }} />
        <Stack.Screen name="os/technical_checklist" options={{ presentation: 'modal', title: 'Checklist Técnico', headerShown: false }} />
        <Stack.Screen name="calendar/create" options={{ presentation: 'modal', title: 'Novo Agendamento', headerShown: true }} />
        <Stack.Screen name="screens/qr_scanner" options={{ headerShown: false, presentation: 'fullScreenModal' }} />
      </Stack>
    </View>
  );
}

const styles = StyleSheet.create({
  overdueBanner: {
    backgroundColor: '#ffdbdb',
    padding: 15,
    paddingTop: 50,
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
  },
});
