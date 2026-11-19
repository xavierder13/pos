import axios from '../api/axiosInstance';
import { PASSIVE_HEADERS } from '../utils/session';

// Notifications — backend NotificationController (the signed-in user's own; no permission).
// Notification = { id, title, body, link (SPA path or null), type (info|success|warning|error), read_at, created_at }
// - getAll({ unread, page, per_page }) → { data: [Notification], meta: { current_page, per_page, total }, unread_count }
// - unreadCount() → { unread_count } — passive: the bell's polling doesn't keep the session alive
// - markRead(id) → { data, unread_count } · markAllRead() → { unread_count: 0 } · remove(id) → { message, unread_count }
const notificationApi = {
  getAll:      (params) => axios.get('/notifications', { params }),
  unreadCount: ()       => axios.get('/notifications/unread-count', { headers: PASSIVE_HEADERS }),
  markRead:    (id)     => axios.post(`/notifications/${id}/read`),
  markAllRead: ()       => axios.post('/notifications/read-all'),
  remove:      (id)     => axios.delete(`/notifications/${id}`),
};

export default notificationApi;
