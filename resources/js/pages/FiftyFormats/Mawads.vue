<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('مواد')"
      :subtitle="t('اضافه کردن یک ریکارډ مواد')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions>
        <v-btn icon />
      </template>
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
          {{ t('اضافه کردن ریکارډ مواد') }}
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
        @update:page="(p) => { repo.page = p; fetch(); }"
        @update:items-per-page="(n) => { repo.itemsPerPage = n; fetch(); }"
      >
        <!-- Simple fields -->
        <template #item.mawad_type="{ item }">
          <v-text-field
            v-model="item.draft.mawad_type"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نوع مواد')"
            hide-details
          />
        </template>
        <template #item.nickname="{ item }">
          <v-text-field
            v-model="item.draft.nickname"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام مستعار')"
            hide-details
          />
        </template>
        <template #item.name="{ item }">
          <v-text-field
            v-model="item.draft.name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام')"
            hide-details
          />
        </template>
        <template #item.father_name="{ item }">
          <v-text-field
            v-model="item.draft.father_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام پدر')"
            hide-details
          />
        </template>

        <!-- Main address -->
        <template #item.main_province="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.main_province" density="compact" variant="outlined" hide-details :label="t('ولایت اصلی')" />
            <v-text-field v-model="item.draft.main_district" density="compact" variant="outlined" hide-details :label="t('ولسوالی اصلی')" />
            <v-text-field v-model="item.draft.main_village" density="compact" variant="outlined" hide-details :label="t('قریه/ناحیه اصلی')" />
          </div>
        </template>

        <!-- Now address -->
        <template #item.now_province="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.now_province" density="compact" variant="outlined" hide-details :label="t('ولایت فعلی')" />
            <v-text-field v-model="item.draft.now_district" density="compact" variant="outlined" hide-details :label="t('ولسوالی فعلی')" />
            <v-text-field v-model="item.draft.now_village" density="compact" variant="outlined" hide-details :label="t('قریه/ناحیه فعلی')" />
          </div>
        </template>

        <!-- General location -->
        <template #item.province="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.province" density="compact" variant="outlined" hide-details :label="t('ولایت')" />
            <v-text-field v-model="item.draft.zone" density="compact" variant="outlined" hide-details :label="t('زون')" />
            <v-text-field v-model="item.draft.district" density="compact" variant="outlined" hide-details :label="t('ولسوالی')" />
            <v-text-field v-model="item.draft.village" density="compact" variant="outlined" hide-details :label="t('قریه/ناحیه')" />
          </div>
        </template>

        <!-- Place / coords / phone -->
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
        <template #item.mawad_sales_phone="{ item }">
          <v-text-field
            v-model="item.draft.mawad_sales_phone"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره فروش مواد')"
            hide-details
          />
        </template>

        <!-- History / supplier / note -->
        <template #item.history_form="{ item }">
          <v-text-field
            v-model="item.draft.history_form"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('تاریخچه/فورم')"
            hide-details
          />
        </template>
        <template #item.supplier="{ item }">
          <v-text-field
            v-model="item.draft.supplier"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تمویل‌کننده')"
            hide-details
          />
        </template>
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

        <!-- Resource code (autocomplete) + phone (readonly) -->
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
            {{ t('ریکارډ مواد یافت نشد، لطفا اضافه کنید') }}
          </div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>

  <!-- FilterableTable -->
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
import { useMawadRepository } from '@/stores/MawadRepository';

const { t, locale } = useI18n();
const drawer = ref(false);
const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => (isRtl.value = (l === 'fa')));

const repo = useMawadRepository();

const rows = ref([]);
const filters = ref({});
let tmpId = 0;
const newTmpId = () => `tmp-${++tmpId}`;

/* Resources dropdown (fill phone+id, same as Fasad page) */
/* Expecting repo.resourcecode = [{ id, code_number, phone_number }, ...] */
const resources = computed(() => repo.resourcecode || []);
function onResourceCodeChange(item) {
  const code = item.draft.resource_code;
  const found = resources.value.find((r) => r.code_number === code);
  item.draft.resource_phone = found?.phone_number ?? '';
  item.draft.resource_id = found?.id ?? null; // hidden post field
}

/* Headers (table + filter) */
const headers = computed(() => [
  { title: t('نوع مواد'), key: 'mawad_type' },
  { title: t('نام مستعار'), key: 'nickname' },
  { title: t('نام'), key: 'name' },
  { title: t('نام پدر'), key: 'father_name' },

  { title: t('آدرس اصلی'), key: 'main_province' },
  { title: t('آدرس فعلی'), key: 'now_province' },
  { title: t('موقعیت عمومی'), key: 'province' },

  { title: t('موقعیت مشخص'), key: 'specific_place' },
  { title: t('مختصات'), key: 'coordinates' },
  { title: t('شماره فروش مواد'), key: 'mawad_sales_phone' },

  { title: t('تاریخچه/فورم'), key: 'history_form' },
  { title: t('تمویل‌کننده'), key: 'supplier' },
  { title: t('یادداشت'), key: 'note' },

  // IMPORTANT: show code here (not id), exactly like Fasad page
  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },

  { title: t('عمل'), key: 'actions', sortable: false },
]);

const headersFT = computed(() => [
  { title: t('نوع مواد'), key: 'mawad_type' },
  { title: t('نام مستعار'), key: 'nickname' },
  { title: t('نام'), key: 'name' },
  { title: t('نام پدر'), key: 'father_name' },

  { title: t('ولایت اصلی'), key: 'main_province' },
  { title: t('ولسوالی اصلی'), key: 'main_district' },
  { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

  { title: t('ولایت فعلی'), key: 'now_province' },
  { title: t('ولسوالی فعلی'), key: 'now_district' },
  { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

  { title: t('ولایت'), key: 'province' },
  { title: t('زون'), key: 'zone' },
  { title: t('ولسوالی'), key: 'district' },
  { title: t('قریه/ناحیه'), key: 'village' },

  { title: t('موقعیت مشخص'), key: 'specific_place' },
  { title: t('مختصات'), key: 'coordinates' },
  { title: t('شماره فروش مواد'), key: 'mawad_sales_phone' },

  { title: t('تاریخچه/فورم'), key: 'history_form' },
  { title: t('تمویل‌کننده'), key: 'supplier' },
  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },
]);

const topGroups = computed(() => [
  { title: t('مشخصات'), keys: ['mawad_type', 'nickname', 'name', 'father_name'] },
  { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
  { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
  { title: t('موقعیت عمومی'), keys: ['province', 'zone', 'district', 'village'] },
  { title: t('موقعیت/تماس'), keys: ['specific_place', 'coordinates', 'mawad_sales_phone'] },
  { title: t('اطلاعات دیگر'), keys: ['history_form', 'supplier', 'note'] },
  { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
]);

/* Map API -> row with draft (same pattern as Fasad) */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? newTmpId(),
    loading: false,

    mawad_type: x.mawad_type ?? '',
    nickname: x.nickname ?? '',
    name: x.name ?? '',
    father_name: x.father_name ?? '',

    main_province: x.main_province ?? '',
    main_district: x.main_district ?? '',
    main_village: x.main_village ?? '',

    now_province: x.now_province ?? '',
    now_district: x.now_district ?? '',
    now_village: x.now_village ?? '',

    province: x.province ?? '',
    zone: x.zone ?? '',
    district: x.district ?? '',
    village: x.village ?? '',

    specific_place: x.specific_place ?? '',
    coordinates: x.coordinates ?? '',
    mawad_sales_phone: x.mawad_sales_phone ?? '',

    history_form: x.history_form ?? '',
    supplier: x.supplier ?? '',
    note: x.note ?? '',

    // Show code + phone, keep id hidden for backend
    resource_code: x.resource_code ?? '',
    resource_phone: x.resource_phone ?? '',
    resource_id: x.resource_id ?? null,
  };
  return { ...base, draft: { ...base } };
}

function hydrate() {
  // NOTE: adjust to your store key. If your store uses `repo.mawads`, keep it.
  const list = repo.mawads || repo.Mawads || [];
  rows.value = list.map(toRowModel);
}

/* In case API row misses resource_phone, fill it from resources by matching resource_code */
function backfillPhones() {
  if (!rows.value?.length || !resources.value?.length) return;
  const mapByCode = new Map(resources.value.map(r => [r.code_number, r.phone_number]));
  rows.value.forEach(r => {
    const phone = mapByCode.get(r.draft.resource_code) ?? '';
    if (!r.resource_phone) r.resource_phone = phone;
    if (!r.draft.resource_phone) r.draft.resource_phone = phone;
  });
}

async function fetch() {
  await repo.FetchMawadsData({
    page: repo.page || 1,
    itemsPerPage: repo.itemsPerPage || 50,
    search: repo.Search || ''
  });
  hydrate();
  backfillPhones();
}

function addBlankRow() {
  const newRow = toRowModel({});
  rows.value = [...rows.value, newRow];
  const per = repo.itemsPerPage || 50;
  const totalLocal = rows.value.length;
  repo.page = Math.max(1, Math.ceil(totalLocal / per));
}

/* Diff — mirror the Fasad keys */
function diffDraft(item) {
  const d = item.draft || {};
  const changed = {};
  const keys = [
    'mawad_type',
    'nickname',
    'name',
    'father_name',
    'main_province',
    'main_district',
    'main_village',
    'now_province',
    'now_district',
    'now_village',
    'province',
    'zone',
    'district',
    'village',
    'specific_place',
    'coordinates',
    'mawad_sales_phone',
    'history_form',
    'supplier',
    'note',
    'resource_code',
    'resource_phone',
    'resource_id',
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

/* Save / Delete (same behavior as Fasad) */
async function save(item) {
  item.loading = true;
  try {
    if (!item.id) {
      const fd = toFormDataSelective(item.draft);
      const created = await repo.CreateMawad(fd);
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
    const updated = await repo.UpdateMawad(item.id, fd);
    if (updated?.id) {
      const model = toRowModel(updated);
      model.idOrTmp = item.idOrTmp;
      Object.assign(item, model);
      item.draft = { ...model };
    }
  } catch (error) {
    console.error('Save error:', error);
  } finally {
    item.loading = false;
  }
}

async function del(item) {
  if (!item.id) {
    rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp);
    return;
  }
  if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name }))) return;

  try {
    await repo.DeleteMawad(item.id);
    await fetch();
  } catch (error) {
    console.error('Delete error:', error);
  }
}

/* init */
onMounted(async () => {
  // must load resources first so code->phone/id mapping works in table immediately
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

/* layout */
.addr-group {
  display: grid;
  grid-template-columns: repeat(4, minmax(120px, 1fr));
  gap: 2px;
}
.text-group {
  display: grid;
  width: 140px;
  gap: 2px;
}
.text-group-note {
  display: grid;
  width: 380px;
  gap: 2px;
}

.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }

.tablebody { display: flex !important; gap: 2px !important; }

:deep(.v-field-label),
:deep(.v-label),
:deep(input::placeholder) {
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

/* compact spacing */
.addr-group,
.text-group,
.text-group-note {
  margin: -16px !important;
  padding: -10px !important;
  row-gap: -6px !important;
  column-gap: 2px !important;
  gap: -10px !important;
}
</style>
