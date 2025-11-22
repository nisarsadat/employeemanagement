<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('مجاهدینو')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید مجاهدینو')"
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

    <!-- Single-row validated editor (Fasad style) -->
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
          <!-- Identity -->
          <template #item.nickname="{ item }">
            <v-text-field
              v-model="item.draft.nickname"
              class="text-group pr-2"
              density="compact"
              variant="outlined"
              :label="t('تخلص')"
              hide-details
            />
          </template>

          <template #item.name="{ item }">
            <v-text-field
              v-model="item.draft.name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام')"
              :rules="[rules.required]"
              hide-details
              :error="hasError(item, 'name')"
              :error-messages="getErrorMessages(item, 'name')"
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
              hide-details
            />
          </template>

          <!-- Main address (grouped) -->
          <template #item.main_province="{ item }">
            <div class="addr-group3">
              <v-text-field
                v-model="item.draft.main_province"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولایت اصلی')"
              />
              <v-text-field
                v-model="item.draft.main_district"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولسوالی اصلی')"
              />
              <v-text-field
                v-model="item.draft.main_village"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('قریه/ناحیه اصلی')"
              />
            </div>
          </template>

          <!-- Current address (grouped) -->
          <template #item.now_province="{ item }">
            <div class="addr-group4">
              <v-text-field
                v-model="item.draft.now_province"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولایت فعلی')"
              />
              <v-text-field
                v-model="item.draft.now_zone"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('زون فعلی')"
              />
              <v-text-field
                v-model="item.draft.now_district"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولسوالی فعلی')"
              />
              <v-text-field
                v-model="item.draft.now_village"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('قریه/ناحیه فعلی')"
              />
            </div>
          </template>

          <!-- Current place details -->
          <template #item.now_specific_place="{ item }">
            <v-text-field
              v-model="item.draft.now_specific_place"
              density="compact"
              variant="outlined"
              class="text-group-note"
              :label="t('موقعیت مشخص فعلی')"
              hide-details
            />
          </template>

          <template #item.now_street_number="{ item }">
            <v-text-field
              v-model="item.draft.now_street_number"
              density="compact"
              variant="outlined"
              class="text-group"
              :label="t('شماره سرک فعلی')"
              hide-details
            />
          </template>

          <template #item.now_home_number="{ item }">
            <v-text-field
              v-model="item.draft.now_home_number"
              density="compact"
              variant="outlined"
              class="text-group"
              :label="t('شماره خانه فعلی')"
              hide-details
            />
          </template>

          <!-- Duty / job / organ -->
          <template #item.duty_type="{ item }">
            <v-text-field
              v-model="item.draft.duty_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع وظیفه')"
              hide-details
            />
          </template>

          <template #item.now_job="{ item }">
            <v-text-field
              v-model="item.draft.now_job"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه فعلی')"
              hide-details
            />
          </template>

          <template #item.organ="{ item }">
            <v-text-field
              v-model="item.draft.organ"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('اداره/ارگان')"
              hide-details
            />
          </template>

          <!-- Contact / province addr / note -->
          <template #item.phone_number="{ item }">
            <v-text-field
              v-model="item.draft.phone_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس')"
              :rules="[rules.number]"
              hide-details
              :error="hasError(item, 'phone_number')"
              :error-messages="getErrorMessages(item, 'phone_number')"
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

          <!-- Resource code (autocomplete) + phone (readonly) -->
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

          <!-- Actions -->
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
        title-key="name"
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
    >
      <!-- ✅ custom cell for the 'map' column -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.code_number"
          type="mujahedino"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>
    </FilterableTable>

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
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
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import { useBadmashanoRepository } from '@/stores/BadmashanoRepository';

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const drawer = ref(false);
  const repo = useBadmashanoRepository();

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
    itemErrors.value[item.idOrTmp] = errors || {};
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
    selectRequired: (label) => (v) => !!v || t('{label} الزامی است', { label }),
    number: (v) => !v || /^[0-9]+$/.test(v) || t('فقط اعداد مجاز است'),
  };

  /* ===== Single editor row ===== */
  const rows = ref([]);
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ===== ALL data for FilterableTable ===== */
  const rowsFT = computed(() => (Array.isArray(repo.Mujahedinos) ? repo.Mujahedinos : []));

  /* ===== Headers (editor table) ===== */
  const headers = computed(() => [
    { title: t('تخلص'), key: 'nickname' },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },

    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک فعلی'), key: 'now_street_number' },
    { title: t('شماره خانه فعلی'), key: 'now_home_number' },

    { title: t('نوع وظیفه'), key: 'duty_type' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('اداره/ارگان'), key: 'organ' },

    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  /* ===== Headers for FilterableTable ===== */
  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('تخلص'), key: 'nickname' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک فعلی'), key: 'now_street_number' },
    { title: t('شماره خانه فعلی'), key: 'now_home_number' },

    { title: t('نوع وظیفه'), key: 'duty_type' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('اداره/ارگان'), key: 'organ' },

    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('یادداشت'), key: 'note' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

  /* ===== Top filter groups ===== */
  const filters = ref({});
  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'nickname', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    {
      title: t('موقعیت/خانه'),
      keys: ['now_specific_place', 'now_street_number', 'now_home_number'],
    },
    { title: t('وظیفه/اداره'), keys: ['duty_type', 'now_job', 'organ'] },
    { title: t('تماس/سایر'), keys: ['phone_number', 'Province_addr', 'note'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
  ]);

  /* ===== Resources dropdown (code/phone + resource_id) ===== */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* ===== Row model & diff ===== */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      nickname: x.nickname ?? '',
      name: x.name ?? '',
      father_name: x.father_name ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      Province_addr: x.Province_addr ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',
      now_specific_place: x.now_specific_place ?? '',
      now_street_number: x.now_street_number ?? '',
      now_home_number: x.now_home_number ?? '',

      duty_type: x.duty_type ?? '',
      now_job: x.now_job ?? '',
      organ: x.organ ?? '',
      phone_number: x.phone_number ?? '',
      note: x.note ?? '',
      coordinates: x.coordinates ?? '',

      resource_code: x.resource_code ?? x.resource?.code_number ?? '',
      resource_phone: x.resource_phone ?? x.resource?.phone_number ?? '',
      resource_id: x.resource_id ?? x.resource_id ?? null,
    };
    return { ...base, draft: { ...base } };
  }

  const EDITABLE_KEYS = [
    'nickname',
    'name',
    'father_name',
    'main_province',
    'main_district',
    'main_village',
    'now_province',
    'now_zone',
    'Province_addr',
    'now_district',
    'now_village',
    'now_specific_place',
    'now_street_number',
    'now_home_number',
    'duty_type',
    'now_job',
    'organ',
    'phone_number',
    'note',
    'coordinates',
    'resource_code',
    'resource_phone',
    'resource_id',
  ];
  const REQUIRED_ON_UPDATE = ['name'];

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
    if (!item.draft?.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
      return;
    }
    item.loading = true;
    clearItemErrors(item);

    try {
      // CREATE
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateMujahedino?.(fd);
        if (!created?.id) {
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
      const updated = await repo.UpdateMujahedino?.(item.id, fd);
      if (updated?.id) {
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
    } catch (error) {
      console.error('Save error:', error);
      if (error?.response?.data?.errors) {
        setItemErrors(item, error.response.data.errors);
        showErrorMessage(t('لطفا خطاهای فرم را بررسی کنید'));
      } else {
        showErrorMessage(t('خطا در ذخیره اطلاعات') + ': ' + (error?.message || t('خطای ناشناخته')));
      }
    } finally {
      item.loading = false;
    }
  }

  // VALIDATED SUBMIT
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
    const root = editorCard.value?.$el || editorCard.value || document;
    const first = root.querySelector(
      '.v-input--error, [aria-invalid="true"], .v-messages__message'
    );
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
      await repo.DeleteMujahedino?.(item.id);
      await fetchAll();
      setBlankEditorRow();
    } catch (e) {
      console.error('Delete error:', e);
      showErrorMessage(t('خطا در حذف') + ': ' + (e?.message || t('خطای ناشناخته')));
    }
  }

  function clearEditor(item) {
    try {
      if (item && item.idOrTmp && itemErrors.value[item.idOrTmp])
        delete itemErrors.value[item.idOrTmp];
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
      await repo.FetchMujahedinoData?.(id);
      detail.value = Array.isArray(repo.Mujahedino)
        ? repo.Mujahedino[0] || null
        : repo.Mujahedino || null;
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
    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }

  /* ===== Fetch ALL (no pagination) ===== */
  async function fetchAll() {
    try {
      await (repo.FetchMujahedinosData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search || '',
      }) ?? Promise.resolve());
    } catch (e) {
      console.error('fetchAll error', e);
    }
  }

  /* ===== Start edit from FilterableTable ===== */
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      await repo.FetchMujahedinoData?.(id);
      const single = Array.isArray(repo.Mujahedino) ? repo.Mujahedino[0] : repo.Mujahedino || row;
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
    await Promise.allSettled([repo.GetResourceCode?.()]);
    await fetchAll();
    setBlankEditorRow();
  });

  onBeforeUnmount(() => {
    // reserved for cleanup if we later add previews/files
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

  /* Grouped inputs */
  .addr-group4 {
    display: grid;
    grid-template-columns: repeat(4, minmax(120px, 1fr));
    gap: 4px;
  }
  .addr-group3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(120px, 1fr));
    gap: 4px;
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
  .addr-group3,
  .addr-group4,
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
  }
</style>
