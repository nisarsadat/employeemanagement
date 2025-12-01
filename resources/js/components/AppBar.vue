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
        </div>
      </div>
    </div>

    <v-spacer />

    <!-- Right-side icons -->
    <div class="d-flex align-center" :class="isRtl ? 'rtl-icons' : 'ltr-icons'" style="gap: 12px">
      <!-- 🗓 Calendar switch (GLOBAL) -->
      <v-tooltip :text="calLabel">
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            @click="cycleCalendar()"
            icon
            size="small"
            variant="tonal"
            :color="calendarColor"
            class="action-btn"
          >
            <v-icon size="20">mdi-calendar-month-outline</v-icon>
          </v-btn>
        </template>
      </v-tooltip>

      <!-- Dark / Light (Commented but styled) -->
      <!-- 
      <v-btn @click.stop="toggleTheme" icon size="small" variant="tonal" class="action-btn">
        <v-icon size="20">{{ darkMode ? 'mdi-weather-night' : 'mdi-weather-sunny' }}</v-icon>
      </v-btn>
      -->

      <!-- Language -->
      <v-menu transition="scale-transition">
        <template #activator="{ props }">
          <v-btn v-bind="props" icon size="small" variant="tonal" class="action-btn">
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

      <!-- Fullscreen -->
      <v-btn @click="toggleFullscreen" icon size="small" variant="tonal" class="action-btn">
        <v-icon size="20">mdi-crop-free</v-icon>
      </v-btn>

      <!-- Notifications (Commented but styled) -->
      <!-- 
      <v-btn icon size="small" variant="tonal" class="action-btn">
        <v-icon size="20">mdi-bell-ring-outline</v-icon>
      </v-btn>
      -->
    </div>
  </v-app-bar>
</template>

<script setup>
  import { ref, defineProps, watch, onMounted, computed } from 'vue';
  import { defineEmits } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useTheme } from 'vuetify';
  import { useEmployeOfficeRepository } from '@/stores/EmployeeRepository';
  import { useCalendar } from '../../composables/useCalendar'; // ⬅️ NEW

  const repo = useEmployeOfficeRepository();
  const { t, locale } = useI18n();
  const theme = useTheme();
  const darkMode = ref(false);

  // ⬅️ NEW: calendar composable
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

  defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
  });
  defineEmits(['toggle-drawer']);

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

  /* === Drawer controls via your Pinia repo === */
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
    border-color: rgba(var(--v-theme-primary), 0.2) !important;
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

  .action-btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(var(--v-theme-surface), 0.7) !important;
    border: 1px solid rgba(var(--v-theme-outline), 0.15);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .action-btn:hover {
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

  /* Enhanced RTL/LTR specific styles */
  .dir-rtl .title-section {
    margin-right: 8px;
  }

  .dir-ltr .title-section {
    margin-left: 8px;
  }

  /* Animation for calendar button */
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

  .action-btn:active .v-icon {
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

    .action-btn,
    .nav-toggle-btn {
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
