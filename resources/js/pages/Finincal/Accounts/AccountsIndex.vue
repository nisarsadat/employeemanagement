<!-- src/pages/Accounts/AccountsIndex.vue -->
<template>
  <v-container fluid class="pt-6 page-bg" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- ✅ SUMMARY CARDS -->
    <v-row class="mb-4" dense>
      <v-col cols="12" md="4">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">
                  {{ t('accounts.summary.income') }}
                </div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(summary.total_income_all) }}
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
                  {{ fmtMoney(summary.total_outcome_all) }}
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
                <div class="text-caption text-medium-emphasis family">
                  {{ t('accounts.summary.balance') }}
                </div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(summary.total_balance_all_accounts) }}
                </div>
              </div>
              <div class="stat-icon-wrap"><v-icon size="26">mdi-bank</v-icon></div>
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
          <v-col cols="12" md="5" lg="5">
            <v-text-field
              v-model="repo.accountsSearch"
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
          <v-col cols="12" md="4" lg="4">
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
          <v-col cols="12" md="3" lg="3" class="d-flex justify-end">
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

              <v-btn color="primary" class="text-none" prepend-icon="mdi-plus" @click="openCreate">
                {{ t('accounts.actions.create') }}
              </v-btn>
            </div>
          </v-col>

          <!-- Active chips -->
          <v-col cols="12" class="pt-2">
            <div class="d-flex ga-2 flex-wrap">
              <v-chip
                v-if="repo.accountsSearch"
                size="small"
                variant="tonal"
                prepend-icon="mdi-magnify"
                closable
                @click:close="clearSearch"
              >
                {{ repo.accountsSearch }}
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
                {{ String(selectedMonth).padStart(2, '0') }}
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
          :totalItems="repo.accountsTotalItems"
          :page="repo.accountsPage"
          :perPage="repo.accountsPerPage"
          @update:page="onPage"
          @update:perPage="onPerPage"
        >
          <template #actions="{ item }">
            <div class="d-flex align-center ga-2">
              <v-tooltip :text="t('accounts.tooltips.edit')" location="top">
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

              <v-tooltip :text="t('common.delete')" location="top">
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

    <AccountFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

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
  import AccountFormDialog from './AccountsFormDialog.vue';
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
    { title: t('accounts.table.accountName'), key: 'name', sortable: true },
    { title: t('accounts.table.balance'), key: 'price', sortable: true },
    { title: t('accounts.table.details'), key: 'details', sortable: false },
  ]);

  const rowsFT = computed(() => {
    const startIndex = (repo.accountsPage - 1) * repo.accountsPerPage;
    return (repo.accounts || []).map((it, i) => ({
      ...it,
      index: startIndex + i + 1,
    }));
  });

  const filters = [];
  const topGroups = [];

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
    if (Math.floor(n) === n) return String(n);
    return String(n)
      .replace(/(\.\d*?)0+$/, '$1')
      .replace(/\.$/, '');
  }

  const selectedYear = ref(null);
  const selectedMonth = ref(null);

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

  const { yearItemsFromGregorianYears } = useCalendar();
  const yearItems = computed(() => yearItemsFromGregorianYears(repo.availableYears, 'fa'));

  function fetchAll() {
    repo.FetchAccountsData({
      page: repo.accountsPage,
      perPage: repo.accountsPerPage,
      search: repo.accountsSearch,
      year: selectedYear.value,
      month: selectedMonth.value,
    });
  }

  function refreshCardsWithFilters() {
    repo.FetchFinancesData({
      year: selectedYear.value,
      month: selectedMonth.value,
      search: repo.accountsSearch || undefined,
      account_id: undefined,
      office_employee_id: undefined,
    });
  }

  function applyFilters() {
    repo.accountsPage = 1;
    fetchAll();
    refreshCardsWithFilters();
  }

  function onYearMonthChanged() {
    if (!selectedYear.value) selectedMonth.value = null;
    applyFilters();
  }

  function clearSearch() {
    repo.accountsSearch = '';
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

  function resetFilters() {
    selectedYear.value = null;
    selectedMonth.value = null;

    if (typeof repo.ResetAccountsFilters === 'function') {
      repo.ResetAccountsFilters();
    } else {
      repo.accountsSearch = '';
      repo.accountsPage = 1;
    }

    applyFilters();
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
      await repo.DeleteAccount(row.id);
      applyFilters();
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  function onPage(p) {
    repo.accountsPage = p;
    fetchAll();
  }

  function onPerPage(pp) {
    repo.accountsPerPage = pp;
    repo.accountsPage = 1;
    fetchAll();
  }

  onMounted(async () => {
    await repo.FetchYears('accounts');
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
