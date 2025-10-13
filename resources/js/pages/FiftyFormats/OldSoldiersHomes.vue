<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('منازل سربازان سابق')"
      :subtitle="t('اضافه کردن یک ریکارډ منزل سرباز سابق')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

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
          @click:clear="() => { repo.Search = ''; fetch(); }"
        />
      </v-col>

      <v-col cols="12" md="3" class="text-md-right">
        <v-btn color="primary" @click="addBlankRow">
          {{ t('اضافه کردن ریکارډ') }}
        </v-btn>
      </v-col>
    </v-row>

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
        @update:page="p => { repo.page = p; fetch(); }"
        @update:items-per-page="n => { repo.itemsPerPage = n; fetch(); }"
      >
        <!-- Location block -->
        <template #item.province="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.province" density="compact" variant="outlined" hide-details :label="t('ولایت')" />
            <v-text-field v-model="item.draft.zone" density="compact" variant="outlined" hide-details :label="t('زون')" />
            <v-text-field v-model="item.draft.district" density="compact" variant="outlined" hide-details :label="t('ولسوالی')" />
            <v-text-field v-model="item.draft.village" density="compact" variant="outlined" hide-details :label="t('قریه/ناحیه')" />
          </div>
        </template>

        <template #item.specific_place="{ item }">
          <v-text-field v-model="item.draft.specific_place" class="text-group-note" density="compact" variant="outlined" :label="t('موقعیت مشخص')" hide-details />
        </template>

        <template #item.street_number="{ item }">
          <v-text-field v-model="item.draft.street_number" class="text-group" density="compact" variant="outlined" :label="t('سرک #')" hide-details />
        </template>

        <template #item.home_number="{ item }">
          <v-text-field v-model="item.draft.home_number" class="text-group" density="compact" variant="outlined" :label="t('نمبر خانه')" hide-details />
        </template>

        <template #item.coordinates="{ item }">
          <v-text-field v-model="item.draft.coordinates" class="text-group" density="compact" variant="outlined" :label="t('مختصات')" hide-details />
        </template>

        <!-- Identity -->
        <template #item.name="{ item }">
          <v-text-field v-model="item.draft.name" class="text-group" density="compact" variant="outlined" :label="t('نام')" hide-details />
        </template>

        <template #item.father_name="{ item }">
          <v-text-field v-model="item.draft.father_name" class="text-group" density="compact" variant="outlined" :label="t('نام پدر')" hide-details />
        </template>

        <template #item.main_province="{ item }">
          <v-text-field v-model="item.draft.main_province" class="text-group" density="compact" variant="outlined" :label="t('ولایت اصلی')" hide-details />
        </template>

        <!-- Work/notes -->
        <template #item.old_job="{ item }">
          <v-text-field v-model="item.draft.old_job" class="text-group" density="compact" variant="outlined" :label="t('وظیفه قبلی')" hide-details />
        </template>

        <template #item.now_activity="{ item }">
          <v-text-field v-model="item.draft.now_activity" class="text-group" density="compact" variant="outlined" :label="t('فعالیت فعلی')" hide-details />
        </template>

        <template #item.note="{ item }">
          <v-text-field v-model="item.draft.note" class="text-group-note" density="compact" variant="outlined" :label="t('یادداشت')" hide-details />
        </template>

        <!-- Resource (code -> id, phone) like Fasad -->
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

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="flex items-center gap-1">
            <v-btn class="bg-primary" variant="outlined" @click="save(item)" :loading="item.loading">
              {{ t('ثبت دیتا') }}
            </v-btn>
            <v-btn class="bg-error" variant="outlined" @click="del(item)">
              {{ t('حذف دیتا') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">
            {{ t('ریکارډی موجود نیست، لطفاً اضافه کنید') }}
          </div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>

  <v-container fluid>
    <FilterableTable
      :headers="headersFT"
      :rows="rows"
      :filters="filters"
      @update:filters="v => (filters = { ...v })"
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

// adjust if your store name differs:
import { useMawadRepository } from '@/stores/MawadRepository';

const { t, locale } = useI18n();
const drawer = ref(false);
const isRtl = ref(locale.value === 'fa');
watch(locale, l => (isRtl.value = l === 'fa'));

const repo = useMawadRepository();

const rows = ref([]);
const filters = ref({});
let tmpId = 0;
const newTmpId = () => `tmp-${++tmpId}`;

/* Resources dropdown (code -> phone, id) */
const resources = computed(() => repo.resourcecode || []);
function onResourceCodeChange(item) {
  const code = item.draft.resource_code;
  const found = resources.value.find(r => r.code_number === code);
  item.draft.resource_phone = found?.phone_number ?? '';
  item.draft.resource_id = found?.id ?? null;
}

/* Headers (table + filter) */
const headers = computed(() => [
  { title: t('ولایت'), key: 'province' },
  { title: t('زون'), key: 'zone' },
  { title: t('ولسوالی'), key: 'district' },
  { title: t('قریه/ناحیه'), key: 'village' },
  { title: t('موقعیت مشخص'), key: 'specific_place' },
  { title: t('سرک #'), key: 'street_number' },
  { title: t('خانه #'), key: 'home_number' },
  { title: t('مختصات'), key: 'coordinates' },

  { title: t('نام'), key: 'name' },
  { title: t('نام پدر'), key: 'father_name' },
  { title: t('ولایت اصلی'), key: 'main_province' },

  { title: t('وظیفه قبلی'), key: 'old_job' },
  { title: t('فعالیت فعلی'), key: 'now_activity' },

  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },

  { title: t('عمل'), key: 'actions', sortable: false },
]);

const headersFT = computed(() => [
  { title: t('ولایت'), key: 'province' },
  { title: t('زون'), key: 'zone' },
  { title: t('ولسوالی'), key: 'district' },
  { title: t('قریه/ناحیه'), key: 'village' },
  { title: t('موقعیت مشخص'), key: 'specific_place' },
  { title: t('سرک #'), key: 'street_number' },
  { title: t('خانه #'), key: 'home_number' },
  { title: t('مختصات'), key: 'coordinates' },

  { title: t('نام'), key: 'name' },
  { title: t('نام پدر'), key: 'father_name' },
  { title: t('ولایت اصلی'), key: 'main_province' },

  { title: t('وظیفه قبلی'), key: 'old_job' },
  { title: t('فعالیت فعلی'), key: 'now_activity' },

  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },
]);

const topGroups = computed(() => [
  { title: t('موقعیت'), keys: ['province','zone','district','village','specific_place','street_number','home_number','coordinates'] },
  { title: t('هویت'), keys: ['name','father_name','main_province'] },
  { title: t('کار/فعالیت'), keys: ['old_job','now_activity'] },
  { title: t('دیگر'), keys: ['note'] },
  { title: t('منبع'), keys: ['resource_code','resource_phone'] },
]);

/* Map API -> row with draft */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? newTmpId(),
    loading: false,

    province: x.province ?? '',
    zone: x.zone ?? '',
    district: x.district ?? '',
    village: x.village ?? '',
    specific_place: x.specific_place ?? '',
    street_number: x.street_number ?? '',
    home_number: x.home_number ?? '',
    coordinates: x.coordinates ?? '',

    name: x.name ?? '',
    father_name: x.father_name ?? '',
    main_province: x.main_province ?? '',

    old_job: x.old_job ?? '',
    now_activity: x.now_activity ?? '',
    note: x.note ?? '',

    resource_id: x.resource_id ?? null,
    resource_code: x.resource_code ?? '',
    resource_phone: x.resource_phone ?? '',
  };
  return { ...base, draft: { ...base } };
}

function hydrate() {
  rows.value = (repo.items || repo.OldSoldiersHomes || []).map(toRowModel);
}

async function fetch() {
  await repo.FetchOldSoldiersHomesData?.({ page: repo.page || 1, itemsPerPage: repo.itemsPerPage || 50, search: repo.Search || '' });
  hydrate();
}

function addBlankRow() {
  const newRow = toRowModel({});
  rows.value = [...rows.value, newRow];
  const per = repo.itemsPerPage || 50;
  const totalLocal = rows.value.length;
  repo.page = Math.max(1, Math.ceil(totalLocal / per));
}

/* Diff (persisted fields only) */
function diffDraft(item) {
  const d = item.draft || {};
  const changed = {};
  const keys = [
    'province','zone','district','village','specific_place','street_number','home_number','coordinates',
    'name','father_name','main_province',
    'old_job','now_activity','note',
    'resource_id','resource_code','resource_phone',
  ];
  for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
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

/* Save / Delete */
async function save(item) {
  item.loading = true;
  try {
    if (!item.id) {
      const fd = toFormDataSelective(item.draft);
      const created = await repo.CreateOldSoldiersHome(fd);
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
    const updated = await repo.UpdateOldSoldiersHome(item.id, fd);
    if (updated?.id) {
      const model = toRowModel(updated);
      model.idOrTmp = item.idOrTmp;
      Object.assign(item, model);
      item.draft = { ...model };
    }
  } catch (e) {
    console.error('Save error:', e);
  } finally {
    item.loading = false;
  }
}

async function del(item) {
  if (!item.id) {
    rows.value = rows.value.filter(r => r.idOrTmp !== item.idOrTmp);
    return;
  }
  if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name }))) return;

  try {
    await repo.DeleteOldSoldierHome(item.id);
    await fetch();
  } catch (e) {
    console.error('Delete error:', e);
  }
}

/* init */
onMounted(async () => {
  await Promise.allSettled([repo.GetResourceCode?.()]); // expects [{id, code_number, phone_number}]
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

/* layout */
.addr-group { display: grid; grid-template-columns: repeat(4, minmax(120px, 1fr)); gap: 2px; }
.text-group { display: grid; width: 140px; gap: 2px; }
.text-group-note { display: grid; width: 380px; gap: 2px; }

.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }

.tablebody { display: flex !important; gap: 2px !important; }

:deep(.v-field-label), :deep(.v-label), :deep(input::placeholder) {
  font-size: 12px !important; line-height: 1.2 !important; color: #555 !important;
}
:deep(.v-field__input) {
  min-height: 26px !important; padding: 2px 6px !important; font-size: 12px !important; line-height: 1.2 !important;
}

/* compact spacing */
.addr-group, .text-group, .text-group-note {
  margin: -16px !important; padding: -10px !important;
  row-gap: -6px !important; column-gap: 2px !important; gap: -10px !important;
}
</style>
