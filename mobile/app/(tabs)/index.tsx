import React, { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  FlatList,
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
import { useRouter } from 'expo-router';

import * as Notifications from 'expo-notifications';

export default function DashboardScreen() {
  const { user } = useAuth();
  const [data, setData] = useState<any>(null); // Changed type and initial value
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const router = useRouter();

  useEffect(() => {
    fetchDashboardData(); // Changed function name
  }, []);

  const fetchDashboardData = async () => { // Changed function name
    try {
      const response = await api.get('/dashboard/stats'); // Changed API endpoint
      setData(response.data);
    } catch (error: any) {
      console.error("Error fetching dashboard data:", error); // Updated log message
      Alert.alert('Erro', 'Não foi possível carregar os dados do painel.'); // Updated error message
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchDashboardData(); // Changed function name
  }, []);

  const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
      case 'pending':
      case 'em aberto':
        return '#FF9F43'; // Orange
      case 'approved':
      case 'aprovado':
        return '#28C76F'; // Green
      case 'running':
      case 'in_progress':
      case 'execução':
        return '#00CFE8'; // Cyan
      case 'finalized':
      case 'concluído':
      case 'paid':
        return '#28C76F'; // Green
      case 'cancelado':
        return '#EA5455'; // Red
      default: return '#7367F0'; // Purple default
    }
  };

  const statusTranslations: { [key: string]: string } = {
    pending: 'Pendente',
    running: 'Em Execução',
    finalized: 'Finalizado',
    approved: 'Aprovado',
    rejected: 'Reprovado',
    paid: 'Pago',
    completed: 'Concluído',
    in_progress: 'Em Andamento'
  };

  const renderAdminHeader = () => {
    if (!data) return null;
    return (
      <View style={styles.adminStatsContainer}>
        {/* Financial Cards Scroll */}
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.horizontalScroll}>
          <View style={[styles.statCard, { borderLeftColor: '#28C76F' }]}>
            <Text style={styles.statLabel}>Receita Mensal</Text>
            <Text style={styles.statValue}>R$ {numberFormat(data.revenueMonth)}</Text>
            <View style={styles.growthContainer}>
              <Ionicons
                name={data.revenueGrowth >= 0 ? "trending-up" : "trending-down"}
                size={14}
                color={data.revenueGrowth >= 0 ? "#28C76F" : "#EA5455"}
              />
              <Text style={[styles.growthText, { color: data.revenueGrowth >= 0 ? "#28C76F" : "#EA5455" }]}>
                {Math.abs(data.revenueGrowth).toFixed(1)}% vs mês ant.
              </Text>
            </View>
          </View>

          <View style={[styles.statCard, { borderLeftColor: '#00CFE8' }]}>
            <Text style={styles.statLabel}>Lucratividade</Text>
            <Text style={styles.statValue}>{numberFormat(data.monthlyProfitability)}%</Text>
            <Text style={styles.statSubLabel}>Margem Líquida</Text>
          </View>

          <View style={[styles.statCard, { borderLeftColor: '#7367F0' }]}>
            <Text style={styles.statLabel}>Clientes Totais</Text>
            <Text style={styles.statValue}>{data.totalClients}</Text>
            <Text style={styles.statSubLabel}>Base Ativa</Text>
          </View>
        </ScrollView>

        {/* OS Status Grid */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Status das OS</Text>
        </View>
        <View style={styles.statusGrid}>
          <TouchableOpacity style={styles.statusBox} onPress={() => router.push('/os')}>
            <View style={[styles.statusIcon, { backgroundColor: '#FF9F4320' }]}>
              <Ionicons name="time-outline" size={20} color="#FF9F43" />
            </View>
            <Text style={styles.statusCountText}>{data.osStats.pending}</Text>
            <Text style={styles.statusLabelText}>Pendentes</Text>
          </TouchableOpacity>

          <TouchableOpacity style={styles.statusBox} onPress={() => router.push('/os')}>
            <View style={[styles.statusIcon, { backgroundColor: '#00CFE820' }]}>
              <Ionicons name="build-outline" size={20} color="#00CFE8" />
            </View>
            <Text style={styles.statusCountText}>{data.osStats.running}</Text>
            <Text style={styles.statusLabelText}>Execução</Text>
          </TouchableOpacity>

          <TouchableOpacity style={styles.statusBox} onPress={() => router.push('/os')}>
            <View style={[styles.statusIcon, { backgroundColor: '#28C76F20' }]}>
              <Ionicons name="checkmark-circle-outline" size={20} color="#28C76F" />
            </View>
            <Text style={styles.statusCountText}>{data.osStats.finalized_today}</Text>
            <Text style={styles.statusLabelText}>Prontas Hoje</Text>
          </TouchableOpacity>
        </View>

        {/* Critical Alerts */}
        {(data.lowStockItems > 0 || data.pendingBudgets > 0) && (
          <View style={styles.alertsContainer}>
            <Text style={styles.sectionTitle}>Atenção Necessária</Text>
            <View style={styles.alertRow}>
              {data.lowStockItems > 0 && (
                <View style={[styles.alertItem, { backgroundColor: '#EA545515' }]}>
                  <Ionicons name="cube-outline" size={18} color="#EA5455" />
                  <Text style={styles.alertText}>{data.lowStockItems} itens com baixo estoque</Text>
                </View>
              )}
              {data.pendingBudgets > 0 && (
                <View style={[styles.alertItem, { backgroundColor: '#FF9F4315' }]}>
                  <Ionicons name="document-text-outline" size={18} color="#FF9F43" />
                  <Text style={styles.alertText}>{data.pendingBudgets} orçamentos aguardando</Text>
                </View>
              )}
            </View>
          </View>
        )}
      </View>
    );
  };

  const numberFormat = (val: any) => {
    return parseFloat(val || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

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
            <Text style={styles.subtitleText}>Resumo da sua oficina hoje</Text>

          </View>
          <TouchableOpacity style={styles.profileButton} onPress={() => router.push('/profile')}>
            {/* Placeholder for user avatar */}
            <View style={styles.avatarPlaceholder}>
              <Text style={styles.avatarText}>
                {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
              </Text>
            </View>
          </TouchableOpacity>
        </View>
      </LinearGradient>

      {/* Content List */}
      <ScrollView
        style={styles.listContainer}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            colors={['#7367F0']}
            tintColor="#7367F0"
          />
        }
      >
        {loading && !refreshing ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#7367F0" />
            <Text style={{ marginTop: 10, color: '#666' }}>Carregando painel...</Text>
          </View>
        ) : (
          <View>
            {user?.role === 'admin' ? (
              <>
                {renderAdminHeader()}
                <View style={{ marginTop: 10 }}>
                  <Text style={styles.sectionTitle}>Atividades Recentes</Text>
                  {data?.recentOS?.map((item: any) => (
                    <TouchableOpacity
                      key={item.id}
                      style={styles.card}
                      activeOpacity={0.9}
                      onPress={() => Alert.alert('Detalhes', `Ordem #${item.id} - ${item.client_name}`)}
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
                  ))}
                </View>
              </>
            ) : (
              <View style={{ marginTop: 25 }}>
                <Text style={styles.sectionTitle}>Minhas Atividades</Text>
                {/* Employee logic ... maybe list their tasks here */}
              </View>
            )}
          </View>
        )}
      </ScrollView>

      {/* Floating Action Button (Optional - for creating new order) */}
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
    paddingTop: 60, // Safe area padding
    paddingBottom: 25,
    paddingHorizontal: 24,
    borderBottomLeftRadius: 30,
    borderBottomRightRadius: 30,
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
    marginTop: -20, // Overlap the header slightly
    paddingHorizontal: 20,
  },
  flatListContent: {
    paddingBottom: 80, // Space for FAB
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 50,
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
    backgroundColor: '#f0f0f0',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  idText: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#555',
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusText: {
    fontSize: 12,
    fontWeight: 'bold',
    textTransform: 'uppercase',
  },
  cardBody: {
    // Body styles
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 6,
  },
  clientName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    flex: 1,
  },
  vehicleInfo: {
    fontSize: 14,
    color: '#666',
    flex: 1,
  },
  divider: {
    height: 1,
    backgroundColor: '#f0f0f0',
    marginVertical: 12,
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
  detailsLink: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  detailsText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#7367F0',
    marginRight: 4,
  },
  emptyContainer: {
    alignItems: 'center',
    marginTop: 60,
  },
  emptyText: {
    marginTop: 10,
    color: '#999',
    fontSize: 16,
  },
  fab: {
    position: 'absolute',
    bottom: 24,
    right: 24,
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#7367F0',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#7367F0',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.4,
    shadowRadius: 8,
    elevation: 6,
  },
  // Admin Dashboard Styles
  adminStatsContainer: {
    marginTop: 25,
    marginBottom: 20,
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
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
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
  },
  alertsContainer: {
    marginBottom: 20,
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
});
