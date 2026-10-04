<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح صرافیانو') : t('اضافه کردن یک ریکارډ جدید صرافیانو')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ موجود صرافیانو')
          : t('ثبت یک ریکارډ جدید صرافیانو در سیستم')
      "
    >
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
          <v-row>
            <!-- ✅ Picture (same as other form) -->
            <v-col cols="12" md="3">
              <div class="d-flex flex-column align-center gap-2">
                <div class="text-subtitle-2 font-weight-medium mb-2">
                  {{ t('تصویر') }}
                </div>

                <div class="pic-wrap-simple">
                  <input
                    ref="pictureInput"
                    class="hidden-file"
                    type="file"
                    accept="image/*"
                    @change="onPickPicture"
                  />

                  <v-avatar
                    size="88"
                    class="mb-2 cursor-pointer primary-avatar"
                    :key="form.previewUrl || form.picture_url || form.picture || 'no-picture'"
                    @click="triggerPicture"
                  >
                    <v-img
                      v-if="form.previewUrl || form.picture_url || form.picture"
                      :src="normalizePicture(form.previewUrl || form.picture_url || form.picture)"
                      :alt="t('عکس')"
                      cover
                    />
                    <v-icon v-else size="40">mdi-account</v-icon>
                  </v-avatar>

                  <div class="d-flex flex-column align-center gap-1">
                    <v-btn
                      size="small"
                      color="primary"
                      variant="tonal"
                      class="text-none rounded-pill soft-btn my-2"
                      @click.stop="triggerPicture"
                    >
                      <v-icon start size="22">mdi-pencil</v-icon>
                      {{ t('تغییر عکس') }}
                    </v-btn>

                    <v-btn
                      v-if="form.previewUrl || form.picture_url || form.picture"
                      size="small"
                      color="secondary"
                      variant="tonal"
                      class="text-none rounded-pill soft-btn"
                      @click.stop="openPreview(form.previewUrl || form.picture_url || form.picture)"
                    >
                      <v-icon start size="22">mdi-eye</v-icon>
                      {{ t('نمایش') }}
                    </v-btn>
                  </div>
                </div>
              </div>
            </v-col>

            <!-- ✅ The rest of form (no other changes) -->
            <v-col cols="12" md="9">
              <!-- مشخصات اساسی -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">
                    {{ t('مشخصات اساسی') }}
                  </h3>
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.sarafi_name"
                    :label="t('نام صرافی')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.sarafi_type"
                    :label="t('نوع صرافی')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
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
              </v-row>

              <v-row class="mt-1">
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.position"
                    :label="t('وظیفه/موقعیت')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
                </v-col>

                <v-col cols="12" md="9">
                  <v-text-field
                    v-model="form.phone_number"
                    :label="t('شماره تماس')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                    maxlength="10"
                  />
                </v-col>
              </v-row>

              <!-- آدرس -->
              <v-row class="mt-2">
                <v-col cols="12">
                  <h3 class="section-title">
                    {{ t('آدرس') }}
                  </h3>
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12" md="12">
                  <ProvinceDistrictNameSelect
                    v-model:province="form.province"
                    v-model:district="form.district"
                    :province-label="t('ولایت')"
                    :district-label="t('ولسوالی')"
                    :mdProvince="6"
                    :mdDistrict="6"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <FormatZoneAutocomplete
                    v-model="form.zone"
                    :label="t('آمریت')"
                    :disabled="saving"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.village"
                    :label="t('قریه/ناحیه')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
                </v-col>
              </v-row>

              <v-row class="mt-1">
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.specific_place"
                    :label="t('موقعیت مشخص')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
                </v-col>
              </v-row>

              <!-- بازار و دکان -->
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.market_name"
                    :label="t('نام بازار/مارکیټ')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.shop_number"
                    :label="t('نمبر دکان')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.cordinate"
                    :label="t('مختصات')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                    persistent-hint
                    :rules="[rules.coordinatesOptional]"
                  />
                </v-col>
              </v-row>

              <!-- حواله و کارمندان -->
              <v-row class="mt-2">
                <v-col cols="12">
                  <h3 class="section-title">
                    {{ t('حواله و کارمندان') }}
                  </h3>
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.provinces_hawala"
                    :label="t('حواله به ولایات')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.countries_hawala"
                    :label="t('حواله به کشورها')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
                </v-col>
              </v-row>

              <v-row class="mt-1">
                <v-col cols="12" md="12">
                  <v-text-field
                    v-model="form.employee_quantity"
                    :label="t('تعداد کارمند')"
                    type="number"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                    :rules="[rules.nonNegativeNumberOptional]"
                  />
                </v-col>
              </v-row>

              <!-- منبع -->
              <v-row class="mt-2">
                <v-col cols="12">
                  <h3 class="section-title">
                    {{ t('معلومات منبع') }}
                  </h3>
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12" md="4">
                  <ResourceRemoteAutocomplete
                    v-model="form.resource_id"
                    :label="t('کود منبع')"
                    density="compact"
                    variant="outlined"
                    :rules="[rules.required]"
                    :item-title="resourceTitle"
                    @selected="onResourceSelected"
                    @cleared="onResourceCleared"
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

              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="form.notes"
                    :label="t('ملاحظات')"
                    density="compact"
                    variant="outlined"
                    hide-details="auto"
                  />
                </v-col>
              </v-row>

              <!-- ACTIONS -->
              <v-row class="mt-4">
                <v-col cols="12" class="d-flex justify-end ga-2 flex-wrap">
                  <!-- ✅ Export buttons beside submit (works even blank) -->
                  <v-btn
                    color="primary"
                    variant="tonal"
                    class="text-none rounded-pill secondary-action-btn"
                    prepend-icon="mdi-printer"
                    @click="printForm"
                  >
                    {{ t('چاپ') }}
                  </v-btn>

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
                    {{ isEdit ? t('ذخیره تغییرات') : t('ثبت') }}
                  </v-btn>

                  <v-btn color="secondary" variant="text" class="text-none" @click="resetForm">
                    {{ t('پاک کردن فرم') }}
                  </v-btn>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-card>
      </v-form>
    </v-container>

    <!-- ✅ IMAGE PREVIEW DIALOG (same as other form) -->
    <v-dialog v-model="previewOpen" max-width="520">
      <v-card>
        <v-toolbar density="compact" color="primary" :title="t('پیش نمایش')" class="text-white" />
        <v-card-text class="d-flex justify-center">
          <v-img
            :src="normalizePicture(previewSrc)"
            :alt="t('پیش نمایش')"
            max-width="480"
            max-height="420"
            cover
          />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn color="primary" variant="flat" @click="previewOpen = false">
            {{ t('بستن') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';
  import { printBlankForm } from '@/utils/printBlankForm';

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
    name: '',
    sarafi_name: '',
    sarafi_type: '',
    father_name: '',
    position: '',
    phone_number: '',
    Province_addr: '',

    province: '',
    zone: '',
    district: '',
    village: '',
    specific_place: '',
    cordinate: '',

    market_name: '',
    shop_number: '',

    provinces_hawala: '',
    countries_hawala: '',
    employee_quantity: '',

    resource_code: '',
    resource_phone_number: '',
    resource_id: null,

    notes: '',

    // ✅ media (added)
    picture: null,
    picture_url: null,
    previewUrl: null,
  });

  /* Rules */
  const rules = {
    required: (v) => !!String(v ?? '').trim() || t('این فیلد الزامی است'),

    // empty OK, otherwise "lat, lng"
    coordinatesOptional: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      if (!/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/.test(s)) return t('مختصات درست نیست');
      return true;
    },

    // empty OK, otherwise must be number >= 0
    nonNegativeNumberOptional: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      const n = Number(s);
      if (!Number.isFinite(n)) return t('عدد درست نیست');
      if (n < 0) return t('عدد منفی قبول نیست');
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

  /* ✅ Picture (same logic as other form) */
  const pictureInput = ref(null);
  const pictureFile = ref(null);
  const pictureChanged = ref(false);

  const previewOpen = ref(false);
  const previewSrc = ref(null);

  function triggerPicture() {
    pictureInput.value?.click();
  }

  function onPickPicture(e) {
    const file = e.target.files?.[0] ?? null;
    pictureFile.value = file;
    pictureChanged.value = !!file;

    if (file) {
      if (form.value.previewUrl && form.value.previewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(form.value.previewUrl);
      }
      form.value.previewUrl = URL.createObjectURL(file);
    } else {
      form.value.previewUrl = form.value.picture_url || null;
    }

    if (e.target) e.target.value = '';
  }

  function normalizePicture(src) {
    if (!src) return null;
    let clean = String(src).trim();

    if (clean.startsWith('blob:') || clean.startsWith('data:')) return clean;

    clean = clean.replace(/\\\//g, '/');

    if (clean.startsWith('http://') || clean.startsWith('https://')) return clean;
    if (clean.startsWith('/')) return clean;

    return '/' + clean.replace(/^\/+/, '');
  }

  function openPreview(src) {
    const base = src || form.value.previewUrl || form.value.picture_url || form.value.picture;
    const normalized = normalizePicture(base);
    previewSrc.value = normalized;
    if (previewSrc.value) previewOpen.value = true;
  }

  function resourceTitle(r) {
    if (!r) return '';
    const code = r.code_number ?? '';
    const name = r.name ?? '';
    return [code, name].filter(Boolean).join(' - ');
  }

  function onResourceSelected(r) {
    if (!r) return;
    form.value.resource_id = r.id ?? null;
    form.value.resource_code = r.code_number ?? '';
    form.value.resource_phone_number = r.phone_number ?? '';
  }

  function onResourceCleared() {
    form.value.resource_id = null;
    form.value.resource_code = '';
    form.value.resource_phone_number = '';
  }

  /* Helpers */
  function resetForm() {
    if (form.value.previewUrl && form.value.previewUrl.startsWith('blob:')) {
      URL.revokeObjectURL(form.value.previewUrl);
    }

    form.value = {
      id: isEdit.value ? form.value.id : null,
      name: '',
      sarafi_name: '',
      sarafi_type: '',
      father_name: '',
      position: '',
      phone_number: '',
      Province_addr: '',

      province: '',
      zone: '',
      district: '',
      village: '',
      specific_place: '',
      cordinate: '',

      market_name: '',
      shop_number: '',

      provinces_hawala: '',
      countries_hawala: '',
      employee_quantity: '',

      resource_code: '',
      resource_phone_number: '',
      resource_id: null,

      notes: '',

      // ✅ keep media reset too
      picture: null,
      picture_url: isEdit.value ? form.value.picture_url : null,
      previewUrl: isEdit.value ? form.value.picture_url : null,
    };

    pictureFile.value = null;
    pictureChanged.value = false;

    formRef.value?.resetValidation?.();
  }

  function fillFormFromRecord(x = {}) {
    const fullUrl = x.picture ?? null;

    form.value = {
      id: x.id ?? null,
      name: x.name ?? '',
      sarafi_name: x.sarafi_name ?? '',
      sarafi_type: x.sarafi_type ?? '',
      father_name: x.father_name ?? '',
      position: x.position ?? '',
      phone_number: x.phone_number ?? '',
      Province_addr: x.Province_addr ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',
      specific_place: x.specific_place ?? '',
      cordinate: x.cordinate ?? '',

      market_name: x.market_name ?? '',
      shop_number: x.shop_number ?? '',

      provinces_hawala: x.provinces_hawala ?? '',
      countries_hawala: x.countries_hawala ?? '',
      employee_quantity: x.employee_quantity ?? '',

      resource_code: x.resource_code ?? '',
      resource_phone_number: x.resource_phone_number ?? '',
      resource_id: x.resource_id ?? null,

      notes: x.notes ?? '',

      // ✅ media from API
      picture: x.picture ?? null,
      picture_url: fullUrl,
      previewUrl: fullUrl,
    };

    pictureFile.value = null;
    pictureChanged.value = false;
  }

  /* Build FormData */
  function toFormData(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  function buildCreateFormData() {
    const payload = { ...form.value };

    delete payload.previewUrl;
    delete payload.picture_url;
    delete payload.picture;

    const fd = toFormData(payload);

    if (pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };

    delete payload.previewUrl;
    delete payload.picture_url;
    delete payload.picture;

    const fd = toFormData(payload);

    if (pictureChanged.value && pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

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
      if (isEdit.value && form.value.id) {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateSarafyano?.(form.value.id, fd);
        if (updated?.id) {
          showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
          goBack();
          return;
        }
      } else {
        const fd = buildCreateFormData();
        const created = await repo.CreateSarafyano?.(fd);
        if (created?.id) {
          showSuccessMsg(t('صرافیانو جدید موفقانه ثبت شد'));
          goBack();
          return;
        }
      }

      showErrorMessage(t('خطا در ذخیره معلومات'));
    } catch (e) {
      console.error('Sarafyano save error', e);
      showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
    } finally {
      saving.value = false;
    }
  }

  /* Navigation */
  function goBack() {
    router.push('/app/sarafyanos');
  }

  /* Load on edit */
  async function loadExisting() {
    const id = route.params.id;
    if (!id) return;
    try {
      await repo.FetchSarafyanoData?.(id);
      const rec =
        repo.Sarafyano && Array.isArray(repo.Sarafyano) ? repo.Sarafyano[0] : repo.Sarafyano || {};
      fillFormFromRecord(rec);
    } catch (e) {
      console.error('loadExisting error', e);
      showErrorMessage(t('خطا در دریافت معلومات صرافیانو'));
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
      [t('نام شخص'), form.value.name],
      [t('نام صرافی'), form.value.sarafi_name],
      [t('نوع صرافی'), form.value.sarafi_type],
      [t('نام پدر'), form.value.father_name],

      [t('وظیفه/موقعیت'), form.value.position],
      [t('شماره تماس'), form.value.phone_number],
      [t('ولایت مربوطه'), form.value.Province_addr],

      [t('ولایت'), form.value.province],
      [t('آمریت'), form.value.zone],
      [t('ولسوالی'), form.value.district],
      [t('قریه/ناحیه'), form.value.village],
      [t('موقعیت مشخص'), form.value.specific_place],
      [t('مختصات'), form.value.cordinate],

      [t('نام بازار/مارکیټ'), form.value.market_name],
      [t('نمبر دکان'), form.value.shop_number],

      [t('حواله به ولایات'), form.value.provinces_hawala],
      [t('حواله به کشورها'), form.value.countries_hawala],
      [t('تعداد کارمند'), form.value.employee_quantity],

      [t('کود منبع'), form.value.resource_code],
      [t('شماره منبع'), form.value.resource_phone_number],

      [t('ملاحظات'), form.value.notes],
    ];

    return `
<!doctype html>
<html dir="rtl" lang="fa">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${escapeHtml(t('فورم صرافیانو'))}</title>
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
  <h2>${escapeHtml(t('فورم صرافیانو'))}</h2>
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

  /* PRINT BLANK FORM (empty fields, same design, one A4 page) */
  function printForm(e) {
    printBlankForm(e, { onError: showErrorMessage });
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

    const namePart = form.value.id ? `sarafyano_${form.value.id}` : 'sarafyano_blank';
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

  /* ✅ picture styles (same as other form) */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .pic-wrap-simple {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .cursor-pointer {
    cursor: pointer;
  }
  .primary-avatar {
    border: 2px solid rgba(33, 150, 243, 0.25);
  }
  .gap-2 {
    gap: 8px;
  }
  .soft-btn {
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
  }
</style>
