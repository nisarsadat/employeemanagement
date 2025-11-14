<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('هېست')"
      :subtitle="t('اضافه کردن یک ریکارډ هېست')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions>
        <v-btn icon />
      </template>
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
          hide-default-footer
          :dir="isRtl ? 'rtl' : 'ltr'"
        >
          <!-- Picture (file -> FormData) with hover tools -->
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
                size="small"
                color="primary"
                variant="outlined"
                icon
                @click="triggerFile(item)"
              >
                <v-icon>mdi-plus</v-icon>
              </v-btn>

              <div v-else class="pic-thumb" :title="t('گزینه ها')">
                <v-avatar size="32">
                  <v-img :src="item.previewUrl || item.picture" :alt="t('عکس')" cover />
                </v-avatar>
                <div class="pic-overlay">
                  <v-btn
                    size="small"
                    color="secondary"
                    variant="flat"
                    @click.stop="triggerFile(item)"
                  >
                    <v-icon start>mdi-pencil</v-icon>{{ t('ویرایش') }}
                  </v-btn>
                  <v-btn
                    size="small"
                    color="secondary"
                    @click.stop="openPreview(item.previewUrl || item.picture)"
                  >
                    <v-icon start>mdi-eye</v-icon>{{ t('نمایش') }}
                  </v-btn>
                </div>
              </div>
            </div>
          </template>

          <!-- Basics -->
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
              :error="hasError(item, 'father_name')"
              :error-messages="getErrorMessages(item, 'father_name')"
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
                :error="hasError(item, 'main_province')"
                :error-messages="getErrorMessages(item, 'main_province')"
              />
              <v-text-field
                v-model="item.draft.main_district"
                density="compact"
                variant="outlined"
                :label="t('ولسوالی اصلی')"
                hide-details
                :error="hasError(item, 'main_district')"
                :error-messages="getErrorMessages(item, 'main_district')"
              />
              <v-text-field
                v-model="item.draft.main_village"
                density="compact"
                variant="outlined"
                :label="t('قریه/ناحیه اصلی')"
                hide-details
                :error="hasError(item, 'main_village')"
                :error-messages="getErrorMessages(item, 'main_village')"
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
              <v-text-field
                v-model="item.draft.now_village"
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
              hide-details
            />
          </template>
          <template #item.coordinates="{ item }">
            <v-text-field
              v-model="item.draft.coordinates"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('مختصات (lat,lng)')"
              hide-details
            />
          </template>

          <!-- Jobs -->
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

          <!-- Contact / activity -->
          <template #item.phone_number="{ item }">
            <v-text-field
              v-model="item.draft.phone_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس')"
              hide-details
              :error="hasError(item, 'phone_number')"
              :error-messages="getErrorMessages(item, 'phone_number')"
            />
          </template>
          <template #item.done_roberies="{ item }">
            <v-text-field
              v-model.number="item.draft.done_roberies"
              type="number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('سرقت‌های انجام‌شده')"
              hide-details
            />
          </template>
          <template #item.gun_type="{ item }">
            <v-text-field
              v-model="item.draft.gun_type"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نوع سلاح')"
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
          <!-- Resource (like Fasad) -->
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
              hide-details
              :error="hasError(item, 'note')"
              :error-messages="getErrorMessages(item, 'note')"
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

        <!-- Image preview dialog -->
        <v-dialog v-model="previewOpen" max-width="520">
          <v-card>
            <v-toolbar
              density="compact"
              color="primary"
              :title="t('پیش نمایش')"
              class="text-white"
            />
            <v-card-text class="d-flex justify-center">
              <v-img
                :src="previewSrc"
                :alt="t('پیش نمایش')"
                max-width="480"
                max-height="420"
                cover
              />
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn color="primary" variant="flat" @click="previewOpen = false">{{
                t('بستن')
              }}</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
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
        image-key="picture"
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
  import { ref, onMounted, computed, watch, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';

  const { t, locale } = useI18n();
  const drawer = ref(false);

  const repo = useFasadRepository();

  /* RTL */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* Preview dialog */
  const previewOpen = ref(false);
  const previewSrc = ref(null);
  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }

  /* Validation + form */
  const editorForm = ref(null);
  const editorValid = ref(false);
  const formKey = ref(0);
  const editorCard = ref(null);

  /* Row errors */
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

  /* Rules */
  const rules = {
    required: (v) => !!v || 'This field is required',
    number: (v) => !v || /^[0-9]+$/.test(String(v)) || 'Only numbers allowed',
  };

  /* Resources dropdown */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone_number = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* Single-row model */
  const rows = ref([]);
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? 'tmp-1',

      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,
      loading: false,

      name: x.name ?? '',
      father_name: x.father_name ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',

      specific_place: x.specific_place ?? '',
      street_number: x.street_number ?? '',
      home_number: x.home_number ?? '',
      coordinates: x.coordinates ?? '',

      old_job: x.old_job ?? '',
      now_job: x.now_job ?? '',
      Province_addr: x.Province_addr ?? '',

      phone_number: x.phone_number ?? '',
      done_roberies: x.done_roberies ?? '',
      gun_type: x.gun_type ?? '',

      resource_code: x.resource_code ?? '',
      resource_phone_number: x.resource_phone_number ?? '',
      resource_id: x.resource_id ?? null,

      note: x.note ?? '',
    };
    return { ...base, draft: { ...base, picture: null } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* Table headers */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', width: 120, sortable: false },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('سرقت‌های انجام‌شده'), key: 'done_roberies' },
    { title: t('نوع سلاح'), key: 'gun_type' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    { title: t('ملاحظات'), key: 'note' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* Filterable table dataset */
  const rowsFT = computed(() => repo.Heists || []);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },
    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('سرقت‌های انجام‌شده'), key: 'done_roبرies' },
    { title: t('نوع سلاح'), key: 'gun_type' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    { title: t('ملاحظات'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    {
      title: t('خانه/مختصات'),
      keys: ['specific_place', 'street_number', 'home_number', 'coordinates'],
    },
    { title: t('وظایف'), keys: ['old_job', 'now_job'] },
    { title: t('تماس/فعالیت'), keys: ['phone_number', 'done_roberies', 'gun_type'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['note'] },
  ]);

  /* Filters */
  const filters = ref({});

  /* Pickers */
  function triggerFile(item) {
    const el = document.getElementById(`up-${item.idOrTmp}`);
    if (el) el.click();
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

  /* Diff + selective FormData */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'name',
      'father_name',
      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_zone',
      'now_district',
      'now_village',
      'specific_place',
      'street_number',
      'home_number',
      'coordinates',
      'old_job',
      'now_job',
      'phone_number',
      'done_roberies',
      'gun_type',
      'Province_addr',
      'resource_code',
      'resource_phone_number',
      'resource_id',
      'note',
    ];
    for (const k of keys) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    if (d.picture && typeof d.picture === 'object') changed.picture = d.picture;
    return { changed };
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v !== undefined && v !== null) fd.append(k, v);
    });
    return fd;
  }

  /* Save / Delete + validated submit */
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

  async function save(item) {
    if (!item?.draft?.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
      return;
    }
    item.loading = true;
    clearItemErrors(item);
    try {
      if (!item.id) {
        const fd = new FormData();
        Object.entries(item.draft).forEach(([k, v]) => {
          if (v !== undefined && v !== null) fd.append(k, v);
        });
        const created = await repo.CreateHeist(fd);
        if (created?.id) {
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
      const { changed } = diffDraft(item);
      if (Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }
      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateHeist(item.id, fd);
      if (updated?.id) {
        if (item._blobUrl) {
          URL.revokeObjectURL(item._blobUrl);
          item._blobUrl = null;
        }
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
    } catch (error) {
      console.error('Save error:', error);
      if (error.response?.data?.errors) {
        setItemErrors(item, error.response.data.errors);
        showErrorMessage(t('لطفا خطاهای فرم را بررسی کنید'));
      } else {
        showErrorMessage(t('خطا در ذخیره اطلاعات') + ': ' + (error.message || t('خطای ناشناخته')));
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
    if (!confirm(t('آیا از حذف  مطمئن هستید؟', { name: item.name }))) return;
    if (!confirm(t('با حذف این مورد مواد را کاملا از دست میتین .آیا  واقعااز حذف  مطمئن هستید؟')))
      return;
    try {
      await repo.DeleteHeist(item.id);
      await fetchAll();
      setBlankEditorRow();
    } catch (error) {
      console.error('Delete error:', error);
      showErrorMessage(t('خطا در حذف') + ': ' + (error.message || t('خطای ناشناخته')));
    }
  }

  function clearEditor(item) {
    try {
      if (item && item._blobUrl) {
        URL.revokeObjectURL(item._blobUrl);
        item._blobUrl = null;
      }
      const inputEl = document.getElementById(`up-${item?.idOrTmp}`);
      if (inputEl) inputEl.value = '';
      if (item && item.idOrTmp && itemErrors.value[item.idOrTmp])
        delete itemErrors.value[item.idOrTmp];
      setBlankEditorRow();
      showErrorMessage(t('ورودی‌ها پاک شد'));
    } catch (e) {
      console.error('clearEditor error:', e);
    }
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

  /* Peek / Fetch one */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);
  async function fetchOne(id) {
    loading.value = true;
    try {
      await repo.FetchHeistData(id);
      detail.value = repo.Heist || [];
    } catch (e) {
      console.error('Fetch one error:', e);
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

  /* Fetch ALL (for listing + FT) */
  async function fetchAll() {
    try {
      if (typeof repo.FetchHeistsData === 'function') {
        await repo.FetchHeistsData({ page: repo.page, perPage: repo.perPage, search: repo.Search });
        return;
      }
      repo.page = 1;
      repo.itemsPerPage = 100000;
      await repo.FetchHeistsData({ page: 1, itemsPerPage: 100000 });
    } catch (e) {
      console.error('fetchAll error', e);
    }
  }

  /* Init */
  const showError = ref(false);
  const errorMessage = ref('');
  function showErrorMessage(m) {
    errorMessage.value = m;
    showError.value = true;
  }

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

  /* layout like your default */
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
    display: inline-flex;
    cursor: pointer;
    position: relative;
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

  /* compact spacing */
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
