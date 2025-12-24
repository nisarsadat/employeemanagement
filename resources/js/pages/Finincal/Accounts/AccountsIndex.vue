<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">

    <!-- Top Bar (like Oil) -->
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="4">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو حساب‌ها')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          @keyup.enter="fetchAll()"
          @click:clear="() => { repo.Search=''; fetchAll(); }"
        />
      </v-col>

      <v-col cols="12" md="8" class="text-md-right">
        <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">
          {{ t('حساب جدید') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- List (FilterableTable) -->
    <FilterableTable
      :title="t('لیست حساب‌ها')"
      :subtitle="t('جستجو و مشاهده همه حساب‌ها')"
      :headers="headersFT"
      :rows="rowsFT"
      :filters="{ search: repo.Search }"
      :topGroups="[]"
      :loading="repo.loading"
      @edit="openEdit"
      @peek="startPeek"
      @refresh="fetchAll"
      @delete="askDelete"
    />

    <!-- Pagination bottom (like Oil) -->
    <v-card elevation="2" class="mt-3">
      <div class="px-4 py-3 d-flex align-center justify-space-between">
        <div class="text-caption">
          {{ t('ټول') }}: <strong>{{ repo.totalItems }}</strong>
        </div>

        <v-pagination
          v-model="repo.page"
          :length="pageCount"
          total-visible="7"
          @update:modelValue="fetchAll"
        />
      </div>
    </v-card>

    <!-- Create/Edit Dialog -->
    <AccountsFormDialog
      v-model="dialog"
      :editMode="editMode"
      :value="draft"
      :busy="repo.loading"
      @save="onSave"
    />

    <!-- Peek -->
    <PeekButton
      v-if="peek.open"
      :open="peek.open"
      :title="t('جزئیات حساب')"
      :loading="peek.loading"
      @close="peek.open = false"
    >
      <div class="pa-4">
        <div class="text-subtitle-2 font-weight-bold mb-2">{{ peek.data?.name || '—' }}</div>
        <div class="text-caption">{{ t('موجودی') }}: {{ money(peek.data?.price) }}</div>
        <div class="text-caption">
          {{ t('نوع حساب') }}: {{ peek.data?.account_type_name || peek.data?.account_type_id || '—' }}
        </div>
        <div class="text-caption mt-2">{{ t('جزئیات') }}: {{ peek.data?.details || '—' }}</div>
      </div>
    </PeekButton>

    <!-- Delete confirm -->
    <DeleteConfirmDialog
      v-model="deleteDialog.open"
      :title="t('حذف رکورد')"
      :subtitle="t('آیا مطمئن هستید؟')"
      @confirm="confirmDelete"
    />

    <v-snackbar v-model="snack.open" :color="snack.color" timeout="2500">
      {{ snack.text }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";

import AppBar from "@/components/AppBar.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import PeekButton from "@/components/PeekButton.vue";
import DeleteConfirmDialog from "@/components/DeleteConfirmDialog.vue";

import AccountsFormDialog from "./AccountsFormDialog.vue";
import { useAccountsRepository } from "@/stores/AccountsRepository";

const repo = useAccountsRepository();
const { t, locale } = useI18n();
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ""));

const dialog = ref(false);
const editMode = ref(false);

const snack = reactive({ open: false, text: "", color: "primary" });
const peek = reactive({ open: false, loading: false, data: null });
const deleteDialog = reactive({ open: false, id: null });

const draft = reactive({
  id: null,
  name: "",
  price: 0,
  account_type_id: null,
  details: "",
});

const headersFT = computed(() => [
  { title: t("ID"), key: "id" },
  { title: t("نام"), key: "name" },
  { title: t("موجودی"), key: "price_display" },
  { title: t("نوع"), key: "account_type_name" },
  { title: t("جزئیات"), key: "details" },
  { title: t("عملیات"), key: "actions", sortable: false },
]);

const rowsFT = computed(() => {
  const rows = repo.Accounts || [];
  return rows.map((r) => ({
    ...r,
    price_display: money(r?.price),
    account_type_name: r?.account_type_name ?? r?.account_type_id ?? "—",
  }));
});

const pageCount = computed(() => {
  const per = Number(repo.itemsPerPage || 10);
  return Math.max(1, Math.ceil(Number(repo.totalItems || 0) / per));
});

function showSnack(text, color = "primary") {
  snack.text = text;
  snack.color = color;
  snack.open = true;
}

function money(n) {
  const v = Number(n ?? 0);
  return v.toLocaleString(undefined, { maximumFractionDigits: 2 });
}

const fetchAll = async () => {
  await repo.FetchAccountsData({ page: repo.page, itemsPerPage: repo.itemsPerPage });
};

const openCreate = () => {
  editMode.value = false;
  Object.assign(draft, {
    id: null,
    name: "",
    price: 0,
    account_type_id: null,
    details: "",
  });
  dialog.value = true;
};

const openEdit = async (rowPayload) => {
  const row = rowPayload?.row || rowPayload;
  if (!row?.id) return;

  editMode.value = true;
  await repo.FetchAccountData(row.id);

  const d = repo.Account || {};
  Object.assign(draft, {
    id: d.id ?? null,
    name: d.name ?? "",
    price: d.price ?? 0,
    account_type_id: d.account_type_id ?? null,
    details: d.details ?? "",
  });

  dialog.value = true;
};

const onSave = async (payload) => {
  try {
    const p = { ...(payload || {}) };

    if (editMode.value && p.id) await repo.UpdateAccount(p.id, p);
    else await repo.CreateAccount(p);

    dialog.value = false;
    showSnack(t("ثبت شد"));
    await fetchAll();
  } catch (e) {
    showSnack(t("خطا در ثبت دیتا"), "error");
  }
};

const startPeek = async (rowPayload) => {
  const row = rowPayload?.row || rowPayload;
  if (!row?.id) return;

  peek.open = true;
  peek.loading = true;
  try {
    await repo.FetchAccountData(row.id);
    peek.data = repo.Account;
  } finally {
    peek.loading = false;
  }
};

const askDelete = (rowPayload) => {
  const row = rowPayload?.row || rowPayload;
  if (!row?.id) return;
  deleteDialog.id = row.id;
  deleteDialog.open = true;
};

const confirmDelete = async () => {
  const id = deleteDialog.id;
  deleteDialog.open = false;
  if (!id) return;

  try {
    await repo.DeleteAccount(id);
    showSnack(t("حذف شد"));
    await fetchAll();
  } catch {
    showSnack(t("خطا در حذف"), "error");
  }
};

onMounted(fetchAll);
</script>
