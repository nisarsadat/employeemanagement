<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح منزل سرباز سابق') : t('اضافه کردن یک ریکارډ منزل سرباز سابق')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ موجود منزل سرباز سابق')
          : t('ثبت یک ریکارډ جدید منزل سرباز سابق در سیستم')
      "
    >
      <template #actions>
        <v-btn
          variant="text"
          :prepend-icon="isRtl ? 'mdi-arrow-right' : 'mdi-arrow-left'"
          class="text-none"
          @click="goBack"
        >
          {{ t('برگشت') }}
        </v-btn>
      </template>
    </AppBar>

    <!-- FORM -->
    <v-container class="mt-4">
      <v-form
        ref="formRef"
        v-model="formValid"
        lazy-validation
        @submit.prevent="onSubmit"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <v-card class="simple-form-card" elevation="1">
          <!-- موقعیت -->
          <div class="section-header">
            <span class="section-title">{{ t('موقعیت منزل') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.province"
                :label="t('ولایت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('province')"
                :error-messages="getErrorMessages('province')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.zone"
                :label="t('زون')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('zone')"
                :error-messages="getErrorMessages('zone')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.district"
                :label="t('ولسوالی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('district')"
                :error-messages="getErrorMessages('district')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.village"
                :label="t('قریه/ناحیه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('village')"
                :error-messages="getErrorMessages('village')"
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.specific_place"
                :label="t('موقعیت مشخص')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('specific_place')"
                :error-messages="getErrorMessages('specific_place')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.street_number"
                :label="t('شماره سرک')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('street_number')"
                :error-messages="getErrorMessages('street_number')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.home_number"
                :label="t('شماره خانه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('home_number')"
                :error-messages="getErrorMessages('home_number')"
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                hint="مثال: 34.1234, 69.1234"
                persistent-hint
                :rules="[rules.coordinatesOptional]"
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- هویت -->
          <div class="section-header">
            <span class="section-title">{{ t('مشخصات سرباز سابق') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.name"
                :label="t('نام *')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.required]"
                :error="hasError('name')"
                :error-messages="getErrorMessages('name')"
                required
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.father_name"
                :label="t('نام پدر')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('father_name')"
                :error-messages="getErrorMessages('father_name')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_province"
                :label="t('ولایت اصلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('main_province')"
                :error-messages="getErrorMessages('main_province')"
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.phone]"
                maxlength="10"
                :error="hasError('phone_number')"
                :error-messages="getErrorMessages('phone_number')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- کار / فعالیت / یادداشت -->
          <div class="section-header">
            <span class="section-title">{{ t('کار، فعالیت و یادداشت') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.old_job"
                :label="t('وظیفه قبلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('old_job')"
                :error-messages="getErrorMessages('old_job')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_activity"
                :label="t('فعالیت فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('now_activity')"
                :error-messages="getErrorMessages('now_activity')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('Province_addr')"
                :error-messages="getErrorMessages('Province_addr')"
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.note"
                :label="t('یادداشت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :error="hasError('note')"
                :error-messages="getErrorMessages('note')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- منبع -->
          <div class="section-header">
            <span class="section-title">{{ t('معلومات منبع') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                clearable
                @update:model-value="onResourceCodeChange"
                :error="hasError('resource_code')"
                :error-messages="getErrorMessages('resource_code')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.resource_phone"
                :label="t('شماره منبع')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                readonly
              />
            </v-col>
          </v-row>

          <!-- ACTIONS -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2 flex-wrap">
              <!-- ✅ Simple Export (NO image / NO app headers) -->
              <v-tooltip :text="t('PDF ساده (بدون عکس)')" location="top">
                <template #activator="{ props }">
                  <v-btn
                    v-bind="props"
                    variant="tonal"
                    color="primary"
                    class="text-none rounded-pill"
                    prepend-icon="mdi-file-pdf-box"
                    @click="downloadPdf"
                  >
                    {{ t('PDF') }}
                  </v-btn>
                </template>
              </v-tooltip>

              <v-tooltip :text="t('Word ساده (بدون عکس)')" location="top">
                <template #activator="{ props }">
                  <v-btn
                    v-bind="props"
                    variant="tonal"
                    color="primary"
                    class="text-none rounded-pill"
                    prepend-icon="mdi-file-word-box"
                    @click="downloadWord"
                  >
                    {{ t('Word') }}
                  </v-btn>
                </template>
              </v-tooltip>

              <v-btn
                color="primary"
                variant="flat"
                class="text-none rounded-pill primary-action-btn"
                :loading="saving"
                type="submit"
                :prepend-icon="isEdit ? 'mdi-content-save-edit-outline' : 'mdi-plus-circle-outline'"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت منزل سرباز سابق') }}
              </v-btn>

              <v-btn
                color="warning"
                variant="outlined"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-broom"
                @click="resetForm"
              >
                {{ t('پاک کردن فرم') }}
              </v-btn>
            </v-col>
          </v-row>
        </v-card>
      </v-form>
    </v-container>

    <!-- ❗ Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>

    <!-- ✅ Success Snackbar -->
    <v-snackbar v-model="showSuccess" color="success" timeout="4000">
      {{ successMessage }}
      <template #actions>
        <v-btn variant="text" @click="showSuccess = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';

import AppBar from '@/components/AppBar.vue';
import { useMawadRepository } from '@/stores/MawadRepository';

const { t, locale } = useI18n();
const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => (isRtl.value = l === 'fa'));

const route = useRoute();
const router = useRouter();
const repo = useMawadRepository();

const isEdit = computed(() => !!route.params.id);

/* FORM */
const formRef = ref(null);
const formValid = ref(false);
const saving = ref(false);

/* BACKEND ERRORS */
const backendErrors = ref({});
function setBackendErrors(errors) {
  backendErrors.value = errors || {};
}
function clearBackendErrors() {
  backendErrors.value = {};
}
function hasError(field) {
  return !!backendErrors.value?.[field];
}
function getErrorMessages(field) {
  return backendErrors.value?.[field] ?? [];
}

/* MODEL */
const blankForm = () => ({
  id: null,

  province: '',
  zone: '',
  district: '',
  village: '',
  specific_place: '',
  street_number: '',
  home_number: '',
  coordinates: '',

  name: '',
  father_name: '',
  main_province: '',
  phone_number: '',

  old_job: '',
  now_activity: '',
  Province_addr: '',
  note: '',

  resource_code: '',
  resource_phone: '',
  resource_id: null,
});

const form = ref(blankForm());

/* RULES */
const rules = {
  required: (v) => !!String(v ?? '').trim() || t('این فیلد الزامی است'),
  phone: (v) => {
    const s = String(v ?? '').trim();
    if (!s) return true;
    if (!/^[0-9]+$/.test(s)) return t('فقط اعداد مجاز است');
    if (s.length !== 10) return t('شماره تماس باید دقیق ۱۰ رقم باشد');
    return true;
  },
  coordinatesOptional: (v) => {
    const s = String(v ?? '').trim();
    if (!s) return true;
    if (!/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/.test(s)) return t('مختصات درست نیست');
    return true;
  },
};

/* SNACKBAR */
const showError = ref(false);
const errorMessage = ref('');
const showSuccess = ref(false);
const successMessage = ref('');

function showErrorMessage(msg) {
  errorMessage.value = msg;
  showError.value = true;
}
function showSuccessMsg(msg) {
  successMessage.value = msg;
  showSuccess.value = true;
}

/* RESOURCES */
const resources = computed(() => repo.resourcecode || []);
function onResourceCodeChange(code) {
  const found = resources.value.find((r) => r.code_number === code);
  form.value.resource_phone = found?.phone_number ?? '';
  form.value.resource_id = found?.id ?? null;
}

/* MAP FROM API */
function mapFromApi(payload = {}) {
  const x = payload.data ?? payload;
  const b = blankForm();

  b.id = x.id ?? null;

  b.province = x.province ?? '';
  b.zone = x.zone ?? '';
  b.district = x.district ?? '';
  b.village = x.village ?? '';
  b.specific_place = x.specific_place ?? '';
  b.street_number = x.street_number ?? '';
  b.home_number = x.home_number ?? '';
  b.coordinates = x.coordinates ?? '';

  b.name = x.name ?? '';
  b.father_name = x.father_name ?? '';
  b.main_province = x.main_province ?? '';
  b.phone_number = x.phone_number ?? '';

  b.old_job = x.old_job ?? '';
  b.now_activity = x.now_activity ?? '';
  b.Province_addr = x.Province_addr ?? '';
  b.note = x.note ?? '';

  const r = x.resource || {};
  b.resource_code = x.resource_code ?? r.code_number ?? '';
  b.resource_phone = x.resource_phone ?? r.phone_number ?? '';
  b.resource_id = x.resource_id ?? r.id ?? null;

  return b;
}

/* LOAD */
async function loadRecord() {
  const id = route.params.id;
  if (!id) {
    form.value = blankForm();
    return;
  }
  try {
    await repo.FetchOldSoldiersHomeData?.(id);
    const single = Array.isArray(repo.OldSoldiersHome)
      ? repo.OldSoldiersHome[0]
      : repo.OldSoldiersHome || {};
    form.value = { ...mapFromApi(single) };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات منزل سرباز سابق'));
  }
}

/* FORMDATA */
function toFormData(obj) {
  const fd = new FormData();
  Object.entries(obj || {}).forEach(([k, v]) => {
    if (v === undefined || v === null) return;
    fd.append(k, v);
  });
  return fd;
}

/* SCROLL */
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

/* SUBMIT */
async function onSubmit() {
  clearBackendErrors();

  if (!formRef.value) return;
  const { valid } = await formRef.value.validate();

  if (!valid) {
    showErrorMessage(t('لطفاً فیلدهای الزامی را درست پُر کنید'));
    await nextTick();
    scrollToFirstInvalid();
    return;
  }

  if (!form.value.name?.trim()) {
    showErrorMessage(t('نام الزامی است'));
    return;
  }

  saving.value = true;
  try {
    const fd = toFormData({ ...form.value });

    if (isEdit.value && form.value.id) {
      const updated = await repo.UpdateOldSoldiersHome?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
        goBack();
        return;
      }
    } else {
      const created = await repo.CreateOldSoldiersHome?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMsg(t('منزل سرباز سابق موفقانه ثبت شد'));
        goBack();
        return;
      }
    }

    showErrorMessage(t('خطا در ذخیره معلومات'));
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در ذخیره معلومات'));
  } finally {
    saving.value = false;
  }
}

/* RESET & NAV */
function resetForm() {
  if (isEdit.value) {
    loadRecord();
  } else {
    form.value = blankForm();
  }

  clearBackendErrors();
  formRef.value?.resetValidation?.();
  showSuccessMsg(t('فرم پاک شد'));
}

function goBack() {
  router.push('/app/oldsoldiershomes');
}

/* ✅ SIMPLE EXPORT (NO image / NO app headers) */
function escapeHtml(s) {
  return String(s ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function buildExportHtml() {
  const rid = form.value.id ? `#${form.value.id}` : t('بدون شماره');

  const rows = [
    [t('ولایت'), form.value.province],
    [t('زون'), form.value.zone],
    [t('ولسوالی'), form.value.district],
    [t('قریه/ناحیه'), form.value.village],
    [t('موقعیت مشخص'), form.value.specific_place],
    [t('شماره سرک'), form.value.street_number],
    [t('شماره خانه'), form.value.home_number],
    [t('مختصات'), form.value.coordinates],

    [t('نام'), form.value.name],
    [t('نام پدر'), form.value.father_name],
    [t('ولایت اصلی'), form.value.main_province],
    [t('شماره تماس'), form.value.phone_number],

    [t('وظیفه قبلی'), form.value.old_job],
    [t('فعالیت فعلی'), form.value.now_activity],
    [t('ولایت مربوطه'), form.value.Province_addr],
    [t('یادداشت'), form.value.note],

    [t('کود منبع'), form.value.resource_code],
    [t('شماره منبع'), form.value.resource_phone],
  ];

  return `
<!doctype html>
<html dir="rtl" lang="fa">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${escapeHtml(t('فورم منزل سرباز سابق'))}</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 18px; color: #111; }
    h2 { margin: 0 0 12px; font-size: 18px; }
    .meta { font-size: 12px; color: #444; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    td { border: 1px solid #ddd; padding: 8px; vertical-align: top; font-size: 12px; }
    td.label { width: 32%; font-weight: 700; background: #fafafa; }
    .sig { margin-top: 28px; display: flex; gap: 16px; }
    .sig .box { flex: 1; border-top: 1px solid #333; padding-top: 6px; font-size: 12px; }
    @media print { body { padding: 0; } }
  </style>
</head>
<body>
  <h2>${escapeHtml(t('فورم منزل سرباز سابق'))}</h2>
  <div class="meta">${escapeHtml(t('شماره ریکارډ'))}: ${escapeHtml(rid)}</div>

  <table>
    ${rows
      .map(([k, v]) => {
        const val = String(v ?? '').trim();
        return `
          <tr>
            <td class="label">${escapeHtml(k)}</td>
            <td>${escapeHtml(val || '-')}</td>
          </tr>
        `;
      })
      .join('')}
  </table>

  <div class="sig">
    <div class="box">${escapeHtml(t('امضا مسئول'))}</div>
    <div class="box">${escapeHtml(t('امضا ثبت کننده'))}</div>
    <div class="box">${escapeHtml(t('تاریخ'))}: ____________</div>
  </div>
</body>
</html>
  `.trim();
}

function downloadPdf() {
  const html = buildExportHtml();
  const w = window.open('', '_blank');
  if (!w) return;
  w.document.open();
  w.document.write(html);
  w.document.close();
  w.focus();
  setTimeout(() => w.print(), 250);
}

function downloadWord() {
  const html = buildExportHtml();
  const blob = new Blob([html], { type: 'application/msword' });
  const url = URL.createObjectURL(blob);

  const namePart = form.value.id
    ? `old_soldier_home_${form.value.id}`
    : 'old_soldier_home_blank';

  const a = document.createElement('a');
  a.href = url;
  a.download = `${namePart}.doc`;
  document.body.appendChild(a);
  a.click();
  a.remove();

  setTimeout(() => URL.revokeObjectURL(url), 2000);
}

/* INIT */
onMounted(async () => {
  await repo.GetResourceCode?.();
  if (isEdit.value) {
    await loadRecord();
  }
});
</script>

<style scoped>
.hidden-file {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

.simple-form-card {
  padding: 16px 16px 20px;
  border-radius: 12px;
}

.section-header {
  display: flex;
  align-items: center;
  padding-block: 2px;
}

.section-title {
  font-size: 0.9rem;
  font-weight: 600;
  opacity: 0.8;
}

.ga-2 {
  gap: 8px;
}

:deep(.v-field--variant-outlined .v-field__input) {
  padding-top: 4px;
  padding-bottom: 4px;
  min-height: 36px;
}

.primary-action-btn {
  padding-inline: 18px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(25, 118, 210, 0.25);
}

.secondary-action-btn {
  padding-inline: 14px;
  font-weight: 500;
}
</style>
