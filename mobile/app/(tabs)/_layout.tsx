import { Tabs, router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { View, StyleSheet } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';

export default function TabLayout() {
  const { user, loading } = useAuth();

  const handleTabPress = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
  };

  if (loading || !user) {
    return <View style={{ flex: 1, backgroundColor: '#f8f9fa' }} />;
  }

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarShowLabel: true,
        tabBarActiveTintColor: '#7367F0',
        tabBarInactiveTintColor: '#B0B0B0',
        tabBarStyle: {
          position: 'absolute',
          bottom: 20,
          left: 15,
          right: 20,
          elevation: 10,
          backgroundColor: '#ffffff',
          borderRadius: 20,
          height: 75,
          paddingBottom: 12,
          paddingTop: 8,
          ...styles.shadow,
        },
      }}>

      <Tabs.Screen
        name="index"
        options={{
          title: 'Início',
          tabBarIcon: ({ color, focused }) => (
            <Ionicons name={focused ? "home" : "home-outline"} size={24} color={color} />
          ),
        }}
        listeners={{ tabPress: handleTabPress }}
      />

      <Tabs.Screen
        name="actions"
        options={{
          title: 'Novo',
          tabBarIcon: ({ color, focused }) => (
            <Ionicons name={focused ? "add-circle" : "add-circle-outline"} size={24} color={color} />
          ),
        }}
        listeners={{ tabPress: handleTabPress }}
      />

      <Tabs.Screen
        name="checklist_view"
        options={{
          title: 'Vistoria',
          href: null,
          tabBarIcon: ({ focused }) => (
            <View style={styles.middleButtonWrapper}>
              <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                style={styles.middleButton}
              >
                <Ionicons name="car-sport" size={28} color="#fff" />
              </LinearGradient>
            </View>
          ),
        }}
        listeners={() => ({
          tabPress: (e) => {
            e.preventDefault();
            handleTabPress();
            router.push('/os/checklist');
          },
        })}
      />

      <Tabs.Screen
        name="profile"
        options={{
          title: 'Perfil',
          tabBarIcon: ({ color, focused }) => (
            <Ionicons name={focused ? "person" : "person-outline"} size={24} color={color} />
          ),
        }}
        listeners={{ tabPress: handleTabPress }}
      />
    </Tabs>
  );
}

const styles = StyleSheet.create({
  shadow: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 5 },
    shadowOpacity: 0.1,
    shadowRadius: 10,
  },
  middleButtonWrapper: {
    top: -25,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#7367F0',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 5,
  },
  middleButton: {
    width: 56,
    height: 56,
    borderRadius: 28,
    justifyContent: 'center',
    alignItems: 'center',
  }
});
