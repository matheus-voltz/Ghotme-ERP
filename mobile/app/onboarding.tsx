import React, { useState, useRef } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, Dimensions, Animated, StatusBar } from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as Haptics from 'expo-haptics';
import AsyncStorage from '@react-native-async-storage/async-storage';

const { width, height } = Dimensions.get('window');

const SLIDES = [
    {
        id: '1',
        title: 'Bem-vindo ao Ghotme ERP',
        description: 'Sua oficina, pet shop ou assistência técnica na palma da sua mão.',
        icon: 'rocket-outline',
        colors: ['#7367F0', '#CE9FFC']
    },
    {
        id: '2',
        title: 'Gestão Simplificada',
        description: 'Acompanhe ordens de serviço, faturamento e produtividade em tempo real.',
        icon: 'stats-chart-outline',
        colors: ['#28C76F', '#81FBB8']
    },
    {
        id: '3',
        title: 'Comunicação Direta',
        description: 'Fale com sua equipe e com o suporte Ghotme via chat integrado.',
        icon: 'chatbubbles-outline',
        colors: ['#00CFE8', '#7367F0']
    }
];

export default function OnboardingScreen() {
    const [currentIndex, setCurrentIndex] = useState(0);
    const scrollX = useRef(new Animated.Value(0)).current;
    const slidesRef = useRef<FlatList>(null);
    const router = useRouter();

    const finishOnboarding = async () => {
        await AsyncStorage.setItem('onboarding_completed', 'true');
        router.replace('/(auth)/login');
    };

    const viewableItemsChanged = useRef(({ viewableItems }: any) => {
        if (viewableItems[0]) {
            setCurrentIndex(viewableItems[0].index);
        }
    }).current;

    const viewConfig = useRef({ viewAreaCoveragePercentThreshold: 50 }).current;

    const scrollTo = () => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
        if (currentIndex < SLIDES.length - 1) {
            slidesRef.current?.scrollToIndex({ index: currentIndex + 1 });
        } else {
            finishOnboarding();
        }
    };

    const skip = () => {
        Haptics.selectionAsync();
        finishOnboarding();
    };

    const renderItem = ({ item }: any) => (
        <View style={styles.container}>
            <LinearGradient colors={item.colors} style={styles.topSection}>
                <Ionicons name={item.icon} size={120} color="#fff" />
            </LinearGradient>

            <View style={styles.bottomSection}>
                <Text style={styles.title}>{item.title}</Text>
                <Text style={styles.description}>{item.description}</Text>
            </View>
        </View>
    );

    return (
        <View style={styles.main}>
            <StatusBar barStyle="light-content" translucent backgroundColor="transparent" />

            <FlatList
                data={SLIDES}
                renderItem={renderItem}
                horizontal
                showsHorizontalScrollIndicator={false}
                pagingEnabled
                bounces={false}
                keyExtractor={(item) => item.id}
                onScroll={Animated.event([{ nativeEvent: { contentOffset: { x: scrollX } } }], {
                    useNativeDriver: false
                })}
                onViewableItemsChanged={viewableItemsChanged}
                viewabilityConfig={viewConfig}
                ref={slidesRef}
            />

            <View style={styles.footer}>
                <View style={styles.paginator}>
                    {SLIDES.map((_, i) => {
                        const inputRange = [(i - 1) * width, i * width, (i + 1) * width];
                        const dotWidth = scrollX.interpolate({
                            inputRange,
                            outputRange: [10, 20, 10],
                            extrapolate: 'clamp'
                        });
                        const opacity = scrollX.interpolate({
                            inputRange,
                            outputRange: [0.3, 1, 0.3],
                            extrapolate: 'clamp'
                        });

                        return (
                            <Animated.View
                                key={i.toString()}
                                style={[styles.dot, { width: dotWidth, opacity, backgroundColor: SLIDES[currentIndex].colors[0] }]}
                            />
                        );
                    })}
                </View>

                <View style={styles.buttonContainer}>
                    {currentIndex < SLIDES.length - 1 ? (
                        <TouchableOpacity onPress={skip}>
                            <Text style={styles.skipBtn}>Pular</Text>
                        </TouchableOpacity>
                    ) : <View style={{ width: 40 }} />}

                    <TouchableOpacity style={[styles.nextBtn, { backgroundColor: SLIDES[currentIndex].colors[0] }]} onPress={scrollTo}>
                        <Ionicons
                            name={currentIndex === SLIDES.length - 1 ? "checkmark" : "arrow-forward"}
                            size={24}
                            color="#fff"
                        />
                    </TouchableOpacity>
                </View>
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    main: { flex: 1, backgroundColor: '#fff' },
    container: { width, flex: 1 },
    topSection: {
        flex: 0.6,
        justifyContent: 'center',
        alignItems: 'center',
        borderBottomRightRadius: 100,
    },
    bottomSection: {
        flex: 0.4,
        padding: 40,
        alignItems: 'center',
    },
    title: {
        fontSize: 28,
        fontWeight: 'bold',
        color: '#333',
        textAlign: 'center',
        marginBottom: 20
    },
    description: {
        fontSize: 16,
        color: '#666',
        textAlign: 'center',
        lineHeight: 24
    },
    footer: {
        position: 'absolute',
        bottom: 50,
        left: 0,
        right: 0,
        paddingHorizontal: 40,
    },
    paginator: {
        flexDirection: 'row',
        height: 64,
        justifyContent: 'center',
        alignItems: 'center'
    },
    dot: {
        height: 10,
        borderRadius: 5,
        marginHorizontal: 8
    },
    buttonContainer: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginTop: 20
    },
    skipBtn: {
        fontSize: 16,
        color: '#999',
        fontWeight: '600'
    },
    nextBtn: {
        width: 60,
        height: 60,
        borderRadius: 30,
        justifyContent: 'center',
        alignItems: 'center',
        elevation: 5,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.2,
        shadowRadius: 5
    }
});
