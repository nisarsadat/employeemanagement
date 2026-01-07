<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح معلومات مواد') : t('اضافه کردن یک ریکارډ مواد')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ موجود مواد') : t('ثبت یک ریکارډ جدید مواد در سیستم')
      "
      @toggle-drawer="drawer = !drawer"
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
              <h3 class="section-title">
                {{ t('مشخصات اساسی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.mawad_type"
                :label="t('نوع مواد')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.nickname"
                :label="t('نام مستعار')"
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
                :rules="[rules.required]"
                hide-details="auto"
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

          <!-- آدرس اصلی -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('آدرس اصلی') }}
              </h3>
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
              <h3 class="section-title">
                {{ t('آدرس فعلی') }}
              </h3>
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
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.now_village"
                :label="t('قریه/ناحیه فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- موقعیت عمومی -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('موقعیت عمومی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.province"
                :label="t('ولایت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.zone"
                :label="t('زون')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.district"
                :label="t('ولسوالی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.village"
                :label="t('قریه/ناحیه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- موقعیت/تماس -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('موقعیت و تماس') }}
              </h3>
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
                v-model="form.coordinates"
                :label="t('مختصات')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                hint="مثال: 34.1234, 69.1234"
                persistent-hint
                :rules="[rules.coordinatesOptional]"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.mawad_sales_phone"
                :label="t('شماره فروش مواد')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- اطلاعات دیگر -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('اطلاعات دیگر') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.history_form"
                :label="t('تاریخچه/فورم')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.supplier"
                :label="t('تمویل‌کننده')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.note"
                :label="t('یادداشت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ولایت مربوطه -->
          <v-row class="mt-2">
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
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
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2 flex-wrap">
              <!-- ✅ Simple Export (NO image / NO app headers) -->
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت مواد جدید') }}
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
  import { ref, computed, watch, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useMawadRepository } from '@/stores/MawadRepository';

  const repo = useMawadRepository();
  const route = useRoute();
  const router = useRouter();

  /* i18n / RTL */
  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));
  const drawer = ref(false);

  const isEdit = computed(() => !!route.params.id);

  /* Form state */
  const formRef = ref(null);
  const formValid = ref(false);
  const saving = ref(false);

  const form = ref({
    id: null,
    mawad_type: '',
    nickname: '',
    name: '',
    father_name: '',
    main_province: '',
    main_district: '',
    main_village: '',
    now_province: '',
    now_district: '',
    now_village: '',
    province: '',
    zone: '',
    district: '',
    village: '',
    specific_place: '',
    coordinates: '',
    mawad_sales_phone: '',
    Province_addr: '',
    history_form: '',
    supplier: '',
    note: '',
    resource_code: '',
    resource_phone: '',
    resource_id: null,
  });

  /* Rules */
  const rules = {
    required: (v) => !!String(v ?? '').trim() || t('این فیلد الزامی است'),
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
    form.value.resource_phone = found?.phone_number ?? '';
    form.value.resource_id = found?.id ?? null;
  }

  /* Helpers */
  function resetForm() {
    const keepId = isEdit.value ? form.value.id : null;
    form.value = {
      id: keepId,
      mawad_type: '',
      nickname: '',
      name: '',
      father_name: '',
      main_province: '',
      main_district: '',
      main_village: '',
      now_province: '',
      now_district: '',
      now_village: '',
      province: '',
      zone: '',
      district: '',
      village: '',
      specific_place: '',
      coordinates: '',
      mawad_sales_phone: '',
      Province_addr: '',
      history_form: '',
      supplier: '',
      note: '',
      resource_code: '',
      resource_phone: '',
      resource_id: null,
    };
    formRef.value?.resetValidation?.();
  }

  function fillFormFromRecord(x = {}) {
    form.value = {
      id: x.id ?? null,
      mawad_type: x.mawad_type ?? '',
      nickname: x.nickname ?? '',
      name: x.name ?? '',
      father_name: x.father_name ?? '',
      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',
      now_province: x.now_province ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',
      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',
      specific_place: x.specific_place ?? '',
      coordinates: x.coordinates ?? '',
      mawad_sales_phone: x.mawad_sales_phone ?? '',
      Province_addr: x.Province_addr ?? '',
      history_form: x.history_form ?? '',
      supplier: x.supplier ?? '',
      note: x.note ?? '',
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
        const updated = await repo.UpdateMawad?.(form.value.id, fd);
        if (updated?.id) {
          showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
          goBack();
          return;
        }
      } else {
        const created = await repo.CreateMawad?.(fd);
        if (created?.id) {
          showSuccessMsg(t('مواد جدید موفقانه ثبت شد'));
          goBack();
          return;
        }
      }

      showErrorMessage(t('خطا در ذخیره معلومات'));
    } catch (e) {
      console.error('Mawad save error', e);
      showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
    } finally {
      saving.value = false;
    }
  }

  /* Navigation */
  function goBack() {
    router.push('/app/mawads');
  }

  /* Load on edit */
  async function loadExisting() {
    const id = route.params.id;
    if (!id) return;
    try {
      await repo.FetchMawadData?.(id);
      const rec = repo.Mawad && Array.isArray(repo.Mawad) ? repo.Mawad[0] : repo.Mawad || {};
      fillFormFromRecord(rec);
    } catch (e) {
      console.error('loadExisting error', e);
      showErrorMessage(t('خطا در دریافت معلومات مواد'));
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
      [t('نوع مواد'), form.value.mawad_type],
      [t('نام مستعار'), form.value.nickname],
      [t('نام'), form.value.name],
      [t('نام پدر'), form.value.father_name],

      [t('ولایت اصلی'), form.value.main_province],
      [t('ولسوالی اصلی'), form.value.main_district],
      [t('قریه/ناحیه اصلی'), form.value.main_village],

      [t('ولایت فعلی'), form.value.now_province],
      [t('ولسوالی فعلی'), form.value.now_district],
      [t('قریه/ناحیه فعلی'), form.value.now_village],

      [t('ولایت'), form.value.province],
      [t('زون'), form.value.zone],
      [t('ولسوالی'), form.value.district],
      [t('قریه/ناحیه'), form.value.village],

      [t('موقعیت مشخص'), form.value.specific_place],
      [t('مختصات'), form.value.coordinates],
      [t('شماره فروش مواد'), form.value.mawad_sales_phone],

      [t('تاریخچه/فورم'), form.value.history_form],
      [t('تمویل‌کننده'), form.value.supplier],
      [t('یادداشت'), form.value.note],
      [t('ولایت مربوطه'), form.value.Province_addr],

      [t('کود منبع'), form.value.resource_code],
      [t('شماره منبع'), form.value.resource_phone],
    ];

    return `
<!doctype html>
<html dir="rtl" lang="fa">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${escapeHtml(t('فورم معلومات مواد'))}</title>
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
  <h2>${escapeHtml(t('فورم معلومات مواد'))}</h2>
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

    const namePart = form.value.id ? `mawad_${form.value.id}` : 'mawad_blank';
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
