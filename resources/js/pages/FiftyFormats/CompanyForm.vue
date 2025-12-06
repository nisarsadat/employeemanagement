<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش شرکت') : t('ایجاد شرکت جدید')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ شرکت موجود') : t('ثبت یک ریکارډ شرکت جدید در سیستم')
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
            <v-col cols="12" md="9">
              <!-- Identity -->
              <div class="section-header">
                <span class="section-title">
                  {{ isEdit ? t('ویرایش ریکارډ شرکت') : t('ایجاد ریکارډ شرکت') }}
                </span>
              </div>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_name"
                    :label="t('نام شرکت *')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.required]"
                    :error="hasError('com_name')"
                    :error-messages="getErrorMessages('com_name')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_type"
                    :label="t('نوع شرکت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_type')"
                    :error-messages="getErrorMessages('com_type')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.product_type"
                    :label="t('نوع محصول/خدمت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('product_type')"
                    :error-messages="getErrorMessages('product_type')"
                  />
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_level"
                    :label="t('سطح شرکت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_level')"
                    :error-messages="getErrorMessages('com_level')"
                  />
                </v-col>

                <v-col cols="12" md="8">
                  <v-text-field
                    v-model="form.main_owner"
                    :label="t('مالک اصلی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('main_owner')"
                    :error-messages="getErrorMessages('main_owner')"
                  />
                </v-col>
              </v-row>

              <!-- Responsible -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('مسوول شرکت') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.responsible_name"
                    :label="t('مسوول')"
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
                    :label="t('وظیفه/موقف')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('responsible_position')"
                    :error-messages="getErrorMessages('responsible_position')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.responsible_phone"
                    :label="t('شماره تماس مسوول')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.phone]"
                    :error="hasError('responsible_phone')"
                    :error-messages="getErrorMessages('responsible_phone')"
                  />
                </v-col>
              </v-row>

              <!-- Address -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس شرکت') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_province"
                    :label="t('ولایت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_province')"
                    :error-messages="getErrorMessages('com_province')"
                  />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_zone"
                    :label="t('زون')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_zone')"
                    :error-messages="getErrorMessages('com_zone')"
                  />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_district"
                    :label="t('ولسوالی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_district')"
                    :error-messages="getErrorMessages('com_district')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_village"
                    :label="t('قریه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_village')"
                    :error-messages="getErrorMessages('com_village')"
                  />
                </v-col>
                <v-col cols="12" md="8">
                  <v-text-field
                    v-model="form.com_specific_place"
                    :label="t('موقعیت مشخص')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_specific_place')"
                    :error-messages="getErrorMessages('com_specific_place')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.com_coordinates"
                    :label="t('مختصات')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('com_coordinates')"
                    :error-messages="getErrorMessages('com_coordinates')"
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Extra fields -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('کارمندان، محافظین و منبع') }}</span>
          </div>
          <v-row>
            <!-- Employees -->
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.male_employee_quantity_inner"
                type="number"
                :label="t('کارمندان داخلی مرد')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('male_employee_quantity_inner')"
                :error-messages="getErrorMessages('male_employee_quantity_inner')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.female_employee_quantity_inner"
                type="number"
                :label="t('کارمندان داخلی زن')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('female_employee_quantity_inner')"
                :error-messages="getErrorMessages('female_employee_quantity_inner')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.male_employee_quantity_out"
                type="number"
                :label="t('کارمندان بیرونی مرد')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('male_employee_quantity_out')"
                :error-messages="getErrorMessages('male_employee_quantity_out')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.female_employee_quantity_out"
                type="number"
                :label="t('کارمندان بیرونی زن')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('female_employee_quantity_out')"
                :error-messages="getErrorMessages('female_employee_quantity_out')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.guard_quantity"
                type="number"
                :label="t('تعداد محافظ')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('guard_quantity')"
                :error-messages="getErrorMessages('guard_quantity')"
              />
            </v-col>

            <!-- Province addr + resource -->
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

            <!-- hidden resource_id (still in payload) -->
            <v-col cols="12" md="4" v-if="false">
              <v-text-field
                v-model="form.resource_id"
                :label="t('Resource ID')"
                variant="outlined"
                density="compact"
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ شرکت') }}
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

      <!-- IMAGE PREVIEW DIALOG -->
      <v-dialog v-model="previewOpen" max-width="520">
        <v-card>
          <v-toolbar density="compact" color="primary" :title="t('پیش نمایش')" class="text-white" />
          <v-card-text class="d-flex justify-center">
            <v-img
              :src="normalizePicture(previewSrc)"
              :alt="t('پیش نمایش')"
              max-width="480"
              max-height="420"
              cover
            />
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn color="primary" variant="flat" @click="previewOpen = false">
              {{ t('بستن') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

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

    // media
    picture: null,
    previewUrl: null,

    // identity
    com_name: '',
    com_type: '',
    product_type: '',
    com_level: '',
    main_owner: '',

    // responsible
    responsible_name: '',
    responsible_f_name: '',
    responsible_position: '',
    responsible_phone: '',

    // address
    com_province: '',
    com_zone: '',
    com_district: '',
    com_village: '',
    com_specific_place: '',
    com_coordinates: '',

    // employees & guards
    male_employee_quantity_inner: '',
    female_employee_quantity_inner: '',
    male_employee_quantity_out: '',
    female_employee_quantity_out: '',
    guard_quantity: '',

    Province_addr: '',

    // resource
    resource_code: '',
    resource_phone_number: '',
    resource_id: null,

    // note
    note: '',
  });

  const form = ref(blankForm());

  /* RULES */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    nonNegativeInt: (v) =>
      v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
    phone: (v) => !v || /^[+]?\d[\d\s\-]{5,}$/.test(String(v)) || t('شماره تماس نادرست است'),
  };

  /* PICTURE */
  const pictureInput = ref(null);
  const pictureFile = ref(null);
  const pictureChanged = ref(false);

  const previewOpen = ref(false);
  const previewSrc = ref(null);

  function triggerPicture() {
    pictureInput.value?.click();
  }

  function onPickPicture(e) {
    const file = e.target.files?.[0] ?? null;
    pictureFile.value = file;
    pictureChanged.value = !!file;

    if (file) {
      if (form.value.previewUrl && form.value.previewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(form.value.previewUrl);
      }
      form.value.previewUrl = URL.createObjectURL(file);
    } else {
      form.value.previewUrl = form.value.picture || null;
    }
    if (e.target) e.target.value = '';
  }

  function normalizePicture(src) {
    if (!src) return null;
    let clean = String(src).trim();

    if (clean.startsWith('blob:') || clean.startsWith('data:')) return clean;
    clean = clean.replace(/\\\//g, '/');

    if (clean.startsWith('http://') || clean.startsWith('https://')) return clean;
    if (clean.startsWith('/')) return clean;

    return '/' + clean.replace(/^\/+/, '');
  }

  function openPreview(src) {
    const base = src || form.value.previewUrl || form.value.picture;
    const normalized = normalizePicture(base);
    previewSrc.value = normalized;
    if (previewSrc.value) {
      previewOpen.value = true;
    }
  }

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

    const fullUrl = x.picture ?? null;
    b.picture = fullUrl;
    b.previewUrl = fullUrl;

    b.com_name = x.com_name ?? '';
    b.com_type = x.com_type ?? '';
    b.product_type = x.product_type ?? '';
    b.com_level = x.com_level ?? '';
    b.main_owner = x.main_owner ?? '';

    b.responsible_name = x.responsible_name ?? '';
    b.responsible_f_name = x.responsible_f_name ?? '';
    b.responsible_position = x.responsible_position ?? '';
    b.responsible_phone = x.responsible_phone ?? '';

    b.com_province = x.com_province ?? '';
    b.com_zone = x.com_zone ?? '';
    b.com_district = x.com_district ?? '';
    b.com_village = x.com_village ?? '';
    b.com_specific_place = x.com_specific_place ?? '';
    b.com_coordinates = x.com_coordinates ?? '';

    b.male_employee_quantity_inner = x.male_employee_quantity_inner ?? '';
    b.female_employee_quantity_inner = x.female_employee_quantity_inner ?? '';
    b.male_employee_quantity_out = x.male_employee_quantity_out ?? '';
    b.female_employee_quantity_out = x.female_employee_quantity_out ?? '';
    b.guard_quantity = x.guard_quantity ?? '';

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
      pictureFile.value = null;
      pictureChanged.value = false;
      return;
    }
    try {
      await repo.FetchCompanyData?.(id);
      const single = Array.isArray(repo.Company) ? repo.Company[0] : repo.Company || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };
      pictureFile.value = null;
      pictureChanged.value = false;
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات شرکت'));
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

    delete payload.previewUrl;

    const fd = toFormData(payload);

    if (pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };

    delete payload.previewUrl;
    delete payload.picture;

    const fd = toFormData(payload);

    if (pictureChanged.value && pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

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

    if (!form.value.com_name?.trim()) {
      showErrorMessage(t('نام شرکت الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateCompany?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/companies').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateCompany?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/companies').catch(() => {});
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
    if (form.value.previewUrl && form.value.previewUrl.startsWith('blob:')) {
      URL.revokeObjectURL(form.value.previewUrl);
    }

    if (isEdit.value) {
      loadRecord();
    } else {
      form.value = blankForm();
      pictureFile.value = null;
      pictureChanged.value = false;
    }

    clearBackendErrors();
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/companies');
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
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }

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

  /* picture */
  .pic-wrap-simple {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .cursor-pointer {
    cursor: pointer;
  }
  .primary-avatar {
    border: 2px solid rgba(33, 150, 243, 0.25);
  }

  /* small gap utility */
  .gap-2 {
    gap: 8px;
  }

  /* tighter fields like Heist */
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
