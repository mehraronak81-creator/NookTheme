import * as React from 'react';
import ContentBox from '@/components/elements/ContentBox';
import UpdatePasswordForm from '@/components/dashboard/forms/UpdatePasswordForm';
import UpdateEmailAddressForm from '@/components/dashboard/forms/UpdateEmailAddressForm';
import ConfigureTwoFactorForm from '@/components/dashboard/forms/ConfigureTwoFactorForm';
import PageContentBlock from '@/components/elements/PageContentBlock';
import tw from 'twin.macro';
import { breakpoint } from '@/theme';
import styled from 'styled-components/macro';
import MessageBox from '@/components/MessageBox';
import { useLocation } from 'react-router-dom';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCalendarAlt, faEnvelope, faShieldAlt, faUserCircle } from '@fortawesome/free-solid-svg-icons';

const Container = styled.div`
    ${tw`flex flex-wrap`};

    & > div {
        ${tw`w-full`};

        ${breakpoint('sm')`
      width: calc(50% - 1rem);
    `}

        ${breakpoint('md')`
      ${tw`w-auto flex-1`};
    `}
    }
`;

const ProfileHeader = styled.div`
    ${tw`rounded-xl p-6 flex flex-col sm:flex-row items-center sm:items-start gap-5 relative overflow-hidden`};
    background: var(--card-bg, rgba(26, 26, 40, 0.55));
    border: 1px solid var(--color-border, #2a2a3a);
    backdrop-filter: blur(var(--glass-blur, 16px));
    -webkit-backdrop-filter: blur(var(--glass-blur, 16px));
    box-shadow: var(--shadow-card, 0 2px 8px rgba(0, 0, 0, 0.35));

    &::before {
        content: '';
        ${tw`absolute top-0 left-0 w-full h-1`};
        background: var(--gradient-accent, linear-gradient(90deg, #7382ff, #a78bfa));
    }
`;

const Avatar = styled.div`
    ${tw`w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-bold flex-shrink-0 select-none`};
    background: var(--gradient-accent, linear-gradient(135deg, #7382ff 0%, #a78bfa 100%));
    color: #fff;
    box-shadow: 0 4px 16px var(--color-accent-glow, rgba(115, 130, 255, 0.35)),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
`;

const RoleBadge = styled.span<{ $admin?: boolean }>`
    ${tw`text-xs font-semibold px-2.5 py-1 rounded-full uppercase tracking-wide inline-flex items-center gap-1`};
    ${(props) =>
        props.$admin
            ? `background: var(--gradient-accent, linear-gradient(135deg, #7382ff, #a78bfa)); color: #fff;
               box-shadow: 0 0 12px var(--color-accent-glow, rgba(115,130,255,0.3));`
            : `background: var(--color-surface, #1e1e2a); color: var(--text-secondary, #b0b0c0);
               border: 1px solid var(--color-border, #2a2a3a);`};
`;

const MetaItem = styled.div`
    ${tw`flex items-center gap-2 text-sm`};
    color: var(--text-secondary, #b0b0c0);

    & svg {
        color: var(--color-accent, #7382ff);
    }
`;

export default () => {
    const { state } = useLocation<undefined | { twoFactorRedirect?: boolean }>();
    const user = useStoreState((state: ApplicationStore) => state.user.data!);

    const initials = (user.username || '?')
        .split(/[\s_.-]+/)
        .map((part) => part.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase();

    return (
        <PageContentBlock title={'Account Overview'}>
            {state?.twoFactorRedirect && (
                <MessageBox title={'2-Factor Required'} type={'error'}>
                    Your account must have two-factor authentication enabled in order to continue.
                </MessageBox>
            )}

            <ProfileHeader css={state?.twoFactorRedirect ? tw`mt-4` : tw`mt-10`}>
                <Avatar>{initials}</Avatar>
                <div css={tw`flex-1 text-center sm:text-left min-w-0`}>
                    <div css={tw`flex flex-col sm:flex-row items-center sm:items-baseline gap-2 sm:gap-3`}>
                        <h1 css={tw`text-2xl font-semibold break-all`} style={{ color: 'var(--text-main, #e8e8f0)' }}>
                            {user.username}
                        </h1>
                        <RoleBadge $admin={user.rootAdmin}>
                            <FontAwesomeIcon icon={user.rootAdmin ? faShieldAlt : faUserCircle} size={'xs'} />
                            {user.rootAdmin ? 'Administrator' : 'Member'}
                        </RoleBadge>
                    </div>
                    <div css={tw`flex flex-col sm:flex-row gap-2 sm:gap-6 mt-3 items-center sm:items-start`}>
                        <MetaItem>
                            <FontAwesomeIcon icon={faEnvelope} size={'sm'} />
                            <span css={tw`break-all`}>{user.email}</span>
                        </MetaItem>
                        {user.createdAt && (
                            <MetaItem>
                                <FontAwesomeIcon icon={faCalendarAlt} size={'sm'} />
                                <span>
                                    Member since{' '}
                                    {new Date(user.createdAt).toLocaleDateString(undefined, {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    })}
                                </span>
                            </MetaItem>
                        )}
                    </div>
                </div>
            </ProfileHeader>

            <Container css={tw`lg:grid lg:grid-cols-3 mb-10 mt-8`}>
                <ContentBox title={'Update Password'} showFlashes={'account:password'}>
                    <UpdatePasswordForm />
                </ContentBox>
                <ContentBox css={tw`mt-8 sm:mt-0 sm:ml-8`} title={'Update Email Address'} showFlashes={'account:email'}>
                    <UpdateEmailAddressForm />
                </ContentBox>
                <ContentBox css={tw`md:ml-8 mt-8 md:mt-0`} title={'Two-Step Verification'}>
                    <ConfigureTwoFactorForm />
                </ContentBox>
            </Container>
        </PageContentBlock>
    );
};
