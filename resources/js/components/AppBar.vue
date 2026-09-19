<template>
  <v-app-bar
    class="appbar-shell bg-bgproject"
    :class="{ 'dir-rtl': isRtl, 'dir-ltr': !isRtl }"
    :height="barHeight"
    density="comfortable"
    elevation="0"
    border
  >
    <div class="appbar-left">
      <v-btn
        :to="{ name: 'Dashboard' }"
        variant="plain"
        class="logo-btn"
        :ripple="false"
        :aria-label="t('داشبورد')"
      >
        <v-img src="/logo.jpeg" width="44" height="44" contain />
      </v-btn>

      <div class="title-block">
        <div class="title-row">
          <v-toolbar-title class="main-title">
            {{ t('۳۳۳') }}
          </v-toolbar-title>

          <v-divider
            v-if="showDivider"
            vertical
            :thickness="2"
            class="border-opacity-25 title-divider"
          />

          <div v-if="title" class="section-block">
            <span class="section-title text-truncate">
              {{ title }}
            </span>

            <span v-if="subtitle && showSubtitle" class="section-subtitle text-truncate">
              {{ subtitle }}
            </span>

            <v-breadcrumbs
              v-if="breadcrumbs && breadcrumbs.length && showBreadcrumbs"
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
    </div>

    <v-spacer />

    <div v-if="$slots.actions" class="appbar-actions">
      <slot name="actions" />
    </div>

    <div class="right-icons" :class="isRtl ? 'rtl-icons' : 'ltr-icons'">
      <v-btn
        @click="!iconsLoading && goBack()"
        icon
        size="small"
        variant="tonal"
        class="action-btn"
        :disabled="iconsLoading"
      >
        <v-icon size="18" color="primary">mdi-arrow-left</v-icon>
      </v-btn>

      <v-btn
        @click="!iconsLoading && toggleFullscreen()"
        icon
        size="small"
        variant="tonal"
        class="action-btn"
        :disabled="iconsLoading"
      >
        <v-icon size="18" color="primary">mdi-crop-free</v-icon>
      </v-btn>

      <v-btn
        @click="!iconsLoading && refreshPage()"
        icon
        size="small"
        variant="tonal"
        class="action-btn"
        :disabled="iconsLoading || refreshing"
      >
        <v-icon size="18" color="primary" :class="{ 'spin-refresh': refreshing }">
          mdi-refresh
        </v-icon>
      </v-btn>

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
            :disabled="iconsLoading"
          >
            <v-icon size="18">mdi-calendar-month-outline</v-icon>
          </v-btn>
        </template>
      </v-tooltip>

      <v-menu transition="scale-transition">
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            icon
            size="small"
            variant="tonal"
            class="action-btn"
            :disabled="iconsLoading"
          >
            <v-icon size="18" color="primary">mdi-web</v-icon>
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
            <div class="d-flex align-center justify-space-between w-100">
              <v-list-item-title class="text-body-2">
                {{ it.title }}
              </v-list-item-title>
              <v-icon v-if="locale === it.lang" size="small" color="primary">mdi-check</v-icon>
            </div>
          </v-list-item>
        </v-list>
      </v-menu>

      <NotificationMenu v-if="canSeeNotifications" />
      <ChatMenu v-if="canSeeChatbox" />
    </div>
  </v-app-bar>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme, useDisplay } from 'vuetify';
import { useRouter, useRoute } from 'vue-router';
import { useCalendar } from '../../composables/useCalendar';
import { useLoginRepository } from '@/stores/LoginRepository';
import NotificationMenu from './NotificationBell.vue';
import ChatMenu from './chat/ChatMenu.vue';
import { runPageRefresh } from '@/composables/usePageRefresh';

const login = useLoginRepository();
const router = useRouter();
const route = useRoute();
const { t, locale } = useI18n();
const theme = useTheme();
const display = useDisplay();

const darkMode = ref(false);
const refreshing = ref(false);

defineProps({
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  breadcrumbs: { type: Array, default: () => [] },
  iconsLoading: { type: Boolean, default: false },
});

defineEmits(['toggle-drawer']);

const rtlLangs = ['fa', 'ps'];
const isRtl = computed(() => rtlLangs.includes(locale.value));

const canSeeNotifications = computed(() => login?.can?.('notifications.view'));
const canSeeChatbox = computed(() => login?.can?.('chatbox.view'));

const barHeight = computed(() => (display.smAndDown.value ? 64 : 72));
const showDivider = computed(() => !display.xs.value);
const showSubtitle = computed(() => display.mdAndUp.value);
const showBreadcrumbs = computed(() => display.lgAndUp.value);

const goBack = () => {
  if (window.history.length > 1) router.back();
  else router.push('/');
};

const refreshPage = async () => {
  if (refreshing.value) return;
  refreshing.value = true;

  try {
    const refreshed =
      (await runPageRefresh(route.name)) ||
      (await runPageRefresh(route.path)) ||
      (await runPageRefresh(route.fullPath));

    if (!refreshed) {
      console.warn('No page refresh handler registered for:', route.name || route.path);
    }
  } catch (error) {
    console.error('Page refresh failed:', error);
  } finally {
    refreshing.value = false;
  }
};

const { calendar, cycleCalendar } = useCalendar();

const calLabel = computed(() =>
  calendar.value === 'gregorian'
    ? 'Gregorian'
    : calendar.value === 'jalali'
      ? 'هجری شمسی'
      : calendar.value === 'hijri'
        ? 'هجری قمری'
        : 'Calendar'
);

const calendarColor = computed(() =>
  calendar.value === 'gregorian'
    ? 'blue'
    : calendar.value === 'jalali'
      ? 'green'
      : calendar.value === 'hijri'
        ? 'purple'
        : 'primary'
);

onMounted(() => {
  const saved = localStorage.getItem('apartment-theme');
  darkMode.value = saved
    ? saved === 'dark'
    : window.matchMedia('(prefers-color-scheme: dark)').matches;
  theme.global.name.value = darkMode.value ? 'dark' : 'light';

  const lang = localStorage.getItem('apartment-lang') || locale.value || 'ps';
  applyGlobalDir(lang);
});

const toggleFullscreen = async () => {
  if (!document.fullscreenElement) await document.documentElement.requestFullscreen();
  else await document.exitFullscreen();
};

const items = computed(() => [
  { title: t('english'), lang: 'en' },
  { title: t('dari'), lang: 'fa' },
  { title: t('pashto'), lang: 'ps' },
]);

const applyGlobalDir = (lang) => {
  const rtl = lang === 'fa' || lang === 'ps';
  document.documentElement.setAttribute('dir', rtl ? 'rtl' : 'ltr');
};

const changeLanguage = (lang) => {
  locale.value = lang;
  localStorage.setItem('apartment-lang', lang);
  applyGlobalDir(lang);
};


watch(locale, (lang) => {
  applyGlobalDir(lang);
});
</script>

<style scoped>
@keyframes spinRefresh {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.spin-refresh {
  animation: spinRefresh 0.8s linear infinite;
}

.appbar-shell {
  backdrop-filter: blur(10px);
  background: rgba(var(--v-theme-bgproject), 0.9) !important;
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.1);
  padding-inline: 12px;
}

.appbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  flex: 1 1 auto;
}

.title-block {
  min-width: 0;
  flex: 1 1 auto;
}

.title-row {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.section-block {
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.main-title {
  white-space: nowrap;
  flex: 0 0 auto;
  background: linear-gradient(
    135deg,
    rgb(var(--v-theme-primary)) 0%,
    rgb(var(--v-theme-secondary)) 100%
  );
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.35rem !important;
  font-weight: 700 !important;
  letter-spacing: -0.01em;
}

.title-divider {
  height: 28px;
  align-self: center;
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

.appbar-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-inline: 10px;
  flex: 0 1 auto;
}

.right-icons {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 0 0 auto;
}

.rtl-icons {
  flex-direction: row-reverse;
}

.ltr-icons {
  flex-direction: row;
}

.action-btn {
  width: 40px !important;
  height: 40px !important;
  min-width: 40px !important;
  border-radius: 50% !important;
  background: rgba(var(--v-theme-surface), 0.7) !important;
  border: 1px solid rgba(var(--v-theme-outline), 0.15) !important;
  box-shadow: none !important;
  transition: all 0.22s ease !important;
}

.action-btn:hover {
  background: rgba(var(--v-theme-surface), 0.92) !important;
  border-color: rgba(var(--v-theme-primary), 0.28) !important;
  transform: translateY(-1px);
}

.logo-btn {
  padding: 0 !important;
  min-width: 0 !important;
  width: 44px !important;
  height: 44px !important;
  background: transparent !important;
  box-shadow: none !important;
  cursor: pointer;
}

/* make NotificationMenu and ChatMenu same as other buttons */
.right-icons :deep(.v-btn) {
  border-radius: 50% !important;
}

.right-icons :deep(.notification-btn),
.right-icons :deep(.chat-btn),
.right-icons :deep(.notification-trigger),
.right-icons :deep(.chat-trigger) {
  width: 40px !important;
  height: 40px !important;
  min-width: 40px !important;
  border-radius: 50% !important;
  background: rgba(var(--v-theme-surface), 0.7) !important;
  border: 1px solid rgba(var(--v-theme-outline), 0.15) !important;
  box-shadow: none !important;
  transition: all 0.22s ease !important;
}

.right-icons > :deep(*) .v-btn {
  width: 40px !important;
  height: 40px !important;
  min-width: 40px !important;
  border-radius: 50% !important;
  background: rgba(var(--v-theme-surface), 0.7) !important;
  border: 1px solid rgba(var(--v-theme-outline), 0.15) !important;
  box-shadow: none !important;
  transition: all 0.22s ease !important;
}

.right-icons > :deep(*) .v-btn:hover {
  background: rgba(var(--v-theme-surface), 0.92) !important;
  border-color: rgba(var(--v-theme-primary), 0.28) !important;
  transform: translateY(-1px);
}

.right-icons :deep(.v-btn .v-btn__content) {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

.right-icons > :deep(*) .v-icon {
  font-size: 18px !important;
  color: rgb(var(--v-theme-primary)) !important;
}

.right-icons > :deep(*) .v-badge__badge {
  box-shadow: 0 0 0 2px rgba(var(--v-theme-bgproject), 1);
}

.language-menu {
  border-radius: 16px !important;
  padding: 8px !important;
}

.language-item {
  border-radius: 12px;
  margin: 2px 0;
}

.language-item.active-language {
  background: rgba(var(--v-theme-primary), 0.12) !important;
}

.appbar-breadcrumbs {
  padding: 0;
  font-size: 0.75rem;
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dir-rtl {
  direction: rtl;
}

.dir-ltr {
  direction: ltr;
}

@media (max-width: 1264px) {
  .main-title {
    font-size: 1.15rem !important;
  }

  .section-title {
    font-size: 0.88rem !important;
  }
}

@media (max-width: 960px) {
  .appbar-shell {
    padding-inline: 8px;
  }

  .appbar-actions {
    margin-inline: 6px;
  }

  .right-icons {
    gap: 6px;
  }

  .action-btn {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
  }

  .right-icons > :deep(*) .v-btn {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
  }

  .logo-btn {
    width: 40px !important;
    height: 40px !important;
  }
}

@media (max-width: 700px) {
  .appbar-actions {
    display: none;
  }

  .main-title {
    font-size: 1rem !important;
  }

  .section-title {
    font-size: 0.82rem !important;
  }
}
</style>
