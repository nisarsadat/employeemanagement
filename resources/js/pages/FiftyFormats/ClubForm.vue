<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح کلب') : t('اضافه کردن یک ریکارډ جدید کلب')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ موجود کلب') : t('ثبت یک ریکارډ جدید کلب در سیستم')
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
          <!-- هدر بخش‌ها -->
          <v-row>
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('مشخصات اساسی کلب') }}
              </h3>
            </v-col>
          </v-row>

          <!-- Identity / basic -->
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.name"
                :label="t('نام مسئول/شخص')"
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
                v-model="form.club_name"
                :label="t('نام کلب')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.required]"
                required
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.club_type"
                :label="t('نوع کلب')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.position"
                :label="t('وظیفه/موقعیت مسئول')"
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
              />
            </v-col>
            <!-- <v-col cols="12" md="3">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col> -->
          </v-row>

          <!-- آدرس / موقعیت -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('آدرس و موقعیت کلب') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="12">
              <ProvinceDistrictNameSelect
                v-model:province="form.province"
                v-model:district="form.district"
                :province-label="t('ولایت اصلی')"
                :district-label="t('ولسوالی اصلی')"
                :mdProvince="6"
                :mdDistrict="6"
              />
            </v-col>
            <v-col cols="12" md="6">
              <FormatZoneAutocomplete
                    v-model="form.zone"
                    :label="t('زون ')"
                    :disabled="saving"
                  />
            </v-col>

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

          <!-- شاگردان -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('معلومات شاگردان کلب') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.am_students"
                :label="t('تعداد شاگردان صبح')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                type="number"
                :rules="[rules.nonNegativeNumberOptional]"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.pm_students"
                :label="t('تعداد شاگردان عصر')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                type="number"
                :rules="[rules.nonNegativeNumberOptional]"
              />
            </v-col>
          </v-row>

          <!-- مختصات -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('مختصات و نقشه') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.cordinate"
                :label="t('مختصات (cordinate)')"
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
              <!-- ✅ Export buttons beside submit (works even blank) -->
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت کلب جدید') }}
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
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

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
    father_name: '',
    club_name: '',
    club_type: '',
    position: '',
    phone_number: '',
    Province_addr: '',

    province: '',
    zone: '',
    district: '',
    specific_place: '',

    am_students: '',
    pm_students: '',

    cordinate: '',

    resource_code: '',
    resource_phone_number: '',
    resource_id: null,
  });

  /* Rules */
  const rules = {
    required: (v) => !!String(v ?? '').trim() || t('این فیلد الزامی است'),

    // empty OK, otherwise must be number >= 0
    nonNegativeNumberOptional: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      const n = Number(s);
      if (!Number.isFinite(n)) return t('عدد درست نیست');
      if (n < 0) return t('عدد منفی قبول نیست');
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
    form.value = {
      id: isEdit.value ? form.value.id : null,
      name: '',
      father_name: '',
      club_name: '',
      club_type: '',
      position: '',
      phone_number: '',
      Province_addr: '',

      province: '',
      zone: '',
      district: '',
      specific_place: '',

      am_students: '',
      pm_students: '',

      cordinate: '',

      resource_code: '',
      resource_phone_number: '',
      resource_id: null,
    };
    formRef.value?.resetValidation?.();
  }

  function fillFormFromRecord(x = {}) {
    form.value = {
      id: x.id ?? null,
      name: x.name ?? '',
      father_name: x.father_name ?? '',
      club_name: x.club_name ?? '',
      club_type: x.club_type ?? '',
      position: x.position ?? '',
      phone_number: x.phone_number ?? '',
      Province_addr: x.Province_addr ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      specific_place: x.specific_place ?? '',

      am_students: x.am_students ?? '',
      pm_students: x.pm_students ?? '',

      cordinate: x.cordinate ?? '',

      resource_code: x.resource_code ?? '',
      resource_phone_number: x.resource_phone_number ?? '',
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
        const updated = await repo.UpdateClub?.(form.value.id, fd);
        if (updated?.id) {
          showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
          goBack();
          return;
        }
      } else {
        const created = await repo.CreateClub?.(fd);
        if (created?.id) {
          showSuccessMsg(t('کلب جدید موفقانه ثبت شد'));
          goBack();
          return;
        }
      }

      showErrorMessage(t('خطا در ذخیره معلومات'));
    } catch (e) {
      console.error('Club save error', e);
      showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
    } finally {
      saving.value = false;
    }
  }

  /* Navigation */
  function goBack() {
    router.push('/app/clubs');
  }

  /* Load on edit */
  async function loadExisting() {
    const id = route.params.id;
    if (!id) return;
    try {
      await repo.FetchClubData?.(id);
      const rec = repo.Club && Array.isArray(repo.Club) ? repo.Club[0] : repo.Club || {};
      fillFormFromRecord(rec);
    } catch (e) {
      console.error('loadExisting error', e);
      showErrorMessage(t('خطا در دریافت معلومات کلب'));
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
      [t('نام مسئول/شخص'), form.value.name],
      [t('نام پدر'), form.value.father_name],
      [t('نام کلب'), form.value.club_name],
      [t('نوع کلب'), form.value.club_type],

      [t('وظیفه/موقعیت مسئول'), form.value.position],
      [t('شماره تماس'), form.value.phone_number],
      [t('ولایت مربوطه'), form.value.Province_addr],

      [t('ولایت'), form.value.province],
      [t('زون'), form.value.zone],
      [t('ولسوالی'), form.value.district],
      [t('موقعیت مشخص'), form.value.specific_place],

      [t('تعداد شاگردان صبح'), form.value.am_students],
      [t('تعداد شاگردان عصر'), form.value.pm_students],

      [t('مختصات'), form.value.cordinate],

      [t('کود منبع'), form.value.resource_code],
      [t('شماره منبع'), form.value.resource_phone_number],
    ];

    return `
<!doctype html>
<html dir="rtl" lang="fa">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${escapeHtml(t('فورم کلب'))}</title>
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
  <h2>${escapeHtml(t('فورم کلب'))}</h2>
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

    const namePart = form.value.id ? `club_${form.value.id}` : 'club_blank';
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
