import { Tabs } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { Redirect } from 'expo-router';
import { View, Platform, StyleSheet } from 'react-native';

export default function TabLayout() {
  const { user } = useAuth();

  if (!user) {
    return <Redirect href="/" />;
  }

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarShowLabel: false, // Hide labels for a cleaner look
        tabBarStyle: {
          position: 'absolute',
          bottom: 25,
          left: 20,
          right: 20,
          elevation: 5,
          backgroundColor: '#ffffff',
          borderRadius: 25,
          height: 70,
          borderTopWidth: 0, // Remove top border
          ...styles.shadow, // Apply shadow
        },
        tabBarHideOnKeyboard: true, // Hide tab bar when keyboard is open
      }}>
      <Tabs.Screen
        name="index"
        options={{
          title: 'Dashboard',
          tabBarIcon: ({ focused }) => (
            <View style={{
              alignItems: 'center',
              justifyContent: 'center',
              top: 0, // Adjust icon position
            }}>
              <View style={{
                backgroundColor: focused ? '#7367F0' : 'transparent',
                width: 50,
                height: 50,
                borderRadius: 25,
                alignItems: 'center',
                justifyContent: 'center',
                marginBottom: 5,
                // Add soft shadow to active button
                shadowColor: focused ? '#7367F0' : 'transparent',
                shadowOffset: { width: 0, height: 4 },
                shadowOpacity: 0.3,
                shadowRadius: 5,
                elevation: focused ? 5 : 0,
              }}>
                <Ionicons
                  name={focused ? "home" : "home-outline"}
                  size={24}
                  color={focused ? "#ffffff" : "#A8A8A8"}
                />
              </View>
            </View>
          ),
        }}
      />

      {/* 
         You can enable this if you want a central "Add" button later
         It would be a placeholder screen that opens a modal
      */}
      {/* 
      <Tabs.Screen
        name="add"
        options={{
          tabBarIcon: ({ focused }) => (
             <Ionicons name="add-circle" size={58} color="#7367F0" style={{ marginTop: -30 }} />
          ),
        }}
      /> 
      */}

      <Tabs.Screen
        name="profile"
        options={{
          title: 'Perfil',
          tabBarIcon: ({ focused }) => (
            <View style={{
              alignItems: 'center',
              justifyContent: 'center',
              top: 0,
            }}>
              <View style={{
                backgroundColor: focused ? '#7367F0' : 'transparent',
                width: 50,
                height: 50,
                borderRadius: 25,
                alignItems: 'center',
                justifyContent: 'center',
                marginBottom: 5,
                shadowColor: focused ? '#7367F0' : 'transparent',
                shadowOffset: { width: 0, height: 4 },
                shadowOpacity: 0.3,
                shadowRadius: 5,
                elevation: focused ? 5 : 0,
              }}>
                <Ionicons
                  name={focused ? "person" : "person-outline"}
                  size={24}
                  color={focused ? "#ffffff" : "#A8A8A8"}
                />
              </View>
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
    shadowOpacity: 0.25,
    shadowRadius: 3.5,
    elevation: 5,
  },
});
