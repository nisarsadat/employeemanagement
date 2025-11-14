<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('مدرسه ')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید مدرسه')"
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
          <!-- Basic info -->
          <template #item.name="{ item }">
            <v-text-field
              v-model="item.draft.name"
              class="text-group pr-2  "
              density="compact"
              variant="outlined"
              :label="t('نام مدرسه')"
              :rules="[rules.required]"
              hide-details
              :error="hasError(item, 'name')"
              :error-messages="getErrorMessages(item, 'name')"
              required
            />
          </template>
          <template #item.madrasa_type="{ item }">
            <v-text-field
              v-model="item.draft.madrasa_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع مدرسه')"
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
              :label="t('مسؤول: نام')"
              hide-details
            />
          </template>
          <template #item.responsible_f_name="{ item }">
            <v-text-field
              v-model="item.draft.responsible_f_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مسؤول: نام پدر')"
              hide-details
            />
          </template>
          <template #item.responsible_position="{ item }">
            <v-text-field
              v-model="item.draft.responsible_position"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مسؤول: موقف')"
              hide-details
            />
          </template>
          <template #item.responsible_phone="{ item }">
            <v-text-field
              v-model="item.draft.responsible_phone"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مسؤول: شماره تماس')"
              hide-details
            />
          </template>
          <template #item.responsible_main_province="{ item }">
            <v-text-field
              v-model="item.draft.responsible_main_province"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('ولایت اصلی مسؤول')"
              hide-details
            />
          </template>
          <template #item.tarafdari_type_responsible="{ item }">
            <v-text-field
              v-model="item.draft.tarafdari_type_responsible"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع طرفداری مسؤول')"
              hide-details
            />
          </template>

          <!-- Religious & counts -->
          <template #item.mazhab="{ item }">
            <v-text-field
              v-model="item.draft.mazhab"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مذهب')"
              hide-details
            />
          </template>
          <template #item.male_teacher_quantity="{ item }">
            <v-text-field
              v-model="item.draft.male_teacher_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تعداد استادان (ذکور)')"
              hide-details
            />
          </template>
          <template #item.female_teacher_quantity="{ item }">
            <v-text-field
              v-model="item.draft.female_teacher_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تعداد استادان (اناث)')"
              hide-details
            />
          </template>
          <template #item.male_students_quantity="{ item }">
            <v-text-field
              v-model="item.draft.male_students_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تعداد شاگردان (ذکور)')"
              hide-details
            />
          </template>
          <template #item.female_students_quantity="{ item }">
            <v-text-field
              v-model="item.draft.female_students_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تعداد شاگردان (اناث)')"
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
              <v-text-field
                v-model="item.draft.village"
                density="compact"
                variant="outlined"
                :label="t('قریه/ناحیه')"
                hide-details
              />
            </div>
          </template>

          <!-- Location details -->
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

          <!-- Note -->
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
            <div class="py-8 text-medium-emphasis">{{ t('یک سطر ویرایش/ایجاد آماده است') }}</div>
          </template>
        </v-data-table>
      </v-card>
    </v-form>

    <!-- Filterable Table -->
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
      :enablePeek="false"
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
  import { ref, onMounted, watch, computed, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
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
  const editorCard = ref(null);
  const showError = ref(false);
  const errorMessage = ref('');
  function showErrorMessage(m) {
    errorMessage.value = m;
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
    return !!itemErrors.value[item.idOrTmp]?.[field];
  }
  function getErrorMessages(item, field) {
    return itemErrors.value[item.idOrTmp]?.[field] ?? [];
  }

  /* ===== Rules ===== */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
  };

  /* ===== Headers ===== */
  const headers = computed(() => [
    { title: t('نام مدرسه'), key: 'name' },
    { title: t('نوع مدرسه'), key: 'madrasa_type' },

    { title: t('مسؤول: نام'), key: 'responsible_name' },
    { title: t('مسؤول: نام پدر'), key: 'responsible_f_name' },
    { title: t('مسؤول: موقف'), key: 'responsible_position' },
    { title: t('مسؤول: شماره تماس'), key: 'responsible_phone' },
    { title: t('ولایت اصلی مسؤول'), key: 'responsible_main_province' },
    { title: t('نوع طرفداری مسؤول'), key: 'tarafdari_type_responsible' },

    { title: t('مذهب'), key: 'mazhab' },
    { title: t('استادان (ذکور)'), key: 'male_teacher_quantity' },
    { title: t('استادان (اناث)'), key: 'female_teacher_quantity' },
    { title: t('شاگردان (ذکور)'), key: 'male_students_quantity' },
    { title: t('شاگردان (اناث)'), key: 'female_students_quantity' },

    { title: t('آدرس'), key: 'province' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('یادداشت'), key: 'note' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام مدرسه'), key: 'name' },
    { title: t('نوع مدرسه'), key: 'madrasa_type' },

    { title: t('مسؤول: نام'), key: 'responsible_name' },
    { title: t('مسؤول: نام پدر'), key: 'responsible_f_name' },
    { title: t('مسؤول: موقف'), key: 'responsible_position' },
    { title: t('مسؤول: شماره تماس'), key: 'responsible_phone' },
    { title: t('ولایت اصلی مسؤول'), key: 'responsible_main_province' },
    { title: t('نوع طرفداری مسؤول'), key: 'tarafdari_type_responsible' },

    { title: t('مذهب'), key: 'mazhab' },
    { title: t('استادان (ذکور)'), key: 'male_teacher_quantity' },
    { title: t('استادان (اناث)'), key: 'female_teacher_quantity' },
    { title: t('شاگردان (ذکور)'), key: 'male_students_quantity' },
    { title: t('شاگردان (اناث)'), key: 'female_students_quantity' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه/ناحیه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('یادداشت'), key: 'note' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  const topGroups = computed(() => [
    { title: t('مدرسه'), keys: ['name', 'madrasa_type'] },
    {
      title: t('مسؤول مدرسه'),
      keys: [
        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'responsible_main_province',
        'tarafdari_type_responsible',
      ],
    },
    {
      title: t('دینی و احصائیه'),
      keys: [
        'mazhab',
        'male_teacher_quantity',
        'female_teacher_quantity',
        'male_students_quantity',
        'female_students_quantity',
      ],
    },
    { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village'] },
    { title: t('موقعیت/سرک/مختصات'), keys: ['specific_place', 'street_number', 'coordinates'] },
    { title: t('یادداشت/منبع'), keys: ['note', 'resource_code', 'resource_phone'] },
  ]);

  /* ===== Filterable data ===== */
  const rowsFT = computed(() => repo.Madrasas || []);
  const filters = ref({});

  /* ===== Resources dropdown -> code/phone + id ===== */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* ===== Main rows ===== */
  const rows = ref([]);
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ===== Row model & diff ===== */
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      picture: x.picture ?? null,

      name: x.name ?? '',
      madrasa_type: x.madrasa_type ?? '',

      responsible_name: x.responsible_name ?? '',
      responsible_f_name: x.responsible_f_name ?? '',
      responsible_position: x.responsible_position ?? '',
      responsible_phone: x.responsible_phone ?? '',
      responsible_main_province: x.responsible_main_province ?? '',
      tarafdari_type_responsible: x.tarafdari_type_responsible ?? '',

      mazhab: x.mazhab ?? '',
      male_teacher_quantity: x.male_teacher_quantity ?? '',
      female_teacher_quantity: x.female_teacher_quantity ?? '',
      male_students_quantity: x.male_students_quantity ?? '',
      female_students_quantity: x.female_students_quantity ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',
      specific_place: x.specific_place ?? '',
      Province_addr: x.Province_addr ?? '',
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
    'picture',
    'name',
    'madrasa_type',
    'responsible_name',
    'responsible_f_name',
    'responsible_position',
    'responsible_phone',
    'responsible_main_province',
    'tarafdari_type_responsible',
    'mazhab',
    'male_teacher_quantity',
    'female_teacher_quantity',
    'male_students_quantity',
    'female_students_quantity',
    'province',
    'zone',
      'Province_addr',
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
  const REQUIRED_ON_UPDATE = ['name'];

  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    for (const k of EDITABLE_KEYS) {
      const prev = item[k];
      const next = d[k];
      if (next instanceof File) {
        if (!(prev instanceof File)) changed[k] = next;
        continue;
      }
      if ((next ?? '') !== (prev ?? '')) changed[k] = next;
    }
    for (const k of REQUIRED_ON_UPDATE) {
      if (d[k] !== undefined) changed[k] = d[k];
    }
    return changed;
  }

  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v !== undefined && v !== null) fd.append(k, v);
    });
    return fd;
  }

  /* ===== Save / Delete / Submit ===== */
  async function save(item) {
    if (!item?.draft?.name?.trim()) {
      showErrorMessage(t('نام مدرسه الزامی است'));
      await nextTick();
      scrollToFirstInvalid();
      return;
    }
    item.loading = true;
    clearItemErrors(item);
    try {
      // CREATE
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        await repo.CreateMadrasa?.(fd);
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
        return;
      }
      // UPDATE
      const changed = diffDraft(item);
      if (Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }
      const fd = toFormDataSelective(changed);
      await repo.UpdateMadrasa?.(item.id, fd);
      await fetchAll();
      setBlankEditorRow();
      editorForm.value?.resetValidation?.();
    } catch (error) {
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

  async function onSubmit(item) {
    const { valid } = await editorForm.value.validate();
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
      input?.focus?.();
    }
  }

  async function del(item) {
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا مطمئن هستید؟'))) return;
    try {
      await repo.DeleteMadrasa?.(item.id);
      await fetchAll();
      setBlankEditorRow();
    } catch (e) {
      showErrorMessage(t('خطا در حذف') + ': ' + (e?.message || t('خطای ناشناخته')));
    }
  }

  async function startEdit(row) {
    const id = row?.id;
    if (!id) return;
    try {
      await repo.FetchMadrasaData?.(id);
      const single = Array.isArray(repo.Madrasa) ? repo.Madrasa[0] : repo.Madrasa || row;
      rows.value = [toRowModel(single || row)];
      await nextTick();
      (editorCard.value?.$el || editorCard.value)?.scrollIntoView?.({
        behavior: 'smooth',
        block: 'start',
      });
    } catch (e) {
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* ===== Fetch & init ===== */
  async function fetchAll() {
    await repo.FetchMadrasasData?.({ page: 1, itemsPerPage: 100000, search: repo.Search || '' });
  }
  onMounted(async () => {
    await Promise.allSettled([repo.GetResourceCode?.()]);
    await fetchAll();
    setBlankEditorRow();
  });
</script>

<style scoped>
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }

  /* layout like Fasad */
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

  /* compact spacing like Fasad */
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
