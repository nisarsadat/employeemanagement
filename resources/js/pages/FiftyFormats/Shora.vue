<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('شورا')"
      :subtitle="t('ایجاد/ویرایش Shora')"
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
          <!-- Basics -->
          <template #item.shora_name="{ item }">
            <v-text-field
              v-model="item.draft.shora_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام شورا')"
              :rules="[rules.required]"
              hide-details
            />
          </template>
          <template #item.shora_goal="{ item }">
            <v-text-field
              v-model="item.draft.shora_goal"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('هدف شورا')"
              hide-details
            />
          </template>
          <template #item.shora_village="{ item }">
            <v-text-field
              v-model="item.draft.shora_village"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('قریه شورا')"
              hide-details
            />
          </template>

          <!-- Metrics -->
          <template #item.people_quantity="{ item }">
            <v-text-field
              v-model="item.draft.people_quantity"
              class="text-group"
              type="number"
              density="compact"
              variant="outlined"
              :label="t('تعداد افراد')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>
          <template #item.week_meeting_quantity="{ item }">
            <v-text-field
              v-model="item.draft.week_meeting_quantity"
              class="text-group"
              type="number"
              density="compact"
              variant="outlined"
              :label="t('جلسات در هفته')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>
          <template #item.regester="{ item }">
            <v-text-field
              v-model="item.draft.regester"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('ثبت/راجستر')"
              hide-details
            />
          </template>
          <template #item.shora_meeting_place="{ item }">
            <v-text-field
              v-model="item.draft.shora_meeting_place"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('محل جلسات شورا')"
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
              :label="t('مسؤول شورا')"
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
          <template #item.responsible_job="{ item }">
            <v-text-field
              v-model="item.draft.responsible_job"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه مسؤول')"
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

          <!-- Supplier -->
          <template #item.supplier_name="{ item }">
            <v-text-field
              v-model="item.draft.supplier_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تمویل‌کننده')"
              hide-details
            />
          </template>
          <template #item.supplier_job="{ item }">
            <v-text-field
              v-model="item.draft.supplier_job"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه تمویل‌کننده')"
              hide-details
            />
          </template>
          <template #item.supplier_phone="{ item }">
            <v-text-field
              v-model="item.draft.supplier_phone"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تمویل‌کننده')"
              :rules="[rules.phone]"
              hide-details
            />
          </template>

          <!-- Other flags -->
          <template #item.emarat_tarfdary="{ item }">
            <v-text-field
              v-model="item.draft.emarat_tarfdary"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('طرفداری امارت')"
              hide-details
            />
          </template>

          <!-- Address (grouped) -->
          <template #item.shora_province="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.shora_province"
                density="compact"
                variant="outlined"
                :label="t('ولایت')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.shora_zone"
                density="compact"
                variant="outlined"
                :label="t('زون')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.shora_district"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی')"
                hide-details
              />
            </div>
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
              auto-grow
              rows="2"
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

      shora_village: x.shora_village ?? '',
      note: x.note ?? '',

      shora_name: x.shora_name ?? '',
      shora_goal: x.shora_goal ?? '',
      people_quantity: x.people_quantity ?? '',
      Province_addr: x.Province_addr ?? '',
      week_meeting_quantity: x.week_meeting_quantity ?? '',
      regester: x.regester ?? '',
      shora_meeting_place: x.shora_meeting_place ?? '',

      responsible_name: x.responsible_name ?? '',
      responsible_f_name: x.responsible_f_name ?? '',
      responsible_job: x.responsible_job ?? '',
      responsible_phone: x.responsible_phone ?? '',

      supplier_name: x.supplier_name ?? '',
      supplier_job: x.supplier_job ?? '',
      supplier_phone: x.supplier_phone ?? '',

      emarat_tarfdary: x.emarat_tarfdary ?? '',

      shora_province: x.shora_province ?? '',
      shora_zone: x.shora_zone ?? '',
      shora_district: x.shora_district ?? '',

      // Resource UI fields
      resource_code: x.resource_code ?? x.resource?.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? x.resource?.phone_number ?? '',
      resource_id: x.resource_id ?? x.resource?.id ?? null,
    };
    return { ...base, draft: { ...base } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* Dataset for FilterableTable */
  const rowsFT = computed(() => repo.Shoras || []);
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
    { title: t('نام شورا'), key: 'shora_name' },
    { title: t('هدف شورا'), key: 'shora_goal' },
    { title: t('قریه شورا'), key: 'shora_village' },

    { title: t('تعداد افراد'), key: 'people_quantity' },
    { title: t('جلسات در هفته'), key: 'week_meeting_quantity' },
    { title: t('ثبت/راجستر'), key: 'regester' },
    { title: t('محل جلسات شورا'), key: 'shora_meeting_place' },

    { title: t('مسؤول شورا'), key: 'responsible_name' },
    { title: t('نام پدر مسؤول'), key: 'responsible_f_name' },
    { title: t('وظیفه مسؤول'), key: 'responsible_job' },
    { title: t('شماره تماس مسؤول'), key: 'responsible_phone' },

    { title: t('تمویل‌کننده'), key: 'supplier_name' },
    { title: t('وظیفه تمویل‌کننده'), key: 'supplier_job' },
    { title: t('شماره تمویل‌کننده'), key: 'supplier_phone' },

    { title: t('طرفداری امارت'), key: 'emarat_tarfdary' },

    { title: t('آدرس شورا'), key: 'shora_province' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    // { title: t('Resource ID'), key: 'resource_id' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* FilterableTable headers */
  const headersFT = computed(() => [
    { title: t('نام شورا'), key: 'shora_name' },
    { title: t('هدف شورا'), key: 'shora_goal' },
    { title: t('قریه شورا'), key: 'shora_village' },
    { title: t('تعداد افراد'), key: 'people_quantity' },
    { title: t('جلسات در هفته'), key: 'week_meeting_quantity' },
    { title: t('ثبت/راجستر'), key: 'regester' },
    { title: t('محل جلسات شورا'), key: 'shora_meeting_place' },

    { title: t('مسؤول شورا'), key: 'responsible_name' },
    { title: t('نام پدر مسؤول'), key: 'responsible_f_name' },
    { title: t('وظیفه مسؤول'), key: 'responsible_job' },
    { title: t('شماره تماس مسؤول'), key: 'responsible_phone' },

    { title: t('تمویل‌کننده'), key: 'supplier_name' },
    { title: t('وظیفه تمویل‌کننده'), key: 'supplier_job' },
    { title: t('شماره تمویل‌کننده'), key: 'supplier_phone' },

    { title: t('طرفداری امارت'), key: 'emarat_tarfdary' },

    { title: t('ولایت'), key: 'shora_province' },
    { title: t('زون'), key: 'shora_zone' },
    { title: t('ولسوالی'), key: 'shora_district' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('یادداشت'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات شورا'), keys: ['shora_name', 'shora_goal', 'shora_village', 'note'] },
    {
      title: t('آمار'),
      keys: ['people_quantity', 'week_meeting_quantity', 'regester', 'shora_meeting_place'],
    },
    {
      title: t('مسؤول شورا'),
      keys: ['responsible_name', 'responsible_f_name', 'responsible_job', 'responsible_phone'],
    },
    { title: t('تمویل‌کننده'), keys: ['supplier_name', 'supplier_job', 'supplier_phone'] },
    { title: t('سایر'), keys: ['emarat_tarfdary'] },
    { title: t('آدرس شورا'), keys: ['shora_province', 'shora_zone', 'shora_district'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number', 'resource_id'] },
  ]);

  const editorCard = ref(null);

  /* Edit flow */
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      if (typeof repo.FetchShoraData === 'function') {
        await repo.FetchShoraData(id);
      } else if (typeof repo.FetchShorasData === 'function') {
        await repo.FetchShorasData({ page: 1, itemsPerPage: 100000 });
      }
      const single =
        (repo.Shora && Array.isArray(repo.Shora) ? repo.Shora[0] : repo.Shora) ||
        (repo.Shoras || []).find((r) => r.id === id) ||
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

  /* Diff + payload helpers — only persisted keys */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'shora_village',
      'note',
      'shora_name',
      'shora_goal',
      'Province_addr',
      'people_quantity',
      'week_meeting_quantity',
      'regester',
      'shora_meeting_place',
      'responsible_name',
      'responsible_f_name',
      'responsible_job',
      'responsible_phone',
      'supplier_name',
      'supplier_job',
      'supplier_phone',
      'emarat_tarfdary',
      'shora_province',
      'shora_zone',
      'shora_district',
      'resource_id',
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
    if (!item?.draft?.shora_name?.trim()) {
      showErrorMessage(t('نام شورا الزامی است'));
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
        const created = await repo.CreateShora?.(fd);
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
      const updated = await repo.UpdateShora?.(item.id, fd);
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
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.shora_name || '' }))) return;
    try {
      await repo.DeleteShora?.(item.id);
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
      repo.FetchShorasData?.({ page: 1, itemsPerPage: 100000, search: repo.Search }),
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
  .pic-thumb {
    display: inline-flex;
    cursor: pointer;
    position: relative;
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

  /* compact spacing in groups */
  .addr-group,
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
  }

  /* picture hover tools */
  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
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
  .pic-overlay :deep(.v-btn) {
    color: #fff;
  }
</style>
