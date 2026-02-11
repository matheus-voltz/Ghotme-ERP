import { Tabs, router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { Redirect } from 'expo-router';
import { useEffect } from 'react';
import { View, Platform, StyleSheet } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';

export default function TabLayout() {
  const { user, loading } = useAuth();

  useEffect(() => {
    if (!loading && !user) {
      router.replace('/');
    }
  }, [user, loading]);

  if (loading || !user) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#f8f9fa' }}>
        {/* Placeholder for loading */}
      </View>
    );
  }

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarShowLabel: false,
        tabBarStyle: {
          position: 'absolute',
          bottom: 25,
          left: 20,
          right: 20,
          elevation: 0, // Reset default elevation to use custom shadow
          backgroundColor: '#ffffff',
          borderRadius: 30, // Softer corners
          height: 70, // Taller bar
          borderTopWidth: 0,
          ...styles.shadow, // Custom premium shadow
        },
        tabBarHideOnKeyboard: true,
      }}>
      <Tabs.Screen
        name="index"
        options={{
          title: 'Dashboard',
          tabBarIcon: ({ focused }) => (
            <View style={[styles.iconContainer, focused && styles.iconActive]}>
              <Ionicons
                name={focused ? "home" : "home-outline"}
                size={26}
                color={focused ? "#7367F0" : "#B0B0B0"}
              />
              {focused && <View style={styles.activeDot} />}
            </View>
          ),
        }}
      />

      <Tabs.Screen
        name="actions"
        options={{
          title: 'Novo',
          tabBarIcon: ({ focused }) => (
            <View style={styles.middleButtonWrapper}>
              <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                style={styles.middleButton}
              >
                <Ionicons name="add" size={32} color="#fff" />
              </LinearGradient>
            </View>
          ),
        }}
      />

      <Tabs.Screen
        name="profile"
        options={{
          title: 'Perfil',
          tabBarIcon: ({ focused }) => (
            <View style={[styles.iconContainer, focused && styles.iconActive]}>
              <Ionicons
                name={focused ? "person" : "person-outline"}
                size={26}
                color={focused ? "#7367F0" : "#B0B0B0"}
              />
              {focused && <View style={styles.activeDot} />}
            </View>
          ),
        }}
      />
    </Tabs>
  );
}

const styles = StyleSheet.create({
  shadow: {
    shadowColor: '#7F5DF0',
    shadowOffset: {
      width: 0,
      height: 10,
    },
    shadowOpacity: 0.15,
    shadowRadius: 20,
    elevation: 10,
  },
  iconContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    height: '100%',
    width: 60,
  },
  iconActive: {
    // transform: [{scale: 1.1}] // Optional subtle zoom
  },
  activeDot: {
    width: 5,
    height: 5,
    borderRadius: 2.5,
    backgroundColor: '#7367F0',
    marginTop: 4,
  },
  middleButtonWrapper: {
    top: -25, // Float effectively
    justifyContent: 'center',
    alignItems: 'center',
    // Shadow for button
    shadowColor: '#7367F0',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.5,
    shadowRadius: 10,
    elevation: 8,
  },
  middleButton: {
    width: 64,
    height: 64,
    borderRadius: 32,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 4,
    borderColor: '#f8f9fa', // Seamless blending if background matches, or white
  }
});
