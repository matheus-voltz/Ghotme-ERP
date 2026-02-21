import React, { createContext, useContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';

type LanguageType = 'pt-BR' | 'en' | 'es' | 'fr';

interface LanguageContextData {
    language: LanguageType;
    setLanguage: (lang: LanguageType) => Promise<void>;
    t: (key: string, params?: any) => string;
}

const translations = {
    'pt-BR': {
        welcome: 'Bem-vindo',
        hello: 'Olá',
        summary_today: 'Resumo {niche} hoje',
        portal: 'Seu portal de trabalho',
        recent_activities: 'Atividades Recentes',
        revenue: 'Faturamento',
        profitability: 'Lucratividade',
        new_clients: 'Novos Clientes',
        active_base: 'Base ativa',
        pending: 'Pendente',
        in_progress: 'Em Execução',
        completed: 'Finalizada',
        canceled: 'Cancelada',
        notifications: 'Notificações',
        language: 'Idioma',
        support: 'Suporte',
        theme: 'Tema',
        logout: 'Sair da Conta',
        // Perfil
        personal_data: 'Dados Pessoais',
        security: 'Segurança',
        preferences: 'Preferências',
        manage_alerts: 'Gerenciar alertas',
        help: 'Ajuda',
        team_chat: 'Chat da Equipe',
        contact_us: 'Fale Conosco',
        today: 'Hoje',
        efficiency: 'Eficiência',
        // OS Tabs e Botões
        all: 'Todas',
        new_os: 'Nova OS',
        // ...
    },
    'en': {
        welcome: 'Welcome',
        hello: 'Hello',
        summary_today: '{niche} summary today',
        portal: 'Your work portal',
        recent_activities: 'Recent Activities',
        revenue: 'Revenue',
        profitability: 'Profitability',
        new_clients: 'New Clients',
        active_base: 'Active base',
        pending: 'Pending',
        in_progress: 'In Progress',
        completed: 'Completed',
        canceled: 'Canceled',
        notifications: 'Notifications',
        language: 'Language',
        support: 'Support',
        theme: 'Theme',
        logout: 'Log Out',
        // Profile
        personal_data: 'Personal Data',
        security: 'Security',
        preferences: 'Preferences',
        manage_alerts: 'Manage alerts',
        help: 'Help',
        team_chat: 'Team Chat',
        contact_us: 'Contact Us',
        today: 'Today',
        efficiency: 'Efficiency',
        // OS Tabs
        all: 'All',
        new_os: 'New OS',
    },
    'es': {
        welcome: 'Bienvenido',
        hello: 'Hola',
        summary_today: 'Resumen de {niche} hoy',
        portal: 'Tu portal de trabajo',
        recent_activities: 'Actividades Recientes',
        revenue: 'Ingresos',
        profitability: 'Rentabilidad',
        new_clients: 'Nuevos Clientes',
        active_base: 'Base activa',
        pending: 'Pendiente',
        in_progress: 'En Ejecución',
        completed: 'Completado',
        canceled: 'Cancelado',
        notifications: 'Notificaciones',
        language: 'Idioma',
        support: 'Soporte',
        theme: 'Tema',
        logout: 'Cerrar Sesión',
        // Profile
        personal_data: 'Datos Personales',
        security: 'Seguridad',
        preferences: 'Preferencias',
        manage_alerts: 'Administrar alertas',
        help: 'Ayuda',
        team_chat: 'Chat de Equipo',
        contact_us: 'Contáctanos',
        today: 'Hoy',
        efficiency: 'Eficiencia',
        // OS Tabs
        all: 'Todas',
        new_os: 'Nueva OS',
    },
    'fr': {
        welcome: 'Bienvenue',
        hello: 'Bonjour',
        summary_today: 'Résumé {niche} aujourd\'hui',
        portal: 'Votre portail de travail',
        recent_activities: 'Activités Récentes',
        revenue: 'Chiffre d\'affaires',
        profitability: 'Rentabilité',
        new_clients: 'Nouveaux Clients',
        active_base: 'Base active',
        pending: 'En attente',
        in_progress: 'En Cours',
        completed: 'Terminé',
        canceled: 'Annulé',
        notifications: 'Notifications',
        language: 'Langue',
        support: 'Support',
        theme: 'Thème',
        logout: 'Se Déconnecter',
        // Profile
        personal_data: 'Données Personnelles',
        security: 'Sécurité',
        preferences: 'Préférences',
        manage_alerts: 'Gérer les alertes',
        help: 'Aide',
        team_chat: 'Chat d\'Équipe',
        contact_us: 'Nous Contacter',
        today: 'Aujourd\'hui',
        efficiency: 'Efficacité',
        // OS Tabs
        all: 'Toutes',
        new_os: 'Nouvelle OS',
    }
};

const LanguageContext = createContext<LanguageContextData>({} as LanguageContextData);

export function LanguageProvider({ children }: { children: React.ReactNode }) {
    const [language, setLanguageState] = useState<LanguageType>('pt-BR');

    useEffect(() => {
        const loadLanguage = async () => {
            try {
                const storedLanguage = await AsyncStorage.getItem('@Ghotme:language');
                if (storedLanguage) {
                    setLanguageState(storedLanguage as LanguageType);
                }
            } catch (err) {
                console.error('Failed to load language config', err);
            }
        };
        loadLanguage();
    }, []);

    const setLanguage = async (lang: LanguageType) => {
        try {
            await AsyncStorage.setItem('@Ghotme:language', lang);
            setLanguageState(lang);
        } catch (err) {
            console.error('Failed to set language config', err);
        }
    };

    const t = (key: string, params?: { [key: string]: string | number }) => {
        // @ts-ignore
        let text = translations[language]?.[key] || translations['pt-BR'][key] || key;
        if (params) {
            Object.keys(params).forEach((k) => {
                text = text.replace(`{${k}}`, String(params[k]));
            });
        }
        return text;
    };

    return (
        <LanguageContext.Provider value={{ language, setLanguage, t }}>
            {children}
        </LanguageContext.Provider>
    );
}

export function useLanguage() {
    return useContext(LanguageContext);
}
