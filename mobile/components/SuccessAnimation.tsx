import React, { useEffect, useRef } from 'react';
import { View, StyleSheet, Modal, Text } from 'react-native';
import LottieView from 'lottie-react-native';
import { useTheme } from '../context/ThemeContext';
import Animated, { FadeIn, FadeOut } from 'react-native-reanimated';

interface SuccessAnimationProps {
    visible: boolean;
    onFinish: () => void;
    message?: string;
}

export const SuccessAnimation = ({ visible, onFinish, message }: SuccessAnimationProps) => {
    const { colors } = useTheme();

    if (!visible) return null;

    return (
        <Modal transparent visible={visible} animationType="fade">
            <Animated.View
                entering={FadeIn}
                exiting={FadeOut}
                style={[styles.container, { backgroundColor: 'rgba(0,0,0,0.7)' }]}
            >
                <View style={[styles.card, { backgroundColor: colors.card }]}>
                    <LottieView
                        source={{ uri: 'https://assets9.lottiefiles.com/packages/lf20_pqnqpob9.json' }} // Success check
                        autoPlay
                        loop={false}
                        onAnimationFinish={() => {
                            setTimeout(onFinish, 1500);
                        }}
                        style={styles.lottie}
                    />
                    <Text style={[styles.message, { color: colors.text }]}>
                        {message || 'Sucesso!'}
                    </Text>
                </View>
            </Animated.View>
        </Modal>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
    card: {
        width: 250,
        padding: 30,
        borderRadius: 30,
        alignItems: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.3,
        shadowRadius: 20,
        elevation: 10,
    },
    lottie: {
        width: 150,
        height: 150,
    },
    message: {
        fontSize: 18,
        fontWeight: 'bold',
        marginTop: 10,
        textAlign: 'center',
    },
});
