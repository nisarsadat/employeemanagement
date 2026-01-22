<!-- src/pages/Incomes/IncomesIndex.vue -->
<template>
  <v-container fluid class="pt-6 page-bg" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- ✅ SAME SUMMARY CARDS (reuse finances summary) -->
    <v-row class="mb-4" dense>
      <v-col cols="12" md="4">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">موجود</div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(summary.total_balance_all_accounts) }}
                </div>
              </div>

              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-bank</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">عاید</div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(summary.total_income_all) }}
                </div>
              </div>

              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-cash-plus</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">مصرف</div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(summary.total_outcome_all) }}
                </div>
              </div>

              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-cash-minus</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-card rounded="xl" class="shadow-sm mb-4 filters-card">
      <v-card-text class="pa-4">
        <div class="d-flex align-center justify-space-between flex-wrap gap-2">
          <div class="d-flex gap-2 flex-wrap">
            <v-text-field
              v-model="repo.incomesSearch"
              label="جستجو"
              density="compact"
              variant="outlined"
              style="max-width: 320px"
              prepend-inner-icon="mdi-magnify"
              @keyup.enter="fetchAll"
              clearable
              @click:clear="clearSearch"
            />
          </div>
          <div class="d-flex gap-2 flex-wrap">
<v-select
  v-model="selectedYear"
  :items="yearItems"
  item-title="title"
  item-value="value"
  label="سال"
  density="compact"
  variant="outlined"
  clearable
/>


            <v-select
              v-model="selectedMonth"
              :items="monthOptions"
              label="ماه"
              density="compact"
              variant="outlined"
              prepend-inner-icon="mdi-calendar-month"
              clearable
              :disabled="!selectedYear"
              @update:modelValue="onYearMonthChanged"
            />
          </div>

          <div class="d-flex gap-2">
            <v-btn color="success" prepend-icon="mdi-plus" class="text-none" @click="openCreate">
              ثبت عاید
            </v-btn>
          </div>
        </div>
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
          :totalItems="repo.incomesTotalItems"
          :page="repo.incomesPage"
          :perPage="repo.incomesPerPage"
          @update:page="onPage"
          @update:perPage="onPerPage"
        >
          <template #actions="{ item }">
            <div class="d-flex align-center ga-2">
              <v-tooltip text="ویرایش عاید" location="top">
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

              <v-tooltip text="حذف عاید" location="top">
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

    <!-- Create/Edit Dialog -->
    <IncomeFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <!-- Delete confirm -->
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
  import FilterableTable from '@/components/FilterableTable.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import IncomeFormDialog from './IncomesFormDialog.vue';
  import { useFinanceRepository } from '@/stores/financeRepository';

  const rtl = ref(true);
  const repo = useFinanceRepository();

  const dialog = ref(false);
  const editId = ref(null);

  const deleteDialog = ref(false);
  const deleteTarget = ref(null);

  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  const accountOptions = computed(() => repo.accounts || []);

  const headersFT = [
    { title: 'حساب', key: 'account_name', sortable: true },
    { title: 'مبلغ', key: 'amount', sortable: true },
    { title: 'تاریخ', key: 'received_at', sortable: true },
    { title: 'یادداشت', key: 'note', sortable: false },
  ];

  const rowsFT = computed(() => {
    const startIndex = (repo.incomesPage - 1) * repo.incomesPerPage;

    return (repo.incomes || []).map((it, i) => ({
      ...it,
      index: startIndex + i + 1,
      account_name: it?.account?.name ?? it?.account_name ?? '',
      received_at: (it?.received_at ?? '').toString().slice(0, 10),
    }));
  });

  const filters = [];
  const topGroups = [];

  /**
   * ✅ IMPORTANT:
   * Your repository currently fills financesSummary ONLY when /finances is called.
   * So we call FetchFinancesData once (lightweight) to get the summary for cards.
   */
  const summary = computed(
    () =>
      repo.financesSummary || {
        total_balance_all_accounts: '0.00',
        total_income_all: '0.00',
        total_outcome_all: '0.00',
        filtered_outcome: '0.00',
      }
  );

  function fmtMoney(v) {
    const n = Number(v ?? 0);
    if (Number.isNaN(n)) return String(v ?? '');

    // if integer => show without .00
    if (Math.floor(n) === n) return String(n);

    // if has decimals => show as-is (trim trailing zeros)
    return String(n)
      .replace(/(\.\d*?)0+$/, '$1')
      .replace(/\.$/, '');
  }
  const selectedYear = ref(null); // ex: 2026
  const selectedMonth = ref(null); // ex: 1..12

  const yearOptions = computed(() => {
    const now = new Date().getFullYear();
    // last 6 years + current
    return Array.from({ length: 7 }, (_, i) => now - i);
  });

  const monthOptions = computed(() => [
    { title: '01', value: 1 },
    { title: '02', value: 2 },
    { title: '03', value: 3 },
    { title: '04', value: 4 },
    { title: '05', value: 5 },
    { title: '06', value: 6 },
    { title: '07', value: 7 },
    { title: '08', value: 8 },
    { title: '09', value: 9 },
    { title: '10', value: 10 },
    { title: '11', value: 11 },
    { title: '12', value: 12 },
  ]);

  function onYearMonthChanged() {
    // if year cleared -> clear month too
    if (!selectedYear.value) selectedMonth.value = null;

    // reset pagination when filters change (if page uses paging)
    if (typeof repo.accountsPage !== 'undefined') repo.accountsPage = 1;
    if (typeof repo.incomesPage !== 'undefined') repo.incomesPage = 1;
    if (typeof repo.financesPage !== 'undefined') repo.financesPage = 1;

    fetchAll();
  }

  function fetchAll() {
    repo.FetchIncomesData({
      page: repo.incomesPage,
      perPage: repo.incomesPerPage,
      search: repo.incomesSearch,
      account_id: repo.incomesAccountId,
      year: selectedYear.value,
      month: selectedMonth.value,
    });
  }

  function clearSearch() {
    repo.incomesSearch = '';
    fetchAll();
  }

  async function ensureAccountsLoaded() {
    if (Array.isArray(repo.accounts) && repo.accounts.length) return;
    await repo.FetchAccountsData({ page: 1, perPage: 2000, search: '' });
  }

  function resetFilters() {
    if (typeof repo.ResetIncomesFilters === 'function') repo.ResetIncomesFilters();
    else {
      repo.incomesSearch = '';
      repo.incomesAccountId = null;
      repo.incomesPage = 1;
    }
    fetchAll();
  }

  function onAccountFilterChanged() {
    repo.incomesPage = 1;
    fetchAll();
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

  function onSaved() {
    fetchAll();
    // refresh cards after save
    repo.FetchFinancesData({ page: 1, perPage: 1, search: '' });
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
      await repo.DeleteIncome(row.id);
      await fetchAll();
      // refresh cards after delete
      await repo.FetchFinancesData({ page: 1, perPage: 1, search: '' });
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  function onPage(p) {
    repo.incomesPage = p;
    fetchAll();
  }

  function onPerPage(pp) {
    repo.incomesPerPage = pp;
    repo.incomesPage = 1;
    fetchAll();
  }
import { useCalendar } from '../../../../composables/useCalendar';

const { yearItemsFromGregorianYears } = useCalendar();

const yearItems = computed(() =>
  yearItemsFromGregorianYears(repo.availableYears, 'fa') // 'fa' or locale.value
);
  onMounted(async () => {
    await ensureAccountsLoaded();

    // ✅ fetch incomes list
    fetchAll();

    // ✅ fetch summary cards (loads totals)
    await repo.FetchFinancesData({ page: 1, perPage: 1, search: '' });
  });
  onMounted(async () => {
    await repo.FetchYears('incomes');
    fetchAll();
  });
</script>

<style scoped>
  .page-bg {
    min-height: 100%;
  }

  .gap-2 {
    gap: 8px;
  }

  /* Summary cards */
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
  /* ✅ Finance/Income/Accounts summary cards font + look */
  .finance-cards {
    font-family: var(--app-font) !important;
  }

  .finance-card {
    font-family: var(--app-font) !important;
    border: 1px solid rgba(0, 0, 0, 0.06);
    overflow: hidden;
    position: relative;
  }

  .finance-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.07), rgba(156, 39, 176, 0.05));
    pointer-events: none;
  }

  .finance-card .finance-card__value {
    font-family: var(--app-font) !important;
    letter-spacing: 0.2px;
  }

  .finance-card .finance-card__icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(25, 118, 210, 0.08);
  }
  .family {
    font-family: var(--app-font) !important;
  }
</style>
