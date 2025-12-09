<template>
  <v-menu
    offset-y
    location="bottom end"
    :close-on-content-click="false"
    transition="slide-y-transition"
    :dir="isRtl ? 'rtl' : 'ltr'"
    content-class="notification-menu"
    :max-width="420"
    min-width="380"
  >
    <!-- 🔔 BELL BADGE + BUTTON -->
    <template #activator="{ props }">
      <v-badge
        v-bind="props"
        :content="badgeContent"
        :model-value="badgeVisible"
        color="error"
        overlap
        bordered
        :dir="isRtl ? 'rtl' : 'ltr'"
        location="top end"
        offset-x="2"
        offset-y="2"
        class="notification-badge"
      >
        <v-btn
          icon
          size="small"
          variant="tonal"
          color="primary"
          class="action-btn notification-btn"
          :dir="isRtl ? 'rtl' : 'ltr'"
          :loading="repo.loading"
          :disabled="repo.loading"
          @click="onOpen"
        >
          <v-icon size="22" :class="{ 'bell-animate': badgeVisible }"> mdi-bell-outline </v-icon>
        </v-btn>
      </v-badge>
    </template>

    <!-- CARD -->
    <v-card class="notification-card" elevation="8" rounded="xl" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="notification-header px-4 py-3 d-flex justify-space-between align-center">
        <div class="d-flex align-center" style="gap: 8px">
          <v-icon color="primary" size="20">mdi-bell-ring</v-icon>
          <span class="text-subtitle-1 font-weight-bold fontfamily">
            {{ $t('اطلاعیه‌ها') }}
          </span>
          <v-chip
            v-if="repo.unreadCount > 0"
            size="x-small"
            color="primary"
            class="ml-2"
            density="comfortable"
          >
            {{ repo.unreadCount }}
          </v-chip>
        </div>

        <div class="d-flex align-center" style="gap: 4px">
          <v-btn
            icon
            size="small"
            variant="text"
            color="grey-darken-1"
            :disabled="repo.unreadCount === 0"
            @click="markAll"
            class="mark-all-btn"
          >
            <v-icon size="20">mdi-check-all</v-icon>
          </v-btn>

          <v-btn
            icon
            size="small"
            variant="text"
            color="grey-darken-1"
            @click="refresh"
            :loading="repo.loading"
            class="refresh-btn"
          >
            <v-icon size="20">mdi-refresh</v-icon>
          </v-btn>
        </div>
      </v-card-title>

      <v-divider class="notification-divider" />

      <!-- LIST -->
      <v-card-text class="notification-list px-0 py-2">
        <v-list
          v-if="visibleNotifications.length"
          density="compact"
          class="py-0 notification-items-container"
          lines="two"
        >
          <div class="notification-groups">
            <!-- TODAY'S GROUP -->
            <div v-if="hasTodayNotifications" class="notification-group">
              <div class="group-label px-4 py-2">
                <span class="text-caption font-weight-medium text-grey-darken-1 fontfamily">
                  {{ $t('امروز') }}
                </span>
              </div>
              <v-list-item
                v-for="n in todayNotifications"
                :key="n.id"
                class="notification-item px-4 py-3"
                :class="{
                  'notification-unread': !n.is_read,
                }"
                @click="goTo(n)"
                ripple
              >
                <!-- Unread indicator -->
                <div v-if="!n.is_read" class="unread-indicator">
                  <div class="unread-dot"></div>
                </div>

                <template #prepend>
                  <div class="notification-icon-container ml-5">
                    <v-avatar
                      size="36"
                      :color="getNotificationColor(n)"
                      variant="flat"
                      class="notification-avatar"
                    >
                      <v-icon size="18" :color="getNotificationIconColor(n)">
                        {{ getNotificationIcon(n) }}
                      </v-icon>
                    </v-avatar>
                  </div>
                </template>

                <div class="notification-content">
                  <div class="d-flex justify-space-between align-start mb-1">
                    <v-list-item-title class="text-body-2 font-weight-medium notification-title">
                      {{ formatTitle(n) }}
                    </v-list-item-title>
                    <v-list-item-subtitle
                      class="text-caption text-grey-darken-1 ml-2 notification-time"
                    >
                      {{ formatTime(n.created_at) }}
                    </v-list-item-subtitle>
                  </div>

                  <v-list-item-subtitle
                    class="text-caption text-grey-darken-2 notification-message"
                  >
                    {{ n.message || 'بدون پیام' }}
                  </v-list-item-subtitle>

                  <v-chip
                    v-if="n.module"
                    size="x-small"
                    :color="getModuleColor(n.module)"
                    density="comfortable"
                    class="mt-2"
                    label
                  >
                    {{ getModuleName(n.module) }}
                  </v-chip>
                </div>

                <template #append>
                  <v-btn
                    icon
                    size="x-small"
                    variant="text"
                    color="grey-darken-1"
                    @click.stop="goTo(n)"
                    class="notification-action-btn"
                  >
                    <v-icon size="16">mdi-chevron-left</v-icon>
                  </v-btn>
                </template>
              </v-list-item>
            </div>

            <!-- OLDER UNREAD GROUP -->
            <div v-if="hasOlderUnreadNotifications" class="notification-group">
              <div class="group-label px-4 py-2">
                <span class="text-caption font-weight-medium text-grey-darken-1">
                  {{ $t('قبلی') }}
                </span>
              </div>
              <v-list-item
                v-for="n in olderUnreadNotifications"
                :key="n.id"
                class="notification-item px-4 py-3"
                :class="{
                  'notification-unread': !n.is_read,
                }"
                @click="goTo(n)"
                ripple
              >
                <!-- Unread indicator -->
                <div v-if="!n.is_read" class="unread-indicator">
                  <div class="unread-dot"></div>
                </div>

                <template #prepend>
                  <div class="notification-icon-container ml-5">
                    <v-avatar
                      size="36"
                      :color="getNotificationColor(n)"
                      variant="flat"
                      class="notification-avatar"
                    >
                      <v-icon size="18" :color="getNotificationIconColor(n)">
                        {{ getNotificationIcon(n) }}
                      </v-icon>
                    </v-avatar>
                  </div>
                </template>

                <div class="notification-content">
                  <div class="d-flex justify-space-between align-start mb-1">
                    <v-list-item-title class="text-body-2 font-weight-medium notification-title">
                      {{ formatTitle(n) }}
                    </v-list-item-title>
                    <v-list-item-subtitle
                      class="text-caption text-grey-darken-1 ml-2 notification-time"
                    >
                      {{ formatTime(n.created_at) }}
                    </v-list-item-subtitle>
                  </div>

                  <v-list-item-subtitle
                    class="text-caption text-grey-darken-2 notification-message"
                  >
                    {{ n.message || 'بدون پیام' }}
                  </v-list-item-subtitle>

                  <v-chip
                    v-if="n.module"
                    size="x-small"
                    :color="getModuleColor(n.module)"
                    density="comfortable"
                    class="mt-2"
                    label
                  >
                    {{ getModuleName(n.module) }}
                  </v-chip>
                </div>

                <template #append>
                  <v-btn
                    icon
                    size="x-small"
                    variant="text"
                    color="grey-darken-1"
                    @click.stop="goTo(n)"
                    class="notification-action-btn"
                  >
                    <v-icon size="16">mdi-chevron-left</v-icon>
                  </v-btn>
                </template>
              </v-list-item>
            </div>
          </div>
        </v-list>

        <!-- EMPTY -->
        <div v-else class="notification-empty-state py-8 px-4 text-center">
          <v-icon size="48" color="grey-lighten-1" class="mb-3"> mdi-bell-off-outline </v-icon>
          <div class="text-body-2 text-grey-darken-2 mb-1">
            {{ $t('هیچ اطلاعیه‌ای یافت نشد') }}
          </div>
          <div class="text-caption text-grey">
            {{ $t('همه اطلاعیه‌های شما در اینجا نمایش داده می‌شود') }}
          </div>
        </div>
      </v-card-text>

      <!-- FOOTER: VIEW ALL -->
      <v-card-actions class="px-4 pb-3">
        <v-btn
          block
          variant="outlined"
          color="primary"
          size="default"
          class="view-all-btn"
          @click="viewAll"
        >
          {{ $t('مشاهده همه اطلاعیه‌ها') }}
          <v-icon size="16" class="mr-2">mdi-arrow-left</v-icon>
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>
</template>
<script setup>
  import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import { useNotificationRepository } from '@/stores/NotificationRepository';

  const router = useRouter();
  const repo = useNotificationRepository();

  const { t, locale } = useI18n();
  /* ======== SOUND FIXED VERSION ======== */
  let notificationSound = null;
  const soundEnabled = ref(false);
  const previousUnread = ref(0);
  const setupSound = () => {
    try {
      notificationSound = new Audio('/sounds/notify.wav');
      notificationSound.volume = 0.8;
    } catch (e) {
      console.warn('Sound failed to load', e);
    }
  };

  const playSound = () => {
    if (!soundEnabled.value || !notificationSound) return;
    notificationSound.currentTime = 0;
    notificationSound.play().catch(() => {});
  };

  // User explicitly enabled audio when clicking bell
  const onOpen = async () => {
    if (!soundEnabled.value) {
      soundEnabled.value = true;

      try {
        await notificationSound.play();
      } catch (e) {
        console.warn('Autoplay blocked until user interacts');
      }
    }

    await repo.fetchNotifications(1);
  };

  /* Watch for new notifications */
  watch(
    () => repo.unreadCount,
    (newVal, oldVal) => {
      if (!soundEnabled.value) return;
      if (typeof oldVal === 'number' && newVal > oldVal) {
        playSound();
      }
      previousUnread.value = newVal;
    }
  );

  /* Mounted */
  onMounted(async () => {
    setupSound();
    await repo.fetchNotifications(1);
    await repo.fetchUnreadCount();
    repo.startPolling();
  });

  /* ---------- BADGE ---------- */
  const badgeVisible = computed(() => repo.unreadCount > 0);
  const badgeContent = computed(() => (repo.unreadCount > 99 ? '99+' : repo.unreadCount || ''));

  /* ===== RTL ===== */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  /* ---------- NOTIFICATION GROUPS ---------- */
  const todayNotifications = computed(() => {
    const today = new Date().toISOString().slice(0, 10);
    return repo.notifications.filter((n) => n.created_at && n.created_at.slice(0, 10) === today);
  });

  const olderUnreadNotifications = computed(() => {
    const today = new Date().toISOString().slice(0, 10);
    return repo.notifications.filter(
      (n) => !n.is_read && n.created_at && n.created_at.slice(0, 10) !== today
    );
  });

  const hasTodayNotifications = computed(() => todayNotifications.value.length > 0);
  const hasOlderUnreadNotifications = computed(() => olderUnreadNotifications.value.length > 0);

  const visibleNotifications = computed(() => [
    ...todayNotifications.value,
    ...olderUnreadNotifications.value,
  ]);

  /* ---------- HELPERS ---------- */
  const getNotificationColor = (n) => {
    switch (n.action) {
      case 'created':
        return 'rgb(var(--v-theme-success))';
      case 'updated':
        return 'rgb(var(--v-theme-warning))';
      case 'deleted':
        return 'rgb(var(--v-theme-error))';
      default:
        return 'rgb(var(--v-theme-primary))';
    }
  };
  const getNotificationIconColor = () => '#fff';
  const getNotificationIcon = (n) => {
    switch (n.action) {
      case 'created':
        return 'mdi-plus-circle';
      case 'updated':
        return 'mdi-pencil-circle';
      case 'deleted':
        return 'mdi-delete-circle';
      default:
        return 'mdi-information';
    }
  };
  const getModuleColor = (m) =>
    ({
      resources: 'deep-purple-lighten-5',
      events: 'cyan-lighten-5',
      informations: 'amber-lighten-5',
    }[m] || 'grey-lighten-4');
  const getModuleName = (m) =>
    ({ resources: 'منبع', events: 'رویداد', informations: 'اطلاعیه' }[m] || m);

  const formatTitle = (n) => {
    const name = n.actor?.name || n.actor_name || n.user?.name || n.user_name || 'کاربر';

    if (n.title) {
      return `${name} - ${n.title}`;
    }
    const module = getModuleName(n.module);
    return (
      {
        created: `${name} ${module} جدید ایجاد کرد`,
        updated: `${name} ${module} را بروزرسانی کرد`,
        deleted: `${name} ${module} را حذف کرد`,
      }[n.action] || `${name} عملیات روی ${module} انجام داد`
    );
  };

  const formatTime = (dateString) => {
    if (!dateString) return '';

    const created = new Date(dateString);
    const now = new Date();

    const diffMs = now - created;

    if (diffMs < 0) {
      return created.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      });
    }

    const sec = Math.floor(diffMs / 1000);
    if (sec < 10) return 'just now';
    if (sec < 60) return `${sec} seconds ago`;

    const min = Math.floor(sec / 60);
    if (min < 60) return `${min} minutes ago`;

    const hr = Math.floor(min / 60);
    if (hr < 24) return `${hr} hours ago`;

    const day = Math.floor(hr / 24);
    if (day < 30) return `${day} days ago`;

    const month = Math.floor(day / 30);
    if (month < 12) return `${month} months ago`;

    const year = Math.floor(month / 12);
    return `${year} years ago`;
  };

  const markAll = async () => {
    if (!repo.unreadCount) return;
    await repo.markAllAsRead();
  };

  const refresh = async () => {
    await repo.fetchNotifications(1);
  };

  const goTo = async (n) => {
    if (!n.is_read) {
      await repo.markAsRead(n.id);
    }

    if (n.route?.name) {
      try {
        await router.push({ name: n.route.name, params: n.route.params || {} });
      } catch (e) {
        console.error('Navigation error:', e);
      }
    } else {
      router.push({ name: 'notifications' });
    }
  };

  const viewAll = () => {
    router.push({ name: 'notifications' });
  };

  onMounted(async () => {
    setupSound();

    await repo.fetchNotifications(1);
    await repo.fetchUnreadCount();

    repo.startPolling();
  });

  onUnmounted(() => {
    repo.stopPolling();
  });
  
</script>

<style scoped>
  /* Use global app font */
  .notification-card,
  .notification-item,
  .notification-empty-state,
  .notification-title,
  .notification-message,
  .notification-time {
    font-family: var(--app-font);
  }
  .fontfamily {
    font-family: var(--app-font);
  }

  /* ============================
 🔔 MENU + CARD
============================ */
  .notification-menu {
    border-radius: 16px !important;
    overflow: hidden !important;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12) !important;
  }

  .notification-card {
    display: flex;
    flex-direction: column;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid rgba(226, 232, 240, 0.8);
  }

  /* Header */
  .notification-header {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  }

  /* Divider */
  .notification-divider {
    border-color: rgba(226, 232, 240, 0.8) !important;
  }

  /* ============================
 📜 SCROLLABLE LIST (Professional Scrollbar)
============================ */
  .notification-list {
    flex: 1 1 auto;
    max-height: 400px;
    overflow-y: auto;
  }

  .notification-items-container {
    height: 100%;
  }

  /* Custom scrollbar - Professional Design */
  .notification-list::-webkit-scrollbar {
    width: 6px;
  }

  .notification-list::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
  }

  .notification-list::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 3px;
    transition: all 0.3s ease;
  }

  .notification-list::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
  }

  /* For Firefox */
  .notification-list {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
  }

  /* ============================
 📅 NOTIFICATION GROUPS
============================ */
  .notification-groups {
    padding-bottom: 8px;
  }

  .notification-group {
    margin-bottom: 4px;
  }

  .group-label {
    background: rgba(241, 245, 249, 0.6);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    margin-top: 4px;
  }

  /* ============================
 🧾 NOTIFICATION ITEMS
============================ */
  .notification-item {
    position: relative;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border-bottom: 1px solid rgba(226, 232, 240, 0.6);
    margin: 0 4px;
    border-radius: 8px;
    min-height: 72px;
  }

  .notification-item:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.04) 0%, rgba(59, 130, 246, 0.02) 100%);
    transform: translateX(-2px);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
  }

  [dir='rtl'] .notification-item:hover {
    transform: translateX(2px);
  }

  .notification-item:hover::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(
      to bottom,
      rgb(var(--v-theme-primary)),
      rgba(var(--v-theme-primary), 0.8)
    );
    border-radius: 0 4px 4px 0;
  }

  [dir='rtl'] .notification-item:hover::before {
    left: auto;
    right: 0;
    border-radius: 4px 0 0 4px;
  }

  /* Unread notification styling */
  .notification-unread {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(59, 130, 246, 0.04) 100%);
    border-left: 2px solid rgb(var(--v-theme-primary));
  }

  [dir='rtl'] .notification-unread {
    border-left: none;
    border-right: 2px solid rgb(var(--v-theme-primary));
  }

  /* Unread indicator dot */
  .unread-indicator {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  [dir='rtl'] .unread-indicator {
    left: auto;
    right: 12px;
  }

  .unread-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: linear-gradient(
      135deg,
      rgb(var(--v-theme-primary)) 0%,
      rgba(var(--v-theme-primary), 0.8) 100%
    );
    animation: pulse-dot 2s infinite;
  }

  @keyframes pulse-dot {
    0% {
      box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0.4);
    }
    70% {
      box-shadow: 0 0 0 4px rgba(var(--v-theme-primary), 0);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0);
    }
  }

  /* ============================
 🎨 NOTIFICATION ELEMENTS
============================ */
  /* Avatar */
  .notification-icon-container {
    position: relative;
  }

  .notification-avatar {
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.8);
  }

  .notification-item:hover .notification-avatar {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
  }

  /* Text Styling */
  .notification-title {
    color: #1e293b;
    line-height: 1.4;
    font-weight: 500;
    margin-bottom: 2px;
    font-size: 0.875rem;
  }

  .notification-message {
    color: #64748b;
    line-height: 1.5;
    font-size: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .notification-time {
    color: #94a3b8;
    font-size: 0.7rem;
    font-weight: 400;
    white-space: nowrap;
    min-width: 45px;
  }

  /* Action button */
  .notification-action-btn {
    opacity: 0;
    transform: translateX(4px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: rgba(255, 255, 255, 0.8) !important;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
  }

  .notification-item:hover .notification-action-btn {
    opacity: 1;
    transform: translateX(0);
  }

  /* ============================
 📊 EMPTY STATE
============================ */
  .notification-empty-state {
    animation: fadeIn 0.5s ease;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 12px;
    margin: 8px;
  }

  @keyframes fadeIn {
    0% {
      opacity: 0;
      transform: translateY(8px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* ============================
 🔘 BUTTONS
============================ */
  .notification-btn {
    border-radius: 10px;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
  }

  .notification-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
  }

  .mark-all-btn:hover,
  .refresh-btn:hover {
    background: rgba(59, 130, 246, 0.08) !important;
    transform: rotate(90deg);
  }

  .refresh-btn:active {
    animation: spin 0.6s ease;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }

  .view-all-btn {
    border-radius: 10px;
    font-weight: 500;
    letter-spacing: 0.2px;
    transition: all 0.3s ease;
    border: 1px solid rgb(var(--v-theme-primary)) !important;
    background: rgba(var(--v-theme-primary), 0.02) !important;
  }

  .view-all-btn:hover {
    background: rgba(var(--v-theme-primary), 0.08) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.1);
  }

  /* ============================
 🔔 BELL ANIMATION
============================ */
  .bell-animate {
    animation: bell-ring 1.2s cubic-bezier(0.36, 0, 0.66, 1) infinite;
  }

  @keyframes bell-ring {
    0%,
    100% {
      transform: rotate(0deg);
    }
    10%,
    30%,
    50%,
    70%,
    90% {
      transform: rotate(5deg);
    }
    20%,
    40%,
    60%,
    80% {
      transform: rotate(-5deg);
    }
  }
</style>
