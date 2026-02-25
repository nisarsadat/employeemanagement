<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('منابع')" :subtitle="t('فهرست و جستجو')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- 🔍 Search + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Field -->
        <v-col cols="12" sm="12" md="12" lg="12">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو در منابع...')"
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

        <!-- YEAR -->
        <v-col cols="12" sm="6" md="5">
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
          />
        </v-col>

        <!-- MONTH -->
        <v-col cols="12" sm="4" md="3">
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
          />
        </v-col>

        <v-col cols="12" sm="4" md="2">
          <v-btn color="primary" block @click="applyMonthYearFilter">
            {{ t('فلتر') }}
          </v-btn>
        </v-col>

        <!-- Create Button -->
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

    <!-- 📊 STATS CARDS -->
    <v-row class="px-2 mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- Total resources (filters table: all) -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'all' }"
          @click="applyQuickFilter('all')"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ totalCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کل منابع') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-database</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Active resources (ROUTE) -->
      <v-col cols="12" md="3" lg="3">
        <v-card class="stat-card" @click="goActiveResources" rounded="xl">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ activeCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('منابع فعال') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-check-circle</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Inactive resources (ROUTE) -->
      <v-col cols="12" md="3" lg="3">
        <v-card class="stat-card" @click="goInactiveResources" rounded="xl">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-purple fontfamily">
                {{ inactiveCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('منابع غیر فعال') }}
              </div>
            </div>
            <v-avatar color="purple" variant="tonal" size="48">
              <v-icon color="purple">mdi-cancel</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Today added (filters table: today) -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'today' }"
          @click="applyQuickFilter('today')"
          rounded="xl"
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

    <!-- 👁 Peek (side/details) -->
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
        dir="rtl"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- 📋 MAIN TABLE -->
    <FilterableTable
      :headers="headersFT"
      :rows="displayedRowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
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

      <!-- 🟢🔴 Activity dot (فعال/غیر فعال) -->
      <template #cell-activity="{ row }">
        <div class="d-flex align-center justify-center">
          <span
            class="status-dot"
            :class="{
              'status-dot--active': String(getRow(row).activity || '').trim() === 'فعال',
              'status-dot--inactive': String(getRow(row).activity || '').trim() === 'غیر فعال',
            }"
            :title="String(getRow(row).activity || '').trim()"
          />
        </div>
      </template>

      <!-- 👤 Office employee cell with route + tooltip -->
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
                  <span class="text-body-2">
                    {{ getRow(row).events_count }}
                  </span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-red"> 0 </span>
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
                  <span class="text-body-2">
                    {{ getRow(row).informations_count }}
                  </span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-red">0</span>
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
                  <span class="text-body-2">
                    {{ getRow(row).fifty_six_formats_count }}
                  </span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-red">0</span>
      </template>
      <!-- 📑 Fifty-six formats count -->
      <template #cell-code_number="{ row }">
        <div class="d-flex flex-column align-start">
          <v-btn class="px-2 py-1 text-none" color="warning" size="default" variant="tonal">
            <div class="d-flex align-center ga-1">
              <span class="text-body-2">
                {{ getRow(row).code_number }}
              </span>
            </div>
          </v-btn>
        </div>
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

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip text="مشاهده منبع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" @click.stop="openPeek(item)" color="blue">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip text="تغییر جزیات منبع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('resources.update')"
                size="small"
                v-bind="tooltipProps"
                @click.stop="startEdit(item)"
                color="amber-darken-2"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <!-- دانلود اسناد منبع -->
          <v-tooltip text="دانلود اسناد مربوط به منبع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadResourceDocs(item)"
                color="green-darken-2"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <!-- حذف -->
          <v-tooltip text="حذف منبع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('resources.delete')"
                size="small"
                v-bind="tooltipProps"
                @click.stop="openDeleteDialog(item)"
                color="red-darken-2"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
        </div>
      </template>
    </FilterableTable>

    <!-- 🖼 BIG PREVIEW DIALOG -->
    <v-dialog v-model="previewOpen" max-width="520" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card :dir="isRtl ? 'rtl' : 'ltr'">
        <v-toolbar
          density="compact"
          color="primary"
          :title="t('پیش نمایش عکس منبع')"
          class="text-white"
          :dir="isRtl ? 'rtl' : 'ltr'"
        />
        <v-card-text class="d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="480" max-height="420" cover />
        </v-card-text>
        <v-card-actions class="justify-end" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-btn color="primary" variant="flat" @click="previewOpen = false">
            {{ t('بستن') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- 🔁 Reusable Delete Confirm Dialog -->
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
  import { ref, computed, watch, onMounted } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import dayjs from 'dayjs';
  import { useCalendar } from '../../../composables/useCalendar';

  import AppBar from '@/components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';

  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';

  const route = useRoute();
  const router = useRouter();
  const { t, locale } = useI18n();
  const { calendar, formatYearDisplay } = useCalendar();

  const repo = useDatabaseRepository();
  const auth = useLoginRepository();

  /* ===== RTL ===== */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  /* ===== Helpers: unwrap + URL ===== */
  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  function normalizeUrl(url) {
    if (!url) return null;
    return url.replace('/storage/storage/', '/storage/');
  }

  function getPhotoUrl(payload) {
    const row = getRow(payload);
    if (!row) return null;
    return normalizeUrl(row.picture || row.picture_url);
  }

  /* ===== Month/Year Filter ===== */
  const filterYear = ref(null); // sent to backend (gregorian)
  const filterMonth = ref(null); // sent to backend (gregorian 1..12)

  // ✅ year items from DB, but titles follow calendar mode
  const yearItems = ref([]);
  const yearNumbers = ref([]);

  async function fetchYearItemsFromDb() {
    try {
      const res = await repo.FetchResourceDateOptions({ date_field: 'resource_saved_date' });
      const years = res?.data?.years || [];

      yearNumbers.value = years.map((y) => Number(y)).filter((n) => Number.isFinite(n));

      yearItems.value = yearNumbers.value.map((y) => ({
        value: y,
        title: typeof formatYearDisplay === 'function' ? formatYearDisplay(y) : String(y),
      }));
    } catch (e) {
      console.error('fetchYearItemsFromDb error', e);
      showErrorMessage(t('خطا در دریافت سال‌ها'));
    }
  }

  // ✅ update year titles when calendar/locale changes (selected value stays same)
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

  // ✅ month titles follow calendar mode, but values stay gregorian 1..12
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

  async function fetchAll() {
    try {
      await repo.FetchResourcesData({
        search: repo.Search,
        year: filterYear.value,
        month: filterMonth.value,
        date_field: 'resource_saved_date',
      });
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت فهرست منابع'));
    }
  }

  /* ===== Office employee ===== */
  function getOfficeEmployee(payload) {
    const row = getRow(payload);
    if (!row) return null;
    return row.office_employee || null;
  }

  // 🔗 Go to events of this resource
  function goResourceEvents(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push({ path: '/app/events', query: { resource_id: row.id } });
  }

  // 🔗 Go to informations of this resource
  function goResourceInformations(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push({ path: '/app/informations', query: { resource_id: row.id } });
  }

  // 🔗 Go to 56 formats of this resource
  function goResourceFiftySixFormats(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push({ path: '/app/fifthyformatsroute', query: { resource_id: row.id } });
  }

  /* ===== Data for table ===== */
  const rowsFT = computed(() => repo.Resources || []);

  /* ===== Date helpers ===== */
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

  /* ===== Stats ===== */
  const totalCount = computed(() => rowsFT.value.length);

  const todayAddedCount = computed(() => {
    const today = new Date();
    return rowsFT.value.filter((r) => isSameDate(r.resource_saved_date || r.created_at, today))
      .length;
  });

  function isActiveResource(r) {
    const v = (r?.activity ?? r?.status ?? r?.is_active ?? '').toString().trim().toLowerCase();
    return v === 'فعال' || v === 'active' || v === '1' || v === 'true';
  }

  const activeCount = computed(() => rowsFT.value.filter((r) => isActiveResource(r)).length);
  const inactiveCount = computed(() => rowsFT.value.filter((r) => !isActiveResource(r)).length);

  function goActiveResources() {
    router.push('/app/activeresource');
  }

  function goInactiveResources() {
    router.push('/app/inactiveresource');
  }

  /* ===== Quick filter for stats cards ===== */
  const quickFilter = ref('all'); // 'all' | 'today'

  function applyQuickFilter(mode) {
    quickFilter.value = mode;
  }

  const routeResourceId = computed(() => {
    const v = route.query?.resource_id;
    return v ? Number(v) : null;
  });

  const displayedRowsFT = computed(() => {
    let data = rowsFT.value || [];
    const today = new Date();

    // ✅ if we came here from Events by resource_id
    if (routeResourceId.value) {
      data = data.filter((r) => Number(r.id) === routeResourceId.value);
    }

    if (quickFilter.value === 'today') {
      data = data.filter((r) => isSameDate(r.resource_saved_date || r.created_at, today));
    }

    return data;
  });

  /* ===== Headers for FilterableTable ===== */
  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },
    { title: t('پول داده شده'), key: 'payed_money' },
    { title: t('تعداد واقعات'), key: 'events_count' },
    { title: t('تعداد اطلاعات'), key: 'informations_count' },
    { title: t('تعداد ۵۶ فارمت'), key: 'fifty_six_formats_count' },
    { title: t('۵۶ فارمټ'), key: 'letry_format_name' },
    { title: t('کارمند مربوطه'), key: 'office_employee_id' },
    { title: t('رزیدنت مربوطه'), key: 'resedint_code' },
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

    { title: t('زون تدارک'), key: 'zone' },
    { title: t('حوزه تدارک'), key: 'hawza' },
    { title: t('محل مشخص تدارک'), key: 'specefic_place' },
    { title: t('کوردینات تدارک'), key: 'coordinates' },

    { title: t('فعالیت'), key: 'activity' },
    { title: t('حالت مدنی'), key: 'halat_madani' },
    { title: t('عمر'), key: 'age' },
    { title: t('رتبه علمی'), key: 'elmi_level' },
    { title: t('فاکولته/دیپارتمنت'), key: 'faculty' },
    { title: t('مهارت‌های خاص'), key: 'specific_skills' },
    { title: t('تاریخ ثبت منبع'), key: 'resource_saved_date' },
    { title: t('نوع فعالیت منبع'), key: 'resource_activity_type' },

    { title: t('پوشش منبع'), key: 'resource_cover' },
    { title: t('زبان'), key: 'language' },
    { title: t('قوم'), key: 'nation' },
    { title: t('گروپ خون'), key: 'blood_group' },
    { title: t('دوره/آموزش'), key: 'train' },
    { title: t('مربوطه پروژه'), key: 'project' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('میزان اعتماد'), key: 'trust_percentage' },
    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('نوت'), key: 'note' },
  ]);

  /* ===== Top filter groups ===== */
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

  const filters = ref({});

  /* ===== Error Snackbar ===== */
  const showError = ref(false);
  const errorMessage = ref('');

  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  /* ===== Preview dialog ===== */
  const previewOpen = ref(false);
  const previewSrc = ref(null);

  function openPreview(rowPayload) {
    const url = getPhotoUrl(rowPayload);
    if (!url) return;
    previewSrc.value = url;
    previewOpen.value = true;
  }

  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  /* ===== Peek / single fetch ===== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  const detailForPeek = computed(() => {
    if (!detail.value) return null;
    return {
      ...detail.value,
      picture: getPhotoUrl(detail.value),
    };
  });

  async function fetchOne(id) {
    if (!id) return;
    loadingOne.value = true;
    try {
      if (typeof repo.FetchResourceData === 'function') {
        await repo.FetchResourceData(id);
        const raw =
          repo.Resource && Array.isArray(repo.Resource) ? repo.Resource[0] : repo.Resource;
        detail.value = raw || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error('FetchOne resource error:', e);
      showErrorMessage(t('خطا در دریافت معلومات منبع'));
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

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* ===== Navigation ===== */
  function goCreate() {
    router.push('/app/resourcesform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    const id = row?.id;
    if (!id) return;
    router.push(`/app/resourcesform/${id}`);
  }

  // 🔗 Go to office employee page from resource
  function goOfficeEmployee(payload) {
    const row = getRow(payload);
    if (!row) return;

    const empId = row.office_employee_id || row.office_employee?.id;
    if (!empId) return;

    router.push({
      path: '/app/officeemployee',
      query: { office_employee_id: empId },
    });
  }

  /* ===== Download documents ZIP ===== */
  async function downloadResourceDocs(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    try {
      if (typeof repo.DownloadResourceZip === 'function') {
        await repo.DownloadResourceZip(row.id);
      } else {
        console.warn('DownloadResourceZip not implemented on repo');
        showErrorMessage(t('دانلود اسناد برای این منبع هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error('downloadResourceDocs error', e);
      showErrorMessage(t('خطا در دانلود اسناد منبع'));
    }
  }

  /* ===== Delete using DeleteConfirmDialog ===== */
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
      if (typeof repo.DeleteResource === 'function') {
        await repo.DeleteResource(row.id);
      } else {
        console.warn('DeleteResource not implemented on repo');
        showErrorMessage(t('حذف برای این منبع هنوز تنظیم نشده است'));
        return;
      }

      await fetchAll();
    } catch (e) {
      console.error('handleDeleteConfirmed error', e);
      showErrorMessage(t('خطا در حذف منبع'));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  /* ===== Init ===== */
  onMounted(async () => {
    await fetchYearItemsFromDb();
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

  .stat-card-active {
    border-color: #1976d2 !important;
    box-shadow: 0 0 0 1px rgba(25, 118, 210, 0.3);
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

  .cursor-pointer {
    cursor: pointer;
  }

  .fontfamily {
    font-family: var(--app-font) !important;
  }

  .text-grey {
    color: #9e9e9e;
  }

  .status-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    display: inline-block;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.06);
  }

  .status-dot--active {
    background: #4caf50;
  }

  .status-dot--inactive {
    background: #f44336;
  }
</style>
