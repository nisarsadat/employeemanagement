<!-- src/views/fifthyFormats/LuchakanoForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش لچکانو') : t('اضافه کردن لچکانو')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ لچکانو موجود') : t('ثبت یک ریکارډ جدید لچکانو در سیستم')
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
        <v-card class="simple-form-card" elevation="1">
          <!-- BASIC INFO -->

          <!-- ✅ PICTURE PART (added) -->
          <v-row class="mb-2">
            <v-col cols="12" class="d-flex align-center ga-4 flex-wrap">
              <input
                ref="pictureInputRef"
                type="file"
                accept="image/*"
                class="d-none"
                @change="onPickPicture"
              />

              <v-avatar size="86" rounded="xl" class="picture-avatar" @click="triggerPickPicture">
                <v-img v-if="currentPictureUrl" :src="currentPictureUrl" cover />
                <v-icon v-else size="34">mdi-account-circle</v-icon>
              </v-avatar>

              <div class="d-flex flex-column ga-2">
                <div class="text-body-2 font-weight-bold">
                  {{ t('عکس') }}
                </div>

                <div class="d-flex ga-2 flex-wrap">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    class="text-none rounded-pill"
                    prepend-icon="mdi-image-plus"
                    @click="triggerPickPicture"
                  >
                    {{ t('انتخاب عکس') }}
                  </v-btn>

                  <v-btn
                    v-if="currentPictureUrl"
                    color="info"
                    variant="tonal"
                    class="text-none rounded-pill"
                    prepend-icon="mdi-eye"
                    @click="openPicturePreview"
                  >
                    {{ t('پیش نمایش') }}
                  </v-btn>

                  <v-btn
                    v-if="currentPictureUrl"
                    color="warning"
                    variant="tonal"
                    class="text-none rounded-pill"
                    prepend-icon="mdi-trash-can-outline"
                    @click="removePicture"
                  >
                    {{ t('حذف عکس') }}
                  </v-btn>
                </div>
              </div>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.nickname"
                :label="t('تخلص')"
                variant="outlined"
                density="compact"
                :error="hasError('nickname')"
                :error-messages="getErrorMessages('nickname')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.name"
                :label="t('نام *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('name')"
                :error-messages="getErrorMessages('name')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.father_name"
                :label="t('نام پدر')"
                variant="outlined"
                density="compact"
                :error="hasError('father_name')"
                :error-messages="getErrorMessages('father_name')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- MAIN ADDRESS -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('آدرس اصلی') }}</span>
          </div>
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
                variant="outlined"
                density="compact"
                :error="hasError('main_village')"
                :error-messages="getErrorMessages('main_village')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- CURRENT ADDRESS -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('آدرس فعلی') }}</span>
          </div>
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
            <v-col cols="12" md="3">
              <FormatZoneAutocomplete
                v-model="form.now_zone"
                :label="t('زون فعلی')"
                :disabled="saving"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_village"
                :label="t('قریه/ناحیه فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_village')"
                :error-messages="getErrorMessages('now_village')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.now_specific_place"
                :label="t('موقعیت مشخص فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_specific_place')"
                :error-messages="getErrorMessages('now_specific_place')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_street_number"
                :label="t('شماره سرک فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_street_number')"
                :error-messages="getErrorMessages('now_street_number')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_home_number"
                :label="t('شماره خانه فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_home_number')"
                :error-messages="getErrorMessages('now_home_number')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                variant="outlined"
                density="compact"
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- JOB / GUN / CONTACT -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('وظیفه، اسلحه و تماس') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.old_job"
                :label="t('وظیفه قبلی')"
                variant="outlined"
                density="compact"
                :error="hasError('old_job')"
                :error-messages="getErrorMessages('old_job')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.job"
                :label="t('وظیفه فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('job')"
                :error-messages="getErrorMessages('job')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.gun_type"
                :label="t('نوع اسلحه')"
                variant="outlined"
                density="compact"
                :error="hasError('gun_type')"
                :error-messages="getErrorMessages('gun_type')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field v-model="form.supplier" :label="t('حمایه کننده')" variant="outlined" />
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                variant="outlined"
                density="compact"
                maxlength="10"
                :error="hasError('phone_number')"
                :error-messages="getErrorMessages('phone_number')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.history_jorm"
                :label="t('سابقه جرم')"
                variant="outlined"
                density="compact"
                :error="hasError('history_jorm')"
                :error-messages="getErrorMessages('history_jorm')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.takhlof_type"
                :label="t('نوع تخلف')"
                variant="outlined"
                density="compact"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- RESOURCE + HISTORY / NOTE -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('معلومات منبع') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
                :rules="[rules.required]"
                variant="outlined"
                density="compact"
                clearable
                @update:model-value="onResourceCodeChange"
                :error="hasError('resource_code')"
                :error-messages="getErrorMessages('resource_code')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.resource_phone"
                :label="t('شماره منبع')"
                variant="outlined"
                density="compact"
                readonly
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="form.note"
                :label="t('یادداشت')"
                variant="outlined"
                density="compact"
                :error="hasError('note')"
                :error-messages="getErrorMessages('note')"
              />
            </v-col>
          </v-row>

          <!-- Actions -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2 flex-wrap">
              <v-btn
                color="primary"
                variant="elevated"
                class="text-none rounded-pill primary-action-btn"
                :loading="saving"
                type="submit"
                :prepend-icon="isEdit ? 'mdi-content-save-edit-outline' : 'mdi-plus-circle-outline'"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت') }}
              </v-btn>

              <v-btn
                color="warning"
                variant="tonal"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-broom"
                @click="resetForm"
              >
                {{ t('پاک کردن فرم') }}
              </v-btn>

              <v-btn
                color="red"
                variant="tonal"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-file-pdf-box"
                @click="downloadPdf"
              >
                {{ t('PDF') }}
              </v-btn>

              <v-btn
                color="blue"
                variant="tonal"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-file-word-box"
                @click="downloadWord"
              >
                {{ t('Word') }}
              </v-btn>
            </v-col>
          </v-row>
        </v-card>
      </v-form>

      <!-- ✅ picture preview dialog (added) -->
      <v-dialog v-model="picturePreviewOpen" max-width="720">
        <v-card rounded="xl">
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="text-body-1 font-weight-bold">{{ t('پیش نمایش عکس') }}</div>
            <v-btn icon variant="text" @click="picturePreviewOpen = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>
          <v-divider />
          <v-card-text>
            <v-img v-if="currentPictureUrl" :src="currentPictureUrl" cover class="rounded-xl" />
            <div v-else class="text-caption text-medium-emphasis">{{ t('عکسی موجود نیست') }}</div>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn
              variant="tonal"
              class="text-none rounded-pill"
              @click="picturePreviewOpen = false"
            >
              {{ t('بستن') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Error Snackbar -->
      <v-snackbar v-model="showError" color="error" timeout="5000">
        {{ errorMessage }}
        <template #actions>
          <v-btn variant="text" @click="showError = false">
            {{ t('بستن') }}
          </v-btn>
        </template>
      </v-snackbar>

      <!-- Success Snackbar -->
      <v-snackbar v-model="showSuccess" color="success" timeout="3000">
        {{ successMessage }}
        <template #actions>
          <v-btn variant="text" @click="showSuccess = false">
            {{ t('بستن') }}
          </v-btn>
        </template>
      </v-snackbar>
    </v-container>
  </v-container>
</template>

<script setup>
  import { ref, computed, onMounted, watch, nextTick, onBeforeUnmount } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useMawadRepository } from '@/stores/MawadRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

  const repo = useMawadRepository();
  const route = useRoute();
  const router = useRouter();

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* MODE */
  const isEdit = computed(() => !!route.params.id);

  /* FORM / STATE */
  const formRef = ref(null);
  const formValid = ref(false);
  const saving = ref(false);

  const showError = ref(false);
  const errorMessage = ref('');
  const showSuccess = ref(false);
  const successMessage = ref('');

  function showErrorMessage(message) {
    errorMessage.value = message;
    showError.value = true;
  }
  function showSuccessMessage(message) {
    successMessage.value = message;
    showSuccess.value = true;
  }

  /* BACKEND ERRORS */
  const backendErrors = ref({});
  function setBackendErrors(errors) {
    backendErrors.value = errors || {};
  }
  function clearBackendErrors() {
    backendErrors.value = {};
  }
  function hasError(field) {
    return !!backendErrors.value?.[field];
  }
  function getErrorMessages(field) {
    return backendErrors.value?.[field] ?? [];
  }

  /* ✅ PICTURE STATE (added) */
  const pictureInputRef = ref(null);
  const pictureFile = ref(null);
  const picturePreviewUrl = ref(''); // blob URL
  const pictureServerUrl = ref(''); // from API (picture_url)

  const picturePreviewOpen = ref(false);

  const currentPictureUrl = computed(() => {
    return picturePreviewUrl.value || pictureServerUrl.value || '';
  });

  function revokeBlobUrl() {
    if (picturePreviewUrl.value && picturePreviewUrl.value.startsWith('blob:')) {
      URL.revokeObjectURL(picturePreviewUrl.value);
    }
    picturePreviewUrl.value = '';
  }

  function triggerPickPicture() {
    pictureInputRef.value?.click?.();
  }

  function onPickPicture(e) {
    const file = e?.target?.files?.[0];
    if (!file) return;

    // basic guard (image)
    if (!String(file.type || '').startsWith('image/')) {
      showErrorMessage(t('فایل انتخاب شده عکس نیست'));
      return;
    }

    pictureFile.value = file;

    revokeBlobUrl();
    picturePreviewUrl.value = URL.createObjectURL(file);
  }

  function removePicture() {
    pictureFile.value = null;
    pictureServerUrl.value = '';
    revokeBlobUrl();
    if (pictureInputRef.value) pictureInputRef.value.value = '';
  }

  function openPicturePreview() {
    if (!currentPictureUrl.value) return;
    picturePreviewOpen.value = true;
  }

  onBeforeUnmount(() => {
    revokeBlobUrl();
  });

  /* FORM MODEL */
  const blankForm = () => ({
    id: null,

    nickname: '',
    name: '',
    father_name: '',

    main_province: '',
    main_district: '',
    main_village: '',

    now_province: '',
    now_zone: '',
    now_district: '',
    now_village: '',
    now_specific_place: '',
    now_street_number: '',
    now_home_number: '',
    coordinates: '',

    old_job: '',
    job: '',
    gun_type: '',
    phone_number: '',

    history_jorm: '',
    takhlof_type: '',
    supplier: '',
    note: '',
    Province_addr: '',

    resource_code: '',
    resource_phone: '',
    resource_id: null,

    // ✅ keep for mapping only (not required to send)
    picture_url: '',
  });

  const form = ref(blankForm());

  /* RULES */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
  };

  /* RESOURCES DROPDOWN */
  const resources = computed(() => repo.resourcecode || []);

  function onResourceCodeChange(code) {
    const found = resources.value.find((r) => r.code_number === code);
    form.value.resource_code = code || '';
    form.value.resource_phone = found?.phone_number ?? '';
    form.value.resource_id = found?.id ?? null;
  }

  /* MAP FROM API */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

    b.nickname = x.nickname ?? '';
    b.name = x.name ?? '';
    b.father_name = x.father_name ?? '';

    b.main_province = x.main_province ?? '';
    b.main_district = x.main_district ?? '';
    b.main_village = x.main_village ?? '';

    b.now_province = x.now_province ?? '';
    b.now_zone = x.now_zone ?? '';
    b.now_district = x.now_district ?? '';
    b.now_village = x.now_village ?? '';
    b.now_specific_place = x.now_specific_place ?? '';
    b.now_street_number = x.now_street_number ?? '';
    b.now_home_number = x.now_home_number ?? '';
    b.coordinates = x.coordinates ?? '';

    b.old_job = x.old_job ?? '';
    b.job = x.job ?? '';
    b.gun_type = x.gun_type ?? '';
    b.phone_number = x.phone_number ?? '';

    b.history_jorm = x.history_jorm ?? '';
    b.takhlof_type = x.takhlof_type ?? '';
    b.supplier = x.supplier ?? '';
    b.note = x.note ?? '';
    b.Province_addr = x.Province_addr ?? '';

    const r = x.resource || {};
    b.resource_id = x.resource_id ?? r.id ?? null;
    b.resource_code = x.resource_code ?? r.code_number ?? '';
    b.resource_phone = x.resource_phone ?? r.phone_number ?? '';

    // ✅ picture_url from API
    b.picture_url = x.picture_url ?? '';
    return b;
  }

  async function loadRecord() {
    const id = route.params.id;
    if (!id) {
      form.value = blankForm();
      pictureFile.value = null;
      pictureServerUrl.value = '';
      revokeBlobUrl();
      return;
    }
    try {
      await repo.FetchLuchakanoData?.(id);
      const single =
        repo.Luchakano && Array.isArray(repo.Luchakano) ? repo.Luchakano[0] : repo.Luchakano || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };

      // ✅ set server picture preview (edit mode)
      pictureServerUrl.value = mapped.picture_url || '';
      pictureFile.value = null;
      revokeBlobUrl();
    } catch (e) {
      console.error('loadRecord error', e);
      showErrorMessage(t('خطا در دریافت معلومات لچکانو'));
    }
  }

  /* FORM DATA HELPERS */
  function toFormData(obj) {
    const fd = new FormData();
    Object.entries(obj).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  function buildCreateFormData() {
    const payload = { ...form.value };
    delete payload.id;

    // ✅ do not send picture_url
    delete payload.picture_url;

    const fd = toFormData(payload);

    // ✅ append picture file
    if (pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    delete payload.id;

    // ✅ do not send picture_url
    delete payload.picture_url;

    const fd = toFormData(payload);

    // ✅ append picture file
    if (pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

    return fd;
  }

  /* SCROLL TO FIRST INVALID */
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

  /* =========================
   SIMPLE EXPORT (NO IMAGE, NO APP HEADERS)
========================= */
  function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, (m) => {
      const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
      return map[m];
    });
  }
  function safeFileName(s) {
    return String(s ?? '')
      .replace(/[\\/:*?"<>|]/g, '-')
      .replace(/\s+/g, ' ')
      .trim();
  }
  function v(x) {
    return x && String(x).trim() ? x : '—';
  }
  function exportRows() {
    return [
      { label: t('نام'), value: v(form.value.name) },
      { label: t('تخلص'), value: v(form.value.nickname) },
      { label: t('نام پدر'), value: v(form.value.father_name) },

      { label: t('ولایت اصلی'), value: v(form.value.main_province) },
      { label: t('ولسوالی اصلی'), value: v(form.value.main_district) },
      { label: t('قریه/ناحیه اصلی'), value: v(form.value.main_village) },

      { label: t('ولایت فعلی'), value: v(form.value.now_province) },
      { label: t('زون فعلی'), value: v(form.value.now_zone) },
      { label: t('ولسوالی فعلی'), value: v(form.value.now_district) },
      { label: t('قریه/ناحیه فعلی'), value: v(form.value.now_village) },

      { label: t('موقعیت مشخص فعلی'), value: v(form.value.now_specific_place) },
      { label: t('شماره سرک فعلی'), value: v(form.value.now_street_number) },
      { label: t('شماره خانه فعلی'), value: v(form.value.now_home_number) },
      { label: t('مختصات'), value: v(form.value.coordinates) },

      { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },

      { label: t('وظیفه قبلی'), value: v(form.value.old_job) },
      { label: t('وظیفه فعلی'), value: v(form.value.job) },
      { label: t('نوع اسلحه'), value: v(form.value.gun_type) },
      { label: t('تمیا کننده'), value: v(form.value.supplier) },
      { label: t('شماره تماس'), value: v(form.value.phone_number) },

      { label: t('کود منبع'), value: v(form.value.resource_code) },
      { label: t('شماره منبع'), value: v(form.value.resource_phone) },

      { label: t('سابقه جرم'), value: v(form.value.history_jorm) },
      { label: t('نوع تخلف'), value: v(form.value.takhlof_type) },
      { label: t('یادداشت'), value: v(form.value.note) },
    ];
  }
  function buildSimpleTableHtml() {
    const rows = exportRows()
      .map(
        (r) => `
      <tr>
        <th>${esc(r.label)}</th>
        <td>${esc(r.value)}</td>
      </tr>
    `
      )
      .join('');

    return `
    <h3 class="title">فورم لچکانو</h3>
    (table omitted for brevity)
  `;
  }
  function buildDocumentHtml() {
    const bodyHtml = buildSimpleTableHtml();
    const safeId = esc(form.value.id || '');
    const safeName = esc(form.value.name || '');

    return `<!DOCTYPE html>
<html dir="rtl" xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8" />
  <title>luchakano_${safeId}_${safeName}</title>
</head>
<body>
  ${bodyHtml}
</body>
</html>`;
  }

  function downloadPdf() {
    const html = buildDocumentHtml();
    const w = window.open('', '_blank', 'width=1024,height=768');
    if (!w) return;

    w.document.open();
    w.document.write(html);
    w.document.close();

    setTimeout(() => {
      w.focus();
      w.print();
    }, 350);
  }

  function downloadWord() {
    const html = buildDocumentHtml();
    const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = `لچکانو_${safeFileName(form.value.name || 'جدید')}_${Date.now()}.doc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    URL.revokeObjectURL(url);
    showSuccessMessage(t('فایل Word با موفقیت ایجاد شد'));
  }

  /* SUBMIT */
  async function onSubmit() {
    clearBackendErrors();

    const f = formRef.value;
    if (!f) return;

    const { valid } = await f.validate();
    if (!valid) {
      showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
      await nextTick();
      scrollToFirstInvalid();
      return;
    }

    if (!form.value.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateLuchakano?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/luchakanos').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateLuchakano?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/luchakanos').catch(() => {});
        } else {
          showErrorMessage(t('ذخیرهٔ تغییرات ناکام شد'));
        }
      }
    } catch (e) {
      console.error('submit error', e);
      showErrorMessage(t('خطا در ذخیره اطلاعات'));
    } finally {
      saving.value = false;
    }
  }

  /* RESET & NAVIGATION */
  function resetForm() {
    if (isEdit.value) {
      loadRecord();
    } else {
      form.value = blankForm();
      pictureFile.value = null;
      pictureServerUrl.value = '';
      revokeBlobUrl();
    }

    clearBackendErrors();
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/luchakanos');
  }

  /* INIT */
  onMounted(async () => {
    await repo.GetResourceCode?.();
    if (isEdit.value) {
      await loadRecord();
    }
  });
</script>

<style scoped>
  .simple-form-card {
    padding: 16px 16px 20px;
    border-radius: 12px;
  }

  .section-header {
    display: flex;
    align-items: center;
    padding-block: 2px;
  }
  .section-title {
    font-size: 0.9rem;
    font-weight: 600;
    opacity: 0.8;
  }

  :deep(.v-field--variant-outlined .v-field__input) {
    padding-top: 4px;
    padding-bottom: 4px;
    min-height: 36px;
  }

  .primary-action-btn {
    padding-inline: 18px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.25);
  }
  .secondary-action-btn {
    padding-inline: 14px;
    font-weight: 500;
  }

  /* ✅ picture avatar */
  .picture-avatar {
    border: 1px dashed rgba(0, 0, 0, 0.25);
    cursor: pointer;
    overflow: hidden;
  }
</style>
