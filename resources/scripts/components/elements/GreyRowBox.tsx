import styled from 'styled-components/macro';
import tw from 'twin.macro';

export default styled.div<{ $hoverable?: boolean }>`
    ${tw`flex rounded-xl no-underline items-center p-4 transition-all duration-200 overflow-hidden`};
    background: var(--card-bg, #1a1a28);
    color: var(--text-main, #e8e8f0);
    border: 1px solid var(--color-border, #2a2a3a);

    ${(props) => props.$hoverable !== false && `
        &:hover {
            border-color: var(--color-accent, #6c5ce7);
            box-shadow: 0 0 20px var(--color-accent-glow, rgba(108, 92, 231, 0.15));
            transform: translateY(-1px);
        }
    `};

    & .icon {
        ${tw`rounded-xl w-16 flex items-center justify-center p-3`};
        background: var(--color-surface, #1e1e2a);
    }
`;
