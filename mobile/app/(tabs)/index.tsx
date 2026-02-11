import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, ActivityIndicator, Alert, TouchableOpacity } from 'react-native';
import api from '../../services/api';
import { useAuth } from '../../context/AuthContext';
import { router } from 'expo-router';

export default function DashboardScreen() {
  const { user } = useAuth();
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchOrdens();
  }, []);

  async function fetchOrdens() {
    try {
      const response = await api.get('/ordens-servico');
      setData(response.data.data);
    } catch (error) {
      Alert.alert('Erro', 'Não foi possível carregar as ordens.');
    } finally {
      setLoading(false);
    }
  }

  const renderItem = ({ item }) => (
    <TouchableOpacity
      style={{
        padding: 15,
        borderBottomWidth: 1,
        borderColor: '#eee',
        backgroundColor: '#fff',
        borderRadius: 8,
        marginVertical: 5,
        marginHorizontal: 10,
        shadowColor: '#000',
        shadowOpacity: 0.1,
        shadowRadius: 3,
        elevation: 2,
      }}
      onPress={() => Alert.alert('Detalhes', `OS #${item.id} - ${item.status}`)}>
      <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
        <Text style={{ fontWeight: 'bold' }}>OS #{item.id}</Text>
        <Text style={{ color: item.status === 'Em Aberto' ? 'orange' : 'green' }}>
          {item.status}
        </Text>
      </View>
      <Text style={{ color: '#555', marginTop: 5 }}>
        {item.client ? item.client.name : 'Cliente não informado'}
      </Text>
      <Text style={{ color: '#777', fontSize: 12 }}>
        {new Date(item.created_at).toLocaleDateString()}
      </Text>
    </TouchableOpacity>
  );

  return (
    <View style={{ flex: 1, backgroundColor: '#f4f4f4' }}>
      <View style={{ padding: 20, backgroundColor: '#fff', borderBottomWidth: 1, borderColor: '#ddd' }}>
        <Text style={{ fontSize: 20, fontWeight: 'bold' }}>Olá, {user?.name}</Text>
        <Text style={{ color: '#666' }}>Suas Ordens de Serviço Recentes</Text>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color="#007bff" style={{ marginTop: 20 }} />
      ) : (
        <FlatList
          data={data}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={{ paddingVertical: 10 }}
          refreshing={loading}
          onRefresh={fetchOrdens}
        />
      )}
    </View>
  );
}
