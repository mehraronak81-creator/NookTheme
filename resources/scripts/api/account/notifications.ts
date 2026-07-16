import http from '@/api/http';

export interface Notification {
    id: string;
    title: string | null;
    message: string;
    level: 'info' | 'success' | 'warning' | 'error';
    actionUrl: string | null;
    readAt: Date | null;
    createdAt: Date;
}

export interface NotificationResponse {
    items: Notification[];
    unreadCount: number;
}

export const rawDataToNotification = (data: any): Notification => ({
    id: data.id,
    title: data.title,
    message: data.message,
    level: data.level || 'info',
    actionUrl: data.action_url ?? null,
    readAt: data.read_at ? new Date(data.read_at) : null,
    createdAt: new Date(data.created_at),
});

export const getNotifications = (): Promise<NotificationResponse> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/account/notifications')
            .then(({ data }) =>
                resolve({
                    items: (data.data || []).map((d: any) => rawDataToNotification(d.attributes)),
                    unreadCount: data.meta?.unread_count ?? 0,
                })
            )
            .catch(reject);
    });
};

export const markNotificationRead = (id: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/account/notifications/${id}/read`)
            .then(() => resolve())
            .catch(reject);
    });
};

export const markAllNotificationsRead = (): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/account/notifications/read')
            .then(() => resolve())
            .catch(reject);
    });
};

export const deleteNotification = (id: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.delete(`/api/client/account/notifications/${id}`)
            .then(() => resolve())
            .catch(reject);
    });
};
