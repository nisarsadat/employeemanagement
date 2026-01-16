<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="4">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('د راتلونکو تېلو لټون')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined" density="compact" clearable
          @keyup.enter="fetchAll()"
          @click:clear="() => { repo.Search=''; fetchAll(); }"
        />
      </v-col>
      <v-col cols="12" md="8" class="text-md-right">
        <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">
          {{ t('نوی د تيلو داخلول') }}
        </v-btn>
      </v-col>
    </v-row>

    <v-card elevation="2">
      <v-data-table
        :headers="headers"
        :items="repo.IncomeOils"
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

        <template #item.oil_quantity="{ item }">
          <strong>{{ qty(item.oil_quantity) }}</strong>
        </template>

        <template #item.account="{ item }">
          <span>{{ item?.account?.name ?? '—' }}</span>
        </template>

        <!-- Date (formatted) -->
        <template #item.date="{ item }">
          <span>{{ formatDisplay(item.date, locale.value) }}</span>
        </template>

        <template #item.actions="{ item }">
          <v-btn size="small" variant="text" color="primary" icon="mdi-pencil" @click="openEdit(item.id)" />
          <v-btn size="small" variant="text" color="error" icon="mdi-delete" @click="del(item)" />
        </template>

        <template #bottom>
          <div class="px-4 py-3 d-flex align-center justify-space-between">
            <div class="text-caption">{{ t('ټول') }}: <strong>{{ repo.totalItems }}</strong></div>
            <v-pagination v-model="repo.page" :length="pageCount" total-visible="7" @update:modelValue="fetchAll" />
          </div>
        </template>
      </v-data-table>
    </v-card>

    <IncomeOilFormDialog v-model="dialog" :editMode="editMode" :value="draft" :busy="repo.loading" @save="onSave" />
  </v-container>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useOilRepository } from '@/stores/OilRepository';
import IncomeOilFormDialog from './IncomeOilFormDialog.vue';
import { useCalendar } from '../../../../composables/useCalendar';

const repo = useOilRepository();
const { t, locale } = useI18n();
const { formatDisplay, toIso } = useCalendar();
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

const headers = computed(() => [
  { title: t('حساب'), key: 'account', sortable: false },
  { title: t('مقدار (L)'), key: 'oil_quantity', sortable: false },
  { title: t('نیټه'), key: 'date', sortable: false },
  { title: t('یادونه'), key: 'note', sortable: false },
  { title: t('کړنې'), key: 'actions', sortable: false, align: 'end' },
]);

const pageCount = computed(() => {
  const per = Number(repo.itemsPerPage || 10);
  return Math.max(1, Math.ceil(Number(repo.totalItems || 0) / per));
});

const dialog = ref(false);
const editMode = ref(false);

// include date in draft
const draft = reactive({
  id: null,
  oil_account_id: null,
  date: null,
  oil_quantity: 0,
  source: '',
  note: '',
});

const fetchAll = async () => { await repo.FetchIncomeOils({ page: repo.page, itemsPerPage: repo.itemsPerPage }); };
const onPage = async (p) => { repo.page = p || 1; await fetchAll(); };
const onItemsPerPage = async (pp) => { repo.itemsPerPage = pp || 10; repo.page = 1; await fetchAll(); };

const openCreate = () => {
  editMode.value = false;
  Object.assign(draft, { id:null, oil_account_id:null, date:null, oil_quantity:0, source:'', note:'' });
  dialog.value = true;
};

const openEdit = async (id) => {
  editMode.value = true;
  await repo.FetchIncomeOil(id);
  const d = repo.IncomeOil || {};
  Object.assign(draft, {
    id: d.id ?? null,
    oil_account_id: d.oil_account_id ?? d.account?.id ?? null,
    date: d.date ?? null,
    oil_quantity: d.oil_quantity ?? 0,
    source: d.source ?? '',
    note: d.note ?? '',
  });
  dialog.value = true;
};

const onSave = async (payload) => {
  if (payload?.date) payload.date = toIso(payload.date); // normalize to YYYY-MM-DD
  if (editMode.value && payload.id) await repo.UpdateIncomeOil(payload.id, payload);
  else await repo.CreateIncomeOil(payload);
  dialog.value = false;
  await fetchAll();
};

const del = async (item) => {
  if (!window.confirm(t('ایا د حذف کولو ډاډ لرئ؟'))) return;
  await repo.DeleteIncomeOil(item.id);
  await fetchAll();
};

const qty = (n) => Number(n ?? 0).toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 });

onMounted(fetchAll);
</script>
