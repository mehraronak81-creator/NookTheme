import { useCallback } from 'react';
import { useStoreActions } from 'easy-peasy';
import { Actions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { FlashMessageType } from '@/components/MessageBox';

interface ToastOptions {
    title?: string;
    type?: FlashMessageType;
    // Milliseconds before auto-dismiss. Pass 0 to require manual dismissal.
    timeout?: number;
}

let counter = 0;

/**
 * Returns a helper for pushing floating toast notifications. Usage:
 *
 *   const toast = useToast();
 *   toast('Server started.', { type: 'success' });
 */
export default () => {
    const add = useStoreActions((actions: Actions<ApplicationStore>) => actions.toasts.add);

    return useCallback(
        (message: string, options?: ToastOptions) => {
            counter += 1;
            add({
                id: `toast-${counter}`,
                message,
                title: options?.title,
                type: options?.type ?? 'info',
                timeout: options?.timeout ?? 5000,
            });
        },
        [add]
    );
};
