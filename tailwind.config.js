const colors = require('tailwindcss/colors');

const gray = {
    50: colors.neutral[50],
    100: colors.neutral[100],
    200: colors.neutral[200],
    300: colors.neutral[300],
    400: colors.neutral[400],
    500: colors.neutral[500],
    600: colors.neutral[600],
    700: colors.neutral[700],
    800: colors.neutral[800],
    900: colors.neutral[900],
};

module.exports = {
    content: [
        './resources/scripts/**/*.{js,ts,tsx}',
    ],
    theme: {
        extend: {
            fontFamily: {
                header: ['"IBM Plex Sans"', '"Roboto"', 'system-ui', 'sans-serif'],
            },
            colors: {
                black: '#0a0a12',
                // "primary" and "neutral" are deprecated, prefer the use of "blue" and "gray"
                // in new code.
                primary: colors.blue,
                orange: colors.orange,
                gray: gray,
                neutral: gray,
                cyan: colors.cyan,
                neutral: {
                    50: colors.neutral[50],
                    100: colors.neutral[100],
                    200: colors.neutral[200],
                    300: colors.neutral[300],
                    400: colors.neutral[400],
                    500: colors.neutral[500],
                    600: colors.neutral[600],
                    700: '#17171B',
                    800: '#12121a',
                    900: '#0a0a12',
                },
                accent: {
                    DEFAULT: '#7382FF',
                    50: '#f0f1ff',
                    100: '#e0e3ff',
                    200: '#c5cbff',
                    300: '#a0aaff',
                    400: '#8a96ff',
                    500: '#7382FF',
                    600: '#5f6ee6',
                    700: '#4f5ccc',
                    800: '#414bb3',
                    900: '#343d99',
                },
            },
            fontSize: {
                '2xs': '0.625rem',
            },
            transitionDuration: {
                250: '250ms',
            },
            borderColor: theme => ({
                default: theme('colors.neutral.400', 'currentColor'),
            }),
            borderRadius: {
                'xl': '12px',
                '2xl': '16px',
            },
        },
    },
    plugins: [
        require('@tailwindcss/line-clamp'),
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
    ]
};
