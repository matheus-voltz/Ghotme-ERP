import React, { useState, useMemo } from 'react';
import {
    View,
    Text,
    StyleSheet,
    TouchableOpacity,
    ScrollView,
    TextInput,
    StatusBar,
    Platform,
    KeyboardAvoidingView
} from 'react-native';
import { Stack, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import Animated, {
    FadeInDown,
    FadeInRight,
    FadeOutUp,
    Layout,
    useAnimatedStyle,
    withTiming,
    useSharedValue
} from 'react-native-reanimated';
import { useTheme } from '../../../context/ThemeContext';
import { useLanguage } from '../../../context/LanguageContext';

interface FAQItem {
    id: string;
    question: string;
    answer: string;
    category: string;
}

const FAQ_DATA: FAQItem[] = [
    {
        id: '1',
        category: 'Geral',
        question: 'Como faço para cadastrar uma nova Ordem de Serviço?',
        answer: 'Para cadastrar uma nova OS, vá para a aba "Ordens" e clique no botão flutuante "+". Preencha os dados do cliente, do veículo e descreva o serviço a ser realizado.'
    },
    {
        id: '2',
        category: 'Financeiro',
        question: 'Como funcionam os pagamentos via ASAAS?',
        answer: 'O sistema integra com o ASAAS para gerar cobranças automáticas. Você pode gerar boletos, cartões de crédito ou PIX diretamente pela tela de fechamento da OS.'
    },
    {
        id: '3',
        category: 'Geral',
        question: 'Posso usar o aplicativo offline?',
        answer: 'Alguns dados são salvos em cache para consulta rápida, mas para realizar atualizações, criar novas OS ou enviar notificações, é necessária uma conexão ativa com a internet.'
    },
    {
        id: '4',
        category: 'Configurações',
        question: 'Como ativo a biometria para login?',
        answer: 'Vá em Perfil > Segurança e ative a opção "Entrar com Biometria". Certifique-se de que seu celular já possui uma biometria cadastrada no sistema.'
    },
    {
        id: '5',
        category: 'Mensagens',
        question: 'O cliente recebe notificações automáticas?',
        answer: 'Sim! Sempre que o status de uma OS é alterado ou um novo orçamento é gerado, o cliente pode receber notificações via e-mail e futuramente via WhatsApp, dependendo das configurações do seu plano.'
    },
    {
        id: '6',
        category: 'Financeiro',
        question: 'Como vejo meu faturamento mensal?',
        answer: 'No Dashboard (aba Início), você tem um resumo visual do faturamento. Para relatórios detalhados, utilize a versão web do Ghotme ERP.'
    },
    {
        id: '7',
        category: 'Configurações',
        question: 'Como altero o idioma do aplicativo?',
        answer: 'Na aba Perfil, procure pela opção "Idioma". Atualmente suportamos Português, Inglês, Espanhol e Francês.'
    }
];

const CATEGORIES = ['Tudo', 'Geral', 'Financeiro', 'Configurações', 'Mensagens'];

const AccordionItem = ({ item, isExpanded, onToggle, colors }: {
    item: FAQItem;
    isExpanded: boolean;
    onToggle: () => void;
    colors: any;
}) => {
    const rotateStyle = useAnimatedStyle(() => {
        return {
            transform: [{ rotate: withTiming(isExpanded ? '180deg' : '0deg') }]
        };
    });

    return (
        <Animated.View
            layout={Layout.springify()}
            style={[
                styles.faqCard,
                {
                    backgroundColor: colors.card,
                    borderLeftColor: isExpanded ? '#7367F0' : 'transparent',
                    borderLeftWidth: 4
                }
            ]}
        >
            <TouchableOpacity
                onPress={onToggle}
                activeOpacity={0.7}
                style={styles.faqHeader}
            >
                <View style={styles.faqHeaderLeft}>
                    <View style={styles.categoryBadge}>
                        <Text style={styles.categoryBadgeText}>{item.category}</Text>
                    </View>
                    <Text style={[styles.question, { color: colors.text }]}>{item.question}</Text>
                </View>
                <Animated.View style={rotateStyle}>
                    <Ionicons name="chevron-down" size={20} color={colors.subText} />
                </Animated.View>
            </TouchableOpacity>

            {isExpanded && (
                <Animated.View
                    entering={FadeInDown.duration(300)}
                    exiting={FadeOutUp.duration(200)}
                    style={styles.answerContainer}
                >
                    <Text style={[styles.answer, { color: colors.subText }]}>
                        {item.answer}
                    </Text>
                </Animated.View>
            )}
        </Animated.View>
    );
};

export default function FAQScreen() {
    const { colors, activeTheme } = useTheme();
    const { t } = useLanguage();
    const router = useRouter();
    const [search, setSearch] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('Tudo');
    const [expandedId, setExpandedId] = useState<string | null>(null);

    const filteredFaq = useMemo(() => {
        return FAQ_DATA.filter(item => {
            const matchesSearch = item.question.toLowerCase().includes(search.toLowerCase()) ||
                item.answer.toLowerCase().includes(search.toLowerCase());
            const matchesCategory = selectedCategory === 'Tudo' || item.category === selectedCategory;
            return matchesSearch && matchesCategory;
        });
    }, [search, selectedCategory]);

    return (
        <KeyboardAvoidingView
            behavior={Platform.OS === "ios" ? "padding" : "height"}
            style={[styles.container, { backgroundColor: colors.background }]}
        >
            <StatusBar barStyle="light-content" />

            {/* Premium Header */}
            <LinearGradient
                colors={['#7367F0', '#CE9FFC']}
                style={styles.header}
            >
                <View style={styles.headerTop}>
                    <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
                        <Ionicons name="chevron-back" size={28} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.headerTitle}>{t('faq_title')}</Text>
                    <View style={{ width: 40 }} />
                </View>

                <View style={styles.searchContainer}>
                    <Ionicons name="search" size={20} color="#fff" style={styles.searchIcon} />
                    <TextInput
                        style={styles.searchInput}
                        placeholder={t('faq_search_placeholder')}
                        placeholderTextColor="rgba(255,255,255,0.7)"
                        value={search}
                        onChangeText={setSearch}
                    />
                </View>
            </LinearGradient>

            <View style={styles.content}>
                {/* Categories */}
                <View style={styles.categoriesWrapper}>
                    <ScrollView
                        horizontal
                        showsHorizontalScrollIndicator={false}
                        contentContainerStyle={styles.categoriesScroll}
                    >
                        {CATEGORIES.map((cat) => (
                            <TouchableOpacity
                                key={cat}
                                onPress={() => setSelectedCategory(cat)}
                                style={[
                                    styles.categoryItem,
                                    selectedCategory === cat && styles.categoryItemActive,
                                    { backgroundColor: selectedCategory === cat ? '#7367F0' : (activeTheme === 'dark' ? '#2f2b3a' : '#fff') }
                                ]}
                            >
                                <Text style={[
                                    styles.categoryText,
                                    { color: selectedCategory === cat ? '#fff' : colors.text }
                                ]}>
                                    {cat === 'Tudo' ? t('all') : cat}
                                </Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>
                </View>

                {/* FAQ List */}
                <ScrollView
                    contentContainerStyle={styles.faqList}
                    showsVerticalScrollIndicator={false}
                >
                    {filteredFaq.length > 0 ? (
                        filteredFaq.map((item) => (
                            <AccordionItem
                                key={item.id}
                                item={item}
                                isExpanded={expandedId === item.id}
                                onToggle={() => setExpandedId(expandedId === item.id ? null : item.id)}
                                colors={colors}
                            />
                        ))
                    ) : (
                        <View style={styles.emptyContainer}>
                            <Ionicons name="search-outline" size={64} color={colors.subText + '33'} />
                            <Text style={[styles.emptyText, { color: colors.subText }]}>
                                {t('faq_empty')}
                            </Text>
                        </View>
                    )}

                    {/* Contact Support Section */}
                    <Animated.View entering={FadeInDown.delay(400)} style={styles.supportCard}>
                        <LinearGradient
                            colors={['#FF9F43', '#FF6B00']}
                            style={styles.supportGradient}
                        >
                            <View style={styles.supportContent}>
                                <View style={{ flex: 1, marginRight: 10 }}>
                                    <Text style={styles.supportTitle}>{t('faq_still_questions')}</Text>
                                    <Text style={styles.supportSubtitle}>{t('faq_support_subtitle')}</Text>
                                </View>
                                <TouchableOpacity
                                    style={styles.supportButton}
                                    onPress={() => router.push('/chat/contacts')}
                                >
                                    <Text style={styles.supportButtonText}>{t('faq_talk_now')}</Text>
                                </TouchableOpacity>
                            </View>
                        </LinearGradient>
                    </Animated.View>
                </ScrollView>
            </View>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
    },
    header: {
        paddingTop: 60,
        paddingBottom: 25,
        paddingHorizontal: 20,
        borderBottomLeftRadius: 30,
        borderBottomRightRadius: 30,
    },
    headerTop: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        marginBottom: 20,
    },
    headerTitle: {
        color: '#fff',
        fontSize: 20,
        fontWeight: 'bold',
    },
    backButton: {
        width: 40,
        height: 40,
        borderRadius: 20,
        backgroundColor: 'rgba(255,255,255,0.2)',
        justifyContent: 'center',
        alignItems: 'center',
    },
    searchContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(255,255,255,0.2)',
        borderRadius: 15,
        paddingHorizontal: 15,
        height: 50,
    },
    searchIcon: {
        marginRight: 10,
    },
    searchInput: {
        flex: 1,
        color: '#fff',
        fontSize: 16,
    },
    content: {
        flex: 1,
    },
    categoriesWrapper: {
        marginTop: 20,
        marginBottom: 10,
    },
    categoriesScroll: {
        paddingHorizontal: 20,
    },
    categoryItem: {
        paddingHorizontal: 20,
        paddingVertical: 10,
        borderRadius: 20,
        marginRight: 10,
        borderWidth: 1,
        borderColor: 'rgba(115, 103, 240, 0.1)',
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 5,
        elevation: 2,
    },
    categoryItemActive: {
        borderColor: '#7367F0',
    },
    categoryText: {
        fontWeight: '600',
        fontSize: 14,
    },
    faqList: {
        paddingHorizontal: 20,
        paddingBottom: 40,
    },
    faqCard: {
        borderRadius: 16,
        marginBottom: 12,
        padding: 16,
        borderLeftWidth: 4,
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 1,
    },
    faqHeader: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
    },
    faqHeaderLeft: {
        flex: 1,
        paddingRight: 10,
    },
    categoryBadge: {
        backgroundColor: 'rgba(115, 103, 240, 0.1)',
        paddingHorizontal: 8,
        paddingVertical: 2,
        borderRadius: 4,
        alignSelf: 'flex-start',
        marginBottom: 6,
    },
    categoryBadgeText: {
        color: '#7367F0',
        fontSize: 10,
        fontWeight: 'bold',
        textTransform: 'uppercase',
    },
    question: {
        fontSize: 15,
        fontWeight: '700',
        lineHeight: 20,
    },
    answerContainer: {
        overflow: 'hidden',
    },
    answer: {
        fontSize: 14,
        lineHeight: 22,
    },
    emptyContainer: {
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 60,
    },
    emptyText: {
        marginTop: 15,
        fontSize: 14,
        textAlign: 'center',
        width: '80%',
    },
    supportCard: {
        marginTop: 20,
        borderRadius: 20,
        overflow: 'hidden',
    },
    supportGradient: {
        padding: 20,
    },
    supportContent: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
    },
    supportTitle: {
        color: '#fff',
        fontSize: 18,
        fontWeight: 'bold',
    },
    supportSubtitle: {
        color: 'rgba(255,255,255,0.8)',
        fontSize: 12,
        marginTop: 4,
    },
    supportButton: {
        backgroundColor: '#fff',
        paddingHorizontal: 15,
        paddingVertical: 10,
        borderRadius: 12,
    },
    supportButtonText: {
        color: '#FF6B00',
        fontWeight: 'bold',
        fontSize: 14,
    },
});
