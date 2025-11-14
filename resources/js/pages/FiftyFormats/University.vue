<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('دانشگاه')"
      :subtitle="t('ایجاد/ویرایش دانشگاه')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search (feeds FilterableTable dataset) -->
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

    <!-- Single-row validated editor (no pagination/footer) -->
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
          <!-- Identity -->
          <template #item.uni_name="{ item }">
            <v-text-field
              v-model="item.draft.uni_name"
              class="text-group pr-2"
              density="compact"
              variant="outlined"
              :label="t('نام دانشگاه')"
              :rules="[rules.required]"
              hide-details
              required
            />
          </template>
          <template #item.uni_type="{ item }">
            <v-text-field
              v-model="item.draft.uni_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع دانشگاه')"
              hide-details
            />
          </template>

          <!-- Responsible -->
          <template #item.responsible_name="{ item }">
            <v-text-field
              v-model="item.draft.responsible_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مسوول')"
              hide-details
            />
          </template>
          <template #item.responsible_f_name="{ item }">
            <v-text-field
              v-model="item.draft.responsible_f_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدر مسوول')"
              hide-details
            />
          </template>
          <template #item.responsible_position="{ item }">
            <v-text-field
              v-model="item.draft.responsible_position"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه/موقف')"
              hide-details
            />
          </template>
          <template #item.responsible_phone="{ item }">
            <v-text-field
              v-model="item.draft.responsible_phone"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس مسوول')"
              :rules="[rules.phone]"
              hide-details
            />
          </template>

          <!-- Teachers / Students -->
          <template #item.male_teachers_quantity="{ item }">
            <v-text-field
              v-model="item.draft.male_teachers_quantity"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('استادان مرد')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>
          <template #item.female_teachers_quantity="{ item }">
            <v-text-field
              v-model="item.draft.female_teachers_quantity"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('استادان زن')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>
          <template #item.male_students_quantity="{ item }">
            <v-text-field
              v-model="item.draft.male_students_quantity"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('شاگردان مرد')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>
          <template #item.female_students_quantity="{ item }">
            <v-text-field
              v-model="item.draft.female_students_quantity"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('شاگردان زن')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <!-- Address (grouped) -->
          <template #item.province="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.province"
                density="compact"
                variant="outlined"
                :label="t('ولایت')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.zone"
                density="compact"
                variant="outlined"
                :label="t('زون')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.district"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی')"
                hide-details
              />
            </div>
          </template>
          <template #item.village="{ item }">
            <v-text-field
              v-model="item.draft.village"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('قریه')"
              hide-details
            />
          </template>
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
              :label="t('شماره سرک')"
              hide-details
            />
          </template>
          <template #item.home_number="{ item }">
            <v-text-field
              v-model="item.draft.home_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره خانه')"
              hide-details
            />
          </template>

          <!-- Coordinates BEFORE note -->
          <template #item.coordinates="{ item }">
            <v-text-field
              v-model="item.draft.coordinates"
              class="text-group"
              density="compact"
              variant="outlined"
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
            />
          </template>
          <!-- Resource (as provided) -->
          <template #item.resource_id="{ item }">
            <v-text-field
              v-model="item.draft.resource_id"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('Resource ID')"
              hide-details
            />
          </template>

          <!-- Note -->
          <template #item.note="{ item }">
            <v-textarea
              v-model="item.draft.note"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('یادداشت')"
              hide-details
              auto-grow
              rows="2"
            />
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="flex items-center gap-1">
              <v-btn class="bg-primary" variant="outlined" type="submit" :loading="item.loading">
                {{ t('ثبت دیتا') }}
              </v-btn>
              <v-btn class="bg-error" variant="outlined" @click="del(item)">
                {{ t('حذف دیتا') }}
              </v-btn>
              <v-btn color="warning" variant="outlined" @click="clearRow">
                {{ t('پاک کردن ورودی‌ها') }}
              </v-btn>
            </div>
          </template>

          <template #no-data>
            <div class="py-8 text-medium-emphasis">{{ t('یک سطر ویرایش/ایجاد آماده است') }}</div>
          </template>
        </v-data-table>
      </v-card>
    </v-form>

    <!-- FilterableTable (list/peek/edit) -->
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
      :dateKeys="[]"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    />

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="4500">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>

    <!-- Success Snackbar -->
    <v-snackbar v-model="showOk" color="primary" timeout="3000">
      {{ okMessage }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, computed, watch, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useWakilanoRepository } from '@/stores/WakilanoRepository';

  const { t, locale } = useI18n();
  const drawer = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const repo = useWakilanoRepository();

  /* ===== Editor state ===== */
  const rows = ref([]);
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? 'tmp-1',
      loading: false,

      uni_name: x.uni_name ?? '',
      Province_addr: x.Province_addr ?? '',
      uni_type: x.uni_type ?? '',

      responsible_name: x.responsible_name ?? '',
      responsible_f_name: x.responsible_f_name ?? '',
      responsible_position: x.responsible_position ?? '',
      responsible_phone: x.responsible_phone ?? '',

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
      home_number: x.home_number ?? '',

      coordinates: x.coordinates ?? '',
      resource_id: x.resource_id ?? '',
      note: x.note ?? '',
    };
    return { ...base, draft: { ...base } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ===== Filterable dataset ===== */
  const rowsFT = computed(() => repo.Universities || []);
  const filters = ref({});

  /* ===== Headers ===== */
  const headers = computed(() => [
    { title: t('نام دانشگاه'), key: 'uni_name' },
    { title: t('نوع دانشگاه'), key: 'uni_type' },

    { title: t('مسوول'), key: 'responsible_name' },
    { title: t('نام پدر مسوول'), key: 'responsible_f_name' },
    { title: t('وظیفه/موقف'), key: 'responsible_position' },
    { title: t('شماره تماس مسوول'), key: 'responsible_phone' },

    { title: t('استادان مرد'), key: 'male_teachers_quantity' },
    { title: t('استادان زن'), key: 'female_teachers_quantity' },
    { title: t('شاگردان مرد'), key: 'male_students_quantity' },
    { title: t('شاگردان زن'), key: 'female_students_quantity' },

    { title: t('آدرس (۱)'), key: 'province' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('شماره خانه'), key: 'home_number' },

    { title: t('مختصات'), key: 'coordinates' }, // before note
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('Resource ID'), key: 'resource_id' },
    { title: t('یادداشت'), key: 'note' },

    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام دانشگاه'), key: 'uni_name' },
    { title: t('نوع دانشگاه'), key: 'uni_type' },

    { title: t('مسوول'), key: 'responsible_name' },
    { title: t('نام پدر مسوول'), key: 'responsible_f_name' },
    { title: t('وظیفه/موقف'), key: 'responsible_position' },
    { title: t('شماره تماس مسوول'), key: 'responsible_phone' },

    { title: t('استادان مرد'), key: 'male_teachers_quantity' },
    { title: t('استادان زن'), key: 'female_teachers_quantity' },
    { title: t('شاگردان مرد'), key: 'male_students_quantity' },
    { title: t('شاگردان زن'), key: 'female_students_quantity' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('شماره خانه'), key: 'home_number' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('مختصات'), key: 'coordinates' },
    { title: t('Resource ID'), key: 'resource_id' },
    { title: t('یادداشت'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('دانشگاه'), keys: ['uni_name', 'uni_type'] },
    {
      title: t('مسوول'),
      keys: ['responsible_name', 'responsible_f_name', 'responsible_position', 'responsible_phone'],
    },
    {
      title: t('کارمندان/شاگردان'),
      keys: [
        'male_teachers_quantity',
        'female_teachers_quantity',
        'male_students_quantity',
        'female_students_quantity',
      ],
    },
    {
      title: t('آدرس'),
      keys: [
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
      ],
    },
    { title: t('سایر'), keys: ['coordinates', 'resource_id', 'note'] },
  ]);

  /* ===== Form + rules ===== */
  const editorForm = ref(null);
  const editorValid = ref(false);
  const formKey = ref(0);
  const rules = {
    required: (v) => !!(v && String(v).trim()) || t('این فیلد الزامی است'),
    nonNegativeInt: (v) =>
      v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
    phone: (v) => !v || /^[+]?\d[\d\s\-]{5,}$/.test(String(v)) || t('شماره تماس نادرست است'),
  };

  /* ===== Snackbars ===== */
  const showError = ref(false),
    errorMessage = ref('');
  const showOk = ref(false),
    okMessage = ref('');
  function showErrorMessage(msg) {
    errorMessage.value = msg || t('خطا رخ داد');
    showError.value = true;
  }
  function showOkMessage(msg) {
    okMessage.value = msg || t('موفقیت‌آمیز');
    showOk.value = true;
  }

  /* ===== Helpers ===== */
  const editorCard = ref(null);
  function scrollToFirstInvalid() {
    const root = editorCard.value?.$el || editorCard.value || document;
    const first = root.querySelector(
      '.v-input--error, [aria-invalid="true"], .v-messages__message'
    );
    if (first) {
      first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      const input = first.querySelector('input, textarea, .v-field__input');
      input?.focus?.();
    }
  }
  async function validateForm() {
    const { valid } = (await editorForm.value?.validate?.()) ?? { valid: true };
    if (!valid) scrollToFirstInvalid();
    return { valid };
  }

  /* ===== Diff + payload ===== */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const KEYS = [
      'uni_name',
      'uni_type',
      'responsible_name',
      'responsible_f_name',
      'responsible_position',
      'responsible_phone',
      'male_teachers_quantity',
      'female_teachers_quantity',
      'male_students_quantity',
      'female_students_quantity',
      'province',
      'zone',
      'district',
      'Province_addr',
      'village',
      'specific_place',
      'street_number',
      'home_number',
      'coordinates',
      'resource_id',
      'note',
    ];
    for (const k of KEYS) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    return changed;
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v !== undefined && v !== null) fd.append(k, v);
    });
    return fd;
  }

  /* ===== CRUD ===== */
  async function onSubmit(item) {
    if (!item?.draft?.uni_name?.trim()) {
      showErrorMessage(t('نام دانشگاه الزامی است'));
      return;
    }
    const v = await validateForm();
    if (!v.valid) {
      showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
      return;
    }
    await save(item);
  }

  async function save(item) {
    item.loading = true;
    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateUniversity?.(fd);
        if (created?.id) showOkMessage(t('ریکارد ایجاد شد'));
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
        return;
      }
      const changed = diffDraft(item);
      if (Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }
      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateUniversity?.(item.id, fd);
      if (updated?.id) showOkMessage(t('ریکارد بروزرسانی شد'));
      await fetchAll();
      setBlankEditorRow();
      editorForm.value?.resetValidation?.();
    } catch (e) {
      showErrorMessage(t('خطا در ذخیره داده'));
    } finally {
      item.loading = false;
    }
  }

  async function del(item) {
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.uni_name || '' }))) return;
    try {
      await repo.DeleteUniversity?.(item.id);
      await fetchAll();
      showOkMessage(t('ریکارد حذف شد'));
      setBlankEditorRow();
    } catch (e) {
      showErrorMessage(t('خطا در حذف'));
    }
  }

  function clearRow() {
    setBlankEditorRow();
    showOkMessage(t('ورودی‌ها پاک شد'));
  }

  /* ===== FilterableTable actions ===== */
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      if (typeof repo.FetchUniversityData === 'function') {
        await repo.FetchUniversityData(id);
      } else if (typeof repo.FetchUniversitiesData === 'function') {
        await repo.FetchUniversitiesData({ page: 1, itemsPerPage: 100000 });
      }
      const single =
        (repo.University && Array.isArray(repo.University)
          ? repo.University[0]
          : repo.University) ||
        (repo.Universities || []).find((r) => r.id === id) ||
        row;

      rows.value = [toRowModel(single)];
      await nextTick();
      (editorCard.value?.$el || editorCard.value)?.scrollIntoView?.({
        behavior: 'smooth',
        block: 'start',
      });
    } catch (e) {
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }
  function openPeek(row) {
    /* your Peek handler if needed */
  }

  /* ===== Fetch & init ===== */
  async function fetchAll() {
    await repo.FetchUniversitiesData?.({
      page: 1,
      itemsPerPage: 100000,
      search: repo.Search || '',
    });
  }
  onMounted(async () => {
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

  /* layout like Fasad */
  .addr-group {
    display: grid;
    grid-template-columns: repeat(3, minmax(120px, 1fr));
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

  .flex {
    display: flex;
  }
  .items-center {
    align-items: center;
  }
  .gap-1 {
    gap: 4px;
  }

  .tablebody {
    display: flex !important;
    gap: 2px !important;
  }

  /* compact inputs */
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
