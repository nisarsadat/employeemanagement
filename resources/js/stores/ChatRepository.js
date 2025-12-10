// src/stores/ChatRepository.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useChatRepository = defineStore('ChatRepository', {
  state: () => ({
    loading: false,
    sending: false,

    // auth user
    currentUser: null,

    // users to chat with (loaded once on open)
    users: [],

    // list of conversations with unread_count & last_message
    conversations: [],

    // current conversation & partner
    activeUser: null, // { id, name, email? }
    activeConversationId: null,

    // messages of current conversation (ASC)
    messages: [],

    // total unread count for appbar badge
    unreadCount: 0,

    // pagination meta
    messagesMeta: {
      current_page: 1,
      last_page: 1,
      per_page: 50,
      total: 0,
    },

    // Echo
    userChannelSubscribed: false,
    conversationChannelName: null,

    // polling (fallback when Echo is not configured)
    conversationsPollId: null,
    messagesPollId: null,

    initialized: false,
  }),

  getters: {
    hasActiveConversation(state) {
      return !!state.activeConversationId && !!state.activeUser;
    },
  },

  actions: {
    /* ===============================
       BOOTSTRAP (called when chat menu opened)
       - ONE TIME:
         /user
         /chat/users
         /chat/conversations
       - then start polling (2s) only for:
         /chat/conversations
         /chat/conversations/user/{id}
    =============================== */
    async bootstrap() {
      if (this.initialized) return;

      try {
        this.loading = true;

        // 1) fetch current auth user
        await this.fetchCurrentUser();

        // 2) fetch users (list at left)
        await this.fetchUsers();

        // 3) fetch conversations (for unread + last message)
        await this.fetchConversations();

        // 4) subscribe to Echo user channel
        this.subscribeToUserChannel();

        // 5) start polling fallback (every 2s) – only conversations + active messages
        this.startPolling();

        this.initialized = true;
      } catch (e) {
        console.error('Chat bootstrap error', e);
      } finally {
        this.loading = false;
      }
    },

    /* ===============================
       BASIC LOADERS
    =============================== */
    async fetchCurrentUser() {
      try {
        const { data } = await axios.get('/user'); // Sanctum default
        this.currentUser = data;
      } catch (e) {
        console.error('fetchCurrentUser error', e);
      }
    },

    async fetchUsers() {
      try {
        const { data } = await axios.get('/chat/users');
        this.users = Array.isArray(data) ? data : [];
      } catch (e) {
        console.error('fetchUsers error', e);
      }
    },

    async fetchConversations() {
      try {
        const { data } = await axios.get('/chat/conversations');
        this.conversations = Array.isArray(data) ? data : [];
        this.recalculateUnread();
      } catch (e) {
        console.error('fetchConversations error', e);
      }
    },

    async refreshActiveConversationMessages() {
      if (!this.activeUser || !this.activeUser.id) return;

      try {
        const { data } = await axios.get(
          `/chat/conversations/user/${this.activeUser.id}`,
          {
            params: { perPage: this.messagesMeta.per_page },
          },
        );

        this.activeConversationId = data.conversation.id;

        const msgs = Array.isArray(data.messages?.data)
          ? data.messages.data
          : [];

        // backend DESC → show ASC
        this.messages = [...msgs].reverse().map((m) => ({
          ...m,
          sender: m.sender || {},
        }));

        this.messagesMeta = {
          current_page: data.messages.meta.current_page,
          last_page: data.messages.meta.last_page,
          per_page: data.messages.meta.per_page,
          total: data.messages.meta.total,
        };
      } catch (e) {
        console.error('refreshActiveConversationMessages error', e);
      }
    },

    /* ===============================
       OPEN CONVERSATION WITH USER
    =============================== */
    async openConversationWith(user) {
      if (!user || !user.id) return;

      this.loading = true;

      try {
        this.activeUser = user;

        const { data } = await axios.get(
          `/chat/conversations/user/${user.id}`,
          {
            params: { perPage: this.messagesMeta.per_page },
          },
        );

        this.activeConversationId = data.conversation.id;

        const msgs = Array.isArray(data.messages?.data)
          ? data.messages.data
          : [];

        this.messages = [...msgs].reverse().map((m) => ({
          ...m,
          sender: m.sender || {},
        }));

        this.messagesMeta = {
          current_page: data.messages.meta.current_page,
          last_page: data.messages.meta.last_page,
          per_page: data.messages.meta.per_page,
          total: data.messages.meta.total,
        };

        // mark read in backend + local
        await this.markConversationAsReadById(this.activeConversationId);

        // subscribe to conversation channel
        this.subscribeToConversationChannel(this.activeConversationId);

        // ensure messages polling is active for this conv (every 2s)
        this.startMessagesPolling();
      } catch (e) {
        console.error('openConversationWith error', e);
      } finally {
        this.loading = false;
      }
    },

    /* ===============================
       UNREAD / READ HELPERS
    =============================== */
    recalculateUnread() {
      this.unreadCount = (this.conversations || []).reduce(
        (sum, c) => sum + (c.unread_count || 0),
        0,
      );
    },

    async markConversationAsReadById(conversationId) {
      if (!conversationId) return;

      try {
        await axios.post(`/chat/conversations/${conversationId}/read`);

        const idx = this.conversations.findIndex((c) => c.id === conversationId);
        if (idx !== -1) {
          const conv = this.conversations[idx];
          const updated = {
            ...conv,
            unread_count: 0,
          };
          this.conversations.splice(idx, 1, updated);
        }

        this.recalculateUnread();
      } catch (e) {
        console.error('markConversationAsReadById error', e);
      }
    },

    /**
     * Used by component: mark all conversations with this user as read.
     */
    async markMessagesAsRead(userId) {
      if (!userId) return;

      const targetConversations = this.conversations.filter(
        (c) => c.other_user && c.other_user.id === userId,
      );

      if (!targetConversations.length) {
        if (this.activeConversationId && this.activeUser?.id === userId) {
          await this.markConversationAsReadById(this.activeConversationId);
        }
        return;
      }

      for (const conv of targetConversations) {
        await this.markConversationAsReadById(conv.id);
      }
    },

    /* ===============================
       SEND MESSAGE
    =============================== */
    async sendMessage(body) {
      if (!this.activeUser || !this.activeUser.id) return;
      if (!body || !body.trim()) return;
      if (!this.currentUser) return;

      this.sending = true;

      try {
        const trimmed = body.trim();

        const { data } = await axios.post('/chat/messages', {
          receiver_id: this.activeUser.id,
          body: trimmed,
        });

        const message = {
          id: data.id,
          conversation_id: data.conversation_id,
          body: data.body,
          sender: data.sender,
          created_at: data.created_at,
        };

        if (!this.activeConversationId) {
          this.activeConversationId = data.conversation_id;
        }

        this.messages.push(message);
        this.messages.sort(
          (a, b) => new Date(a.created_at) - new Date(b.created_at),
        );

        // update conversations list (outgoing message, mark read for us)
        this.upsertConversationFromMessage(message, true, { markRead: true });
      } catch (e) {
        console.error('sendMessage error', e);
      } finally {
        this.sending = false;
      }
    },

    /* ===============================
       INCOMING MESSAGE (Echo)
    =============================== */
    handleIncomingMessage(payload) {
      if (!payload) return;

      const currentUserId = this.currentUser?.id ?? null;

      // if we are sender, ignore duplicate
      if (currentUserId && payload.sender?.id === currentUserId) {
        return;
      }

      const message = {
        id: payload.id,
        conversation_id: payload.conversation_id,
        body: payload.body,
        sender: payload.sender,
        created_at: payload.created_at,
      };

      const isActiveConversation =
        this.activeConversationId &&
        payload.conversation_id === this.activeConversationId;

      if (isActiveConversation) {
        this.messages.push(message);
        this.messages.sort(
          (a, b) => new Date(a.created_at) - new Date(b.created_at),
        );

        this.upsertConversationFromMessage(message, false, { markRead: true });

        // tell backend it's read
        this.markConversationAsReadById(payload.conversation_id);
      } else {
        // different conversation → increment unread
        this.upsertConversationFromMessage(message, false, {
          incrementUnread: true,
        });
      }
    },

    /**
     * Sync conversations list with last message + unread_count.
     */
    upsertConversationFromMessage(message, isOutgoing, options = {}) {
      const { markRead = false, incrementUnread = false } = options;
      const convId = message.conversation_id;

      const idx = this.conversations.findIndex((c) => c.id === convId);

      let otherUser = null;
      if (
        this.activeConversationId === convId &&
        this.activeUser &&
        this.activeUser.id
      ) {
        otherUser = { id: this.activeUser.id, name: this.activeUser.name };
      } else if (
        message.sender &&
        this.currentUser &&
        message.sender.id !== this.currentUser.id
      ) {
        otherUser = { id: message.sender.id, name: message.sender.name };
      }

      if (idx !== -1) {
        const conv = this.conversations[idx];
        let unread = conv.unread_count || 0;

        if (markRead) {
          unread = 0;
        } else if (incrementUnread) {
          unread += 1;
        }

        const updated = {
          ...conv,
          last_message: message,
          other_user: conv.other_user || otherUser,
          unread_count: unread,
        };

        this.conversations.splice(idx, 1, updated);
      } else {
        const unread = incrementUnread ? 1 : 0;
        const newConv = {
          id: convId,
          type: 'direct',
          other_user: otherUser,
          last_message: message,
          unread_count: unread,
        };
        this.conversations.unshift(newConv);
      }

      this.recalculateUnread();
    },

    /* ===============================
       POLLING FALLBACK (2 seconds)
       Only calls:
       - fetchConversations()
       - refreshActiveConversationMessages()
    =============================== */
    startPolling() {
      // conversations polling (badge + list)
      if (this.conversationsPollId) {
        clearInterval(this.conversationsPollId);
      }
      this.conversationsPollId = window.setInterval(() => {
        this.fetchConversations();
      }, 2000); // 🔁 every 2 seconds

      // messages polling for active conversation
      this.startMessagesPolling();
    },

    startMessagesPolling() {
      if (this.messagesPollId) {
        clearInterval(this.messagesPollId);
      }

      if (!this.activeUser || !this.activeUser.id) return;

      this.messagesPollId = window.setInterval(() => {
        this.refreshActiveConversationMessages();
      }, 2000); // 🔁 every 2 seconds
    },

    stopPolling() {
      if (this.conversationsPollId) {
        clearInterval(this.conversationsPollId);
        this.conversationsPollId = null;
      }
      if (this.messagesPollId) {
        clearInterval(this.messagesPollId);
        this.messagesPollId = null;
      }
    },

    /* ===============================
       ECHO SUBSCRIPTIONS
    =============================== */
    subscribeToUserChannel() {
      if (this.userChannelSubscribed) return;
      if (!this.currentUser) return;
      if (typeof window === 'undefined' || !window.Echo) return;

      const userId = this.currentUser.id;
      if (!userId) return;

      window.Echo.private(`user.${userId}`).listen('.MessageSent', (e) => {
        this.handleIncomingMessage(e);
      });

      this.userChannelSubscribed = true;
    },

    subscribeToConversationChannel(conversationId) {
      if (!conversationId) return;
      if (typeof window === 'undefined' || !window.Echo) return;

      if (this.conversationChannelName) {
        try {
          window.Echo.leave(this.conversationChannelName);
        } catch (e) {
          console.warn('Failed to leave previous conversation channel', e);
        }
      }

      const channelName = `conversation.${conversationId}`;
      this.conversationChannelName = channelName;

      window.Echo.private(channelName).listen('.MessageSent', (e) => {
        this.handleIncomingMessage(e);
      });
    },
  },
});
