<template>
  <v-container fluid class="resource-container" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Search Window -->
    <v-card class="search-window mb-4" elevation="2" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="bg-primary">
        <v-icon icon="mdi-magnify" class="mr-2"></v-icon>
        {{ t('جستجوی منبع') }}
      </v-card-title>

      <v-card-text class="pa-4" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-row>
          <v-col cols="12" md="8">
            <v-autocomplete
              v-model="selectedId"
              :items="resourceItems"
              item-title="display"
              item-value="id"
              :label="t('نام یا کد منبع')"
              variant="outlined"
              density="compact"
              prepend-inner-icon="mdi-account-search"
              :hint="selectedObj ? selectedObj.display : ''"
              persistent-hint
              hide-details="auto"
              color="primary"
              :dir="isRtl ? 'rtl' : 'ltr'"
              @update:search="onType"
            />
          </v-col>

          <v-col cols="12" md="4" class="text-center">
            <v-chip color="primary" variant="flat" class="mt-2">
              {{ t('تعداد') }}: {{ resourceItems.length }}
            </v-chip>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Details Window -->
    <v-card v-if="model" class="details-window" elevation="2" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="bg-primary">
        <v-icon icon="mdi-account-details" class="mr-2"></v-icon>
        {{ t('مشخصات منبع') }}
      </v-card-title>
      <v-divider class="bg-white"></v-divider>
      <!-- Header Info -->
      <v-row class="mb-4 bg-primary" align="center">
        <v-col cols="auto">
          <v-avatar size="220" color="primary" class="mx-1">
            <v-img :src="model.picture || placeholder" :alt="t('تصویر')" cover>
              <template v-slot:error>
                <v-icon icon="mdi-account" size="40" color="white"></v-icon>
              </template>
            </v-img>
          </v-avatar>
        </v-col>

        <v-col>
          <v-row>
            <v-col cols="12">
              <h2 class="text-h6">{{ model.firstName }} {{ model.lastName }}</h2>
            </v-col>
            <v-col cols="12">
              <v-chip :color="statusChipColor" size="small" class="mr-2">
                {{ model.status || t('نامشخص') }}
              </v-chip>
              <v-chip variant="outlined" class="bg-black" size="small">
                {{ model.code_number || t('بدون کد') }} کود نمبر
              </v-chip>
            </v-col>
          </v-row>
        </v-col>
      </v-row>

      <!-- Information Tabs -->
      <v-tabs v-model="activeTab" color="primary" class="mb-4">
        <v-tab value="personal">
          <v-icon start icon="mdi-account"></v-icon>
          {{ t('اطلاعات شخصی') }}
        </v-tab>
        <v-tab value="professional">
          <v-icon start icon="mdi-briefcase"></v-icon>
          {{ t('اطلاعات کاری') }}
        </v-tab>
      </v-tabs>

      <v-window v-model="activeTab">
        <!-- Personal Information -->
        <v-window-item value="personal">
          <v-row>
            <v-col cols="12" md="6">
              <v-list lines="two" density="compact">
                <v-list-subheader>{{ t('مشخصات هویتی') }}</v-list-subheader>
                <v-list-item
                  v-for="item in personalInfoItems"
                  :key="item.key"
                  :title="item.value"
                  :subtitle="item.label"
                >
                  <template v-slot:prepend>
                    <v-icon :icon="item.icon" color="primary"></v-icon>
                  </template>
                </v-list-item>
              </v-list>
            </v-col>

            <v-col cols="12" md="6">
              <v-list lines="two" density="compact">
                <v-list-subheader>{{ t('تحصیلات و مشخصات') }}</v-list-subheader>
                <v-list-item
                  v-for="item in educationInfoItems"
                  :key="item.key"
                  :title="item.value"
                  :subtitle="item.label"
                >
                  <template v-slot:prepend>
                    <v-icon :icon="item.icon" color="primary"></v-icon>
                  </template>
                </v-list-item>
              </v-list>
            </v-col>

            <v-col cols="12">
              <v-list lines="two" density="compact">
                <v-list-subheader>{{ t('آدرس') }}</v-list-subheader>
                <v-list-item
                  v-for="item in addressItems"
                  :key="item.key"
                  :title="item.value"
                  :subtitle="item.label"
                >
                  <template v-slot:prepend>
                    <v-icon :icon="item.icon" color="primary"></v-icon>
                  </template>
                </v-list-item>
              </v-list>
            </v-col>
          </v-row>
        </v-window-item>

        <!-- Professional Information -->
        <v-window-item value="professional">
          <v-row>
            <v-col cols="12" md="6">
              <v-list lines="two" density="compact">
                <v-list-subheader>{{ t('سوابق شغلی') }}</v-list-subheader>
                <v-list-item
                  v-for="item in professionalInfoItems"
                  :key="item.key"
                  :title="item.value"
                  :subtitle="item.label"
                >
                  <template v-slot:prepend>
                    <v-icon :icon="item.icon" color="primary"></v-icon>
                  </template>
                </v-list-item>
              </v-list>
            </v-col>

            <v-col cols="12" md="6">
              <v-list lines="two" density="compact">
                <v-list-subheader>{{ t('فعالیت و تماس') }}</v-list-subheader>
                <v-list-item
                  v-for="item in activityInfoItems"
                  :key="item.key"
                  :title="item.value"
                  :subtitle="item.label"
                >
                  <template v-slot:prepend>
                    <v-icon :icon="item.icon" color="primary"></v-icon>
                  </template>
                </v-list-item>
              </v-list>
            </v-col>

            <v-col cols="12">
              <v-textarea
                :model-value="model.notes || '—'"
                :label="t('ملاحظات')"
                variant="outlined"
                disabled=""
                rows="3"
                readonly
                auto-grow
              ></v-textarea>
            </v-col>
          </v-row>
        </v-window-item>
      </v-window>
    </v-card>

    <!-- Empty State -->
    <v-card v-else class="text-center py-8" variant="outlined">
      <v-icon size="60" color="grey" class="mb-4">mdi-account-search</v-icon>
      <h3 class="text-h6 text-grey">{{ t('منبعی انتخاب نشده') }}</h3>
      <p class="text-grey">{{ t('لطفاً از باکس جستجو یک منبع انتخاب کنید') }}</p>
    </v-card>
  </v-container>
</template>

<script setup>
  import { ref, computed, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';

  const repo = useDatabaseRepository();
  const { t, locale } = useI18n();
  const RTL_LOCALES = ['fa', 'fa-AF', 'prs', 'ps', 'ar'];
  const isRtl = ref(RTL_LOCALES.includes(locale.value));
  watch(locale, (v) => {
    isRtl.value = RTL_LOCALES.includes(v);
  });
  /* Local state */
  const selectedId = ref(null);
  const loading = ref(false);
  const searchText = ref('');
  const activeTab = ref('personal');
  // script
  const selectedObj = computed(
    () => resourceItems.value.find((i) => i.id === selectedId.value) || null
  );

  /* Utilities */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  /* Data list for autocomplete */
  const rawList = computed(() => toArray(repo.Resources || repo.resource));

  const resourceItems = computed(() =>
    rawList.value.map((r) => ({
      id: r.id,
      display: r.code_number
        ? `${r.name || ''} — ${r.code_number}`
        : r.name || r.nickname || r.tazkira || `#${r.id}`,
      _raw: r,
    }))
  );

  /* Selected resource */
  const selectedItem = computed(
    () => resourceItems.value.find((i) => i.id === selectedId.value)?._raw || null
  );

  const model = computed(() => {
    if (!selectedItem.value) return null;
    const r = selectedItem.value;

    const [first, ...rest] = String(r.name || '')
      .trim()
      .split(/\s+/);
    const last = rest.join(' ');

    return {
      id: r.id,
      picture: r.picture,
      status: r.activity || r.halat_madani,

      // Identity
      name: r.name || '',
      firstName: first || r.name || '',
      lastName: last || r.nickname || '',

      // Personal info
      father_name: r.father_name || '',
      grand_father_name: r.grand_father_name || '',
      nickname: r.nickname || '',
      code_number: r.code_number || '',
      tazkira: r.tazkira || '',
      age: r.age || '',
      halat_madani: r.halat_madani || '',
      study_status: r.study_status || '',
      language: r.language || '',
      nation: r.nation || '',

      // Address info
      main_province: r.main_province || '',
      main_district: r.main_district || '',
      main_village: r.main_village || '',
      now_province: r.now_province || '',
      now_district: r.now_district || '',
      now_village: r.now_village || '',
      zone: r.zone || '',
      hawza: r.hawza || '',
      specific_place: r.specific_place || '',

      // Professional info
      old_job: r.old_job || '',
      new_job: r.new_job || '',
      job_place: r.job_place || '',
      activity: r.activity || '',
      resource_activity_type: r.resource_activity_type || '',
      resource_cover: r.resource_cover || '',
      resource_saved_date: r.resource_saved_date || '',
      project: r.project || '',
      company: r.company || '',
      resedint_id: r.resedint_id ?? '',
      officeemployee_id: r.officeemployee_id ?? '',

      // Contact info
      phone: r.phone_number || '',
      notes: r.notes ?? r.note ?? '',
    };
  });

  /* Status computations */
  const statusChipColor = computed(() => {
    const s = (model.value?.status || '').toString().toLowerCase();
    if (['active', 'on', 'salaried', 'working', 'valid'].some((k) => s.includes(k)))
      return 'success';
    if (['inactive', 'off', 'blocked', 'invalid'].some((k) => s.includes(k))) return 'error';
    return 'warning';
  });

  /* Tab data computations */
  const personalInfoItems = computed(() => [
    { key: 'name', label: t('نام'), value: model.value?.name || '—', icon: 'mdi-account' },
    {
      key: 'father_name',
      label: t('نام پدر'),
      value: model.value?.father_name || '—',
      icon: 'mdi-account-supervisor',
    },
    {
      key: 'tazkira',
      label: t('تذکره'),
      value: model.value?.tazkira || '—',
      icon: 'mdi-card-account-details',
    },
    {
      key: 'nickname',
      label: t('نام مستعار'),
      value: model.value?.nickname || '—',
      icon: 'mdi-tag',
    },
  ]);

  const educationInfoItems = computed(() => [
    { key: 'age', label: t('عمر'), value: model.value?.age || '—', icon: 'mdi-cake' },
    {
      key: 'halat_madani',
      label: t('حالت مدنی'),
      value: model.value?.halat_madani || '—',
      icon: 'mdi-heart',
    },
    {
      key: 'study_status',
      label: t('رتبه علمی'),
      value: model.value?.study_status || '—',
      icon: 'mdi-school',
    },
    {
      key: 'nation',
      label: t('قوم'),
      value: model.value?.nation || '—',
      icon: 'mdi-account-group',
    },
  ]);

  const addressItems = computed(() => [
    {
      key: 'main_province',
      label: t('ولایت اصلی'),
      value: model.value?.main_province || '—',
      icon: 'mdi-map',
    },
    {
      key: 'main_district',
      label: t('ولسوالی اصلی'),
      value: model.value?.main_district || '—',
      icon: 'mdi-map-outline',
    },
    {
      key: 'now_province',
      label: t('ولایت فعلی'),
      value: model.value?.now_province || '—',
      icon: 'mdi-map-marker',
    },
    {
      key: 'now_district',
      label: t('ولسوالی فعلی'),
      value: model.value?.now_district || '—',
      icon: 'mdi-map-marker-outline',
    },
  ]);

  const professionalInfoItems = computed(() => [
    {
      key: 'old_job',
      label: t('وظیفه سابقه'),
      value: model.value?.old_job || '—',
      icon: 'mdi-history',
    },
    {
      key: 'new_job',
      label: t('وظیفه فعلی'),
      value: model.value?.new_job || '—',
      icon: 'mdi-briefcase',
    },
    {
      key: 'job_place',
      label: t('محل وظیفه'),
      value: model.value?.job_place || '—',
      icon: 'mdi-office-building',
    },
    { key: 'company', label: t('شرکت'), value: model.value?.company || '—', icon: 'mdi-domain' },
  ]);

  const activityInfoItems = computed(() => [
    { key: 'activity', label: t('فعالیت'), value: model.value?.activity || '—', icon: 'mdi-run' },
    {
      key: 'resource_activity_type',
      label: t('نوع فعالیت'),
      value: model.value?.resource_activity_type || '—',
      icon: 'mdi-chart-bubble',
    },
    { key: 'phone', label: t('شماره تماس'), value: model.value?.phone || '—', icon: 'mdi-phone' },
    {
      key: 'resource_saved_date',
      label: t('تاریخ ثبت'),
      value: model.value?.resource_saved_date || '—',
      icon: 'mdi-calendar',
    },
  ]);

  /* Search functionality */
  let tmr = null;
  const onType = (val) => {
    searchText.value = val || '';
    clearTimeout(tmr);
    tmr = setTimeout(async () => {
      loading.value = true;
      try {
        repo.Search = searchText.value;
        await repo.FetchResourcesData?.({ page: 1, itemsPerPage: 20 }).catch(() => {});
      } finally {
        loading.value = false;
      }
    }, 300);
  };

  /* Prime initial data */
  (async () => {
    loading.value = true;
    try {
      await repo.FetchResourcesData?.({ page: 1, itemsPerPage: 20 }).catch(() => {});
    } finally {
      loading.value = false;
    }
  })();
</script>

<style scoped>
  .resource-container {
    max-width: 1200px;
    margin: 0 auto;
  }

  .search-window,
  .details-window {
    border-radius: 8px;
  }

  .bg-primary {
    background-color: rgb(var(--v-theme-primary));
    color: white;
  }
</style>
