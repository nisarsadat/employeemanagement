<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('مجرمین گم شده')"
      :subtitle="t('اضافه کردن یک ریکارډ مجرم گم شده')"
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
        <!-- Picture -->
        <template #item.picture="{ item }">
          <div class="pic-wrap">
            <input
              class="hidden-file"
              type="file"
              accept="image/*"
              :id="`up-${item.idOrTmp}`"
              @change="e => onPickFile(item, e.target.files?.[0] ?? null)"
            />
            <v-btn
              v-if="!item.previewUrl && !item.picture"
              size="small" color="primary" variant="outlined" icon
              @click="triggerFile(item)"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
            <div v-else class="pic-thumb">
              <v-avatar size="32">
                <v-img :src="item.previewUrl || item.picture" :alt="t('عکس')" cover />
              </v-avatar>
              <div class="pic-overlay">
                <v-btn size="x-small" color="primary" variant="flat" @click.stop="triggerFile(item)">
                  <v-icon start>mdi-pencil</v-icon>{{ t('ویرایش') }}
                </v-btn>
              </div>
            </div>
          </div>
        </template>

        <!-- Simple fields -->
        <template #item.nickname="{ item }">
          <v-text-field v-model="item.draft.nickname" class="text-group" density="compact" variant="outlined" :label="t('نام مستعار')" hide-details />
        </template>
        <template #item.name="{ item }">
          <v-text-field v-model="item.draft.name" class="text-group" density="compact" variant="outlined" :label="t('نام')" hide-details />
        </template>
        <template #item.father_name="{ item }">
          <v-text-field v-model="item.draft.father_name" class="text-group" density="compact" variant="outlined" :label="t('نام پدر')" hide-details />
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
        <template #item.now_specific_place="{ item }">
          <v-text-field v-model="item.draft.now_specific_place" class="text-group-note" density="compact" variant="outlined" :label="t('موقعیت فعلی مشخص')" hide-details />
        </template>
        <template #item.now_street_number="{ item }">
          <v-text-field v-model="item.draft.now_street_number" class="text-group" density="compact" variant="outlined" :label="t('سرک نمبر فعلی')" hide-details />
        </template>
        <template #item.now_home_number="{ item }">
          <v-text-field v-model="item.draft.now_home_number" class="text-group" density="compact" variant="outlined" :label="t('نمبر خانه فعلی')" hide-details />
        </template>

        <!-- General -->
        <template #item.province="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.province" density="compact" variant="outlined" hide-details :label="t('ولایت')" />
            <v-text-field v-model="item.draft.zone" density="compact" variant="outlined" hide-details :label="t('زون')" />
            <v-text-field v-model="item.draft.district" density="compact" variant="outlined" hide-details :label="t('ولسوالی')" />
          </div>
        </template>

        <!-- Other info -->
        <template #item.old_job="{ item }">
          <v-text-field v-model="item.draft.old_job" class="text-group" density="compact" variant="outlined" :label="t('وظیفه قبلی')" hide-details />
        </template>
        <template #item.jorm="{ item }">
          <v-text-field v-model="item.draft.jorm" class="text-group" density="compact" variant="outlined" :label="t('جرم')" hide-details />
        </template>
        <template #item.history_jorm="{ item }">
          <v-text-field v-model="item.draft.history_jorm" class="text-group-note" density="compact" variant="outlined" :label="t('تاریخچه جرم')" hide-details />
        </template>
        <template #item.lost_reason="{ item }">
          <v-text-field v-model="item.draft.lost_reason" class="text-group-note" density="compact" variant="outlined" :label="t('دلیل مفقودی')" hide-details />
        </template>
        <template #item.phone_number="{ item }">
          <v-text-field v-model="item.draft.phone_number" class="text-group" density="compact" variant="outlined" :label="t('شماره تماس')" hide-details />
        </template>
        <template #item.note="{ item }">
          <v-text-field v-model="item.draft.note" class="text-group-note" density="compact" variant="outlined" :label="t('یادداشت')" hide-details />
        </template>

        <!-- Resource: code (autocomplete) + phone (readonly) like Fasad -->
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

// ADJUST if your store name differs:
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
  item.draft.resource_id = found?.id ?? null; // hidden id to submit
}

/* Headers (table + filter) */
const headers = computed(() => [
  { title: t('تصویر'), key: 'picture', width: 120, sortable: false },
  { title: t('نام مستعار'), key: 'nickname' },
  { title: t('نام'), key: 'name' },
  { title: t('نام پدر'), key: 'father_name' },

  { title: t('آدرس اصلی'), key: 'main_province' },
  { title: t('آدرس فعلی'), key: 'now_province' },
  { title: t('موقعیت فعلی مشخص'), key: 'now_specific_place' },
  { title: t('سرک فعلی'), key: 'now_street_number' },
  { title: t('خانه فعلی'), key: 'now_home_number' },

  { title: t('موقعیت عمومی'), key: 'province' },

  { title: t('وظیفه قبلی'), key: 'old_job' },
  { title: t('جرم'), key: 'jorm' },
  { title: t('تاریخچه جرم'), key: 'history_jorm' },
  { title: t('دلیل مفقودی'), key: 'lost_reason' },
  { title: t('شماره تماس'), key: 'phone_number' },

  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },

  { title: t('عمل'), key: 'actions', sortable: false },
]);

const headersFT = computed(() => [
  { title: t('نام'), key: 'name' },
  { title: t('نام مستعار'), key: 'nickname' },
  { title: t('نام پدر'), key: 'father_name' },

  { title: t('ولایت اصلی'), key: 'main_province' },
  { title: t('ولسوالی اصلی'), key: 'main_district' },
  { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

  { title: t('ولایت فعلی'), key: 'now_province' },
  { title: t('ولسوالی فعلی'), key: 'now_district' },
  { title: t('قریه/ناحیه فعلی'), key: 'now_village' },
  { title: t('موقعیت فعلی مشخص'), key: 'now_specific_place' },
  { title: t('سرک فعلی'), key: 'now_street_number' },
  { title: t('خانه فعلی'), key: 'now_home_number' },

  { title: t('ولایت'), key: 'province' },
  { title: t('زون'), key: 'zone' },
  { title: t('ولسوالی'), key: 'district' },

  { title: t('وظیفه قبلی'), key: 'old_job' },
  { title: t('جرم'), key: 'jorm' },
  { title: t('تاریخچه جرم'), key: 'history_jorm' },
  { title: t('دلیل مفقودی'), key: 'lost_reason' },
  { title: t('شماره تماس'), key: 'phone_number' },

  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },
]);

const topGroups = computed(() => [
  { title: t('مشخصات'), keys: ['nickname', 'name', 'father_name'] },
  { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
  { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village', 'now_specific_place', 'now_street_number', 'now_home_number'] },
  { title: t('موقعیت عمومی'), keys: ['province', 'zone', 'district'] },
  { title: t('اطلاعات'), keys: ['old_job', 'jorm', 'history_jorm', 'lost_reason', 'phone_number'] },
  { title: t('دیگر'), keys: ['note'] },
  { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
]);

/* Map API -> row with draft (like Fasad) */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? newTmpId(),
    picture: x.picture ?? null,
    previewUrl: x.picture ?? null,
    loading: false,

    nickname: x.nickname ?? '',
    name: x.name ?? '',
    father_name: x.father_name ?? '',

    main_province: x.main_province ?? '',
    main_district: x.main_district ?? '',
    main_village: x.main_village ?? '',

    now_province: x.now_province ?? '',
    now_district: x.now_district ?? '',
    now_village: x.now_village ?? '',
    now_specific_place: x.now_specific_place ?? '',
    now_street_number: x.now_street_number ?? '',
    now_home_number: x.now_home_number ?? '',

    province: x.province ?? '',
    zone: x.zone ?? '',
    district: x.district ?? '',

    old_job: x.old_job ?? '',
    jorm: x.jorm ?? '',
    history_jorm: x.history_jorm ?? '',
    lost_reason: x.lost_reason ?? '',
    phone_number: x.phone_number ?? '',

    note: x.note ?? '',

    resource_code: x.resource_code ?? '',
    resource_phone: x.resource_phone ?? '',
    resource_id: x.resource_id ?? null,
  };
  return { ...base, draft: { ...base, picture: null } };
}

function hydrate() {
  rows.value = (repo.items || repo.LostMujremeens || []).map(toRowModel);
}

async function fetch() {
  await repo.FetchLostMujremeenssData?.({ page: repo.page || 1, itemsPerPage: repo.itemsPerPage || 50, search: repo.Search || '' });
  hydrate();
}

function addBlankRow() {
  const newRow = toRowModel({});
  rows.value = [...rows.value, newRow];
  const per = repo.itemsPerPage || 50;
  const totalLocal = rows.value.length;
  repo.page = Math.max(1, Math.ceil(totalLocal / per));
}

/* Image picker */
function triggerFile(item) {
  const el = document.getElementById(`up-${item.idOrTmp}`);
  if (el) el.click();
}
function onPickFile(item, file) {
  item.draft.picture = file || null;
  if (file) {
    if (item._blobUrl) URL.revokeObjectURL(item._blobUrl);
    item._blobUrl = URL.createObjectURL(file);
    item.previewUrl = item._blobUrl;
  } else {
    item.previewUrl = item.picture || null;
  }
}

/* Diff (only persisted fields + picture) */
function diffDraft(item) {
  const d = item.draft || {};
  const changed = {};
  const keys = [
    'nickname','name','father_name',
    'main_province','main_district','main_village',
    'now_province','now_district','now_village','now_specific_place','now_street_number','now_home_number',
    'province','zone','district',
    'old_job','jorm','history_jorm','lost_reason','phone_number',
    'note',
    'resource_code','resource_phone','resource_id',
  ];
  for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];

  const imageChanged = !!(d.picture && typeof d.picture === 'object');
  if (imageChanged) {
    changed.picture = d.picture;
    changed.old_picture = item.picture;
  }
  return { changed, imageChanged };
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
      const created = await repo.CreateLostMujremeen(fd);
      if (created?.id) {
        const model = toRowModel(created);
        model.idOrTmp = item.idOrTmp;
        Object.assign(item, model);
        item.draft = { ...model, picture: null };
      } else {
        await fetch();
      }
      return;
    }

    const { changed, imageChanged } = diffDraft(item);
    if (Object.keys(changed).length === 0) return;

    const fd = toFormDataSelective(changed);
    const updated = await repo.UpdateLostMujremeen(item.id, fd);
    if (updated?.id) {
      const model = toRowModel(updated);
      model.idOrTmp = item.idOrTmp;
      if (!imageChanged && item._blobUrl) {
        URL.revokeObjectURL(item._blobUrl);
        item._blobUrl = null;
      }
      Object.assign(item, model);
      item.draft = { ...model, picture: null };
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
    await repo.DeleteLostMujremeen(item.id);
    await fetch();
  } catch (e) {
    console.error('Delete error:', e);
  }
}

/* init */
onMounted(async () => {
  await Promise.allSettled([repo.GetResourceCode?.()]); // fills repo.resourcecode [{id, code_number, phone_number}]
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
  grid-template-columns: repeat(3, minmax(120px, 1fr));
  gap: 2px;
}
.text-group { display: grid; width: 140px; gap: 2px; }
.text-group-note { display: grid; width: 380px; gap: 2px; }

.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }

.hidden-file { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
.pic-wrap { position: relative; display: inline-flex; align-items: center; }
.pic-thumb { display: inline-flex; cursor: pointer; position: relative; }
.pic-overlay {
  position: absolute; inset: -6px -6px auto auto; display: none; gap: 6px;
  background: rgba(0,0,0,.55); padding: 6px; border-radius: 8px; transform: translateY(-40%); white-space: nowrap;
}
.pic-thumb:hover .pic-overlay { display: inline-flex; }
.pic-overlay :deep(.v-btn) { color: #fff; }

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
2