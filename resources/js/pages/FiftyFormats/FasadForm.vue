<template>
  <!-- APP BAR -->
  <AppBar
    :title="isEdit ? t('اصلاح فساد') : t('اضافه کردن یک ریکارډ جدید فساد')"
    :subtitle="
      isEdit ? t('ویرایش جزئیات یک ریکارډ موجود فساد') : t('ثبت یک ریکارډ جدید فساد در سیستم')
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

  <div class="fasad-page" dir="rtl">
    <!-- TOP BAR (only for app view, hidden on print) -->
    <div class="top-bar no-print">
      <div>
        <button type="button" class="top-btn" @click="downloadPdf">PDF</button>
        <button type="button" class="top-btn" @click="downloadWord">Word</button>
      </div>
    </div>

    <!-- FORM -->
    <v-container class="mt-4 form-container no-print" style="max-width: 1200px">
      <v-form
        ref="formRef"
        v-model="formValid"
        lazy-validation
        @submit.prevent="onSubmit"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <v-card class="pa-4" elevation="2">
          <!-- هدر بخش‌ها -->
          <v-row class="mt-1">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-account-details" size="small" class="ml-1"></v-icon>
                {{ t('مشخصات اساسی') }}
              </h3>
            </v-col>
          </v-row>

          <!-- Identity -->
          <v-row dense>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.nick_name"
                :label="t('تخلص')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.name"
                :label="t('نام') + ' *'"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.required]"
                required
                color="primary"
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
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- آدرس اصلی -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-home-map-marker" size="small" class="ml-1"></v-icon>
                {{ t('آدرس اصلی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_province"
                :label="t('ولایت اصلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.main_district"
                :label="t('ولسوالی اصلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="4">
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
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-map-marker-radius" size="small" class="ml-1"></v-icon>
                {{ t('آدرس فعلی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_province"
                :label="t('ولایت فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_zone"
                :label="t('زون فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_district"
                :label="t('ولسوالی فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_village"
                :label="t('قریه/ناحیه فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- موقعیت / خانه -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-home-analytics" size="small" class="ml-1"></v-icon>
                {{ t('موقعیت و خانه') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.specific_place"
                :label="t('موقعیت مشخص فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.street_number"
                :label="t('شماره سرک')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.home_number"
                :label="t('شماره خانه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- شبکه/تماس/فعالیت -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-account-network" size="small" class="ml-1"></v-icon>
                {{ t('فعالیت، شبکه و تماس') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.jabha"
                :label="t('جبهه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.supplier_name"
                :label="t('نام تمویل‌کننده')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsibility"
                :label="t('مسئولیت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.activity_zone"
                :label="t('ساحه فعالیت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <v-row dense class="mt-1">
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.friends_number"
                :label="t('تعداد دوستان')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                type="number"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.bond_distance"
                :label="t('فاصله ارتباط')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.now_job"
                :label="t('وظیفه فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- سوابق بند / ارتباط -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-history" size="small" class="ml-1"></v-icon>
                {{ t('سوابق بند / ارتباط') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.last_band_reason"
                :label="t('دلیل آخرین بند/ارتباط')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.band_place"
                :label="t('محل بند/ارتباط')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- مختصات -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-map-marker-path" size="small" class="ml-1"></v-icon>
                {{ t('مختصات و نقشه') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                hint="مثال: 34.1234, 69.1234"
                persistent-hint
              />
            </v-col>
          </v-row>

          <!-- منبع -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-source-branch" size="small" class="ml-1"></v-icon>
                {{ t('معلومات منبع') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
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
                v-model="form.resource_phone"
                :label="t('شماره منبع')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                readonly
              />
            </v-col>
          </v-row>

          <!-- ACTIONS -->
          <v-row class="mt-6">
            <v-col cols="12">
              <v-divider class="mb-4"></v-divider>
              <div class="d-flex justify-space-between align-center">
                <div class="d-flex ga-2">
                  <v-btn
                    color="primary"
                    variant="flat"
                    class="text-none"
                    :prepend-icon="isRtl ? 'mdi-content-save' : null"
                    :append-icon="!isRtl ? 'mdi-content-save' : null"
                    :loading="saving"
                    type="submit"
                  >
                    {{ isEdit ? t('ذخیره تغییرات') : t('ثبت فساد جدید') }}
                  </v-btn>
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    class="text-none"
                    @click="resetForm"
                    :prepend-icon="isRtl ? 'mdi-delete-sweep' : null"
                    :append-icon="!isRtl ? 'mdi-delete-sweep' : null"
                  >
                    {{ t('پاک کردن فرم') }}
                  </v-btn>
                </div>

                <div class="d-flex ga-2">
                  <v-btn
                    color="red"
                    variant="outlined"
                    class="text-none"
                    @click="downloadPdf"
                    :prepend-icon="isRtl ? 'mdi-file-pdf-box' : null"
                    :append-icon="!isRtl ? 'mdi-file-pdf-box' : null"
                  >
                    {{ t('PDF') }}
                  </v-btn>
                  <v-btn
                    color="blue"
                    variant="outlined"
                    class="text-none"
                    @click="downloadWord"
                    :prepend-icon="isRtl ? 'mdi-file-word-box' : null"
                    :append-icon="!isRtl ? 'mdi-file-word-box' : null"
                  >
                    {{ t('Word') }}
                  </v-btn>
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card>
      </v-form>
    </v-container>
  </div>

  <!-- ❗ Error Snackbar -->
  <v-snackbar v-model="showError" color="error" timeout="5000">
    <div class="d-flex align-center">
      <v-icon icon="mdi-alert-circle" class="mr-2"></v-icon>
      <span>{{ errorMessage }}</span>
    </div>
    <template #actions>
      <v-btn variant="text" @click="showError = false">
        {{ t('بستن') }}
      </v-btn>
    </template>
  </v-snackbar>

  <!-- ✅ Success Snackbar -->
  <v-snackbar v-model="showSuccess" color="success" timeout="4000">
    <div class="d-flex align-center">
      <v-icon icon="mdi-check-circle" class="mr-2"></v-icon>
      <span>{{ successMessage }}</span>
    </div>
    <template #actions>
      <v-btn variant="text" @click="showSuccess = false">
        {{ t('بستن') }}
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';
  import AppBar from '@/components/AppBar.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';

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
    nick_name: '',
    name: '',
    father_name: '',
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
    jabha: '',
    supplier_name: '',
    responsibility: '',
    activity_zone: '',
    friends_number: '',
    phone_number: '',
    bond_distance: '',
    last_band_reason: '',
    band_place: '',
    now_job: '',
    Province_addr: '',
    coordinates: '',
    resource_code: '',
    resource_phone: '',
    resource_id: null,
  });

  /* Rules */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
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
    form.value.resource_phone = found?.phone_number ?? '';
    form.value.resource_id = found?.id ?? null;
  }

  /* Helpers */
  function resetForm() {
    form.value = {
      id: isEdit.value ? form.value.id : null,
      nick_name: '',
      name: '',
      father_name: '',
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
      jabha: '',
      supplier_name: '',
      responsibility: '',
      activity_zone: '',
      friends_number: '',
      phone_number: '',
      bond_distance: '',
      last_band_reason: '',
      band_place: '',
      now_job: '',
      Province_addr: '',
      coordinates: '',
      resource_code: '',
      resource_phone: '',
      resource_id: null,
    };
    formRef.value?.resetValidation?.();
  }

  function fillFormFromRecord(x = {}) {
    form.value = {
      id: x.id ?? null,
      nick_name: x.nick_name ?? '',
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
      jabha: x.jabha ?? '',
      supplier_name: x.supplier_name ?? '',
      responsibility: x.responsibility ?? '',
      activity_zone: x.activity_zone ?? '',
      friends_number: x.friends_number ?? '',
      phone_number: x.phone_number ?? '',
      bond_distance: x.bond_distance ?? '',
      last_band_reason: x.last_band_reason ?? '',
      band_place: x.band_place ?? '',
      now_job: x.now_job ?? '',
      Province_addr: x.Province_addr ?? '',
      coordinates: x.coordinates ?? '',
      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
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

  /* =========================
   SIMPLE EXPORT (NO IMAGE, NO HEADERS)
========================= */
  function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, (m) => {
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
      };
      return map[m];
    });
  }

  function exportRows() {
    return [
      { label: 'تخلص', value: form.value.nick_name },
      { label: 'نام', value: form.value.name },
      { label: 'نام پدر', value: form.value.father_name },
      { label: 'ولایت مربوطه', value: form.value.Province_addr },
      { label: 'وظیفه فعلی', value: form.value.now_job },

      { label: 'ولایت اصلی', value: form.value.main_province },
      { label: 'ولسوالی اصلی', value: form.value.main_district },
      { label: 'قریه/ناحیه اصلی', value: form.value.main_village },

      { label: 'ولایت فعلی', value: form.value.now_province },
      { label: 'زون فعلی', value: form.value.now_zone },
      { label: 'ولسوالی فعلی', value: form.value.now_district },
      { label: 'قریه/ناحیه فعلی', value: form.value.now_village },

      { label: 'موقعیت مشخص فعلی', value: form.value.specific_place },
      { label: 'شماره سرک', value: form.value.street_number },
      { label: 'شماره خانه', value: form.value.home_number },

      { label: 'جبهه', value: form.value.jabha },
      { label: 'نام تمویل‌کننده', value: form.value.supplier_name },
      { label: 'مسئولیت', value: form.value.responsibility },
      { label: 'ساحه فعالیت', value: form.value.activity_zone },
      { label: 'تعداد دوستان', value: form.value.friends_number },
      { label: 'شماره تماس', value: form.value.phone_number },
      { label: 'فاصله ارتباط', value: form.value.bond_distance },

      { label: 'دلیل آخرین بند/ارتباط', value: form.value.last_band_reason },
      { label: 'محل بند/ارتباط', value: form.value.band_place },

      { label: 'مختصات', value: form.value.coordinates },

      { label: 'کود منبع', value: form.value.resource_code },
      { label: 'شماره منبع', value: form.value.resource_phone },
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
    <h3 class="title">فورم فساد</h3>
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

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>فساد_${esc(form.value.id || '')}_${esc(form.value.name || '')}</title>
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
      margin: 0 0 12px;
      font-size: 16px;
      font-weight: 700;
      margin-top: 22px;
    }

    .simple-table {
      width: 95%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-left: 12px;
      margin-right: 12px;
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
      margin-right: 16px;
      margin-right: 16px;
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

    // No images => simple and reliable
    w.focus();
    w.print();
  }

  function downloadWord() {
    const html = buildDocumentHtml();

    const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = `فساد_${form.value.name || 'جدید'}_${Date.now()}.doc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    URL.revokeObjectURL(url);
    showSuccessMsg(t('فایل Word با موفقیت ایجاد شد'));
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
      const payload = { ...form.value };
      const fd = toFormData(payload);

      if (isEdit.value && form.value.id) {
        const updated = await repo.UpdateFasad?.(form.value.id, fd);
        if (updated && updated.id) {
          showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
          goBack();
          return;
        }
      } else {
        const created = await repo.CreateFasad?.(fd);
        if (created && created.id) {
          showSuccessMsg(t('فساد جدید موفقانه ثبت شد'));
          goBack();
          return;
        }
      }

      showErrorMessage(t('خطا در ذخیره معلومات'));
    } catch (e) {
      console.error('Fasad save error', e);
      showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
    } finally {
      saving.value = false;
    }
  }

  /* Navigation */
  function goBack() {
    router.push('/app/fasads');
  }

  /* Load on edit */
  async function loadExisting() {
    const id = route.params.id;
    if (!id) return;
    try {
      await repo.FetchFasadData?.(id);
      const rec = repo.Fasad && Array.isArray(repo.Fasad) ? repo.Fasad[0] : repo.Fasad || {};
      fillFormFromRecord(rec);
    } catch (e) {
      console.error('loadExisting error', e);
      showErrorMessage(t('خطا در دریافت معلومات فساد'));
    }
  }

  /* Init */
  onMounted(async () => {
    await repo.GetResourceCode?.();
    if (isEdit.value) {
      await loadExisting();
    }
  });
</script>

<style scoped>
  /* ===== GENERAL ===== */
  .fasad-page {
    background: #ffffff !important;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 12px;
  }

  /* ===== SCREEN VIEW ===== */
  .no-print {
    display: block;
  }

  /* top buttons (not printed) */
  .top-bar {
    display: flex;
    justify-content: flex-start;
    gap: 4px;
    margin: 8px 16px;
  }

  .top-btn {
    font-size: 12px;
    padding: 4px 12px;
    background: #ffffff;
    border: 1px solid #000;
    cursor: pointer;
    border-radius: 4px;
  }

  .top-btn:hover {
    background: #f5f5f5;
  }

  /* Form container */
  .form-container {
    margin: 0 auto;
  }

  /* Form styling */
  .section-title {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #37474f;
    background-color: #f5f5f5;
    padding: 8px 12px;
    border-radius: 4px;
    border-right: 4px solid #2196f3;
  }

  .v-divider {
    border-color: rgba(0, 0, 0, 0.1);
  }

  .v-btn {
    border-radius: 4px;
    font-weight: 500;
    letter-spacing: 0;
  }

  /* ===== RESPONSIVE ===== */
  @media (max-width: 960px) {
    .form-container {
      padding: 8px !important;
    }

    .v-card {
      padding: 16px !important;
    }

    .d-flex.justify-space-between {
      flex-direction: column;
      gap: 16px;
    }

    .d-flex.justify-space-between > div {
      width: 100%;
      justify-content: center;
    }
  }
</style>
