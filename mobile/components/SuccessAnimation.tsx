import React, { useEffect, useRef } from 'react';
import { View, StyleSheet, Modal, Text, Animated } from 'react-native';

interface SuccessAnimationProps {
    visible: boolean;
    onFinish: () => void;
    message?: string;
    emoji?: string;
}

export const SuccessAnimation = ({ visible, onFinish, message, emoji }: SuccessAnimationProps) => {
    const scale = useRef(new Animated.Value(0)).current;
    const opacity = useRef(new Animated.Value(0)).current;
    const emojiScale = useRef(new Animated.Value(0)).current;
    const bgOpacity = useRef(new Animated.Value(0)).current;

    useEffect(() => {
        if (visible) {
            // Reset
            scale.setValue(0);
            opacity.setValue(0);
            emojiScale.setValue(0);
            bgOpacity.setValue(0);

            // Entrada: fundo escurece, card sobe e emoji pula
            Animated.sequence([
                Animated.timing(bgOpacity, { toValue: 1, duration: 200, useNativeDriver: true }),
                Animated.spring(scale, { toValue: 1, friction: 5, tension: 120, useNativeDriver: true }),
                Animated.spring(emojiScale, { toValue: 1, friction: 4, tension: 100, useNativeDriver: true }),
                Animated.timing(opacity, { toValue: 1, duration: 200, useNativeDriver: true }),
            ]).start();

            // Auto-dismiss depois de 2.5s
            const timer = setTimeout(() => {
                Animated.parallel([
                    Animated.timing(bgOpacity, { toValue: 0, duration: 300, useNativeDriver: true }),
                    Animated.timing(scale, { toValue: 0.8, duration: 300, useNativeDriver: true }),
                ]).start(() => onFinish());
            }, 2500);

            return () => clearTimeout(timer);
        }
    }, [visible]);

    if (!visible) return null;

    return (
        <Modal transparent visible={visible} animationType="none" statusBarTranslucent>
            <Animated.View style={[styles.overlay, { opacity: bgOpacity }]}>
                <Animated.View style={[styles.card, { transform: [{ scale }] }]}>
                    <Animated.Text style={[styles.emoji, { transform: [{ scale: emojiScale }] }]}>
                        {emoji || '✅'}
                    </Animated.Text>
                    <Animated.Text style={[styles.message, { opacity }]}>
                        {message || 'Concluído!'}
                    </Animated.Text>
                    <Animated.Text style={[styles.sub, { opacity }]}>
                        Tudo certo por aqui 🎉
                    </Animated.Text>
                </Animated.View>
            </Animated.View>
        </Modal>
    );
};

const styles = StyleSheet.create({
    overlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.65)',
        justifyContent: 'center',
        alignItems: 'center',
    },
    card: {
        width: 260,
        paddingVertical: 36,
        paddingHorizontal: 28,
        borderRadius: 32,
        backgroundColor: '#fff',
        alignItems: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 12 },
        shadowOpacity: 0.25,
        shadowRadius: 24,
        elevation: 12,
    },
    emoji: {
        fontSize: 72,
        marginBottom: 16,
    },
    message: {
        fontSize: 20,
        fontWeight: '800',
        color: '#1c1c1e',
        textAlign: 'center',
        marginBottom: 6,
    },
    sub: {
        fontSize: 14,
        color: '#8e8e93',
        textAlign: 'center',
        fontWeight: '500',
    },
});
