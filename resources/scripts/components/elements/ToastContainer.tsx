import React, { useEffect } from 'react';
import { createPortal } from 'react-dom';
import { Actions, useStoreActions, useStoreState } from 'easy-peasy';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faTimes } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import { ApplicationStore } from '@/state';
import { Toast } from '@/state/toasts';
import { FlashMessageType } from '@/components/MessageBox';

const background = (type?: FlashMessageType) => {
    switch (type) {
        case 'error':
            return tw`bg-red-600 border-red-800`;
        case 'success':
            return tw`bg-green-600 border-green-800`;
        case 'warning':
            return tw`bg-yellow-600 border-yellow-800`;
        default:
            return tw`bg-primary-600 border-primary-800`;
    }
};

const ToastCard = styled.div<{ $type?: FlashMessageType }>`
    ${tw`p-3 border rounded shadow-lg text-sm text-white flex items-start w-full`};
    ${(props) => background(props.$type)};
`;

const ToastItem = ({ toast }: { toast: Toast }) => {
    const remove = useStoreActions((actions: Actions<ApplicationStore>) => actions.toasts.remove);

    useEffect(() => {
        if (!toast.timeout) {
            return;
        }
        const timer = setTimeout(() => remove(toast.id), toast.timeout);
        return () => clearTimeout(timer);
    }, [toast.id, toast.timeout, remove]);

    return (
        <ToastCard $type={toast.type} css={tw`mb-2`} role={'alert'}>
            <div css={tw`flex-1`}>
                {toast.title && <p css={tw`font-semibold mb-1`}>{toast.title}</p>}
                <p>{toast.message}</p>
            </div>
            <button
                type={'button'}
                onClick={() => remove(toast.id)}
                css={tw`ml-3 opacity-80 hover:opacity-100`}
                aria-label={'Dismiss notification'}
            >
                <FontAwesomeIcon icon={faTimes} />
            </button>
        </ToastCard>
    );
};

export default () => {
    const toasts = useStoreState((state: ApplicationStore) => state.toasts.items);

    if (typeof document === 'undefined') {
        return null;
    }

    return createPortal(
        <div css={tw`fixed z-50 bottom-0 right-0 p-4 w-full max-w-sm pointer-events-none`}>
            <div css={tw`pointer-events-auto`}>
                {toasts.map((toast) => (
                    <ToastItem key={toast.id} toast={toast} />
                ))}
            </div>
        </div>,
        document.body
    );
};
