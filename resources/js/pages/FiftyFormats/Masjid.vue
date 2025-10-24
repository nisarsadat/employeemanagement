<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('مسجد')"
      :subtitle="t('ایجاد/ویرایش مسجد')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search (for the FilterableTable dataset) -->
    <v-row class="d-flex">
      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetchAll()"
          clearable
          @click:clear="() => { repo.Search = ''; fetchAll(); }"
        />
      </v-col>
    </v-row>

    <!-- Single-row editor (create/edit) — NO pagination/footers/add-row -->
    <v-form
      ref="editorForm"
      v-model="editorValid"
      lazy-validation
      :key="formKey"
      @submit.prevent="onSubmit(rows[0])"
    >
      <v-card class="tablebody" ref="editorCard">
        <v-data-table
          :headers="headers"
          :items="rows"
          :loading="repo.loading"
          item-key="idOrTmp"
          class="elevation-2 custom-table"
          :dir="isRtl ? 'rtl' : 'ltr'"
          density="comfortable"
          hide-default-footer
        >
          <!-- Identity -->
          <template #item.masjid_name="{ item }">
            <v-text-field
              v-model="item.draft.masjid_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام مسجد')"
              :rules="[rules.required]"
              hide-details
            />
          </template>

          <template #item.masjid_type="{ item }">
            <v-text-field
              v-model="item.draft.masjid_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع مسجد')"
              hide-details
            />
          </template>

          <!-- Responsible person -->
          <template #item.responsible_name="{ item }">
            <v-text-field
              v-model="item.draft.responsible_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مسوول')"
              hide-details
            />
          </template>

          <template #item.responsible_f_name="{ item }">
            <v-text-field
              v-model="item.draft.responsible_f_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدر مسوول')"
              hide-details
            />
          </template>

          <template #item.responsible_position="{ item }">
            <v-text-field
              v-model="item.draft.responsible_position"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه/موقف مسوول')"
              hide-details
            />
          </template>

          <template #item.responsible_mazhab="{ item }">
            <v-text-field
              v-model="item.draft.responsible_mazhab"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مذهب مسوول')"
              hide-details
            />
          </template>

          <template #item.responsible_old_job="{ item }">
            <v-text-field
              v-model="item.draft.responsible_old_job"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه قبلی مسوول')"
              hide-details
            />
          </template>

          <template #item.responsible_now_job="{ item }">
            <v-text-field
              v-model="item.draft.responsible_now_job"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه فعلی مسوول')"
              hide-details
            />
          </template>

          <template #item.responsible_tarfdary="{ item }">
            <v-text-field
              v-model="item.draft.responsible_tarfdary"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('طرفداری/وابستگی')"
              hide-details
            />
          </template>

          <!-- Responsible address (main) -->
          <template #item.responsible_main_province="{ item }">
            <div class="addr-group">
              <v-text-field v-model="item.draft.responsible_main_province" density="compact" variant="outlined" :label="t('ولایت اصلی مسوول')" hide-details />
              <v-text-field v-model="item.draft.responsible_main_district" density="compact" variant="outlined" :label="t('ولسوالی اصلی مسوول')" hide-details />
              <v-text-field v-model="item.draft.responsible_main_village" density="compact" variant="outlined" :label="t('قریه اصلی مسوول')" hide-details />
            </div>
          </template>

          <!-- Responsible address (now) -->
          <template #item.responsible_now_province="{ item }">
            <div class="addr-group">
              <v-text-field v-model="item.draft.responsible_now_province" density="compact" variant="outlined" :label="t('ولایت فعلی مسوول')" hide-details />
              <v-text-field v-model="item.draft.responsible_now_district" density="compact" variant="outlined" :label="t('ولسوالی فعلی مسوول')" hide-details />
              <v-text-field v-model="item.draft.responsible_now_village" density="compact" variant="outlined" :label="t('قریه فعلی مسوول')" hide-details />
            </div>
          </template>

          <!-- Masjid community & count -->
          <template #item.masjid_people_mazhab="{ item }">
            <v-text-field
              v-model="item.draft.masjid_people_mazhab"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مذهب مردم مسجد')"
              hide-details
            />
          </template>

          <template #item.masjid_homes_quantity="{ item }">
            <v-text-field
              v-model="item.draft.masjid_homes_quantity"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('تعداد خانه‌های مربوط مسجد')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <!-- Masjid address -->
          <template #item.masjid_province="{ item }">
            <div class="addr-group">
              <v-text-field v-model="item.draft.masjid_province" density="compact" variant="outlined" :label="t('ولایت مسجد')" hide-details />
              <v-text-field v-model="item.draft.masjid_zone" density="compact" variant="outlined" :label="t('زون مسجد')" hide-details />
              <v-text-field v-model="item.draft.masjid_district" density="compact" variant="outlined" :label="t('ولسوالی مسجد')" hide-details />
            </div>
          </template>

          <template #item.masjid_village="{ item }">
            <v-text-field
              v-model="item.draft.masjid_village"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('قریه مسجد')"
              hide-details
            />
          </template>

          <template #item.masjid_specific_place="{ item }">
            <v-text-field
              v-model="item.draft.masjid_specific_place"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('موقعیت مشخص مسجد')"
              hide-details
            />
          </template>

          <template #item.masjid_street_number="{ item }">
            <v-text-field
              v-model="item.draft.masjid_street_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره سرک مسجد')"
              hide-details
            />
          </template>

          <!-- Coordinates BEFORE note -->
          <template #item.masjid_coordinates="{ item }">
            <v-text-field
              v-model="item.draft.masjid_coordinates"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مختصات مسجد')"
              hide-details
            />
          </template>

          <!-- Resource -->
          <template #item.resource_id="{ item }">
            <v-text-field
              v-model="item.draft.resource_id"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('Resource ID')"
              hide-details
            />
          </template>

          <!-- Note -->
          <template #item.note="{ item }">
            <v-textarea
              v-model="item.draft.note"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('یادداشت')"
              hide-details
              auto-grow
              rows="2"
            />
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="flex items-center gap-1">
              <v-btn class="bg-primary" variant="outlined" :loading="item.loading" @click="onSubmit(item)">
                {{ t('ثبت دیتا') }}
              </v-btn>
              <v-btn class="bg-error" variant="outlined" @click="del(item)">
                {{ t('حذف دیتا') }}
              </v-btn>
              <v-btn color="warning" variant="outlined" @click="clearRow()">
                {{ t('پاک کردن ورودی‌ها') }}
              </v-btn>
            </div>
          </template>

          <template #no-data>
            <div class="py-8 text-medium-emphasis">
              {{ t('یک سطر ویرایش/ایجاد آماده است') }}
            </div>
          </template>
        </v-data-table>
      </v-card>
    </v-form>
  </v-container>

  <!-- FilterableTable -->
  <v-container fluid>
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
      :autoDetectDates="false"
      :dateKeys="[]"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    />
  </v-container>

  <!-- Error Snackbar -->
  <v-snackbar v-model="showError" color="error" timeout="4500">
    {{ errorMessage }}
    <template #actions>
      <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
    </template>
  </v-snackbar>

  <!-- Success Snackbar -->
  <v-snackbar v-model="showOk" color="primary" timeout="3000">
    {{ okMessage }}
  </v-snackbar>
</template>

<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import AppBar from '@/components/AppBar.vue';
import FilterableTable from '@/components/FilterableTable.vue';
import { useWakilanoRepository } from '@/stores/WakilanoRepository';

const { t, locale } = useI18n();
const drawer = ref(false);
const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => (isRtl.value = l === 'fa'));

const repo = useWakilanoRepository();

/* Single-row editor state */
const rows = ref([]);
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? 'tmp-1',
    loading: false,

    masjid_name: x.masjid_name ?? '',
    masjid_type: x.masjid_type ?? '',

    responsible_name: x.responsible_name ?? '',
    responsible_f_name: x.responsible_f_name ?? '',
    responsible_position: x.responsible_position ?? '',
    responsible_mazhab: x.responsible_mazhab ?? '',
    responsible_old_job: x.responsible_old_job ?? '',
    responsible_now_job: x.responsible_now_job ?? '',
    responsible_tarfdary: x.responsible_tarfdary ?? '',

    responsible_main_province: x.responsible_main_province ?? '',
    responsible_main_district: x.responsible_main_district ?? '',
    responsible_main_village: x.responsible_main_village ?? '',
    responsible_now_province: x.responsible_now_province ?? '',
    responsible_now_district: x.responsible_now_district ?? '',
    responsible_now_village: x.responsible_now_village ?? '',

    masjid_people_mazhab: x.masjid_people_mazhab ?? '',
    masjid_homes_quantity: x.masjid_homes_quantity ?? '',

    masjid_province: x.masjid_province ?? '',
    masjid_zone: x.masjid_zone ?? '',
    masjid_district: x.masjid_district ?? '',
    masjid_village: x.masjid_village ?? '',
    masjid_specific_place: x.masjid_specific_place ?? '',
    masjid_street_number: x.masjid_street_number ?? '',
    masjid_coordinates: x.masjid_coordinates ?? '',

    resource_id: x.resource_id ?? '',
    note: x.note ?? '',
  };
  return { ...base, draft: { ...base } };
}
function setBlankEditorRow() {
  rows.value = [toRowModel({})];
}

/* Dataset for FilterableTable */
const rowsFT = computed(() => repo.Masjids || []);
const filters = ref({});

/* Snackbars */
const showError = ref(false);
const errorMessage = ref('');
const showOk = ref(false);
const okMessage = ref('');
function showErrorMessage(msg) {
  errorMessage.value = msg || t('خطا رخ داد');
  showError.value = true;
}
function showOkMessage(msg) {
  okMessage.value = msg || t('موفقیت‌آمیز');
  showOk.value = true;
}

/* Table headers (editor) — masjid_coordinates before note */
const headers = computed(() => [
  { title: t('نام مسجد'), key: 'masjid_name' },
  { title: t('نوع مسجد'), key: 'masjid_type' },

  { title: t('مسوول'), key: 'responsible_name' },
  { title: t('نام پدر مسوول'), key: 'responsible_f_name' },
  { title: t('وظیفه/موقف مسوول'), key: 'responsible_position' },
  { title: t('مذهب مسوول'), key: 'responsible_mazhab' },
  { title: t('وظیفه قبلی مسوول'), key: 'responsible_old_job' },
  { title: t('وظیفه فعلی مسوول'), key: 'responsible_now_job' },
  { title: t('طرفداری/وابستگی'), key: 'responsible_tarfdary' },

  { title: t('آدرس اصلی مسوول'), key: 'responsible_main_province' },
  { title: t('آدرس فعلی مسوول'), key: 'responsible_now_province' },

  { title: t('مذهب مردم مسجد'), key: 'masjid_people_mazhab' },
  { title: t('تعداد خانه‌های مربوط مسجد'), key: 'masjid_homes_quantity' },

  { title: t('آدرس مسجد (۱)'), key: 'masjid_province' },
  { title: t('قریه مسجد'), key: 'masjid_village' },
  { title: t('موقعیت مشخص مسجد'), key: 'masjid_specific_place' },
  { title: t('شماره سرک مسجد'), key: 'masjid_street_number' },

  { title: t('مختصات مسجد'), key: 'masjid_coordinates' },  // <-- before note
  { title: t('Resource ID'), key: 'resource_id' },
  { title: t('یادداشت'), key: 'note' },

  { title: t('عمل'), key: 'actions', sortable: false },
]);

/* FilterableTable headers (list view) */
const headersFT = computed(() => [
  { title: t('نام مسجد'), key: 'masjid_name' },
  { title: t('نوع مسجد'), key: 'masjid_type' },

  { title: t('مسوول'), key: 'responsible_name' },
  { title: t('نام پدر مسوول'), key: 'responsible_f_name' },
  { title: t('وظیفه/موقف مسوول'), key: 'responsible_position' },
  { title: t('مذهب مسوول'), key: 'responsible_mazhab' },
  { title: t('وظیفه قبلی مسوول'), key: 'responsible_old_job' },
  { title: t('وظیفه فعلی مسوول'), key: 'responsible_now_job' },
  { title: t('طرفداری/وابستگی'), key: 'responsible_tarfdary' },

  { title: t('ولایت اصلی مسوول'), key: 'responsible_main_province' },
  { title: t('ولسوالی اصلی مسوول'), key: 'responsible_main_district' },
  { title: t('قریه اصلی مسوول'), key: 'responsible_main_village' },
  { title: t('ولایت فعلی مسوول'), key: 'responsible_now_province' },
  { title: t('ولسوالی فعلی مسوول'), key: 'responsible_now_district' },
  { title: t('قریه فعلی مسوول'), key: 'responsible_now_village' },

  { title: t('مذهب مردم مسجد'), key: 'masjid_people_mazhab' },
  { title: t('تعداد خانه‌های مربوط مسجد'), key: 'masjid_homes_quantity' },

  { title: t('ولایت مسجد'), key: 'masjid_province' },
  { title: t('زون مسجد'), key: 'masjid_zone' },
  { title: t('ولسوالی مسجد'), key: 'masjid_district' },
  { title: t('قریه مسجد'), key: 'masjid_village' },
  { title: t('موقعیت مشخص مسجد'), key: 'masjid_specific_place' },
  { title: t('شماره سرک مسجد'), key: 'masjid_street_number' },

  { title: t('مختصات مسجد'), key: 'masjid_coordinates' },
  { title: t('Resource ID'), key: 'resource_id' },
  { title: t('یادداشت'), key: 'note' },
]);

const topGroups = computed(() => [
  { title: t('مسجد'), keys: ['masjid_name', 'masjid_type'] },
  { title: t('مسوول مسجد'), keys: ['responsible_name','responsible_f_name','responsible_position','responsible_mazhab','responsible_old_job','responsible_now_job','responsible_tarfdary'] },
  { title: t('آدرس مسوول'), keys: ['responsible_main_province','responsible_main_district','responsible_main_village','responsible_now_province','responsible_now_district','responsible_now_village'] },
  { title: t('اجتماع مسجد'), keys: ['masjid_people_mazhab','masjid_homes_quantity'] },
  { title: t('آدرس مسجد'), keys: ['masjid_province','masjid_zone','masjid_district','masjid_village','masjid_specific_place','masjid_street_number','masjid_coordinates'] },
  { title: t('سایر'), keys: ['resource_id','note'] },
]);

const editorCard = ref(null);

async function startEdit(row) {
  try {
    const id = row?.id;
    if (!id) return;
    if (typeof repo.FetchMasjidData === 'function') {
      await repo.FetchMasjidData(id);
    } else if (typeof repo.FetchMasjidsData === 'function') {
      await repo.FetchMasjidsData({ page: 1, itemsPerPage: 100000 });
    }
    const single =
      (repo.Masjid && Array.isArray(repo.Masjid) ? repo.Masjid[0] : repo.Masjid) ||
      (repo.Masjids || []).find((r) => r.id === id) ||
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

/* Diff + payload helpers */
function diffDraft(item) {
  const d = item.draft || {};
  const changed = {};
  const keys = [
    'masjid_name','masjid_type',
    'responsible_name','responsible_f_name','responsible_position',
    'responsible_mazhab','responsible_old_job','responsible_now_job','responsible_tarfdary',
    'responsible_main_province','responsible_main_district','responsible_main_village',
    'responsible_now_province','responsible_now_district','responsible_now_village',
    'masjid_people_mazhab','masjid_homes_quantity',
    'masjid_province','masjid_zone','masjid_district','masjid_village',
    'masjid_specific_place','masjid_street_number','masjid_coordinates',
    'resource_id','note',
  ];
  for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
  return { changed };
}
function toFormDataSelective(obj) {
  const fd = new FormData();
  Object.entries(obj || {}).forEach(([k, v]) => {
    if (v === undefined || v === null) return;
    fd.append(k, v);
  });
  return fd;
}

/* Validation helpers */
const editorForm = ref(null);
const editorValid = ref(false);
const formKey = ref(0);
const rules = {
  required: (v) => !!(v && String(v).trim()) || t('این فیلد الزامی است'),
  nonNegativeInt: (v) => v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
};

async function validateForm() {
  const form = editorForm.value;
  if (!form?.validate) return { valid: true };
  const { valid } = await form.validate();
  if (!valid) await nextTick().then(scrollToFirstInvalid);
  return { valid };
}
function scrollToFirstInvalid() {
  const first =
    document.querySelector('.v-input--error, [aria-invalid="true"]') ||
    document.querySelector('.v-messages__message');
  if (first) {
    first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    const input = first.querySelector('input, textarea, .v-field__input');
    if (input && typeof input.focus === 'function') input.focus();
  }
}

/* Save / Delete / Clear */
async function onSubmit(item) {
  if (!item?.draft?.masjid_name?.trim()) {
    showErrorMessage(t('نام مسجد الزامی است'));
    return;
  }
  const v = await validateForm();
  if (!v.valid) {
    showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
    return;
  }
  await save(item);
}

async function save(item) {
  item.loading = true;
  try {
    if (!item.id) {
      const fd = toFormDataSelective(item.draft);
      const created = await repo.CreateMasjid?.(fd);
      if (created?.id) {
        showOkMessage(t('ریکارد ایجاد شد'));
        await fetchAll();
        setBlankEditorRow();
      } else {
        await fetchAll();
        setBlankEditorRow();
      }
      return;
    }
    const { changed } = diffDraft(item);
    if (Object.keys(changed).length === 0) {
      showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
      return;
    }
    const fd = toFormDataSelective(changed);
    const updated = await repo.UpdateMasjid?.(item.id, fd);
    if (updated?.id) {
      showOkMessage(t('ریکارد بروزرسانی شد'));
      await fetchAll();
      setBlankEditorRow();
    }
  } catch (e) {
    console.error('Save error:', e);
    showErrorMessage(t('خطا در ذخیره داده'));
  } finally {
    item.loading = false;
  }
}

async function del(item) {
  if (!item.id) {
    setBlankEditorRow();
    return;
  }
  if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.masjid_name || '' }))) return;
  try {
    await repo.DeleteMasjid?.(item.id);
    await fetchAll();
    showOkMessage(t('ریکارد حذف شد'));
    setBlankEditorRow();
  } catch (e) {
    console.error('Delete error:', e);
    showErrorMessage(t('خطا در حذف'));
  }
}

function clearRow() {
  setBlankEditorRow();
  showOkMessage(t('ورودی‌ها پاک شد'));
}

/* Load dataset for FilterableTable only */
async function fetchAll() {
  await Promise.allSettled([
    repo.FetchMasjidsData?.({ page: 1, itemsPerPage: 100000, search: repo.Search }),
  ]);
}

onMounted(async () => {
  await fetchAll();
  setBlankEditorRow();
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
  grid-template-columns: repeat(3, minmax(120px, 1fr));
  gap: 2px;
}
.text-group { display: grid; width: 140px; gap: 2px; }
.text-group-note { display: grid; width: 380px; gap: 2px; }
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
</style>
