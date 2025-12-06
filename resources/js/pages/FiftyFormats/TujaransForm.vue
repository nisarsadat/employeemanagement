<!-- src/views/tujaran/TujaransForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش تاجر') : t('ایجاد تاجر جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ تاجر موجود')
          : t('ثبت یک ریکارډ جدید تاجر در سیستم')
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
          <!-- Identity -->
          <v-row>
            <v-col cols="12">
              <div class="section-header">
                <span class="section-title">
                  {{ isEdit ? t('ویرایش معلومات تاجر') : t('ایجاد معلومات تاجر جدید') }}
                </span>
              </div>
            </v-col>

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
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_province"
                :label="t('ولایت اصلی')"
                variant="outlined"
                density="compact"
                :error="hasError('main_province')"
                :error-messages="getErrorMessages('main_province')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_district"
                :label="t('ولسوالی اصلی')"
                variant="outlined"
                density="compact"
                :error="hasError('main_district')"
                :error-messages="getErrorMessages('main_district')"
              />
            </v-col>
            <v-col cols="12" md="4">
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
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_province"
                :label="t('ولایت فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_province')"
                :error-messages="getErrorMessages('now_province')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_zone"
                :label="t('زون فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_zone')"
                :error-messages="getErrorMessages('now_zone')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_district"
                :label="t('ولسوالی فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_district')"
                :error-messages="getErrorMessages('now_district')"
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
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_street_number"
                :label="t('شماره سرک فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_street_number')"
                :error-messages="getErrorMessages('now_street_number')"
              />
            </v-col>
            <v-col cols="12" md="3">
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

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.guard_guntity"
                :label="t('تعداد محافظین')"
                variant="outlined"
                density="compact"
                :error="hasError('guard_guntity')"
                :error-messages="getErrorMessages('guard_guntity')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                variant="outlined"
                density="compact"
                :error="hasError('phone_number')"
                :error-messages="getErrorMessages('phone_number')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.people_type"
                :label="t('نوع افراد')"
                variant="outlined"
                density="compact"
                :error="hasError('people_type')"
                :error-messages="getErrorMessages('people_type')"
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

            <v-col cols="12" md="8">
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ تاجر') }}
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
  };

  /* FORM MODEL */
  const blankForm = () => ({
    id: null,

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
    form.value.resource_phone = found?.phone_number ?? '';
    form.value.resource_id = found?.id ?? null;
  }

  /* MAP FROM API */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

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
    b.people_type = x.people_type ?? '';

    b.Province_addr = x.Province_addr ?? '';
    b.coordinates = x.coordinates ?? '';

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
      await repo.FetchTujaranData?.(id);
      const single = Array.isArray(repo.Tujaran) ? repo.Tujaran[0] : repo.Tujaran || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };
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
    return fd;
  }

  function buildCreateFormData() {
    const payload = { ...form.value };
    return toFormData(payload);
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    return toFormData(payload);
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
