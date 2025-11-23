<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('ادارات دولتی')"
      :subtitle="t('ایجاد/ویرایش GovernmentPlace')"
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
          <template #item.government_place_name="{ item }">
            <v-text-field
              v-model="item.draft.government_place_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام اداره دولتی')"
              :rules="[rules.required]"
              hide-details
            />
          </template>
          <template #item.daily_clientele="{ item }">
            <v-text-field
              v-model="item.draft.daily_clientele"
              class="text-group"
              type="number"
              density="compact"
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
              :label="t('مسؤول اداره')"
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

          <!-- Details / Security -->
          <template #item.tafseel="{ item }">
            <v-textarea
              v-model="item.draft.tafseel"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('تفصیل')"
              hide-details
              rows="2"
              auto-grow
            />
          </template>
          <template #item.amnyatee_khala="{ item }">
            <v-text-field
              v-model="item.draft.amnyatee_khala"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('خلای امنیتی')"
              hide-details
            />
          </template>
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
          <template #item.resource_id="{ item }">
            <v-text-field
              v-model="item.draft.resource_id"
              class="text-group"
              type="number"
              density="compact"
              variant="outlined"
              :label="t('Resource ID')"
              hide-details
              readonly
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
    >
      <!-- ✅ custom cell for the 'map' column -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.code_number"
          type="g-p"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>
    </FilterableTable>
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
  import FlyToMapButton from '../../components/FlyToMapButton.vue';
  import PeekButton from '@/components/PeekButton.vue';

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

      government_place_name: x.government_place_name ?? '',
      daily_clientele: x.daily_clientele ?? '',

      responsible_name: x.responsible_name ?? '',
      responsible_f_name: x.responsible_f_name ?? '',
      Province_addr: x.Province_addr ?? '',
      responsible_possition: x.responsible_possition ?? '',
      responsible_phone: x.responsible_phone ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',
      specefic_place: x.specefic_place ?? '',
      coordinates: x.coordinates ?? '',

      tafseel: x.tafseel ?? '',
      amnyatee_khala: x.amnyatee_khala ?? '',
      guard_quantity: x.guard_quantity ?? '',

      // Resource UI fields
      resource_code: x.resource_code ?? x.resource?.code_number ?? '',
      resource_phone: x.resource_phone ?? x.resource?.phone_number ?? '',
      resource_id: x.resource_id ?? x.resource?.id ?? null,

      note: x.note ?? '',
    };
    return { ...base, draft: { ...base } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* FilterableTable dataset */
  const rowsFT = computed(() => repo.GovernmentPlaces || []);
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
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* Editor headers — coordinates before note */
  const headers = computed(() => [
    { title: t('نام اداره دولتی'), key: 'government_place_name' },
    { title: t('مراجعین روزانه'), key: 'daily_clientele' },

    { title: t('مسؤول اداره'), key: 'responsible_name' },
    { title: t('نام پدر مسؤول'), key: 'responsible_f_name' },
    { title: t('وظیفه/موقف مسؤول'), key: 'responsible_possition' },
    { title: t('شماره تماس مسؤول'), key: 'responsible_phone' },

    { title: t('آدرس (۱)'), key: 'province' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specefic_place' },

    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('خلای امنیتی'), key: 'amnyatee_khala' },
    { title: t('تعداد محافظ'), key: 'guard_quantity' },

    { title: t('مختصات'), key: 'coordinates' }, // <-- before note

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* FilterableTable headers */
  const headersFT = computed(() => [
    { title: t('نام اداره دولتی'), key: 'government_place_name' },
    { title: t('مراجعین روزانه'), key: 'daily_clientele' },

    { title: t('مسؤول اداره'), key: 'responsible_name' },
    { title: t('نام پدر مسؤول'), key: 'responsible_f_name' },
    { title: t('وظیفه/موقف مسؤول'), key: 'responsible_possition' },
    { title: t('شماره تماس مسؤول'), key: 'responsible_phone' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specefic_place' },

    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('خلای امنیتی'), key: 'amnyatee_khala' },
    { title: t('تعداد محافظ'), key: 'guard_quantity' },

    { title: t('مختصات'), key: 'coordinates' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('یادداشت'), key: 'note' },
    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['government_place_name', 'daily_clientele', 'coordinates'] },
    {
      title: t('مسؤول اداره'),
      keys: [
        'responsible_name',
        'responsible_f_name',
        'responsible_possition',
        'responsible_phone',
      ],
    },
    { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village', 'specefic_place'] },
    { title: t('امنیت/تفصیل'), keys: ['tafseel', 'amnyatee_khala', 'guard_quantity'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone', 'resource_id'] },
    { title: t('سایر'), keys: ['note'] },
  ]);

  const editorCard = ref(null);

  /* Edit flow */
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      if (typeof repo.FetchGovernmentPlaceData === 'function') {
        await repo.FetchGovernmentPlaceData(id);
      } else if (typeof repo.FetchGovernmentPlacesData === 'function') {
        await repo.FetchGovernmentPlacesData({ page: 1, itemsPerPage: 100000 });
      }
      const single =
        (repo.GovernmentPlace && Array.isArray(repo.GovernmentPlace)
          ? repo.GovernmentPlace[0]
          : repo.GovernmentPlace) ||
        (repo.GovernmentPlaces || []).find((r) => r.id === id) ||
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
      'government_place_name',
      'daily_clientele',
      'responsible_name',
      'responsible_f_name',
      'responsible_possition',
      'responsible_phone',
      'province',
      'zone',
      'district',
      'village',
      'Province_addr',
      'specefic_place',
      'coordinates',
      'tafseel',
      'amnyatee_khala',
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
    if (!item?.draft?.government_place_name?.trim()) {
      showErrorMessage(t('نام اداره دولتی الزامی است'));
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
        const created = await repo.CreateGovernmentPlace?.(fd);
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
      const updated = await repo.UpdateGovernmentPlace?.(item.id, fd);
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
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.government_place_name || '' })))
      return;
    try {
      await repo.DeleteGovernmentPlace?.(item.id);
      await fetchAll();
      showOkMessage(t('ریکارد حذف شد'));
      setBlankEditorRow();
    } catch (e) {
      console.error('Delete error', e);
      showErrorMessage(t('خطا در حذف'));
    }
  }
  function clearRow() {
    setBlankEditorRow();
    showOkMessage(t('ورودی‌ها پاک شد'));
  }
  /* ===== Peek / Fetch ===== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      await repo.FetchGovernmentPlaceData?.(id);
      detail.value = repo.GovernmentPlace || [];
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
    fetchOne(row.id);
  }

  /* Load dataset for FilterableTable only */
  async function fetchAll() {
    await Promise.allSettled([
      repo.GetResourceCode?.(), // for resource dropdown
      repo.FetchGovernmentPlacesData?.({ page: 1, itemsPerPage: 100000, search: repo.Search }),
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
