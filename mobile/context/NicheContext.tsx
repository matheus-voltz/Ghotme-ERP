import React, { createContext, useState, useContext, useEffect } from 'react';
import api from '../services/api';
import { useAuth } from './AuthContext';

interface NicheLabels {
  entity: string;
  entities: string;
  new_entity: string;
  identifier: string;
  secondary_identifier: string;
  metric: string;
  metric_unit: string;
  brand: string;
  model: string;
  color: string;
  year: string;
  features?: string;
  inventory_items?: string;
  checklist_categories?: { [key: string]: string[] };
}

interface NicheContextData {
  niche: string;
  labels: NicheLabels;
  loading: boolean;
  refreshNiche: () => Promise<void>;
}

// Valores padrão (Automotive) para fallback
const defaultLabels: NicheLabels = {
  entity: 'Veículo',
  entities: 'Veículos',
  new_entity: 'Novo Veículo',
  identifier: 'Placa',
  secondary_identifier: 'Renavam',
  metric: 'KM',
  metric_unit: 'km',
  brand: 'Marca',
  model: 'Modelo',
  color: 'Cor',
  year: 'Ano',
  inventory_items: 'Peças',
};

const NicheContext = createContext<NicheContextData>({} as NicheContextData);

export const NicheProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user } = useAuth();
  const [niche, setNiche] = useState('automotive');
  const [labels, setLabels] = useState<NicheLabels>(defaultLabels);
  const [loading, setLoading] = useState(true);

  const refreshNiche = async () => {
    try {
      const response = await api.get('/niche-config');
      setNiche(response.data.niche);
      setLabels(response.data.labels);
    } catch (error) {
      console.log('Error fetching niche config, using default:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (user) {
      refreshNiche();
    }
  }, [user]);

  return (
    <NicheContext.Provider value={{ niche, labels, loading, refreshNiche }}>
      {children}
    </NicheContext.Provider>
  );
};

export const useNiche = () => {
  const context = useContext(NicheContext);
  if (!context) {
    throw new Error('useNiche must be used within a NicheProvider');
  }
  return context;
};
