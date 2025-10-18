<template>
  <v-app-bar
    class="px-4 abbbar bg-bgproject rounded"
    :class="{ 'dir-rtl': isRtl, 'dir-ltr': !isRtl }"
    :height="64"
    density="comfortable"
    elevation="2"
    border
  >
    <!-- Title and subtitle -->
    <div class="d-flex align-center" style="gap: 8px">
      <!-- FULL HIDE/SHOW NAV -->
      <v-icon @click="toggleDrawerVisible" flat fluid color="primary"> mdi-tune </v-icon>

      <v-toolbar-title class="font-weight-bold text-h5">
        {{ t('مدیریت کارمندان') }}
      </v-toolbar-title>

      <v-divider vertical :thickness="1" class="border-opacity-50" />

      <div v-if="title" class="d-flex flex-column ml-2 min-w-0">
        <span class="text-subtitle-1 font-weight-medium text-truncate">
          {{ title }}
        </span>
        <span v-if="subtitle" class="text-caption text-medium-emphasis text-truncate">
          {{ subtitle }}
        </span>
      </div>
    </div>

    <v-spacer />

    <!-- Right-side icons -->
    <div class="d-flex align-center" :class="isRtl ? 'rtl-icons' : 'ltr-icons'" style="gap: 10px">
      <!-- 🗓 Calendar switch (GLOBAL) -->
      <v-tooltip :text="calLabel">
        <!-- ⬅️ NEW -->
        <template #activator="{ props }">
          <v-icon v-bind="props" flat fluid class="iconss" @click="cycleCalendar()">
            <!-- ⬅️ NEW -->
            mdi-calendar-month-outline
          </v-icon>
        </template>
      </v-tooltip>

      <!-- Dark / Light -->
      <v-icon @click.stop="toggleTheme" flat fluid class="iconss">
        {{ darkMode ? 'mdi-weather-night' : 'mdi-weather-sunny' }}
      </v-icon>

      <!-- Language -->
      <v-menu transition="scale-transition">
        <template #activator="{ props }">
          <v-icon v-bind="props" flat fluid class="iconss"> mdi-web </v-icon>
        </template>
        <v-list class="bg-navi">
          <v-list-item
            v-for="it in items"
            :key="it.lang"
            @click="changeLanguage(it.lang)"
            class="px-3"
          >
            <div class="d-flex align-center justify-space-between w-100" style="gap: 12px">
              <div class="d-flex align-center" style="gap: 10px">
                <v-avatar size="24" rounded="lg">
                  <img :src="it.icon" :alt="it.title" />
                </v-avatar>
                <v-list-item-title>{{ it.title }}</v-list-item-title>
              </div>
              <v-icon v-if="locale === it.lang" size="small">mdi-check</v-icon>
            </div>
          </v-list-item>
        </v-list>
      </v-menu>

      <!-- Fullscreen -->
      <v-icon @click="toggleFullscreen" flat fluid class="iconss"> mdi-crop-free </v-icon>

      <!-- Notifications -->
      <v-icon flat fluid class="iconss"> mdi-bell-ring-outline </v-icon>
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
  .v-toolbar-title {
    white-space: nowrap;
    margin-right: 8px;
  }
  .v-divider {
    height: 30px;
    align-self: center;
  }
  .iconss {
    background-color: #0404042b;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
  }
  .iconss :where(i, .v-icon) {
    font-size: 20px !important;
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
</style>
