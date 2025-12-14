<!-- src/views/fifthyFormats/NonResponsablePeopleForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش شخص غیرمسؤول') : t('ایجاد شخص غیرمسؤول جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ اشخاص غیرمسؤول موجود')
          : t('ثبت یک ریکارډ اشخاص غیرمسؤول جدید در سیستم')
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
            <!-- Identity + addresses -->
            <v-col cols="12">
              <!-- Identity -->
              <div class="section-header">
                <span class="section-title">
                  {{
                    isEdit
                      ? t('ویرایش ریکارډ اشخاص غیرمسؤول')
                      : t('ایجاد ریکارډ اشخاص غیرمسؤول')
                  }}
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
                    v-model="form.nick_name"
                    :label="t('نام مستعار')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('nick_name')"
                    :error-messages="getErrorMessages('nick_name')"
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

              <!-- Main address -->
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
                    :label="t('قریه/ناحیه اصلی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('main_village')"
                    :error-messages="getErrorMessages('main_village')"
                  />
                </v-col>
              </v-row>

              <!-- Now address -->
              <div class="section-header mt-4">
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
                    :label="t('موقعیت مشخص')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('now_specific_place')"
                    :error-messages="getErrorMessages('now_specific_place')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.now_street_number"
                    :label="t('شماره سرک')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('now_street_number')"
                    :error-messages="getErrorMessages('now_street_number')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.now_home_number"
                    :label="t('شماره خانه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('now_home_number')"
                    :error-messages="getErrorMessages('now_home_number')"
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Extra fields -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('جزئیات اضافی، امنیتی و منبع') }}</span>
          </div>

          <v-row>
            <!-- Social / work -->
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.friends_quantity"
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
                :label="t('تمویل‌کننده')"
                variant="outlined"
                density="compact"
                :error="hasError('supplier')"
                :error-messages="getErrorMessages('supplier')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                variant="outlined"
                density="compact"
                :rules="[rules.number]"
                :error="hasError('phone_number')"
                :error-messages="getErrorMessages('phone_number')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.activity_place"
                :label="t('محل فعالیت')"
                variant="outlined"
                density="compact"
                :error="hasError('activity_place')"
                :error-messages="getErrorMessages('activity_place')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.history_jorm"
                :label="t('سابقه جرم')"
                variant="outlined"
                density="compact"
                :error="hasError('history_jorm')"
                :error-messages="getErrorMessages('history_jorm')"
              />
            </v-col>

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

            <!-- Security -->
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.gun_type"
                :label="t('نوع سلاح')"
                variant="outlined"
                density="compact"
                :error="hasError('gun_type')"
                :error-messages="getErrorMessages('gun_type')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.tanzim"
                :label="t('تنظیم')"
                variant="outlined"
                density="compact"
                :error="hasError('tanzim')"
                :error-messages="getErrorMessages('tanzim')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.takhalof_type"
                :label="t('نوع تخلف')"
                variant="outlined"
                density="compact"
                :error="hasError('takhalof_type')"
                :error-messages="getErrorMessages('takhalof_type')"
              />
            </v-col>

            <!-- Province addr + coordinates -->
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

            <!-- Note -->
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

          <!-- Actions (✅ added PDF + Word, works even if blank) -->
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ اشخاص غیرمسؤول') }}
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
  return backendErrors.value?.[field] ?? [];
}

/* FORM MODEL */
const blankForm = () => ({
  id: null,

  // identity
  name: '',
  nick_name: '',
  father_name: '',

  // main address
  main_province: '',
  main_district: '',
  main_village: '',

  // now address
  now_province: '',
  now_zone: '',
  now_district: '',
  now_village: '',
  now_specific_place: '',
  now_street_number: '',
  now_home_number: '',

  // social / work / security
  friends_quantity: '',
  supplier: '',
  phone_number: '',
  history_jorm: '',
  activity_place: '',
  old_job: '',
  now_job: '',
  gun_type: '',
  tanzim: '',
  takhalof_type: '',
  Province_addr: '',
  coordinates: '',

  // resource
  resource_code: '',
  resource_phone: '',
  resource_id: null,

  // misc
  note: '',
});

const form = ref(blankForm());

/* RULES */
const rules = {
  required: (v) => !!v || t('این فیلد الزامی است'),
  number: (v) => !v || /^[0-9]+$/.test(String(v)) || t('فقط اعداد مجاز است'),
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
  b.nick_name = x.nick_name ?? '';
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

  b.friends_quantity = x.friends_quantity ?? '';
  b.supplier = x.supplier ?? '';
  b.phone_number = x.phone_number ?? '';
  b.history_jorm = x.history_jorm ?? '';
  b.activity_place = x.activity_place ?? '';
  b.old_job = x.old_job ?? '';
  b.now_job = x.now_job ?? '';
  b.gun_type = x.gun_type ?? '';
  b.tanzim = x.tanzim ?? '';
  b.takhalof_type = x.takhalof_type ?? '';
  b.Province_addr = x.Province_addr ?? '';
  b.coordinates = x.coordinates ?? '';

  b.resource_code = x.resource_code ?? x.resource?.code_number ?? '';
  b.resource_phone = x.resource_phone ?? x.resource?.phone_number ?? '';
  b.resource_id = x.resource_id ?? x.resource?.id ?? null;

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
    await repo.FetchNonResponsablePeopleData?.(id);
    const single = Array.isArray(repo.NonResponsablePeople)
      ? repo.NonResponsablePeople[0]
      : repo.NonResponsablePeople || {};
    if (!single) return;

    const mapped = mapFromApi(single);
    form.value = { ...mapped };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات اشخاص غیرمسؤول'));
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
    { label: t('نام'), value: v(form.value.name) },
    { label: t('نام مستعار'), value: v(form.value.nick_name) },
    { label: t('نام پدر'), value: v(form.value.father_name) },

    { label: t('ولایت اصلی'), value: v(form.value.main_province) },
    { label: t('ولسوالی اصلی'), value: v(form.value.main_district) },
    { label: t('قریه/ناحیه اصلی'), value: v(form.value.main_village) },

    { label: t('ولایت فعلی'), value: v(form.value.now_province) },
    { label: t('زون فعلی'), value: v(form.value.now_zone) },
    { label: t('ولسوالی فعلی'), value: v(form.value.now_district) },
    { label: t('قریه/ناحیه فعلی'), value: v(form.value.now_village) },

    { label: t('موقعیت مشخص'), value: v(form.value.now_specific_place) },
    { label: t('شماره سرک'), value: v(form.value.now_street_number) },
    { label: t('شماره خانه'), value: v(form.value.now_home_number) },

    { label: t('تعداد دوستان'), value: v(form.value.friends_quantity) },
    { label: t('تمویل‌کننده'), value: v(form.value.supplier) },
    { label: t('شماره تماس'), value: v(form.value.phone_number) },
    { label: t('محل فعالیت'), value: v(form.value.activity_place) },

    { label: t('سابقه جرم'), value: v(form.value.history_jorm) },
    { label: t('وظیفه قبلی'), value: v(form.value.old_job) },
    { label: t('وظیفه فعلی'), value: v(form.value.now_job) },

    { label: t('نوع سلاح'), value: v(form.value.gun_type) },
    { label: t('تنظیم'), value: v(form.value.tanzim) },
    { label: t('نوع تخلف'), value: v(form.value.takhalof_type) },

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
    <h3 class="title">فورم اشخاص غیرمسؤول</h3>
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
  <title>non_responsable_people_${safeId}_${safeName}</title>
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
  link.download = `اشخاص_غیرمسؤول_${safeFileName(form.value.name || 'جدید')}_${Date.now()}.doc`;
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
      const created = await repo.CreateNonResponsablePeople?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/nonresponsablepeople').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo.UpdateNonResponsablePeoples?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/nonresponsablepeople').catch(() => {});
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
  router.push('/app/nonresponsablepeople');
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
