<!-- src/views/hada/HadaBusStops.vue -->
<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('ایستگاه‌های هدا')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید ایستگاه هدا')"
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
          <template #item.name="{ item }">
            <v-text-field
              v-model="item.draft.name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام ایستگاه')"
              :rules="[rules.required]"
              hide-details
              :error="hasError(item, 'name')"
              :error-messages="getErrorMessages(item, 'name')"
              required
            />
          </template>
          <template #item.hada_type="{ item }">
            <v-text-field
              v-model="item.draft.hada_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع هدا')"
              hide-details
            />
          </template>

          <!-- RESPONSIBLE -->
          <template #item.responsible_name="{ item }">
            <v-text-field
              v-model="item.draft.responsible_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام مسئول')"
              hide-details
            />
          </template>
          <template #item.responsible_f_name="{ item }">
            <v-text-field
              v-model="item.draft.responsible_f_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدر مسئول')"
              hide-details
            />
          </template>
          <template #item.responsible_position="{ item }">
            <v-text-field
              v-model="item.draft.responsible_position"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه مسئول')"
              hide-details
            />
          </template>
          <template #item.responsible_phone="{ item }">
            <v-text-field
              v-model="item.draft.responsible_phone"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره مسئول')"
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
                :label="t('ولسوالی')"
              />
              <v-text-field
                v-model="item.draft.village"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('قریه')"
              />
            </div>
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

    <!-- Filterable list (no repo changes, no extra API calls) -->
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
  import { useSchoolRepository } from '@/stores/SchoolRepository'; // <-- unchanged per your request

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const drawer = ref(false);
  const repo = useSchoolRepository(); // <-- unchanged

  /* ===== Editor form + snackbar ===== */
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

  /* ===== FilterableTable data (no repo changes) ===== */
  const rowsFT = computed(() => repo.HadoBusStops || []); // uses your existing state
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
    { title: t('نام ایستگاه'), key: 'name' },
    { title: t('نوع هدا'), key: 'hada_type' },

    { title: t('نام مسئول'), key: 'responsible_name' },
    { title: t('نام پدر مسئول'), key: 'responsible_f_name' },
    { title: t('وظیفه مسئول'), key: 'responsible_position' },
    { title: t('شماره مسئول'), key: 'responsible_phone' },

    { title: t('آدرس'), key: 'province' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام ایستگاه'), key: 'name' },
    { title: t('نوع هدا'), key: 'hada_type' },

    { title: t('نام مسئول'), key: 'responsible_name' },
    { title: t('نام پدر مسئول'), key: 'responsible_f_name' },
    { title: t('وظیفه مسئول'), key: 'responsible_position' },
    { title: t('شماره مسئول'), key: 'responsible_phone' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه'), key: 'village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('یادداشت'), key: 'note' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'hada_type'] },
    {
      title: t('مسئول'),
      keys: ['responsible_name', 'responsible_f_name', 'responsible_position', 'responsible_phone'],
    },
    { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village'] },
    { title: t('موقعیت'), keys: ['specific_place', 'street_number', 'coordinates'] },
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

      name: x.name ?? '',
      hada_type: x.hada_type ?? '',

      responsible_name: x.responsible_name ?? '',
      responsible_f_name: x.responsible_f_name ?? '',
      responsible_position: x.responsible_position ?? '',
      responsible_phone: x.responsible_phone ?? '',

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
    'name',
    'hada_type',
    'responsible_name',
    'responsible_f_name',
    'responsible_position',
    'responsible_phone',
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

  /* ===== Submit / Save / Delete / Edit ===== */
  async function onSubmit(item) {
    if (!item?.draft?.name?.trim()) {
      showErrorMessage(t('نام ایستگاه الزامی است'));
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
        const created = await repo.CreateHadoBusStop?.(fd);
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
      const updated = await repo.UpdateHadoBusStop?.(item.id, fd);
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
      await repo.DeleteHadoBusStop?.(item.id);
      await fetchAll();
      setBlankEditorRow();
    } catch (e) {
      showErrorMessage(t('خطا در حذف') + ': ' + (e?.message || t('خطای ناشناخته')));
    }
  }

  async function startEdit(row) {
    // No repo/function changes: just load the selected row into the single editor
    rows.value = [toRowModel(row || {})];
    await nextTick();
    const el = editorCard.value?.$el || editorCard.value;
    el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
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

  /* ===== Fetch (unchanged function names) ===== */
  async function fetchAll() {
    await repo.FetchHadoBusStopsData?.({
      page: 1,
      itemsPerPage: 100000,
      search: repo.Search || '',
    });
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
