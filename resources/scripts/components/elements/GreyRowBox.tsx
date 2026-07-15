import styled from 'styled-components/macro';
import tw from 'twin.macro';

export default styled.div<{ $hoverable?: boolean }>`
    ${tw`flex rounded-xl no-underline items-center p-4 transition-all duration-250 overflow-hidden relative`};
    background: var(--card-bg, #1a1a28);
    color: var(--text-main, #e8e8f0);
    border: 1px solid var(--color-border, #2a2a3a);
    backdrop-filter: blur(var(--glass-blur, 16px));
    -webkit-backdrop-filter: blur(var(--glass-blur, 16px));
    box-shadow: var(--shadow-card, 0 2px 8px rgba(0, 0, 0, 0.35));
    transition-timing-function: var(--ease-premium, cubic-bezier(0.22, 1, 0.36, 1));

    &::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        pointer-events: none;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }

    ${(props) =>
        props.$hoverable !== false &&
        `
        &:hover {
            border-color: var(--color-accent, #6c5ce7);
            background: var(--card-bg-hover, #222235);
            box-shadow: var(--shadow-card-hover, 0 0 20px rgba(108, 92, 231, 0.15));
            transform: translateY(-2px);
        }
    `};

    & .icon {
        ${tw`rounded-xl w-16 flex items-center justify-center p-3`};
        background: var(--color-surface, #1e1e2a);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04), var(--shadow-sm, 0 1px 3px rgba(0, 0, 0, 0.4));
        transition: all 0.25s var(--ease-premium, ease);
    }

    ${(props) =>
        props.$hoverable !== false &&
        `
        &:hover .icon {
            background: var(--gradient-accent, linear-gradient(135deg, #7382FF, #a78bfa));
            color: #fff;
            box-shadow: var(--shadow-glow, 0 0 20px rgba(115, 130, 255, 0.3));
        }
    `};
`;
