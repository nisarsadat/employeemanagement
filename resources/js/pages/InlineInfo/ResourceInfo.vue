<template>
  <v-container fluid class="resource-container fontfamily" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Search Window -->
    <v-card class="search-window mb-4" elevation="2" rounded="lg" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="search-header gradient-bg d-flex">
        <v-icon icon="mdi-magnify" class="mr-2 fontfamily"></v-icon>
        {{ t('جستجوی منبع') }}
        <v-spacer></v-spacer>
        <v-chip color="primary" variant="elevated" class="ml-2 fontfamily" size="default">
          {{ t('تعداد') }}: {{ resourceItems.length }}
        </v-chip>
      </v-card-title>

      <v-card-text class="pa-4">
        <v-row align="center" no-gutters>
          <v-col cols="12" :dir="isRtl ? 'rtl' : 'ltr'">
            <v-autocomplete
              v-model="selectedId"
              :items="resourceItems"
              item-title="display"
              item-value="id"
              :label="t('نام یا کد منبع')"
              variant="outlined"
              density="compact"
              :hint="selectedObj ? selectedObj.display : ''"
              persistent-hint
              hide-details="auto"
              color="primary"
              bg-color="background"
              :loading="loading"
              class="search-field elevation-1 fontfamily"
              :dir="isRtl ? 'rtl' : 'ltr'"
              clearable
              no-filter
              @update:search="onType"
            >
              <template #prepend-inner>
                <v-icon :color="selectedId ? 'primary' : 'grey'" class="ml-1">
                  mdi-account-search
                </v-icon>
              </template>

              <template #selection="{ item }">
                <div class="d-flex align-center">
                  <v-avatar size="24" color="primary-lighten-5" class="mr-2">
                    <v-icon icon="mdi-account" size="14" color="primary"></v-icon>
                  </v-avatar>
                  <span class="text-truncate text-body-2 fontfamily">{{ item.title }}</span>
                </div>
              </template>
            </v-autocomplete>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Details Window -->
    <v-expand-transition>
      <v-card
        v-if="model"
        class="details-window elevation-2"
        rounded="lg"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <v-card-title class="details-header gradient-bg fontfamily">
          <v-icon icon="mdi-account-details" class="mr-2"></v-icon>
          {{ t('مشخصات منبع') }}
          <v-spacer></v-spacer>
          <v-chip
            :color="statusChipColor"
            size="large"
            class="status-chip fontfamily bg-black white font-extrabold text-xl py-2.5"
          >
            {{ model.activity || t('نامشخص') }}

            <span
              class="status-dot mx-2"
              :class="{
                active: statusState === 'active',
                inactive: statusState === 'inactive',
              }"
            ></span>
          </v-chip>
        </v-card-title>

        <v-card-text class="pa-4">
          <!-- Header Info -->
          <v-row align="center" class="mb-4">
            <!-- Avatar + Code -->
            <v-col cols="12" md="4" class="text-center">
              <div class="avatar-container">
                <v-avatar size="170" class="profile-avatar elevation-3 fontfamily">
                  <v-img
                    :src="model.picture || placeholder"
                    :alt="t('تصویر')"
                    cover
                    class="avatar-image"
                  >
                    <template #error>
                      <div class="avatar-fallback">
                        <v-icon icon="mdi-account" size="42" color="white"></v-icon>
                      </div>
                    </template>
                  </v-img>
                </v-avatar>

                <!-- Clickable code chip → Resources page -->
                <v-chip
                  color="primary"
                  variant="flat"
                  size="small"
                  class="mt-2 code-chip fontfamily"
                  @click="goToResources"
                >
                  <v-icon start size="default">mdi-identifier</v-icon>
                  {{ t('کود منبع') }}: {{ model.code_number || t('ندارد') }}
                </v-chip>
              </div>
            </v-col>

            <!-- Main info (Name / Father / Code) + Contacts -->
            <v-col cols="12" md="8">
              <div class="info-section">
                <!-- Labels + values in one row -->
                <div class="primary-info mb-3">
                  <div class="info-row">
                    <span class="info-label fontfamily">{{ t('نام') }}</span>
                    <span class="info-value fontfamily">
                      {{ model.name || t('بدون نام') }}
                    </span>
                  </div>

                  <div class="info-row" v-if="model.nick_name">
                    <span class="info-label fontfamily">{{ t('نام مستعار') }}</span>
                    <span class="info-value fontfamily">
                      {{ model.nick_name }}
                    </span>
                  </div>

                  <div class="info-row" v-if="model.office_employee_code">
                    <span class="info-label fontfamily">{{ t('کود نمبر کارمند') }}</span>
                    <span class="info-value fontfamily">
                      {{ model.office_employee_code }}
                    </span>
                  </div>
                </div>

                <v-divider class="mb-3"></v-divider>

                <!-- Contact info (phones) -->
                <div class="contact-info fontfamily">
                  <v-list lines="two" density="compact" class="bg-transparent">
                    <!-- Phone (main) -->
                    <v-list-item>
                      <template #prepend>
                        <v-icon color="primary" class="mr-3" size="18">mdi-phone</v-icon>
                      </template>
                      <v-list-item-title class="font-weight-medium text-body-2 fontfamily">
                        {{ t('شماره تماس') }}
                      </v-list-item-title>
                      <v-list-item-subtitle class="text-body-2 fontfamily">
                        {{ model.phone || t('شماره تماس موجود نیست') }}
                      </v-list-item-subtitle>
                    </v-list-item>

                    <!-- Phone 1 -->
                    <v-list-item v-if="model.phone1">
                      <template #prepend>
                        <v-icon color="primary" class="mr-3" size="18">mdi-phone</v-icon>
                      </template>
                      <v-list-item-title class="font-weight-medium text-body-2 fontfamily">
                        {{ t('شماره تماس ۱') }}
                      </v-list-item-title>
                      <v-list-item-subtitle class="text-body-2 fontfamily">
                        {{ model.phone1 }}
                      </v-list-item-subtitle>
                    </v-list-item>

                    <!-- Phone 2 -->
                    <v-list-item v-if="model.phone2">
                      <template #prepend>
                        <v-icon color="primary" class="mr-3" size="18">mdi-phone</v-icon>
                      </template>
                      <v-list-item-title class="font-weight-medium text-body-2 fontfamily">
                        {{ t('شماره تماس ۲') }}
                      </v-list-item-title>
                      <v-list-item-subtitle class="text-body-2 fontfamily">
                        {{ model.phone2 }}
                      </v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                </div>
              </div>
            </v-col>
          </v-row>

          <!-- Action Cards -->
          <v-row class="action-cards" dense>
            <v-col cols="12" md="4">
              <v-card
                class="action-card elevation-1"
                hover
                @click="goToEvents"
                :disabled="model.events_count === 0"
              >
                <v-card-text class="text-center pa-4">
                  <v-icon color="primary" size="32" class="mb-2"> mdi-calendar-text </v-icon>
                  <h3 class="text-body-2 font-weight-bold mb-1 fontfamily">
                    {{ t('واقعات') }}
                  </h3>
                  <v-chip
                    color="primary"
                    variant="outlined"
                    size="small"
                    class="count-chip fontfamily"
                  >
                    {{ model.events_count }}
                  </v-chip>
                  <v-btn
                    color="primary"
                    variant="text"
                    class="mt-2 text-caption fontfamily"
                    :disabled="model.events_count === 0"
                  >
                    {{ t('مشاهده') }}
                    <v-icon end size="16">mdi-arrow-left</v-icon>
                  </v-btn>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card
                class="action-card elevation-1"
                hover
                @click="goToInformations"
                :disabled="model.informations_count === 0"
              >
                <v-card-text class="text-center pa-4">
                  <v-icon color="primary" size="32" class="mb-2"> mdi-information </v-icon>
                  <h3 class="text-body-2 font-weight-bold mb-1 fontfamily">
                    {{ t('اطلاعات') }}
                  </h3>
                  <v-chip
                    color="primary"
                    variant="outlined"
                    size="small"
                    class="count-chip fontfamily"
                  >
                    {{ model.informations_count }}
                  </v-chip>
                  <v-btn
                    color="primary"
                    variant="text"
                    class="mt-2 text-caption fontfamily"
                    :disabled="model.informations_count === 0"
                  >
                    {{ t('مشاهده') }}
                    <v-icon end size="16">mdi-arrow-left</v-icon>
                  </v-btn>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card
                class="action-card elevation-1"
                hover
                @click="goToFifthyFormats"
                :disabled="model.fifty_six_formats_count === 0"
              >
                <v-card-text class="text-center pa-4">
                  <v-icon color="primary" size="32" class="mb-2"> mdi-file-multiple </v-icon>
                  <h3 class="text-body-2 font-weight-bold mb-1 fontfamily">
                    {{ t('۵۶ فارمت') }}
                  </h3>
                  <v-chip
                    color="primary"
                    variant="outlined"
                    size="small"
                    class="count-chip fontfamily"
                  >
                    {{ model.fifty_six_formats_count }}
                  </v-chip>
                  <v-btn
                    color="primary"
                    variant="text"
                    class="mt-2 text-caption fontfamily"
                    :disabled="model.fifty_six_formats_count === 0"
                  >
                    {{ t('مشاهده') }}
                    <v-icon end size="16">mdi-arrow-left</v-icon>
                  </v-btn>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-expand-transition>

    <!-- Empty State -->
    <v-fade-transition>
      <v-card
        v-if="!model"
        class="empty-state elevation-1 text-center py-8"
        variant="outlined"
        rounded="lg"
      >
        <v-icon size="56" color="grey-lighten-1" class="mb-3"> mdi-account-search-outline </v-icon>
        <h3 class="text-subtitle-1 text-grey mb-2 fontfamily">
          {{ t('منبعی انتخاب نشده') }}
        </h3>
        <p class="text-body-2 text-grey-lighten-1 mb-4 fontfamily">
          {{ t('لطفاً از باکس جستجو یک منبع انتخاب کنید') }}
        </p>
        <v-btn
          color="primary"
          variant="tonal"
          rounded="lg"
          size="default"
          @click="$el.querySelector('.search-field input')?.focus()"
        >
          <v-icon start size="16" class="fontfamily">mdi-magnify</v-icon>
          {{ t('جستجو را شروع کنید') }}
        </v-btn>
      </v-card>
    </v-fade-transition>
  </v-container>
</template>

<script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRouter } from 'vue-router';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import { useLookupRepository } from '@/stores/LookupRepository';

  const repo = useDatabaseRepository();
  const lookupRepo = useLookupRepository();
  const router = useRouter();
  const { t, locale } = useI18n();

  const RTL_LOCALES = ['fa', 'fa-AF', 'prs', 'ps', 'ar'];
  const isRtl = ref(RTL_LOCALES.includes(locale.value));
  watch(locale, (v) => {
    isRtl.value = RTL_LOCALES.includes(v);
  });

  /* Local state */
  const selectedId = ref(null);
  const selectedItemRaw = ref(null);
  const loading = ref(false);
  const searchText = ref('');
  const placeholder = '';

  const rawList = computed(() => lookupRepo.resources || []);

  const resourceItems = computed(() =>
    rawList.value.map((r) => ({
      id: String(r.id),
      display: r.code_number
        ? `${r.name || ''} — ${r.code_number}`
        : r.name || r.nick_name || r.tazkira || `#${r.id}`,
      _raw: r,
    }))
  );

  const selectedObj = computed(
    () => resourceItems.value.find((i) => i.id === selectedId.value) || null
  );

  watch(selectedId, (id) => {
    const found = resourceItems.value.find((i) => i.id === id);
    selectedItemRaw.value = found ? found._raw : null;
  });

  const model = computed(() => {
    if (!selectedItemRaw.value) return null;
    const r = selectedItemRaw.value;

    return {
      id: r.id,
      picture: r.picture,
      name: r.name || '',
      nick_name: r.nick_name || '',
      code_number: r.code_number || '',
      office_employee_code: r.office_employee?.code_number || r.employee_code || '',
      activity: r.activity || '',
      phone: r.phone_number || '',
      phone1: r.phone_number_1 || '',
      phone2: r.phone_number_2 || '',
      email: r.email || '',
      events_count: r.events_count ?? 0,
      informations_count: r.informations_count ?? 0,
      fifty_six_formats_count: r.fifty_six_formats_count ?? 0,
    };
  });

  const statusState = computed(() => {
    const s = (model.value?.activity || '').toString().toLowerCase();

    if (['فعال', 'active', 'on', 'working', 'valid'].some((k) => s.includes(k))) {
      return 'active';
    }

    if (['غیر فعال', 'inactive', 'off', 'blocked', 'invalid'].some((k) => s.includes(k))) {
      return 'inactive';
    }

    return 'unknown';
  });

  const statusChipColor = computed(() => {
    if (statusState.value === 'active') return 'white';
    if (statusState.value === 'inactive') return 'error';
    return 'warning';
  });

  /* Search */
  let tmr = null;
  const onType = (val) => {
    searchText.value = val || '';
    clearTimeout(tmr);
    tmr = setTimeout(async () => {
      loading.value = true;
      try {
        await lookupRepo.FetchResourcesLookup({
          search: searchText.value || '',
          perPage: 20,
        });
      } finally {
        loading.value = false;
      }
    }, 300);
  };

  /* Navigation */
  const goToEvents = () => {
    if (!model.value) return;
    router.push({
      path: '/app/events',
      query: { resource_id: model.value.id },
    });
  };

  const goToInformations = () => {
    if (!model.value) return;
    router.push({
      path: '/app/informations',
      query: { resource_id: model.value.id },
    });
  };

  const goToFifthyFormats = () => {
    if (!model.value) return;
    router.push({
      path: '/app/fifthyformatsroute',
      query: { resource_id: model.value.id },
    });
  };

  const goToResources = () => {
    if (!model.value) return;
    router.push({
      path: '/app/resources',
      query: { id: model.value.id },
    });
  };

  /* Initial load */
  onMounted(async () => {
    loading.value = true;
    try {
      await lookupRepo.FetchResourcesLookup({ search: '', perPage: 20 });
    } finally {
      loading.value = false;
    }
  });
</script>

<style scoped>
  .resource-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 16px;
    font-size: 0.9rem;
  }

  .search-window {
    border-radius: 12px;
    overflow: hidden;
  }

  .search-header {
    background: linear-gradient(
      135deg,
      rgb(var(--v-theme-primary)),
      rgb(var(--v-theme-primary-darken-1))
    );
    color: white;
    padding: 12px 18px;
    font-size: 0.95rem;
  }

  .gradient-bg {
    background: linear-gradient(
      135deg,
      rgb(var(--v-theme-primary)),
      rgb(var(--v-theme-primary-darken-1))
    );
  }

  .search-field {
    border-radius: 10px;
    transition: all 0.2s ease;
  }

  .search-field:hover {
    box-shadow: 0 2px 8px rgba(var(--v-theme-primary), 0.08);
  }

  .details-window {
    border-radius: 12px;
    overflow: hidden;
    animation: slideUp 0.3s ease-out;
  }

  .details-header {
    background: linear-gradient(
      135deg,
      rgb(var(--v-theme-primary)),
      rgb(var(--v-theme-primary-darken-1))
    );
    color: white;
    padding: 12px 18px;
    font-size: 0.95rem;
  }

  .status-chip {
    font-weight: 500;
    letter-spacing: 0.3px;
  }

  .avatar-container {
    position: relative;
  }

  .profile-avatar {
    border: 3px solid rgba(var(--v-theme-primary), 0.15);
    transition: transform 0.2s ease;
  }

  .profile-avatar:hover {
    transform: scale(1.03);
  }

  .avatar-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(
      135deg,
      rgb(var(--v-theme-primary)),
      rgb(var(--v-theme-primary-lighten-1))
    );
  }

  .code-chip {
    font-weight: 600;
    font-family: monospace;
    cursor: pointer;
  }

  .info-section {
    background: linear-gradient(
      to bottom,
      rgba(var(--v-theme-primary-lighten-5), 0.08),
      transparent
    );
    padding: 16px;
    border-radius: 12px;
    border: 1px solid rgba(var(--v-theme-primary), 0.08);
  }

  .primary-info {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 8px;
  }

  .info-row {
    display: flex;
    flex-direction: column;
    flex: 1 1 0;
    min-width: 0;
  }

  .info-label {
    font-size: 0.75rem;
    color: rgba(0, 0, 0, 0.6);
  }

  .info-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: rgba(0, 0, 0, 0.9);
  }

  .contact-info {
    background: white;
    border-radius: 10px;
    padding: 6px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
  }

  .action-cards {
    margin-top: 4px;
  }

  .action-card {
    border-radius: 12px;
    height: 100%;
    transition: all 0.2s ease;
    cursor: pointer;
    border-top: 3px solid transparent;
  }

  .action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1) !important;
    border-top-color: rgb(var(--v-theme-primary));
  }

  .count-chip {
    font-size: 0.9rem;
    font-weight: 600;
    min-width: 44px;
  }

  .empty-state {
    border-radius: 12px;
    border-style: dashed;
    border-width: 2px;
    animation: fadeIn 0.3s ease-out;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(16px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  @media (max-width: 960px) {
    .resource-container {
      padding: 12px;
    }

    .search-header,
    .details-header {
      padding: 10px 14px;
    }

    .info-section {
      padding: 12px;
    }
  }

  @media (max-width: 600px) {
    .profile-avatar {
      width: 140px !important;
      height: 140px !important;
    }

    .action-card {
      margin-bottom: 10px;
    }
  }

  .fontfamily {
    font-family: var(--app-font) !important;
  }

  .status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
  }

  .status-dot.active {
    background-color: #2e7d32;
  }

  .status-dot.inactive {
    background-color: #c62828;
  }
</style>
