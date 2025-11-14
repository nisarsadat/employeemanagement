<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('شرکت')" :subtitle="t('ایجاد/ویرایش شرکت')" @toggle-drawer="drawer = !drawer">
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search for FilterableTable -->
    <v-row class="d-flex">
      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetchAll()"
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

    <!-- Single-row editor -->
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
          <template #item.com_name="{ item }">
            <v-text-field
              v-model="item.draft.com_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام شرکت')"
              :rules="[rules.required]"
              hide-details
            />
          </template>

          <template #item.com_type="{ item }">
            <v-text-field
              v-model="item.draft.com_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع شرکت')"
              hide-details
            />
          </template>

          <template #item.product_type="{ item }">
            <v-text-field
              v-model="item.draft.product_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع محصول/خدمت')"
              hide-details
            />
          </template>

          <template #item.com_level="{ item }">
            <v-text-field
              v-model="item.draft.com_level"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('سطح شرکت')"
              hide-details
            />
          </template>

          <template #item.main_owner="{ item }">
            <v-text-field
              v-model="item.draft.main_owner"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مالک اصلی')"
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

          <!-- Address -->
          <template #item.com_province="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.com_province"
                density="compact"
                variant="outlined"
                :label="t('ولایت')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.com_zone"
                density="compact"
                variant="outlined"
                :label="t('زون')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.com_district"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی')"
                hide-details
              />
            </div>
          </template>

          <template #item.com_village="{ item }">
            <v-text-field
              v-model="item.draft.com_village"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('قریه')"
              hide-details
            />
          </template>

          <template #item.com_specific_place="{ item }">
            <v-text-field
              v-model="item.draft.com_specific_place"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('موقعیت مشخص')"
              hide-details
            />
          </template>

          <!-- Coordinates BEFORE note -->
          <template #item.com_coordinates="{ item }">
            <v-text-field
              v-model="item.draft.com_coordinates"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مختصات')"
              hide-details
            />
          </template>

          <!-- Employees -->
          <template #item.male_employee_quantity_inner="{ item }">
            <v-text-field
              v-model="item.draft.male_employee_quantity_inner"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('کارمندان داخلی مرد')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <template #item.female_employee_quantity_inner="{ item }">
            <v-text-field
              v-model="item.draft.female_employee_quantity_inner"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('کارمندان داخلی زن')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <template #item.male_employee_quantity_out="{ item }">
            <v-text-field
              v-model="item.draft.male_employee_quantity_out"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('کارمندان بیرونی مرد')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <template #item.female_employee_quantity_out="{ item }">
            <v-text-field
              v-model="item.draft.female_employee_quantity_out"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('کارمندان بیرونی زن')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <template #item.guard_quantity="{ item }">
            <v-text-field
              v-model="item.draft.guard_quantity"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('تعداد محافظ')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <!-- Resource & Note -->
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
          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="flex items-center gap-1">
              <v-btn
                class="bg-primary"
                variant="outlined"
                :loading="item.loading"
                @click="onSubmit(item)"
              >
                {{ t('ثبت دیتا') }}
              </v-btn>
              <v-btn class="bg-error" variant="outlined" @click="del(item)">
                {{ t('حذف دیتا') }}
              </v-btn>
              <v-btn color="warning" variant="outlined" @click="clearRow()">
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
  </v-container>

  <!-- FilterableTable -->
  <v-container fluid>
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
  </v-container>

  <!-- Snackbars -->
  <v-snackbar v-model="showError" color="error" timeout="4500">
    {{ errorMessage }}
    <template #actions>
      <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
    </template>
  </v-snackbar>
  <v-snackbar v-model="showOk" color="primary" timeout="3000">
    {{ okMessage }}
  </v-snackbar>
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

  /* Single-row editor state */
  const rows = ref([]);
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? 'tmp-1',
      loading: false,

      com_name: x.com_name ?? '',
      com_type: x.com_type ?? '',
      product_type: x.product_type ?? '',
      com_level: x.com_level ?? '',
      main_owner: x.main_owner ?? '',

      responsible_name: x.responsible_name ?? '',
      responsible_f_name: x.responsible_f_name ?? '',
      responsible_position: x.responsible_position ?? '',
      Province_addr: x.Province_addr ?? '',
      responsible_phone: x.responsible_phone ?? '',

      com_province: x.com_province ?? '',
      com_zone: x.com_zone ?? '',
      com_district: x.com_district ?? '',
      com_village: x.com_village ?? '',
      com_specific_place: x.com_specific_place ?? '',
      com_coordinates: x.com_coordinates ?? '',

      male_employee_quantity_inner: x.male_employee_quantity_inner ?? '',
      female_employee_quantity_inner: x.female_employee_quantity_inner ?? '',
      male_employee_quantity_out: x.male_employee_quantity_out ?? '',
      female_employee_quantity_out: x.female_employee_quantity_out ?? '',
      guard_quantity: x.guard_quantity ?? '',

      resource_id: x.resource_id ?? '',
      note: x.note ?? '',
    };
    return { ...base, draft: { ...base } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* FilterableTable dataset */
  const rowsFT = computed(() => repo.Companies || []);
  const filters = ref({});

  /* Snackbars */
  const showError = ref(false),
    showOk = ref(false);
  const errorMessage = ref(''),
    okMessage = ref('');
  function showErrorMessage(msg) {
    errorMessage.value = msg || t('خطا رخ داد');
    showError.value = true;
  }
  function showOkMessage(msg) {
    okMessage.value = msg || t('موفقیت‌آمیز');
    showOk.value = true;
  }

  /* Headers — com_coordinates before note */
  const headers = computed(() => [
    { title: t('نام شرکت'), key: 'com_name' },
    { title: t('نوع شرکت'), key: 'com_type' },
    { title: t('نوع محصول/خدمت'), key: 'product_type' },
    { title: t('سطح شرکت'), key: 'com_level' },
    { title: t('مالک اصلی'), key: 'main_owner' },

    { title: t('مسوول'), key: 'responsible_name' },
    { title: t('نام پدر مسوول'), key: 'responsible_f_name' },
    { title: t('وظیفه/موقف'), key: 'responsible_position' },
    { title: t('شماره تماس مسوول'), key: 'responsible_phone' },

    { title: t('آدرس (۱)'), key: 'com_province' },
    { title: t('قریه'), key: 'com_village' },
    { title: t('موقعیت مشخص'), key: 'com_specific_place' },

    { title: t('مختصات'), key: 'com_coordinates' }, // <-- before note
    { title: t('داخلی مرد'), key: 'male_employee_quantity_inner' },
    { title: t('داخلی زن'), key: 'female_employee_quantity_inner' },
    { title: t('بیرونی مرد'), key: 'male_employee_quantity_out' },
    { title: t('بیرونی زن'), key: 'female_employee_quantity_out' },
    { title: t('تعداد محافظ'), key: 'guard_quantity' },

    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('یادداشت'), key: 'note' },

    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام شرکت'), key: 'com_name' },
    { title: t('نوع شرکت'), key: 'com_type' },
    { title: t('نوع محصول/خدمت'), key: 'product_type' },
    { title: t('سطح شرکت'), key: 'com_level' },
    { title: t('مالک اصلی'), key: 'main_owner' },

    { title: t('مسوول'), key: 'responsible_name' },
    { title: t('نام پدر مسوول'), key: 'responsible_f_name' },
    { title: t('وظیفه/موقف'), key: 'responsible_position' },
    { title: t('شماره تماس مسوول'), key: 'responsible_phone' },

    { title: t('ولایت'), key: 'com_province' },
    { title: t('زون'), key: 'com_zone' },
    { title: t('ولسوالی'), key: 'com_district' },
    { title: t('قریه'), key: 'com_village' },
    { title: t('موقعیت مشخص'), key: 'com_specific_place' },
    { title: t('مختصات'), key: 'com_coordinates' },

    { title: t('داخلی مرد'), key: 'male_employee_quantity_inner' },
    { title: t('داخلی زن'), key: 'female_employee_quantity_inner' },
    { title: t('بیرونی مرد'), key: 'male_employee_quantity_out' },
    { title: t('بیرونی زن'), key: 'female_employee_quantity_out' },
    { title: t('تعداد محافظ'), key: 'guard_quantity' },

    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('یادداشت'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('شرکت'), keys: ['com_name', 'com_type', 'product_type', 'com_level', 'main_owner'] },
    {
      title: t('مسوول'),
      keys: ['responsible_name', 'responsible_f_name', 'responsible_position', 'responsible_phone'],
    },
    {
      title: t('آدرس'),
      keys: [
        'com_province',
        'com_zone',
        'com_district',
        'com_village',
        'com_specific_place',
        'com_coordinates',
      ],
    },
    {
      title: t('کارمندان/محافظ'),
      keys: [
        'male_employee_quantity_inner',
        'female_employee_quantity_inner',
        'male_employee_quantity_out',
        'female_employee_quantity_out',
        'guard_quantity',
      ],
    },
    { title: t('سایر'), keys: ['resource_id', 'note'] },
  ]);

  const editorCard = ref(null);
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      if (typeof repo.FetchCompanyData === 'function') {
        await repo.FetchCompanyData(id);
      } else if (typeof repo.FetchCompaniesData === 'function') {
        await repo.FetchCompaniesData({ page: 1, itemsPerPage: 100000 });
      }
      const single =
        (repo.Company && Array.isArray(repo.Company) ? repo.Company[0] : repo.Company) ||
        (repo.Companies || []).find((r) => r.id === id) ||
        row;

      rows.value = [toRowModel(single)];
      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      console.error('startEdit error', e);
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* Diff + payload helpers */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'com_name',
      'com_type',
      'product_type',
      'com_level',
      'main_owner',
      'responsible_name',
      'responsible_f_name',
      'responsible_position',
      'responsible_phone',
      'com_province',
      'com_zone',
      'com_district',
      'com_village',
      'com_specific_place',
      'com_coordinates',
      'male_employee_quantity_inner',
      'female_employee_quantity_inner',
      'Province_addr',
      'male_employee_quantity_out',
      'female_employee_quantity_out',
      'guard_quantity',
      'resource_id',
      'note',
    ];
    for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    return { changed };
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  /* Validation */
  const editorForm = ref(null);
  const editorValid = ref(false);
  const formKey = ref(0);
  const rules = {
    required: (v) => !!(v && String(v).trim()) || t('این فیلد الزامی است'),
    nonNegativeInt: (v) =>
      v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
    phone: (v) => !v || /^[+]?\d[\d\s\-]{5,}$/.test(String(v)) || t('شماره تماس نادرست است'),
  };
  async function validateForm() {
    const form = editorForm.value;
    if (!form?.validate) return { valid: true };
    const { valid } = await form.validate();
    if (!valid) await nextTick().then(scrollToFirstInvalid);
    return { valid };
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

  /* Save / Delete / Clear */
  async function onSubmit(item) {
    if (!item?.draft?.com_name?.trim()) {
      showErrorMessage(t('نام شرکت الزامی است'));
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
        const created = await repo.CreateCompany?.(fd);
        if (created?.id) {
          showOkMessage(t('ریکارد ایجاد شد'));
          await fetchAll();
          setBlankEditorRow();
        } else {
          await fetchAll();
          setBlankEditorRow();
        }
        return;
      }
      const { changed } = diffDraft(item);
      if (Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }
      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateCompany?.(item.id, fd);
      if (updated?.id) {
        showOkMessage(t('ریکارد بروزرسانی شد'));
        await fetchAll();
        setBlankEditorRow();
      }
    } catch (e) {
      console.error('Save error:', e);
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
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.com_name || '' }))) return;
    try {
      await repo.DeleteCompany?.(item.id);
      await fetchAll();
      showOkMessage(t('ریکارد حذف شد'));
      setBlankEditorRow();
    } catch (e) {
      console.error('Delete error:', e);
      showErrorMessage(t('خطا در حذف'));
    }
  }
  function clearRow() {
    setBlankEditorRow();
    showOkMessage(t('ورودی‌ها پاک شد'));
  }

  /* Load dataset for FilterableTable only */
  async function fetchAll() {
    await Promise.allSettled([
      repo.FetchCompaniesData?.({ page: 1, itemsPerPage: 100000, search: repo.Search }),
    ]);
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
</style>
