import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, TextInput, ActivityIndicator, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import Animated, { FadeIn, FadeOut } from 'react-native-reanimated';
import { useTheme } from '../../../../context/ThemeContext';
import * as Haptics from 'expo-haptics';
import api from '../../../../services/api';
import Svg, { Line, Circle } from 'react-native-svg';

interface DevicePasswordProps {
    osId: string | number;
    initialPassword?: string;
    initialPattern?: string;
    onUpdate?: (newPassword: string, newPattern: string) => void;
}

const GRID_SIZE = 240;
const DOTS = [
    { id: 1, cx: 40, cy: 40 },
    { id: 2, cx: 120, cy: 40 },
    { id: 3, cx: 200, cy: 40 },
    { id: 4, cx: 40, cy: 120 },
    { id: 5, cx: 120, cy: 120 },
    { id: 6, cx: 200, cy: 120 },
    { id: 7, cx: 40, cy: 200 },
    { id: 8, cx: 120, cy: 200 },
    { id: 9, cx: 200, cy: 200 },
];

export default function DevicePassword({ osId, initialPassword, initialPattern, onUpdate }: DevicePasswordProps) {
    const { colors } = useTheme();

    const [password, setPassword] = useState(initialPassword || '');
    const [pattern, setPattern] = useState(initialPattern || '');

    // UI states
    const [isVisible, setIsVisible] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [loading, setLoading] = useState(false);

    // Editor States
    const [activeTab, setActiveTab] = useState<'pin' | 'pattern'>('pin');
    const [tempPassword, setTempPassword] = useState('');
    const [tempPatternSeq, setTempPatternSeq] = useState<number[]>([]);

    useEffect(() => {
        setPassword(initialPassword || '');
        setPattern(initialPattern || '');
    }, [initialPassword, initialPattern]);

    const toggleVisibility = () => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        setIsVisible(!isVisible);
    };

    const handleEdit = () => {
        setTempPassword(password);
        setTempPatternSeq(pattern ? pattern.split('-').map(Number) : []);
        setActiveTab(pattern ? 'pattern' : 'pin');
        setIsEditing(true);
    };

    const handleDotPress = (dotId: number) => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        if (tempPatternSeq.includes(dotId)) {
            return; // Ignore if already selected
        }
        setTempPatternSeq([...tempPatternSeq, dotId]);
    };

    const clearPattern = () => {
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
        setTempPatternSeq([]);
    };

    const handleSave = async () => {
        if (loading) return;
        setLoading(true);

        // Clean values based on active tab
        const finalPwd = activeTab === 'pin' ? tempPassword.trim() : '';
        const finalPat = activeTab === 'pattern' ? tempPatternSeq.join('-') : '';

        try {
            await api.patch(`/os/${osId}/password`, {
                device_password: finalPwd,
                device_pattern_lock: finalPat
            });

            setPassword(finalPwd);
            setPattern(finalPat);

            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
            if (onUpdate) onUpdate(finalPwd, finalPat);
            setIsEditing(false);
        } catch (error) {
            console.error('Erro ao salvar senha:', error);
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
        } finally {
            setLoading(false);
        }
    };

    const renderPatternGrid = (seq: number[], interactive: boolean = false) => {
        return (
            <View style={{ width: GRID_SIZE, height: GRID_SIZE, alignSelf: 'center', marginVertical: 10 }}>
                <Svg height={GRID_SIZE} width={GRID_SIZE}>
                    {/* Draw lines between points */}
                    {seq.map((dotId, index) => {
                        if (index === seq.length - 1) return null;
                        const nextDotId = seq[index + 1];
                        const d1 = DOTS.find(d => d.id === dotId);
                        const d2 = DOTS.find(d => d.id === nextDotId);
                        if (!d1 || !d2) return null;
                        return (
                            <Line
                                key={`line-${index}`}
                                x1={d1.cx} y1={d1.cy}
                                x2={d2.cx} y2={d2.cy}
                                stroke="#00CFE8"
                                strokeWidth="4"
                                strokeLinecap="round"
                            />
                        );
                    })}

                    {/* Draw dots */}
                    {DOTS.map((dot) => {
                        const isSelected = seq.includes(dot.id);
                        return (
                            <Circle
                                key={`dot-${dot.id}`}
                                cx={dot.cx}
                                cy={dot.cy}
                                r={isSelected ? "12" : "8"}
                                fill={isSelected ? "#00CFE8" : colors.border}
                            />
                        );
                    })}
                </Svg>

                {/* Invisible touchable overlay for easy tapping when interactive */}
                {interactive && DOTS.map((dot) => (
                    <TouchableOpacity
                        key={`touch-${dot.id}`}
                        style={{
                            position: 'absolute',
                            left: dot.cx - 24,
                            top: dot.cy - 24,
                            width: 48,
                            height: 48,
                            borderRadius: 24,
                        }}
                        onPress={() => handleDotPress(dot.id)}
                        activeOpacity={1}
                    />
                ))}
            </View>
        );
    };

    const hasAnyLock = !!password || !!pattern;

    const renderViewer = () => {
        if (!hasAnyLock) {
            return (
                <Text style={[styles.emptyText, { color: colors.subText }]}>
                    Nenhum bloqueio cadastrado.
                </Text>
            );
        }

        if (!isVisible) {
            return (
                <View style={styles.dotsContainer}>
                    {[...Array(6)].map((_, i) => (
                        <View key={i} style={[styles.dot, { backgroundColor: colors.text }]} />
                    ))}
                </View>
            );
        }

        if (pattern) {
            const seq = pattern.split('-').map(Number);
            return renderPatternGrid(seq, false);
        }

        return (
            <Text style={[styles.passwordText, { color: colors.text }]}>
                {password}
            </Text>
        );
    };

    return (
        <View style={styles.container}>
            {/* Display Mode */}
            {!isEditing && (
                <Animated.View exiting={FadeOut} style={[styles.card, { backgroundColor: colors.card, borderColor: colors.border }]}>
                    <View style={styles.header}>
                        <View style={styles.headerLeft}>
                            <Ionicons name="lock-closed" size={18} color="#00CFE8" />
                            <Text style={[styles.headerTitle, { color: colors.text }]}>Desbloqueio Seguro</Text>
                        </View>
                        <TouchableOpacity style={styles.editBtn} onPress={handleEdit}>
                            <Ionicons name="pencil" size={16} color={colors.subText} />
                        </TouchableOpacity>
                    </View>

                    <View style={styles.contentRow}>
                        <View style={[styles.passwordViewer, isVisible && pattern ? { height: 'auto', minHeight: GRID_SIZE } : {}]}>
                            {renderViewer()}
                        </View>

                        {hasAnyLock ? (
                            <TouchableOpacity
                                style={[styles.eyeBtn, { backgroundColor: isVisible ? '#00CFE820' : colors.background }]}
                                onPress={toggleVisibility}
                                activeOpacity={0.7}
                            >
                                <Ionicons
                                    name={isVisible ? "eye-off" : "eye"}
                                    size={20}
                                    color={isVisible ? "#00CFE8" : colors.subText}
                                />
                            </TouchableOpacity>
                        ) : null}
                    </View>

                    {hasAnyLock && !isVisible ? (
                        <Text style={[styles.hintText, { color: colors.subText }]}>Toque no ícone para revelar a senha/padrão</Text>
                    ) : null}
                </Animated.View>
            )}

            {/* Edit Mode */}
            {isEditing && (
                <Animated.View entering={FadeIn} exiting={FadeOut} style={[styles.card, { backgroundColor: colors.card, borderColor: '#00CFE8' }]}>
                    <View style={styles.header}>
                        <View style={styles.headerLeft}>
                            <Ionicons name="shield-checkmark" size={18} color="#00CFE8" />
                            <Text style={[styles.headerTitle, { color: colors.text }]}>Atualizar Desbloqueio</Text>
                        </View>
                    </View>

                    <View style={[styles.tabsContainer, { backgroundColor: colors.background }]}>
                        <TouchableOpacity
                            style={[styles.tab, activeTab === 'pin' ? [styles.activeTab, { backgroundColor: colors.card }] : {}]}
                            onPress={() => setActiveTab('pin')}
                        >
                            <Text style={[styles.tabText, { color: activeTab === 'pin' ? '#00CFE8' : colors.subText, fontWeight: activeTab === 'pin' ? 'bold' : 'normal' }]}>PIN / Senha</Text>
                        </TouchableOpacity>
                        <TouchableOpacity
                            style={[styles.tab, activeTab === 'pattern' ? [styles.activeTab, { backgroundColor: colors.card }] : {}]}
                            onPress={() => setActiveTab('pattern')}
                        >
                            <Text style={[styles.tabText, { color: activeTab === 'pattern' ? '#00CFE8' : colors.subText, fontWeight: activeTab === 'pattern' ? 'bold' : 'normal' }]}>Padrão/Desenho</Text>
                        </TouchableOpacity>
                    </View>

                    {activeTab === 'pin' ? (
                        <TextInput
                            style={[styles.input, { color: colors.text, borderColor: colors.border, backgroundColor: colors.background }]}
                            value={tempPassword}
                            onChangeText={setTempPassword}
                            placeholder="Ex: 123456 ou Alfanumérico"
                            placeholderTextColor={colors.subText}
                            autoCapitalize="none"
                            autoCorrect={false}
                            autoFocus
                        />
                    ) : (
                        <View style={styles.patternEditContainer}>
                            <Text style={[styles.hintText, { color: colors.subText, fontSize: 13 }]}>Toque nas bolinhas na sequência do desenho do padrão.</Text>
                            {renderPatternGrid(tempPatternSeq, true)}
                            <TouchableOpacity onPress={clearPattern} style={styles.clearBtn}>
                                <Ionicons name="trash-outline" size={16} color="#EA5455" />
                                <Text style={styles.clearText}>Refazer Desenho</Text>
                            </TouchableOpacity>
                        </View>
                    )}

                    <View style={styles.actionsRow}>
                        <TouchableOpacity
                            style={[styles.actionBtn, { backgroundColor: colors.background }]}
                            onPress={() => setIsEditing(false)}
                            disabled={loading}
                        >
                            <Text style={[styles.actionBtnText, { color: colors.text }]}>Cancelar</Text>
                        </TouchableOpacity>

                        <TouchableOpacity
                            style={[styles.actionBtn, { backgroundColor: '#00CFE8' }]}
                            onPress={handleSave}
                            disabled={loading}
                        >
                            {loading ? (
                                <ActivityIndicator size="small" color="#fff" />
                            ) : (
                                <Text style={[styles.actionBtnText, { color: '#fff', fontWeight: 'bold' }]}>Salvar</Text>
                            )}
                        </TouchableOpacity>
                    </View>
                </Animated.View>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        marginBottom: 20,
    },
    card: {
        borderWidth: 1,
        borderRadius: 16,
        padding: 16,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 5,
        elevation: 1,
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 12,
    },
    headerLeft: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
    },
    headerTitle: {
        fontSize: 14,
        fontWeight: 'bold',
    },
    editBtn: {
        padding: 4,
    },
    contentRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
    },
    passwordViewer: {
        flex: 1,
        height: 44,
        justifyContent: 'center',
    },
    emptyText: {
        fontSize: 14,
        fontStyle: 'italic',
        opacity: 0.7,
    },
    passwordText: {
        fontSize: 20,
        fontWeight: '700',
        letterSpacing: 2,
    },
    dotsContainer: {
        flexDirection: 'row',
        gap: 6,
        alignItems: 'center',
    },
    dot: {
        width: 10,
        height: 10,
        borderRadius: 5,
    },
    eyeBtn: {
        width: 44,
        height: 44,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        marginLeft: 12,
    },
    hintText: {
        fontSize: 11,
        marginTop: 8,
        opacity: 0.6,
        textAlign: 'center',
    },
    tabsContainer: {
        flexDirection: 'row',
        borderRadius: 10,
        padding: 4,
        marginBottom: 16,
    },
    tab: {
        flex: 1,
        paddingVertical: 8,
        alignItems: 'center',
        borderRadius: 8,
    },
    activeTab: {
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.1,
        shadowRadius: 2,
        elevation: 2,
    },
    tabText: {
        fontSize: 14,
    },
    input: {
        height: 48,
        borderWidth: 1,
        borderRadius: 12,
        paddingHorizontal: 16,
        fontSize: 16,
        marginBottom: 16,
    },
    patternEditContainer: {
        alignItems: 'center',
        marginBottom: 16,
    },
    clearBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        backgroundColor: '#EA545510',
        paddingVertical: 8,
        paddingHorizontal: 16,
        borderRadius: 20,
        marginTop: 10,
    },
    clearText: {
        color: '#EA5455',
        fontWeight: 'bold',
        fontSize: 13,
    },
    actionsRow: {
        flexDirection: 'row',
        justifyContent: 'flex-end',
        gap: 12,
    },
    actionBtn: {
        paddingVertical: 10,
        paddingHorizontal: 16,
        borderRadius: 8,
        minWidth: 90,
        alignItems: 'center',
    },
    actionBtnText: {
        fontSize: 14,
        fontWeight: '600',
    }
});
