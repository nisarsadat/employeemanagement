<template>
  <v-container fluid class="pt-6" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- <AppBar title="مصارف" subtitle="ثبت و مدیریت مصارف (Outcome/Expense)" /> -->

    <!-- Filters -->
    <v-card rounded="xl" class="shadow-sm mb-4">
      <v-card-text class="pa-4">
        <div class="d-flex align-center justify-space-between flex-wrap gap-2">
          <div class="d-flex gap-2 flex-wrap">
            <v-text-field
              v-model="repo.financesSearch"
              label="جستجو"
              density="compact"
              variant="outlined"
              style="max-width: 320px"
              prepend-inner-icon="mdi-magnify"
              @keyup.enter="fetchAll"
            />

            <v-autocomplete
              v-model="repo.financesAccountId"
              :items="accountOptions"
              item-title="name"
              item-value="id"
              label="فلتر حساب"
              density="compact"
              variant="outlined"
              style="min-width: 240px"
              prepend-inner-icon="mdi-bank"
              clearable
              @update:modelValue="onAccountFilterChanged"
            />

            <v-btn variant="tonal" @click="resetFilters">ریست</v-btn>
          </div>

          <div class="d-flex gap-2">
            <v-btn color="error" prepend-icon="mdi-plus" @click="openCreate">
              ثبت مصرف
            </v-btn>
          </div>
        </div>
      </v-card-text>
    </v-card>

    <!-- Table -->
    <v-card rounded="xl" class="shadow-sm">
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
          @edit="startEdit"
          @delete="askDelete"
        />
      </v-card-text>
    </v-card>

    <!-- Create/Edit Dialog -->
    <FinanceFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <!-- Delete confirm -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :title="'حذف مصرف'"
      :text="'آیا مطمئن هستید که این مصرف حذف شود؟'"
      @confirm="doDelete"
    />
  </v-container>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import AppBar from "@/components/AppBar.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import DeleteConfirmDialog from "@/components/DeleteConfirmDialog.vue";
import FinanceFormDialog from "./FinancesFormDialog.vue";
import { useFinanceRepository } from "@/stores/financeRepository";

// RTL (replace with i18n)
const rtl = ref(true);

const repo = useFinanceRepository();

const dialog = ref(false);
const editId = ref(null);

const deleteDialog = ref(false);
const deleteId = ref(null);

const accountOptions = computed(() => repo.accounts || []);

const headersFT = [
  { title: "#", key: "index", sortable: false, width: 70 },
  { title: "حساب", key: "account_name", sortable: true },
  { title: "مبلغ", key: "money_amount_number", sortable: true },
  { title: "تاریخ", key: "date", sortable: true },
  { title: "پرداخت کننده", key: "payer", sortable: false },
  { title: "دریافت کننده", key: "receiver", sortable: false },
  { title: "نتیجه", key: "expense_result", sortable: false },
  { title: "یادداشت", key: "notes", sortable: false },
  { title: "عملیات", key: "actions", sortable: false, width: 180 },
];

const rowsFT = computed(() => {
  const startIndex = (repo.financesPage - 1) * repo.financesPerPage;

  return (repo.finances || []).map((it, i) => ({
    ...it,
    index: startIndex + i + 1,
    account_name: it?.account?.name ?? it?.account_name ?? "",
    date: (it?.date ?? "").toString().slice(0, 10),
  }));
});

// keep empty if you don't use them
const filters = [];
const topGroups = [];

function fetchAll() {
  repo.FetchFinancesData({
    page: repo.financesPage,
    perPage: repo.financesPerPage,
    search: repo.financesSearch,
    account_id: repo.financesAccountId,
    office_employee_id: repo.financesOfficeEmployeeId, // optional
  });
}

async function ensureAccountsLoaded() {
  if (Array.isArray(repo.accounts) && repo.accounts.length) return;
  await repo.FetchAccountsData({ page: 1, perPage: 2000, search: "" });
}

function resetFilters() {
  repo.ResetFinancesFilters();
  fetchAll();
}

function onAccountFilterChanged() {
  repo.financesPage = 1;
  fetchAll();
}

function openCreate() {
  editId.value = null;
  dialog.value = true;
}

function startEdit(row) {
  editId.value = row?.id;
  dialog.value = true;
}

function onSaved() {
  fetchAll();
}

function askDelete(row) {
  deleteId.value = row?.id;
  deleteDialog.value = true;
}

async function doDelete() {
  if (!deleteId.value) return;
  await repo.DeleteFinance(deleteId.value);
  deleteDialog.value = false;
  deleteId.value = null;
  fetchAll();
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

onMounted(async () => {
  await ensureAccountsLoaded();
  fetchAll();
});
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}
</style>
