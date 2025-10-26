<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Top Bar -->
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="4">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('د عایداتو لټون')"
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
          {{ t('نوی عاید') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Table -->
    <v-card elevation="2">
      <v-data-table
        :headers="headers"
        :items="repo.Incomes"
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

        <template #item.amount="{ item }">
          <span>{{ money(item.amount) }}</span>
        </template>

        <template #item.account="{ item }">
          <span>{{ item?.account?.name ?? '—' }}</span>
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
    <IncomeFormDialog
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
  import { useIncomesRepository } from '@/stores/IncomesRepository';
  import IncomeFormDialog from './IncomeFormDialog.vue';
  import { useCalendar } from '../../../../composables/useCalendar';
  const repo = useIncomesRepository();

  const { t, locale } = useI18n();
  const { formatDisplay, toIso } = useCalendar();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  const headers = computed(() => [
    { title: t('نیټه'), key: 'date', sortable: false },
    { title: t('مبلغ'), key: 'amount', sortable: false },
    { title: t('حساب'), key: 'account', sortable: false },
    { title: t('یادونه'), key: 'note', sortable: false },
    { title: t('کړنې'), key: 'actions', sortable: false, align: 'end' },
  ]);

  const pageCount = computed(() => {
    const per = Number(repo.itemsPerPage || 10);
    return Math.max(1, Math.ceil(Number(repo.totalItems || 0) / per));
  });

  const dialog = ref(false);
  const editMode = ref(false);
  const draft = reactive({ id: null, account_id: null, amount: 0, date: null, note: '' });

  const fetchAll = async () => {
    await repo.FetchIncomesData({ page: repo.page, itemsPerPage: repo.itemsPerPage });
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
    Object.assign(draft, { id: null, account_id: null, amount: 0, date: null, note: '' });
    dialog.value = true;
  };

  const openEdit = async (id) => {
    editMode.value = true;
    await repo.FetchIncomeData(id);
    const d = repo.Income || {};
    Object.assign(draft, {
      id: d.id ?? null,
      account_id: d.account?.id ?? d.account_id ?? null,
      amount: d.amount ?? 0,
      date: d.date ?? null,
      note: d.note ?? '',
    });
    dialog.value = true;
  };

  const onSave = async (payload) => {
    if (payload?.date) payload.date = toIso(payload.date);
    if (editMode.value && payload.id) {
      await repo.UpdateIncome(payload.id, payload);
    } else {
      await repo.CreateIncome(payload);
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
    await repo.DeleteIncome(item.id);
    await fetchAll();
  };

  const money = (n) =>
    Number(n ?? 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

  onMounted(fetchAll);
</script>
