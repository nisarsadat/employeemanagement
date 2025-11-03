<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('افراد مشکوک ')"
      :subtitle="t('اضافه کردن ریکارډ جدید مشکوکات')"
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
          <!-- Picture (picker + preview) -->
          <template #item.picture="{ item }">
            <div class="pic-wrap">
              <input
                class="hidden-file"
                type="file"
                accept="image/*"
                :id="`up-${item.idOrTmp}`"
                @change="(e) => onPickFile(item, e.target.files?.[0] ?? null)"
              />
              <v-btn
                v-if="!item.previewUrl && !item.picture"
                color="primary"
                variant="outlined"
                icon
                @click="triggerFile(item)"
              >
                <v-icon>mdi-plus</v-icon>
              </v-btn>

              <div v-else class="pic-thumb" :title="t('گزینه ها')">
                <v-avatar size="32">
                  <v-img :src="item.previewUrl || item.picture" :alt="t('تصویر')" cover />
                </v-avatar>
                <div class="pic-overlay">
                  <v-btn
                    size="x-small"
                    color="primary"
                    variant="flat"
                    @click.stop="triggerFile(item)"
                  >
                    <v-icon start>mdi-pencil</v-icon>{{ t('ویرایش') }}
                  </v-btn>
                  <v-btn
                    size="x-small"
                    color="secondary"
                    variant="flat"
                    @click.stop="openPreview(item.previewUrl || item.picture)"
                  >
                    <v-icon start>mdi-eye</v-icon>{{ t('نمایش') }}
                  </v-btn>
                </div>
              </div>
            </div>
          </template>

          <!-- Name / father / phone -->
          <template #item.name="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.name"
                density="compact"
                variant="outlined"
                :label="t('نام')"
                :rules="[rules.required]"
                hide-details
                :error="hasError(item, 'name')"
                :error-messages="getErrorMessages(item, 'name')"
                required
              />
              <v-text-field
                v-model="item.draft.father_name"
                density="compact"
                variant="outlined"
                :label="t('نام پدر')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.phone_number"
                density="compact"
                variant="outlined"
                :label="t('شماره تماس')"
                hide-details
              />
            </div>
          </template>

          <!-- Main address -->
          <template #item.main_province="{ item }">
            <div class="addr-group">
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

          <!-- Current address -->
          <template #item.now_province="{ item }">
            <div class="addr-group1">
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
                v-model="item.draft.now_districts"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی‌های فعلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.now_village"
                density="compact"
                variant="outlined"
                :label="t('قریه/ناحیه فعلی')"
                hide-details
              />
            </div>
          </template>

          <template #item.now_specific_place="{ item }">
            <div class="addr-group1">
              <v-text-field
                v-model="item.draft.now_specific_place"
                density="compact"
                variant="outlined"
                :label="t('موقعیت مشخص فعلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.now_street_number"
                density="compact"
                variant="outlined"
                :label="t('شماره سرک فعلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.now_home_number"
                density="compact"
                variant="outlined"
                :label="t('شماره خانه فعلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.coordinates"
                density="compact"
                variant="outlined"
                :label="t('مختصات')"
                hide-details
              />
            </div>
          </template>

          <!-- Jobs / suspicion -->
          <template #item.old_job="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.old_job"
                density="compact"
                variant="outlined"
                :label="t('وظیفه قبلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.now_job"
                density="compact"
                variant="outlined"
                :label="t('وظیفه فعلی')"
                hide-details
              />
              <v-text-field
                v-model="item.draft.mushkokyat_points"
                density="compact"
                variant="outlined"
                :label="t('نقاط مشکوکیت')"
                hide-details
              />
            </div>
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

    <!-- Image preview dialog -->
    <v-dialog v-model="previewOpen" max-width="520">
      <v-card>
        <v-toolbar density="compact" color="primary" :title="t('پیش نمایش')" class="text-white" />
        <v-card-text class="d-flex justify-center">
          <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="480" max-height="420" cover />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn color="primary" variant="flat" @click="previewOpen = false">{{ t('بستن') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
  import { ref, onMounted, watch, computed, nextTick, onBeforeUnmount } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useBadmashanoRepository } from '@/stores/BadmashanoRepository';

  const { t, locale } = useI18n();
  const drawer = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

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

  /* ===== Main rows ===== */
  const rows = ref([]);
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ===== Headers (editor & FT) ===== */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', width: 120, sortable: false },

    { title: t('نام/پدر/تماس'), key: 'name' },

    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },
    { title: t('موقعیت/مختصات'), key: 'now_specific_place' },

    { title: t('وظایف/نقاط مشکوکیت'), key: 'old_job' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی‌های فعلی'), key: 'now_districts' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک فعلی'), key: 'now_street_number' },
    { title: t('شماره خانه فعلی'), key: 'now_home_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('نقاط مشکوکیت'), key: 'mushkokyat_points' },

    { title: t('یادداشت'), key: 'note' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  /* ===== Filterable data ===== */
  const rowsFT = computed(() => repo.MashkokPeoples || []);
  const filters = ref({});
  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'father_name', 'phone_number'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_districts', 'now_village'] },
    {
      title: t('موقعیت/مختصات'),
      keys: ['now_specific_place', 'now_street_number', 'now_home_number', 'coordinates'],
    },
    { title: t('وظایف/مشکوکیت'), keys: ['old_job', 'now_job', 'mushkokyat_points'] },
    { title: t('منبع/یادداشت'), keys: ['resource_code', 'resource_phone', 'note'] },
  ]);

  /* ===== Resources dropdown -> code/phone + set resource_id ===== */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* ===== Picture handling ===== */
  const previewOpen = ref(false);
  const previewSrc = ref(null);
  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }
  function triggerFile(item) {
    document.getElementById(`up-${item.idOrTmp}`)?.click();
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
      previewUrl: x.picture ?? null,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      phone_number: x.phone_number ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_districts: x.now_districts ?? '',
      now_village: x.now_village ?? '',
      now_specific_place: x.now_specific_place ?? '',
      now_street_number: x.now_street_number ?? '',
      now_home_number: x.now_home_number ?? '',
      coordinates: x.coordinates ?? '',

      old_job: x.old_job ?? '',
      now_job: x.now_job ?? '',
      mushkokyat_points: x.mushkokyat_points ?? '',

      note: x.note ?? '',

      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base, picture: null } };
  }

  function onPickFile(item, file) {
    item.draft.picture = file || null;
    if (file) {
      if (item._blobUrl) URL.revokeObjectURL(item._blobUrl);
      item._blobUrl = URL.createObjectURL(file);
      item.previewUrl = item._blobUrl;
    } else {
      item.previewUrl = item.picture || null;
    }
  }

  const EDITABLE_KEYS = [
    'name',
    'father_name',
    'phone_number',
    'main_province',
    'main_district',
    'main_village',
    'now_province',
    'now_zone',
    'now_districts',
    'now_village',
    'now_specific_place',
    'now_street_number',
    'now_home_number',
    'coordinates',
    'old_job',
    'now_job',
    'mushkokyat_points',
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
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    if (d.picture && typeof d.picture === 'object') changed.picture = d.picture;
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

  /* ===== Save / Delete ===== */
  async function save(item) {
    if (!item.draft?.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
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
        const created = await repo.CreateMashkokPeople?.(fd);
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
      const updated = await repo.UpdateMashkokPeople?.(item.id, fd);
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
      if (input?.focus) input.focus();
    }
  }

  async function del(item) {
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا مطمئن هستید؟'))) return;
    try {
      await repo.DeleteMashkokPeople?.(item.id);
      await fetchAll();
      setBlankEditorRow();
    } catch (e) {
      console.error('Delete error:', e);
      showErrorMessage(t('خطا در حذف') + ': ' + (e?.message || t('خطای ناشناخته')));
    }
  }

  /* ===== Fetch / Start edit ===== */
  async function fetchAll() {
    try {
      await repo.FetchMashkokPeoplesData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search || '',
      });
    } catch (e) {
      console.error('fetchAll error', e);
    }
  }
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      await repo.FetchMashkokPeopleData?.(id);
      const single = Array.isArray(repo.MashkokPeople)
        ? repo.MashkokPeople[0]
        : repo.MashkokPeople || row;
      rows.value = [toRowModel(single || row)];
      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      console.error('startEdit error', e);
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* ===== Init & cleanup ===== */
  onMounted(async () => {
    await Promise.allSettled([repo.GetResourceCode?.()]);
    await fetchAll();
    setBlankEditorRow();
  });
  onBeforeUnmount(() => {
    // revoke any blob URLs to avoid leaks
    rows.value.forEach((r) => {
      if (r?._blobUrl) URL.revokeObjectURL(r._blobUrl);
    });
  });
</script>

<style scoped>
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }

  /* groups */
  .addr-group {
    display: grid;
    grid-template-columns: repeat(3, minmax(160px, 1fr));
    gap: 2px;
  }
  .addr-group1 {
    display: grid;
    grid-template-columns: repeat(4, minmax(160px, 1fr));
    gap: 2px;
  }
  .text-group {
    display: grid;
    width: 160px;
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

  /* image overlay */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .pic-thumb {
    position: relative;
    display: inline-flex;
    cursor: pointer;
  }
  .pic-overlay {
    position: absolute;
    inset: -6px -6px auto auto;
    display: none;
    gap: 6px;
    background: rgba(0, 0, 0, 0.55);
    padding: 6px;
    border-radius: 8px;
    transform: translateY(-40%);
    white-space: nowrap;
  }
  .pic-thumb:hover .pic-overlay {
    display: inline-flex;
  }
  .pic-overlay :deep(.v-btn) {
    color: #fff;
  }

  /* compact inputs */
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
