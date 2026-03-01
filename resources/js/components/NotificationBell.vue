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
          color="grey-200"
          class="action-btn notification-btn"
          :dir="isRtl ? 'rtl' : 'ltr'"
          :loading="repo.loading"
          :disabled="repo.loading"
          @click="onOpen"
        >
          <v-icon size="22" color="primary" :class="{ 'bell-animate': badgeVisible }"> mdi-bell-outline </v-icon>
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
          <!-- Mute/Unmute Button -->
          <v-btn
            icon
            size="small"
            variant="text"
            :color="soundEnabled ? 'grey-darken-1' : 'error'"
            @click="toggleSound"
            class="sound-toggle-btn"
            :title="soundEnabled ? $t('بی‌صدا کردن') : $t('صداگذاری')"
          >
            <v-icon size="20">
              {{ soundEnabled ? 'mdi-volume-high' : 'mdi-volume-off' }}
            </v-icon>
          </v-btn>

          <v-btn
            icon
            size="small"
            variant="text"
            color="grey-darken-1"
            :disabled="repo.unreadCount === 0"
            @click="markAll"
            class="mark-all-btn"
            :title="$t('خواندن همه')"
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
            :title="$t('بروزرسانی')"
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
                :class="{ 'notification-unread': !n.is_read }"
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
                      {{ formatTime(n) }}
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
                :class="{ 'notification-unread': !n.is_read }"
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
                      {{ formatTime(n) }}
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

  /* =========================
   RTL
========================= */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => {
    isRtl.value = n === 'fa';
  });

  /* =========================
   🔊 SOUND
========================= */
  let notificationSound = null;
  const soundEnabled = ref(true); // Default to enabled

  // 🔊 Create a centralized function to handle sound playback
  const playNotificationSound = () => {
    // First check if sound is enabled
    if (!soundEnabled.value) {
      console.log('Sound is muted, skipping sound playback');
      return;
    }

    if (!notificationSound) {
      console.warn('Sound not initialized');
      return;
    }

    try {
      // Reset and play
      notificationSound.currentTime = 0;
      notificationSound.play().catch((error) => {
        console.warn('Audio play blocked or failed:', error);
      });
    } catch (e) {
      console.warn('Play sound error:', e);
    }
  };

  // Toggle sound on/off
  const toggleSound = () => {
    soundEnabled.value = !soundEnabled.value;

    // Save preference to localStorage
    localStorage.setItem('notificationSoundEnabled', soundEnabled.value.toString());

    // If turning sound on, try to unlock audio
    if (soundEnabled.value && notificationSound) {
      try {
        notificationSound
          .play()
          .then(() => {
            notificationSound.pause();
            notificationSound.currentTime = 0;
          })
          .catch((e) => {
            console.log('Audio unlock may be blocked:', e);
          });
      } catch (e) {
        // Ignore errors
      }
    }

    // Show feedback in console
    console.log(`Sound ${soundEnabled.value ? 'enabled' : 'muted'}`);
  };

  // Initialize sound on component mount
  const setupSound = () => {
    try {
      notificationSound = new Audio('/sounds/notify.wav');
      notificationSound.volume = 0.8;
      notificationSound.preload = 'auto';

      // Try to unlock audio by playing/pausing on load
      notificationSound.load();
    } catch (e) {
      console.warn('Sound setup failed:', e);
    }
  };

  // When user opens the notification menu
  const onOpen = async () => {
    await repo.fetchNotifications(1);
  };

  /* =========================
   🔔 DETECT NEW NOTIFICATIONS
========================= */
  const lastSeenMaxId = ref(0);

  // Watch for any changes in notifications array
  watch(
    () => repo.notifications,
    (newNotifications, oldNotifications) => {
      if (!newNotifications || newNotifications.length === 0) return;

      // Find the highest ID in current notifications
      const maxId = Math.max(...newNotifications.map((n) => n.id || 0));

      // If we have no previous maxId, set it and return (initial load)
      if (lastSeenMaxId.value === 0) {
        lastSeenMaxId.value = maxId;
        return;
      }

      // If new maxId is greater than previous, we have new notifications
      if (maxId > lastSeenMaxId.value) {
        console.log('New notification detected! Playing sound...');
        playNotificationSound(); // 🔊 Use centralized sound function
        lastSeenMaxId.value = maxId;
      }
    },
    { deep: true, immediate: true }
  );

  // Also watch unread count changes
  watch(
    () => repo.unreadCount,
    (newCount, oldCount) => {
      // Only trigger on increase and when we have an old value (not initial)
      if (oldCount !== undefined && newCount > oldCount) {
        console.log('Unread count increased! Playing sound...');
        playNotificationSound(); // 🔊 Use centralized sound function
      }
    }
  );

  /* =========================
   MOUNT / UNMOUNT
========================= */
  onMounted(async () => {
    // Load sound preference from localStorage
    const savedSoundPref = localStorage.getItem('notificationSoundEnabled');
    if (savedSoundPref !== null) {
      soundEnabled.value = savedSoundPref === 'true';
    } else {
      // Save default to localStorage
      localStorage.setItem('notificationSoundEnabled', 'true');
    }

    console.log('Initial sound state:', soundEnabled.value ? 'enabled' : 'muted');

    // Setup sound
    setupSound();

    // Try to unlock audio immediately
    try {
      // Create a temporary silent audio context to unlock audio
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (AudioContext) {
        const audioContext = new AudioContext();
        // Create a silent buffer
        const buffer = audioContext.createBuffer(1, 1, 22050);
        const source = audioContext.createBufferSource();
        source.buffer = buffer;
        source.connect(audioContext.destination);
        source.start();
        setTimeout(() => audioContext.close(), 100);
      }
    } catch (e) {
      console.log('Audio context initialization may be blocked');
    }

    // Fetch notifications
    await repo.fetchNotifications(1);
    await repo.fetchUnreadCount();

    // Initialize last seen ID
    if (repo.notifications.length > 0) {
      lastSeenMaxId.value = Math.max(...repo.notifications.map((n) => n.id || 0));
    }

    // Start polling
  });

  onUnmounted(() => {

    // Clean up audio
    if (notificationSound) {
      try {
        notificationSound.pause();
        notificationSound.src = ''; // Clear source
      } catch (e) {
        // Ignore cleanup errors
      }
    }
  });

  /* =========================
   BADGE
========================= */
  const badgeVisible = computed(() => repo.unreadCount > 0);
  const badgeContent = computed(() => (repo.unreadCount > 99 ? '99+' : repo.unreadCount || ''));

  /* =========================
   GROUPS (TODAY / OLDER)
========================= */
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

  /* =========================
   HELPERS
========================= */
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
    ({
      resources: 'منبع',
      events: 'رویداد',
      informations: 'اطلاعیه',
    }[m] || m);

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

  /* =========================
   ⏱ TIME FORMAT (ENGLISH)
========================= */
  const formatTime = (n) => {
    if (!n) return '';

    if (n.created_at_human) {
      return n.created_at_human;
    }

    const dateString = n.created_at;
    if (!dateString) return '';

    const [datePart, timePart] = dateString.split(' ');
    if (!datePart || !timePart) return '';

    const [y, m, d] = datePart.split('-').map(Number);
    const [hh, mm, ss] = timePart.split(':').map(Number);

    const created = new Date(y, m - 1, d, hh, mm, ss);
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

  /* =========================
   ACTIONS
========================= */
  const markAll = async () => {
    if (!repo.unreadCount) return;
    await repo.markAllAsRead();
  };

  const refresh = async () => {
    await repo.fetchNotifications(1);
    await repo.fetchUnreadCount();
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
 📜 SCROLLABLE LIST
============================ */
  .notification-list {
    flex: 1 1 auto;
    max-height: 400px;
    overflow-y: auto;
  }

  .notification-items-container {
    height: 100%;
  }

  /* Custom scrollbar */
  .notification-list::-webkit-scrollbar {
    width: 18px;
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

  /* Firefox */
  .notification-list {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
  }

  /* ============================
 📅 GROUPS
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
 🧾 ITEMS
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

  /* Unread */
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

  /* Text */
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

  /* append button */
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

  /* Empty state */
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

  /* Buttons */
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

  /* Sound toggle button */
  .sound-toggle-btn:hover {
    background: rgba(var(--v-theme-error), 0.08) !important;
    transform: scale(1.1);
  }

  .mark-all-btn:hover,
  .refresh-btn:hover {
    background: rgba(59, 130, 246, 0.08) !important;
    transform: scale(1.1);
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

  /* Bell animation */
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
