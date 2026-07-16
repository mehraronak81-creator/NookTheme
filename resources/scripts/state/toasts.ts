import { Action, action } from 'easy-peasy';
import { FlashMessageType } from '@/components/MessageBox';

export interface Toast {
    id: string;
    type: FlashMessageType;
    title?: string;
    message: string;
    // Milliseconds before the toast auto-dismisses. 0 disables auto-dismiss.
    timeout: number;
}

export interface ToastStore {
    items: Toast[];
    add: Action<ToastStore, Toast>;
    remove: Action<ToastStore, string>;
    clear: Action<ToastStore, void>;
}

const toasts: ToastStore = {
    items: [],

    add: action((state, payload) => {
        state.items = [...state.items.filter((t) => t.id !== payload.id), payload];
    }),

    remove: action((state, id) => {
        state.items = state.items.filter((t) => t.id !== id);
    }),

    clear: action((state) => {
        state.items = [];
    }),
};

export default toasts;
