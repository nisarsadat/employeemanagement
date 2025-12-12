<template>
  <v-menu
    v-model="menuOpen"
    location="bottom end"
    :close-on-content-click="false"
    transition="slide-y-transition"
    content-class="chat-menu"
    :max-width="950"
    min-width="850"
    rounded="xl"
  >
    <!-- 💬 CHAT ICON -->
    <template #activator="{ props }">
      <v-badge
        :content="unreadCount"
        :model-value="unreadCount > 0"
        color="error"
        overlap
        location="top end"
        offset-x="6"
        offset-y="6"
        bordered
        class="chat-badge"
      >
        <v-btn
          v-bind="props"
          icon
          size="default"
          variant="tonal"
          class="chat-activator"
          width="44"
          height="44"
          :loading="repo.bootLoading"
          @click="onActivatorClick"
        >
          <v-icon size="22" color="primary">mdi-forum</v-icon>
        </v-btn>
      </v-badge>
    </template>

    <!-- 🧊 PANEL -->
    <v-card class="chat-card" rounded="xl">
      <!-- HEADER -->
      <div class="chat-header" :dir="chatDir">
        <div class="d-flex align-center gap-3">
          <div class="header-icon">
            <v-icon size="26" color="white">mdi-forum</v-icon>
          </div>
          <div class="header-content">
            <div class="title">{{ tr('chat.title') }}</div>
            <div class="subtitle">{{ tr('chat.subtitle') }}</div>
          </div>
        </div>

        <div class="header-actions">
          <!-- Language Selector (ONLY INSIDE CHAT) -->
          <v-menu location="bottom end" offset="5">
            <template #activator="{ props: menuProps }">
              <v-btn v-bind="menuProps" icon variant="text" size="small" class="action-btn">
                <v-icon color="white">mdi-translate</v-icon>
                <v-tooltip activator="parent" location="bottom">
                  {{ tr('chat.changeLanguage') }}
                </v-tooltip>
              </v-btn>
            </template>
            <v-list density="compact" class="language-list">
              <v-list-item
                v-for="lang in languages"
                :key="lang.code"
                @click="changeLanguage(lang.code)"
                :class="{ active: chatLocale === lang.code }"
              >
                <template #prepend>
                  <v-icon size="20" color="primary">{{ lang.icon }}</v-icon>
                </template>
                <v-list-item-title>{{ lang.name }}</v-list-item-title>
                <template #append>
                  <v-icon v-if="chatLocale === lang.code" size="18" color="primary">mdi-check</v-icon>
                </template>
              </v-list-item>
            </v-list>
          </v-menu>

          <!-- Sound Toggle -->
          <v-btn icon variant="text" size="small" class="action-btn" @click="toggleSound">
            <v-icon color="white">{{ soundEnabled ? 'mdi-volume-high' : 'mdi-volume-off' }}</v-icon>
            <v-tooltip activator="parent" location="bottom">
              {{ soundEnabled ? tr('chat.muteSound') : tr('chat.unmuteSound') }}
            </v-tooltip>
          </v-btn>

          <!-- Notifications Toggle -->
          <v-btn icon variant="text" size="small" class="action-btn" @click="toggleNotifications">
            <v-icon color="white">{{ notificationsEnabled ? 'mdi-bell' : 'mdi-bell-off' }}</v-icon>
            <v-tooltip activator="parent" location="bottom">
              {{ notificationsEnabled ? tr('chat.muteNotifications') : tr('chat.unmuteNotifications') }}
            </v-tooltip>
          </v-btn>

          <!-- Mark All as Read -->
          <v-btn
            icon
            variant="text"
            size="small"
            class="action-btn"
            @click="markAllAsRead"
            :disabled="unreadCount === 0"
          >
            <v-icon color="white">mdi-check-all</v-icon>
            <v-tooltip activator="parent" location="bottom">
              {{ tr('chat.markAllRead') }}
            </v-tooltip>
          </v-btn>

          <!-- Refresh -->
          <v-btn
            icon
            variant="text"
            size="small"
            class="action-btn"
            :loading="repo.silentLoading"
            @click="repo.refreshLists()"
          >
            <v-icon color="white">mdi-refresh</v-icon>
            <v-tooltip activator="parent" location="bottom">
              {{ tr('chat.refresh') }}
            </v-tooltip>
          </v-btn>

          <!-- Close -->
          <v-btn icon variant="text" size="small" class="action-btn" @click="menuOpen = false">
            <v-icon color="white">mdi-close</v-icon>
            <v-tooltip activator="parent" location="bottom">
              {{ tr('chat.close') }}
            </v-tooltip>
          </v-btn>
        </div>
      </div>

      <!-- BODY -->
      <div class="chat-body" :dir="chatDir">
        <!-- LEFT: USERS -->
        <div class="chat-left">
          <div class="search-container">
            <v-text-field
              v-model="searchQuery"
              density="compact"
              variant="outlined"
              hide-details
              :placeholder="tr('chat.searchContacts')"
              prepend-inner-icon="mdi-magnify"
              class="users-search"
              clearable
              @click:clear="searchQuery = ''"
              rounded
              single-line
              :dir="chatDir"
            >
              <template #append-inner>
                <v-btn
                  icon
                  size="small"
                  variant="text"
                  class="search-btn"
                  :disabled="!searchQuery"
                  @click="searchQuery = ''"
                >
                  <v-icon size="20">mdi-close</v-icon>
                </v-btn>
              </template>
            </v-text-field>
          </div>

          <div class="users-scroll thin-scroll">
            <v-list density="compact" class="py-0">
              <v-list-item
                v-for="user in filteredUsers"
                :key="user.id"
                rounded="lg"
                class="user-item"
                :active="repo.activeUser?.id === user.id"
                :active-class="getUserActiveClass(user)"
                @click="selectUser(user)"
              >
                <template #prepend>
                  <div class="avatar-wrap">
                    <v-avatar size="48" class="avatar">
                      <img v-if="user.image_url" :src="user.image_url" :alt="user.name" />
                      <v-icon v-else size="48" :color="getIconColor(user)">mdi-account-circle</v-icon>
                      <div class="status-dot-avatar" :class="{ online: !!user.isOnline }"></div>
                    </v-avatar>
                  </div>
                </template>

                <div class="user-info">
                  <div class="user-top">
                    <div class="name-section">
                      <v-list-item-title class="name">{{ user.name }}</v-list-item-title>
                      <v-icon v-if="isUserMuted(user.id)" size="14" color="error" class="mute-icon">
                        mdi-volume-off
                      </v-icon>
                    </div>

                    <div class="time" v-if="getLastMessageTime(user)">
                      {{ formatRelativeTime(getLastMessageTime(user)) }}
                    </div>
                  </div>

                  <div class="message-preview">
                    <v-list-item-subtitle class="message-text">
                      {{ getLastMessagePreview(user) }}
                    </v-list-item-subtitle>

                    <div v-if="conversationUnreadByUser(user.id) > 0" class="unread-indicator">
                      {{ conversationUnreadByUser(user.id) }}
                    </div>
                  </div>
                </div>
              </v-list-item>

              <div v-if="!filteredUsers.length" class="empty-left">
                <v-icon size="56" color="grey-lighten-1">mdi-account-search-outline</v-icon>
                <div class="empty-text">{{ tr('chat.noContacts') }}</div>
                <v-btn variant="text" color="primary" size="small" @click="searchQuery = ''" class="reset-search-btn">
                  {{ tr('chat.resetSearch') }}
                </v-btn>
              </div>
            </v-list>
          </div>
        </div>

        <!-- RIGHT: CHAT -->
        <div class="chat-right" v-if="repo.hasActiveConversation">
          <div class="chat-right-header" :dir="chatDir">
            <div class="active-user-info">
              <v-avatar size="46" class="active-user-avatar">
                <img
                  v-if="repo.activeUser.image_url"
                  :src="repo.activeUser.image_url"
                  :alt="repo.activeUser.name"
                />
                <v-icon v-else size="46">mdi-account-circle</v-icon>
                <div class="active-status-dot" :class="{ online: !!repo.activeUser.isOnline }"></div>
              </v-avatar>

              <div class="active-user-details">
                <div class="active-user-name">{{ repo.activeUser.name }}</div>
              </div>
            </div>

            <div class="header-actions-right">
              <v-menu location="bottom end" offset="8" :close-on-content-click="true">
                <template #activator="{ props: dotProps }">
                  <v-btn v-bind="dotProps" icon variant="text" size="small">
                    <v-icon>mdi-dots-vertical</v-icon>
                    <v-tooltip activator="parent" location="bottom">
                      {{ tr('chat.moreActions') }}
                    </v-tooltip>
                  </v-btn>
                </template>

                <v-list density="compact">
                  <v-list-item @click="clearChat">
                    <template #prepend><v-icon size="20" color="error">mdi-delete</v-icon></template>
                    <v-list-item-title>{{ tr('chat.clearChat') }}</v-list-item-title>
                  </v-list-item>

                  <v-list-item @click="toggleMuteUser(repo.activeUser.id)">
                    <template #prepend>
                      <v-icon size="20" :color="isUserMuted(repo.activeUser.id) ? 'error' : 'grey'">
                        {{ isUserMuted(repo.activeUser.id) ? 'mdi-volume-off' : 'mdi-volume-high' }}
                      </v-icon>
                    </template>
                    <v-list-item-title>
                      {{ isUserMuted(repo.activeUser.id) ? tr('chat.unmuteUser') : tr('chat.muteUser') }}
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </div>
          </div>

          <div
            ref="messagesBox"
            class="messages-box thin-scroll"
            @scroll="handleScroll"
            :dir="chatDir"
          >
            <div v-if="repo.messagesLoading" class="loading-messages">
              <v-progress-circular indeterminate size="24" color="primary" />
              <span>{{ tr('chat.loading') }}</span>
            </div>

            <div v-if="showWelcome" class="welcome-message">
              <v-avatar size="68" class="welcome-avatar">
                <img
                  v-if="repo.activeUser.image_url"
                  :src="repo.activeUser.image_url"
                  :alt="repo.activeUser.name"
                />
                <v-icon v-else size="68">mdi-account-circle</v-icon>
              </v-avatar>
              <div class="welcome-content">
                <div class="welcome-title">{{ tr('chat.conversationStarted') }}</div>
                <div class="welcome-subtitle">{{ tr('chat.youCanNowChat') }}</div>
                <div class="welcome-time">{{ formatDate(new Date()) }}</div>
              </div>
            </div>

            <div
              v-for="(messageGroup, groupIndex) in groupedMessages"
              :key="groupIndex"
              class="message-group"
            >
              <div v-if="shouldShowDateSeparator(messageGroup)" class="date-separator">
                <span>{{ formatGroupDate(messageGroup[0].created_at) }}</span>
              </div>

              <div
                v-for="message in messageGroup"
                :key="message.id"
                class="message-wrapper"
                :data-message-id="message.id"
                :class="{
                  sent: message.sender?.id === repo.currentUser?.id,
                  received: message.sender?.id !== repo.currentUser?.id,
                }"
              >
                <div
                  v-if="message.sender?.id !== repo.currentUser?.id && isFirstInGroup(messageGroup, message)"
                  class="sender-avatar"
                >
                  <v-avatar size="36">
                    <img
                      v-if="message.sender?.image_url"
                      :src="message.sender.image_url"
                      :alt="message.sender?.name"
                    />
                    <v-icon v-else size="36">mdi-account-circle</v-icon>
                  </v-avatar>
                </div>
                <div v-else class="avatar-spacer"></div>

                <div class="message-bubble-wrapper">
                  <div
                    v-if="message.sender?.id !== repo.currentUser?.id && isFirstInGroup(messageGroup, message)"
                    class="sender-name"
                  >
                    {{ message.sender?.name }}
                  </div>

                  <div v-if="message.replyTo" class="reply-indicator" @click="scrollToMessage(message.replyTo.id)">
                    <v-icon size="14">mdi-reply</v-icon>
                    <span class="reply-text">
                      {{ tr('chat.replyingTo') }} {{ getMessageSender(message.replyTo) }}
                    </span>
                  </div>

                  <div
                    class="message-bubble"
                    :class="{
                      first: isFirstInGroup(messageGroup, message),
                      last: isLastInGroup(messageGroup, message),
                      'has-reply': message.replyTo,
                    }"
                  >
                    <div v-if="message.replyTo" class="reply-quote" @click="scrollToMessage(message.replyTo.id)">
                      <div class="reply-quote-top">
                        <v-icon size="14">mdi-reply</v-icon>
                        <span class="reply-quote-name">{{ getMessageSender(message.replyTo) }}</span>
                      </div>
                      <div class="reply-quote-body">
                        {{ truncateText(message.replyTo.body || '', 80) }}
                      </div>
                    </div>

                    <div class="message-content">
                      <div
                        class="message-text"
                        :class="{
                          'long-text': isLongMessage(message.body),
                          expanded: !!message.expanded,
                        }"
                      >
                        {{ message.body }}
                      </div>

                      <v-btn
                        v-if="isLongMessage(message.body) && !message.expanded"
                        variant="text"
                        size="x-small"
                        class="read-more-btn"
                        @click="toggleMessageExpand(message)"
                      >
                        {{ tr('chat.readMore') }}
                        <v-icon size="16">mdi-chevron-down</v-icon>
                      </v-btn>
                    </div>

                    <div class="message-meta">
                      <span class="message-time">{{ formatTime(message.created_at) }}</span>

                      <div class="message-status">
                        <v-icon
                          v-if="message.sender?.id === repo.currentUser?.id"
                          size="16"
                          :color="getMessageStatusColor(message)"
                          class="status-icon"
                          :title="getMessageStatusText(message)"
                        >
                          {{ getMessageStatusIcon(message) }}
                        </v-icon>

                        <v-btn
                          icon
                          size="x-small"
                          variant="text"
                          class="reply-btn"
                          @click="startReply(message)"
                        >
                          <v-icon size="14" color="grey">mdi-reply</v-icon>
                          <v-tooltip activator="parent" location="top">
                            {{ tr('chat.reply') }}
                          </v-tooltip>
                        </v-btn>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div ref="bottomAnchor"></div>
          </div>

          <div v-if="replyToMessage" class="reply-preview" :dir="chatDir">
            <div class="reply-preview-content">
              <div class="reply-header">
                <v-icon size="16" color="primary">mdi-reply</v-icon>
                <span class="reply-title">
                  {{ tr('chat.replyingTo') }} {{ getMessageSender(replyToMessage) }}
                </span>
                <v-btn icon size="x-small" variant="text" @click="cancelReply" class="cancel-reply-btn">
                  <v-icon size="16">mdi-close</v-icon>
                </v-btn>
              </div>
              <div class="reply-text">{{ truncateText(replyToMessage.body, 50) }}</div>
            </div>
          </div>

          <div class="composer" :dir="chatDir">
            <v-text-field
              ref="messageInput"
              v-model="draft"
              density="comfortable"
              variant="outlined"
              hide-details
              :placeholder="replyToMessage ? tr('chat.typeReply') : tr('chat.typeMessage')"
              class="composer-input"
              @keydown.enter.stop.prevent="send"
              rounded
              single-line
              :append-inner-icon="draft.trim() ? 'mdi-send' : null"
              @click:append-inner="send"
            />

            <v-btn
              icon
              color="primary"
              class="send-btn"
              :loading="repo.sending"
              :disabled="!draft.trim()"
              @click="send"
              size="large"
            >
              <v-icon>mdi-send</v-icon>
            </v-btn>
          </div>
        </div>

        <div v-else class="chat-empty" :dir="chatDir">
          <div class="empty-content">
            <div class="empty-icon">
              <v-icon size="90" color="primary-lighten-2">mdi-chat-outline</v-icon>
            </div>
            <div class="empty-title">{{ tr('chat.welcome') }}</div>
            <div class="empty-subtitle">{{ tr('chat.selectContact') }}</div>
          </div>
        </div>
      </div>
    </v-card>
  </v-menu>
</template>

<script setup>
import { computed, nextTick, ref, watch, onMounted } from 'vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import isToday from 'dayjs/plugin/isToday';
import isYesterday from 'dayjs/plugin/isYesterday';
import 'dayjs/locale/fa';
import 'dayjs/locale/en';
import { useChatRepository } from '@/stores/ChatRepository';

dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);
dayjs.extend(isToday);
dayjs.extend(isYesterday);

const repo = useChatRepository();

/* =========================================================
   LOCAL (IN-COMPONENT) I18N ONLY — DOES NOT TOUCH APP I18N
========================================================= */
const chatLocale = ref('en'); // en | fa | ps
const chatDir = computed(() => (chatLocale.value === 'fa' || chatLocale.value === 'ps' ? 'rtl' : 'ltr'));

const DICT = {
  en: {
    chat: {
      title: 'Chat',
      subtitle: 'Messages & contacts',
      changeLanguage: 'Change language',
      muteSound: 'Mute sound',
      unmuteSound: 'Unmute sound',
      muteNotifications: 'Mute notifications',
      unmuteNotifications: 'Unmute notifications',
      markAllRead: 'Mark all as read',
      refresh: 'Refresh',
      close: 'Close',
      searchContacts: 'Search contacts...',
      noContacts: 'No contacts found',
      resetSearch: 'Reset search',
      moreActions: 'More actions',
      clearChat: 'Clear chat',
      confirmClearChat: 'Are you sure you want to clear this chat?',
      loading: 'Loading...',
      conversationStarted: 'Conversation started',
      youCanNowChat: 'You can now chat with this contact',
      today: 'Today',
      yesterday: 'Yesterday',
      justNow: 'Just now',
      replyingTo: 'Replying to',
      reply: 'Reply',
      you: 'You',
      typeMessage: 'Type a message...',
      typeReply: 'Type your reply...',
      readMore: 'Read more',
      sent: 'Sent',
      seen: 'Seen',
      unmuteUser: 'Unmute user',
      muteUser: 'Mute user',
      welcome: 'Welcome',
      selectContact: 'Select a contact to start chatting',
      noMessages: 'No messages yet',
      newMessage: 'New message',
    },
  },
  fa: {
    chat: {
      title: 'چت',
      subtitle: 'پیام‌ها و مخاطبین',
      changeLanguage: 'تغییر زبان',
      muteSound: 'بی‌صدا کردن صدا',
      unmuteSound: 'فعال کردن صدا',
      muteNotifications: 'بی‌صدا کردن اعلان‌ها',
      unmuteNotifications: 'فعال کردن اعلان‌ها',
      markAllRead: 'خواندن همه',
      refresh: 'تازه‌سازی',
      close: 'بستن',
      searchContacts: 'جستجوی مخاطبین...',
      noContacts: 'مخاطبی پیدا نشد',
      resetSearch: 'پاک کردن جستجو',
      moreActions: 'گزینه‌های بیشتر',
      clearChat: 'پاک کردن چت',
      confirmClearChat: 'آیا مطمئن هستید که چت پاک شود؟',
      loading: 'در حال بارگذاری...',
      conversationStarted: 'گفتگو شروع شد',
      youCanNowChat: 'اکنون می‌توانید پیام بدهید',
      today: 'امروز',
      yesterday: 'دیروز',
      justNow: 'همین حالا',
      replyingTo: 'پاسخ به',
      reply: 'پاسخ',
      you: 'شما',
      typeMessage: 'پیام بنویسید...',
      typeReply: 'پاسخ بنویسید...',
      readMore: 'بیشتر',
      sent: 'ارسال شد',
      seen: 'دیده شد',
      unmuteUser: 'فعال کردن صدا',
      muteUser: 'بی‌صدا کردن',
      welcome: 'خوش آمدید',
      selectContact: 'برای شروع، یک مخاطب را انتخاب کنید',
      noMessages: 'هنوز پیامی نیست',
      newMessage: 'پیام جدید',
    },
  },
  ps: {
    chat: {
      title: 'چټ',
      subtitle: 'پیغامونه او اړیکې',
      changeLanguage: 'ژبه بدلول',
      muteSound: 'غږ بندول',
      unmuteSound: 'غږ فعالول',
      muteNotifications: 'خبرتیاوې بندول',
      unmuteNotifications: 'خبرتیاوې فعالول',
      markAllRead: 'ټول لوستل',
      refresh: 'ریفریش',
      close: 'بندول',
      searchContacts: 'اړیکې ولټوه...',
      noContacts: 'اړیکه ونه موندل شوه',
      resetSearch: 'لټون پاکول',
      moreActions: 'نور',
      clearChat: 'چټ پاکول',
      confirmClearChat: 'ایا ډاډه یاست چې چټ پاک کړئ؟',
      loading: 'لوډېږي...',
      conversationStarted: 'خبرې پیل شوې',
      youCanNowChat: 'اوس کولی شئ پیغام واستوئ',
      today: 'نن',
      yesterday: 'پرون',
      justNow: 'همدا اوس',
      replyingTo: 'ځواب',
      reply: 'ځواب',
      you: 'ته',
      typeMessage: 'پیغام ولیکئ...',
      typeReply: 'ځواب ولیکئ...',
      readMore: 'نور',
      sent: 'لېږل شو',
      seen: 'ولیدل شو',
      unmuteUser: 'غږ فعالول',
      muteUser: 'غږ بندول',
      welcome: 'ښه راغلاست',
      selectContact: 'د چټ لپاره یو کس وټاکئ',
      noMessages: 'تر اوسه پیغام نشته',
      newMessage: 'نوی پیغام',
    },
  },
};

const tr = (key, vars = {}) => {
  const [ns, k] = key.split('.');
  const base = DICT[chatLocale.value]?.[ns]?.[k] ?? DICT.en?.[ns]?.[k] ?? key;
  return base.replace(/\{(\w+)\}/g, (_, v) => vars[v] ?? `{${v}}`);
};
/* ========================================================= */

const menuOpen = ref(false);
const searchQuery = ref('');
const draft = ref('');
const messagesBox = ref(null);
const messageInput = ref(null);
const isNearBottom = ref(true);
const showWelcome = ref(true);
const replyToMessage = ref(null);
let autoScrollEnabled = true;

// settings (NO localStorage)
const soundEnabled = ref(true);
const notificationsEnabled = ref(true);
const mutedUsers = ref([]);

// ✅ local sound file (NO beep/oscillator)
const NOTIFY_SOUND_SRC = '/sounds/notify.mp3';
let notifyAudio = null;

const ensureAudio = () => {
  if (!notifyAudio) {
    notifyAudio = new Audio(NOTIFY_SOUND_SRC);
    notifyAudio.preload = 'auto';
    notifyAudio.volume = 0.7;
  }
};

const playNotifySound = async (incomingUserId) => {
  if (!soundEnabled.value) return;
  if (incomingUserId && mutedUsers.value.includes(incomingUserId)) return;

  try {
    ensureAudio();
    notifyAudio.currentTime = 0;
    const p = notifyAudio.play();
    if (p && typeof p.catch === 'function') p.catch(() => {});
  } catch {}
};

// ✅ Web Notification (real notify)
const ensureNotificationPermission = async () => {
  try {
    if (!('Notification' in window)) return 'unsupported';
    if (Notification.permission === 'granted') return 'granted';
    if (Notification.permission === 'denied') return 'denied';
    const perm = await Notification.requestPermission();
    return perm;
  } catch {
    return 'error';
  }
};

const showWebNotification = async ({ title, body, icon, onClick }) => {
  if (!notificationsEnabled.value) return;
  if (!('Notification' in window)) return;

  const perm = await ensureNotificationPermission();
  if (perm !== 'granted') return;

  try {
    const n = new Notification(title, { body, icon });
    n.onclick = () => {
      try {
        window.focus?.();
        onClick?.();
        n.close?.();
      } catch {}
    };
  } catch {}
};

const onActivatorClick = async () => {
  // ✅ this user gesture helps audio play later without being blocked
  try {
    ensureAudio();
    await ensureNotificationPermission();
  } catch {}
};

// Language options
const languages = [
  { code: 'en', name: 'English', icon: 'mdi-translate' },
  { code: 'fa', name: 'فارسی', icon: 'mdi-translate' },
  { code: 'ps', name: 'پښتو', icon: 'mdi-translate' },
];

const filteredUsers = computed(() => {
  const term = searchQuery.value.trim().toLowerCase();
  if (!term) return repo.users;

  return repo.users.filter(
    (user) =>
      (user.name || '').toLowerCase().includes(term) ||
      (user.email || '').toLowerCase().includes(term) ||
      (getLastMessagePreview(user) || '').toLowerCase().includes(term)
  );
});

const unreadCount = computed(() => {
  return repo.conversations.reduce((total, conversation) => total + (conversation.unread_count || 0), 0);
});

// Group messages by sender and time
const groupedMessages = computed(() => {
  const groups = [];
  let currentGroup = [];
  let lastSenderId = null;
  let lastMessageTime = null;

  repo.messages.forEach((message, index) => {
    const messageTime = dayjs(message.created_at);
    const timeDiff = lastMessageTime ? Math.abs(messageTime.diff(lastMessageTime, 'minute')) : 30;

    if (message.sender?.id !== lastSenderId || timeDiff > 5 || index === 0) {
      if (currentGroup.length > 0) groups.push([...currentGroup]);
      currentGroup = [message];
    } else {
      currentGroup.push(message);
    }

    lastSenderId = message.sender?.id;
    lastMessageTime = messageTime;
  });

  if (currentGroup.length > 0) groups.push(currentGroup);
  return groups;
});

// ✅ NOTIFY + SOUND ONLY when NEW INCOMING message comes (not when I send)
watch(
  () => repo.lastIncomingMessage,
  async (msg) => {
    if (!msg?.id) return;

    // do not notify if muted
    const senderId = msg?.sender?.id || msg?.sender_id || null;
    if (senderId && mutedUsers.value.includes(senderId)) return;

    // ✅ sound
    await playNotifySound(senderId);

    // ✅ show notification (only if menu closed OR tab hidden)
    const shouldNotify = !menuOpen.value || document.visibilityState !== 'visible';
    if (!shouldNotify) return;

    const senderName = msg?.sender?.name || tr('chat.newMessage');
    const body = (msg?.body || '').toString();
    const icon = msg?.sender?.image_url || undefined;

    await showWebNotification({
      title: senderName,
      body: body.length > 120 ? body.slice(0, 120) + '...' : body,
      icon,
      onClick: async () => {
        // open chat + select sender
        menuOpen.value = true;
        await repo.bootstrap();
        const u = repo.users.find((x) => String(x.id) === String(senderId));
        if (u) await selectUser(u);
      },
    });
  }
);

watch(menuOpen, async (open) => {
  if (open) {
    await repo.bootstrap();
    await nextTick();
    scrollToBottom();
  } else {
    repo.closeChatSession();
    searchQuery.value = '';
    draft.value = '';
    replyToMessage.value = null;
  }
});

watch(
  () => repo.activeUser,
  async (user) => {
    if (user) {
      showWelcome.value = true;
      setTimeout(() => (showWelcome.value = false), 3000);
    }
    await nextTick();
    scrollToBottom();
  }
);

watch(
  () => repo.messages.length,
  () => {
    nextTick(() => {
      if (isNearBottom.value && autoScrollEnabled) scrollToBottom();
    });
  }
);

const selectUser = async (user) => {
  await repo.openConversationWith(user);
  await nextTick();
  scrollToBottom();
};

const send = async () => {
  const text = draft.value.trim();
  if (!text || !repo.activeUser || repo.sending) return;

  autoScrollEnabled = true;

  const messageData = { body: text, replyTo: replyToMessage.value?.id };
  await repo.sendMessage(text, messageData);

  draft.value = '';
  replyToMessage.value = null;
  await nextTick();
  scrollToBottom();
};

const handleScroll = () => {
  if (!messagesBox.value) return;

  const el = messagesBox.value;
  const distanceFromBottom = el.scrollHeight - el.scrollTop - el.clientHeight;
  isNearBottom.value = distanceFromBottom < 100;

  if (el.scrollTop < 100 && !repo.messagesLoading && repo.hasMoreMessages) {
    repo.loadMoreMessages();
  }
};

const scrollToBottom = () => {
  if (!messagesBox.value) return;

  if (autoScrollEnabled) {
    requestAnimationFrame(() => {
      messagesBox.value?.scrollTo({ top: messagesBox.value.scrollHeight, behavior: 'smooth' });
    });
  }
};

const scrollToMessage = (messageId) => {
  const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
  if (messageElement) {
    messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    messageElement.classList.add('highlighted');
    setTimeout(() => messageElement.classList.remove('highlighted'), 2000);
  }
};

const startReply = (message) => {
  replyToMessage.value = message;
  messageInput.value?.focus?.();
};

const cancelReply = () => {
  replyToMessage.value = null;
};

const toggleSound = () => {
  soundEnabled.value = !soundEnabled.value;
};

const toggleNotifications = async () => {
  notificationsEnabled.value = !notificationsEnabled.value;
  if (notificationsEnabled.value) {
    await ensureNotificationPermission();
  }
};

const toggleMuteUser = (userId) => {
  const index = mutedUsers.value.indexOf(userId);
  if (index > -1) mutedUsers.value.splice(index, 1);
  else mutedUsers.value.push(userId);
};

const isUserMuted = (userId) => mutedUsers.value.includes(userId);

const markAllAsRead = async () => {
  await repo.markAllAsRead();
};

const changeLanguage = (langCode) => {
  chatLocale.value = langCode;
  if (langCode === 'fa' || langCode === 'ps') dayjs.locale('fa');
  else dayjs.locale('en');
};

const clearChat = async () => {
  if (!repo.activeConversationId) return;
  if (!confirm(tr('chat.confirmClearChat'))) return;
  await repo.clearChat(repo.activeConversationId);
};

const truncateText = (text, length) => {
  if (!text) return '';
  if (text.length <= length) return text;
  return text.substring(0, length) + '...';
};

const getMessageSender = (message) => {
  return message.sender?.id === repo.currentUser?.id ? tr('chat.you') : message.sender?.name;
};

const isFirstInGroup = (group, message) => group[0] === message;
const isLastInGroup = (group, message) => group[group.length - 1] === message;

const shouldShowDateSeparator = (group) => {
  if (!group || group.length === 0) return false;
  const message = group[0];
  const prevGroup = groupedMessages.value[groupedMessages.value.indexOf(group) - 1];
  if (!prevGroup) return true;

  const prevMessage = prevGroup[prevGroup.length - 1];
  const currentDate = dayjs(message.created_at).format('YYYY-MM-DD');
  const prevDate = dayjs(prevMessage.created_at).format('YYYY-MM-DD');
  return currentDate !== prevDate;
};

const isLongMessage = (text) => text && text.length > 200;

const toggleMessageExpand = (message) => {
  if (!message.expanded) message.expanded = true;
  else delete message.expanded;
};

const getMessageStatusIcon = (message) => {
  if (message.seen_at) return 'mdi-check-all';
  return 'mdi-check';
};

const getMessageStatusColor = (message) => {
  if (message.seen_at) return 'primary';
  return 'grey';
};

const getMessageStatusText = (message) => {
  if (message.seen_at) return tr('chat.seen');
  return tr('chat.sent');
};

const getUserActiveClass = () => 'user-item-active';
const getIconColor = (user) => (user?.isOnline ? 'primary' : 'grey');

const formatTime = (iso) => (iso ? dayjs(iso).format('HH:mm') : '');

const formatRelativeTime = (iso) => {
  if (!iso) return '';
  const now = dayjs();
  const time = dayjs(iso);
  if (now.diff(time, 'minute') < 1) return tr('chat.justNow');
  return time.fromNow();
};

const formatDate = (date) => dayjs(date).format('dddd, MMMM D, YYYY');

const formatGroupDate = (iso) => {
  if (!iso) return '';
  const date = dayjs(iso);
  if (date.isToday()) return tr('chat.today');
  if (date.isYesterday()) return tr('chat.yesterday');
  return date.format('MMMM D, YYYY');
};

const getLastMessageTime = (user) => {
  const conversation = repo.conversations.find((c) => c.other_user?.id === user.id);
  return conversation?.last_message?.created_at;
};

const getLastMessagePreview = (user) => {
  const conversation = repo.conversations.find((c) => c.other_user?.id === user.id);
  const text = conversation?.last_message?.body || '';
  if (text.length > 35) return text.substring(0, 35) + '...';
  return text || tr('chat.noMessages');
};

const conversationUnreadByUser = (userId) => {
  const c = repo.conversations.find((c) => c.other_user?.id === userId);
  return c?.unread_count || 0;
};

onMounted(async () => {
  chatLocale.value = 'en';
  dayjs.locale('en');

  // preload audio + permission (best effort)
  try {
    ensureAudio();
    await ensureNotificationPermission();
  } catch {}
});
</script>

<style scoped>
/* ✅ SAME STYLE YOU SENT (UNCHANGED) */
.chat-card,
.chat-card * {
  font-family: inherit !important;
}
.chat-card {
  height: 720px;
  width: 900px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 10px 20px rgba(0, 0, 0, 0.1);
  background: #ffffff;
}
.chat-header {
  padding: 16px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  flex-shrink: 0;
}
.header-icon {
  background: rgba(255, 255, 255, 0.2);
  padding: 10px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
}
.header-content {
  margin-left: 12px;
}
[dir='rtl'] .header-content {
  margin-left: 0;
  margin-right: 12px;
}
.title {
  font-weight: 700;
  font-size: 20px;
  letter-spacing: 0.3px;
}
.subtitle {
  font-size: 12px;
  opacity: 0.9;
  margin-top: 2px;
}
.header-actions {
  display: flex;
  gap: 4px;
  align-items: center;
}
.header-actions .action-btn {
  color: white;
}
.language-list {
  max-height: 300px;
  overflow-y: auto;
}
.language-list .active {
  background: rgba(102, 126, 234, 0.1);
}
.chat-body {
  flex: 1;
  display: grid;
  grid-template-columns: 320px 1fr;
  height: calc(100% - 81px);
  background: #f8fafc;
  overflow: hidden;
}
.chat-left {
  width: 320px;
  min-width: 320px;
  max-width: 320px;
  border-right: 1px solid rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  background: white;
  overflow: hidden;
}
.search-container {
  padding: 16px;
  background: white;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  flex-shrink: 0;
}
.users-search :deep(.v-field) {
  border-radius: 12px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
  height: 44px;
}
.users-search :deep(.v-field):hover,
.users-search :deep(.v-field):focus-within {
  border-color: #667eea;
  background: white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
}
.search-btn {
  margin-right: 4px;
}
[dir='rtl'] .search-btn {
  margin-right: 0;
  margin-left: 4px;
}
.users-scroll {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 8px 12px;
}
.user-item {
  margin: 4px 0;
  padding: 12px;
  border-radius: 12px;
  transition: all 0.3s ease;
  cursor: pointer;
  border: 2px solid transparent;
}
.user-item:hover {
  background: #f8fafc;
  transform: translateX(2px);
}
[dir='rtl'] .user-item:hover {
  transform: translateX(-2px);
}
.user-item-active {
  background: #f1f5f9;
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}
.avatar-wrap {
  position: relative;
}
.avatar {
  position: relative;
}
.status-dot-avatar {
  position: absolute;
  bottom: 12px;
  right: 0px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
  background: #94a3b8;
  z-index: 4;
}
.status-dot-avatar.online {
  background: #10b981;
}
[dir='rtl'] .status-dot-avatar {
  right: auto;
  left: 0px;
}
.user-info {
  flex: 1;
  min-width: 0;
  margin-left: 12px;
}
[dir='rtl'] .user-info {
  margin-left: 0;
  margin-right: 12px;
}
.user-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 6px;
}
.name-section {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
  min-width: 0;
}
.name {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.mute-icon {
  flex-shrink: 0;
}
.time {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 500;
  white-space: nowrap;
  margin-left: 4px;
}
[dir='rtl'] .time {
  margin-left: 0;
  margin-right: 4px;
}
.message-preview {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
}
.message-text {
  font-size: 13px;
  color: #64748b;
  font-weight: 400;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
  min-width: 0;
}
.unread-indicator {
  background: #667eea;
  color: white;
  font-size: 11px;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 6px;
  flex-shrink: 0;
}
.empty-left {
  padding: 60px 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  align-items: center;
  text-align: center;
}
.empty-text {
  font-size: 14px;
  color: #94a3b8;
  font-weight: 500;
}
.chat-right {
  min-width: 0;
  display: flex;
  flex-direction: column;
  background: white;
  position: relative;
  overflow: hidden;
}
.chat-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: #f8fafc;
}
.empty-content {
  text-align: center;
  max-width: 400px;
}
.chat-right-header {
  padding: 16px 24px;
  background: white;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.active-user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 0;
}
.active-user-avatar {
  position: relative;
  flex-shrink: 0;
}
.active-status-dot {
  position: absolute;
  bottom: 3px;
  right: 4px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid white;
  background: #94a3b8;
  z-index: 2;
}
.active-status-dot.online {
  background: #10b981;
}
[dir='rtl'] .active-status-dot {
  right: auto;
  left: 0px;
}
.active-user-details {
  flex: 1;
  min-width: 0;
}
.active-user-name {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.messages-box {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 24px;
  background: #f8fafc;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.message-wrapper {
  display: flex;
  gap: 8px;
  align-items: flex-end;
  max-width: 100%;
}
.message-wrapper.sent {
  margin-left: auto;
  flex-direction: row-reverse;
}
.message-wrapper.received {
  margin-right: auto;
}
.message-bubble-wrapper {
  display: flex;
  flex-direction: column;
  max-width: 70%;
}
.message-bubble {
  padding: 12px 16px;
  border-radius: 18px;
  position: relative;
  max-width: 100%;
  word-wrap: break-word;
  word-break: break-word;
}
.reply-quote {
  cursor: pointer;
  margin-bottom: 8px;
  padding: 8px 10px;
  border-radius: 12px;
  background: rgba(0, 0, 0, 0.06);
  border-left: 4px solid rgba(102, 126, 234, 0.9);
}
[dir='rtl'] .reply-quote {
  border-left: none;
  border-right: 4px solid rgba(102, 126, 234, 0.9);
}
.reply-quote-top {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  opacity: 0.9;
}
.reply-quote-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.reply-quote-body {
  margin-top: 4px;
  font-size: 12px;
  opacity: 0.85;
  line-height: 1.35;
}
.message-wrapper.received .message-bubble {
  background: #f1f5f9;
  color: #0f172a;
  border-bottom-left-radius: 4px;
}
.message-wrapper.sent .message-bubble {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-bottom-right-radius: 4px;
}
.message-wrapper.sent .message-bubble,
.message-wrapper.sent .message-text,
.message-wrapper.sent .message-time {
  color: #ffffff !important;
}
.message-wrapper.sent .reply-quote {
  background: rgba(255, 255, 255, 0.14);
  border-left: 4px solid rgba(255, 255, 255, 0.7);
}
[dir='rtl'] .message-wrapper.sent .reply-quote {
  border-left: none;
  border-right: 4px solid rgba(255, 255, 255, 0.7);
}
.message-wrapper.sent .reply-quote-top,
.message-wrapper.sent .reply-quote-body {
  color: #ffffff !important;
  opacity: 0.95;
}
.message-text {
  font-size: 14px;
  line-height: 1.5;
  font-weight: 400;
  white-space: pre-wrap;
}
.message-text.long-text {
  max-height: 120px;
  overflow: hidden;
  position: relative;
  transition: max-height 0.3s ease;
}
.message-text.long-text::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 40px;
  background: linear-gradient(to bottom, transparent, currentColor);
  opacity: 0.25;
}
.message-text.long-text.expanded {
  max-height: none;
}
.message-text.long-text.expanded::after {
  display: none;
}
.message-meta {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 4px;
}
.message-time {
  font-size: 11px;
  opacity: 0.85;
  font-weight: 500;
}
.message-status {
  display: flex;
  align-items: center;
  gap: 4px;
}
.reply-btn {
  opacity: 0;
  transition: opacity 0.2s ease;
}
.message-bubble:hover .reply-btn {
  opacity: 1;
}
.composer {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  background: white;
  flex-shrink: 0;
}
.composer-input {
  flex: 1;
}
.composer-input :deep(.v-field) {
  border-radius: 24px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}
.send-btn {
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}
.thin-scroll::-webkit-scrollbar {
  width: 6px;
}
.thin-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.thin-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.message-wrapper.highlighted .message-bubble {
  animation: highlightPulse 2s ease;
}
@keyframes highlightPulse {
  0%,
  100% {
    box-shadow: none;
  }
  50% {
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
  }
}
@media (max-width: 900px) {
  .chat-card {
    width: 100% !important;
    height: 100vh;
    border-radius: 0;
  }
  .chat-body {
    grid-template-columns: 1fr;
    grid-template-rows: auto 1fr;
  }
  .chat-left {
    width: 100%;
    min-width: 100%;
    max-width: 100%;
  }
}
</style>
