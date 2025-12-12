import { defineStore } from 'pinia';
import axios from 'axios';

export const useChatRepository = defineStore('ChatRepository', {
  state: () => ({
    bootLoading: false,
    silentLoading: false,
    messagesLoading: false,
    sending: false,

    currentUser: null,
    users: [],
    conversations: [],

    activeUser: null,
    activeConversationId: null,
    messages: [],

    unreadCount: 0,

    // Echo state
    userChannelSubscribed: false,
    activeConversationChannelName: null,

    // timers
    unreadPollId: null,
    presencePingId: null,

    // init flags
    initialized: false,
    usersLoadedOnce: false,
    conversationsLoadedOnce: false,

    // ✅ used by component for notification + sound trigger (ONLY when receiving)
    lastIncomingMessage: null,

    // ✅ optional: track sends (NO sound)
    lastSentMessageId: null,
  }),

  getters: {
    hasActiveConversation(state) {
      return !!state.activeConversationId && !!state.activeUser;
    },
  },

  actions: {
    async initBadge() {
      if (!this.currentUser) await this.fetchCurrentUser();

      this.startUnreadPolling();
      this.startPresencePing();
      this.subscribeToUserChannel();
    },

    async bootstrap() {
      if (this.initialized) return;

      this.bootLoading = true;
      try {
        if (!this.currentUser) await this.fetchCurrentUser();

        await this.fetchUsersOnce();
        await this.fetchConversationsOnce();

        this.initialized = true;
      } finally {
        this.bootLoading = false;
      }
    },

    async pingOnline() {
      try {
        await axios.post('/chat/ping');
      } catch {}
    },

    startPresencePing() {
      if (this.presencePingId) return;
      this.pingOnline();
      this.presencePingId = setInterval(() => this.pingOnline(), 20000);
    },

    async fetchCurrentUser() {
      const { data } = await axios.get('/user');
      this.currentUser = data;
    },

    async fetchUsersOnce() {
      if (this.usersLoadedOnce) return;
      this.silentLoading = true;
      try {
        const { data } = await axios.get('/chat/users');
        this.users = Array.isArray(data) ? data : [];
        this.usersLoadedOnce = true;
      } finally {
        this.silentLoading = false;
      }
    },

    async fetchConversationsOnce() {
      if (this.conversationsLoadedOnce) return;
      this.silentLoading = true;
      try {
        const { data } = await axios.get('/chat/conversations');
        this.conversations = Array.isArray(data) ? data : [];
        this.recalculateUnread();
        this.conversationsLoadedOnce = true;
      } finally {
        this.silentLoading = false;
      }
    },

    async refreshLists() {
      this.silentLoading = true;
      try {
        const [uRes, cRes] = await Promise.all([
          axios.get('/chat/users'),
          axios.get('/chat/conversations'),
        ]);

        this.users = Array.isArray(uRes.data) ? uRes.data : [];
        this.conversations = Array.isArray(cRes.data) ? cRes.data : [];
        this.recalculateUnread();
      } finally {
        this.silentLoading = false;
      }
    },

    async fetchUnreadCounts() {
      try {
        const { data } = await axios.get('/chat/unread-counts');

        const map = new Map((data.items || []).map((i) => [i.conversation_id, i.unread_count]));

        this.conversations = this.conversations.map((c) =>
          map.has(c.id) ? { ...c, unread_count: map.get(c.id) } : c
        );

        this.unreadCount =
          typeof data.total_unread === 'number' ? data.total_unread : this.unreadCount;
      } catch {}
    },

    recalculateUnread() {
      this.unreadCount = this.conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
    },

    startUnreadPolling() {
      if (this.unreadPollId) return;
      this.fetchUnreadCounts();
      this.unreadPollId = setInterval(() => this.fetchUnreadCounts(), 5000);
    },

    async openConversationWith(user) {
      this.activeUser = user;
      this.messages = [];
      this.activeConversationId = null;

      this.messagesLoading = true;
      try {
        const { data } = await axios.get(`/chat/conversations/user/${user.id}`);

        this.activeConversationId = data?.conversation?.id || null;
        this.messages = data?.messages?.data || [];

        if (this.activeConversationId) {
          await this.markConversationAsReadById(this.activeConversationId);
          this.subscribeToConversationChannel(this.activeConversationId);
        }
      } finally {
        this.messagesLoading = false;
      }
    },

    closeChatSession() {
      this.stopConversationRealtime();
      this.activeUser = null;
      this.activeConversationId = null;
      this.messages = [];
    },

    async markConversationAsReadById(conversationId) {
      try {
        await axios.post(`/chat/conversations/${conversationId}/read`);
      } catch {}

      this.conversations = this.conversations.map((c) =>
        c.id === conversationId ? { ...c, unread_count: 0 } : c
      );
      this.recalculateUnread();
    },

    async markAllAsRead() {
      try {
        await axios.post('/chat/conversations/read-all');
      } catch {}

      this.conversations = this.conversations.map((c) => ({ ...c, unread_count: 0 }));
      this.unreadCount = 0;
    },

    async clearChat(conversationId) {
      try {
        await axios.post(`/chat/conversations/${conversationId}/clear`);
      } catch {}

      if (this.activeConversationId === conversationId) {
        this.messages = [];
      }

      this.conversations = this.conversations.map((c) =>
        c.id === conversationId ? { ...c, last_message: null, unread_count: 0 } : c
      );
      this.recalculateUnread();
    },

    async sendMessage(body) {
      this.sending = true;
      try {
        const { data } = await axios.post('/chat/messages', {
          receiver_id: this.activeUser.id,
          body,
        });

        this.lastSentMessageId = data?.id || data?.message?.id || null;
        return data;
      } finally {
        this.sending = false;
      }
    },

    subscribeToUserChannel() {
      if (this.userChannelSubscribed || !window.Echo || !this.currentUser?.id) return;

      window.Echo.private(`user.${this.currentUser.id}`)
        .listen('.MessageSeen', (e) => this.handleMessageSeen(e))
        .listen('.MessageSent', (e) => this.handleIncomingMessage(e));

      this.userChannelSubscribed = true;
    },

    stopConversationRealtime() {
      if (!window.Echo || !this.activeConversationChannelName) return;
      try {
        window.Echo.leave(this.activeConversationChannelName);
      } catch {}
      this.activeConversationChannelName = null;
    },

    subscribeToConversationChannel(conversationId) {
      if (!window.Echo) return;

      this.stopConversationRealtime();

      const publicName = `conversation.${conversationId}`;
      const internalName = `private-${publicName}`;

      this.activeConversationChannelName = internalName;

      window.Echo.private(publicName)
        .listen('.MessageSent', (e) => this.handleIncomingMessage(e))
        .listen('.MessageSeen', (e) => this.handleMessageSeen(e));
    },

    handleIncomingMessage(message) {
      this.fetchUnreadCounts();

      const myId = this.currentUser?.id;
      const senderId = message?.sender_id || message?.sender?.id || null;
      const isMine = !!myId && !!senderId && String(senderId) === String(myId);

      // if current conversation open => append and mark read
      if (this.activeConversationId === message.conversation_id) {
        const exists = this.messages.some((m) => String(m.id) === String(message.id));
        if (!exists) this.messages.push(message);

        if (!isMine) this.markConversationAsReadById(this.activeConversationId);
      }

      // ✅ trigger UI notify ONLY for incoming (not mine)
      if (!isMine) {
        this.lastIncomingMessage = message;
      }
    },

    handleMessageSeen(payload) {
      const ids = payload.message_ids || [];
      const seenAt = payload.seen_at;

      this.messages = this.messages.map((m) => (ids.includes(m.id) ? { ...m, seen_at: seenAt } : m));
    },
  },
});
