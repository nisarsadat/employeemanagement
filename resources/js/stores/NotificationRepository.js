// src/stores/NotificationRepository.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useNotificationRepository = defineStore('NotificationRepository', {
  state: () => ({
    loading: false,
    notifications: [],
    unreadCount: 0,
    page: 1,
    lastPage: 1,
    perPage: 10, // max 10 per page
    pollingId: null, // 👈 for auto refresh
  }),

  actions: {
    // optional: backend support, but FALLS BACK to local calculation
    async fetchUnreadCount() {
      try {
        const { data } = await axios.get('/notifications/unread-count');

        if (typeof data?.unread_count === 'number') {
          this.unreadCount = data.unread_count;
        } else {
          // fallback: compute from current notifications
          this.unreadCount = this.notifications.filter((n) => !n.is_read).length;
        }
      } catch (e) {
        console.error('fetchUnreadCount error', e);
        // fallback when backend not ready or error
        this.unreadCount = this.notifications.filter((n) => !n.is_read).length;
      }
    },

    async fetchNotifications(page = 1) {
      this.loading = true;
      try {
        const { data } = await axios.get('/notifications', {
          params: {
            page,
            perPage: this.perPage,
          },
        });

        // Laravel paginated resource: { data: [...], meta: {...} }
        this.notifications = data.data || [];
        const meta = data.meta || {};
        this.page = meta.current_page || 1;
        this.lastPage = meta.last_page || 1;

        // 🔥 Always recalc unreadCount from the list
        this.unreadCount = this.notifications.filter((n) => !n.is_read).length;
      } catch (e) {
        console.error('fetchNotifications error', e);
      } finally {
        this.loading = false;
      }
    },

    async markAsRead(id) {
      try {
        await axios.post(`/notifications/${id}/mark-as-read`);

        const item = this.notifications.find((n) => n.id === id);
        if (item && !item.is_read) {
          item.is_read = true;

          // 🔥 keep counter in sync locally
          if (this.unreadCount > 0) {
            this.unreadCount -= 1;
          }
        }
      } catch (e) {
        console.error('markAsRead error', e);
      }
    },

    async markAllAsRead() {
      try {
        await axios.post('/notifications/mark-all-as-read');

        this.notifications = this.notifications.map((n) => ({
          ...n,
          is_read: true,
        }));

        // 🔥 all read now
        this.unreadCount = 0;
      } catch (e) {
        console.error('markAllAsRead error', e);
      }
    },

    async clearAllNotifications() {
      try {
        await axios.delete('/notifications/clear');

        this.notifications = [];
        this.unreadCount = 0;
        this.page = 1;
        this.lastPage = 1;
      } catch (e) {
        console.error('clearAllNotifications error', e);
      }
    },

    // 👇 NEW: auto refresh unread count (and initially notifications)
    startPolling() {
      if (this.pollingId) return;

      // initial fetch
      this.fetchUnreadCount();

      this.pollingId = setInterval(() => {
        this.fetchUnreadCount();
      }, 10000); // every 10 seconds
    },

    stopPolling() {
      if (this.pollingId) {
        clearInterval(this.pollingId);
        this.pollingId = null;
      }
    },
  },
});
