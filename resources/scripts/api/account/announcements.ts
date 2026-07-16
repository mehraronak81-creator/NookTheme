import http from '@/api/http';

export interface Announcement {
    id: number;
    title: string;
    body: string;
    level: 'info' | 'success' | 'warning' | 'error';
    priority: number;
    createdAt: Date;
}

export const rawDataToAnnouncement = (data: any): Announcement => ({
    id: data.id,
    title: data.title,
    body: data.body,
    level: data.level || 'info',
    priority: data.priority ?? 0,
    createdAt: new Date(data.created_at),
});

export const getAnnouncements = (): Promise<Announcement[]> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/announcements')
            .then(({ data }) => resolve((data.data || []).map((d: any) => rawDataToAnnouncement(d.attributes))))
            .catch(reject);
    });
};
