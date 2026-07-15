import React, { forwardRef } from 'react';
import { Form } from 'formik';
import styled from 'styled-components/macro';
import { breakpoint } from '@/theme';
import FlashMessageRender from '@/components/FlashMessageRender';
import tw from 'twin.macro';

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
    title?: string;
};

const Container = styled.div`
    ${breakpoint('sm')`
        ${tw`w-4/5 mx-auto`}
    `};

    ${breakpoint('md')`
        ${tw`p-8`}
    `};

    ${breakpoint('lg')`
        ${tw`w-3/5`}
    `};

    ${breakpoint('xl')`
        ${tw`w-full`}
        max-width: 480px;
    `};
`;

const VantaLogo = () => (
    <svg viewBox="0 0 200 120" fill="none" xmlns="http://www.w3.org/2000/svg" css={tw`w-32 mx-auto`}>
        <defs>
            <linearGradient id="vhGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stopColor="#FFFFFF" />
                <stop offset="50%" stopColor="#FFFFFF" />
                <stop offset="50%" stopColor="#7382FF" />
                <stop offset="100%" stopColor="#7382FF" />
            </linearGradient>
        </defs>
        {/* V shape - white */}
        <path d="M30 15 L70 95 L110 15" stroke="#FFFFFF" strokeWidth="10" fill="none" strokeLinecap="round" strokeLinejoin="round"/>
        {/* H shape - periwinkle, connected to V */}
        <path d="M90 15 L90 95 M90 55 L140 55 M140 15 L140 95" stroke="#7382FF" strokeWidth="10" fill="none" strokeLinecap="round" strokeLinejoin="round"/>
    </svg>
);

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => (
    <Container>
        <div css={tw`text-center mb-6`}>
            <VantaLogo />
            <div
                css={tw`mt-3 text-xs font-semibold tracking-[0.25em] uppercase`}
                style={{ color: 'rgba(255,255,255,0.5)' }}
            >
                VANTA<span style={{ color: '#7382FF' }}>HOST</span>
            </div>
        </div>
        {title && (
            <h2 css={tw`text-2xl text-center font-semibold tracking-tight mb-2`} style={{ color: '#e8e8f0' }}>
                {title}
            </h2>
        )}
        <FlashMessageRender css={tw`mb-3 px-1`} />
        <Form {...props} ref={ref}>
            <div
                css={tw`w-full rounded-2xl overflow-hidden mx-auto`}
                style={{
                    background: 'rgba(18, 18, 26, 0.85)',
                    backdropFilter: 'blur(20px)',
                    border: '1px solid rgba(115, 130, 255, 0.15)',
                    boxShadow: '0 25px 60px -15px rgba(0,0,0,0.5), 0 0 40px -10px rgba(115,130,255,0.1)',
                }}
            >
                <div css={tw`p-8`}>{props.children}</div>
            </div>
        </Form>
        <p css={tw`text-center text-xs mt-6`} style={{ color: 'rgba(255,255,255,0.25)' }}>
            &copy; 2024 - {new Date().getFullYear()}&nbsp;
            <a
                rel={'noopener nofollow noreferrer'}
                href={'#'}
                target={'_blank'}
                css={tw`no-underline`}
                style={{ color: 'rgba(115,130,255,0.5)' }}
            >
                VantaHost
            </a>
        </p>
    </Container>
));
