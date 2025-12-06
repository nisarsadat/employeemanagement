<!-- src/views/wakilano/ComplexForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش کمپلکس') : t('ایجاد کمپلکس جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ کمپلکس موجود')
          : t('ثبت یک ریکارډ کمپلکس جدید در سیستم')
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
            <!-- Full-width form (no picture) -->
            <v-col cols="12" md="12">
              <!-- Identity / activity -->
              <div class="section-header">
                <span class="section-title">
                  {{ isEdit ? t('ویرایش ریکارډ کمپلکس') : t('ایجاد ریکارډ کمپلکس') }}
                </span>
              </div>
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
              <div class="section-header mt-4">
                <span class="section-title">{{ t('ورود/خروج') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.enter_male_quantity"
                    type="number"
                    :label="t('ورود ذکور')"
                    variant="outlined"
                    density="compact"
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
                    v-model="form.responsible_phone"
                    :label="t('شماره تماس مسؤول')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_phone')"
                    :error-messages="getErrorMessages('responsible_phone')"
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
              </v-row>

              <!-- Addresses -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس کمپلکس') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.province"
                    :label="t('ولایت')"
                    variant="outlined"
                    density="compact"
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
                    :label="t('ولسوالی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('district')"
                    :error-messages="getErrorMessages('district')"
                  />
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
                    v-model.number="form.guard_quantity"
                    type="number"
                    :label="t('تعداد محافظ')"
                    variant="outlined"
                    density="compact"
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

          <!-- Actions -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2">
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ کمپلکس') }}
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
import { useWakilanoRepository } from '@/stores/WakilanoRepository';

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

  b.complex_name = x.complex_name ?? '';
  b.activity_type = x.activity_type ?? '';
  b.activity_place = x.activity_place ?? '';
  b.supplier = x.supplier ?? '';

  b.enter_male_quantity = x.enter_male_quantity ?? '';
  b.outer_male_quantity = x.outer_male_quantity ?? '';
  b.enter_femal_quantity = x.enter_femal_quantity ?? '';
  b.outer_femal_quantity = x.outer_femal_quantity ?? '';

  b.responsible_name = x.responsible_name ?? '';
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
    return;
  }
  try {
    await repo.FetchComplexData?.(id);
    const single = Array.isArray(repo.Complex) ? repo.Complex[0] : repo.Complex || {};
    if (!single) return;

    const mapped = mapFromApi(single);
    form.value = { ...mapped };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات کمپلکس'));
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
  return fd;
}

function buildUpdateFormData() {
  const payload = { ...form.value };
  const fd = toFormData(payload);
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

/* small gap utility */
.gap-2 {
  gap: 8px;
}

/* slightly tighter fields */
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
