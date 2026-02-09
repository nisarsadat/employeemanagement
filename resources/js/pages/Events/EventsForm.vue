<template>
  <v-container fluid class="page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح واقعه') : t('اضافه کردن واقعه')"
      :subtitle="isEdit ? t('ویرایش جزئیات یک واقعه موجود') : t('ثبت یک واقعه جدید در سیستم')"
    >
    </AppBar>

    <!-- STEPPER (sticky style like office employee) -->
    <section class="stepper-shell" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card class="stepper-card py-4 pb-12" elevation="0">
        <div class="d-flex justify-space-between align-center mb-1">
          <span class="text-caption text-medium-emphasis stepper-caption">
            {{ t('مراحل خانه پری فرم واقعه') }}
          </span>
          <span class="text-caption text-primary font-weight-medium stepper-caption">
            {{ t('۳ بخش اصلی') }}
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
            <!-- 1: معلومات عمومی -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step1Complete,
                  'is-active': currentStep === 1 && !step1Complete,
                }"
              >
                <v-icon size="16">
                  {{ step1Complete ? 'mdi-check' : 'mdi-alert-decagram' }}
                </v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step1Complete }"
              >
                {{ t('معلومات عمومی') }}
              </span>
            </div>

            <!-- 2: زمان و مکان -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step2Complete,
                  'is-active': currentStep === 2 && !step2Complete,
                }"
              >
                <v-icon size="16">
                  {{ step2Complete ? 'mdi-check' : 'mdi-map-clock' }}
                </v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step2Complete }"
              >
                {{ t('زمان و مکان') }}
              </span>
            </div>

            <!-- 3: تلفات، اسناد و ملاحظات -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step3Complete,
                  'is-active': currentStep === 3 && !step3Complete,
                }"
              >
                <v-icon size="16">
                  {{ step3Complete ? 'mdi-check' : 'mdi-paperclip' }}
                </v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step3Complete }"
              >
                {{ t('تلفات و اسناد') }}
              </span>
            </div>
          </div>
        </div>
      </v-card>
    </section>

    <!-- FORM WRAPPER -->
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
                <v-icon size="22" color="white">mdi-alert-decagram</v-icon>
              </v-avatar>
              <div>
                <div class="text-subtitle-1 font-weight-medium header-title">
                  {{ isEdit ? t('فرم اصلاح واقعه') : t('فرم ثبت واقعه جدید') }}
                </div>
                <div class="text-caption text-medium-emphasis header-subtitle">
                  {{ t('لطفاً تمام خانه‌های مهم را با دقت خانه پری کنید.') }}
                </div>
              </div>
            </div>
          </div>

          <!-- SECTION 1: عمومی (نوع، منبع، هدف، مجرم) -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-alert-decagram</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('معلومات عمومی واقعه') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('نوعیت واقعه، منبع، کارمند دفتر، هدف و مجرم را مشخص کنید.') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="6">
                <EventTypeSpecificSelect
                  v-model:event_type="form.event_type"
                  v-model:event_specific_type="form.event_specific_type"
                  :cols-event-type="12"
                  :md-event-type="6"
                  :cols-specific-type="12"
                  :md-specific-type="6"
                />
              </v-col>

              <!-- منبع -->
              <v-col cols="12" md="3">
                <v-autocomplete
                  v-model="form.resource_id"
                  :items="resourceCodeItems"
                  item-title="display"
                  item-value="id"
                  :label="t('منبع')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :error="hasError('resource_id')"
                  :error-messages="getErrorMessages('resource_id')"
                />
              </v-col>

              <!-- کارمند دفتر -->
              <v-col cols="12" md="3">
                <v-autocomplete
                  v-model="form.office_employee_id"
                  :items="officeEmployeesCodeItems"
                  item-title="display"
                  item-value="id"
                  :label="t('کارمند دفتر')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :error="hasError('office_employee_id')"
                  :error-messages="getErrorMessages('office_employee_id')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-autocomplete
                  v-model="form.office_employee_akhistonky_id"
                  :items="officeEmployeesCodeItems"
                  item-title="display"
                  item-value="id"
                  :label="t('کود کارمند واقعه گیرنده')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :error="hasError('office_employee_akhistonky_id')"
                  :error-messages="getErrorMessages('office_employee_akhistonky_id')"
                />
              </v-col>

              <!-- هدف -->
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.target_person"
                  :label="t('هدف کی بود/مکان بود؟')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('target_person')"
                  :error-messages="getErrorMessages('target_person')"
                />
              </v-col>

              <!-- مجرم -->
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.criminal"
                  :label="t('کی جرم را انجام داده؟')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('criminal')"
                  :error-messages="getErrorMessages('criminal')"
                />
              </v-col>
            </v-row>

            <!-- تفصیل / وضاحت -->
            <v-row class="px-3 pb-3 pt-0">
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.tafseel"
                  :label="t('تفصیل')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('tafseel')"
                  :error-messages="getErrorMessages('tafseel')"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.talks"
                  :label="t('وضاحت/تشریحات')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('talks')"
                  :error-messages="getErrorMessages('talks')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 2: زمان و مکان -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-map-clock</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('زمان و مکان واقعه') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('تاریخ، ساعت، زون، حوزه، محل مشخص و کوردینات را درج کنید.') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <!-- تاریخ -->
              <v-col cols="12" md="8">
                <DateInput v-model="form.event_date" :label="t('تاریخ واقعه *')" />
              </v-col>

              <!-- ساعت -->
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.event_hour"
                  type="time"
                  step="60"
                  :label="t('ساعت واقعه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('event_hour')"
                  :error-messages="getErrorMessages('event_hour')"
                />
              </v-col>

              <!-- ولایت مربوطه -->
              <!-- <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.Province_addr"
                  :label="t('ولایت مربوطه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('Province_addr')"
                  :error-messages="getErrorMessages('Province_addr')"
                />
              </v-col> -->
            </v-row>

            <v-row class="px-3 pb-3 pt-0">
              <v-col cols="12" md="6">
                <ZoneHawzaSelect
                  v-model:zone="form.event_zone"
                  v-model:hawza="form.event_hawza"
                  :zoneLabel="t('زون')"
                  :hawzaLabel="t('حوزه')"
                  :disabled="saving"
                  :zoneError="hasError('event_zone')"
                  :zoneErrorMessages="getErrorMessages('event_zone')"
                  :hawzaError="hasError('event_hawza')"
                  :hawzaErrorMessages="getErrorMessages('event_hawza')"
                  :mdZone="6"
                  :mdHawza="6"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.event_specific_place"
                  :label="t('محل مشخص')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('event_specific_place')"
                  :error-messages="getErrorMessages('event_specific_place')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.coordinates"
                  :label="t('کوردینات')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('coordinates')"
                  :error-messages="getErrorMessages('coordinates')"
                />
              </v-col>
            </v-row>
          </v-sheet>
          <!-- FINANCE SECTION -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-cash-multiple</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('معلومات مالی') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('حساب و جزئیات مالی مربوط به واقعه') }}
              </span>
            </div>

            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="12">
                <v-autocomplete
                  v-model="form.account_id"
                  :items="accountItems"
                  item-title="display"
                  item-value="id"
                  :label="t('حساب')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :error="hasError('account_id')"
                  :error-messages="getErrorMessages('account_id')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.message_to_finance"
                  :label="t('مسج مالی بخش ته')"
                  type="number"
                  density="compact"
                  variant="outlined"
                  :error="hasError('message_to_finance')"
                  :error-messages="getErrorMessages('message_to_finance')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.money_amount_words"
                  :label="t('مقدار په حروف')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('money_amount_words')"
                  :error-messages="getErrorMessages('money_amount_words')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 3: تلفات، اسناد، ملاحظات -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header docs-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-paperclip</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('تلفات، اسناد و ملاحظات') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('تلفات جانی و مالی، اسناد ضمیمه و ملاحظات را درج کنید.') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <!-- <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.talafat_jani"
                  :label="t('تلفات جانی')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('talafat_jani')"
                  :error-messages="getErrorMessages('talafat_jani')"
                />
              </v-col> -->

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.talafat_mali"
                  :label="t('تلفات مالی')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('talafat_mali')"
                  :error-messages="getErrorMessages('talafat_mali')"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.killed"
                  :label="t('کشته شده')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('talafat_jani')"
                  :error-messages="getErrorMessages('talafat_jani')"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.injured"
                  :label="t('زخمی شده')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('talafat_mali')"
                  :error-messages="getErrorMessages('talafat_mali')"
                />
              </v-col>
              <v-col cols="12" md="12">
                <v-text-field
                  v-model="form.note"
                  :label="t('ملاحظات')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('note')"
                  :error-messages="getErrorMessages('note')"
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
            <v-row class="pa-3">
              <v-col cols="12" md="12">
                <v-text-field
                  v-model="form.docs_note"
                  :label="t('نوت راجع به اسناد')"
                  density="compact"
                  variant="outlined"
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
              {{ isEdit ? t('ذخیرهٔ تغییرات') : t('ثبت واقعه') }}
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

    <!-- ERROR SNACKBAR -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>

    <!-- SUCCESS SNACKBAR -->
    <v-snackbar v-model="showSuccess" color="success" timeout="3000">
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
  import { ref, computed, watch, onMounted, nextTick } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import EventTypeSpecificSelect from '@/components/EventTypeSpecificTypePicker.vue';
  import AppBar from '@/components/AppBar.vue';
  import DateInput from '@/components/DateInput.vue';
  import { useDatabaseRepository } from '@/stores/DatabaseRepository';
  import { useLoginRepository } from '@/stores/LoginRepository';
  import ZoneHawzaSelect from '@/components/ZoneHawzaSelect.vue';
  import { useFinanceRepository } from '@/stores/FinanceRepository';

  const financeRepo = useFinanceRepository();
  const repo = useDatabaseRepository();
  const auth = useLoginRepository();
  const route = useRoute();
  const router = useRouter();
  const { t, locale } = useI18n();

  /* Direction */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => {
    isRtl.value = newLocale === 'fa';
  });

  /* Route / Mode */
  const idParam = computed(() => route.params.id);
  const isEdit = computed(() => !!idParam.value);

  /* STATE */
  const formRef = ref(null);
  const formValid = ref(false);
  const saving = ref(false);

  const showError = ref(false);
  const errorMessage = ref('');
  const showSuccess = ref(false);
  const successMessage = ref('');
  const docsInput = ref(null);

  /* error helpers */
  const backendErrors = ref({});
  const setBackendErrors = (e) => (backendErrors.value = e || {});
  const clearBackendErrors = () => (backendErrors.value = {});
  const hasError = (field) => !!backendErrors.value?.[field];
  const getErrorMessages = (field) => backendErrors.value?.[field] ?? [];

  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }
  function showSuccessMessage(msg) {
    successMessage.value = msg;
    showSuccess.value = true;
  }

  /* rules */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
  };

  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  const resourceCodeItems = computed(() =>
    toArray(repo.resourcecode).map((r) => ({
      ...r,
      display:
        r.code_number && r.name ? `${r.code_number} - ${r.name}` : r.code_number || r.name || '',
    }))
  );
  const accountItems = computed(() =>
    (financeRepo.accounts || []).map((a) => ({
      ...a,
      display: a.name ? `${a.name} (${a.price ?? '0'})` : `${a.id}`,
    }))
  );

  const officeEmployeesCodeItems = computed(() =>
    toArray(repo.officeemployeescode).map((o) => ({
      ...o,
      display:
        o.code_number && o.name ? `${o.code_number} - ${o.name}` : o.code_number || o.name || '',
    }))
  );

  const janiTalafatItems = computed(() => toArray(repo.janiTalafatOptions));
  const maliTalafatItems = computed(() => toArray(repo.maliTalafatOptions));

  /* form model */
  const blankForm = () => ({
    id: null,

    resource_id: null,
    office_employee_id: null,
    office_employee_akhistonky_id: null,
    account_id: null,

    event_type: '',
    event_specific_type: '',
    target_person: '',
    criminal: '',

    event_date: '',
    event_hour: '',

    event_zone: '',
    event_hawza: '',
    event_specific_place: '',
    Province_addr: '',
    coordinates: '',

    // talafat_jani: '',
    talafat_mali: '',
    killed: '',
    injured: '',

    tafseel: '',
    talks: '',
    note: '',
    docs_note: '',
    message_to_finance: '',
    money_amount_words: '',

    existingDocs: [],
    _newDocs: [],
    removed_document_ids: [],
  });

  const form = ref(blankForm());

  /* docs */
  function triggerDocs() {
    docsInput.value?.click();
  }
  function onPickDocs(e) {
    const files = Array.from(e.target.files || []);
    form.value._newDocs = files;
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
    if (!Array.isArray(form.value.removed_document_ids)) {
      form.value.removed_document_ids = [];
    }
    if (!form.value.removed_document_ids.includes(doc.id)) {
      form.value.removed_document_ids.push(doc.id);
    }
    form.value.existingDocs = (form.value.existingDocs || []).filter((d) => d.id !== doc.id);
  }

  /* map API -> form */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const f = blankForm();

    f.id = x.id ?? null;

    f.resource_id = x.resource_id ?? x.resource?.id ?? null;
    f.office_employee_id = x.office_employee_id ?? x.office_employee?.id ?? null;
    f.office_employee_akhistonky_id = x.office_employee_akhistonky_id ?? null;
    f.account_id = x.account_id ?? x.account?.id ?? null;

    f.event_type = x.event_type ?? '';
    f.event_specific_type = x.event_specific_type ?? '';
    f.target_person = x.target_person ?? '';
    f.criminal = x.criminal ?? '';

    f.event_date = x.event_date ?? '';
    f.event_hour = x.event_hour ?? '';

    f.event_zone = x.event_zone ?? '';
    f.event_hawza = x.event_hawza ?? '';
    f.event_specific_place = x.event_specific_place ?? '';
    f.Province_addr = x.Province_addr ?? '';
    f.coordinates = x.coordinates ?? '';

    // f.talafat_jani = x.talafat_jani ?? '';
    f.talafat_mali = x.talafat_mali ?? '';
    f.injured = x.injured ?? '';
    f.killed = x.killed ?? '';

    f.tafseel = x.tafseel ?? '';
    f.talks = x.talks ?? '';
    f.note = x.note ?? '';
    f.message_to_finance = x.message_to_finance ?? '';
    f.money_amount_words = x.money_amount_words ?? '';
    f.docs_note = x.docs_note ?? '';

    f.existingDocs = Array.isArray(x.documents) ? x.documents : [];

    return f;
  }

  async function loadForEdit() {
    if (!isEdit.value) {
      form.value = blankForm();
      return;
    }
    try {
      if (typeof repo.FetchEventData === 'function') {
        await repo.FetchEventData(idParam.value);
        const raw = Array.isArray(repo.Event) ? repo.Event[0] : repo.Event || {};
        form.value = { ...mapFromApi(raw), _newDocs: [], removed_document_ids: [] };
      }
    } catch (e) {
      console.error('loadForEdit error', e);
      showErrorMessage(t('خطا در دریافت معلومات واقعه'));
    }
  }

  /* formdata */
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
    const newDocs = payload._newDocs || [];
    delete payload._newDocs;
    delete payload.existingDocs;
    delete payload.removed_document_ids;

    const fd = toFormData(payload);
    newDocs.forEach((file) => fd.append('documents[]', file));
    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    const newDocs = payload._newDocs || [];
    const removed = payload.removed_document_ids || [];

    delete payload._newDocs;
    delete payload.existingDocs;
    delete payload.removed_document_ids;

    const fd = toFormData(payload);
    newDocs.forEach((file) => fd.append('documents[]', file));
    removed.forEach((id) => fd.append('removed_document_ids[]', id));
    return fd;
  }

  /* submit */
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

    if (!form.value.event_type?.trim()) {
      showErrorMessage(t('نوعیت واقعه الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateEvent(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('واقعه موفقانه ثبت شد'));
          router.push({ path: '/app/events' }).catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateEvent(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push({ path: '/app/events' }).catch(() => {});
        } else {
          showErrorMessage(t('ذخیرهٔ تغییرات ناکام شد'));
        }
      }
    } catch (e) {
      console.error('onSubmit error', e);
      showErrorMessage(t('خطا در ذخیره اطلاعات'));
    } finally {
      saving.value = false;
    }
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

  function resetForm() {
    form.value = blankForm();
    formRef.value?.resetValidation?.();
    clearBackendErrors();
    showSuccessMessage(t('ورودی‌ها پاک شد'));
  }

  function goBack() {
    router.back();
  }

  /* init */
  onMounted(async () => {
    await Promise.allSettled([
      repo.GetResource?.(),
      repo.GetOfficeEmployee?.(),
      repo.GetResourceCode?.(),
      repo.GetOfficeEmployeeCode?.(),

      financeRepo.FetchAccountsData(), // ✅ درست (accounts loads into financeRepo.accounts)
    ]);

    await Promise.allSettled([
      repo.GetJaniTalafatOptions?.() ?? Promise.resolve(),
      repo.GetMaliTalafatOptions?.() ?? Promise.resolve(),
    ]).catch(() => {});

    await loadForEdit();
  });

  /* stepper logic */
  const step1Complete = computed(() => !!form.value.event_type?.trim());
  const step2Complete = computed(() => !!form.value.event_date?.trim());
  const step3Complete = computed(
    () =>
      !!form.value.talafat_mali?.trim() ||
      !!form.value.note?.trim() ||
      (form.value._newDocs?.length || 0) > 0 ||
      (form.value.existingDocs?.length || 0) > 0
  );

  const completedSteps = computed(
    () => [step1Complete.value, step2Complete.value, step3Complete.value].filter(Boolean).length
  );

  const currentStep = computed(() => {
    if (!step1Complete.value) return 1;
    if (!step2Complete.value) return 2;
    return 3;
  });

  const progressValue = computed(() => (completedSteps.value / 3) * 100);
</script>

<style scoped>
  .page-bg {
    background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
    font-family: inherit;
  }

  /* stepper like office employee */
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

  /* card */
  .form-card {
    border-radius: 18px;
    border: 1px solid rgba(33, 150, 243, 0.08);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    font-family: inherit;
  }

  /* header */
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

  /* sections */
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

  /* docs */
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

  /* actions */
  .form-actions {
    margin-top: 4px;
  }

  /* compact inputs */
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

  /* buttons & chips font */
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
