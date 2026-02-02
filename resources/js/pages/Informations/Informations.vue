<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('اطلاعات')" :subtitle="t('فهرست و جستجو')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- Search + Create + Year/Month Filter -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search -->
        <v-col cols="12" sm="12" md="12" lg="12">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو در اطلاعات...')"
            density="comfortable"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            color="primary"
            rounded="lg"
            hide-details
            @keyup.enter="fetchAll"
            clearable
            @click:clear="clearSearch"
            class="bg-white"
          />
        </v-col>

        <!-- ✅ NEW: Year -->
        <v-col cols="12" sm="7" md="7" lg="7">
          <v-select
            v-model="filterYear"
            :items="yearItems"
            item-title="title"
            item-value="value"
            density="comfortable"
            variant="outlined"
            rounded="lg"
            hide-details
            clearable
            :label="t('سال')"
            class="bg-white"
          />
        </v-col>

        <!-- ✅ NEW: Month -->
        <v-col cols="12" sm="4" md="2" lg="2">
          <v-select
            v-model="filterMonth"
            :items="monthItems"
            item-title="title"
            item-value="value"
            density="comfortable"
            variant="outlined"
            rounded="lg"
            hide-details
            clearable
            :label="t('ماه')"
            class="bg-white"
          />
        </v-col>

        <!-- ✅ NEW: Filter button -->
        <v-col cols="12" sm="4" md="1" lg="1">
          <v-btn
            color="primary"
            variant="flat"
            rounded="lg"
            block
            class="text-none"
            prepend-icon="mdi-filter"
            @click="applyMonthYearFilter"
          >
            {{ t('فلتر') }}
          </v-btn>
        </v-col>

        <!-- Create -->
        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            @click="goCreate"
            prepend-icon="mdi-plus"
          >
            {{ t('جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- STATS CARDS -->
    <v-row class="px-2 d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('resources')"
          :class="{ 'active-stat': activeStat === 'resources' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ totalResourcesCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کل اطلاعات') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-account-multiple</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('saeen')"
          :class="{ 'active-stat': activeStat === 'saeen' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-amber fontfamily">
                {{ saeenFasadCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('ساعین فساد') }}
              </div>
            </div>
            <v-avatar color="amber" variant="tonal" size="48">
              <v-icon color="amber">mdi-alert-circle</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('shar')"
          :class="{ 'active-stat': activeStat === 'shar' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-purple fontfamily">
                {{ sharOFasadCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('شر او فساد') }}
              </div>
            </div>
            <v-avatar color="purple" variant="tonal" size="48">
              <v-icon color="purple">mdi-fire</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('spy')"
          :class="{ 'active-stat': activeStat === 'spy' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ jasosiCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('جاسوسی') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-shield-search</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('today')"
          :class="{ 'active-stat': activeStat === 'today' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ todayCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('امروز ثبت شده') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-calendar-today</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Peek -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title="جزییات اطلاعات"
        :name-key="'name'"
        :fields="headersFT"
        dir="rtl"
        @close="expanded = false"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- List -->
    <FilterableTable
      :headers="headersFT"
      :rows="filteredRows"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :autoDetectDates="false"
      :dateKeys="['information_saved_date']"
      class="mb-20 mx-2"
    >
      <!-- RESOURCE -->
      <template #cell-resource_id="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip v-if="row.resource_id" text="برای رفتن به صفحه منابع کلیک کنید" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="primary"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToResource(row)"
              >
                <v-icon start size="16">mdi-account-search</v-icon>
                <span class="text-caption">{{ row.resource_id }}</span>
              </v-btn>
            </template>
          </v-tooltip>
          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div>
      </template>

      <!-- OFFICE EMPLOYEE codes -->
      <template #cell-office_employee_code_number="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip
            v-if="row.office_employee_code_number"
            text="برای رفتن به صفحه کارمندان مدیریت کلیک کنید"
            location="top"
          >
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="deep-purple"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToOfficeEmployee(row)"
              >
                <v-icon start size="16">mdi-account-badge</v-icon>
                <span class="text-caption">{{ row.office_employee_code_number }}</span>
              </v-btn>
            </template>
          </v-tooltip>
          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div>
      </template>

      <template #cell-office_employee_info_code_number="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip
            v-if="row.office_employee_info_code_number"
            text="برای رفتن به صفحه کارمندان مدیریت کلیک کنید"
            location="top"
          >
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="deep-orange"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToOfficeEmployee(row)"
              >
                <v-icon start size="16">mdi-account-badge</v-icon>
                <span class="text-caption">{{ row.office_employee_info_code_number }}</span>
              </v-btn>
            </template>
          </v-tooltip>
          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div>
      </template>

      <!-- Finance status -->
      <template #cell-finance_status="{ row }">
        <v-chip
          size="small"
          label
          class="fontfamily"
          :color="financeStatusColor(getRow(row).finance_status)"
          variant="tonal"
        >
          <v-icon start size="14">mdi-cash-check</v-icon>
          {{ financeStatusLabel(getRow(row).finance_status) }}
        </v-chip>
      </template>

      <!-- Actions -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip text="مشاهده اطلاع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="تغییر جزیات اطلاع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="amber-darken-2"
                @click.stop="startEdit(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="دانلود اسناد مربوط به اطلاع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="green-darken-2"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadDocuments(item)"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="حذف اطلاع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="red-darken-2"
                @click.stop="openDeleteDialog(item)"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="فرم معلومات (PDF / Word)" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="primary"
                @click.stop="goPrint(item)"
              >
                mdi-file-document-outline
              </v-icon>
            </template>
          </v-tooltip>
        </div>
      </template>
    </FilterableTable>

    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />

    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, computed, watch } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import dayjs from 'dayjs';

  import AppBar from '../../components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';

  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';

  import { useCalendar } from '../../../composables/useCalendar';

  const auth = useLoginRepository();
  const { t, locale } = useI18n();
  const repo = useDatabaseRepository();
  const router = useRouter();
  const route = useRoute();

  const { calendar, formatYearDisplay } = useCalendar();

  /* ===== RTL ===== */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => (isRtl.value = newLocale === 'fa'));

  /* ===== unwrap helper ===== */
  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  /* ==========================
   * ✅ NEW: Month/Year Filters
   * ========================== */
  const filterYear = ref(null); // gregorian year number
  const filterMonth = ref(null); // 1..12

  const yearItems = ref([]); // [{value: 2026, title:'...'}]
  const yearNumbers = ref([]);

  async function fetchYearItemsFromDb() {
    try {
      const res = await repo.FetchInformationDateOptions?.({
        date_field: 'information_saved_date',
      });
      const years = res?.data?.years || [];

      yearNumbers.value = years.map((y) => Number(y)).filter((n) => Number.isFinite(n));

      yearItems.value = yearNumbers.value.map((y) => ({
        value: y,
        title: typeof formatYearDisplay === 'function' ? formatYearDisplay(y) : String(y),
      }));
    } catch (e) {
      console.error('fetchYearItemsFromDb error', e);
    }
  }

  // auto-update year titles when calendar/locale changes (selected value stays same)
  watch(
    [calendar, locale],
    () => {
      if (!Array.isArray(yearNumbers.value) || yearNumbers.value.length === 0) return;
      yearItems.value = yearNumbers.value.map((y) => ({
        value: y,
        title: typeof formatYearDisplay === 'function' ? formatYearDisplay(y) : String(y),
      }));
    },
    { immediate: false }
  );

  const JALALI_MONTHS_FA = [
    'حمل',
    'ثور',
    'جوزا',
    'سرطان',
    'اسد',
    'سنبله',
    'میزان',
    'عقرب',
    'قوس',
    'جدی',
    'دلو',
    'حوت',
  ];
  const HIJRI_QAMARI_MONTHS_FA = [
    'محرم',
    'صفر',
    'ربیع‌الاول',
    'ربیع‌الثانی',
    'جمادی‌الاول',
    'جمادی‌الثانی',
    'رجب',
    'شعبان',
    'رمضان',
    'شوال',
    'ذوالقعده',
    'ذوالحجه',
  ];

  function monthTitleFromGregorianMonth(gYear, gMonth) {
    const iso = `${gYear || dayjs().year()}-${String(gMonth).padStart(2, '0')}-15`;

    if (calendar.value === 'gregorian') {
      try {
        const dt = new Date(iso + 'T00:00:00');
        return new Intl.DateTimeFormat(locale.value, { month: 'long' }).format(dt);
      } catch {
        return String(gMonth);
      }
    }

    if (calendar.value === 'jalali') {
      try {
        const dt = new Date(iso + 'T00:00:00');
        const fmt = new Intl.DateTimeFormat(locale.value, {
          calendar: 'persian',
          month: 'numeric',
        });
        const parts = fmt.formatToParts(dt);
        const m = Number(parts.find((p) => p.type === 'month')?.value);
        if (m >= 1 && m <= 12) return JALALI_MONTHS_FA[m - 1];
      } catch {}
      return JALALI_MONTHS_FA[(gMonth - 1) % 12];
    }

    if (calendar.value === 'hijri') {
      try {
        const dt = new Date(iso + 'T00:00:00');
        const fmt = new Intl.DateTimeFormat('ar', {
          calendar: 'islamic-umalqura',
          month: 'numeric',
        });
        const parts = fmt.formatToParts(dt);
        const m = Number(parts.find((p) => p.type === 'month')?.value);
        if (m >= 1 && m <= 12) return HIJRI_QAMARI_MONTHS_FA[m - 1];
      } catch {}
      return HIJRI_QAMARI_MONTHS_FA[(gMonth - 1) % 12];
    }

    return String(gMonth);
  }

  const monthItems = computed(() => {
    const y = filterYear.value || dayjs().year();
    return Array.from({ length: 12 }).map((_, i) => {
      const m = i + 1;
      return { value: m, title: monthTitleFromGregorianMonth(y, m) };
    });
  });

  async function applyMonthYearFilter() {
    await fetchAll();
  }

  /* ===== Error snackbar ===== */
  const showError = ref(false);
  const errorMessage = ref('');
  function showErrorMessage(message) {
    errorMessage.value = message;
    showError.value = true;
  }

  /* ===== Active stat cards ===== */
  const activeStat = ref(null);

  function filterByStat(stat) {
    activeStat.value = activeStat.value === stat ? null : stat;
  }

  /* ===== Data sources ===== */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  const allRows = computed(() => toArray(repo.Informations));
  const totalResourcesCount = computed(() => allRows.value.length);

  const todayCount = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    return allRows.value.filter((item) => item.information_saved_date === today).length;
  });

  function matchType(item, wanted) {
    const a = (item?.syasi_info_type ?? '').toString().trim();
    const b = (item?.jenayee_info_type ?? '').toString().trim();
    const w = wanted.toString().trim();
    return a === w || b === w;
  }

  const saeenFasadCount = computed(
    () => allRows.value.filter((item) => matchType(item, 'ساعین بالفساد')).length
  );

  const sharOFasadCount = computed(
    () => allRows.value.filter((item) => matchType(item, 'شر و فساد')).length
  );

  const jasosiCount = computed(
    () =>
      allRows.value.filter((item) => matchType(item, ' جاسوسی') || matchType(item, 'جاسوسی')).length
  );

  const filterOfficeEmployeeId = computed(() => {
    const v = route.query?.office_employee_id;
    return v ? Number(v) : null;
  });

  const filteredRows = computed(() => {
    let rows = allRows.value;

    // URL filter from OfficeEmployee page
    if (filterOfficeEmployeeId.value) {
      rows = rows.filter((r) => Number(r.office_employee_id) === filterOfficeEmployeeId.value);
    }

    // stat filters
    if (activeStat.value === 'today') {
      const today = new Date().toISOString().split('T')[0];
      rows = rows.filter((item) => item.information_saved_date === today);
    } else if (activeStat.value === 'saeen') {
      rows = rows.filter((item) => matchType(item, 'ساعین بالفساد'));
    } else if (activeStat.value === 'shar') {
      rows = rows.filter((item) => matchType(item, 'شر و فساد'));
    } else if (activeStat.value === 'spy') {
      rows = rows.filter((item) => matchType(item, ' جاسوسی') || matchType(item, 'جاسوسی'));
    } else if (activeStat.value === 'resources') {
      rows = allRows.value;
    }

    return rows;
  });

  /* ===== Headers / Groups (unchanged) ===== */
  const headersFT = computed(() => [
    { title: t('آی ډی'), key: 'id' },
    { title: t('کود منبع'), key: 'resource_id' },
    { title: t('کود کارمند مربوطه'), key: 'office_employee_code_number' },
    { title: t('کود کارمند  اطلاع گیرنده'), key: 'office_employee_info_code_number' },

    { title: t('نام'), key: 'name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('حالت مدنی'), key: 'madani_halat' },
    { title: t('سن'), key: 'age' },
    { title: t('وظیفه'), key: 'job' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_districts' },
    { title: t('قریه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },

    { title: t('سیاسی'), key: 'syasi_info_type' },
    { title: t('جنایی'), key: 'jenayee_info_type' },

    { title: t('تاریخ اطلاع'), key: 'information_saved_date' },
    { title: t('ساعت اطلاع'), key: 'information_saved_hour' },
    { title: t('درجه موثقیت اطلاع'), key: 'information_degree' },
    { title: t('د اطلاع کتګوري'), key: 'information_category' },

    { title: t('تاریخ هم‌آهنگی'), key: 'hamahangi_date' },
    { title: t('شماره مکتوب'), key: 'maktop_number' },
    { title: t('ارگان'), key: 'organ' },

    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('نظر مدیر'), key: 'manager_comment' },
    { title: t('رهنمود رهبریت'), key: 'lead_drirection' },
    { title: t('نتیجه'), key: 'result' },
    { title: t('ملاحظات'), key: 'note' },
    { title: t('نوت راجع به اسناد'), key: 'docs_note', width: 140 },
    { title: t('پول پرداخت شده'), key: 'finance_paid_amount' },
    { title: t('د پیسو د پرداخت وضعیت'), key: 'finance_status' },
    { title: t('وجه مصرف'), key: 'expense_result' },
  ]);

  const topGroups = computed(() => [
    { title: t('کودها'), keys: ['resource_id', 'office_employee_id'] },
    { title: t('معرفت'), keys: ['name', 'nick_name', 'father_name', 'grand_father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_districts', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
    {
      title: t('نوع و زمان اطلاع'),
      keys: [
        'syasi_info_type',
        'jenayee_info_type',
        'information_saved_date',
        'information_saved_hour',
        'information_degree',
      ],
    },
    { title: t('هم‌آهنگی'), keys: ['hamahangi_date', 'maktop_number', 'organ'] },
    {
      title: t('متن‌ها'),
      keys: ['tafseel', 'manager_comment', 'lead_drirection', 'note', 'result'],
    },
  ]);

  /* ===== Finance chip helpers ===== */
  function financeStatusLabel(v) {
    const s = String(v ?? '')
      .trim()
      .toLowerCase();
    if (s === 'paid' || s === 'پرداخت' || s === 'paid_finance') return 'پرداخت شوی';
    if (s === 'unpaid' || s === 'pending' || s === 'ناپرداخت' || s === 'not_paid')
      return 'ناپرداخت';
    if (!s) return '—';
    return v;
  }

  function financeStatusColor(v) {
    const s = String(v ?? '')
      .trim()
      .toLowerCase();
    if (s === 'paid' || s === 'پرداخت' || s === 'paid_finance') return 'green';
    if (s === 'unpaid' || s === 'pending' || s === 'ناپرداخت' || s === 'not_paid') return 'amber';
    return 'grey';
  }

  /* ===== Documents download ===== */
  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  async function downloadDocuments(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    const name = row.name || row.code_number || `information_${row.id}`;

    try {
      if (typeof repo.DownloadInformationZip === 'function') {
        await repo.DownloadInformationZip(row.id, name);
      } else {
        showErrorMessage(t('دانلود اسناد برای این اطلاع هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error('downloadDocuments error', e);
      showErrorMessage(t('خطا در دانلود اسناد'));
    }
  }

  /* ===== Delete ===== */
  const deleteDialog = ref(false);
  const deleteTarget = ref(null);

  function openDeleteDialog(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    deleteTarget.value = row;
    deleteDialog.value = true;
  }

  async function handleDeleteConfirmed(target) {
    const row = target || deleteTarget.value;
    if (!row?.id) {
      deleteDialog.value = false;
      deleteTarget.value = null;
      return;
    }

    try {
      if (typeof repo.DeleteInformation === 'function') {
        await repo.DeleteInformation(row.id);
      } else {
        showErrorMessage(t('حذف برای این اطلاع هنوز تنظیم نشده است'));
        return;
      }
      await fetchAll();
    } catch (e) {
      console.error('handleDeleteConfirmed error', e);
      showErrorMessage(t('خطا در حذف اطلاع'));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  /* ===== Peek ===== */
  const filters = ref({});
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      if (typeof repo.FetchInformationData === 'function') {
        await repo.FetchInformationData(id);
        detail.value =
          (repo.Information && Array.isArray(repo.Information)
            ? repo.Information[0]
            : repo.Information) || {};
      } else {
        await fetchAll();
        detail.value = allRows.value.find((r) => r.id === id) || {};
      }
    } finally {
      loading.value = false;
    }
  }

  function openPeek(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }

  /* ===== Navigation ===== */
  function goCreate() {
    router.push('/app/informationform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/informationform/${row.id}`);
  }

  function goPrint(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/informations/print/${row.id}`);
  }

  function goToResource(payload) {
    const row = getRow(payload);
    const id = row?.resource_id;
    if (!id) return;
    router.push({ path: '/app/resources', query: { id: String(id), open: 'peek' } });
  }

  function goToOfficeEmployee(payload) {
    const row = getRow(payload);
    const id = row?.office_employee_id || row?.office_employee_info_id;
    if (!id) return;
    router.push({ path: '/app/officeemployee', query: { id: String(id), open: 'peek' } });
  }

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* ===== Fetch list ===== */
  async function fetchAll() {
    try {
      await repo.FetchInformationsData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search,

        // ✅ NEW: filter payload
        year: filterYear.value,
        month: filterMonth.value,
        date_field: 'information_saved_date',
      });
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت معلومات'));
    }
  }

  /* ===== Init ===== */
  onMounted(async () => {
    await fetchYearItemsFromDb(); // ✅ NEW
    await Promise.allSettled([repo.GetOfficeEmployee?.(), repo.GetResource?.()]);
    await fetchAll();
  });
</script>

<style scoped>
  .stat-card {
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    height: 100px;
    font-family: var(--app-font) !important;
  }

  .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
  }

  .stat-card.active-stat {
    border-color: currentColor;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(240, 240, 240, 0.9) 100%);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
  }

  .stat-card.active-stat .v-avatar {
    background: rgba(255, 255, 255, 0.9) !important;
  }

  .cursor-pointer {
    cursor: pointer;
  }

  :deep(.v-field-label),
  :deep(.v-label),
  :deep(input::placeholder) {
    font-size: 12px !important;
    line-height: 1.2 !important;
    color: #555 !important;
  }

  :deep(.v-field__input) {
    min-height: 26px !important;
    padding: 2px 6px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
  }

  :deep(.v-data-table) {
    border-radius: 8px;
  }

  :deep(.v-data-table__wrapper) {
    border-radius: 8px;
  }

  :deep(.v-data-table__tr:hover) {
    background-color: rgba(33, 150, 243, 0.04) !important;
  }

  :deep(.v-btn--icon) {
    transition: all 0.2s ease;
  }

  :deep(.v-btn--icon:hover) {
    transform: scale(1.1);
  }

  .fontfamily {
    font-family: var(--app-font) !important;
  }
</style>
