import * as Notifications from 'expo-notifications';
import { Platform } from 'react-native';
import * as Device from 'expo-device';
import Constants from 'expo-constants';

// ─── Configuração global de exibição de notificações ─────────────────────────
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowBanner: true,
    shouldShowList: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

// ─── Canais Android por tipo de notificação ───────────────────────────────────
export async function setupNotificationChannels() {
  if (Platform.OS !== 'android') return;

  await Notifications.setNotificationChannelAsync('os_updates', {
    name: '🔧 Ordens de Serviço',
    description: 'Notificações sobre status das ordens de serviço',
    importance: Notifications.AndroidImportance.HIGH,
    vibrationPattern: [0, 200, 100, 200],
    lightColor: '#7367F0',
    sound: 'default',
  });

  await Notifications.setNotificationChannelAsync('chat', {
    name: '💬 Mensagens',
    description: 'Mensagens de clientes e equipe',
    importance: Notifications.AndroidImportance.HIGH,
    vibrationPattern: [0, 100],
    lightColor: '#00CFE8',
    sound: 'default',
  });

  await Notifications.setNotificationChannelAsync('financial', {
    name: '💰 Financeiro',
    description: 'Pagamentos e cobranças',
    importance: Notifications.AndroidImportance.DEFAULT,
    vibrationPattern: [0, 150],
    lightColor: '#28C76F',
    sound: 'default',
  });

  await Notifications.setNotificationChannelAsync('default', {
    name: 'Geral',
    importance: Notifications.AndroidImportance.DEFAULT,
    vibrationPattern: [0, 250, 250, 250],
    lightColor: '#7367F0',
  });
}

// ─── Registro do token de push ────────────────────────────────────────────────
export async function registerForPushNotificationsAsync() {
  try {
    let token;

    if (Platform.OS === 'android') {
      await setupNotificationChannels();
    }

    if (!Device.isDevice) {
      console.log('Push Notifications: Ignorado no simulador.');
      return;
    }

    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    let finalStatus = existingStatus;
    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }

    if (finalStatus !== 'granted') {
      console.warn('Permissão de push negada pelo usuário.');
      return;
    }

    try {
      const projectId =
        Constants?.expoConfig?.extra?.eas?.projectId ?? Constants?.easConfig?.projectId;

      if (projectId) {
        token = (await Notifications.getExpoPushTokenAsync({ projectId })).data;
      } else {
        token = (await Notifications.getExpoPushTokenAsync()).data;
      }

      console.log('Expo Push Token:', token);
    } catch (e: any) {
      // Caso comum em builds de Personal Team no iOS: a falta de entitlements
      // Captura tanto em inglês quanto no português exibido no erro do usuário
      const errorMsg = e.message || '';
      if (
        errorMsg.includes('aps-environment') ||
        errorMsg.includes('authorization code') ||
        errorMsg.includes('autorização válido') ||
        errorMsg.includes('entitlement')
      ) {
        console.warn('Push Notifications indisponíveis: Este build não possui as permissões necessárias (aps-environment).');
        return null;
      }
      token = `Erro ao obter token: ${errorMsg}`;
      console.error(e);
    }
    return token;
  } catch (err: any) {
    console.error('Falha geral no registro de Push:', err);
    return null;
  }
}

// ─── Utilitário: extrair rota de navegação a partir de uma notificação ─────────
export function getRouteFromNotification(data: Record<string, any> | undefined): string | null {
  if (!data) return null;

  // Notificação de OS (nova, status alterado, orçamento aprovado)
  if (data.os_id || data.ordem_servico_id || data.budget_id) {
    const id = data.os_id ?? data.ordem_servico_id ?? data.budget_id;
    return `/os/${id}`;
  }

  // Notificação de chat
  if (data.chat_id || data.conversation_id) {
    const id = data.chat_id ?? data.conversation_id;
    return `/chat/messages?id=${id}`;
  }

  // Evita URLs com http/https enviadas pelo sistema web (como redirects de painel)
  if (data.url && !data.url.startsWith('http')) return data.url;
  if (data.route && !data.route.startsWith('http')) return data.route;

  return null;
}

// ─── Ícone por tipo de notificação ───────────────────────────────────────────
export function getNotificationIcon(data: Record<string, any> | undefined): string {
  if (!data) return 'notifications';
  if (data.os_id || data.ordem_servico_id || data.budget_id) return 'construct';
  if (data.chat_id || data.conversation_id) return 'chatbubbles';
  if (data.type === 'financial' || data.type === 'payment') return 'wallet';
  if (data.type === 'alert') return 'warning';
  return 'notifications';
}

// ─── Cor por tipo de notificação ─────────────────────────────────────────────
export function getNotificationColor(data: Record<string, any> | undefined): string {
  if (!data) return '#7367F0';
  if (data.os_id || data.ordem_servico_id || data.budget_id) return '#7367F0';
  if (data.chat_id || data.conversation_id) return '#00CFE8';
  if (data.type === 'financial' || data.type === 'payment') return '#28C76F';
  if (data.type === 'alert') return '#FF9F43';
  return '#7367F0';
}
