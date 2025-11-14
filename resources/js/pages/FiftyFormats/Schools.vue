<!-- src/views/schools/Schools.vue -->
<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('مکتب ها')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید مکتب')"
      @toggle-drawer="drawer = !drawer"
    >
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

    <!-- Single-row validated editor (Fasad pattern) -->
    <v-form
      ref="editorForm"
      v-model="editorValid"
      lazy-validation
      @submit.prevent="onSubmit(rows[0])"
    >
      <v-card class="tablebody" ref="editorCard">
        <v-data-table
          :headers="headers"
          :items="rows"
          :loading="repo.loading"
          item-key="idOrTmp"
          class="elevation-2 custom-table"
          density="comfortable"
          hide-default-footer
          :dir="isRtl ? 'rtl' : 'ltr'"
        >
          <!-- BASIC -->
          <template #item.school_name="{ item }">
            <v-text-field
              v-model="item.draft.school_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام مکتب')"
              :rules="[rules.required]"
              hide-details
              :error="hasError(item, 'school_name')"
              :error-messages="getErrorMessages(item, 'school_name')"
              required
            />
          </template>
          <template #item.school_type="{ item }">
            <v-text-field
              v-model="item.draft.school_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع مکتب')"
              hide-details
            />
          </template>

          <!-- PRINCIPAL -->
          <template #item.principle_name="{ item }">
            <v-text-field
              v-model="item.draft.principle_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام مدیر (Principle)')"
              hide-details
            />
          </template>
          <template #item.principle_f_name="{ item }">
            <v-text-field
              v-model="item.draft.principle_f_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدر مدیر')"
              hide-details
            />
          </template>
          <template #item.principle_phone="{ item }">
            <v-text-field
              v-model="item.draft.principle_phone"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره مدیر')"
              hide-details
            />
          </template>

          <!-- QUANTITIES -->
          <template #item.male_teachers_quantity="{ item }">
            <v-text-field
              v-model="item.draft.male_teachers_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              type="number"
              :label="t('معلمان مرد')"
              hide-details
            />
          </template>
          <template #item.female_teachers_quantity="{ item }">
            <v-text-field
              v-model="item.draft.female_teachers_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              type="number"
              :label="t('معلمان زن')"
              hide-details
            />
          </template>
          <template #item.male_students_quantity="{ item }">
            <v-text-field
              v-model="item.draft.male_students_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              type="number"
              :label="t('شاگردان پسر')"
              hide-details
            />
          </template>
          <template #item.female_students_quantity="{ item }">
            <v-text-field
              v-model="item.draft.female_students_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              type="number"
              :label="t('شاگردان دختر')"
              hide-details
            />
          </template>

          <!-- ADDRESS (grouped) -->
          <template #item.province="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.province"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولایت')"
              />
              <v-text-field
                v-model="item.draft.zone"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('زون')"
              />
              <v-text-field
                v-model="item.draft.district"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولسوالی/ناحیه')"
              />
              <v-text-field
                v-model="item.draft.village"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('قریه/ناحیه کوچک')"
              />
            </div>
          </template>

          <template #item.specific_place="{ item }">
            <v-text-field
              v-model="item.draft.specific_place"
              density="compact"
              variant="outlined"
              class="text-group-note"
              :label="t('موقعیت مشخص')"
              hide-details
            />
          </template>
          <template #item.street_number="{ item }">
            <v-text-field
              v-model="item.draft.street_number"
              density="compact"
              variant="outlined"
              class="text-group"
              :label="t('شماره سرک')"
              hide-details
            />
          </template>
          <template #item.coordinates="{ item }">
            <v-text-field
              v-model="item.draft.coordinates"
              density="compact"
              variant="outlined"
              class="text-group"
              :label="t('مختصات')"
              hide-details
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
          <!-- NOTE -->
          <template #item.note="{ item }">
            <v-text-field
              v-model="item.draft.note"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('یادداشت')"
              hide-details
            />
          </template>

          <!-- RESOURCE -->
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
          <template #item.resource_phone="{ item }">
            <v-text-field
              v-model="item.draft.resource_phone"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره منبع')"
              hide-details
              readonly
            />
          </template>

          <!-- ACTIONS -->
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
              {{ t('یک سطر برای ویرایش/ایجاد آماده است') }}
            </div>
          </template>
        </v-data-table>
      </v-card>
    </v-form>

    <!-- Filterable list (separate from editor), with edit hook -->
    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      class="mb-20 mx-2"
      :enableEdit="true"
      @edit="startEdit"
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
  import { ref, computed, onMounted, watch, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useSchoolRepository } from '@/stores/SchoolRepository';

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const drawer = ref(false);
  const repo = useSchoolRepository();

  /* ===== Editor form + snackbar (Fasad style) ===== */
  const editorForm = ref(null);
  const editorValid = ref(false);
  const editorCard = ref(null);
  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (msg) => {
    errorMessage.value = msg;
    showError.value = true;
  };

  /* ===== Per-field errors ===== */
  const itemErrors = ref({});
  const setItemErrors = (item, errors) => (itemErrors.value[item.idOrTmp] = errors || {});
  const clearItemErrors = (item) => {
    if (itemErrors.value[item.idOrTmp]) delete itemErrors.value[item.idOrTmp];
  };
  const hasError = (item, f) => !!itemErrors.value[item.idOrTmp]?.[f];
  const getErrorMessages = (item, f) => itemErrors.value[item.idOrTmp]?.[f] ?? [];

  /* ===== Validation rules ===== */
  const rules = { required: (v) => !!v || t('این فیلد الزامی است') };

  /* ===== One editor row only ===== */
  const rows = ref([]);
  const setBlankEditorRow = () => {
    rows.value = [toRowModel({})];
  };

  /* ===== FilterableTable data ===== */
  const rowsFT = computed(() => repo.Schools || []);
  const filters = ref({});

  /* ===== Resources dropdown ===== */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const found = resources.value.find((r) => r.code_number === item.draft.resource_code);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* ===== Headers ===== */
  const headers = computed(() => [
    { title: t('نام مکتب'), key: 'school_name' },
    { title: t('نوع مکتب'), key: 'school_type' },

    { title: t('نام مدیر (Principle)'), key: 'principle_name' },
    { title: t('نام پدر مدیر'), key: 'principle_f_name' },
    { title: t('شماره مدیر'), key: 'principle_phone' },

    { title: t('معلمان مرد'), key: 'male_teachers_quantity' },
    { title: t('معلمان زن'), key: 'female_teachers_quantity' },
    { title: t('شاگردان پسر'), key: 'male_students_quantity' },
    { title: t('شاگردان دختر'), key: 'female_students_quantity' },

    { title: t('آدرس'), key: 'province' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('یادداشت'), key: 'note' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام مکتب'), key: 'school_name' },
    { title: t('نوع مکتب'), key: 'school_type' },

    { title: t('نام مدیر'), key: 'principle_name' },
    { title: t('نام پدر مدیر'), key: 'principle_f_name' },
    { title: t('شماره مدیر'), key: 'principle_phone' },

    { title: t('معلمان مرد'), key: 'male_teachers_quantity' },
    { title: t('معلمان زن'), key: 'female_teachers_quantity' },
    { title: t('شاگردان پسر'), key: 'male_students_quantity' },
    { title: t('شاگردان دختر'), key: 'female_students_quantity' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی/ناحیه'), key: 'district' },
    { title: t('قریه/ناحیه کوچک'), key: 'village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('یادداشت'), key: 'note' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات مکتب'), keys: ['school_name', 'school_type'] },
    { title: t('مدیر'), keys: ['principle_name', 'principle_f_name', 'principle_phone'] },
    {
      title: t('کارمندان/شاگردان'),
      keys: [
        'male_teachers_quantity',
        'female_teachers_quantity',
        'male_students_quantity',
        'female_students_quantity',
      ],
    },
    { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village'] },
    { title: t('موقعیت/سرک/مختصات'), keys: ['specific_place', 'street_number', 'coordinates'] },
    { title: t('یادداشت'), keys: ['note'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
  ]);

  /* ===== Row model / diff / form-data ===== */
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      school_name: x.school_name ?? '',
      school_type: x.school_type ?? '',

      principle_name: x.principle_name ?? '',
      principle_f_name: x.principle_f_name ?? '',
      principle_phone: x.principle_phone ?? '',

      Province_addr: x.Province_addr ?? '',
      male_teachers_quantity: x.male_teachers_quantity ?? '',
      female_teachers_quantity: x.female_teachers_quantity ?? '',
      male_students_quantity: x.male_students_quantity ?? '',
      female_students_quantity: x.female_students_quantity ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',
      specific_place: x.specific_place ?? '',
      street_number: x.street_number ?? '',
      coordinates: x.coordinates ?? '',

      note: x.note ?? '',

      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base } };
  }

  const EDITABLE_KEYS = [
    'school_name',
    'school_type',
    'principle_name',
    'principle_f_name',
    'principle_phone',
    'male_teachers_quantity',
    'female_teachers_quantity',
    'male_students_quantity',
    'female_students_quantity',
      'Province_addr',
    'province',
    'zone',
    'district',
    'village',
    'specific_place',
    'street_number',
    'coordinates',
    'note',
    'resource_code',
    'resource_phone',
    'resource_id',
  ];

  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    for (const k of EDITABLE_KEYS) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
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

  /* ===== Submit / Save / Delete / Edit / Helpers ===== */
  async function onSubmit(item) {
    if (!item?.draft?.school_name?.trim()) {
      showErrorMessage(t('نام مکتب الزامی است'));
      await nextTick();
      scrollToFirstInvalid();
      return;
    }
    await save(item);
  }

  async function save(item) {
    item.loading = true;
    clearItemErrors(item);
    try {
      if (!item.id) {
        const fd = toFormDataSelective(diffDraft(item));
        const created = await repo.CreateSchool?.(fd);
        if (created?.id) {
          await fetchAll();
          setBlankEditorRow();
          editorForm.value?.resetValidation?.();
        } else {
          await fetchAll();
        }
        return;
      }
      const changed = diffDraft(item);
      if (Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }
      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateSchool?.(item.id, fd);
      if (updated?.id) {
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
    } catch (error) {
      if (error?.response?.data?.errors) {
        setItemErrors(item, error.response.data.errors);
        showErrorMessage(t('لطفاً خطاهای فرم را بررسی کنید'));
      } else {
        showErrorMessage(t('خطا در ذخیره اطلاعات') + ': ' + (error?.message || t('خطای ناشناخته')));
      }
    } finally {
      item.loading = false;
    }
  }

  async function del(item) {
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا مطمئن هستید؟'))) return;
    try {
      await repo.DeleteSchool?.(item.id);
      await fetchAll();
      setBlankEditorRow();
    } catch (e) {
      showErrorMessage(t('خطا در حذف') + ': ' + (e?.message || t('خطای ناشناخته')));
    }
  }

  function clearEditor(item) {
    if (item && item.idOrTmp && itemErrors.value[item.idOrTmp])
      delete itemErrors.value[item.idOrTmp];
    setBlankEditorRow();
    showErrorMessage(t('ورودی‌ها پاک شد'));
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

  async function fetchAll() {
    await repo.FetchSchoolsData?.({ page: 1, itemsPerPage: 100000, search: repo.Search || '' });
  }

  async function startEdit(row) {
    const id = row?.id;
    if (!id) {
      rows.value = [toRowModel(row)];
      return;
    }
    await repo.FetchSchoolData?.(id);
    const single = (Array.isArray(repo.School) ? repo.School[0] : repo.School) || row;
    rows.value = [toRowModel(single)];
    await nextTick();
    const el = editorCard.value?.$el || editorCard.value;
    el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
  }

  /* ===== Init ===== */
  onMounted(async () => {
    await Promise.allSettled([repo.GetResourceCode?.()]);
    await fetchAll();
    setBlankEditorRow();
  });
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

  /* layout like your template */
  .tablebody {
    display: flex !important;
    gap: 2px !important;
  }
  .addr-group {
    display: grid;
    grid-template-columns: repeat(4, minmax(120px, 1fr));
    gap: 2px;
  }
  .text-group {
    display: grid;
    width: 140px;
    gap: 2px;
  }
  .text-group-note {
    display: grid;
    width: 380px;
    gap: 2px;
  }

  /* compact inputs + labels */
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
  
  /* Tight spacing inside grouped blocks */
  .addr-group,
  .addr-group1,
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
  }
</style>
