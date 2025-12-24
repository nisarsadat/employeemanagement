<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">

    <!-- Top Bar (like Accounts/Oil) -->
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="4">
        <v-text-field
          v-model.trim="financeStore.Search"
          :label="t('جستجو مصارف')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          @keyup.enter="fetchAll()"
          @click:clear="() => { financeStore.Search=''; fetchAll(); }"
        />
      </v-col>

      <v-col cols="12" md="8" class="text-md-right d-flex justify-end gap-2">


        <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">
          {{ t('مصرف جدید') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- List (FilterableTable) -->
    <FilterableTable
      :title="t('لیست مصارف')"
      :subtitle="t('جستجو و مشاهده')"
      :headers="headersFT"
      :rows="rowsFT"
      :filters="{ search: financeStore.Search }"
      :topGroups="[]"
      :loading="financeStore.loading"
      @edit="openEdit"
      @peek="startPeek"
      @refresh="fetchAll"
      @delete="askDelete"
    />

    <!-- Pagination bottom (like Accounts/Oil) -->
    <v-card elevation="2" class="mt-3">
      <div class="px-4 py-3 d-flex align-center justify-space-between">
        <div class="text-caption">
          {{ t('ټول') }}: <strong>{{ financeStore.totalItems }}</strong>
        </div>

        <v-pagination
          v-model="financeStore.page"
          :length="pageCount"
          total-visible="7"
          @update:modelValue="fetchAll"
        />
      </div>
    </v-card>

    <!-- Create/Edit Dialog -->
    <FinancesFormDialog
      v-model="dialog"
      :editMode="editMode"
      :value="draft"
      :busy="financeStore.loading"
      :accountsOptions="accountsOptions"
      :employeesOptions="employeesOptions"
      :resourcesOptions="resourcesOptions"
      @save="onSave"
    />

    <!-- Peek -->
    <PeekButton
      v-if="peek.open"
      :open="peek.open"
      :title="t('جزئیات مصرف')"
      :loading="peek.loading"
      @close="peek.open = false"
    >
      <div class="pa-4">
        <div class="text-subtitle-2 font-weight-bold mb-2">{{ peek.data?.account_name || '—' }}</div>
        <div class="text-caption">{{ t('مبلغ') }}: {{ money(peek.data?.money_amount_number) }}</div>
        <div class="text-caption">{{ t('تاریخ') }}: {{ peek.data?.date || '—' }}</div>

        <div class="text-caption mt-2">
          {{ t('کارمند') }}: {{ peek.data?.office_employee?.name || '—' }} —
          {{ t('کد') }}: {{ peek.data?.office_employee_code || '—' }}
        </div>

        <div class="text-caption">
          {{ t('منبع') }}: {{ peek.data?.resource?.name || '—' }} —
          {{ t('کد') }}: {{ peek.data?.resource_code || '—' }}
        </div>

        <div class="text-caption mt-2">{{ t('یادداشت') }}: {{ peek.data?.notes || '—' }}</div>
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

import FinancesFormDialog from "./FinancesFormDialog.vue";

import { useFinancesRepository } from "@/stores/FinancesRepository";
import { useAccountsRepository } from "@/stores/AccountsRepository";

const financeStore = useFinancesRepository();
const accountsStore = useAccountsRepository();

const { t, locale } = useI18n();
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ""));

const { Finances, AccountsOptions, EmployeesOptions, ResourcesOptions } = storeToRefs(financeStore);

const accountsOptions = computed(() => AccountsOptions.value || accountsStore.Accounts || []);
const employeesOptions = computed(() => EmployeesOptions.value || []);
const resourcesOptions = computed(() => ResourcesOptions.value || []);

const dialog = ref(false);
const editMode = ref(false);

const snack = reactive({ open: false, text: "", color: "primary" });
const peek = reactive({ open: false, loading: false, data: null });
const deleteDialog = reactive({ open: false, id: null });

const draft = reactive({
  id: null,
  account_id: null,
  office_employee_id: null,
  resource_id: null,
  money_amount_number: 0,
  money_amount_words: "",
  date: null,
  employee_phone: "",
  expense_result: "",
  money_distributer: "",
  payer: "",
  receiver: "",
  account_type: "",
  notes: "",
});

const headersFT = computed(() => [
  { title: t("ID"), key: "id" },
  { title: t("حساب"), key: "account_name" },
  { title: t("مبلغ"), key: "money_amount_number_display" },
  { title: t("تاریخ"), key: "date" },
  { title: t("کارمند"), key: "office_employee_name" },
  { title: t("کد کارمند"), key: "office_employee_code" },
  { title: t("منبع"), key: "resource_name" },
  { title: t("کد منبع"), key: "resource_code" },
  { title: t("نتیجه"), key: "expense_result" },
  { title: t("عملیات"), key: "actions", sortable: false },
]);

const rowsFT = computed(() => {
  const rows = Finances.value || [];
  return rows.map((r) => ({
    ...r,
    money_amount_number_display: money(r?.money_amount_number),
    office_employee_name: r?.office_employee?.name ?? "—",
    resource_name: r?.resource?.name ?? "—",
  }));
});

const pageCount = computed(() => {
  const per = Number(financeStore.itemsPerPage || 10);
  return Math.max(1, Math.ceil(Number(financeStore.totalItems || 0) / per));
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
  await financeStore.FetchFinancesData({ page: financeStore.page, itemsPerPage: financeStore.itemsPerPage });
};

async function fetchOptions() {
  await financeStore.FetchAllOptions();
  accountsStore.itemsPerPage = 500;
  await accountsStore.FetchAccountsData({ page: 1, itemsPerPage: 500 });
}

const openCreate = () => {
  editMode.value = false;
  Object.assign(draft, {
    id: null,
    account_id: null,
    office_employee_id: null,
    resource_id: null,
    money_amount_number: 0,
    money_amount_words: "",
    date: null,
    employee_phone: "",
    expense_result: "",
    money_distributer: "",
    payer: "",
    receiver: "",
    account_type: "",
    notes: "",
  });
  dialog.value = true;
};

const openEdit = async (rowPayload) => {
  const row = rowPayload?.row || rowPayload;
  if (!row?.id) return;

  editMode.value = true;
  await financeStore.FetchFinanceData(row.id);

  const d = financeStore.Finance || {};
  Object.assign(draft, {
    id: d.id ?? null,
    account_id: d.account_id ?? null,
    office_employee_id: d.office_employee_id ?? null,
    resource_id: d.resource_id ?? null,
    money_amount_number: d.money_amount_number ?? 0,
    money_amount_words: d.money_amount_words ?? "",
    date: d.date ?? null,
    employee_phone: d.employee_phone ?? "",
    expense_result: d.expense_result ?? "",
    money_distributer: d.money_distributer ?? "",
    payer: d.payer ?? "",
    receiver: d.receiver ?? "",
    account_type: d.account_type ?? "",
    notes: d.notes ?? "",
  });

  dialog.value = true;
};

const onSave = async (payload) => {
  try {
    const p = { ...(payload || {}) };

    if (editMode.value && p.id) await financeStore.UpdateFinance(p.id, p);
    else await financeStore.CreateFinance(p);

    dialog.value = false;
    showSnack(t("ثبت شد"));

    await fetchAll();
    await fetchOptions(); // balances changed
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
    await financeStore.FetchFinanceData(row.id);
    peek.data = financeStore.Finance;
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
    await financeStore.DeleteFinance(id);
    showSnack(t("حذف شد"));
    await fetchAll();
    await fetchOptions(); // balances changed
  } catch {
    showSnack(t("خطا در حذف"), "error");
  }
};

onMounted(async () => {
  await fetchOptions();
  await fetchAll();
});
</script>
