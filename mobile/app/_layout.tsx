import { Slot, Stack } from 'expo-router';
import { AuthProvider } from '../context/AuthContext';
import { ThemeProvider } from '../context/ThemeContext';
import { SafeAreaProvider } from 'react-native-safe-area-context';

export default function Layout() {
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
