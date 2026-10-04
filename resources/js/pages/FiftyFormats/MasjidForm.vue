<!-- src/views/masjids/MasjidsForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش مسجد') : t('ایجاد مسجد جدید')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ مسجد موجود') : t('ثبت یک ریکارډ مسجد جدید در سیستم')
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

          <!-- ✅ Picture row (ONLY addition in template) -->
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

          <!-- مسجد (نام + نوع) -->
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_name"
                :label="t('نام مسجد *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('masjid_name')"
                :error-messages="getErrorMessages('masjid_name')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_type"
                :label="t('نوع مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_type')"
                :error-messages="getErrorMessages('masjid_type')"
              />
            </v-col>
          </v-row>

          <!-- مسوول مسجد -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('مسول/امام مسجد') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_name"
                :label="t('نام مسوول')"
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
                :label="t('موقف مسول/امام')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_position')"
                :error-messages="getErrorMessages('responsible_position')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_mazhab"
                :label="t('مذهب مسول/امام')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_mazhab')"
                :error-messages="getErrorMessages('responsible_mazhab')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_old_job"
                :label="t('وظیفه قبلی مسول/امام')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_old_job')"
                :error-messages="getErrorMessages('responsible_old_job')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_now_job"
                :label="t('وظیفه فعلی مسول/امام')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_now_job')"
                :error-messages="getErrorMessages('responsible_now_job')"
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.responsible_tarfdary"
                :label="t('طرفداری/وابستگی')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_tarfdary')"
                :error-messages="getErrorMessages('responsible_tarfdary')"
              />
            </v-col>
          </v-row>

          <!-- آدرس مسوول -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('آدرس مسول/امام مسجد') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.responsible_main_province"
                v-model:district="form.responsible_main_district"
                :province-label="t('ولایت اصلی مسول/امام')"
                :district-label="t('ولسوالی اصلی مسول/امام')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.responsible_main_village"
                :label="t('قریه اصلی مسول/امام')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_main_village')"
                :error-messages="getErrorMessages('responsible_main_village')"
              />
            </v-col>

            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.responsible_now_province"
                v-model:district="form.responsible_now_district"
                :province-label="t('ولایت فعلی مسول/امام')"
                :district-label="t('ولسوالی فعلی مسول/امام')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.responsible_now_village"
                :label="t('قریه فعلی مسول/امام')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_now_village')"
                :error-messages="getErrorMessages('responsible_now_village')"
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_people_mazhab"
                :label="t('مذهب مردم مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_people_mazhab')"
                :error-messages="getErrorMessages('masjid_people_mazhab')"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="form.masjid_homes_quantity"
                type="number"
                :label="t('تعداد خانه‌های مربوط مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_homes_quantity')"
                :error-messages="getErrorMessages('masjid_homes_quantity')"
              />
            </v-col>
          </v-row>

          <!-- آدرس مسجد -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('آدرس مسجد') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.masjid_province"
                v-model:district="form.masjid_district"
                :province-label="t('ولایت مسجد')"
                :district-label="t('ولسوالی مسجد')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="3">
              <FormatZoneAutocomplete v-model="form.masjid_zone" :label="t('آمریت')" :disabled="saving" />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_village"
                :label="t('قریه مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_village')"
                :error-messages="getErrorMessages('masjid_village')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_specific_place"
                :label="t('موقعیت مشخص مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_specific_place')"
                :error-messages="getErrorMessages('masjid_specific_place')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.masjid_street_number"
                :label="t('شماره سرک مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_street_number')"
                :error-messages="getErrorMessages('masjid_street_number')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.masjid_coordinates"
                :label="t('مختصات مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_coordinates')"
                :error-messages="getErrorMessages('masjid_coordinates')"
              />
            </v-col>
          </v-row>

          <!-- منبع -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('منبع') }}</span>
          </div>
          <v-row>
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
          </v-row>

          <!-- یادداشت -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('یادداشت') }}</span>
          </div>
          <v-row>
            <v-col cols="12">
              <v-textarea
                v-model="form.note"
                :label="t('ملاحظات')"
                variant="outlined"
                density="compact"
                auto-grow
                rows="2"
                :error="hasError('note')"
                :error-messages="getErrorMessages('note')"
              />
            </v-col>
          </v-row>

          <!-- Actions -->
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

/* ✅ Picture (ONLY addition in script imports) */
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

/* ✅ Picture state (ONLY addition) */
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

  /* ✅ picture url from API (ONLY addition) */
  picture_url: null,

  masjid_name: '',
  masjid_type: '',

  responsible_name: '',
  responsible_f_name: '',
  responsible_position: '',
  responsible_mazhab: '',
  responsible_old_job: '',
  responsible_now_job: '',
  responsible_tarfdary: '',

  responsible_main_province: '',
  responsible_main_district: '',
  responsible_main_village: '',
  responsible_now_province: '',
  responsible_now_district: '',
  responsible_now_village: '',

  masjid_people_mazhab: '',
  masjid_homes_quantity: '',

  masjid_province: '',
  masjid_zone: '',
  masjid_district: '',
  masjid_village: '',
  masjid_specific_place: '',
  masjid_street_number: '',
  masjid_coordinates: '',
  Province_addr: '',

  resource_code: '',
  resource_phone: '',
  resource_id: null,

  note: '',
});

const form = ref(blankForm());

/* RULES */
const rules = {
  required: (v) => !!v || t('این فیلد الزامی است'),
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

  /* ✅ picture mapping (ONLY addition) */
  b.picture_url = x.picture ?? x.picture_url ?? null;

  b.masjid_name = x.masjid_name ?? '';
  b.masjid_type = x.masjid_type ?? '';

  b.responsible_name = x.responsible_name ?? '';
  b.responsible_f_name = x.responsible_f_name ?? '';
  b.responsible_position = x.responsible_position ?? '';
  b.responsible_mazhab = x.responsible_mazhab ?? '';
  b.responsible_old_job = x.responsible_old_job ?? '';
  b.responsible_now_job = x.responsible_now_job ?? '';
  b.responsible_tarfdary = x.responsible_tarfdary ?? '';

  b.responsible_main_province = x.responsible_main_province ?? '';
  b.responsible_main_district = x.responsible_main_district ?? '';
  b.responsible_main_village = x.responsible_main_village ?? '';
  b.responsible_now_province = x.responsible_now_province ?? '';
  b.responsible_now_district = x.responsible_now_district ?? '';
  b.responsible_now_village = x.responsible_now_village ?? '';

  b.masjid_people_mazhab = x.masjid_people_mazhab ?? '';
  b.masjid_homes_quantity = x.masjid_homes_quantity ?? '';

  b.masjid_province = x.masjid_province ?? '';
  b.masjid_zone = x.masjid_zone ?? '';
  b.masjid_district = x.masjid_district ?? '';
  b.masjid_village = x.masjid_village ?? '';
  b.masjid_specific_place = x.masjid_specific_place ?? '';
  b.masjid_street_number = x.masjid_street_number ?? '';
  b.masjid_coordinates = x.masjid_coordinates ?? '';
  b.Province_addr = x.Province_addr ?? '';

  const r = x.resource || {};
  b.resource_code = x.resource_code ?? r.code_number ?? '';
  b.resource_phone = x.resource_phone ?? r.phone_number ?? '';
  b.resource_id = x.resource_id ?? r.id ?? null;

  b.note = x.note ?? '';

  /* ✅ reset picker state after load (ONLY addition) */
  pictureFile.value = null;
  pictureChanged.value = false;

  return b;
}

async function loadRecord() {
  const id = route.params.id;
  if (!id) {
    form.value = blankForm();
    return;
  }
  try {
    await repo.FetchMasjidData?.(id);
    const single = Array.isArray(repo.Masjid) ? repo.Masjid[0] : repo.Masjid || {};
    if (!single) return;

    const mapped = mapFromApi(single);
    form.value = { ...mapped };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات مسجد'));
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

/* ✅ attach picture (ONLY changes here) */
function buildCreateFormData() {
  const fd = toFormData({ ...form.value });

  if (pictureFile.value) fd.append('picture', pictureFile.value);

  return fd;
}

function buildUpdateFormData() {
  const fd = toFormData({ ...form.value });

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
/* (UNCHANGED) */
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
    { label: t('نام مسجد'), value: v(form.value.masjid_name) },
    { label: t('نوع مسجد'), value: v(form.value.masjid_type) },

    { label: t('نام مسوول'), value: v(form.value.responsible_name) },
    { label: t('نام پدر مسوول'), value: v(form.value.responsible_f_name) },
    { label: t('وظیفه/موقف مسوول'), value: v(form.value.responsible_position) },
    { label: t('مذهب مسوول'), value: v(form.value.responsible_mazhab) },
    { label: t('وظیفه قبلی مسوول'), value: v(form.value.responsible_old_job) },
    { label: t('وظیفه فعلی مسوول'), value: v(form.value.responsible_now_job) },
    { label: t('طرفداری/وابستگی'), value: v(form.value.responsible_tarfdary) },

    { label: t('ولایت اصلی مسوول'), value: v(form.value.responsible_main_province) },
    { label: t('ولسوالی اصلی مسوول'), value: v(form.value.responsible_main_district) },
    { label: t('قریه اصلی مسوول'), value: v(form.value.responsible_main_village) },
    { label: t('ولایت فعلی مسوول'), value: v(form.value.responsible_now_province) },
    { label: t('ولسوالی فعلی مسوول'), value: v(form.value.responsible_now_district) },
    { label: t('قریه فعلی مسوول'), value: v(form.value.responsible_now_village) },

    { label: t('مذهب مردم مسجد'), value: v(form.value.masjid_people_mazhab) },
    { label: t('تعداد خانه‌های مربوط مسجد'), value: v(form.value.masjid_homes_quantity) },

    { label: t('ولایت مسجد'), value: v(form.value.masjid_province) },
    { label: t('آمریت مسجد'), value: v(form.value.masjid_zone) },
    { label: t('ولسوالی مسجد'), value: v(form.value.masjid_district) },
    { label: t('قریه مسجد'), value: v(form.value.masjid_village) },
    { label: t('موقعیت مشخص مسجد'), value: v(form.value.masjid_specific_place) },
    { label: t('شماره سرک مسجد'), value: v(form.value.masjid_street_number) },
    { label: t('مختصات مسجد'), value: v(form.value.masjid_coordinates) },
    { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },

    { label: t('کود منبع'), value: v(form.value.resource_code) },
    { label: t('شماره منبع'), value: v(form.value.resource_phone) },

    { label: t('یادداشت'), value: v(form.value.note) },
  ];
}

/* (UNCHANGED) buildSimpleTableHtml / buildDocumentHtml / downloadPdf / downloadWord */

/* PRINT BLANK FORM (empty fields, same design, one A4 page) */
function printForm(e) {
  printBlankForm(e, { onError: showErrorMessage });
}

/* SUBMIT (UNCHANGED except it uses new FormData builders already above) */
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

  if (!form.value.masjid_name?.trim()) {
    showErrorMessage(t('نام مسجد الزامی است'));
    return;
  }

  saving.value = true;

  try {
    if (!isEdit.value) {
      const fd = buildCreateFormData();
      const created = await repo.CreateMasjid?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/masjids').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo.UpdateMasjid?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/masjids').catch(() => {});
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
  /* ✅ reset picture picker state too (ONLY addition) */
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

/* INIT */
onMounted(async () => {
  if (isEdit.value) {
    await loadRecord();
  }
});
</script>

<style scoped>
/* unchanged styles */
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
</style>
