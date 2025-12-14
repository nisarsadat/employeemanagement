<!-- src/views/wakilano/WakilanoForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش وکیل') : t('ایجاد وکیل جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ وکیل موجود')
          : t('ثبت یک ریکارډ وکیل جدید در سیستم')
      "
    >
      <template #actions>
        <v-btn
          variant="text"
          prepend-icon="mdi-arrow-left"
          class="text-none rounded-pill"
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
          <!-- Identity -->
          <div class="section-header">
            <span class="section-title">
              {{ isEdit ? t('ویرایش معلومات وکیل') : t('ایجاد ریکارډ وکیل') }}
            </span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.name"
                :label="t('نام *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('name')"
                :error-messages="getErrorMessages('name')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.f_name"
                :label="t('نام پدر')"
                variant="outlined"
                density="compact"
                :error="hasError('f_name')"
                :error-messages="getErrorMessages('f_name')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.grand_father_name"
                :label="t('نام پدرکلان')"
                variant="outlined"
                density="compact"
                :error="hasError('grand_father_name')"
                :error-messages="getErrorMessages('grand_father_name')"
              />
            </v-col>
          </v-row>

          <!-- Main Address -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('آدرس اصلی') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_province"
                :label="t('ولایت اصلی')"
                variant="outlined"
                density="compact"
                :error="hasError('main_province')"
                :error-messages="getErrorMessages('main_province')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_district"
                :label="t('ولسوالی اصلی')"
                variant="outlined"
                density="compact"
                :error="hasError('main_district')"
                :error-messages="getErrorMessages('main_district')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_village"
                :label="t('قریه اصلی')"
                variant="outlined"
                density="compact"
                :error="hasError('main_village')"
                :error-messages="getErrorMessages('main_village')"
              />
            </v-col>
          </v-row>

          <!-- Current Address -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('آدرس فعلی') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_province"
                :label="t('ولایت فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_province')"
                :error-messages="getErrorMessages('now_province')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_zone"
                :label="t('زون فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_zone')"
                :error-messages="getErrorMessages('now_zone')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_district"
                :label="t('ولسوالی فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_district')"
                :error-messages="getErrorMessages('now_district')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_village"
                :label="t('قریه فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_village')"
                :error-messages="getErrorMessages('now_village')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_specific_place"
                :label="t('موقعیت مشخص فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_specific_place')"
                :error-messages="getErrorMessages('now_specific_place')"
              />
            </v-col>

            <v-col cols="12" md="2">
              <v-text-field
                v-model="form.now_street_number"
                :label="t('شماره سرک')"
                variant="outlined"
                density="compact"
                :error="hasError('now_street_number')"
                :error-messages="getErrorMessages('now_street_number')"
              />
            </v-col>

            <v-col cols="12" md="2">
              <v-text-field
                v-model="form.now_home_number"
                :label="t('شماره خانه')"
                variant="outlined"
                density="compact"
                :error="hasError('now_home_number')"
                :error-messages="getErrorMessages('now_home_number')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.Guzar"
                :label="t('گذر')"
                variant="outlined"
                density="compact"
                :error="hasError('Guzar')"
                :error-messages="getErrorMessages('Guzar')"
              />
            </v-col>

            <v-col cols="12" md="9">
              <v-text-field
                v-model="form.place_tafseel"
                :label="t('تفصیل موقعیت')"
                variant="outlined"
                density="compact"
                :error="hasError('place_tafseel')"
                :error-messages="getErrorMessages('place_tafseel')"
              />
            </v-col>
          </v-row>

          <!-- Jobs & Home -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('وظایف و خانه‌ها') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.old_job"
                :label="t('وظیفه قبلی')"
                variant="outlined"
                density="compact"
                :error="hasError('old_job')"
                :error-messages="getErrorMessages('old_job')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_job"
                :label="t('وظیفه فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_job')"
                :error-messages="getErrorMessages('now_job')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="form.home_quantity"
                type="number"
                :label="t('تعداد خانه')"
                variant="outlined"
                density="compact"
                :error="hasError('home_quantity')"
                :error-messages="getErrorMessages('home_quantity')"
              />
            </v-col>
          </v-row>

          <!-- Contact / History -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('تماس و سابقه') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                variant="outlined"
                density="compact"
                :rules="[rules.phone10]"
                :error="hasError('phone_number')"
                :error-messages="getErrorMessages('phone_number')"
                inputmode="numeric"
                maxlength="10"
                counter="10"
              />
            </v-col>

            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.history_form"
                :label="t('سابقه/فرم')"
                variant="outlined"
                density="compact"
                :error="hasError('history_form')"
                :error-messages="getErrorMessages('history_form')"
              />
            </v-col>
          </v-row>

          <!-- Coordinates / Province_addr -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('مختصات و ولایت مربوطه') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                variant="outlined"
                density="compact"
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                variant="outlined"
                density="compact"
                :error="hasError('Province_addr')"
                :error-messages="getErrorMessages('Province_addr')"
              />
            </v-col>
          </v-row>

          <!-- Resource & Note -->
          <v-divider class="my-4" />
          <div class="section-header mb-2">
            <span class="section-title">{{ t('منبع و یادداشت') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
                variant="outlined"
                density="compact"
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
                variant="outlined"
                density="compact"
                readonly
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.resource_id"
                :label="t('Resource ID')"
                variant="outlined"
                density="compact"
                type="number"
                :error="hasError('resource_id')"
                :error-messages="getErrorMessages('resource_id')"
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="form.note"
                :label="t('یادداشت')"
                variant="outlined"
                density="compact"
                :error="hasError('note')"
                :error-messages="getErrorMessages('note')"
              />
            </v-col>
          </v-row>

          <!-- Actions -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2">
              <v-btn
                color="primary"
                variant="elevated"
                class="text-none rounded-pill primary-action-btn"
                :loading="saving"
                type="submit"
                :prepend-icon="isEdit ? 'mdi-content-save-edit-outline' : 'mdi-plus-circle-outline'"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ') }}
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

          <!-- ✅ SIMPLE EXPORT BUTTONS (BOTTOM) -->
          <v-divider class="my-4" />
          <v-card-actions class="justify-end ga-2">
            <v-tooltip :text="t('PDF ساده (بدون عکس)')" location="top">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  variant="tonal"
                  prepend-icon="mdi-file-pdf-box"
                  class="text-none rounded-pill"
                  @click="downloadPdf"
                >
                  {{ t('PDF ساده') }}
                </v-btn>
              </template>
            </v-tooltip>

            <v-tooltip :text="t('Word ساده (بدون عکس)')" location="top">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  variant="tonal"
                  prepend-icon="mdi-file-word-box"
                  class="text-none rounded-pill"
                  @click="downloadWord"
                >
                  {{ t('Word ساده') }}
                </v-btn>
              </template>
            </v-tooltip>
          </v-card-actions>
        </v-card>
      </v-form>

      <!-- Error Snackbar -->
      <v-snackbar v-model="showError" color="error" timeout="5000">
        {{ errorMessage }}
        <template #actions>
          <v-btn variant="text" @click="showError = false"> {{ t('بستن') }} </v-btn>
        </template>
      </v-snackbar>

      <!-- Success Snackbar -->
      <v-snackbar v-model="showSuccess" color="success" timeout="3000">
        {{ successMessage }}
        <template #actions>
          <v-btn variant="text" @click="showSuccess = false"> {{ t('بستن') }} </v-btn>
        </template>
      </v-snackbar>
    </v-container>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';

import AppBar from '@/components/AppBar.vue';
import { useWakilanoRepository } from '@/stores/WakilanoRepository';

const repo = useWakilanoRepository();
const route = useRoute();
const router = useRouter();

const { t, locale } = useI18n();
const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => (isRtl.value = l === 'fa'));

/* MODE */
const isEdit = computed(() => !!route.params.id);

/* FORM / STATE */
const formRef = ref(null);
const formValid = ref(false);
const saving = ref(false);

const showError = ref(false);
const errorMessage = ref('');
const showSuccess = ref(false);
const successMessage = ref('');

function showErrorMessage(message) {
  errorMessage.value = message;
  showError.value = true;
}
function showSuccessMessage(message) {
  successMessage.value = message;
  showSuccess.value = true;
}

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

/* FORM MODEL */
const blankForm = () => ({
  id: null,

  name: '',
  f_name: '',
  grand_father_name: '',

  main_province: '',
  main_district: '',
  main_village: '',

  now_province: '',
  now_zone: '',
  now_district: '',
  now_village: '',
  now_specific_place: '',
  now_street_number: '',
  now_home_number: '',
  Guzar: '',
  place_tafseel: '',

  old_job: '',
  now_job: '',
  home_quantity: '',

  phone_number: '',
  history_form: '',

  coordinates: '',
  Province_addr: '',

  resource_code: '',
  resource_phone: '',
  resource_id: '',
  note: '',
});

const form = ref(blankForm());

/* ✅ RULES (only validation change: phone exactly 10 digits) */
const rules = {
  required: (v) => !!(v && String(v).trim()) || t('این فیلد الزامی است'),
  phone10: (v) => {
    const s = String(v ?? '').trim();
    if (!s) return true; // not required; keep your logic
    if (!/^\d+$/.test(s)) return t('فقط اعداد');
    if (s.length !== 10) return t('شماره تماس باید دقیقاً ۱۰ رقم باشد');
    return true;
  },
};

/* RESOURCES DROPDOWN */
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

  b.name = x.name ?? '';
  b.f_name = x.f_name ?? '';
  b.grand_father_name = x.grand_father_name ?? '';

  b.main_province = x.main_province ?? '';
  b.main_district = x.main_district ?? '';
  b.main_village = x.main_village ?? '';

  b.now_province = x.now_province ?? '';
  b.now_zone = x.now_zone ?? '';
  b.now_district = x.now_district ?? '';
  b.now_village = x.now_village ?? '';
  b.now_specific_place = x.now_specific_place ?? '';
  b.now_street_number = x.now_street_number ?? '';
  b.now_home_number = x.now_home_number ?? '';
  b.Guzar = x.Guzar ?? '';
  b.place_tafseel = x.place_tafseel ?? '';

  b.old_job = x.old_job ?? '';
  b.now_job = x.now_job ?? '';
  b.home_quantity = x.home_quantity ?? '';

  b.phone_number = x.phone_number ?? '';
  b.history_form = x.history_form ?? '';

  b.coordinates = x.coordinates ?? '';
  b.Province_addr = x.Province_addr ?? '';

  b.resource_code = x.resource_code ?? '';
  b.resource_phone = x.resource_phone ?? '';
  b.resource_id = x.resource_id ?? '';
  b.note = x.note ?? '';

  return b;
}

async function loadRecord() {
  const id = route.params.id;
  if (!id) {
    form.value = blankForm();
    return;
  }
  try {
    await repo.FetchWakilanoData?.(id);
    const single = Array.isArray(repo.Wakilano) ? repo.Wakilano[0] : repo.Wakilano || {};
    if (!single) return;
    form.value = mapFromApi(single);
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات وکیلالو'));
  }
}

/* FORM DATA HELPERS */
function toFormData(obj) {
  const fd = new FormData();
  Object.entries(obj).forEach(([k, v]) => {
    if (v === undefined || v === null) return;
    fd.append(k, v);
  });
  return fd;
}

function buildCreateFormData() {
  const payload = { ...form.value };
  return toFormData(payload);
}

function buildUpdateFormData() {
  const payload = { ...form.value };
  return toFormData(payload);
}

/* SCROLL TO FIRST INVALID */
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

  const f = formRef.value;
  if (!f) return;

  const { valid } = await f.validate();
  if (!valid) {
    showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
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
    if (!isEdit.value) {
      const fd = buildCreateFormData();
      const created = await repo.CreateWakilano?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/wakilanos').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo.UpdateWakilano?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/wakilanos').catch(() => {});
      } else {
        showErrorMessage(t('ذخیرهٔ تغییرات ناکام شد'));
      }
    }
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در ذخیره اطلاعات'));
  } finally {
    saving.value = false;
  }
}

/* RESET & NAVIGATION */
function resetForm() {
  if (isEdit.value) {
    loadRecord();
  } else {
    form.value = blankForm();
  }

  clearBackendErrors();
  formRef.value?.resetValidation?.();
  showSuccessMessage(t('فرم پاک شد'));
}

function goBack() {
  router.push('/app/wakilanos');
}

/* ✅ SIMPLE EXPORT (works for blank too) */
function normalizeValue(v) {
  if (v === null || v === undefined) return '';
  return String(v);
}

function buildSimpleRows() {
  const rows = [
    [t('نام'), form.value.name],
    [t('نام پدر'), form.value.f_name],
    [t('نام پدرکلان'), form.value.grand_father_name],

    [t('ولایت اصلی'), form.value.main_province],
    [t('ولسوالی اصلی'), form.value.main_district],
    [t('قریه اصلی'), form.value.main_village],

    [t('ولایت فعلی'), form.value.now_province],
    [t('زون فعلی'), form.value.now_zone],
    [t('ولسوالی فعلی'), form.value.now_district],
    [t('قریه فعلی'), form.value.now_village],
    [t('موقعیت مشخص فعلی'), form.value.now_specific_place],
    [t('شماره سرک'), form.value.now_street_number],
    [t('شماره خانه'), form.value.now_home_number],
    [t('گذر'), form.value.Guzar],
    [t('تفصیل موقعیت'), form.value.place_tafseel],

    [t('وظیفه قبلی'), form.value.old_job],
    [t('وظیفه فعلی'), form.value.now_job],
    [t('تعداد خانه'), form.value.home_quantity],

    [t('شماره تماس'), form.value.phone_number],
    [t('سابقه/فرم'), form.value.history_form],

    [t('مختصات'), form.value.coordinates],
    [t('ولایت مربوطه'), form.value.Province_addr],

    [t('کود منبع'), form.value.resource_code],
    [t('شماره منبع'), form.value.resource_phone],
    [t('Resource ID'), form.value.resource_id],

    [t('یادداشت'), form.value.note],
  ];

  return rows.map(([label, value]) => [label, normalizeValue(value).trim()]);
}

function buildSimpleExportHtml(docTitle) {
  const rows = buildSimpleRows();

  const tr = rows
    .map(
      ([k, v]) => `
        <tr>
          <td class="k">${k}</td>
          <td class="v">${v || '-'}</td>
        </tr>
      `
    )
    .join('');

  return `
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>${docTitle}</title>
  <style>
    body { font-family: Arial, Tahoma, sans-serif; padding: 24px; color: #111; }
    h2 { margin: 0 0 12px; font-size: 18px; }
    .meta { margin-bottom: 16px; font-size: 12px; color: #444; }
    table { width: 100%; border-collapse: collapse; }
    td { border: 1px solid #ddd; padding: 8px 10px; vertical-align: top; }
    td.k { width: 30%; background: #f7f7f7; font-weight: 700; }
    td.v { width: 70%; }
    .sign { margin-top: 28px; display: flex; justify-content: space-between; gap: 16px; }
    .sign .box { width: 33%; border-top: 1px solid #999; padding-top: 8px; font-size: 12px; color: #333; text-align: center; }
    @media print { body { padding: 0; } }
  </style>
</head>
<body>
  <h2>${docTitle}</h2>
  <div class="meta">${t('تاریخ')}: ${new Date().toLocaleDateString('fa-AF')}</div>
  <table>
    <tbody>
      ${tr}
    </tbody>
  </table>

  <div class="sign">
    <div class="box">${t('امضاء')}</div>
    <div class="box">${t('مهر')}</div>
    <div class="box">${t('تایید')}</div>
  </div>
</body>
</html>
  `.trim();
}

function downloadPdf() {
  const html = buildSimpleExportHtml(t('فورم وکیل'));
  const w = window.open('', '_blank');
  if (!w) return;
  w.document.open();
  w.document.write(html);
  w.document.close();
  w.focus();
  w.print();
}

function downloadWord() {
  const html = buildSimpleExportHtml(t('فورم وکیل'));
  const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
  const url = URL.createObjectURL(blob);

  const a = document.createElement('a');
  a.href = url;
  a.download = 'wakilano.doc';
  document.body.appendChild(a);
  a.click();
  a.remove();

  URL.revokeObjectURL(url);
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

/* tighter fields */
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
