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
  RefreshControl
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
  const [data, setData] = useState<any[]>([]); // Typed as array
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const router = useRouter();

  const simularNotificacao = async () => {
    await Notifications.scheduleNotificationAsync({
      content: {
        title: "Ghotme ERP 🚀",
        body: "Novo orçamento #1059 recebido! Clique para revisar.",
        data: { screen: 'details', id: 1059 },
        sound: 'default',
      },
      trigger: null, // Dispara imediatamente
    });
  };

  useEffect(() => {
    fetchOrdens();
  }, []);

  const fetchOrdens = async () => {
    try {
      const response = await api.get('/ordens-servico');
      console.log("Orders loaded:", response.data); // Debug log

      // Handle both paginated and non-paginated responses just in case
      const orders = response.data.data ? response.data.data : response.data;
      setData(orders);
    } catch (error: any) {
      console.error("Error fetching orders:", error);
      let msg = 'Não foi possível carregar as ordens.';
      if (error.response) {
        msg += ` (Status: ${error.response.status})`;
      } else if (error.message === 'Network Error') {
        msg = 'Erro de conexão. Verifique sua rede.';
      }
      Alert.alert('Erro', msg);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchOrdens();
  }, []);

  const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
      case 'em aberto': return '#FF9F43'; // Orange
      case 'aprovado': return '#28C76F'; // Green
      case 'concluído': return '#00CFE8'; // Cyan
      case 'cancelado': return '#EA5455'; // Red
      default: return '#7367F0'; // Purple default
    }
  };

  const renderItem = ({ item }: { item: any }) => (
    <TouchableOpacity
      style={styles.card}
      activeOpacity={0.9}
      // Add navigation logic here when ready, e.g.:
      // onPress={() => router.push(`/orders/${item.id}`)}
      onPress={() => Alert.alert('Detalhes', `Ordem #${item.id} - ${item.client?.name || 'Cliente Desconhecido'}`)}
    >
      <View style={styles.cardHeader}>
        <View style={styles.idBadge}>
          <Text style={styles.idText}>#{item.id}</Text>
        </View>
        <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '20' }]}>
          <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
            {item.status || 'Pendente'}
          </Text>
        </View>
      </View>

      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Ionicons name="person-outline" size={16} color="#666" style={{ marginRight: 6 }} />
          <Text style={styles.clientName} numberOfLines={1}>
            {item.client?.name || 'Cliente não informado'}
          </Text>
        </View>

        {item.vehicle && (
          <View style={styles.infoRow}>
            <Ionicons name="car-sport-outline" size={16} color="#666" style={{ marginRight: 6 }} />
            <Text style={styles.vehicleInfo}>
              {item.vehicle.brand} {item.vehicle.model} - {item.vehicle.plate}
            </Text>
          </View>
        )}

        <View style={styles.divider} />

        <View style={styles.cardFooter}>
          <View style={styles.dateContainer}>
            <Ionicons name="calendar-outline" size={14} color="#999" />
            <Text style={styles.dateText}>
              {new Date(item.created_at).toLocaleDateString('pt-BR')}
            </Text>
          </View>

          <TouchableOpacity style={styles.detailsLink}>
            <Text style={styles.detailsText}>Ver Detalhes</Text>
            <Ionicons name="arrow-forward" size={14} color="#7367F0" />
          </TouchableOpacity>
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
            <Text style={styles.subtitleText}>Aqui estão suas ordens de serviço</Text>
            
            <TouchableOpacity 
                onPress={simularNotificacao}
                style={{
                    backgroundColor: 'rgba(255,255,255,0.2)',
                    paddingHorizontal: 12,
                    paddingVertical: 6,
                    borderRadius: 20,
                    marginTop: 10,
                    flexDirection: 'row',
                    alignItems: 'center',
                    alignSelf: 'flex-start'
                }}
            >
                <Ionicons name="notifications-outline" size={16} color="#fff" />
                <Text style={{color: '#fff', fontSize: 12, fontWeight: 'bold', marginLeft: 5}}>Simular Orçamento</Text>
            </TouchableOpacity>
          </View>
          <TouchableOpacity style={styles.profileButton}>
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
      <View style={styles.listContainer}>
        {loading && !refreshing ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#7367F0" />
            <Text style={{ marginTop: 10, color: '#666' }}>Carregando ordens...</Text>
          </View>
        ) : (
          <FlatList
            data={data}
            keyExtractor={(item) => item.id.toString()}
            renderItem={renderItem}
            contentContainerStyle={styles.flatListContent}
            showsVerticalScrollIndicator={false}
            refreshControl={
              <RefreshControl
                refreshing={refreshing}
                onRefresh={onRefresh}
                colors={['#7367F0']}
                tintColor="#7367F0"
              />
            }
            ListEmptyComponent={
              <View style={styles.emptyContainer}>
                <Ionicons name="folder-open-outline" size={60} color="#ccc" />
                <Text style={styles.emptyText}>Nenhuma ordem de serviço encontrada.</Text>
              </View>
            }
          />
        )}
      </View>

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
});
