<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('وکیلالو')"
      :subtitle="t('ایجاد/ویرایش وکیلالو')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search (for the FilterableTable dataset) -->
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
          <!-- Basic identity -->
          <template #item.name="{ item }">
            <v-text-field
              v-model="item.draft.name"
              class="text-group pr-2"
              density="compact"
              variant="outlined"
              :label="t('نام')"
              :rules="[rules.required]"
              hide-details
            />
          </template>

          <template #item.f_name="{ item }">
            <v-text-field
              v-model="item.draft.f_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدر')"
              hide-details
            />
          </template>

          <template #item.grand_father_name="{ item }">
            <v-text-field
              v-model="item.draft.grand_father_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدرکلان')"
              hide-details
            />
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
                :label="t('قریه اصلی')"
                hide-details
              />
            </div>
          </template>

          <!-- Current address -->
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
            </div>
          </template>

          <template #item.now_village="{ item }">
            <v-text-field
              v-model="item.draft.now_village"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('قریه فعلی')"
              hide-details
            />
          </template>

          <template #item.now_specific_place="{ item }">
            <v-text-field
              v-model="item.draft.now_specific_place"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('موقعیت مشخص فعلی')"
              hide-details
            />
          </template>

          <template #item.now_street_number="{ item }">
            <v-text-field
              v-model="item.draft.now_street_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره سرک')"
              hide-details
            />
          </template>

          <template #item.now_home_number="{ item }">
            <v-text-field
              v-model="item.draft.now_home_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره خانه')"
              hide-details
            />
          </template>

          <template #item.Guzar="{ item }">
            <v-text-field
              v-model="item.draft.Guzar"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('گذر')"
              hide-details
            />
          </template>

          <template #item.place_tafseel="{ item }">
            <v-text-field
              v-model="item.draft.place_tafseel"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('تفصیل موقعیت')"
              hide-details
            />
          </template>

          <!-- Jobs & home -->
          <template #item.old_job="{ item }">
            <v-text-field
              v-model="item.draft.old_job"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه قبلی')"
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

          <template #item.home_quantity="{ item }">
            <v-text-field
              v-model="item.draft.home_quantity"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('تعداد خانه')"
              :rules="[rules.nonNegativeInt]"
              hide-details
            />
          </template>

          <!-- Contact -->
          <template #item.phone_number="{ item }">
            <v-text-field
              v-model="item.draft.phone_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس')"
              :rules="[rules.phone]"
              hide-details
            />
          </template>

          <!-- History & resource (with resource code/phone logic like Mawad) -->
          <template #item.history_form="{ item }">
            <v-text-field
              v-model="item.draft.history_form"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('سابقه/فرم')"
              hide-details
            />
          </template>

          <!-- ✅ Resource code autocomplete -->
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

          <!-- ✅ Resource phone readonly -->
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

          <!-- Existing resource_id input (kept as requested) -->
          <template #item.resource_id="{ item }">
            <v-text-field
              v-model="item.draft.resource_id"
              class="text-group"
              density="compact"
              type="number"
              variant="outlined"
              :label="t('Resource ID')"
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

          <!-- Coordinates BEFORE note (as requested) -->
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
              <v-btn class="bg-primary" variant="outlined" :loading="item.loading" type="submit">
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

    <!-- ✅ PeekButton like Mawad -->
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

    <!-- ✅ FilterableTable -->
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
            :subtitle="row.phone_number"
            type="wakilanos"
            :photo-url="row.picture"
            :zoom="16"
          />
        </template>
      </FilterableTable>
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
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
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

      coordinates: x.coordinates ?? '',
      name: x.name ?? '',
      f_name: x.f_name ?? '',
      grand_father_name: x.grand_father_name ?? '',
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
      Guzar: x.Guzar ?? '',
      place_tafseel: x.place_tafseel ?? '',
      old_job: x.old_job ?? '',
      now_job: x.now_job ?? '',
      home_quantity: x.home_quantity ?? '',
      phone_number: x.phone_number ?? '',
      history_form: x.history_form ?? '',
      resource_id: x.resource_id ?? '',
      // ✅ new fields for Mawad-style logic
      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      note: x.note ?? '',
    };
    return { ...base, draft: { ...base } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* Dataset for FilterableTable */
  const rowsFT = computed(() => repo.Wakilanos || []);
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

  /* Table headers (editor) — coordinates before note */
  const headers = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'f_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },

    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی (۱)'), key: 'now_province' },
    { title: t('قریه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک'), key: 'now_street_number' },
    { title: t('شماره خانه'), key: 'now_home_number' },
    { title: t('گذر'), key: 'Guzar' },
    { title: t('تفصیل موقعیت'), key: 'place_tafseel' },

    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('تعداد خانه'), key: 'home_quantity' },

    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('سابقه/فرم'), key: 'history_form' },

    // ✅ resource block like Mawad
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('مختصات'), key: 'coordinates' }, // <-- before note
    { title: t('یادداشت'), key: 'note' },

    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* FilterableTable headers (list view) */
  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'f_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک'), key: 'now_street_number' },
    { title: t('شماره خانه'), key: 'now_home_number' },
    { title: t('گذر'), key: 'Guzar' },
    { title: t('تفصیل موقعیت'), key: 'place_tafseel' },

    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('تعداد خانه'), key: 'home_quantity' },

    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('سابقه/فرم'), key: 'history_form' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('مختصات'), key: 'coordinates' },
    { title: t('یادداشت'), key: 'note' },
    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'f_name', 'grand_father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    {
      title: t('آدرس فعلی'),
      keys: [
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'Guzar',
        'place_tafseel',
      ],
    },
    { title: t('وظایف/خانه'), keys: ['old_job', 'now_job', 'home_quantity'] },
    {
      title: t('ارتباط/سایر'),
      keys: ['phone_number', 'history_form', 'coordinates', 'note'],
    },
    {
      title: t('منبع'),
      keys: ['resource_code', 'resource_phone', 'resource_id', 'Province_addr'],
    },
  ]);

  const editorCard = ref(null);

  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      if (typeof repo.FetchWakilanoData === 'function') {
        await repo.FetchWakilanoData(id);
      } else if (typeof repo.FetchWakilanosData === 'function') {
        await repo.FetchWakilanosData({ page: 1, itemsPerPage: 100000 });
      }
      const single =
        (repo.Wakilano && Array.isArray(repo.Wakilano) ? repo.Wakilano[0] : repo.Wakilano) ||
        (repo.Wakilanos || []).find((r) => r.id === id) ||
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
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'coordinates',
      'name',
      'f_name',
      'grand_father_name',
      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_zone',
      'now_district',
      'now_village',
      'now_specific_place',
      'now_street_number',
      'now_home_number',
      'Guzar',
      'place_tafseel',
      'old_job',
      'now_job',
      'home_quantity',
      'phone_number',
      'Province_addr',
      'history_form',
      'resource_id',
      'resource_code',
      'resource_phone',
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
    if (!item?.draft?.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
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
        const created = await repo.CreateWakilano?.(fd);
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
      const updated = await repo.UpdateWakilano?.(item.id, fd);
      if (updated?.id) {
        showOkMessage(t('ریکارد بروزرسانی شد'));
        await fetchAll();
        setBlankEditorRow();
      }
    } catch (e) {
      console.error('Save error', e);
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
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name }))) return;
    try {
      await repo.DeleteWakilano?.(item.id);
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

  /* Load dataset for FilterableTable only */
  async function fetchAll() {
    await Promise.allSettled([
      repo.FetchWakilanosData?.({ page: 1, itemsPerPage: 100000, search: repo.Search }),
    ]);
  }

  /* ✅ Resource dropdown logic (like Mawad) */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* ✅ Peek / Fetch one (for PeekButton) */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    if (!id) return;
    loading.value = true;
    try {
      await repo.FetchWakilanoData?.(id);
      detail.value = repo.Wakilano || [];
      selectedId.value = id;
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

  onMounted(async () => {
    await Promise.allSettled([
      fetchAll(),
      repo.GetResourceCode?.(), // ✅ like Mawad, to populate resource dropdown
    ]);
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
