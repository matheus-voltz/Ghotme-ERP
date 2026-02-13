import axios from 'axios';
import * as SecureStore from 'expo-secure-store';

// URL da API (Local para dev, domínio real para produção)
const DEV_URL = 'http://10.0.0.118:8000/api';
const PROD_URL = 'https://ghotme.com.br/api';

const api = axios.create({
    baseURL: __DEV__ ? DEV_URL : PROD_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

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

export default api;
