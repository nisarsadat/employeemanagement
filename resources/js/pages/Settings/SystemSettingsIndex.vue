<!-- src/pages/Settings/SystemSettingsIndex.vue -->
<template>
  <v-container fluid class="pt-6 settings-page" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('تنظیمات سیستم')" :subtitle="t('مدیریت مسیرهای تنظیمات سیستم')" />
    <v-alert variant="tonal" type="info" class="mt-6" border="start" :dir="isRtl ? 'rtl' : 'ltr'">
      {{ t('برای ورود به هر بخش، روی کارت آن کلیک کنید.') }}
    </v-alert>
    <v-row class="mt-2" dense>
      <v-col v-for="item in items" :key="item.key" cols="12" sm="6" md="4" lg="3">
        <v-card
          class="setting-card"
          rounded="xl"
          hover
          role="button"
          tabindex="0"
          @click="go(item)"
          @keydown.enter.prevent="go(item)"
          @keydown.space.prevent="go(item)"
        >
          <v-card-text class="d-flex align-center ga-3" :dir="isRtl ? 'rtl' : 'ltr'">
            <div class="icon-wrap">
              <v-icon :icon="item.icon" size="22" />
            </div>

            <div class="flex-grow-1 min-w-0">
              <div class="title text-body-1 font-weight-bold text-truncate">
                {{ item.title }}
              </div>
              <div class="subtitle text-caption text-medium-emphasis text-truncate">
                {{ item.subtitle }}
              </div>
            </div>

            <v-icon class="chev" :icon="isRtl ? 'mdi-chevron-left' : 'mdi-chevron-right'" />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
  import { computed } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';

  const router = useRouter();
  const { locale, t } = useI18n();
  const isRtl = computed(() => locale.value === 'fa');

  const items = computed(() => [
    {
      key: 'provinces',
      title: t('ولایت'),
      subtitle: t('مدیریت لیست ولایت‌ها'),
      icon: 'mdi-map-marker-outline',
      to: { path: '/app/settings/provinces' },
    },
    {
      key: 'districts',
      title: t('والسوالی'),
      subtitle: t('مدیریت لیست والسوالی‌ها'),
      icon: 'mdi-map-marker-radius-outline',
      to: { path: '/app/settings/districts' },
    },
    {
      key: 'zones',
      title: t('زون'),
      subtitle: t('مدیریت زون‌ها'),
      icon: 'mdi-map-outline',
      to: { path: '/app/settings/zones/zones' },
    },
    {
      key: 'hawzas',
      title: t('حوزه'),
      subtitle: t('مدیریت حوزه‌ها'),
      icon: 'mdi-map-legend',
      to: { path: '/app/settings/zones/hawzas' },
    },

    {
      key: 'event-types',
      title: t('نوع رویداد'),
      subtitle: t('مدیریت نوع رویداد'),
      icon: 'mdi-shape-outline',
      to: { path: '/app/event-types' },
    },
    {
      key: 'event-specific-types',
      title: t('نوع مشخص رویداد'),
      subtitle: t('مدیریت نوع مشخص رویداد'),
      icon: 'mdi-shape-plus-outline',
      to: { path: '/app/event-specific-types' },
    },
    {
      key: 'jenayee-types',
      title: t('مدیریت نوع جنایی'),
      subtitle: t('مدیریت نوعیت جینایی'),
      icon: 'mdi-shape-plus-outline',
      to: { path: '/app/jenayee-type' },
    },
    {
      key: 'seyasee-types',
      title: t('سیاسی'),
      subtitle: t('مدیریت نوع سیاسی'),
      icon: 'mdi-shape-plus-outline',
      to: { path: '/app/seyasee-type' },
    },
    {
      key: 'organs',
      title: t('ارگان'),
      subtitle: t('مدیریت ارگان‌ها'),
      icon: 'mdi-domain',
      to: { path: '/app/organs' },
    },
    {
      key: 'jobs',
      title: t('مدیریت وظایف'),
      subtitle: t('مدیریت وظایف'),
      icon: 'mdi-briefcase-outline',
      to: { path: '/app/jobs-type' },
    },
    {
      key: 'resource-activity-types',
      title: t('مدیریت نوع فعالیت منبع'),
      subtitle: t('مدیریت نوعیت فعالیت منابع'),
      icon: 'mdi-clipboard-list-outline',
      to: { path: '/app/resource-activity-types' },
    },
    {
      key: 'mawqafjahadi',
      title: t('مدیریت موقف جهادی'),
      subtitle: t('مدیریت موقف جهادی'),
      icon: 'mdi-hand-peace',
      to: { path: '/app/mawqafjahadi' },
    },
  ]);

  function go(item) {
    if (!item?.to) return;
    router.push(item.to);
  }
</script>

<style scoped>
  /* ✅ Force card text to use your global font variable */
  .settings-page,
  .settings-page :deep(.v-card),
  .settings-page :deep(.v-card *),
  .settings-page :deep(.v-card-text),
  .settings-page :deep(.v-list-item-title),
  .settings-page :deep(.v-card-title),
  .settings-page :deep(.v-card-subtitle),
  .settings-page :deep(.v-alert),
  .settings-page :deep(.v-alert *) {
    font-family: var(--app-font) !important;
  }

  .setting-card {
    border: 1px solid rgba(0, 0, 0, 0.06);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
  }
  .setting-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
    border-color: rgba(var(--v-theme-primary), 0.25);
  }

  .icon-wrap {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--v-theme-primary), 0.08);
    color: rgb(var(--v-theme-primary));
  }

  .title,
  .subtitle {
    line-height: 1.2;
  }

  .chev {
    opacity: 0.6;
  }

  [dir='rtl'] .chev {
    transform: translateX(-2px);
  }
  [dir='ltr'] .chev {
    transform: translateX(2px);
  }
</style>
