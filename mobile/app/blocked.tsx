import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Linking, StatusBar } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useAuth } from '../context/AuthContext';
import Animated, { FadeInUp, BounceIn } from 'react-native-reanimated';

export default function BlockedScreen() {
  const { signOut, user } = useAuth();

  const handleOpenBilling = () => {
    // Redireciona para a página de faturamento do site
    Linking.openURL('https://ghotme.com.br/settings');
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" />
      <LinearGradient
        colors={['#ea5455', '#c12323']}
        style={styles.gradient}
      >
        <Animated.View entering={BounceIn.delay(300)} style={styles.iconContainer}>
          <Ionicons name="lock-closed" size={80} color="#fff" />
        </Animated.View>

        <Animated.View entering={FadeInUp.delay(500)} style={styles.content}>
          <Text style={styles.title}>Acesso Bloqueado</Text>
          <Text style={styles.description}>
            Olá, {user?.name}!{'\n'}
            Sua assinatura possui uma pendência financeira em aberto além do prazo de cortesia.
          </Text>

          <View style={styles.features}>
            <View style={styles.featureItem}>
              <Ionicons name="checkmark-circle" size={20} color="#fff" />
              <Text style={styles.featureText}>O acesso de sua equipe foi interrompido</Text>
            </View>
            <View style={styles.featureItem}>
              <Ionicons name="checkmark-circle" size={20} color="#fff" />
              <Text style={styles.featureText}>Liberação imediata pós-pagamento</Text>
            </View>
          </View>

          <TouchableOpacity style={styles.button} onPress={handleOpenBilling}>
            <Text style={styles.buttonText}>REGULARIZAR PAGAMENTO</Text>
            <Ionicons name="arrow-forward" size={20} color="#ea5455" />
          </TouchableOpacity>

          <TouchableOpacity style={styles.logoutButton} onPress={signOut}>
            <Text style={styles.logoutButtonText}>Sair da conta</Text>
          </TouchableOpacity>
        </Animated.View>

        <Text style={styles.footer}>Precisa de ajuda? ghotme.com.br</Text>
      </LinearGradient>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  gradient: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 30,
  },
  iconContainer: {
    width: 140,
    height: 140,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    borderRadius: 70,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 40,
  },
  content: {
    alignItems: 'center',
    width: '100%',
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 20,
    textAlign: 'center',
  },
  description: {
    fontSize: 16,
    color: 'rgba(255, 255, 255, 0.9)',
    textAlign: 'center',
    lineHeight: 24,
    marginBottom: 30,
  },
  features: {
    width: '100%',
    marginBottom: 40,
    paddingHorizontal: 20,
  },
  featureItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  featureText: {
    color: '#fff',
    marginLeft: 10,
    fontSize: 15,
    fontWeight: '500',
  },
  button: {
    backgroundColor: '#fff',
    flexDirection: 'row',
    paddingVertical: 18,
    paddingHorizontal: 30,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    width: '100%',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 5,
  },
  buttonText: {
    color: '#ea5455',
    fontWeight: 'bold',
    fontSize: 16,
    marginRight: 10,
    letterSpacing: 1,
  },
  logoutButton: {
    marginTop: 25,
  },
  logoutButtonText: {
    color: 'rgba(255, 255, 255, 0.7)',
    fontSize: 14,
    fontWeight: '600',
    textDecorationLine: 'underline',
  },
  footer: {
    position: 'absolute',
    bottom: 40,
    color: 'rgba(255, 255, 255, 0.5)',
    fontSize: 12,
  },
});
