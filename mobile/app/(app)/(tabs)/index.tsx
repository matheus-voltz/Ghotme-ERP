import React, { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  ActivityIndicator,
  Alert,
  TouchableOpacity,
  StyleSheet,
  StatusBar,
  RefreshControl,
  ScrollView,
  Platform
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
import { useRouter, router } from 'expo-router';
import Animated, { FadeInDown, FadeInUp, FadeIn, FadeOut } from 'react-native-reanimated';
import * as Haptics from 'expo-haptics';
import { Skeleton } from '../../../components/Skeleton';

// Helper for status translations
const statusTranslations: { [key: string]: string } = {
  'pending': 'Pendente',
  'running': 'Em Execução',
  'finalized': 'Finalizada',
  'canceled': 'Cancelada',
};

// Helper for status colors (Platform Palette)
const getStatusColor = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'pending': return '#FF9F43'; // Warning
    case 'running': return '#00CFE8'; // Info/Execution
    case 'finalized': return '#28C76F'; // Success
    case 'canceled': return '#EA5455'; // Danger
    default: return '#7367F0'; // Primary
  }
};

const numberFormat = (value: any) => {
  return parseFloat(value || 0).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

export default function DashboardScreen() {
  const { user } = useAuth();
  const { colors, activeTheme } = useTheme();
  const { labels, niche } = useNiche();
  const { unreadCount } = useChat();
  const { t, language } = useLanguage();
  const [data, setData] = useState<any>(null);
  const [selectedChartIndex, setSelectedChartIndex] = useState<number | null>(null);

  const getEstablishmentName = () => {
    switch (niche) {
      case 'pet': return 'do Pet Shop';
      case 'beauty_clinic': return 'da Clínica';
      case 'electronics': return 'da Assistência';
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
          <View style={[styles.executiveCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <View style={[styles.miniIcon, { backgroundColor: '#7367F020' }]}>
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
          </View>

          <View style={[styles.executiveCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <View style={[styles.miniIcon, { backgroundColor: '#28C76F20' }]}>
              <Ionicons name="pie-chart-outline" size={18} color="#28C76F" />
            </View>
            <Text style={[styles.statLabel, { color: colors.subText }]}>{t('profitability')}</Text>
            <Text style={[styles.statValue, { color: colors.text, fontSize: 18 }]}>{data.monthlyProfitability || 0}%</Text>
            <Text style={[styles.statSubLabel, { color: colors.subText, fontSize: 10 }]}>Margem real</Text>
          </View>

          <View style={[styles.executiveCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <View style={[styles.miniIcon, { backgroundColor: '#FF9F4320' }]}>
              <Ionicons name="people-outline" size={18} color="#FF9F43" />
            </View>
            <Text style={[styles.statLabel, { color: colors.subText }]}>{t('new_clients')}</Text>
            <Text style={[styles.statValue, { color: colors.text, fontSize: 18 }]}>{data.totalClients || 0}</Text>
            <Text style={[styles.statSubLabel, { color: colors.subText, fontSize: 10 }]}>Base ativa</Text>
          </View>

          <View style={[styles.executiveCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <View style={[styles.miniIcon, { backgroundColor: '#00CFE820' }]}>
              <Ionicons name="flash-outline" size={18} color="#00CFE8" />
            </View>
            <Text style={[styles.statLabel, { color: colors.subText }]}>Produtividade</Text>
            <Text style={[styles.statValue, { color: colors.text, fontSize: 18 }]}>
              {data.osStats?.finalized_today || 0}/{((data.osStats?.pending || 0) + (data.osStats?.running || 0) + (data.osStats?.finalized_today || 0)) || 0}
            </Text>
            <View style={styles.miniProgressBarContainer}>
              <View style={[styles.miniProgressBar, {
                width: `${Math.min(100, (data.osStats?.finalized_today * 100 / ((data.osStats?.pending + data.osStats?.running + data.osStats?.finalized_today) || 1)))}%`,
                backgroundColor: '#00CFE8'
              }]} />
            </View>
          </View>
        </View>

        {/* Quick Actions Bar */}
        <Text style={[styles.sectionTitle, { color: colors.text, marginTop: 10, fontSize: 14, textTransform: 'uppercase', letterSpacing: 1 }]}>Ações Rápidas</Text>
        <View style={styles.quickActionsContainer}>
          {[
            { icon: 'add-circle', label: 'Nova OS', color: '#7367F0', route: '/os/create' },
            { icon: 'person-add', label: 'Cliente', color: '#28C76F', route: '/clients/create' },
            { icon: 'cube', label: 'Estoque', color: '#FF9F43', route: '/inventory' },
            { icon: 'calendar', label: 'Agenda', color: '#00CFE8', route: '/calendar' },
          ].map((action, idx) => (
            <TouchableOpacity key={idx} style={styles.quickActionItem} onPress={() => {
              Haptics.selectionAsync();
              router.push(action.route as any);
            }}>
              <View style={[styles.quickActionIcon, { backgroundColor: action.color + '15' }]}>
                <Ionicons name={action.icon as any} size={22} color={action.color} />
              </View>
              <Text style={[styles.quickActionLabel, { color: colors.text }]}>{action.label}</Text>
            </TouchableOpacity>
          ))}
        </View>

        {/* Financial Trends Chart */}
        <View style={{ marginTop: 10 }}>
          <Text style={[styles.sectionTitle, { color: colors.text }]}>Fluxo de Receita (7 dias)</Text>
          <View style={[styles.chartContainer, { backgroundColor: colors.card, borderColor: colors.border }]}>
            <View style={styles.chartBars}>
              {data.revenueChart?.map((day: any, idx: number) => {
                const maxVal = Math.max(...data.revenueChart.map((d: any) => d.value), 1);
                const height = (day.value / maxVal) * 100;
                const isSelected = selectedChartIndex === idx;
                return (
                  <TouchableOpacity
                    key={idx}
                    style={styles.chartColumn}
                    activeOpacity={0.8}
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
                  </TouchableOpacity>
                );
              })}
            </View>
          </View>
        </View>

        <Text style={[styles.sectionTitle, { color: colors.text, marginTop: 10 }]}>Status Operacional</Text>

        {/* Operational Grid */}
        <View style={styles.statusGrid}>
          <TouchableOpacity
            style={[styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border }]}
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
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border }]}
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
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border }]}
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
          </TouchableOpacity>
        </View>

        {/* Alerts Section */}
        {(data.lowStockCount > 0 || data.pendingBudgetsCount > 0) && (
          <View style={styles.alertsContainer}>
            <Text style={[styles.sectionTitle, { color: colors.text }]}>Alertas Críticos</Text>
            <View style={styles.alertRow}>
              {data.lowStockCount > 0 && (
                <TouchableOpacity style={[styles.alertItem, { backgroundColor: '#EA545515' }]} onPress={() => router.push('/inventory')}>
                  <Ionicons name="warning" size={20} color="#EA5455" />
                  <Text style={[styles.alertText, { color: colors.text }]}>{data.lowStockCount} itens com baixo estoque</Text>
                </TouchableOpacity>
              )}
              {data.pendingBudgetsCount > 0 && (
                <TouchableOpacity style={[styles.alertItem, { backgroundColor: '#FF9F4315' }]} onPress={() => Alert.alert('Orçamentos', 'Existem orçamentos aguardando aprovação.')}>
                  <Ionicons name="document-text" size={20} color="#FF9F43" />
                  <Text style={[styles.alertText, { color: colors.text }]}>{data.pendingBudgetsCount} orçamentos pendentes</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        )}
      </Animated.View>
    );
  };

  const renderMechanicHeader = () => {
    if (!data) return null;
    return (
      <Animated.View entering={FadeInDown.duration(600).springify()} style={styles.adminStatsContainer}>
        {/* Mechanic Summary Cards */}
        <View style={styles.statusGrid}>
          <TouchableOpacity
            style={[styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border }]}
            onPress={() => router.push({ pathname: '/os/list', params: { status: 'running', title: 'Minhas Ordens' } })}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#00CFE820' }]}>
              <Ionicons name="play-outline" size={20} color="#00CFE8" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.stats?.runningOS || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Em Execução</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border }]}
            onPress={() => router.push({ pathname: '/os/list', params: { status: 'finalized', title: 'Minhas Prontas' } })}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#28C76F20' }]}>
              <Ionicons name="checkmark-done-outline" size={20} color="#28C76F" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.stats?.completedToday || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Prontas Hoje</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.statusBox, { backgroundColor: colors.card, borderColor: colors.border }]}
            onPress={() => router.push({ pathname: '/os/list', params: { status: 'pending', title: 'Orçamentos' } })}
          >
            <View style={[styles.statusIcon, { backgroundColor: '#FF9F4320' }]}>
              <Ionicons name="document-text-outline" size={20} color="#FF9F43" />
            </View>
            <Text style={[styles.statusCountText, { color: colors.text }]}>{data.stats?.pendingBudgets || 0}</Text>
            <Text style={[styles.statusLabelText, { color: colors.subText }]}>Orçamentos</Text>
          </TouchableOpacity>
        </View>

        <View style={{ marginTop: 10 }}>
          <Text style={[styles.sectionTitle, { color: colors.text }]}>Minhas Atividades</Text>
          {data?.recentOS?.map((item: any, index: number) => renderOSCard(item, index))}
          {(!data?.recentOS || data.recentOS.length === 0) && (
            <View style={[styles.emptyContainer, { backgroundColor: colors.card, borderColor: colors.border }]}>
              <Ionicons name="clipboard-outline" size={40} color={colors.subText} />
              <Text style={[styles.emptyText, { color: colors.subText }]}>Sem ordens atribuídas</Text>
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
      <TouchableOpacity
        style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}
        activeOpacity={0.9}
        onPress={() => router.push(`/os/${item.id}`)}
      >
        <View style={styles.cardHeader}>
          <View style={styles.idBadge}>
            <Text style={styles.idText}>#{item.id}</Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '20' }]}>
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
            <Ionicons name="car-sport-outline" size={16} color={colors.subText} style={{ marginRight: 6 }} />
            <Text style={[styles.vehicleInfo, { color: colors.subText }]}>{item.vehicle} - {item.plate}</Text>
          </View>
          <View style={[styles.divider, { backgroundColor: colors.border }]} />
          <View style={styles.cardFooter}>
            <View style={styles.dateContainer}>
              <Ionicons name="calendar-outline" size={14} color={colors.subText} />
              <Text style={[styles.dateText, { color: colors.subText }]}>{new Date(item.created_at).toLocaleDateString('pt-BR')}</Text>
            </View>
            <Text style={{ fontSize: 13, fontWeight: '700', color: colors.primary }}>
              R$ {numberFormat(item.total)}
            </Text>
          </View>
        </View>
      </TouchableOpacity>
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
            <TouchableOpacity
              style={styles.chatButton}
              onPress={() => router.push('/screens/notifications')}
            >
              <Ionicons name="notifications-outline" size={24} color="#fff" />
              {data?.unreadNotificationsCount > 0 && (
                <View style={[styles.badge, { backgroundColor: '#EA5455' }]}>
                  <Text style={styles.badgeText}>{data.unreadNotificationsCount > 9 ? '9+' : data.unreadNotificationsCount}</Text>
                </View>
              )}
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.chatButton}
              onPress={() => router.push('/chat/contacts')}
            >
              <Ionicons name="chatbubbles-outline" size={24} color="#fff" />
              {unreadCount > 0 && (
                <View style={styles.badge}>
                  <Text style={styles.badgeText}>{unreadCount > 9 ? '9+' : unreadCount}</Text>
                </View>
              )}
            </TouchableOpacity>

            <TouchableOpacity style={styles.profileButton} onPress={() => router.push('/profile')}>
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
            </TouchableOpacity>
          </View>
        </View>
      </LinearGradient>

      {/* Content Scroll Area */}
      <ScrollView
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
                  <Text style={[styles.sectionTitle, { color: colors.text }]}>Atividades Recentes</Text>
                  {data?.recentOS?.map((item: any, index: number) => renderOSCard(item, index))}
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
        <TouchableOpacity
          style={styles.fab}
          onPress={() => router.push('/os/create')}
          activeOpacity={0.8}
        >
          <Ionicons name="add" size={30} color="#fff" />
        </TouchableOpacity>
      </Animated.View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8f9fa',
  },
  header: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    paddingTop: 60,
    paddingBottom: 25,
    paddingHorizontal: 24,
    borderBottomLeftRadius: 30,
    borderBottomRightRadius: 30,
    zIndex: 10,
    // elevation: 5, // Removed to avoid conflict with list scrolling underneath if any
  },
  headerContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  welcomeText: {
    color: '#fff',
    fontSize: 22,
    fontWeight: 'bold',
  },
  subtitleText: {
    color: 'rgba(255,255,255,0.8)',
    fontSize: 14,
    marginTop: 4,
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
    backgroundColor: '#FF9F43',
    width: 16,
    height: 16,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1.5,
    borderColor: '#7367F0'
  },
  badgeText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: 'bold'
  },
  avatarPlaceholder: {
    width: 36,
    height: 36,
    borderRadius: 18,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.2)',
    overflow: 'hidden'
  },
  avatarText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold'
  },
  listContainer: {
    flex: 1,
    paddingTop: 130, // Make space for header
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
    borderWidth: 1, // Added
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05, // Refined
    shadowRadius: 10,   // Refined
    elevation: 2,       // Refined
  },
  statLabel: {
    fontSize: 12,
    color: '#666',
    fontWeight: '600',
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  statValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  statSubLabel: {
    fontSize: 11,
    color: '#999',
    marginTop: 4,
  },
  growthContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
  },
  growthText: {
    fontSize: 11,
    fontWeight: '700',
    marginLeft: 4,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  statusGrid: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 10,
    marginBottom: 25,
  },
  statusBox: {
    flex: 1,
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 15,
    alignItems: 'center',
    borderWidth: 1, // Added
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05, // Refined
    shadowRadius: 10,   // Refined
    elevation: 2,       // Refined
  },
  statusIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  statusCountText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  statusLabelText: {
    fontSize: 11,
    color: '#666',
    fontWeight: '600',
    marginTop: 2,
    textAlign: 'center',
  },
  alertsContainer: {
    marginBottom: 25,
  },
  alertRow: {
    gap: 10,
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
    borderRadius: 20,
    padding: 15,
    borderWidth: 1,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.05,
    shadowRadius: 12,
    elevation: 3,
  },
  miniIcon: {
    width: 32,
    height: 32,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  miniProgressBarContainer: {
    height: 4,
    backgroundColor: 'rgba(0,0,0,0.05)',
    borderRadius: 2,
    marginTop: 10,
    overflow: 'hidden',
  },
  miniProgressBar: {
    height: '100%',
    borderRadius: 2,
  },
  quickActionsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    backgroundColor: 'rgba(115, 103, 240, 0.05)',
    borderRadius: 20,
    padding: 15,
    marginVertical: 15,
    borderWidth: 1,
    borderColor: 'rgba(115, 103, 240, 0.1)',
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
    marginBottom: 6,
  },
  quickActionLabel: {
    fontSize: 10,
    fontWeight: '700',
    textAlign: 'center',
  },
  alertItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 15,
    borderRadius: 12,
  },
  alertText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginLeft: 10,
  },
  chartContainer: {
    padding: 20,
    borderRadius: 24,
    borderWidth: 1,
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
    width: 10,
    borderRadius: 5,
  },
  chartLabel: {
    fontSize: 9,
    marginTop: 8,
    fontWeight: '600',
  },
  chartValueWrapper: {
    position: 'absolute',
    top: -15,
    width: 40,
    alignItems: 'center',
  },
  chartValueText: {
    fontSize: 8,
    fontWeight: 'bold',
  },
  card: {
    backgroundColor: '#fff',
    borderRadius: 16,
    marginBottom: 16,
    padding: 16,
    borderWidth: 1, // Added
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05, // Refined
    shadowRadius: 10,   // Refined
    elevation: 2,       // Refined
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  idBadge: {
    backgroundColor: '#7367F015',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
  },
  idText: {
    color: '#7367F0',
    fontWeight: '700',
    fontSize: 12,
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusText: {
    fontSize: 11,
    fontWeight: 'bold',
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
    fontWeight: 'bold',
    color: '#333',
    flex: 1,
  },
  vehicleInfo: {
    fontSize: 13,
    color: '#666',
  },
  divider: {
    height: 1,
    backgroundColor: '#f1f1f1',
    marginVertical: 4,
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
    fontSize: 12,
    color: '#999',
    marginLeft: 6,
  },
  emptyContainer: {
    alignItems: 'center',
    marginTop: 40,
    backgroundColor: '#fff',
    padding: 30,
    borderRadius: 16,
    borderStyle: 'dashed',
    borderWidth: 1,
    borderColor: '#ccc',
  },
  emptyText: {
    marginTop: 10,
    color: '#999',
    fontSize: 14,
    fontWeight: '600',
  },
  fabContainer: {
    position: 'absolute',
    bottom: 24,
    right: 24,
    zIndex: 20,
  },
  fab: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#7367F0',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#7367F0',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.4,
    shadowRadius: 8,
    elevation: 6,
  },
});
