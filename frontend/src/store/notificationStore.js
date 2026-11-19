// src/store/notificationStore.js
//
// The bell's state: the unread count (polled) and the latest notifications
// (loaded when the bell opens). The Notifications page has its own paged list
// and keeps the count in sync through setUnread.

import { create } from 'zustand';
import notificationApi from '../services/notificationApi';

const useNotificationStore = create((set) => ({

  // ── State ──────────────────────────────────────────
  unread:    0,
  latest:    [],
  isLoading: false,

  // ── Actions ────────────────────────────────────────
  setUnread: (unread) => set({ unread }),

  fetchUnread: async () => {
    try {
      const { data } = await notificationApi.unreadCount();
      set({ unread: data.unread_count });
    } catch { /* a 401 is handled by the interceptor; anything else: try again next poll */ }
  },

  fetchLatest: async () => {
    set({ isLoading: true });
    try {
      const { data } = await notificationApi.getAll({ per_page: 8 });
      set({ latest: data.data, unread: data.unread_count });
    } catch { /* keep the previous list */ } finally {
      set({ isLoading: false });
    }
  },

  markRead: async (id) => {
    const { data } = await notificationApi.markRead(id);
    set((s) => ({ unread: data.unread_count, latest: s.latest.map((n) => (n.id === id ? data.data : n)) }));
  },

  markAllRead: async () => {
    await notificationApi.markAllRead();
    set((s) => ({ unread: 0, latest: s.latest.map((n) => ({ ...n, read_at: n.read_at ?? new Date().toISOString() })) }));
  },

}));

export default useNotificationStore;
