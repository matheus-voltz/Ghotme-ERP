import { useEffect, useRef, useState } from 'react';
import { Tabs } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../../context/AuthContext';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import { View, Text, Pressable, StyleSheet, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { BlurView } from 'expo-blur';
import * as Haptics from 'expo-haptics';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  withSequence,
  withTiming,
} from 'react-native-reanimated';
import type { BottomTabBarProps } from '@react-navigation/bottom-tabs';

const DROP_SIZE = 46;

function CustomTabBar({ state, descriptors, navigation }: BottomTabBarProps) {
  const { colors, activeTheme } = useTheme();
  const isDark = activeTheme === 'dark';

  // Center (x) of each tab slot, measured on layout.
  const [centers, setCenters] = useState<Record<number, number>>({});

  const dropX = useSharedValue(0);
  const stretchX = useSharedValue(1);
  const stretchY = useSharedValue(1);
  const initialized = useRef(false);

  useEffect(() => {
    const center = centers[state.index];
    if (center == null) return;
    const target = center - DROP_SIZE / 2;

    if (!initialized.current) {
      // Place the drop without animating on first paint.
      dropX.value = target;
      initialized.current = true;
      return;
    }

    // Squash/stretch: snap into a stretched blob, then settle back — liquid feel.
    stretchX.value = withSequence(
      withTiming(1.5, { duration: 130 }),
      withSpring(1, { damping: 11, stiffness: 180 }),
    );
    stretchY.value = withSequence(
      withTiming(0.6, { duration: 130 }),
      withSpring(1, { damping: 11, stiffness: 180 }),
    );
    dropX.value = withSpring(target, { damping: 15, stiffness: 140 });
  }, [state.index, centers]);

  const dropStyle = useAnimatedStyle(() => ({
    transform: [
      { translateX: dropX.value },
      { scaleX: stretchX.value },
      { scaleY: stretchY.value },
    ],
  }));

  return (
    <View style={styles.barWrapper} pointerEvents="box-none">
      <View style={[styles.barShadow, { shadowOpacity: isDark ? 0.3 : 0.08 }]}>
        <View
          style={[
            styles.barInner,
            {
              borderColor: isDark
                ? 'rgba(255, 255, 255, 0.18)'
                : 'rgba(255, 255, 255, 0.55)',
            },
          ]}>
          <BlurView
            intensity={Platform.OS === 'android' ? 50 : 60}
            tint={isDark ? 'systemThickMaterialDark' : 'systemThickMaterialLight'}
            style={StyleSheet.absoluteFill}>
            <View
              style={[
                StyleSheet.absoluteFill,
                {
                  backgroundColor: isDark
                    ? 'rgba(28,28,30,0.35)'
                    : 'rgba(255,255,255,0.35)',
                },
              ]}
            />
          </BlurView>

          {/* The animated liquid drop behind the active icon */}
          <Animated.View
            pointerEvents="none"
            style={[
              styles.drop,
              { backgroundColor: colors.primary, opacity: isDark ? 0.28 : 0.16 },
              dropStyle,
            ]}
          />

          <View style={styles.row}>
            {state.routes.map((route, index) => {
              // checklist_view is not a visible tab (href: null in the original).
              if (route.name === 'checklist_view') return null;

              const { options } = descriptors[route.key];
              const isFocused = state.index === index;

              const onPress = () => {
                Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);

                const event = navigation.emit({
                  type: 'tabPress',
                  target: route.key,
                  canPreventDefault: true,
                });

                if (!isFocused && !event.defaultPrevented) {
                  navigation.navigate(route.name as never);
                }
              };

              const color = isFocused ? colors.primary : colors.subText;

              return (
                <Pressable
                  key={route.key}
                  onPress={onPress}
                  style={styles.tab}
                  onLayout={(e) => {
                    const { x, width } = e.nativeEvent.layout;
                    const center = x + width / 2;
                    setCenters((prev) =>
                      prev[index] === center ? prev : { ...prev, [index]: center },
                    );
                  }}>
                  {options.tabBarIcon?.({ focused: isFocused, color, size: 24 })}
                  <Text style={[styles.label, { color }]} numberOfLines={1}>
                    {options.title}
                  </Text>
                </Pressable>
              );
            })}
          </View>
        </View>
      </View>
    </View>
  );
}

export default function TabLayout() {
  const { user, loading } = useAuth();
  const { colors } = useTheme();
  const { niche } = useNiche();

  if (loading || !user) {
    return <View style={{ flex: 1, backgroundColor: colors.background }} />;
  }

  const isFoodService = niche === 'food_service';

  return (
    <Tabs
      tabBar={(props) => <CustomTabBar {...props} />}
      screenOptions={{ headerShown: false }}>

      <Tabs.Screen
        name="index"
        options={{
          title: 'Início',
          tabBarIcon: ({ color, focused }) => (
            <Ionicons name={focused ? "home" : "home-outline"} size={24} color={color} />
          ),
        }}
      />

      <Tabs.Screen
        name="actions"
        options={{
          title: isFoodService ? 'Cardápio' : 'Novo',
          tabBarIcon: ({ color, focused }) => (
            <Ionicons name={focused ? (isFoodService ? "restaurant" : "add-circle") : (isFoodService ? "restaurant-outline" : "add-circle-outline")} size={24} color={color} />
          ),
        }}
      />

      <Tabs.Screen
        name="checklist_view"
        options={{
          title: isFoodService ? 'Pedidos' : 'Vistoria',
          tabBarIcon: () => (
            <View style={styles.middleButtonWrapper}>
              <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                style={styles.middleButton}
              >
                <Ionicons name={isFoodService ? "receipt" : "car-sport"} size={28} color="#fff" />
              </LinearGradient>
            </View>
          ),
        }}
      />

      <Tabs.Screen
        name="profile"
        options={{
          title: 'Perfil',
          tabBarIcon: ({ color, focused }) => (
            <Ionicons name={focused ? "person" : "person-outline"} size={24} color={color} />
          ),
        }}
      />
    </Tabs>
  );
}

const styles = StyleSheet.create({
  barWrapper: {
    position: 'absolute',
    bottom: 25,
    left: 20,
    right: 20,
  },
  barShadow: {
    borderRadius: 24,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowRadius: 12,
  },
  barInner: {
    height: 75,
    borderRadius: 24,
    borderWidth: 1.5,
    overflow: 'hidden',
  },
  row: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    paddingBottom: 12,
    paddingTop: 8,
  },
  tab: {
    flex: 1,
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    gap: 2,
  },
  label: {
    fontSize: 11,
    fontWeight: '500',
  },
  drop: {
    position: 'absolute',
    top: 6,
    left: 0,
    width: DROP_SIZE,
    height: DROP_SIZE,
    borderRadius: DROP_SIZE / 2,
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
