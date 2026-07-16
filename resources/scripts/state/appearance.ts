import { action, Action } from 'easy-peasy';

export type ThemeMode = 'dark' | 'light' | 'auto';
export type FontScale = 'sm' | 'md' | 'lg';
export type Density = 'comfortable' | 'compact';
export type SidebarStyle = 'expanded' | 'collapsed';

export interface AppearanceSettings {
    theme: ThemeMode;
    accent: string;
    fontScale: FontScale;
    density: Density;
    sidebarStyle: SidebarStyle;
    highContrast: boolean;
}

export const DEFAULT_APPEARANCE: AppearanceSettings = {
    theme: 'dark',
    accent: '#7c6cf7',
    fontScale: 'md',
    density: 'comfortable',
    sidebarStyle: 'expanded',
    highContrast: false,
};

// Preset accent swatches surfaced on the Appearance page.
export const ACCENT_PRESETS: string[] = [
    '#7c6cf7', // vanta violet (default)
    '#4d7cfe', // indigo
    '#0abde3', // cyan
    '#34d97b', // green
    '#ffb020', // amber
    '#ff5c6c', // red
    '#e84393', // pink
    '#576574', // slate
];

const STORAGE_KEY = 'vantahost:appearance';
// The theme toggle historically persisted only to this key; keep it in sync
// so nothing that still reads it breaks.
const LEGACY_THEME_KEY = 'vantahost-theme';

export const loadAppearance = (): AppearanceSettings => {
    if (typeof window === 'undefined') {
        return { ...DEFAULT_APPEARANCE };
    }

    let stored: Partial<AppearanceSettings> = {};
    try {
        stored = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') || {};
    } catch (e) {
        stored = {};
    }

    const merged: AppearanceSettings = { ...DEFAULT_APPEARANCE, ...stored };

    // Back-compat: if the user only ever used the old theme toggle, honour it.
    if (!stored.theme) {
        const legacy = localStorage.getItem(LEGACY_THEME_KEY);
        if (legacy === 'dark' || legacy === 'light') {
            merged.theme = legacy;
        }
    }

    return merged;
};

export const persistAppearance = (settings: AppearanceSettings): void => {
    if (typeof window === 'undefined') return;
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
        if (settings.theme !== 'auto') {
            localStorage.setItem(LEGACY_THEME_KEY, settings.theme);
        }
    } catch (e) {
        // Ignore write failures (private mode / quota).
    }
};

export interface AppearanceStore {
    data: AppearanceSettings;
    setAppearance: Action<AppearanceStore, Partial<AppearanceSettings>>;
    resetAppearance: Action<AppearanceStore>;
}

const appearance: AppearanceStore = {
    data: loadAppearance(),

    setAppearance: action((state, payload) => {
        state.data = { ...state.data, ...payload };
        persistAppearance(state.data);
    }),

    resetAppearance: action((state) => {
        state.data = { ...DEFAULT_APPEARANCE };
        persistAppearance(state.data);
    }),
};

export default appearance;
