<template>
  <v-container flat fluid :dir="isRtl ? 'rtl' : 'ltr'">
    <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="onCreate">
      <v-card class="pa-4 form-card" elevation="2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- Header -->
        <v-card-title class="form-title d-flex align-center mb-4">
          <v-icon color="primary" class="mr-2">mdi-calendar-text</v-icon>
          {{ t('افزودن واقعه جدید') }}
        </v-card-title>

        <v-card-text class="pa-0 pretty-scroll scroll-body max-h-form" :dir="isRtl ? 'rtl' : 'ltr'">
          <!-- Codes -->
          <v-row class="tight-grid">
            <!-- resource_id -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کود منبع') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.resource_id"
                  :items="resourceCodeItems"
                  item-title="code_number"
                  item-value="id"
                  variant="outlined"
                  rounded="lg"
                  class="cell"
                  density="compact"
                  clearable
                  :rules="[rules.selectRequired(t('کود منبع'))]"
                  prepend-inner-icon="mdi-database"
                />
              </v-card-text>
            </v-col>

            <!-- office_employee_id -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کود کارمند') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.office_employee_id"
                  :items="officeEmployeesCodeItems"
                  item-title="code_number"
                  item-value="id"
                  variant="outlined"
                  rounded="lg"
                  class="cell"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-badge-account"
                />
              </v-card-text> </v-col
            ><!-- office_employee_akhistonky_id -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{
                  t('کود کارمند واقعه گیرنده')
                }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.office_employee_akhistonky_id"
                  :items="officeEmployeesCodeItems"
                  item-title="code_number"
                  item-value="id"
                  variant="outlined"
                  rounded="lg"
                  class="cell"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-badge-account"
                />
              </v-card-text>
            </v-col>

            <!-- event_type -->
            <v-col cols="12" md="12">
              <EventTypeSpecificTypeSelect
                v-model:event_type="draft.event_type"
                v-model:event_specific_type="draft.event_specific_type"
                :eventTypeLabel="t('نوعیت واقعه')"
                :eventSpecificTypeLabel="t('نوعیت مشخص واقعه')"
                :colsEventType="12"
                :mdEventType="6"
                :colsSpecificType="12"
                :mdSpecificType="6"
                :disabled="submitting"
              />
            </v-col>

            <!-- target_person -->
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{
                  t('هدف کی بود/مکان بود')
                }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.target_person"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-target-account"
                />
              </v-card-text>
            </v-col>

            <!-- criminal -->
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{
                  t('کی جرم را انجام داده')
                }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.criminal"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  class="cell"
                  clearable
                  prepend-inner-icon="mdi-account-alert"
                />
              </v-card-text>
            </v-col>

            <!-- event_date -->
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تاریخ واقعه') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <DateInput
                  v-model="draft.event_date"
                  :rules="[rules.required]"
                  :label="t('تاریخ واقعه')"
                />
              </v-card-text>
            </v-col>

            <!-- event_hour -->
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('ساعت واقعه') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.event_hour"
                  variant="outlined"
                  density="compact"
                  type="time"
                  clearable
                  placeholder="HH:MM"
                  rounded="lg"
                  class="cell"
                  :rules="[rules.timeHM(t('ساعت واقعه'))]"
                  prepend-inner-icon="mdi-clock-outline"
                />
              </v-card-text>
            </v-col>
            <!-- <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('ولایت مربوطه') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.Province_addr"
                  :label="t('ولایت مربوطه')"
                  variant="outlined"
                  density="compact"
                  clearable
                  rounded="lg"
                  class="cell"
                />
              </v-card-text>
            </v-col> -->
          </v-row>

          <!-- Place -->
          <v-row class="tight-grid">
            <v-col cols="12">
              <v-card-subtitle class="section-subtitle mb-2">
                <v-icon size="small" class="mr-1">mdi-map-marker</v-icon>
                {{ t('محل واقعه') }}
              </v-card-subtitle>
            </v-col>

            <!-- ✅ Zone + Hawza (component) -->
            <v-col cols="12" md="8">
              <ZoneHawzaSelect
                v-model:zone="draft.event_zone"
                v-model:hawza="draft.event_hawza"
                :zoneLabel="t('زون')"
                :hawzaLabel="t('حوزه')"
                :disabled="saving"
                :mdZone="6"
                :mdHawza="6"
              />
            </v-col>
            <!-- event_specific_place -->
            <v-col cols="12" md="4">
              <v-text-field
                v-model="draft.event_specific_place"
                :label="t('محل مشخص')"
                variant="outlined"
                density="compact"
                rounded="lg"
                class="cell"
                clearable
                prepend-inner-icon="mdi-map-marker-radius"
              />
            </v-col>
          </v-row>

          <!-- Casualties -->
          <v-row class="tight-grid">
            <!-- <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تلفات جانی') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.talafat_jani"
                  item-title="name"
                  item-value="id"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  clearable
                  prepend-inner-icon="mdi-hospital"
                />
              </v-card-text>
            </v-col> -->

            <!-- talafat_mali -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تلفات مالی') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.talafat_mali"
                  item-title="name"
                  item-value="id"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  clearable
                  prepend-inner-icon="mdi-cash-multiple"
                />
              </v-card-text>
            </v-col>
            <!-- coordinates -->
            <v-col cols="12" md="8">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کوردینات') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.coordinates"
                  :label="t('کوردینات')"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  class="cell"
                  clearable
                  prepend-inner-icon="mdi-crosshairs-gps"
                />
              </v-card-text>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="draft.killed"
                :label="t('کشته شده')"
                density="compact"
                variant="outlined"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="draft.injured"
                :label="t('زخمی شده')"
                density="compact"
                variant="outlined"
              />
            </v-col>
            <!-- ghost to keep 3-up layout -->
            <v-col cols="12" md="4" class="ghost-col" />
            <!-- ✅ NEW: trust_percentage (read-only) -->
            <v-col cols="12" md="12">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('فیصدی موثقیت') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.trust_percentage"
                  variant="outlined"
                  rounded="lg"
                  class="cell"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-percent"
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Long texts -->
          <v-row class="tight-grid">
            <v-col cols="12" md="12">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تفصیل') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-textarea
                  v-model="draft.tafseel"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  clearable
                  rows="3"
                  auto-grow
                />
              </v-card-text>
            </v-col>

            <!-- ✅ NEW: organ -->
            <v-col cols="12" md="12">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('ارگان') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <OrganSelect v-model="draft.talks" :label="t('ارگان')" :disabled="submitting" />
              </v-card-text>
            </v-col>

          </v-row>
          <!-- Note -->
          <v-row class="tight-grid">
            <v-col cols="12">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('ملاحظات') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-textarea
                  v-model="draft.note"
                  variant="outlined"
                  rounded="lg"
                  density="compact"
                  clearable
                  rows="2"
                  auto-grow
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Documents -->
          <v-card variant="outlined" class="docs-field-row">
            <v-card-item>
              <v-card-subtitle class="field-label">{{ t('اسناد / ضمایم') }}</v-card-subtitle>
            </v-card-item>
            <v-card-text>
              <input
                ref="fileInput"
                type="file"
                class="hidden-file"
                multiple
                @change="onFilesPicked($event)"
              />

              <v-row align="center" class="tight-grid">
                <v-col cols="auto">
                  <v-btn
                    prepend-icon="mdi-tray-arrow-up"
                    color="primary"
                    @click="fileInput?.click()"
                  >
                    {{ t('آپلود فایل ها') }}
                  </v-btn>
                </v-col>
                <v-col>
                  <v-chip-group>
                    <v-chip
                      v-for="(f, i) in draft._newDocs"
                      :key="'nf-' + i"
                      size="small"
                      variant="outlined"
                      color="primary"
                      :title="f.name"
                      closable
                      @click:close="removeFile(i)"
                    >
                      {{ shortName(f.name) }}
                    </v-chip>
                  </v-chip-group>
                </v-col>
              </v-row>

              <v-row v-if="progress > 0 && progress < 100" class="mt-2 tight-grid">
                <v-col cols="12">
                  <v-progress-linear :model-value="progress" height="8" color="primary" rounded />
                  <div class="text-caption mt-1 text-medium-emphasis">
                    {{ t('در حال آپلود') }}... {{ progress }}%
                  </div>
                </v-col>
              </v-row>
              <v-row v-else-if="progress === 100" class="tight-grid">
                <v-col cols="12">
                  <v-alert type="success" density="compact" class="mt-2">
                    {{ t('آپلود با موفقیت انجام شد') }}
                  </v-alert>
                </v-col>
              </v-row>
              <v-row class="pa-3">
                <v-col cols="12" md="12">
                  <v-text-field
                    v-model="draft.docs_note"
                    :label="t('نوت راجع به اسناد')"
                    density="compact"
                    variant="outlined"
                  />
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
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
            {{ t('اظافه کردن واقعه') }}
          </v-btn>
          <v-btn variant="text" color="primary" @click="printEventForm">
            <v-icon start>mdi-printer</v-icon>
            پرنټ / PDF
          </v-btn>

          <v-btn variant="text" color="primary" @click="downloadEventFormWord">
            <v-icon start>mdi-file-word</v-icon>
            Word
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-container>
</template>

<script setup>
  import { ref, reactive, computed, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import DateInput from '../../components/DateInput.vue';
  import ZoneHawzaSelect from '@/components/ZoneHawzaSelect.vue';
  import EventTypeSpecificTypeSelect from '@/components/EventTypeSpecificTypePicker.vue';
  import OrganSelect from '@/components/OrganSelect.vue';

  const repo = useDatabaseRepository();
  const { t, locale } = useI18n();

  const valid = ref(false);
  const submitting = ref(false);
  const progress = ref(0);
  const form = ref(null);
  const fileInput = ref(null);

  /* RTL handling */
  const RTL_LOCALES = ['fa', 'fa-AF', 'prs', 'ps', 'ar'];
  const isRtl = ref(RTL_LOCALES.includes(locale.value));
  watch(locale, (v) => {
    isRtl.value = RTL_LOCALES.includes(v);
  });

  /* helpers */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  /* dropdown items (same names used in the editor) */
  const resourceCodeItems = computed(() => toArray(repo.resourcecode));
  const officeEmployeesCodeItems = computed(() => toArray(repo.officeemployeescode));
  const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
  const zoneEmployeesItems = computed(() => toArray(repo.ZoneEmployees));
  const janiTalafatItems = computed(() => toArray(repo.janiTalafatOptions));
  const maliTalafatItems = computed(() => toArray(repo.maliTalafatOptions));

  /* fixed options — mirror the editor */
  const eventTypeOptions = ['سیاسی', 'جنایی', 'الهی'];
  const eventSpecificTypeOptions = ['قتل', 'چاودنه', 'ټکر', 'سرقت', 'مسلحانه برید'];
  function printEventForm() {
    repo.DownloadEventFormPdf({
      logo_url: '/logo.png',
      form_number: '',

      source_code: draft.resource_id,
      employee_code: draft.office_employee_id,

      event_date: draft.event_date,
      event_hour: draft.event_hour,

      event_type: draft.event_type,
      event_specific_type: draft.event_specific_type,

      target_person: draft.target_person,
      criminal: draft.criminal,

      zone: draft.event_zone,
      hawza: draft.event_hawza,
      specific_place: draft.event_specific_place,

      talafat_mali: draft.talafat_mali,
      killed: draft.killed,
      injured: draft.injured,
      coordinates: draft.coordinates,

      tafseel_text: draft.tafseel,
      note_text: draft.note,
      evidence_text: draft.docs_note,
    });
  }

  function downloadEventFormWord() {
    repo.DownloadEventFormWord(
      {
        logo_url: '/logo.png',
        form_number: '',

        source_code: draft.resource_id,
        employee_code: draft.office_employee_id,

        event_date: draft.event_date,
        event_hour: draft.event_hour,

        event_type: draft.event_type,
        event_specific_type: draft.event_specific_type,

        target_person: draft.target_person,
        criminal: draft.criminal,

        zone: draft.event_zone,
        hawza: draft.event_hawza,
        specific_place: draft.event_specific_place,

        talafat_mali: draft.talafat_mali,
        killed: draft.killed,
        injured: draft.injured,
        coordinates: draft.coordinates,

        tafseel_text: draft.tafseel,
        note_text: draft.note,
        evidence_text: draft.docs_note,
      },
      'event_form.doc'
    );
  }

  /* form model (EXACT keys as the editor) */
  const baseDraft = () => ({
    resource_id: null,
    office_employee_id: null,
    office_employee_akhistonky_id: null,

    event_type: '',
    event_specific_type: '',
    target_person: '',
    criminal: '',

    event_date: '',
    event_hour: '',
    trust_percentage: '',

    event_zone: '',
    event_hawza: '',
    event_specific_place: '',
    coordinates: '',

    talafat_jani: '',
    talafat_mali: '',
    killed: '',
    injured: '',
    tafseel: '',
    talks: '',
    Province_addr: '',
    note: '',
    docs_note: '',

    _newDocs: [],
  });
  const draft = reactive(baseDraft());

  /* validation */
  const rules = {
    required: (label) => (v) => (v && String(v).trim().length > 0) || `«${label}» ضروری است`,
    selectRequired: (label) => (v) =>
      (v !== null && v !== undefined && v !== '') || `لطفاً «${label}» را انتخاب کنید`,
    dateYMD: (label) => (v) =>
      !v || /^\d{4}-\d{2}-\d{2}$/.test(v) || `${label} باید به صورت YYYY-MM-DD باشد`,
    timeHM: (label) => (v) => !v || /^\d{2}:\d{2}$/.test(v) || `${label} باید به صورت HH:MM باشد`,
  };

  /* files */
  function shortName(name = '') {
    if (name.length <= 22) return name;
    const dot = name.lastIndexOf('.');
    if (dot === -1) return name.slice(0, 18) + '…';
    const ext = name.slice(dot);
    const base = name.slice(0, 18);
    return `${base}…${ext}`;
  }
  function onFilesPicked(e) {
    draft._newDocs = Array.from(e.target?.files || []);
  }
  function removeFile(index) {
    draft._newDocs.splice(index, 1);
  }

  /* formdata */
  function appendFD(fd, k, v) {
    if (v == null) return;
    if (Array.isArray(v)) {
      v.forEach((val) => appendFD(fd, k + '[]', val));
      return;
    }
    if (typeof v === 'object') {
      fd.append(k, v.id ?? v.value ?? JSON.stringify(v));
      return;
    }
    fd.append(k, v);
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(obj)) appendFD(fd, k, v);
    return fd;
  }

  /* submit/reset (unchanged) */
  async function onCreate() {
    const ok = await form.value?.validate();
    if (!ok?.valid) {
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
    progress.value = 1;

    const fd = toFormDataSelective({ ...draft });
    (draft._newDocs || []).forEach((f) => fd.append('documents[]', f));

    try {
      const created = await repo
        .CreateEvent(fd, (pct) => (progress.value = pct || progress.value))
        .catch(() => null);
      if (created?.id) onReset();
    } finally {
      submitting.value = false;
      setTimeout(() => (progress.value = 0), 800);
    }
  }
  function onReset() {
    Object.assign(draft, baseDraft());
    form.value?.resetValidation();
    if (fileInput.value) fileInput.value.value = '';
  }

  /* preload dropdowns (kept) */
  (async () => {
    await Promise.all([
      repo.GetResourceCode?.(),
      repo.GetOfficeEmployeeCode?.(),
      repo.GetOfficeEmployee?.(),
      repo.GetZoneEmployees?.(),
      repo.GetJaniTalafatOptions?.(),
      repo.GetMaliTalafatOptions?.(),
    ]).catch(() => {});
  })();
  watch(
    () => draft.resource_id,
    (newVal) => {
      // if resource cleared -> clear auto fields
      if (!newVal) {
        draft.office_employee_id = null;
        draft.trust_percentage = '';
        return;
      }

      // find selected resource object
      const r = resourceCodeItems.value.find((x) => x?.id === newVal);
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

      // ❌ do NOT touch draft.office_employee_akhistonky_id
    }
  );
</script>

<style scoped>
  .form-card {
    border-radius: 16px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  }
  .form-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
  }

  /* Inputs expand within their columns */
  .cell {
    width: 100%;
  }

  /* Labels */
  .field-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #475569;
    opacity: 1;
  }
  .section-subtitle {
    font-size: 0.95rem;
    font-weight: 600;
    color: #334155;
    opacity: 1;
  }

  /* Docs row */
  .docs-field-row {
    display: flex;
    align-items: center;
    gap: 4px;
    border: 1px dashed #9e9e9e;
    border-radius: 8px;
    padding: 6px 8px;
  }
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }

  /* Tight gutters */
  .tight-grid {
    --v-gutter-x: 8px;
    --v-gutter-y: 8px;
  }

  /* Compact paddings */
  :deep(.v-card-item) {
    padding: 4px 0 2px 0;
  }
  :deep(.v-card-text) {
    padding: 4px 0 0 0;
  }

  /* RTL */
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

  /* Ghost column to preserve 3-up rhythm when a row has only 2 items */
  .ghost-col {
    visibility: hidden;
    min-height: 0;
    padding: 0;
  }

  @media (max-width: 960px) {
    .form-card {
      padding: 16px;
    }
    .form-title {
      font-size: 1.3rem;
    }
  }
   /* ✅ only vertical scroll */
  .max-h-form {
    max-height: calc(87.5vh - 220px);
    overflow-y: auto;
    overflow-x: hidden; /* ❌ no horizontal scrollbar */
    overscroll-behavior: contain;
    scroll-behavior: smooth;
    padding-inline-end: 6px; /* RTL/LTR safe instead of padding-right */
  }

  /* ✅ extra safety: if something is wider, clip it */
  .pretty-scroll {
    overflow-x: hidden;
  }

  /* ✅ prevent long content from forcing width */
  :deep(.v-field__input),
  :deep(.v-textarea .v-field__input),
  :deep(.v-autocomplete),
  :deep(.v-select),
  :deep(.v-text-field) {
    max-width: 100%;
  }

  /* ✅ chips can cause horizontal overflow sometimes */
  :deep(.v-chip-group) {
    max-width: 100%;
    overflow-x: hidden;
    flex-wrap: wrap;
  }

  /* ✅ Pretty scrollbar (Chrome/Edge/Safari) */
  .pretty-scroll::-webkit-scrollbar {
    width: 10px;
    height: 10px;
  }

  .pretty-scroll::-webkit-scrollbar-track {
    background: rgba(148, 163, 184, 0.18); /* soft slate */
    border-radius: 999px;
  }

  .pretty-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, rgba(59, 130, 246, 0.65), rgba(99, 102, 241, 0.65));
    border-radius: 999px;
    border: 2px solid rgba(248, 250, 252, 0.8); /* makes it look "floating" */
  }

  .pretty-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, rgba(59, 130, 246, 0.85), rgba(99, 102, 241, 0.85));
  }

  /* ✅ Firefox */
  .pretty-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(12, 111, 112, 0.54) rgba(148, 163, 184, 0.18);
  }

  /* Optional: nicer feel on touchpads */
  .pretty-scroll {
    -webkit-overflow-scrolling: touch;
  }

</style>
