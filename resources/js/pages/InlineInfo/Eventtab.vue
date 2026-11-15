<template>
  <v-container flat fluid :dir="isRtl ? 'rtl' : 'ltr'">
    <v-dialog v-model="reviewOpen" max-width="900" scrollable>
      <v-card rounded="xl" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-card-title class="d-flex align-center justify-space-between">
          <div class="d-flex align-center" style="gap: 10px">
            <v-icon>mdi-clipboard-text-outline</v-icon>
            <span>{{ t('بازبینی معلومات قبل از ثبت') }}</span>
          </div>
          <v-btn icon variant="text" @click="reviewOpen = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider />

        <v-card-text class="pretty-scroll" style="max-height: 70vh">
          <v-table density="compact">
            <tbody>
              <tr v-for="row in reviewRows" :key="row.key">
                <td class="font-weight-bold" style="white-space: nowrap">
                  {{ row.label }}
                </td>
                <td>{{ row.value || '—' }}</td>
              </tr>

              <tr>
                <td class="font-weight-bold">{{ t('اسناد / ضمایم') }}</td>
                <td>
                  <div v-if="(draft._newDocs || []).length">
                    <v-chip
                      v-for="f in draft._newDocs"
                      :key="fileKey(f)"
                      size="small"
                      class="ma-1"
                      variant="outlined"
                    >
                      {{ f.name }}
                    </v-chip>
                  </div>
                  <span v-else>—</span>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-btn variant="text" @click="reviewOpen = false" :disabled="submitting">
            {{ t('برگشت') }}
          </v-btn>
          <v-spacer />
          <v-btn
            color="primary"
            :loading="submitting"
            :disabled="submitting"
            @click="openSubmitConfirm"
          >
            <v-icon start>mdi-check</v-icon>
            {{ t('تایید و ثبت') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- ✅ SMALL CONFIRM: Submit -->
    <v-dialog v-model="confirmSubmitOpen" max-width="420">
      <v-card rounded="xl" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-card-title class="d-flex align-center" style="gap: 10px">
          <v-icon color="primary">mdi-check-decagram</v-icon>
          <span>{{ t('تایید نهایی') }}</span>
        </v-card-title>

        <v-card-text class="pt-2">
          {{ t('آیا مطمئن هستید که می‌خواهید این معلومات ثبت شود؟') }}
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-btn variant="text" @click="confirmSubmitOpen = false" :disabled="submitting">
            {{ t('لغو') }}
          </v-btn>
          <v-spacer />
          <v-btn
            color="primary"
            :loading="submitting"
            :disabled="submitting"
            @click="doConfirmedSubmit"
          >
            <v-icon start>mdi-check</v-icon>
            {{ t('بلی، ثبت شود') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- ✅ SMALL CONFIRM: Reset -->
    <v-dialog v-model="confirmResetOpen" max-width="420">
      <v-card rounded="xl" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-card-title class="d-flex align-center" style="gap: 10px">
          <v-icon color="error">mdi-alert</v-icon>
          <span>{{ t('هشدار') }}</span>
        </v-card-title>

        <v-card-text class="pt-2">
          {{ t('با پاک کردن فرم، تمام معلومات وارد شده حذف می‌شود. ادامه می‌دهید؟') }}
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-btn variant="text" @click="confirmResetOpen = false" :disabled="submitting">
            {{ t('لغو') }}
          </v-btn>
          <v-spacer />
          <v-btn color="error" :disabled="submitting" @click="doConfirmedReset">
            <v-icon start>mdi-delete</v-icon>
            {{ t('بلی، پاک شود') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-form ref="form" v-model="valid" lazy-validation @submit.prevent @keydown.enter.prevent>
      <v-card class="pa-4 form-card" elevation="2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- Header -->
        <v-card-title class="form-title d-flex align-center mb-4">
          <v-icon color="primary" class="mr-2">mdi-calendar-text</v-icon>
          {{ t('افزودن واقعه جدید') }}
        </v-card-title>

        <v-card-text class="pa-0 pretty-scroll scroll-body max-h-form" :dir="isRtl ? 'rtl' : 'ltr'">
          <!-- ✅ SHEET: کود ها / نوعیت -->
          <v-card variant="outlined" class="sheet-card px-4 py-3">
            <v-card-item class="sheet-title-row">
              <v-icon class="mr-2" size="small">mdi-database</v-icon>
              <v-card-subtitle class="sheet-title">{{ t('کود ها / نوعیت') }}</v-card-subtitle>
            </v-card-item>

            <!-- Codes -->
            <v-row class="tight-grid">
              <!-- resource_id -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('کود منبع') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <ResourceRemoteAutocomplete
                    v-model="draft.resource_id"
                    :label="t('کود منبع')"
                    density="compact"
                    variant="outlined"
                    rounded="lg"
                    class="cell"
                    :rules="[rules.selectRequired(t('کود منبع'))]"
                    :item-title="resourceTitle"
                    @selected="onResourceSelected"
                    @cleared="onResourceCleared"
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
                </v-card-text>
              </v-col>
              <!-- trust_percentage -->
              <v-col cols="12" md="4">
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
                    :rules="[rules.required(t('تاریخ واقعه'))]"
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
            </v-row>
          </v-card>

          <!-- ✅ SHEET: محل واقعه -->
          <v-card variant="outlined" class="sheet-card px-4 py-3 mt-3">
            <v-card-item class="sheet-title-row">
              <v-icon class="mr-2" size="small">mdi-map-marker</v-icon>
              <v-card-subtitle class="sheet-title">{{ t('محل واقعه') }}</v-card-subtitle>
            </v-card-item>

            <!-- Place -->
            <v-row class="tight-grid">
              <!-- ✅ Zone + Hawza (component) -->
              <v-col cols="12" md="8">
                <ZoneHawzaSelect
                  v-model:zone="draft.event_zone"
                  v-model:hawza="draft.event_hawza"
                  :zoneLabel="t('آمریت')"
                  :hawzaLabel="t('حوزه')"
                  :disabled="submitting"
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
          </v-card>

          <!-- ✅ SHEET: تلفات / کوردینات / موثقیت -->
          <v-card variant="outlined" class="sheet-card px-4 py-3 mt-3">
            <v-card-item class="sheet-title-row">
              <v-icon class="mr-2" size="small">mdi-crosshairs-gps</v-icon>
              <v-card-subtitle class="sheet-title">{{ t('جزئیات واقعه') }}</v-card-subtitle>
            </v-card-item>

            <!-- Casualties -->
            <v-row class="tight-grid">
              <!-- talafat_mali -->
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="draft.talafat_mali"
                  item-title="name"
                  item-value="id"
                  :label="t('تلفات مالی')"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  clearable
                  prepend-inner-icon="mdi-cash-multiple"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="draft.killed"
                  :label="t('کشته شده')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="draft.injured"
                  :label="t('زخمی شده')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>
              <!-- coordinates -->
              <v-col cols="12" md="12">
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
              </v-col>
              <!-- ghost to keep 3-up layout -->
              <v-col cols="12" md="4" class="ghost-col" />
            </v-row>
          </v-card>

          <!-- ✅ SHEET: متن ها / ارگان -->
          <v-card variant="outlined" class="sheet-card px-4 py-3 mt-3">
            <v-card-item class="sheet-title-row">
              <v-icon class="mr-2" size="small">mdi-text-long</v-icon>
              <v-card-subtitle class="sheet-title">{{ t(' اسناد او شواهد') }}</v-card-subtitle>
            </v-card-item>
            <!-- Documents -->
            <v-card variant="outlined" class="docs-field-row">
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
                        v-for="f in draft._newDocs"
                        :key="fileKey(f)"
                        size="small"
                        variant="outlined"
                        color="primary"
                        :title="f.name"
                        closable
                        @click:close="removeFile(f)"
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

              <!-- organ (kept exactly as you wrote: v-model="draft.talks") -->
              <v-col cols="12" md="12">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('ترسره شوی همغږی') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <OrganSelect v-model="draft.talks" :label="t('ترسره شوی همغږی')" :disabled="submitting" />
                </v-card-text>
              </v-col>
            </v-row>
          </v-card>

          <!-- ✅ SHEET: ملاحظات / واقعه گیرنده -->
          <v-card variant="outlined" class="sheet-card px-4 py-3 mt-3">
            <v-card-item class="sheet-title-row">
              <v-icon class="mr-2" size="small">mdi-note-text</v-icon>
              <v-card-subtitle class="sheet-title">{{ t('ملاحظات') }}</v-card-subtitle>
            </v-card-item>

            <!-- Note -->
            <v-row class="tight-grid">
              <!-- office_employee_akhistonky_id -->
              <v-col cols="12" md="12">
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
          </v-card>
        </v-card-text>

        <v-divider class="my-3" />

        <!-- Actions -->
        <v-card-actions class="pa-0">
          <v-spacer />
          <v-btn
            variant="text"
            color="grey-darken-1"
            @click="openResetConfirm"
            :disabled="submitting"
          >
            <v-icon start>mdi-delete</v-icon>
            {{ t('پاک کردن فرم') }}
          </v-btn>
          <v-btn
            color="primary"
            :loading="submitting"
            :disabled="submitting"
            size="large"
            @click="openReview"
          >
            <v-icon v-if="auth.can('inlineinfo.create')" start>mdi-content-save</v-icon>
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
  import { useLoginRepository } from '../../stores/LoginRepository';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import { useCalendar } from '../../../composables/useCalendar';

  const auth = useLoginRepository();
  const repo = useDatabaseRepository();
  const { t, locale } = useI18n();
  const { formatDisplay } = useCalendar();
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
  const officeEmployeesCodeItems = computed(() => toArray(repo.officeemployeescode));
  const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
  const zoneEmployeesItems = computed(() => toArray(repo.ZoneEmployees));
  const janiTalafatItems = computed(() => toArray(repo.janiTalafatOptions));
  const maliTalafatItems = computed(() => toArray(repo.maliTalafatOptions));
  const resourceCodeItems = computed(() => toArray(repo.resourcescode));

  /* fixed options — mirror the editor */
  const eventTypeOptions = ['سیاسی', 'جنایی', 'الهی'];
  const eventSpecificTypeOptions = ['قتل', 'چاودنه', 'ټکر', 'سرقت', 'مسلحانه برید'];
  function codeAndName(record, fallback = '') {
    const code = String(record?.code_number ?? '').trim();
    const name = String(record?.name ?? '').trim();
    const text = [code, name].filter(Boolean).join(' - ');
    return text || (fallback == null ? '' : String(fallback));
  }

  function eventPrintPayload() {
    const resource =
      pickedResource.value?.id === draft.resource_id
        ? pickedResource.value
        : resourceCodeItems.value.find((item) => item?.id === draft.resource_id);
    const employee = officeEmployeesCodeItems.value.find(
      (item) => item?.id === draft.office_employee_id
    );

    return {
      logo_url: window.systemLogo || '/logo.jpeg',
      id: draft.id || '',
      source_code: codeAndName(resource, ''),
      employee_code: codeAndName(employee, ''),
      event_date: draft.event_date,
      event_hour: draft.event_hour,
      event_type: draft.event_type,
      event_specific_type: draft.event_specific_type,
      target_person: draft.target_person,
      criminal: draft.criminal,
      hamghagi: draft.talks,
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
    };
  }

  function printEventForm() {
    repo.DownloadEventFormPdf(eventPrintPayload());
  }

  function downloadEventFormWord() {
    repo.DownloadEventFormWord(eventPrintPayload());
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
    event_hour: new Date().toTimeString().slice(0, 5),
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
  const pickedResource = ref(null);

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
  function fileKey(f) {
    return `${f.name}__${f.size}__${f.lastModified}`;
  }

  function onFilesPicked(e) {
    const picked = Array.from(e.target?.files || []);
    draft._newDocs = picked;

    if (e?.target) e.target.value = '';
  }

  function removeFile(file) {
    const key = fileKey(file);
    draft._newDocs = draft._newDocs.filter((x) => fileKey(x) !== key);

    if (fileInput.value) fileInput.value.value = '';
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
    pickedResource.value = null;
    Object.assign(draft, baseDraft());
    form.value?.resetValidation();
    if (fileInput.value) fileInput.value.value = '';
  }
  const reviewOpen = ref(false);

  function displayValue(v) {
    if (v == null) return '';
    if (typeof v === 'string') return v.trim();
    return String(v);
  }
  function formatReviewDate(v) {
    if (!v) return '';
    return formatDisplay(String(v).trim(), locale.value) || String(v);
  }
  function formatReviewDateTime(v) {
    if (!v) return '';

    const d = new Date(v);
    if (Number.isNaN(d.getTime())) return String(v);

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hour = String(d.getHours()).padStart(2, '0');
    const minute = String(d.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day} ${hour}:${minute}`;
  }
  const reviewRows = computed(() => {
    const r = resourceCodeItems.value.find((x) => x?.id === draft.resource_id);
    const emp = officeEmployeesCodeItems.value.find((x) => x?.id === draft.office_employee_id);
    const receiver = officeEmployeesCodeItems.value.find(
      (x) => x?.id === draft.office_employee_akhistonky_id
    );

    return [
      { key: 'resource_id', label: t('کود منبع'), value: r?.code_number || draft.resource_id },
      {
        key: 'office_employee_id',
        label: t('کود کارمند'),
        value: emp?.code_number || draft.office_employee_id,
      },
      {
        key: 'trust_percentage',
        label: t('فیصدی موثقیت'),
        value: displayValue(draft.trust_percentage),
      },

      { key: 'event_type', label: t('نوعیت واقعه'), value: displayValue(draft.event_type) },
      {
        key: 'event_specific_type',
        label: t('نوعیت مشخص واقعه'),
        value: displayValue(draft.event_specific_type),
      },

      {
        key: 'target_person',
        label: t('هدف کی بود/مکان بود'),
        value: displayValue(draft.target_person),
      },
      { key: 'criminal', label: t('کی جرم را انجام داده'), value: displayValue(draft.criminal) },

      { key: 'event_date', label: t('تاریخ واقعه'), value: formatReviewDate(draft.event_date) },
      { key: 'event_hour', label: t('ساعت واقعه'), value: displayValue(draft.event_hour) },

      { key: 'event_zone', label: t('آمریت'), value: displayValue(draft.event_zone) },
      { key: 'event_hawza', label: t('حوزه'), value: displayValue(draft.event_hawza) },
      {
        key: 'event_specific_place',
        label: t('محل مشخص'),
        value: displayValue(draft.event_specific_place),
      },

      { key: 'talafat_mali', label: t('تلفات مالی'), value: displayValue(draft.talafat_mali) },
      { key: 'killed', label: t('کشته شده'), value: displayValue(draft.killed) },
      { key: 'injured', label: t('زخمی شده'), value: displayValue(draft.injured) },

      { key: 'coordinates', label: t('کوردینات'), value: displayValue(draft.coordinates) },

      { key: 'docs_note', label: t('نوت راجع به اسناد'), value: displayValue(draft.docs_note) },
      { key: 'tafseel', label: t('تفصیل'), value: displayValue(draft.tafseel) },

      { key: 'talks', label: t('ترسره شوی همغږی'), value: displayValue(draft.talks) },

      {
        key: 'office_employee_akhistonky_id',
        label: t('کود کارمند واقعه گیرنده'),
        value: receiver?.code_number || draft.office_employee_akhistonky_id,
      },
      { key: 'note', label: t('ملاحظات'), value: displayValue(draft.note) },
    ];
  });

  async function openReview() {
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
    reviewOpen.value = true;
  }

  async function confirmSubmit() {
    reviewOpen.value = false;
    await onCreate();
  }
  const confirmSubmitOpen = ref(false);
  const confirmResetOpen = ref(false);

  function openSubmitConfirm() {
    reviewOpen.value = false;
    confirmSubmitOpen.value = true;
  }

  async function doConfirmedSubmit() {
    confirmSubmitOpen.value = false;
    await onCreate();
  }

  function openResetConfirm() {
    confirmResetOpen.value = true;
  }

  function doConfirmedReset() {
    confirmResetOpen.value = false;
    onReset();
  }
  function resourceTitle(r) {
    if (!r) return '';
    const code = r.code_number ?? '';
    const name = r.name ?? '';
    return [code, name].filter(Boolean).join(' - ');
  }

  function onResourceSelected(r) {
    if (!r) return;

    pickedResource.value = r;
    draft.resource_id = r.id ?? null;

    draft.trust_percentage =
      r.trust_percentage ?? r.trustPercentage ?? r.trust_percent ?? r.trustPercent ?? '';

    const empId =
      r.office_employee?.id ??
      r.officeEmployee?.id ??
      r.office_employee_id ??
      r.employee_id ??
      null;

    if (empId) {
      draft.office_employee_id = empId;
    } else {
      draft.office_employee_id = null;
    }
  }

  function onResourceCleared() {
    pickedResource.value = null;
    draft.resource_id = null;
    draft.office_employee_id = null;
    draft.trust_percentage = '';
  }

  /* preload dropdowns (kept) */
  (async () => {
    await Promise.all([
      repo.GetResourcesCode?.(),
      repo.GetOfficeEmployeeCode?.(),
      repo.GetOfficeEmployee?.(),
      repo.GetZoneEmployees?.(),
      repo.GetJaniTalafatOptions?.(),
      repo.GetMaliTalafatOptions?.(),
    ]).catch(() => {});
  })();

  usePageRefresh('/app/inlineinfo', async () => {
    onReset();

    await Promise.all([
      repo.GetResourcesCode?.(),
      repo.GetOfficeEmployeeCode?.(),
      repo.GetOfficeEmployee?.(),
      repo.GetZoneEmployees?.(),
      repo.GetJaniTalafatOptions?.(),
      repo.GetMaliTalafatOptions?.(),
    ]).catch(() => {});
  });
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

  .cell {
    width: 100%;
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

  .sheet-card {
    border-radius: 14px;
  }
  .sheet-title-row {
    padding: 2px 0 6px 0;
  }
  .sheet-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    opacity: 1;
  }

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

  .max-h-form {
    max-height: calc(87.5vh - 220px);
    overflow-y: auto;
    overflow-x: hidden;
    overscroll-behavior: contain;
    scroll-behavior: smooth;
    padding-inline-end: 6px;
  }

  .pretty-scroll {
    overflow-x: hidden;
  }

  :deep(.v-field__input),
  :deep(.v-textarea .v-field__input),
  :deep(.v-autocomplete),
  :deep(.v-select),
  :deep(.v-text-field) {
    max-width: 100%;
  }

  :deep(.v-chip-group) {
    max-width: 100%;
    overflow-x: hidden;
    flex-wrap: wrap;
  }

  .pretty-scroll::-webkit-scrollbar {
    width: 18px;
    height: 18px;
  }

  .pretty-scroll::-webkit-scrollbar-track {
    background: rgba(148, 163, 184, 0.18);
    border-radius: 999px;
  }

  .pretty-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, rgba(59, 130, 246, 0.65), rgba(99, 102, 241, 0.65));
    border-radius: 999px;
    border: 2px solid rgba(248, 250, 252, 0.8);
  }

  .pretty-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, rgba(59, 130, 246, 0.85), rgba(99, 102, 241, 0.85));
  }

  .pretty-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(12, 111, 112, 0.54) rgba(148, 163, 184, 0.18);
  }

  .pretty-scroll {
    -webkit-overflow-scrolling: touch;
  }
</style>
