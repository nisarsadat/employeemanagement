<!-- src/views/fifthyformatone/BadmashanoForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش بدماش') : t('ایجاد بدماش جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ بدماش موجود')
          : t('ثبت یک ریکارډ بدماش جدید در سیستم')
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
          <!-- ================= IDENTITY ================= -->
          <div class="section-header">
            <span class="section-title">
              {{ isEdit ? t('ویرایش ریکارډ بدماش') : t('ایجاد ریکارډ بدماش') }}
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
                v-model="form.nickname"
                :label="t('تخلص')"
                variant="outlined"
                density="compact"
                :error="hasError('nickname')"
                :error-messages="getErrorMessages('nickname')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.father_name"
                :label="t('نام پدر')"
                variant="outlined"
                density="compact"
                :error="hasError('father_name')"
                :error-messages="getErrorMessages('father_name')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- ================= MAIN ADDRESS ================= -->
          <div class="section-header">
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
                :label="t('قریه/ناحیه اصلی')"
                variant="outlined"
                density="compact"
                :error="hasError('main_village')"
                :error-messages="getErrorMessages('main_village')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- ================= NOW ADDRESS ================= -->
          <div class="section-header">
            <span class="section-title">{{ t('آدرس فعلی') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_province"
                :label="t('ولایت فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_province')"
                :error-messages="getErrorMessages('now_province')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_zone"
                :label="t('زون فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_zone')"
                :error-messages="getErrorMessages('now_zone')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_district"
                :label="t('ولسوالی فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_district')"
                :error-messages="getErrorMessages('now_district')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_village"
                :label="t('قریه/ناحیه فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_village')"
                :error-messages="getErrorMessages('now_village')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.now_specific_place"
                :label="t('موقعیت مشخص فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_specific_place')"
                :error-messages="getErrorMessages('now_specific_place')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_street_number"
                :label="t('شماره سرک فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_street_number')"
                :error-messages="getErrorMessages('now_street_number')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_home_number"
                :label="t('شماره خانه فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_home_number')"
                :error-messages="getErrorMessages('now_home_number')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- ================= EXTRA + RESOURCE ================= -->
          <div class="section-header">
            <span class="section-title">{{ t('جزئیات اضافی و منبع') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.old_job"
                :label="t('وظیفه قبلی')"
                variant="outlined"
                density="compact"
                :error="hasError('old_job')"
                :error-messages="getErrorMessages('old_job')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.job"
                :label="t('وظیفه فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('job')"
                :error-messages="getErrorMessages('job')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.friends_quantity"
                type="number"
                :label="t('تعداد دوستان')"
                variant="outlined"
                density="compact"
                :error="hasError('friends_quantity')"
                :error-messages="getErrorMessages('friends_quantity')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.supplier"
                :label="t('تمویل کننده')"
                variant="outlined"
                density="compact"
                :error="hasError('supplier')"
                :error-messages="getErrorMessages('supplier')"
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
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                variant="outlined"
                density="compact"
                :rules="[rules.phoneMinMax]"
                :error="hasError('phone_number')"
                :error-messages="getErrorMessages('phone_number')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.history_jorm"
                :label="t('سابقه جرم')"
                variant="outlined"
                density="compact"
                :error="hasError('history_jorm')"
                :error-messages="getErrorMessages('history_jorm')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.jorm_type"
                :label="t('نوع جرم')"
                variant="outlined"
                density="compact"
                :error="hasError('jorm_type')"
                :error-messages="getErrorMessages('jorm_type')"
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

          <!-- ================= ACTIONS (PDF/WORD bottom) ================= -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2 flex-wrap">
              <v-btn
                color="primary"
                variant="elevated"
                class="text-none rounded-pill primary-action-btn"
                :loading="saving"
                type="submit"
                :prepend-icon="
                  isEdit ? 'mdi-content-save-edit-outline' : 'mdi-plus-circle-outline'
                "
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ بدماش') }}
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
          <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
        </template>
      </v-snackbar>

      <!-- Success Snackbar -->
      <v-snackbar v-model="showSuccess" color="success" timeout="3000">
        {{ successMessage }}
        <template #actions>
          <v-btn variant="text" @click="showSuccess = false">{{ t('بستن') }}</v-btn>
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
import { useBadmashanoRepository } from '@/stores/BadmashanoRepository';

const repo = useBadmashanoRepository();
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
  nickname: '',
  father_name: '',

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

  old_job: '',
  job: '',
  friends_quantity: '',
  supplier: '',
  Province_addr: '',
  coordinates: '',

  resource_code: '',
  resource_phone: '',
  resource_id: null,

  phone_number: '',
  history_jorm: '',
  jorm_type: '',
  note: '',
});

const form = ref(blankForm());

/* RULES */
const rules = {
  required: (v) => !!v || t('این فیلد الزامی است'),

  // ✅ same min/max phone validation pattern (works even if blank)
  phoneMinMax: (v) => {
    if (!v) return true;
    const s = String(v).trim();
    if (!s) return true;
    if (s.length < 7) return t('شماره باید حداقل ۷ رقم باشد');
    if (s.length > 15) return t('شماره باید حداکثر ۱۵ رقم باشد');
    return true;
  },
};

/* RESOURCES DROPDOWN */
const resources = computed(() => repo.resourcecode || []);

function onResourceCodeChange(code) {
  const found = resources.value.find((r) => r.code_number === code);
  form.value.resource_code = code || '';
  form.value.resource_phone = found?.phone_number ?? '';
  form.value.resource_id = found?.id ?? null;
}

/* MAP FROM API */
function mapFromApi(payload = {}) {
  const x = payload.data ?? payload;
  const b = blankForm();

  b.id = x.id ?? null;

  b.name = x.name ?? '';
  b.nickname = x.nickname ?? '';
  b.father_name = x.father_name ?? '';

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

  b.old_job = x.old_job ?? '';
  b.job = x.job ?? '';
  b.friends_quantity = x.friends_quantity ?? '';
  b.supplier = x.supplier ?? '';
  b.Province_addr = x.Province_addr ?? '';
  b.coordinates = x.coordinates ?? '';

  const r = x.resource || {};
  b.resource_id = x.resource_id ?? r.id ?? null;
  b.resource_code = x.resource_code ?? r.code_number ?? '';
  b.resource_phone = x.resource_phone ?? r.phone_number ?? '';

  b.phone_number = x.phone_number ?? '';
  b.history_jorm = x.history_jorm ?? '';
  b.jorm_type = x.jorm_type ?? '';
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
    await repo.FetchBadmashanoData?.(id);
    const single = Array.isArray(repo.Badmashano) ? repo.Badmashano[0] : repo.Badmashano || {};
    if (!single) return;
    form.value = { ...mapFromApi(single) };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات بدماش'));
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
   SIMPLE EXPORT (NO IMAGE, NO APP HEADERS) ✅ works even for blank
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
    { label: t('نام'), value: v(form.value.name) },
    { label: t('تخلص'), value: v(form.value.nickname) },
    { label: t('نام پدر'), value: v(form.value.father_name) },

    { label: t('ولایت اصلی'), value: v(form.value.main_province) },
    { label: t('ولسوالی اصلی'), value: v(form.value.main_district) },
    { label: t('قریه/ناحیه اصلی'), value: v(form.value.main_village) },

    { label: t('ولایت فعلی'), value: v(form.value.now_province) },
    { label: t('زون فعلی'), value: v(form.value.now_zone) },
    { label: t('ولسوالی فعلی'), value: v(form.value.now_district) },
    { label: t('قریه/ناحیه فعلی'), value: v(form.value.now_village) },
    { label: t('موقعیت مشخص فعلی'), value: v(form.value.now_specific_place) },
    { label: t('شماره سرک فعلی'), value: v(form.value.now_street_number) },
    { label: t('شماره خانه فعلی'), value: v(form.value.now_home_number) },

    { label: t('وظیفه قبلی'), value: v(form.value.old_job) },
    { label: t('وظیفه فعلی'), value: v(form.value.job) },
    { label: t('تعداد دوستان'), value: v(form.value.friends_quantity) },
    { label: t('تمویل کننده'), value: v(form.value.supplier) },

    { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },
    { label: t('مختصات'), value: v(form.value.coordinates) },

    { label: t('کود منبع'), value: v(form.value.resource_code) },
    { label: t('شماره منبع'), value: v(form.value.resource_phone) },

    { label: t('شماره تماس'), value: v(form.value.phone_number) },
    { label: t('سابقه جرم'), value: v(form.value.history_jorm) },
    { label: t('نوع جرم'), value: v(form.value.jorm_type) },

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
    <h3 class="title">فورم بدماش</h3>
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
  const safeName = esc(form.value.name || '');

  return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>badmash_${safeId}_${safeName}</title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }

    html, body {
      margin: 0;
      padding: 0;
      background: #ffffff;
      font-family: Tahoma, Arial, sans-serif;
      font-size: 12px;
      direction: rtl;
    }

    .title {
      text-align: center;
      margin: 22px 0 12px;
      font-size: 16px;
      font-weight: 700;
    }

    .simple-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin: 0 auto;
    }

    .simple-table th,
    .simple-table td {
      border: 1px solid #000;
      padding: 6px 8px;
      vertical-align: top;
      font-size: 12px;
    }

    .simple-table th {
      width: 35%;
      background: #f5f5f5;
      text-align: right;
      font-weight: 700;
    }

    .simple-table td {
      width: 65%;
      text-align: right;
      word-break: break-word;
    }

    .sig {
      margin-top: 18px;
      display: flex;
      justify-content: space-between;
      gap: 12px;
      font-size: 12px;
    }
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
  link.download = `بدماش_${safeFileName(form.value.name || 'جدید')}_${Date.now()}.doc`;
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

  if (!form.value.name?.trim()) {
    showErrorMessage(t('نام الزامی است'));
    return;
  }

  saving.value = true;

  try {
    if (!isEdit.value) {
      const fd = buildCreateFormData();
      const created = await repo.CreateBadmashano?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/badmashanos').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo.UpdateBadmashano?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/badmashanos').catch(() => {});
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
  router.push('/app/badmashanos');
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
.ga-2 {
  gap: 8px;
}
</style>
