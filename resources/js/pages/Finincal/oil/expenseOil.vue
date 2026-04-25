<template>
  <AppBar :title="t('حساب مالي')" @toggle-drawer="drawer = !drawer">
    <template #actions>
      <v-btn icon />
    </template>
  </AppBar>
  <v-container fluid class="pt-6 page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- SUMMARY -->
    <v-row class="mb-4" dense>
      <v-col cols="12" md="4">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis">
                  {{ t('ټول مصرف') }}
                </div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ liters(totalExpenseCardValue) }}
                </div>
              </div>
              <div class="stat-icon-wrap"><v-icon size="26">mdi-cash-minus</v-icon></div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis">
                  {{ t('ټول عاید') }}
                </div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ liters(totalIncomeCardValue) }}
                </div>
              </div>
              <div class="stat-icon-wrap"><v-icon size="26">mdi-cash-plus</v-icon></div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis">
                  {{ t('ټول تېل (موجود)') }}
                </div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ liters(balanceCardValue) }}
                </div>
              </div>
              <div class="stat-icon-wrap"><v-icon size="26">mdi-fuel</v-icon></div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-card rounded="xl" class="shadow-sm mb-4 filters-card">
      <v-card-text class="pa-4">
        <v-row dense class="align-center">
          <v-col cols="12" sm="12" md="10">
            <SearchColumnFilter
              v-model:modelSearch="repo.Search"
              v-model:modelColumn="searchField"
              v-model:modelSelectedValue="selectedColumnValue"
              :headers="headersFT"
              :search-label="t('common.search')"
              :column-label="t('ستون جستجو')"
              :all-columns-label="t('همه ستون‌ها')"
              :value-label="t('ارزش ستون')"
              :exclude-keys="[]"
              :fetch-column-values="fetchColumnValues"
              @search="applyFilters"
              @clearSearch="clearSearch"
              @clearSelectedValue="clearSelectedValue"
            />
          </v-col>
          <v-col cols="12" sm="6" md="1">
            <v-btn
              color="primary"
              variant="flat"
              rounded="lg"
              block
              size="x-large"
              class="text-none"
              prepend-icon="mdi-filter"
              @click="applyFilters"
            >
              {{ t('فلتر') }}
            </v-btn>
          </v-col>

          <v-col cols="12" sm="6" md="1">
            <v-btn
              color="orange"
              size="x-large"
              rounded="lg"
              block
              class="text-none"
              prepend-icon="mdi-filter-remove"
              @click="resetFilters"
            >
              {{ t('ریست') }}
            </v-btn>
          </v-col>
          <v-col cols="12" sm="2">
            <v-select
              v-model="selectedYear"
              :items="yearItems"
              item-title="title"
              item-value="value"
              density="comfortable"
              variant="outlined"
              rounded="lg"
              class="bg-white"
              hide-details
              clearable
              :label="t('common.year')"
            />
          </v-col>

          <v-col cols="12" sm="3">
            <v-select
              v-model="selectedMonth"
              :items="monthItems"
              item-title="title"
              item-value="value"
              density="comfortable"
              variant="outlined"
              class="bg-white"
              rounded="lg"
              hide-details
              clearable
              :label="t('common.month')"
            />
          </v-col>
          <v-col cols="12" sm="1" md="1">
            <v-btn
              color="success"
              variant="flat"
              class="text-none"
              size="x-large"
              rounded="lg"
              prepend-icon="mdi-microsoft-excel"
              :loading="excelDownloading"
              :disabled="excelDownloading"
              @click="downloadExcel"
            >
              {{ t('اکسل') }}
            </v-btn>
          </v-col>
          <v-col cols="12" sm="1" md="2">
            <v-btn
              color="primary"
              class="text-none"
              size="x-large"
              prepend-icon="mdi-plus"
              @click="openCreate"
            >
              {{ t('نوی د تيلو مصرف') }}
            </v-btn>
          </v-col>
          <v-col cols="12" sm="3" md="2">
            <v-select
              v-model="selectedPrintColumns"
              :items="printableHeaderOptions"
              item-title="title"
              item-value="key"
              multiple
              density="comfortable"
              variant="outlined"
              rounded="lg"
              class="bg-white print-columns-select"
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
          <v-col cols="12" sm="2" >
            <v-btn
              color="error"
              variant="flat"
              class="text-none"
              size="x-large"
              rounded="lg"
              prepend-icon="mdi-printer"
              @click="printAll"
            >
              {{ t('چاپ') }}
            </v-btn>
          </v-col>

          <v-col cols="12" class="pt-2">
            <div class="d-flex ga-2 flex-wrap">
              <v-chip
                v-if="repo.Search"
                size="small"
                variant="tonal"
                prepend-icon="mdi-magnify"
                closable
                @click:close="clearSearch"
              >
                {{ repo.Search }}
              </v-chip>

              <v-chip
                v-if="normalizedSearchField"
                size="small"
                variant="tonal"
                prepend-icon="mdi-table-column"
                closable
                @click:close="clearSelectedValue"
              >
                {{
                  headersFT.find((h) => h.key === normalizedSearchField)?.title ??
                  normalizedSearchField
                }}
              </v-chip>

              <v-chip
                v-if="normalizedSearchValue"
                size="small"
                variant="tonal"
                prepend-icon="mdi-filter-outline"
                closable
                @click:close="clearSelectedValue"
              >
                {{ normalizedSearchValue }}
              </v-chip>

              <v-chip
                v-if="selectedYear"
                size="small"
                variant="tonal"
                prepend-icon="mdi-calendar"
                closable
                @click:close="clearYear"
              >
                {{ yearItems?.find((y) => y.value === selectedYear)?.title ?? selectedYear }}
              </v-chip>

              <v-chip
                v-if="selectedYear && selectedMonth"
                size="small"
                variant="tonal"
                prepend-icon="mdi-calendar-month"
                closable
                @click:close="clearMonth"
              >
                {{ monthItems.find((m) => m.value === selectedMonth)?.title ?? selectedMonth }}
              </v-chip>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <v-card elevation="2" class="elevation-0 table-card">
      <v-card-text class="pa-0">
        <FilterableTable
          :loading="repo.loading"
          :headers="headersFT"
          :rows="rowsFT"
          :filters="filters"
          :topGroups="topGroups"
          :totalItems="repo.totalItems"
          :page="repo.page"
          :perPage="repo.itemsPerPage"
          :disableDateConvert="true"
          @update:page="onPage"
          @update:perPage="onItemsPerPage"
        >
          <template #actions="{ item }">
            <div class="d-flex align-center ga-2">
              <v-tooltip :text="t('ویرایش')" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-btn
                    v-bind="tooltipProps"
                    icon
                    size="x-small"
                    variant="tonal"
                    color="amber-darken-2"
                    class="icon-btn"
                    @click.stop="startEdit(item)"
                  >
                    <v-icon size="18">mdi-pencil</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>

              <v-tooltip :text="t('حذف')" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-btn
                    v-bind="tooltipProps"
                    icon
                    size="x-small"
                    variant="tonal"
                    color="red-darken-2"
                    class="icon-btn"
                    @click.stop="openDeleteDialog(item)"
                  >
                    <v-icon size="18">mdi-delete</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
            </div>
          </template>
        </FilterableTable>
      </v-card-text>
    </v-card>

    <GlobalPagination
      :page="repo.page"
      :items-per-page="repo.itemsPerPage"
      :total-items="repo.totalItems"
      @update:page="onPage"
      @update:items-per-page="onItemsPerPage"
    />

    <ExpenseOilFormDialog
      v-model="dialog"
      :editMode="editMode"
      :value="draft"
      :busy="repo.loading"
      @save="onSave"
    />

    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
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
  </v-container>
</template>

<script setup>
  import { ref, reactive, computed, onMounted, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useOilRepository } from '@/stores/OilRepository';
  import ExpenseOilFormDialog from './ExpenseOilFormDialog.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';
  import SearchColumnFilter from '@/components/SearchColumnFilter.vue';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import { useCalendar } from '../../../../composables/useCalendar';
  import AppBar from '@/components/AppBar.vue';
  import { useTablePrintExport } from '@/composables/useTablePrintExport';
  const repo = useOilRepository();
  const { t, locale } = useI18n();

  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  const dialog = ref(false);
  const editMode = ref(false);

  const deleteDialog = ref(false);
  const deleteTarget = ref(null);

  const excelDownloadDialog = ref(false);
  const excelDownloadProgress = ref(0);
  const excelDownloading = ref(false);

  const selectedYear = ref(null);
  const selectedMonth = ref(null);
  const searchField = ref(null);
  const selectedColumnValue = ref(null);

  const normalizedSearchField = computed(() => {
    const v = searchField.value;
    if (!v) return null;
    if (typeof v === 'string') return v;
    return v.key ?? v.value ?? null;
  });

  const normalizedSearchValue = computed(() => {
    const v = selectedColumnValue.value;
    if (v === null || v === undefined || v === '') return null;
    if (typeof v === 'string' || typeof v === 'number') return v;
    return v.value ?? v.title ?? null;
  });

  const yearItems = ref([]);
  const monthItems = ref([]);

  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  async function fetchYearItemsFromDb() {
    try {
      const res = await repo.FetchExpenseOilDateOptions();
      const years = res?.data?.years || [];

      yearItems.value = years.map((y) => ({
        value: y.value,
        title: y.label,
      }));
    } catch (e) {
      console.error('fetchYearItemsFromDb error', e);
      yearItems.value = [];
    }
  }

  async function fetchMonthItemsFromDb(year = null) {
    try {
      const res = await repo.FetchExpenseOilDateOptions({ year });
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

  watch(selectedYear, async (newYear) => {
    selectedMonth.value = null;
    await fetchMonthItemsFromDb(newYear);
  });

  const headersFT = computed(() => [
    { title: t('کود کارمند'), key: 'employee_code', sortable: false },
    { title: t('نام'), key: 'name', sortable: false },
    { title: t('نام پدر'), key: 'father_name', sortable: false },
    { title: t('نیټه'), key: 'date', sortable: false },
    { title: t('لیټر په عدد'), key: 'liter_in_number', sortable: false },
    { title: t('لیټر په حروف'), key: 'liter_in_words', sortable: false },
    { title: t('نوعیت'), key: 'account_name', sortable: false },
    { title: t('تفصیل'), key: 'tafseel', sortable: false },
    { title: t('پرداخت کننده'), key: 'payer', sortable: false },
    { title: t('اخیستونکی لاسلیک'), key: 'signature', sortable: false },
    { title: t('ملاحظات'), key: 'note', sortable: false },
  ]);

  async function fetchColumnValues(field, q = '') {
    try {
      const normalizedField =
        typeof field === 'string' ? field : field?.key ?? field?.value ?? null;

      if (!normalizedField) return [];

      const res = await repo.FetchExpenseOilFilterOptions?.({
        field: normalizedField,
        q,
      });

      return res?.data?.data?.options || [];
    } catch (e) {
      console.error('fetchColumnValues error', e);
      return [];
    }
  }

  const rowsFT = computed(() => {
    return (repo.ExpenseOils || []).map((it, i) => ({
      ...it,
      idOrTmp: it?.id ?? `tmp-${i}`,
      index: i + 1,
      account_name: it?.account?.name ?? it?.account ?? '—',
      account: it?.account?.name ?? it?.account ?? '—',
      name: it?.name ?? '—',
      father_name: it?.father_name ?? '—',
      employee_code: it?.employee?.code_number ?? it?.office_employee?.code_number ?? '—',
      officeemployee: it?.employee?.code_number ?? it?.office_employee?.code_number ?? '—',
      payer: it?.payer ?? '—',
      liter_in_number: litersRaw(it?.liter_in_number ?? it?.oil_quantity ?? 0),
      liter_in_words: it?.liter_in_words ?? '—',
      date: it?.date ?? '',
      note: it?.note ?? '',
      tafseel: it?.tafseel ?? it?.destination ?? '',
      raw: it,
    }));
  });

  const filters = [];
  const topGroups = [];

  const summary = computed(() => repo.summary || {});

  const hasAnyFilter = computed(() => {
    return !!(
      repo.Search ||
      normalizedSearchField.value ||
      normalizedSearchValue.value ||
      selectedYear.value ||
      selectedMonth.value ||
      summary.value?.has_filters
    );
  });

  const totalExpenseCardValue = computed(() =>
    hasAnyFilter.value
      ? summary.value?.filtered_expense_quantity
      : summary.value?.total_expense_quantity
  );

  const totalIncomeCardValue = computed(() => summary.value?.total_income_quantity);

  const balanceCardValue = computed(() =>
    hasAnyFilter.value
      ? summary.value?.filtered_balance ?? summary.value?.total_quantity_all_accounts
      : summary.value?.total_quantity_all_accounts
  );

  const draft = reactive({
    id: null,
    oil_account_id: null,
    employee_id: null,
    name: '',
    father_name: '',
    payer: '',
    date: null,
    liter_in_number: 0,
    liter_in_words: '',
    tafseel: '',
    note: '',
  });

  const fetchAll = async () => {
    await repo.FetchExpenseOils({
      page: repo.page,
      itemsPerPage: repo.itemsPerPage,
      search: repo.Search,
      search_field: normalizedSearchField.value,
      search_value: normalizedSearchValue.value,
      year: selectedYear.value,
      month: selectedMonth.value,
    });
  };

  usePageRefresh('/app/finance/oil/expense', fetchAll);

  function applyFilters() {
    repo.page = 1;
    fetchAll();
  }

  function clearSearch() {
    repo.Search = '';
    repo.page = 1;
    fetchAll();
  }

  function clearSelectedValue() {
    selectedColumnValue.value = null;
    repo.page = 1;
    fetchAll();
  }

  function clearYear() {
    selectedYear.value = null;
    selectedMonth.value = null;
    applyFilters();
  }

  function clearMonth() {
    selectedMonth.value = null;
    applyFilters();
  }

  function resetFilters() {
    selectedYear.value = null;
    selectedMonth.value = null;
    searchField.value = null;
    selectedColumnValue.value = null;

    if (typeof repo.ResetExpenseOilsFilters === 'function') {
      repo.ResetExpenseOilsFilters();
    } else {
      repo.page = 1;
      repo.itemsPerPage = 10;
      repo.Search = '';
      repo.searchField = null;
      repo.searchValue = null;
      repo.year = null;
      repo.month = null;
    }

    applyFilters();
  }

  function refreshTable() {
    fetchAll();
  }

  const openCreate = () => {
    editMode.value = false;
    Object.assign(draft, {
      id: null,
      oil_account_id: null,
      employee_id: null,
      name: '',
      father_name: '',
      payer: '',
      date: null,
      liter_in_number: 0,
      liter_in_words: '',
      tafseel: '',
      note: '',
    });
    dialog.value = true;
  };

  const startEdit = async (payload) => {
    const row = getRow(payload);
    const id = row?.id;
    if (!id) return;

    editMode.value = true;

    await repo.FetchExpenseOil(id);

    const d = repo.ExpenseOil || {};
    Object.assign(draft, {
      id: d.id ?? null,
      oil_account_id: d.oil_account_id ?? d.account?.id ?? null,
      employee_id: d.employee_id ?? d.employee?.id ?? d.office_employee?.id ?? null,
      name: d.name ?? '',
      father_name: d.father_name ?? '',
      payer: d.payer ?? '',
      date: d.date ?? null,
      liter_in_number: d.liter_in_number ?? d.oil_quantity ?? 0,
      liter_in_words: d.liter_in_words ?? d.liter_in_word ?? '',
      tafseel: d.tafseel ?? d.destination ?? '',
      note: d.note ?? '',
    });

    dialog.value = true;
  };

  const { toIso } = useCalendar();

  const onSave = async (payload) => {
    if (editMode.value && payload.id) await repo.UpdateExpenseOil(payload.id, payload);
    else await repo.CreateExpenseOil(payload);

    dialog.value = false;
    await fetchAll();
  };

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
      await repo.DeleteExpenseOil(row.id);
      await fetchAll();
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  const onPage = async (p) => {
    repo.page = p || 1;
    await fetchAll();
  };

  const onItemsPerPage = async (pp) => {
    repo.itemsPerPage = pp || 10;
    repo.page = 1;
    await fetchAll();
  };

  async function downloadExcel() {
    try {
      excelDownloading.value = true;
      excelDownloadDialog.value = true;
      excelDownloadProgress.value = 0;

      await repo.DownloadExpenseOilsExcel({
        search: repo.Search || '',
        search_field: normalizedSearchField.value,
        search_value: normalizedSearchValue.value,
        year: selectedYear.value,
        month: selectedMonth.value,
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
    } finally {
      setTimeout(() => {
        excelDownloadDialog.value = false;
        excelDownloading.value = false;
        excelDownloadProgress.value = 0;
      }, 400);
    }
  }
  const selectedPrintColumns = ref([]);

  function buildDefaultPrintColumns() {
    return headersFT.value
      .map((h) => h.key)
      .filter(
        (key) => key && !['actions', 'picture', 'image', 'photo', 'profile_picture'].includes(key)
      );
  }

  const printableHeaderOptions = computed(() => {
    return headersFT.value.filter(
      (h) => h.key && !['actions', 'picture', 'image', 'photo', 'profile_picture'].includes(h.key)
    );
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

  const printLabels = computed(() => ({
    print: t('چاپ'),
    index: t('#'),
    record_count: t('تعداد ریکارد'),
    no_data: t('معلومات برای چاپ وجود ندارد'),
    footer_note: '',
  }));

  const { printAll } = useTablePrintExport({
    repo: {
      OpenResourcesPrintView: repo.OpenExpenseOilsPrintView,
    },

    moduleKey: 'expense-oils',

    getFilters: () => ({
      search: repo.Search || '',
      search_field: normalizedSearchField.value || '',
      search_value: normalizedSearchValue.value || '',
      year: selectedYear.value || '',
      month: selectedMonth.value || '',
      date_field: 'date',

      // keep these for account/employee filters if you use them later
      oil_account_id: repo.oilAccountId || '',
      employee_id: repo.employeeId || '',
    }),

    getHeaders: () => headersFT.value,

    getSelectedColumnKeys: () => selectedPrintColumns.value,

    getTitle: () => t('چاپ مصرف تیل'),

    getPrintLabels: () => printLabels.value,

    getLocale: () => locale.value,

    defaultFileName: 'expense-oils-print',
  });
  const litersRaw = (n) =>
    Number(n ?? 0).toLocaleString(undefined, {
      maximumFractionDigits: 3,
    });

  const liters = (n) =>
    `${Number(n ?? 0).toLocaleString(undefined, { maximumFractionDigits: 3 })} L`;

  onMounted(async () => {
    await fetchYearItemsFromDb();
    await fetchMonthItemsFromDb(null);
    selectedPrintColumns.value = buildDefaultPrintColumns();
    await fetchAll();
  });
</script>

<style scoped>
  .page-bg {
    min-height: 100%;
  }

  .stat-card {
    border: 1px solid rgba(0, 0, 0, 0.05);
    overflow: hidden;
    position: relative;
  }
  .stat-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.07), rgba(156, 39, 176, 0.05));
    pointer-events: none;
  }
  .stat-value {
    letter-spacing: 0.2px;
  }
  .stat-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(25, 118, 210, 0.08);
  }

  .filters-card,
  .table-card {
    border: 1px solid rgba(0, 0, 0, 0.05);
  }

  .icon-btn {
    border-radius: 10px;
  }

  :deep(.v-table) {
    border-radius: 16px;
  }
  .print-columns-select {
    max-width: 320px;
    min-width: 260px;
  }
  .search-field :deep(.v-field) {
    min-height: 48px;
  }
</style>
