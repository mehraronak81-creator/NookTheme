import * as React from 'react';
import { useState, useEffect, useCallback } from 'react';
import { Link, NavLink, useLocation } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faBars, faBell, faCogs, faLayerGroup, faSignOutAlt, faMoon, faSun } from '@fortawesome/free-solid-svg-icons';
import { getNotifications } from '@/api/account/notifications';
import { Actions, useStoreState, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import SearchContainer from '@/components/dashboard/search/SearchContainer';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import http from '@/api/http';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import Avatar from '@/components/Avatar';

const RightNavigation = styled.div`
    & > a,
    & > button,
    & > .navigation-link {
        ${tw`flex items-center h-full no-underline px-6 cursor-pointer transition-all duration-150`};
        color: var(--text-secondary);

        &:active,
        &:hover {
            color: var(--text-main);
            background: var(--color-surface-hover);
        }

        &:active,
        &:hover,
        &.active {
            box-shadow: inset 0 -2px var(--color-accent);
        }
    }
`;

const onTriggerNavButton = () => {
    const sidebar = document.getElementById('sidebar');

    if (sidebar) {
        sidebar.classList.toggle('active-nav');
    }
};

export default () => {
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    const rootAdmin = useStoreState((state: ApplicationStore) => state.user.data!.rootAdmin);
    const themeMode = useStoreState((state: ApplicationStore) => state.appearance.data.theme);
    const setAppearance = useStoreActions((actions: Actions<ApplicationStore>) => actions.appearance.setAppearance);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const location = useLocation();
    const [showSidebar, setShowSidebar] = useState(false);
    const [unreadCount, setUnreadCount] = useState(0);

    // Fetch the unread notification count once on mount to drive the bell badge.
    useEffect(() => {
        getNotifications()
            .then((res) => setUnreadCount(res.unreadCount))
            .catch(() => setUnreadCount(0));
    }, [location.pathname === '/account/notifications']);

    // Resolve the effective theme (the appearance controller keeps <html> in
    // sync; here we only need it to pick the toggle icon/label).
    const resolvedTheme =
        themeMode === 'auto'
            ? typeof window !== 'undefined' &&
              window.matchMedia &&
              window.matchMedia('(prefers-color-scheme: light)').matches
                ? 'light'
                : 'dark'
            : themeMode;

    const toggleTheme = useCallback(() => {
        setAppearance({ theme: resolvedTheme === 'dark' ? 'light' : 'dark' });
    }, [resolvedTheme, setAppearance]);

    useEffect(() => {
        if (location.pathname.startsWith('/server') || location.pathname.startsWith('/account')) {
            setShowSidebar(true);
            return;
        }
        setShowSidebar(false);
    }, [location.pathname]);

    const onTriggerLogout = () => {
        setIsLoggingOut(true);
        http.post('/auth/logout').finally(() => {
            // @ts-expect-error this is valid
            window.location = '/';
        });
    };

    return (
        <div className={'shadow-md overflow-x-auto topbar'}>
            <SpinnerOverlay visible={isLoggingOut} />
            <div className={'mx-auto w-full flex items-center h-[3.5rem] max-w-[1200px]'}>
                {showSidebar && (
                    <FontAwesomeIcon
                        icon={faBars}
                        className='navbar-button'
                        onClick={onTriggerNavButton}
                    ></FontAwesomeIcon>
                )}

                <div id={'logo'} className={'flex-1'}>
                    <Link
                        to={'/'}
                        className={
                            'text-2xl font-header font-medium px-4 no-underline transition-colors duration-150 flex items-center gap-2'
                        }
                        style={{ color: 'var(--text-main)' }}
                    >
                        <span className='vanta-brand-icon'>V</span>
                        {name}
                    </Link>
                </div>

                <RightNavigation className={'flex h-full items-center justify-center'}>
                    <SearchContainer />
                    <Tooltip placement={'bottom'} content={'Dashboard'}>
                        <NavLink to={'/'} exact>
                            <FontAwesomeIcon icon={faLayerGroup} />
                        </NavLink>
                    </Tooltip>
                    {rootAdmin && (
                        <Tooltip placement={'bottom'} content={'Admin'}>
                            <a href={'/admin'} rel={'noreferrer'}>
                                <FontAwesomeIcon icon={faCogs} />
                            </a>
                        </Tooltip>
                    )}
                    <Tooltip placement={'bottom'} content={'Account Settings'}>
                        <NavLink to={'/account'}>
                            <span className={'flex items-center w-5 h-5'}>
                                <Avatar.User />
                            </span>
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={'Notifications'}>
                        <NavLink to={'/account/notifications'} css={tw`relative`}>
                            <FontAwesomeIcon icon={faBell} />
                            {unreadCount > 0 && (
                                <span
                                    css={tw`absolute -top-1 -right-1 text-white text-xs rounded-full flex items-center justify-center`}
                                    style={{
                                        background: '#ef4444',
                                        minWidth: '16px',
                                        height: '16px',
                                        padding: '0 4px',
                                        lineHeight: 1,
                                    }}
                                >
                                    {unreadCount > 9 ? '9+' : unreadCount}
                                </span>
                            )}
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={resolvedTheme === 'dark' ? 'Light Mode' : 'Dark Mode'}>
                        <button onClick={toggleTheme} className='theme-toggle'>
                            <FontAwesomeIcon icon={resolvedTheme === 'dark' ? faSun : faMoon} />
                        </button>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={'Sign Out'}>
                        <button onClick={onTriggerLogout}>
                            <FontAwesomeIcon icon={faSignOutAlt} />
                        </button>
                    </Tooltip>
                </RightNavigation>
            </div>
        </div>
    );
};
