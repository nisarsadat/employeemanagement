<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش نقطه آسیب') : t('ایجاد نقطه آسیب جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ نقطه آسیب موجود')
          : t('ثبت یک ریکارډ نقطه آسیب جدید در سیستم')
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
          <!-- Location -->
          <div class="section-header">
            <span class="section-title">{{ t('موقعیت') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.province"
                v-model:district="form.district"
                :province-label="t('ولایت ')"
                :district-label="t('ولسوالی ')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="3">
              <FormatZoneAutocomplete v-model="form.zone" :label="t('زون ')" :disabled="saving" />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.village"
                :label="t('قریه/ناحیه')"
                variant="outlined"
                density="compact"
                :error="hasError('village')"
                :error-messages="getErrorMessages('village')"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.specific_place"
                :label="t('موقعیت مشخص')"
                variant="outlined"
                density="compact"
                :error="hasError('specific_place')"
                :error-messages="getErrorMessages('specific_place')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.specific_dot"
                :label="t('نقطه مشخص')"
                variant="outlined"
                density="compact"
                :error="hasError('specific_dot')"
                :error-messages="getErrorMessages('specific_dot')"
              />
            </v-col>
            <v-col cols="12" md="9">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                variant="outlined"
                density="compact"
                persistent-hint
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Threat / Status -->
          <!-- <div class="section-header">
            <span class="section-title">{{ t('تهدید و وضعیت') }}</span>
          </div> -->
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.tahdid_type"
                :label="t('نوع تهدید')"
                variant="outlined"
                density="compact"
                :error="hasError('tahdid_type')"
                :error-messages="getErrorMessages('tahdid_type')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.percentage"
                type="number"
                :label="t('درصدی')"
                variant="outlined"
                density="compact"
                :rules="[rules.number]"
                :error="hasError('percentage')"
                :error-messages="getErrorMessages('percentage')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.tahdid_place"
                :label="t('محل تهدید')"
                variant="outlined"
                density="compact"
                :error="hasError('tahdid_place')"
                :error-messages="getErrorMessages('tahdid_place')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.amnyat_khala"
                :label="t('خلأ امنیتی')"
                variant="outlined"
                density="compact"
                :error="hasError('amnyat_khala')"
                :error-messages="getErrorMessages('amnyat_khala')"
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="form.cameesa_tafseel"
                :label="t('تفصیل کمیسه/کمیسیه')"
                variant="outlined"
                density="compact"
                :error="hasError('cameesa_tafseel')"
                :error-messages="getErrorMessages('cameesa_tafseel')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Guards / Masool -->
          <div class="section-header">
            <span class="section-title">{{ t('مسوول و محافظ') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.guard_quantity"
                type="number"
                :label="t('تعداد محافظ')"
                variant="outlined"
                density="compact"
                :rules="[rules.number]"
                :error="hasError('guard_quantity')"
                :error-messages="getErrorMessages('guard_quantity')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.masool_name"
                :label="t('نام مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('masool_name')"
                :error-messages="getErrorMessages('masool_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.masool_f_name"
                :label="t('نام پدر مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('masool_f_name')"
                :error-messages="getErrorMessages('masool_f_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.masool_position"
                :label="t('وظیفه/موقف مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('masool_position')"
                :error-messages="getErrorMessages('masool_position')"
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.masool_phone"
                :label="t('شماره مسوول')"
                variant="outlined"
                density="compact"
                :error="hasError('masool_phone')"
                :error-messages="getErrorMessages('masool_phone')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Source & Note -->
          <div class="section-header">
            <span class="section-title">{{ t('معلومات منبع') }}</span>
          </div>
          <v-row>
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

            <!-- Resource mapping (code autocomplete -> id + phone) -->
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

          <!-- Actions (✅ add PDF/Word; no other logic change) -->
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
                variant="outlined"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-broom"
                @click="resetForm"
              >
                {{ t('پاک کردن فرم') }}
              </v-btn>

              <v-btn
                color="red"
                variant="outlined"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-file-pdf-box"
                @click="downloadPdf"
              >
                {{ t('PDF') }}
              </v-btn>

              <v-btn
                color="blue"
                variant="outlined"
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
  import { ref, computed, onMounted, watch, nextTick } from 'vue';
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

  /* FORM MODEL */
  const blankForm = () => ({
    id: null,

    province: '',
    zone: '',
    district: '',
    village: '',
    specific_place: '',
    specific_dot: '',
    coordinates: '',

    tahdid_type: '',
    percentage: '',
    tahdid_place: '',
    amnyat_khala: '',
    cameesa_tafseel: '',

    guard_quantity: '',
    masool_name: '',
    masool_f_name: '',
    masool_position: '',
    masool_phone: '',

    Province_addr: '',
    note: '',

    resource_id: null,
    resource_code: '',
    resource_phone: '',
  });

  const form = ref(blankForm());

  /* RULES */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    number: (v) => !v || /^[0-9]+(\.[0-9]+)?$/.test(String(v)) || t('فقط اعداد مجاز است'),
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

    b.province = x.province ?? '';
    b.zone = x.zone ?? '';
    b.district = x.district ?? '';
    b.village = x.village ?? '';

    b.specific_place = x.specific_place ?? '';
    b.specific_dot = x.specific_dot ?? '';
    b.coordinates = x.coordinates ?? '';

    b.tahdid_type = x.tahdid_type ?? '';
    b.percentage = x.percentage ?? '';
    b.tahdid_place = x.tahdid_place ?? '';
    b.amnyat_khala = x.amnyat_khala ?? '';
    b.cameesa_tafseel = x.cameesa_tafseel ?? '';

    b.guard_quantity = x.guard_quantity ?? '';
    b.masool_name = x.masool_name ?? '';
    b.masool_f_name = x.masool_f_name ?? '';
    b.masool_position = x.masool_position ?? '';
    b.masool_phone = x.masool_phone ?? '';

    b.Province_addr = x.Province_addr ?? '';
    b.note = x.note ?? '';

    const r = x.resource || {};
    b.resource_id = x.resource_id ?? r.id ?? null;
    b.resource_code = x.resource_code ?? r.code_number ?? '';
    b.resource_phone = x.resource_phone ?? r.phone_number ?? '';

    return b;
  }

  async function loadRecord() {
    const id = route.params.id;
    if (!id) {
      form.value = blankForm();
      return;
    }
    try {
      await repo.FetchDamagePlaceData?.(id);
      const single = Array.isArray(repo.DamagePlace) ? repo.DamagePlace[0] : repo.DamagePlace || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات نقطه آسیب'));
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
      { label: t('ولایت'), value: v(form.value.province) },
      { label: t('زون'), value: v(form.value.zone) },
      { label: t('ولسوالی'), value: v(form.value.district) },
      { label: t('قریه/ناحیه'), value: v(form.value.village) },

      { label: t('موقعیت مشخص'), value: v(form.value.specific_place) },
      { label: t('نقطه مشخص'), value: v(form.value.specific_dot) },
      { label: t('مختصات'), value: v(form.value.coordinates) },

      { label: t('نوع تهدید'), value: v(form.value.tahdid_type) },
      { label: t('درصدی'), value: v(form.value.percentage) },
      { label: t('محل تهدید'), value: v(form.value.tahdid_place) },
      { label: t('خلأ امنیتی'), value: v(form.value.amnyat_khala) },
      { label: t('تفصیل کمیسه/کمیسیه'), value: v(form.value.cameesa_tafseel) },

      { label: t('تعداد محافظ'), value: v(form.value.guard_quantity) },
      { label: t('نام مسوول'), value: v(form.value.masool_name) },
      { label: t('نام پدر مسوول'), value: v(form.value.masool_f_name) },
      { label: t('وظیفه/موقف مسوول'), value: v(form.value.masool_position) },
      { label: t('شماره مسوول'), value: v(form.value.masool_phone) },

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
    <h3 class="title">فورم نقطه آسیب</h3>
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
    const safeName = esc(form.value.province || '');

    return `<!DOCTYPE html>
<html dir="rtl" xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8" />
  <title>damageplace_${safeId}_${safeName}</title>
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
    link.download = `نقطه_آسیب_${safeFileName(form.value.province || 'جدید')}_${Date.now()}.doc`;
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

    if (!form.value.province?.trim() || !form.value.district?.trim()) {
      showErrorMessage(t('ولایت و ولسوالی الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateDamagePlace?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/damageplaces').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateDamagePlace?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/damageplaces').catch(() => {});
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
    router.push('/app/damageplaces');
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
