import { createStore } from 'easy-peasy';
import flashes, { FlashStore } from '@/state/flashes';
import user, { UserStore } from '@/state/user';
import permissions, { GloablPermissionsStore } from '@/state/permissions';
import settings, { SettingsStore } from '@/state/settings';
import progress, { ProgressStore } from '@/state/progress';
import appearance, { AppearanceStore } from '@/state/appearance';
import toasts, { ToastStore } from '@/state/toasts';

export interface ApplicationStore {
    permissions: GloablPermissionsStore;
    flashes: FlashStore;
    user: UserStore;
    settings: SettingsStore;
    progress: ProgressStore;
    appearance: AppearanceStore;
    toasts: ToastStore;
}

const state: ApplicationStore = {
    permissions,
    flashes,
    user,
    settings,
    progress,
    appearance,
    toasts,
};

export const store = createStore(state);
