<!-- src/views/government/GovernmentPlaceForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش اداره دولتی') : t('ایجاد اداره دولتی جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ اداره دولتی موجود')
          : t('ثبت یک ریکارډ اداره دولتی جدید در سیستم')
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
          <v-row>
            <!-- Identity + address (FULL WIDTH now; picture removed) -->
            <v-col cols="12">
              <!-- Identity -->
              <div class="section-header">
                <span class="section-title">
                  {{ isEdit ? t('ویرایش ریکارډ اداره دولتی') : t('ایجاد ریکارډ اداره دولتی') }}
                </span>
              </div>

              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.government_place_name"
                    :label="t('نام اداره دولتی *')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.required]"
                    :error="hasError('government_place_name')"
                    :error-messages="getErrorMessages('government_place_name')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.daily_clientele"
                    type="number"
                    :label="t('مراجعین روزانه')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.nonNegativeInt]"
                    :error="hasError('daily_clientele')"
                    :error-messages="getErrorMessages('daily_clientele')"
                  />
                </v-col>
              </v-row>

              <!-- Responsible -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('مسؤول اداره') }}</span>
              </div>

              <v-row>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.responsible_name"
                    :label="t('مسؤول اداره')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_name')"
                    :error-messages="getErrorMessages('responsible_name')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.responsible_f_name"
                    :label="t('نام پدر مسؤول')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_f_name')"
                    :error-messages="getErrorMessages('responsible_f_name')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.responsible_possition"
                    :label="t('وظیفه/موقف مسؤول')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_possition')"
                    :error-messages="getErrorMessages('responsible_possition')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.responsible_phone"
                    :label="t('شماره تماس مسؤول')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.phone]"
                    :error="hasError('responsible_phone')"
                    :error-messages="getErrorMessages('responsible_phone')"
                  />
                </v-col>
              </v-row>

              <!-- Address -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس اداره') }}</span>
              </div>

              <v-row>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.province"
                    :label="t('ولایت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('province')"
                    :error-messages="getErrorMessages('province')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.zone"
                    :label="t('زون')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('zone')"
                    :error-messages="getErrorMessages('zone')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.district"
                    :label="t('ولسوالی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('district')"
                    :error-messages="getErrorMessages('district')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.village"
                    :label="t('قریه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('village')"
                    :error-messages="getErrorMessages('village')"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.specefic_place"
                    :label="t('موقعیت مشخص')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('specefic_place')"
                    :error-messages="getErrorMessages('specefic_place')"
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Extra fields -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('امنیت، منبع و سایر جزئیات') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.tafseel"
                :label="t('تفصیل')"
                variant="outlined"
                density="compact"
                :error="hasError('tafseel')"
                :error-messages="getErrorMessages('tafseel')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.amnyatee_khala"
                :label="t('خلای امنیتی')"
                variant="outlined"
                density="compact"
                :error="hasError('amnyatee_khala')"
                :error-messages="getErrorMessages('amnyatee_khala')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="form.guard_quantity"
                type="number"
                :label="t('تعداد محافظ')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('guard_quantity')"
                :error-messages="getErrorMessages('guard_quantity')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                variant="outlined"
                density="compact"
                :error="hasError('Province_addr')"
                :error-messages="getErrorMessages('Province_addr')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                variant="outlined"
                density="compact"
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>

            <!-- Resource mapping -->
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

            <v-col cols="12" md="8">
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

          <!-- Actions (BOTTOM + EXPORT) -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2 flex-wrap">
              <v-btn
                color="primary"
                variant="elevated"
                class="text-none rounded-pill primary-action-btn"
                :loading="saving"
                type="submit"
                :prepend-icon="isEdit ? 'mdi-content-save-edit-outline' : 'mdi-plus-circle-outline'"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ اداره دولتی') }}
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

              <!-- ✅ Simple Export (NO image / NO app headers) - works even for blank -->
              <v-btn
                color="red"
                variant="outlined"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-file-pdf-box"
                @click="downloadPdf"
              >
                {{ t('PDF') }}
              </v-btn>

              <v-btn
                color="blue"
                variant="outlined"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-file-word-box"
                @click="downloadWord"
              >
                {{ t('Word') }}
              </v-btn>
            </v-col>
          </v-row>
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

/* messages */
function showErrorMessage(message) {
  errorMessage.value = message || t('خطا رخ داد');
  showError.value = true;
}
function showSuccessMessage(message) {
  successMessage.value = message || t('عملیات موفقانه انجام شد');
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

  // identity
  government_place_name: '',
  daily_clientele: '',

  // responsible
  responsible_name: '',
  responsible_f_name: '',
  responsible_possition: '',
  responsible_phone: '',

  // address
  province: '',
  zone: '',
  district: '',
  village: '',
  specefic_place: '',

  // extra
  tafseel: '',
  amnyatee_khala: '',
  guard_quantity: '',
  Province_addr: '',
  coordinates: '',

  // resource
  resource_code: '',
  resource_phone: '',
  resource_id: null,

  // note
  note: '',
});

const form = ref(blankForm());

/* RULES */
const rules = {
  required: (v) => !!(v && String(v).trim()) || t('این فیلد الزامی است'),
  nonNegativeInt: (v) => v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
  phone: (v) => !v || /^[+]?\d[\d\s\-]{5,}$/.test(String(v)) || t('شماره تماس نادرست است'),
};

/* RESOURCES DROPDOWN */
const resources = computed(() => repo.resourcecode || []);

function onResourceCodeChange(code) {
  const found = resources.value.find((r) => r.code_number === code);
  form.value.resource_code = code || '';
  form.value.resource_phone = found?.phone_number ?? '';
  form.value.resource_id = found?.id ?? null;
}

/* MAP FROM API (picture removed) */
function mapFromApi(payload = {}) {
  const x = payload.data ?? payload;
  const b = blankForm();

  b.id = x.id ?? null;

  b.government_place_name = x.government_place_name ?? '';
  b.daily_clientele = x.daily_clientele ?? '';

  b.responsible_name = x.responsible_name ?? '';
  b.responsible_f_name = x.responsible_f_name ?? '';
  b.responsible_possition = x.responsible_possition ?? '';
  b.responsible_phone = x.responsible_phone ?? '';

  b.province = x.province ?? '';
  b.zone = x.zone ?? '';
  b.district = x.district ?? '';
  b.village = x.village ?? '';
  b.specefic_place = x.specefic_place ?? '';

  b.tafseel = x.tafseel ?? '';
  b.amnyatee_khala = x.amnyatee_khala ?? '';
  b.guard_quantity = x.guard_quantity ?? '';
  b.Province_addr = x.Province_addr ?? '';
  b.coordinates = x.coordinates ?? '';

  const r = x.resource || {};
  b.resource_code = x.resource_code ?? r.code_number ?? '';
  b.resource_phone = x.resource_phone ?? r.phone_number ?? '';
  b.resource_id = x.resource_id ?? r.id ?? null;

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
    await repo.FetchGovernmentPlaceData?.(id);
    const single = Array.isArray(repo.GovernmentPlace)
      ? repo.GovernmentPlace[0]
      : repo.GovernmentPlace || {};
    form.value = { ...mapFromApi(single || {}) };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات اداره دولتی'));
  }
}

/* FORM DATA HELPERS */
function toFormData(obj) {
  const fd = new FormData();
  Object.entries(obj || {}).forEach(([k, v]) => {
    if (v === undefined || v === null) return;
    fd.append(k, v);
  });
  return fd;
}

function buildCreateFormData() {
  return toFormData({ ...form.value });
}

function buildUpdateFormData() {
  return toFormData({ ...form.value });
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

/* =========================
   SIMPLE EXPORT (NO image, NO app headers) ✅ works even for blank
========================= */
function esc(v) {
  return String(v ?? '').replace(/[&<>"']/g, (m) => {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return map[m];
  });
}
function safeFileName(s) {
  return String(s ?? '')
    .replace(/[\\/:*?"<>|]/g, '-')
    .replace(/\s+/g, ' ')
    .trim();
}
function v(x) {
  return x && String(x).trim() ? x : '—';
}
function exportRows() {
  return [
    { label: t('نام اداره دولتی'), value: v(form.value.government_place_name) },
    { label: t('مراجعین روزانه'), value: v(form.value.daily_clientele) },

    { label: t('مسؤول اداره'), value: v(form.value.responsible_name) },
    { label: t('نام پدر مسؤول'), value: v(form.value.responsible_f_name) },
    { label: t('وظیفه/موقف مسؤول'), value: v(form.value.responsible_possition) },
    { label: t('شماره تماس مسؤول'), value: v(form.value.responsible_phone) },

    { label: t('ولایت'), value: v(form.value.province) },
    { label: t('زون'), value: v(form.value.zone) },
    { label: t('ولسوالی'), value: v(form.value.district) },
    { label: t('قریه'), value: v(form.value.village) },
    { label: t('موقعیت مشخص'), value: v(form.value.specefic_place) },

    { label: t('تفصیل'), value: v(form.value.tafseel) },
    { label: t('خلای امنیتی'), value: v(form.value.amnyatee_khala) },
    { label: t('تعداد محافظ'), value: v(form.value.guard_quantity) },

    { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },
    { label: t('مختصات'), value: v(form.value.coordinates) },

    { label: t('کود منبع'), value: v(form.value.resource_code) },
    { label: t('شماره منبع'), value: v(form.value.resource_phone) },

    { label: t('یادداشت'), value: v(form.value.note) },
  ];
}
function buildSimpleTableHtml() {
  const rows = exportRows()
    .map(
      (r) => `
      <tr>
        <th>${esc(r.label)}</th>
        <td>${esc(r.value)}</td>
      </tr>
    `
    )
    .join('');

  return `
    <h3 class="title">فورم اداره دولتی</h3>
    <table class="simple-table">
      <tbody>
        ${rows}
      </tbody>
    </table>
    <div class="sig">
      <div>امضاء / مهر مسئول: ____________________</div>
      <div>تاریخ / امضاء ثبت کوونکی: ____________________</div>
    </div>
  `;
}
function buildDocumentHtml() {
  const bodyHtml = buildSimpleTableHtml();
  const safeId = esc(form.value.id || '');
  const safeName = esc(form.value.government_place_name || '');

  return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>government_place_${safeId}_${safeName}</title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }
    html, body {
      margin: 0; padding: 0; background: #fff;
      font-family: Tahoma, Arial, sans-serif;
      font-size: 12px; direction: rtl;
    }
    .title { text-align: center; margin: 22px 0 12px; font-size: 16px; font-weight: 700; }
    .simple-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 auto; }
    .simple-table th, .simple-table td {
      border: 1px solid #000; padding: 6px 8px; vertical-align: top; font-size: 12px;
    }
    .simple-table th { width: 35%; background: #f5f5f5; text-align: right; font-weight: 700; }
    .simple-table td { width: 65%; text-align: right; word-break: break-word; }
    .sig { margin-top: 18px; display: flex; justify-content: space-between; gap: 12px; font-size: 12px; }
  </style>
</head>
<body>
  ${bodyHtml}
</body>
</html>
`;
}

function downloadPdf() {
  const html = buildDocumentHtml();
  const w = window.open('', '_blank', 'width=1024,height=768');
  if (!w) return;

  w.document.open();
  w.document.write(html);
  w.document.close();

  w.onload = () => {
    w.focus();
    w.print();
  };
}

function downloadWord() {
  const html = buildDocumentHtml();
  const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
  const url = URL.createObjectURL(blob);

  const link = document.createElement('a');
  link.href = url;
  link.download = `اداره_دولتی_${safeFileName(form.value.government_place_name || 'جدید')}_${Date.now()}.doc`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  URL.revokeObjectURL(url);
  showSuccessMessage(t('فایل Word با موفقیت ایجاد شد'));
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

  if (!form.value.government_place_name?.trim()) {
    showErrorMessage(t('نام اداره دولتی الزامی است'));
    return;
  }

  saving.value = true;

  try {
    if (!isEdit.value) {
      const fd = buildCreateFormData();
      const created = await repo.CreateGovernmentPlace?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/governmentplaces').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo.UpdateGovernmentPlace?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/governmentplaces').catch(() => {});
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
  router.push('/app/governmentplaces');
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
/* main card */
.simple-form-card {
  padding: 16px 16px 20px;
  border-radius: 12px;
}

/* sections */
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

/* slightly tighter fields */
:deep(.v-field--variant-outlined .v-field__input) {
  padding-top: 4px;
  padding-bottom: 4px;
  min-height: 36px;
}

/* prettier buttons */
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
