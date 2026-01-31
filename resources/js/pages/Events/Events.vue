<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('واقعات')" :subtitle="t('فهرست و جستجو و مدیریت واقعات')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- 🔍 Search + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Field -->
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو در واقعات...')"
            density="comfortable"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            color="primary"
            rounded="lg"
            hide-details
            @keyup.enter="fetchAll"
            clearable
            @click:clear="clearSearch"
            class="bg-white search-field"
          />
        </v-col>

        <!-- Create Button -->
        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            v-if="auth.can('events.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('واقعه جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- 📊 STATS CARDS (clickable filters) -->
    <v-row class="px-2 mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- All -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card--active': activeCard === 'all' }"
          @click="setCardFilter('all')"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-purple fontfamily">
                {{ totalCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کل واقعات') }}
              </div>
            </div>
            <v-avatar color="purple" variant="tonal" size="48">
              <v-icon color="purple">mdi-alert-circle-outline</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
      <!-- سیاسی -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card--active': activeCard === 'syasi' }"
          @click="setCardFilter('syasi')"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ syasiCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('سیاسی') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-scale-balance</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- جینایی -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card--active': activeCard === 'jenayee' }"
          @click="setCardFilter('jenayee')"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ jenayeeCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('جینایی') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-gavel</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Today -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card--active': activeCard === 'today' }"
          @click="setCardFilter('today')"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-amber fontfamily">
                {{ todayAddedCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('امروز اضافه شده') }}
              </div>
            </div>
            <v-avatar color="amber" variant="tonal" size="48">
              <v-icon color="amber">mdi-calendar-today</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- 👁 Peek -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loadingOne"
        title-key="event_type"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- 📋 MAIN TABLE -->
    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      :dateKeys="['event_date']"
      @edit="goEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    >
      <!-- 🔗 Resource cell: route to Resource page -->
      <template #cell-resource_display="{ row }">
        <v-tooltip :text="t('برای رفتن به صفحه منبع کلیک کنید')" location="top">
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="primary"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goResource(row)"
            >
              <v-icon size="16" class="mr-1">mdi-account-search</v-icon>
              {{ getRow(row).resource_display || t('منبع') }}
            </v-btn>
          </template>
        </v-tooltip>
      </template>

      <!-- 🔗 Office employee cell: route to OfficeEmployee page -->
      <template #cell-office_employee_display="{ row }">
        <v-tooltip :text="t('برای رفتن به صفحه کارمند کلیک کنید')" location="top">
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="deep-purple"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goOfficeEmployee(row)"
            >
              <v-icon size="16" class="mr-1">mdi-account-badge</v-icon>
              {{ getRow(row).office_employee_display || t('کارمند') }}
            </v-btn>
          </template>
        </v-tooltip>
      </template>
      <!-- 🔗 Office employee cell: route to OfficeEmployee page -->
      <template #cell-office_employee_akhistonky_code="{ row }">
        <v-tooltip :text="t('برای رفتن به صفحه کارمند کلیک کنید')" location="top">
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="deep-purple"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goOfficeEmployee(row)"
            >
              <v-icon size="16" class="mr-1">mdi-account-badge</v-icon>
              {{ getRow(row).office_employee_akhistonky_code || t('کارمند') }}
            </v-btn>
          </template>
        </v-tooltip>
      </template>

      <!-- 🗺 Map cell: FlyToMapButton -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="getRow(row).coordinates"
          :title="getRow(row).event_type"
          :subtitle="getRow(row).event_specific_type"
          type="event"
          :zoom="16"
        />
      </template>
      <!-- ✅ Finance status: Pashto chip -->
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
      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده واقعه')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش واقعه')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="amber-darken-2"
                @click.stop="goEdit(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <!-- دانلود اسناد -->
          <v-tooltip :text="t('دانلود اسناد واقعه')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="green-darken-2"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadEventDocs(item)"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <!-- حذف با دیالوگ تایید -->
          <v-tooltip :text="t('حذف واقعه')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('events.delete')"
                size="small"
                v-bind="tooltipProps"
                color="red-darken-2"
                @click.stop="openDeleteDialog(item)"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
        </div>
      </template>
    </FilterableTable>

    <!-- 🗑 Delete Confirm Dialog -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />

    <!-- ❗ Error Snackbar -->
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
  import { ref, onMounted, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRouter } from 'vue-router';

  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';

  import { useDatabaseRepository } from '@/stores/DatabaseRepository';
  import { useLoginRepository } from '@/stores/LoginRepository';

  const repo = useDatabaseRepository();
  const auth = useLoginRepository();
  const router = useRouter();

  /* i18n / RTL */
  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => {
    isRtl.value = newLocale === 'fa';
  });

  /* snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (m) => {
    errorMessage.value = m;
    showError.value = true;
  };

  /* helpers */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* === Card filter state === */
  const activeCard = ref('all'); // 'syasi' | 'jenayee' | 'today' | 'all'
  const setCardFilter = (type) => {
    activeCard.value = type;
  };

  /* === Base events from API === */
  const baseEvents = computed(() => repo.Events || []);

  /* date helper for display YYYY-MM-DD */
  function formatDateYMD(value) {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) {
      // fallback: try first 10 chars if it is already string
      const s = String(value);
      return s.length >= 10 ? s.slice(0, 10) : s;
    }
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }
  function normalizeText(v) {
    return String(v || '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, ' ');
  }

  function isSyasiType(eventType) {
    const s = normalizeText(eventType);
    return s === 'سیاسی' || s === 'syasi' || s.includes('سیاسی') || s.includes('syasi');
  }

  function isJenayeeType(eventType) {
    const s = normalizeText(eventType);
    // supports both جینایی and جنایی spellings + latin
    return (
      s === 'جینایی' ||
      s === 'جنایی' ||
      s === 'jinayee' ||
      s === 'jenayee' ||
      s.includes('جینایی') ||
      s.includes('جنایی') ||
      s.includes('jinayee') ||
      s.includes('jenayee')
    );
  }
  const syasiCount = computed(
    () => baseEvents.value.filter((r) => isSyasiType(r.event_type)).length
  );

  const jenayeeCount = computed(
    () => baseEvents.value.filter((r) => isJenayeeType(r.event_type)).length
  );

  function isSameDate(dateValue, today) {
    if (!dateValue) return false;
    const d = new Date(dateValue);
    if (Number.isNaN(d.getTime())) return false;
    return (
      d.getFullYear() === today.getFullYear() &&
      d.getMonth() === today.getMonth() &&
      d.getDate() === today.getDate()
    );
  }

  /* === Stats (always based on ALL events) === */
  const totalCount = computed(() => baseEvents.value.length);

  const withDocumentsCount = computed(() => baseEvents.value.filter((r) => hasDocsRow(r)).length);

  const withCoordinatesCount = computed(
    () => baseEvents.value.filter((r) => hasCoordsRow(r)).length
  );

  const todayAddedCount = computed(() => {
    const today = new Date();
    return baseEvents.value.filter((r) => isSameDate(r.created_at, today)).length;
  });

  /* === Filtered list for table based on activeCard === */
  const filteredEvents = computed(() => {
    const all = baseEvents.value;

    if (activeCard.value === 'syasi') {
      return all.filter((r) => isSyasiType(r.event_type));
    }
    if (activeCard.value === 'jenayee') {
      return all.filter((r) => isJenayeeType(r.event_type));
    }
    if (activeCard.value === 'today') {
      const today = new Date();
      return all.filter((r) => isSameDate(r.created_at, today));
    }
    return all; // 'all'
  });

  /* rows for FilterableTable (uses filteredEvents) */
  const rowsFT = computed(() =>
    (filteredEvents.value || []).map((e) => ({
      ...e,
      // formatted date for display
      event_date: formatDateYMD(e.event_date),
      resource_display: e.resource.code_number || e.resource_id || '',
      office_employee_display: e.office_employee_code || e.office_employee_id || '',
    }))
  );

  /* headers */
  const headersFT = computed(() => [
    { title: t('آی ډی'), key: 'id' },
    { title: t('منبع'), key: 'resource_display' },
    { title: t('کارمند دفتر'), key: 'office_employee_display' },
    { title: t('کود کارمند واقعه گیرنده'), key: 'office_employee_akhistonky_code' },

    { title: t('نوعیت واقعه'), key: 'event_type' },
    { title: t('نوعیت مشخص واقعه'), key: 'event_specific_type' },

    { title: t('هدف کی بود؟/مکان بود؟'), key: 'target_person' },
    { title: t('کی جرم را انجام داده؟'), key: 'criminal' },

    { title: t('تاریخ واقعه'), key: 'event_date' },
    { title: t('ساعت واقعه'), key: 'event_hour' },

    { title: t('زون'), key: 'event_zone' },
    { title: t('حوزه'), key: 'event_hawza' },
    { title: t('محل مشخص'), key: 'event_specific_place' },
    { title: t('کوردینات'), key: 'coordinates' },

    // { title: t('تلفات جانی'), key: 'talafat_jani' },
    { title: t('تلفات مالی'), key: 'talafat_mali' },

    // ✅ FIXED: these two were swapped in your snippet
    { title: t('زخمی شده'), key: 'injured' },
    { title: t('کشته شده'), key: 'killed' },

    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('همغږی'), key: 'organ' },
    // { title: t('وضاحت'), key: 'talks' },
    { title: t('ملاحظات'), key: 'note' },
    { title: t('نوت راجع به اسناد'), key: 'docs_note' },
    { title: t('پول پرداخت شده'), key: 'finance_paid_amount' },
    { title: t('د پیسو د پرداخت وضعیت'), key: 'finance_status' },

    // { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

  const topGroups = computed(() => [
    {
      title: t('شناسه‌ها'),
      // ✅ FIXED: group keys should match your header keys
      keys: ['resource_display', 'office_employee_display', 'office_employee_akhistonky_code'],
    },
    { title: t('نوع واقعه'), keys: ['event_type', 'event_specific_type'] },
    { title: t('هدف و عامل'), keys: ['target_person', 'criminal'] },
    { title: t('زمان'), keys: ['event_date', 'event_hour'] },
    {
      title: t('مکان'),
      keys: ['event_zone', 'event_hawza', 'event_specific_place', 'coordinates'],
    },
    // ✅ aligned with your headers (talafat_jani is commented out)
    { title: t('تلفات'), keys: ['talafat_mali', 'injured', 'killed'] },

    { title: t('متن‌ها'), keys: ['tafseel', 'note', 'docs_note'] },
  ]);
  // ✅ Finance status label (Pashto)
  function financeStatusLabel(v) {
    const s = String(v ?? '')
      .trim()
      .toLowerCase();

    // handle common values from backend
    if (s === 'paid' || s === 'پرداخت' || s === 'paid_finance') return 'پرداخت شوی';
    if (s === 'unpaid' || s === 'pending' || s === 'ناپرداخت' || s === 'not_paid')
      return 'ناپرداخت';

    // empty / null
    if (!s) return '—';

    // fallback: show original
    return v;
  }

  // ✅ Chip color by status (Vuetify uses color names)
  function financeStatusColor(v) {
    const s = String(v ?? '')
      .trim()
      .toLowerCase();
    if (s === 'paid' || s === 'پرداخت' || s === 'paid_finance') return 'green';
    if (s === 'unpaid' || s === 'pending' || s === 'ناپرداخت' || s === 'not_paid') return 'amber';
    return 'grey';
  }

  const filters = ref({});

  /* Peek / single fetch */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loadingOne.value = true;
    try {
      if (typeof repo.FetchEventData === 'function') {
        await repo.FetchEventData(id);
        detail.value = (repo.Event && Array.isArray(repo.Event) ? repo.Event[0] : repo.Event) || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error('FetchOne event error:', e);
      showErrorMessage(t('خطا در دریافت معلومات واقعه'));
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

  /* navigation */
  function goCreate() {
    router.push('/app/eventsform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/eventsform/${row.id}`);
  }

  // 🔗 Go to office employee from event row
  function goOfficeEmployee(payload) {
    const row = getRow(payload);
    const empId = row?.office_employee_id;
    if (!empId) return;

    router.push({
      path: '/app/officeemployee',
      query: { office_employee_id: empId },
    });
  }

  // 🔗 Go to resource from event row
  function goResource(payload) {
    const row = getRow(payload);
    const resId = row?.resource_id;
    if (!resId) return;

    router.push({
      path: '/app/resources',
      query: { resource_id: resId },
    });
  }

  /* documents */
  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  async function downloadEventDocs(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    try {
      if (typeof repo.DownloadEventZip === 'function') {
        await repo.DownloadEventZip(row.id);
      } else {
        showErrorMessage(t('دانلود اسناد برای این واقعه هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error('downloadEventDocs error', e);
      showErrorMessage(t('خطا در دانلود اسناد واقعه'));
    }
  }

  /* ===== Delete with dialog ===== */
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
      if (typeof repo.DeleteEvent === 'function') {
        await repo.DeleteEvent(row.id);
      } else {
        showErrorMessage(t('حذف برای این واقعه هنوز تنظیم نشده است'));
        return;
      }
      await fetchAll();
    } catch (error) {
      console.error('Delete event error', error);
      showErrorMessage(t('خطا در حذف واقعه') + ': ' + (error.message || t('خطای ناشناخته')));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  /* Fetch all */
  async function fetchAll() {
    try {
      await repo.FetchEventsData({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search,
      });
    } catch (e) {
      console.error('fetchAll events error', e);
      showErrorMessage(t('خطا در دریافت فهرست واقعات'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* init */
  onMounted(async () => {
    await fetchAll();
  });
</script>

<style scoped>
  .ga-2 {
    gap: 8px;
  }

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

  /* active card highlight */
  .stat-card--active {
    border-color: #1976d2 !important;
    box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.25) !important;
  }

  .text-green {
    color: #4caf50 !important;
  }

  .text-amber {
    color: #ff9800 !important;
  }

  .text-purple {
    color: #9c27b0 !important;
  }

  /* 🔹 make search field height similar to large button */
  .search-field :deep(.v-field) {
    min-height: 48px;
  }

  .fontfamily {
    font-family: var(--app-font) !important;
  }
</style>
