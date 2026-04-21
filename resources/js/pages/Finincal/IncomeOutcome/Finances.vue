<template>
  <AppBar :title="t('حساب مالي')" @toggle-drawer="drawer = !drawer">
    <template #actions>
      <v-btn icon />
    </template>
  </AppBar>
  <v-container fluid class="pt-6 page-bg" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- ✅ SUMMARY CARDS -->
    <v-row class="mb-4" dense>
      <v-col cols="12" md="4">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">
                  {{ t('accounts.summary.balance') }}
                </div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(balanceCardValue) }}
                </div>
              </div>
              <div class="stat-icon-wrap"><v-icon size="26">mdi-bank</v-icon></div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">
                  {{ t('accounts.summary.income') }}
                </div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(incomeCardValue) }}
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
                <div class="text-caption text-medium-emphasis family">
                  {{ t('accounts.summary.outcome') }}
                </div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(outcomeCardValue) }}
                </div>
              </div>
              <div class="stat-icon-wrap"><v-icon size="26">mdi-cash-minus</v-icon></div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-card rounded="xl" class="shadow-sm mb-4 filters-card">
      <v-card-text class="pa-4">
        <v-row dense class="align-center">
          <!-- Search Column Filter -->
          <v-col cols="12" sm="12" md="8">
            <SearchColumnFilter
              v-model:modelSearch="repo.financesSearch"
              v-model:modelColumn="searchField"
              v-model:modelSelectedValue="selectedColumnValue"
              :headers="headersFT"
              :search-label="t('common.search')"
              :column-label="t('ستون جستجو')"
              :all-columns-label="t('همه ستون‌ها')"
              :value-label="t('ارزش ستون')"
              :exclude-keys="['expense_result']"
              :fetch-column-values="fetchColumnValues"
              @search="applyFilters"
              @clearSearch="clearSearch"
              @clearSelectedValue="clearSelectedValue"
            />
          </v-col>

          <!-- YEAR -->
          <v-col cols="12" sm="6" md="2">
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

          <!-- MONTH -->
          <v-col cols="12" sm="6" md="2">
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

          <!-- Apply -->
          <v-col cols="12" sm="6" md="1">
            <v-btn
              color="primary"
              variant="flat"
              rounded="lg"
              block
              class="text-none"
              prepend-icon="mdi-filter"
              @click="applyFilters"
            >
              {{ t('فلتر') }}
            </v-btn>
          </v-col>

          <!-- Reset -->
          <v-col cols="12" sm="6" md="1">
            <v-btn
              color="orange"
              rounded="lg"
              block
              class="text-none"
              prepend-icon="mdi-filter-remove"
              @click="resetFilters"
            >
              {{ t('فلتر') }}
            </v-btn>
          </v-col>

          <!-- Actions -->
          <v-col cols="12" class="d-flex justify-end mt-3">
            <div class="d-flex ga-2 flex-wrap justify-end w-100">
              <v-btn
                color="success"
                variant="flat"
                class="text-none"
                rounded="lg"
                prepend-icon="mdi-microsoft-excel"
                :loading="excelDownloading"
                :disabled="excelDownloading"
                @click="downloadExcel"
              >
                {{ t('اکسل') }}
              </v-btn>

              <v-btn color="error" class="text-none" prepend-icon="mdi-plus" @click="openCreate">
                {{ t('finances.actions.createExpense') }}
              </v-btn>

              <v-btn
                color="primary"
                variant="tonal"
                class="text-none"
                prepend-icon="mdi-file-pdf-box"
                @click="exportPdfAll"
              >
                {{ t('pdf') }}
              </v-btn>

              <v-btn
                color="primary"
                variant="tonal"
                class="text-none"
                prepend-icon="mdi-file-word-box"
                @click="exportWordAll"
              >
                {{ t('word') }}
              </v-btn>

              <v-btn
                color="primary"
                class="text-none"
                prepend-icon="mdi-refresh"
                @click="refreshTable"
              >
                {{ t('refresh') }}
              </v-btn>
            </div>
          </v-col>

          <!-- Active chips -->
          <v-col cols="12" class="pt-2">
            <div class="d-flex ga-2 flex-wrap">
              <v-chip
                v-if="repo.financesSearch"
                size="small"
                variant="tonal"
                prepend-icon="mdi-magnify"
                closable
                @click:close="clearSearch"
              >
                {{ repo.financesSearch }}
              </v-chip>

              <v-chip
                v-if="normalizedSearchField"
                size="small"
                variant="tonal"
                prepend-icon="mdi-table-column"
                closable
                @click:close="clearSelectedValue"
              >
                {{ headersFT.find((h) => h.key === normalizedSearchField)?.title ?? normalizedSearchField }}
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

    <!-- Table -->
    <v-card rounded="xl" class="shadow-sm table-card">
      <v-card-text class="pa-0">
        <FilterableTable
          :loading="repo.loading"
          :headers="headersFT"
          :rows="rowsFT"
          :filters="filters"
          :topGroups="topGroups"
          :totalItems="repo.financesTotalItems"
          :page="repo.financesPage"
          :perPage="repo.financesPerPage"
          @update:page="onPage"
          @update:perPage="onPerPage"
        >
          <template #expense_result="{ item }">
            <div class="d-flex align-center">
              <v-chip
                size="small"
                class="rounded-lg"
                variant="tonal"
                :color="
                  item?.expense_kind === 'information'
                    ? 'info'
                    : item?.expense_kind === 'event'
                    ? 'deep-purple'
                    : 'grey'
                "
              >
                <v-icon start size="16">
                  {{
                    item?.expense_kind === 'information'
                      ? 'mdi-information-outline'
                      : item?.expense_kind === 'event'
                      ? 'mdi-calendar-alert'
                      : 'mdi-clipboard-text-outline'
                  }}
                </v-icon>
                {{ item?.expense_label || '—' }}
              </v-chip>
            </div>
          </template>

          <template #actions="{ item }">
            <div class="d-flex align-center ga-2">
              <v-tooltip :text="t('finances.tooltips.editExpense')" location="top">
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

              <v-tooltip :text="t('finances.tooltips.deleteExpense')" location="top">
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
      :page="repo.financesPage"
      :items-per-page="repo.financesPerPage"
      :total="repo.financesTotalItems"
      @update:page="onPage"
      @update:itemsPerPage="onPerPage"
    />

    <FinanceFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="rtl"
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
import { computed, ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import FilterableTable from '@/components/FilterableTable.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import GlobalPagination from '@/components/GlobalPagination.vue';
import FinanceFormDialog from './FinancesFormDialog.vue';
import { useFinanceRepository } from '@/stores/FinanceRepository';
import { usePageRefresh } from '@/composables/usePageRefresh';
import AppBar from '@/components/AppBar.vue';
import SearchColumnFilter from '@/components/SearchColumnFilter.vue';

const { t, locale } = useI18n();
const repo = useFinanceRepository();

/* RTL */
const rtl = ref(locale.value === 'fa');
watch(locale, (n) => (rtl.value = n === 'fa'));

const dialog = ref(false);
const editId = ref(null);

const deleteDialog = ref(false);
const deleteTarget = ref(null);

const excelDownloadDialog = ref(false);
const excelDownloadProgress = ref(0);
const excelDownloading = ref(false);

function getRow(payload) {
  return payload?.raw ?? payload;
}

/* Filters */
const selectedYear = ref(null);
const selectedMonth = ref(null);
const searchField = ref(null);
const selectedColumnValue = ref(null);

const yearItems = ref([]);
const monthItems = ref([]);

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

async function fetchYearItemsFromDb() {
  try {
    const res = await repo.FetchFinanceDateOptions({ date_field: 'date' });
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
    const res = await repo.FetchFinanceDateOptions({
      date_field: 'date',
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

watch(selectedYear, async (newYear) => {
  selectedMonth.value = null;
  await fetchMonthItemsFromDb(newYear);
});

/* Headers */
const headersFT = computed(() => [
  { title: t('finances.table.employeeCode'), key: 'employee_code', sortable: true },
  { title: t('finances.table.employeeName'), key: 'employee_name', sortable: true },
  { title: t('finances.table.amountNumber'), key: 'money_amount_number', sortable: true },
  { title: t('finances.table.amountWords'), key: 'money_amount_words', sortable: false },
  { title: t('finances.table.sourceNickName'), key: 'resource_nick_name', sortable: false },
  { title: t('finances.table.sourceCode'), key: 'source_code', sortable: false },
  { title: t('finances.table.paidDate'), key: 'date', sortable: true },
  { title: t('finances.table.employeePhone'), key: 'employee_phone', sortable: false },
  { title: t('finances.table.expenseReason'), key: 'expense_result', sortable: false },
  { title: t('finances.table.payer'), key: 'payer', sortable: true },
  { title: t('finances.table.account'), key: 'account_name', sortable: true },
  { title: t('finances.table.notes'), key: 'notes', sortable: true },
]);

async function fetchColumnValues(field, q = '') {
  try {
    const normalizedField =
      typeof field === 'string' ? field : field?.key ?? field?.value ?? null;

    if (!normalizedField) return [];

    const res = await repo.FetchFinanceFilterOptions?.({
      field: normalizedField,
      q,
    });

    return res?.data?.data?.options || [];
  } catch (e) {
    console.error('fetchColumnValues error', e);
    return [];
  }
}

function toDate10(v) {
  return (v ?? '').toString().slice(0, 10);
}

function prettyExpenseInfo(it) {
  const infoId = it?.information_id ?? it?.information?.id ?? null;
  const eventId = it?.event_id ?? it?.event?.id ?? null;

  if (infoId) return { kind: 'information', label: `اطلاع ${infoId}` };
  if (eventId) return { kind: 'event', label: `واقعه ${eventId}` };

  const text = String(it?.expense_result ?? '').trim();
  return { kind: 'manual', label: text || '—' };
}

const rowsFT = computed(() => {
  const startIndex = (repo.financesPage - 1) * repo.financesPerPage;

  return (repo.finances || []).map((it, i) => {
    const amount = it?.money_amount_number ?? it?.amount ?? '';
    const exp = prettyExpenseInfo(it);

    return {
      ...it,
      index: startIndex + i + 1,
      employee_name: it?.office_employee?.name ?? '',
      employee_code: it?.office_employee?.code_number ?? '',
      resource_nick_name: it?.resource?.nick_name ?? '',
      payer: it?.payer ?? '',
      money_amount_number: amount,
      money_amount_words: it?.money_amount_words ?? '',
      source_code: it?.resource?.code_number ?? it?.source_code ?? '—',
      source_nick_name: it?.resource?.nick_name ?? it?.source_nick_name ?? '—',
      employee_phone: it?.office_employee?.phone_number ?? '—',
      expense_kind: exp.kind,
      expense_label: exp.label,
      expense_result: exp.label,
      notes: it?.notes ?? '',
      date: toDate10(it?.date),
      signature: it?.signature ?? '—',
      account_name: it?.account?.name ?? '—',
      account: it?.account?.name ?? '—',
    };
  });
});

const filters = [];
const topGroups = [];

/* Summary cards */
const summary = computed(() => repo.financesSummary || {});

function fmtMoney(v) {
  const n = Number(v ?? 0);
  if (Number.isNaN(n)) return String(v ?? '');
  if (Math.floor(n) === n) return String(n);
  return String(n).replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
}

const hasAnyFilter = computed(() => {
  return !!(
    repo.financesSearch ||
    normalizedSearchField.value ||
    normalizedSearchValue.value ||
    selectedYear.value ||
    selectedMonth.value ||
    repo.financesAccountId ||
    repo.financesOfficeEmployeeId ||
    summary.value?.has_filters
  );
});

const outcomeCardValue = computed(() =>
  hasAnyFilter.value ? summary.value?.filtered_outcome : summary.value?.total_outcome_all
);

const incomeCardValue = computed(() => {
  if (!hasAnyFilter.value) return summary.value?.total_income_all;
  return summary.value?.filtered_income ?? summary.value?.total_income_all;
});

const balanceCardValue = computed(() => {
  if (!hasAnyFilter.value) return summary.value?.total_balance_all_accounts;
  return summary.value?.filtered_balance ?? summary.value?.total_balance_all_accounts;
});

/* Fetch */
function fetchAll() {
  repo.FetchFinancesData({
    page: repo.financesPage,
    perPage: repo.financesPerPage,
    search: repo.financesSearch,
    search_field: normalizedSearchField.value,
    search_value: normalizedSearchValue.value,
    account_id: repo.financesAccountId,
    office_employee_id: repo.financesOfficeEmployeeId,
    year: selectedYear.value,
    month: selectedMonth.value,
    date_field: 'date',
  });
}

usePageRefresh('/app/finincalindex', fetchAll);

function applyFilters() {
  repo.financesPage = 1;
  fetchAll();
}

function clearSearch() {
  repo.financesSearch = '';
  repo.financesPage = 1;
  fetchAll();
}

function clearSelectedValue() {
  selectedColumnValue.value = null;
  repo.financesPage = 1;
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

async function ensureAccountsLoaded() {
  if (Array.isArray(repo.accounts) && repo.accounts.length) return;
  await repo.FetchAccountsData({ page: 1, perPage: 2000, search: '' });
}

function openCreate() {
  editId.value = null;
  dialog.value = true;
}

function startEdit(payload) {
  const row = getRow(payload);
  editId.value = row?.id ?? null;
  if (!editId.value) return;
  dialog.value = true;
}

function refreshTable() {
  fetchAll();
}

function onSaved() {
  applyFilters();
}

/* Delete */
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
    await repo.DeleteFinance(row.id);
    applyFilters();
  } finally {
    deleteDialog.value = false;
    deleteTarget.value = null;
  }
}

function resetFilters() {
  selectedYear.value = null;
  selectedMonth.value = null;
  searchField.value = null;
  selectedColumnValue.value = null;

  if (typeof repo.ResetFinancesFilters === 'function') {
    repo.ResetFinancesFilters();
  } else {
    repo.financesSearch = '';
    repo.financesSearchField = null;
    repo.financesAccountId = null;
    repo.financesOfficeEmployeeId = null;
    repo.financesPage = 1;
  }

  applyFilters();
}

function onPage(p) {
  repo.financesPage = p;
  fetchAll();
}

function onPerPage(pp) {
  repo.financesPerPage = pp;
  repo.financesPage = 1;
  fetchAll();
}

async function downloadExcel() {
  try {
    excelDownloading.value = true;
    excelDownloadDialog.value = true;
    excelDownloadProgress.value = 0;

    await repo.DownloadFinancesExcel({
      search: repo.financesSearch || '',
      search_field: normalizedSearchField.value,
      search_value: normalizedSearchValue.value,
      account_id: repo.financesAccountId,
      office_employee_id: repo.financesOfficeEmployeeId,
      year: selectedYear.value,
      month: selectedMonth.value,
      date_field: 'date',
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

/* Export PDF/Word */
function buildExportHtml(rows, title) {

  const thead = `
    <tr>
      <th rowspan="2">${t('finances.export.no')}</th>
      <th rowspan="2">${t('finances.export.employeeName')}</th>
      <th colspan="2">${t('finances.export.amount')}</th>
      <th colspan="2">${t('finances.export.source')}</th>
      <th rowspan="2">${t('finances.export.paidDate')}</th>
      <th rowspan="2">${t('finances.export.employeePhone')}</th>
      <th rowspan="2">${t('finances.export.expenseReason')}</th>
      <th rowspan="2">${t('finances.export.signature')}</th>
      <th rowspan="2">${t('finances.export.account')}</th>
      <th rowspan="2">${t('finances.export.notes')}</th>
    </tr>
    <tr>
      <th>${t('finances.export.amountNumber')}</th>
      <th>${t('finances.export.amountWords')}</th>
      <th>${t('finances.export.sourceNickName')}</th>
      <th>${t('finances.export.sourceCode')}</th>
    </tr>
  `;

  const esc = (v) =>
    String(v ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;');

  const tbody = (rows || [])
    .map(
      (r) => `
      <tr>
        <td>${esc(r.index)}</td>
        <td>${esc(r.employee_name)}</td>
        <td>${esc(r.money_amount_number)}</td>
        <td>${esc(r.money_amount_words)}</td>
        <td>${esc(r.source_nick_name)}</td>
        <td>${esc(r.source_code)}</td>
        <td>${esc(r.date)}</td>
        <td>${esc(r.employee_phone)}</td>
        <td>${esc(r.expense_result)}</td>
        <td>${esc(r.signature)}</td>
        <td>${esc(r.account)}</td>
        <td>${esc(r.notes)}</td>
      </tr>
    `
    )
    .join('');

  return `
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8" />
        <title>${esc(title)}</title>
        <style>
          body { font-family: Arial, Tahoma, sans-serif; direction: rtl; padding: 18px; }
          .top {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 10px;
}

.title {
  font-weight: 700;
  font-size: 18px;
  text-align: center;
}
          table { width: 100%; border-collapse: collapse; margin-top: 10px; }
          th, td { border: 1px solid #111; padding: 6px 8px; font-size: 12px; vertical-align: middle; }
          th { font-weight: 700; text-align: center; }
          td { text-align: right; vertical-align: top; }
          .signs { display:flex; justify-content: space-between; margin-top: 18px; font-size: 12px; }
          .signs > div { width: 24%; text-align: center; }
        </style>
      </head>
      <body>
       <div class="top">
  <div class="title">${esc(title)}</div>
</div>
        <table>
          <thead>${thead}</thead>
          <tbody>${
            tbody ||
            "<tr><td colspan='12' style='text-align:center'>" +
              esc(t('common.noData')) +
              '</td></tr>'
          }</tbody>
        </table>
        <div class="signs">
          <div>${t('finances.export.signTrusted')}</div>
          <div>${t('finances.export.signManager')}</div>
          <div>${t('finances.export.signChief')}</div>
          <div>${t('finances.export.signAudit')}</div>
        </div>
      </body>
    </html>
  `;
}

function exportPdfAll() {
  const html = buildExportHtml(rowsFT.value || [], t('finances.export.title'));
  const w = window.open('', '_blank');
  if (!w) return;
  w.document.open();
  w.document.write(html);
  w.document.close();
  w.focus();
  w.print();
}

function exportWordAll() {
  const html = buildExportHtml(rowsFT.value || [], t('finances.export.title'));
  const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
  const url = URL.createObjectURL(blob);

  const a = document.createElement('a');
  a.href = url;
  a.download = `مصارف_${new Date().toISOString().slice(0, 10)}.doc`;
  document.body.appendChild(a);
  a.click();
  a.remove();

  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

/* Init */
onMounted(async () => {
  await ensureAccountsLoaded();
  await fetchYearItemsFromDb();
  await fetchMonthItemsFromDb(null);
  applyFilters();
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

.family {
  font-family: var(--app-font) !important;
}

.filter-input :deep(.v-field) {
  border-radius: 16px !important;
}

.filter-input :deep(.v-field__prepend-inner) {
  opacity: 0.85;
}

.search-field :deep(.v-field) {
  min-height: 48px;
}
</style>