<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('منابع فعال')" :subtitle="t('فهرست و جستجو')">
      <template #actions>
        <v-btn variant="text" prepend-icon="mdi-arrow-left" class="text-none" @click="goBack">
          {{ t('برگشت') }}
        </v-btn>
      </template>
    </AppBar>

    <!-- 🔍 Search + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="repo.search"
            :label="t('جستجو در منابع...')"
            density="comfortable"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            color="primary"
            rounded="lg"
            hide-details
            @keyup.enter="reload"
            clearable
            @click:clear="clearSearch"
            class="bg-white search-field"
          />
        </v-col>

        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            v-if="auth.can('resources.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('منبع جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
    <!-- Filters row (perPage + reload) — Vuetify only -->
    <v-container class="px-2 pt-2 pb-2" fluid :dir="isRtl ? 'rtl' : 'ltr'">
      <v-row justify="center" align="center" class="ga-2">
        <v-col cols="12" md="4">
          <v-select
            v-model="repo.perPage"
            :items="perPageItems"
            item-title="title"
            item-value="value"
            :label="t('صفحه‌بندی')"
            density="compact"
            variant="outlined"
            rounded="lg"
            hide-details
          />
        </v-col>

        <v-col cols="12" md="4">
          <v-btn
            block
            color="primary"
            variant="flat"
            rounded="lg"
            size="large"
            :loading="repo.loading"
            :disabled="repo.loading"
            prepend-icon="mdi-refresh"
            @click="reload"
          >
            {{ repo.loading ? t('در حال بارگیری…') : t('بازخوانی') }}
          </v-btn>
        </v-col>

        <v-col cols="12" md="4" class="d-flex justify-center">
          <v-alert type="info" variant="tonal" density="compact" rounded="lg" class="w-120">
            {{ t('معیار فعال بودن: داشتن واقعه یا اطلاعات یا راپور خیریت در ۳۰ روز اخیر') }}
          </v-alert>
        </v-col>
      </v-row>
    </v-container>

    <!-- 👁 Peek -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detailForPeek"
        :loading="loadingOne"
        title-key="name"
        image-key="picture"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- 📋 TABLE -->
    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="isRtl"
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      :dateKeys="['resource_saved_date']"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    >
      <!-- 🖼 Picture cell -->
      <template #cell-picture="{ row }">
        <v-avatar size="36" class="cursor-pointer" @click="openPreview(row)">
          <v-img v-if="getPhotoUrl(row)" :src="getPhotoUrl(row)" cover />
          <v-icon v-else size="20">mdi-account</v-icon>
        </v-avatar>
      </template>

      <!-- 👤 Office employee cell (route) -->
      <template #cell-office_employee_id="{ row }">
        <div v-if="getOfficeEmployee(row)" class="d-flex flex-column align-start">
          <v-tooltip :text="t('برای رفتن به صفحه کارمندان لطفا کلیک کنید')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                class="px-2 py-1 text-none"
                color="primary"
                size="default"
                variant="tonal"
                v-bind="tooltipProps"
                @click.stop="goOfficeEmployee(row)"
              >
                <div class="d-flex flex-column align-start">
                  <span class="text-body-2">
                    {{ getOfficeEmployee(row).code_number || t('بدون کود') }}
                  </span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-grey">-</span>
      </template>

      <!-- 📌 Events count -->
      <template #cell-events_count="{ row }">
        <div v-if="getRow(row)?.events_count > 0" class="d-flex flex-column align-start">
          <v-tooltip :text="t('برای دیدن واقعات این منبع کلیک کنید')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                class="px-2 py-1 text-none"
                color="success"
                size="default"
                variant="tonal"
                v-bind="tooltipProps"
                @click.stop="goResourceEvents(row)"
              >
                <div class="d-flex align-center ga-1">
                  <v-icon size="18">mdi-alert-circle-check</v-icon>
                  <span class="text-body-2">{{ getRow(row).events_count }}</span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-grey">-</span>
      </template>

      <!-- 🧩 Informations count -->
      <template #cell-informations_count="{ row }">
        <div v-if="getRow(row)?.informations_count > 0" class="d-flex flex-column align-start">
          <v-tooltip :text="t('برای دیدن اطلاعات این منبع کلیک کنید')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                class="px-2 py-1 text-none"
                color="info"
                size="default"
                variant="tonal"
                v-bind="tooltipProps"
                @click.stop="goResourceInformations(row)"
              >
                <div class="d-flex align-center ga-1">
                  <v-icon size="18">mdi-information-variant-circle</v-icon>
                  <span class="text-body-2">{{ getRow(row).informations_count }}</span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-grey">-</span>
      </template>

      <!-- 📑 Fifty-six formats count -->
      <template #cell-fifty_six_formats_count="{ row }">
        <div v-if="getRow(row)?.fifty_six_formats_count > 0" class="d-flex flex-column align-start">
          <v-tooltip :text="t('برای دیدن ۵۶ فارمت های این منبع کلیک کنید')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                class="px-2 py-1 text-none"
                color="pink"
                size="default"
                variant="tonal"
                v-bind="tooltipProps"
                @click.stop="goResourceFiftySixFormats(row)"
              >
                <div class="d-flex align-center ga-1">
                  <v-icon size="18">mdi-file-document-multiple-outline</v-icon>
                  <span class="text-body-2">{{ getRow(row).fifty_six_formats_count }}</span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-grey">-</span>
      </template>

      <!-- 🗺 Map cell -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="getRow(row).coordinates"
          :title="getRow(row).name"
          :subtitle="getRow(row).code_number"
          type="resources"
          :photo-url="getPhotoUrl(row)"
          :zoom="16"
        />
      </template>
    </FilterableTable>

    <!-- 🖼 BIG PREVIEW -->
    <v-dialog v-model="previewOpen" max-width="520" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card :dir="isRtl ? 'rtl' : 'ltr'">
        <v-toolbar
          density="compact"
          color="primary"
          :title="t('پیش نمایش عکس منبع')"
          class="text-white"
        />
        <v-card-text class="d-flex justify-center">
          <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="480" max-height="420" cover />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn color="primary" variant="flat" @click="previewOpen = false">
            {{ t('بستن') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- error -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';

  import AppBar from '@/components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';

  import { useResourceActivityRepository } from '@/stores/ResourceActivityRepository';
  import { useLoginRepository } from '@/stores/LoginRepository';

  const router = useRouter();
  const { t, locale } = useI18n();
  const repo = useResourceActivityRepository();
  const auth = useLoginRepository();

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  function goBack() {
    router.back();
  }

  function getRow(payload) {
    return payload?.raw ?? payload;
  }
  // in <script setup>
  const perPageItems = [
    { title: t('همه / بدون صفحه‌بندی'), value: 'all' },
    { title: '10', value: 10 },
    { title: '25', value: 25 },
    { title: '50', value: 50 },
  ];

  function normalizeUrl(url) {
    if (!url) return null;
    return url.replace('/storage/storage/', '/storage/');
  }

  function getPhotoUrl(payload) {
    const row = getRow(payload);
    return normalizeUrl(row?.picture || row?.picture_url);
  }

  function getOfficeEmployee(payload) {
    const row = getRow(payload);
    return row?.office_employee || null;
  }

  const rowsFT = computed(() => repo.active.items || []);
  const filters = ref({});

  // Preview
  const previewOpen = ref(false);
  const previewSrc = ref(null);

  function openPreview(rowPayload) {
    const url = getPhotoUrl(rowPayload);
    if (!url) return;
    previewSrc.value = url;
    previewOpen.value = true;
  }

  // Peek
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  const detailForPeek = computed(() => {
    if (!detail.value) return null;
    return { ...detail.value, picture: getPhotoUrl(detail.value) };
  });

  async function fetchOne(id) {
    if (!id) return;
    loadingOne.value = true;
    try {
      const data = await repo.fetchOne(id);
      detail.value = data || {};
    } catch (e) {
      console.error(e);
    } finally {
      loadingOne.value = false;
    }
  }

  function openPeek(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }

  // routes like Resources page
  function goCreate() {
    router.push('/app/resourcesform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/resourcesform/${row.id}`);
  }

  function goOfficeEmployee(payload) {
    const row = getRow(payload);
    const empId = row?.office_employee_id || row?.office_employee?.id;
    if (!empId) return;
    router.push(`/app/officeemployeeform/${empId}`);
  }

  function goResourceEvents(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push({ path: '/app/events', query: { resource_id: row.id } });
  }

  function goResourceInformations(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push({ path: '/app/informations', query: { resource_id: row.id } });
  }

  function goResourceFiftySixFormats(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push({ path: '/app/fifthyformatsroute', query: { resource_id: row.id } });
  }

  // errors
  const showError = ref(false);
  const errorMessage = ref('');

  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  async function reload() {
    try {
      await repo.fetchCounts();
      await repo.fetchList('active');
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست منابع فعال'));
    }
  }

  function clearSearch() {
    repo.search = '';
    reload();
  }

  // headers same as Resources page
  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },

    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('کود نمبر'), key: 'code_number' },
    { title: t('تذکره نمبر'), key: 'tazkira' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی'), key: 'main_district' },
    { title: t('قریه'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک'), key: 'now_street_number' },
    { title: t('شماره خانه'), key: 'now_home_number' },

    { title: t('وظیفه سابقه'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('محل وظیفه'), key: 'job_place' },

    { title: t('فعالیت'), key: 'activity' },
    { title: t('حالت مدنی'), key: 'halat_madani' },
    { title: t('عمر'), key: 'age' },
    { title: t('رتبه علمی'), key: 'elmi_level' },
    { title: t('فاکولته/دیپارتمنت'), key: 'faculty' },
    { title: t('مهارت‌های خاص'), key: 'specific_skills' },
    { title: t('دوره/آموزش'), key: 'train' },

    { title: t('تاریخ ثبت منبع'), key: 'resource_saved_date' },
    { title: t('نوع فعالیت منبع'), key: 'resource_activity_type' },

    { title: t('کارمند مربوطه'), key: 'office_employee_id' },

    { title: t('رزیدنت مربوطه'), key: 'resedint_code' },
    { title: t('پوشش منبع'), key: 'resource_cover' },
    { title: t('زبان'), key: 'language' },
    { title: t('قوم'), key: 'nation' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('کوردینات'), key: 'coordinates' },
    { title: t('نوت'), key: 'note' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('تعداد واقعات'), key: 'events_count' },
    { title: t('تعداد اطلاعات'), key: 'informations_count' },
    { title: t('تعداد ۵۶ فارمت'), key: 'fifty_six_formats_count' },

    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

  const topGroups = computed(() => [
    {
      title: t('معرفت'),
      keys: ['name', 'father_name', 'grand_father_name', 'nick_name', 'code_number', 'tazkira'],
    },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    {
      title: t('آدرس فعلی'),
      keys: [
        'now_province',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
      ],
    },
    { title: t('وظایف'), keys: ['old_job', 'now_job', 'job_place'] },
  ]);

  onMounted(async () => {
    repo.perPage = repo.perPage || 'all';
    await reload();
  });
</script>

<style scoped>
  .search-field :deep(.v-field) {
    min-height: 48px;
  }
  .cursor-pointer {
    cursor: pointer;
  }
  .text-grey {
    color: #9e9e9e;
  }
</style>
