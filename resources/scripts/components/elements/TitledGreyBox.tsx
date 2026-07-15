import React, { memo } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { IconProp } from '@fortawesome/fontawesome-svg-core';
import tw from 'twin.macro';
import isEqual from 'react-fast-compare';

interface Props {
    icon?: IconProp;
    title: string | React.ReactNode;
    className?: string;
    children: React.ReactNode;
}

const TitledGreyBox = ({ icon, title, children, className }: Props) => (
    <div
        css={tw`rounded-xl overflow-hidden transition-all duration-250`}
        className={className}
        style={{
            background: 'var(--card-bg, #1a1a28)',
            border: '1px solid var(--color-border, #2a2a3a)',
            backdropFilter: 'blur(var(--glass-blur, 16px))',
            WebkitBackdropFilter: 'blur(var(--glass-blur, 16px))',
            boxShadow: 'var(--shadow-card, 0 2px 8px rgba(0,0,0,0.35))',
        }}
    >
        <div
            css={tw`p-3`}
            style={{
                background: 'var(--gradient-accent-subtle, rgba(115,130,255,0.08))',
                borderBottom: '1px solid var(--color-border, #2a2a3a)',
            }}
        >
            {typeof title === 'string' ? (
                <p css={tw`text-sm uppercase tracking-wide font-medium`} style={{ color: 'var(--text-secondary)' }}>
                    {icon && <FontAwesomeIcon icon={icon} css={tw`mr-2`} style={{ color: 'var(--color-accent)' }} />}
                    {title}
                </p>
            ) : (
                title
            )}
        </div>
        <div css={tw`p-3`}>{children}</div>
    </div>
);

export default memo(TitledGreyBox, isEqual);
