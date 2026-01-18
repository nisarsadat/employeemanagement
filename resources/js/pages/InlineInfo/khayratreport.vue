<!-- src/pages/KhayratReports/KhayratReportCreate.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Error -->
    <v-alert
      v-if="repo.error"
      type="error"
      variant="tonal"
      class="mb-4 mx-2"
      border="start"
      closable
      @click:close="repo.error = null"
    >
      {{ repo.error }}
    </v-alert>

    <v-col cols="12" md="12" class="d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-alert type="info" variant="tonal" density="compact" rounded="lg" class="w-120">
        {{ t('هر منبع فقط ماهانه یک عدد راپور خیریت میتواند دشته باشد!') }}
      </v-alert>
    </v-col>

    <!-- FORM -->
    <v-form
      ref="formRef"
      v-model="valid"
      lazy-validation
      @submit.prevent="onCreate"
      :dir="isRtl ? 'rtl' : 'ltr'"
    >
      <v-card class="pa-4 form-card mx-2" elevation="2" rounded="xl">
        <!-- Header -->
        <v-card-title class="form-title d-flex align-center mb-4">
          <v-icon color="primary" class="me-2">mdi-file-document</v-icon>
          {{ t('ثبت گزارش خیرات') }}
        </v-card-title>

        <v-card-text class="pa-0">
          <!-- Main fields -->
          <v-row class="tight-grid">
            <!-- Resource -->
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('منبع') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.resource_id"
                  :items="resourceOptions"
                  :item-title="resourceTitle"
                  item-value="id"
                  density="compact"
                  variant="outlined"
                  rounded="lg"
                  clearable
                  prepend-inner-icon="mdi-account-badge"
                  :loading="repo.lookups.loadingResources"
                  :rules="[rules.selectRequired(t('منبع'))]"
                  @update:search="onResourceSearch"
                  class="cell"
                />
              </v-card-text>
            </v-col>

            <!-- Office Employee (auto-filled when resource selected) -->
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کارمند') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.office_employee_id"
                  :items="employeeOptions"
                  :item-title="employeeTitle"
                  item-value="id"
                  density="compact"
                  variant="outlined"
                  rounded="lg"
                  clearable
                  prepend-inner-icon="mdi-account"
                  :loading="repo.lookups.loadingEmployees"
                  :rules="[rules.selectRequired(t('کارمند'))]"
                  @update:search="onEmployeeSearch"
                  class="cell"
                />
              </v-card-text>
            </v-col>
            <!-- ✅ Trust Percentage (read-only) -->
            <v-col cols="12" md="12">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('فیصدی موثقیت') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.trust_percentage"
                  density="compact"
                  variant="outlined"
                  rounded="lg"
                  readonly
                  prepend-inner-icon="mdi-percent"
                  class="cell"
                />
              </v-card-text>
            </v-col>

            <!-- Zone/Hawza -->
            <v-col cols="12">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('زون / حوزه') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <ZoneHawzaPicker
                  v-model:zone="draft.zone"
                  v-model:hawza="draft.hawza"
                  :disabled="false"
                  :colsZone="12"
                  :mdZone="6"
                  :colsHawza="12"
                  :mdHawza="6"
                />
              </v-card-text>
            </v-col>

            <!-- Specific place -->
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('محل مشخص') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.specefic_place"
                  density="compact"
                  variant="outlined"
                  rounded="lg"
                  clearable
                  prepend-inner-icon="mdi-map"
                  :rules="[rules.required(t('محل مشخص'))]"
                  class="cell"
                />
              </v-card-text>
            </v-col>

            <!-- Saved date -->
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تاریخ ثبت') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <div class="dateinput">
                  <DateInput v-model="draft.saved_date" :label="t('تاریخ ثبت')" />
                </div>
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Backend errors -->
          <v-row v-if="backendErrors.general?.length" class="tight-grid mt-2">
            <v-col cols="12">
              <v-alert type="error" variant="tonal" density="compact" class="rounded-lg">
                <div v-for="(m, i) in backendErrors.general" :key="i" class="text-caption">
                  {{ m }}
                </div>
              </v-alert>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider class="my-3" />

        <!-- Actions -->
        <v-card-actions class="pa-0">
          <v-spacer />

          <v-btn variant="text" color="grey-darken-1" @click="onReset" :disabled="submitting">
            <v-icon start>mdi-delete</v-icon>
            {{ t('پاک کردن فرم') }}
          </v-btn>

          <v-btn
            color="primary"
            type="submit"
            :loading="submitting"
            :disabled="submitting || !valid"
            size="large"
          >
            <v-icon start>mdi-content-save</v-icon>
            {{ t('ثبت گزارش') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="10000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>

    <v-snackbar v-model="showSuccess" color="success" timeout="10000">
      {{ successMessage }}
      <template #actions>
        <v-btn variant="text" @click="showSuccess = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, reactive, computed, watch, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';

  import DateInput from '@/components/DateInput.vue';
  import ZoneHawzaPicker from '@/components/ZoneHawzaSelect.vue';

  import { useKhayratReportRepository } from '@/stores/KhayratReportRepository';

  const router = useRouter();
  const { t, locale } = useI18n();
  const repo = useKhayratReportRepository();

  /* RTL handling */
  const RTL_LOCALES = ['fa', 'fa-AF', 'prs', 'ps', 'ar'];
  const isRtl = ref(RTL_LOCALES.includes(locale.value));
  watch(locale, (v) => (isRtl.value = RTL_LOCALES.includes(v)));

  /* UI state */
  const formRef = ref(null);
  const valid = ref(false);
  const submitting = ref(false);

  const showError = ref(false);
  const errorMessage = ref('');
  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  const showSuccess = ref(false);
  const successMessage = ref('');
  function showSuccessMessage(msg) {
    successMessage.value = msg;
    showSuccess.value = true;
  }

  /* draft (CREATE only) */
  const baseDraft = () => ({
    resource_id: null,
    trust_percentage: '', // ✅ NEW (auto-filled)
    office_employee_id: null, // ✅ auto-filled from resource
    zone: '',
    hawza: '',
    specefic_place: '',
    saved_date: '', // 'YYYY-MM-DD'
  });
  const draft = reactive(baseDraft());

  const backendErrors = reactive({});
  function clearBackendErrors() {
    Object.keys(backendErrors).forEach((k) => delete backendErrors[k]);
  }

  /* Rules */
  const rules = {
    required: (label) => (v) => (v && String(v).trim().length > 0) || `«${label}» ضروری است`,
    selectRequired: (label) => (v) =>
      (v !== null && v !== undefined && v !== '') || `لطفاً «${label}» را انتخاب کنید`,
  };

  /* Lookups */
  const resourceOptions = computed(() => repo.lookups?.resources || []);
  const employeeOptions = computed(() => repo.lookups?.employees || []);

  function resourceTitle(x) {
    const code = x?.code_number ?? '';
    const name = x?.name ?? '';
    const left = String(code || '').trim();
    const right = String(name || '').trim();
    if (left && right) return `${left} - ${right}`;
    return right || left || '';
  }

  function employeeTitle(x) {
    const code = x?.code_number ?? '';
    const name = x?.name ?? '';
    const left = String(code || '').trim();
    const right = String(name || '').trim();
    if (left && right) return `${left} - ${right}`;
    return right || left || '';
  }

  let resTimer = null;
  let empTimer = null;

  function onResourceSearch(q) {
    clearTimeout(resTimer);
    resTimer = setTimeout(() => repo.FetchResourcesLookup({ search: q || '' }), 250);
  }

  function onEmployeeSearch(q) {
    clearTimeout(empTimer);
    empTimer = setTimeout(() => repo.FetchOfficeEmployeesLookup({ search: q || '' }), 250);
  }

  async function preloadLookups() {
    await Promise.allSettled([
      repo.FetchResourcesLookup({ search: '' }),
      repo.FetchOfficeEmployeesLookup({ search: '' }),
    ]);
  }

  function onReset() {
    Object.assign(draft, baseDraft());
    clearBackendErrors();
    formRef.value?.resetValidation?.();
  }

  async function onCreate() {
    clearBackendErrors();

    const ok = await formRef.value?.validate?.();
    if (ok && ok.valid === false) {
      requestAnimationFrame(() => {
        const firstInvalid =
          document.querySelector('[aria-invalid="true"]') ||
          document.querySelector('.v-input--error');
        firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid?.focus?.();
      });
      return;
    }

    submitting.value = true;
    try {
      const payload = {
        resource_id: draft.resource_id || null,
        office_employee_id: draft.office_employee_id || null,
        zone: draft.zone || null,
        hawza: draft.hawza || null,
        specefic_place: draft.specefic_place || null,
        saved_date: draft.saved_date || null,

        // NOTE: trust_percentage is read-only UI field.
        // Only include it in payload IF your backend expects it.
        // trust_percentage: draft.trust_percentage || null,
      };

      await repo.CreateKhayratReport(payload);

      onReset();
      showSuccessMessage(t('گزارش با موفقیت ثبت شد'));
    } catch (e) {
      const errors = e?.response?.data?.errors;
      if (errors && typeof errors === 'object') {
        Object.assign(backendErrors, errors);
      } else {
        backendErrors.general = [t('خطا رخ داد. دوباره تلاش کنید.')];
        showErrorMessage(t('خطا در ثبت گزارش خیرات'));
      }
    } finally {
      submitting.value = false;
    }
  }

  onMounted(async () => {
    await preloadLookups();
  });

  // ✅ Auto-fill office_employee_id + trust_percentage when resource selected
  watch(
    () => draft.resource_id,
    (newVal) => {
      if (!newVal) {
        draft.office_employee_id = null;
        draft.trust_percentage = '';
        return;
      }

      const r = resourceOptions.value.find((x) => x?.id === newVal);
      if (!r) return;

      // ✅ trust percentage (support multiple backend keys)
      draft.trust_percentage =
        r.trust_percentage ?? r.trustPercentage ?? r.trust_percent ?? r.trustPercent ?? '';

      // ✅ resource -> employee
      const empId =
        r.office_employee?.id ??
        r.officeEmployee?.id ??
        r.office_employee_id ??
        r.employee_id ??
        null;

      if (empId) {
        draft.office_employee_id = empId;
      }
    }
  );
</script>

<style scoped>
  .form-card {
    border-radius: 16px;
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
  }

  .form-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
  }

  .cell {
    width: 100%;
  }

  .field-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #475569;
    opacity: 1;
  }

  .dateinput {
    width: 100% !important;
  }

  .tight-grid {
    --v-gutter-x: 8px;
    --v-gutter-y: 8px;
  }

  :deep(.v-card-item) {
    padding: 4px 0 2px 0;
  }

  :deep(.v-card-text) {
    padding: 4px 0 0 0;
  }

  :deep([dir='rtl'] .v-field__input) {
    direction: rtl;
    text-align: right;
  }
  :deep([dir='rtl'] .v-field-label),
  :deep([dir='rtl'] .v-label),
  :deep([dir='rtl'] .v-input__details) {
    text-align: right;
  }

  :deep(.v-field__input) {
    padding-inline-start: 10px !important;
    padding-inline-end: 10px !important;
  }
</style>
