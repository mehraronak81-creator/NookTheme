import React, { useEffect, useState } from 'react';
import { Server } from '@/api/server/getServer';
import getServers from '@/api/getServers';
import ServerRow from '@/components/dashboard/ServerRow';
import Spinner from '@/components/elements/Spinner';
import PageContentBlock from '@/components/elements/PageContentBlock';
import useFlash from '@/plugins/useFlash';
import { useStoreState } from 'easy-peasy';
import { usePersistedState } from '@/plugins/usePersistedState';
import Switch from '@/components/elements/Switch';
import tw from 'twin.macro';
import useSWR from 'swr';
import { PaginatedResult } from '@/api/http';
import Pagination from '@/components/elements/Pagination';
import { useLocation } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faSearch, faTimes } from '@fortawesome/free-solid-svg-icons';
import AnnouncementBanner from '@/components/elements/AnnouncementBanner';

export default () => {
    const { search } = useLocation();
    const defaultPage = Number(new URLSearchParams(search).get('page') || '1');

    const [page, setPage] = useState(!isNaN(defaultPage) && defaultPage > 0 ? defaultPage : 1);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = useStoreState((state) => state.user.data!.uuid);
    const username = useStoreState((state) => state.user.data!.username);
    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const [showOnlyAdmin, setShowOnlyAdmin] = usePersistedState(`${uuid}:show_all_servers`, false);
    const [favorites, setFavorites] = usePersistedState<string[]>(`${uuid}:favorites`, []);
    const [welcomeDismissed, setWelcomeDismissed] = usePersistedState(`${uuid}:welcome_dismissed`, false);
    const [filter, setFilter] = useState('');

    const favoriteList = favorites || [];
    const toggleFavorite = (serverUuid: string) =>
        setFavorites((prev) => {
            const current = prev || [];
            return current.includes(serverUuid) ? current.filter((u) => u !== serverUuid) : [...current, serverUuid];
        });

    const { data: servers, error } = useSWR<PaginatedResult<Server>>(
        ['/api/client/servers', showOnlyAdmin && rootAdmin, page],
        () => getServers({ page, type: showOnlyAdmin && rootAdmin ? 'admin' : undefined })
    );

    useEffect(() => {
        setPage(1);
    }, [showOnlyAdmin]);

    useEffect(() => {
        if (!servers) return;
        if (servers.pagination.currentPage > 1 && !servers.items.length) {
            setPage(1);
        }
    }, [servers?.pagination.currentPage]);

    useEffect(() => {
        // Don't use react-router to handle changing this part of the URL, otherwise it
        // triggers a needless re-render. We just want to track this in the URL incase the
        // user refreshes the page.
        window.history.replaceState(null, document.title, `/${page <= 1 ? '' : `?page=${page}`}`);
    }, [page]);

    useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'dashboard', error });
        if (!error) clearFlashes('dashboard');
    }, [error]);

    const matchesFilter = (server: Server): boolean => {
        const q = filter.trim().toLowerCase();
        if (!q) return true;
        return (
            server.name.toLowerCase().includes(q) ||
            (server.description || '').toLowerCase().includes(q) ||
            server.uuid.toLowerCase().includes(q)
        );
    };

    return (
        <PageContentBlock className='content-dashboard' title={'Dashboard'} showFlashKey={'dashboard'}>
            <AnnouncementBanner />
            {!welcomeDismissed && (
                <div
                    css={tw`mb-4 rounded p-4 flex items-center justify-between`}
                    style={{ background: 'var(--gradient-accent)', color: '#fff' }}
                >
                    <div>
                        <p css={tw`text-lg font-medium`}>Welcome back, {username}!</p>
                        <p css={tw`text-sm opacity-90`}>
                            You have {servers?.pagination.total ?? 0} server
                            {(servers?.pagination.total ?? 0) === 1 ? '' : 's'} on your account.
                        </p>
                    </div>
                    <button
                        css={tw`text-white text-sm opacity-80 hover:opacity-100`}
                        onClick={() => setWelcomeDismissed(true)}
                    >
                        <FontAwesomeIcon icon={faTimes} />
                    </button>
                </div>
            )}

            <div css={tw`grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4`}>
                <StatCard label={'Total Servers'} value={servers?.pagination.total ?? '—'} />
                <StatCard label={'Favorites'} value={favoriteList.length} />
                <StatCard label={'On This Page'} value={servers?.items.length ?? '—'} />
            </div>

            <div css={tw`mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2`}>
                <div css={tw`relative flex-1 max-w-md`}>
                    <FontAwesomeIcon
                        icon={faSearch}
                        css={tw`absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500 text-sm`}
                    />
                    <input
                        type={'text'}
                        value={filter}
                        onChange={(e) => setFilter(e.target.value)}
                        placeholder={'Filter servers on this page...'}
                        css={tw`w-full pl-9 pr-3 py-2 rounded text-sm`}
                        style={{
                            background: 'var(--color-surface)',
                            border: '1px solid var(--color-border)',
                            color: 'var(--text-main)',
                        }}
                    />
                </div>
                {rootAdmin && (
                    <div css={tw`flex justify-end items-center`}>
                        <p css={tw`uppercase text-xs text-neutral-400 mr-2`}>
                            {showOnlyAdmin ? "Showing others' servers" : 'Showing your servers'}
                        </p>
                        <Switch
                            name={'show_all_servers'}
                            defaultChecked={showOnlyAdmin}
                            onChange={() => setShowOnlyAdmin((s) => !s)}
                        />
                    </div>
                )}
            </div>

            {!servers ? (
                <Spinner centered size={'large'} />
            ) : (
                <Pagination data={servers} onPageSelect={setPage}>
                    {({ items }) => {
                        const visible = items.filter(matchesFilter);
                        const favs = visible.filter((s) => favoriteList.includes(s.uuid));
                        const others = visible.filter((s) => !favoriteList.includes(s.uuid));

                        if (visible.length === 0) {
                            return (
                                <p css={tw`text-center text-sm text-neutral-400`}>
                                    {filter.trim()
                                        ? 'No servers on this page match your filter.'
                                        : showOnlyAdmin
                                        ? 'There are no other servers to display.'
                                        : 'There are no servers associated with your account.'}
                                </p>
                            );
                        }

                        return (
                            <>
                                {favs.length > 0 && (
                                    <div css={tw`mb-4`}>
                                        <p css={tw`text-xs uppercase tracking-wide text-neutral-400 mb-2`}>Favorites</p>
                                        {favs.map((server, index) => (
                                            <ServerRow
                                                key={server.uuid}
                                                server={server}
                                                isFavorite
                                                onToggleFavorite={toggleFavorite}
                                                css={index > 0 ? tw`mt-2` : undefined}
                                            />
                                        ))}
                                    </div>
                                )}
                                {others.map((server, index) => (
                                    <ServerRow
                                        key={server.uuid}
                                        server={server}
                                        isFavorite={false}
                                        onToggleFavorite={toggleFavorite}
                                        css={index > 0 ? tw`mt-2` : undefined}
                                    />
                                ))}
                            </>
                        );
                    }}
                </Pagination>
            )}
        </PageContentBlock>
    );
};

const StatCard = ({ label, value }: { label: string; value: React.ReactNode }) => (
    <div css={tw`rounded p-3`} style={{ background: 'var(--card-bg)', border: '1px solid var(--color-border)' }}>
        <p css={tw`text-2xl font-medium`} style={{ color: 'var(--text-main)' }}>
            {value}
        </p>
        <p css={tw`text-xs uppercase tracking-wide text-neutral-400`}>{label}</p>
    </div>
);
