<template>
  <v-container flat fluid :dir="isRtl ? 'rtl' : 'ltr'">
    <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="onCreate">
      <v-card class="pa-4 form-card" elevation="2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- Header -->
        <v-card-title class="form-title d-flex align-center mb-4">
          <v-icon color="primary" class="mr-2">mdi-calendar-text</v-icon>
          {{ t('افزودن واقعه جدید') }}
        </v-card-title>

        <v-card-text class="pa-0">
          <!-- Codes -->
          <v-row class="tight-grid">
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
                  density="compact"
                  clearable
                  :rules="[rules.selectRequired(t('کود منبع'))]"
                  prepend-inner-icon="mdi-database"
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کود کارمند دفتر') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.office_employee_id"
                  :items="officeEmployeesCodeItems"
                  item-title="code_number"
                  item-value="id"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-badge-account"
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('نوعیت واقعه') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.event_type"
                  :items="eventTypeItems"
                  item-title="name"
                  item-value="id"
                  variant="outlined"
                  density="compact"
                  clearable
                  :rules="[rules.selectRequired(t('نوعیت واقعه'))]"
                  prepend-inner-icon="mdi-shape-outline"
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Free text descriptors -->
          <v-row class="tight-grid">
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('نوعیت مشخص واقعه') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.event_specific_type"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-format-list-bulleted-type"
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('هدف کی بود/مکان بود') }}</v-card-subtitle>
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

            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کی جرم را انجام داده') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.who_did"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-account-alert"
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Date & Hour -->
          <v-row class="tight-grid">
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تاریخ واقعه') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.event_date"
                  variant="outlined"
                  density="compact"
                  clearable
                  placeholder="YYYY-MM-DD"
                  :rules="[rules.required(t('تاریخ واقعه')), rules.dateYMD(t('تاریخ واقعه'))]"
                  prepend-inner-icon="mdi-calendar"
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('ساعت واقعه') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.event_hour"
                  variant="outlined"
                  density="compact"
                  clearable
                  placeholder="HH:MM"
                  :rules="[rules.timeHM(t('ساعت واقعه'))]"
                  prepend-inner-icon="mdi-clock-outline"
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Place -->
          <v-row class="tight-grid">
            <v-col cols="12">
              <v-card-subtitle class="section-subtitle mb-2">
                <v-icon size="small" class="mr-1">mdi-map-marker</v-icon>
                {{ t('محل واقعه') }}
              </v-card-subtitle>
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="draft.zone"
                :label="t('زون')"
                variant="outlined"
                density="compact"
                clearable
                prepend-inner-icon="mdi-map"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="draft.hawza"
                :label="t('حوزه')"
                variant="outlined"
                density="compact"
                clearable
                prepend-inner-icon="mdi-map-legend"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="draft.specific_place"
                :label="t('محل مشخص')"
                variant="outlined"
                density="compact"
                clearable
                prepend-inner-icon="mdi-map-marker-radius"
              />
            </v-col>
          </v-row>

          <!-- Casualties -->
          <v-row class="tight-grid">
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تلفات جانی') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.jani_talafat"
                  :items="janiTalafatItems"
                  item-title="name"
                  item-value="id"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-hospital"
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تلفات مالی') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.mali_talafat"
                  :items="maliTalafatItems"
                  item-title="name"
                  item-value="id"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-cash-multiple"
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Event employee codes -->
          <v-row class="tight-grid">
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کود کارمند واقعه گیرنده') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.event_office_employee_code"
                  :items="officeEmployeesItems"
                  item-title="code_number"
                  item-value="id"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-badge-account"
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کود رزیدانت واقعه گیرنده') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.event_zone_employee_code"
                  :items="zoneEmployeesItems"
                  item-title="code_number"
                  item-value="id"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-account-tie"
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Long texts -->
          <v-row class="tight-grid">
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تفصیل') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-textarea
                  v-model="draft.tafseel"
                  variant="outlined"
                  density="compact"
                  clearable
                  rows="3"
                  auto-grow
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('وضاحت') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-textarea
                  v-model="draft.talks"
                  variant="outlined"
                  density="compact"
                  clearable
                  rows="3"
                  auto-grow
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Notes -->
          <v-row class="tight-grid">
            <v-col cols="12">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('ملاحظات') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-textarea
                  v-model="draft.notes"
                  variant="outlined"
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
        </v-card-actions>
      </v-card>
    </v-form>
  </v-container>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDatabaseRepository } from '../../stores/DatabaseRepository';

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

/* dropdown items */
const resourceCodeItems = computed(() => toArray(repo.resourcecode));
const officeEmployeesCodeItems = computed(() => toArray(repo.officeemployeescode));
const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
const zoneEmployeesItems = computed(() => toArray(repo.ZoneEmployees));
const eventTypeItems = computed(() => toArray(repo.eventTypeOptions));
const janiTalafatItems = computed(() => toArray(repo.janiTalafatOptions));
const maliTalafatItems = computed(() => toArray(repo.maliTalafatOptions));

/* form model */
const baseDraft = () => ({
  resource_id: null,
  office_employee_id: null,
  event_type: null,
  event_specific_type: '',
  target_person: '',
  who_did: '',
  event_date: '',
  event_hour: '',
  zone: '',
  hawza: '',
  specific_place: '',
  jani_talafat: null,
  mali_talafat: null,
  event_office_employee_code: null,
  event_zone_employee_code: null,
  tafseel: '',
  talks: '',
  notes: '',
  _newDocs: [],
});
const draft = reactive(baseDraft());

/* validation rules (same style as info form) */
const rules = {
  required: (label) => (v) => (v && String(v).trim().length > 0) || `«${label}» ضروری است`,
  selectRequired: (label) => (v) =>
    (v !== null && v !== undefined && v !== '') || `لطفاً «${label}» را انتخاب کنید`,
  dateYMD: (label) => (v) =>
    !v || /^\d{4}-\d{2}-\d{2}$/.test(v) || `${label} باید به صورت YYYY-MM-DD باشد`,
  timeHM: (label) => (v) => !v || /^\d{2}:\d{2}$/.test(v) || `${label} باید به صورت HH:MM باشد`,
};

/* file helpers */
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

/* actions */
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

/* preload dropdowns */
(async () => {
  await Promise.all([
    repo.GetResourceCode(),
    repo.GetOfficeEmployeeCode(),
    repo.GetOfficeEmployee(),
    repo.GetZoneEmployees(),
    repo.GetEventTypeOptions?.(),
    repo.GetJaniTalafatOptions?.(),
    repo.GetMaliTalafatOptions?.(),
  ]).catch(() => {});
})();
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

/* docs uploader */
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

/* tighter gutters */
.tight-grid {
  --v-gutter-x: 8px;
  --v-gutter-y: 8px;
}

/* trim inner paddings */
:deep(.v-card-item) {
  padding: 4px 0 2px 0;
}
:deep(.v-card-text) {
  padding: 4px 0 0 0;
}

/* RTL support */
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

/* responsive */
@media (max-width: 960px) {
  .form-card { padding: 16px; }
  .form-title { font-size: 1.3rem; }
}
</style>
