import React, { createContext, useState, useContext, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface Device {
    id: string; // MAC Address or unique ID
    name: string;
    type: 'printer' | 'card_reader';
    brand?: 'pagseguro' | 'generic';
    lastConnected?: string;
}

interface DeviceContextType {
    pairedDevices: Device[];
    addDevice: (device: Device) => Promise<void>;
    removeDevice: (deviceId: string) => Promise<void>;
    loading: boolean;
}

const DeviceContext = createContext<DeviceContextType>({} as DeviceContextType);

export const DeviceProvider = ({ children }: { children: React.ReactNode }) => {
    const [pairedDevices, setPairedDevices] = useState<Device[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadDevices();
    }, []);

    const loadDevices = async () => {
        try {
            const stored = await AsyncStorage.getItem('paired_devices');
            if (stored) {
                setPairedDevices(JSON.parse(stored));
            }
        } catch (error) {
            console.error('Error loading devices', error);
        } finally {
            setLoading(false);
        }
    };

    const addDevice = async (device: Device) => {
        const updated = [...pairedDevices.filter(d => d.id !== device.id), device];
        setPairedDevices(updated);
        await AsyncStorage.setItem('paired_devices', JSON.stringify(updated));
    };

    const removeDevice = async (deviceId: string) => {
        const updated = pairedDevices.filter(d => d.id !== deviceId);
        setPairedDevices(updated);
        await AsyncStorage.setItem('paired_devices', JSON.stringify(updated));
    };

    return (
        <DeviceContext.Provider value={{ pairedDevices, addDevice, removeDevice, loading }}>
            {children}
        </DeviceContext.Provider>
    );
};

export const useDevices = () => useContext(DeviceContext);
