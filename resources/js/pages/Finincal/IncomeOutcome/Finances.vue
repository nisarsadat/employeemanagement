<!-- src/pages/Finances/FinancesIndex.vue -->
<template>
  <v-container fluid class="pt-6 page-bg" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- ✅ SUMMARY CARDS -->
    <v-row class="mb-4" dense>
      <!-- Balance -->
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

      <!-- Income -->
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

      <!-- Outcome -->
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
          <!-- Search -->
          <v-col cols="12" md="3">
            <v-text-field
              v-model="repo.financesSearch"
              :label="t('common.search')"
              density="comfortable"
              variant="outlined"
              rounded="lg"
              prepend-inner-icon="mdi-magnify"
              clearable
              hide-details="auto"
              class="filter-input"
              @keyup.enter="applyFilters"
              @click:clear="clearSearch"
            />
          </v-col>

          <!-- Year / Month (✅ same calendar-aware logic as Resources) -->
          <v-col cols="12" md="3">
            <div class="d-flex ga-2 flex-wrap">
              <v-select
                v-model="selectedYear"
                :items="yearItems"
                item-title="title"
                item-value="value"
                :label="t('common.year')"
                density="comfortable"
                variant="outlined"
                rounded="lg"
                clearable
                hide-details="auto"
                class="filter-input"
                prepend-inner-icon="mdi-calendar"
                @update:modelValue="onYearMonthChanged"
              />

              <v-select
                v-model="selectedMonth"
                :items="monthOptions"
                item-title="title"
                item-value="value"
                :label="t('common.month')"
                density="comfortable"
                variant="outlined"
                rounded="lg"
                clearable
                hide-details="auto"
                class="filter-input"
                prepend-inner-icon="mdi-calendar-month"
                :disabled="!selectedYear"
                @update:modelValue="onYearMonthChanged"
              />
            </div>
          </v-col>

          <!-- Actions -->
          <v-col cols="12" md="6" class="d-flex justify-end">
            <div class="d-flex ga-2 flex-wrap justify-end w-100">
              <v-btn
                variant="tonal"
                color="grey-darken-1"
                class="text-none"
                prepend-icon="mdi-filter-remove"
                @click="resetFilters"
              >
                {{ t('common.resetFilters') }}
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
                {{ monthOptions.find((m) => m.value === selectedMonth)?.title ?? selectedMonth }}
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
  </v-container>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import dayjs from 'dayjs';

import FilterableTable from '@/components/FilterableTable.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import GlobalPagination from '@/components/GlobalPagination.vue';
import FinanceFormDialog from './FinancesFormDialog.vue';
import { useFinanceRepository } from '@/stores/FinanceRepository';
import { useCalendar } from '../../../../composables/useCalendar';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  usePageRefresh('/app/finincalindex', fetchAll);
const { t, locale } = useI18n();
const repo = useFinanceRepository();

/* ===== RTL (same pattern as Resources) ===== */
const rtl = ref(locale.value === 'fa');
watch(locale, (n) => (rtl.value = n === 'fa'));

/* ===== Calendar (same logic as Resources) ===== */
const { calendar, formatYearDisplay } = useCalendar();

const dialog = ref(false);
const editId = ref(null);

const deleteDialog = ref(false);
const deleteTarget = ref(null);

function getRow(payload) {
  return payload?.raw ?? payload;
}

/* ===== Headers ===== */
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
  { title: t('finances.table.notes'), key: 'notes', sortable: true },
]);

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
    };
  });
});

const filters = [];
const topGroups = [];

/* ===== Summary cards ===== */
const summary = computed(() => repo.financesSummary || {});

function fmtMoney(v) {
  const n = Number(v ?? 0);
  if (Number.isNaN(n)) return String(v ?? '');
  if (Math.floor(n) === n) return String(n);
  return String(n).replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
}

const selectedYear = ref(null);  // sent to backend (gregorian)
const selectedMonth = ref(null); // sent to backend (gregorian 1..12)

/* ===== ✅ Year items from backend, but titles follow calendar mode ===== */
const yearItems = ref([]);
const yearNumbers = ref([]);

function rebuildYearItems() {
  if (!Array.isArray(yearNumbers.value) || yearNumbers.value.length === 0) return;
  yearItems.value = yearNumbers.value.map((y) => ({
    value: y,
    title: typeof formatYearDisplay === 'function' ? formatYearDisplay(y) : String(y),
  }));
}

async function fetchYearItemsFromRepo() {
  // you already call repo.FetchYears('finances'); we use repo.availableYears after that
  const years = Array.isArray(repo.availableYears) ? repo.availableYears : [];
  yearNumbers.value = years.map((y) => Number(y)).filter((n) => Number.isFinite(n));
  rebuildYearItems();
}

/* update year titles when calendar/locale changes (selected value stays same) */
watch([calendar, locale], () => rebuildYearItems());

/* ===== ✅ Month titles follow calendar mode, values stay gregorian 1..12 ===== */
const JALALI_MONTHS_FA = [
  'حمل','ثور','جوزا','سرطان','اسد','سنبله','میزان','عقرب','قوس','جدی','دلو','حوت',
];

const HIJRI_QAMARI_MONTHS_FA = [
  'محرم','صفر','ربیع‌الاول','ربیع‌الثانی','جمادی‌الاول','جمادی‌الثانی',
  'رجب','شعبان','رمضان','شوال','ذوالقعده','ذوالحجه',
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
      const fmt = new Intl.DateTimeFormat(locale.value, { calendar: 'persian', month: 'numeric' });
      const parts = fmt.formatToParts(dt);
      const m = Number(parts.find((p) => p.type === 'month')?.value);
      if (m >= 1 && m <= 12) return JALALI_MONTHS_FA[m - 1];
    } catch {}
    return JALALI_MONTHS_FA[(gMonth - 1) % 12];
  }

  if (calendar.value === 'hijri') {
    try {
      const dt = new Date(iso + 'T00:00:00');
      const fmt = new Intl.DateTimeFormat('ar', { calendar: 'islamic-umalqura', month: 'numeric' });
      const parts = fmt.formatToParts(dt);
      const m = Number(parts.find((p) => p.type === 'month')?.value);
      if (m >= 1 && m <= 12) return HIJRI_QAMARI_MONTHS_FA[m - 1];
    } catch {}
    return HIJRI_QAMARI_MONTHS_FA[(gMonth - 1) % 12];
  }

  return String(gMonth);
}

const monthOptions = computed(() => {
  const y = selectedYear.value || dayjs().year();
  return Array.from({ length: 12 }).map((_, i) => {
    const m = i + 1;
    return { value: m, title: monthTitleFromGregorianMonth(y, m) };
  });
});

/* ===== decide if we are in filtered mode (same logic as backend) ===== */
const hasAnyFilter = computed(() => {
  return !!(
    repo.financesSearch ||
    selectedYear.value ||
    selectedMonth.value ||
    repo.financesAccountId ||
    repo.financesOfficeEmployeeId ||
    summary.value?.has_filters
  );
});

/* ===== card values ===== */
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

/* ===== Fetch / filters ===== */
function fetchAll() {
  repo.FetchFinancesData({
    page: repo.financesPage,
    perPage: repo.financesPerPage,
    search: repo.financesSearch,
    account_id: repo.financesAccountId,
    office_employee_id: repo.financesOfficeEmployeeId,
    year: selectedYear.value,
    month: selectedMonth.value,
  });
}

function applyFilters() {
  repo.financesPage = 1;
  fetchAll();
}

function onYearMonthChanged() {
  if (!selectedYear.value) selectedMonth.value = null;
  applyFilters();
}

function clearSearch() {
  repo.financesSearch = '';
  applyFilters();
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

/* ===== Delete ===== */
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

  if (typeof repo.ResetFinancesFilters === 'function') {
    repo.ResetFinancesFilters();
  } else {
    repo.financesSearch = '';
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

/* ===== Export (unchanged) ===== */
function buildExportHtml(rows, title) {
  const year = new Date().getFullYear();

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
          .top { display:flex; justify-content: space-between; align-items:center; margin-bottom: 10px; }
          .title { font-weight: 700; font-size: 18px; text-align:center; flex:1; }
          .year { font-size: 13px; white-space: nowrap; }
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
          <div class="year">${t('finances.export.year')}: ${year}</div>
          <div class="title">${esc(title)}</div>
          <div style="width:120px"></div>
        </div>
        <table>
          <thead>${thead}</thead>
          <tbody>${
            tbody ||
            "<tr><td colspan='11' style='text-align:center'>" +
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

/* ===== Init ===== */
onMounted(async () => {
  await ensureAccountsLoaded();

  // keep your existing behavior
  await repo.FetchYears('finances');

  // ✅ now convert the available gregorian years to calendar-aware titles
  await fetchYearItemsFromRepo();

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
</style>