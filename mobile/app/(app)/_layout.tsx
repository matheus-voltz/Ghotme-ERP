import { Stack } from 'expo-router';

export default function AppLayout() {
  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
      <Stack.Screen name="os/checklist" options={{ presentation: 'modal', title: 'Vistoria', headerShown: true }} />
      <Stack.Screen name="os/technical_checklist" options={{ presentation: 'modal', title: 'Checklist Técnico', headerShown: false }} />
      <Stack.Screen name="calendar/create" options={{ presentation: 'modal', title: 'Novo Agendamento', headerShown: true }} />
      <Stack.Screen name="chat/contacts" options={{ headerShown: false }} />
      <Stack.Screen name="chat/messages" options={{ headerShown: false }} />
    </Stack>
  );
}