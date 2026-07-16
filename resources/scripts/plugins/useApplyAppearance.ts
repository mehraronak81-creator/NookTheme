import { useEffect } from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { AppearanceSettings, FontScale, ThemeMode } from '@/state/appearance';

const FONT_SCALE_MAP: Record<FontScale, string> = {
    sm: '0.9',
    md: '1',
    lg: '1.1',
};

const hexToRgb = (hex: string): { r: number; g: number; b: number } | null => {
    const normalized = hex.replace('#', '');
    if (normalized.length !== 6) return null;
    const int = parseInt(normalized, 16);
    if (Number.isNaN(int)) return null;
    return { r: (int >> 16) & 255, g: (int >> 8) & 255, b: int & 255 };
};

// Lighten a hex colour by `amount` (0-255) per channel — used to derive the
// accent hover colour so custom accents still have a sensible hover state.
const lighten = (hex: string, amount: number): string => {
    const rgb = hexToRgb(hex);
    if (!rgb) return hex;
    const clamp = (v: number) => Math.max(0, Math.min(255, v));
    const r = clamp(rgb.r + amount);
    const g = clamp(rgb.g + amount);
    const b = clamp(rgb.b + amount);
    return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
};

const resolveTheme = (theme: ThemeMode): 'dark' | 'light' => {
    if (theme !== 'auto') return theme;
    if (typeof window !== 'undefined' && window.matchMedia) {
        return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    }
    return 'dark';
};

// Imperatively writes the appearance settings onto <html>. Safe to call
// outside React (e.g. eagerly on boot to avoid a flash of default styling).
export const applyAppearance = (data: AppearanceSettings): void => {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;

    root.setAttribute('data-theme', resolveTheme(data.theme));
    root.setAttribute('data-density', data.density);
    root.setAttribute('data-sidebar', data.sidebarStyle);
    root.setAttribute('data-contrast', data.highContrast ? 'high' : 'normal');
    root.style.setProperty('--font-scale', FONT_SCALE_MAP[data.fontScale] ?? '1');

    if (data.accent) {
        root.style.setProperty('--color-accent', data.accent);
        root.style.setProperty('--color-accent-hover', lighten(data.accent, 16));
        const rgb = hexToRgb(data.accent);
        if (rgb) {
            root.style.setProperty('--color-accent-glow', `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.3)`);
        }
    }
};

/**
 * Applies the current appearance settings to the document whenever they change,
 * and keeps `auto` theme in sync with the OS colour-scheme preference.
 */
export default (): void => {
    const data = useStoreState((state: ApplicationStore) => state.appearance.data);

    useEffect(() => {
        applyAppearance(data);
    }, [data]);

    useEffect(() => {
        if (data.theme !== 'auto' || typeof window === 'undefined' || !window.matchMedia) {
            return;
        }
        const mq = window.matchMedia('(prefers-color-scheme: light)');
        const handler = () => applyAppearance(data);
        // Safari <14 only supports the deprecated addListener signature.
        if (mq.addEventListener) {
            mq.addEventListener('change', handler);
            return () => mq.removeEventListener('change', handler);
        }
        mq.addListener(handler);
        return () => mq.removeListener(handler);
    }, [data]);
};
