<!-- src/views/fifthyFormats/MashkokHomes.vue -->
<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('خانه‌های مشکوک')"
      :subtitle="t('اضافه کردن یک ریکارډ خانه مشکوک')"
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
          density="comfortable"
          :dir="isRtl ? 'rtl' : 'ltr'"
          hide-default-footer
        >
          <!-- LOCATION -->
          <template #item.province="{ item }">
            <v-text-field
              v-model="item.draft.province"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('ولایت')"
              :rules="[rules.required]"
              hide-details
              :error="hasError(item, 'province')"
              :error-messages="getErrorMessages(item, 'province')"
              required
            />
          </template>
          <template #item.zone="{ item }">
            <v-text-field
              v-model="item.draft.zone"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('زون')"
              hide-details
            />
          </template>
          <template #item.district="{ item }">
            <v-text-field
              v-model="item.draft.district"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('ولسوالی')"
              :rules="[rules.required]"
              hide-details
              :error="hasError(item, 'district')"
              :error-messages="getErrorMessages(item, 'district')"
              required
            />
          </template>
          <template #item.village="{ item }">
            <v-text-field
              v-model="item.draft.village"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('قریه/ناحیه')"
              hide-details
            />
          </template>

          <!-- ADDRESS DETAILS -->
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

          <!-- HOUSE INFO -->
          <template #item.home_type="{ item }">
            <v-text-field
              v-model="item.draft.home_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع خانه')"
              hide-details
            />
          </template>
          <template #item.shak_symbols="{ item }">
            <v-text-field
              v-model="item.draft.shak_symbols"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('علایم شک')"
              hide-details
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

          <!-- RESOURCE (id select -> phone auto) -->
          <template #item.resource_id="{ item }">
            <v-autocomplete
              v-model="item.draft.resource_id"
              :items="resources"
              item-title="code_number"
              item-value="id"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('منبع')"
              hide-details
              clearable
              @update:model-value="onResourceSelect(item)"
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

          <!-- No Data -->
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
        title-key="specific_place"
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
      class="mb-20 mx-2"
      :enableEdit="true"
      :enablePeek="true"
      @edit="startEdit"
      @peek="openPeek"
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
  import { ref, onMounted, computed, watch, nextTick, onBeforeUnmount } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useMawadRepository } from '@/stores/MawadRepository';

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));
  const drawer = ref(false);

  const repo = useMawadRepository();

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
    number: (v) => !v || /^[0-9]+$/.test(v) || t('فقط اعداد مجاز است'),
  };

  /* ===== Single editor row ===== */
  const rows = ref([]);
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ===== Data for FilterableTable (all) ===== */
  const rowsFT = computed(() => repo.MashkokHomes || []);

  /* ===== Headers (editor) ===== */
  const headers = computed(() => [
    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه/ناحیه'), key: 'village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('شماره خانه'), key: 'home_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('نوع خانه'), key: 'home_type' },
    { title: t('علایم شک'), key: 'shak_symbols' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('منبع'), key: 'resource_id' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  /* ===== Headers for FilterableTable ===== */
  const headersFT = computed(() => [
    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه/ناحیه'), key: 'village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('شماره خانه'), key: 'home_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('نوع خانه'), key: 'home_type' },
    { title: t('علایم شک'), key: 'shak_symbols' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  /* ===== Top filter groups ===== */
  const topGroups = computed(() => [
    { title: t('موقعیت'), keys: ['province', 'zone', 'district', 'village'] },
    { title: t('آدرس'), keys: ['specific_place', 'street_number', 'home_number', 'coordinates'] },
    { title: t('مشخصات خانه'), keys: ['home_type', 'shak_symbols'] },
    { title: t('منبع/یادداشت'), keys: ['resource_code', 'resource_phone', 'note'] },
  ]);

  /* ===== Resources dropdown -> set phone/code ===== */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceSelect(item) {
    const found = resources.value.find((r) => r.id === item.draft.resource_id);
    item.draft.resource_code = found?.code_number ?? '';
    item.draft.resource_phone = found?.phone_number ?? '';
  }

  /* ===== Row model & diff ===== */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? 'tmp-1',
      loading: false,

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',

      specific_place: x.specific_place ?? '',
      street_number: x.street_number ?? '',
      home_number: x.home_number ?? '',
      coordinates: x.coordinates ?? '',

      home_type: x.home_type ?? '',
      shak_symbols: x.shak_symbols ?? '',

      note: x.note ?? '',

      resource_id: x.resource_id ?? null,
      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
    };
    return { ...base, draft: { ...base } };
  }

  const EDITABLE_KEYS = [
    'province',
    'zone',
    'district',
    'village',
    'specific_place',
    'street_number',
    'home_number',
    'coordinates',
    'home_type',
    'shak_symbols',
    'note',
    'resource_id',
    'resource_code',
    'resource_phone',
  ];
  const REQUIRED_ON_UPDATE = ['province', 'district'];

  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    for (const k of EDITABLE_KEYS) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    for (const k of REQUIRED_ON_UPDATE) {
      if (d[k] !== undefined) changed[k] = d[k];
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

  async function hardResetFormValidation() {
    editorValid.value = false;
    itemErrors.value = {};
    await nextTick();
    editorForm.value?.resetValidation?.();
    formKey.value++;
    await nextTick();
  }

  /* ===== Save / Delete ===== */
  async function save(item) {
    // quick client validation
    if (!item.draft?.province?.trim() || !item.draft?.district?.trim()) {
      showErrorMessage(t('ولایت و ولسوالی الزامی است'));
      return;
    }

    item.loading = true;
    clearItemErrors(item);

    try {
      // CREATE
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateMashkokHome?.(fd);
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
      if (Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }
      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateMashkokHome?.(item.id, fd);
      if (updated && updated.id) {
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
    } catch (error) {
      console.error('Save error:', error);
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

  /* VALIDATED SUBMIT */
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
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا مطمئن هستید؟'))) return;
    try {
      await repo.DeleteMashkokHome?.(item.id);
      await fetchAll();
      setBlankEditorRow();
    } catch (e) {
      console.error('Delete error:', e);
      showErrorMessage(t('خطا در حذف') + ': ' + (e?.message || t('خطای ناشناخته')));
    }
  }

  function clearEditor(item) {
    try {
      if (item && item.idOrTmp && itemErrors.value[item.idOrTmp]) {
        delete itemErrors.value[item.idOrTmp];
      }
      setBlankEditorRow();
      showErrorMessage(t('ورودی‌ها پاک شد'));
    } catch (e) {
      console.error('clearEditor error:', e);
    }
  }

  /* ===== Peek / Fetch one ===== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      await repo.FetchMashkokHomeData?.(id);
      detail.value =
        repo.MashkokHome && Array.isArray(repo.MashkokHome)
          ? repo.MashkokHome[0]
          : repo.MashkokHome;
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
    fetchOne(row.id);
  }

  /* ===== Fetch ALL (no pagination) ===== */
  async function fetchAll() {
    try {
      if (typeof repo.FetchMashkokHomesData === 'function') {
        await repo.FetchMashkokHomesData({
          page: 1,
          itemsPerPage: 100000,
          search: repo.Search || '',
        });
        return;
      }
      await repo.FetchMashkokHomesData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search || '',
      });
    } catch (e) {
      console.error('fetchAll error', e);
    }
  }

  /* ===== Start edit from FilterableTable ===== */
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) {
        rows.value = [toRowModel(row)];
        return;
      }
      await repo.FetchMashkokHomeData?.(id);
      const single =
        repo.MashkokHome && Array.isArray(repo.MashkokHome)
          ? repo.MashkokHome[0]
          : repo.MashkokHome || row;
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
    await Promise.allSettled([repo.GetResourceCode?.()]); // fills repo.resourcecode
    await fetchAll();
    setBlankEditorRow();
  });

  onBeforeUnmount(() => {
    // reserved for cleanup if previews/files are added later
  });
</script>

<style scoped>
  /* Prevent header wrap */
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

  /* Input sizing */
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

  /* Tight spacing like Fasad */
  .text-group,
  .text-group-note {
    margin: -16px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
  }
</style>
