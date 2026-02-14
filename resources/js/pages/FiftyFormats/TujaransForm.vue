<!-- src/views/tujaran/TujaransForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش تاجر') : t('ایجاد تاجر جدید')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ تاجر موجود') : t('ثبت یک ریکارډ جدید تاجر در سیستم')
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
          <!-- ✅ PICTURE (added like others) -->
          <v-row class="mb-2">
            <v-col cols="12">
              <div class="d-flex align-center justify-space-between flex-wrap" style="gap: 12px">
                <div class="section-header">
                  <span class="section-title">{{ t('عکس') }}</span>
                </div>

                <div class="d-flex align-center" style="gap: 12px">
                  <v-avatar size="72" class="picture-avatar" rounded="xl">
                    <v-img v-if="picturePreviewUrl" :src="picturePreviewUrl" cover />
                    <v-icon v-else size="30">mdi-account</v-icon>
                  </v-avatar>

                  <div class="d-flex flex-column" style="gap: 8px">
                    <v-btn
                      variant="tonal"
                      color="primary"
                      class="text-none rounded-pill"
                      prepend-icon="mdi-image-edit-outline"
                      @click="pickPicture"
                    >
                      {{ t('انتخاب عکس') }}
                    </v-btn>

                    <v-btn
                      v-if="picturePreviewUrl"
                      variant="text"
                      color="error"
                      class="text-none rounded-pill"
                      prepend-icon="mdi-delete-outline"
                      @click="clearPicture"
                    >
                      {{ t('حذف عکس') }}
                    </v-btn>
                  </div>
                </div>

                <input
                  ref="pictureInputRef"
                  type="file"
                  accept="image/*"
                  class="d-none"
                  @change="onPictureSelected"
                />
              </div>
            </v-col>
          </v-row>

          <!-- Identity -->
          <v-row>
           
            <v-col cols="12" md="6">
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

            <v-col cols="12" md="6">
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
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.main_province"
                v-model:district="form.main_district"
                :province-label="t('ولایت اصلی')"
                :district-label="t('ولسوالی اصلی')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>

            <v-col cols="12" md="6">
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
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.now_province"
                v-model:district="form.now_district"
                :province-label="t('ولایت فعلی')"
                :district-label="t('ولسوالی فعلی')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>

            <v-col cols="12" md="3">
              <FormatZoneAutocomplete
                v-model="form.now_zone"
                :label="t('زون فعلی')"
                :disabled="saving"
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

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.now_street_number"
                :label="t('شماره سرک فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_street_number')"
                :error-messages="getErrorMessages('now_street_number')"
              />
            </v-col>

            <v-col cols="12" md="6">
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

          <!-- Work & others -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('وظیفه، محافظین و تماس') }}</span>
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
                v-model="form.organ"
                :label="t('اداره/ارگان')"
                variant="outlined"
                density="compact"
                :error="hasError('organ')"
                :error-messages="getErrorMessages('organ')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.guard_guntity"
                :label="t('تعداد محافظین')"
                variant="outlined"
                density="compact"
                :error="hasError('guard_guntity')"
                :error-messages="getErrorMessages('guard_guntity')"
              />
            </v-col>

            <!-- Phone (✅ exactly 10 digits) -->
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                variant="outlined"
                density="compact"
                :rules="[rules.phoneDigits]"
                :error="hasError('phone_number')"
                :error-messages="getErrorMessages('phone_number')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.tahdid_mutawaji"
                :label="t('متوجه تهدید')"
                variant="outlined"
                density="compact"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="form.people_type"
                :items="typetujaran"
                :label="t('قسمت افراد')"
                variant="outlined"
                density="compact"
                clearable
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Province / coordinates / resource / note -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('سایر معلومات') }}</span>
          </div>
          <v-row>
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
                :rules="[rules.required]"
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

          <!-- Actions (✅ bottom, with PDF + Word) -->
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
  import { ref, computed, onMounted, watch, nextTick, onBeforeUnmount } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useBadmashanoRepository } from '@/stores/BadmashanoRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

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
  const typetujaran = computed(() => [t('علما'), t('متنفذین'), t('عالي رتبه'), 'تجاران']);

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

  /* RULES */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    phoneDigits: (v) => {
      if (!v) return true;
      const digits = String(v).replace(/\D/g, '');
      return digits.length === 10 || t('شماره تماس باید دقیقاً ۱۰ رقم باشد');
    },
  };

  /* ✅ PICTURE STATE */
  const pictureInputRef = ref(null);
  const pictureFile = ref(null);
  const picturePreviewUrl = ref('');

  function revokePreview() {
    if (picturePreviewUrl.value && picturePreviewUrl.value.startsWith('blob:')) {
      URL.revokeObjectURL(picturePreviewUrl.value);
    }
  }

  function pickPicture() {
    pictureInputRef.value?.click?.();
  }

  function onPictureSelected(e) {
    const file = e?.target?.files?.[0];
    if (!file) return;

    if (!String(file.type || '').startsWith('image/')) {
      showErrorMessage(t('لطفاً یک فایل عکس انتخاب کنید'));
      return;
    }

    revokePreview();
    pictureFile.value = file;
    picturePreviewUrl.value = URL.createObjectURL(file);
  }

  function clearPicture() {
    revokePreview();
    pictureFile.value = null;
    picturePreviewUrl.value = '';
    if (pictureInputRef.value) pictureInputRef.value.value = '';
  }

  onBeforeUnmount(() => {
    revokePreview();
  });

  /* FORM MODEL */
  const blankForm = () => ({
    id: null,

    // ✅ returned by API
    picture: null,
    picture_url: null,

    name: '',
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
    now_job: '',
    organ: '',

    guard_guntity: '',
    phone_number: '',
    tahdid_mutawaji: '',
    people_type: '',

    Province_addr: '',
    coordinates: '',

    resource_code: '',
    resource_phone: '',
    resource_id: null,

    note: '',
  });

  const form = ref(blankForm());

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

    b.picture = x.picture ?? null;
    b.picture_url = x.picture_url ?? null;

    b.name = x.name ?? '';
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
    b.now_job = x.now_job ?? '';
    b.organ = x.organ ?? '';

    b.guard_guntity = x.guard_guntity ?? '';
    b.phone_number = x.phone_number ?? '';
    b.tahdid_mutawaji = x.tahdid_mutawaji ?? '';
    b.people_type = x.people_type ?? '';

    b.Province_addr = x.Province_addr ?? '';
    b.coordinates = x.coordinates ?? '';

    const r = x.resource || {};
    b.resource_code = x.resource_code ?? r.code_number ?? '';
    b.resource_phone = x.resource_phone  ?? '';
    b.resource_id = x.resource_id ?? r.id ?? null;

    b.note = x.note ?? '';

    return b;
  }

  async function loadRecord() {
    const id = route.params.id;
    if (!id) {
      form.value = blankForm();
      clearPicture();
      return;
    }

    try {
      await repo.FetchTujaranData?.(id);
      const single = Array.isArray(repo.Tujaran) ? repo.Tujaran[0] : repo.Tujaran || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };

      // ✅ show server image unless user selected a new one
      if (!pictureFile.value) {
        picturePreviewUrl.value = mapped.picture_url || '';
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات تاجر'));
    }
  }

  /* FORM DATA HELPERS */
  function toFormData(obj) {
    const fd = new FormData();
    Object.entries(obj).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });

    // ✅ send picture
    if (pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

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
      { label: t('وظیفه فعلی'), value: v(form.value.now_job) },
      { label: t('اداره/ارگان'), value: v(form.value.organ) },

      { label: t('تعداد محافظین'), value: v(form.value.guard_guntity) },
      { label: t('شماره تماس'), value: v(form.value.phone_number) },
      { label: t('متوجه تهدید'), value: v(form.value.tahdid_mutawaji) },
      { label: t('نوع افراد'), value: v(form.value.people_type) },

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
    <h3 class="title">فورم تاجر</h3>
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
  <title>tujaran_${safeId}_${safeName}</title>
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
    link.download = `تاجر_${safeFileName(form.value.name || 'جدید')}_${Date.now()}.doc`;
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
        const created = await repo.CreateTujaran?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/tujarans').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateTujaran?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/tujarans').catch(() => {});
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
      clearPicture();
    }

    clearBackendErrors();
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/tujarans');
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

  .picture-avatar {
    border: 1px solid rgba(0, 0, 0, 0.08);
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
