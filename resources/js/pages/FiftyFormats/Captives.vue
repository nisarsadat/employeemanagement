<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('اسیر/گرفتار')" :subtitle="t('اضافه کردن یک ریکارډ اسیر')">
      <template #actions><v-btn icon /></template>
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

    <!-- Single-row validated editor -->
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
          :loading="repo.loading"
          item-key="idOrTmp"
          class="elevation-2 custom-table"
          :dir="isRtl ? 'rtl' : 'ltr'"
          density="comfortable"
          hide-default-footer
        >
          <!-- Picture -->
          <template #item.picture="{ item }">
            <div class="pic-wrap">
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
                  <v-img :src="item.previewUrl || item.picture" :alt="t('عکس')" cover />
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
                    @click.stop="openPreview(item.previewUrl || item.picture)"
                  >
                    <v-icon start>mdi-eye</v-icon>{{ t('نمایش') }}
                  </v-btn>
                </div>
              </div>
            </div>
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

          <!-- Main address (3) -->
          <template #item.main_province="{ item }">
            <div class="addr-group-3">
              <v-text-field
                v-model="item.draft.main_province"
                density="compact"
                variant="outlined"
                :label="t('ولایت اصلی')"
                hide-details
                :error="hasError(item, 'main_province')"
                :error-messages="getErrorMessages(item, 'main_province')"
              />
              <v-text-field
                v-model="item.draft.main_district"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی اصلی')"
                hide-details
                :error="hasError(item, 'main_district')"
                :error-messages="getErrorMessages(item, 'main_district')"
              />
              <v-text-field
                v-model="item.draft.main_village"
                density="compact"
                variant="outlined"
                :label="t('قریه/ناحیه اصلی')"
                hide-details
                :error="hasError(item, 'main_village')"
                :error-messages="getErrorMessages(item, 'main_village')"
              />
            </div>
          </template>

          <!-- Now address (4) -->
          <template #item.now_province="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.now_province"
                density="compact"
                variant="outlined"
                :label="t('ولایت فعلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.now_zone"
                density="compact"
                variant="outlined"
                :label="t('زون فعلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.now_district"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی فعلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.now_village"
                density="compact"
                variant="outlined"
                :label="t('قریه/ناحیه فعلی')"
                hide-details
              />
            </div>
          </template>

          <!-- Address details -->
          <template #item.specific_place="{ item }">
            <v-text-field
              v-model="item.draft.specific_place"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('موقعیت مشخص')"
              hide-details
            />
          </template>
          <template #item.street_number="{ item }">
            <v-text-field
              v-model="item.draft.street_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('سرک نمبر')"
              hide-details
            />
          </template>
          <template #item.home_number="{ item }">
            <v-text-field
              v-model="item.draft.home_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نمبر خانه')"
              hide-details
            />
          </template>

          <!-- Specific form / band / activity -->
          <template #item.specific_form="{ item }">
            <v-text-field
              v-model="item.draft.specific_form"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('فرم مشخص/ویژه')"
              hide-details
            />
          </template>
          <template #item.band_distince="{ item }">
            <v-text-field
              v-model="item.draft.band_distince"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('فاصله بانډ')"
              hide-details
            />
          </template>
          <template #item.now_activity="{ item }">
            <v-text-field
              v-model="item.draft.now_activity"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('فعالیت فعلی')"
              hide-details
            />
          </template>

          <!-- Contact -->
          <template #item.phone_number="{ item }">
            <v-text-field
              v-model="item.draft.phone_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس')"
              hide-details
              :error="hasError(item, 'phone_number')"
              :error-messages="getErrorMessages(item, 'phone_number')"
            />
          </template>

          <!-- Resource mapping -->
          <template #item.resource_code="{ item }">
            <v-autocomplete
              v-model="item.draft.resource_code"
              :items="resources"
              item-title="code_number"
              item-value="code_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کود منبع')"
              hide-details
              clearable
              @update:model-value="onResourceCodeChange(item)"
            />
          </template>
          <template #item.resource_phone_number="{ item }">
            <v-text-field
              v-model="item.draft.resource_phone_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره منبع')"
              hide-details
              readonly
            />
          </template>

          <!-- Note & Province -->
          <template #item.note="{ item }">
            <v-text-field
              v-model="item.draft.note"
              density="compact"
              class="text-group-note"
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
                  <!-- New (unsaved) files -->
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

                  <!-- Existing files -->
                  <v-chip
                    v-for="(d, idx) in item.existingDocs || []"
                    :key="`${item.idOrTmp}-df-${idx}`"
                    size="small"
                    color="primary"
                    variant="text"
                    class="chip-existing"
                    :title="d.file_name"
                    @click="openDoc(d.url)"
                  >
                    {{ shortName(d.file_name) }}
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
              <v-btn class="bg-primary" variant="outlined" type="submit" :loading="item.loading">
                {{ t('ثبت دیتا') }}
              </v-btn>
              <v-btn class="bg-error" variant="outlined" @click="del(item)">
                {{ t('حذف') }}
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

    <!-- Peek + FilterableTable -->
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
        dir="rtl"
        :auto-detect-dates="false"
        :date-keys="[]"
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
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    />

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, computed, watch, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const repo = useFasadRepository();

  /* ===== Editor form + snackbar ===== */
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

  /* ===== Image preview dialog ===== */
  const previewOpen = ref(false);
  const previewSrc = ref(null);
  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }

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

  /* ===== Rules ===== */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    selectRequired: (label) => (v) => !!v || t('{label} الزامی است', { label }),
    number: (v) => !v || /^[0-9]+$/.test(v) || t('فقط اعداد مجاز است'),
  };

  /* ===== Rows ===== */
  const rows = ref([]);
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  function toRowModel(x = {}) {
    const r = x.resource || {};
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),

      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,
      loading: false,

      // Identity
      name: x.name ?? '',
      nick_name: x.nick_name ?? '',
      father_name: x.father_name ?? '',

      // Main address
      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      // Now address
      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',

      // Details
      specific_place: x.specific_place ?? '',
      street_number: x.street_number ?? '',
      home_number: x.home_number ?? '',

      // Specifics
      specific_form: x.specific_form ?? '',
      band_distince: x.band_distince ?? '',
      now_activity: x.now_activity ?? '',
      Province_addr: x.Province_addr ?? '',

      // Resource mapping
      resource_code: x.resource_code ?? r.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? r.phone_number ?? '',
      resource_id: x.resource_id ?? r.id ?? null,

      // Contact & note
      phone_number: x.phone_number ?? '',
      note: x.note ?? '',

      // Documents coming from API
      existingDocs: Array.isArray(x.documents) ? x.documents : [],
    };
    return {
      ...base,
      draft: {
        ...base,
        picture: null,
        _newDocs: [],
        removed_document_ids: [],
      },
    };
  }

  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ===== Table headers ===== */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false, width: 120 },

    // Identity
    { title: t('نام'), key: 'name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('نام پدر'), key: 'father_name' },

    // Addresses
    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },

    // Details
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },

    // Specifics
    { title: t('فرم مشخص'), key: 'specific_form' },
    { title: t('فاصله بانډ'), key: 'band_distince' },
    { title: t('فعالیت فعلی'), key: 'now_activity' },

    // Contact & resource
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    // Note & province
    { title: t('ملاحظات'), key: 'note' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    // Documents
    { title: t('اسناد'), key: 'documents' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },

    { title: t('فرم مشخص'), key: 'specific_form' },
    { title: t('فاصله بانډ'), key: 'band_distince' },
    { title: t('فعالیت فعلی'), key: 'now_activity' },

    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('ملاحظات'), key: 'note', width: 400 },
  ]);

  /* ===== Filter groups ===== */
  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'nick_name', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    { title: t('منزل/موقعیت'), keys: ['specific_place', 'street_number', 'home_number'] },
    { title: t('فرم/بانډ/فعالیت'), keys: ['specific_form', 'band_distince', 'now_activity'] },
    { title: t('تماس'), keys: ['phone_number'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['note'] },
  ]);

  /* ===== Data for FilterableTable ===== */
  const rowsFT = computed(() => repo.Captives || []);

  /* ===== Resources dropdown ===== */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone_number = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* ===== diff + FormData ===== */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'name',
      'nick_name',
      'father_name',
      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_zone',
      'now_district',
      'now_village',
      'specific_place',
      'street_number',
      'home_number',
      'specific_form',
      'band_distince',
      'now_activity',
      'phone_number',
      'Province_addr',
      'resource_code',
      'resource_phone_number',
      'resource_id',
      'note',
    ];
    for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];

    if (d.picture && typeof d.picture === 'object') changed.picture = d.picture;
    else if (typeof d.picture === 'string' && d.picture !== (item.picture ?? ''))
      changed.picture = d.picture;

    return changed;
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  /* ===== Save / Delete / Submit ===== */
  async function save(item) {
    if (!item.draft?.name?.trim()) {
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
        const created = await repo.CreateCaptive?.(fd);
        if (created && created.id) {
          await fetchAll();
          setBlankEditorRow();
          editorForm.value?.resetValidation?.();
        } else {
          await fetchAll();
          setBlankEditorRow();
          editorForm.value?.resetValidation?.();
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

      const fd = new FormData();
      Object.entries(changed).forEach(([k, v]) => {
        if (v !== undefined && v !== null) fd.append(k, v);
      });
      (item.draft._newDocs || []).forEach((file) => fd.append('documents[]', file));
      (item.draft.removed_document_ids || []).forEach((id) =>
        fd.append('removed_document_ids[]', id)
      );

      const updated = await repo.UpdateCaptive?.(item.id, fd);
      if (updated && updated.id) {
        if (item._blobUrl) {
          URL.revokeObjectURL(item._blobUrl);
          item._blobUrl = null;
        }
        // If peek is open for this record, refresh its detail
        if (expanded.value && selectedId.value === item.id) {
          await fetchOne(item.id);
        }
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
    } catch (error) {
      console.error('Save error:', error);
      if (error?.response?.data?.errors) {
        setItemErrors(item, error.response.data.errors);
        showErrorMessage(t('لطفا خطاهای فرم را بررسی کنید'));
      } else {
        showErrorMessage(t('خطا در ذخیره اطلاعات') + ': ' + (error?.message || t('خطای ناشناخته')));
      }
    } finally {
      item.loading = false;
    }
  }

  /* ===== Validated submit ===== */
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

  /* ===== Delete / Clear ===== */
  async function del(item) {
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا مطمئن هستید؟'))) return;
    try {
      await repo.DeleteCaptive?.(item.id);
      await fetchAll();
      setBlankEditorRow();
      // close peek if we were showing this record
      if (expanded.value && selectedId.value === item.id) {
        expanded.value = false;
        detail.value = null;
        selectedId.value = null;
      }
    } catch (e) {
      console.error('Delete error:', e);
      showErrorMessage(t('خطا در حذف') + ': ' + (e?.message || t('خطای ناشناخته')));
    }
  }
  function clearEditor(item) {
    try {
      if (item && item._blobUrl) {
        URL.revokeObjectURL(item._blobUrl);
        item._blobUrl = null;
      }
      const inputEl = document.getElementById(`up-${item?.idOrTmp}`);
      if (inputEl) inputEl.value = '';
      if (item && item.idOrTmp && itemErrors.value[item.idOrTmp]) {
        delete itemErrors.value[item.idOrTmp];
      }
      setBlankEditorRow();
      showErrorMessage(t('ورودی‌ها پاک شد'));
    } catch (e) {
      console.error('clearEditor error:', e);
    }
  }

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
  async function downloadAll(item) {
    if (!item.id) return;
    const base = (item.name || 'captive_documents').toLowerCase().replace(/\s+/g, '_');
    const filename = `${base}_documents.zip`;
    if (typeof repo.DownloadCaptiveZip === 'function') {
      await repo.DownloadCaptiveZip(item.id, filename);
    } else {
      showErrorMessage(t('دانلود پشتیبانی نشده است: تابع DownloadCaptiveZip یافت نشد'));
    }
  }

  /* ===== Peek / Fetch single (Employee-style) ===== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  function normalizeForPeek(x = {}) {
    // Place for light formatting if needed
    return { ...x };
  }

  async function fetchOne(id) {
    loading.value = true;
    try {
      await repo.FetchCaptiveData?.(id);
      const single = Array.isArray(repo.Captive) ? repo.Captive[0] : repo.Captive;
      detail.value = normalizeForPeek(single || {});
      selectedId.value = single?.id ?? Number(id);
    } catch (error) {
      console.error('Fetch one error:', error);
      showErrorMessage(t('خطا در دریافت اطلاعات'));
    } finally {
      loading.value = false;
    }
  }

  function openPeek(payload) {
    const row = payload?.raw ?? payload;
    if (!row?.id) return;
    expanded.value = true;
    selectedId.value = row.id;
    if (!detail.value || detail.value?.id !== row.id) {
      fetchOne(row.id);
    }
  }
  watch(expanded, (on) => {
    if (!on) {
      // optional clear:
      // detail.value = null;
      // selectedId.value = null;
    }
  });

  /* ===== Fetch ALL ===== */
  const filters = ref({});
  async function fetchAll() {
    try {
      await repo.FetchCaptivesData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search || '',
      });
      await repo.GetResourceCode?.(); // preload dropdown
    } catch (e) {
      console.error('fetchAll error', e);
    }
  }

  /* ===== Start edit from FilterableTable ===== */
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      await repo.FetchCaptiveData?.(id);
      const single =
        repo.Captive && Array.isArray(repo.Captive) ? repo.Captive[0] : repo.Captive || row;
      rows.value = [toRowModel(single || row)];
      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      console.error('startEdit error', e);
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* ===== Init ===== */
  onMounted(async () => {
    await fetchAll();
    setBlankEditorRow();
  });
</script>

<style scoped>
  table th {
    white-space: nowrap;
  }

  /* Colored table header */
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }

  /* Layout helpers (Fasad style) */
  .tablebody {
    display: flex !important;
    gap: 2px !important;
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

  /* Grouped inputs */
  .addr-group {
    /* now address: 4 cols */
    display: grid;
    grid-template-columns: repeat(4, minmax(120px, 1fr));
    gap: 2px;
  }
  .addr-group-3 {
    /* main address: 3 cols */
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
    width: 380px;
    gap: 2px;
  }

  /* Compact inputs + labels */
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

  /* Tight spacing in grouped blocks */
  .addr-group,
  .addr-group-3,
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
  }

  /* Picture hover tools */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .pic-thumb {
    display: inline-flex;
    cursor: pointer;
    position: relative;
  }
  .pic-overlay {
    position: absolute;
    inset: -6px -16px auto auto;
    display: none;
    gap: 2px;
    background: rgba(0, 0, 0, 0.55);
    padding: 6px;
    border-radius: 8px;
    transform: translateY(-20%);
    white-space: nowrap;
  }
  .pic-thumb:hover .pic-overlay {
    display: flex;
    flex-direction: column-reverse;
    gap: 8px;
  }

  /* Documents UI */
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
