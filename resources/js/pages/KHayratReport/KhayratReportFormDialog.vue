<template>
  <v-dialog v-model="model" max-width="900" scrollable :scrim="true">
    <v-card rounded="xl" class="dialog-card overflow-hidden" :dir="rtl ? 'rtl' : 'ltr'">
      <div class="dialog-header">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center gap-3">
            <div class="icon-badge">
              <v-icon icon="mdi-file-document" size="22" />
            </div>

            <div>
              <div class="text-h6 font-weight-bold mb-0">
                {{ isEdit ? t('ویرایش گزارش خیرات') : t('ثبت گزارش خیرات') }}
              </div>
              <div class="text-caption opacity-90">
                {{ isEdit ? t('اطلاعات گزارش را ویرایش کنید') : t('اطلاعات گزارش جدید را وارد کنید') }}
              </div>
            </div>
          </div>

          <v-btn icon="mdi-close" variant="text" size="small" class="rounded-lg" @click="close" />
        </div>
      </div>

      <v-divider />

      <v-card-text class="pa-5">
        <v-form ref="formRef" @submit.prevent="onSubmit">
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-information-outline" size="18" class="me-2" />
              {{ t('اطلاعات اصلی') }}
            </div>

            <div class="grid-2">
              <!-- ✅ Resource dropdown (code + name) -->
              <v-autocomplete
                v-model="draft.resource_id"
                :items="resourceOptions"
                :item-title="resourceTitle"
                item-value="id"
                :label="t('منبع')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-account-badge"
                clearable
                :loading="repo.lookups.loadingResources"
                :error="hasError('resource_id')"
                :error-messages="getErrorMessages('resource_id')"
                class="field"
                @update:search="onResourceSearch"
              />

              <!-- ✅ Office employee dropdown (code + name) -->
              <v-autocomplete
                v-model="draft.office_employee_id"
                :items="employeeOptions"
                :item-title="employeeTitle"
                item-value="id"
                :label="t('کارمند')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-account"
                clearable
                :loading="repo.lookups.loadingEmployees"
                :error="hasError('office_employee_id')"
                :error-messages="getErrorMessages('office_employee_id')"
                class="field"
                @update:search="onEmployeeSearch"
              />

              <!-- ✅ Zone/Hawza component (backend expects names) -->
              <div class="grid-span-2">
                <ZoneHawzaPicker
                  v-model:zone="draft.zone"
                  v-model:hawza="draft.hawza"
                  :disabled="false"
                  :colsZone="12"
                  :mdZone="6"
                  :colsHawza="12"
                  :mdHawza="6"
                />
              </div>

              <v-text-field
                v-model="draft.specefic_place"
                :label="t('محل مشخص')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-map"
                :error="hasError('specefic_place')"
                :error-messages="getErrorMessages('specefic_place')"
                class="field"
              />

              <!-- ✅ DateInput component -->
              <DateInput
                v-model="draft.saved_date"
                :label="t('تاریخ ثبت')"
                :error="hasError('saved_date')"
                :error-messages="getErrorMessages('saved_date')"
                class="field"
              />
            </div>
          </div>

          <div v-if="backendErrors.general?.length" class="mt-4">
            <v-alert type="error" variant="tonal" density="compact" class="rounded-lg">
              <div v-for="(m, i) in backendErrors.general" :key="i" class="text-caption">
                {{ m }}
              </div>
            </v-alert>
          </div>

          <div class="actions mt-6">
            <v-btn variant="text" class="rounded-lg px-4" color="grey-darken-1" @click="close">
              {{ t('انصراف') }}
            </v-btn>

            <v-btn
              color="primary"
              :loading="saving"
              type="submit"
              class="rounded-lg px-6"
              prepend-icon="mdi-check"
              elevation="1"
            >
              {{ isEdit ? t('ذخیره تغییرات') : t('ثبت دیتا') }}
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useKhayratReportRepository } from '@/stores/KhayratReportRepository';

// ✅ import your zone/hawza component
import ZoneHawzaPicker from '@/components/ZoneHawzaSelect.vue';

// ✅ import DateInput (adjust path if yours is different)
import DateInput from '@/components/DateInput.vue';

const { t, locale } = useI18n();
const rtl = computed(() => locale.value === 'fa');

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editId: { type: [Number, String, null], default: null },
});

const emit = defineEmits(['update:modelValue', 'saved']);
const repo = useKhayratReportRepository();

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
});

const isEdit = computed(() => props.editId !== null && props.editId !== undefined && props.editId !== '');

const formRef = ref(null);
const saving = ref(false);
const loadingEdit = ref(false);

const draft = reactive({
  resource_id: null,
  office_employee_id: null,
  zone: '',
  hawza: '',
  specefic_place: '',
  saved_date: '', // ✅ DateInput keeps string like 'YYYY-MM-DD'
});

const backendErrors = reactive({});

function clearErrors() {
  Object.keys(backendErrors).forEach((k) => delete backendErrors[k]);
}
function hasError(key) {
  return Array.isArray(backendErrors[key]) && backendErrors[key].length > 0;
}
function getErrorMessages(key) {
  return backendErrors[key] || [];
}
function resetDraft() {
  draft.resource_id = null;
  draft.office_employee_id = null;
  draft.zone = '';
  draft.hawza = '';
  draft.specefic_place = '';
  draft.saved_date = '';
}
function unwrapData(res) {
  return res?.data ?? res;
}

/* ==========================
 * dropdown options
 * ========================== */
const resourceOptions = computed(() => repo.lookups?.resources || []);
const employeeOptions = computed(() => repo.lookups?.employees || []);

/* ==========================
 * ✅ titles (code_number + name)
 * ========================== */
function resourceTitle(x) {
  const code = x?.code_number ?? '';
  const name = x?.name ?? '';
  const left = String(code || '').trim();
  const right = String(name || '').trim();
  if (left && right) return `${left} - ${right}`;
  return right || left || '';
}

function employeeTitle(x) {
  // ✅ office employee code_number
  const code = x?.code_number ?? '';
  const name = x?.name ?? '';
  const left = String(code || '').trim();
  const right = String(name || '').trim();
  if (left && right) return `${left} - ${right}`;
  return right || left || '';
}

/* ==========================
 * debounce search
 * ========================== */
let resTimer = null;
let empTimer = null;

function onResourceSearch(q) {
  clearTimeout(resTimer);
  resTimer = setTimeout(() => {
    repo.FetchResourcesLookup({ search: q || '' });
  }, 250);
}

function onEmployeeSearch(q) {
  clearTimeout(empTimer);
  empTimer = setTimeout(() => {
    repo.FetchOfficeEmployeesLookup({ search: q || '' });
  }, 250);
}

async function preloadLookups() {
  await Promise.allSettled([
    repo.FetchResourcesLookup({ search: '' }),
    repo.FetchOfficeEmployeesLookup({ search: '' }),
  ]);
}

async function loadForEdit() {
  if (!isEdit.value) return;

  clearErrors();
  loadingEdit.value = true;

  try {
    const res = await repo.FetchKhayratReportData(props.editId);
    const item = unwrapData(res);

    draft.resource_id = item?.resource_id ?? null;
    draft.office_employee_id = item?.office_employee_id ?? null;

    draft.zone = item?.zone ?? '';
    draft.hawza = item?.hawza ?? '';

    draft.specefic_place = item?.specefic_place ?? '';
    draft.saved_date = item?.saved_date ?? ''; // must be 'YYYY-MM-DD'
  } catch (e) {
    console.error('loadForEdit error', e);
    backendErrors.general = [t('خطا در دریافت معلومات گزارش')];
  } finally {
    loadingEdit.value = false;
  }
}

watch(
  () => model.value,
  async (open) => {
    if (!open) return;

    resetDraft();
    clearErrors();

    await preloadLookups();
    if (isEdit.value) await loadForEdit();
  }
);

watch(
  () => props.editId,
  async () => {
    if (!model.value) return;

    resetDraft();
    clearErrors();

    await preloadLookups();
    if (isEdit.value) await loadForEdit();
  }
);

function close() {
  model.value = false;
}

async function onSubmit() {
  clearErrors();

  const res = await formRef.value?.validate?.();
  if (res && res.valid === false) return;

  saving.value = true;
  try {
    const payload = {
      resource_id: draft.resource_id || null,
      office_employee_id: draft.office_employee_id || null,

      zone: draft.zone || null,
      hawza: draft.hawza || null,

      specefic_place: draft.specefic_place || null,
      saved_date: draft.saved_date || null,
    };

    if (isEdit.value) {
      await repo.UpdateKhayratReport(props.editId, payload);
    } else {
      await repo.CreateKhayratReport(payload);
    }

    emit('saved');
    close();
  } catch (e) {
    const errors = e?.response?.data?.errors;
    if (errors && typeof errors === 'object') {
      Object.assign(backendErrors, errors);
    } else {
      backendErrors.general = [t('خطا رخ داد. دوباره تلاش کنید.')];
    }
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
.field :deep(.v-field) {
  border-radius: 12px;
  background: #fff;
  transition: all 0.2s;
  border: 1px solid transparent;
  overflow: hidden;
}
.field :deep(.v-field__outline) {
  display: none;
}
.field :deep(.v-field)::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 12px;
  border: 1.5px solid #e0e0e0;
  pointer-events: none;
  transition: all 0.2s;
}
.field :deep(.v-field):hover::before {
  border-color: #bdbdbd;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.field :deep(.v-field--focused) {
  background: #fff;
  box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.15);
}
.field :deep(.v-field--focused)::before {
  border-color: #2196f3;
  border-width: 2px;
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.1);
}
.field :deep(.v-field--error) {
  background: #fff5f5;
}
.field :deep(.v-field--error)::before {
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.02);
}
.dialog-card {
  border: 1px solid rgba(0, 0, 0, 0.06);
  background: #fff;
}
.dialog-header {
  padding: 18px;
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.12), rgba(33, 150, 243, 0.06));
}
.icon-badge {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: rgba(33, 150, 243, 0.14);
  color: #2196f3;
  border: 1px solid rgba(33, 150, 243, 0.18);
}
.section {
  background: #fafafa;
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 16px;
  padding: 14px;
}
.section-title {
  display: flex;
  align-items: center;
  font-weight: 800;
  font-size: 13px;
  margin-bottom: 10px;
  color: rgba(0, 0, 0, 0.7);
}
.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.grid-span-2 {
  grid-column: 1 / -1;
}
@media (max-width: 700px) {
  .grid-2 {
    grid-template-columns: 1fr;
  }
}
.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
.v-btn {
  text-transform: none;
  font-weight: 700;
  letter-spacing: normal;
}
</style>
