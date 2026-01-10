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
              clearable
              @click:clear="clearSearch"
            />
          </div>

          <div class="d-flex gap-2">
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">ثبت حساب</v-btn>
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
        >
          <!-- ✅ ACTIONS inside FilterableTable slot -->
          <template #actions="{ item }">
            <div class="d-flex align-center ga-2">
              <!-- ویرایش -->
              <v-tooltip text="تغییر جزیات" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-icon
                    size="small"
                    v-bind="tooltipProps"
                    color="amber-darken-2"
                    @click.stop="startEdit(item)"
                  >
                    mdi-pencil
                  </v-icon>
                </template>
              </v-tooltip>

              <!-- حذف -->
              <v-tooltip text="حذف" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-icon
                    size="small"
                    v-bind="tooltipProps"
                    color="red-darken-2"
                    @click.stop="openDeleteDialog(item)"
                  >
                    mdi-delete
                  </v-icon>
                </template>
              </v-tooltip>
            </div>
          </template>
        </FilterableTable>
      </v-card-text>
    </v-card>

    <!-- Create/Edit Dialog -->
    <AccountFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <!-- ✅ Delete confirm (Finances/Informations style) -->
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

// RTL (replace with your i18n)
const rtl = ref(true);

const repo = useFinanceRepository();

const dialog = ref(false);
const editId = ref(null);

// ✅ delete (Finances/Informations style)
const deleteDialog = ref(false);
const deleteTarget = ref(null);

/* helper to unwrap row/raw like other pages */
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

function clearSearch() {
  repo.accountsSearch = "";
  fetchAll();
}

function resetFilters() {
  if (typeof repo.ResetAccountsFilters === "function") repo.ResetAccountsFilters();
  else {
    repo.accountsSearch = "";
    repo.accountsPage = 1;
  }
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
}

/* ✅ DELETE like Finances page */
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

onMounted(() => {
  fetchAll();
});
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}

:deep(.v-btn--icon) {
  transition: all 0.2s ease;
}
:deep(.v-btn--icon:hover) {
  transform: scale(1.1);
}
</style>
