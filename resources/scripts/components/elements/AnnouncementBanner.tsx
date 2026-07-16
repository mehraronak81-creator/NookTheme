import React, { useEffect, useState } from 'react';
import { useStoreState } from 'easy-peasy';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faTimes } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import { ApplicationStore } from '@/state';
import { usePersistedState } from '@/plugins/usePersistedState';
import { Announcement, getAnnouncements } from '@/api/account/announcements';

const levelStyle = (level: Announcement['level']): { background: string; border: string } => {
    switch (level) {
        case 'error':
            return { background: '#7f1d1d', border: '#991b1b' };
        case 'success':
            return { background: '#14532d', border: '#166534' };
        case 'warning':
            return { background: '#78350f', border: '#92400e' };
        default:
            return { background: 'var(--color-surface)', border: 'var(--color-border)' };
    }
};

export default () => {
    const uuid = useStoreState((state: ApplicationStore) => state.user.data?.uuid);
    const [announcements, setAnnouncements] = useState<Announcement[]>([]);
    const [dismissed, setDismissed] = usePersistedState<number[]>(`${uuid}:dismissed_announcements`, []);

    useEffect(() => {
        getAnnouncements()
            .then((data) => setAnnouncements(data))
            .catch(() => setAnnouncements([]));
    }, []);

    const dismissedList = dismissed || [];
    const visible = announcements.filter((a) => !dismissedList.includes(a.id));

    if (visible.length === 0) {
        return null;
    }

    return (
        <div css={tw`mb-4`}>
            {visible.map((announcement) => {
                const style = levelStyle(announcement.level);
                return (
                    <div
                        key={announcement.id}
                        css={tw`rounded p-4 mb-2 flex items-start border`}
                        style={{
                            background: style.background,
                            borderColor: style.border,
                            color: 'var(--text-main)',
                        }}
                        role={'alert'}
                    >
                        <div css={tw`flex-1`}>
                            <p css={tw`font-semibold`}>{announcement.title}</p>
                            <p css={tw`text-sm opacity-90 whitespace-pre-line`}>{announcement.body}</p>
                        </div>
                        <button
                            type={'button'}
                            css={tw`ml-3 opacity-70 hover:opacity-100`}
                            aria-label={'Dismiss announcement'}
                            onClick={() => setDismissed([...dismissedList, announcement.id])}
                        >
                            <FontAwesomeIcon icon={faTimes} />
                        </button>
                    </div>
                );
            })}
        </div>
    );
};
