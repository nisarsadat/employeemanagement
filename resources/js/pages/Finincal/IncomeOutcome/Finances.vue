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

          <!-- Year / Month -->
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
import { computed, ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import FilterableTable from '@/components/FilterableTable.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import FinanceFormDialog from './FinancesFormDialog.vue';
import { useFinanceRepository } from '@/stores/financeRepository';
import { useCalendar } from '../../../../composables/useCalendar';

const { t } = useI18n();

const rtl = ref(true);
const repo = useFinanceRepository();

const dialog = ref(false);
const editId = ref(null);

const deleteDialog = ref(false);
const deleteTarget = ref(null);

function getRow(payload) {
  return payload?.raw ?? payload;
}

const headersFT = computed(() => [
  { title: t('finances.table.employeeCode'), key: 'employee_code', sortable: true },
  { title: t('finances.table.employeeName'), key: 'employee_name', sortable: true },
  { title: t('finances.table.amountNumber'), key: 'money_amount_number', sortable: true },
  { title: t('finances.table.amountWords'), key: 'money_amount_words', sortable: false },
  { title: t('finances.table.sourceCode'), key: 'source_code', sortable: false, width: 160 },
  { title: t('finances.table.sourceNickName'), key: 'resource.name', sortable: false, width: 160 },
  { title: t('finances.table.paidDate'), key: 'date', sortable: true },
  { title: t('finances.table.employeePhone'), key: 'employee_phone', sortable: false },
  { title: t('finances.table.expenseReason'), key: 'expense_result', sortable: false, width: 200 },
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
  return (repo.finances || []).map((it, i) => {
    const amount = it?.money_amount_number ?? it?.amount ?? '';
    const exp = prettyExpenseInfo(it);

    return {
      ...it,
      index: i + 1,

      employee_name: it?.office_employee?.name ?? '',
      employee_code: it?.office_employee?.code_number ?? '',
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

const summary = computed(() => repo.financesSummary || {});

function fmtMoney(v) {
  const n = Number(v ?? 0);
  if (Number.isNaN(n)) return String(v ?? '');
  if (Math.floor(n) === n) return String(n);
  return String(n).replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
}

const selectedYear = ref(null);
const selectedMonth = ref(null);

const monthOptions = computed(() => [
  { title: '1 - حمل', value: 1 },
  { title: '2 - ثور', value: 2 },
  { title: '3 - جوزا', value: 3 },
  { title: '4 - سرطان', value: 4 },
  { title: '5 - اسد', value: 5 },
  { title: '6 - سنبله', value: 6 },   // ✅ around Sep
  { title: '7 - میزان', value: 7 },
  { title: '8 - عقرب', value: 8 },
  { title: '9 - قوس', value: 9 },
  { title: '10 - جدی', value: 10 },
  { title: '11 - دلو', value: 11 },
  { title: '12 - حوت', value: 12 },
]);

const { yearItemsFromGregorianYears } = useCalendar();
const yearItems = computed(() => yearItemsFromGregorianYears(repo.availableYears, 'fa'));

// ✅ decide if we are in filtered mode (same logic as backend)
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

// ✅ card values
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

function fetchAll() {
  repo.FetchFinancesData({
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

// export html (same as your code)
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

onMounted(async () => {
  await ensureAccountsLoaded();
  await repo.FetchYears('finances');
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
