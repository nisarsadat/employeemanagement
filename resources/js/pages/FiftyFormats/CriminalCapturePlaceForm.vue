<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش محل دستگیری') : t('ایجاد محل دستگیری جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ محل دستگیری موجود')
          : t('ثبت یک ریکارډ جدید محل دستگیری در سیستم')
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
          <!-- Identity + addresses -->
          <v-row>
            <v-col cols="12">
              <!-- Identity -->
              <div class="section-header">
                <span class="section-title">
                  {{
                    isEdit
                      ? t('ویرایش ریکارډ محل دستگیری')
                      : t('ایجاد ریکارډ محل دستگیری')
                  }}
                </span>
              </div>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.name"
                    :label="t('نام مجرم *')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.required]"
                    :error="hasError('name')"
                    :error-messages="getErrorMessages('name')"
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

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.job"
                    :label="t('وظیفه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('job')"
                    :error-messages="getErrorMessages('job')"
                  />
                </v-col>
              </v-row>

              <!-- Crime details -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('جزئیات جرم') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.jorm_type"
                    :label="t('نوع جرم')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('jorm_type')"
                    :error-messages="getErrorMessages('jorm_type')"
                  />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model.number="form.criminals_quantity"
                    type="number"
                    :label="t('تعداد مجرمین')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('criminals_quantity')"
                    :error-messages="getErrorMessages('criminals_quantity')"
                  />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.office"
                    :label="t('اداره')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('office')"
                    :error-messages="getErrorMessages('office')"
                  />
                </v-col>
              </v-row>

              <!-- Address -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس محل دستگیری') }}</span>
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
                    :label="t('قریه/ناحیه')"
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

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.coordinates"
                    :label="t('مختصات')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('coordinates')"
                    :error-messages="getErrorMessages('coordinates')"
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Extra fields -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('جزئیات اضافی و منبع') }}</span>
          </div>
          <v-row>
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
                v-model="form.resource_phone_number"
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ محل دستگیری') }}
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

  name: '',
  father_name: '',
  job: '',

  jorm_type: '',
  criminals_quantity: '',
  office: '',

  province: '',
  zone: '',
  district: '',
  village: '',
  specefic_place: '',
  coordinates: '',

  Province_addr: '',

  resource_code: '',
  resource_phone_number: '',
  resource_id: null,

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
  form.value.resource_phone_number = found?.phone_number ?? '';
  form.value.resource_id = found?.id ?? null;
}

/* MAP FROM API */
function mapFromApi(payload = {}) {
  const x = payload.data ?? payload;
  const b = blankForm();

  b.id = x.id ?? null;

  b.name = x.name ?? '';
  b.father_name = x.father_name ?? '';
  b.job = x.job ?? '';

  b.jorm_type = x.jorm_type ?? '';
  b.criminals_quantity = x.criminals_quantity ?? '';
  b.office = x.office ?? '';

  b.province = x.province ?? '';
  b.zone = x.zone ?? '';
  b.district = x.district ?? '';
  b.village = x.village ?? '';
  b.specefic_place = x.specefic_place ?? '';
  b.coordinates = x.coordinates ?? '';

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
    await repo.FetchCriminalCapturePlaceData?.(id);
    const single = Array.isArray(repo.CriminalCapturePlace)
      ? repo.CriminalCapturePlace[0]
      : repo.CriminalCapturePlace || {};
    if (!single) return;

    const mapped = mapFromApi(single);
    form.value = { ...mapped };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات محل دستگیری'));
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

  if (!form.value.name?.trim()) {
    showErrorMessage(t('نام الزامی است'));
    return;
  }

  saving.value = true;

  try {
    if (!isEdit.value) {
      const fd = buildCreateFormData();
      const created = await repo.CreateCriminalCapturePlace?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/criminalcaptureplaces').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo
        .UpdateCriminalCapturePlace?.(form.value.id, fd)
        .catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/criminalcaptureplaces').catch(() => {});
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
  router.push('/app/criminalcaptureplaces');
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
