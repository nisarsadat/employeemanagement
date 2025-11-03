<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('کمپلکس/کومپلکس')"
      :subtitle="t('ایجاد/ویرایش Complex')"
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
          <template #item.complex_name="{ item }">
            <v-text-field
              v-model="item.draft.complex_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام کمپلکس')"
              :rules="[rules.required]"
              hide-details
            />
          </template>
          <template #item.activity_type="{ item }">
            <v-text-field
              v-model="item.draft.activity_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع فعالیت')"
              hide-details
            />
          </template>
          <template #item.activity_place="{ item }">
            <v-text-field
              v-model="item.draft.activity_place"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('محل فعالیت')"
              hide-details
            />
          </template>
          <template #item.supplier="{ item }">
            <v-text-field
              v-model="item.draft.supplier"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تمویل‌کننده')"
              hide-details
            />
          </template>

          <!-- Flows (enter/outer) -->
          <template #item.enter_male_quantity="{ item }">
            <v-text-field
              v-model="item.draft.enter_male_quantity"
              class="text-group"
              type="number"
              density="compact"
              variant="outlined"
              :label="t('ورود ذکور')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>
          <template #item.outer_male_quantity="{ item }">
            <v-text-field
              v-model="item.draft.outer_male_quantity"
              class="text-group"
              type="number"
              density="compact"
              variant="outlined"
              :label="t('خروج ذکور')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>
          <template #item.enter_femal_quantity="{ item }">
            <v-text-field
              v-model="item.draft.enter_femal_quantity"
              class="text-group"
              type="number"
              density="compact"
              variant="outlined"
              :label="t('ورود اناث')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>
          <template #item.outer_femal_quantity="{ item }">
            <v-text-field
              v-model="item.draft.outer_femal_quantity"
              class="text-group"
              type="number"
              density="compact"
              variant="outlined"
              :label="t('خروج اناث')"
              :rules="[rules.nonNegativeInt]"
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
              :label="t('مسؤول')"
              hide-details
            />
          </template>
          <template #item.responsible_f_name="{ item }">
            <v-text-field
              v-model="item.draft.responsible_f_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدر مسؤول')"
              hide-details
            />
          </template>
          <template #item.responsible_phone="{ item }">
            <v-text-field
              v-model="item.draft.responsible_phone"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس مسؤول')"
              :rules="[rules.phone]"
              hide-details
            />
          </template>
          <template #item.responsible_country="{ item }">
            <v-text-field
              v-model="item.draft.responsible_country"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کشور مسؤول')"
              hide-details
            />
          </template>

          <!-- Address -->
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
          <template #item.specefic_place="{ item }">
            <v-text-field
              v-model="item.draft.specefic_place"
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
              :label="t('نمبر سرک')"
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

          <!-- Security -->
          <template #item.guard_quantity="{ item }">
            <v-text-field
              v-model="item.draft.guard_quantity"
              class="text-group"
              type="number"
              density="compact"
              variant="outlined"
              :label="t('تعداد محافظ')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <!-- Resource (code → phone → id) -->
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
          <!-- <template #item.resource_id="{ item }">
            <v-text-field
              v-model="item.draft.resource_id"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('Resource ID')"
              hide-details
              readonly
            />
          </template> -->

          <!-- Note -->
          <template #item.note="{ item }">
            <v-textarea
              v-model="item.draft.note"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('یادداشت')"
              hide-details
              rows="2"
              auto-grow
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
                >{{ t('ثبت دیتا') }}</v-btn
              >
              <v-btn class="bg-error" variant="outlined" @click="del(item)">{{
                t('حذف دیتا')
              }}</v-btn>
              <v-btn color="warning" variant="outlined" @click="clearRow()">{{
                t('پاک کردن ورودی‌ها')
              }}</v-btn>
            </div>
          </template>

          <template #no-data>
            <div class="py-8 text-medium-emphasis">{{ t('یک سطر ویرایش/ایجاد آماده است') }}</div>
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
    <template #actions
      ><v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn></template
    >
  </v-snackbar>
  <v-snackbar v-model="showOk" color="primary" timeout="3000">{{ okMessage }}</v-snackbar>
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

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',
      specefic_place: x.specefic_place ?? '',
      street_number: x.street_number ?? '',
      home_number: x.home_number ?? '',

      complex_name: x.complex_name ?? '',
      activity_type: x.activity_type ?? '',
      activity_place: x.activity_place ?? '',
      supplier: x.supplier ?? '',

      enter_male_quantity: x.enter_male_quantity ?? '',
      outer_male_quantity: x.outer_male_quantity ?? '',
      enter_femal_quantity: x.enter_femal_quantity ?? '',
      outer_femal_quantity: x.outer_femal_quantity ?? '',

      responsible_name: x.responsible_name ?? '',
      responsible_f_name: x.responsible_f_name ?? '',
      responsible_phone: x.responsible_phone ?? '',
      responsible_country: x.responsible_country ?? '',

      guard_quantity: x.guard_quantity ?? '',

      // Resource UI fields
      resource_code: x.resource_code ?? x.resource?.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? x.resource?.phone_number ?? '',
      resource_id: x.resource_id ?? x.resource?.id ?? null,

      note: x.note ?? '',
    };
    return { ...base, draft: { ...base } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* Dataset for FilterableTable */
  const rowsFT = computed(() => repo.Complexes || []);
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

  /* Resource dropdown */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone_number = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* Editor headers */
  const headers = computed(() => [
    { title: t('نام کمپلکس'), key: 'complex_name' },
    { title: t('نوع فعالیت'), key: 'activity_type' },
    { title: t('محل فعالیت'), key: 'activity_place' },
    { title: t('تمویل‌کننده'), key: 'supplier' },

    { title: t('ورود ذکور'), key: 'enter_male_quantity' },
    { title: t('خروج ذکور'), key: 'outer_male_quantity' },
    { title: t('ورود اناث'), key: 'enter_femal_quantity' },
    { title: t('خروج اناث'), key: 'outer_femal_quantity' },

    { title: t('مسؤول'), key: 'responsible_name' },
    { title: t('نام پدر مسؤول'), key: 'responsible_f_name' },
    { title: t('شماره مسؤول'), key: 'responsible_phone' },
    { title: t('کشور مسؤول'), key: 'responsible_country' },

    { title: t('آدرس (۱)'), key: 'province' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specefic_place' },
    { title: t('نمبر سرک'), key: 'street_number' },
    { title: t('نمبر خانه'), key: 'home_number' },

    { title: t('تعداد محافظ'), key: 'guard_quantity' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    // { title: t('Resource ID'), key: 'resource_id' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* FilterableTable headers */
  const headersFT = computed(() => [
    { title: t('نام کمپلکس'), key: 'complex_name' },
    { title: t('نوع فعالیت'), key: 'activity_type' },
    { title: t('محل فعالیت'), key: 'activity_place' },
    { title: t('تمویل‌کننده'), key: 'supplier' },

    { title: t('ورود ذکور'), key: 'enter_male_quantity' },
    { title: t('خروج ذکور'), key: 'outer_male_quantity' },
    { title: t('ورود اناث'), key: 'enter_femal_quantity' },
    { title: t('خروج اناث'), key: 'outer_femal_quantity' },

    { title: t('مسؤول'), key: 'responsible_name' },
    { title: t('نام پدر مسؤول'), key: 'responsible_f_name' },
    { title: t('شماره مسؤول'), key: 'responsible_phone' },
    { title: t('کشور مسؤول'), key: 'responsible_country' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specefic_place' },
    { title: t('نمبر سرک'), key: 'street_number' },
    { title: t('نمبر خانه'), key: 'home_number' },

    { title: t('تعداد محافظ'), key: 'guard_quantity' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('یادداشت'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    {
      title: t('مشخصات کمپلکس'),
      keys: ['complex_name', 'activity_type', 'activity_place', 'supplier'],
    },
    {
      title: t('ورود/خروج'),
      keys: [
        'enter_male_quantity',
        'outer_male_quantity',
        'enter_femal_quantity',
        'outer_femal_quantity',
      ],
    },
    {
      title: t('مسؤول'),
      keys: ['responsible_name', 'responsible_f_name', 'responsible_phone', 'responsible_country'],
    },
    {
      title: t('آدرس'),
      keys: [
        'province',
        'zone',
        'district',
        'village',
        'specefic_place',
        'street_number',
        'home_number',
      ],
    },
    { title: t('امنیت'), keys: ['guard_quantity'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number', 'resource_id'] },
    { title: t('سایر'), keys: ['note'] },
  ]);

  const editorCard = ref(null);

  /* Edit flow */
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      if (typeof repo.FetchComplexData === 'function') {
        await repo.FetchComplexData(id);
      } else if (typeof repo.FetchComplexesData === 'function') {
        await repo.FetchComplexesData({ page: 1, itemsPerPage: 100000 });
      }
      const single =
        (repo.Complex && Array.isArray(repo.Complex) ? repo.Complex[0] : repo.Complex) ||
        (repo.Complexes || []).find((r) => r.id === id) ||
        row;

      rows.value = [toRowModel(single)];
      await nextTick();
      (editorCard.value?.$el || editorCard.value)?.scrollIntoView?.({
        behavior: 'smooth',
        block: 'start',
      });
    } catch (e) {
      console.error('startEdit error', e);
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* Diff + payload helpers — only persisted keys */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'province',
      'zone',
      'district',
      'village',
      'specefic_place',
      'street_number',
      'home_number',
      'complex_name',
      'activity_type',
      'activity_place',
      'supplier',
      'enter_male_quantity',
      'outer_male_quantity',
      'enter_femal_quantity',
      'outer_femal_quantity',
      'responsible_name',
      'responsible_f_name',
      'responsible_phone',
      'responsible_country',
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
    if (!item?.draft?.complex_name?.trim()) {
      showErrorMessage(t('نام کمپلکس الزامی است'));
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
        const created = await repo.CreateComplex?.(fd);
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
      const updated = await repo.UpdateComplex?.(item.id, fd);
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
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.complex_name || '' }))) return;
    try {
      await repo.DeleteComplex?.(item.id);
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
      repo.GetResourceCode?.(), // for resource dropdown
      repo.FetchComplexesData?.({ page: 1, itemsPerPage: 100000, search: repo.Search }),
    ]);
  }
  onMounted(async () => {
    await fetchAll();
    setBlankEditorRow();
    repo.GetResourceCode?.();
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
    grid-template-columns: repeat(2, minmax(120px, 1fr));
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
