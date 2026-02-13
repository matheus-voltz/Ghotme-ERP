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
  ScrollView
} from 'react-native';
import api from '../../services/api';
import { useAuth } from '../../context/AuthContext';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter, router } from 'expo-router';

// Helper for status translations
const statusTranslations: { [key: string]: string } = {
  'pending': 'Pendente',
  'running': 'Em Execução',
  'finalized': 'Finalizada',
  'canceled': 'Cancelada',
};

// Helper for status colors (Vuexy Palette)
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
  const [data, setData] = useState<any>(null);
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
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchDashboardData();
  }, []);

  const renderAdminHeader = () => {
    if (!data) return null;
    return (
      <View style={styles.adminStatsContainer}>
        {/* Horizontal Financial Summary */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={styles.horizontalScroll}
        >
          {/* Revenue Card */}
          <View style={[styles.statCard, { borderLeftColor: '#7367F0' }]}>
            <Text style={styles.statLabel}>Receita Mensal</Text>
            <Text style={styles.statValue}>R$ {numberFormat(data.monthlyRevenue)}</Text>
            <View style={styles.growthContainer}>
              <Ionicons
                name={data.revenueGrowth >= 0 ? "trending-up" : "trending-down"}
                size={14}
                color={data.revenueGrowth >= 0 ? "#28C76F" : "#EA5455"}
              />
              <Text style={[styles.growthText, { color: data.revenueGrowth >= 0 ? "#28C76F" : "#EA5455" }]}>
                {Math.abs(data.revenueGrowth || 0).toFixed(1)}% vs ant.
              </Text>
            </View>
          </View>

          {/* Profitability Card */}
          <View style={[styles.statCard, { borderLeftColor: '#28C76F' }]}>
            <Text style={styles.statLabel}>Lucratividade</Text>
            <Text style={styles.statValue}>{data.monthlyProfitability || 0}%</Text>
            <Text style={styles.statSubLabel}>Margem média do mês</Text>
          </View>

          {/* Clients Card */}
          <View style={[styles.statCard, { borderLeftColor: '#FF9F43' }]}>
            <Text style={styles.statLabel}>Novos Clientes</Text>
            <Text style={styles.statValue}>{data.totalClients || 0}</Text>
            <Text style={styles.statSubLabel}>Base ativa na oficina</Text>
          </View>
        </ScrollView>

        {/* Operational Grid */}
        <View style={styles.statusGrid}>
          <View style={styles.statusBox}>
            <View style={[styles.statusIcon, { backgroundColor: '#FF9F4320' }]}>
              <Ionicons name="hourglass-outline" size={20} color="#FF9F43" />
            </View>
            <Text style={styles.statusCountText}>{data.osStats?.pending || 0}</Text>
            <Text style={styles.statusLabelText}>Pendentes</Text>
          </View>

          <View style={styles.statusBox}>
            <View style={[styles.statusIcon, { backgroundColor: '#00CFE820' }]}>
              <Ionicons name="play-outline" size={20} color="#00CFE8" />
            </View>
            <Text style={styles.statusCountText}>{data.osStats?.running || 0}</Text>
            <Text style={styles.statusLabelText}>Em Execução</Text>
          </View>

          <View style={styles.statusBox}>
            <View style={[styles.statusIcon, { backgroundColor: '#28C76F20' }]}>
              <Ionicons name="checkmark-done-outline" size={20} color="#28C76F" />
            </View>
            <Text style={styles.statusCountText}>{data.osStats?.finalized_today || 0}</Text>
            <Text style={styles.statusLabelText}>Finalizadas Hoje</Text>
          </View>
        </View>

        {/* Alerts Section */}
        {(data.lowStockCount > 0 || data.pendingBudgetsCount > 0) && (
          <View style={styles.alertsContainer}>
            <Text style={styles.sectionTitle}>Alertas Críticos</Text>
            <View style={styles.alertRow}>
              {data.lowStockCount > 0 && (
                <TouchableOpacity style={[styles.alertItem, { backgroundColor: '#EA545515' }]} onPress={() => router.push('/inventory')}>
                  <Ionicons name="warning" size={20} color="#EA5455" />
                  <Text style={styles.alertText}>{data.lowStockCount} itens com baixo estoque</Text>
                </TouchableOpacity>
              )}
              {data.pendingBudgetsCount > 0 && (
                <TouchableOpacity style={[styles.alertItem, { backgroundColor: '#FF9F4315' }]} onPress={() => Alert.alert('Orçamentos', 'Existem orçamentos aguardando aprovação.')}>
                  <Ionicons name="document-text" size={20} color="#FF9F43" />
                  <Text style={styles.alertText}>{data.pendingBudgetsCount} orçamentos pendentes</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        )}
      </View>
    );
  };

  const renderMechanicHeader = () => {
    if (!data) return null;
    return (
      <View style={styles.adminStatsContainer}>
        {/* Mechanic Summary Cards */}
        <View style={styles.statusGrid}>
          <View style={styles.statusBox}>
            <View style={[styles.statusIcon, { backgroundColor: '#00CFE820' }]}>
              <Ionicons name="play-outline" size={20} color="#00CFE8" />
            </View>
            <Text style={styles.statusCountText}>{data.stats?.runningOS || 0}</Text>
            <Text style={styles.statusLabelText}>Em Execução</Text>
          </View>

          <View style={styles.statusBox}>
            <View style={[styles.statusIcon, { backgroundColor: '#28C76F20' }]}>
              <Ionicons name="checkmark-done-outline" size={20} color="#28C76F" />
            </View>
            <Text style={styles.statusCountText}>{data.stats?.completedToday || 0}</Text>
            <Text style={styles.statusLabelText}>Prontas Hoje</Text>
          </View>

          <View style={styles.statusBox}>
            <View style={[styles.statusIcon, { backgroundColor: '#FF9F4320' }]}>
              <Ionicons name="document-text-outline" size={20} color="#FF9F43" />
            </View>
            <Text style={styles.statusCountText}>{data.stats?.pendingBudgets || 0}</Text>
            <Text style={styles.statusLabelText}>Orçamentos</Text>
          </View>
        </View>

        <View style={{ marginTop: 10 }}>
          <Text style={styles.sectionTitle}>Minhas Atividades</Text>
          {data?.recentOS?.map((item: any) => renderOSCard(item))}
          {(!data?.recentOS || data.recentOS.length === 0) && (
            <View style={styles.emptyContainer}>
              <Ionicons name="clipboard-outline" size={40} color="#ccc" />
              <Text style={styles.emptyText}>Sem ordens atribuídas</Text>
            </View>
          )}
        </View>
      </View>
    );
  };

  const renderOSCard = (item: any) => (
    <TouchableOpacity
      key={item.id}
      style={styles.card}
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
          <Ionicons name="person-outline" size={16} color="#666" style={{ marginRight: 6 }} />
          <Text style={styles.clientName} numberOfLines={1}>{item.client_name}</Text>
        </View>
        <View style={styles.infoRow}>
          <Ionicons name="car-sport-outline" size={16} color="#666" style={{ marginRight: 6 }} />
          <Text style={styles.vehicleInfo}>{item.vehicle} - {item.plate}</Text>
        </View>
        <View style={styles.divider} />
        <View style={styles.cardFooter}>
          <View style={styles.dateContainer}>
            <Ionicons name="calendar-outline" size={14} color="#999" />
            <Text style={styles.dateText}>{new Date(item.created_at).toLocaleDateString('pt-BR')}</Text>
          </View>
          <Text style={{ fontSize: 13, fontWeight: '700', color: '#7367F0' }}>
            R$ {numberFormat(item.total)}
          </Text>
        </View>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
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
            <Text style={styles.welcomeText}>Olá, {user?.name || 'Bem-vindo'}</Text>
            <Text style={styles.subtitleText}>
              {user?.role === 'admin' ? 'Resumo da oficina hoje' : 'Seu portal de trabalho'}
            </Text>
          </View>
          <TouchableOpacity style={styles.profileButton} onPress={() => router.push('/profile')}>
            <View style={styles.avatarPlaceholder}>
              {user?.profile_photo_url ? (
                <Image 
                  source={{ uri: user.profile_photo_url }} 
                  style={{ width: 45, height: 45, borderRadius: 22.5 }} 
                />
              ) : (
                <Text style={styles.avatarText}>
                  {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
                </Text>
              )}
            </View>
          </TouchableOpacity>
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
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#7367F0" />
            <Text style={{ marginTop: 10, color: '#666' }}>Carregando...</Text>
          </View>
        ) : (
          <View>
            {user?.role === 'admin' ? (
              <>
                {renderAdminHeader()}
                <View style={{ marginTop: 10 }}>
                  <Text style={styles.sectionTitle}>Atividades Recentes</Text>
                  {data?.recentOS?.map((item: any) => renderOSCard(item))}
                </View>
              </>
            ) : renderMechanicHeader()}
          </View>
        )}
      </ScrollView>

      {/* Floating Action Button */}
      <TouchableOpacity
        style={styles.fab}
        onPress={() => router.push('/os/create')}
        activeOpacity={0.8}
      >
        <Ionicons name="add" size={30} color="#fff" />
      </TouchableOpacity>
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
    elevation: 5,
  },
  headerContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  welcomeText: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
  },
  subtitleText: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 4,
  },
  profileButton: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 5,
    elevation: 5,
  },
  avatarPlaceholder: {
    width: 45,
    height: 45,
    borderRadius: 22.5,
    backgroundColor: '#fff',
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    color: '#7367F0',
    fontSize: 18,
    fontWeight: 'bold',
  },
  listContainer: {
    flex: 1,
  },
  scrollContent: {
    paddingTop: 140,
    paddingHorizontal: 20,
    paddingBottom: 100,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 50,
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
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 3,
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
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 3,
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
  card: {
    backgroundColor: '#fff',
    borderRadius: 16,
    marginBottom: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 3,
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
  fab: {
    position: 'absolute',
    bottom: 24,
    right: 24,
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
    zIndex: 20,
  },
});
