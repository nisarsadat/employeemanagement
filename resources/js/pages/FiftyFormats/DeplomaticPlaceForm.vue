<!-- src/views/deplomatic/DeplomaticPlaceForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش محل دیپلوماتیک') : t('ایجاد محل دیپلوماتیک جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ محل دیپلوماتیک موجود')
          : t('ثبت یک ریکارډ محل دیپلوماتیک جدید در سیستم')
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
          <!-- Identity -->
          <div class="section-header">
            <span class="section-title">
              {{ isEdit ? t('ویرایش ریکارډ محل دیپلوماتیک') : t('ایجاد ریکارډ محل دیپلوماتیک') }}
            </span>
          </div>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.deplomatic_place_name"
                :label="t('نام محل دیپلوماتیک *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('deplomatic_place_name')"
                :error-messages="getErrorMessages('deplomatic_place_name')"
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
              /> </v-col
            ><v-col cols="12" md="3">
              <v-text-field
                v-model="form.supplier_country"
                :label="t('کشوره حمایه  کننده')"
                variant="outlined"
                density="compact"
              />
            </v-col>
          </v-row>

          <!-- Counts -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('آمار ورود/خروج') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.enter_male_quantity"
                type="number"
                :label="t('ورودی ذکور')"
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
                :label="t('خروجی ذکور')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('outer_male_quantity')"
                :error-messages="getErrorMessages('outer_male_quantity')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.enter_female_quantity"
                type="number"
                :label="t('ورودی اناث')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('enter_female_quantity')"
                :error-messages="getErrorMessages('enter_female_quantity')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.outer_female_quantity"
                type="number"
                :label="t('خروجی اناث')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('outer_female_quantity')"
                :error-messages="getErrorMessages('outer_female_quantity')"
              />
            </v-col>
          </v-row>

          <!-- Responsible -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('مسؤول محل دیپلوماتیک') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="3">
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
                v-model="form.responsible_possition"
                :label="t('وظیفه/موقف مسؤول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_possition')"
                :error-messages="getErrorMessages('responsible_possition')"
              />
            </v-col>
            <v-col cols="12" md="12">
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
            <span class="section-title">{{ t('آدرس محل دیپلوماتیک') }}</span>
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
              <FormatZoneAutocomplete v-model="form.zone" :label="t('زون')" :disabled="saving" />
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

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.specefic_place"
                :label="t('موقعیت مشخص')"
                variant="outlined"
                density="compact"
                :error="hasError('specefic_place')"
                :error-messages="getErrorMessages('specefic_place')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.street_number"
                :label="t('نمبر سرک')"
                variant="outlined"
                density="compact"
                :error="hasError('street_number')"
                :error-messages="getErrorMessages('street_number')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.home_number"
                :label="t('نمبر خانه')"
                variant="outlined"
                density="compact"
                :error="hasError('home_number')"
                :error-messages="getErrorMessages('home_number')"
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                variant="outlined"
                density="compact"
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>
            <!-- <v-col cols="12" md="6">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                variant="outlined"
                density="compact"
                :error="hasError('Province_addr')"
                :error-messages="getErrorMessages('Province_addr')"
              />
            </v-col> -->
          </v-row>

          <!-- Security / Resource / Note -->
          <v-divider class="my-4" />
          <div class="section-header mb-2">
            <span class="section-title">{{ t('امنیت، منبع و یادداشت') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="6">
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

            <v-col cols="12" md="3">
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
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.resource_phone"
                :label="t('شماره منبع')"
                variant="outlined"
                density="compact"
                readonly
              />
            </v-col>

            <v-col cols="12" md="12">
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ') }}
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

    deplomatic_place_name: '',
    activity_type: '',
    activity_place: '',
    supplier_country: '',

    enter_male_quantity: '',
    outer_male_quantity: '',
    enter_female_quantity: '',
    outer_female_quantity: '',

    responsible_name: '',
    responsible_f_name: '',
    responsible_country: '',
    responsible_possition: '',
    responsible_phone: '',

    province: '',
    zone: '',
    district: '',
    village: '',
    specefic_place: '',
    street_number: '',
    home_number: '',
    coordinates: '',
    Province_addr: '',

    guard_quantity: '',

    resource_code: '',
    resource_phone: '',
    resource_id: null,

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

    b.deplomatic_place_name = x.deplomatic_place_name ?? '';
    b.activity_type = x.activity_type ?? '';
    b.activity_place = x.activity_place ?? '';
    b.supplier_country = x.supplier_country ?? '';

    b.enter_male_quantity = x.enter_male_quantity ?? '';
    b.outer_male_quantity = x.outer_male_quantity ?? '';
    b.enter_female_quantity = x.enter_female_quantity ?? '';
    b.outer_female_quantity = x.outer_female_quantity ?? '';

    b.responsible_name = x.responsible_name ?? '';
    b.responsible_f_name = x.responsible_f_name ?? '';
    b.responsible_country = x.responsible_country ?? '';
    b.responsible_possition = x.responsible_possition ?? '';
    b.responsible_phone = x.responsible_phone ?? '';

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
      return;
    }
    try {
      await repo.FetchDeplomaticPlaceData?.(id);
      const single = Array.isArray(repo.DeplomaticPlace)
        ? repo.DeplomaticPlace[0]
        : repo.DeplomaticPlace || {};
      form.value = { ...mapFromApi(single || {}) };
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات محل دیپلوماتیک'));
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
      { label: t('نام محل دیپلوماتیک'), value: v(form.value.deplomatic_place_name) },
      { label: t('نوع فعالیت'), value: v(form.value.activity_type) },
      { label: t('محل فعالیت'), value: v(form.value.activity_place) },
      { label: t('کشور حمایه کننده'), value: v(form.value.supplier_country) },

      { label: t('ورودی ذکور'), value: v(form.value.enter_male_quantity) },
      { label: t('خروجی ذکور'), value: v(form.value.outer_male_quantity) },
      { label: t('ورودی اناث'), value: v(form.value.enter_female_quantity) },
      { label: t('خروجی اناث'), value: v(form.value.outer_female_quantity) },

      { label: t('مسؤول'), value: v(form.value.responsible_name) },
      { label: t('نام پدر مسؤول'), value: v(form.value.responsible_f_name) },
      { label: t('کشور مسؤول'), value: v(form.value.responsible_country) },
      { label: t('وظیفه/موقف مسؤول'), value: v(form.value.responsible_possition) },
      { label: t('شماره تماس مسؤول'), value: v(form.value.responsible_phone) },

      { label: t('ولایت'), value: v(form.value.province) },
      { label: t('زون'), value: v(form.value.zone) },
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
    <h3 class="title">فورم محل دیپلوماتیک</h3>
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
    const safeName = esc(form.value.deplomatic_place_name || '');

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>deplomatic_${safeId}_${safeName}</title>
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
    link.download = `دیپلوماتیک_${safeFileName(
      form.value.deplomatic_place_name || 'جدید'
    )}_${Date.now()}.doc`;
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

    if (!form.value.deplomatic_place_name?.trim()) {
      showErrorMessage(t('نام محل دیپلوماتیک الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateDeplomaticPlace?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/deplomaticplaces').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateDeplomaticPlace?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/deplomaticplaces').catch(() => {});
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
    router.push('/app/deplomaticplaces');
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

  .ga-2 {
    gap: 8px;
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
