<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="4">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('حساب د تيلو لټون')"
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
          {{ t('نوی د تيلو حساب') }}
        </v-btn>
      </v-col>
    </v-row>

    <v-card elevation="2">
      <v-data-table
        :headers="headers"
        :items="repo.OilAccounts"
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

        <template #item.quantity="{ item }">
          <strong>{{ qty(item.quantity) }}</strong>
        </template>

        <template #item.actions="{ item }">
          <v-btn size="small" variant="text" color="primary" icon="mdi-pencil" @click="openEdit(item.id)" />
          <v-btn size="small" variant="text" color="error" icon="mdi-delete" @click="del(item)" />
        </template>

        <template #bottom>
          <div class="px-4 py-3 d-flex align-center justify-space-between">
            <div class="text-caption">
              {{ t('ټول') }}: <strong>{{ repo.totalItems }}</strong>
            </div>
            <v-pagination v-model="repo.page" :length="pageCount" total-visible="7" @update:modelValue="fetchAll" />
          </div>
        </template>
      </v-data-table>
    </v-card>

    <OilAccountFormDialog v-model="dialog" :editMode="editMode" :value="draft" :busy="repo.loading" @save="onSave" />
  </v-container>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useOilRepository } from '@/stores/OilRepository';
import OilAccountFormDialog from './OilAccountFormDialog.vue';

const repo = useOilRepository();
const { t, locale } = useI18n();
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

const headers = computed(() => [
  { title: t('نوم'), key: 'name', sortable: false },
  { title: t('موجوده مقدار (L)'), key: 'quantity', sortable: false },
  { title: t('تفصیل'), key: 'details', sortable: false },
  { title: t('کړنې'), key: 'actions', sortable: false, align: 'end' },
]);

const pageCount = computed(() => {
  const per = Number(repo.itemsPerPage || 10);
  return Math.max(1, Math.ceil(Number(repo.totalItems || 0) / per));
});

const dialog = ref(false);
const editMode = ref(false);
const draft = reactive({ id: null, name: '', details: '' });

const fetchAll = async () => {
  await repo.FetchOilAccounts({ page: repo.page, itemsPerPage: repo.itemsPerPage });
};

const onPage = async (p) => { repo.page = p || 1; await fetchAll(); };
const onItemsPerPage = async (pp) => { repo.itemsPerPage = pp || 10; repo.page = 1; await fetchAll(); };

const openCreate = () => { editMode.value = false; Object.assign(draft, { id: null, name: '', details: '' }); dialog.value = true; };
const openEdit = async (id) => {
  editMode.value = true;
  await repo.FetchOilAccount(id);
  const d = repo.OilAccount || {};
  Object.assign(draft, { id: d.id ?? null, name: d.name ?? '', details: d.details ?? '' });
  dialog.value = true;
};

const onSave = async (payload) => {
  if (editMode.value && payload.id) await repo.UpdateOilAccount(payload.id, payload);
  else await repo.CreateOilAccount(payload);
  dialog.value = false;
  await fetchAll();
};

const del = async (item) => {
  if (!window.confirm(t('ایا د حذف کولو ډاډ لرئ؟'))) return;
  await repo.DeleteOilAccount(item.id);
  await fetchAll();
};

const qty = (n) => Number(n ?? 0).toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 });

onMounted(fetchAll);
</script>
