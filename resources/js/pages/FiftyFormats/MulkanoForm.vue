<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح مُلکانو') : t('اضافه کردن یک ریکارډ جدید مُلکانو')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ موجود مُلکانو')
          : t('ثبت یک ریکارډ جدید مُلکانو در سیستم')
      "
    >
      <template #actions>
        <v-btn
          variant="text"
          :prepend-icon="isRtl ? 'mdi-arrow-right' : 'mdi-arrow-left'"
          class="text-none"
          @click="goBack"
        >
          {{ t('برگشت') }}
        </v-btn>
      </template>
    </AppBar>

    <!-- FORM -->
    <v-container class="mt-4">
      <v-form
        ref="formRef"
        v-model="formValid"
        lazy-validation
        @submit.prevent="onSubmit"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <v-card class="pa-4" elevation="2">
          <!-- مشخصات اساسی -->
          <v-row>
            <v-col cols="12">
              <h3 class="section-title">{{ t('مشخصات اساسی') }}</h3>
            </v-col>
          </v-row>

          <v-row>
            <!-- picture -->
            <v-col cols="12" md="3">
              <v-card class="d-flex flex-column align-center justify-center pa-3" outlined>
                <v-avatar size="80" class="mb-2">
                  <v-img v-if="previewUrl" :src="previewUrl" :alt="form.name" cover />
                  <v-icon v-else size="48">mdi-account</v-icon>
                </v-avatar>

                <v-btn
                  size="small"
                  variant="outlined"
                  prepend-icon="mdi-image"
                  class="text-none mb-1"
                  @click="triggerFile"
                >
                  {{ t('انتخاب تصویر') }}
                </v-btn>

                <input
                  ref="fileInput"
                  type="file"
                  class="d-none"
                  accept="image/*"
                  @change="onFileChange"
                />
              </v-card>
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.name"
                :label="t('نام')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.required]"
                required
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.father_name"
                :label="t('نام پدر')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.grand_father_name"
                :label="t('نام پدرکلان')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <v-row class="mt-1">
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.phoneOptional]"
                inputmode="tel"
              />
            </v-col>
          </v-row>

          <!-- آدرس اصلی -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">{{ t('آدرس اصلی') }}</h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.main_province"
                v-model:district="form.main_district"
                :province-label="t('ولایت اصلی')"
                :district-label="t('ولسوالی اصلی')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.main_village"
                :label="t('قریه/ناحیه اصلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- آدرس فعلی -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">{{ t('آدرس فعلی') }}</h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.now_province"
                v-model:district="form.now_district"
                :province-label="t('ولایت فعلی')"
                :district-label="t('ولسوالی فعلی')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.now_zone"
                :label="t('زون فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.now_village"
                :label="t('قریه/ناحیه فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- موقعیت و خانه -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">{{ t('موقعیت و خانه') }}</h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.specific_place"
                :label="t('موقعیت مشخص')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.street_number"
                :label="t('سرک نمبر')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.intOptional]"
                inputmode="numeric"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.home_number"
                :label="t('نمبر خانه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.intOptional]"
                inputmode="numeric"
              />
            </v-col>
          </v-row>

          <v-row class="mt-1">
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.guzar"
                :label="t('گذر')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- مقادیر و وظایف -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">{{ t('مقادیر و وظایف') }}</h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.street_amount"
                :label="t('مقدار سرک')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.numberOptional]"
                inputmode="decimal"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.agent_quantity"
                :label="t('تعداد عامل')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.intOptional]"
                inputmode="numeric"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.home_quantity"
                :label="t('تعداد خانه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.intOptional]"
                inputmode="numeric"
              />
            </v-col>
          </v-row>

          <v-row class="mt-1">
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.old_job"
                :label="t('وظیفه قبلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.new_job"
                :label="t('وظیفه جدید')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- سوابق و وابستگی -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">{{ t('سوابق و وابستگی') }}</h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.jorm_history"
                :label="t('سابقه جرم')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.tanzim"
                :label="t('تنظیم / وابستگی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <v-row class="mt-1">
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.tafseel_sahi"
                :label="t('تفسیر صحیح / توضیحات')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <v-row class="mt-1">
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.notes"
                :label="t('ملاحظات')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- مختصات -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">{{ t('مختصات و نقشه') }}</h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.coordinates"
                :label="t('coordinates')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                hint="مثال: 34.1234, 69.1234"
                persistent-hint
                :rules="[rules.coordinatesOptional]"
              />
            </v-col>
          </v-row>

          <!-- منبع -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">{{ t('معلومات منبع') }}</h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                clearable
                @update:model-value="onResourceCodeChange"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.resource_phone_number"
                :label="t('شماره منبع')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                readonly
              />
            </v-col>
          </v-row>

          <!-- ACTIONS -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2 flex-wrap">
              <!-- ✅ Export buttons here (download even blank form) -->
              <v-tooltip :text="t('PDF ساده (بدون عکس)')" location="top">
                <template #activator="{ props }">
                  <v-btn
                    v-bind="props"
                    variant="tonal"
                    color="primary"
                    class="text-none"
                    prepend-icon="mdi-file-pdf-box"
                    @click="downloadPdf"
                  >
                    {{ t('PDF') }}
                  </v-btn>
                </template>
              </v-tooltip>

              <v-tooltip :text="t('Word ساده (بدون عکس)')" location="top">
                <template #activator="{ props }">
                  <v-btn
                    v-bind="props"
                    variant="tonal"
                    color="primary"
                    class="text-none"
                    prepend-icon="mdi-file-word-box"
                    @click="downloadWord"
                  >
                    {{ t('Word') }}
                  </v-btn>
                </template>
              </v-tooltip>

              <v-btn
                color="primary"
                variant="flat"
                class="text-none"
                :loading="saving"
                type="submit"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت مُلکانو جدید') }}
              </v-btn>

              <v-btn color="secondary" variant="text" class="text-none" @click="resetForm">
                {{ t('پاک کردن فرم') }}
              </v-btn>
            </v-col>
          </v-row>
        </v-card>
      </v-form>
    </v-container>

    <!-- ❗ Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>

    <!-- ✅ Success Snackbar -->
    <v-snackbar v-model="showSuccess" color="success" timeout="4000">
      {{ successMessage }}
      <template #actions>
        <v-btn variant="text" @click="showSuccess = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';

  import AppBar from '@/components/AppBar.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const route = useRoute();
  const router = useRouter();
  const repo = useFasadRepository();

  const isEdit = computed(() => !!route.params.id);

  /* Form state */
  const formRef = ref(null);
  const formValid = ref(false);
  const saving = ref(false);

  const form = ref({
    id: null,
    picture: null,

    name: '',
    father_name: '',
    grand_father_name: '',
    Province_addr: '',
    phone_number: '',

    main_province: '',
    main_district: '',
    main_village: '',

    now_province: '',
    now_zone: '',
    now_district: '',
    now_village: '',

    specific_place: '',
    street_number: '',
    home_number: '',
    guzar: '',

    tafseel_sahi: '',
    street_amount: '',
    old_job: '',
    new_job: '',

    agent_quantity: '',
    home_quantity: '',

    jorm_history: '',
    tanzim: '',
    notes: '',

    coordinates: '',

    resource_code: '',
    resource_phone_number: '',
    resource_id: null,
  });

  /* image preview */
  const fileInput = ref(null);
  const previewUrl = ref(null);

  function triggerFile() {
    fileInput.value?.click();
  }

  function onFileChange(e) {
    const file = e.target.files?.[0] ?? null;
    form.value.picture = file;

    if (previewUrl.value && String(previewUrl.value).startsWith('blob:')) {
      URL.revokeObjectURL(previewUrl.value);
    }
    previewUrl.value = file ? URL.createObjectURL(file) : null;
  }

  /* Rules */
  const rules = {
    required: (v) => !!String(v ?? '').trim() || t('این فیلد الزامی است'),

    // empty OK, otherwise integer >= 0
    intOptional: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      if (!/^\d+$/.test(s)) return t('لطفاً عدد صحیح وارد کنید');
      return true;
    },

    // empty OK, otherwise number (12, 12.5, 12,5)
    numberOptional: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      if (!/^\d+([.,]\d+)?$/.test(s)) return t('لطفاً عدد معتبر وارد کنید');
      return true;
    },

    // empty OK, otherwise digits (and optional +)
    phoneOptional: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      const clean = s.replace(/\s+/g, '');
      if (!/^\+?\d{6,20}$/.test(clean)) return t('شماره تماس معتبر نیست');
      return true;
    },

    // empty OK, otherwise "lat, lng"
    coordinatesOptional: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      if (!/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/.test(s)) return t('مختصات درست نیست');
      return true;
    },
  };

  /* Snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  const showSuccess = ref(false);
  const successMessage = ref('');

  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  function showSuccessMsg(msg) {
    successMessage.value = msg;
    showSuccess.value = true;
  }

  /* Resources dropdown */
  const resources = computed(() => repo.resourcecode || []);

  function onResourceCodeChange(code) {
    const found = resources.value.find((r) => r.code_number === code);
    form.value.resource_phone_number = found?.phone_number ?? '';
    form.value.resource_id = found?.id ?? null;
  }

  /* Helpers */
  function resetForm() {
    const id = isEdit.value ? form.value.id : null;

    form.value = {
      id,
      picture: null,

      name: '',
      father_name: '',
      grand_father_name: '',
      Province_addr: '',
      phone_number: '',

      main_province: '',
      main_district: '',
      main_village: '',

      now_province: '',
      now_zone: '',
      now_district: '',
      now_village: '',

      specific_place: '',
      street_number: '',
      home_number: '',
      guzar: '',

      tafseel_sahi: '',
      street_amount: '',
      old_job: '',
      new_job: '',

      agent_quantity: '',
      home_quantity: '',

      jorm_history: '',
      tanzim: '',
      notes: '',

      coordinates: '',

      resource_code: '',
      resource_phone_number: '',
      resource_id: null,
    };

    if (previewUrl.value && String(previewUrl.value).startsWith('blob:')) {
      URL.revokeObjectURL(previewUrl.value);
    }
    previewUrl.value = null;

    formRef.value?.resetValidation?.();
  }

  function fillFormFromRecord(x = {}) {
    form.value = {
      id: x.id ?? null,
      picture: null,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      grand_father_name: x.grand_father_name ?? '',
      Province_addr: x.Province_addr ?? '',
      phone_number: x.phone_number ?? '',

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

      jorm_history: x.jorm_history ?? '',
      tanzim: x.tanzim ?? '',
      notes: x.notes ?? '',

      coordinates: x.coordinates ?? '',

      resource_code: x.resource_code ?? '',
      resource_phone_number: x.resource_phone_number ?? '',
      resource_id: x.resource_id ?? null,
    };

    // show backend image path/url (but export will NOT include it)
    previewUrl.value = x.picture ?? null;
  }

  /* Build FormData */
  function toFormData(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null || v === '') return;

      // normalize decimal comma
      if (k === 'street_amount' && typeof v === 'string') {
        fd.append(k, v.replace(',', '.'));
        return;
      }
      fd.append(k, v);
    });
    return fd;
  }

  /* Submit */
  async function onSubmit() {
    if (!formRef.value) return;

    const { valid } = await formRef.value.validate();
    if (!valid) {
      showErrorMessage(t('لطفاً فیلدهای الزامی را درست پُر کنید'));
      return;
    }

    saving.value = true;
    try {
      const fd = toFormData({ ...form.value });

      if (isEdit.value && form.value.id) {
        const updated = await repo.UpdateMulkano?.(form.value.id, fd);
        if (updated?.id) {
          showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
          goBack();
          return;
        }
      } else {
        const created = await repo.CreateMulkano?.(fd);
        if (created?.id) {
          showSuccessMsg(t('مُلکانو جدید موفقانه ثبت شد'));
          goBack();
          return;
        }
      }

      showErrorMessage(t('خطا در ذخیره معلومات'));
    } catch (e) {
      console.error('Mulkano save error', e);
      showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
    } finally {
      saving.value = false;
    }
  }

  /* Navigation */
  function goBack() {
    router.push('/app/mulkanos');
  }

  /* Load on edit */
  async function loadExisting() {
    const id = route.params.id;
    if (!id) return;
    try {
      await repo.FetchMulkanoData?.(id);
      const rec =
        repo.Mulkano && Array.isArray(repo.Mulkano) ? repo.Mulkano[0] : repo.Mulkano || {};
      fillFormFromRecord(rec);
    } catch (e) {
      console.error('loadExisting error', e);
      showErrorMessage(t('خطا در دریافت معلومات مُلکانو'));
    }
  }

  /* =========================
   ✅ SIMPLE EXPORT (PDF/Word)
   - Works even if form is blank
   - NO images
   - NO app headers/top bar
   - Clean RTL table + signature lines
========================= */
  function escapeHtml(s) {
    return String(s ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function buildExportHtml() {
    const rid = form.value.id ? `#${form.value.id}` : t('بدون شماره');

    const rows = [
      [t('نام'), form.value.name],
      [t('نام پدر'), form.value.father_name],
      [t('نام پدرکلان'), form.value.grand_father_name],
      [t('ولایت مربوطه'), form.value.Province_addr],
      [t('شماره تماس'), form.value.phone_number],

      // [t('ولایت اصلی'), form.value.main_province],
      [t('ولسوالی اصلی'), form.value.main_district],
      [t('قریه/ناحیه اصلی'), form.value.main_village],

      [t('ولایت فعلی'), form.value.now_province],
      [t('زون فعلی'), form.value.now_zone],
      [t('ولسوالی فعلی'), form.value.now_district],
      [t('قریه/ناحیه فعلی'), form.value.now_village],

      [t('موقعیت مشخص'), form.value.specific_place],
      [t('سرک نمبر'), form.value.street_number],
      [t('نمبر خانه'), form.value.home_number],
      [t('گذر'), form.value.guzar],

      [t('مقدار سرک'), form.value.street_amount],
      [t('تعداد عامل'), form.value.agent_quantity],
      [t('تعداد خانه'), form.value.home_quantity],

      [t('وظیفه قبلی'), form.value.old_job],
      [t('وظیفه جدید'), form.value.new_job],

      [t('سابقه جرم'), form.value.jorm_history],
      [t('تنظیم / وابستگی'), form.value.tanzim],

      [t('تفسیر صحیح / توضیحات'), form.value.tafseel_sahi],
      [t('ملاحظات'), form.value.notes],

      [t('مختصات'), form.value.coordinates],

      [t('کود منبع'), form.value.resource_code],
      [t('شماره منبع'), form.value.resource_phone_number],
    ];

    return `
<!doctype html>
<html dir="rtl" lang="fa">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${escapeHtml(t('فورم مُلکانو'))}</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 18px; color: #111; }
    h2 { margin: 0 0 12px; font-size: 18px; }
    .meta { font-size: 12px; color: #444; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    td { border: 1px solid #ddd; padding: 8px; vertical-align: top; font-size: 12px; }
    td.label { width: 32%; font-weight: 700; background: #fafafa; }
    .sig { margin-top: 28px; display: flex; gap: 16px; }
    .sig .box { flex: 1; border-top: 1px solid #333; padding-top: 6px; font-size: 12px; }
    @media print { body { padding: 0; } }
  </style>
</head>
<body>
  <h2>${escapeHtml(t('فورم مُلکانو'))}</h2>
  <div class="meta">${escapeHtml(t('شماره ریکارډ'))}: ${escapeHtml(rid)}</div>

  <table>
    ${rows
      .map(([k, v]) => {
        const val = String(v ?? '').trim();
        return `
          <tr>
            <td class="label">${escapeHtml(k)}</td>
            <td>${escapeHtml(val || '-')}</td>
          </tr>
        `;
      })
      .join('')}
  </table>

  <div class="sig">
    <div class="box">${escapeHtml(t('امضا مسئول'))}</div>
    <div class="box">${escapeHtml(t('امضا ثبت کننده'))}</div>
    <div class="box">${escapeHtml(t('تاریخ'))}: ____________</div>
  </div>
</body>
</html>
  `.trim();
  }

  function downloadPdf() {
    const html = buildExportHtml();
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.open();
    w.document.write(html);
    w.document.close();
    w.focus();
    setTimeout(() => w.print(), 250);
  }

  function downloadWord() {
    const html = buildExportHtml();
    const blob = new Blob([html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const namePart = form.value.id ? `mulkano_${form.value.id}` : 'mulkano_blank';
    const a = document.createElement('a');
    a.href = url;
    a.download = `${namePart}.doc`;
    document.body.appendChild(a);
    a.click();
    a.remove();

    setTimeout(() => URL.revokeObjectURL(url), 2000);
  }

  /* Init */
  onMounted(async () => {
    await repo.GetResourceCode?.();
    if (isEdit.value) await loadExisting();
  });
</script>

<style scoped>
  .section-title {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 4px;
    color: #37474f;
  }
  .ga-2 {
    gap: 8px;
  }
</style>
