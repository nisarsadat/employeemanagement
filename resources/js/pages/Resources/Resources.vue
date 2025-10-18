<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('منبع')"
      :subtitle="t('یک منبع اضافه کنید')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <!-- Search -->
    <v-row class="d-flex">
      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetchAll"
          clearable
          
          @click:clear="
            () => {
              repo.Search = '';
              fetchAll();
            }
          "
        />
      </v-col>
      <!-- <v-col cols="12" md="3" class="text-md-right">
        <v-btn color="primary" @click="setBlankEditorRow">
          {{ t('اضافه کردن منبع') }}
        </v-btn>
      </v-col> -->
    </v-row>

    <!-- Single-row editor (create/edit) — NO pagination/footers -->
    <v-card class="tablebody" ref="editorCard">
      <v-data-table
        :headers="headers"
        :items="rows"
        :loading="repo.loading"
        item-key="idOrTmp"
        :dir="isRtl ? 'rtl' : 'ltr'"
        class="elevation-2 custom-table"
        density="comfortable"
        hide-default-footer
      >
        <!-- Picture with view/edit overlay -->
        <template #item.picture="{ item }">
          <div class="pic-wrap">
            <input
              class="hidden-file"
              type="file"
              accept="image/*"
              :id="`up-${item.idOrTmp}`"
              @change="(e) => onPickFile(item, e.target.files?.[0] ?? null)"
            />
            <v-btn
              v-if="!item.previewUrl && !item.picture"
              color="primary"
              variant="outlined"
              icon
              @click="triggerFile(item)"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
            <div v-else class="pic-thumb" :title="t('گزینه ها')">
              <v-avatar size="32">
                <v-img :src="item.previewUrl || item.picture" :alt="t('تصویر')" cover />
              </v-avatar>
              <div class="pic-overlay">
                <v-btn
                  size="small"
                  color="secondary"
                  variant="flat"
                  @click.stop="triggerFile(item)"
                >
                  <v-icon start>mdi-pencil</v-icon>{{ t('ویرایش') }}
                </v-btn>
                <v-btn
                  size="small"
                  color="secondary"
                  variant="flat"
                  @click.stop="openPreview(item.previewUrl || item.picture)"
                >
                  <v-icon start>mdi-eye</v-icon>{{ t('نمایش') }}
                </v-btn>
              </div>
            </div>
          </div>
        </template>

        <!-- Introduction block -->
        <template #item.introduction="{ item }">
          <div class="introduction-group">
            <v-text-field v-model="item.draft.name" density="compact" variant="outlined" :label="t('نام')" hide-details />
            <v-text-field v-model="item.draft.father_name" density="compact" variant="outlined" :label="t('نام پدر')" hide-details />
            <v-text-field v-model="item.draft.grand_father_name" density="compact" variant="outlined" :label="t('نام پدرکلان')" hide-details />
            <v-text-field v-model="item.draft.nickname" density="compact" variant="outlined" :label="t('نام مستعار')" hide-details />
            <v-text-field v-model="item.draft.code_number" density="compact" variant="outlined" :label="t('کود نمبر')" hide-details />
            <v-text-field v-model="item.draft.tazkira" density="compact" variant="outlined" :label="t('تذکره نمبر')" hide-details />
          </div>
        </template>

        <!-- Main address -->
        <template #item.main_province="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.main_province" density="compact" variant="outlined" :label="t('ولایت اصلی')" hide-details />
            <v-text-field v-model="item.draft.main_district" density="compact" variant="outlined" :label="t('ولسوالی')" hide-details />
            <v-text-field v-model="item.draft.main_village" density="compact" variant="outlined" :label="t('قریه')" hide-details />
          </div>
        </template>

        <!-- Now address -->
        <template #item.now_province="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.now_province" density="compact" variant="outlined" :label="t('ولایت فعلی')" hide-details />
            <v-text-field v-model="item.draft.now_district" density="compact" variant="outlined" :label="t('ولسوالی فعلی')" hide-details />
            <v-text-field v-model="item.draft.now_village" density="compact" variant="outlined" :label="t('قریه فعلی')" hide-details />
          </div>
        </template>

        <!-- Provision place -->
        <template #item.provision_place="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.zone" density="compact" variant="outlined" :label="t('زون')" hide-details />
            <v-text-field v-model="item.draft.hawza" density="compact" variant="outlined" :label="t('حوزه')" hide-details />
            <v-text-field v-model="item.draft.specific_place" density="compact" variant="outlined" :label="t('موقعیت مشخص')" hide-details />
          </div>
        </template>

        <!-- NEW: coordenates -->
        <template #item.coordenates="{ item }">
          <v-text-field
            v-model="item.draft.coordenates"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کوردینات')"
            hide-details
          />
        </template>

        <!-- Simple fields -->
        <template #item.study_status="{ item }">
          <v-text-field v-model="item.draft.study_status" class="text-group" density="compact" :label="t('رتبه علمی')" variant="outlined" hide-details />
        </template>

        <template #item.activity="{ item }">
          <v-autocomplete
            v-model="item.draft.activity"
            :items="repo.activityOptions"
            class="text-group"
            density="compact"
            :label="t('فعالیت')"
            variant="outlined"
            hide-details
          />
        </template>

        <!-- Jobs -->
        <template #item.old_job="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.old_job" density="compact" variant="outlined" :label="t('وظیفه سابقه')" hide-details />
            <v-text-field v-model="item.draft.new_job" density="compact" variant="outlined" :label="t('وظیفه فعلی')" hide-details />
            <v-text-field v-model="item.draft.job_place" density="compact" variant="outlined" :label="t('محل وظیفه')" hide-details />
          </div>
        </template>

        <template #item.halat_madani="{ item }">
          <v-autocomplete
            v-model="item.draft.halat_madani"
            :items="repo.halatMadaniOptions"
            class="text-group"
            density="compact"
            :label="t('حالت مدنی')"
            variant="outlined"
            hide-details
          />
        </template>

        <!-- FK dropdowns -->
        <template #item.officeemployee_id="{ item }">
          <v-autocomplete
            v-model="item.draft.officeemployee_id"
            :items="repo.officeemployees"
            item-title="name"
            item-value="id"
            class="text-group"
            density="compact"
            :label="t('کود کارمند مربوطه')"
            variant="outlined"
            hide-details
            clearable
          />
        </template>

        <template #item.resedint_id="{ item }">
          <v-autocomplete
            v-model="item.draft.resedint_id"
            :items="repo.resedints"
            item-title="name"
            item-value="id"
            class="text-group"
            density="compact"
            :label="t('کود رزیدانت  مربوطه')"
            variant="outlined"
            hide-details
            clearable
          />
        </template>

        <template #item.phone_number="{ item }">
          <v-text-field v-model="item.draft.phone_number" class="text-group" density="compact" variant="outlined" :label="t('شماره تماس')" hide-details />
        </template>

        <template #item.age="{ item }">
          <v-text-field v-model="item.draft.age" class="text-group" density="compact" variant="outlined" :label="t('عمر')" hide-details />
        </template>

        <template #item.resource_saved_date="{ item }">
          <v-text-field v-model="item.draft.resource_saved_date" class="text-group" density="compact" variant="outlined" :label="t('تاریخ ثبت منبع')" hide-details />
        </template>

        <template #item.resource_activity_type="{ item }">
          <v-text-field v-model="item.draft.resource_activity_type" class="text-group" density="compact" variant="outlined" :label="t('نوع فعالیت منبع')" hide-details />
        </template>

        <template #item.resource_cover="{ item }">
          <v-text-field v-model="item.draft.resource_cover" class="text-group" density="compact" variant="outlined" :label="t('پوشش منبع')" hide-details />
        </template>

        <template #item.language="{ item }">
          <v-text-field v-model="item.draft.language" class="text-group" density="compact" variant="outlined" :label="t('زبان')" hide-details />
        </template>

        <template #item.nation="{ item }">
          <v-text-field v-model="item.draft.nation" class="text-group" density="compact" variant="outlined" :label="t('قوم')" hide-details />
        </template>

        <template #item.project="{ item }">
          <v-text-field v-model="item.draft.project" class="text-group" density="compact" variant="outlined" :label="t('پروجکت مربوطه')" hide-details />
        </template>

        <template #item.notes="{ item }">
          <v-text-field v-model="item.draft.notes" density="compact" class="text-group-note" variant="outlined" :label="t('نوت')" hide-details />
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
            <v-btn color="warning" variant="outlined" @click="clearEditor(item)">
              {{ t('پاک کردن ورودی‌ها') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">{{ t('یک سطر ویرایش/ایجاد آماده است') }}</div>
        </template>
      </v-data-table>

      <!-- Image preview dialog -->
      <v-dialog v-model="previewOpen" max-width="520">
        <v-card>
          <v-toolbar density="compact" color="primary" :title="t('پیش نمایش')" class="text-white" />
          <v-card-text class="d-flex justify-center">
            <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="480" max-height="420" cover />
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn color="primary" variant="flat" @click="previewOpen = false">
              {{ t('بستن') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card>

    <!-- Peek drawer -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title-key="name"
        image-key="picture"
        :fields="headers"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- Filterable table (print/export-friendly) with Edit + Peek -->
    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    />

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, onMounted, watch, computed, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import AppBar from '../../components/AppBar.vue';
import FilterableTable from '@/components/FilterableTable.vue';
import PeekButton from '@/components/PeekButton.vue';
import { useDatabaseRepository } from '../../stores/DatabaseRepository';

const repo = useDatabaseRepository();
const { t, locale } = useI18n();

/* UI / RTL */
const drawer = ref(false);
const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => (isRtl.value = l === 'fa'));

/* Snackbar + preview */
const previewOpen = ref(false);
const previewSrc = ref(null);
const showError = ref(false);
const errorMessage = ref('');
function openPreview(src) {
  previewSrc.value = src || null;
  if (previewSrc.value) previewOpen.value = true;
}
function showErrorMessage(msg) {
  errorMessage.value = msg;
  showError.value = true;
}

/* Single-row editor data */
const rows = ref([]); // always one editor row
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? 'tmp-1',
    picture: x.picture ?? null,
    previewUrl: x.picture ?? null,
    loading: false,

    name: x.name ?? '',
    father_name: x.father_name ?? '',
    grand_father_name: x.grand_father_name ?? '',
    nickname: x.nickname ?? '',
    tazkira: x.tazkira ?? '',
    code_number: x.code_number ?? '',

    main_province: x.main_province ?? '',
    main_district: x.main_district ?? '',
    main_village: x.main_village ?? '',

    now_province: x.now_province ?? '',
    now_district: x.now_district ?? '',
    now_village: x.now_village ?? '',

    old_job: x.old_job ?? '',
    new_job: x.new_job ?? '',
    job_place: x.job_place ?? '',

    zone: x.zone ?? '',
    hawza: x.hawza ?? '',
    specific_place: x.specific_place ?? '',

    activity: x.activity ?? '',
    halat_madani: x.halat_madani ?? '',
    study_status: x.study_status ?? '',

    phone_number: x.phone_number ?? '',
    age: x.age ?? '',

    resource_saved_date: x.resource_saved_date ?? '',
    resource_activity_type: x.resource_activity_type ?? '',
    resource_cover: x.resource_cover ?? '',
    language: x.language ?? '',
    nation: x.nation ?? '',
    project: x.project ?? '',

    resedint_id: x.resedint_id ?? null,
    officeemployee_id: x.officeemployee_id ?? null,

    coordenates: x.coordenates ?? '', // NEW field
    notes: x.notes ?? '',
  };
  return { ...base, draft: { ...base, picture: null } };
}
function setBlankEditorRow() {
  rows.value = [toRowModel({})];
}

/* Full dataset for FilterableTable */
const rowsFT = computed(() => repo.Resources || []);

/* Headers (editor table) */
const headers = computed(() => [
  { title: t('تصویر'), key: 'picture', width: 120, sortable: false },
  { title: t('معرفت'), key: 'introduction' },
  { title: t('سکونت اصلی'), key: 'main_province' },
  { title: t('سکونت فعلی'), key: 'now_province' },
  { title: t('محل تدارک'), key: 'provision_place' },
  { title: t('کوردینات'), key: 'coordenates' }, // NEW
  { title: t('فعالیت'), key: 'activity' },
  { title: t('حالت مدنی'), key: 'halat_madani' },
  { title: t('عمر'), key: 'age' },
  { title: t('رتبه علمی'), key: 'study_status' },
  { title: t('تاریخ ثبت منبع'), key: 'resource_saved_date' },
  { title: t('وظایف'), key: 'old_job' },
  { title: t('نوع فعالیت'), key: 'resource_activity_type' },
  { title: t('کود رزیدانت مربوطه'), key: 'resedint_id' },
  { title: t('کود کارمند مربوطه'), key: 'officeemployee_id' },
  { title: t('پوشش منبع'), key: 'resource_cover' },
  { title: t('زبان'), key: 'language' },
  { title: t('قوم'), key: 'nation' },
  { title: t('پروجکت مربوطه'), key: 'project' },
  { title: t('شماره تماس'), key: 'phone_number' },
  { title: t('ملاحظات'), key: 'notes' },
  { title: t('عمل'), key: 'actions', sortable: false },
]);

/* Headers for FilterableTable */
const headersFT = computed(() => [
  { title: t('نام'), key: 'name' },
  { title: t('نام پدر'), key: 'father_name' },
  { title: t('نام پدرکلان'), key: 'grand_father_name' },
  { title: t('نام مستعار'), key: 'nickname' },
  { title: t('کود نمبر'), key: 'code_number' },
  { title: t('تذکره نمبر'), key: 'tazkira' },

  { title: t('ولایت اصلی'), key: 'main_province' },
  { title: t('ولسوالی'), key: 'main_district' },
  { title: t('قریه'), key: 'main_village' },

  { title: t('ولایت فعلی'), key: 'now_province' },
  { title: t('ولسوالی فعلی'), key: 'now_district' },
  { title: t('قریه فعلی'), key: 'now_village' },

  { title: t('زون'), key: 'zone' },
  { title: t('حوزه'), key: 'hawza' },
  { title: t('موقعیت مشخص'), key: 'specific_place' },

  { title: t('وظیفه سابقه'), key: 'old_job' },
  { title: t('وظیفه فعلی'), key: 'new_job' },
  { title: t('محل وظیفه'), key: 'job_place' },

  { title: t('فعالیت'), key: 'activity' },
  { title: t('حالت مدنی'), key: 'halat_madani' },
  { title: t('رتبه علمی'), key: 'study_status' },

  { title: t('شماره تماس'), key: 'phone_number' },
  { title: t('عمر'), key: 'age' },

  { title: t('تاریخ ثبت منبع'), key: 'resource_saved_date' },
  { title: t('نوع فعالیت منبع'), key: 'resource_activity_type' },
  { title: t('پوشش منبع'), key: 'resource_cover' },
  { title: t('زبان'), key: 'language' },
  { title: t('قوم'), key: 'nation' },
  { title: t('پروجکت مربوطه'), key: 'project' },

  { title: t('کوردینات'), key: 'coordenates' }, // NEW
  { title: t('کود رزیدانت  مربوطه'), key: 'resedint_id' },
  { title: t('کود کارمند مربوطه'), key: 'officeemployee_id' },
  { title: t('ملاحظات'), key: 'notes' },
]);

/* top groups for FilterableTable */
const topGroups = computed(() => [
  {
    title: t('معرفت'),
    keys: ['name', 'father_name', 'grand_father_name', 'nickname', 'code_number', 'tazkira'],
  },
  { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
  { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
  { title: t('وظایف'), keys: ['old_job', 'new_job', 'job_place'] },
]);

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

/* Diff + payload helpers */
function diffDraft(item) {
  const d = item.draft || {};
  const changed = {};
  const keys = [
    'name',
    'father_name',
    'grand_father_name',
    'nickname',
    'code_number',
    'tazkira',
    'main_province',
    'main_district',
    'main_village',
    'now_province',
    'now_district',
    'now_village',
    'old_job',
    'new_job',
    'job_place',
    'zone',
    'hawza',
    'specific_place',
    'activity',
    'halat_madani',
    'age',
    'phone_number',
    'project',
    'nation',
    'language',
    'resource_cover',
    'resedint_id',
    'officeemployee_id',
    'resource_activity_type',
    'resource_saved_date',
    'study_status',
    'coordenates',  // NEW
    'notes',
  ];
  for (const k of keys) {
    if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
  }
  const imageChanged = !!(d.picture && typeof d.picture === 'object');
  if (imageChanged) {
    changed.picture = d.picture;
    changed.old_picture = item.picture;
  }
  return { changed, imageChanged };
}
function toFormDataSelective(obj) {
  const fd = new FormData();
  Object.entries(obj).forEach(([k, v]) => {
    if (v === undefined || v === null) return;
    fd.append(k, v);
  });
  return fd;
}

/* Save / Delete / Clear */
async function save(item) {
  if (!item?.draft?.name?.trim()) {
    showErrorMessage(t('نام الزامی است'));
    return;
  }

  item.loading = true;
  try {
    // CREATE
    if (!item.id) {
      const fd = toFormDataSelective(item.draft);
      const created = await repo.CreateResource(fd).catch((e) => {
        console.error('CreateResource error', e);
        return null;
      });
      if (created?.id) {
        showErrorMessage(t('منبع با موفقیت ایجاد شد'));
        await fetchAll();
        setBlankEditorRow();
      } else {
        await fetchAll();
        setBlankEditorRow();
      }
      return;
    }

    // UPDATE
    const { changed, imageChanged } = diffDraft(item);
    if (Object.keys(changed).length === 0) {
      showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
      return;
    }
    const fd = toFormDataSelective(changed);
    const updated = await repo.UpdateResource(item.id, fd).catch((e) => {
      console.error('UpdateResource error', e);
      return null;
    });
    if (updated?.id) {
      if (!imageChanged && item._blobUrl) {
        URL.revokeObjectURL(item._blobUrl);
        item._blobUrl = null;
      }
      showErrorMessage(t('منبع با موفقیت بروزرسانی شد'));
      await fetchAll();
      setBlankEditorRow();
    }
  } finally {
    item.loading = false;
  }
}

async function del(item) {
  if (!item.id) {
    setBlankEditorRow();
    return;
  }
  if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name }))) return;

  try {
    await repo.DeleteResource(item.id);
    await fetchAll();
    showErrorMessage(t('منبع حذف شد'));
    setBlankEditorRow();
  } catch (e) {
    console.error('DeleteResource error', e);
    showErrorMessage(t('خطا در حذف منبع'));
  }
}

function clearEditor(item) {
  try {
    if (item && item._blobUrl) {
      URL.revokeObjectURL(item._blobUrl);
      item._blobUrl = null;
    }
    const picEl = document.getElementById(`up-${item?.idOrTmp}`);
    if (picEl) picEl.value = '';
    setBlankEditorRow();
    showErrorMessage(t('ورودی‌ها پاک شد'));
  } catch (e) {
    console.error('clearEditor error:', e);
  }
}

/* Fetch ALL (for FilterableTable) */
async function fetchAll() {
  try {
    // support both signatures your repos used elsewhere
    if (typeof repo.FetchResourcesData === 'function') {
      await repo.FetchResourcesData({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search,
      });
    }
  } catch (e) {
    console.error('fetchAll error', e);
  }
}

/* Start edit from FilterableTable */
const editorCard = ref(null);
async function startEdit(row) {
  try {
    const id = row?.id;
    if (!id) return;
    if (typeof repo.FetchResourceData === 'function') {
      await repo.FetchResourceData(id);
    } else if (typeof repo.FetchResourcesData === 'function') {
      await repo.FetchResourcesData({ page: 1, itemsPerPage: 100000 });
    }
    const single =
      (repo.Resource && Array.isArray(repo.Resource) ? repo.Resource[0] : repo.Resource) ||
      (repo.Resources || []).find((r) => r.id === id) ||
      row;

    rows.value = [toRowModel(single)];
    await nextTick();
    const el = editorCard.value?.$el || editorCard.value;
    el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
  } catch (e) {
    console.error('startEdit error', e);
    showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
  }
}

/* ===== Peek state & methods ===== */
const expanded = ref(false);
const selectedId = ref(null);
const loading = ref(false);
const detail = ref(null);

async function fetchOne(id) {
  loading.value = true;
  try {
    if (typeof repo.FetchResourceData === 'function') {
      await repo.FetchResourceData(id);
      detail.value =
        (repo.Resource && Array.isArray(repo.Resource) ? repo.Resource[0] : repo.Resource) || {};
    } else {
      await fetchAll();
      const list = repo.Resources || [];
      detail.value = list.find((r) => r.id === id) || {};
    }
  } catch (e) {
    console.error('Fetch one resource error:', e);
    showErrorMessage(t('خطا در دریافت منبع'));
  } finally {
    loading.value = false;
  }
}
function openPeek(payload) {
  const row = payload?.raw ?? payload;
  if (!row?.id) return;
  selectedId.value = row.id;
  expanded.value = true;
  fetchOne(row.id);
}

/* Init */
onMounted(async () => {
  await Promise.allSettled([
    repo.GetOfficeEmployee?.(),
    repo.GetResedints?.(),
  ]);
  await fetchAll();
  setBlankEditorRow();
});
</script>

<style scoped>
.custom-table :deep(.v-data-table__thead th) {
  background: rgb(var(--v-theme-primary));
  color: #fff;
  font-weight: 400;
}

/* group layouts */
.addr-group {
  display: grid;
  grid-template-columns: repeat(3, minmax(250px, 1fr));
  gap: 2px;
}
.introduction-group {
  display: grid;
  grid-template-columns: repeat(6, minmax(250px, 1fr));
  gap: 2px;
}
.text-group {
  display: grid;
  width: 250px;
  gap: 2px;
}
.text-group-note {
  display: grid;
  width: 400px;
  gap: 2px;
}

.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }

.tablebody {
  display: flex !important;
  gap: 2px !important;
}

/* compact inputs */
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

/* picture cell + overlay */
.hidden-file {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}
.pic-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
}
.pic-thumb {
  position: relative;
  display: inline-flex;
  cursor: pointer;
}
.pic-overlay {
  position: absolute;
  inset: -6px -6px auto auto;
  display: none;
  gap: 6px;
  background: rgba(0, 0, 0, 0.55);
  padding: 6px;
  border-radius: 8px;
  transform: translateY(-40%);
  white-space: nowrap;
}
.pic-thumb:hover .pic-overlay {
  display: inline-flex;
}
.pic-overlay :deep(.v-btn) {
  color: #fff;
}
</style>
