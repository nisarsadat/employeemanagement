<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('واقعات')" :subtitle="t('فهرست و جستجو و مدیریت واقعات')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>
    <EventFinanceDialog
      v-model="eventFinanceDialogOpen"
      :id="selectedRow?.id"
      :account_id="selectedRow?.account_id ?? null"
      :message_to_finance="selectedRow?.message_to_finance ?? ''"
      :money_amount_words="selectedRow?.money_amount_words ?? ''"
      :account-items="accountItems"
      :is-rtl="isRtl"
      :loading="dialogSaving"
      @save="handleSaveEventFinance"
    />
    <!-- 🔍 Search + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Field -->
        <!-- Search Field -->
        <v-col cols="12" sm="12" md="12" lg="12">
          <SearchColumnFilter
            v-model:modelSearch="repo.Search"
            v-model:modelColumn="searchField"
            v-model:modelSelectedValue="selectedColumnValue"
            :headers="headersFT"
            :search-label="t('جستجو در واقعات...')"
            :column-label="t('ستون جستجو')"
            :all-columns-label="t('همه ستون‌ها')"
            :value-label="t('ارزش ستون')"
            :exclude-keys="['map']"
            :fetch-column-values="fetchColumnValues"
            @search="fetchAll"
            @clearSearch="clearSearch"
            @clearSelectedValue="clearSelectedValue"
          />
        </v-col>

        <!-- ✅ NEW: Year -->
        <v-col cols="12" sm="7" md="7" lg="5">
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
            {{ t('فلتر') }}
          </v-btn>
        </v-col>
        <v-col cols="12" sm="4" md="2" lg="1">
          <v-btn
            color="success"
            variant="flat"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-microsoft-excel"
            :loading="excelDownloading"
            :disabled="excelDownloading"
            @click="downloadExcel"
          >
            {{ t('اکسل') }}
          </v-btn>
        </v-col>
        <!-- Create Button -->
        <v-col cols="12" sm="4" md="3" lg="2">
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
        <v-col cols="12" sm="10">
          <v-select
            v-model="selectedPrintColumns"
            :items="printableHeaderOptions"
            item-title="title"
            item-value="key"
            multiple
            density="comfortable"
            variant="outlined"
            rounded="lg"
            class="bg-white"
            hide-details
            :label="t('ستون‌های چاپ')"
            :menu-props="{ maxHeight: 400 }"
          >
            <template #selection="{ index }">
              <span v-if="index === 0" class="text-body-2">
                {{ selectedPrintColumns.length }}
                {{ t('ستون انتخاب شده') }}
              </span>
            </template>
          </v-select>
        </v-col>
        <v-col cols="12" sm="2">
          <v-btn
            color="error"
            variant="flat"
            rounded="lg"
            size="large"
            block
            class="text-none"
            prepend-icon="mdi-printer"
            @click="printAll"
          >
            {{ t('چاپ') }}
          </v-btn>
        </v-col>
        <!-- ✅ NEW: Filter button -->
      </v-row>
    </v-container>

    <!-- 📊 STATS CARDS (backend counts + backend filters) -->
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
      <v-col cols="12" md="3" lg="2">
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
      <v-col cols="12" md="3" lg="2">
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

      <!-- Unpaid -->
      <v-col cols="12" md="3" lg="2">
        <v-card
          class="stat-card"
          :class="{ 'stat-card--active': activeCard === 'unpaid' }"
          @click="setCardFilter('unpaid')"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-red-darken-2 fontfamily">
                {{ unpaidCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('ناپرداخت') }}
              </div>
            </div>
            <v-avatar color="red-darken-2" variant="tonal" size="48">
              <v-icon color="red-darken-2">mdi-cash-remove</v-icon>
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
        :fields="headersFT"
        :cols="3"
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
      class="mx-2"
    >
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

      <template #cell-office_employee_akhistonky_code="{ row }">
        <v-tooltip :text="t('برای رفتن به صفحه کارمند کلیک کنید')" location="top">
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="deep-purple"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goOfficeEmployeeAkh(row)"
            >
              <v-icon size="16" class="mr-1">mdi-account-badge</v-icon>
              {{ getRow(row).office_employee_akhistonky_code || t('کارمند') }}
            </v-btn>
          </template>
        </v-tooltip>
      </template>

      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="getRow(row).coordinates"
          :title="getRow(row).event_type"
          :subtitle="getRow(row).event_specific_type"
          type="event"
          :zoom="16"
        />
      </template>

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

      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip :text="t('مشاهده واقعه')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('ویرایش واقعه')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('events.update')"
                size="small"
                v-bind="tooltipProps"
                color="amber-darken-2"
                @click.stop="goEdit(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('دانلود اسناد واقعه')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-if="auth.can('attachmentDownload.create')"
                v-bind="tooltipProps"
                color="green-darken-2"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadEventDocs(item)"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

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
          <v-tooltip :text="t('مالی')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="indigo"
                @click.stop="openEventFinanceDialog(item)"
              >
                mdi-cash-edit
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
    <v-dialog v-model="excelDownloadDialog" max-width="360" persistent>
      <v-card rounded="xl">
        <v-card-text class="pa-5">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-1 font-weight-bold">
              {{ t('در حال دانلود اکسل...') }}
            </div>
            <div class="text-body-2 text-primary font-weight-bold">
              {{ excelDownloadProgress }}%
            </div>
          </div>

          <v-progress-linear
            :model-value="excelDownloadProgress"
            color="success"
            height="10"
            rounded
            striped
          />

          <div class="text-caption text-medium-emphasis mt-3">
            {{ t('لطفاً منتظر بمانید') }}
          </div>
        </v-card-text>
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
  import { ref, onMounted, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRouter, useRoute } from 'vue-router';
  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';

  import { useDatabaseRepository } from '@/stores/DatabaseRepository';
  import { useLoginRepository } from '@/stores/LoginRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import SearchColumnFilter from '@/components/SearchColumnFilter.vue';
  import { useFinanceRepository } from '@/stores/FinanceRepository';
  import EventFinanceDialog from './EventFinanceDialog.vue';
  import { useRelatedRouteFilters } from '@/composables/useRelatedRouteFilters';
  import { useTablePrintExport } from '@/composables/useTablePrintExport';
  const financeRepo = useFinanceRepository();
  const accountItems = computed(() =>
    (financeRepo.accounts || []).map((a) => ({
      ...a,
      display: a.name ? `${a.name} (${a.price ?? '0'})` : `${a.id}`,
    }))
  );
  const eventFinanceDialogOpen = ref(false);
  const dialogSaving = ref(false);
  const selectedRow = ref(null);
  function openEventFinanceDialog(payload) {
    selectedRow.value = getRow(payload);
    eventFinanceDialogOpen.value = true;
  }
  const auth = useLoginRepository();
  const repo = useDatabaseRepository();
  const router = useRouter();
  const route = useRoute();
  const searchField = ref(null);
  const selectedColumnValue = ref(null);
  const { t, locale } = useI18n();
  const { relatedFilters } = useRelatedRouteFilters();
  const excelDownloadDialog = ref(false);
  const excelDownloadProgress = ref(0);
  const excelDownloading = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => (isRtl.value = newLocale === 'fa'));

  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (m) => {
    errorMessage.value = m;
    showError.value = true;
  };

  const getRow = (s) => (s && s.raw ? s.raw : s);

  const filterYear = ref(null);
  const filterMonth = ref(null);

  const yearItems = ref([]);
  const monthItems = ref([]);

  const activeCard = ref('all');

  // for the finance part
  async function handleSaveEventFinance(patch) {
    dialogSaving.value = true;
    try {
      const cleanPatch = {
        id: patch?.id,
        account_id: patch?.account_id ?? null,
        message_to_finance: patch?.message_to_finance ?? '',
        money_amount_words: patch?.money_amount_words ?? '',
      };

      await repo.UpdateEvent(cleanPatch.id, cleanPatch);
      await fetchAll();
      eventFinanceDialogOpen.value = false;
    } catch (e) {
      console.error('handleSaveEventFinance error', e);
      showErrorMessage(t('خطا در ذخیره معلومات مالی واقعه'));
    } finally {
      dialogSaving.value = false;
    }
  }
  async function fetchYearItemsFromDb() {
    try {
      const res = await repo.FetchEventDateOptions({ date_field: 'event_date' });
      const years = res?.data?.years || [];

      yearItems.value = years.map((y) => ({
        value: y.value,
        title: y.label,
      }));
    } catch (e) {
      console.error('fetchYearItemsFromDb error', e);
    }
  }
  async function fetchMonthItemsFromDb(year = null) {
    try {
      const res = await repo.FetchEventDateOptions({
        date_field: 'event_date',
        year,
      });

      const months = res?.data?.months || [];

      monthItems.value = months.map((m) => ({
        value: m.value,
        title: m.label,
      }));
    } catch (e) {
      console.error('fetchMonthItemsFromDb error', e);
      monthItems.value = [];
    }
  }

  async function clearAllFilters() {
    repo.Search = '';
    searchField.value = null;
    filterYear.value = null;
    filterMonth.value = null;
    activeCard.value = 'all';
    selectedColumnValue.value = null;
    repo.page = 1;

    await router.replace({ path: '/app/events', query: {} });
    await fetchAll();
  }
  async function downloadExcel() {
    try {
      excelDownloading.value = true;
      excelDownloadDialog.value = true;
      excelDownloadProgress.value = 0;

      await repo.DownloadEventsExcel({
        search: repo.Search || '',
        search_field: searchField.value,
        year: filterYear.value,
        month: filterMonth.value,
        date_field: 'event_date',
        stat: relatedFilters.value.stat || activeCard.value,
        type: relatedFilters.value.type,
        search_value: selectedColumnValue.value,
        date: relatedFilters.value.date,
        date_from: relatedFilters.value.date_from,
        date_to: relatedFilters.value.date_to,
        from_date: relatedFilters.value.from_date,
        to_date: relatedFilters.value.to_date,

        event_id: relatedFilters.value.event_id,
        resource_id: relatedFilters.value.resource_id,
        office_employee_id: relatedFilters.value.office_employee_id,
        information_id: relatedFilters.value.information_id,
        fifty_six_format_id: relatedFilters.value.fifty_six_format_id,

        onDownloadProgress: (progressEvent) => {
          const total = progressEvent.total || 0;
          const loaded = progressEvent.loaded || 0;

          if (total > 0) {
            excelDownloadProgress.value = Math.min(100, Math.round((loaded * 100) / total));
          } else {
            if (excelDownloadProgress.value < 90) {
              excelDownloadProgress.value += 5;
            }
          }
        },
      });

      excelDownloadProgress.value = 100;
    } catch (e) {
      console.error('downloadExcel error', e);
      showErrorMessage(t('خطا در دانلود اکسل'));
    } finally {
      setTimeout(() => {
        excelDownloadDialog.value = false;
        excelDownloading.value = false;
        excelDownloadProgress.value = 0;
      }, 400);
    }
  }
  async function applyMonthYearFilter() {
    repo.page = 1;
    await fetchAll();
  }

  function setCardFilter(type) {
    activeCard.value = type;
    repo.page = 1;
    fetchAll();
  }
  async function fetchColumnValues(field, q = '') {
    try {
      const res = await repo.FetchEventFilterOptions({
        field,
        q,
      });

      return res?.data?.options || [];
    } catch (e) {
      console.error('fetchColumnValues error', e);
      return [];
    }
  }
  function clearSelectedValue() {
    selectedColumnValue.value = null;
    repo.page = 1;
    fetchAll();
  }
  const baseEvents = computed(() => repo.Events || []);

  const totalCount = computed(() => Number(repo.eventCardCounts?.total ?? 0));
  const syasiCount = computed(() => Number(repo.eventCardCounts?.syasi ?? 0));
  const jenayeeCount = computed(() => Number(repo.eventCardCounts?.jenayee ?? 0));
  const todayAddedCount = computed(() => Number(repo.eventCardCounts?.today ?? 0));
  const unpaidCount = computed(() => Number(repo.eventCardCounts?.unpaid ?? 0));

  function formatDateYMD(value) {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) {
      const s = String(value);
      return s.length >= 10 ? s.slice(0, 10) : s;
    }
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }

  const rowsFT = computed(() =>
    (baseEvents.value || []).map((e) => ({
      ...e,
      event_date: formatDateYMD(e.event_date),
      resource_display: [e.resource?.code_number, e.resource?.name].filter(Boolean).join(' - ') || e.resource_id || '',
      office_employee_display: [ (e.officeEmployee || e.office_employee)?.code_number, (e.officeEmployee || e.office_employee)?.name ].filter(Boolean).join(' - ') || e.office_employee_id || '',
    }))
  );

  const headersFT = computed(() => [
    { title: t('آی ډی'), key: 'id' },
    { title: t('کود منبع'), key: 'resource_display' },
    { title: t('کود کارمند'), key: 'office_employee_display' },
    { title: t('ترسره شوی همغږی'), key: 'talks' },
    { title: t('پول پرداخت شده'), key: 'finance_paid_amount' },
    { title: t('د پیسو د پرداخت وضعیت'), key: 'finance_status' },
    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('نوعیت واقعه'), key: 'event_type' },
    { title: t('نوعیت مشخص واقعه'), key: 'event_specific_type' },
    { title: t('هدف کی بود؟/مکان بود؟'), key: 'target_person' },
    { title: t('کی جرم را انجام داده؟'), key: 'criminal' },
    { title: t('تاریخ واقعه'), key: 'event_date' },
    { title: t('ساعت واقعه'), key: 'event_hour' },
    { title: t('آمریت'), key: 'event_zone' },
    { title: t('حوزه'), key: 'event_hawza' },
    { title: t('محل مشخص'), key: 'event_specific_place' },
    { title: t('کوردینات'), key: 'coordinates' },
    { title: t('تلفات مالی'), key: 'talafat_mali' },
    { title: t('زخمی شده'), key: 'injured' },
    { title: t('کشته شده'), key: 'killed' },
    { title: t('کود کارمند واقعه گیرنده'), key: 'office_employee_akhistonky_code' },
    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('نوت راجع به اسناد'), key: 'docs_note' },
    { title: t('ملاحظات'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    {
      title: t('شناسه‌ها'),
      keys: ['resource_display', 'office_employee_display', 'office_employee_akhistonky_code'],
    },
    { title: t('نوع واقعه'), keys: ['event_type', 'event_specific_type'] },
    { title: t('هدف و عامل'), keys: ['target_person', 'criminal'] },
    { title: t('زمان'), keys: ['event_date', 'event_hour'] },
    {
      title: t('مکان'),
      keys: ['event_zone', 'event_hawza', 'event_specific_place', 'coordinates'],
    },
    { title: t('تلفات'), keys: ['talafat_mali', 'injured', 'killed'] },
    { title: t('متن‌ها'), keys: ['tafseel', 'note', 'docs_note'] },
  ]);

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

  const filters = ref({});

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

  function goCreate() {
    router.push('/app/eventsform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/eventsform/${row.id}`);
  }

  function goOfficeEmployee(payload) {
    const row = getRow(payload);
    const empId = row?.office_employee_id || row?.office_employee_akhistonky_id;
    if (!empId) return;

    router.push({
      path: '/app/officeemployee',
      query: {
        office_employee_id: empId,
        source: 'events',
      },
    });
  }
  function goOfficeEmployeeAkh(payload) {
    const row = getRow(payload);
    const empId = row?.office_employee_akhistonky_id;
    if (!empId) return;

    router.push({
      path: '/app/officeemployee',
      query: {
        office_employee_akhistonky_id: empId,
        source: 'events',
      },
    });
  }
  const printLabels = computed(() => ({
    print: t('چاپ'),
    record_count: t('تعداد ریکارد'),
  }));
  const { printAll } = useTablePrintExport({
    repo: {
      OpenResourcesPrintView: repo.OpenEventsPrintView,
    },
    moduleKey: 'events',

    getFilters: () => ({
      search: repo.Search,
      search_field: searchField.value,
      search_value: selectedColumnValue.value,
      year: filterYear.value,
      month: filterMonth.value,
      card_filter: activeCard.value,
    }),

    getHeaders: () => headersFT.value,

    getSelectedColumnKeys: () => selectedPrintColumns.value,

    getTitle: () => t('چاپ واقعات'),

    getPrintLabels: () => printLabels.value,

    getLocale: () => locale.value,

    defaultFileName: 'events-print',
  });
  const selectedPrintColumns = ref([]);

  function buildDefaultPrintColumns() {
    return headersFT.value.map((h) => h.key).filter((key) => !['map', 'picture'].includes(key));
  }

  const printableHeaderOptions = computed(() => {
    return headersFT.value.filter((h) => !['map', 'picture'].includes(h.key));
  });

  watch(
    printableHeaderOptions,
    (newColumns) => {
      if (!selectedPrintColumns.value.length) {
        selectedPrintColumns.value = newColumns.map((c) => c.key);
      }
    },
    { immediate: true }
  );
  function goResource(payload) {
    const row = getRow(payload);
    const resId = row?.resource_id;
    if (!resId) return;

    router.push({
      path: '/app/resources',
      query: {
        resource_id: resId,
        source: 'events',
      },
    });
  }

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

  function handlePageChange(value) {
    repo.page = value;
    fetchAll();
  }

  function handlePerPageChange(value) {
    repo.itemsPerPage = value;
    repo.page = 1;
    fetchAll();
  }

  async function fetchAll() {
    try {
      await repo.FetchEventsData({
        search: repo.Search,
        search_field: searchField.value,
        search_value: selectedColumnValue.value,
        year: filterYear.value,
        month: filterMonth.value,
        date_field: 'event_date',

        card_filter: relatedFilters.value.stat || activeCard.value,
        type: relatedFilters.value.type,

        date: relatedFilters.value.date,
        date_from: relatedFilters.value.date_from,
        date_to: relatedFilters.value.date_to,
        from_date: relatedFilters.value.from_date,
        to_date: relatedFilters.value.to_date,

        event_id: relatedFilters.value.event_id,
        resource_id: relatedFilters.value.resource_id,
        office_employee_id: relatedFilters.value.office_employee_id,
        information_id: relatedFilters.value.information_id,
        fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
      });
    } catch (e) {
      console.error('fetchAll events error', e);
      showErrorMessage(t('خطا در دریافت فهرست واقعات'));
    }
  }
  function clearSearch() {
    repo.Search = '';
    searchField.value = null;
    selectedColumnValue.value = null;
    repo.page = 1;
    fetchAll();
  }
  watch(filterYear, async (newYear) => {
    filterMonth.value = null;
    await fetchMonthItemsFromDb(newYear || null);
  });
  watch(
    () => route.query,
    async () => {
      repo.page = 1;
      await fetchAll();
    },
    { deep: true }
  );

  onMounted(async () => {
    await fetchYearItemsFromDb();
    await fetchMonthItemsFromDb(null);
    await fetchAll();
    await financeRepo.FetchAccountsData();
    selectedPrintColumns.value = buildDefaultPrintColumns();
  });
  usePageRefresh('/app/events', fetchAll);
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

  .search-field :deep(.v-field) {
    min-height: 48px;
  }

  .fontfamily {
    font-family: var(--app-font) !important;
  }
</style>
