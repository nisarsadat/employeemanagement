<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Top Bar -->
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="4">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('د لګښتونو لټون')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          @keyup.enter="fetchAll()"
          @click:clear="
            () => {
              repo.Search = '';
              fetchAll();
            }
          "
        />
      </v-col>

      <v-col cols="12" md="8" class="text-md-right">
        <v-btn color="primary" size="larg" prepend-icon="mdi-plus" @click="openCreate()">
          {{ t('نوی لګښت') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Table -->
    <v-card elevation="2">
      <v-data-table
        :headers="headers"
        :items="repo.Expenses"
        :items-length="repo.totalItems"
        :items-per-page="repo.itemsPerPage"
        :page="repo.page"
        :loading="repo.loading"
        item-key="id"
        @update:page="onPage"
        @update:items-per-page="onItemsPerPage"
        class="elevation-0"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <template #loading>
          <div class="pa-6 text-center">{{ t('لېست د پورته کېدو په حال کې دی…') }}</div>
        </template>

        <template #item.amount_price="{ item }">
          <span>{{ money(item.amount_price) }}</span>
        </template>

        <template #item.account="{ item }">
          <span>{{ item?.account?.name ?? '—' }}</span>
        </template>

        <template #item.payee="{ item }">
          <span>
            {{ item?.payee?.name ?? '—' }}
            <small v-if="item?.payee?.code" class="text-medium-emphasis">
              ({{ t('کوډ') }}: {{ item.payee.code }})
            </small>
          </span>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            size="small"
            variant="text"
            color="primary"
            icon="mdi-pencil"
            @click="openEdit(item.id)"
          />
          <v-btn size="small" variant="text" color="error" icon="mdi-delete" @click="del(item)" />
        </template>

        <template #bottom>
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
        </template>
        <template #item.date="{ item }">
          <span>{{ formatDisplay(item.date, locale.value) }}</span>
        </template>
      </v-data-table>
    </v-card>

    <!-- Dialog -->
    <ExpenseFormDialog
      v-model="dialog"
      :editMode="editMode"
      :value="draft"
      :busy="repo.loading"
      @save="onSave"
    />
  </v-container>
</template>

<script setup>
  import { ref, reactive, computed, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useExpensesRepository } from '@/stores/ExpensesRepository';
  import ExpenseFormDialog from './ExpenseFormDialog.vue';
  import { useCalendar } from '../../../../composables/useCalendar';
  const repo = useExpensesRepository();

  const { t, locale } = useI18n();
  const { formatDisplay, toIso } = useCalendar();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  const headers = computed(() => [
    { title: t('نیټه'), key: 'date', sortable: false },
    { title: t('مبلغ'), key: 'amount_price', sortable: false },
    { title: t('حساب'), key: 'account', sortable: false },
    { title: t('چا ته پیسې؟'), key: 'payee', sortable: false },
    { title: t('دلیل'), key: 'reason_expense', sortable: false },
    { title: t('کړنې'), key: 'actions', sortable: false, align: 'end' },
  ]);

  const pageCount = computed(() => {
    const per = Number(repo.itemsPerPage || 10);
    return Math.max(1, Math.ceil(Number(repo.totalItems || 0) / per));
  });

  const dialog = ref(false);
  const editMode = ref(false);
  const draft = reactive({
    id: null,
    account_id: null,
    date: null,
    payee_type: null,
    payee_id: null,
    amount_price: 0,
    phone_number: '',
    reason_expense: '',
    note: '',
  });

  const fetchAll = async () => {
    await repo.FetchExpensesData({ page: repo.page, itemsPerPage: repo.itemsPerPage });
  };

  const onPage = async (p) => {
    repo.page = p || 1;
    await fetchAll();
  };
  const onItemsPerPage = async (pp) => {
    repo.itemsPerPage = pp || 10;
    repo.page = 1;
    await fetchAll();
  };

  const openCreate = () => {
    editMode.value = false;
    Object.assign(draft, {
      id: null,
      account_id: null,
      date: null,
      payee_type: null,
      payee_id: null,
      amount_price: 0,
      phone_number: '',
      reason_expense: '',
      note: '',
    });
    dialog.value = true;
  };

  const openEdit = async (id) => {
    if (payload?.date) payload.date = toIso(payload.date);
    editMode.value = true;
    await repo.FetchExpenseData(id);
    const d = repo.Expense || {};
    Object.assign(draft, {
      id: d.id ?? null,
      account_id: d.account?.id ?? d.account_id ?? null,
      date: d.date ?? null,
      payee_type:
        d.payee?.type === 'OfficeEmployee' ? 'office_employee' : d.payee?.type ? 'resource' : null,
      payee_id: d.payee?.id ?? null,
      amount_price: d.amount_price ?? 0,
      phone_number: d.phone_number ?? '',
      reason_expense: d.reason_expense ?? '',
      note: d.note ?? '',
    });
    dialog.value = true;
  };

  const onSave = async (payload) => {
    if (payload?.date) payload.date = toIso(payload.date);
    if (editMode.value && payload.id) {
      await repo.UpdateExpense(payload.id, payload);
    } else {
      await repo.CreateExpense(payload);
    }
    dialog.value = false;
    await fetchAll();
  };

  // delete with 3 confirms
  const del = async (item) => {
    const msg = (s) => `${t('ایا د حذف کولو ډاډ لرئ؟')} "${item.account?.name ?? ''}" (${s}/3)`;
    if (!window.confirm(msg(1))) return;
    if (!window.confirm(msg(2))) return;
    if (!window.confirm(msg(3))) return;
    await repo.DeleteExpense(item.id);
    await fetchAll();
  };

  const money = (n) =>
    Number(n ?? 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

  onMounted(fetchAll);
</script>
