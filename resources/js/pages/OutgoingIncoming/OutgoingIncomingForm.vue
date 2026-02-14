<template>
  <v-container fluid class="page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح مکتوب') : t('ثبت مکتوب صادره/وارده')"
      :subtitle="isEdit ? t('ویرایش جزئیات') : t('ثبت یک مورد جدید در سیستم')"
    >
    </AppBar>

    <!-- STEPPER -->
    <section class="stepper-shell" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card class="stepper-card py-4 pb-12" elevation="0">
        <div class="d-flex justify-space-between align-center mb-1">
          <span class="text-caption text-medium-emphasis stepper-caption">
            {{ t('مراحل خانه پری') }}
          </span>
          <span class="text-caption text-primary font-weight-medium stepper-caption">
            {{ t('۴ بخش') }}
          </span>
        </div>

        <div class="stepper-track-wrapper">
          <v-progress-linear
            class="stepper-track"
            :model-value="progressValue"
            height="3"
            rounded
            color="primary"
          />
          <div class="stepper-dots">
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step1Complete,
                  'is-active': currentStep === 1 && !step1Complete,
                }"
              >
                <v-icon size="16">{{ step1Complete ? 'mdi-check' : 'mdi-text' }}</v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step1Complete }"
              >
                {{ t('نوع و متن') }}
              </span>
            </div>

            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step2Complete,
                  'is-active': currentStep === 2 && !step2Complete,
                }"
              >
                <v-icon size="16">{{ step2Complete ? 'mdi-check' : 'mdi-calendar' }}</v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step2Complete }"
              >
                {{ t('تاریخ‌ها') }}
              </span>
            </div>

            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step3Complete,
                  'is-active': currentStep === 3 && !step3Complete,
                }"
              >
                <v-icon size="16">{{ step3Complete ? 'mdi-check' : 'mdi-file-document' }}</v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step3Complete }"
              >
                {{ t('صفحات و فهرست') }}
              </span>
            </div>

            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step4Complete,
                  'is-active': currentStep === 4 && !step4Complete,
                }"
              >
                <v-icon size="16">{{ step4Complete ? 'mdi-check' : 'mdi-note-text' }}</v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step4Complete }"
              >
                {{ t('ضمیمه و ملاحظات') }}
              </span>
            </div>
          </div>
        </div>
      </v-card>
    </section>

    <!-- FORM -->
    <v-form
      ref="formRef"
      v-model="formValid"
      lazy-validation
      @submit.prevent="onSubmit"
      :dir="isRtl ? 'rtl' : 'ltr'"
    >
      <div class="form-shell mx-auto">
        <v-card class="pa-4 pa-md-6 form-card" elevation="4">
          <!-- HEADER -->
          <div class="d-flex justify-space-between align-center mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-avatar size="40" class="mr-2 header-avatar" color="primary">
                <v-icon size="22" color="white">mdi-file-document</v-icon>
              </v-avatar>
              <div>
                <div class="text-subtitle-1 font-weight-medium header-title">
                  {{ isEdit ? t('فرم اصلاح مکتوب') : t('فرم ثبت مکتوب') }}
                </div>
                <div class="text-caption text-medium-emphasis header-subtitle">
                  {{ t('خانه‌های ستاره‌دار اجباری است.') }}
                </div>
              </div>
            </div>
          </div>

          <!-- SECTION 1: Type + Text -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-text</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('نوع و متن') }}
                </span>
              </div>
              <!-- <span class="section-subtitle text-caption">
                {{ t('نوع مکتوب، تفصیل، مرجع و امضاء') }}
              </span> -->
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="12">
                <DocsTypeAutocomplete v-model="form.docs_type" :label="t('نوع سند')" />
              </v-col>

              <v-col cols="12" md="12">
                <MarjaAutocomplete v-model="form.marja" :label="t('مرجع')" />
              </v-col>

              <v-col cols="12" md="12">
                <v-textarea
                  v-model="form.tafseel"
                  :label="t('تفصیل')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('tafseel')"
                  :error-messages="getErrorMessages('tafseel')"
                  auto-grow
                  rows="3"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 2: Dates -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-calendar</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('تاریخ ها و صفحات') }}
                </span>
              </div>
              <!-- <span class="section-subtitle text-caption">
                {{ t('تاریخ‌های دفتر، ریاست‌ها، پاسخ و امضاء') }}
              </span> -->
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="6">
                <DateInput
                  v-model="form.docs_sign_date"
                  :label="t('تاریخ امضاء')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('docs_sign_date')"
                  :error-messages="getErrorMessages('docs_sign_date')"
                />
              </v-col>

              <!-- <v-col cols="12" md="3">
                <DateInput
                  v-model="form.outgoing_incoming_date"
                  :label="t('تاریخ صادره/وارده')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('outgoing_incoming_date')"
                  :error-messages="getErrorMessages('outgoing_incoming_date')"
                />
              </v-col> -->
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.answer_page"
                  :label="t('صفحه پاسخ')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('answer_page')"
                  :error-messages="getErrorMessages('answer_page')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <DateInput
                  v-model="form.answer_date"
                  :label="t('پاسخ تاریخ')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('answer_date')"
                  :error-messages="getErrorMessages('answer_date')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.office_outgoing_page"
                  :label="t('صادره (دفتر) صفحه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('office_outgoing_page')"
                  :error-messages="getErrorMessages('office_outgoing_page')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <DateInput
                  v-model="form.office_outgoing_date"
                  :label="t('صادره (دفتر) تاریخ')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('office_outgoing_date')"
                  :error-messages="getErrorMessages('office_outgoing_date')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.office_incoming_page"
                  :label="t('وارده (دفتر) صفحه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('office_incoming_page')"
                  :error-messages="getErrorMessages('office_incoming_page')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <DateInput
                  v-model="form.office_incoming_date"
                  :label="t('وارده (دفتر) تاریخ')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('office_incoming_date')"
                  :error-messages="getErrorMessages('office_incoming_date')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.reyasat_outgoing_page"
                  :label="t('صادره به ریاست صفحه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('reyasat_outgoing_page')"
                  :error-messages="getErrorMessages('reyasat_outgoing_page')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <DateInput
                  v-model="form.reyasat_outgoing_date"
                  :label="t('صادره به ریاست تاریخ')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('reyasat_outgoing_date')"
                  :error-messages="getErrorMessages('reyasat_outgoing_date')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.loy_reyasat_outgoing_page"
                  :label="t('صادره (لوی ریاست) صفحه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('loy_reyasat_outgoing_page')"
                  :error-messages="getErrorMessages('loy_reyasat_outgoing_page')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <DateInput
                  v-model="form.loy_reyasat_outgoing_date"
                  :label="t('صادره (لوی ریاست) تاریخ')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('loy_reyasat_outgoing_date')"
                  :error-messages="getErrorMessages('loy_reyasat_outgoing_date')"
                />
              </v-col>
            </v-row>
          </v-sheet>
          <!-- SECTION 4: Counts + Notes -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header docs-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-note-text</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('ضمیمه و ملاحظات') }}
                </span>
              </div>
              <!-- <span class="section-subtitle text-caption">
                {{ t('تعداد نقل، اوراق، ضمیمه و ملاحظات') }}
              </span> -->
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.page"
                  :label="t('صفحه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('page')"
                  :error-messages="getErrorMessages('page')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.dusya"
                  :label="t('دوسیه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('dusya')"
                  :error-messages="getErrorMessages('dusya')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.fihrist_page"
                  :label="t('صفحه فهرست')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('fihrist_page')"
                  :error-messages="getErrorMessages('fihrist_page')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.naqal_count"
                  :label="t('تعداد نقل')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.awraq"
                  :label="t('اوراق')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.zamima"
                  :label="t('ضمیمه')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="12">
                <v-textarea
                  v-model="form.notes"
                  :label="t('ملاحظات')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('notes')"
                  :error-messages="getErrorMessages('notes')"
                  auto-grow
                  rows="3"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- ACTIONS -->
          <div class="form-actions d-flex justify-end gap-2 flex-wrap">
            <v-btn
              type="submit"
              color="primary"
              class="text-none"
              :loading="saving"
              prepend-icon="mdi-content-save"
            >
              {{ isEdit ? t('ذخیرهٔ تغییرات') : t('ثبت') }}
            </v-btn>

            <v-btn
              color="warning"
              variant="outlined"
              class="text-none"
              prepend-icon="mdi-broom"
              @click="resetForm"
            >
              {{ t('پاک کردن ورودی‌ها') }}
            </v-btn>
          </div>
        </v-card>
      </div>
    </v-form>

    <!-- ERROR -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>

    <!-- SUCCESS -->
    <v-snackbar v-model="showSuccess" color="success" timeout="3000">
      {{ successMessage }}
      <template #actions>
        <v-btn variant="text" @click="showSuccess = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, computed, watch, onMounted, nextTick } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';

  import DateInput from '@/components/DateInput.vue';
  import AppBar from '@/components/AppBar.vue';
  import { useOutgoingIncomingRepository } from '@/stores/OutgoingIncomingRepository';
  import DocsTypeAutocomplete from '@/components/DocsTypeAutocomplete.vue';
  import MarjaAutocomplete from '@/components/MarjaAutocomplete.vue';
  const repo = useOutgoingIncomingRepository();
  const route = useRoute();
  const router = useRouter();
  const { t, locale } = useI18n();

  /* RTL */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  /* MODE */
  const idParam = computed(() => route.params.id);
  const isEdit = computed(() => !!idParam.value);

  /* FORM */
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
  function todayISO() {
    if (repo?.getCurrentDate && typeof repo.getCurrentDate === 'function') {
      return repo.getCurrentDate();
    }
    return new Date().toISOString().slice(0, 10);
  }

  /* Backend Errors */
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

  /* Blank form (✅ matches OutgoingIncoming model) */
  const blankForm = () => ({
    id: null,

    docs_type: '',

    office_outgoing_date: '',
    office_outgoing_page: '',

    office_incoming_date: '',
    office_incoming_page: '',

    reyasat_outgoing_date: '',
    reyasat_outgoing_page: '',

    loy_reyasat_outgoing_date: '',
    loy_reyasat_outgoing_page: '',

    answer_date: '',
    answer_page: '',

    docs_sign_date: todayISO(),
    // outgoing_incoming_date: todayISO(),

    who_sighn: '',
    marja: '',
    tafseel: '',

    page: '',
    dusya: '',
    fihrist_page: '',

    naqal_count: '',
    awraq: '',
    zamima: '',

    notes: '',
  });

  const form = ref(blankForm());

  /* Rules */
  const rules = {
    required: (v) => !!String(v ?? '').trim() || t('این فیلد الزامی است'),
  };

  /* Map API -> form */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();
    b.id = x.id ?? null;

    b.docs_type = x.docs_type ?? '';

    b.office_outgoing_date = x.office_outgoing_date ?? '';
    b.office_outgoing_page = x.office_outgoing_page ?? '';

    b.office_incoming_date = x.office_incoming_date ?? '';
    b.office_incoming_page = x.office_incoming_page ?? '';

    b.reyasat_outgoing_date = x.reyasat_outgoing_date ?? '';
    b.reyasat_outgoing_page = x.reyasat_outgoing_page ?? '';

    b.loy_reyasat_outgoing_date = x.loy_reyasat_outgoing_date ?? '';
    b.loy_reyasat_outgoing_page = x.loy_reyasat_outgoing_page ?? '';

    b.answer_date = x.answer_date ?? '';
    b.answer_page = x.answer_page ?? '';

    b.docs_sign_date = x.docs_sign_date ?? todayISO();
    // b.outgoing_incoming_date = x.outgoing_incoming_date ?? todayISO();

    b.who_sighn = x.who_sighn ?? '';
    b.marja = x.marja ?? '';
    b.tafseel = x.tafseel ?? '';

    b.page = x.page ?? '';
    b.dusya = x.dusya ?? '';
    b.fihrist_page = x.fihrist_page ?? '';

    b.naqal_count = x.naqal_count ?? '';
    b.awraq = x.awraq ?? '';
    b.zamima = x.zamima ?? '';

    b.notes = x.notes ?? '';

    return b;
  }

  async function loadForEdit() {
    if (!isEdit.value) {
      form.value = blankForm();
      return;
    }

    try {
      const d = await repo.FetchOutgoingIncomingItem(idParam.value);
      form.value = mapFromApi(d || {});
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات'));
    }
  }

  /* Payload builder (no files ✅) */
 function buildPayload() {
  const { id, ...p } = form.value; // ✅ remove id

  ['naqal_count', 'awraq', 'zamima'].forEach((k) => {
    if (p[k] === '') return;
    const n = Number(p[k]);
    p[k] = Number.isFinite(n) ? n : p[k];
  });

  Object.keys(p).forEach((k) => {
    if (typeof p[k] === 'string') p[k] = p[k].trim();
  });

  return p;
}


  /* Submit */
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

    saving.value = true;
    try {
      const payload = buildPayload();

      if (!isEdit.value) {
        const created = await repo.CreateOutgoingIncoming(payload).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push({ path: '/app/outgoingincommings' }).catch(() => {});
        } else {
          showErrorMessage(t('ثبت ناکام شد'));
        }
      } else {
        try {
          const payload = buildPayload();

          // ✅ use route id (safer)
          const res = await repo.UpdateOutgoingIncoming(idParam.value, payload);

          // ✅ handle axios response shape
          const updatedRow = res?.data?.data ?? res?.data ?? res;

          if (updatedRow?.id) {
            showSuccessMessage(t('تغییرات ذخیره شد'));
            router.push({ path: '/app/outgoingincommings' }).catch(() => {});
          } else {
            showErrorMessage(t('ذخیره ناکام شد'));
          }
        } catch (e) {
          console.error('UPDATE ERROR =>', e);

          // ✅ show backend validation errors (Laravel)
          if (e?.response?.data?.errors) {
            setBackendErrors(e.response.data.errors);
            showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
            await nextTick();
            scrollToFirstInvalid();
            return;
          }

          // ✅ show backend message if exists
          const msg = e?.response?.data?.message || e?.message || t('خطا در ذخیره اطلاعات');
          showErrorMessage(msg);
        }
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در ذخیره اطلاعات'));
    } finally {
      saving.value = false;
    }
  }

  function scrollToFirstInvalid() {
    const first =
      document.querySelector(".v-input--error, [aria-invalid='true']") ||
      document.querySelector('.v-messages__message');
    if (first) {
      first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      const input = first.querySelector('input, textarea, .v-field__input');
      if (input && typeof input.focus === 'function') input.focus();
    }
  }

  function resetForm() {
    form.value = blankForm();
    formRef.value?.resetValidation?.();
    clearBackendErrors();
    showSuccessMessage(t('ورودی‌ها پاک شد'));
  }

  function goBack() {
    router.back();
  }

  /* Init */
  onMounted(loadForEdit);

  /* Stepper */
  const step1Complete = computed(() => !!String(form.value.docs_type || '').trim());

  const step2Complete = computed(
    () =>
      !!String(form.value.outgoing_incoming_date || '').trim() ||
      !!String(form.value.docs_sign_date || '').trim() ||
      !!String(form.value.office_outgoing_date || '').trim() ||
      !!String(form.value.office_incoming_date || '').trim() ||
      !!String(form.value.reyasat_outgoing_date || '').trim() ||
      !!String(form.value.loy_reyasat_outgoing_date || '').trim()
  );

  const step3Complete = computed(
    () =>
      !!String(form.value.office_outgoing_page || '').trim() ||
      !!String(form.value.office_incoming_page || '').trim() ||
      !!String(form.value.reyasat_outgoing_page || '').trim() ||
      !!String(form.value.loy_reyasat_outgoing_page || '').trim() ||
      !!String(form.value.page || '').trim() ||
      !!String(form.value.dusya || '').trim() ||
      !!String(form.value.fihrist_page || '').trim()
  );

  const step4Complete = computed(
    () =>
      !!String(form.value.notes || '').trim() ||
      !!String(form.value.zamima || '').trim() ||
      !!String(form.value.naqal_count || '').trim() ||
      !!String(form.value.awraq || '').trim()
  );

  const completedSteps = computed(
    () =>
      [step1Complete.value, step2Complete.value, step3Complete.value, step4Complete.value].filter(
        Boolean
      ).length
  );

  const currentStep = computed(() => {
    if (!step1Complete.value) return 1;
    if (!step2Complete.value) return 2;
    if (!step3Complete.value) return 3;
    return 4;
  });

  const progressValue = computed(() => (completedSteps.value / 4) * 100);
</script>

<style scoped>
  .page-bg {
    background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
    font-family: inherit;
  }
  .stepper-shell {
    position: absolute;
    top: 80px;
    left: 50%;
    transform: translateX(-50%);
    max-width: 90%;
    width: 100%;
    z-index: 20;
    margin: 0;
    padding-bottom: 12px;
  }
  .form-shell {
    max-width: 1150px;
    margin-top: 140px;
    margin-bottom: 40px;
  }
  @media (max-width: 960px) {
    .stepper-shell {
      top: 64px;
    }
  }
  .stepper-card {
    border-radius: 14px;
    padding: 10px 14px 12px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(33, 150, 243, 0.12);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    font-family: inherit;
  }
  .stepper-track-wrapper {
    position: relative;
    margin-top: 4px;
  }
  .stepper-track {
    opacity: 0.35;
  }
  .stepper-dots {
    position: absolute;
    inset-inline: 0;
    inset-block-start: -7px;
    display: flex;
    justify-content: space-between;
    pointer-events: none;
  }
  .stepper-dot-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
  }
  .stepper-dot {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e0e7f1;
    border: 2px solid #b0bec5;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: auto;
    transition: all 0.2s ease;
  }
  .stepper-dot.is-active:not(.is-complete) {
    background: #1976d2;
    border-color: #1976d2;
    box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.18);
  }
  .stepper-dot.is-complete {
    background: #4caf50;
    border-color: #4caf50;
    box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.18);
  }
  .stepper-dot :deep(.v-icon) {
    color: #fff !important;
  }
  .stepper-label {
    color: #555;
    font-family: inherit;
    font-weight: 600;
  }
  .is-complete-text {
    color: #2e7d32;
  }
  .stepper-caption {
    font-family: inherit;
    font-weight: 600;
  }

  .form-card {
    border-radius: 18px;
    border: 1px solid rgba(33, 150, 243, 0.08);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    font-family: inherit;
  }
  .header-avatar {
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.35);
  }
  .header-title {
    font-family: inherit;
    font-weight: 700;
  }
  .header-subtitle {
    font-family: inherit;
    font-weight: 600;
  }

  .section-card {
    background: #fafafa;
    border-radius: 16px;
    border: 1px solid #e0e0e0;
    transition: box-shadow 0.2s ease, transform 0.15s ease, border-color 0.2s ease;
    font-family: inherit;
  }
  .section-card:hover {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    border-color: rgba(33, 150, 243, 0.35);
    transform: translateY(-1px);
  }
  .section-header {
    padding: 8px 14px;
    background: #f7f7f9;
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-family: inherit;
  }
  .docs-header {
    background: #f3f6ff;
  }
  .section-subtitle {
    color: #777;
    font-family: inherit;
    font-weight: 600;
  }
  .section-icon {
    color: #1976d2;
  }
  .section-title {
    font-family: inherit;
    font-weight: 700;
  }

  .form-actions {
    margin-top: 4px;
  }

  :deep(.v-field-label),
  :deep(.v-label),
  :deep(input::placeholder) {
    font-size: 12px !important;
    line-height: 1.2 !important;
    color: #555 !important;
    font-family: inherit !important;
    font-weight: 600;
  }
  :deep(.v-field__input) {
    min-height: 30px !important;
    padding: 3px 8px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
    font-family: inherit !important;
  }
  :deep(.v-btn),
  :deep(.v-chip),
  :deep(.v-snackbar),
  :deep(.v-card-title),
  :deep(.v-card-subtitle),
  :deep(.v-list-item-title),
  :deep(.v-list-item-subtitle) {
    font-family: inherit !important;
  }
  .gap-2 {
    gap: 8px;
  }
</style>
