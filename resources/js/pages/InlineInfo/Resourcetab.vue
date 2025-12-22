<template>
  <v-container flat fluid :dir="isRtl ? 'rtl' : 'ltr'">
    <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="onCreate">
      <v-card class="pa-4 form-card" elevation="2">
        <!-- Header -->
        <v-card-title class="form-title d-flex align-center mb-4" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-icon color="primary" class="mr-2">mdi-information-outline</v-icon>
          {{ t('افزودن اطلاعات جدید') }}
        </v-card-title>

        <!-- Codes (+ Name moved here to make 3 columns) -->
        <v-card-text class="pa-0" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-row class="tight-grid">
            <!-- resource_id -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کود منبع') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.resource_id"
                  :items="resourceItems"
                  :item-title="formatResource"
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
                  :items="officeEmployeesItems"
                  item-title="code_number"
                  item-value="id"
                  rounded="lg"
                  class="cell"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-account-badge"
                />
              </v-card-text>
            </v-col>
            <!-- name (moved up from Identity row) -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('نام') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.name"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  class="cell"
                  clearable
                  :rules="[rules.required(t('نام'))]"
                  prepend-inner-icon="mdi-account"
                />
              </v-card-text>
            </v-col>
            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{
                  t('کود کارمند تدابیری مربوطه')
                }}</v-card-subtitle>
              </v-card-item>
              <v-autocomplete
                class="primary-select"
                v-model="draft.office_employee_tadabiri_id"
                :items="officeEmployeesItems"
                item-title="code_number"
                item-value="id"
                :label="t('کود کارمند تدابیری مربوطه')"
                density="compact"
                variant="outlined"
                clearable
              /> </v-col
            ><v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{
                  t('کود کارمند اطلاع گیرنده')
                }}</v-card-subtitle>
              </v-card-item>
              <v-autocomplete
                class="primary-select"
                v-model="draft.office_employee_info_id"
                :items="officeEmployeesItems"
                item-title="code_number"
                item-value="id"
                :label="t('کود کارمند اطلاع گیرنده')"
                density="compact"
                variant="outlined"
                clearable
              />
            </v-col>
          </v-row>

          <!-- Identity (remaining 3 fields in 3-up layout) -->
          <v-row class="tight-grid">
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('نام مستعار') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.nick_name"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  class="cell"
                  clearable
                  prepend-inner-icon="mdi-tag"
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('نام پدر') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.father_name"
                  variant="outlined"
                  density="compact"
                  clearable
                  rounded="lg"
                  class="cell"
                  prepend-inner-icon="mdi-account-supervisor"
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('نام پدرکلان') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.grand_father_name"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  class="cell"
                  clearable
                  prepend-inner-icon="mdi-account-supervisor-outline"
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- Addresses -->
          <v-expansion-panels variant="accordion">
            <v-expansion-panel>
              <v-expansion-panel-title>
                <v-icon class="mr-2">mdi-map-marker</v-icon>
                {{ t('آدرس‌ها') }}
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <!-- Main Address -->
                <v-row class="tight-grid">
                  <v-col cols="12">
                    <v-card-subtitle class="section-subtitle mb-2">
                      <v-icon size="small" class="mr-1">mdi-home</v-icon>
                      {{ t('آدرس اصلی') }}
                    </v-card-subtitle>
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="draft.main_province"
                      :label="t('ولایت اصلی')"
                      variant="outlined"
                      density="compact"
                      rounded="lg"
                      clearable
                      :rules="[rules.selectRequired(t('ولایت اصلی'))]"
                    />
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="draft.main_districts"
                      :label="t('ولسوالی اصلی')"
                      variant="outlined"
                      density="compact"
                      clearable
                    />
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="draft.main_village"
                      :label="t('قریه/ناحیه اصلی')"
                      variant="outlined"
                      density="compact"
                      rounded="lg"
                      clearable
                    />
                  </v-col>
                </v-row>

                <!-- Current Address -->
                <v-row class="tight-grid">
                  <v-col cols="12">
                    <v-card-subtitle class="section-subtitle mb-2">
                      <v-icon size="small" class="mr-1">mdi-map-marker</v-icon>
                      {{ t('آدرس فعلی') }}
                    </v-card-subtitle>
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="draft.now_province"
                      :label="t('ولایت فعلی')"
                      variant="outlined"
                      density="compact"
                      clearable
                      rounded="lg"
                    />
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="draft.now_district"
                      :label="t('ولسوالی فعلی')"
                      variant="outlined"
                      density="compact"
                      clearable
                      rounded="lg"
                    />
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="draft.now_village"
                      :label="t('قریه/ناحیه فعلی')"
                      variant="outlined"
                      density="compact"
                      clearable
                      rounded="lg"
                    />
                  </v-col>
                </v-row>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>

          <!-- Types + Time -->
          <v-row class="tight-grid mt-2">
            <!-- syasi_info_type -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('سیاسی - نوع') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.syasi_info_type"
                  :items="seyasiItems"
                  item-title="name"
                  item-value="id"
                  rounded="lg"
                  class="cell"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-shield-account"
                />
              </v-card-text>
            </v-col>

            <!-- jenayee_info_type -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('جنایی - هو/نه') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.jenayee_info_type"
                  :items="jenayeeItems"
                  item-title="name"
                  item-value="id"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  class="cell"
                  clearable
                  prepend-inner-icon="mdi-shield-check"
                />
              </v-card-text>
            </v-col>

            <!-- information_saved_date -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('تاریخ اطلاع') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <div class="dateinput">
                  <DateInput
                    v-model="draft.information_saved_date"
                    :rules="[rules.required]"
                    :label="t('تاریخ اطلاع')"
                  />
                </div>
              </v-card-text>
            </v-col>

            <!-- information_saved_hour -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('ساعت اطلاع') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.information_saved_hour"
                  variant="outlined"
                  density="compact"
                  clearable
                  type="time"
                  step="60"
                  placeholder="HH:MM"
                  prepend-inner-icon="mdi-clock"
                  rounded="lg"
                  class="cell"
                />
              </v-card-text>
            </v-col>

            <!-- information_degree -->
            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('درجه موثقیت اطلاع') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.information_degree"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-star"
                  rounded="lg"
                  class="cell"
                />
              </v-card-text>
            </v-col>
            <v-col cols="12" md="4">
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
                  rounded="lg"
                  rows="3"
                  auto-grow
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="6">
              <v-card-item>
                <v-card-subtitle class="field-label">{{
                  t('گفتگو های انجام شده')
                }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-textarea
                  v-model="draft.talks"
                  variant="outlined"
                  density="compact"
                  clearable
                  rounded="lg"
                  rows="3"
                  auto-grow
                />
              </v-card-text>
            </v-col>

            <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('نظر مدیر') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-textarea
                  v-model="draft.manager_comment"
                  variant="outlined"
                  density="compact"
                  clearable
                  rounded="lg"
                  rows="2"
                  auto-grow
                />
              </v-card-text>
            </v-col>

            <!-- <v-col cols="12" md="4">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('رهنمود رهبریت') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-textarea
                  v-model="draft.lead_drirection"
                  variant="outlined"
                  density="compact"
                  clearable
                  rounded="lg"
                  rows="2"
                  auto-grow
                />
              </v-card-text>
            </v-col> -->

            <v-col cols="12" md="8">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('ملاحظات') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-textarea
                  v-model="draft.note"
                  variant="outlined"
                  density="compact"
                  clearable
                  rounded="lg"
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
                rounded="lg"
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

        <v-card-actions class="pa-0">
          <v-spacer />

          <!-- ✅ NEW: PDF + Word (beside Create button) -->
          <v-btn variant="tonal" color="primary" class="mr-2" @click="onDownloadPdf">
            <v-icon start>mdi-file-pdf-box</v-icon>
            PDF
          </v-btn>

          <v-btn variant="tonal" color="primary" class="mr-2" @click="onDownloadWord">
            <v-icon start>mdi-file-word-box</v-icon>
            Word
          </v-btn>

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
            {{ t('اضافه کردن اطلاعات') }}
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

  /* toArray helper + dropdowns */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };
  const resourceItems = computed(() => toArray(repo.resource));
  const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
  const seyasiItems = computed(() => toArray(repo.seyasiOptions));
  const jenayeeItems = computed(() => toArray(repo.jenayeeOptions));

  /* DRAFT — exactly the single-row editor fields */
  const baseDraft = () => ({
    // codes
    resource_id: null,
    office_employee_id: null,
    office_employee_tadabiri_id: null,
    office_employee_info_id: null,
    // identity
    name: '',
    nick_name: '',
    father_name: '',
    grand_father_name: '',

    // addresses
    main_province: '',
    main_districts: '',
    main_village: '',
    now_province: '',
    now_district: '',
    now_village: '',

    // types & time
    syasi_info_type: '',
    jenayee_info_type: '',
    information_saved_date: '',
    information_saved_hour: '',
    information_degree: '',

    // texts
    tafseel: '',
    manager_comment: '',
    lead_drirection: '',
    talks: '',
    note: '',
    docs_note: '',
    Province_addr: '',

    // files
    _newDocs: [],
  });
  const draft = reactive(baseDraft());

  /* Rules (unchanged) */
  const rules = {
    required: (label) => (v) => (v && String(v).trim().length > 0) || `«${label}» ضروری است`,
    selectRequired: (label) => (v) =>
      (v !== null && v !== undefined && v !== '') || `لطفاً «${label}» را انتخاب کنید`,
    dateYMD: (label) => (v) =>
      !v || /^\d{4}-\d{2}-\d{2}$/.test(v) || `${label} باید به صورت YYYY-MM-DD باشد`,
    timeHM: (label) => (v) => !v || /^\d{2}:\d{2}$/.test(v) || `${label} باید به صورت HH:MM باشد`,
  };

  /* Helpers (unchanged) */
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

  function onDownloadPdf() {
    repo.DownloadInformationPdf(draft, {
      dir: isRtl.value ? 'rtl' : 'ltr',
      // logoCenter: 'data:image/png;base64,...'
    });
  }

  function onDownloadWord() {
    repo.DownloadInformationWord(draft, {
      dir: isRtl.value ? 'rtl' : 'ltr',
    });
  }

  /* FormData (unchanged) */
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

  /* Actions (unchanged) */
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
        .CreateInformation(fd, (pct) => (progress.value = pct))
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

  /* Preload dropdowns (unchanged) */
  (async () => {
    await Promise.all([repo.GetOfficeEmployee(), repo.GetResource()]).catch(() => {});
  })();

  /* pretty resource label */
  const formatResource = (r) => {
    if (!r) return '';
    const code = r.code_number ? String(r.code_number) : '';
    const nm = r.name ? String(r.name) : '';
    const dad = r.father_name ? String(r.father_name) : '';
    const id = r.id != null ? `#${r.id}` : '';
    return [code, nm, dad, id].filter(Boolean).join(' — ');
  };
</script>

<style scoped>
  .form-card {
    border-radius: 16px;
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
  }
  .form-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
  }

  /* Let inputs expand within columns (no fixed min/max) */
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

  /* Hidden file input */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .dateinput {
    width: 100% !important;
  }
  /* Tighter grid gutters */
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

  /* RTL polishing */
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

  @media (max-width: 960px) {
    .form-card {
      padding: 16px;
    }
    .form-title {
      font-size: 1.3rem;
    }
  }
</style>
