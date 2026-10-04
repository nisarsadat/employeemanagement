<!-- src/views/wakilano/ComplexForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش کمپلکس') : t('ایجاد کمپلکس جدید')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ کمپلکس موجود') : t('ثبت یک ریکارډ کمپلکس جدید در سیستم')
      "
    >
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
          <!-- ✅ Picture row (same pattern) -->
          <v-row>
            <v-col cols="12" md="3">
              <PicturePicker
                v-model:file="pictureFile"
                v-model:url="form.picture_url"
                :label="t('تصویر')"
                :alt="t('عکس')"
                :change-text="t('تغییر عکس')"
                :view-text="t('نمایش')"
                :close-text="t('بستن')"
                :preview-title="t('پیش نمایش')"
                @change="({ changed }) => (pictureChanged = !!changed)"
              />
            </v-col>
          </v-row>

          <v-row>
            <!-- Full-width form -->
            <v-col cols="12" md="12">
              <!-- Identity / activity -->
              <v-row>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.complex_name"
                    :label="t('نام کمپلکس *')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.required]"
                    :error="hasError('complex_name')"
                    :error-messages="getErrorMessages('complex_name')"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.activity_type"
                    :label="t('نوع فعالیت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('activity_type')"
                    :error-messages="getErrorMessages('activity_type')"
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
              </v-row>

              <!-- Flows -->
              <v-row>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.enter_male_quantity"
                    type="number"
                    :label="t('ورود ذکور')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.nonNegativeInt]"
                    :error="hasError('enter_male_quantity')"
                    :error-messages="getErrorMessages('enter_male_quantity')"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.outer_male_quantity"
                    type="number"
                    :label="t('خروج ذکور')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.nonNegativeInt]"
                    :error="hasError('outer_male_quantity')"
                    :error-messages="getErrorMessages('outer_male_quantity')"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.enter_femal_quantity"
                    type="number"
                    :label="t('ورود اناث')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.nonNegativeInt]"
                    :error="hasError('enter_femal_quantity')"
                    :error-messages="getErrorMessages('enter_femal_quantity')"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.outer_femal_quantity"
                    type="number"
                    :label="t('خروج اناث')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.nonNegativeInt]"
                    :error="hasError('outer_femal_quantity')"
                    :error-messages="getErrorMessages('outer_femal_quantity')"
                  />
                </v-col>
              </v-row>

              <!-- Responsible -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('مسؤول کمپلکس') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.responsible_name"
                    :label="t('مسؤول')"
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
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.responsible_job"
                    :label="t('مسول موقف')"
                    variant="outlined"
                    density="compact"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.responsible_country"
                    :label="t('کشور مسؤول')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_country')"
                    :error-messages="getErrorMessages('responsible_country')"
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

              <!-- Addresses -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس کمپلکس') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="12">
                  <ProvinceDistrictNameSelect
                    v-model:province="form.province"
                    v-model:district="form.district"
                    :province-label="t('ولایت')"
                    :district-label="t('ولسوالی')"
                    :mdProvince="6"
                    :mdDistrict="6"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <FormatZoneAutocomplete v-model="form.zone" :label="t('آمریت')" :disabled="saving" />
                </v-col>

                <v-col cols="12" md="6">
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
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.street_number"
                    :label="t('نمبر سرک')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('street_number')"
                    :error-messages="getErrorMessages('street_number')"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.home_number"
                    :label="t('نمبر خانه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('home_number')"
                    :error-messages="getErrorMessages('home_number')"
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
              </v-row>

              <v-divider class="my-4" />

              <!-- Extra fields + Resource -->
              <div class="section-header mb-2">
                <span class="section-title">{{ t('منبع و ملاحظات') }}</span>
              </div>
              <v-row>
                <!-- Resource mapping -->
                <v-col cols="12" md="4">
                  <ResourceRemoteAutocomplete
  v-model="form.resource_id"
  :label="t('کود منبع')"
  density="compact"
  variant="outlined"
  :rules="[rules.required]"
  :item-title="resourceTitle"
  @selected="onResourceSelected"
  @cleared="onResourceCleared"
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
                <v-col cols="12" md="12">
                  <v-text-field
                    v-model="form.note"
                    :label="t('ملاحظات')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('note')"
                    :error-messages="getErrorMessages('note')"
                  />
                </v-col>
              </v-row>
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت') }}
              </v-btn>

              <v-btn
                color="warning"
                variant="tonal"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-broom"
                @click="resetForm"
              >
                {{ t('پاک کردن فرم') }}
              </v-btn>

              <v-btn
                color="primary"
                variant="tonal"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-printer"
                @click="printForm"
              >
                {{ t('چاپ') }}
              </v-btn>

              <!-- ✅ Simple Export (NO image / NO app headers) - works even for blank -->
              <v-btn
                color="red"
                variant="tonal"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-file-pdf-box"
                @click="downloadPdf"
              >
                {{ t('PDF') }}
              </v-btn>

              <v-btn
                color="blue"
                variant="tonal"
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
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useWakilanoRepository } from '@/stores/WakilanoRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

  // ✅ added for picture
  import PicturePicker from '@/components/form/PicturePicker.vue';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';
  import { printBlankForm } from '@/utils/printBlankForm';

  const repo = useWakilanoRepository();
  const route = useRoute();
  const router = useRouter();

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* MODE */
  const isEdit = computed(() => !!route.params.id);

  /* ✅ Picture state */
  const pictureFile = ref(null); // File | null
  const pictureChanged = ref(false); // boolean

  /* FORM / STATE */
  const formRef = ref(null);
  const formValid = ref(false);
  const saving = ref(false);

  const showError = ref(false);
  const errorMessage = ref('');
  const showSuccess = ref(false);
  const successMessage = ref('');

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
    backendErrors.value?.[field] ?? [];
    return backendErrors.value?.[field] ?? [];
  }

  /* FORM MODEL */
  const blankForm = () => ({
    id: null,

    // ✅ picture url from API
    picture_url: null,

    // identity / activity
    complex_name: '',
    activity_type: '',
    activity_place: '',
    supplier: '',

    // flows
    enter_male_quantity: '',
    outer_male_quantity: '',
    enter_femal_quantity: '',
    outer_femal_quantity: '',

    // responsible
    responsible_name: '',
    responsible_job: '',
    responsible_f_name: '',
    responsible_phone: '',
    responsible_country: '',

    // addresses
    province: '',
    zone: '',
    district: '',
    village: '',
    specefic_place: '',
    street_number: '',
    home_number: '',
    coordinates: '',
    Province_addr: '',

    // security
    guard_quantity: '',

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
    nonNegativeInt: (v) =>
      v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
    phone: (v) => !v || /^[+]?\d[\d\s\-]{5,}$/.test(String(v)) || t('شماره تماس نادرست است'),
  };

function resourceTitle(r) {
  if (!r) return '';
  const code = r.code_number ?? '';
  const name = r.name ?? '';
  return [code, name].filter(Boolean).join(' - ');
}

function onResourceSelected(r) {
  if (!r) return;
  form.value.resource_id = r.id ?? null;
  form.value.resource_code = r.code_number ?? '';
  form.value.resource_phone = r.phone_number ?? '';
}

function onResourceCleared() {
  form.value.resource_id = null;
  form.value.resource_code = '';
  form.value.resource_phone = '';
}

  /* MAP FROM API */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

    // ✅ picture mapping
    b.picture_url = x.picture ?? x.picture_url ?? null;

    b.complex_name = x.complex_name ?? '';
    b.activity_type = x.activity_type ?? '';
    b.activity_place = x.activity_place ?? '';
    b.supplier = x.supplier ?? '';

    b.enter_male_quantity = x.enter_male_quantity ?? '';
    b.outer_male_quantity = x.outer_male_quantity ?? '';
    b.enter_femal_quantity = x.enter_femal_quantity ?? '';
    b.outer_femal_quantity = x.outer_femal_quantity ?? '';

    b.responsible_name = x.responsible_name ?? '';
    b.responsible_job = x.responsible_job ?? '';
    b.responsible_f_name = x.responsible_f_name ?? '';
    b.responsible_phone = x.responsible_phone ?? '';
    b.responsible_country = x.responsible_country ?? '';

    b.province = x.province ?? '';
    b.zone = x.zone ?? '';
    b.district = x.district ?? '';
    b.village = x.village ?? '';
    b.specefic_place = x.specefic_place ?? '';
    b.street_number = x.street_number ?? '';
    b.home_number = x.home_number ?? '';
    b.coordinates = x.coordinates ?? '';
    b.Province_addr = x.Province_addr ?? '';

    b.guard_quantity = x.guard_quantity ?? '';

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

      // ✅ reset picker state
      pictureFile.value = null;
      pictureChanged.value = false;

      return;
    }
    try {
      await repo.FetchComplexData?.(id);
      const single = Array.isArray(repo.Complex) ? repo.Complex[0] : repo.Complex || {};
      form.value = { ...mapFromApi(single || {}) };

      // ✅ reset picker state after load
      pictureFile.value = null;
      pictureChanged.value = false;
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات کمپلکس'));
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
    const fd = toFormData({ ...form.value });

    // ✅ attach picture
    if (pictureFile.value) fd.append('picture', pictureFile.value);

    return fd;
  }

  function buildUpdateFormData() {
    const fd = toFormData({ ...form.value });

    // ✅ attach only when changed
    if (pictureChanged.value && pictureFile.value) fd.append('picture', pictureFile.value);

    return fd;
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
      { label: t('نام کمپلکس'), value: v(form.value.complex_name) },
      { label: t('نوع فعالیت'), value: v(form.value.activity_type) },
      { label: t('محل فعالیت'), value: v(form.value.activity_place) },
      { label: t('تمویل‌کننده'), value: v(form.value.supplier) },

      { label: t('ورود ذکور'), value: v(form.value.enter_male_quantity) },
      { label: t('خروج ذکور'), value: v(form.value.outer_male_quantity) },
      { label: t('ورود اناث'), value: v(form.value.enter_femal_quantity) },
      { label: t('خروج اناث'), value: v(form.value.outer_femal_quantity) },

      { label: t('مسؤول'), value: v(form.value.responsible_name) },
      { label: t('مسؤول موقف'), value: v(form.value.responsible_job) },
      { label: t('نام پدر مسؤول'), value: v(form.value.responsible_f_name) },
      { label: t('شماره تماس مسؤول'), value: v(form.value.responsible_phone) },
      { label: t('کشور مسؤول'), value: v(form.value.responsible_country) },

      { label: t('ولایت'), value: v(form.value.province) },
      { label: t('آمریت'), value: v(form.value.zone) },
      { label: t('ولسوالی'), value: v(form.value.district) },
      { label: t('قریه'), value: v(form.value.village) },
      { label: t('موقعیت مشخص'), value: v(form.value.specefic_place) },
      { label: t('نمبر سرک'), value: v(form.value.street_number) },
      { label: t('نمبر خانه'), value: v(form.value.home_number) },

      { label: t('مختصات'), value: v(form.value.coordinates) },
      { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },
      { label: t('تعداد محافظ'), value: v(form.value.guard_quantity) },

      { label: t('کود منبع'), value: v(form.value.resource_code) },
      { label: t('شماره منبع'), value: v(form.value.resource_phone) },

      { label: t('ملاحظات'), value: v(form.value.note) },
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
    <h3 class="title">فورم کمپلکس</h3>
    <table class="simple-table">
      <tbody>
        ${rows}
      </tbody>
    </table>
    <div class="sig">
      <div>${t('امضا مسئول')}: ____________________</div>
      <div>${t('امضا ثبت کننده')}: ____________________</div>
      <div>${t('تاریخ')}: ____________________</div>
    </div>
  `;
  }
  function buildDocumentHtml() {
    const bodyHtml = buildSimpleTableHtml();
    const safeId = esc(form.value.id || '');
    const safeName = esc(form.value.complex_name || '');

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>complex_${safeId}_${safeName}</title>
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

  /* PRINT BLANK FORM (empty fields, same design, one A4 page) */
  function printForm(e) {
    printBlankForm(e, { onError: showErrorMessage });
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
    link.download = `کمپلکس_${safeFileName(form.value.complex_name || 'جدید')}_${Date.now()}.doc`;
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

    if (!form.value.complex_name?.trim()) {
      showErrorMessage(t('نام کمپلکس الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateComplex?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/complexes').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateComplex?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/complexes').catch(() => {});
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

      // ✅ reset picker state
      pictureFile.value = null;
      pictureChanged.value = false;
    }

    clearBackendErrors();
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/complexes');
  }

  /* INIT */
  onMounted(async () => {
    if (isEdit.value) {
      await loadRecord();
    } else {
      // ✅ ensure clean picker state in create mode
      pictureFile.value = null;
      pictureChanged.value = false;
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
