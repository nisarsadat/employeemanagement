<!-- src/views/university/UniversityForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش دانشگاه') : t('ایجاد دانشگاه جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ دانشگاه موجود')
          : t('ثبت یک ریکارډ دانشگاه جدید در سیستم')
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
          <!-- ✅ Picture row (added) -->
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

          <!-- Identity + responsible + counts + address -->
          <v-row>
            <!-- Identity -->

            <!-- Identity row -->
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.uni_name"
                :label="t('نام دانشگاه *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('uni_name')"
                :error-messages="getErrorMessages('uni_name')"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.uni_type"
                :label="t('نوع دانشگاه')"
                variant="outlined"
                density="compact"
                :error="hasError('uni_type')"
                :error-messages="getErrorMessages('uni_type')"
              />
            </v-col>
            <!-- <v-col cols="12" md="4">
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

          <!-- Responsible -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('مسوول دانشگاه') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_name"
                :label="t('مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_name')"
                :error-messages="getErrorMessages('responsible_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_f_name"
                :label="t('نام پدر مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_f_name')"
                :error-messages="getErrorMessages('responsible_f_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_position"
                :label="t('وظیفه/موقف')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_position')"
                :error-messages="getErrorMessages('responsible_position')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_phone"
                :label="t('شماره تماس مسوول')"
                variant="outlined"
                density="compact"
                inputmode="numeric"
                :rules="[rules.phone10]"
                :error="hasError('responsible_phone')"
                :error-messages="getErrorMessages('responsible_phone')"
              />
            </v-col>
          </v-row>

          <!-- Teachers / Students -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('استادان و شاگردان') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.male_teachers_quantity"
                type="number"
                :label="t('استادان مرد')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('male_teachers_quantity')"
                :error-messages="getErrorMessages('male_teachers_quantity')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.female_teachers_quantity"
                type="number"
                :label="t('استادان زن')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('female_teachers_quantity')"
                :error-messages="getErrorMessages('female_teachers_quantity')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.male_students_quantity"
                type="number"
                :label="t('شاگردان مرد')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('male_students_quantity')"
                :error-messages="getErrorMessages('male_students_quantity')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.female_students_quantity"
                type="number"
                :label="t('شاگردان زن')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('female_students_quantity')"
                :error-messages="getErrorMessages('female_students_quantity')"
              />
            </v-col>
          </v-row>

          <!-- Address -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('آدرس دانشگاه') }}</span>
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
                v-model="form.specific_place"
                :label="t('موقعیت مشخص')"
                variant="outlined"
                density="compact"
                :error="hasError('specific_place')"
                :error-messages="getErrorMessages('specific_place')"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.street_number"
                :label="t('شماره سرک')"
                variant="outlined"
                density="compact"
                :error="hasError('street_number')"
                :error-messages="getErrorMessages('street_number')"
              />
            </v-col>
            <!-- Coordinates -->
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

            <!-- <v-col cols="12" md="9">
              <v-text-field
                v-model="form.home_number"
                :label="t('شماره خانه')"
                variant="outlined"
                density="compact"
                :error="hasError('home_number')"
                :error-messages="getErrorMessages('home_number')"
              />
            </v-col> -->
          </v-row>

          <v-divider class="my-4" />

          <!-- Extra + resource + note -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('معلومات منبع') }}</span>
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

          <!-- ✅ EXPORT BUTTONS (BOTTOM) -->
          <v-row class="mt-2">
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
                color="secondary"
                variant="tonal"
                class="text-none rounded-pill soft-btn"
                prepend-icon="mdi-file-pdf-box"
                @click="downloadPdf"
              >
                {{ t('PDF ساده') }}
              </v-btn>

              <v-btn
                color="secondary"
                variant="tonal"
                class="text-none rounded-pill soft-btn"
                prepend-icon="mdi-file-word-box"
                @click="downloadWord"
              >
                {{ t('Word ساده') }}
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
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useWakilanoRepository } from '@/stores/WakilanoRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

  // ✅ Picture (added)
  import PicturePicker from '@/components/form/PicturePicker.vue';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';

  const repo = useWakilanoRepository();
  const route = useRoute();
  const router = useRouter();

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* MODE */
  const isEdit = computed(() => !!route.params.id);

  /* ✅ Picture state (added) */
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

    // ✅ picture url from API (added)
    picture_url: null,

    uni_name: '',
    uni_type: '',
    Province_addr: '',

    responsible_name: '',
    responsible_f_name: '',
    responsible_position: '',
    responsible_phone: '',

    male_teachers_quantity: '',
    female_teachers_quantity: '',
    male_students_quantity: '',
    female_students_quantity: '',

    province: '',
    zone: '',
    district: '',
    village: '',
    specific_place: '',
    street_number: '',
    home_number: '',

    coordinates: '',
    resource_code: '',
    resource_phone: '',
    resource_id: null,

    note: '',
  });

  const form = ref(blankForm());

  /* RULES (✅ phone must be min/max 10 digits) */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    nonNegativeInt: (v) =>
      v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
    phone10: (v) => {
      if (v === null || v === undefined || String(v).trim() === '') return true;
      const digits = String(v).replace(/\D/g, '');
      return digits.length === 10 || t('شماره تماس باید دقیقاً ۱۰ رقم باشد');
    },
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

  /* SIMPLE EXPORT (NO IMAGE / NO APP HEADERS) */
  function esc(v) {
    return String(v ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function buildSimpleHtml() {
    const rows = [
      [t('نام دانشگاه'), form.value.uni_name],
      [t('نوع دانشگاه'), form.value.uni_type],
      [t('ولایت مربوطه'), form.value.Province_addr],

      [t('مسوول'), form.value.responsible_name],
      [t('نام پدر مسوول'), form.value.responsible_f_name],
      [t('وظیفه/موقف'), form.value.responsible_position],
      [t('شماره تماس مسوول'), form.value.responsible_phone],

      [t('استادان مرد'), form.value.male_teachers_quantity],
      [t('استادان زن'), form.value.female_teachers_quantity],
      [t('شاگردان مرد'), form.value.male_students_quantity],
      [t('شاگردان زن'), form.value.female_students_quantity],

      [t('ولایت'), form.value.province],
      [t('زون'), form.value.zone],
      [t('ولسوالی'), form.value.district],
      [t('قریه'), form.value.village],
      [t('موقعیت مشخص'), form.value.specific_place],
      [t('شماره سرک'), form.value.street_number],
      [t('شماره خانه'), form.value.home_number],

      [t('مختصات'), form.value.coordinates],

      [t('کود منبع'), form.value.resource_code],
      [t('شماره منبع'), form.value.resource_phone],

      [t('یادداشت'), form.value.note],
    ];

    const tableRows = rows
      .map(
        ([k, v]) => `
      <tr>
        <td class="k">${esc(k)}</td>
        <td class="v">${esc(v || '')}</td>
      </tr>`
      )
      .join('');

    return `
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>${esc(t('دانشگاه'))}</title>
  <style>
    body{font-family: Arial, sans-serif; direction: rtl; padding: 18px; color:#111;}
    h2{margin:0 0 10px 0; font-size: 16px;}
    .meta{margin-bottom: 12px; font-size: 12px; color:#444;}
    table{width:100%; border-collapse:collapse; margin-top:10px;}
    td{border:1px solid #ddd; padding:8px; vertical-align:top; font-size: 12px;}
    td.k{width: 32%; background:#f7f7f9; font-weight:700;}
    .sig{margin-top:18px; display:flex; gap:24px; justify-content:space-between;}
    .sig .line{flex:1; border-top:1px solid #444; padding-top:6px; font-size:12px; text-align:center;}
    @media print{ body{padding:0} }
  </style>
</head>
<body>
  <h2>${esc(t('فورم معلومات دانشگاه'))}</h2>
  <div class="meta">${esc(t('این سند به صورت ساده (بدون عکس و بدون هدر اپ) تولید شده است.'))}</div>
  <table>
    <tbody>
      ${tableRows}
    </tbody>
  </table>

  <div class="sig">
    <div class="line">${esc(t('امضا'))}</div>
    <div class="line">${esc(t('تاریخ'))}</div>
  </div>
</body>
</html>`;
  }

  function downloadPdf() {
    const html = buildSimpleHtml();
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.open();
    w.document.write(html);
    w.document.close();
    w.focus();
    setTimeout(() => w.print(), 250);
  }

  function downloadWord() {
    const html = buildSimpleHtml();
    const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `university_simple_${form.value?.id ?? 'new'}.doc`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  /* MAP FROM API */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

    // ✅ picture (added)
    b.picture_url = x.picture ?? x.picture_url ?? null;

    b.uni_name = x.uni_name ?? '';
    b.uni_type = x.uni_type ?? '';
    b.Province_addr = x.Province_addr ?? '';

    b.responsible_name = x.responsible_name ?? '';
    b.responsible_f_name = x.responsible_f_name ?? '';
    b.responsible_position = x.responsible_position ?? '';
    b.responsible_phone = x.responsible_phone ?? '';

    b.male_teachers_quantity = x.male_teachers_quantity ?? '';
    b.female_teachers_quantity = x.female_teachers_quantity ?? '';
    b.male_students_quantity = x.male_students_quantity ?? '';
    b.female_students_quantity = x.female_students_quantity ?? '';

    b.province = x.province ?? '';
    b.zone = x.zone ?? '';
    b.district = x.district ?? '';
    b.village = x.village ?? '';
    b.specific_place = x.specific_place ?? '';
    b.street_number = x.street_number ?? '';
    b.home_number = x.home_number ?? '';

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

      // ✅ reset picker (added)
      pictureFile.value = null;
      pictureChanged.value = false;

      return;
    }

    try {
      await repo.FetchUniversityData?.(id);
      const single = Array.isArray(repo.University) ? repo.University[0] : repo.University || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };


      // ✅ reset picker after load (added)
      pictureFile.value = null;
      pictureChanged.value = false;
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات دانشگاه'));
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

    if (!form.value.uni_name?.trim()) {
      showErrorMessage(t('نام دانشگاه الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();

        // ✅ attach picture on create (added)
        if (pictureFile.value) fd.append('picture', pictureFile.value);

        const created = await repo.CreateUniversity?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/universities').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();

        // ✅ attach picture on update ONLY if changed (added)
        if (pictureChanged.value && pictureFile.value) fd.append('picture', pictureFile.value);

        const updated = await repo.UpdateUniversity?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/universities').catch(() => {});
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

      // ✅ reset picker on blank (added)
      pictureFile.value = null;
      pictureChanged.value = false;
      form.value.picture_url = null;
    }

    clearBackendErrors();
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/universities');
  }

  /* INIT */
  onMounted(async () => {
    if (isEdit.value) await loadRecord();
  });
</script>

<style scoped>
  /* main card */
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
