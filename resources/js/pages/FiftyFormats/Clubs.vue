<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('کلب‌ها')"
      :subtitle="t('اضافه کردن یک ریکارډ کلب')"
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

    <!-- Single-row editor — NO pagination/items-per-page -->
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

          <template #item.club_name="{ item }">
            <v-text-field
              v-model="item.draft.club_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام کلب')"
              :rules="[rules.required]"
              :error="hasError(item, 'club_name')"
              :error-messages="getErrorMessages(item, 'club_name')"
              hide-details
              required
            />
          </template>

          <template #item.club_type="{ item }">
            <v-text-field
              v-model="item.draft.club_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع کلب')"
              :error="hasError(item, 'club_type')"
              :error-messages="getErrorMessages(item, 'club_type')"
              hide-details
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

          <template #item.position="{ item }">
            <v-text-field
              v-model="item.draft.position"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه/موقعیت')"
              :error="hasError(item, 'position')"
              :error-messages="getErrorMessages(item, 'position')"
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

          <template #item.cordinate="{ item }">
            <v-text-field
              v-model="item.draft.cordinate"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مختصات')"
              :error="hasError(item, 'cordinate')"
              :error-messages="getErrorMessages(item, 'cordinate')"
              hide-details
            />
          </template>

          <!-- Students -->
          <template #item.am_students="{ item }">
            <v-text-field
              v-model="item.draft.am_students"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('شاگردان صبح')"
              :rules="[rules.nonNegativeInt]"
              :error="hasError(item, 'am_students')"
              :error-messages="getErrorMessages(item, 'am_students')"
              hide-details
            />
          </template>

          <template #item.pm_students="{ item }">
            <v-text-field
              v-model="item.draft.pm_students"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('شاگردان عصر')"
              :rules="[rules.nonNegativeInt]"
              :error="hasError(item, 'pm_students')"
              :error-messages="getErrorMessages(item, 'pm_students')"
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
              :error="hasError(item, 'Province_addr')"
              :error-messages="getErrorMessages(item, 'Province_addr')"
            />
          </template>
          <!-- Resource link -->
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

    <!-- Peek button for details -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loadingOne"
        title-key="club_name"
        :fields="headers"
        :cols="3"
        dir="rtl"
        @fetch="fetchOne"
      />
    </v-container>

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
        :dateKeys="['resource_saved_date']"
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
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';

  const { t, locale } = useI18n();
  const drawer = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const repo = useFasadRepository();

  /* ========= Validation + form ========= */
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

  /* ========= Per-row error bucket ========= */
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

  /* ========= Single editor row ========= */
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
      club_name: x.club_name ?? '',
      club_type: x.club_type ?? '',
      father_name: x.father_name ?? '',
      position: x.position ?? '',
      Province_addr: x.Province_addr ?? '',
      phone_number: x.phone_number ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      specific_place: x.specific_place ?? '',
      cordinate: x.cordinate ?? '',

      am_students: x.am_students ?? '',
      pm_students: x.pm_students ?? '',

      resource_code: x.resource_code ?? r.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? r.phone_number ?? '',
      resource_id: x.resource_id ?? r.id ?? null,
    };
    return { ...base, draft: { ...base } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ========= Filterable dataset ========= */
  const rowsFT = computed(() => repo.Clubs || []);
  const filters = ref({});

  /* ========= Resources dropdown ========= */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone_number = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* ========= Headers ========= */
  const headers = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام کلب'), key: 'club_name' },
    { title: t('نوع کلب'), key: 'club_type' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('وظیفه/موقعیت'), key: 'position' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('موقعیت'), key: 'province' }, // grouped province+zone+district
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('مختصات'), key: 'cordinate' },
    { title: t('شاگردان صبح'), key: 'am_students' },
    { title: t('شاگردان عصر'), key: 'pm_students' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام کلب'), key: 'club_name' },
    { title: t('نوع کلب'), key: 'club_type' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('وظیفه/موقعیت'), key: 'position' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('مختصات'), key: 'cordinate' },
    { title: t('شاگردان صبح'), key: 'am_students' },
    { title: t('شاگردان عصر'), key: 'pm_students' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
  ]);

  const topGroups = computed(() => [
    {
      title: t('مشخصات'),
      keys: ['name', 'club_name', 'club_type', 'father_name', 'position', 'phone_number'],
    },
    { title: t('موقعیت'), keys: ['province', 'zone', 'district', 'specific_place', 'cordinate'] },
    { title: t('شاگردان'), keys: ['am_students', 'pm_students'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
  ]);

  /* ========= Diff & payload ========= */
  const EDITABLE_KEYS = [
    'name',
    'club_name',
    'club_type',
    'father_name',
    'position',
    'phone_number',
    'province',
    'zone',
      'Province_addr',
    'district',
    'specific_place',
    'cordinate',
    'am_students',
    'pm_students',
    'resource_code',
    'resource_phone_number',
    'resource_id',
  ];
  const REQUIRED_ON_UPDATE = ['name', 'club_name'];

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

  /* ========= Snackbars ========= */
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

  /* ========= Save / Submit / Delete / Clear ========= */
  async function onSubmit(item) {
    if (!item?.draft?.name?.trim()) return showErrorMessage(t('نام الزامی است'));
    if (!item?.draft?.club_name?.trim()) return showErrorMessage(t('نام کلب الزامی است'));

    const v = await validateForm();
    if (!v.valid) {
      showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
      return;
    }
    await save(item);
  }

  async function save(item) {
    item.loading = true;
    clearItemErrors(item);
    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateClub?.(fd);
        if (created?.id) {
          showOkMessage(t('کلب با موفقیت ایجاد شد'));
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
      if (Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }

      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateClub?.(item.id, fd);
      if (updated?.id) {
        showOkMessage(t('کلب بروزرسانی شد'));
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
    } catch (e) {
      console.error('Save error:', e);
      if (e?.response?.data?.errors) {
        setItemErrors(item, e.response.data.errors);
        showErrorMessage(t('لطفا خطاهای فرم را بررسی کنید'));
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
      await repo.DeleteClub?.(item.id);
      await fetchAll();
      showOkMessage(t('کلب حذف شد'));
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

  /* ========= Peek / single fetch ========= */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loadingOne.value = true;
    try {
      await repo.FetchClubData?.(id);
      detail.value = repo.Club || [];
    } catch (e) {
      console.error('fetchOne error', e);
      showErrorMessage(t('خطا در دریافت اطلاعات'));
    } finally {
      loadingOne.value = false;
    }
  }
  function openPeek(payload) {
    const row = payload?.raw ?? payload;
    if (!row?.id) return;
    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }

  /* ========= Edit from table ========= */
  const editorCard = ref(null);
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      await repo.FetchClubData?.(id);
      const single = repo.Club && Array.isArray(repo.Club) ? repo.Club[0] : repo.Club || row;
      rows.value = [toRowModel(single || row)];
      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      console.error('startEdit error', e);
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* ========= Data load ========= */
  async function fetchAll() {
    await Promise.allSettled([
      repo.GetResourceCode?.(),
      repo.FetchClubsData?.({ page: 1, itemsPerPage: 100000, search: repo.Search || '' }),
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

  /* Compact labels/inputs */
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
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
  }
</style>
