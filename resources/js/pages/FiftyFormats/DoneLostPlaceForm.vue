<!-- src/views/wakilano/DoneLostPlaceForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش محل گمشده/یافته') : t('ایجاد محل گمشده/یافته جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ DoneLostPlace موجود')
          : t('ثبت یک ریکارډ جدید DoneLostPlace در سیستم')
      "
    >
      
    </AppBar>

    <v-container class="mt-4">
      <v-form
        ref="formRef"
        v-model="formValid"
        lazy-validation
        @submit.prevent="onSubmit"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <v-card class="simple-form-card" elevation="1">
          <!-- Section: Place info -->
          <div class="section-header">
            <span class="section-title">{{ t('مشخصات محل') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.place_name"
                :label="t('نام محل *')"
                density="compact"
                variant="outlined"
                :rules="[rules.required]"
                :error="hasError('place_name')"
                :error-messages="getErrorMessages('place_name')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                density="compact"
                variant="outlined"
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.lost_quantity"
                :label="t('تعداد موارد')"
                density="compact"
                variant="outlined"
                type="number"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('lost_quantity')"
                :error-messages="getErrorMessages('lost_quantity')"
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.province"
                v-model:district="form.district"
                :province-label="t('ولایت محل')"
                :district-label="t('ولسوالی محل')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="12">
              <FormatZoneAutocomplete
                    v-model="form.zone"
                    :label="t('زون محل')"
                    :disabled="saving"
                  />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.village"
                :label="t('قریه محل')"
                density="compact"
                variant="outlined"
                :error="hasError('village')"
                :error-messages="getErrorMessages('village')"
              />
            </v-col>
            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.specific_place"
                :label="t('موقعیت مشخص')"
                density="compact"
                variant="outlined"
                :error="hasError('specific_place')"
                :error-messages="getErrorMessages('specific_place')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Section: Person info -->
          <div class="section-header">
            <span class="section-title">{{ t('شخص گمشده/یافته') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.lost_person_name"
                :label="t('نام شخص')"
                density="compact"
                variant="outlined"
                :error="hasError('lost_person_name')"
                :error-messages="getErrorMessages('lost_person_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.lost_person_f_name"
                :label="t('نام پدر شخص')"
                density="compact"
                variant="outlined"
                :error="hasError('lost_person_f_name')"
                :error-messages="getErrorMessages('lost_person_f_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.lost_person_phone"
                :label="t('شماره تماس شخص')"
                density="compact"
                variant="outlined"
                :rules="[rules.phone]"
                :error="hasError('lost_person_phone')"
                :error-messages="getErrorMessages('lost_person_phone')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.lost_person_job"
                :label="t('وظیفه شخص')"
                density="compact"
                variant="outlined"
                :error="hasError('lost_person_job')"
                :error-messages="getErrorMessages('lost_person_job')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Section: Addresses -->
          <div class="section-header">
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
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.main_village"
                :label="t('قریه اصلی')"
                density="compact"
                variant="outlined"
                :error="hasError('main_village')"
                :error-messages="getErrorMessages('main_village')"
              />
            </v-col>
          </v-row>

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
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.now_village"
                :label="t('قریه فعلی')"
                density="compact"
                variant="outlined"
                :error="hasError('now_village')"
                :error-messages="getErrorMessages('now_village')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Section: Source & other -->
          <div class="section-header">
            <span class="section-title">{{ t('منبع و سایر معلومات') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
                :rules="[rules.required]"
                density="compact"
                variant="outlined"
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
                readonly
              />
            </v-col>
            <!-- <v-col cols="12" md="4">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                density="compact"
                variant="outlined"
                :error="hasError('Province_addr')"
                :error-messages="getErrorMessages('Province_addr')"
              />
            </v-col> -->
          </v-row>

          <v-row>
            <v-col cols="12">
              <v-textarea
                v-model="form.note"
                :label="t('یادداشت')"
                density="compact"
                variant="outlined"
                auto-grow
                rows="2"
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ محل') }}
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
      <v-snackbar v-model="showError" color="error" timeout="4500">
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
  import { ref, computed, watch, onMounted, nextTick } from 'vue';
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

  const isEdit = computed(() => !!route.params.id);

  /* Form state */
  const formRef = ref(null);
  const formValid = ref(false);
  const saving = ref(false);

  const showError = ref(false);
  const errorMessage = ref('');
  const showSuccess = ref(false);
  const successMessage = ref('');

  function showErrorMessage(msg) {
    errorMessage.value = msg || t('خطا رخ داد');
    showError.value = true;
  }
  function showSuccessMessage(msg) {
    successMessage.value = msg || t('عملیات موفقانه انجام شد');
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

    // place
    place_name: '',
    coordinates: '',
    lost_quantity: '',

    province: '',
    zone: '',
    district: '',
    village: '',
    specific_place: '',

    // person
    lost_person_name: '',
    lost_person_f_name: '',
    lost_person_phone: '',
    lost_person_job: '',

    // main address
    main_province: '',
    main_district: '',
    main_village: '',

    // now address
    now_province: '',
    now_district: '',
    now_village: '',

    // source
    Province_addr: '',
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

  /* RESOURCES */
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

    b.place_name = x.place_name ?? '';
    b.coordinates = x.coordinates ?? '';
    b.lost_quantity = x.lost_quantity ?? '';

    b.province = x.province ?? '';
    b.zone = x.zone ?? '';
    b.district = x.district ?? '';
    b.village = x.village ?? '';
    b.specific_place = x.specific_place ?? '';

    b.lost_person_name = x.lost_person_name ?? '';
    b.lost_person_f_name = x.lost_person_f_name ?? '';
    b.lost_person_phone = x.lost_person_phone ?? '';
    b.lost_person_job = x.lost_person_job ?? '';

    b.main_province = x.main_province ?? '';
    b.main_district = x.main_district ?? '';
    b.main_village = x.main_village ?? '';

    b.now_province = x.now_province ?? '';
    b.now_district = x.now_district ?? '';
    b.now_village = x.now_village ?? '';

    b.Province_addr = x.Province_addr ?? '';

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
      await repo.FetchDoneLostPlaceData?.(id);
      const single = Array.isArray(repo.DoneLostPlace)
        ? repo.DoneLostPlace[0]
        : repo.DoneLostPlace || {};
      form.value = { ...mapFromApi(single || {}) };
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات ریکارډ'));
    }
  }

  /* FORM DATA */
  function toFormData(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
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
      { label: t('نام محل'), value: v(form.value.place_name) },
      { label: t('مختصات'), value: v(form.value.coordinates) },
      { label: t('تعداد موارد'), value: v(form.value.lost_quantity) },

      { label: t('ولایت محل'), value: v(form.value.province) },
      { label: t('زون محل'), value: v(form.value.zone) },
      { label: t('ولسوالی محل'), value: v(form.value.district) },
      { label: t('قریه محل'), value: v(form.value.village) },
      { label: t('موقعیت مشخص'), value: v(form.value.specific_place) },

      { label: t('نام شخص'), value: v(form.value.lost_person_name) },
      { label: t('نام پدر شخص'), value: v(form.value.lost_person_f_name) },
      { label: t('شماره تماس شخص'), value: v(form.value.lost_person_phone) },
      { label: t('وظیفه شخص'), value: v(form.value.lost_person_job) },

      { label: t('ولایت اصلی'), value: v(form.value.main_province) },
      { label: t('ولسوالی اصلی'), value: v(form.value.main_district) },
      { label: t('قریه اصلی'), value: v(form.value.main_village) },

      { label: t('ولایت فعلی'), value: v(form.value.now_province) },
      { label: t('ولسوالی فعلی'), value: v(form.value.now_district) },
      { label: t('قریه فعلی'), value: v(form.value.now_village) },

      { label: t('کود منبع'), value: v(form.value.resource_code) },
      { label: t('شماره منبع'), value: v(form.value.resource_phone) },
      { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },

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
    <h3 class="title">فورم محل گمشده/یافته</h3>
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
    const safeName = esc(form.value.place_name || '');

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>done_lost_place_${safeId}_${safeName}</title>
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
    link.download = `محل_گمشده_یافته_${safeFileName(
      form.value.place_name || 'جدید'
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
    if (f?.validate) {
      const { valid } = await f.validate();
      if (!valid) {
        showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
        await nextTick();
        scrollToFirstInvalid();
        return;
      }
    }

    if (!form.value.place_name?.trim()) {
      showErrorMessage(t('نام محل الزامی است'));
      return;
    }

    saving.value = true;

    try {
      const fd = toFormData({ ...form.value });

      if (!isEdit.value) {
        const created = await repo.CreateDoneLostPlace?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('ریکارد موفقانه ثبت شد'));
          router.push('/app/donelostplaces').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const updated = await repo.UpdateDoneLostPlace?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/donelostplaces').catch(() => {});
        } else {
          showErrorMessage(t('ذخیرهٔ تغییرات ناکام شد'));
        }
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در ذخیره معلومات'));
    } finally {
      saving.value = false;
    }
  }

  /* RESET & NAVIGATION */
  function resetForm() {
    clearBackendErrors();
    if (isEdit.value) {
      loadRecord();
    } else {
      form.value = blankForm();
    }
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/donelostplaces');
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
    padding-block: 4px;
  }
  .section-title {
    font-size: 0.9rem;
    font-weight: 600;
    opacity: 0.85;
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
