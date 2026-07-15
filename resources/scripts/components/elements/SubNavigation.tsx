import styled from 'styled-components/macro';
import tw from 'twin.macro';

const SubNavigation = styled.div`
    ${tw`w-full overflow-x-auto`};
    background: var(--glass-bg, rgba(26, 26, 40, 0.6));
    backdrop-filter: blur(var(--glass-blur, 16px));
    -webkit-backdrop-filter: blur(var(--glass-blur, 16px));
    border-bottom: 1px solid var(--glass-border, rgba(115, 130, 255, 0.08));
    box-shadow: var(--shadow-sm, 0 1px 3px rgba(0, 0, 0, 0.4));

    & > div {
        ${tw`flex items-center text-sm mx-auto px-2`};
        max-width: 1200px;

        & > a,
        & > div {
            ${tw`inline-block py-3 px-4 no-underline whitespace-nowrap transition-all duration-150 relative`};
            color: var(--text-secondary, #b0b0c0);

            &:not(:first-of-type) {
                ${tw`ml-2`};
            }

            &::after {
                content: '';
                position: absolute;
                left: 50%;
                bottom: 0;
                transform: translateX(-50%) scaleX(0);
                width: calc(100% - 1.5rem);
                height: 2px;
                border-radius: 2px 2px 0 0;
                background: var(--gradient-accent, linear-gradient(135deg, #7382ff, #a78bfa));
                transition: transform 0.25s var(--ease-premium, cubic-bezier(0.22, 1, 0.36, 1));
            }

            &:hover {
                color: var(--text-main, #e8e8f0);
            }

            &:hover::after {
                transform: translateX(-50%) scaleX(0.6);
            }

            &:active,
            &.active {
                color: var(--text-main, #e8e8f0);
            }

            &:active::after,
            &.active::after {
                transform: translateX(-50%) scaleX(1);
                box-shadow: 0 0 10px var(--color-accent-glow, rgba(115, 130, 255, 0.3));
            }
        }
    }
`;

export default SubNavigation;
