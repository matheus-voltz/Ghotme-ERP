import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator, Alert } from 'react-native';
import { Calendar, LocaleConfig } from 'react-native-calendars';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import api from '../../services/api';
import { useTheme } from '../../context/ThemeContext';

// Configuração para PT-BR
LocaleConfig.locales['pt-br'] = {
  monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
  monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
  dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
  dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
  today: 'Hoje'
};
LocaleConfig.defaultLocale = 'pt-br';

export default function CalendarScreen() {
  const router = useRouter();
  const { colors } = useTheme();
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [events, setEvents] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [markedDates, setMarkedDates] = useState({});

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = async () => {
    setLoading(true);
    try {
      // Usaremos a rota que você já tem no Laravel para calendário
      const response = await api.get('/calendar/events');
      const data = response.data;
      
      // Processar datas marcadas no calendário
      const marked: any = {};
      data.forEach((event: any) => {
        const date = event.start.split(' ')[0]; // Pega YYYY-MM-DD
        marked[date] = { 
            marked: true, 
            dotColor: '#7367F0', 
            selected: date === selectedDate,
            selectedColor: date === selectedDate ? '#7367F0' : undefined 
        };
      });
      
      // Garante que a data selecionada tenha destaque
      marked[selectedDate] = { ...marked[selectedDate], selected: true, selectedColor: '#7367F0' };
      
      setMarkedDates(marked);
      setEvents(data);
    } catch (error) {
      console.error("Error fetching events:", error);
      // Alert.alert("Erro", "Não foi possível carregar os agendamentos.");
    } finally {
      setLoading(false);
    }
  };

  const dayEvents = events.filter(e => e.start.startsWith(selectedDate));

  return (
    <View style={[styles.container, { backgroundColor: '#F3F4F6' }]}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Ionicons name="chevron-back" size={28} color="#333" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Agenda da Oficina</Text>
        <TouchableOpacity onPress={fetchEvents}>
          <Ionicons name="refresh" size={24} color="#7367F0" />
        </TouchableOpacity>
      </View>

      <View style={styles.calendarCard}>
        <Calendar
          onDayPress={day => setSelectedDate(day.dateString)}
          markedDates={markedDates}
          theme={{
            todayTextColor: '#7367F0',
            arrowColor: '#7367F0',
            selectedDayBackgroundColor: '#7367F0',
            selectedDayTextColor: '#ffffff',
            dotColor: '#7367F0',
            textDayFontSize: 16,
            textMonthFontSize: 18,
            textMonthFontWeight: 'bold',
          }}
        />
      </View>

      <View style={styles.listSection}>
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>
            {selectedDate === new Date().toISOString().split('T')[0] ? 'Hoje' : selectedDate.split('-').reverse().join('/')}
          </Text>
          <Text style={styles.eventCount}>{dayEvents.length} Agendamentos</Text>
        </View>

        {loading ? (
          <ActivityIndicator color="#7367F0" style={{ marginTop: 40 }} />
        ) : (
          <ScrollView showsVerticalScrollIndicator={false}>
            {dayEvents.length > 0 ? (
              dayEvents.map((event, index) => (
                <TouchableOpacity 
                  key={index} 
                  style={styles.eventCard}
                  onPress={() => router.push({
                    pathname: "/calendar/[id]",
                    params: { 
                        id: event.id,
                        title: event.title,
                        start: event.start,
                        description: event.extendedProps?.description,
                        color: event.color
                    }
                  })}
                >
                  <View style={[styles.eventBorder, { backgroundColor: event.color || '#7367F0' }]} />
                  <View style={styles.eventInfo}>
                    <Text style={styles.eventTime}>
                        <Ionicons name="time-outline" size={14} /> {event.start.split(' ')[1]?.substring(0, 5) || 'O dia todo'}
                    </Text>
                    <Text style={styles.eventTitle}>{event.title}</Text>
                    <Text style={styles.eventDesc} numberOfLines={1}>{event.extendedProps?.description || 'Sem observações'}</Text>
                  </View>
                  <Ionicons name="chevron-forward" size={20} color="#ccc" />
                </TouchableOpacity>
              ))
            ) : (
              <View style={styles.emptyState}>
                <Ionicons name="calendar-outline" size={50} color="#ccc" />
                <Text style={styles.emptyText}>Nenhum serviço agendado para este dia.</Text>
              </View>
            )}
          </ScrollView>
        )}
      </View>

      <TouchableOpacity 
        style={styles.fab} 
        onPress={() => router.push({ pathname: '/calendar/create', params: { date: selectedDate } })}
      >
        <Ionicons name="add" size={30} color="#fff" />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingTop: 60, paddingBottom: 20, paddingHorizontal: 20, backgroundColor: '#fff' },
  backBtn: { width: 40 },
  headerTitle: { fontSize: 20, fontWeight: 'bold', color: '#1F2937' },
  calendarCard: { backgroundColor: '#fff', paddingBottom: 10, borderBottomLeftRadius: 30, borderBottomRightRadius: 30, elevation: 4, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 8 },
  listSection: { flex: 1, padding: 20 },
  sectionHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', color: '#374151' },
  eventCount: { fontSize: 13, color: '#6B7280', fontWeight: '600' },
  eventCard: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 16, padding: 15, marginBottom: 12, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 10 },
  eventBorder: { width: 4, height: '80%', borderRadius: 2 },
  eventInfo: { flex: 1, marginLeft: 15 },
  eventTime: { fontSize: 12, color: '#7367F0', fontWeight: 'bold', marginBottom: 2 },
  eventTitle: { fontSize: 16, fontWeight: 'bold', color: '#333' },
  eventDesc: { fontSize: 13, color: '#888', marginTop: 2 },
  emptyState: { alignItems: 'center', marginTop: 50 },
  emptyText: { color: '#9CA3AF', marginTop: 10, fontSize: 15 },
  fab: { position: 'absolute', bottom: 30, right: 20, width: 60, height: 60, borderRadius: 30, backgroundColor: '#7367F0', justifyContent: 'center', alignItems: 'center', elevation: 5, shadowColor: '#7367F0', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 10 }
});
