<!-- src/views/wakilano/ShoraForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش شورا') : t('ایجاد شورای جدید')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ شورا موجود') : t('ثبت یک ریکارډ شورای جدید در سیستم')
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
          <!-- Identity + stats -->
          <div class="section-header">
            <span class="section-title">
              {{ isEdit ? t('ویرایش ریکارډ شورا') : t('ایجاد ریکارډ شورا') }}
            </span>
          </div>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.shora_name"
                :label="t('نام شورا *')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('shora_name')"
                :error-messages="getErrorMessages('shora_name')"
              />
            </v-col>

            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.shora_goal"
                :label="t('هدف شورا')"
                variant="outlined"
                density="compact"
                :error="hasError('shora_goal')"
                :error-messages="getErrorMessages('shora_goal')"
              />
            </v-col>

            <!-- <v-col cols="12" md="4">
              <v-text-field
                v-model="form.shora_village"
                :label="t('قریه شورا')"
                variant="outlined"
                density="compact"
                :error="hasError('shora_village')"
                :error-messages="getErrorMessages('shora_village')"
              />
            </v-col> -->
          </v-row>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.people_quantity"
                type="number"
                :label="t('تعداد افراد')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('people_quantity')"
                :error-messages="getErrorMessages('people_quantity')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.week_meeting_quantity"
                type="number"
                :label="t('جلسات در هفته')"
                variant="outlined"
                density="compact"
                :rules="[rules.nonNegativeInt]"
                :error="hasError('week_meeting_quantity')"
                :error-messages="getErrorMessages('week_meeting_quantity')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.regester"
                :label="t('ثبت/راجستر')"
                variant="outlined"
                density="compact"
                :error="hasError('regester')"
                :error-messages="getErrorMessages('regester')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.shora_meeting_place"
                :label="t('محل جلسات شورا')"
                variant="outlined"
                density="compact"
                :error="hasError('shora_meeting_place')"
                :error-messages="getErrorMessages('shora_meeting_place')"
              />
            </v-col>
          </v-row>

          <!-- Responsible -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('مسؤول شورا') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_name"
                :label="t('مسؤول شورا')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_name')"
                :error-messages="getErrorMessages('responsible_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_f_name"
                :label="t('نام پدر مسؤول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_f_name')"
                :error-messages="getErrorMessages('responsible_f_name')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_job"
                :label="t('وظیفه مسؤول')"
                variant="outlined"
                density="compact"
                :error="hasError('responsible_job')"
                :error-messages="getErrorMessages('responsible_job')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsible_phone"
                :label="t('شماره تماس مسؤول')"
                variant="outlined"
                density="compact"
                :rules="[rules.phone]"
                :error="hasError('responsible_phone')"
                :error-messages="getErrorMessages('responsible_phone')"
              />
            </v-col>
          </v-row>

          <!-- Supplier -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('تمویل‌کننده') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.supplier_name"
                :label="t('تمویل‌کننده')"
                variant="outlined"
                density="compact"
                :error="hasError('supplier_name')"
                :error-messages="getErrorMessages('supplier_name')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.supplier_job"
                :label="t('وظیفه تمویل‌کننده')"
                variant="outlined"
                density="compact"
                :error="hasError('supplier_job')"
                :error-messages="getErrorMessages('supplier_job')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.supplier_phone"
                :label="t('شماره تمویل‌کننده')"
                variant="outlined"
                density="compact"
                :rules="[rules.phone]"
                :error="hasError('supplier_phone')"
                :error-messages="getErrorMessages('supplier_phone')"
              />
            </v-col>
          </v-row>

          <!-- Flags + Addresses -->
          <div class="section-header mt-4">
            <span class="section-title">{{ t('سایر اطلاعات و آدرس') }}</span>
          </div>
          <v-row>
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.emarat_tarfdary"
                :label="t('طرفداری امارت')"
                variant="outlined"
                density="compact"
                :error="hasError('emarat_tarfdary')"
                :error-messages="getErrorMessages('emarat_tarfdary')"
              />
            </v-col>

            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.shora_province"
                v-model:district="form.shora_district"
                :province-label="t('ولایت شورا')"
                :district-label="t('ولسوالی شورا')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="3">
               <FormatZoneAutocomplete
                    v-model="form.shora_zone"
                    :label="t('زون شورا')"
                    :disabled="saving"
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

            <!-- Resource -->
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
                variant="outlined"
                :rules="[rules.required]"
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

            <!-- Note -->
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

          <!-- Actions (BOTTOM + EXPORT) -->
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ شورا') }}
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

              <!-- ✅ Simple Export (NO image / NO app headers) - works even for blank -->
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
  import { useWakilanoRepository } from '@/stores/WakilanoRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

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

  /* messages */
  function showErrorMessage(message) {
    errorMessage.value = message || t('خطا رخ داد');
    showError.value = true;
  }
  function showSuccessMessage(message) {
    successMessage.value = message || t('عملیات موفقانه انجام شد');
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

    shora_name: '',
    shora_goal: '',
    shora_village: '',

    people_quantity: '',
    week_meeting_quantity: '',
    regester: '',
    shora_meeting_place: '',

    responsible_name: '',
    responsible_f_name: '',
    responsible_job: '',
    responsible_phone: '',

    supplier_name: '',
    supplier_job: '',
    supplier_phone: '',

    emarat_tarfdary: '',

    shora_province: '',
    shora_zone: '',
    shora_district: '',
    Province_addr: '',
    coordinates: '',

    resource_code: '',
    resource_phone: '',
    resource_id: null,

    note: '',
  });

  const form = ref(blankForm());

  /* RULES */
  const rules = {
    required: (v) => !!(v && String(v).trim()) || t('این فیلد الزامی است'),
    nonNegativeInt: (v) =>
      v === '' || v === null || /^\d+$/.test(String(v)) || t('فقط اعداد غیرمنفی'),
    phone: (v) => !v || /^[+]?\d[\d\s\-]{5,}$/.test(String(v)) || t('شماره تماس نادرست است'),
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

    b.shora_name = x.shora_name ?? '';
    b.shora_goal = x.shora_goal ?? '';
    b.shora_village = x.shora_village ?? '';

    b.people_quantity = x.people_quantity ?? '';
    b.week_meeting_quantity = x.week_meeting_quantity ?? '';
    b.regester = x.regester ?? '';
    b.shora_meeting_place = x.shora_meeting_place ?? '';

    b.responsible_name = x.responsible_name ?? '';
    b.responsible_f_name = x.responsible_f_name ?? '';
    b.responsible_job = x.responsible_job ?? '';
    b.responsible_phone = x.responsible_phone ?? '';

    b.supplier_name = x.supplier_name ?? '';
    b.supplier_job = x.supplier_job ?? '';
    b.supplier_phone = x.supplier_phone ?? '';

    b.emarat_tarfdary = x.emarat_tarfdary ?? '';

    b.shora_province = x.shora_province ?? '';
    b.shora_zone = x.shora_zone ?? '';
    b.shora_district = x.shora_district ?? '';
    b.Province_addr = x.Province_addr ?? '';
    b.coordinates = x.coordinates ?? '';

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
      await repo.FetchShoraData?.(id);
      const single = Array.isArray(repo.Shora) ? repo.Shora[0] : repo.Shora || {};
      form.value = { ...mapFromApi(single || {}) };
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات شورا'));
    }
  }

  /* FORM DATA HELPERS */
  function toFormData(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
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
   SIMPLE EXPORT (NO image, NO app headers) ✅ works even for blank
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
      { label: t('نام شورا'), value: v(form.value.shora_name) },
      { label: t('هدف شورا'), value: v(form.value.shora_goal) },
      { label: t('قریه شورا'), value: v(form.value.shora_village) },

      { label: t('تعداد افراد'), value: v(form.value.people_quantity) },
      { label: t('جلسات در هفته'), value: v(form.value.week_meeting_quantity) },
      { label: t('ثبت/راجستر'), value: v(form.value.regester) },
      { label: t('محل جلسات شورا'), value: v(form.value.shora_meeting_place) },

      { label: t('مسؤول شورا'), value: v(form.value.responsible_name) },
      { label: t('نام پدر مسؤول'), value: v(form.value.responsible_f_name) },
      { label: t('وظیفه مسؤول'), value: v(form.value.responsible_job) },
      { label: t('شماره تماس مسؤول'), value: v(form.value.responsible_phone) },

      { label: t('تمویل‌کننده'), value: v(form.value.supplier_name) },
      { label: t('وظیفه تمویل‌کننده'), value: v(form.value.supplier_job) },
      { label: t('شماره تمویل‌کننده'), value: v(form.value.supplier_phone) },

      { label: t('طرفداری امارت'), value: v(form.value.emarat_tarfdary) },

      { label: t('ولایت شورا'), value: v(form.value.shora_province) },
      { label: t('زون شورا'), value: v(form.value.shora_zone) },
      { label: t('ولسوالی شورا'), value: v(form.value.shora_district) },

      { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },
      { label: t('مختصات'), value: v(form.value.coordinates) },

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
    <h3 class="title">فورم شورا</h3>
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
    const safeName = esc(form.value.shora_name || '');

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>shora_${safeId}_${safeName}</title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }
    html, body {
      margin: 0; padding: 0; background: #fff;
      font-family: Tahoma, Arial, sans-serif;
      font-size: 12px; direction: rtl;
    }
    .title { text-align: center; margin: 22px 0 12px; font-size: 16px; font-weight: 700; }
    .simple-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 auto; }
    .simple-table th, .simple-table td {
      border: 1px solid #000; padding: 6px 8px; vertical-align: top; font-size: 12px;
    }
    .simple-table th { width: 35%; background: #f5f5f5; text-align: right; font-weight: 700; }
    .simple-table td { width: 65%; text-align: right; word-break: break-word; }
    .sig { margin-top: 18px; display: flex; justify-content: space-between; gap: 12px; font-size: 12px; }
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
    link.download = `شورا_${safeFileName(form.value.shora_name || 'جدید')}_${Date.now()}.doc`;
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

    if (!form.value.shora_name?.trim()) {
      showErrorMessage(t('نام شورا الزامی است'));
      return;
    }

    saving.value = true;

    try {
      const fd = toFormData({ ...form.value });

      if (!isEdit.value) {
        const created = await repo.CreateShora?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/shoras').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const updated = await repo.UpdateShora?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/shoras').catch(() => {});
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
    router.push('/app/shoras');
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

  .ga-2 {
    gap: 8px;
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
