import tw from 'twin.macro';
import { createGlobalStyle } from 'styled-components/macro';
// @ts-expect-error untyped font file
import font from '@fontsource-variable/ibm-plex-sans/files/ibm-plex-sans-latin-wght-normal.woff2';

export default createGlobalStyle`
    @font-face {
        font-family: 'IBM Plex Sans';
        font-style: normal;
        font-display: swap;
        font-weight: 100 700;
        src: url(${font}) format('woff2-variations');
        unicode-range: U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;
    }

    body {
        ${tw`font-sans`};
        background-color: var(--background-color);
        color: var(--text-main);
        letter-spacing: 0.015em;
        transition: background-color 0.25s ease, color 0.25s ease;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    h1, h2, h3, h4, h5, h6 {
        ${tw`font-medium tracking-normal font-header`};
        color: var(--text-main);
    }

    h1, h2 {
        letter-spacing: -0.02em;
    }

    p {
        color: var(--text-secondary);
        ${tw`leading-snug font-sans`};
    }

    form {
        ${tw`m-0`};
    }

    textarea, select, input, button, button:focus, button:focus-visible {
        ${tw`outline-none`};
    }

    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield !important;
    }

    /* Scroll Bar Style */
    ::-webkit-scrollbar {
        background: none;
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--scrollbar-thumb, #2a2a40);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--color-accent, #6c5ce7);
    }

    ::-webkit-scrollbar-track-piece {
        margin: 4px 0;
    }

    ::-webkit-scrollbar-corner {
        background: transparent;
    }

    /* Selection */
    ::selection {
        background: var(--color-accent-glow, rgba(108, 92, 231, 0.3));
        color: var(--text-main);
    }
`;
