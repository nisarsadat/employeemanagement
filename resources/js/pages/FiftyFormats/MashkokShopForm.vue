<!-- src/views/fifthyFormats/MashkokShopForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش دکان مشکوک') : t('ایجاد دکان مشکوک جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ دکان مشکوک موجود')
          : t('ثبت یک ریکارډ دکان مشکوک جدید در سیستم')
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
          <!-- Location -->
          <div class="section-header">
            <span class="section-title">{{ t('موقعیت') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.province"
                :label="t('ولایت *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
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
                :label="t('ولسوالی *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('district')"
                :error-messages="getErrorMessages('district')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.village"
                :label="t('قریه/ناحیه')"
                variant="outlined"
                density="compact"
                :error="hasError('village')"
                :error-messages="getErrorMessages('village')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.specific_place"
                :label="t('موقعیت مشخص')"
                variant="outlined"
                density="compact"
                :error="hasError('specific_place')"
                :error-messages="getErrorMessages('specific_place')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.market_name"
                :label="t('نام بازار')"
                variant="outlined"
                density="compact"
                :error="hasError('market_name')"
                :error-messages="getErrorMessages('market_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                variant="outlined"
                density="compact"
                hint="34.5553, 69.2075"
                persistent-hint
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Owner / Person info -->
          <div class="section-header">
            <span class="section-title">{{ t('مالک دکان') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.name"
                :label="t('نام مالک')"
                variant="outlined"
                density="compact"
                :error="hasError('name')"
                :error-messages="getErrorMessages('name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.father_name"
                :label="t('نام پدر')"
                variant="outlined"
                density="compact"
                :error="hasError('father_name')"
                :error-messages="getErrorMessages('father_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.main_province"
                :label="t('ولایت اصلی')"
                variant="outlined"
                density="compact"
                :error="hasError('main_province')"
                :error-messages="getErrorMessages('main_province')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.phone_numbers"
                :label="t('شماره تماس')"
                variant="outlined"
                density="compact"
                :error="hasError('phone_numbers')"
                :error-messages="getErrorMessages('phone_numbers')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Shop type / suspicion -->
          <div class="section-header">
            <span class="section-title">{{ t('مشخصات دکان') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.shop_type"
                :label="t('نوع دکان')"
                variant="outlined"
                density="compact"
                :error="hasError('shop_type')"
                :error-messages="getErrorMessages('shop_type')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.mashkok_type"
                :label="t('نوع شک')"
                variant="outlined"
                density="compact"
                :error="hasError('mashkok_type')"
                :error-messages="getErrorMessages('mashkok_type')"
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
          </v-row>

          <v-divider class="my-4" />

          <!-- Source & Note -->
          <div class="section-header">
            <span class="section-title">{{ t('منبع و یادداشت') }}</span>
          </div>
          <v-row>
            <!-- Resource mapping (code autocomplete -> id + phone) -->
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

          <!-- Actions (✅ PDF/Word works even for blank) -->
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ دکان مشکوک') }}
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
          <v-btn variant="text" @click="showError = false">
            {{ t('بستن') }}
          </v-btn>
        </template>
      </v-snackbar>

      <!-- Success Snackbar -->
      <v-snackbar v-model="showSuccess" color="success" timeout="3000">
        {{ successMessage }}
        <template #actions>
          <v-btn variant="text" @click="showSuccess = false">
            {{ t('بستن') }}
          </v-btn>
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
import { useMawadRepository } from '@/stores/MawadRepository';

const repo = useMawadRepository();
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
  backendErrors.value?.[field] ?? [];
  return backendErrors.value?.[field] ?? [];
}

/* FORM MODEL */
const blankForm = () => ({
  id: null,

  province: '',
  zone: '',
  district: '',
  village: '',
  specific_place: '',
  market_name: '',
  coordinates: '',

  name: '',
  father_name: '',
  main_province: '',
  phone_numbers: '',

  shop_type: '',
  mashkok_type: '',
  Province_addr: '',

  note: '',

  resource_id: null,
  resource_code: '',
  resource_phone: '',
});

const form = ref(blankForm());

/* RULES */
const rules = {
  required: (v) => !!v || t('این فیلد الزامی است'),
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

  b.province = x.province ?? '';
  b.zone = x.zone ?? '';
  b.district = x.district ?? '';
  b.village = x.village ?? '';

  b.specific_place = x.specific_place ?? '';
  b.market_name = x.market_name ?? '';
  b.coordinates = x.coordinates ?? '';

  b.name = x.name ?? '';
  b.father_name = x.father_name ?? '';
  b.main_province = x.main_province ?? '';
  b.phone_numbers = x.phone_numbers ?? '';

  b.shop_type = x.shop_type ?? '';
  b.mashkok_type = x.mashkok_type ?? '';
  b.Province_addr = x.Province_addr ?? '';

  b.note = x.note ?? '';

  const r = x.resource || {};
  b.resource_id = x.resource_id ?? r.id ?? null;
  b.resource_code = x.resource_code ?? r.code_number ?? '';
  b.resource_phone = x.resource_phone ?? r.phone_number ?? '';

  return b;
}

async function loadRecord() {
  const id = route.params.id;
  if (!id) {
    form.value = blankForm();
    return;
  }
  try {
    await repo.FetchMashkokShopData?.(id);
    const single = Array.isArray(repo.MashkokShop) ? repo.MashkokShop[0] : repo.MashkokShop || {};
    if (!single) return;

    const mapped = mapFromApi(single);
    form.value = { ...mapped };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات دکان مشکوک'));
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
    { label: t('ولایت'), value: v(form.value.province) },
    { label: t('زون'), value: v(form.value.zone) },
    { label: t('ولسوالی'), value: v(form.value.district) },
    { label: t('قریه/ناحیه'), value: v(form.value.village) },

    { label: t('موقعیت مشخص'), value: v(form.value.specific_place) },
    { label: t('نام بازار'), value: v(form.value.market_name) },
    { label: t('مختصات'), value: v(form.value.coordinates) },

    { label: t('نام مالک'), value: v(form.value.name) },
    { label: t('نام پدر'), value: v(form.value.father_name) },
    { label: t('ولایت اصلی'), value: v(form.value.main_province) },
    { label: t('شماره تماس'), value: v(form.value.phone_numbers) },

    { label: t('نوع دکان'), value: v(form.value.shop_type) },
    { label: t('نوع شک'), value: v(form.value.mashkok_type) },
    { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },

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
    <h3 class="title">فورم دکان مشکوک</h3>
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
  const safeName = esc(form.value.province || '');

  return `<!DOCTYPE html>
<html dir="rtl" xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8" />
  <title>mashkokshop_${safeId}_${safeName}</title>
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
</html>`;
}

function downloadPdf() {
  const html = buildDocumentHtml();
  const w = window.open('', '_blank', 'width=1024,height=768');
  if (!w) return;

  w.document.open();
  w.document.write(html);
  w.document.close();

  setTimeout(() => {
    w.focus();
    w.print();
  }, 350);
}

function downloadWord() {
  const html = buildDocumentHtml();
  const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
  const url = URL.createObjectURL(blob);

  const link = document.createElement('a');
  link.href = url;
  link.download = `دکان_مشکوک_${safeFileName(form.value.province || 'جدید')}_${Date.now()}.doc`;
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

  if (!form.value.province?.trim() || !form.value.district?.trim()) {
    showErrorMessage(t('ولایت و ولسوالی الزامی است'));
    return;
  }

  saving.value = true;

  try {
    if (!isEdit.value) {
      const fd = buildCreateFormData();
      const created = await repo.CreateMashkokShop?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/mashkokshops').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo.UpdateMashkokShop?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/mashkokshops').catch(() => {});
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
  router.push('/app/mashkokshops');
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
