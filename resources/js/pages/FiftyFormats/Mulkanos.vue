<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('ملکانُو')"
      :subtitle="t('اضافه کردن یک ریکارډ مُلکانو')"
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
          <!-- Picture -->
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

          <!-- Main address (3 cols) -->
          <template #item.main_province="{ item }">
            <div class="addr-group-3">
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

          <!-- Now address (4 cols) -->
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
          <template #item.guzar="{ item }">
            <v-text-field
              v-model="item.draft.guzar"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('گذر')"
              hide-details
            />
          </template>

          <!-- Specific / amounts / jobs -->
          <template #item.tafseel_sahi="{ item }">
            <v-text-field
              v-model="item.draft.tafseel_sahi"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('تفسیر صحیح / توضیحات')"
              hide-details
            />
          </template>
          <template #item.street_amount="{ item }">
            <v-text-field
              v-model="item.draft.street_amount"
              class="text-group"
              density="compact"
              variant="outlined"
              type="number"
              :label="t('مقدار سرک')"
              hide-details
            />
          </template>
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
          <template #item.new_job="{ item }">
            <v-text-field
              v-model="item.draft.new_job"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه جدید')"
              hide-details
            />
          </template>

          <!-- Quantities -->
          <template #item.agent_quantity="{ item }">
            <v-text-field
              v-model="item.draft.agent_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              type="number"
              :label="t('تعداد عامل')"
              hide-details
            />
          </template>
          <template #item.home_quantity="{ item }">
            <v-text-field
              v-model="item.draft.home_quantity"
              class="text-group"
              density="compact"
              variant="outlined"
              type="number"
              :label="t('تعداد خانه')"
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
              hide-details
              :error="hasError(item, 'phone_number')"
              :error-messages="getErrorMessages(item, 'phone_number')"
            />
          </template>

          <!-- History / org -->
          <template #item.jorm_history="{ item }">
            <v-text-field
              v-model="item.draft.jorm_history"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('سابقه جرم')"
              hide-details
            />
          </template>
          <template #item.tanzim="{ item }">
            <v-text-field
              v-model="item.draft.tanzim"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('تنظیم / وابستگی')"
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

          <!-- Notes -->
          <template #item.notes="{ item }">
            <v-text-field
              v-model="item.draft.notes"
              class="text-group-note"
              density="compact"
              variant="outlined"
              :label="t('ملاحظات')"
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

    <!-- Big image preview dialog -->
    <v-dialog v-model="previewOpen" max-width="720">
      <v-card>
        <v-toolbar
          density="compact"
          color="primary"
          :title="t('پیش نمایش تصویر')"
          class="text-white"
        />
        <v-card-text class="d-flex justify-center">
          <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="680" max-height="560" cover />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn color="primary" variant="flat" @click="previewOpen = false">{{ t('بستن') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));
  const drawer = ref(false);

  const repo = useFasadRepository();

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

  /* ===== Image preview dialog ===== */
  const previewOpen = ref(false);
  const previewSrc = ref(null);
  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
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
    selectRequired: (label) => (v) => !!v || t('{label} الزامی است', { label }),
    number: (v) => !v || /^[0-9]+$/.test(v) || t('فقط اعداد مجاز است'),
  };

  /* ===== Single editor row ===== */
  const rows = ref([]);
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ===== ALL data for FilterableTable ===== */
  const rowsFT = computed(() => repo.Mulkanos || []);

  /* ===== Headers (editor) ===== */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false, width: 120 },

    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },

    { title: t('آدرس اصلی'), key: 'main_province' }, // grouped (3)
    { title: t('آدرس فعلی'), key: 'now_province' }, // grouped (4)

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('گذر'), key: 'guzar' },

    { title: t('تفسیر صحیح'), key: 'tafseel_sahi' },
    { title: t('مقدار سرک'), key: 'street_amount' },
    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه جدید'), key: 'new_job' },

    { title: t('تعداد عامل'), key: 'agent_quantity' },
    { title: t('تعداد خانه'), key: 'home_quantity' },

    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('سابقه جرم'), key: 'jorm_history' },
    { title: t('تنظیم'), key: 'tanzim' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('ملاحظات'), key: 'notes' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  /* ===== Headers for FilterableTable ===== */
  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },

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
    { title: t('گذر'), key: 'guzar' },

    { title: t('تفسیر صحیح'), key: 'tafseel_sahi' },
    { title: t('مقدار سرک'), key: 'street_amount' },
    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه جدید'), key: 'new_job' },

    { title: t('تعداد عامل'), key: 'agent_quantity' },
    { title: t('تعداد خانه'), key: 'home_quantity' },

    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('سابقه جرم'), key: 'jorm_history' },
    { title: t('تنظیم'), key: 'tanzim' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('ملاحظات'), key: 'notes' },
  ]);

  /* ===== Top filter groups (Fasad style) ===== */
  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'father_name', 'grand_father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    { title: t('منزل/موقعیت'), keys: ['specific_place', 'street_number', 'home_number', 'guzar'] },
    {
      title: t('توضیحات/مقادیر/وظایف'),
      keys: ['tafseel_sahi', 'street_amount', 'old_job', 'new_job'],
    },
    { title: t('مقادیر'), keys: ['agent_quantity', 'home_quantity'] },
    { title: t('تماس'), keys: ['phone_number'] },
    { title: t('سابقه/وابستگی'), keys: ['jorm_history', 'tanzim'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['notes'] },
  ]);

  /* ===== Resources dropdown -> code/phone + set resource_id ===== */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone_number = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* ===== Row model & diff ===== */
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  function toRowModel(x = {}) {
    const r = x.resource || {};
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),

      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,

      loading: false,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      grand_father_name: x.grand_father_name ?? '',

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
      guzar: x.guzar ?? '',

      tafseel_sahi: x.tafseel_sahi ?? '',
      street_amount: x.street_amount ?? '',
      old_job: x.old_job ?? '',
      new_job: x.new_job ?? '',

      agent_quantity: x.agent_quantity ?? '',
      home_quantity: x.home_quantity ?? '',

      phone_number: x.phone_number ?? '',

      jorm_history: x.jorm_history ?? '',
      tanzim: x.tanzim ?? '',

      resource_code: x.resource_code ?? r.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? r.phone_number ?? '',
      resource_id: x.resource_id ?? r.id ?? null,

      notes: x.notes ?? '',
    };
    return { ...base, draft: { ...base, picture: null } };
  }

  const EDITABLE_KEYS = [
    'name',
    'father_name',
    'grand_father_name',
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
    'guzar',
    'tafseel_sahi',
    'street_amount',
    'old_job',
    'new_job',
    'agent_quantity',
    'home_quantity',
    'phone_number',
    'jorm_history',
    'tanzim',
    'resource_code',
    'resource_phone_number',
    'resource_id',
    'notes',
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
    if (d.picture && typeof d.picture === 'object') changed.picture = d.picture;
    else if (typeof d.picture === 'string' && d.picture !== (item.picture ?? ''))
      changed.picture = d.picture;
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

  /* ===== Save / Delete / Submit ===== */
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
        const created = await repo.CreateMulkano?.(fd);
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
      const updated = await repo.UpdateMulkano?.(item.id, fd);
      if (updated && updated.id) {
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
      await repo.DeleteMulkano?.(item.id);
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

  /* ===== Peek / Fetch single ===== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      await repo.FetchMulkanoData?.(id);
      detail.value = repo.Mulkano || [];
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

  /* ===== Fetch ALL (no pagination in FilterableTable) ===== */
  const filters = ref({});
  async function fetchAll() {
    try {
      await repo.FetchMulkanosData?.({
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
      if (!id) return;
      await repo.FetchMulkanoData?.(id);
      const single =
        repo.Mulkano && Array.isArray(repo.Mulkano) ? repo.Mulkano[0] : repo.Mulkano || row;
      rows.value = [toRowModel(single || row)];
      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      console.error('startEdit error', e);
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* ===== Image pick helpers ===== */
  function triggerFile(item) {
    document.getElementById(`up-${item.idOrTmp}`)?.click();
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

  /* ===== Init ===== */
  onMounted(async () => {
    await Promise.allSettled([repo.GetResourceCode?.()]);
    await fetchAll();
    setBlankEditorRow();
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
  .addr-group {
    /* now address: 4 cols */
    display: grid;
    grid-template-columns: repeat(4, minmax(120px, 1fr));
    gap: 2px;
  }
  .addr-group-3 {
    /* main address: 3 cols */
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

  /* Tight spacing inside grouped blocks */
  .addr-group,
  .addr-group-3,
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
  }

  /* Picture hover tools */
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
</style>
