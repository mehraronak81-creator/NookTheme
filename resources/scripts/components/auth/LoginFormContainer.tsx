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
        max-width: 760px;
    `};
`;

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => (
    <Container>
        {title && <h2 css={tw`text-3xl text-center text-neutral-100 font-semibold tracking-tight py-4`}>{title}</h2>}
        <FlashMessageRender css={tw`mb-3 px-1`} />
        <Form {...props} ref={ref}>
            <div css={tw`md:flex w-full bg-white/95 backdrop-blur-sm shadow-[0_20px_60px_-20px_rgba(15,23,42,0.35)] rounded-2xl border border-neutral-200 overflow-hidden mx-1 md:mx-0`}>
                <div css={tw`flex-none select-none mb-6 md:mb-0 self-center p-6 md:p-8 bg-gradient-to-br from-neutral-800 via-neutral-700 to-neutral-900 md:min-w-[240px]`}>
                    <div css={tw`rounded-2xl bg-white/10 p-4 shadow-inner backdrop-blur-sm`}>
                        <img src={'/assets/svgs/pterodactyl.svg'} css={tw`block w-40 md:w-48 mx-auto`} />
                    </div>
                </div>
                <div css={tw`flex-1 p-6 md:p-8`}>{props.children}</div>
            </div>
        </Form>
        <p css={tw`text-center text-neutral-500 text-xs mt-5`}>
            &copy; 2024 - {new Date().getFullYear()}&nbsp;
            <a
                rel={'noopener nofollow noreferrer'}
                href={'#'}
                target={'_blank'}
                css={tw`no-underline text-neutral-500 hover:text-neutral-300`}
            >
                VantaHost
            </a>
            &nbsp;| Powered by Pterodactyl
        </p>
    </Container>
));
