<!-- src/views/masjids/MasjidsForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش مسجد') : t('ایجاد مسجد جدید')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ مسجد موجود') : t('ثبت یک ریکارډ مسجد جدید در سیستم')
      "
    >
      <template #actions>
        <v-btn
          variant="text"
          prepend-icon="mdi-arrow-left"
          class="text-none rounded-pill"
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
        <v-card class="simple-form-card" elevation="1">
          <!-- مسجد (نام + نوع) -->
          <div class="section-header mb-1">
            <span class="section-title">
              {{ isEdit ? t('ویرایش ریکارډ مسجد') : t('ایجاد ریکارډ مسجد') }}
            </span>
          </div>
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_name"
                :label="t('نام مسجد *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('masjid_name')"
                :error-messages="getErrorMessages('masjid_name')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_type"
                :label="t('نوع مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_type')"
                :error-messages="getErrorMessages('masjid_type')"
              />
            </v-col>
          </v-row>

          <!-- مسوول مسجد -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('مسوول مسجد') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_name"
                :label="t('نام مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_name')"
                :error-messages="getErrorMessages('responsible_name')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_f_name"
                :label="t('نام پدر مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_f_name')"
                :error-messages="getErrorMessages('responsible_f_name')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_position"
                :label="t('وظیفه/موقف مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_position')"
                :error-messages="getErrorMessages('responsible_position')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_mazhab"
                :label="t('مذهب مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_mazhab')"
                :error-messages="getErrorMessages('responsible_mazhab')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_old_job"
                :label="t('وظیفه قبلی مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_old_job')"
                :error-messages="getErrorMessages('responsible_old_job')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsible_now_job"
                :label="t('وظیفه فعلی مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_now_job')"
                :error-messages="getErrorMessages('responsible_now_job')"
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.responsible_tarfdary"
                :label="t('طرفداری/وابستگی')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_tarfdary')"
                :error-messages="getErrorMessages('responsible_tarfdary')"
              />
            </v-col>
          </v-row>

          <!-- آدرس مسوول -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('آدرس مسوول مسجد') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.responsible_main_province"
                v-model:district="form.responsible_main_district"
                :province-label="t('ولایت اصلی مسوول')"
                :district-label="t('ولسوالی اصلی مسوول')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.responsible_main_village"
                :label="t('قریه اصلی مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_main_village')"
                :error-messages="getErrorMessages('responsible_main_village')"
              />
            </v-col>

            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.responsible_now_province"
                v-model:district="form.responsible_now_district"
                :province-label="t('ولایت فعلی مسوول')"
                :district-label="t('ولسوالی فعلی مسوول')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.responsible_now_village"
                :label="t('قریه فعلی مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_now_village')"
                :error-messages="getErrorMessages('responsible_now_village')"
              />
            </v-col>
          </v-row>

          <!-- اجتماع مسجد -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('اجتماع مسجد') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_people_mazhab"
                :label="t('مذهب مردم مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_people_mazhab')"
                :error-messages="getErrorMessages('masjid_people_mazhab')"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="form.masjid_homes_quantity"
                type="number"
                :label="t('تعداد خانه‌های مربوط مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_homes_quantity')"
                :error-messages="getErrorMessages('masjid_homes_quantity')"
              />
            </v-col>
          </v-row>

          <!-- آدرس مسجد -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('آدرس مسجد') }}</span>
          </div>
          <v-row>
           <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.masjid_province"
                v-model:district="form.masjid_district"
                :province-label="t('ولایت مسجد ')"
                :district-label="t('ولسوالی مسجد ')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.masjid_zone"
                :label="t('زون مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_zone')"
                :error-messages="getErrorMessages('masjid_zone')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.masjid_district"
                :label="t('ولسوالی مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_district')"
                :error-messages="getErrorMessages('masjid_district')"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_village"
                :label="t('قریه مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_village')"
                :error-messages="getErrorMessages('masjid_village')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.masjid_specific_place"
                :label="t('موقعیت مشخص مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_specific_place')"
                :error-messages="getErrorMessages('masjid_specific_place')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.masjid_street_number"
                :label="t('شماره سرک مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_street_number')"
                :error-messages="getErrorMessages('masjid_street_number')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.masjid_coordinates"
                :label="t('مختصات مسجد')"
                variant="outlined"
                density="compact"
                :error="hasError('masjid_coordinates')"
                :error-messages="getErrorMessages('masjid_coordinates')"
              />
            </v-col>

            <!-- <v-col cols="12" md="4">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                variant="outlined"
                density="compact"
                :error="hasError('Province_addr')"
                :error-messages="getErrorMessages('Province_addr')"
              />
            </v-col> -->
          </v-row>

          <!-- منبع -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('منبع') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
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
          </v-row>

          <!-- یادداشت -->
          <div class="section-header mt-4 mb-1">
            <span class="section-title">{{ t('یادداشت') }}</span>
          </div>
          <v-row>
            <v-col cols="12">
              <v-textarea
                v-model="form.note"
                :label="t('ملاحظات')"
                variant="outlined"
                density="compact"
                auto-grow
                rows="2"
                :error="hasError('note')"
                :error-messages="getErrorMessages('note')"
              />
            </v-col>
          </v-row>

          <!-- Actions (✅ bottom + PDF/Word like your rule) -->
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ مسجد') }}
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

      <!-- Error Snackbar -->
      <v-snackbar v-model="showError" color="error" timeout="5000">
        {{ errorMessage }}
        <template #actions>
          <v-btn variant="text" @click="showError = false"> {{ t('بستن') }} </v-btn>
        </template>
      </v-snackbar>

      <!-- Success Snackbar -->
      <v-snackbar v-model="showSuccess" color="success" timeout="3000">
        {{ successMessage }}
        <template #actions>
          <v-btn variant="text" @click="showSuccess = false"> {{ t('بستن') }} </v-btn>
        </template>
      </v-snackbar>
    </v-container>
  </v-container>
</template>

<script setup>
  import { ref, computed, onMounted, watch, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useWakilanoRepository } from '@/stores/WakilanoRepository';

  const repo = useWakilanoRepository();
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

  /* FORM MODEL */
  const blankForm = () => ({
    id: null,

    masjid_name: '',
    masjid_type: '',

    responsible_name: '',
    responsible_f_name: '',
    responsible_position: '',
    responsible_mazhab: '',
    responsible_old_job: '',
    responsible_now_job: '',
    responsible_tarfdary: '',

    responsible_main_province: '',
    responsible_main_district: '',
    responsible_main_village: '',
    responsible_now_province: '',
    responsible_now_district: '',
    responsible_now_village: '',

    masjid_people_mazhab: '',
    masjid_homes_quantity: '',

    masjid_province: '',
    masjid_zone: '',
    masjid_district: '',
    masjid_village: '',
    masjid_specific_place: '',
    masjid_street_number: '',
    masjid_coordinates: '',
    Province_addr: '',

    resource_code: '',
    resource_phone: '',
    resource_id: null,

    note: '',
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
    form.value.resource_phone = found?.phone_number ?? '';
    form.value.resource_id = found?.id ?? null;
  }

  /* MAP FROM API */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

    b.masjid_name = x.masjid_name ?? '';
    b.masjid_type = x.masjid_type ?? '';

    b.responsible_name = x.responsible_name ?? '';
    b.responsible_f_name = x.responsible_f_name ?? '';
    b.responsible_position = x.responsible_position ?? '';
    b.responsible_mazhab = x.responsible_mazhab ?? '';
    b.responsible_old_job = x.responsible_old_job ?? '';
    b.responsible_now_job = x.responsible_now_job ?? '';
    b.responsible_tarfdary = x.responsible_tarfdary ?? '';

    b.responsible_main_province = x.responsible_main_province ?? '';
    b.responsible_main_district = x.responsible_main_district ?? '';
    b.responsible_main_village = x.responsible_main_village ?? '';
    b.responsible_now_province = x.responsible_now_province ?? '';
    b.responsible_now_district = x.responsible_now_district ?? '';
    b.responsible_now_village = x.responsible_now_village ?? '';

    b.masjid_people_mazhab = x.masjid_people_mazhab ?? '';
    b.masjid_homes_quantity = x.masjid_homes_quantity ?? '';

    b.masjid_province = x.masjid_province ?? '';
    b.masjid_zone = x.masjid_zone ?? '';
    b.masjid_district = x.masjid_district ?? '';
    b.masjid_village = x.masjid_village ?? '';
    b.masjid_specific_place = x.masjid_specific_place ?? '';
    b.masjid_street_number = x.masjid_street_number ?? '';
    b.masjid_coordinates = x.masjid_coordinates ?? '';
    b.Province_addr = x.Province_addr ?? '';

    const r = x.resource || {};
    b.resource_code = x.resource_code ?? r.code_number ?? '';
    b.resource_phone = x.resource_phone ?? r.phone_number ?? '';
    b.resource_id = x.resource_id ?? r.id ?? null;

    b.note = x.note ?? '';

    return b;
  }

  async function loadRecord() {
    const id = route.params.id;
    if (!id) {
      form.value = blankForm();
      return;
    }
    try {
      await repo.FetchMasjidData?.(id);
      const single = Array.isArray(repo.Masjid) ? repo.Masjid[0] : repo.Masjid || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات مسجد'));
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
    return toFormData(payload);
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    return toFormData(payload);
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
   SIMPLE EXPORT (NO IMAGE, NO APP HEADERS) ✅ works even for blank
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
      { label: t('نام مسجد'), value: v(form.value.masjid_name) },
      { label: t('نوع مسجد'), value: v(form.value.masjid_type) },

      { label: t('نام مسوول'), value: v(form.value.responsible_name) },
      { label: t('نام پدر مسوول'), value: v(form.value.responsible_f_name) },
      { label: t('وظیفه/موقف مسوول'), value: v(form.value.responsible_position) },
      { label: t('مذهب مسوول'), value: v(form.value.responsible_mazhab) },
      { label: t('وظیفه قبلی مسوول'), value: v(form.value.responsible_old_job) },
      { label: t('وظیفه فعلی مسوول'), value: v(form.value.responsible_now_job) },
      { label: t('طرفداری/وابستگی'), value: v(form.value.responsible_tarfdary) },

      { label: t('ولایت اصلی مسوول'), value: v(form.value.responsible_main_province) },
      { label: t('ولسوالی اصلی مسوول'), value: v(form.value.responsible_main_district) },
      { label: t('قریه اصلی مسوول'), value: v(form.value.responsible_main_village) },
      { label: t('ولایت فعلی مسوول'), value: v(form.value.responsible_now_province) },
      { label: t('ولسوالی فعلی مسوول'), value: v(form.value.responsible_now_district) },
      { label: t('قریه فعلی مسوول'), value: v(form.value.responsible_now_village) },

      { label: t('مذهب مردم مسجد'), value: v(form.value.masjid_people_mazhab) },
      { label: t('تعداد خانه‌های مربوط مسجد'), value: v(form.value.masjid_homes_quantity) },

      { label: t('ولایت مسجد'), value: v(form.value.masjid_province) },
      { label: t('زون مسجد'), value: v(form.value.masjid_zone) },
      { label: t('ولسوالی مسجد'), value: v(form.value.masjid_district) },
      { label: t('قریه مسجد'), value: v(form.value.masjid_village) },
      { label: t('موقعیت مشخص مسجد'), value: v(form.value.masjid_specific_place) },
      { label: t('شماره سرک مسجد'), value: v(form.value.masjid_street_number) },
      { label: t('مختصات مسجد'), value: v(form.value.masjid_coordinates) },
      { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },

      { label: t('کود منبع'), value: v(form.value.resource_code) },
      { label: t('شماره منبع'), value: v(form.value.resource_phone) },

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
    <h3 class="title">فورم مسجد</h3>
    <table class="simple-table">
      <tbody>
        ${rows}
      </tbody>
    </table>
    <div class="sig">
      <div>امضاء / مهر مسئول: ____________________</div>
      <div>تاریخ / امضاء ثبت کوونکی: ____________________</div>
    </div>
  `;
  }

  function buildDocumentHtml() {
    const bodyHtml = buildSimpleTableHtml();
    const safeId = esc(form.value.id || '');
    const safeName = esc(form.value.masjid_name || '');

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>masjid_${safeId}_${safeName}</title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }

    html, body {
      margin: 0;
      padding: 0;
      background: #ffffff;
      font-family: Tahoma, Arial, sans-serif;
      font-size: 12px;
      direction: rtl;
    }

    .title {
      text-align: center;
      margin: 22px 0 12px;
      font-size: 16px;
      font-weight: 700;
    }

    .simple-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin: 0 auto;
    }

    .simple-table th,
    .simple-table td {
      border: 1px solid #000;
      padding: 6px 8px;
      vertical-align: top;
      font-size: 12px;
    }

    .simple-table th {
      width: 35%;
      background: #f5f5f5;
      text-align: right;
      font-weight: 700;
    }

    .simple-table td {
      width: 65%;
      text-align: right;
      word-break: break-word;
    }

    .sig {
      margin-top: 18px;
      display: flex;
      justify-content: space-between;
      gap: 12px;
      font-size: 12px;
    }
  </style>
</head>
<body>
  ${bodyHtml}
</body>
</html>
`;
  }

  function downloadPdf() {
    const html = buildDocumentHtml();
    const w = window.open('', '_blank', 'width=1024,height=768');
    if (!w) return;

    w.document.open();
    w.document.write(html);
    w.document.close();

    w.onload = () => {
      w.focus();
      w.print();
    };
  }

  function downloadWord() {
    const html = buildDocumentHtml();
    const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = `مسجد_${safeFileName(form.value.masjid_name || 'جدید')}_${Date.now()}.doc`;
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

    if (!form.value.masjid_name?.trim()) {
      showErrorMessage(t('نام مسجد الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateMasjid?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/masjids').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateMasjid?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/masjids').catch(() => {});
        } else {
          showErrorMessage(t('ذخیرهٔ تغییرات ناکام شد'));
        }
      }
    } catch (e) {
      console.error(e);
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
    }

    clearBackendErrors();
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/masjids');
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
  /* main card */
  .simple-form-card {
    padding: 16px 16px 20px;
    border-radius: 12px;
  }

  /* sections */
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

  /* slightly tighter fields */
  :deep(.v-field--variant-outlined .v-field__input) {
    padding-top: 4px;
    padding-bottom: 4px;
    min-height: 36px;
  }

  /* prettier buttons */
  .primary-action-btn {
    padding-inline: 18px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.25);
  }

  .secondary-action-btn {
    padding-inline: 14px;
    font-weight: 500;
  }
</style>
