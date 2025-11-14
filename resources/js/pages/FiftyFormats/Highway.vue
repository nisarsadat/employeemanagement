<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('سرک/شاهراه')"
      :subtitle="t('ایجاد/ویرایش شاهراه')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search (for FilterableTable dataset) -->
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

    <!-- Single-row editor (create/edit) — NO pagination/footers/add-row -->
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
          <!-- Name -->
          <template #item.high_way_name="{ item }">
            <v-text-field
              v-model="item.draft.high_way_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام شاهراه')"
              :rules="[rules.required]"
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

          <!-- Resource ID (hidden in headersFT, stored for API) -->
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

      high_way_name: x.high_way_name ?? '',
      Province_addr: x.Province_addr ?? '',
      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',
      specific_place: x.specific_place ?? '',
      coordinates: x.coordinates ?? '',

      // Resource UI fields (derived):
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
  const rowsFT = computed(() => repo.Highways || []);
  const filters = ref({});

  /* Snackbars */
  const showError = ref(false);
  const errorMessage = ref('');
  const showOk = ref(false);
  const okMessage = ref('');
  function showErrorMessage(msg) {
    errorMessage.value = msg || t('خطا رخ داد');
    showError.value = true;
  }
  function showOkMessage(msg) {
    okMessage.value = msg || t('موفقیت‌آمیز');
    showOk.value = true;
  }

  /* Resource dropdown data + mapping */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone_number = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* Table headers (editor) — includes resource_code + resource_phone_number; coordinates before note */
  const headers = computed(() => [
    { title: t('نام شاهراه'), key: 'high_way_name' },

    { title: t('آدرس (۱)'), key: 'province' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },

    { title: t('مختصات'), key: 'coordinates' }, // <-- before note
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('یادداشت'), key: 'note' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* FilterableTable headers (list view) */
  const headersFT = computed(() => [
    { title: t('نام شاهراه'), key: 'high_way_name' },
    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    // We usually keep resource_id hidden in list, but include if you want:
    // { title: t('Resource ID'), key: 'resource_id' },

    { title: t('یادداشت'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('شاهراه'), keys: ['high_way_name'] },
    {
      title: t('آدرس'),
      keys: ['province', 'zone', 'district', 'village', 'specific_place', 'coordinates'],
    },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number', 'resource_id'] },
    { title: t('سایر'), keys: ['note'] },
  ]);

  const editorCard = ref(null);

  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      if (typeof repo.FetchHighwayData === 'function') {
        await repo.FetchHighwayData(id);
      } else if (typeof repo.FetchHighwaysData === 'function') {
        await repo.FetchHighwaysData({ page: 1, itemsPerPage: 100000 });
      }
      const single =
        (repo.Highway && Array.isArray(repo.Highway) ? repo.Highway[0] : repo.Highway) ||
        (repo.Highways || []).find((r) => r.id === id) ||
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
  /* Only send persisted backend fields; resource_code/phone are UI-only */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'high_way_name',
      'province',
      'zone',
      'district',
      'Province_addr',
      'village',
      'specific_place',
      'coordinates',
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

  /* Validation helpers */
  const editorForm = ref(null);
  const editorValid = ref(false);
  const formKey = ref(0);
  const rules = {
    required: (v) => !!(v && String(v).trim()) || t('این فیلد الزامی است'),
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
    if (!item?.draft?.high_way_name?.trim()) {
      showErrorMessage(t('نام شاهراه الزامی است'));
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
        const created = await repo.CreateHighway?.(fd);
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
      const updated = await repo.UpdateHighway?.(item.id, fd);
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
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.high_way_name || '' }))) return;
    try {
      await repo.DeleteHighway?.(item.id);
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
      repo.GetResourceCode?.(), // <--- load resources for dropdown
      repo.FetchHighwaysData?.({ page: 1, itemsPerPage: 100000, search: repo.Search }),
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
