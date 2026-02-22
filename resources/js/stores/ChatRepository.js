import { defineStore } from 'pinia';
import axios from 'axios';
import dayjs from 'dayjs';

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
    onCallIncoming: null,
    onCallSignal: null,
    onCallEnded: null,
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

      // ❌ remove startPresencePing()
      // ✅ replace with realtime presence
      this.subscribeToPresence();

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
      this.unreadCount = (this.conversations || []).reduce(
        (sum, c) => sum + (Number(c.unread_count) || 0),
        0
      );
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
    async sendMessageForm({ body, replyToId, attachments, receiverId, conversationId }) {
      const fd = new FormData();
      fd.append('receiver_id', String(receiverId));
      if (body?.trim()) fd.append('body', body.trim());
      if (replyToId) fd.append('reply_to', String(replyToId));

      (attachments?.images || []).forEach((f) => fd.append('images[]', f));
      (attachments?.videos || []).forEach((f) => fd.append('videos[]', f));
      (attachments?.docs || []).forEach((f) => fd.append('docs[]', f));
      (attachments?.voices || []).forEach((f) => fd.append('voices[]', f));

      const res = await axios.post('/chat/messages', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      return res.data;
    },

    async sendMessage(body, { replyToId = null, attachments = null } = {}) {
      if (!this.activeUser?.id) throw new Error('No active user selected');

      return this.sendMessageForm({
        body,
        replyToId,
        attachments,
        receiverId: this.activeUser.id,
        conversationId: this.activeConversationId,
      });
    },

    /* =========================================================
      ECHO SUBSCRIPTIONS
    ========================================================= */
    subscribeToUserChannel() {
      if (this.userChannelSubscribed || !window.Echo || !this.currentUser?.id) return;

      window.Echo.private(`user.${this.currentUser.id}`)
        .listen('.MessageSeen', (e) => this.handleMessageSeen(e))
        .listen('.MessageSent', (e) => this.handleIncomingMessage(e?.message ?? e))
        // ✅ CALLS
        .listen('.CallIncoming', (e) => this.onCallIncoming?.(e?.call ?? e))
        .listen('.CallSignal', (e) => this.onCallSignal?.(e?.call ?? e))
        .listen('.CallEnded', (e) => this.onCallEnded?.(e?.call ?? e));

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

      const channelName = `conversation.${conversationId}`;
      this.activeConversationChannelName = channelName;

      window.Echo.private(channelName)
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
        if (!exists) {
          // ✅ REPLACE optimistic tmp message instead of pushing duplicate
          const incomingSenderId = m?.sender_id || m?.sender?.id;
          const incomingBody = (m?.body || '').trim();

          const incomingTs = m?.created_at ? dayjs(m.created_at) : dayjs();

          const replaceIndex = this.messages.findIndex((x) => {
            if (!String(x.id || '').startsWith('tmp-')) return false;

            const tmpSenderId = x?.sender_id || x?.sender?.id;
            if (String(tmpSenderId) !== String(incomingSenderId)) return false;

            if ((x?.body || '').trim() !== incomingBody) return false;

            const tmpTs = x?.created_at ? dayjs(x.created_at) : null;
            if (!tmpTs) return false;

            return Math.abs(incomingTs.diff(tmpTs, 'second')) <= 10;
          });

          if (replaceIndex !== -1) {
            this.messages.splice(replaceIndex, 1, m);
          } else {
            this.messages.push(m);
          }
        }

        // if not mine, mark read (this will broadcast MessageSeen to others)
        if (!isMine) this.markConversationAsReadById(this.activeConversationId);
      }
      // ✅ if active conversation open, do NOT increment unread
      if (
        this.activeConversationId &&
        String(this.activeConversationId) === String(m.conversation_id)
      ) {
        // (your existing logic to add message)
        if (!isMine) this.markConversationAsReadById(this.activeConversationId);
        return; // ✅ IMPORTANT
      }

      // ✅ NEW: conversation is NOT open -> increment unread in realtime (receiver only)
      if (!isMine) {
        let found = false;

        this.conversations = this.conversations.map((c) => {
          if (String(c.id) !== String(m.conversation_id)) return c;

          found = true;
          return {
            ...c,
            last_message: m,
            unread_count: (c.unread_count || 0) + 1,
          };
        });

        // if it's a new conversation not loaded in list
        if (!found) {
          this.fetchConversations(); // one-time sync
        } else {
          this.recalculateUnread(); // ✅ updates badge instantly
        }
      }
    },
    subscribeToPresence() {
      if (!window.Echo || !this.currentUser?.id) return;

      window.Echo.join('presence.chat')
        .here((users) => {
          const onlineIds = new Set(users.map((u) => String(u.id)));
          this.users = (this.users || []).map((u) => ({
            ...u,
            isOnline: onlineIds.has(String(u.id)),
          }));
        })
        .joining((user) => {
          this.users = (this.users || []).map((u) =>
            String(u.id) === String(user.id) ? { ...u, isOnline: true } : u
          );
        })
        .leaving((user) => {
          this.users = (this.users || []).map((u) =>
            String(u.id) === String(user.id) ? { ...u, isOnline: false } : u
          );
        });
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
    async startCall({ type, toUserId, conversationId, call_id }) {
      await axios.post('/chat/calls/start', {
        call_type: type,
        to_user_id: toUserId,
        conversation_id: conversationId,
        call_id,
      });
    },

    async sendCallSignal({ toUserId, conversationId, callId, callType, signal }) {
      await axios.post('/chat/calls/signal', {
        to_user_id: toUserId,
        conversation_id: conversationId,
        call_id: callId,
        call_type: callType,
        signal,
      });
    },

    async endCall({ toUserId, conversationId, callId, type, status, duration_seconds }) {
      await axios.post('/chat/calls/end', {
        to_user_id: toUserId,
        conversation_id: conversationId,
        call_id: callId,
        call_type: type,
        status,
        duration_seconds: duration_seconds ?? 0,
      });
    },
  },
});
