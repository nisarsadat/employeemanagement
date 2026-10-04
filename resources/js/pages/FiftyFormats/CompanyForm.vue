<!-- src/views/companies/CompaniesForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش شرکت') : t('ایجاد شرکت جدید')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ شرکت موجود') : t('ثبت یک ریکارډ شرکت جدید در سیستم')
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
          <!-- ✅ Picture row (ONLY added part) -->
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
            <!-- Identity + addresses -->
            <v-col cols="12" md="12">
              <!-- Identity -->
              <!-- <div class="section-header">
                <span class="section-title">
                  {{ isEdit ? t('ویرایش ریکارډ شرکت') : t('ایجاد ریکارډ شرکت') }}
                </span>
              </div> -->

              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_name"
                    :label="t('نام شرکت *')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.required]"
                    :error="hasError('com_name')"
                    :error-messages="getErrorMessages('com_name')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_type"
                    :label="t('نوع شرکت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_type')"
                    :error-messages="getErrorMessages('com_type')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.product_type"
                    :label="t('نوع محصول/خدمت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('product_type')"
                    :error-messages="getErrorMessages('product_type')"
                  />
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_level"
                    :label="t('سطح شرکت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_level')"
                    :error-messages="getErrorMessages('com_level')"
                  />
                </v-col>

                <v-col cols="12" md="8">
                  <v-text-field
                    v-model="form.main_owner"
                    :label="t('مالک اصلی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('main_owner')"
                    :error-messages="getErrorMessages('main_owner')"
                  />
                </v-col>
              </v-row>

              <!-- Responsible -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('مسوول شرکت') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.responsible_name"
                    :label="t('مسوول')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_name')"
                    :error-messages="getErrorMessages('responsible_name')"
                  />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.responsible_f_name"
                    :label="t('نام پدر مسوول')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_f_name')"
                    :error-messages="getErrorMessages('responsible_f_name')"
                  />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.responsible_position"
                    :label="t('وظیفه/موقف')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_position')"
                    :error-messages="getErrorMessages('responsible_position')"
                  />
                </v-col>

                <v-col cols="12" md="12">
                  <v-text-field
                    v-model="form.responsible_phone"
                    :label="t('شماره تماس مسوول')"
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
                <span class="section-title">{{ t('آدرس شرکت') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="12">
                  <ProvinceDistrictNameSelect
                    v-model:province="form.com_province"
                    v-model:district="form.com_district"
                    :province-label="t('ولایت')"
                    :district-label="t('ولسوالی')"
                    :mdProvince="6"
                    :mdDistrict="6"
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <FormatZoneAutocomplete
                    v-model="form.com_zone"
                    :label="t('آمریت')"
                    :disabled="saving"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.com_village"
                    :label="t('قریه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_village')"
                    :error-messages="getErrorMessages('com_village')"
                  />
                </v-col>
                <v-col cols="12" md="8">
                  <v-text-field
                    v-model="form.com_specific_place"
                    :label="t('موقعیت مشخص')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_specific_place')"
                    :error-messages="getErrorMessages('com_specific_place')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_coordinates"
                    :label="t('مختصات')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_coordinates')"
                    :error-messages="getErrorMessages('com_coordinates')"
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Extra fields -->
          <!-- <div class="section-header mb-2">
            <span class="section-title">{{ t('کارمندان، محافظین و منبع') }}</span>
          </div> -->
          <v-row>
            <!-- Employees -->
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.male_employee_quantity_inner"
                type="number"
                :label="t('کارمندان داخلی مرد')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('male_employee_quantity_inner')"
                :error-messages="getErrorMessages('male_employee_quantity_inner')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.female_employee_quantity_inner"
                type="number"
                :label="t('کارمندان داخلی زن')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('female_employee_quantity_inner')"
                :error-messages="getErrorMessages('female_employee_quantity_inner')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.male_employee_quantity_out"
                type="number"
                :label="t('کارمندان بیرونی مرد')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('male_employee_quantity_out')"
                :error-messages="getErrorMessages('male_employee_quantity_out')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.female_employee_quantity_out"
                type="number"
                :label="t('کارمندان بیرونی زن')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('female_employee_quantity_out')"
                :error-messages="getErrorMessages('female_employee_quantity_out')"
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
                v-model="form.resource_phone_number"
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

          <!-- Actions (✅ bottom + PDF/Word) -->
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
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useWakilanoRepository } from '@/stores/WakilanoRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

  // ✅ Picture (ONLY added import)
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

  /* ✅ Picture state (ONLY added state) */
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

    // ✅ picture url from API (ONLY added field)
    picture_url: null,

    // identity
    com_name: '',
    com_type: '',
    product_type: '',
    com_level: '',
    main_owner: '',

    // responsible
    responsible_name: '',
    responsible_f_name: '',
    responsible_position: '',
    responsible_phone: '',

    // address
    com_province: '',
    com_zone: '',
    com_district: '',
    com_village: '',
    com_specific_place: '',
    com_coordinates: '',

    // employees & guards
    male_employee_quantity_inner: '',
    female_employee_quantity_inner: '',
    male_employee_quantity_out: '',
    female_employee_quantity_out: '',
    guard_quantity: '',

    Province_addr: '',

    // resource
    resource_code: '',
    resource_phone_number: '',
    resource_id: null,

    // note
    note: '',
  });

  const form = ref(blankForm());

  /* RULES */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    nonNegativeInt: (v) =>
      v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
    phone: (v) => !v || /^[+]?\d[\d\s\-]{10,10}$/.test(String(v)) || t('شماره تماس نادرست است'),
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
  form.value.resource_phone_number = r.phone_number ?? '';
}

function onResourceCleared() {
  form.value.resource_id = null;
  form.value.resource_code = '';
  form.value.resource_phone_number = '';
}

  /* MAP FROM API */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

    // ✅ picture mapping (ONLY added mapping)
    b.picture_url = x.picture ?? x.picture_url ?? null;

    b.com_name = x.com_name ?? '';
    b.com_type = x.com_type ?? '';
    b.product_type = x.product_type ?? '';
    b.com_level = x.com_level ?? '';
    b.main_owner = x.main_owner ?? '';

    b.responsible_name = x.responsible_name ?? '';
    b.responsible_f_name = x.responsible_f_name ?? '';
    b.responsible_position = x.responsible_position ?? '';
    b.responsible_phone = x.responsible_phone ?? '';

    b.com_province = x.com_province ?? '';
    b.com_zone = x.com_zone ?? '';
    b.com_district = x.com_district ?? '';
    b.com_village = x.com_village ?? '';
    b.com_specific_place = x.com_specific_place ?? '';
    b.com_coordinates = x.com_coordinates ?? '';

    b.male_employee_quantity_inner = x.male_employee_quantity_inner ?? '';
    b.female_employee_quantity_inner = x.female_employee_quantity_inner ?? '';
    b.male_employee_quantity_out = x.male_employee_quantity_out ?? '';
    b.female_employee_quantity_out = x.female_employee_quantity_out ?? '';
    b.guard_quantity = x.guard_quantity ?? '';

    b.Province_addr = x.Province_addr ?? '';

    const r = x.resource || {};
    b.resource_code = x.resource_code ?? r.code_number ?? '';
    b.resource_phone_number = x.resource_phone_number ?? r.phone_number ?? '';
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
      await repo.FetchCompanyData?.(id);
      const single = Array.isArray(repo.Company) ? repo.Company[0] : repo.Company || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };

      // ✅ reset picker state after load (ONLY added)
      pictureFile.value = null;
      pictureChanged.value = false;
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات شرکت'));
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
    const fd = toFormData(payload);

    // ✅ attach picture file (ONLY added behavior)
    if (pictureFile.value) fd.append('picture', pictureFile.value);

    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    const fd = toFormData(payload);

    // ✅ attach picture file ONLY when changed (ONLY added behavior)
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
      { label: t('نام شرکت'), value: v(form.value.com_name) },
      { label: t('نوع شرکت'), value: v(form.value.com_type) },
      { label: t('نوع محصول/خدمت'), value: v(form.value.product_type) },
      { label: t('سطح شرکت'), value: v(form.value.com_level) },
      { label: t('مالک اصلی'), value: v(form.value.main_owner) },

      { label: t('مسوول'), value: v(form.value.responsible_name) },
      { label: t('نام پدر مسوول'), value: v(form.value.responsible_f_name) },
      { label: t('وظیفه/موقف'), value: v(form.value.responsible_position) },
      { label: t('شماره تماس مسوول'), value: v(form.value.responsible_phone) },

      { label: t('ولایت'), value: v(form.value.com_province) },
      { label: t('آمریت'), value: v(form.value.com_zone) },
      { label: t('ولسوالی'), value: v(form.value.com_district) },
      { label: t('قریه'), value: v(form.value.com_village) },
      { label: t('موقعیت مشخص'), value: v(form.value.com_specific_place) },
      { label: t('مختصات'), value: v(form.value.com_coordinates) },

      { label: t('کارمندان داخلی مرد'), value: v(form.value.male_employee_quantity_inner) },
      { label: t('کارمندان داخلی زن'), value: v(form.value.female_employee_quantity_inner) },
      { label: t('کارمندان بیرونی مرد'), value: v(form.value.male_employee_quantity_out) },
      { label: t('کارمندان بیرونی زن'), value: v(form.value.female_employee_quantity_out) },

      { label: t('تعداد محافظ'), value: v(form.value.guard_quantity) },
      { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },

      { label: t('کود منبع'), value: v(form.value.resource_code) },
      { label: t('شماره منبع'), value: v(form.value.resource_phone_number) },

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
    <h3 class="title">فورم شرکت</h3>
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
    const safeName = esc(form.value.com_name || '');

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>company_${safeId}_${safeName}</title>
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
    link.download = `شرکت_${safeFileName(form.value.com_name || 'جدید')}_${Date.now()}.doc`;
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

    if (!form.value.com_name?.trim()) {
      showErrorMessage(t('نام شرکت الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateCompany?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/companies').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateCompany?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/companies').catch(() => {});
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
    // ✅ reset picture picker state too (ONLY added)
    pictureFile.value = null;
    pictureChanged.value = false;

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
    router.push('/app/companies');
  }

  /* INIT */
  onMounted(async () => {
    if (isEdit.value) {
      await loadRecord();
    } else {
      // ✅ ensure picker default state (ONLY added)
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

  /* small gap utility */
  .gap-2 {
    gap: 8px;
  }

  /* tighter fields */
  :deep(.v-field--variant-outlined .v-field__input) {
    padding-top: 4px;
    padding-bottom: 4px;
    min-height: 36px;
  }

  /* prettier buttons */
  .soft-btn {
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
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
