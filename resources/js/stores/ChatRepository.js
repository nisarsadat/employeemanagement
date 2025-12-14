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

    // timers (kept, but NOT used for unread polling anymore)
    unreadPollId: null,
    presencePingId: null,

    // init flags
    initialized: false,
    usersLoadedOnce: false,
    conversationsLoadedOnce: false,

    lastIncomingMessage: null,
    lastSentMessageId: null,
  }),

  getters: {
    hasActiveConversation(state) {
      return !!state.activeConversationId && !!state.activeUser;
    },
  },

  actions: {
    /* =========================================================
      INIT
    ========================================================= */
    async initBadge() {
      if (!this.currentUser) await this.fetchCurrentUser();

      // ✅ realtime: no unread polling
      this.startPresencePing();
      this.subscribeToUserChannel();
    },
    async fetchUsers() {
      this.silentLoading = true;
      try {
        const { data } = await axios.get('/chat/users');
        this.users = Array.isArray(data) ? data : [];
        this.usersLoadedOnce = true; // keep flags consistent
      } finally {
        this.silentLoading = false;
      }
    },

    async fetchConversations() {
      this.silentLoading = true;
      try {
        const { data } = await axios.get('/chat/conversations');
        this.conversations = Array.isArray(data) ? data : [];
        this.recalculateUnread();
        this.conversationsLoadedOnce = true; // keep flags consistent
      } finally {
        this.silentLoading = false;
      }
    },

    async bootstrap() {
      if (this.initialized) return;

      this.bootLoading = true;
      try {
        if (!this.currentUser) await this.fetchCurrentUser();
        await this.fetchUsersOnce();
        await this.fetchConversationsOnce();

        // ✅ subscribe after we have currentUser
        this.subscribeToUserChannel();

        this.initialized = true;
      } finally {
        this.bootLoading = false;
      }
    },

    async fetchCurrentUser() {
      const { data } = await axios.get('/user');
      this.currentUser = data;
    },

    async fetchUsersOnce() {
      if (this.usersLoadedOnce) return;
      await this.fetchUsers();
    },

    async fetchConversationsOnce() {
      if (this.conversationsLoadedOnce) return;
      await this.fetchConversations();
    },

    /* =========================================================
      UNREAD (REALTIME ONLY)
    ========================================================= */
    recalculateUnread() {
      this.unreadCount = this.conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
    },

    // ❌ polling disabled (kept so nothing breaks if referenced somewhere)
    startUnreadPolling() {
      // intentionally disabled for realtime mode
    },

    // ❌ endpoint disabled (kept so nothing breaks if referenced somewhere)
    async fetchUnreadCounts() {
      // intentionally disabled for realtime mode
    },

    /* =========================================================
      PRESENCE (still via ping)
    ========================================================= */
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

    /* =========================================================
      OPEN / CLOSE CONVERSATION
    ========================================================= */
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
          // ✅ mark read + zero unread locally (realtime is for updates after this point)
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

      if (this.activeConversationId === conversationId) this.messages = [];

      this.conversations = this.conversations.map((c) =>
        c.id === conversationId ? { ...c, last_message: null, unread_count: 0 } : c
      );
      this.recalculateUnread();
    },

    /* =========================================================
      DELETE / SEARCH
    ========================================================= */
    async deleteAllChats() {
      await axios.post('/chat/chats/delete-all');
      this.activeUser = null;
      this.activeConversationId = null;
      this.messages = [];

      // ✅ realtime style: just clear local lists
      this.conversations = [];
      this.users = this.users; // keep users
      this.unreadCount = 0;

      // optional: reload lists once after destructive operation
      await this.fetchConversations();
    },

    async deleteMessage(messageId) {
      await axios.delete(`/chat/messages/${messageId}`);

      this.messages = this.messages.map((m) =>
        String(m.id) === String(messageId)
          ? { ...m, deleted_at: new Date().toISOString(), body: null }
          : m
      );
      // ✅ no refreshLists / unread polling
    },

    async searchInConversation(q) {
      if (!this.activeConversationId) return [];
      const { data } = await axios.get(`/chat/conversations/${this.activeConversationId}/search`, {
        params: { q },
      });
      return data;
    },

    /* =========================================================
      SEND (API) - KEEP AS IS
    ========================================================= */
    async sendMessageForm({ body, replyToId = null, attachments = null }) {
      if (!this.activeUser?.id) return;

      this.sending = true;
      try {
        const fd = new FormData();
        fd.append('receiver_id', String(this.activeUser.id));
        if (body && body.trim()) fd.append('body', body.trim());
        if (replyToId) fd.append('reply_to', String(replyToId));

        const addMany = (key, files) => {
          if (!files || !Array.isArray(files) || files.length === 0) return;
          files.forEach((f) => fd.append(`${key}[]`, f));
        };

        addMany('images', attachments?.images);
        addMany('videos', attachments?.videos);
        addMany('docs', attachments?.docs);
        addMany('voices', attachments?.voices);

        const { data } = await axios.post('/chat/messages', fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        // ✅ optimistic append for sender (because backend broadcasts with ->toOthers())
        if (
          this.activeConversationId &&
          String(data?.conversation_id) === String(this.activeConversationId)
        ) {
          const exists = this.messages.some((m) => String(m.id) === String(data.id));
          if (!exists) this.messages.push(data);
        }

        this.lastSentMessageId = data?.id || null;

        // ✅ update last_message locally (no refreshLists)
        this.bumpConversationLastMessage(data);

        return data;
      } finally {
        this.sending = false;
      }
    },

    async sendMessage(body) {
      return this.sendMessageForm({ body });
    },

    /* =========================================================
      ECHO SUBSCRIPTIONS
    ========================================================= */
    subscribeToUserChannel() {
      if (this.userChannelSubscribed || !window.Echo || !this.currentUser?.id) return;

      window.Echo.private(`user.${this.currentUser.id}`)
        .listen('.MessageSeen', (e) => this.handleMessageSeen(e))
        .listen('.MessageSent', (e) => this.handleIncomingMessage(e?.message ?? e));

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
        .listen('.MessageSent', (e) => this.handleIncomingMessage(e?.message ?? e))
        .listen('.MessageSeen', (e) => this.handleMessageSeen(e))
        .listen('.MessageDeleted', (e) => {
          const id = e?.message_id;
          const deletedAt = e?.deleted_at || new Date().toISOString();
          if (!id) return;
          this.messages = this.messages.map((m) =>
            String(m.id) === String(id)
              ? { ...m, deleted_at: deletedAt, body: null, attachments: [] }
              : m
          );
        });
    },

    /* =========================================================
      REALTIME HANDLERS
    ========================================================= */
    bumpConversationLastMessage(message) {
      const convId = message?.conversation_id;
      if (!convId) return;

      this.conversations = this.conversations.map((c) => {
        if (String(c.id) !== String(convId)) return c;

        return {
          ...c,
          last_message: message,
          // keep existing unread_count (changes in handleIncomingMessage)
        };
      });
    },

    handleIncomingMessage(message) {
      const m = message?.message ?? message; // safety
      if (!m?.id || !m?.conversation_id) return;

      const myId = this.currentUser?.id;
      const senderId = m?.sender_id || m?.sender?.id || null;
      const isMine = !!myId && !!senderId && String(senderId) === String(myId);

      // always update last_message
      this.bumpConversationLastMessage(m);

      // if active conversation open
      if (
        this.activeConversationId &&
        String(this.activeConversationId) === String(m.conversation_id)
      ) {
        const exists = this.messages.some((x) => String(x.id) === String(m.id));
        if (!exists) this.messages.push(m);

        // if not mine, mark read (this will broadcast MessageSeen to others)
        if (!isMine) this.markConversationAsReadById(this.activeConversationId);
      } else {
        // not open => realtime unread increment ONLY if not mine
        if (!isMine) {
          this.conversations = this.conversations.map((c) =>
            String(c.id) === String(m.conversation_id)
              ? { ...c, unread_count: (c.unread_count || 0) + 1 }
              : c
          );
          this.recalculateUnread();
          this.lastIncomingMessage = m;
        }
      }
    },

    handleMessageSeen(payload) {
      const ids = payload?.message_ids || [];
      const seenAt = payload?.seen_at;

      if (!ids.length || !seenAt) return;

      // mark messages seen
      this.messages = this.messages.map((m) =>
        ids.includes(m.id) ? { ...m, seen_at: seenAt } : m
      );

      // ✅ also update conversation unread_count = 0 if it matches
      const convId = payload?.conversation_id;
      if (convId) {
        this.conversations = this.conversations.map((c) =>
          String(c.id) === String(convId) ? { ...c, unread_count: 0 } : c
        );
        this.recalculateUnread();
      }
    },
    async refreshLists() {
      try {
        if (!this.currentUser) await this.fetchCurrentUser();
        await Promise.all([this.fetchUsers(), this.fetchConversations()]);
        this.recalculateUnread();
      } catch (e) {
        console.error('ChatRepository.refreshLists failed', e);
      }
    },
  },
});
