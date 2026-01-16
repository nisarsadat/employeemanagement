<!-- src/pages/Accounts/AccountsIndex.vue -->
<template>
  <v-container fluid class="pt-6 page-bg" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- ✅ SUMMARY CARDS (same beautiful cards) -->
    <v-row class="mb-4" dense :dir="rtl ? 'rtl' : 'ltr'">
      <v-col cols="12" md="3">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">مجموع بیلانس همه حساب‌ها</div>
                <div class="text-h6 font-weight-bold stat-value family">
                  {{ fmtMoney(summary.total_balance_all_accounts) }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1 family">جمع کل حساب‌ها</div>
              </div>
              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-bank</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family" >مجموع عواید </div>
                <div class="text-h6 font-weight-bold stat-value family">
                  {{ fmtMoney(summary.total_income_all) }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1 family">جمع کل درآمد</div>
              </div>
              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-cash-plus</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">مجموع مصارف</div>
                <div class="text-h6 font-weight-bold stat-value family">
                  {{ fmtMoney(summary.total_outcome_all) }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1 family">جمع کل مصرف</div>
              </div>
              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-cash-minus</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">مصارف همین لیست</div>
                <div class="text-h6 font-weight-bold stat-value family">
                  {{ fmtMoney(summary.filtered_outcome) }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1 family">همین صفحه/جستجو</div>
              </div>
              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-filter</v-icon>
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
              v-model="repo.accountsSearch"
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

          <div class="d-flex gap-2">
            <v-btn color="primary" prepend-icon="mdi-plus" class="text-none" @click="openCreate">
              ثبت حساب
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
          :totalItems="repo.accountsTotalItems"
          :page="repo.accountsPage"
          :perPage="repo.accountsPerPage"
          @update:page="onPage"
          @update:perPage="onPerPage"
        >
          <template #actions="{ item }">
            <div class="d-flex align-center ga-2">
              <v-tooltip text="تغییر جزیات" location="top">
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

              <v-tooltip text="حذف" location="top">
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
    <AccountFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

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
import { computed, ref, onMounted } from "vue";
import FilterableTable from "@/components/FilterableTable.vue";
import DeleteConfirmDialog from "@/components/DeleteConfirmDialog.vue";
import AccountFormDialog from "./AccountsFormDialog.vue";
import { useFinanceRepository } from "@/stores/financeRepository";

const rtl = ref(true);
const repo = useFinanceRepository();

const dialog = ref(false);
const editId = ref(null);

const deleteDialog = ref(false);
const deleteTarget = ref(null);

function getRow(payload) {
  return payload?.raw ?? payload;
}

const headersFT = [
  { title: "نام حساب", key: "name", sortable: true },
  { title: "موجودی", key: "price", sortable: true },
  { title: "جزئیات", key: "details", sortable: false },
];

const rowsFT = computed(() => {
  const startIndex = (repo.accountsPage - 1) * repo.accountsPerPage;
  return (repo.accounts || []).map((it, i) => ({
    ...it,
    index: startIndex + i + 1,
  }));
});

const filters = [];
const topGroups = [];

/**
 * ✅ Summary cards source:
 * repository fills financesSummary only when /finances is called.
 * So we fetch /finances once for cards.
 */
const summary = computed(() => repo.financesSummary || {
  total_balance_all_accounts: "0.00",
  total_income_all: "0.00",
  total_outcome_all: "0.00",
  filtered_outcome: "0.00",
});

function fmtMoney(v) {
  const n = Number(v ?? 0);
  if (Number.isNaN(n)) return String(v ?? "");

  // if integer => show without .00
  if (Math.floor(n) === n) return String(n);

  // if has decimals => show as-is (trim trailing zeros)
  return String(n).replace(/(\.\d*?)0+$/, "$1").replace(/\.$/, "");
}


function fetchAll() {
  repo.FetchAccountsData({
    page: repo.accountsPage,
    perPage: repo.accountsPerPage,
    search: repo.accountsSearch,
  });
}

function clearSearch() {
  repo.accountsSearch = "";
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
  // refresh cards
  repo.FetchFinancesData({ page: 1, perPage: 1, search: "" });
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
    await fetchAll();
    // refresh cards
    await repo.FetchFinancesData({ page: 1, perPage: 1, search: "" });
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
  fetchAll();
  // ✅ load cards summary
  await repo.FetchFinancesData({ page: 1, perPage: 1, search: "" });
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
  content: "";
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
  background: linear-gradient(
    135deg,
    rgba(25, 118, 210, 0.07),
    rgba(156, 39, 176, 0.05)
  );
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
  font-family: var(--app-font) !important;

}
.family
{
  font-family: var(--app-font) !important;

}
</style>
