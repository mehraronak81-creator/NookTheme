import React, { useEffect, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCheckDouble, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import { Button } from '@/components/elements/button/index';
import GreyRowBox from '@/components/elements/GreyRowBox';
import { useFlashKey } from '@/plugins/useFlash';
import {
    deleteNotification,
    getNotifications,
    markAllNotificationsRead,
    markNotificationRead,
    Notification,
} from '@/api/account/notifications';

const levelColor = (level: Notification['level']): string => {
    switch (level) {
        case 'error':
            return '#ef4444';
        case 'success':
            return '#22c55e';
        case 'warning':
            return '#eab308';
        default:
            return 'var(--color-accent)';
    }
};

export default () => {
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('notifications');
    const [loading, setLoading] = useState(true);
    const [items, setItems] = useState<Notification[]>([]);

    const load = () => {
        clearFlashes();
        getNotifications()
            .then((res) => setItems(res.items))
            .catch((error) => clearAndAddHttpError(error))
            .then(() => setLoading(false));
    };

    useEffect(() => {
        load();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const onRead = (id: string) => {
        markNotificationRead(id)
            .then(() => setItems((prev) => prev.map((n) => (n.id === id ? { ...n, readAt: new Date() } : n))))
            .catch((error) => clearAndAddHttpError(error));
    };

    const onReadAll = () => {
        markAllNotificationsRead()
            .then(() => setItems((prev) => prev.map((n) => ({ ...n, readAt: n.readAt ?? new Date() }))))
            .catch((error) => clearAndAddHttpError(error));
    };

    const onDelete = (id: string) => {
        deleteNotification(id)
            .then(() => setItems((prev) => prev.filter((n) => n.id !== id)))
            .catch((error) => clearAndAddHttpError(error));
    };

    return (
        <PageContentBlock title={'Notifications'} showFlashKey={'notifications'}>
            {loading ? (
                <Spinner centered size={'large'} />
            ) : (
                <>
                    {items.some((n) => !n.readAt) && (
                        <div css={tw`flex justify-end mb-4`}>
                            <Button.Text onClick={onReadAll}>
                                <FontAwesomeIcon icon={faCheckDouble} css={tw`mr-2`} />
                                Mark all as read
                            </Button.Text>
                        </div>
                    )}
                    {items.length === 0 ? (
                        <p css={tw`text-center text-sm text-neutral-400`}>You have no notifications.</p>
                    ) : (
                        items.map((n, index) => (
                            <GreyRowBox key={n.id} css={index > 0 ? tw`mt-2` : undefined}>
                                <div
                                    css={tw`w-1 h-10 rounded mr-4 flex-shrink-0`}
                                    style={{ background: levelColor(n.level) }}
                                />
                                <div css={tw`flex-1`}>
                                    <div css={tw`flex items-center`}>
                                        {!n.readAt && (
                                            <span
                                                css={tw`w-2 h-2 rounded-full mr-2`}
                                                style={{ background: 'var(--color-accent)' }}
                                            />
                                        )}
                                        {n.title && <p css={tw`font-medium`}>{n.title}</p>}
                                    </div>
                                    <p css={tw`text-sm text-neutral-300`}>{n.message}</p>
                                    <p css={tw`text-xs text-neutral-500 mt-1`}>{n.createdAt.toLocaleString()}</p>
                                </div>
                                <div css={tw`flex items-center ml-3`}>
                                    {!n.readAt && (
                                        <button
                                            css={tw`text-neutral-400 hover:text-neutral-200 mr-3`}
                                            title={'Mark as read'}
                                            onClick={() => onRead(n.id)}
                                        >
                                            <FontAwesomeIcon icon={faCheckDouble} />
                                        </button>
                                    )}
                                    <button
                                        css={tw`text-neutral-400 hover:text-red-400`}
                                        title={'Delete'}
                                        onClick={() => onDelete(n.id)}
                                    >
                                        <FontAwesomeIcon icon={faTrashAlt} />
                                    </button>
                                </div>
                            </GreyRowBox>
                        ))
                    )}
                </>
            )}
        </PageContentBlock>
    );
};
