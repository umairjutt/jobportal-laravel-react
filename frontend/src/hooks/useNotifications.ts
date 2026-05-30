import { useEffect } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '../lib/api';
import { echo } from '../lib/echo';
import { useAuth } from '../store/auth';

export interface AppNotification {
  id: string;
  read_at: string | null;
  data: {
    type: string;
    message: string;
    [key: string]: unknown;
  };
}

interface NotificationsResponse {
  unread_count: number;
  notifications: AppNotification[];
}

/**
 * Loads the current user's notifications and subscribes to the private
 * Laravel notification channel for live pushes (new application / message /
 * status change). The unread badge updates optimistically on mark-as-read.
 */
export function useNotifications() {
  const queryClient = useQueryClient();
  const user = useAuth((s) => s.user);
  const token = useAuth((s) => s.token);

  const query = useQuery<NotificationsResponse>({
    queryKey: ['notifications'],
    queryFn: async () => (await api.get('/api/notifications')).data,
    enabled: !!token,
  });

  useEffect(() => {
    if (!user) return;
    const channel = echo.private(`App.Models.User.${user.id}`);
    channel.notification(() => {
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
    });
    return () => {
      echo.leave(`App.Models.User.${user.id}`);
    };
  }, [user, queryClient]);

  const markAllRead = useMutation({
    mutationFn: async () => api.post('/api/notifications/read-all'),
    onMutate: async () => {
      await queryClient.cancelQueries({ queryKey: ['notifications'] });
      const previous = queryClient.getQueryData<NotificationsResponse>(['notifications']);
      queryClient.setQueryData<NotificationsResponse>(['notifications'], (old) =>
        old
          ? {
              unread_count: 0,
              notifications: old.notifications.map((n) => ({ ...n, read_at: new Date().toISOString() })),
            }
          : old,
      );
      return { previous };
    },
    onError: (_e, _v, ctx) => {
      if (ctx?.previous) queryClient.setQueryData(['notifications'], ctx.previous);
    },
    onSettled: () => queryClient.invalidateQueries({ queryKey: ['notifications'] }),
  });

  return {
    notifications: query.data?.notifications ?? [],
    unreadCount: query.data?.unread_count ?? 0,
    isLoading: query.isLoading,
    markAllRead: () => markAllRead.mutate(),
  };
}
