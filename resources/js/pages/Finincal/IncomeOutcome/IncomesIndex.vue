<template>
  <v-container fluid class="pt-6" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- <AppBar title="عایدات" subtitle="ثبت و مدیریت عایدات - افزایش موجودی حساب" /> -->

    <!-- Filters -->
    <v-card rounded="xl" class="shadow-sm mb-4">
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
            />

            <v-autocomplete
              v-model="repo.incomesAccountId"
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
            <v-btn color="success" prepend-icon="mdi-plus" @click="openCreate">
              ثبت عاید
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
          :totalItems="repo.incomesTotalItems"
          :page="repo.incomesPage"
          :perPage="repo.incomesPerPage"
          @update:page="onPage"
          @update:perPage="onPerPage"
          @edit="startEdit"
          @delete="askDelete"
        />
      </v-card-text>
    </v-card>

    <!-- Create/Edit Dialog -->
    <IncomeFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <!-- Delete confirm -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :title="'حذف عاید'"
      :text="'آیا مطمئن هستید که این عاید حذف شود؟'"
      @confirm="doDelete"
    />
  </v-container>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import AppBar from "@/components/AppBar.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import DeleteConfirmDialog from "@/components/DeleteConfirmDialog.vue";
import IncomeFormDialog from "./IncomesFormDialog.vue";
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
  { title: "مبلغ", key: "amount", sortable: true },
  { title: "تاریخ", key: "received_at", sortable: true },
  { title: "یادداشت", key: "note", sortable: false },
  { title: "عملیات", key: "actions", sortable: false, width: 180 },
];

const rowsFT = computed(() => {
  const startIndex = (repo.incomesPage - 1) * repo.incomesPerPage;

  return (repo.incomes || []).map((it, i) => ({
    ...it,
    index: startIndex + i + 1,
    // if backend returns account relation, prefer it, else fallback
    account_name: it?.account?.name ?? it?.account_name ?? "",
    received_at: (it?.received_at ?? "").toString().slice(0, 10),
  }));
});

// keep empty if you don't use them
const filters = [];
const topGroups = [];

function fetchAll() {
  repo.FetchIncomesData({
    page: repo.incomesPage,
    perPage: repo.incomesPerPage,
    search: repo.incomesSearch,
    account_id: repo.incomesAccountId,
  });
}

async function ensureAccountsLoaded() {
  if (Array.isArray(repo.accounts) && repo.accounts.length) return;
  await repo.FetchAccountsData({ page: 1, perPage: 2000, search: "" });
}

function resetFilters() {
  repo.ResetIncomesFilters();
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
  await repo.DeleteIncome(deleteId.value);
  deleteDialog.value = false;
  deleteId.value = null;
  fetchAll();
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
