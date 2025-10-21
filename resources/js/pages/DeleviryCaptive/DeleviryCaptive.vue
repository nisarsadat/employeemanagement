<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('زندانی های تسلیم شده')"
      :subtitle="t('اضافه کردن زندانی های تسلیم شده')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search -->
    <v-row class="d-flex">
      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetchAll"
          clearable
          @click:clear="
            () => {
              repo.Search = '';
              fetchAll();
            }
          "
        />
      </v-col>
    </v-row>

    <!-- ✅ Validated editor form -->
    <v-form
      ref="editorForm"
      v-model="editorValid"
      lazy-validation
      :key="formKey"
      @submit.prevent="onSubmit(rows[0])"
    >
      <!-- Single-row editor (create/edit) -->
      <v-card class="tablebody" ref="editorCard">
        <v-data-table
          :headers="headers"
          :items="rows"
          :dir="isRtl ? 'rtl' : 'ltr'"
          item-key="idOrTmp"
          class="elevation-2 custom-table"
          hide-default-footer
          :loading="repo.loading"
        >
          <!-- Picture with small preview + edit -->
          <template #item.picture="{ item }">
            <div class="pic-cell">
              <input
                class="hidden-file"
                type="file"
                accept="image/*"
                :id="`up-${item.idOrTmp}`"
                @change="(e) => onPickFile(item, e.target.files?.[0] ?? null)"
              />
              <v-btn
                v-if="!item.previewUrl && !item.picture"
                size="small"
                color="primary"
                variant="outlined"
                icon
                @click="triggerFile(item)"
              >
                <v-icon>mdi-plus</v-icon>
              </v-btn>
              <div v-else class="pic-thumb" :title="t('گزینه ها')">
                <v-avatar size="32">
                  <v-img :src="item.previewUrl || item.picture" :alt="t('تصویر')" cover />
                </v-avatar>
                <div class="pic-overlay">
                  <v-btn
                    size="small"
                    color="secondary"
                    variant="flat"
                    @click.stop="triggerFile(item)"
                  >
                    <v-icon start>mdi-pencil</v-icon>{{ t('ویرایش') }}
                  </v-btn>
                  <v-btn
                    size="small"
                    color="secondary"
                    variant="flat"
                    @click.stop="openPreview(item.previewUrl || item.picture)"
                  >
                    <v-icon start>mdi-eye</v-icon>{{ t('نمایش') }}
                  </v-btn>
                </div>
              </div>
            </div>
          </template>

          <!-- Introduction -->
          <template #item.intro="{ item }">
            <div class="intro-group">
              <v-text-field
                v-model="item.draft.name"
                density="compact"
                variant="outlined"
                :label="t('نام')"
                hide-details
                :rules="[rules.required]"
                :error="hasError(item, 'name')"
                :error-messages="getErrorMessages(item, 'name')"
              />
              <v-text-field
                v-model="item.draft.father_name"
                density="compact"
                variant="outlined"
                :label="t('نام پدر')"
                hide-details
                :error="hasError(item, 'father_name')"
                :error-messages="getErrorMessages(item, 'father_name')"
              />
              <v-text-field
                v-model="item.draft.grand_father_name"
                density="compact"
                variant="outlined"
                :label="t('نام پدر کلان')"
                hide-details
                :error="hasError(item, 'grand_father_name')"
                :error-messages="getErrorMessages(item, 'grand_father_name')"
              />
              <v-text-field
                v-model="item.draft.nickname"
                density="compact"
                variant="outlined"
                :label="t('نام مستعار')"
                hide-details
                :error="hasError(item, 'nickname')"
                :error-messages="getErrorMessages(item, 'nickname')"
              />
            </div>
          </template>

          <template #item.tazkira="{ item }">
            <v-text-field
              v-model="item.draft.tazkira"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نمبر تذکره')"
              hide-details
              :error="hasError(item, 'tazkira')"
              :error-messages="getErrorMessages(item, 'tazkira')"
            />
          </template>

          <template #item.age="{ item }">
            <v-text-field
              v-model="item.draft.age"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('عمر')"
              hide-details
              :rules="[rules.number]"
              :error="hasError(item, 'age')"
              :error-messages="getErrorMessages(item, 'age')"
            />
          </template>

          <template #item.halat_madani="{ item }">
            <v-text-field
              v-model="item.draft.halat_madani"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('حالت مدنی')"
              hide-details
              :error="hasError(item, 'halat_madani')"
              :error-messages="getErrorMessages(item, 'halat_madani')"
            />
          </template>

          <template #item.criminal_history="{ item }">
            <v-text-field
              v-model="item.draft.criminal_history"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('سابقه جرمی')"
              hide-details
              :error="hasError(item, 'criminal_history')"
              :error-messages="getErrorMessages(item, 'criminal_history')"
            />
          </template>

          <!-- Jobs -->
          <template #item.jobs="{ item }">
            <div class="drop-group2">
              <v-text-field
                v-model="item.draft.old_job"
                density="compact"
                variant="outlined"
                :label="t('وظیفه سابقه')"
                hide-details
                :error="hasError(item, 'old_job')"
                :error-messages="getErrorMessages(item, 'old_job')"
              />
              <v-text-field
                v-model="item.draft.new_job"
                density="compact"
                variant="outlined"
                :label="t('وظیفه جدید')"
                hide-details
                :error="hasError(item, 'new_job')"
                :error-messages="getErrorMessages(item, 'new_job')"
              />
            </div>
          </template>

          <template #item.language="{ item }">
            <v-text-field
              v-model="item.draft.language"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('زبان')"
              hide-details
              :error="hasError(item, 'language')"
              :error-messages="getErrorMessages(item, 'language')"
            />
          </template>

          <template #item.scientific_level="{ item }">
            <v-text-field
              v-model="item.draft.scientific_level"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('سطح علمی')"
              hide-details
              :error="hasError(item, 'scientific_level')"
              :error-messages="getErrorMessages(item, 'scientific_level')"
            />
          </template>

          <!-- Main address -->
          <template #item.main_add="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.main_province"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولایت اصلی')"
                :error="hasError(item, 'main_province')"
                :error-messages="getErrorMessages(item, 'main_province')"
              />
              <v-text-field
                v-model="item.draft.main_district"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولسوالی اصلی')"
                :error="hasError(item, 'main_district')"
                :error-messages="getErrorMessages(item, 'main_district')"
              />
              <v-text-field
                v-model="item.draft.main_village"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('قریه/ناحیه اصلی')"
                :error="hasError(item, 'main_village')"
                :error-messages="getErrorMessages(item, 'main_village')"
              />
            </div>
          </template>

          <!-- Now address -->
          <template #item.now_add="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.now_province"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولایت فعلی')"
                :error="hasError(item, 'now_province')"
                :error-messages="getErrorMessages(item, 'now_province')"
              />
              <v-text-field
                v-model="item.draft.now_district"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولسوالی فعلی')"
                :error="hasError(item, 'now_district')"
                :error-messages="getErrorMessages(item, 'now_district')"
              />
              <v-text-field
                v-model="item.draft.now_village"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('قریه/ناحیه فعلی')"
                :error="hasError(item, 'now_village')"
                :error-messages="getErrorMessages(item, 'now_village')"
              />
            </div>
          </template>

          <!-- Captive details -->
          <template #item.captive_place="{ item }">
            <v-text-field
              v-model="item.draft.captive_place"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('محل دستگیری')"
              hide-details
              :error="hasError(item, 'captive_place')"
              :error-messages="getErrorMessages(item, 'captive_place')"
            />
          </template>

          <!-- NEW: coordenates -->
          <template #item.coordenates="{ item }">
            <v-text-field
              v-model="item.draft.coordenates"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کوردینات محل دستگیری')"
              hide-details
              :error="hasError(item, 'coordenates')"
              :error-messages="getErrorMessages(item, 'coordenates')"
            />
          </template>

          <template #item.captive_marja="{ item }">
            <v-text-field
              v-model="item.draft.captive_marja"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مرجع دستگیری')"
              hide-details
              :error="hasError(item, 'captive_marja')"
              :error-messages="getErrorMessages(item, 'captive_marja')"
            />
          </template>

          <!-- Date / Hour -->
          <template #item.captive_date="{ item }">
            <DateInput
              v-model="item.draft.captive_date"
              class="text-group"
              :label="t('تاریخ دستگیری')"
              :rules="[rules.required]"
              :error="hasError(item, 'captive_date')"
              :error-messages="getErrorMessages(item, 'captive_date')"
            />
          </template>

          <template #item.captive_hour="{ item }">
            <v-text-field
              v-model="item.draft.captive_hour"
              type="time"
              step="60"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('ساعت دستگیری')"
              hide-details
              :error="hasError(item, 'captive_hour')"
              :error-messages="getErrorMessages(item, 'captive_hour')"
            />
          </template>

          <template #item.suspect_type="{ item }">
            <v-text-field
              v-model="item.draft.suspect_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوعیت مظنونیت')"
              hide-details
              :error="hasError(item, 'suspect_type')"
              :error-messages="getErrorMessages(item, 'suspect_type')"
            />
          </template>

          <template #item.phone_number="{ item }">
            <v-text-field
              v-model="item.draft.phone_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس')"
              hide-details
              :rules="[rules.phone]"
              :error="hasError(item, 'phone_number')"
              :error-messages="getErrorMessages(item, 'phone_number')"
            />
          </template>

          <template #item.captive_halat="{ item }">
            <v-text-field
              v-model="item.draft.captive_halat"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('حالت دستگیری')"
              hide-details
              :error="hasError(item, 'captive_halat')"
              :error-messages="getErrorMessages(item, 'captive_halat')"
            />
          </template>

          <template #item.suspect_tools="{ item }">
            <v-text-field
              v-model="item.draft.suspect_tools"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وسایل و شواهد گرفته شده')"
              hide-details
              :error="hasError(item, 'suspect_tools')"
              :error-messages="getErrorMessages(item, 'suspect_tools')"
            />
          </template>

          <!-- Documents -->
          <template #item.documents="{ item }">
            <div class="documents-inline">
              <input
                class="hidden-file"
                :id="`docs-${item.idOrTmp}`"
                type="file"
                multiple
                @change="(e) => onPickDocs(item, Array.from(e.target.files || []))"
              />

              <div class="docs-field-row">
                <v-btn
                  class="uploader-btn2"
                  size="small"
                  variant="text"
                  density="compact"
                  prepend-icon="mdi-tray-arrow-up"
                  @click="openDocsPicker(item)"
                >
                  {{ t('آپلود فایل ها') }}
                </v-btn>

                <div class="chips-row">
                  <!-- new files -->
                  <v-chip
                    v-for="(f, idx) in item.draft._newDocs || []"
                    :key="`${item.idOrTmp}-nf-${idx}`"
                    size="small"
                    variant="text"
                    class="chip-outline"
                    :title="f.name"
                  >
                    {{ shortName(f.name) }}
                  </v-chip>

                  <!-- existing files -->
                  <v-chip
                    v-for="(d, idx) in item.existingDocs || []"
                    :key="`${item.idOrTmp}-df-${idx}`"
                    size="small"
                    color="primary"
                    variant="text"
                    class="chip-existing"
                    :title="d.original_name || d.file_name"
                    @click="openDoc(d.url)"
                  >
                    {{ shortName(d.original_name || d.file_name) }}
                    <v-icon
                      size="16"
                      class="ml-1"
                      title="حذف"
                      @click.stop="markDocForRemoval(item, d)"
                      >mdi-close</v-icon
                    >
                  </v-chip>
                </div>

                <v-btn
                  class="download-all-btn"
                  size="small"
                  prepend-icon="mdi-download"
                  :disabled="!item.id"
                  color="error"
                  @click="downloadAll(item)"
                >
                  {{ t('دانلود فایل ها') }}
                </v-btn>
              </div>

              <!-- progress -->
              <div
                v-if="uploadingPct[item.idOrTmp] && uploadingPct[item.idOrTmp] < 100"
                class="mt-2"
              >
                <v-progress-linear
                  :model-value="uploadingPct[item.idOrTmp]"
                  height="6"
                  color="primary"
                  rounded
                />
                <div class="text-caption mt-1">
                  ...{{ t('در حال آپلود') }} {{ uploadingPct[item.idOrTmp] }}%
                </div>
              </div>
              <div
                v-else-if="uploadingPct[item.idOrTmp] === 100"
                class="text-caption mt-1 text-success"
              >
                100% — {{ t('آپلود شد') }}
              </div>
            </div>
          </template>

          <template #item.notes="{ item }">
            <v-text-field
              v-model="item.draft.notes"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('ملاحظات')"
              hide-details
              :error="hasError(item, 'notes')"
              :error-messages="getErrorMessages(item, 'notes')"
            />
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="flex items-center gap-1">
              <!-- submit through v-form -->
              <v-btn class="bg-primary" variant="outlined" type="submit" :loading="item.loading">
                {{ t('ثبت دیتا') }}
              </v-btn>
              <v-btn class="bg-error" variant="outlined" @click="del(item)">
                {{ t('حذف دیتا') }}
              </v-btn>
              <v-btn color="warning" variant="outlined" @click="clearEditor(item)">
                {{ t('پاک کردن ورودی‌ها') }}
              </v-btn>
            </div>
          </template>

          <template #no-data>
            <div class="py-8 text-medium-emphasis">
              {{ t('یک سطر ویرایش/ایجاد آماده است') }}
            </div>
          </template>
        </v-data-table>

        <!-- Image preview dialog -->
        <v-dialog v-model="previewOpen" max-width="520">
          <v-card>
            <v-toolbar
              density="compact"
              color="primary"
              :title="t('پیش نمایش')"
              class="text-white"
            />
            <v-card-text class="d-flex justify-center">
              <v-img
                :src="previewSrc"
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
      </v-card>
    </v-form>

    <!-- Peek drawer -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title-key="name"
        image-key="picture"
        :fields="headers"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- Filterable / printable table with Edit + Peek -->
    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    />

    <!-- Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, watch, computed, reactive, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '../../components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import DateInput from '../../components/DateInput.vue';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';

  const repo = useDatabaseRepository();

  /* i18n / RTL */
  const { t, locale } = useI18n();
  const drawer = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* Snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  function toast(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  /* v-form state */
  const editorForm = ref(null);
  const editorValid = ref(false);
  const formKey = ref(0);

  /* progress per row */
  const uploadingPct = reactive({});
  const startProgress = (key) => (uploadingPct[key] = 1);
  const setProgress = (key, pct) => (uploadingPct[key] = pct);
  const endProgress = (key) => (uploadingPct[key] = 0);

  /* Per-row backend errors */
  const itemErrors = ref({});
  function setItemErrors(item, errors) {
    itemErrors.value[item.idOrTmp] = errors;
  }
  function clearItemErrors(item) {
    if (itemErrors.value[item.idOrTmp]) delete itemErrors.value[item.idOrTmp];
  }
  function hasError(item, field) {
    return !!(itemErrors.value[item.idOrTmp] && itemErrors.value[item.idOrTmp][field]);
  }
  function getErrorMessages(item, field) {
    return itemErrors.value[item.idOrTmp]?.[field] ?? [];
  }

  /* Validation rules */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    number: (v) => !v || /^[0-9]+$/.test(v) || t('فقط اعداد مجاز است'),
    phone: (v) => !v || /^(\+?\d{7,15})$/.test(v) || t('شماره تماس نامعتبر است'),
  };

  /* unwrap for slot proxies */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* tmp id for unsaved rows */
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  /* guarantee arrays */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  /* Single-row editor data */
  const rows = ref([]);
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      grand_father_name: x.grand_father_name ?? '',
      nickname: x.nickname ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',
      now_province: x.now_province ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',

      halat_madani: x.halat_madani ?? '',
      age: x.age ?? '',
      tazkira: x.tazkira ?? '',
      criminal_history: x.criminal_history ?? '',

      old_job: x.old_job ?? '',
      new_job: x.new_job ?? '',
      language: x.language ?? '',
      scientific_level: x.scientific_level ?? '',

      captive_place: x.captive_place ?? '',
      coordenates: x.coordenates ?? '',
      captive_marja: x.captive_marja ?? '',
      captive_date: x.captive_date ?? '',
      captive_hour: x.captive_hour ?? '',
      suspect_type: x.suspect_type ?? '',
      phone_number: x.phone_number ?? '',
      captive_halat: x.captive_halat ?? '',
      suspect_tools: x.suspect_tools ?? '',

      existingDocs: Array.isArray(x.documents) ? x.documents : [],
      notes: x.notes ?? '',
    };
    return { ...base, draft: { ...base, picture: null, _newDocs: [], removed_document_ids: [] } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* Full dataset for FilterableTable */
  const rowsFT = computed(() => {
    const list = repo.CaptiveDeliveries || repo.DeleviryCaptives || [];
    return toArray(list);
  });

  /* Headers (editor) */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', width: 120, sortable: false },
    { title: t('معرفت'), key: 'intro' },
    { title: t('نمبر تذکره'), key: 'tazkira' },
    { title: t('عمر'), key: 'age' },
    { title: t('حالت مدنی'), key: 'halat_madani' },
    { title: t('سابقه جرمی'), key: 'criminal_history' },
    { title: t('وظایف'), key: 'jobs' },
    { title: t('زبان'), key: 'language' },
    { title: t('سطح علمی'), key: 'scientific_level' },
    { title: t('آدرس اصلی'), key: 'main_add' },
    { title: t('آدرس فعلی'), key: 'now_add' },
    { title: t('محل دستگیری'), key: 'captive_place' },
    { title: t('کوردینات محل دستگیری'), key: 'coordenates' },
    { title: t('مرجع دستگیری'), key: 'captive_marja' },
    { title: t('تاریخ دستگیری'), key: 'captive_date' },
    { title: t('ساعت دستگیری'), key: 'captive_hour' },
    { title: t('نوعیت مظنونیت'), key: 'suspect_type' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('حالت دستگیری'), key: 'captive_halat' },
    { title: t('وسایل و شواهد گرفته شده'), key: 'suspect_tools' },
    { title: t('شواهد دستگیری زندانی'), key: 'documents' },
    { title: t('ملاحظات'), key: 'notes' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* Headers for FilterableTable (atomic) */
  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدر کلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nickname' },

    { title: t('نمبر تذکره'), key: 'tazkira' },
    { title: t('عمر'), key: 'age' },
    { title: t('حالت مدنی'), key: 'halat_madani' },
    { title: t('سابقه جرمی'), key: 'criminal_history' },

    { title: t('وظیفه سابقه'), key: 'old_job' },
    { title: t('وظیفه جدید'), key: 'new_job' },

    { title: t('زبان'), key: 'language' },
    { title: t('سطح علمی'), key: 'scientific_level' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

    { title: t('محل دستگیری'), key: 'captive_place' },
    { title: t('کوردینات محل دستگیری'), key: 'coordenates' },
    { title: t('مرجع دستگیری'), key: 'captive_marja' },
    { title: t('تاریخ دستگیری'), key: 'captive_date' },
    { title: t('ساعت دستگیری'), key: 'captive_hour' },

    { title: t('نوعیت مظنونیت'), key: 'suspect_type' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('حالت دستگیری'), key: 'captive_halat' },
    { title: t('وسایل و شواهد گرفته شده'), key: 'suspect_tools' },

    { title: t('ملاحظات'), key: 'notes' },
  ]);

  /* Group bar for FilterableTable */
  const topGroups = computed(() => [
    { title: t('معرفت'), keys: ['name', 'father_name', 'grand_father_name', 'nickname'] },
    { title: t('اطلاعات شخصی'), keys: ['tazkira', 'age', 'halat_madani', 'criminal_history'] },
    { title: t('وظایف'), keys: ['old_job', 'new_job'] },
    { title: t('مهارت‌ها/زبان'), keys: ['language', 'scientific_level'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
    {
      title: t('جزئیات دستگیری'),
      keys: ['captive_place', 'coordenates', 'captive_marja', 'captive_date', 'captive_hour'],
    },
    {
      title: t('مظنون/تماس'),
      keys: ['suspect_type', 'phone_number', 'captive_halat', 'suspect_tools'],
    },
  ]);

  /* Image picker */
  const previewOpen = ref(false);
  const previewSrc = ref(null);
  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }
  function triggerFile(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;
    document.getElementById(`up-${item.idOrTmp}`)?.click();
  }
  function onPickFile(slotItem, file) {
    const item = getRow(slotItem);
    if (!item) return;
    item.draft.picture = file || null;
    if (file) {
      if (item._blobUrl) URL.revokeObjectURL(item._blobUrl);
      item._blobUrl = URL.createObjectURL(file);
      item.previewUrl = item._blobUrl;
    } else {
      item.previewUrl = item.picture || null;
    }
  }

  /* documents UI */
  function openDocsPicker(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;
    document.getElementById(`docs-${item.idOrTmp}`)?.click();
  }
  function onPickDocs(slotItem, files) {
    const item = getRow(slotItem);
    if (!item?.draft) return;
    item.draft._newDocs = Array.isArray(files) ? files : [];
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
  function markDocForRemoval(item, doc) {
    if (!doc?.id) return;
    if (!Array.isArray(item.draft.removed_document_ids)) item.draft.removed_document_ids = [];
    if (!item.draft.removed_document_ids.includes(doc.id)) {
      item.draft.removed_document_ids.push(doc.id);
    }
    item.existingDocs = (item.existingDocs || []).filter((d) => d.id !== doc.id); // optimistic
  }

  /* diff + payload helpers */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'name',
      'father_name',
      'grand_father_name',
      'nickname',

      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_district',
      'now_village',

      'halat_madani',
      'age',
      'tazkira',
      'criminal_history',

      'old_job',
      'new_job',
      'language',
      'scientific_level',

      'captive_place',
      'coordenates',
      'captive_marja',
      'captive_date',
      'captive_hour',
      'suspect_type',
      'phone_number',
      'captive_halat',
      'suspect_tools',

      'notes',
    ];
    for (const k of keys) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    if (d.picture && typeof d.picture === 'object') {
      changed.picture = d.picture;
      changed.old_picture = item.picture;
    }
    return changed;
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      if (Array.isArray(v)) {
        v.forEach((vv) => fd.append(`${k}[]`, vv));
      } else {
        fd.append(k, v);
      }
    });
    return fd;
  }

  /* Save / Delete / Clear / Zip */
  async function save(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;

    clearItemErrors(item);

    // Minimal client checks
    if (!item.draft?.name || !item.draft?.captive_date) {
      toast(t('لطفاً نام و تاریخ دستگیری را تکمیل کنید'));
      await nextTick();
      scrollToFirstInvalid();
      return;
    }

    item.loading = true;
    const changed = item.id ? diffDraft(item) : { ...item.draft };
    const fd = toFormDataSelective(changed);
    (item.draft._newDocs || []).forEach((file) => fd.append('documents[]', file));
    (item.draft.removed_document_ids || []).forEach((id) =>
      fd.append('removed_document_ids[]', id)
    );

    const key = item.idOrTmp ?? item.id ?? `tmp-${Date.now()}`;
    startProgress(key);

    try {
      if (!item.id) {
        const created = await repo
          .CreateCaptiveDelivery(fd, (pct) => setProgress(key, pct))
          .catch((e) => {
            if (e?.response?.data?.errors) setItemErrors(item, e.response.data.errors);
            return null;
          });
        if (created?.id) {
          await fetchAll();
          setBlankEditorRow();
          editorForm.value?.resetValidation?.();
          toast(t('ثبت انجام شد'));
        } else {
          await fetchAll();
          setBlankEditorRow();
        }
        return;
      }

      if ([...fd.keys()].length === 0) {
        toast(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }

      const updated = await repo
        .UpdateCaptiveDelivery(item.id, fd, (pct) => setProgress(key, pct))
        .catch((e) => {
          if (e?.response?.data?.errors) setItemErrors(item, e.response.data.errors);
          return null;
        });

      if (updated?.id) {
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
        toast(t('بروزرسانی شد'));
      }
    } finally {
      endProgress(key);
      item.loading = false;
    }
  }

  /* VALIDATED SUBMIT */
  async function onSubmit(item) {
    const form = editorForm.value;
    if (!form) return;
    const { valid } = await form.validate();
    if (!valid) {
      toast(t('لطفاً فیلدهای قرمز را درست کنید'));
      await nextTick();
      scrollToFirstInvalid();
      return;
    }
    await save(item);
  }

  /* SCROLL TO FIRST INVALID INPUT */
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

  async function del(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name || t('رکورد') }))) return;

    try {
      await repo.DeleteCaptiveDelivery(item.id);
      await fetchAll();
      setBlankEditorRow();
      toast(t('حذف شد'));
    } catch {
      toast(t('خطا در حذف'));
    }
  }

  async function downloadAll(slotItem) {
    const item = getRow(slotItem);
    if (!item?.id) return;
    const base = (item.name || 'documents').toLowerCase().replace(/\s+/g, '_');
    await repo.DownloadCaptiveDeliveryZip(item.id, `${base}_documents.zip`);
  }

  function clearEditor(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;
    if (item && item._blobUrl) {
      URL.revokeObjectURL(item._blobUrl);
      item._blobUrl = null;
    }
    const picEl = document.getElementById(`up-${item?.idOrTmp}`);
    if (picEl) picEl.value = '';
    setBlankEditorRow();
    toast(t('ورودی‌ها پاک شد'));
  }

  /* Fetch ALL (for FilterableTable) */
  async function fetchAll() {
    try {
      if (typeof repo.FetchCaptiveDeliveriesData === 'function') {
        await repo.FetchCaptiveDeliveriesData({
          page: 1,
          itemsPerPage: 100000,
          search: repo.Search,
        });
      }
    } catch {}
  }

  /* Edit from FilterableTable */
  const editorCard = ref(null);
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      if (typeof repo.FetchCaptiveDeliveryData === 'function') {
        await repo.FetchCaptiveDeliveryData(id);
        const single =
          (repo.CaptiveDelivery && Array.isArray(repo.CaptiveDelivery)
            ? repo.CaptiveDelivery[0]
            : repo.CaptiveDelivery) || row;
        rows.value = [toRowModel(single)];
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        const single = list.find((r) => r.id === id) || row;
        rows.value = [toRowModel(single)];
      }
      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch {
      toast(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* ===== Peek state & methods ===== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      if (typeof repo.FetchCaptiveDeliveryData === 'function') {
        await repo.FetchCaptiveDeliveryData(id);
        detail.value =
          (repo.CaptiveDelivery && Array.isArray(repo.CaptiveDelivery)
            ? repo.CaptiveDelivery[0]
            : repo.CaptiveDelivery) || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } finally {
      loading.value = false;
    }
  }
  function openPeek(payload) {
    const row = payload?.raw ?? payload;
    if (!row?.id) return;
    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }

  /* Init */
  onMounted(async () => {
    await fetchAll();
    setBlankEditorRow();
  });
</script>

<style scoped>
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }
  .custom-table :deep(td) {
    vertical-align: middle;
    padding-top: 8px;
    padding-bottom: 8px;
  }

  /* compact inputs */
  .text-group {
    display: grid;
    width: 200px;
    gap: 2px;
  }
  .text-group-note {
    display: grid;
    width: 400px;
    gap: 2px;
  }
  .addr-group {
    display: grid;
    grid-template-columns: repeat(3, minmax(200px, 1fr));
    gap: 10px !important;
  }
  .intro-group {
    display: grid;
    grid-template-columns: repeat(4, minmax(200px, 1fr));
    gap: 10px;
  }
  .drop-group2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(200px, 1fr));
    gap: 10px;
  }

  .flex {
    display: flex;
  }
  .items-center {
    align-items: center;
  }
  .gap-1 {
    gap: 4px;
  }

  .pic-cell {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-right: 12px;
    margin-left: 12px;
  }
  .pic-thumb {
    position: relative;
    display: inline-flex;
    cursor: pointer;
  }
  .pic-overlay {
    position: absolute;
    inset: -6px -6px auto auto;
    display: none;
    gap: 6px;
    background: rgba(0, 0, 0, 0.55);
    padding: 6px;
    border-radius: 8px;
    transform: translateY(-40%);
    white-space: nowrap;
  }
  .pic-thumb:hover .pic-overlay {
    display: inline-flex;
  }
  .pic-overlay :deep(.v-btn) {
    color: #fff;
  }

  /* labels/fields */
  :deep(.v-field-label),
  :deep(.v-label),
  :deep(input::placeholder) {
    font-size: 12px !important;
    line-height: 1.2 !important;
    color: #555 !important;
  }
  :deep(.v-field__input) {
    min-height: 26px !important;
    padding: 2px 6px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
  }

  /* Documents layout */
  .documents-inline {
    min-width: 520px;
  }
  .docs-field-row {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px dashed #9e9e9e;
    border-radius: 8px;
    padding: 6px 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  }
  .uploader-btn2 {
    white-space: nowrap;
    text-transform: none;
    padding: 0 10px;
    box-shadow: 0 1px 2px rgba(141, 2, 2, 0.14), 0 2px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(252, 250, 250, 0.06);
    border-radius: 8px;
  }
  .uploader-btn2:hover {
    box-shadow: 0 0 0 3px rgba(1, 63, 125, 0.25) !important;
  }
  .uploader-btn2:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(1, 63, 125, 0.25);
  }

  .chip-existing {
    background: #c8dae1;
    color: black !important;
  }
  .chips-row {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 2px 0;
    scrollbar-width: thin;
  }
  .chip-outline {
    background: #c8dae1;
  }
  .download-all-btn {
    white-space: nowrap;
  }

  /* Hidden input */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }

  /* tighter table cell padding */
  .custom-table :deep(td) {
    padding-left: 8px !important;
    padding-right: 1px !important;
  }
  :deep(.v-text-field),
  :deep(.v-autocomplete) {
    margin-right: -10px !important;
    margin-left: 2px;
  }
</style>
