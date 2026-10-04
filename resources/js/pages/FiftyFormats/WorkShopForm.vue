<!-- src/views/workshops/WorkShopForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش ورکشاپ') : t('ایجاد ورکشاپ جدید')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ ورکشاپ موجود') : t('ثبت یک ریکارډ ورکشاپ جدید در سیستم')
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
          <!-- ✅ Picture row (ONLY addition in template) -->
          <v-row>
            <v-col cols="12" md="3">
              <PicturePicker
                v-model:file="pictureFile"
                v-model:url="form.picture_url"
                :label="t('تصویر')"
                :alt="t('عکس')"
                :change-text="t('تغییر عکس')"
                :view-text="t('نمایش')"
                :close-text="t('بستن')"
                :preview-title="t('پیش نمایش')"
                @change="({ changed }) => (pictureChanged = !!changed)"
              />
            </v-col>
          </v-row>

          <!-- ================= WORKSHOP INFO ================= -->
          <div class="section-header">
            <span class="section-title">{{ t('مشخصات ورکشاپ') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.workshop_name"
                :label="t('نام ورکشاپ *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('workshop_name')"
                :error-messages="getErrorMessages('workshop_name')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.workshop_type"
                :label="t('نوع ورکشاپ')"
                variant="outlined"
                density="compact"
                :error="hasError('workshop_type')"
                :error-messages="getErrorMessages('workshop_type')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- ================= RESPONSIBLE ================= -->
          <div class="section-header">
            <span class="section-title">{{ t('مسئول ورکشاپ') }}</span>
          </div>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_name"
                :label="t('نام مسئول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_name')"
                :error-messages="getErrorMessages('responsible_name')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_f_name"
                :label="t('نام پدر مسئول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_f_name')"
                :error-messages="getErrorMessages('responsible_f_name')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_position"
                :label="t('وظیفه مسئول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_position')"
                :error-messages="getErrorMessages('responsible_position')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_phone"
                :label="t('شماره مسئول')"
                variant="outlined"
                density="compact"
                :rules="[rules.phoneMinMax]"
                :error="hasError('responsible_phone')"
                :error-messages="getErrorMessages('responsible_phone')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- ================= ADDRESS ================= -->
          <div class="section-header">
            <span class="section-title">{{ t('آدرس ورکشاپ') }}</span>
          </div>

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

            <v-col cols="12" md="3">
              <FormatZoneAutocomplete v-model="form.zone" :label="t('آمریت')" :disabled="saving" />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.village"
                :label="t('قریه')"
                variant="outlined"
                density="compact"
                :error="hasError('village')"
                :error-messages="getErrorMessages('village')"
              />
            </v-col>

            <v-col cols="12" md="5">
              <v-text-field
                v-model="form.specific_place"
                :label="t('موقعیت مشخص')"
                variant="outlined"
                density="compact"
                :error="hasError('specific_place')"
                :error-messages="getErrorMessages('specific_place')"
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                variant="outlined"
                density="compact"
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.workers_quantity"
                :label="t('تعداد کارگران')"
                type="number"
                variant="outlined"
                density="compact"
                :error="hasError('workers_quantity')"
                :error-messages="getErrorMessages('workers_quantity')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- ================= RESOURCE + NOTE ================= -->
          <div class="section-header">
            <span class="section-title">{{ t('معلوات منبع') }}</span>
          </div>

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
                v-model="form.resource_phone"
                :label="t('شماره منبع')"
                variant="outlined"
                density="compact"
                readonly
              />
            </v-col>

            <v-col cols="12" md="12">
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

          <!-- ================= ACTIONS (PDF/WORD bottom) ================= -->
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
                color="primary"
                variant="tonal"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-printer"
                @click="printForm"
              >
                {{ t('چاپ') }}
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
  import { useSchoolRepository } from '@/stores/SchoolRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

  /* ✅ Picture (ONLY addition) */
  import PicturePicker from '@/components/form/PicturePicker.vue';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';
  import { printBlankForm } from '@/utils/printBlankForm';

  const repo = useSchoolRepository();
  const route = useRoute();
  const router = useRouter();

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* MODE */
  const isEdit = computed(() => !!route.params.id);

  /* ✅ Picture state (ONLY addition) */
  const pictureFile = ref(null); // File | null
  const pictureChanged = ref(false); // boolean

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

    /* ✅ picture url from API (ONLY addition) */
    picture_url: null,

    workshop_name: '',
    workshop_type: '',
    workers_quantity: '',

    responsible_name: '',
    responsible_f_name: '',
    responsible_position: '',
    responsible_phone: '',

    province: '',
    zone: '',
    district: '',
    village: '',
    specific_place: '',
    coordinates: '',
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
    phoneMinMax: (v) => {
      if (!v) return true;
      const s = String(v).replace(/\s+/g, '');
      if (!/^\d+$/.test(s)) return t('شماره تیلیفون باید فقط عدد باشد');
      if (s.length < 10) return t('شماره تیلیفون باید حداقل ۱۰ رقم باشد');
      if (s.length > 13) return t('شماره تیلیفون باید حداکثر ۱۳ رقم باشد');
      return true;
    },
  };

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
  form.value.resource_phone = r.phone_number ?? '';
}

function onResourceCleared() {
  form.value.resource_id = null;
  form.value.resource_code = '';
  form.value.resource_phone = '';
}

  /* MAP FROM API */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

    /* ✅ picture mapping (ONLY addition) */
    b.picture_url = x.picture ?? x.picture_url ?? null;

    b.workshop_name = x.workshop_name ?? '';
    b.workshop_type = x.workshop_type ?? '';
    b.workers_quantity = x.workers_quantity ?? '';

    b.responsible_name = x.responsible_name ?? '';
    b.responsible_f_name = x.responsible_f_name ?? '';
    b.responsible_position = x.responsible_position ?? '';
    b.responsible_phone = x.responsible_phone ?? '';

    b.province = x.province ?? '';
    b.zone = x.zone ?? '';
    b.district = x.district ?? '';
    b.village = x.village ?? '';
    b.specific_place = x.specific_place ?? '';
    b.coordinates = x.coordinates ?? '';
    b.Province_addr = x.Province_addr ?? '';

    const r = x.resource || {};
    b.resource_id = x.resource_id ?? r.id ?? null;
    b.resource_code = x.resource_code ?? r.code_number ?? '';
    b.resource_phone = x.resource_phone ?? r.phone_number ?? '';

    b.note = x.note ?? '';

    /* ✅ reset picker state after load (ONLY addition) */
    pictureFile.value = null;
    pictureChanged.value = false;

    return b;
  }

  async function loadRecord() {
    const id = route.params.id;
    if (!id) {
      form.value = blankForm();
      return;
    }

    try {
      await repo.FetchWorkShopData?.(id);
      const single = Array.isArray(repo.WorkShop) ? repo.WorkShop[0] : repo.WorkShop || {};
      if (!single) return;

      form.value = { ...mapFromApi(single) };
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات ورکشاپ'));
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

  /* ✅ picture append (ONLY changes here) */
  function buildCreateFormData() {
    const payload = { ...form.value };
    const fd = toFormData(payload);
    if (pictureFile.value) fd.append('picture', pictureFile.value);
    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    const fd = toFormData(payload);
    if (pictureChanged.value && pictureFile.value) fd.append('picture', pictureFile.value);
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
      { label: t('نام ورکشاپ'), value: v(form.value.workshop_name) },
      { label: t('نوع ورکشاپ'), value: v(form.value.workshop_type) },
      { label: t('تعداد کارگران'), value: v(form.value.workers_quantity) },

      { label: t('نام مسئول'), value: v(form.value.responsible_name) },
      { label: t('نام پدر مسئول'), value: v(form.value.responsible_f_name) },
      { label: t('وظیفه مسئول'), value: v(form.value.responsible_position) },
      { label: t('شماره مسئول'), value: v(form.value.responsible_phone) },

      { label: t('ولایت'), value: v(form.value.province) },
      { label: t('آمریت'), value: v(form.value.zone) },
      { label: t('ولسوالی'), value: v(form.value.district) },
      { label: t('قریه'), value: v(form.value.village) },

      { label: t('موقعیت مشخص'), value: v(form.value.specific_place) },
      { label: t('مختصات'), value: v(form.value.coordinates) },
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
    <h3 class="title">فورم ورکشاپ</h3>
    <table class="simple-table">
      <tbody>
        ${rows}
      </tbody>
    </table>
    <div class="sig">
      <div>${t('امضا مسئول')}: ____________________</div>
      <div>${t('امضا ثبت کننده')}: ____________________</div>
      <div>${t('تاریخ')}: ____________________</div>
    </div>
  `;
  }

  function buildDocumentHtml() {
    const bodyHtml = buildSimpleTableHtml();
    const safeId = esc(form.value.id || '');
    const safeName = esc(form.value.workshop_name || '');

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>workshop_${safeId}_${safeName}</title>
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

  /* PRINT BLANK FORM (empty fields, same design, one A4 page) */
  function printForm(e) {
    printBlankForm(e, { onError: showErrorMessage });
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
    link.download = `ورکشاپ_${safeFileName(form.value.workshop_name || 'جدید')}_${Date.now()}.doc`;
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

    if (!form.value.workshop_name?.trim()) {
      showErrorMessage(t('نام ورکشاپ الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateWorkShop?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/workshops').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateWorkShop?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/workshops').catch(() => {});
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
    /* ✅ reset picture picker state too (ONLY addition) */
    pictureFile.value = null;
    pictureChanged.value = false;

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
    router.push('/app/workshops');
  }

  /* INIT */
  onMounted(async () => {
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

  /* buttons */
  .primary-action-btn {
    padding-inline: 18px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.25);
  }
  .secondary-action-btn {
    padding-inline: 14px;
    font-weight: 500;
  }
  .ga-2 {
    gap: 8px;
  }
</style>
