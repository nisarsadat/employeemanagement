<!-- src/views/park/ParkForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش پارک') : t('ایجاد پارک جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ پارک موجود')
          : t('ثبت یک ریکارډ پارک جدید در سیستم')
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
            <v-col cols="12" md="12">
              <!-- Identity -->
              <div class="section-header">
                <span class="section-title">
                  {{ isEdit ? t('ویرایش ریکارډ پارک') : t('ایجاد ریکارډ پارک') }}
                </span>
              </div>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.park_name"
                    :label="t('نام پارک *')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.required]"
                    :error="hasError('park_name')"
                    :error-messages="getErrorMessages('park_name')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.park_type"
                    :label="t('نوع پارک')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('park_type')"
                    :error-messages="getErrorMessages('park_type')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model.number="form.dayly_clientele"
                    type="number"
                    :label="t('مراجعین روزانه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('dayly_clientele')"
                    :error-messages="getErrorMessages('dayly_clientele')"
                  />
                </v-col>
              </v-row>

              <!-- Responsible -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('مسؤول/مدیریت پارک') }}</span>
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
                    v-model="form.responsible_possition"
                    :label="t('وظیفه/موقف مسؤول')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_possition')"
                    :error-messages="getErrorMessages('responsible_possition')"
                  />
                </v-col>
              </v-row>

              <!-- Address -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس پارک') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.province"
                    :label="t('ولایت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('province')"
                    :error-messages="getErrorMessages('province')"
                  />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.zone"
                    :label="t('زون')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('zone')"
                    :error-messages="getErrorMessages('zone')"
                  />
                </v-col>
                <v-col cols="12" md="4">
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
            <span class="section-title">{{ t('کارمندان، منبع و توضیحات') }}</span>
          </div>
          <v-row>
            <!-- Employees -->
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.employee_quantity"
                type="number"
                :label="t('تعداد کارمندان')"
                variant="outlined"
                density="compact"
                :error="hasError('employee_quantity')"
                :error-messages="getErrorMessages('employee_quantity')"
              />
            </v-col>

            <!-- Province addr -->
            <v-col cols="12" md="3">
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

            <!-- Notes -->
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.notes"
                :label="t('یادداشت‌ها')"
                variant="outlined"
                density="compact"
                :error="hasError('notes')"
                :error-messages="getErrorMessages('notes')"
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ پارک') }}
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

  park_name: '',
  park_type: '',
  dayly_clientele: '',

  responsible_name: '',
  responsible_f_name: '',
  responsible_phone: '',
  responsible_possition: '',

  province: '',
  zone: '',
  village: '',
  specefic_place: '',
  coordinates: '',

  employee_quantity: '',
  Province_addr: '',

  resource_code: '',
  resource_phone: '',
  resource_id: null,

  notes: '',
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

  b.park_name = x.park_name ?? '';
  b.park_type = x.park_type ?? '';
  b.dayly_clientele = x.dayly_clientele ?? '';

  b.responsible_name = x.responsible_name ?? '';
  b.responsible_f_name = x.responsible_f_name ?? '';
  b.responsible_phone = x.responsible_phone ?? '';
  b.responsible_possition = x.responsible_possition ?? '';

  b.province = x.province ?? '';
  b.zone = x.zone ?? '';
  b.village = x.village ?? '';
  b.specefic_place = x.specefic_place ?? '';
  b.coordinates = x.coordinates ?? '';

  b.employee_quantity = x.employee_quantity ?? '';
  b.Province_addr = x.Province_addr ?? '';

  const r = x.resource || {};
  b.resource_code = x.resource_code ?? r.code_number ?? '';
  b.resource_phone = x.resource_phone ?? r.phone_number ?? '';
  b.resource_id = x.resource_id ?? r.id ?? null;

  b.notes = x.notes ?? '';

  return b;
}

async function loadRecord() {
  const id = route.params.id;
  if (!id) {
    form.value = blankForm();
    return;
  }
  try {
    await repo.FetchParkData?.(id);
    const single = Array.isArray(repo.Park) ? repo.Park[0] : repo.Park || {};
    if (!single) return;

    const mapped = mapFromApi(single);
    form.value = { ...mapped };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات پارک'));
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

  if (!form.value.park_name?.trim()) {
    showErrorMessage(t('نام پارک الزامی است'));
    return;
  }

  saving.value = true;

  try {
    if (!isEdit.value) {
      const fd = buildCreateFormData();
      const created = await repo.CreatePark?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/parks').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo.UpdatePark?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/parks').catch(() => {});
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
  router.push('/app/parks');
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
