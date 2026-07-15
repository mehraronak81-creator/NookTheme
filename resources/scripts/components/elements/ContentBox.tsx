import React from 'react';
import FlashMessageRender from '@/components/FlashMessageRender';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import tw from 'twin.macro';

type Props = Readonly<
    React.DetailedHTMLProps<React.HTMLAttributes<HTMLDivElement>, HTMLDivElement> & {
        title?: string;
        borderColor?: string;
        showFlashes?: string | boolean;
        showLoadingOverlay?: boolean;
    }
>;

const ContentBox = ({ title, borderColor, showFlashes, showLoadingOverlay, children, ...props }: Props) => (
    <div {...props}>
        {title && (
            <h2 css={tw`mb-4 px-4 text-2xl`} style={{ color: 'var(--text-main)' }}>
                {title}
            </h2>
        )}
        {showFlashes && (
            <FlashMessageRender byKey={typeof showFlashes === 'string' ? showFlashes : undefined} css={tw`mb-4`} />
        )}
        <div
            css={[tw`p-4 rounded-xl relative transition-all duration-250`, !!borderColor && tw`border-t-4`]}
            style={{
                background: 'var(--card-bg, #1a1a28)',
                border: '1px solid var(--color-border, #2a2a3a)',
                borderTopWidth: borderColor ? undefined : '1px',
                backdropFilter: 'blur(var(--glass-blur, 16px))',
                WebkitBackdropFilter: 'blur(var(--glass-blur, 16px))',
                boxShadow: 'var(--shadow-card, 0 2px 8px rgba(0,0,0,0.35))',
            }}
        >
            <SpinnerOverlay visible={showLoadingOverlay || false} />
            {children}
        </div>
    </div>
);

export default ContentBox;
