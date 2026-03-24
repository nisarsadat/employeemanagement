<template>
  <div class="p-4 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('کارمندان فعال')" :subtitle="t('فهرست و جستجو')" />

    <!-- Filters row -->
    <div class="mb-3 grid grid-cols-1 md:grid-cols-3 gap-2">
      <input
        type="text"
        v-model.trim="repo.search"
        :placeholder="t('جستجو (نام/کد/تلفن)')"
        class="w-full rounded border border-gray-300 focus:border-gray-500 focus:ring-2 focus:ring-gray-200 px-3 py-2 text-sm"
        @keyup.enter="reload"
      />

      <select
        v-model="repo.itemsPerPage"
        class="w-full rounded border border-gray-300 focus:border-gray-500 focus:ring-2 focus:ring-gray-200 px-3 py-2 text-sm"
        @change="handlePerPageChange(repo.itemsPerPage)"
      >
        <option :value="10">10</option>
        <option :value="25">25</option>
        <option :value="50">50</option>
      </select>

      <button
        type="button"
        class="px-3 py-2 text-sm rounded bg-gray-900 text-white hover:bg-gray-800"
        @click="reload"
        :disabled="repo.loading"
      >
        {{ repo.loading ? t('در حال بارگیری…') : t('بازخوانی') }}
      </button>
    </div>

    <v-col cols="12" md="4" class="d-flex justify-center">
      <v-alert type="info" variant="tonal" density="compact" rounded="lg" class="w-120">
        {{ t('معیار فعال بودن: داشتن ۱۰۰ عدد منابع فعال') }}
      </v-alert>
    </v-col>

    <FilterablePrintTable
      :headers="headers"
      :rows="rowsActive"
      :filters="filters"
      @update:filters="(f) => (filters = f)"
      :rtl="isRtl"
      :top-groups="topGroups"
      :dropdown-keys="dropdownKeys"
      :selectable="true"
      :enable-edit="false"
      :enable-peek="true"
      :auto-detect-dates="true"
      :date-keys="dateKeys"
      :row-key="'id'"
      :show-index="true"
      :options-map="null"
      :include-empty-option="true"
      @peek="openDetails"
    >
      <template #cell-picture="{ row }">
        <button
          class="inline-flex items-center justify-center w-9 h-9 rounded overflow-hidden ring-1 ring-gray-200 hover:ring-gray-300"
          type="button"
          @click="openPic(getPhotoUrl(row))"
        >
          <v-img
            v-if="getPhotoUrl(row)"
            :src="getPhotoUrl(row)"
            class="w-full h-full object-cover"
            loading="lazy"
            referrerpolicy="no-referrer"
          />
          <div v-else class="w-full h-full grid place-items-center text-gray-400">No Pic</div>
        </button>
      </template>
    </FilterablePrintTable>

    <GlobalPagination
      :page="repo.active.page"
      :items-per-page="repo.itemsPerPage"
      :total="repo.active.totalItems"
      @update:page="handlePageChange"
      @update:itemsPerPage="handlePerPageChange"
    />

    <PeekButton
      v-model="peekOpen"
      :id="peekId"
      :item="peekItem"
      :loading="peekLoading"
      title-key="name"
      image-key="picture"
      :fields="headers"
      :cols="3"
      :dir="isRtl ? 'rtl' : 'ltr'"
      @fetch="fetchOne"
    />

    <p v-if="repo.error" class="mt-3 px-3 py-2 text-sm rounded bg-red-50 text-red-700">
      {{ repo.error }}
    </p>

    <div
      v-if="picUrl"
      class="fixed inset-0 z-[999] bg-black/70 backdrop-blur-sm grid place-items-center p-4"
      @click.self="picUrl = null"
    >
      <div class="bg-white rounded-lg shadow-2xl max-w-[80vw]">
        <div class="flex items-center justify-between px-4 py-2 border-b">
          <div class="text-sm font-medium">{{ t('عکس کارمند') }}</div>
          <button class="text-gray-600 hover:text-black" @click="picUrl = null">✕</button>
        </div>
        <div class="p-3">
          <img :src="picUrl" class="max-h-[70vh] max-w-full object-contain rounded-md" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { computed, onMounted, ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useOfficeEmployeeActivityRepository } from '@/stores/OfficeEmployeeActivityRepository';
  import FilterablePrintTable from '../../components/FilterableTable.vue';
  import { useCalendar } from '../../../composables/useCalendar';
  import PeekButton from '../../components/PeekButton.vue';
  import AppBar from '@/components/AppBar.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  const STATUS = 'active';

  const { t, locale } = useI18n();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));
  const repo = useOfficeEmployeeActivityRepository();

  const peekOpen = ref(false);
  const peekId = ref(null);
  const peekItem = ref(null);
  const peekLoading = ref(false);

  const filters = ref({});
  const picUrl = ref(null);

  const API_BASE = (import.meta.env.VITE_BACKEND_URL || 'http://127.0.0.1:8000').replace(
    /\/+$/,
    ''
  );

  function unwrapRow(r) {
    return r?.raw ?? r?.row ?? r;
  }

  function normalizeUrl(url) {
    if (!url) return null;
    return String(url).trim().replace(/\\/g, '/');
  }

  function getPhotoUrl(row) {
    const r = unwrapRow(row);
    const raw = r?.picture;
    if (!raw) return null;

    const u = normalizeUrl(raw);

    if (/^https?:\/\//i.test(u)) return u;
    if (u.startsWith('uploads/')) return `${API_BASE}/${u}`;
    if (u.startsWith('/uploads/')) return `${API_BASE}${u}`;
    if (u.startsWith('/')) return `${API_BASE}${u}`;
    return `${API_BASE}/${u}`;
  }

  const rowsActive = computed(() => repo.active.items || []);

  function openPic(url) {
    picUrl.value = url || null;
  }

  function openDetails(payload) {
    const row = payload && (payload.raw || payload.row) ? payload.raw || payload.row : payload;
    if (!row || !row.id) return;

    peekItem.value = { ...row, picture: getPhotoUrl(row) };
    peekId.value = row.id;
    peekOpen.value = true;
    fetchOne(row.id);
  }

  async function fetchOne(id) {
    peekLoading.value = true;
    try {
      const rec = (repo.active.items || []).find((x) => x.id === id);
      if (rec) {
        const fmt = (v) => {
          if (!v) return '—';
          const s = String(v);
          const d = /^\d{4}-\d{2}-\d{2}$/.test(s) ? new Date(s + 'T00:00:00') : new Date(v);
          if (isNaN(d.getTime())) return s;
          const y = d.getFullYear();
          const m = String(d.getMonth() + 1).padStart(2, '0');
          const day = String(d.getDate()).padStart(2, '0');
          return `${y}-${m}-${day}`;
        };

        peekItem.value = {
          ...rec,
          picture: getPhotoUrl(rec),
          join_date: fmt(rec.join_date),
          left_date: fmt(rec.left_date),
        };
      }
    } finally {
      peekLoading.value = false;
    }
  }

  const headers = computed(() => [
    { title: t('اطلاعات'), key: 'informations_count', sortable: false },
    { title: t('حوادث'), key: 'events_count | 0', sortable: false },
    { title: t('منابع'), key: 'resources_count', sortable: false },
    { title: t('منابع فعال'), key: 'resources_active_count', sortable: false },
    { title: t('منابع غیر فعال'), key: 'resources_inactive_count', sortable: false },

    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('کود نمبر'), key: 'code_number' },
    { title: t('شماره تذکره'), key: 'tazkira' },
    { title: t('سن'), key: 'age' },

    { title: t('نوعیت وظیفه'), key: 'job_type' },
    { title: t('وظیفه مشخص'), key: 'specific_job' },
    { title: t('قسم'), key: 'qisem' },
    { title: t('بست'), key: 'bast' },
    { title: t('رتبه/سطح'), key: 'level' },
    { title: t('زون'), key: 'zone' },
    { title: t('حوزه'), key: 'hawza' },

    { title: t('سابقه جهاد'), key: 'history_jahad' },
    { title: t('دلګی مشر'), key: 'dilgay_mesher' },
    { title: t('موقف جهادی'), key: 'jahadi_mawaqaf' },
    { title: t('مسئولیت جهاد'), key: 'jahad_time_responsibility' },
    { title: t('محل جهاد'), key: 'jahad_place' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },
    { title: t('محل مشخص فعلی'), key: 'now_specific_place' },
    { title: t('نمبر سرک'), key: 'now_street_number' },
    { title: t('نمبر خانه'), key: 'now_home_number' },

    { title: t('تعلیمات دینی'), key: 'relegy_study' },
    { title: t('تعلیمات عصری'), key: 'new_study' },
    { title: t('پوهنحی/رشته'), key: 'faculty' },
    { title: t('نظامی'), key: 'nizami' },
    { title: t('معلومات سلاح'), key: 'gun_knowledge' },

    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('زبان'), key: 'language' },
    { title: t('قوم/ملیت'), key: 'nation' },
    { title: t('مهارت‌های خاص'), key: 'specific_skills' },
    { title: t('فعالیت'), key: 'activity' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('مخافات'), key: 'makhafat' },
    { title: t('مکافات'), key: 'makafat' },

    { title: t('تاریخ آمدن'), key: 'join_date' },
    { title: t('تاریخ رفتن'), key: 'left_date' },
    { title: t('پول داده شده'), key: 'payed_money' },
    { title: t('ملاحظات'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('رسانه'), keys: ['picture'] },
    {
      title: t('هویت'),
      keys: [
        'name',
        'father_name',
        'grand_father_name',
        'nick_name',
        'Province_addr',
        'code_number',
        'tazkira',
        'phone_number',
      ],
    },
    { title: t('وضعیت/تاریخ'), keys: ['activity', 'join_date', 'left_date'] },
    { title: t('محل اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('محل فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
    { title: t('وظیفه/اداره'), keys: ['specific_job', 'qisem', 'zone', 'hawza'] },
    {
      title: t('تحصیلات/مهارت'),
      keys: ['relegy_study', 'new_study', 'faculty', 'nizami', 'gun_knowledge', 'specific_skills'],
    },
    { title: t('عمومی'), keys: ['age', 'language', 'nation', 'coordinates', 'note'] },
    {
      title: t('شمارنده‌ها'),
      keys: ['events_count', 'resources_count', 'informations_count', 'documents_count'],
    },
  ]);

  const dropdownKeys = computed(() => headers.value.map((h) => h.key).filter(Boolean));
  const dateKeys = ['join_date', 'left_date'];

  let formatForCalendarFn = null;
  try {
    const cal = typeof useCalendar === 'function' ? useCalendar() : null;
    formatForCalendarFn =
      (cal && (cal.formatForCalendar || cal.formatDateForCalendar || cal.formatDate)) || null;
  } catch {
    formatForCalendarFn = null;
  }

  function fetchAll() {
    return repo.fetchList(STATUS);
  }

function normalizePerPage(value) {
  if (value === 10) return 10;
  const n = Number(value);
  return Number.isFinite(n) && n > 0 ? n : 10;
}

function reload() {
  repo.inactive.page = 1;
  repo.fetchCounts();
  fetchAll();
}

function handlePageChange(value) {
  repo.inactive.page = Number(value) || 1;
  fetchAll();
}

function handlePerPageChange(value) {
  repo.itemsPerPage = normalizePerPage(value);
  repo.inactive.page = 1;
  fetchAll();
}

  usePageRefresh('/app/activeemployees', async () => {
    repo.setPage(STATUS, 1);
    await repo.fetchCounts();
    await fetchAll();
  });

  onMounted(async () => {
    await repo.fetchCounts();
    await fetchAll();
  });
</script>