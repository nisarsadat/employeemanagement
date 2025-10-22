<template>
  <v-container fluid class="py-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('واقعات')"
      :subtitle="t('اضافه کردن واقعات')"
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

    <!-- Form (logic unchanged) -->
    <v-form
      ref="editorForm"
      v-model="editorValid"
      lazy-validation
      :key="formKey"
      @submit.prevent="onSubmit(rows[0])"
    >
      <v-card class="tablebody" ref="editorCard">
        <v-data-table
          :headers="headers"
          :items="rows"
          item-key="idOrTmp"
          :dir="isRtl ? 'rtl' : 'ltr'"
          class="elevation-2 custom-table uniform-input"
          density="comfortable"
          hide-default-footer
          :loading="repo.loading"
        >
          <!-- resource_id -->
          <template #item.resource_id="{ item }">
            <v-autocomplete
              v-model="item.draft.resource_id"
              :items="resourceCodeItems"
              item-title="code_number"
              item-value="id"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کود منبع')"
              hide-details
              clearable
              :rules="[rules.selectRequired(t('کود منبع'))]"
              :error="hasError(item, 'resource_id')"
              :error-messages="getErrorMessages(item, 'resource_id')"
            />
          </template>

          <!-- office_employee_id -->
          <template #item.office_employee_id="{ item }">
            <v-autocomplete
              v-model="item.draft.office_employee_id"
              :items="officeEmployeesCodeItems"
              item-title="code_number"
              item-value="id"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کود کارمند دفتر')"
              hide-details
              clearable
              :error="hasError(item, 'office_employee_id')"
              :error-messages="getErrorMessages(item, 'office_employee_id')"
            />
          </template>

          <!-- event_type -->
          <template #item.event_type="{ item }">
            <v-autocomplete
              v-model="item.draft.event_type"
              :items="eventTypeOptions"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوعیت واقعه')"
              hide-details
              clearable
              :rules="[rules.required]"
              :error="hasError(item, 'event_type')"
              :error-messages="getErrorMessages(item, 'event_type')"
            />
          </template>

          <!-- event_specific_type -->
          <template #item.event_specific_type="{ item }">
            <v-autocomplete
              v-model="item.draft.event_specific_type"
              :items="eventSpecificTypeOptions"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوعیت مشخص واقعه')"
              hide-details
              clearable
              :error="hasError(item, 'event_specific_type')"
              :error-messages="getErrorMessages(item, 'event_specific_type')"
            />
          </template>

          <!-- target_person -->
          <template #item.target_person="{ item }">
            <v-text-field
              v-model="item.draft.target_person"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('هدف کی بود/مکان بود')"
              hide-details
              clearable
              :error="hasError(item, 'target_person')"
              :error-messages="getErrorMessages(item, 'target_person')"
            />
          </template>

          <!-- criminal -->
          <template #item.criminal="{ item }">
            <v-text-field
              v-model="item.draft.criminal"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کی جرم را انجام داده')"
              hide-details
              clearable
              :error="hasError(item, 'criminal')"
              :error-messages="getErrorMessages(item, 'criminal')"
            />
          </template>

          <!-- Date / Hour -->
          <template #item.event_date="{ item }">
            <DateInput
              v-model="item.draft.event_date"
              class="text-group"
              :rules="[rules.required]"
              :label="t('تاریخ واقعه')"
            />
          </template>

          <template #item.event_hour="{ item }">
            <v-text-field
              v-model="item.draft.event_hour"
              type="time"
              step="60"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('ساعت واقعه')"
              hide-details
              clearable
              :error="hasError(item, 'event_hour')"
              :error-messages="getErrorMessages(item, 'event_hour')"
            />
          </template>

          <!-- Place -->
          <template #item.event_zone="{ item }">
            <v-text-field
              v-model="item.draft.event_zone"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('زون')"
              clearable
              :error="hasError(item, 'event_zone')"
              :error-messages="getErrorMessages(item, 'event_zone')"
            />
          </template>

          <template #item.event_hawza="{ item }">
            <v-text-field
              v-model="item.draft.event_hawza"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('حوزه')"
              clearable
              :error="hasError(item, 'event_hawza')"
              :error-messages="getErrorMessages(item, 'event_hawza')"
            />
          </template>

          <template #item.event_specific_place="{ item }">
            <v-text-field
              v-model="item.draft.event_specific_place"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('محل مشخص')"
              clearable
              :error="hasError(item, 'event_specific_place')"
              :error-messages="getErrorMessages(item, 'event_specific_place')"
            />
          </template>

          <!-- coordinates -->
          <template #item.coordinates="{ item }">
            <v-text-field
              v-model="item.draft.coordinates"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کوردینات')"
              hide-details
              clearable
              :error="hasError(item, 'coordinates')"
              :error-messages="getErrorMessages(item, 'coordinates')"
            />
          </template>

          <!-- talafat -->
          <template #item.talafat_jani="{ item }">
            <v-autocomplete
              v-model="item.draft.talafat_jani"
              :items="janiTalafatItems"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تلفات جانی')"
              hide-details
              clearable
              :error="hasError(item, 'talafat_jani')"
              :error-messages="getErrorMessages(item, 'talafat_jani')"
            />
          </template>

          <template #item.talafat_mali="{ item }">
            <v-autocomplete
              v-model="item.draft.talafat_mali"
              :items="maliTalafatItems"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تلفات مالی')"
              hide-details
              clearable
              :error="hasError(item, 'talafat_mali')"
              :error-messages="getErrorMessages(item, 'talafat_mali')"
            />
          </template>

          <!-- texts -->
          <template #item.tafseel="{ item }">
            <v-text-field
              v-model="item.draft.tafseel"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('تفصیل')"
              hide-details
              clearable
              :error="hasError(item, 'tafseel')"
              :error-messages="getErrorMessages(item, 'tafseel')"
            />
          </template>

          <template #item.talks="{ item }">
            <v-text-field
              v-model="item.draft.talks"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('وضاحت')"
              hide-details
              clearable
              :error="hasError(item, 'talks')"
              :error-messages="getErrorMessages(item, 'talks')"
            />
          </template>

          <!-- Documents (unchanged logic) -->
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
                  prepend-icon="mdi-download"
                  :disabled="!item.id"
                  color="error"
                  size="small"
                  @click="downloadAll(item)"
                >
                  {{ t('دانلود فایل ها') }}
                </v-btn>
              </div>

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
                  ...{{ t('در حال پلود') }} {{ uploadingPct[item.idOrTmp] }}%
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

          <!-- note -->
          <template #item.note="{ item }">
            <v-text-field
              v-model="item.draft.note"
              density="compact"
              class="text-group-note"
              variant="outlined"
              :label="t('ملاحظات')"
              hide-details
              clearable
              :error="hasError(item, 'note')"
              :error-messages="getErrorMessages(item, 'note')"
            />
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="flex items-center gap-1">
              <v-btn class="bg-primary" variant="outlined" type="submit" :loading="item.loading">
                {{ t('ذخیره دیتا') }}
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
      </v-card>
    </v-form>

    <!-- Peek (unchanged) -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title-key="event_type"
        :fields="headers"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- Filterable table -->
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

    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, watch, computed, nextTick, reactive } from 'vue';
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
  watch(locale, (newLocale) => (isRtl.value = newLocale === 'fa'));

  /* Snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (m) => {
    errorMessage.value = m;
    showError.value = true;
  };

  /* v-form */
  const editorForm = ref(null);
  const editorValid = ref(false);
  const formKey = ref(0);

  /* helpers */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  /* option sources */
  const resourceCodeItems = computed(() => toArray(repo.resourcecode));
  const officeEmployeesCodeItems = computed(() => toArray(repo.officeemployeescode));
  const janiTalafatItems = computed(() => toArray(repo.janiTalafatOptions));
  const maliTalafatItems = computed(() => toArray(repo.maliTalafatOptions));

  /* required fixed dropdowns */
  const eventTypeOptions = ['سیاسی', 'جنایی', 'الهی'];
  const eventSpecificTypeOptions = ['قتل', 'چاودنه', 'ټکر', 'سرقت', 'مسلحانه برید'];

  /* progress */
  const uploadingPct = reactive({});
  const startProgress = (k) => (uploadingPct[k] = 1);
  const setProgress = (k, pct) => (uploadingPct[k] = pct);
  const endProgress = (k) => (uploadingPct[k] = 0);

  /* errors */
  const itemErrors = ref({});
  const setItemErrors = (it, errs) => (itemErrors.value[it.idOrTmp] = errs);
  const clearItemErrors = (it) => {
    if (itemErrors.value[it.idOrTmp]) delete itemErrors.value[it.idOrTmp];
  };
  const hasError = (it, f) => !!(itemErrors.value[it.idOrTmp] && itemErrors.value[it.idOrTmp][f]);
  const getErrorMessages = (it, f) => itemErrors.value[it.idOrTmp]?.[f] ?? [];

  /* rules */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    selectRequired: (label) => (v) => !!v || t('{label} الزامی است', { label }),
  };

  /* unwrap slot proxies */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* rows */
  const rows = ref([]);
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  /* map API -> row (ONLY your fields) */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      resource_id: x.resource_id ?? x.resource?.id ?? null,
      office_employee_id: x.office_employee_id ?? x.office_employee?.id ?? null,

      event_type: x.event_type ?? '',
      event_specific_type: x.event_specific_type ?? '',
      target_person: x.target_person ?? '',
      criminal: x.criminal ?? '',

      event_date: x.event_date ?? '',
      event_hour: x.event_hour ?? '',

      event_zone: x.event_zone ?? '',
      event_hawza: x.event_hawza ?? '',
      event_specific_place: x.event_specific_place ?? '',
      coordinates: x.coordinates ?? '',

      talafat_jani: x.talafat_jani ?? '',
      talafat_mali: x.talafat_mali ?? '',

      tafseel: x.tafseel ?? '',
      talks: x.talks ?? '',
      note: x.note ?? '',

      /* docs */
      existingDocs: Array.isArray(x.documents) ? x.documents : [],
    };
    return { ...base, draft: { ...base, _newDocs: [], removed_document_ids: [] } };
  }

  /* blank editor */
  const setBlankEditorRow = () => (rows.value = [toRowModel({})]);

  /* headers (editor) — ONLY your fields */
  const headers = computed(() => [
    { title: t('کود منبع (ID)'), key: 'resource_id' },
    { title: t('کود کارمند (ID)'), key: 'office_employee_id' },
    { title: t('نوعیت واقعه'), key: 'event_type' },
    { title: t('نوعیت مشخص واقعه'), key: 'event_specific_type' },
    { title: t('هدف/مکان'), key: 'target_person' },
    { title: t('عامل/مرتکب'), key: 'criminal' },
    { title: t('تاریخ واقعه'), key: 'event_date' },
    { title: t('ساعت واقعه'), key: 'event_hour' },
    { title: t('زون'), key: 'event_zone' },
    { title: t('حوزه'), key: 'event_hawza' },
    { title: t('محل مشخص'), key: 'event_specific_place' },
    { title: t('کوردینات'), key: 'coordinates' },
    { title: t('تلفات جانی'), key: 'talafat_jani' },
    { title: t('تلفات مالی'), key: 'talafat_mali' },
    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('شواهد مربوط به واقعه'), key: 'documents' },
    { title: t('وضاحت'), key: 'talks' },
    { title: t('ملاحظات'), key: 'note' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* Filterable table */
  const rowsFT = computed(() => toArray(repo.Events));
  const headersFT = computed(() => [
    { title: t('کود منبع (ID)'), key: 'resource_id' },
    { title: t('کود کارمند (ID)'), key: 'office_employee_id' },
    { title: t('نوعیت واقعه'), key: 'event_type' },
    { title: t('نوعیت مشخص واقعه'), key: 'event_specific_type' },
    { title: t('هدف/مکان'), key: 'target_person' },
    { title: t('عامل/مرتکب'), key: 'criminal' },
    { title: t('تاریخ واقعه'), key: 'event_date' },
    { title: t('ساعت واقعه'), key: 'event_hour' },
    { title: t('زون'), key: 'event_zone' },
    { title: t('حوزه'), key: 'event_hawza' },
    { title: t('محل مشخص'), key: 'event_specific_place' },
    { title: t('کوردینات'), key: 'coordinates' },
    { title: t('تلفات جانی'), key: 'talafat_jani' },
    { title: t('تلفات مالی'), key: 'talafat_mali' },
    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('وضاحت'), key: 'talks' },
    { title: t('ملاحظات'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('شناسه‌ها'), keys: ['resource_id', 'office_employee_id'] },
    { title: t('نوع واقعه'), keys: ['event_type', 'event_specific_type'] },
    { title: t('هدف و عامل'), keys: ['target_person', 'criminal'] },
    { title: t('زمان'), keys: ['event_date', 'event_hour'] },
    {
      title: t('مکان'),
      keys: ['event_zone', 'event_hawza', 'event_specific_place', 'coordinates'],
    },
    { title: t('تلفات'), keys: ['talafat_jani', 'talafat_mali'] },
    { title: t('متن‌ها'), keys: ['tafseel', 'talks', 'note'] },
  ]);

  /* filters */
  const filters = ref({});

  /* documents */
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
    if (url) window.open(url, '_blank');
  }
  function markDocForRemoval(item, doc) {
    if (!doc?.id) return;
    if (!Array.isArray(item.draft.removed_document_ids)) item.draft.removed_document_ids = [];
    if (!item.draft.removed_document_ids.includes(doc.id))
      item.draft.removed_document_ids.push(doc.id);
    item.existingDocs = (item.existingDocs || []).filter((d) => d.id !== doc.id);
  }

  /* diff + FormData — ONLY your fields */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'resource_id',
      'office_employee_id',
      'event_type',
      'event_specific_type',
      'target_person',
      'criminal',
      'event_date',
      'event_hour',
      'event_zone',
      'event_hawza',
      'event_specific_place',
      'coordinates',
      'talafat_jani',
      'talafat_mali',
      'tafseel',
      'talks',
      'note',
    ];
    for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    return changed;
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(obj)) if (v != null) fd.append(k, v);
    return fd;
  }

  /* save/update (unchanged flow) */
  async function save(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;

    clearItemErrors(item);

    if (!item.draft?.event_type) {
      showErrorMessage(t('نوعیت واقعه الزامی است'));
      await nextTick();
      scrollToFirstInvalid();
      return;
    }

    const changed = item.id ? diffDraft(item) : { ...item.draft };
    const fd = toFormDataSelective(changed);
    (item.draft._newDocs || []).forEach((file) => fd.append('documents[]', file));
    (item.draft.removed_document_ids || []).forEach((id) =>
      fd.append('removed_document_ids[]', id)
    );

    const key = item.idOrTmp ?? item.id ?? `tmp-${Date.now()}`;
    item.loading = true;
    startProgress(key);

    try {
      if (!item.id) {
        const created = await repo
          .CreateEvent(fd, (pct) => setProgress(key, pct))
          .catch((e) => {
            if (e?.response?.data?.errors) setItemErrors(item, e.response.data.errors);
            return null;
          });
        if (created?.id) {
          await fetchAll();
          setBlankEditorRow();
          editorForm.value?.resetValidation?.();
          showErrorMessage(t('واقعه ایجاد شد'));
        } else {
          await fetchAll();
          setBlankEditorRow();
        }
        return;
      }

      if ([...fd.keys()].length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }

      const updated = await repo
        .UpdateEvent(item.id, fd, (pct) => setProgress(key, pct))
        .catch((e) => {
          if (e?.response?.data?.errors) setItemErrors(item, e.response.data.errors);
          return null;
        });

      if (updated?.id) {
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
        showErrorMessage(t('واقعه بروزرسانی شد'));
      }
    } finally {
      endProgress(key);
      item.loading = false;
    }
  }

  /* validated submit */
  async function onSubmit(item) {
    const form = editorForm.value;
    if (!form) return;
    const { valid } = await form.validate();
    if (!valid) {
      showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
      await nextTick();
      scrollToFirstInvalid();
      return;
    }
    await save(item);
  }

  /* scroll to first invalid */
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

  /* hard reset */
  async function hardResetFormValidation() {
    editorValid.value = false;
    itemErrors.value = {};
    await nextTick();
    editorForm.value?.resetValidation?.();
    formKey.value++;
    await nextTick();
  }

  /* fetch list (unchanged) */
  async function fetchAll() {
    try {
      await repo.FetchEventsData({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search,
      });
    } catch {}
  }

  /* edit / peek (unchanged) */
  const editorCard = ref(null);
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;

      if (typeof repo.FetchEventData === 'function') {
        await repo.FetchEventData(id);
        const single =
          (repo.Event && Array.isArray(repo.Event) ? repo.Event[0] : repo.Event) || row;
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
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      if (typeof repo.FetchEventData === 'function') {
        await repo.FetchEventData(id);
        detail.value = (repo.Event && Array.isArray(repo.Event) ? repo.Event[0] : repo.Event) || {};
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

  /* init */
  onMounted(async () => {
    await Promise.allSettled([
      repo.GetOfficeEmployee?.(),
      repo.GetZoneEmployees?.(),
      repo.GetResource?.(),
      repo.GetOfficeEmployeeCode?.(),
      repo.GetZoneEmployeesCode?.(),
      repo.GetResourceCode?.(),
    ]);
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

  .flex {
    display: flex;
  }
  .items-center {
    align-items: center;
  }
  .gap-1 {
    gap: 4px;
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

  /* documents */
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
  .chips-row {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    padding: 2px 0;
    scrollbar-width: thin;
  }
  .chip-outline,
  .chip-existing {
    background: #c8dae1;
    color: #000 !important;
  }
  .download-all-btn {
    white-space: nowrap;
  }
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }

  /* tighter cells */
  .custom-table :deep(td) {
    padding-left: 8px !important;
    padding-right: 1px !important;
  }
</style>
