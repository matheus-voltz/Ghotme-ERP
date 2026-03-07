import axios from 'axios';
import * as SecureStore from 'expo-secure-store';

// URL da API originária das Variáveis de Ambiente (.env)
const DEV_URL = 'http://10.0.0.171:8000/api';
const PROD_URL = 'https://ghotme.com.br/api';

const api = axios.create({
    baseURL: __DEV__ ? DEV_URL : PROD_URL,
    timeout: 15000, // 15 segundos de timeout
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

console.log('App conectando em:', api.defaults.baseURL);

api.interceptors.request.use(
    async (config) => {
        const token = await SecureStore.getItemAsync('userToken');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Interceptor para lidar com respostas de erro globais
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        // Se receber 401 (Unauthorized), significa que o token expirou ou é inválido
        if (error.response && error.response.status === 401) {
            console.warn('Sessão expirada (401). Limpando tokens...');
            await SecureStore.deleteItemAsync('userToken');
            await SecureStore.deleteItemAsync('userData');
            // O app deve reagir à mudança no SecureStore ou no estado do AuthContext
        }
        return Promise.reject(error);
    }
);

export default api;
