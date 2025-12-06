<!-- src/views/badmashano/SarayanoForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش سرای') : t('ایجاد سرای جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ سرای موجود')
          : t('ثبت یک ریکارډ جدید سرای در سیستم')
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
          <!-- Saray basics -->
          <div class="section-header">
            <span class="section-title">
              {{ isEdit ? t('ویرایش معلومات سرای') : t('ایجاد سرای جدید') }}
            </span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.saray_name"
                :label="t('نام سرای *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('saray_name')"
                :error-messages="getErrorMessages('saray_name')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.saray_type"
                :label="t('نوع سرای')"
                variant="outlined"
                density="compact"
                :error="hasError('saray_type')"
                :error-messages="getErrorMessages('saray_type')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.employee_quantity"
                :label="t('تعداد کارمندان')"
                type="number"
                variant="outlined"
                density="compact"
                :error="hasError('employee_quantity')"
                :error-messages="getErrorMessages('employee_quantity')"
              />
            </v-col>
          </v-row>

          <!-- Responsible -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('مسؤول سرای') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_name"
                :label="t('مسؤول: نام')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_name')"
                :error-messages="getErrorMessages('responsible_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_f_name"
                :label="t('مسؤول: نام پدر')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_f_name')"
                :error-messages="getErrorMessages('responsible_f_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_position"
                :label="t('مسؤول: موقف')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_position')"
                :error-messages="getErrorMessages('responsible_position')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_phone"
                :label="t('مسؤول: شماره تماس')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_phone')"
                :error-messages="getErrorMessages('responsible_phone')"
              />
            </v-col>
          </v-row>

          <!-- Address -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('آدرس سرای') }}</span>
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

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.specific_place"
                :label="t('موقعیت مشخص')"
                variant="outlined"
                density="compact"
                :error="hasError('specific_place')"
                :error-messages="getErrorMessages('specific_place')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.cordenates"
                :label="t('مختصات')"
                variant="outlined"
                density="compact"
                :error="hasError('cordenates')"
                :error-messages="getErrorMessages('cordenates')"
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
          </v-row>

          <!-- Source + Note -->
          <div class="section-header mt-4 mb-2">
            <span class="section-title">{{ t('منبع و یادداشت') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
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

            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.note"
                :label="t('یادداشت')"
                density="compact"
                variant="outlined"
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ سرای') }}
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
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';

import AppBar from '@/components/AppBar.vue';
import { useBadmashanoRepository } from '@/stores/BadmashanoRepository';

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

/* Backend errors */
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
  saray_name: '',
  saray_type: '',
  employee_quantity: '',

  responsible_name: '',
  responsible_f_name: '',
  responsible_position: '',
  responsible_phone: '',

  province: '',
  zone: '',
  district: '',
  village: '',
  specific_place: '',
  cordenates: '',
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

/* RESOURCES DROPDOWN */
const resources = computed(() => repo.resourcecode || []);
function onResourceCodeChange(code) {
  const found = resources.value.find((r) => r.code_number === code);
  form.value.resource_phone = found?.phone_number ?? '';
  form.value.resource_id = found?.id ?? null;
}

/* MAP from API */
function mapFromApi(payload = {}) {
  const x = payload.data ?? payload;
  const base = blankForm();

  base.id = x.id ?? null;

  base.saray_name = x.saray_name ?? '';
  base.saray_type = x.saray_type ?? '';
  base.employee_quantity = x.employee_quantity ?? '';

  base.responsible_name = x.responsible_name ?? '';
  base.responsible_f_name = x.responsible_f_name ?? '';
  base.responsible_position = x.responsible_position ?? '';
  base.responsible_phone = x.responsible_phone ?? '';

  base.province = x.province ?? '';
  base.zone = x.zone ?? '';
  base.district = x.district ?? '';
  base.village = x.village ?? '';
  base.specific_place = x.specific_place ?? '';
  base.cordenates = x.cordenates ?? '';
  base.Province_addr = x.Province_addr ?? '';

  const r = x.resource || {};
  base.resource_code = x.resource_code ?? r.code_number ?? '';
  base.resource_phone = x.resource_phone ?? r.phone_number ?? '';
  base.resource_id = x.resource_id ?? r.id ?? null;

  base.note = x.note ?? '';

  return base;
}

async function loadRecord() {
  const id = route.params.id;
  if (!id) {
    form.value = blankForm();
    return;
  }
  try {
    await repo.FetchSarayanoData?.(id);
    const single = Array.isArray(repo.Sarayano) ? repo.Sarayano[0] : repo.Sarayano || {};
    const mapped = mapFromApi(single || {});
    form.value = { ...mapped };
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات سرای'));
  }
}

/* helpers */
function toFormData(obj) {
  const fd = new FormData();
  Object.entries(obj).forEach(([k, v]) => {
    if (v === undefined || v === null) return;
    fd.append(k, v);
  });
  return fd;
}

/* scroll to error */
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

  if (!form.value.saray_name?.trim()) {
    showErrorMessage(t('نام سرای الزامی است'));
    return;
  }

  saving.value = true;

  try {
    if (!isEdit.value) {
      const fd = toFormData(form.value);
      const created = await repo.CreateSarayano?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/sarayanos').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = toFormData(form.value);
      const updated = await repo.UpdateSarayano?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/sarayanos').catch(() => {});
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

/* RESET & NAV */
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
  router.push('/app/sarayanos');
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
.primary-action-btn {
  padding-inline: 18px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(25, 118, 210, 0.25);
}
.secondary-action-btn {
  padding-inline: 14px;
  font-weight: 500;
}
/* tighter fields */
:deep(.v-field--variant-outlined .v-field__input) {
  padding-top: 4px;
  padding-bottom: 4px;
  min-height: 36px;
}
</style>
