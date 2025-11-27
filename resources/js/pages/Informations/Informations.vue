<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('اطلاعات')" :subtitle="t('اضافه کردن اطلاعات')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- Search -->
    <v-row class="d-flex">
      <v-col>
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

    <v-form
      ref="editorForm"
      v-model="editorValid"
      lazy-validation
      :key="formKey"
      @submit.prevent="onSubmit(rows[0])"
    >
      <!-- Single-row editor (create/edit) — NO pagination/footers -->
      <v-card class="tablebody" ref="editorCard">
        <v-data-table
          :headers="headers"
          :items="rows"
          :loading="repo.loading"
          item-key="idOrTmp"
          class="elevation-2 custom-table"
          :dir="isRtl ? 'rtl' : 'ltr'"
          density="comfortable"
          hide-default-footer
        >
          <!-- Codes -->
          <template #item.resource_id="{ item }">
            <v-autocomplete
              v-model="item.draft.resource_id"
              class="text-group pr-1"
              density="compact"
              variant="outlined"
              :label="t('کود منبع')"
              :items="resourceItems"
              :item-title="formatResource"
              item-value="id"
              clearable
              hide-details
              :rules="[rules.selectRequired(t('کود منبع'))]"
              :error="hasError(item, 'resource_id')"
              :error-messages="getErrorMessages(item, 'resource_id')"
            />
          </template>

          <template #item.office_employee_id="{ item }">
            <v-autocomplete
              v-model="item.draft.office_employee_id"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کود کارمند اداری')"
              :items="officeEmployeesItems"
              item-title="code_number"
              item-value="id"
              clearable
              hide-details
              :error="hasError(item, 'office_employee_id')"
              :error-messages="getErrorMessages(item, 'office_employee_id')"
            />
          </template>

          <!-- Identity -->
          <template #item.name="{ item }">
            <v-text-field
              v-model="item.draft.name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام')"
              :rules="[rules.required]"
              hide-details
              required
              :error="hasError(item, 'name')"
              :error-messages="getErrorMessages(item, 'name')"
            />
          </template>

          <template #item.nick_name="{ item }">
            <v-text-field
              v-model="item.draft.nick_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام مستعار')"
              hide-details
              :error="hasError(item, 'nick_name')"
              :error-messages="getErrorMessages(item, 'nick_name')"
            />
          </template>

          <template #item.father_name="{ item }">
            <v-text-field
              v-model="item.draft.father_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدر')"
              hide-details
              :error="hasError(item, 'father_name')"
              :error-messages="getErrorMessages(item, 'father_name')"
            />
          </template>

          <template #item.grand_father_name="{ item }">
            <v-text-field
              v-model="item.draft.grand_father_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدرکلان')"
              hide-details
              :error="hasError(item, 'grand_father_name')"
              :error-messages="getErrorMessages(item, 'grand_father_name')"
            />
          </template>

          <!-- Main address -->
          <template #item.main_province="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.main_province"
                density="compact"
                variant="outlined"
                :label="t('ولایت اصلی')"
                :rules="[rules.required]"
                hide-details
                :error="hasError(item, 'main_province')"
                :error-messages="getErrorMessages(item, 'main_province')"
              />
              <v-text-field
                v-model="item.draft.main_districts"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی اصلی')"
                :rules="[rules.required]"
                hide-details
                :error="hasError(item, 'main_districts')"
                :error-messages="getErrorMessages(item, 'main_districts')"
              />
              <v-text-field
                v-model="item.draft.main_village"
                density="compact"
                variant="outlined"
                :label="t('قریه/گذر اصلی')"
                :rules="[rules.required]"
                hide-details
                :error="hasError(item, 'main_village')"
                :error-messages="getErrorMessages(item, 'main_village')"
              />
            </div>
          </template>

          <!-- Now address -->
          <template #item.now_province="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.now_province"
                density="compact"
                variant="outlined"
                :label="t('ولایت فعلی')"
                hide-details
                :error="hasError(item, 'now_province')"
                :error-messages="getErrorMessages(item, 'now_province')"
              />
              <v-text-field
                v-model="item.draft.now_district"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی فعلی')"
                hide-details
                :error="hasError(item, 'now_district')"
                :error-messages="getErrorMessages(item, 'now_district')"
              />
              <v-text-field
                v-model="item.draft.now_village"
                density="compact"
                variant="outlined"
                :label="t('قریه/گذر فعلی')"
                hide-details
                :error="hasError(item, 'now_village')"
                :error-messages="getErrorMessages(item, 'now_village')"
              />
            </div>
          </template>

          <!-- Types -->
          <template #item.syasi_info_type="{ item }">
            <v-autocomplete
              v-model="item.draft.syasi_info_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('سیاسی - نوع')"
              :items="seyasiItems"
              item-title="name"
              item-value="id"
              clearable
              hide-details
              :error="hasError(item, 'syasi_info_type')"
              :error-messages="getErrorMessages(item, 'syasi_info_type')"
            />
          </template>

          <template #item.jenayee_info_type="{ item }">
            <v-autocomplete
              v-model="item.draft.jenayee_info_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('جنایی - هو/نه')"
              :items="jenayeeItems"
              item-title="name"
              item-value="id"
              clearable
              hide-details
              :error="hasError(item, 'jenayee_info_type')"
              :error-messages="getErrorMessages(item, 'jenayee_info_type')"
            />
          </template>

          <!-- Time -->
          <template #item.information_saved_date="{ item }">
            <DateInput
              v-model="item.draft.information_saved_date"
              class="text-group"
              :label="t('تاریخ اطلاع')"
              :rules="[rules.required]"
            />
          </template>

          <template #item.information_saved_hour="{ item }">
            <v-text-field
              v-model="item.draft.information_saved_hour"
              class="text-group"
              density="compact"
              type="time"
              step="60"
              variant="outlined"
              :label="t('ساعت اطلاع')"
              hide-details
              :error="hasError(item, 'information_saved_hour')"
              :error-messages="getErrorMessages(item, 'information_saved_hour')"
            />
          </template>

          <!-- Degree -->
          <template #item.information_degree="{ item }">
            <v-text-field
              v-model="item.draft.information_degree"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('درجه موثقیت اطلاع')"
              hide-details
              :error="hasError(item, 'information_degree')"
              :error-messages="getErrorMessages(item, 'information_degree')"
            />
          </template>

          <!-- Long texts -->
          <template #item.tafseel="{ item }">
            <v-text-field
              v-model="item.draft.tafseel"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('تفصیل')"
              hide-details
              :error="hasError(item, 'tafseel')"
              :error-messages="getErrorMessages(item, 'tafseel')"
            />
          </template>

          <template #item.manager_comment="{ item }">
            <v-text-field
              v-model="item.draft.manager_comment"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('نظر مدیر')"
              hide-details
              :error="hasError(item, 'manager_comment')"
              :error-messages="getErrorMessages(item, 'manager_comment')"
            />
          </template>

          <template #item.lead_drirection="{ item }">
            <v-text-field
              v-model="item.draft.lead_drirection"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('رهنمود رهبریت')"
              hide-details
              :error="hasError(item, 'lead_drirection')"
              :error-messages="getErrorMessages(item, 'lead_drirection')"
            />
          </template>

          <template #item.talks="{ item }">
            <v-text-field
              v-model="item.draft.talks"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('گفتگو های انجام شده')"
              hide-details
              :error="hasError(item, 'talks')"
              :error-messages="getErrorMessages(item, 'talks')"
            />
          </template>

          <template #item.note="{ item }">
            <v-text-field
              v-model="item.draft.note"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('ملاحظات')"
              hide-details
              :error="hasError(item, 'note')"
              :error-messages="getErrorMessages(item, 'note')"
            />
          </template>

          <template #item.Province_addr="{ item }">
            <v-text-field
              v-model="item.draft.Province_addr"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('ولایت مربوطه')"
              hide-details
              :error="hasError(item, 'Province_addr')"
              :error-messages="getErrorMessages(item, 'Province_addr')"
            />
          </template>
          <!-- Documents (same UX as your sample) -->
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
                  @click="triggerDocs(item)"
                >
                  {{ t('آپلود فایل ها') }}
                </v-btn>

                <div
                  class="chips-row"
                  :title="
                    item.draft._newDocs?.length || item.existingDocs?.length
                      ? ''
                      : t('فایلی انتخاب نشده است')
                  "
                >
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
            </div>
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="flex items-center gap-1">
              <v-btn
                class="bg-primary"
                v-if="auth.can('informations.create')"
                variant="outlined"
                type="submit"
                :loading="item.loading"
              >
                {{ t('ثبت دیتا') }}
              </v-btn>

              <v-btn
                class="bg-error"
                v-if="auth.can('informations.delete')"
                variant="outlined"
                @click="del(item)"
              >
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

    <!-- Peek + FilterableTable -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title-key="name"
        :fields="headers"
        :cols="3"
        dir="rtl"
        @fetch="fetchOne"
      />
    </v-container>

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
      :autoDetectDates="false"
      :dateKeys="['information_saved_date']"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    />

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, computed, watch, nextTick } from 'vue';
  import AppBar from '../../components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import DateInput from '../../components/DateInput.vue';
  import { useI18n } from 'vue-i18n';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';

  import { useLoginRepository } from '../../stores/LoginRepository';
  const auth = useLoginRepository();

  const { t, locale } = useI18n();
  const repo = useDatabaseRepository();

  const editorForm = ref(null);
  const editorValid = ref(false);
  const formKey = ref(0);

  const showError = ref(false);
  const errorMessage = ref('');
  const editorCard = ref(null);

  function showErrorMessage(message) {
    errorMessage.value = message;
    showError.value = true;
  }

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => {
    isRtl.value = newLocale === 'fa';
  });

  /* ===== Errors per row ===== */
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

  /* ===== Data sources to arrays ===== */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  /* ===== Dropdown options (provided by repo) ===== */
  const resourceItems = computed(() => toArray(repo.resource));
  const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
  const seyasiItems = computed(() => toArray(repo.seyasiOptions)); // from your repo
  const jenayeeItems = computed(() => toArray(repo.jenayeeOptions)); // (Pashto yes/no) from your repo

  /* ===== Single editor row ===== */
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;
  const rows = ref([]);

  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      /* codes */
      resource_id: x.resource_id ?? null,
      office_employee_id: x.office_employee_id ?? null,

      /* identity */
      name: x.name ?? '',
      nick_name: x.nick_name ?? '',
      father_name: x.father_name ?? '',
      grand_father_name: x.grand_father_name ?? '',

      /* addresses */
      main_province: x.main_province ?? '',
      main_districts: x.main_districts ?? '',
      main_village: x.main_village ?? '',
      now_province: x.now_province ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',

      /* types */
      syasi_info_type: x.syasi_info_type ?? '',
      jenayee_info_type: x.jenayee_info_type ?? '',

      /* time & degree */
      information_saved_date: x.information_saved_date ?? '',
      information_saved_hour: x.information_saved_hour ?? '',
      information_degree: x.information_degree ?? '',

      /* texts */
      tafseel: x.tafseel ?? '',
      manager_comment: x.manager_comment ?? '',
      Province_addr: x.Province_addr ?? '',
      lead_drirection: x.lead_drirection ?? '',
      talks: x.talks ?? '',
      note: x.note ?? '',

      /* documents */
      existingDocs: Array.isArray(x.documents) ? x.documents : [],
    };

    return { ...base, draft: { ...base, _newDocs: [], removed_document_ids: [] } };
  }

  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ===== Headers (editor table) ===== */
  const headers = computed(() => [
    { title: t('کود منبع'), key: 'resource_id' },
    { title: t('کود کارمند'), key: 'office_employee_id' },

    { title: t('نام'), key: 'name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },

    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },

    { title: t('سیاسی'), key: 'syasi_info_type' },
    { title: t('جنایی (هو/نه)'), key: 'jenayee_info_type' },

    { title: t('تاریخ اطلاع'), key: 'information_saved_date' },
    { title: t('ساعت اطلاع'), key: 'information_saved_hour' },
    { title: t('درجه موثقیت اطلاع'), key: 'information_degree' },

    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('نظر مدیر'), key: 'manager_comment' },
    { title: t('رهنمود رهبریت'), key: 'lead_drirection' },
    { title: t('گفتگوهای انجام شده'), key: 'talks' },
    { title: t('ملاحظات'), key: 'note' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('اسناد'), key: 'documents' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* ===== Headers for FilterableTable ===== */
  const rowsFT = computed(() => toArray(repo.Informations));
  const headersFT = computed(() => [
    { title: t('کود منبع'), key: 'resource_id' },
    { title: t('کود کارمند'), key: 'office_employee_id' },

    { title: t('نام'), key: 'name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_districts' },
    { title: t('قریه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },

    { title: t('سیاسی'), key: 'syasi_info_type' },
    { title: t('جنایی (هو/نه)'), key: 'jenayee_info_type' },

    { title: t('تاریخ اطلاع'), key: 'information_saved_date' },
    { title: t('ساعت اطلاع'), key: 'information_saved_hour' },
    { title: t('درجه موثقیت اطلاع'), key: 'information_degree' },

    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('نظر مدیر'), key: 'manager_comment' },
    { title: t('رهنمود رهبریت'), key: 'lead_drirection' },
    { title: t('گفت‌وگوها'), key: 'talks' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('ملاحظات'), key: 'note', width: 400 },
  ]);

  const topGroups = computed(() => [
    {
      title: t('کودها'),
      keys: ['resource_id', 'office_employee_id'],
    },
    {
      title: t('معرفت'),
      keys: ['name', 'nick_name', 'father_name', 'grand_father_name'],
    },
    {
      title: t('آدرس اصلی'),
      keys: ['main_province', 'main_districts', 'main_village'],
    },
    {
      title: t('آدرس فعلی'),
      keys: ['now_province', 'now_district', 'now_village'],
    },
    {
      title: t('نوع و زمان اطلاع'),
      keys: [
        'syasi_info_type',
        'jenayee_info_type',
        'information_saved_date',
        'information_saved_hour',
        'information_degree',
      ],
    },
    {
      title: t('متن‌ها'),
      keys: ['tafseel', 'manager_comment', 'lead_drirection', 'talks', 'note'],
    },
  ]);

  /* ===== Rules ===== */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    selectRequired: (label) => (v) => !!v || t('{label} الزامی است', { label }),
    min: (n) => (v) => (v && v.length >= n) || t('حداقل {n} حرف', { n }),
    max: (n) => (v) => (v && v.length <= n) || t('حداکثر {n} حرف', { n }),
    number: (v) => !v || /^[0-9]+$/.test(v) || t('فقط اعداد مجاز است'),
  };

  const filters = ref({});

  /* ===== Docs helpers ===== */
  function triggerDocs(item) {
    const el = document.getElementById(`docs-${item.idOrTmp}`);
    if (el) el.click();
  }
  function onPickDocs(item, files) {
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
    item.existingDocs = (item.existingDocs || []).filter((d) => d.id !== doc.id);
  }

  /* ===== Save / Delete ===== */
  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj).forEach(([k, v]) => {
      if (v !== undefined && v !== null) fd.append(k, v);
    });
    return fd;
  }
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};

    const keys = [
      'resource_id',
      'office_employee_id',

      'name',
      'nick_name',
      'father_name',
      'grand_father_name',

      'main_province',
      'main_districts',
      'main_village',
      'now_province',
      'Province_addr',
      'now_district',
      'now_village',

      'syasi_info_type',
      'jenayee_info_type',

      'information_saved_date',
      'information_saved_hour',
      'information_degree',

      'tafseel',
      'manager_comment',
      'lead_drirection',
      'talks',
      'note',
    ];

    for (const k of keys) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    return changed;
  }

  async function save(item) {
    if (!item?.draft?.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
      return;
    }
    item.loading = true;
    clearItemErrors(item);

    try {
      // CREATE
      if (!item.id) {
        const fd = new FormData();
        Object.entries(item.draft).forEach(([k, v]) => {
          if (k === '_newDocs' || k === 'removed_document_ids') return;
          if (v !== undefined && v !== null) fd.append(k, v);
        });
        (item.draft._newDocs || []).forEach((file) => fd.append('documents[]', file));
        const created = await repo.CreateInformation(fd).catch((e) => {
          if (e?.response?.data?.errors) setItemErrors(item, e.response.data.errors);
          return null;
        });

        if (created?.id) {
          await fetchAll();
          setBlankEditorRow();
          editorForm.value?.resetValidation?.();
        } else {
          await fetchAll();
        }
        return;
      }

      // UPDATE
      const changed = diffDraft(item);
      const hasRemovals = (item.draft.removed_document_ids || []).length > 0;
      const hasNewDocs = (item.draft._newDocs || []).length > 0;

      if (!hasRemovals && !hasNewDocs && Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }

      const fd = toFormDataSelective(changed);
      (item.draft._newDocs || []).forEach((file) => fd.append('documents[]', file));
      (item.draft.removed_document_ids || []).forEach((id) =>
        fd.append('removed_document_ids[]', id)
      );

      const updated = await repo.UpdateInformation(item.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setItemErrors(item, e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
    } catch (error) {
      console.error('Save error:', error);
      showErrorMessage(t('خطا در ذخیره اطلاعات'));
    } finally {
      item.loading = false;
    }
  }

  // VALIDATED SUBMIT
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

  async function del(item) {
    if (!item?.id) {
      setBlankEditorRow();
      editorForm.value?.resetValidation?.();
      return;
    }
    if (!confirm(t('آیا از حذف  مطمئن هستید؟', { name: item.name || t('رکورد') }))) return;
    try {
      await repo.DeleteInformation(item.id);
      await fetchAll();
      setBlankEditorRow();
      editorForm.value?.resetValidation?.();
    } catch (error) {
      console.error('Delete error:', error);
      showErrorMessage(t('خطا در حذف'));
    }
  }

  function clearEditor(item) {
    try {
      if (item && item.idOrTmp && itemErrors.value[item.idOrTmp]) {
        delete itemErrors.value[item.idOrTmp];
      }
      setBlankEditorRow();
      editorForm.value?.resetValidation?.();
      showErrorMessage(t('ورودی‌ها پاک شد'));
    } catch (e) {
      console.error('clearEditor error:', e);
    }
  }

  async function downloadAll(item) {
    if (!item?.id) return;
    const base = (item.name || 'documents').toLowerCase().replace(/\s+/g, '_');
    const filename = `${base}_documents.zip`;
    await repo.DownloadInformationZip(item.id, filename);
  }

  /* ===== Peek / Fetch ===== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      if (typeof repo.FetchInformationData === 'function') {
        await repo.FetchInformationData(id);
        detail.value =
          (repo.Information && Array.isArray(repo.Information)
            ? repo.Information[0]
            : repo.Information) || {};
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

  /* ===== Fetch ALL ===== */
  async function fetchAll() {
    try {
      await repo.FetchInformationsData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search,
      });
    } catch (e) {
      console.error('fetchAll error', e);
    }
  }

  /* ===== Init ===== */
  onMounted(async () => {
    await Promise.allSettled([repo.GetOfficeEmployee?.(), repo.GetResource?.()]);
    await fetchAll();
    setBlankEditorRow();
  });

  /* ===== helpers ===== */
  const formatResource = (r) => {
    if (!r) return '';
    // Show: code_number — name — father_name — #id
    const code = r.code_number ? String(r.code_number) : '';
    const nm = r.name ? String(r.name) : '';
    const dad = r.father_name ? String(r.father_name) : '';
    const id = r.id != null ? `#${r.id}` : '';
    return [code, nm, dad, id].filter(Boolean).join(' — ');
  };
</script>

<style scoped>
  table th {
    white-space: nowrap;
  }
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }
  .addr-group {
    display: grid;
    grid-template-columns: repeat(3, minmax(120px, 1fr));
    gap: 2px;
  }
  .text-group {
    display: grid;
    width: 120px;
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
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .tablebody {
    display: flex !important;
    gap: 2px !important;
  }

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

  /* compact spacing in groups (to match your sample) */
  .addr-group,
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
  }

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
    overflow-y: hidden;
    padding: 2px 0;
    scrollbar-width: thin;
  }
  .chip-existing {
    background: #c8dae1;
    color: black !important;
  }
  .chip-outline {
    background: #c8dae1;
  }
  .download-all-btn {
    white-space: nowrap;
  }
</style>
