import axios from 'axios';
import * as SecureStore from 'expo-secure-store';

// CHANGE THIS TO YOUR COMPUTER'S IP IF TESTING ON PHYSICAL DEVICE
// e.g., 'http://192.168.1.10:8000/api'
const API_URL = 'http://10.0.0.118:8000/api';

const api = axios.create({
    baseURL: API_URL,
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
