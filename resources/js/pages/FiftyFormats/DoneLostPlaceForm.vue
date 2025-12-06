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
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.province"
                :label="t('ولایت محل')"
                density="compact"
                variant="outlined"
                :error="hasError('province')"
                :error-messages="getErrorMessages('province')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.zone"
                :label="t('زون محل')"
                density="compact"
                variant="outlined"
                :error="hasError('zone')"
                :error-messages="getErrorMessages('zone')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.district"
                :label="t('ولسوالی محل')"
                density="compact"
                variant="outlined"
                :error="hasError('district')"
                :error-messages="getErrorMessages('district')"
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
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_province"
                :label="t('ولایت اصلی')"
                density="compact"
                variant="outlined"
                :error="hasError('main_province')"
                :error-messages="getErrorMessages('main_province')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_district"
                :label="t('ولسوالی اصلی')"
                density="compact"
                variant="outlined"
                :error="hasError('main_district')"
                :error-messages="getErrorMessages('main_district')"
              />
            </v-col>
            <v-col cols="12" md="4">
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
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_province"
                :label="t('ولایت فعلی')"
                density="compact"
                variant="outlined"
                :error="hasError('now_province')"
                :error-messages="getErrorMessages('now_province')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_district"
                :label="t('ولسوالی فعلی')"
                density="compact"
                variant="outlined"
                :error="hasError('now_district')"
                :error-messages="getErrorMessages('now_district')"
              />
            </v-col>
            <v-col cols="12" md="4">
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
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                density="compact"
                variant="outlined"
                :error="hasError('Province_addr')"
                :error-messages="getErrorMessages('Province_addr')"
              />
            </v-col>
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

          <!-- Actions -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2">
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

  import AppBar from '@/components/AppBar.vue';
  import { useWakilanoRepository } from '@/stores/WakilanoRepository';

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

  const blankForm = () => ({
    id: null,
    place_name: '',
    lost_person_name: '',
    lost_person_f_name: '',
    lost_person_phone: '',
    lost_person_job: '',

    main_province: '',
    main_district: '',
    main_village: '',
    now_province: '',
    now_district: '',
    now_village: '',

    province: '',
    zone: '',
    district: '',
    village: '',
    specific_place: '',
    coordinates: '',
    Province_addr: '',

    lost_quantity: '',

    resource_code: '',
    resource_phone: '',
    resource_id: null,

    note: '',
  });

  const form = ref(blankForm());

  const rules = {
    required: (v) => !!(v && String(v).trim()) || t('این فیلد الزامی است'),
    nonNegativeInt: (v) =>
      v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
    phone: (v) => !v || /^[+]?\d[\d\s\-]{5,}$/.test(String(v)) || t('شماره تماس نادرست است'),
  };

  const resources = computed(() => repo.resourcecode || []);

  function onResourceCodeChange(code) {
    const found = resources.value.find((r) => r.code_number === code);
    form.value.resource_phone = found?.phone_number ?? '';
    form.value.resource_id = found?.id ?? null;
  }

  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

    b.place_name = x.place_name ?? '';
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

    b.province = x.province ?? '';
    b.zone = x.zone ?? '';
    b.district = x.district ?? '';
    b.village = x.village ?? '';
    b.specific_place = x.specific_place ?? '';
    b.coordinates = x.coordinates ?? '';

    b.Province_addr = x.Province_addr ?? '';
    b.lost_quantity = x.lost_quantity ?? '';

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
      form.value = mapFromApi(single || {});
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات ریکارډ'));
    }
  }

  function toFormData(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  async function validateForm() {
    const f = formRef.value;
    if (!f?.validate) return { valid: true };
    const { valid } = await f.validate();
    if (!valid) {
      await nextTick();
      scrollToFirstInvalid();
    }
    return { valid };
  }

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

  async function onSubmit() {
    clearBackendErrors();

    if (!form.value.place_name?.trim()) {
      showErrorMessage(t('نام محل الزامی است'));
      return;
    }

    const { valid } = await validateForm();
    if (!valid) {
      showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = toFormData(form.value);
        const created = await repo.CreateDoneLostPlace?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });
        if (created?.id) {
          showSuccessMessage(t('ریکارد موفقانه ثبت شد'));
          router.push('/app/donelostplaces');
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = toFormData(form.value);
        const updated = await repo.UpdateDoneLostPlace?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });
        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/donelostplaces');
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
