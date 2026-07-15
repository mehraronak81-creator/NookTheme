import React, { useContext } from 'react';
import { DialogContext } from './';
import { useDeepCompareEffect } from '@/plugins/useDeepCompareEffect';

export default ({ children }: { children: React.ReactNode }) => {
    const { setFooter } = useContext(DialogContext);

    useDeepCompareEffect(() => {
        setFooter(
            <div
                className={'px-6 py-3 flex items-center justify-end space-x-3 rounded-b-xl'}
                style={{
                    background: 'var(--color-surface, #1e1e2a)',
                    borderTop: '1px solid var(--color-border, #2a2a3a)',
                }}
            >
                {children}
            </div>
        );
    }, [children]);

    return null;
};
