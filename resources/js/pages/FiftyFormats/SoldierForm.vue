<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح سرباز') : t('اضافه کردن یک ریکارډ جدید سرباز')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ موجود سرباز') : t('ثبت یک ریکارډ جدید سرباز در سیستم')
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
          <!-- مشخصات اساسی -->
          <v-row>
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('مشخصات اساسی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <!-- ✅ Picture Picker (good design) -->
            <v-col cols="12" md="3">
              <div class="picture-wrap">
                <div class="picture-label">{{ t('عکس') }}</div>

                <div class="picture-box" @click="triggerPicturePick" role="button" tabindex="0">
                  <v-avatar size="92" class="picture-avatar">
                    <v-img
                      v-if="picturePreviewUrl || form.picture_url"
                      :src="picturePreviewUrl || form.picture_url"
                      cover
                    />
                    <v-icon v-else size="42">mdi-account</v-icon>
                  </v-avatar>

                  <div class="picture-actions">
                    <v-btn
                      variant="tonal"
                      color="primary"
                      class="text-none"
                      size="small"
                      prepend-icon="mdi-image-edit"
                      @click.stop="triggerPicturePick"
                    >
                      {{ t('انتخاب عکس') }}
                    </v-btn>

                    <v-btn
                      v-if="picturePreviewUrl || form.picture_url"
                      variant="text"
                      color="error"
                      class="text-none"
                      size="small"
                      prepend-icon="mdi-delete"
                      @click.stop="clearPicture"
                    >
                      {{ t('حذف') }}
                    </v-btn>
                  </div>

                  <div class="picture-hint">
                    {{ t('jpg / png / webp - حداکثر 5MB') }}
                  </div>

                  <input
                    ref="pictureInputRef"
                    type="file"
                    accept="image/*"
                    class="d-none"
                    @change="onPicturePicked"
                  />
                </div>
              </div>
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
              <!-- ⚠️ NOTE: your original code used now_province/now_district here.
                   I keep your logic unchanged as requested. -->
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
            <v-col cols="12" md="6">
              <FormatZoneAutocomplete
                v-model="form.now_zone"
                :label="t('زون فعلی')"
                :disabled="saving"
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

          <!-- منزل / موقعیت -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('منزل و موقعیت') }}
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

          <!-- وظایف و محافظ -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('وظایف و محافظ') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.old_job"
                :label="t('وظیفه قبلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_job"
                :label="t('وظیفه فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.guard_amount"
                type="number"
                :label="t('تعداد محافظ')"
                density="compact"
                variant="outlined"
                hide-details="auto"
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

          <!-- ملاحظات -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('ملاحظات') }}
              </h3>
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
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت سرباز جدید') }}
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
  import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';
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

  // ✅ picture picker refs + state
  const pictureInputRef = ref(null);
  const picturePreviewUrl = ref('');
  const pictureFile = ref(null);

  const form = ref({
    id: null,
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
    old_job: '',
    now_job: '',
    guard_amount: '',
    Province_addr: '',
    phone_number: '',
    coordinates: '',
    resource_code: '',
    resource_phone_number: '',
    resource_id: null,
    notes: '',

    // ✅ picture fields from API
    picture: null,
    picture_url: '',
  });

  /* Rules */
  const rules = {
    required: (v) => !!String(v ?? '').trim() || t('این فیلد الزامی است'),

    nonNegativeNumberOptional: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      const n = Number(s);
      if (!Number.isFinite(n)) return t('عدد درست نیست');
      if (n < 0) return t('عدد منفی قبول نیست');
      return true;
    },

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

  /* =======================
   ✅ Picture handlers
  ======================= */
  function triggerPicturePick() {
    pictureInputRef.value?.click?.();
  }

  function revokePreview() {
    if (picturePreviewUrl.value) {
      URL.revokeObjectURL(picturePreviewUrl.value);
      picturePreviewUrl.value = '';
    }
  }

  function onPicturePicked(e) {
    const file = e?.target?.files?.[0];
    if (!file) return;

    // basic size guard (5MB)
    if (file.size > 5 * 1024 * 1024) {
      showErrorMessage(t('حجم عکس زیاد است (حداکثر 5MB)'));
      e.target.value = '';
      return;
    }

    // ✅ keep file for submit
    pictureFile.value = file;

    // ✅ create preview
    revokePreview();
    picturePreviewUrl.value = URL.createObjectURL(file);
  }

  function clearPicture() {
    // keep API picture until save? -> user explicitly wants remove in UI now
    // We clear preview + file; also clear current url in UI.
    revokePreview();
    pictureFile.value = null;
    form.value.picture_url = '';
    form.value.picture = null;

    // also clear input element value
    if (pictureInputRef.value) pictureInputRef.value.value = '';
  }

  onBeforeUnmount(() => {
    revokePreview();
  });

  /* Helpers */
  function resetForm() {
    revokePreview();
    pictureFile.value = null;
    if (pictureInputRef.value) pictureInputRef.value.value = '';

    form.value = {
      id: isEdit.value ? form.value.id : null,
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
      old_job: '',
      now_job: '',
      guard_amount: '',
      Province_addr: '',
      phone_number: '',
      coordinates: '',
      resource_code: '',
      resource_phone_number: '',
      resource_id: null,
      notes: '',

      picture: null,
      picture_url: '',
    };
    formRef.value?.resetValidation?.();
  }

  function fillFormFromRecord(x = {}) {
    revokePreview();
    pictureFile.value = null;
    if (pictureInputRef.value) pictureInputRef.value.value = '';

    form.value = {
      id: x.id ?? null,
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
      old_job: x.old_job ?? '',
      now_job: x.now_job ?? '',
      guard_amount: x.guard_amount ?? '',
      Province_addr: x.Province_addr ?? '',
      phone_number: x.phone_number ?? '',
      coordinates: x.coordinates ?? '',
      resource_code: x.resource_code ?? '',
      resource_phone_number: x.resource_phone_number ?? '',
      resource_id: x.resource_id ?? null,
      notes: x.notes ?? '',

      picture: x.picture ?? null,
      picture_url: x.picture_url ?? '',
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
      // ✅ build payload without preview-only fields
      const payload = { ...form.value };

      // remove picture_url from request (server doesn't need it)
      delete payload.picture_url;

      // if user picked a new file, attach it
      if (pictureFile.value) {
        payload.picture = pictureFile.value;
      } else {
        // if pictureFile not selected, do not force picture field
        // (keep existing picture in DB)
        delete payload.picture;
      }

      const fd = toFormData(payload);

      if (isEdit.value && form.value.id) {
        const updated = await repo.UpdateSoldier?.(form.value.id, fd);
        if (updated?.id) {
          showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
          goBack();
          return;
        }
      } else {
        const created = await repo.CreateSoldier?.(fd);
        if (created?.id) {
          showSuccessMsg(t('سرباز جدید موفقانه ثبت شد'));
          goBack();
          return;
        }
      }

      showErrorMessage(t('خطا در ذخیره معلومات'));
    } catch (e) {
      console.error('Soldier save error', e);
      showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
    } finally {
      saving.value = false;
    }
  }

  /* Navigation */
  function goBack() {
    router.push('/app/soldiers');
  }

  /* Load on edit */
  async function loadExisting() {
    const id = route.params.id;
    if (!id) return;
    try {
      await repo.FetchSoldierData?.(id);
      const rec =
        repo.Soldier && Array.isArray(repo.Soldier) ? repo.Soldier[0] : repo.Soldier || {};
      fillFormFromRecord(rec);
    } catch (e) {
      console.error('loadExisting error', e);
      showErrorMessage(t('خطا در دریافت معلومات سرباز'));
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
      [t('ولایت مربوطه'), form.value.Province_addr],
      [t('شماره تماس'), form.value.phone_number],

      [t('ولایت اصلی'), form.value.main_province],
      [t('ولسوالی اصلی'), form.value.main_district],
      [t('قریه/ناحیه اصلی'), form.value.main_village],

      [t('ولایت فعلی'), form.value.now_province],
      [t('زون فعلی'), form.value.now_zone],
      [t('ولسوالی فعلی'), form.value.now_district],
      [t('قریه/ناحیه فعلی'), form.value.now_village],

      [t('موقعیت مشخص'), form.value.specific_place],
      [t('شماره سرک'), form.value.street_number],
      [t('شماره خانه'), form.value.home_number],

      [t('وظیفه قبلی'), form.value.old_job],
      [t('وظیفه فعلی'), form.value.now_job],
      [t('تعداد محافظ'), form.value.guard_amount],

      [t('مختصات'), form.value.coordinates],

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
  <title>${escapeHtml(t('فورم سرباز'))}</title>
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
  <h2>${escapeHtml(t('فورم سرباز'))}</h2>
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

    const namePart = form.value.id ? `soldier_${form.value.id}` : 'soldier_blank';
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

  /* ✅ Picture UI */
  .picture-wrap {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 12px;
    padding: 12px;
    height: 100%;
    background: rgba(0, 0, 0, 0.015);
  }
  .picture-label {
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #455a64;
  }
  .picture-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 10px 8px;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.08s ease, box-shadow 0.08s ease;
    outline: none;
  }
  .picture-box:hover {
    transform: translateY(-1px);
  }
  .picture-avatar {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
  }
  .picture-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: center;
  }
  .picture-hint {
    font-size: 11px;
    color: rgba(0, 0, 0, 0.55);
    text-align: center;
    line-height: 1.4;
  }
</style>
