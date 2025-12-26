<template>
  <v-container fluid class="pt-6" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- <AppBar title="حساب‌ها" subtitle="ثبت و مدیریت حساب‌ها" /> -->

    <!-- Filters -->
    <v-card rounded="xl" class="shadow-sm mb-4">
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
            />
            <v-btn variant="tonal" @click="resetFilters">ریست</v-btn>
          </div>

          <div class="d-flex gap-2">
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">
              ثبت حساب
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
          :totalItems="repo.accountsTotalItems"
          :page="repo.accountsPage"
          :perPage="repo.accountsPerPage"
          @update:page="onPage"
          @update:perPage="onPerPage"
          @edit="startEdit"
          @delete="askDelete"
        />
      </v-card-text>
    </v-card>

    <!-- Create/Edit Dialog -->
    <AccountFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <!-- Delete confirm -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :title="'حذف حساب'"
      :text="'آیا مطمئن هستید که این حساب حذف شود؟'"
      @confirm="doDelete"
    />
  </v-container>
</template>

<script setup>
import { computed, ref } from "vue";
import AppBar from "@/components/AppBar.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import DeleteConfirmDialog from "@/components/DeleteConfirmDialog.vue";
import AccountFormDialog from "./AccountsFormDialog.vue";
import { useFinanceRepository } from "@/stores/financeRepository";

// RTL (replace with your i18n)
const rtl = ref(true);

const repo = useFinanceRepository();

const dialog = ref(false);
const editId = ref(null);

const deleteDialog = ref(false);
const deleteId = ref(null);

const headersFT = [
  { title: "#", key: "index", sortable: false, width: 70 },
  { title: "نام حساب", key: "name", sortable: true },
  { title: "موجودی", key: "price", sortable: true },
  { title: "جزئیات", key: "details", sortable: false },
  { title: "عملیات", key: "actions", sortable: false, width: 180 },
];

const rowsFT = computed(() => {
  const startIndex = (repo.accountsPage - 1) * repo.accountsPerPage;
  return (repo.accounts || []).map((it, i) => ({
    ...it,
    index: startIndex + i + 1,
  }));
});

// keep empty if not used
const filters = [];
const topGroups = [];

function fetchAll() {
  repo.FetchAccountsData({
    page: repo.accountsPage,
    perPage: repo.accountsPerPage,
    search: repo.accountsSearch,
  });
}

function resetFilters() {
  repo.ResetAccountsFilters();
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
  await repo.DeleteAccount(deleteId.value);
  deleteDialog.value = false;
  deleteId.value = null;
  fetchAll();
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

// initial load
fetchAll();
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}
</style>
