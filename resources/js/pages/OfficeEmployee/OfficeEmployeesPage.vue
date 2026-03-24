<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('کارمندان مدیریت')" :subtitle="t('فهرست و جستجو')">
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
            :label="t('جستجو در کارمندان...')"
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

        <!-- ✅ NEW: Year -->
        <v-col cols="12" sm="7" md="7" lg="6">
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
        <v-col cols="12" sm="4" md="2" lg="1">
          <v-btn
            color="orange"
            rounded="lg"
            block
            class="text-none"
            prepend-icon="mdi-filter-remove"
            @click="clearAllFilters"
          >
            {{ t('فلترها') }}
          </v-btn>
        </v-col>
        <!-- Create Button -->
        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            v-if="auth.can('officeemployee.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('کارمند جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- 📊 STATS CARDS -->
    <v-row class="px-2 mt-2 d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- 1) Total employees -->
      <v-col cols="12" md="2" lg="2">
        <v-card
          class="stat-card"
          rounded="xl"
          @click="applyCardFilter({ status: 'all', job_type: null })"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ totalCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کل کارمندان') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-account-group</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 2) اداری -->
      <v-col cols="12" md="2" lg="2">
        <v-card
          class="stat-card"
          rounded="xl"
          @click="applyCardFilter({ status: 'all', job_type: 'اداری' })"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-indigo fontfamily">
                {{ edariCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('اداری') }}
              </div>
            </div>
            <v-avatar color="indigo" variant="tonal" size="48">
              <v-icon color="indigo">mdi-briefcase-account</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 3) اوپراتیفی -->
      <v-col cols="12" md="2" lg="2">
        <v-card
          class="stat-card"
          rounded="xl"
          @click="applyCardFilter({ status: 'all', job_type: 'اوپراتیفی' })"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-cyan fontfamily">
                {{ operatifiCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('اوپراتیفی') }}
              </div>
            </div>
            <v-avatar color="cyan" variant="tonal" size="48">
              <v-icon color="cyan">mdi-shield-account</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 4) Active employees -->
      <v-col cols="12" md="2" lg="2">
        <v-card
          class="stat-card"
          rounded="xl"
          @click="applyCardFilter({ status: 'active', job_type: null })"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ activeEmployeesCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کارمندان فعال') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-account-check</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 5) Inactive employees -->
      <v-col cols="12" md="2" lg="2">
        <v-card
          class="stat-card"
          rounded="xl"
          @click="applyCardFilter({ status: 'inactive', job_type: null })"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-red fontfamily">
                {{ inactiveEmployeesCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کارمندان غیر فعال') }}
              </div>
            </div>
            <v-avatar color="red" variant="tonal" size="48">
              <v-icon color="red">mdi-account-off</v-icon>
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
      :rows="focusedRowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      :dateKeys="['join_date', 'left_date']"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-4 mx-2"
    >
      <template #cell-picture="{ row }">
        <v-avatar size="36" class="cursor-pointer" @click="openPreview(row)">
          <v-img v-if="getPhotoUrl(row)" :src="getPhotoUrl(row)" cover />
          <v-icon v-else size="20">mdi-account</v-icon>
        </v-avatar>
      </template>

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

      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="getRow(row).coordinates"
          :title="getRow(row).name"
          :subtitle="getRow(row).code_number"
          type="office_employee"
          :photo-url="getPhotoUrl(row)"
          :zoom="16"
        />
      </template>

      <template #cell-informations_count="{ row }">
        <v-tooltip
          v-if="getRow(row).informations_count && getRow(row).informations_count > 0"
          text="تعداد معلومات این کارمند، کلیک کنید تا به صفحه معلومات بروید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="primary"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goEmployeeInformations(row)"
            >
              <v-icon size="18" class="mr-1">mdi-file-document-multiple</v-icon>
              {{ getRow(row).informations_count }}
            </v-btn>
          </template>
        </v-tooltip>
        <span v-else class="text-red">0</span>
      </template>

      <template #cell-events_count="{ row }">
        <v-tooltip
          v-if="getRow(row).events_count && getRow(row).events_count > 0"
          text="تعداد واقعات این کارمند، کلیک کنید تا به صفحه واقعات بروید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="deep-purple"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goEmployeeEvents(row)"
            >
              <v-icon size="18" class="mr-1">mdi-calendar-alert</v-icon>
              {{ getRow(row).events_count }}
            </v-btn>
          </template>
        </v-tooltip>
        <span v-else class="text-red">0</span>
      </template>

      <template #cell-resources_active_count="{ row }">
        <v-tooltip
          v-if="getRow(row).resources_active_count && getRow(row).resources_active_count > 0"
          text="منابع فعال این کارمند — کلیک کنید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <div
              v-bind="tooltipProps"
              class="count-pill count-pill--active"
              role="button"
              tabindex="0"
              @click.stop="goActiveResources(row)"
              @keydown.enter.stop="goActiveResources(row)"
            >
              <v-icon size="18" class="mr-1">mdi-check-decagram</v-icon>
              <span class="count-pill__num">{{ getRow(row).resources_active_count }}</span>
            </div>
          </template>
        </v-tooltip>

        <span v-else class="text-red">0</span>
      </template>

      <template #cell-resources_inactive_count="{ row }">
        <v-tooltip
          v-if="getRow(row).resources_inactive_count && getRow(row).resources_inactive_count > 0"
          text="منابع غیر فعال این کارمند — کلیک کنید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <div
              v-bind="tooltipProps"
              class="count-pill count-pill--inactive"
              role="button"
              tabindex="0"
              @click.stop="goInactiveResources(row)"
              @keydown.enter.stop="goInactiveResources(row)"
            >
              <v-icon size="18" class="mr-1">mdi-close-octagon</v-icon>
              <span class="count-pill__num">{{ getRow(row).resources_inactive_count }}</span>
            </div>
          </template>
        </v-tooltip>

        <span v-else class="text-red">0</span>
      </template>

      <template #cell-resources_count="{ row }">
        <v-tooltip
          v-if="getRow(row).resources_count && getRow(row).resources_count > 0"
          text="تعداد منابع این کارمند، کلیک کنید تا به صفحه منابع بروید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="teal"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goEmployeeResources(row)"
            >
              <v-icon size="18" class="mr-1">mdi-database</v-icon>
              {{ getRow(row).resources_count }}
            </v-btn>
          </template>
        </v-tooltip>
        <span v-else class="text-red">0</span>
      </template>

      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip text="مشاهده کارمند" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" @click.stop="openPeek(item)" color="blue">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="تغییر جزیات کارمند" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('officeemployee.update')"
                size="small"
                v-bind="tooltipProps"
                @click.stop="startEdit(item)"
                color="amber-darken-2"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="دانلود اسناد مربوط به کارمند" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-if="auth.can('attachmentDownload.create')"
                v-bind="tooltipProps"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadEmployeeDocs(item)"
                color="green-darken-2"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="حذف کارمند" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('officeemployee.delete')"
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

    <GlobalPagination
      :page="repo.page"
      :items-per-page="repo.itemsPerPage"
      :total="repo.totalItems"
      @update:page="handlePageChange"
      @update:itemsPerPage="handlePerPageChange"
    />

    <v-dialog v-model="previewOpen" max-width="520" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card :dir="isRtl ? 'rtl' : 'ltr'">
        <v-toolbar
          density="compact"
          color="primary"
          :title="t('پیش نمایش عکس کارمند')"
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

  import { useEmployeOfficeRepository } from '../../stores/EmployeeRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';

  import GlobalPagination from '@/components/GlobalPagination.vue';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  const router = useRouter();
  const route = useRoute();
  const { t, locale } = useI18n();
  const { calendar, formatYearDisplay } = useCalendar();

  const repo = useEmployeOfficeRepository();
  const auth = useLoginRepository();

  const filterYear = ref(null);
  const filterMonth = ref(null);

  const yearItems = ref([]);
  const yearNumbers = ref([]);

  const selectedCardStatus = ref('all');
  const selectedCardJobType = ref(null);

  async function fetchYearItemsFromDb() {
    try {
      const res = await repo.FetchEmployeeDateOptions({ date_field: 'join_date' });
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
    repo.page = 1;
    await fetchAll();
  }

  function applyCardFilter({ status = 'all', job_type = null } = {}) {
    selectedCardStatus.value = status;
    selectedCardJobType.value = job_type;
    repo.page = 1;
    fetchAll();
  }

  const focusId = computed(() => {
    const id = route.query?.id;
    return id ? Number(id) : null;
  });

  const rowsFT = computed(() => repo.Employees || []);

  const focusedRowsFT = computed(() => {
    if (!focusId.value) return rowsFT.value;
    return rowsFT.value.filter((r) => Number(r.id) === focusId.value);
  });

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

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

  const totalCount = computed(() => Number(repo.employeeCardCounts?.total ?? 0));
  const edariCount = computed(() => Number(repo.employeeCardCounts?.edari ?? 0));
  const operatifiCount = computed(() => Number(repo.employeeCardCounts?.operatifi ?? 0));
  const activeEmployeesCount = computed(() => Number(repo.employeeCardCounts?.active ?? 0));
  const inactiveEmployeesCount = computed(() => Number(repo.employeeCardCounts?.inactive ?? 0));

  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },
    { title: t('منابع'), key: 'resources_count', sortable: false },
    { title: t('منابع فعال'), key: 'resources_active_count', sortable: false },
    { title: t('منابع غیر فعال'), key: 'resources_inactive_count', sortable: false },
    { title: t('تاریخ رفتن'), key: 'left_date' },
    { title: t('پول داده شده'), key: 'payed_money' },
    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('اطلاعات'), key: 'informations_count', sortable: false },
    { title: t('حوادث'), key: 'events_count', sortable: false },
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
    { title: t('تخلفات'), key: 'makhafat' },
    { title: t('مکافات'), key: 'makafat' },
    { title: t('تاریخ آمدن'), key: 'join_date' },
    { title: t('ملاحظات'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    {
      title: t('هویت'),
      keys: [
        'name',
        'father_name',
        'grand_father_name',
        'nick_name',
        'code_number',
        'tazkira',
        'age',
        'language',
        'nation',
      ],
    },
    {
      title: t('وظیفه و رتبه'),
      keys: ['job_type', 'specific_job', 'qisem', 'bast', 'level', 'zone', 'hawza'],
    },
    {
      title: t('سابقه جهاد'),
      keys: [
        'history_jahad',
        'dilgay_mesher',
        'jahadi_mawaqaf',
        'jahad_time_responsibility',
        'jahad_place',
      ],
    },
    {
      title: t('آدرس اصلی'),
      keys: ['main_province', 'main_district', 'main_village'],
    },
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
    {
      title: t('تحصیلات'),
      keys: ['relegy_study', 'new_study', 'faculty', 'nizami', 'gun_knowledge'],
    },
    {
      title: t('تماس و سایر'),
      keys: ['phone_number', 'specific_skills', 'activity', 'coordinates', 'note'],
    },
  ]);

  const filters = ref({});

  const showError = ref(false);
  const errorMessage = ref('');

  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

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
      if (typeof repo.FetchEmployeeData === 'function') {
        await repo.FetchEmployeeData(id);
        const raw =
          repo.Employee && Array.isArray(repo.Employee) ? repo.Employee[0] : repo.Employee;
        detail.value = raw || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error('FetchOne employee error:', e);
      showErrorMessage(t('خطا در دریافت معلومات کارمند'));
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

  async function fetchAll() {
    try {
      await repo.FetchEmployeesData({
        year: filterYear.value,
        month: filterMonth.value,
        date_field: 'join_date',
        status: selectedCardStatus.value,
        job_type: selectedCardJobType.value,
      });
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت فهرست کارمندان'));
    }
  }

  function handlePageChange(value) {
    repo.page = value;
    fetchAll();
  }

  function handlePerPageChange(value) {
    repo.itemsPerPage = value;
    repo.page = 1;
    fetchAll();
  }

  function clearSearch() {
    repo.Search = '';
    repo.page = 1;
    fetchAll();
  }

  function goCreate() {
    router.push('/app/officeemployeeform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    const id = row?.id;
    if (!id) return;
    router.push(`/app/officeemployeeform/${id}`);
  }

  function goEmployeeInformations(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/informations',
      query: { office_employee_id: row.id },
    });
  }

  function goEmployeeEvents(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/events',
      query: { office_employee_id: row.id },
    });
  }

  function goEmployeeResources(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/resources',
      query: { office_employee_id: row.id },
    });
  }

  function goActiveResources(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/activeresource',
      query: { office_employee_id: row.id },
    });
  }

  function goInactiveResources(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/inactiveresource',
      query: { office_employee_id: row.id },
    });
  }
  async function clearAllFilters() {
    repo.Search = '';
    filterYear.value = null;
    filterMonth.value = null;
    selectedCardStatus.value = 'all';
    selectedCardJobType.value = null;
    repo.page = 1;

    await fetchAll();
  }
  async function downloadEmployeeDocs(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    try {
      if (typeof repo.DownloadOfficeEmployeeZip === 'function') {
        await repo.DownloadOfficeEmployeeZip(row.id);
      } else {
        console.warn('DownloadOfficeEmployeeZip not implemented on repo');
        showErrorMessage(t('دانلود اسناد برای این کارمند هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error('downloadEmployeeDocs error', e);
      showErrorMessage(t('خطا در دانلود اسناد کارمند'));
    }
  }

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
      if (typeof repo.DeleteEmployee === 'function') {
        await repo.DeleteEmployee(row.id);
      } else {
        console.warn('DeleteEmployee not implemented on repo');
        showErrorMessage(t('حذف برای این کارمند هنوز تنظیم نشده است'));
        return;
      }

      await fetchAll();
    } catch (e) {
      console.error('handleDeleteConfirmed error', e);
      showErrorMessage(t('خطا در حذف کارمند'));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  usePageRefresh('/app/officeemployee', fetchAll);

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

  .text-green {
    color: #4caf50 !important;
  }

  .text-amber {
    color: #ff9800 !important;
  }

  .text-purple {
    color: #9c27b0 !important;
  }

  .search-field :deep(.v-field) {
    min-height: 48px;
  }

  .cursor-pointer {
    cursor: pointer;
  }

  .fontfamily {
    font-family: var(--app-font) !important;
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
