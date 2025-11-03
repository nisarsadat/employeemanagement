<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('مهاجرت')"
      :subtitle="t('اضافه کردن یک ریکارډ مهاجرت')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search (dataset for FilterableTable) -->
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

    <!-- Single-row editor (create/edit) — NO pagination/footers -->
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
          <template #item.name="{ item }">
            <v-text-field
              v-model="item.draft.name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام')"
              :rules="[rules.required]"
              :error="hasError(item, 'name')"
              :error-messages="getErrorMessages(item, 'name')"
              hide-details
              required
            />
          </template>
          <template #item.father_name="{ item }">
            <v-text-field
              v-model="item.draft.father_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدر')"
              :error="hasError(item, 'father_name')"
              :error-messages="getErrorMessages(item, 'father_name')"
              hide-details
            />
          </template>
          <template #item.scientific_level="{ item }">
            <v-text-field
              v-model="item.draft.scientific_level"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('سطح علمی')"
              :error="hasError(item, 'scientific_level')"
              :error-messages="getErrorMessages(item, 'scientific_level')"
              hide-details
            />
          </template>

          <!-- Main address -->
          <template #item.main_province="{ item }">
            <div class="addr-group1">
              <v-text-field
                v-model="item.draft.main_province"
                density="compact"
                variant="outlined"
                :label="t('ولایت اصلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.main_district"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی اصلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.main_village"
                density="compact"
                variant="outlined"
                :label="t('قریه/ناحیه اصلی')"
                hide-details
              />
            </div>
          </template>

          <!-- Now address -->
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
              <!-- kept backend key: now_vinage -->
              <v-text-field
                v-model="item.draft.now_vinage"
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
              :error="hasError(item, 'specific_place')"
              :error-messages="getErrorMessages(item, 'specific_place')"
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
              :error="hasError(item, 'street_number')"
              :error-messages="getErrorMessages(item, 'street_number')"
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
              :error="hasError(item, 'home_number')"
              :error-messages="getErrorMessages(item, 'home_number')"
              hide-details
            />
          </template>
          <template #item.cordinates="{ item }">
            <v-text-field
              v-model="item.draft.cordinates"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مختصات')"
              :error="hasError(item, 'cordinates')"
              :error-messages="getErrorMessages(item, 'cordinates')"
              hide-details
            />
          </template>

          <!-- Migration target / counts / contact -->
          <template #item.country="{ item }">
            <v-text-field
              v-model="item.draft.country"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کشور')"
              :error="hasError(item, 'country')"
              :error-messages="getErrorMessages(item, 'country')"
              hide-details
            />
          </template>
          <template #item.male_amount="{ item }">
            <v-text-field
              v-model="item.draft.male_amount"
              type="number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تعداد مرد')"
              :rules="[rules.nonNegativeInt]"
              :error="hasError(item, 'male_amount')"
              :error-messages="getErrorMessages(item, 'male_amount')"
              hide-details
            />
          </template>
          <template #item.female_amount="{ item }">
            <v-text-field
              v-model="item.draft.female_amount"
              type="number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تعداد زن')"
              :rules="[rules.nonNegativeInt]"
              :error="hasError(item, 'female_amount')"
              :error-messages="getErrorMessages(item, 'female_amount')"
              hide-details
            />
          </template>
          <template #item.phone_number="{ item }">
            <v-text-field
              v-model="item.draft.phone_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس')"
              :rules="[rules.phone]"
              :error="hasError(item, 'phone_number')"
              :error-messages="getErrorMessages(item, 'phone_number')"
              hide-details
            />
          </template>

          <!-- Resource -->
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

          <!-- Note -->
          <template #item.note="{ item }">
            <v-text-field
              v-model="item.draft.note"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('ملاحظات')"
              :error="hasError(item, 'note')"
              :error-messages="getErrorMessages(item, 'note')"
              hide-details
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
      </v-card>
    </v-form>

    <!-- FilterableTable (list + edit/peek triggers) -->
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
  </v-container>
</template>

<script setup>
  import { ref, onMounted, computed, watch, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';

  const { t, locale } = useI18n();
  const drawer = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const repo = useFasadRepository();

  /* ====== Validation & per-row errors ====== */
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
      if (input?.focus) input.focus();
    }
  }

  const itemErrors = ref({});
  function setItemErrors(item, errors) {
    itemErrors.value[item.idOrTmp] = errors || {};
  }
  function clearItemErrors(item) {
    if (itemErrors.value[item.idOrTmp]) delete itemErrors.value[item.idOrTmp];
  }
  function hasError(item, field) {
    return !!itemErrors.value[item.idOrTmp]?.[field];
  }
  function getErrorMessages(item, field) {
    return itemErrors.value[item.idOrTmp]?.[field] ?? [];
  }

  /* ====== Single-row editor state ====== */
  const rows = ref([]);
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  function toRowModel(x = {}) {
    const r = x.resource || {};
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      scientific_level: x.scientific_level ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_district: x.now_district ?? '',
      now_vinage: x.now_vinage ?? '', // keep backend key

      specific_place: x.specific_place ?? '',
      street_number: x.street_number ?? '',
      home_number: x.home_number ?? '',
      cordinates: x.cordinates ?? '',

      country: x.country ?? '',
      male_amount: x.male_amount ?? '',
      female_amount: x.female_amount ?? '',
      phone_number: x.phone_number ?? '',

      resource_code: x.resource_code ?? r.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? r.phone_number ?? '',
      resource_id: x.resource_id ?? r.id ?? null,

      note: x.note ?? '',
    };
    return { ...base, draft: { ...base } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ====== Filterable dataset ====== */
  const rowsFT = computed(() => repo.Mahajerats || []);
  const filters = ref({});

  /* ====== Resources dropdown ====== */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone_number = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* ====== Headers ====== */
  const headers = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('سطح علمی'), key: 'scientific_level' },

    { title: t('آدرس اصلی'), key: 'main_province' }, // grouped (province/district/village)
    { title: t('آدرس فعلی'), key: 'now_province' }, // grouped (province/zone/district/vinage)

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('مختصات'), key: 'cordinates' },

    { title: t('کشور'), key: 'country' },
    { title: t('مرد'), key: 'male_amount' },
    { title: t('زن'), key: 'female_amount' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('ملاحظات'), key: 'note' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('سطح علمی'), key: 'scientific_level' },
    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },
    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_vinage' }, // backend key
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('مختصات'), key: 'cordinates' },
    { title: t('کشور'), key: 'country' },
    { title: t('مرد'), key: 'male_amount' },
    { title: t('زن'), key: 'female_amount' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    { title: t('ملاحظات'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'father_name', 'scientific_level'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_vinage'] },
    {
      title: t('منزل/موقعیت'),
      keys: ['specific_place', 'street_number', 'home_number', 'cordinates'],
    },
    {
      title: t('مقصد/تعداد/تماس'),
      keys: ['country', 'male_amount', 'female_amount', 'phone_number'],
    },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['note'] },
  ]);

  /* ====== Diff & payload ====== */
  const EDITABLE_KEYS = [
    'name',
    'father_name',
    'scientific_level',
    'main_province',
    'main_district',
    'main_village',
    'now_province',
    'now_zone',
    'now_district',
    'now_vinage',
    'specific_place',
    'street_number',
    'home_number',
    'cordinates',
    'country',
    'male_amount',
    'female_amount',
    'phone_number',
    'resource_code',
    'resource_phone_number',
    'resource_id',
    'note',
  ];
  const REQUIRED_ON_UPDATE = ['name'];

  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    for (const k of EDITABLE_KEYS) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    for (const k of REQUIRED_ON_UPDATE) if (d[k] !== undefined) changed[k] = d[k];
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

  /* ====== Snackbars ====== */
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

  /* ====== Save / Submit / Delete / Clear ====== */
  async function onSubmit(item) {
    if (!item?.draft?.name?.trim()) return showErrorMessage(t('نام الزامی است'));
    const v = await validateForm();
    if (!v.valid) return showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
    await save(item);
  }

  async function save(item) {
    item.loading = true;
    clearItemErrors(item);
    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateMahajerat?.(fd);
        if (created?.id) {
          showOkMessage(t('ریکارډ مهاجرت ایجاد شد'));
          await fetchAll();
          setBlankEditorRow();
          editorForm.value?.resetValidation?.();
        } else {
          await fetchAll();
          setBlankEditorRow();
        }
        return;
      }

      const changed = diffDraft(item);
      if (Object.keys(changed).length === 0)
        return showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));

      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateMahajerat?.(item.id, fd);
      if (updated?.id) {
        showOkMessage(t('ریکارډ مهاجرت بروزرسانی شد'));
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
    } catch (e) {
      console.error('Save error:', e);
      if (e?.response?.data?.errors) {
        setItemErrors(item, e.response.data.errors);
        showErrorMessage(t('لطفاً خطاهای فرم را بررسی کنید'));
      } else {
        showErrorMessage(t('خطا در ذخیره داده'));
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
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name }))) return;

    try {
      await repo.DeleteMahajerat?.(item.id);
      await fetchAll();
      showOkMessage(t('ریکارډ مهاجرت حذف شد'));
      setBlankEditorRow();
    } catch (e) {
      console.error('Delete error:', e);
      showErrorMessage(t('خطا در حذف'));
    }
  }

  function clearEditor(item) {
    if (item && itemErrors.value[item.idOrTmp]) delete itemErrors.value[item.idOrTmp];
    setBlankEditorRow();
    showOkMessage(t('ورودی‌ها پاک شد'));
  }

  /* ====== Edit/Peek from table ====== */
  const editorCard = ref(null);
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      await repo.FetchMahajeratData?.(id);
      const single =
        repo.Mahajerat && Array.isArray(repo.Mahajerat) ? repo.Mahajerat[0] : repo.Mahajerat || row;
      rows.value = [toRowModel(single || row)];
      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      console.error('startEdit error', e);
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }
  function openPeek(payload) {
    // If you have a PeekButton component, wire it here; for now FilterableTable emits 'peek'
    // You can open a drawer/dialog and show payload.raw
    console.debug('peek', payload);
  }

  /* ====== Load dataset for table ====== */
  async function fetchAll() {
    await Promise.allSettled([
      repo.GetResourceCode?.(),
      repo.FetchMahajeratsData?.({ page: 1, itemsPerPage: 100000, search: repo.Search || '' }),
    ]);
  }

  onMounted(async () => {
    await fetchAll();
    setBlankEditorRow();
  });
  repo.GetResourceCode?.();
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
    grid-template-columns: repeat(4, minmax(120px, 1fr));
    gap: 2px;
  }
  .addr-group1 {
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

  /* Compact inputs like your standard */
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
