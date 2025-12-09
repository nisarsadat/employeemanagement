<template>
  <v-app-bar
    class="px-4 abbbar bg-bgproject rounded"
    :class="{ 'dir-rtl': isRtl, 'dir-ltr': !isRtl }"
    :height="72"
    density="comfortable"
    elevation="2"
    border
  >
    <!-- Title and subtitle -->
    <div class="d-flex align-center" style="gap: 16px">
      <!-- FULL HIDE/SHOW NAV -->
      <v-btn
        @click="toggleDrawerVisible"
        icon
        size="small"
        variant="tonal"
        color="primary"
        class="nav-toggle-btn"
      >
        <v-icon size="24">mdi-tune</v-icon>
      </v-btn>

      <div class="d-flex align-center title-section" style="gap: 12px">
        <v-toolbar-title class="font-weight-bold text-h5 main-title">
          {{ t('مدیریت کارمندان') }}
        </v-toolbar-title>

        <v-divider vertical :thickness="2" class="border-opacity-25 title-divider" />

        <div v-if="title" class="d-flex flex-column ml-2 min-w-0">
          <span class="text-subtitle-1 font-weight-medium text-truncate section-title">
            {{ title }}
          </span>
          <span
            v-if="subtitle"
            class="text-caption text-medium-emphasis text-truncate section-subtitle"
          >
            {{ subtitle }}
          </span>

          <!-- 🧭 Breadcrumbs (optional) -->
          <v-breadcrumbs
            v-if="breadcrumbs && breadcrumbs.length"
            class="appbar-breadcrumbs mt-1"
            :items="breadcrumbs"
            density="compact"
          >
            <template #divider>
              <v-icon size="14">
                {{ isRtl ? 'mdi-chevron-left' : 'mdi-chevron-right' }}
              </v-icon>
            </template>
          </v-breadcrumbs>
        </div>
      </div>
    </div>

    <v-spacer />

    <!-- 🔙 Page actions (your back button, etc.) -->
    <div class="d-flex align-center appbar-actions" style="gap: 8px">
      <slot name="actions" />
    </div>

    <!-- Right-side icons -->
    <div class="d-flex align-center" :class="isRtl ? 'rtl-icons' : 'ltr-icons'" style="gap: 12px">
      <!-- 🗓 Calendar switch (GLOBAL) -->
      <v-tooltip :text="calLabel">
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            @click="!iconsLoading && cycleCalendar()"
            icon
            size="small"
            variant="tonal"
            :color="calendarColor"
            class="action-btn"
            :loading="iconsLoading"
            :disabled="iconsLoading"
          >
            <v-icon size="20">mdi-calendar-month-outline</v-icon>
          </v-btn>
        </template>
      </v-tooltip>

      <!-- Language -->
      <v-menu transition="scale-transition">
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            icon
            size="small"
            variant="tonal"
            class="action-btn"
            :loading="iconsLoading"
            :disabled="iconsLoading"
          >
            <v-icon size="20">mdi-web</v-icon>
          </v-btn>
        </template>
        <v-list class="language-menu bg-navi">
          <v-list-item
            v-for="it in items"
            :key="it.lang"
            @click="changeLanguage(it.lang)"
            class="px-3 language-item"
            :class="{ 'active-language': locale === it.lang }"
          >
            <div class="d-flex align-center justify-space-between w-100" style="gap: 12px">
              <div class="d-flex align-center" style="gap: 10px">
                <v-avatar size="28" rounded="lg" class="language-flag">
                  <img :src="it.icon" :alt="it.title" />
                </v-avatar>
                <v-list-item-title class="text-body-2">{{ it.title }}</v-list-item-title>
              </div>
              <v-icon v-if="locale === it.lang" size="small" color="primary">mdi-check</v-icon>
            </div>
          </v-list-item>
        </v-list>
      </v-menu>

      <!-- 🔔 Notifications -->
      <NotificationMenu />

      <!-- Fullscreen -->
      <v-btn
        @click="!iconsLoading && toggleFullscreen()"
        icon
        size="small"
        variant="tonal"
        class="action-btn"
        :loading="iconsLoading"
        :disabled="iconsLoading"
      >
        <v-icon size="20">mdi-crop-free</v-icon>
      </v-btn>
    </div>
  </v-app-bar>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from 'vuetify';
import { useEmployeOfficeRepository } from '@/stores/EmployeeRepository';
import { useCalendar } from '../../composables/useCalendar';
import NotificationMenu from './NotificationBell.vue'; // 👈 adjust path/name if different

const repo = useEmployeOfficeRepository();
const { t, locale } = useI18n();
const theme = useTheme();
const darkMode = ref(false);

/* PROPS */
defineProps({
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  breadcrumbs: {
    type: Array,
    default: () => [],
  },
  iconsLoading: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['toggle-drawer']);

/* Calendar composable */
const { calendar, cycleCalendar } = useCalendar();
const calLabel = computed(() => {
  switch (calendar.value) {
    case 'gregorian':
      return 'Gregorian';
    case 'jalali':
      return 'هجری شمسی';
    case 'hijri':
      return 'هجری قمری';
    default:
      return 'Calendar';
  }
});

const calendarColor = computed(() => {
  switch (calendar.value) {
    case 'gregorian':
      return 'blue';
    case 'jalali':
      return 'green';
    case 'hijri':
      return 'purple';
    default:
      return 'primary';
  }
});

/* Theme */
onMounted(() => {
  const savedTheme = localStorage.getItem('apartment-theme');
  darkMode.value = savedTheme
    ? savedTheme === 'dark'
    : window.matchMedia('(prefers-color-scheme: dark)').matches;
  theme.global.name.value = darkMode.value ? 'dark' : 'light';
});

const toggleTheme = () => {
  darkMode.value = !darkMode.value;
  theme.global.name.value = darkMode.value ? 'dark' : 'light';
  localStorage.setItem('apartment-theme', theme.global.name.value);
};

/* Fullscreen */
const toggleFullscreen = async () => {
  try {
    if (!document.fullscreenElement) {
      await document.documentElement.requestFullscreen();
    } else {
      await document.exitFullscreen();
    }
  } catch (err) {
    console.error('Failed to toggle fullscreen mode:', err);
  }
};

/* Language items */
const items = computed(() => [
  { title: t('english'), lang: 'en', icon: '/assets/english.png' },
  { title: t('dari'), lang: 'fa', icon: '/assets/dari.png' },
]);

const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => {
  isRtl.value = l === 'fa';
});

const changeLanguage = (lang) => {
  locale.value = lang;
  isRtl.value = lang === 'fa';
  localStorage.setItem('apartment-lang', lang);
};

/* Drawer controls */
const toggleRail = () => {
  repo.toggleRail?.();
};
const toggleDrawerVisible = () => {
  repo.toggleDrawerVisible?.();
};
</script>

<style scoped>
.abbbar {
  backdrop-filter: blur(10px);
  background: rgba(var(--v-theme-bgproject), 0.9) !important;
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.1);
  transition: all 0.3s ease;
}

.main-title {
  white-space: nowrap;
  background: linear-gradient(
    135deg,
    rgb(var(--v-theme-primary)) 0%,
    rgb(var(--v-theme-secondary)) 100%
  );
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.5rem !important;
  font-weight: 700 !important;
  letter-spacing: -0.01em;
}

.title-divider {
  height: 32px;
  align-self: center;
  border-color: black;
}

.section-title {
  font-weight: 600 !important;
  color: rgba(var(--v-theme-on-background), 0.9) !important;
  font-size: 0.95rem !important;
}

.section-subtitle {
  font-size: 0.8rem !important;
  color: rgba(var(--v-theme-on-background), 0.6) !important;
}

.nav-toggle-btn {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(var(--v-theme-primary), 0.1) !important;
  border: 1px solid rgba(var(--v-theme-primary), 0.2);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-toggle-btn:hover {
  background: rgba(var(--v-theme-primary), 0.2) !important;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(var(--v-theme-primary), 0.2);
}

/* 🔔 Make action buttons (including NotificationMenu) share the same design */
:deep(.action-btn) {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(var(--v-theme-surface), 0.7) !important;
  border: 1px solid rgba(var(--v-theme-outline), 0.15);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.action-btn:hover) {
  background: rgba(var(--v-theme-surface), 0.9) !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.language-menu {
  border-radius: 16px !important;
  padding: 8px !important;
  border: 1px solid rgba(var(--v-theme-outline), 0.1);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
  backdrop-filter: blur(20px);
  background: rgba(var(--v-theme-navi), 0.95) !important;
}

.language-item {
  border-radius: 12px;
  margin: 2px 0;
  transition: all 0.2s ease;
  min-height: 48px !important;
}

.language-item:hover {
  background: rgba(var(--v-theme-primary), 0.08) !important;
}

.language-item.active-language {
  background: rgba(var(--v-theme-primary), 0.12) !important;
}

.language-flag {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.8);
}

.dir-rtl {
  direction: rtl;
}
.dir-ltr {
  direction: ltr;
}
.ltr-icons {
  flex-direction: row;
}
.rtl-icons {
  flex-direction: row-reverse;
}
.min-w-0 {
  min-width: 0;
}
.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dir-rtl .title-section {
  margin-right: 8px;
}
.dir-ltr .title-section {
  margin-left: 8px;
}

/* 🧭 breadcrumbs styling */
.appbar-breadcrumbs {
  padding: 0;
  font-size: 0.75rem;
}
.appbar-breadcrumbs :deep(.v-breadcrumbs-item) {
  opacity: 0.85;
}

/* ✨ Back button / actions hover animation */
.appbar-actions :deep(.v-btn) {
  transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
}

/* LTR: slide a bit left on hover */
.dir-ltr .appbar-actions :deep(.v-btn:hover) {
  transform: translateX(-2px);
  box-shadow: 0 4px 10px rgba(var(--v-theme-primary), 0.2);
}

/* RTL: slide a bit right on hover */
.dir-rtl .appbar-actions :deep(.v-btn:hover) {
  transform: translateX(2px);
  box-shadow: 0 4px 10px rgba(var(--v-theme-primary), 0.2);
}

/* small press effect */
.appbar-actions :deep(.v-btn:active) {
  transform: scale(0.97);
}

/* Animation for calendar button icon */
@keyframes calendarPulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

:deep(.action-btn:active .v-icon) {
  animation: calendarPulse 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .main-title {
    font-size: 1.3rem !important;
  }

  .section-title {
    font-size: 0.9rem !important;
  }

  .section-subtitle {
    font-size: 0.75rem !important;
  }

  .nav-toggle-btn {
    width: 40px;
    height: 40px;
  }

  :deep(.action-btn) {
    width: 40px;
    height: 40px;
  }
}

.main-title,
.section-title,
.section-subtitle {
  font-family: var(--app-font) !important;
}

@media (max-width: 600px) {
  .abbbar {
    padding-left: 12px !important;
    padding-right: 12px !important;
  }

  .main-title {
    font-size: 1.1rem !important;
  }

  .title-divider {
    display: none;
  }
}
</style>
