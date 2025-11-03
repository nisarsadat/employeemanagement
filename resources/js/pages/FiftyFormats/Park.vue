<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('پارک')" :subtitle="t('ایجاد/ویرایش Park')" @toggle-drawer="drawer = !drawer">
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
          <template #item.park_name="{ item }">
            <v-text-field
              v-model="item.draft.park_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پارک')"
              :rules="[rules.required]"
              hide-details
            />
          </template>
          <template #item.park_type="{ item }">
            <v-text-field
              v-model="item.draft.park_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع پارک')"
              hide-details
            />
          </template>
          <template #item.dayly_clientele="{ item }">
            <v-text-field
              v-model="item.draft.dayly_clientele"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('مراجعین روزانه')"
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
          <template #item.responsible_possition="{ item }">
            <v-text-field
              v-model="item.draft.responsible_possition"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه/موقف مسؤول')"
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
              <!-- no district in model -->
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

          <!-- Staff -->
          <template #item.employee_quantity="{ item }">
            <v-text-field
              v-model="item.draft.employee_quantity"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('تعداد کارمندان')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <!-- Coordinates BEFORE notes -->
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

          <!-- Notes -->
          <template #item.notes="{ item }">
            <v-textarea
              v-model="item.draft.notes"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('یادداشت‌ها')"
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

      park_name: x.park_name ?? '',
      park_type: x.park_type ?? '',
      dayly_clientele: x.dayly_clientele ?? '',

      responsible_name: x.responsible_name ?? '',
      responsible_f_name: x.responsible_f_name ?? '',
      responsible_phone: x.responsible_phone ?? '',
      responsible_possition: x.responsible_possition ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      village: x.village ?? '',
      specefic_place: x.specefic_place ?? '',
      coordinates: x.coordinates ?? '',

      employee_quantity: x.employee_quantity ?? '',

      // Resource UI
      resource_code: x.resource_code ?? x.resource?.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? x.resource?.phone_number ?? '',
      resource_id: x.resource_id ?? x.resource?.id ?? null,

      notes: x.notes ?? '',
    };
    return { ...base, draft: { ...base } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* FilterableTable dataset */
  const rowsFT = computed(() => repo.Parks || []); // note: repo.Parks
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

  /* Editor headers — coordinates before notes */
  const headers = computed(() => [
    { title: t('نام پارک'), key: 'park_name' },
    { title: t('نوع پارک'), key: 'park_type' },
    { title: t('مراجعین روزانه'), key: 'dayly_clientele' },

    { title: t('مسؤول'), key: 'responsible_name' },
    { title: t('نام پدر مسؤول'), key: 'responsible_f_name' },
    { title: t('شماره تماس مسؤول'), key: 'responsible_phone' },
    { title: t('وظیفه/موقف مسؤول'), key: 'responsible_possition' },

    { title: t('آدرس (۱)'), key: 'province' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specefic_place' },

    { title: t('تعداد کارمندان'), key: 'employee_quantity' },

    { title: t('مختصات'), key: 'coordinates' }, // <-- before notes

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    // { title: t('Resource ID'), key: 'resource_id' },

    { title: t('یادداشت‌ها'), key: 'notes' },

    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* FilterableTable headers */
  const headersFT = computed(() => [
    { title: t('نام پارک'), key: 'park_name' },
    { title: t('نوع پارک'), key: 'park_type' },
    { title: t('مراجعین روزانه'), key: 'dayly_clientele' },

    { title: t('مسؤول'), key: 'responsible_name' },
    { title: t('نام پدر مسؤول'), key: 'responsible_f_name' },
    { title: t('شماره تماس مسؤول'), key: 'responsible_phone' },
    { title: t('وظیفه/موقف مسؤول'), key: 'responsible_possition' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specefic_place' },

    { title: t('مختصات'), key: 'coordinates' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('تعداد کارمندان'), key: 'employee_quantity' },

    { title: t('یادداشت‌ها'), key: 'notes' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات پارک'), keys: ['park_name', 'park_type', 'dayly_clientele', 'coordinates'] },
    {
      title: t('مسؤول پارک'),
      keys: [
        'responsible_name',
        'responsible_f_name',
        'responsible_phone',
        'responsible_possition',
      ],
    },
    { title: t('آدرس'), keys: ['province', 'zone', 'village', 'specefic_place'] },
    { title: t('کارمندان'), keys: ['employee_quantity'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number', 'resource_id'] },
    { title: t('سایر'), keys: ['notes'] },
  ]);

  const editorCard = ref(null);

  /* Edit flow */
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      if (typeof repo.FetchParkData === 'function') {
        await repo.FetchParkData(id);
      } else if (typeof repo.FetchParksData === 'function') {
        await repo.FetchParksData({ page: 1, itemsPerPage: 100000 });
      }
      const single =
        (repo.Park && Array.isArray(repo.Park) ? repo.Park[0] : repo.Park) ||
        (repo.Parks || []).find((r) => r.id === id) ||
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
      'park_name',
      'park_type',
      'dayly_clientele',
      'responsible_name',
      'responsible_f_name',
      'responsible_phone',
      'responsible_possition',
      'province',
      'zone',
      'village',
      'specefic_place',
      'coordinates',
      'employee_quantity',
      'resource_id',
      'notes',
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
    if (!item?.draft?.park_name?.trim()) {
      showErrorMessage(t('نام پارک الزامی است'));
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
        const created = await repo.CreatePark?.(fd);
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
      const updated = await repo.UpdatePark?.(item.id, fd);
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
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.park_name || '' }))) return;
    try {
      await repo.DeletePark?.(item.id);
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
      repo.FetchParksData?.({ page: 1, itemsPerPage: 100000, search: repo.Search }),
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
