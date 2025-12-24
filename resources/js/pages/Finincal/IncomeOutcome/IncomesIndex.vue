<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">

    <!-- Top Bar (like Accounts/Oil) -->
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="4">
        <v-text-field
          v-model.trim="incomeStore.Search"
          :label="t('جستجو عایدات')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          @keyup.enter="fetchAll()"
          @click:clear="() => { incomeStore.Search=''; fetchAll(); }"
        />
      </v-col>

      <v-col cols="12" md="8" class="text-md-right d-flex justify-end gap-2">


        <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">
          {{ t('عاید جدید') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- List (FilterableTable) -->
    <FilterableTable
      :title="t('لیست عایدات')"
      :subtitle="t('جستجو و مشاهده')"
      :headers="headersFT"
      :rows="rowsFT"
      :filters="{ search: incomeStore.Search }"
      :topGroups="[]"
      :loading="incomeStore.loading"
      @edit="openEdit"
      @peek="startPeek"
      @refresh="fetchAll"
      @delete="askDelete"
    />

    <!-- Pagination bottom (like Accounts/Oil) -->
    <v-card elevation="2" class="mt-3">
      <div class="px-4 py-3 d-flex align-center justify-space-between">
        <div class="text-caption">
          {{ t('ټول') }}: <strong>{{ incomeStore.totalItems }}</strong>
        </div>

        <v-pagination
          v-model="incomeStore.page"
          :length="pageCount"
          total-visible="7"
          @update:modelValue="fetchAll"
        />
      </div>
    </v-card>

    <!-- Create/Edit Dialog -->
    <IncomesFormDialog
      v-model="dialog"
      :editMode="editMode"
      :value="draft"
      :busy="incomeStore.loading"
      :accountsOptions="accountsOptions"
      @save="onSave"
    />

    <!-- Peek -->
    <PeekButton
      v-if="peek.open"
      :open="peek.open"
      :title="t('جزئیات عاید')"
      :loading="peek.loading"
      @close="peek.open = false"
    >
      <div class="pa-4">
        <div class="text-subtitle-2 font-weight-bold mb-2">{{ peek.data?.account_name || '—' }}</div>
        <div class="text-caption">{{ t('مقدار') }}: {{ money(peek.data?.amount) }}</div>
        <div class="text-caption">{{ t('تاریخ') }}: {{ peek.data?.received_at || '—' }}</div>
        <div class="text-caption mt-2">{{ t('یادداشت') }}: {{ peek.data?.note || '—' }}</div>
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
import { storeToRefs } from "pinia";
import { useI18n } from "vue-i18n";

import AppBar from "@/components/AppBar.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import PeekButton from "@/components/PeekButton.vue";
import DeleteConfirmDialog from "@/components/DeleteConfirmDialog.vue";
import DateInput from "@/components/DateInput.vue";

import IncomesFormDialog from "./IncomesFormDialog.vue";

import { useIncomesRepository } from "@/stores/IncomesRepository";
import { useAccountsRepository } from "@/stores/AccountsRepository";

const incomeStore = useIncomesRepository();
const accountsStore = useAccountsRepository();

const { t, locale } = useI18n();
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ""));

const { Incomes } = storeToRefs(incomeStore);
const { Accounts } = storeToRefs(accountsStore);

const dialog = ref(false);
const editMode = ref(false);

const snack = reactive({ open: false, text: "", color: "primary" });
const peek = reactive({ open: false, loading: false, data: null });
const deleteDialog = reactive({ open: false, id: null });

const accountsOptions = computed(() => Accounts.value || []);

const draft = reactive({
  id: null,
  account_id: null,
  amount: 0,
  received_at: null,
  note: "",
});

const headersFT = computed(() => [
  { title: t("ID"), key: "id" },
  { title: t("حساب"), key: "account_name" },
  { title: t("مقدار"), key: "amount_display" },
  { title: t("تاریخ"), key: "received_at" },
  { title: t("یادداشت"), key: "note" },
  { title: t("عملیات"), key: "actions", sortable: false },
]);

const rowsFT = computed(() => {
  const rows = Incomes.value || [];
  return rows.map((r) => ({
    ...r,
    amount_display: money(r?.amount),
  }));
});

const pageCount = computed(() => {
  const per = Number(incomeStore.itemsPerPage || 10);
  return Math.max(1, Math.ceil(Number(incomeStore.totalItems || 0) / per));
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
  await incomeStore.FetchIncomesData({ page: incomeStore.page, itemsPerPage: incomeStore.itemsPerPage });
};

async function fetchAccountsOptions() {
  accountsStore.itemsPerPage = 500;
  await accountsStore.FetchAccountsData({ page: 1, itemsPerPage: 500 });
}

const openCreate = () => {
  editMode.value = false;
  Object.assign(draft, {
    id: null,
    account_id: null,
    amount: 0,
    received_at: null,
    note: "",
  });
  dialog.value = true;
};

const openEdit = async (rowPayload) => {
  const row = rowPayload?.row || rowPayload;
  if (!row?.id) return;

  editMode.value = true;
  await incomeStore.FetchIncomeData(row.id);

  const d = incomeStore.Income || {};
  Object.assign(draft, {
    id: d.id ?? null,
    account_id: d.account_id ?? null,
    amount: d.amount ?? 0,
    received_at: d.received_at ?? null,
    note: d.note ?? "",
  });

  dialog.value = true;
};

const onSave = async (payload) => {
  try {
    const p = { ...(payload || {}) };

    if (editMode.value && p.id) await incomeStore.UpdateIncome(p.id, p);
    else await incomeStore.CreateIncome(p);

    dialog.value = false;
    showSnack(t("ثبت شد"));

    await fetchAll();
    await fetchAccountsOptions(); // balances changed
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
    await incomeStore.FetchIncomeData(row.id);
    peek.data = incomeStore.Income;
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
    await incomeStore.DeleteIncome(id);
    showSnack(t("حذف شد"));
    await fetchAll();
    await fetchAccountsOptions(); // balances changed
  } catch {
    showSnack(t("خطا در حذف"), "error");
  }
};

onMounted(async () => {
  await fetchAccountsOptions();
  await fetchAll();
});
</script>
