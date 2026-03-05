import React, { useEffect, useState, useCallback, useRef } from 'react';
import {
  View,
  Text,
  ActivityIndicator,
  Alert,
  StyleSheet,
  StatusBar,
  RefreshControl,
  ScrollView,
  Platform,
  Pressable
} from 'react-native';
import api from '../../../services/api';
import { useAuth } from '../../../context/AuthContext';
import { useTheme } from '../../../context/ThemeContext';
import { useNiche } from '../../../context/NicheContext';
import { useChat } from '../../../context/ChatContext';
import { useLanguage } from '../../../context/LanguageContext';

import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter, router, useNavigation } from 'expo-router';
import Animated, { FadeInDown, FadeInUp, FadeIn, FadeOut, useSharedValue, useAnimatedStyle, withSpring } from 'react-native-reanimated';
import * as Haptics from 'expo-haptics';
import { Skeleton } from '../../../components/Skeleton';

// Helper for status translations
const statusTranslations: { [key: string]: string } = {
  'pending': 'Pendente',
  'approved': 'Aprovada',
  'running': 'Em Execução',
  'finalized': 'Finalizada',
  'canceled': 'Cancelada',
};

// Helper for status colors (Platform Palette)
const getStatusColor = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'pending': return '#FF9F43'; // Warning
    case 'approved': return '#00CFE8'; // Info/Approved
    case 'running': return '#00CFE8'; // Info/Execution
    case 'finalized': return '#28C76F'; // Success
    case 'canceled': return '#EA5455'; // Danger
    default: return '#7367F0'; // Primary
  }
};

const getEntityIcon = (niche: string) => {
  switch (niche) {
    case 'pet': return 'paw-outline';
    case 'beauty_clinic': return 'heart-outline';
    case 'electronics': return 'laptop-outline';
    case 'construction': return 'construct-outline';
    case 'food_service': return 'fast-food-outline';
    default: return 'car-sport-outline';
  }
};

const numberFormat = (value: any) => {
  return parseFloat(value || 0).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const premiumShadow = {
  shadowColor: '#000',
  shadowOffset: { width: 0, height: 2 },
  shadowOpacity: 0.04,
  shadowRadius: 8,
  elevation: 2,
};

// Reusable Animated Card Wrapper
const AnimatedCard = ({ children, onPress, style, activeOpacity = 0.9 }: any) => {
  const scale = useSharedValue(1);
  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }]
  }));

  return (
    <Pressable
      onPressIn={() => {
        scale.value = withSpring(0.97, { damping: 15, stiffness: 200 });
      }}
      onPressOut={() => {
        scale.value = withSpring(1, { damping: 15, stiffness: 200 });
      }}
      onPress={() => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        if (onPress) onPress();
      }}
    >
      <Animated.View style={[style, animatedStyle]}>
        {children}
      </Animated.View>
    </Pressable>
  );
};

export default function DashboardScreen() {
  const { user } = useAuth();
  const { colors, activeTheme } = useTheme();
  const { labels, niche } = useNiche();
  const { unreadCount } = useChat();
  const { t, language } = useLanguage();
  const [data, setData] = useState<any>(null);
  const [selectedChartIndex, setSelectedChartIndex] = useState<number | null>(null);
  const scrollRef = useRef<ScrollView>(null);

  const getEstablishmentName = () => {
    switch (niche) {
      case 'pet': return 'do Pet Shop';
      case 'beauty_clinic': return 'da Clínica';
      case 'electronics': return 'da Assistência';
      case 'food_service': return 'do Food Truck';
      default: return 'da Oficina';
    }
  };
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchDashboardData = async () => {
    try {
      const response = await api.get('/dashboard/stats');
      setData(response.data);
    } catch (error) {
      console.error("Dashboard error:", error);
      Alert.alert('Erro', 'Não foi possível carregar os dados do painel.');
    } finally {
      // Simular um pequeno delay para mostrar o skeleton se for muito rápido
      setTimeout(() => {
        setLoading(false);
        setRefreshing(false);
      }, 800);
    }
  };

  useEffect(() => {
    fetchDashboardData();
  }, []);

  // Scroll to top when "Início" tab is pressed again
  const navigation = useNavigation();
  useEffect(() => {
    const unsubscribe = navigation.addListener('tabPress' as any, () => {
      scrollRef.current?.scrollTo({ y: 0, animated: true });
    });
    return unsubscribe;
  }, [navigation]);

  const onRefresh = useCallback(() => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    setRefreshing(true);
    fetchDashboardData();
  }, []);

  const handleActionPress = (route: string) => {
    Haptics.selectionAsync();
    router.push(route as any);
  };

  const renderSkeleton = () => (
    <View style={styles.adminStatsContainer}>
      <View style={styles.executiveGrid}>
        {[1, 2, 3, 4].map(i => (
          <View key={i} style={[styles.executiveCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <Skeleton width={32} height={32} borderRadius={10} style={{ marginBottom: 10 }} />
            <Skeleton width="60%" height={12} style={{ marginBottom: 8 }} />
            <Skeleton width="90%" height={24} style={{ marginBottom: 8 }} />
            <Skeleton width="40%" height={10} />
          </View>
        ))}
      </View>
      <Skeleton width="40%" height={18} style={{ marginVertical: 15 }} />
      <View style={styles.quickActionsContainer}>
        {[1, 2, 3, 4].map(i => (
          <View key={i} style={styles.quickActionItem}>
            <Skeleton width={48} height={48} borderRadius={16} style={{ marginBottom: 6 }} />
            <Skeleton width="80%" height={10} />
          </View>
        ))}
      </View>
      <Skeleton width="60%" height={18} style={{ marginVertical: 15 }} />
      <Skeleton width="100%" height={160} borderRadius={24} />
      <View style={{ height: 20 }} />
      <Skeleton width="60%" height={18} style={{ marginBottom: 15 }} />
      <View style={styles.statusGrid}>
        {[1, 2, 3].map(i => (
          <View key={i} style={[styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <Skeleton width={40} height={40} borderRadius={20} style={{ marginBottom: 10 }} />
            <Skeleton width="60%" height={18} style={{ marginBottom: 5 }} />
            <Skeleton width="80%" height={10} />
          </View>
        ))}
      </View>
    </View>
  );

  const renderAdminHeader = () => {
    if (!data) return null;
    return (
      <Animated.View entering={FadeInDown.duration(600).springify()} style={styles.adminStatsContainer}>
        {/* Executive 2x2 Grid */}
        <View style={styles.executiveGrid}>
          <AnimatedCard
            style={[styles.executiveCard, { backgroundColor: colors.card, borderColor: colors.border, ...premiumShadow }]}
            onPress={() => router.push('/reports/revenue')}
          >
            <View style={[styles.miniIcon, { backgroundColor: '#7367F015' }]}>
              <Ionicons name="cash-outline" size={18} color="#7367F0" />
            </View>
            <Text style={[styles.statLabel, { color: colors.subText }]}>Faturamento</Text>
            <Text style={[styles.statValue, { color: colors.text, fontSize: 18 }]}>R$ {numberFormat(data.monthlyRevenue)}</Text>
            <View style={styles.growthContainer}>
              <Ionicons
                name={data.revenueGrowth >= 0 ? "trending-up" : "trending-down"}
                size={12}
                color={data.revenueGrowth >= 0 ? "#28C76F" : "#EA5455"}
              />
              <Text style={[styles.growthText, { color: data.revenueGrowth >= 0 ? "#28C76F" : "#EA5455", fontSize: 10 }]}>
                {Math.abs(data.revenueGrowth || 0).toFixed(1)}%
              </Text>
            </View>
          </AnimatedCard>

          <AnimatedCard
            style={[styles.executiveCard, { backgroundColor: colors.card, borderColor: colors.border, ...premiumShadow }]}
            onPress={() => router.push('/reports/profitability')}
          >
            <View style={[styles.miniIcon, { backgroundColor: '#28C76F15' }]}>
              <Ionicons name="pie-chart-outline" size={18} color="#28C76F" />
            </View>
            <Text style={[styles.statLabel, { color: colors.subText }]}>{t('profitability')}</Text>
            <Text style={[styles.statValue, { color: colors.text, fontSize: 18 }]}>{data.monthlyProfitability || 0}%</Text>
            <Text style={[styles.statSubLabel, { color: colors.subText, fontSize: 10 }]}>Margem real</Text>
          </AnimatedCard>

          <AnimatedCard
            style={[styles.executiveCard, { backgroundColor: colors.card, borderColor: colors.border, ...premiumShadow }]}
            onPress={() => router.push('/reports/clients')}
          >
            <View style={[styles.miniIcon, { backgroundColor: '#FF9F4315' }]}>
              <Ionicons name="people-outline" size={18} color="#FF9F43" />
            </View>
            <Text style={[styles.statLabel, { color: colors.subText }]}>{t('new_clients')}</Text>
            <Text style={[styles.statValue, { color: colors.text, fontSize: 18 }]}>{data.totalClients || 0}</Text>
            <Text style={[styles.statSubLabel, { color: colors.subText, fontSize: 10 }]}>Base ativa</Text>
          </AnimatedCard>

          <AnimatedCard
            style={[styles.executiveCard, { backgroundColor: colors.card, borderColor: colors.border, ...premiumShadow }]}
            onPress={() => router.push('/reports/productivity')}
          >
            <View style={[styles.miniIcon, { backgroundColor: '#00CFE815' }]}>
              <Ionicons name="flash-outline" size={18} color="#00CFE8" />
            </View>
            <Text style={[styles.statLabel, { color: colors.subText }]}>Produtividade</Text>
            <Text style={[styles.statValue, { color: colors.text, fontSize: 18 }]}>
              {data.osStats?.finalized_today || 0}/{((data.osStats?.pending || 0) + (data.osStats?.approved || 0) + (data.osStats?.running || 0) + (data.osStats?.finalized_today || 0)) || 0}
            </Text>
            <View style={styles.miniProgressBarContainer}>
              <View style={[styles.miniProgressBar, {
                width: `${Math.min(100, (data.osStats?.finalized_today * 100 / ((data.osStats?.pending + (data.osStats?.approved || 0) + data.osStats?.running + data.osStats?.finalized_today) || 1)))}%`,
                backgroundColor: '#00CFE8'
              }]} />
            </View>
          </AnimatedCard>
        </View>

        {/* Quick Actions Bar */}
        <Text style={[styles.sectionTitle, { color: colors.text, marginTop: 10, fontSize: 14, textTransform: 'uppercase', letterSpacing: 1 }]}>Ações Rápidas</Text>
        <View style={styles.quickActionsContainer}>
          {[
            { icon: 'add-circle', label: niche === 'food_service' ? 'Novo Pedido' : 'Nova OS', color: '#7367F0', route: '/os/create' },
            { icon: 'person-add', label: 'Cliente', color: '#28C76F', route: '/clients/create' },
            { icon: 'cube', label: 'Estoque', color: '#FF9F43', route: '/inventory' },
            ...(niche === 'food_service' ? [{ icon: 'receipt', label: 'Balcão', color: '#00CFE8', route: '/os/list' }] : [{ icon: 'calendar', label: 'Agenda', color: '#00CFE8', route: '/calendar' }]),
          ].map((action, idx) => (
            <Pressable 
              key={idx} 
              style={({ pressed }) => [styles.quickActionItem, { opacity: pressed ? 0.7 : 1 }]} 
              onPress={() => {
                Haptics.selectionAsync();
                router.push(action.route as any);
              }}
            >
              <View style={[styles.quickActionIcon, { backgroundColor: action.color + '15' }]}>
                <Ionicons name={action.icon as any} size={22} color={action.color} />
              </View>
              <Text style={[styles.quickActionLabel, { color: colors.text }]}>{action.label}</Text>
            </Pressable>
          ))}
        </View>

        {/* Financial Trends Chart */}
        <Pressable
          style={({ pressed }) => [{ marginTop: 10, opacity: pressed ? 0.9 : 1 }]}
          onPress={() => {
            Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
            router.push('/reports/chart');
          }}
        >
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
            <Text style={[styles.sectionTitle, { color: colors.text }]}>Fluxo de Receita (7 dias)</Text>
            <Ionicons name="chevron-forward" size={16} color={colors.text} style={{ opacity: 0.5, marginBottom: 8 }} />
          </View>
          <View style={[styles.chartContainer, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <View style={styles.chartBars}>
              {data.revenueChart?.map((day: any, idx: number) => {
                const maxVal = Math.max(...data.revenueChart.map((d: any) => d.value), 1);
                const height = (day.value / maxVal) * 100;
                const isSelected = selectedChartIndex === idx;
                return (
                  <Pressable
                    key={idx}
                    style={styles.chartColumn}
                    onPress={() => {
                      Haptics.selectionAsync();
                      setSelectedChartIndex(isSelected ? null : idx);
                    }}
                  >
                    <View style={styles.chartValueWrapper}>
                      {(day.value > 0 && (isSelected || idx === 6)) && (
                        <Animated.View entering={FadeIn.duration(200)} exiting={FadeOut.duration(200)}>
                          <Text style={[styles.chartValueText, { color: colors.text, fontWeight: isSelected ? 'bold' : 'normal' }]}>
                            R$ {day.value > 1000 ? (day.value / 1000).toFixed(1) + 'k' : day.value.toFixed(0)}
                          </Text>
                        </Animated.View>
                      )}
                    </View>
                    <View style={[
                      styles.chartBar,
                      {
                        height: `${Math.max(2, height)}%`,
                        backgroundColor: isSelected ? '#CE9FFC' : (idx === 6 ? '#7367F0' : '#7367F040'),
                        width: isSelected ? 14 : 10,
                      }
                    ]} />
                    <Text style={[styles.chartLabel, { color: colors.subText, fontWeight: isSelected ? 'bold' : 'normal' }]}>{day.day}</Text>
                  </Pressable>
                );
              })}
            </View>
          </View>
        </Pressable>

        <Text style={[styles.sectionTitle, { color: colors.text, marginTop: 10 }]}>Status Operacional</Text>

        {/* Operational Grid */}
        <View style={styles.statusGrid}>
          <Pressable
            style={({ pressed }) => [styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border, opacity: pressed ? 0.8 : 1 }]}
            onPress={() => {
              Haptics.selectionAsync();
              router.push({ pathname: '/os/list', params: { status: 'pending', title: 'Ordens Pendentes' } });
            }}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#FF9F4320' }]}>
              <Ionicons name="hourglass-outline" size={20} color="#FF9F43" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.osStats?.pending || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Pendentes</Text>
          </Pressable>

          <Pressable
            style={({ pressed }) => [styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border, opacity: pressed ? 0.8 : 1 }]}
            onPress={() => {
              Haptics.selectionAsync();
              router.push({ pathname: '/os/list', params: { status: 'approved', title: 'Ordens Aprovadas' } });
            }}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#00CFE820' }]}>
              <Ionicons name="thumbs-up-outline" size={20} color="#00CFE8" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.osStats?.approved || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Aprovadas</Text>
          </Pressable>

          <Pressable
            style={({ pressed }) => [styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border, opacity: pressed ? 0.8 : 1 }]}
            onPress={() => {
              Haptics.selectionAsync();
              router.push({ pathname: '/os/list', params: { status: 'running', title: 'Em Execução' } });
            }}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#00CFE820' }]}>
              <Ionicons name="play-outline" size={20} color="#00CFE8" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.osStats?.running || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Em Execução</Text>
          </Pressable>

          <Pressable
            style={({ pressed }) => [styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border, opacity: pressed ? 0.8 : 1 }]}
            onPress={() => {
              Haptics.selectionAsync();
              router.push({ pathname: '/os/list', params: { status: 'finalized', title: 'Finalizadas Hoje' } });
            }}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#28C76F20' }]}>
              <Ionicons name="checkmark-done-outline" size={20} color="#28C76F" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.osStats?.finalized_today || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Finalizadas Hoje</Text>
          </Pressable>
        </View>

        {/* Alerts Section */}
        {(data.lowStockCount > 0 || data.pendingBudgetsCount > 0) && (
          <View style={styles.alertsContainer}>
            <Text style={[styles.sectionTitle, { color: colors.text }]}>Alertas Críticos</Text>
            <View style={styles.alertRow}>
              {data.lowStockCount > 0 && (
                <Pressable 
                  style={({ pressed }) => [styles.alertItem, { backgroundColor: '#EA545515', opacity: pressed ? 0.8 : 1 }]} 
                  onPress={() => router.push('/inventory')}
                >
                  <Ionicons name="warning" size={20} color="#EA5455" />
                  <Text style={[styles.alertText, { color: colors.text }]}>{data.lowStockCount} itens com baixo estoque</Text>
                </Pressable>
              )}
              {data.pendingBudgetsCount > 0 && (
                <Pressable 
                  style={({ pressed }) => [styles.alertItem, { backgroundColor: '#FF9F4315', opacity: pressed ? 0.8 : 1 }]} 
                  onPress={() => router.push('/budgets/pending')}
                >
                  <Ionicons name="document-text" size={20} color="#FF9F43" />
                  <Text style={[styles.alertText, { color: colors.text }]}>{data.pendingBudgetsCount} orçamentos atrasados {'>'} 5 dias</Text>
                </Pressable>
              )}
            </View>
          </View>
        )}
      </Animated.View>
    );
  };

  const renderMechanicHeader = () => {
    if (!data) return null;
    const totalToday = (data.stats?.runningOS || 0) + (data.stats?.completedToday || 0);
    const progress = totalToday > 0 ? (data.stats?.completedToday / totalToday) * 100 : 0;

    return (
      <Animated.View entering={FadeInDown.duration(600).springify()} style={styles.adminStatsContainer}>
        {/* Progress Overview Card */}
        <View style={[styles.mainProgressCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <View style={styles.progressHeader}>
            <View>
              <Text style={[styles.progressTitle, { color: colors.text }]}>Seu Progresso de Hoje</Text>
              <Text style={[styles.progressSubtitle, { color: colors.subText }]}>
                {data.stats?.completedToday || 0} de {totalToday} tarefas finalizadas
              </Text>
            </View>
            <View style={[styles.percentageCircle, { borderColor: colors.primary + '30' }]}>
              <Text style={[styles.percentageText, { color: colors.primary }]}>{progress.toFixed(0)}%</Text>
            </View>
          </View>
          <View style={[styles.progressBarBg, { backgroundColor: colors.border + '40' }]}>
            <View style={[styles.progressBarFill, { width: `${progress}%`, backgroundColor: colors.primary }]} />
          </View>
        </View>

        {/* Quick Actions for Employee */}
        <View style={styles.quickActionsContainer}>
          {[
            ...(niche === 'food_service' ? [] : [{ icon: 'scan-outline', label: 'Vistoria', color: '#7367F0', route: '/os/checklist' }]),
            { icon: 'add-circle-outline', label: niche === 'food_service' ? 'Novo Pedido' : 'Nova OS', color: '#28C76F', route: '/os/create' },
            { icon: 'cube-outline', label: 'Estoque', color: '#FF9F43', route: '/inventory' },
            ...(niche === 'food_service' ? [{ icon: 'receipt-outline', label: 'Balcão', color: '#00CFE8', route: '/os/list' }] : [{ icon: 'calendar-outline', label: 'Agenda', color: '#00CFE8', route: '/calendar' }]),
          ].map((action, idx) => (
            <Pressable 
              key={idx} 
              style={({ pressed }) => [styles.quickActionItem, { opacity: pressed ? 0.7 : 1 }]} 
              onPress={() => {
                Haptics.selectionAsync();
                router.push(action.route as any);
              }}
            >
              <View style={[styles.quickActionIcon, { backgroundColor: action.color + '15' }]}>
                <Ionicons name={action.icon as any} size={22} color={action.color} />
              </View>
              <Text style={[styles.quickActionLabel, { color: colors.text }]}>{action.label}</Text>
            </Pressable>
          ))}
        </View>

        {/* Ghotme IA Inside - Tech Insight */}
        <View style={[styles.aiInsightCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <View style={styles.aiHeader}>
            <View style={styles.aiIconWrapper}>
              <Ionicons name="sparkles" size={16} color="#fff" />
            </View>
            <Text style={[styles.aiTitle, { color: colors.primary }]}>Ghotme IA • Produtividade</Text>
          </View>
          <Text style={[styles.aiInsightText, { color: colors.text }]}>
            {progress >= 100
              ? "Incrível! Você concluiu todas as suas ordens de hoje. Que tal revisar o estoque ou organizar a agenda de amanhã?"
              : progress > 50
                ? "Você está no ritmo certo! Já passou da metade das suas tarefas. Continue assim para fechar o dia com chave de ouro."
                : "Temos algumas ordens aguardando você. Focar na finalização da OS mais antiga pode ajudar a liberar espaço na bancada."}
          </Text>
        </View>

        {/* Mechanic Summary Grid */}
        <Text style={[styles.sectionTitle, { color: colors.text, marginTop: 10, fontSize: 14, textTransform: 'uppercase', letterSpacing: 1 }]}>Status das Minhas OS</Text>
        <View style={styles.statusGrid}>
          <Pressable
            style={({ pressed }) => [styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border, opacity: pressed ? 0.8 : 1 }]}
            onPress={() => router.push({ pathname: '/os/list', params: { status: 'running', title: 'Minhas Ordens' } })}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#00CFE820' }]}>
              <Ionicons name="play-outline" size={20} color="#00CFE8" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.stats?.runningOS || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Em Execução</Text>
          </Pressable>

          <Pressable
            style={({ pressed }) => [styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border, opacity: pressed ? 0.8 : 1 }]}
            onPress={() => router.push({ pathname: '/os/list', params: { status: 'finalized', title: 'Minhas Prontas' } })}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#28C76F20' }]}>
              <Ionicons name="checkmark-done-outline" size={20} color="#28C76F" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.stats?.completedToday || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Finalizadas Hoje</Text>
          </Pressable>

          <Pressable
            style={({ pressed }) => [styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border, opacity: pressed ? 0.8 : 1 }]}
            onPress={() => router.push({ pathname: '/os/list', params: { status: 'pending', title: 'Meus Orçamentos' } })}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#FF9F4320' }]}>
              <Ionicons name="document-text-outline" size={20} color="#FF9F43" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.stats?.pendingBudgets || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Meus Orçamentos</Text>
          </Pressable>
        </View>

        <View style={{ marginTop: 10 }}>
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
            <Text style={[styles.sectionTitle, { color: colors.text, marginBottom: 0 }]}>Minhas Atividades</Text>
            <Pressable onPress={() => router.push('/os/list')} style={({ pressed }) => [{ opacity: pressed ? 0.6 : 1 }]}>
              <Text style={{ color: colors.primary, fontWeight: '600' }}>Ver Tudo</Text>
            </Pressable>
          </View>
          {data?.recentOS?.map((item: any, index: number) => renderOSCard(item, index))}
          {(!data?.recentOS || data.recentOS.length === 0) && (
            <View style={[styles.emptyContainer, { backgroundColor: colors.card, borderColor: colors.border }]}>
              <Ionicons name="clipboard-outline" size={40} color={colors.subText} />
              <Text style={[styles.emptyText, { color: colors.subText }]}>Sem ordens atribuídas hj.</Text>
            </View>
          )}
        </View>
      </Animated.View>
    );
  };

  const renderOSCard = (item: any, index: number = 0) => (
    <Animated.View
      key={item.id}
      entering={FadeInDown.delay(index * 100).duration(600).springify()}
    >
      <Pressable
        style={({ pressed }) => [
          styles.card, 
          { backgroundColor: colors.card, borderColor: colors.border },
          pressed && { opacity: 0.9, transform: [{ scale: 0.98 }] }
        ]}
        onPress={() => router.push(`/os/${item.id}`)}
      >
        <View style={styles.cardHeader}>
          <View style={styles.idBadge}>
            <Text style={styles.idText}>#{item.id}</Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '15' }]}>
            <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
              {statusTranslations[item.status?.toLowerCase()] || item.status}
            </Text>
          </View>
        </View>

        <View style={styles.cardBody}>
          <View style={styles.infoRow}>
            <Ionicons name="person-outline" size={16} color={colors.subText} style={{ marginRight: 6 }} />
            <Text style={[styles.clientName, { color: colors.text }]} numberOfLines={1}>{item.client_name}</Text>
          </View>
          <View style={styles.infoRow}>
            <Ionicons name={getEntityIcon(niche) as any} size={16} color={colors.subText} style={{ marginRight: 6 }} />
            <Text style={[styles.vehicleInfo, { color: colors.subText }]}>
              {niche === 'food_service' ? (item.plate || 'Balcão/Mesa') : `${item.vehicle} - ${item.plate}`}
            </Text>
          </View>
          <View style={[styles.divider, { backgroundColor: colors.border }]} />
          <View style={styles.cardFooter}>
            <View style={styles.dateContainer}>
              <Ionicons name="calendar-outline" size={14} color={colors.subText} />
              <Text style={[styles.dateText, { color: colors.subText }]}>{new Date(item.created_at).toLocaleDateString('pt-BR')}</Text>
            </View>
            <Text style={{ fontSize: 14, fontWeight: '800', color: colors.primary }}>
              R$ {numberFormat(item.total)}
            </Text>
          </View>
        </View>
      </Pressable>
    </Animated.View>
  );

  return (
    <View style={[styles.container, { backgroundColor: colors.background }]}>
      <StatusBar barStyle="light-content" backgroundColor="#7367F0" />

      {/* Header with Gradient */}
      <LinearGradient
        colors={['#7367F0', '#CE9FFC']}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.header}
      >
        <View style={styles.headerContent}>
          <View>
            <Text style={styles.welcomeText}>{t('hello')}, {user?.name || t('welcome')}</Text>
            <Text style={styles.subtitleText}>
              {user?.role === 'admin' ? t('summary_today', { niche: getEstablishmentName() }) : t('portal')}
            </Text>
          </View>

          <View style={{ flexDirection: 'row', alignItems: 'center' }}>
            <Pressable
              style={({ pressed }) => [styles.chatButton, { opacity: pressed ? 0.6 : 1 }]}
              onPress={() => router.push('/screens/notifications')}
            >
              <Ionicons name="notifications-outline" size={24} color="#fff" />
              {data?.unreadNotificationsCount > 0 && (
                <View style={[styles.badge, { backgroundColor: '#EA5455' }]}>
                  <Text style={styles.badgeText}>{data.unreadNotificationsCount > 9 ? '9+' : data.unreadNotificationsCount}</Text>
                </View>
              )}
            </Pressable>

            <Pressable
              style={({ pressed }) => [styles.chatButton, { opacity: pressed ? 0.6 : 1 }]}
              onPress={() => router.push('/chat/contacts')}
            >
              <Ionicons name="chatbubbles-outline" size={24} color="#fff" />
              {unreadCount > 0 && (
                <View style={styles.badge}>
                  <Text style={styles.badgeText}>{unreadCount > 9 ? '9+' : unreadCount}</Text>
                </View>
              )}
            </Pressable>

            <Pressable 
              style={({ pressed }) => [styles.profileButton, { opacity: pressed ? 0.8 : 1 }]} 
              onPress={() => router.push('/profile')}
            >
              <View style={styles.avatarPlaceholder}>
                {user?.profile_photo_url ? (
                  <Image
                    source={{ uri: user.profile_photo_url }}
                    style={{ width: '100%', height: '100%', borderRadius: 18 }}
                    contentFit="cover"
                  />
                ) : (
                  <Text style={styles.avatarText}>
                    {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
                  </Text>
                )}
              </View>
            </Pressable>
          </View>
        </View>
      </LinearGradient>

      {/* Content Scroll Area */}
      <ScrollView
        ref={scrollRef}
        style={styles.listContainer}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            colors={['#7367F0']}
            tintColor="#7367F0"
            progressViewOffset={140}
          />
        }
      >
        {loading && !refreshing ? (
          renderSkeleton()
        ) : (
          <View>
            {user?.role === 'admin' ? (
              <>
                {renderAdminHeader()}
                <View style={{ marginTop: 10 }}>
                  <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
                    <Text style={[styles.sectionTitle, { color: colors.text, marginBottom: 0 }]}>Atividades Recentes</Text>
                  </View>
                  {data?.recentOS?.map((item: any, index: number) => renderOSCard(item, index))}
                  {(!data?.recentOS || data.recentOS.length === 0) && (
                    <View style={[styles.emptyContainer, { backgroundColor: colors.card, borderColor: colors.border }]}>
                      <Ionicons name="clipboard-outline" size={40} color={colors.subText} />
                      <Text style={[styles.emptyText, { color: colors.subText }]}>Nenhuma atividade recente.</Text>
                    </View>
                  )}
                </View>
              </>
            ) : renderMechanicHeader()}
          </View>
        )}
      </ScrollView>

      {/* Floating Action Button */}
      <Animated.View
        entering={FadeInUp.delay(500).springify()}
        style={styles.fabContainer}
      >
        <Pressable
          style={({ pressed }) => [
            styles.fab,
            pressed && { transform: [{ scale: 0.95 }], opacity: 0.9 }
          ]}
          onPress={() => router.push('/os/create')}
        >
          <Ionicons name="add" size={32} color="#fff" />
        </Pressable>
      </Animated.View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f2f2f7',
  },
  header: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    paddingTop: 60,
    paddingBottom: 25,
    paddingHorizontal: 24,
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
    zIndex: 10,
  },
  headerContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  welcomeText: {
    color: '#fff',
    fontSize: 22,
    fontWeight: '800',
    letterSpacing: -0.5,
  },
  subtitleText: {
    color: 'rgba(255,255,255,0.85)',
    fontSize: 14,
    marginTop: 4,
    fontWeight: '500',
  },
  profileButton: {
    padding: 4,
    justifyContent: 'center',
    alignItems: 'center',
  },
  chatButton: {
    padding: 8,
    marginRight: 4,
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  badge: {
    position: 'absolute',
    top: 6,
    right: 4,
    backgroundColor: '#EA5455',
    width: 18,
    height: 18,
    borderRadius: 9,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1.5,
    borderColor: '#7367F0'
  },
  badgeText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: '900'
  },
  avatarPlaceholder: {
    width: 38,
    height: 38,
    borderRadius: 19,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.25)',
    overflow: 'hidden',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.5)',
  },
  avatarText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold'
  },
  listContainer: {
    flex: 1,
    paddingTop: 130, 
  },
  scrollContent: {
    paddingHorizontal: 20,
    paddingBottom: 100,
    paddingTop: 20,
  },
  loadingContainer: {
    marginTop: 50,
    alignItems: 'center',
  },
  adminStatsContainer: {
    width: '100%',
  },
  horizontalScroll: {
    marginHorizontal: -20,
    paddingHorizontal: 20,
    marginBottom: 25,
  },
  statCard: {
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 16,
    marginRight: 15,
    width: 200,
    borderLeftWidth: 5,
    borderWidth: 1, 
    borderColor: '#e5e5ea',
  },
  statLabel: {
    fontSize: 12,
    color: '#8e8e93',
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 4,
    letterSpacing: 0.5,
  },
  statValue: {
    fontSize: 20,
    fontWeight: '800',
    color: '#1c1c1e',
  },
  statSubLabel: {
    fontSize: 11,
    color: '#aeaeb2',
    marginTop: 4,
    fontWeight: '500',
  },
  growthContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
  },
  growthText: {
    fontSize: 11,
    fontWeight: '800',
    marginLeft: 4,
  },
  sectionTitle: {
    fontSize: 17,
    fontWeight: '800',
    color: '#1c1c1e',
    marginBottom: 15,
    letterSpacing: -0.2,
  },
  statusGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    gap: 12,
    marginBottom: 25,
  },
  statusBox: {
    flex: 1,
    minWidth: '45%',
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 16,
    alignItems: 'center',
    borderWidth: 1, 
    borderColor: '#e5e5ea',
  },
  statusIcon: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  statusCountText: {
    fontSize: 20,
    fontWeight: '800',
    color: '#1c1c1e',
  },
  statusLabelText: {
    fontSize: 12,
    color: '#8e8e93',
    fontWeight: '600',
    marginTop: 2,
    textAlign: 'center',
  },
  alertsContainer: {
    marginBottom: 25,
  },
  alertRow: {
    gap: 12,
  },
  executiveGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    gap: 12,
    marginBottom: 20,
  },
  executiveCard: {
    width: '48%',
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: '#e5e5ea',
    backgroundColor: '#fff',
  },
  miniIcon: {
    width: 36,
    height: 36,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  miniProgressBarContainer: {
    height: 5,
    backgroundColor: '#f2f2f7',
    borderRadius: 3,
    marginTop: 12,
    overflow: 'hidden',
  },
  miniProgressBar: {
    height: '100%',
    borderRadius: 3,
  },
  quickActionsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 16,
    marginVertical: 15,
    borderWidth: 1,
    borderColor: '#e5e5ea',
  },
  quickActionItem: {
    alignItems: 'center',
    width: '22%',
  },
  quickActionIcon: {
    width: 48,
    height: 48,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  quickActionLabel: {
    fontSize: 11,
    fontWeight: '600',
    textAlign: 'center',
    color: '#1c1c1e',
  },
  alertItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  alertText: {
    fontSize: 14,
    fontWeight: '700',
    color: '#1c1c1e',
    marginLeft: 12,
  },
  chartContainer: {
    padding: 20,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#e5e5ea',
    backgroundColor: '#fff',
    marginBottom: 20,
  },
  chartBars: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    justifyContent: 'space-between',
    height: 120,
    paddingTop: 20,
  },
  chartColumn: {
    alignItems: 'center',
    width: '12%',
  },
  chartBar: {
    width: 12,
    borderRadius: 6,
  },
  chartLabel: {
    fontSize: 10,
    marginTop: 8,
    fontWeight: '600',
  },
  chartValueWrapper: {
    position: 'absolute',
    top: -18,
    width: 40,
    alignItems: 'center',
  },
  chartValueText: {
    fontSize: 9,
    fontWeight: '800',
  },
  card: {
    backgroundColor: '#fff',
    borderRadius: 16,
    marginBottom: 16,
    padding: 16,
    borderWidth: 1, 
    borderColor: '#e5e5ea',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  idBadge: {
    backgroundColor: '#f2f2f7',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  idText: {
    color: '#1c1c1e',
    fontWeight: '800',
    fontSize: 12,
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  cardBody: {
    gap: 8,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  clientName: {
    fontSize: 16,
    fontWeight: '800',
    color: '#1c1c1e',
    flex: 1,
  },
  vehicleInfo: {
    fontSize: 14,
    color: '#8e8e93',
    fontWeight: '500',
  },
  divider: {
    height: 1,
    backgroundColor: '#f2f2f7',
    marginVertical: 8,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  dateContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  dateText: {
    fontSize: 13,
    color: '#8e8e93',
    marginLeft: 6,
    fontWeight: '600',
  },
  fabContainer: {
    position: 'absolute',
    bottom: 100,
    right: 20,
    zIndex: 100,
  },
  fab: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#7367F0',
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 4,
    shadowColor: '#7367F0',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
  },
  mainProgressCard: {
    padding: 20,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#e5e5ea',
    backgroundColor: '#fff',
    marginBottom: 20,
  },
  progressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
  },
  progressTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: '#1c1c1e',
  },
  progressSubtitle: {
    fontSize: 13,
    marginTop: 2,
    color: '#8e8e93',
    fontWeight: '500',
  },
  percentageCircle: {
    width: 52,
    height: 52,
    borderRadius: 26,
    borderWidth: 2,
    alignItems: 'center',
    justifyContent: 'center',
  },
  percentageText: {
    fontSize: 15,
    fontWeight: '900',
  },
  progressBarBg: {
    height: 6,
    borderRadius: 3,
    overflow: 'hidden',
    backgroundColor: '#f2f2f7',
  },
  progressBarFill: {
    height: '100%',
    borderRadius: 3,
  },
  aiInsightCard: {
    padding: 16,
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 25,
    borderLeftWidth: 4,
    borderLeftColor: '#7367F0',
    backgroundColor: '#fff',
  },
  aiHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
    gap: 8,
  },
  aiIconWrapper: {
    padding: 6,
    backgroundColor: '#7367F0',
    borderRadius: 8,
  },
  aiTitle: {
    fontSize: 13,
    fontWeight: '800',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  aiInsightText: {
    fontSize: 14,
    lineHeight: 22,
    color: '#3a3a3c',
    fontWeight: '500',
  },
  emptyContainer: {
    paddingVertical: 40,
    borderRadius: 16,
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: 'center',
    borderStyle: 'dashed',
    backgroundColor: '#f2f2f7',
    borderColor: '#c7c7cc',
  },
  emptyText: {
    marginTop: 12,
    fontSize: 15,
    fontWeight: '600',
  }
});

