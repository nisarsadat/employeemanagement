<template>
  <div class="p-4 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('کارمندان فعال')" :subtitle="t('فهرست و جستجو')">
      <template #actions>
        <v-btn variant="text" prepend-icon="mdi-arrow-left" class="text-none" @click="goBack">
          {{ t('برگشت') }}
        </v-btn>
      </template>
    </AppBar>

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
        v-model="repo.perPage"
        class="w-full rounded border border-gray-300 focus:border-gray-500 focus:ring-2 focus:ring-gray-200 px-3 py-2 text-sm"
      >
        <option value="all">{{ t('همه / بدون صفحه‌بندی') }}</option>
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
    <!-- Filterable / Printable table -->
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
      :all-label="t('همه')"
      @peek="openDetails"
    >
      <!-- ✅ Picture cell -->
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

    <!-- Peek drawer -->
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

    <!-- Errors -->
    <p v-if="repo.error" class="mt-3 px-3 py-2 text-sm rounded bg-red-50 text-red-700">
      {{ repo.error }}
    </p>

    <!-- Picture Modal -->
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

    <!-- Details Modal (unchanged) -->
    <div
      v-if="selected"
      class="fixed inset-0 z-[999] bg-black/60 backdrop-blur-sm overflow-y-auto"
      @click.self="selected = null"
    >
      <div class="mx-auto my-10 w-[min(980px,92vw)] bg-white rounded-xl shadow-2xl">
        <div class="flex items-center justify-between px-4 py-3 border-b">
          <div class="flex items-center gap-3">
            <button
              class="inline-flex items-center justify-center w-12 h-12 rounded overflow-hidden ring-1 ring-gray-200"
              @click="openPic(getPhotoUrl(selected))"
              v-if="selected"
            >
              <img
                v-if="getPhotoUrl(selected)"
                :src="getPhotoUrl(selected)"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full grid place-items-center text-gray-400">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-6 h-6"
                  viewBox="0 0 24 24"
                  fill="currentColor"
                >
                  <path
                    d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                  />
                </svg>
              </div>
            </button>
            <div>
              <div class="text-base font-semibold">{{ selected?.name ?? '—' }}</div>
              <div class="text-xs text-gray-500">{{ selected?.code_number ?? '—' }}</div>
            </div>
          </div>
          <button class="text-gray-600 hover:text-black" @click="selected = null">✕</button>
        </div>

        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <template v-for="(grp, gi) in fieldGroups" :key="gi">
              <div class="md:col-span-2 lg:col-span-3 text-[13px] font-semibold text-gray-700 mt-2">
                {{ grp.title }}
              </div>
              <template v-for="(f, fi) in grp.fields" :key="`${gi}-${fi}`">
                <div class="space-y-0.5">
                  <div class="text-[11px] text-gray-500">{{ f.label }}</div>
                  <div class="text-[13px]">
                    <template v-if="f.type === 'date'">
                      {{ displayDate(selected?.[f.key]) }}
                    </template>
                    <template v-else>
                      {{ pretty(selected?.[f.key]) }}
                    </template>
                  </div>
                </div>
              </template>
            </template>

            <div class="md:col-span-2 lg:col-span-3 mt-3">
              <div class="text-[13px] font-semibold text-gray-700">{{ t('شمارنده‌ها') }}</div>
              <div class="mt-2 flex flex-wrap gap-2 text-[12px]">
                <span class="px-2 py-1 rounded bg-blue-50 text-blue-700">
                  {{ t('پیښې') }}: {{ selected?.events_count ?? 0 }}
                </span>
                <span class="px-2 py-1 rounded bg-sky-50 text-sky-700">
                  {{ t('منابع') }}: {{ selected?.resources_count ?? 0 }}
                </span>
                <span class="px-2 py-1 rounded bg-indigo-50 text-indigo-700">
                  {{ t('مالومات') }}: {{ selected?.informations_count ?? 0 }}
                </span>
                <span class="px-2 py-1 rounded bg-violet-50 text-violet-700">
                  {{ t('اسناد') }}: {{ selected?.documents_count ?? 0 }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="px-4 py-3 border-t flex justify-end">
          <button
            class="px-3 py-1.5 text-sm rounded bg-gray-900 text-white hover:bg-gray-800"
            @click="selected = null"
          >
            {{ t('بستن') }}
          </button>
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
  import { useRouter } from 'vue-router';

  const router = useRouter();
  function goBack() {
    router.back();
  }

  const { t, locale } = useI18n();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));
  const repo = useOfficeEmployeeActivityRepository();

  const peekOpen = ref(false);
  const peekId = ref(null);
  const peekItem = ref(null);
  const peekLoading = ref(false);

  const filters = ref({});
  // ==============================
  // IMAGE URL FIX (FRONTEND ONLY)
  // ==============================

  // ⚠️ force backend base (DO NOT fallback to frontend origin)
  const API_BASE = (import.meta.env.VITE_BACKEND_URL || 'http://127.0.0.1:8000').replace(
    /\/+$/,
    ''
  );

  // unwrap row coming from FilterableTable / Peek
  function unwrapRow(r) {
    return r?.raw ?? r?.row ?? r;
  }

  // normalize slashes
  function normalizeUrl(url) {
    if (!url) return null;
    return String(url).trim().replace(/\\/g, '/');
  }

  // ✅ FINAL IMAGE URL BUILDER
  function getPhotoUrl(row) {
    const r = unwrapRow(row);
    const raw = r?.picture;
    if (!raw) return null;

    const u = normalizeUrl(raw);

    // already absolute
    if (/^https?:\/\//i.test(u)) return u;

    // your API returns: "uploads/office-employees/xxx.png"
    if (u.startsWith('uploads/')) return `${API_BASE}/${u}`;
    if (u.startsWith('/uploads/')) return `${API_BASE}${u}`;

    // fallback (safe)
    if (u.startsWith('/')) return `${API_BASE}${u}`;
    return `${API_BASE}/${u}`;
  }

  // ⚠️ DO NOT rewrite picture here
  const rowsActive = computed(() => repo.active.items || []);

  const picUrl = ref(null);
  function openPic(url) {
    picUrl.value = url || null;
  }

  const selected = ref(null);

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
      let rec = (repo.active.items || []).find((x) => x.id === id);
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
    } catch (e) {
      console.error('peek fetchOne error', e);
    } finally {
      peekLoading.value = false;
    }
  }

  function pretty(v) {
    return v === null || v === undefined || v === '' ? '—' : String(v);
  }

  const headers = computed(() => [
    { title: t('عکس'), key: 'picture', width: 64 },
    { title: t('نام'), key: 'name', width: 160 },
    { title: t('کود کارمند'), key: 'code_number', width: 110 },
    { title: t('شماره تماس'), key: 'phone_number', width: 130 },
    { title: t('فعالیت'), key: 'activity', width: 100 },
    { title: t('تاریخ شروع'), key: 'join_date', width: 120 },
    { title: t('تاریخ ترک'), key: 'left_date', width: 120 },
    { title: t('واقعات'), key: 'events_count', width: 90 },
    { title: t('منابع'), key: 'resources_count', width: 90 },
    { title: t('اطلاعات'), key: 'informations_count', width: 100 },
    { title: t('اسناد'), key: 'documents_count', width: 90 },

    { title: t('نام پدر'), key: 'father_name', width: 140 },
    { title: t('نام پدرکلان'), key: 'grand_father_name', width: 140 },
    { title: t('نام مستعار'), key: 'nick_name', width: 120 },
    { title: t('ولایت مربوطه'), key: 'Province_addr', width: 140 },

    { title: t('بست'), key: 'bast', width: 90 },
    { title: t('سطح'), key: 'level', width: 90 },
    { title: t('سابقه جهاد'), key: 'history_jahad', width: 120 },
    { title: t('شماره تذکره'), key: 'tazkira', width: 130 },

    { title: t('ولایت اصلی'), key: 'main_province', width: 120 },
    { title: t('ولسوالی اصلی'), key: 'main_district', width: 120 },
    { title: t('قریه اصلی'), key: 'main_village', width: 120 },

    { title: t('ولایت فعلی'), key: 'now_province', width: 120 },
    { title: t('ولسوالی فعلی'), key: 'now_district', width: 120 },
    { title: t('قریه فعلی'), key: 'now_village', width: 120 },

    { title: t('وظیفه مشخص'), key: 'specific_job', width: 140 },
    { title: t('قسم'), key: 'qisem', width: 100 },
    { title: t('زون'), key: 'zone', width: 100 },
    { title: t('حوزه'), key: 'hawza', width: 100 },

    { title: t('تحصیلات دینی'), key: 'relegy_study', width: 110 },
    { title: t('تحصیلات عصری'), key: 'new_study', width: 110 },
    { title: t('فاکولته'), key: 'faculty', width: 110 },
    { title: t('دروس نظامی'), key: 'nizami', width: 90 },
    { title: t('مهارت های سلاح'), key: 'gun_knowledge', width: 110 },

    { title: t('عمر'), key: 'age', width: 80 },
    { title: t('مهارت های خاص'), key: 'specific_skills', width: 140 },
    { title: t('زبان'), key: 'language', width: 100 },
    { title: t('قوم'), key: 'nation', width: 100 },

    { title: t('مختصات'), key: 'coordinates', width: 140 },
    { title: t('یادداشت'), key: 'note', width: 200 },
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

  const fieldGroups = computed(() => [
    { title: t('رسانه'), fields: [{ key: 'picture', label: t('عکس') }] },
    {
      title: t('هویت / شناسایی'),
      fields: [
        { key: 'name', label: t('نام') },
        { key: 'father_name', label: t('نام پدر') },
        { key: 'grand_father_name', label: t('نام پدرکلان') },
        { key: 'nick_name', label: t('نام مستعار') },
        { key: 'Province_addr', label: t('ولایت مربوطه') },
        { key: 'code_number', label: t('کود کارمند') },
      ],
    },
    {
      title: t('مرتبه و سوابق'),
      fields: [
        { key: 'bast', label: t('بست') },
        { key: 'level', label: t('سطح') },
        { key: 'history_jahad', label: t('سابقه جهاد') },
        { key: 'dilgay_mesher', label: t('دلګی مشران') },
        { key: 'jahadi_mawaqaf', label: t('مواقف جهادی') },
        { key: 'jahad_time_responsibility', label: t('مسئولیت در زمان جهاد') },
        { key: 'tazkira', label: t('تذکره') },
      ],
    },
    { title: t('محل جهاد'), fields: [{ key: 'jahad_place', label: t('محل جهاد') }] },
    {
      title: t('محل اصلی'),
      fields: [
        { key: 'main_province', label: t('ولایت اصلی') },
        { key: 'main_district', label: t('ولسوالی اصلی') },
        { key: 'main_village', label: t('قریه اصلی') },
      ],
    },
    {
      title: t('محل فعلی'),
      fields: [
        { key: 'now_province', label: t('ولایت فعلی') },
        { key: 'now_district', label: t('ولسوالی فعلی') },
        { key: 'now_village', label: t('قریه فعلی') },
        { key: 'now_specific_place', label: t('محل مشخص فعلی') },
        { key: 'now_street_number', label: t('نمبر کوچه') },
        { key: 'now_home_number', label: t('نمبر خانه') },
      ],
    },
    {
      title: t('وظیفه و اداره'),
      fields: [
        { key: 'specific_job', label: t('وظیفه مشخص') },
        { key: 'qisem', label: t('قسم') },
        { key: 'zone', label: t('زون') },
        { key: 'hawza', label: t('حوزه') },
      ],
    },
    {
      title: t('وضعیت و تحصیل'),
      fields: [
        { key: 'madani_halat', label: t('حالت مدنی') },
        { key: 'activity', label: t('وضعیت فعالیت') },
        { key: 'relegy_study', label: t('تحصیلات دینی') },
        { key: 'new_study', label: t('تحصیلات عصری') },
        { key: 'faculty', label: t('فاکولته') },
        { key: 'nizami', label: t('دروس نظامی') },
        { key: 'gun_knowledge', label: t('مهارت های سلاح') },
      ],
    },
    {
      title: t('مشخصات عمومی'),
      fields: [
        { key: 'age', label: t('عمر') },
        { key: 'specific_skills', label: t('مهارت‌های خاص') },
        { key: 'language', label: t('زبان') },
        { key: 'nation', label: t('قوم') },
      ],
    },
    {
      title: t('تماس و متفرقه'),
      fields: [
        { key: 'phone_number', label: t('تلفن') },
        { key: 'coordinates', label: t('مختصات') },
        { key: 'join_date', label: t('تاریخ شروع'), type: 'date' },
        { key: 'left_date', label: t('تاریخ ترک'), type: 'date' },
        { key: 'note', label: t('یادداشت') },
      ],
    },
  ]);

  let formatForCalendarFn = null;
  try {
    const cal = typeof useCalendar === 'function' ? useCalendar() : null;
    formatForCalendarFn =
      (cal && (cal.formatForCalendar || cal.formatDateForCalendar || cal.formatDate)) || null;
  } catch (e) {
    formatForCalendarFn = null;
  }

  function fallbackFormat(v) {
    if (!v) return '—';
    const s = String(v);
    const d = /^\d{4}-\d{2}-\d{2}/.test(s) ? new Date(s + 'T00:00:00') : new Date(v);
    if (isNaN(d.getTime())) return s;
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }

  function displayDate(v) {
    return formatForCalendarFn ? formatForCalendarFn(v) : fallbackFormat(v);
  }

  function reload() {
    if (repo.perPage === 'all') repo.setPage(1);
    repo.fetchCounts();
    repo.fetchList('active');
  }

  onMounted(async () => {
    await repo.fetchCounts();
    await repo.fetchList('active');
  });
</script>
