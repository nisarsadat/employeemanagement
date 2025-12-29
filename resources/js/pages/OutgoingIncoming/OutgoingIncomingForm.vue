<template>
  <v-container fluid class="page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح مکتوب') : t('ثبت مکتوب صادره/وارده')"
      :subtitle="isEdit ? t('ویرایش جزئیات') : t('ثبت یک مورد جدید در سیستم')"
    >
      <template #actions>
        <v-btn variant="text" prepend-icon="mdi-arrow-left" class="text-none" @click="goBack">
          {{ t('برگشت') }}
        </v-btn>
      </template>
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
                <v-icon size="16">{{ step1Complete ? 'mdi-check' : 'mdi-numeric' }}</v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step1Complete }"
              >
                {{ t('شماره‌ها') }}
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
                <v-icon size="16">{{ step2Complete ? 'mdi-check' : 'mdi-swap-horizontal' }}</v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step2Complete }"
              >
                {{ t('صادره/وارده') }}
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
                <v-icon size="16">{{ step3Complete ? 'mdi-check' : 'mdi-calendar' }}</v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step3Complete }"
              >
                {{ t('تاریخ و مرجع') }}
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
                <v-icon size="16">{{ step4Complete ? 'mdi-check' : 'mdi-paperclip' }}</v-icon>
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

          <!-- SECTION 1: Numbers -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-numeric</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('شماره‌ها و جزئیات') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('شماره، جلد، لیست، صفحات، مقدار نقل') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.number"
                  :label="t('شماره *')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.required]"
                  :error="hasError('number')"
                  :error-messages="getErrorMessages('number')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.numer_of_jold"
                  :label="t('شماره جلد')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('numer_of_jold')"
                  :error-messages="getErrorMessages('numer_of_jold')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.list_number"
                  :label="t('شماره لیست')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('list_number')"
                  :error-messages="getErrorMessages('list_number')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.pages"
                  :label="t('صفحات')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('pages')"
                  :error-messages="getErrorMessages('pages')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.naql_amount"
                  :label="t('مقدار نقل')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('naql_amount')"
                  :error-messages="getErrorMessages('naql_amount')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 2: Outgoing/Incoming + Ahkam -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-swap-horizontal</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('صادره / وارده و احکام') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('وارده، صادره به ریاست/دیگران و احکام') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.incomming"
                  :label="t('وارده')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('incomming')"
                  :error-messages="getErrorMessages('incomming')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.outgoing_to_reyasat"
                  :label="t('صادره به ریاست')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('outgoing_to_reyasat')"
                  :error-messages="getErrorMessages('outgoing_to_reyasat')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.out_going_to_others"
                  :label="t('صادره به دیگران')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('out_going_to_others')"
                  :error-messages="getErrorMessages('out_going_to_others')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.ahkam_reyasat"
                  :label="t('احکام ریاست')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.ahkam_rad_shoda_reyasat"
                  :label="t('احکام رد شده ریاست')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.reyasat_na_direction"
                  :label="t('ریاست بدون هدایت')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.loy_reyasat_ahkam"
                  :label="t('لوی ریاست احکام')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.loy_reyasat_ahakm_rad"
                  :label="t('لوی ریاست احکام رد')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.loy_reyasat_direction"
                  :label="t('لوی ریاست هدایت')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 3: Dates + Marja -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-calendar</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('تاریخ و مرجع') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('تاریخ‌ها، مرجع و امضاء') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <DateInput
                  v-model="form.sign_date"
                  :label="t('تاریخ امضاء')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('sign_date')"
                  :error-messages="getErrorMessages('sign_date')"
                />
              </v-col>
              <v-col cols="12" md="4">
                <DateInput
                  v-model="form.outgoing_incoming_date"
                  :label="t('تاریخ صادره/وارده')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('outgoing_incoming_date')"
                  :error-messages="getErrorMessages('outgoing_incoming_date')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.marja"
                  :label="t('مرجع')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('marja')"
                  :error-messages="getErrorMessages('marja')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.who_sign"
                  :label="t('امضاء کننده')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('who_sign')"
                  :error-messages="getErrorMessages('who_sign')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 4: Zamima + Notes + Docs -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header docs-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-paperclip</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('ضمیمه و ملاحظات') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('ضمیمه (متن)، ملاحظات و فایل‌ها') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.zamima"
                  :label="t('ضمیمه (متن)')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('zamima')"
                  :error-messages="getErrorMessages('zamima')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.notes"
                  :label="t('ملاحظات')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('notes')"
                  :error-messages="getErrorMessages('notes')"
                />
              </v-col>
            </v-row>

            <!-- DOCS -->
            <div class="pa-3 pt-0">
              <input
                ref="docsInput"
                class="hidden-file"
                type="file"
                multiple
                @change="onPickDocs"
              />

              <div class="docs-field-row">
                <div class="d-flex align-center gap-2 flex-wrap">
                  <v-btn
                    class="uploader-btn2"
                    size="small"
                    variant="elevated"
                    color="primary"
                    density="comfortable"
                    prepend-icon="mdi-tray-arrow-up"
                    @click="triggerDocs"
                  >
                    {{ t('انتخاب و آپلود فایل‌ها') }}
                  </v-btn>

                  <v-chip size="small" variant="outlined" class="text-caption docs-counter-chip">
                    <v-icon size="16" class="ml-1">mdi-file-multiple</v-icon>
                    {{ t('فایل جدید') }}: {{ form._newDocs.length }}
                    &nbsp;|&nbsp;
                    {{ t('موجود') }}: {{ form.existingDocs.length }}
                  </v-chip>
                </div>

                <div
                  class="chips-row"
                  :title="
                    form._newDocs.length || form.existingDocs.length
                      ? ''
                      : t('فایلی انتخاب نشده است')
                  "
                >
                  <!-- NEW FILES -->
                  <v-chip
                    v-for="(f, idx) in form._newDocs"
                    :key="`nf-${idx}`"
                    size="small"
                    variant="outlined"
                    class="chip-outline"
                    :title="f.name"
                    pill
                  >
                    <v-icon size="16" class="ml-1">mdi-plus</v-icon>
                    {{ shortName(f.name) }}
                    <v-icon size="16" class="ml-1" title="حذف" @click.stop="removeNewDocAt(idx)">
                      mdi-close
                    </v-icon>
                  </v-chip>

                  <!-- EXISTING FILES -->
                  <v-chip
                    v-for="(d, idx) in form.existingDocs"
                    :key="`df-${idx}`"
                    size="small"
                    color="primary"
                    variant="flat"
                    class="chip-existing"
                    :title="d.original_name || d.file_name"
                    @click="openDoc(d.url)"
                    pill
                  >
                    <v-icon size="16" class="ml-1">mdi-file-eye</v-icon>
                    {{ shortName(d.original_name || d.file_name) }}
                    <v-icon size="16" class="ml-1" title="حذف" @click.stop="markDocForRemoval(d)">
                      mdi-close
                    </v-icon>
                  </v-chip>
                </div>
              </div>
            </div>
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
    // ✅ like Info form: use repo.getCurrentDate() if exists
    if (repo?.getCurrentDate && typeof repo.getCurrentDate === 'function') {
      return repo.getCurrentDate();
    }
    // fallback (yyyy-mm-dd)
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

  /* Blank form */
  const blankForm = () => ({
    id: null,

    outgoing_to_reyasat: '',
    out_going_to_others: '',
    incomming: '',

    ahkam_reyasat: '',
    ahkam_rad_shoda_reyasat: '',
    reyasat_na_direction: '',

    loy_reyasat_ahkam: '',
    loy_reyasat_ahakm_rad: '',
    loy_reyasat_direction: '',

    number: '',

    // ✅ default today
    sign_date: repo.getCurrentDate ? repo.getCurrentDate() : '',
    outgoing_incoming_date: repo.getCurrentDate ? repo.getCurrentDate() : '',

    marja: '',
    who_sign: '',

    numer_of_jold: '',
    list_number: '',

    naql_amount: '',
    pages: '',

    zamima: '',
    notes: '',

    existingDocs: [],
    _newDocs: [],
    removed_document_ids: [],
  });

  const form = ref(blankForm());

  /* Rules */
  const rules = {
    required: (v) => !!String(v ?? '').trim() || t('این فیلد الزامی است'),
  };

  /* DOCS */
  const docsInput = ref(null);

  function triggerDocs() {
    docsInput.value?.click();
  }
  function onPickDocs(e) {
    const files = Array.from(e.target.files || []);
    form.value._newDocs = files;
    if (docsInput.value) docsInput.value.value = '';
  }
  function removeNewDocAt(index) {
    if (!Array.isArray(form.value._newDocs)) return;
    form.value._newDocs.splice(index, 1);
    if (docsInput.value) docsInput.value.value = '';
  }
  function shortName(name = '') {
    if (name.length <= 18) return name;
    const dot = name.lastIndexOf('.');
    if (dot === -1) return name.slice(0, 15) + '…';
    const ext = name.slice(dot);
    const base = name.slice(0, 15);
    return `${base}…${ext}`;
  }
  function openDoc(url) {
    if (!url) return;
    window.open(url, '_blank');
  }
  function markDocForRemoval(doc) {
    if (!doc?.id) return;
    if (!Array.isArray(form.value.removed_document_ids)) form.value.removed_document_ids = [];
    if (!form.value.removed_document_ids.includes(doc.id))
      form.value.removed_document_ids.push(doc.id);
    form.value.existingDocs = (form.value.existingDocs || []).filter((d) => d.id !== doc.id);
  }

  /* Map API -> form */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();
    b.id = x.id ?? null;

    b.outgoing_to_reyasat = x.outgoing_to_reyasat ?? '';
    b.out_going_to_others = x.out_going_to_others ?? '';
    b.incomming = x.incomming ?? '';

    b.ahkam_reyasat = x.ahkam_reyasat ?? '';
    b.ahkam_rad_shoda_reyasat = x.ahkam_rad_shoda_reyasat ?? '';
    b.reyasat_na_direction = x.reyasat_na_direction ?? '';

    b.loy_reyasat_ahkam = x.loy_reyasat_ahkam ?? '';
    b.loy_reyasat_ahakm_rad = x.loy_reyasat_ahakm_rad ?? '';
    b.loy_reyasat_direction = x.loy_reyasat_direction ?? '';

    b.number = x.number ?? '';
    b.sign_date = x.sign_date ?? todayISO();
    b.outgoing_incoming_date = x.outgoing_incoming_date ?? todayISO();

    b.marja = x.marja ?? '';
    b.who_sign = x.who_sign ?? '';

    b.numer_of_jold = x.numer_of_jold ?? '';
    b.list_number = x.list_number ?? '';

    b.naql_amount = x.naql_amount ?? '';
    b.pages = x.pages ?? '';

    b.zamima = x.zamima ?? '';
    b.notes = x.notes ?? '';

    // docs: your backend might return any of these
    b.existingDocs =
      (Array.isArray(x.documents) && x.documents) ||
      (Array.isArray(x.sundryDocuments) && x.sundryDocuments) ||
      (Array.isArray(x.sundry_documents) && x.sundry_documents) ||
      [];

    return b;
  }

  async function loadForEdit() {
    if (!isEdit.value) {
      form.value = blankForm();
      return;
    }

    try {
      const d = await repo.FetchOutgoingIncomingItem(idParam.value);
      const mapped = mapFromApi(d || {});
      form.value = { ...mapped, _newDocs: [], removed_document_ids: [] };
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات'));
    }
  }

  /* FormData helpers */
  function toFormData(obj) {
    const fd = new FormData();

    Object.entries(obj).forEach(([k, v]) => {
      if (v === undefined || v === null) return;

      // skip empty strings -> otherwise backend converts "" to null
      if (typeof v === 'string' && v.trim() === '') return;

      fd.append(k, v);
    });

    return fd;
  }
  function isYmdString(v) {
    return typeof v === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(v);
  }

  function toYmd(v) {
    // Already correct
    if (isYmdString(v)) return v;

    // If Date object
    if (v instanceof Date && !isNaN(v)) {
      return v.toISOString().slice(0, 10);
    }

    // If string but includes time like "2025-12-29T..."
    if (typeof v === 'string' && v.includes('T')) {
      const maybe = v.slice(0, 10);
      if (isYmdString(maybe)) return maybe;
    }

    return ''; // invalid -> skip
  }

  function appendDate(fd, key, v) {
    const ymd = toYmd(v);
    if (ymd) fd.append(key, ymd);
  }

  function buildCreateFormData() {
    const payload = { ...form.value };
    delete payload.existingDocs;
    delete payload._newDocs;
    delete payload.removed_document_ids;

    // remove date keys from payload, we will append them safely
    const signDate = payload.sign_date;
    const outgoingDate = payload.outgoing_incoming_date;
    delete payload.sign_date;
    delete payload.outgoing_incoming_date;

    const fd = toFormData(payload);

    // ✅ append dates safely
    fd.set('sign_date', String(form.value.sign_date || repo.getCurrentDate()));
    fd.set(
      'outgoing_incoming_date',
      String(form.value.outgoing_incoming_date || repo.getCurrentDate())
    );

    (form.value._newDocs || []).forEach((file) => fd.append('documents[]', file));
    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    const removed = payload.removed_document_ids || [];

    delete payload.existingDocs;
    delete payload._newDocs;
    delete payload.removed_document_ids;

    const signDate = payload.sign_date;
    const outgoingDate = payload.outgoing_incoming_date;
    delete payload.sign_date;
    delete payload.outgoing_incoming_date;

    const fd = toFormData(payload);

    // ✅ append dates safely
    fd.set('sign_date', String(form.value.sign_date || repo.getCurrentDate()));
    fd.set(
      'outgoing_incoming_date',
      String(form.value.outgoing_incoming_date || repo.getCurrentDate())
    );

    (form.value._newDocs || []).forEach((file) => fd.append('documents[]', file));
    removed.forEach((id) => fd.append('removed_document_ids[]', id));

    return fd;
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
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateOutgoingIncoming(fd).catch((e) => {
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
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateOutgoingIncoming(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات ذخیره شد'));
          router.push({ path: '/app/outgoingincommings' }).catch(() => {});
        } else {
          showErrorMessage(t('ذخیره ناکام شد'));
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
  const step1Complete = computed(() => !!String(form.value.number || '').trim());
  const step2Complete = computed(
    () =>
      !!String(form.value.incomming || '').trim() ||
      !!String(form.value.outgoing_to_reyasat || '').trim() ||
      !!String(form.value.out_going_to_others || '').trim()
  );
  const step3Complete = computed(
    () =>
      !!String(form.value.sign_date || '').trim() ||
      !!String(form.value.outgoing_incoming_date || '').trim() ||
      !!String(form.value.marja || '').trim()
  );
  const step4Complete = computed(
    () =>
      (form.value._newDocs?.length || 0) > 0 ||
      (form.value.existingDocs?.length || 0) > 0 ||
      !!String(form.value.notes || '').trim()
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

  /* DOCS */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .docs-field-row {
    display: flex;
    flex-direction: column;
    gap: 8px;
    border-radius: 12px;
    border: 1px dashed #b0bec5;
    padding: 10px 12px;
    background: linear-gradient(135deg, #f9fbff 0%, #f3f6ff 100%);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    font-family: inherit;
  }
  .chips-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding-top: 4px;
    max-height: 92px;
    overflow-y: auto;
    scrollbar-width: thin;
  }
  .chip-existing {
    background: #c8dae1 !important;
    color: #000 !important;
  }
  .chip-outline {
    background: #e3f2fd;
  }
  .docs-counter-chip {
    font-family: inherit;
    font-weight: 600;
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
