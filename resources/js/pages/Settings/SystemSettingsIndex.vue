<!-- src/pages/Settings/SystemSettingsIndex.vue -->
<template>
  <v-container fluid class="pt-6 settings-page" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('تنظیمات سیستم')" :subtitle="t('مدیریت مسیرهای تنظیمات سیستم')" />

    <v-alert variant="tonal" type="info" class="mt-6" border="start" :dir="isRtl ? 'rtl' : 'ltr'">
      {{ t('برای ورود به هر بخش، روی کارت آن کلیک کنید.') }}
    </v-alert>

    <v-row class="mt-2" dense :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col
        v-for="item in visibleItems"
        :key="item.key"
        cols="12"
        sm="6"
        md="4"
        lg="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <v-card
          class="setting-card"
          rounded="xl"
          hover
          :dir="isRtl ? 'rtl' : 'ltr'"
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

            <div class="flex-grow-1 min-w-0" :dir="isRtl ? 'rtl' : 'ltr'">
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

    <!-- Optional: if user tries to open something they don't have access to -->
    <v-snackbar v-model="denySnack" :timeout="2200" location="top">
      {{ t('اجازه دسترسی ندارید') }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { computed, ref } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import { useLoginRepository } from '@/stores/LoginRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  const router = useRouter();
  const { locale, t } = useI18n();
  const isRtl = computed(() => locale.value === 'fa');

  // ✅ RBAC from your store
  const loginRepo = useLoginRepository();

  const denySnack = ref(false);

  // ✅ Define permissions per item (change strings if your backend uses other names)
  const items = computed(() => [
    {
      key: 'provinces',
      title: t('ولایت'),
      // subtitle: t('مدیریت لیست ولایت‌ها'),
      icon: 'mdi-map-marker-outline',
      to: { path: '/app/settings/provinces' },
      permission: 'provinces.view',
    },
    {
      key: 'districts',
      title: t('والسوالی/حوزه'),
      // subtitle: t('مدیریت لیست والسوالی‌ها'),
      icon: 'mdi-map-marker-radius-outline',
      to: { path: '/app/settings/districts' },
      permission: 'districts.view',
    },
    {
      key: 'zones',
      title: t('آمریت'),
      // subtitle: t('مدیریت آمریت‌ها'),
      icon: 'mdi-map-outline',
      to: { path: '/app/settings/zones/zones' },
      permission: 'zones.view',
    },
    {
      key: 'hawzas',
      title: t('حوزه'),
      // subtitle: t('مدیریت حوزه‌ها'),
      icon: 'mdi-map-legend',
      to: { path: '/app/settings/zones/hawzas' },
      permission: 'hawzas.view',
    },

    {
      key: 'event-types',
      title: t('نوع رویداد'),
      // subtitle: t('مدیریت نوع رویداد'),
      icon: 'mdi-shape-outline',
      to: { path: '/app/event-types' },
      permission: 'event_types.view',
    },
    {
      key: 'event-specific-types',
      title: t('نوع مشخص رویداد'),
      // subtitle: t('مدیریت نوع مشخص رویداد'),
      icon: 'mdi-shape-plus-outline',
      to: { path: '/app/event-specific-types' },
      permission: 'event_specific_types.view',
    },
    {
      key: 'jenayee-types',
      title: t('نوع جنایی'),
      // subtitle: t('مدیریت نوعیت جینایی'),
      icon: 'mdi-shape-plus-outline',
      to: { path: '/app/jenayee-type' },
      permission: 'jenayee_types.view',
    },
    {
      key: 'seyasee-types',
      title: t('نوع سیاسی'),
      // subtitle: t('مدیریت نوع سیاسی'),
      icon: 'mdi-shape-plus-outline',
      to: { path: '/app/seyasee-type' },
      permission: 'seyasee_types.view',
    },
    {
      key: 'organs',
      title: t('ارگان برای گفتکو های انجام شده'),
      // subtitle: t('مدیریت ارگان‌ها'),
      icon: 'mdi-domain',
      to: { path: '/app/organs' },
      permission: 'organs.view',
    },
    {
      key: 'jobs',
      title: t('مدیریت وظایف'),
      // subtitle: t('مدیریت وظایف'),
      icon: 'mdi-briefcase-outline',
      to: { path: '/app/jobs-type' },
      permission: 'jobs.view',
    },
    {
      key: 'resource-activity-types',
      title: t('نوع فعالیت منبع'),
      // subtitle: t('مدیریت نوعیت فعالیت منابع'),
      icon: 'mdi-clipboard-list-outline',
      to: { path: '/app/resource-activity-types' },
      permission: 'resource_activity_types.view',
    },
    {
      key: 'mawqafjahadi',
      title: t('موقف جهادی'),
      // subtitle: t('مدیریت موقف جهادی'),
      icon: 'mdi-hand-peace',
      to: { path: '/app/mawqafjahadi' },
      permission: 'mawqafjahadi.view',
    },
    {
      key: 'formatzone',
      title: t('فارمت آمریت'),
      // subtitle: t('مدیریت فارمت آمریت‌ها'),
      icon: 'mdi-map-marker-multiple-outline',
      to: { path: '/app/formatzone' },
      permission: 'formatzone.view',
    },
    {
      key: 'docstypes',
      title: t('نوعیت اسناد'),
      // subtitle: t('مدیریت نوعیت اسناد'),
      icon: 'mdi-map-marker-multiple-outline',
      to: { path: '/app/docstypesindex' },
      permission: 'docstypes.view',
    },
    {
      key: 'marjas',
      title: t('مرجع اسناد'),
      // subtitle: t('مرجع صادره و وارده'),
      icon: 'mdi-map-marker-multiple-outline',
      to: { path: '/app/marjas' },
      permission: 'marjas.view',
    },
    {
      key: 'letryformatindex',
      title: t('۵۶ فارمټ ډراپ ډوون'),
      icon: 'mdi-book',
      to: { path: '/app/letryformatindex' },
      permission: 'letryformatindex.view',
    },
    {
      key: 'infoCategory',
      title: t('کتگوری اطلاعات'),
      icon: 'mdi-book',
      to: { path: '/app/infoCategory' },
      permission: 'InfoCategory.view',
    },
  ]);
  usePageRefresh('/app/settings');

  // ✅ show only permitted items
  const visibleItems = computed(() =>
    items.value.filter((it) => !it.permission || loginRepo.can(it.permission))
  );

  function go(item) {
    if (!item?.to) return;

    if (item.permission && !loginRepo.can(item.permission)) {
      denySnack.value = true;
      return;
    }

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
