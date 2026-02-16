<template>
  <div :dir="isRtl ? 'rtl' : 'ltr'" class="page-bg">
    <!-- APP BAR -->
    <AppBar :title="t('تمام اطلاعیه‌ها')">
     
    </AppBar>

    <v-container class=" px-4" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- HEADER CARD -->
      <v-card
        class="rounded-xl pa-4 mb-2  elevation-1"
        :dir="isRtl ? 'rtl' : 'ltr'"
        style="border: 1px solid rgba(226, 232, 240, 0.8)"
      >
        <div class="d-flex justify-space-between align-center">
          <div class="d-flex align-center" style="gap: 12px">
            <v-icon color="primary" size="24" class="header-icon">
              mdi-bell-ring-outline
            </v-icon>
            <div>
              <div class="text-h6 font-weight-bold text-grey-darken-3">
                {{ t('تمام اطلاعیه‌ها') }}
              </div>
              <div class="text-caption text-grey-darken-1 mt-1">
                {{ t('لیست کامل تمام اطلاعیه‌های سیستم') }}
              </div>
            </div>
          </div>

          <v-chip
            size="small"
            color="primary"
            variant="flat"
            class="notification-count"
          >
            <v-icon start size="16">mdi-counter</v-icon>
            {{ repo.notifications.length }}
          </v-chip>
        </div>
      </v-card>

      <!-- MAIN CONTENT CARD -->
      <v-card
        class="rounded-xl elevation-1"
        :dir="isRtl ? 'rtl' : 'ltr'"
        style="border: 1px solid rgba(226, 232, 240, 0.8)"
      >
        <!-- TOP ACTIONS -->
        <div class="pa-4 pb-0">
          <div class="d-flex justify-space-between align-center mb-3">
            <div class="d-flex align-center" style="gap: 8px">
              <v-icon color="grey-darken-2" size="20">mdi-filter-variant</v-icon>
              <div class="text-subtitle-2 font-weight-bold text-grey-darken-3">
                {{ t('همه اطلاعیه‌ها') }}
              </div>
              <v-chip
                v-if="unreadCount > 0"
                size="x-small"
                color="primary"
                variant="flat"
                density="comfortable"
              >
                {{ unreadCount }} {{ t('خوانده نشده') }}
              </v-chip>
            </div>

            <div class="d-flex" style="gap: 8px">
              <v-btn
                size="small"
                color="primary"
                variant="tonal"
                @click="markAllAsRead"
                :disabled="unreadCount === 0"
                class="action-btn"
              >
                <v-icon start size="18">mdi-check-all</v-icon>
                {{ t('علامت‌گذاری همه به عنوان خوانده شده') }}
              </v-btn>

              <v-btn
                size="small"
                color="error"
                variant="tonal"
                @click="clearAll"
                :disabled="!repo.notifications.length"
                class="action-btn"
              >
                <v-icon start size="18">mdi-delete-sweep</v-icon>
                {{ t('پاک کردن همه') }}
              </v-btn>
            </div>
          </div>
        </div>

        <v-divider class="mb-2" />

        <!-- NOTIFICATIONS LIST WITH SCROLLABLE AREA -->
        <div class="notifications-container">
          <v-list
            v-if="repo.notifications.length"
            density="comfortable"
            class="pa-0 notifications-list"
            :class="{ 'empty-state': !repo.notifications.length }"
          >
            <!-- NOTIFICATION GROUPS -->
            <div v-for="(group, groupIndex) in groupedNotifications" :key="groupIndex">
              <!-- GROUP HEADER -->
              <div class="group-header px-4 py-2">
                <v-icon size="16" class="mr-2" color="grey-darken-1">
                  {{ getGroupIcon(group.date) }}
                </v-icon>
                <span class="text-caption font-weight-medium text-grey-darken-2">
                  {{ formatGroupDate(group.date) }}
                </span>
                <span class="text-caption text-grey ml-2">
                  ({{ group.notifications.length }} {{ t('مورد') }})
                </span>
              </div>

              <!-- NOTIFICATION ITEMS -->
              <v-list-item
                v-for="(n, index) in group.notifications"
                :key="n.id"
                class="notification-item px-4 py-3"
                :class="{
                  'notification-unread': !n.is_read,
                  'notification-read': n.is_read,
                  'first-item': index === 0,
                  'last-item': index === group.notifications.length - 1
                }"
                @click="handleNotificationClick(n)"
                ripple
              >
                <!-- UNREAD INDICATOR -->
                <div v-if="!n.is_read" class="unread-indicator">
                  <div class="unread-dot"></div>
                </div>

                <!-- AVATAR -->
                <template #prepend>
                  <div class="notification-avatar-container mr-3">
                    <v-avatar
                      size="40"
                      :color="getColor(n)"
                      variant="flat"
                      class="notification-avatar"
                      :class="`notification-avatar-${n.action}`"
                    >
                      <v-icon size="20" color="white">
                        {{ getIcon(n) }}
                      </v-icon>
                    </v-avatar>
                  </div>
                </template>

                <!-- CONTENT -->
                <div class="notification-content w-100">
                  <div class="d-flex justify-space-between align-start mb-1">
                    <div class="d-flex align-center" style="gap: 8px">
                      <span class="text-body-2 font-weight-medium text-grey-darken-3">
                        {{ formatTitle(n) }}
                      </span>
                      <v-chip
                        v-if="n.module"
                        size="x-small"
                        :color="getModuleColor(n.module)"
                        variant="flat"
                        density="comfortable"
                        class="module-chip"
                      >
                        {{ getModuleName(n.module) }}
                      </v-chip>
                    </div>
                    <div class="d-flex align-center" style="gap: 8px">
                      <span class="text-caption text-grey-darken-1 notification-time">
                        {{ formatTime(n.created_at) }}
                      </span>
                      <v-icon
                        size="16"
                        color="grey-lighten-1"
                        class="chevron-icon"
                      >
                        {{ isRtl ? 'mdi-chevron-left' : 'mdi-chevron-right' }}
                      </v-icon>
                    </div>
                  </div>

                  <!-- MAIN MESSAGE -->
                  <div class="text-caption text-grey-darken-2 notification-message">
                    {{ n.message || t('بدون پیام') }}
                  </div>

                  <!-- 🔥 INLINE CHANGES (no hover, always visible if exists) -->
                  <div
                    v-if="hasChanges(n)"
                    class="changes-box mt-2"
                  >
                    <div class="text-caption text-grey-darken-3 font-weight-medium mb-1">
                      {{ t('فیلدهای تغییر یافته') }}:
                    </div>

                    <div class="changes-items">
                      <div
                        v-for="(change, field) in n.changes"
                        :key="field"
                        class="change-row text-caption"
                      >
                        <span class="change-field">
                          {{ getFieldLabel(field) }}:
                        </span>

                        <!-- old → new -->
                        <span
                          v-if="change && change.old !== undefined && change.old !== null && change.old !== change.new"
                          class="change-old"
                        >
                          {{ formatValue(change.old) }}
                        </span>

                        <span
                          v-if="change && change.old !== undefined && change.old !== null && change.old !== change.new"
                          class="change-arrow"
                        >
                          ⟶
                        </span>

                        <span class="change-new">
                          {{ formatValue(change.new) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- META INFO -->
                  <div class="d-flex align-center mt-2" style="gap: 12px">
                    <div class="d-flex align-center" style="gap: 4px">
                      <v-icon size="14" color="grey">mdi-account-outline</v-icon>
                      <span class="text-caption text-grey">
                        {{ n.user?.name || n.user_name || t('کاربر') }}
                      </span>
                    </div>
                    
                    <div v-if="n.action" class="d-flex align-center" style="gap: 4px">
                      <v-icon size="14" color="grey">mdi-flash-outline</v-icon>
                      <span class="text-caption text-grey">
                        {{ getActionText(n.action) }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- ACTIONS -->
                <template #append>
                  <div class="notification-actions">
                    <v-btn
                      icon
                      size="x-small"
                      variant="text"
                      color="grey"
                      @click.stop="toggleReadStatus(n)"
                      class="action-icon"
                      :title="n.is_read ? t('علامت‌گذاری به عنوان خوانده نشده') : t('علامت‌گذاری به عنوان خوانده شده')"
                    >
                      <v-icon size="18">
                        {{ n.is_read ? 'mdi-email-outline' : 'mdi-email-open-outline' }}
                      </v-icon>
                    </v-btn>
                  </div>
                </template>
              </v-list-item>

              <!-- GROUP DIVIDER -->
              <v-divider
                v-if="groupIndex < groupedNotifications.length - 1"
                class="my-2"
              />
            </div>
          </v-list>

          <!-- EMPTY STATE -->
          <div v-else class="empty-state py-12 text-center">
            <div class="empty-state-content">
              <v-icon size="72" color="grey-lighten-2" class="empty-icon">
                mdi-bell-off-outline
              </v-icon>
              <div class="text-h6 text-grey-darken-1 mt-4 mb-2">
                {{ t('هیچ اطلاعیه‌ای موجود نیست') }}
              </div>
              <div class="text-body-1 text-grey mb-6">
                {{ t('همه اطلاعیه‌های شما در اینجا نمایش داده خواهد شد') }}
              </div>
              <v-btn
                color="primary"
                variant="tonal"
                @click="refreshNotifications"
                class="refresh-btn"
              >
                <v-icon start size="20">mdi-refresh</v-icon>
                {{ t('تازه‌سازی') }}
              </v-btn>
            </div>
          </div>
        </div>

        <!-- FOOTER -->
        <v-card-actions v-if="repo.notifications.length" class="pa-4 pt-2">
          <div class="d-flex justify-space-between align-center w-100">
            <div class="text-caption text-grey">
              {{ t('نمایش') }} {{ repo.notifications.length }} {{ t('از') }} {{ repo.notifications.length }} {{ t('اطلاعیه') }}
            </div>
            <v-btn
              size="small"
              variant="text"
              color="primary"
              @click="refreshNotifications"
              :loading="repo.loading"
            >
              <v-icon start size="18">mdi-refresh</v-icon>
              {{ t('تازه‌سازی لیست') }}
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-container>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import AppBar from '@/components/AppBar.vue';
import { useNotificationRepository } from '@/stores/NotificationRepository';

const router = useRouter();
const { t, locale } = useI18n();
const repo = useNotificationRepository();

const isRtl = ref(locale.value === 'fa');
watch(locale, (n) => (isRtl.value = n === 'fa'));

// ---------- labels for changed fields ----------
const fieldLabels = {
  // OfficeEmployee common fields
  name: 'نام',
  father_name: 'نام پدر',
  grand_father_name: 'نام پدرکلان',
  nick_name: 'نام مستعار',
  code_number: 'کود نمبر',
  phone_number: 'شماره تماس',
  bast: 'بست',
  level: 'رتبه/سطح',
  main_province: 'آدرس اصلی - ولایت',
  now_province: 'آدرس فعلی - ولایت',
  now_specific_place: 'محل مشخص فعلی',
  activity: 'فعالیت',
  join_date: 'تاریخ آمدن',
  left_date: 'تاریخ رفتن',
  note: 'ملاحظات',
  // you can add more field => label here if you want
};

const getFieldLabel = (field) => fieldLabels[field] || field;

// small helper for showing values nicely
const formatValue = (val) => {
  if (val === null || val === undefined || val === '') return t('خالی');
  if (typeof val === 'boolean') return val ? t('بلی') : t('نخیر');
  return String(val);
};

// does this notification have a non-empty changes object?
const hasChanges = (n) =>
  n &&
  n.changes &&
  typeof n.changes === 'object' &&
  Object.keys(n.changes).length > 0;

// Computed
const unreadCount = computed(() =>
  repo.notifications.filter((n) => !n.is_read).length
);

const groupedNotifications = computed(() => {
  const groups = {};

  repo.notifications.forEach((notification) => {
    const date = new Date(notification.created_at).toLocaleDateString('fa-IR');
    if (!groups[date]) {
      groups[date] = [];
    }
    groups[date].push(notification);
  });

  // Convert to array and sort by date (newest first)
  return Object.entries(groups)
    .map(([date, notifications]) => ({ date, notifications }))
    .sort((a, b) => new Date(b.date) - new Date(a.date));
});

// Methods
const goBack = () => {
  router.push('/app/dashboard');
};

const clearAll = async () => {
  if (confirm(t('آیا از پاک کردن همه اطلاعیه‌ها اطمینان دارید؟'))) {
    await repo.clearAllNotifications();
  }
};

const markAllAsRead = async () => {
  await repo.markAllAsRead();
};

const refreshNotifications = async () => {
  await repo.fetchNotifications(1);
};

const handleNotificationClick = async (n) => {
  if (!n.is_read) {
    await repo.markAsRead(n.id);
  }

  if (n.route?.name) {
    try {
      await router.push({ name: n.route.name, params: n.route.params || {} });
    } catch (e) {
      console.error('Navigation error:', e);
      router.push({ name: 'notifications' });
    }
  }
};

const toggleReadStatus = async (n) => {
  if (n.is_read) {
    await repo.markAsUnread(n.id);
  } else {
    await repo.markAsRead(n.id);
  }
};

// Styling helpers
const getColor = (n) =>
  n.action === 'created'
    ? 'success'
    : n.action === 'deleted'
    ? 'error'
    : n.action === 'updated'
    ? 'warning'
    : 'primary';

const getIcon = (n) =>
  n.action === 'created'
    ? 'mdi-plus-circle'
    : n.action === 'deleted'
    ? 'mdi-delete-circle'
    : n.action === 'updated'
    ? 'mdi-pencil-circle'
    : 'mdi-information';

const getModuleColor = (m) =>
  ({
    resources: 'deep-purple',
    events: 'cyan',
    informations: 'amber',
    users: 'indigo',
    system: 'blue-grey',
  }[m] || 'grey');

const getModuleName = (m) =>
  ({
    resources: 'منابع',
    events: 'رویدادها',
    informations: 'اطلاعیه‌ها',
    users: 'کاربران',
    system: 'سیستم',
  }[m] || m);

const getActionText = (action) =>
  ({
    created: 'ایجاد شده',
    updated: 'بروزرسانی شده',
    deleted: 'حذف شده',
  }[action] || 'تغییر یافته');

const formatTitle = (n) => {
  const name = n.user?.name || n.user_name || 'کاربر';
  if (n.title) {
    return n.title;
  }
  const module = getModuleName(n.module);
  return `${module} ${getActionText(n.action)}`;
};

const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleTimeString('fa-IR', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  });
};

const formatGroupDate = (dateString) => {
  const date = new Date(dateString);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  if (date.toDateString() === today.toDateString()) {
    return t('امروز');
  }
  if (date.toDateString() === yesterday.toDateString()) {
    return t('دیروز');
  }

  return date.toLocaleDateString('fa-IR', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
  });
};

const getGroupIcon = (dateString) => {
  const date = new Date(dateString);
  const today = new Date();

  if (date.toDateString() === today.toDateString()) {
    return 'mdi-calendar-today';
  }

  const diffDays = Math.floor((today - date) / (1000 * 60 * 60 * 24));

  if (diffDays === 1) return 'mdi-calendar-arrow-left';
  if (diffDays <= 7) return 'mdi-calendar-week';
  return 'mdi-calendar-month';
};

onMounted(async () => {
  await repo.fetchNotifications(1);
});
</script>

<style scoped>
/* Apply app font everywhere */
:deep(*) {
  font-family: var(--app-font, 'Vazirmatn', sans-serif) !important;
}

.page-bg {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  font-family: var(--app-font, 'Vazirmatn', sans-serif);
}

/* BACK BUTTON */
.back-btn {
  font-weight: 500;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
  transform: translateX(-2px);
}

.rtl-icon :deep(.v-icon) {
  transform: rotate(180deg);
}

/* HEADER ICON */
.header-icon {
  background: rgba(var(--v-theme-primary), 0.1);
  padding: 8px;
  border-radius: 12px;
}

.notification-count {
  font-weight: 600;
  font-size: 0.875rem;
}

/* ACTION BUTTONS */
.action-btn {
  border-radius: 10px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* NOTIFICATIONS CONTAINER */
.notifications-container {
  max-height: calc(100vh - 280px);
  overflow-y: auto;
}

.notifications-list {
  padding-bottom: 16px;
}

/* CUSTOM SCROLLBAR */
.notifications-container::-webkit-scrollbar {
  width: 6px;
}

.notifications-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.notifications-container::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
  border-radius: 3px;
  transition: all 0.3s ease;
}

.notifications-container::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
}

/* GROUP HEADER */
.group-header {
  background: rgba(241, 245, 249, 0.6);
  border-top: 1px solid rgba(226, 232, 240, 0.8);
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  margin-top: 4px;
}

/* NOTIFICATION ITEM */
.notification-item {
  position: relative;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 12px;
  margin: 2px 8px;
  min-height: 88px;
}

.notification-item:hover {
  background: linear-gradient(
    135deg,
    rgba(59, 130, 246, 0.05) 0%,
    rgba(59, 130, 246, 0.02) 100%
  );
  transform: translateX(-2px);
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
}

[dir='rtl'] .notification-item:hover {
  transform: translateX(2px);
}

/* UNREAD NOTIFICATION */
.notification-unread {
  background: linear-gradient(
    135deg,
    rgba(59, 130, 246, 0.08) 0%,
    rgba(59, 130, 246, 0.04) 100%
  );
  border-left: 3px solid rgb(var(--v-theme-primary));
}

[dir='rtl'] .notification-unread {
  border-left: none;
  border-right: 3px solid rgb(var(--v-theme-primary));
}

/* UNREAD INDICATOR */
.unread-indicator {
  position: absolute;
  left: 16px;
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
  right: 16px;
}

.unread-dot {
  width: 8px;
  height: 8px;
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

/* AVATAR */
.notification-avatar-container {
  position: relative;
}

.notification-avatar {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 2px solid white;
}

.notification-item:hover .notification-avatar {
  transform: scale(1.08);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

/* AVATAR COLORS BASED ON ACTION */
.notification-avatar-created {
  background: linear-gradient(
    135deg,
    var(--v-theme-success) 0%,
    var(--v-theme-success-darken-1) 100%
  );
}

.notification-avatar-updated {
  background: linear-gradient(
    135deg,
    var(--v-theme-warning) 0%,
    var(--v-theme-warning-darken-1) 100%
  );
}

.notification-avatar-deleted {
  background: linear-gradient(
    135deg,
    var(--v-theme-error) 0%,
    var(--v-theme-error-darken-1) 100%
  );
}

/* CONTENT */
.notification-content {
  font-family: var(--app-font, 'Vazirmatn', sans-serif);
}

.notification-time {
  font-size: 0.75rem;
  font-weight: 500;
  min-width: 45px;
  text-align: left;
}

[dir='rtl'] .notification-time {
  text-align: right;
}

.notification-message {
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* INLINE CHANGES BOX */
.changes-box {
  margin-top: 6px;
  padding: 6px 10px;
  border-radius: 8px;
  background: rgba(148, 163, 184, 0.08);
}

.changes-items {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.change-row {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  color: #475569;
}

.change-field {
  font-weight: 600;
  color: #0f172a;
}

.change-old {
  text-decoration: line-through;
  opacity: 0.8;
}

.change-arrow {
  opacity: 0.7;
}

.change-new {
  font-weight: 500;
  color: #15803d;
}

/* MODULE CHIP */
.module-chip {
  font-size: 0.65rem;
  height: 20px;
  font-weight: 500;
}

/* ACTIONS */
.notification-actions {
  opacity: 0;
  transform: translateX(8px);
  transition: all 0.3s ease;
  display: flex;
  gap: 4px;
}

[dir='rtl'] .notification-actions {
  transform: translateX(-8px);
}

.notification-item:hover .notification-actions {
  opacity: 1;
  transform: translateX(0);
}

.action-icon {
  transition: all 0.3s ease;
}

.action-icon:hover {
  background: rgba(var(--v-theme-primary), 0.1) !important;
  transform: scale(1.1);
}

/* CHEVRON ICON */
.chevron-icon {
  transition: transform 0.3s ease;
}

.notification-item:hover .chevron-icon {
  transform: translateX(4px);
}

[dir='rtl'] .notification-item:hover .chevron-icon {
  transform: translateX(-4px);
}

/* EMPTY STATE */
.empty-state {
  font-family: var(--app-font, 'Vazirmatn', sans-serif);
  animation: fadeIn 0.5s ease;
}

.empty-state-content {
  max-width: 320px;
  margin: 0 auto;
}

.empty-icon {
  opacity: 0.6;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.refresh-btn {
  border-radius: 12px;
  font-weight: 500;
  padding: 8px 24px;
}

.refresh-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.2);
}

/* FADE IN ANIMATION */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* FIRST AND LAST ITEM STYLING */
.first-item {
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
}

.last-item {
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
}
</style>
