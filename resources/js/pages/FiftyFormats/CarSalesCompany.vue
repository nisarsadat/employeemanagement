<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('شرکت‌های فروش موتر')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید شرکت فروش موتر')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search + Add -->
    <v-row class="d-flex">
      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetch()"
          clearable
          @click:clear="
            () => {
              repo.Search = '';
              fetch();
            }
          "
        />
      </v-col>

      <v-col cols="12" md="3" class="text-md-right">
        <v-btn color="primary" @click="addBlankRow">
          {{ t('اضافه کردن ریکارډ جدید') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- TABLE -->
    <v-card class="tablebody">
      <v-data-table
        :headers="headers"
        :items="rows"
        :loading="repo.loading"
        :items-per-page="repo.itemsPerPage"
        :page="repo.page"
        :items-length="repo.totalItems || 0"
        item-key="idOrTmp"
        class="elevation-2 custom-table"
        :dir="isRtl ? 'rtl' : 'ltr'"
        density="comfortable"
        @update:page="(p) => { repo.page = p; fetch(); }"
        @update:items-per-page="(n) => { repo.itemsPerPage = n; fetch(); }"
      >
        <!-- Company -->
        <template #item.car_sales_company_name="{ item }">
          <v-text-field
            v-model="item.draft.car_sales_company_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام شرکت فروش موتر')"
            hide-details
          />
        </template>

        <!-- Responsible -->
        <template #item.responsible_name="{ item }">
          <v-text-field
            v-model="item.draft.responsible_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام مسئول')"
            hide-details
          />
        </template>
        <template #item.responsible_f_name="{ item }">
          <v-text-field
            v-model="item.draft.responsible_f_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام پدر مسئول')"
            hide-details
          />
        </template>
        <template #item.responsible_position="{ item }">
          <v-text-field
            v-model="item.draft.responsible_position"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('وظیفه مسئول')"
            hide-details
          />
        </template>
        <template #item.responsible_phone="{ item }">
          <v-text-field
            v-model="item.draft.responsible_phone"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره مسئول')"
            hide-details
          />
        </template>

        <!-- Address -->
        <template #item.province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.province"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولایت')"
            />
            <v-text-field
              v-model="item.draft.zone"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('زون')"
            />
            <v-text-field
              v-model="item.draft.district"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولسوالی')"
            />
            <v-text-field
              v-model="item.draft.village"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('قریه')"
            />
          </div>
        </template>

        <template #item.specific_place="{ item }">
          <v-text-field
            v-model="item.draft.specific_place"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('موقعیت مشخص')"
            hide-details
          />
        </template>
        <template #item.coordinates="{ item }">
          <v-text-field
            v-model="item.draft.coordinates"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('مختصات')"
            hide-details
          />
        </template>

        <!-- Note -->
        <template #item.note="{ item }">
          <v-text-field
            v-model="item.draft.note"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('یادداشت')"
            hide-details
          />
        </template>

        <!-- Resource -->
        <template #item.resource_code="{ item }">
          <v-autocomplete
            v-model="item.draft.resource_code"
            :items="resources"
            item-title="code_number"
            item-value="code_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کود منبع')"
            hide-details
            clearable
            @update:model-value="onResourceCodeChange(item)"
          />
        </template>
        <template #item.resource_phone="{ item }">
          <v-text-field
            v-model="item.draft.resource_phone"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره منبع')"
            hide-details
            readonly
          />
        </template>

        <!-- ACTIONS -->
        <template #item.actions="{ item }">
          <div class="flex items-center gap-1">
            <v-btn class="bg-primary" variant="outlined" @click="save(item)" :loading="item.loading">
              {{ t('ثبت دیتا') }}
            </v-btn>
            <v-btn class="bg-error" variant="outlined" @click="del(item)">
              {{ t('حذف') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">{{ t('هیچ ریکارډ یافت نشد') }}</div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>

  <!-- FILTERABLE TABLE -->
  <v-container fluid>
    <FilterableTable
      :headers="headersFT"
      :rows="rows"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      class="mb-20 mx-2"
    />
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import AppBar from '@/components/AppBar.vue';
import FilterableTable from '@/components/FilterableTable.vue';
import { useSchoolRepository } from '@/stores/SchoolRepository';

const { t, locale } = useI18n();
const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => (isRtl.value = l === 'fa'));
const drawer = ref(false);

const repo = useSchoolRepository();
const rows = ref([]);
const filters = ref({});
let tmpId = 0;
const newTmpId = () => `tmp-${++tmpId}`;

/* Resource code → phone → id */
const resources = computed(() => repo.resourcecode || []);
function onResourceCodeChange(item) {
  const code = item.draft.resource_code;
  const found = resources.value.find((r) => r.code_number === code);
  item.draft.resource_phone = found?.phone_number ?? '';
  item.draft.resource_id = found?.id ?? null;
}

/* HEADERS */
const headers = computed(() => [
  { title: t('نام شرکت فروش موتر'), key: 'car_sales_company_name' },

  { title: t('نام مسئول'), key: 'responsible_name' },
  { title: t('نام پدر مسئول'), key: 'responsible_f_name' },
  { title: t('وظیفه مسئول'), key: 'responsible_position' },
  { title: t('شماره مسئول'), key: 'responsible_phone' },

  { title: t('آدرس'), key: 'province' },
  { title: t('موقعیت مشخص'), key: 'specific_place' },
  { title: t('مختصات'), key: 'coordinates' },

  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },

  { title: t('عملیات'), key: 'actions', sortable: false },
]);

/* FILTER HEADERS */
const headersFT = computed(() => [
  { title: t('نام شرکت فروش موتر'), key: 'car_sales_company_name' },

  { title: t('نام مسئول'), key: 'responsible_name' },
  { title: t('نام پدر مسئول'), key: 'responsible_f_name' },
  { title: t('وظیفه مسئول'), key: 'responsible_position' },
  { title: t('شماره مسئول'), key: 'responsible_phone' },

  { title: t('ولایت'), key: 'province' },
  { title: t('زون'), key: 'zone' },
  { title: t('ولسوالی'), key: 'district' },
  { title: t('قریه'), key: 'village' },

  { title: t('موقعیت مشخص'), key: 'specific_place' },
  { title: t('مختصات'), key: 'coordinates' },

  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },
]);

/* GROUPS */
const topGroups = computed(() => [
  { title: t('شرکت'), keys: ['car_sales_company_name'] },
  { title: t('مسئول'), keys: ['responsible_name', 'responsible_f_name', 'responsible_position', 'responsible_phone'] },
  { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village'] },
  { title: t('موقعیت/مختصات'), keys: ['specific_place', 'coordinates'] },
  { title: t('یادداشت'), keys: ['note'] },
  { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
]);

/* ROW MODEL */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? newTmpId(),
    loading: false,

    car_sales_company_name: x.car_sales_company_name ?? '',

    responsible_name: x.responsible_name ?? '',
    responsible_f_name: x.responsible_f_name ?? '',
    responsible_position: x.responsible_position ?? '',
    responsible_phone: x.responsible_phone ?? '',

    province: x.province ?? '',
    zone: x.zone ?? '',
    district: x.district ?? '',
    village: x.village ?? '',
    specific_place: x.specific_place ?? '',
    coordinates: x.coordinates ?? '',

    note: x.note ?? '',

    resource_code: x.resource_code ?? '',
    resource_phone: x.resource_phone ?? '',
    resource_id: x.resource_id ?? null,
  };
  return { ...base, draft: { ...base } };
}

/* DIFF / SAVE */
const EDITABLE_KEYS = [
  'car_sales_company_name',
  'responsible_name',
  'responsible_f_name',
  'responsible_position',
  'responsible_phone',
  'province',
  'zone',
  'district',
  'village',
  'specific_place',
  'coordinates',
  'note',
  'resource_code',
  'resource_phone',
  'resource_id',
];
const REQUIRED_ON_UPDATE = ['car_sales_company_name'];

function diffDraft(item) {
  const d = item.draft || {};
  const changed = {};
  for (const k of EDITABLE_KEYS) {
    if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
  }
  for (const k of REQUIRED_ON_UPDATE) {
    if (d[k] !== undefined) changed[k] = d[k];
  }
  return changed;
}

function toFormDataSelective(obj) {
  const fd = new FormData();
  Object.entries(obj || {}).forEach(([k, v]) => {
    if (v === undefined || v === null) return;
    fd.append(k, v);
  });
  return fd;
}

/* FETCH + CRUD */
function hydrate() {
  rows.value = (repo.CarSalesCompanies || []).map(toRowModel);
}
async function fetch() {
  await repo.FetchCarSalesCompaniesData?.({
    page: repo.page || 1,
    itemsPerPage: repo.itemsPerPage || 50,
    search: repo.Search || '',
  });
  hydrate();
}
function addBlankRow() {
  const newRow = toRowModel({});
  rows.value = [...rows.value, newRow];
  const per = repo.itemsPerPage || 50;
  repo.page = Math.max(1, Math.ceil(rows.value.length / per));
}
async function save(item) {
  item.loading = true;
  try {
    if (!item.id) {
      const fd = toFormDataSelective(item.draft);
      const created = await repo.CreateCarSalesCompany?.(fd);
      if (created?.id) {
        const model = toRowModel(created);
        model.idOrTmp = item.idOrTmp;
        Object.assign(item, model);
        item.draft = { ...model };
      } else {
        await fetch();
      }
      return;
    }

    const changed = diffDraft(item);
    if (Object.keys(changed).length === 0) return;

    const fd = toFormDataSelective(changed);
    const updated = await repo.UpdateCarSalesCompany?.(item.id, fd);
    if (updated?.id) {
      const model = toRowModel(updated);
      model.idOrTmp = item.idOrTmp;
      Object.assign(item, model);
      item.draft = { ...model };
    }
  } finally {
    item.loading = false;
  }
}
async function del(item) {
  if (!item.id) {
    rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp);
    return;
  }
  if (!confirm(t('آیا مطمئن هستید؟'))) return;
  await repo.DeleteCarSalesCompany?.(item.id);
  await fetch();
}

/* INIT */
onMounted(async () => {
  await Promise.allSettled([repo.GetResourceCode?.()]);
  await fetch();
});
</script>

<style scoped>
table th { white-space: nowrap; }
.custom-table :deep(.v-data-table__thead th) {
  background: rgb(var(--v-theme-primary));
  color: #fff;
  font-weight: 400;
}
.addr-group {
  display: grid;
  grid-template-columns: repeat(4, minmax(120px, 1fr));
  gap: 2px;
}
.text-group { display: grid; width: 140px; gap: 2px; }
.text-group-note { display: grid; width: 380px; gap: 2px; }
.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }
.tablebody { display: flex !important; gap: 2px !important; }
:deep(.v-field-label), :deep(.v-label), :deep(input::placeholder) {
  font-size: 12px !important;
  line-height: 1.2 !important;
  color: #555 !important;
}
:deep(.v-field__input) {
  min-height: 26px !important;
  padding: 2px 6px !important;
  font-size: 12px !important;
  line-height: 1.2 !important;
}
.addr-group, .text-group, .text-group-note {
  margin: -16px !important;
  padding: -10px !important;
  gap: -10px !important;
}
</style>
