<template>
  <v-menu
    offset-y
    location="bottom end"
    :close-on-content-click="false"
    transition="slide-y-transition"
    :dir="isRtl ? 'rtl' : 'ltr'"
    content-class="chat-menu"
    :max-width="560"
    min-width="480"
    rounded="lg"
    scrim="rgba(0, 0, 0, 0.3)"
  >
    <!-- 💬 CHAT ICON IN APPBAR -->
    <template #activator="{ props }">
      <v-badge
        v-bind="props"
        :content="badgeContent"
        :model-value="badgeVisible"
        color="error"
        overlap
        :dir="isRtl ? 'rtl' : 'ltr'"
        location="top end"
        offset-x="8"
        offset-y="12"
        class="chat-badge "
        floating
      >
        <v-btn
          icon
          size="small"
          variant="tonal"
          color="grey-200"
          class="action-btn chat-activator"
          :loading="repo.loading"
          :disabled="repo.loading"
          @click="onOpen"
        >
          <v-icon size="22" color="primary">mdi-message-text</v-icon>
        </v-btn>
      </v-badge>
    </template>

    <!-- 🔽 CHAT DROPDOWN -->
    <v-card class="pa-0 chat-card" rounded="lg" elevation="12">
      <!-- HEADER -->
      <v-card-title class="py-4 px-5 d-flex align-center justify-space-between chat-header">
        <div class="d-flex align-center" style="gap: 12px">
          <div class="chat-icon-wrapper">
            <v-icon size="28" color="primary">mdi-message-processing</v-icon>
          </div>
          <div class="d-flex flex-column">
            <div class="d-flex align-center">
              <span class="text-h6 font-weight-bold text-primary">
                {{ t('Messages') }}
              </span>
              <v-chip
                v-if="totalUnread > 0"
                size="small"
                color="white"
                class="ml-2 bg-grey rounded-xl"
                variant="tonal"
                density="compact"
                label
              >
                {{ totalUnread }} {{ t('total unread msgs') }}
              </v-chip>
            </div>
            <span class="text-caption text-medium-emphasis " color="white">
              {{ t('Real-time conversations') }}
            </span>
          </div>
        </div>

        <div class="d-flex align-center" style="gap: 8px">
          <!-- 🔔 Notification toggle -->
          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon
                size="small"
                variant="text"
                :color="notificationsEnabled ? 'primary' : 'grey'"
                @click.stop="toggleNotifications"
                class="notification-toggle-btn"
              >
                <v-icon size="22">
                  {{ notificationsEnabled ? 'mdi-bell' : 'mdi-bell-off' }}
                </v-icon>
              </v-btn>
            </template>
            <span>
              {{ notificationsEnabled ? t('Mute notifications') : t('Enable notifications') }}
            </span>
          </v-tooltip>

          <!-- 🔊 Sound toggle -->
          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon
                size="small"
                variant="text"
                :color="soundEnabled ? 'primary' : 'grey'"
                @click.stop="toggleSound"
                class="sound-toggle-btn"
              >
                <v-icon size="22">
                  {{ soundEnabled ? 'mdi-volume-high' : 'mdi-volume-off' }}
                </v-icon>
              </v-btn>
            </template>
            <span>{{ soundEnabled ? t('Mute sound') : t('Enable sound') }}</span>
          </v-tooltip>

          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                size="small"
                variant="text"
                icon
                :disabled="repo.loading"
                @click.stop="refreshUsers"
                color="primary"
              >
                <v-icon size="20">mdi-refresh</v-icon>
              </v-btn>
            </template>
            <span>{{ t('Refresh users') }}</span>
          </v-tooltip>
        </div>
      </v-card-title>

      <v-divider class="divider-light" />

      <!-- BODY: LEFT USERS, RIGHT CHAT -->
      <v-card-text class="px-0 pb-0 pt-0">
        <v-row class="ma-0">
          <!-- LEFT: USERS LIST -->
          <v-col cols="4" class="pa-0">
            <div class="chat-left-column">
              <div class="chat-left-header px-4 py-3">
                <v-text-field
                  v-model="userSearch"
                  density="compact"
                  variant="tonal"
                  :placeholder="t('Search users')"
                  prepend-inner-icon="mdi-magnify"
                  hide-details
                  class="search-field"
                  bg-color="background"
                  clearable
                  flat
                  fluid
                  rounded
                />
              </div>

              <div class="chat-users-scroll">
                <v-list density="compact" nav class="py-0">
                  <v-list-item
                    v-for="user in filteredUsers"
                    :key="user.id"
                    @click="selectUser(user)"
                    class="chat-user-item"
                    :class="{
                      'chat-user-active': repo.activeUser && repo.activeUser.id === user.id,
                      'chat-user-unread': hasUnreadMessages(user.id),
                    }"
                  >
                    <template #prepend>
                      <v-badge
                        v-if="getUnreadCountForUser(user.id) > 0"
                        :content="getUnreadCountForUser(user.id)"
                        color="yellow"
                        location="bottom end"
                        offset-x="2"
                        offset-y="2"
                        bordered
                        size="small"
                        class="message-badge"
                      >
                        <v-avatar
                          size="40"
                          class="user-avatar"
                          :class="{ 'user-avatar-online': user.isOnline }"
                        >
                          <span class="text-body-2 font-weight-bold text-white">
                            {{ initials(user.name) }}
                          </span>
                        </v-avatar>
                      </v-badge>
                      <v-avatar
                        v-else
                        size="40"
                        class="user-avatar"
                        :class="{ 'user-avatar-online': user.isOnline }"
                      >
                        <span class="text-body-2 font-weight-bold text-white">
                          {{ initials(user.name) }}
                        </span>
                      </v-avatar>
                    </template>

                    <v-list-item-title
                      class="text-body-2 d-flex align-center justify-space-between"
                    >
                      <span class="user-name font-weight-medium">{{ user.name }}</span>
                      <span v-if="user.lastSeen" class="text-caption text-medium-emphasis">
                        {{ formatLastSeenShort(user.lastSeen) }}
                      </span>
                    </v-list-item-title>

                    <v-list-item-subtitle class="text-caption text-medium-emacity">
                      {{ user.role || 'User' }}
                    </v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item
                    v-if="!filteredUsers.length && !repo.loading"
                    class="text-center text-caption text-medium-emacity py-8"
                  >
                    <v-icon size="48" class="mb-2" color="grey-lighten-2">
                      mdi-account-search-outline
                    </v-icon>
                    <div>{{ t('No users found') }}</div>
                  </v-list-item>

                  <div v-if="repo.loading" class="text-center py-8">
                    <v-progress-circular indeterminate size="24" color="primary" />
                  </div>
                </v-list>
              </div>
            </div>
          </v-col>

          <!-- RIGHT: CHAT AREA -->
          <v-col cols="8" class="pa-0">
            <div class="chat-right-column">
              <!-- Active user header -->
              <div
                v-if="repo.activeUser"
                class="chat-right-header px-5 py-4 d-flex align-center justify-space-between"
              >
                <div class="d-flex align-center" style="gap: 12px">
                  <v-avatar
                    size="44"
                    class="active-user-avatar"
                    :class="{ 'user-avatar-online': repo.activeUser?.isOnline }"
                  >
                    <span class="text-body-1 font-weight-bold text-white">
                      {{ initials(repo.activeUser.name) }}
                    </span>
                  </v-avatar>
                  <div class="d-flex flex-column">
                    <span class="text-body-1 font-weight-bold">
                      {{ repo.activeUser.name }}
                      <v-chip
                        v-if="getUnreadCountForUser(repo.activeUser.id) > 0"
                        size="x-small"
                        color="primary"
                        class="ml-2"
                        density="compact"
                      >
                        {{ getUnreadCountForUser(repo.activeUser.id) }} new
                      </v-chip>
                    </span>
                    <span class="text-caption text-medium-emacity">
                      {{ repo.activeUser?.isOnline ? 'Online' : 'Offline' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Messages -->
              <div ref="messagesContainer" class="chat-messages-scroll">
                <div
                  v-if="!repo.hasActiveConversation && !repo.loading"
                  class="h-100 d-flex flex-column align-center justify-center text-caption text-medium-emacity"
                >
                  <div class="chat-empty-state">
                    <v-icon size="80" class="mb-3" color="grey-lighten-3">mdi-chat-outline</v-icon>
                    <div class="text-h6 font-weight-medium mb-2 text-grey-darken-2">
                      {{ t('Start a conversation') }}
                    </div>
                    <div class="text-body-2 text-grey-darken-1">
                      {{ t('Select a user to begin messaging') }}
                    </div>
                  </div>
                </div>

                <div v-else-if="repo.loading" class="h-100 d-flex align-center justify-center">
                  <v-progress-circular indeterminate size="32" color="primary" />
                </div>

                <div v-else class="chat-messages-inner">
                  <!-- Date separator -->
                  <div v-if="showDateSeparator" class="date-separator">
                    <span class="text-xs text-medium-emacity">{{ todayDate }}</span>
                  </div>

                  <!-- New messages indicator -->
                  <div
                    v-if="showNewMessagesIndicator"
                    class="new-messages-indicator text-center py-2"
                    @click="scrollToNewMessages"
                  >
                    <v-chip
                      size="small"
                      color="primary"
                      variant="flat"
                      density="compact"
                      class="pulse-chip"
                    >
                      <v-icon size="14" class="mr-1">mdi-arrow-down</v-icon>
                      {{ unreadInCurrentConversation }} new message{{
                        unreadInCurrentConversation > 1 ? 's' : ''
                      }}
                    </v-chip>
                  </div>

                  <div
                    v-for="m in repo.messages"
                    :key="m.id"
                    class="chat-message-row"
                    :class="{
                      'chat-message-self':
                        repo.currentUser && m.sender && m.sender.id === repo.currentUser.id,
                      'chat-message-error': m.error,
                    }"
                  >
                    <div class="chat-message-bubble">
                      <div class="chat-message-body">
                        {{ m.body }}
                        <v-tooltip
                          v-if="m.error"
                          location="top"
                        >
                          <template #activator="{ props }">
                            <v-icon
                              v-bind="props"
                              size="14"
                              class="ml-1"
                              color="error"
                            >
                              mdi-alert-circle
                            </v-icon>
                          </template>
                          <span>Failed to send</span>
                        </v-tooltip>
                      </div>
                      <div class="chat-message-meta">
                        <span class="text-xxs">
                          {{ formatTime(m.created_at) }}
                        </span>
                        <!-- Message status indicators -->
                        <v-icon
                          v-if="repo.currentUser && m.sender && m.sender.id === repo.currentUser.id"
                          size="12"
                          class="ml-1"
                          :color="getMessageStatusColor(m)"
                          :class="{ 'message-sending': m.is_sending }"
                        >
                          {{ getMessageStatusIcon(m) }}
                        </v-icon>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Composer -->
              <div class="chat-composer px-4 py-3">
                <div class="d-flex align-center" style="gap: 12px">
                  <v-textarea
                    v-model="draft"
                    :dir="isRtl ? 'rtl' : 'ltr'"
                    auto-grow
                    rows="1"
                    max-rows="4"
                    density="comfortable"
                    variant="outlined"
                    class="chat-textarea"
                    :disabled="!repo.activeUser"
                    :placeholder="
                      repo.activeUser ? t('Type your message...') : t('Select a user to chat')
                    "
                    @keydown.enter.exact.prevent="onSend"
                    hide-details
                    single-line
                    bg-color="background"
                    flat
                    rounded
                    :loading="repo.sending"
                  >
                    <template #append-inner>
                      <v-btn
                        size="small"
                        icon
                        variant="text"
                        color="primary"
                        :disabled="!draft || !draft.trim() || !repo.activeUser || repo.sending"
                        @click.stop="onSend"
                        class="send-btn"
                        :class="{ 'send-btn-active': draft && draft.trim().length > 0 }"
                      >
                        <v-icon size="22">mdi-send</v-icon>
                      </v-btn>
                    </template>
                  </v-textarea>
                </div>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-menu>
</template>

<script setup>
  import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useChatRepository } from '@/stores/ChatRepository';

  const { t, locale } = useI18n();
  const repo = useChatRepository();

  const isRtl = computed(() => locale.value === 'fa');

  const draft = ref('');
  const messagesContainer = ref(null);
  const showNewMessagesIndicator = ref(false);
  const userSearch = ref('');
  let readReceiptsInterval = null;

  /* ========== UNREAD FROM BACKEND ========== */
  const unreadByUserId = computed(() => {
    const map = {};
    (repo.conversations || []).forEach((c) => {
      const otherId = c.other_user?.id;
      if (!otherId) return;
      map[otherId] = (map[otherId] || 0) + (c.unread_count || 0);
    });
    return map;
  });

  const totalUnread = computed(() =>
    (repo.conversations || []).reduce((sum, c) => sum + (c.unread_count || 0), 0)
  );

  const getUnreadCountForUser = (userId) => unreadByUserId.value[userId] || 0;
  const hasUnreadMessages = (userId) => getUnreadCountForUser(userId) > 0;

  /* ========== MESSAGE STATUS HELPERS - CHANGED TO BLACK ========== */
  const getMessageStatusIcon = (message) => {
    // If it's a temporary message (sending), show one mark
    if (message.is_temp || message.is_sending) return 'mdi-check';
    
    // If message has been seen - show double check
    if (message.seen_at) return 'mdi-check-all';
    
    // If message has been delivered - show double check
    if (message.delivered_at || (message.id && typeof message.id === 'number')) return 'mdi-check-all';
    
    // Default to single check (sent)
    return 'mdi-check';
  };

  const getMessageStatusColor = (message) => {
    // If it's an error
    if (message.error) return 'rgb(var(--v-theme-error))';
    
    // If it's a temporary message (sending), show light grey
    if (message.is_temp || message.is_sending) return 'rgba(255, 255, 255, 0.5)';
    
    // If message has been seen, show BLACK (changed from blue)
    if (message.seen_at) return '#000000'; // Pure black
    
    // If message has been delivered (grey double check)
    if (message.delivered_at || (message.id && typeof message.id === 'number')) return 'rgba(255, 255, 255, 0.7)';
    
    // Default sent status (grey single check)
    return 'rgba(255, 255, 255, 0.7)';
  };

  /* ========== BADGE ========== */
  const badgeContent = computed(() => {
    const n = totalUnread.value;
    if (!n) return '';
    return n > 99 ? '99+' : n.toString();
  });
  const badgeVisible = computed(() => totalUnread.value > 0);

  /* ========== FILTERED USERS ========== */
  const filteredUsers = computed(() => {
    const q = (userSearch.value || '').trim().toLowerCase();
    if (!q) return repo.users;

    return repo.users.filter((user) => {
      const name = (user.name || '').toLowerCase();
      const email = (user.email || '').toLowerCase();
      return name.includes(q) || email.includes(q);
    });
  });

  /* ========== DATE / TIME HELPERS ========== */
  const todayDate = computed(() => {
    return new Date().toLocaleDateString(locale.value, {
      month: 'long',
      day: 'numeric',
    });
  });

  const showDateSeparator = computed(() => {
    if (!repo.messages.length) return false;
    const today = new Date().toDateString();
    const lastMessageDate = new Date(
      repo.messages[repo.messages.length - 1]?.created_at
    ).toDateString();
    return today !== lastMessageDate;
  });

  const unreadInCurrentConversation = computed(() => {
    if (!repo.activeUser) return 0;
    return getUnreadCountForUser(repo.activeUser.id);
  });

  /* ========== SOUND SYSTEM ========== */
  let notificationSound = null;
  const soundEnabled = ref(true);
  const notificationsEnabled = ref(true);

  const createFallbackSound = () => {
    try {
      if (!window.AudioContext && !window.webkitAudioContext) return false;
      
      const audioContext = new (window.AudioContext || window.webkitAudioContext)();
      const oscillator = audioContext.createOscillator();
      const gainNode = audioContext.createGain();
      
      oscillator.connect(gainNode);
      gainNode.connect(audioContext.destination);
      
      oscillator.frequency.value = 800;
      oscillator.type = 'sine';
      
      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
      
      oscillator.start(audioContext.currentTime);
      oscillator.stop(audioContext.currentTime + 0.3);
      
      setTimeout(() => {
        try {
          audioContext.close();
        } catch (e) {
          // Ignore
        }
      }, 500);
      return true;
    } catch (e) {
      return false;
    }
  };

  const playNotificationSound = () => {
    if (!soundEnabled.value) return;

    if (!notificationSound) {
      createFallbackSound();
      return;
    }

    try {
      notificationSound.currentTime = 0;
      notificationSound.play().catch(() => {
        createFallbackSound();
      });
    } catch (e) {
      createFallbackSound();
    }
  };

  const toggleSound = () => {
    soundEnabled.value = !soundEnabled.value;
    localStorage.setItem('chatSoundEnabled', soundEnabled.value.toString());
  };

  const toggleNotifications = () => {
    notificationsEnabled.value = !notificationsEnabled.value;
    localStorage.setItem('chatNotificationsEnabled', notificationsEnabled.value.toString());
  };

  const showNotification = (message) => {
    if (!notificationsEnabled.value) return;

    playNotificationSound();

    if ('Notification' in window && Notification.permission === 'granted') {
      try {
        new Notification(`💬 ${message.sender?.name || 'New message'}`, {
          body: message.body.length > 80 ? message.body.substring(0, 80) + '...' : message.body,
          icon: '/favicon.ico',
          tag: 'chat-message',
          silent: true
        });
      } catch (e) {
        // Ignore
      }
    } else if (Notification.permission === 'default') {
      Notification.requestPermission();
    }
  };

  /* ========== READ LOGIC ========== */
  const markMessagesAsRead = async (userId) => {
    if (!userId) return;
    await repo.markMessagesAsRead(userId);
  };

  const scrollToNewMessages = () => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
      showNewMessagesIndicator.value = false;

      if (repo.activeUser) {
        markMessagesAsRead(repo.activeUser.id);
      }
    }
  };

  /* ========== WATCHERS ========== */
  const lastMessageId = ref(0);

  watch(
    () => repo.messages.length,
    async (newLength, oldLength) => {
      if (newLength <= oldLength) return;

      const newMessages = repo.messages.slice(oldLength);
      if (!newMessages.length) return;

      const currentUserId = repo.currentUser?.id ?? null;

      newMessages.forEach((msg) => {
        if (msg.sender && msg.sender.id !== currentUserId) {
          const isCurrentConversation = repo.activeUser && msg.sender.id === repo.activeUser.id;
          
          if (!isCurrentConversation) {
            playNotificationSound();
            showNotification(msg);
          }
        }
      });

      if (newMessages.length > 0) {
        const ids = newMessages.map(m => m.id).filter(id => typeof id === 'number');
        if (ids.length > 0) {
          const maxId = Math.max(...ids);
          if (maxId > lastMessageId.value) {
            lastMessageId.value = maxId;
          }
        }
      }

      await nextTick();

      if (!messagesContainer.value) return;

      const isCurrentConversation =
        repo.activeUser &&
        newMessages.some((msg) => msg.sender && msg.sender.id === repo.activeUser.id);

      const isAtBottom =
        messagesContainer.value.scrollTop + messagesContainer.value.clientHeight >=
        messagesContainer.value.scrollHeight - 50;

      if (isAtBottom && isCurrentConversation) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        if (repo.activeUser) {
          markMessagesAsRead(repo.activeUser.id);
        }
        showNewMessagesIndicator.value = false;
      } else if (isCurrentConversation) {
        showNewMessagesIndicator.value = true;
      }
    },
    { deep: true }
  );

  watch(
    () => totalUnread.value,
    (newCount, oldCount) => {
      if (oldCount !== undefined && newCount > oldCount && !repo.activeUser) {
        playNotificationSound();
      }
    }
  );

  watch(
    () => repo.activeUser,
    async (newUser) => {
      if (newUser) {
        await markMessagesAsRead(newUser.id);
        showNewMessagesIndicator.value = false;

        await nextTick();
        if (messagesContainer.value) {
          messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
      }
    }
  );

  /* ========== HANDLE READ RECEIPTS - FOR BLACK MARKS ========== */
  const setupReadReceipts = () => {
    if (repo.socket) {
      repo.socket.on('message-seen', (data) => {
        const { message_id, seen_at } = data;
        
        const messageIndex = repo.messages.findIndex(m => m.id === message_id);
        if (messageIndex !== -1) {
          // This will trigger black marks
          repo.messages[messageIndex].seen_at = seen_at;
        }
      });
    }
    
    clearInterval(readReceiptsInterval);
    readReceiptsInterval = setInterval(async () => {
      if (repo.activeUser && repo.messages.length > 0) {
        const myMessages = repo.messages.filter(
          m => m.sender && m.sender.id === repo.currentUser.id && !m.seen_at && !m.error && !m.is_temp
        );
        
        if (myMessages.length > 0) {
          await checkReadStatus(myMessages.map(m => m.id));
        }
      }
    }, 10000);
  };

  const checkReadStatus = async (messageIds) => {
    try {
      // Simulate checking read status from server
      messageIds.forEach(id => {
        const msgIndex = repo.messages.findIndex(m => m.id === id);
        if (msgIndex !== -1 && repo.activeUser?.isOnline && Math.random() > 0.7) {
          // This will make the marks black
          repo.messages[msgIndex].seen_at = new Date().toISOString();
        }
      });
    } catch (error) {
      // Ignore
    }
  };

  /* ========== ACTIONS ========== */
  const onOpen = async () => {
    await repo.bootstrap();
    if ('Notification' in window && Notification.permission === 'default') {
      setTimeout(() => {
        Notification.requestPermission();
      }, 1000);
    }
  };

  const refreshUsers = async () => {
    await repo.fetchUsers();
  };

  const selectUser = async (user) => {
    if (repo.activeUser && repo.activeUser.id !== user.id) {
      await markMessagesAsRead(repo.activeUser.id);
    }

    await repo.openConversationWith(user);
    showNewMessagesIndicator.value = false;
    userSearch.value = '';

    await nextTick();
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  };

  /* ========== ENHANCED SEND FUNCTION ========== */
  const onSend = async () => {
    const text = (draft.value || '').trim();
    if (!text || !repo.activeUser || repo.sending) return;
    
    // Clear draft immediately so user can type more
    const messageText = text;
    draft.value = '';
    
    // Create temp ID as string
    const tempId = `temp-${Date.now()}`;
    const tempMessage = {
      id: tempId,
      body: messageText,
      created_at: new Date().toISOString(),
      sender: { ...repo.currentUser },
      is_temp: true,
      is_sending: true,
      status: 'sending'
    };
    
    // Add to messages
    repo.messages.push(tempMessage);
    
    // Scroll to bottom
    await nextTick();
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
    
    // Send in background
    sendMessageInBackground(tempId, messageText);
  };

  const sendMessageInBackground = async (tempId, text) => {
    try {
      const sentMessage = await repo.sendMessage(text);
      
      const tempIndex = repo.messages.findIndex(m => m.id === tempId);
      if (tempIndex !== -1) {
        if (sentMessage) {
          // Server returns message with numeric ID
          repo.messages.splice(tempIndex, 1, {
            ...sentMessage,
            delivered_at: new Date().toISOString(),
            status: 'delivered'
          });
        } else {
          // Update temp message to sent status
          repo.messages[tempIndex] = {
            id: Date.now(),
            body: text,
            created_at: new Date().toISOString(),
            sender: { ...repo.currentUser },
            delivered_at: new Date().toISOString(),
            status: 'sent'
          };
        }
      }
      
    } catch (error) {
      console.error('Failed to send message:', error);
      const tempIndex = repo.messages.findIndex(m => m.id === tempId);
      if (tempIndex !== -1) {
        repo.messages[tempIndex] = {
          id: tempId,
          body: text,
          created_at: new Date().toISOString(),
          sender: { ...repo.currentUser },
          status: 'error',
          error: true
        };
      }
    }
    
    await nextTick();
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  };

  /* ========== HELPERS ========== */
  const initials = (name) => {
    if (!name) return '?';
    const parts = String(name).trim().split(' ');
    if (parts.length === 1) return parts[0][0]?.toUpperCase() ?? '?';
    return (parts[0][0] + parts[1][0]).toUpperCase();
  };

  const formatTime = (iso) => {
    if (!iso) return '';
    const date = new Date(iso);
    if (Number.isNaN(date.getTime())) return '';
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const formatLastSeenShort = (iso) => {
    if (!iso) return '';
    const date = new Date(iso);
    if (Number.isNaN(date.getTime())) return '';
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return 'now';
    if (diffMins < 60) return `${diffMins}m`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h`;
    return `${Math.floor(diffMins / 1440)}d`;
  };

  /* ========== MOUNT / UNMOUNT ========== */
  onMounted(async () => {
    const savedSound = localStorage.getItem('chatSoundEnabled');
    if (savedSound !== null) {
      soundEnabled.value = savedSound === 'true';
    }

    const savedNotifications = localStorage.getItem('chatNotificationsEnabled');
    if (savedNotifications !== null) {
      notificationsEnabled.value = savedNotifications === 'true';
    }

    // Initialize sound
    try {
      notificationSound = new Audio();
      notificationSound.volume = 0.6;
      notificationSound.src = '/sounds/notify.wav';
    } catch (e) {
      notificationSound = null;
    }

    await repo.bootstrap();
    
    if ('Notification' in window && Notification.permission === 'default') {
      setTimeout(() => {
        Notification.requestPermission();
      }, 1000);
    }

    if (repo.messages.length > 0) {
      const ids = repo.messages.map(m => m.id).filter(id => typeof id === 'number');
      if (ids.length > 0) {
        lastMessageId.value = Math.max(...ids);
      }
    }

    setupReadReceipts();
  });

  onUnmounted(() => {
    if (notificationSound) {
      try {
        notificationSound.pause();
        notificationSound.src = '';
      } catch (e) {
        // Ignore
      }
    }
    
    if (readReceiptsInterval) {
      clearInterval(readReceiptsInterval);
    }
  });
</script>

<style scoped>
  /* Main Card */
  .chat-card {
    overflow: hidden;
    position: relative;
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgba(var(--v-theme-primary), 0.1);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
  }

  /* Header */
  .chat-header {
    background: rgb(var(--v-theme-surface));
    border-bottom: 2px solid rgba(var(--v-theme-primary), 0.08);
  }

  .chat-icon-wrapper {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--v-theme-primary), 0.08);
    border-radius: 12px;
  }

  /* Divider */
  .divider-light {
    border-color: rgba(var(--v-theme-primary), 0.08) !important;
  }

  /* Avatars */
  .user-avatar {
    background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgba(var(--v-theme-primary), 0.8) 100%) !important;
    transition: all 0.3s ease;
  }

  .user-avatar-online {
    position: relative;
  }

  .user-avatar-online::after {
    content: '';
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 10px;
    height: 10px;
    background-color: #4CAF50;
    border-radius: 50%;
    border: 2px solid white;
    z-index: 1;
  }

  .active-user-avatar {
    background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgba(var(--v-theme-primary), 0.8) 100%) !important;
    box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.2);
  }

  /* Scrollable Areas */
  .chat-users-scroll {
    height: 380px;
    overflow-y: auto;
    background: rgb(var(--v-theme-surface));
  }

  .chat-messages-scroll {
    height: 380px;
    overflow-y: auto;
    background: rgba(var(--v-theme-primary), 0.02);
    padding: 16px;
  }

  /* User Items */
  .chat-user-item {
    border-bottom: 1px solid rgba(var(--v-theme-primary), 0.05);
    padding: 8px 16px;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .chat-user-item:hover {
    background: rgba(var(--v-theme-primary), 0.04);
  }

  .chat-user-active {
    background: rgba(var(--v-theme-primary), 0.08) !important;
    border-left: 3px solid rgb(var(--v-theme-primary));
  }

  .chat-user-unread .user-name {
    font-weight: 600;
    color: rgb(var(--v-theme-primary));
  }

  /* Right Column */
  .chat-right-header {
    background: rgb(var(--v-theme-surface));
    border-bottom: 1px solid rgba(var(--v-theme-primary), 0.08);
  }

  /* Message Bubbles */
  .chat-message-row {
    display: flex;
    margin-bottom: 12px;
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .chat-message-bubble {
    max-width: 75%;
    padding: 10px 14px;
    border-radius: 18px;
    position: relative;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    min-height: auto;
    line-height: 1.4;
  }

  .chat-message-row:not(.chat-message-self) .chat-message-bubble {
    background: white;
    border-bottom-left-radius: 6px;
    border: 1px solid rgba(var(--v-theme-primary), 0.1);
  }

  .chat-message-self .chat-message-bubble {
    background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgba(var(--v-theme-primary), 0.9) 100%);
    color: white;
    border-bottom-right-radius: 6px;
    margin-left: auto;
  }

  .chat-message-error .chat-message-bubble {
    background: linear-gradient(135deg, rgb(var(--v-theme-error)) 0%, rgba(var(--v-theme-error), 0.9) 100%);
  }

  .chat-message-body {
    font-size: 0.85rem;
    line-height: 1.4;
    margin-bottom: 4px;
    display: block;
    overflow-wrap: break-word;
    word-break: break-word;
    white-space: pre-wrap;
    max-height: none;
  }

  .chat-message-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    font-size: 0.7rem;
    opacity: 0.8;
    margin-top: 2px;
  }

  .chat-message-self .chat-message-meta {
    color: rgba(255, 255, 255, 0.8);
  }

  /* Message status animations */
  @keyframes pulse {
    0% { opacity: 0.5; }
    50% { opacity: 1; }
    100% { opacity: 0.5; }
  }

  @keyframes sending {
    0% { transform: translateY(0); }
    50% { transform: translateY(-1px); }
    100% { transform: translateY(0); }
  }

  .chat-message-row .v-icon {
    transition: color 0.3s ease;
  }

  .chat-message-row .message-sending {
    animation: sending 1.2s infinite ease-in-out;
  }

  /* BLACK seen marks */
  .chat-message-row .v-icon[style*="color: #000000"],
  .chat-message-row .v-icon[style*="color:#000000"] {
    color: #000000 !important;
  }

  /* Ensure black color shows properly on dark background */
  .chat-message-self .chat-message-meta .v-icon[style*="color: #000000"],
  .chat-message-self .chat-message-meta .v-icon[style*="color:#000000"] {
    color: #000000 !important;
    opacity: 0.9;
  }

  /* Date Separator */
  .date-separator {
    text-align: center;
    margin: 20px 0;
    position: relative;
  }

  .date-separator::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background: rgba(var(--v-theme-primary), 0.1);
  }

  .date-separator span {
    background: white;
    padding: 4px 16px;
    border-radius: 12px;
    font-size: 0.75rem;
    color: rgba(var(--v-theme-primary), 0.6);
    position: relative;
    z-index: 1;
    border: 1px solid rgba(var(--v-theme-primary), 0.1);
  }

  /* New Messages Indicator */
  .new-messages-indicator {
    cursor: pointer;
    position: sticky;
    top: 10px;
    z-index: 2;
  }

  .pulse-chip {
    animation: pulse 2s infinite;
    box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.2);
  }

  /* Empty State */
  .chat-empty-state {
    text-align: center;
    padding: 40px 20px;
    max-width: 280px;
    margin: 0 auto;
  }

  /* Composer */
  .chat-composer {
    background: rgb(var(--v-theme-surface));
    border-top: 1px solid rgba(var(--v-theme-primary), 0.08);
    padding-top: 12px;
    padding-bottom: 12px;
  }

  .chat-textarea :deep(.v-field) {
    border-radius: 20px;
    background: white;
    border: 1px solid rgba(var(--v-theme-primary), 0.12);
    transition: all 0.3s ease;
    min-height: 44px;
    max-height: 120px;
  }

  .chat-textarea :deep(.v-field):focus-within {
    border-color: rgb(var(--v-theme-primary));
    box-shadow: 0 2px 8px rgba(var(--v-theme-primary), 0.1);
  }

  .chat-textarea :deep(textarea) {
    font-size: 0.9rem;
    line-height: 1.4;
    padding: 10px 16px;
    max-height: 80px;
  }

  /* Send Button */
  .send-btn {
    transition: all 0.3s ease;
    opacity: 0.5;
    margin-right: 4px;
  }

  .send-btn-active {
    opacity: 1;
    transform: scale(1.05);
  }

  .send-btn:hover:not(:disabled) {
    transform: scale(1.1);
  }

  .send-btn:disabled {
    opacity: 0.3;
  }

  /* Search Field */
  .search-field :deep(.v-field) {
    border-radius: 12px;
    background: rgba(var(--v-theme-primary), 0.03);
    border: 1px solid rgba(var(--v-theme-primary), 0.1);
  }

  /* Scrollbars */
  .chat-users-scroll::-webkit-scrollbar,
  .chat-messages-scroll::-webkit-scrollbar {
    width: 6px;
  }

  .chat-users-scroll::-webkit-scrollbar-track,
  .chat-messages-scroll::-webkit-scrollbar-track {
    background: rgba(var(--v-theme-primary), 0.04);
    border-radius: 3px;
  }

  .chat-users-scroll::-webkit-scrollbar-thumb,
  .chat-messages-scroll::-webkit-scrollbar-thumb {
    background: rgba(var(--v-theme-primary), 0.2);
    border-radius: 3px;
  }

  .chat-users-scroll::-webkit-scrollbar-thumb:hover,
  .chat-messages-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(var(--v-theme-primary), 0.3);
  }

  /* Badge */
  .message-badge :deep(.v-badge__badge) {
    box-shadow: 0 0 0 2px rgb(var(--v-theme-surface));
    font-size: 0.65rem;
    min-width: 18px;
    height: 18px;
    font-weight: 600;
  }

  /* Typography */
  .text-xxs {
    font-size: 0.7rem;
  }

  .user-name {
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  /* Responsive */
  @media (max-width: 600px) {
    .chat-menu {
      min-width: 95vw !important;
      max-width: 95vw !important;
    }

    .chat-users-scroll,
    .chat-messages-scroll {
      max-height: 250px;
    }
    
    .chat-message-bubble {
      max-width: 85%;
    }
  }
</style>