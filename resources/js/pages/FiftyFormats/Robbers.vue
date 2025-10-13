<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('رهزنان')"
      :subtitle="t('اضافه کردن یک ریکارډ رهزن')"
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
          {{ t('اضافه کردن ریکارډ رهزن') }}
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
        @update:page="
          (p) => {
            repo.page = p;
            fetch();
          }
        "
        @update:items-per-page="
          (n) => {
            repo.itemsPerPage = n;
            fetch();
          }
        "
      >
        <!-- Basics -->
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
            <v-text-field
              v-model="item.draft.main_province"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولایت اصلی')"
            />
            <v-text-field
              v-model="item.draft.main_district"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولسوالی اصلی')"
            />
            <v-text-field
              v-model="item.draft.main_village"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('قریه/ناحیه اصلی')"
            />
          </div>
        </template>

        <!-- Now address -->
        <template #item.now_province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.now_province"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولایت فعلی')"
            />
            <v-text-field
              v-model="item.draft.now_zone"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('زون فعلی')"
            />
            <v-text-field
              v-model="item.draft.now_district"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولسوالی فعلی')"
            />
            <v-text-field
              v-model="item.draft.now_village"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('قریه/ناحیه فعلی')"
            />
          </div>
        </template>

        <!-- Current specific address -->
        <template #item.now_specific_place="{ item }">
          <v-text-field
            v-model="item.draft.now_specific_place"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('موقعیت مشخص فعلی')"
            hide-details
          />
        </template>
        <template #item.now_street_number="{ item }">
          <v-text-field
            v-model="item.draft.now_street_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('سرک نمبر فعلی')"
            hide-details
          />
        </template>
        <template #item.now_home_number="{ item }">
          <v-text-field
            v-model="item.draft.now_home_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نمبر خانه فعلی')"
            hide-details
          />
        </template>

        <!-- Work / contact -->
        <template #item.old_job="{ item }">
          <v-text-field
            v-model="item.draft.old_job"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('وظیفه قبلی')"
            hide-details
          />
        </template>
        <template #item.now_job="{ item }">
          <v-text-field
            v-model="item.draft.now_job"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('وظیفه فعلی')"
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
        <template #item.phone_number="{ item }">
          <v-text-field
            v-model="item.draft.phone_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره تماس')"
            hide-details
          />
        </template>
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
            <v-btn
              class="bg-primary"
              variant="outlined"
              @click="save(item)"
              :loading="item.loading"
            >
              {{ t('ثبت دیتا') }}
            </v-btn>
            <v-btn class="bg-error" variant="outlined" @click="del(item)">
              {{ t('حذف دیتا') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">
            {{ t('ریکارډ رهزن یافت نشد، لطفا اضافه کنید') }}
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

// Use the same repo pattern as your other pages.
// Expect these methods: FetchRobbersData, CreateRobber, UpdateRobber, DeleteRobber, GetResourceCode
import { useMawadRepository } from '@/stores/MawadRepository';
const repo = useMawadRepository();

const { t, locale } = useI18n();
const drawer = ref(false);
const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => (isRtl.value = l === 'fa'));

const rows = ref([]);
const filters = ref({});
let tmpId = 0;
const newTmpId = () => `tmp-${++tmpId}`;

/* Resource dropdown (fill phone+id) */
const resources = computed(() => repo.resourcecode || []);
function onResourceCodeChange(item) {
  const code = item.draft.resource_code;
  const found = resources.value.find((r) => r.code_number === code);
  item.draft.resource_phone = found?.phone_number ?? '';
  item.draft.resource_id = found?.id ?? null;
}

/* Headers (table) */
const headers = computed(() => [
  { title: t('نام مستعار'), key: 'nickname' },
  { title: t('نام'), key: 'name' },
  { title: t('نام پدر'), key: 'father_name' },

  { title: t('آدرس اصلی'), key: 'main_province' },
  { title: t('آدرس فعلی'), key: 'now_province' },

  { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
  { title: t('سرک # فعلی'), key: 'now_street_number' },
  { title: t('خانه # فعلی'), key: 'now_home_number' },

  { title: t('وظیفه قبلی'), key: 'old_job' },
  { title: t('وظیفه فعلی'), key: 'now_job' },
  { title: t('تمویل‌کننده'), key: 'supplier' },
  { title: t('شماره تماس'), key: 'phone_number' },
  { title: t('تاریخچه/فورم'), key: 'history_form' },

  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },

  { title: t('عمل'), key: 'actions', sortable: false },
]);

/* Headers (filter table) */
const headersFT = computed(() => [
  { title: t('نام مستعار'), key: 'nickname' },
  { title: t('نام'), key: 'name' },
  { title: t('نام پدر'), key: 'father_name' },

  { title: t('ولایت اصلی'), key: 'main_province' },
  { title: t('ولسوالی اصلی'), key: 'main_district' },
  { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

  { title: t('ولایت فعلی'), key: 'now_province' },
  { title: t('زون فعلی'), key: 'now_zone' },
  { title: t('ولسوالی فعلی'), key: 'now_district' },
  { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

  { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
  { title: t('سرک # فعلی'), key: 'now_street_number' },
  { title: t('خانه # فعلی'), key: 'now_home_number' },

  { title: t('وظیفه قبلی'), key: 'old_job' },
  { title: t('وظیفه فعلی'), key: 'now_job' },
  { title: t('تمویل‌کننده'), key: 'supplier' },
  { title: t('شماره تماس'), key: 'phone_number' },
  { title: t('تاریخچه/فورم'), key: 'history_form' },

  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },
]);

/* Top groups for FilterableTable */
const topGroups = computed(() => [
  { title: t('مشخصات'), keys: ['nickname', 'name', 'father_name'] },
  { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
  { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
  {
    title: t('منزل/آدرس فعلی'),
    keys: ['now_specific_place', 'now_street_number', 'now_home_number'],
  },
  {
    title: t('وظایف/تماس'),
    keys: ['old_job', 'now_job', 'supplier', 'phone_number', 'history_form'],
  },
  { title: t('ثبت'), keys: ['note'] },
  { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
]);

/* Map API -> row with draft */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? newTmpId(),
    loading: false,

    nickname: x.nickname ?? '',
    name: x.name ?? '',
    father_name: x.father_name ?? '',

    main_province: x.main_province ?? '',
    main_district: x.main_district ?? '',
    main_village: x.main_village ?? '',

    now_province: x.now_province ?? '',
    now_zone: x.now_zone ?? '',
    now_district: x.now_district ?? '',
    now_village: x.now_village ?? '',
    now_specific_place: x.now_specific_place ?? '',
    now_street_number: x.now_street_number ?? '',
    now_home_number: x.now_home_number ?? '',

    old_job: x.old_job ?? '',
    now_job: x.now_job ?? '',
    supplier: x.supplier ?? '',
    phone_number: x.phone_number ?? '',
    history_form: x.history_form ?? '',

    note: x.note ?? '',

    resource_code: x.resource_code ?? '',
    resource_phone: x.resource_phone ?? '',
    resource_id: x.resource_id ?? null,
  };
  return { ...base, draft: { ...base } };
}

function hydrate() {
  rows.value = (repo.Robbers || []).map(toRowModel);
}

async function fetch() {
  await repo.FetchRobbersData({
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
  const totalLocal = rows.value.length;
  repo.page = Math.max(1, Math.ceil(totalLocal / per));
  repo.totalItems = (repo.totalItems ?? 0) + 1; // keep paginator in sync
}

/* Diff & selective form-data */
function diffDraft(item) {
  const d = item.draft || {};
  const changed = {};
  const keys = [
    'nickname',
    'name',
    'father_name',
    'main_province',
    'main_district',
    'main_village',
    'now_province',
    'now_zone',
    'now_district',
    'now_village',
    'now_specific_place',
    'now_street_number',
    'now_home_number',
    'old_job',
    'now_job',
    'supplier',
    'phone_number',
    'history_form',
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
    if (v === undefined || v === null || v === '') return;
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
      const created = await repo.CreateRobber(fd);
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
    const updated = await repo.UpdateRobber(item.id, fd);
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
    await repo.DeleteRobber(item.id);
    await fetch();
  } catch (error) {
    console.error('Delete error:', error);
  }
}

/* init */
onMounted(async () => {
  await Promise.allSettled([repo.GetResourceCode?.()]);
  await fetch();
});
</script>

<style scoped>
table th {
  white-space: nowrap;
}
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

.flex {
  display: flex;
}
.items-center {
  align-items: center;
}
.gap-1 {
  gap: 4px;
}

.tablebody {
  display: flex !important;
  gap: 2px !important;
}

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
