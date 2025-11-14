<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('شرکت‌های فروش موتر')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید شرکت فروش موتر')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search -->
    <v-row class="d-flex">
      <v-col>
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetchAll"
          clearable
          @click:clear="() => { repo.Search = ''; fetchAll(); }"
        />
      </v-col>
    </v-row>

    <!-- Single-row validated editor (NO pagination/footer) -->
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
          density="comfortable"
          :dir="isRtl ? 'rtl' : 'ltr'"
          hide-default-footer
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
              :rules="[rules.required]"
              :error="hasError(item, 'car_sales_company_name')"
              :error-messages="getErrorMessages(item, 'car_sales_company_name')"
              required
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
              :rules="[rules.number]"
              :error="hasError(item, 'responsible_phone')"
              :error-messages="getErrorMessages(item, 'responsible_phone')"
            />
          </template>

          <!-- Address (grouped) -->
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
          <template #item.Province_addr="{ item }">
            <v-text-field
              v-model="item.draft.Province_addr"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('ولایت مربوطه')"
              hide-details
              :error="hasError(item, 'Province_addr')"
              :error-messages="getErrorMessages(item, 'Province_addr')"
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

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="flex items-center gap-1">
              <v-btn class="bg-primary" variant="outlined" type="submit" :loading="item.loading">
                {{ t('ثبت دیتا') }}
              </v-btn>
              <v-btn class="bg-error" variant="outlined" @click="del(item)">
                {{ t('حذف') }}
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
      </v-card>
    </v-form>

    <!-- Peek + FilterableTable -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title-key="car_sales_company_name"
        :fields="headers"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        @fetch="fetchOne"
      />
    </v-container>

    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      class="mb-20 mx-2"
      :enableEdit="true"
      :enablePeek="true"
      @edit="startEdit"
      @peek="openPeek"
    />

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import AppBar from '@/components/AppBar.vue';
import PeekButton from '@/components/PeekButton.vue';
import FilterableTable from '@/components/FilterableTable.vue';
import { useSchoolRepository } from '@/stores/SchoolRepository';

const { t, locale } = useI18n();
const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => (isRtl.value = l === 'fa'));
const drawer = ref(false);

const repo = useSchoolRepository();

/* ===== Editor form + snackbar ===== */
const editorForm = ref(null);
const editorValid = ref(false);
const formKey = ref(0);
const showError = ref(false);
const errorMessage = ref('');
const editorCard = ref(null);
function showErrorMessage(message) {
  errorMessage.value = message;
  showError.value = true;
}

/* ===== Errors per row ===== */
const itemErrors = ref({});
function setItemErrors(item, errors) {
  itemErrors.value[item.idOrTmp] = errors;
}
function clearItemErrors(item) {
  if (itemErrors.value[item.idOrTmp]) delete itemErrors.value[item.idOrTmp];
}
function hasError(item, field) {
  return !!(itemErrors.value[item.idOrTmp] && itemErrors.value[item.idOrTmp][field]);
}
function getErrorMessages(item, field) {
  return itemErrors.value[item.idOrTmp]?.[field] ?? [];
}

/* ===== Rules ===== */
const rules = {
  required: (v) => !!v || t('این فیلد الزامی است'),
  selectRequired: (label) => (v) => !!v || t('{label} الزامی است', { label }),
  number: (v) => !v || /^[0-9]+$/.test(v) || t('فقط اعداد مجاز است'),
};

/* ===== Single editor row ===== */
const rows = ref([]);
function setBlankEditorRow() {
  rows.value = [toRowModel({})];
}

/* ===== ALL data for FilterableTable ===== */
const rowsFT = computed(() => repo.CarSalesCompanies || []);

/* ===== Headers (editor table) ===== */
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
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },
  { title: t('عملیات'), key: 'actions', sortable: false },
]);

/* ===== Headers for FilterableTable ===== */
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
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

  { title: t('شماره منبع'), key: 'resource_phone' },
]);

/* ===== Top filter groups (Fasad style) ===== */
const topGroups = computed(() => [
  { title: t('شرکت'), keys: ['car_sales_company_name'] },
  { title: t('مسئول'), keys: ['responsible_name', 'responsible_f_name', 'responsible_position', 'responsible_phone'] },
  { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village'] },
  { title: t('موقعیت/مختصات'), keys: ['specific_place', 'coordinates'] },
  { title: t('یادداشت'), keys: ['note'] },
  { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
]);

/* ===== Resources dropdown -> code/phone + set resource_id ===== */
const resources = computed(() => repo.resourcecode || []);
function onResourceCodeChange(item) {
  const code = item.draft.resource_code;
  const found = resources.value.find((r) => r.code_number === code);
  item.draft.resource_phone = found?.phone_number ?? '';
  item.draft.resource_id = found?.id ?? null;
}

/* ===== Row model & diff ===== */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? 'tmp-1',
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

      Province_addr: x.Province_addr ?? '',
    note: x.note ?? '',

    resource_code: x.resource_code ?? '',
    resource_phone: x.resource_phone ?? '',
    resource_id: x.resource_id ?? null,
  };
  return { ...base, draft: { ...base } };
}

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
      'Province_addr',
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

async function hardResetFormValidation() {
  editorValid.value = false;
  itemErrors.value = {};
  await nextTick();
  editorForm.value?.resetValidation?.();
  formKey.value++;
  await nextTick();
}

/* ===== Save / Delete ===== */
async function save(item) {
  if (!item.draft?.car_sales_company_name?.trim()) {
    showErrorMessage(t('نام شرکت الزامی است'));
    return;
  }
  item.loading = true;
  clearItemErrors(item);

  try {
    // CREATE
    if (!item.id) {
      const fd = toFormDataSelective(item.draft);
      const created = await repo.CreateCarSalesCompany?.(fd);
      if (created && created.id) {
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      } else {
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
      return;
    }

    // UPDATE
    const changed = diffDraft(item);
    if (Object.keys(changed).length === 0) {
      showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
      return;
    }
    const fd = toFormDataSelective(changed);
    const updated = await repo.UpdateCarSalesCompany?.(item.id, fd);
    if (updated && updated.id) {
      await fetchAll();
      setBlankEditorRow();
      editorForm.value?.resetValidation?.();
    }
  } catch (error) {
    console.error('Save error:', error);
    if (error?.response?.data?.errors) {
      setItemErrors(item, error.response.data.errors);
      showErrorMessage(t('لطفا خطاهای فرم را بررسی کنید'));
    } else {
      showErrorMessage(t('خطا در ذخیره اطلاعات') + ': ' + (error?.message || t('خطای ناشناخته')));
    }
  } finally {
    item.loading = false;
  }
}

// VALIDATED SUBMIT
async function onSubmit(item) {
  const form = editorForm.value;
  if (!form) return;
  const { valid } = await form.validate();
  if (!valid) {
    showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
    await nextTick();
    scrollToFirstInvalid();
    return;
  }
  await save(item);
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

async function del(item) {
  if (!item.id) {
    setBlankEditorRow();
    return;
  }
  if (!confirm(t('آیا مطمئن هستید؟'))) return;
  try {
    await repo.DeleteCarSalesCompany?.(item.id);
    await fetchAll();
    setBlankEditorRow();
  } catch (e) {
    console.error('Delete error:', e);
    showErrorMessage(t('خطا در حذف') + ': ' + (e?.message || t('خطای ناشناخته')));
  }
}

function clearEditor(item) {
  try {
    if (item && item.idOrTmp && itemErrors.value[item.idOrTmp]) {
      delete itemErrors.value[item.idOrTmp];
    }
    setBlankEditorRow();
    showErrorMessage(t('ورودی‌ها پاک شد'));
  } catch (e) {
    console.error('clearEditor error:', e);
  }
}

/* ===== Peek / Fetch ===== */
const expanded = ref(false);
const selectedId = ref(null);
const loading = ref(false);
const detail = ref(null);

async function fetchOne(id) {
  loading.value = true;
  try {
    await repo.FetchCarSalesCompanyData?.(id);
    detail.value = repo.CarSalesCompany || [];
  } catch (error) {
    console.error('Fetch one error:', error);
    showErrorMessage(t('خطا در دریافت اطلاعات'));
  } finally {
    loading.value = false;
  }
}
function openPeek(payload) {
  const row = payload?.raw ?? payload;
  if (!row?.id) return;
  expanded.value = true;
  fetchOne(row.id);
}

/* ===== Fetch ALL (no pagination) ===== */
async function fetchAll() {
  try {
    if (typeof repo.FetchCarSalesCompaniesData === 'function') {
      await repo.FetchCarSalesCompaniesData({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search || '',
      });
      return;
    }
    await repo.FetchCarSalesCompaniesData?.({
      page: 1,
      itemsPerPage: 100000,
      search: repo.Search || '',
    });
  } catch (e) {
    console.error('fetchAll error', e);
  }
}

/* ===== Start edit from FilterableTable ===== */
async function startEdit(row) {
  try {
    const id = row?.id;
    if (!id) return;
    await repo.FetchCarSalesCompanyData?.(id);
    const single = repo.CarSalesCompany && Array.isArray(repo.CarSalesCompany)
      ? repo.CarSalesCompany[0]
      : repo.CarSalesCompany || row;
    rows.value = [toRowModel(single || row)];
    await nextTick();
    const el = editorCard.value?.$el || editorCard.value;
    el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
  } catch (e) {
    console.error('startEdit error', e);
    showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
  }
}

/* ===== Init ===== */
onMounted(async () => {
  await Promise.allSettled([repo.GetResourceCode?.()]);
  await fetchAll();
  setBlankEditorRow();
});
</script>

<style scoped>
/* Prevent header wrap */
table th { white-space: nowrap; }

/* Colored table header */
.custom-table :deep(.v-data-table__thead th) {
  background: rgb(var(--v-theme-primary));
  color: #fff;
  font-weight: 400;
}

/* Layout helpers (Fasad style) */
.tablebody { display: flex !important; gap: 2px !important; }
.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }

/* Grouped inputs */
.addr-group {
  display: grid;
  grid-template-columns: repeat(4, minmax(120px, 1fr));
  gap: 2px;
}
.text-group { display: grid; width: 140px; gap: 2px; }
.text-group-note { display: grid; width: 380px; gap: 2px; }

/* Compact inputs + labels */
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

/* Tight spacing inside grouped blocks */
.addr-group, .text-group, .text-group-note {
  margin: -16px !important;
  padding: -10px !important;
  gap: -10px !important;
}
</style>
