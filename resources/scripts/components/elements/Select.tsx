import styled, { css } from 'styled-components/macro';
import tw from 'twin.macro';

interface Props {
    hideDropdownArrow?: boolean;
}

const Select = styled.select<Props>`
    ${tw`shadow-none block p-3 pr-8 rounded-lg border w-full text-sm transition-all duration-150 ease-linear`};
    background-color: var(--color-surface, #1e1e2a);
    border-color: var(--color-border, #2a2a3a);
    color: var(--text-main, #e8e8f0);

    &,
    &:hover:not(:disabled),
    &:focus {
        ${tw`outline-none`};
    }

    -webkit-appearance: none;
    -moz-appearance: none;
    background-size: 1rem;
    background-repeat: no-repeat;
    background-position-x: calc(100% - 0.75rem);
    background-position-y: center;

    &::-ms-expand {
        display: none;
    }

    &:focus {
        border-color: var(--color-accent, #7382ff);
        box-shadow: 0 0 0 3px var(--color-accent-soft, rgba(115, 130, 255, 0.12));
    }

    ${(props) =>
        !props.hideDropdownArrow &&
        css`
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='%237382FF' d='M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z'/%3e%3c/svg%3e ");

            &:hover:not(:disabled),
            &:focus {
                border-color: var(--color-border-strong, #383850);
            }

            &:focus {
                border-color: var(--color-accent, #7382ff);
            }
        `};
`;

export default Select;
