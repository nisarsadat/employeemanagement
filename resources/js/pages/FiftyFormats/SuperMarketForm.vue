<!-- src/views/market/SuperMarketForm.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش سوپرمارکیت') : t('ایجاد سوپرمارکیت جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ سوپرمارکیت موجود')
          : t('ثبت یک ریکارډ سوپرمارکیت جدید در سیستم')
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
          <!-- ✅ PICTURE (ONLY ADDITION) -->
          <v-row>
            <v-col cols="12" md="3">
              <PicturePicker
                v-model:file="pictureFile"
                v-model:url="form.previewUrl"
                :label="t('تصویر')"
                :alt="t('عکس سوپرمارکیت')"
                :change-text="t('تغییر عکس')"
                :view-text="t('نمایش')"
                :close-text="t('بستن')"
                :preview-title="t('پیش نمایش')"
                @change="({ changed }) => (pictureChanged = !!changed)"
              />
            </v-col>
          </v-row>

          <v-row>
            <!-- Identity + address -->
            <v-col cols="12" md="12">
              <!-- Identity -->

              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.supermarket_name"
                    :label="t('نام سوپرمارکیت *')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.required]"
                    :error="hasError('supermarket_name')"
                    :error-messages="getErrorMessages('supermarket_name')"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.market_activity_type"
                    :label="t('کدام فعالیت')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('market_activity_type')"
                    :error-messages="getErrorMessages('market_activity_type')"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.shops_quantity"
                    type="number"
                    :label="t('تعداد دکان‌ها')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('shops_quantity')"
                    :error-messages="getErrorMessages('shops_quantity')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.daily_clientele"
                    type="number"
                    :label="t('مراجعین روزانه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('daily_clientele')"
                    :error-messages="getErrorMessages('daily_clientele')"
                  />
                </v-col>
              </v-row>

              <!-- Responsible -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('مسؤول سوپرمارکیت') }}</span>
              </div>

              <v-row>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.responsible_name"
                    :label="t('مسؤول')"
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
                    :label="t(' موقف مسؤول')"
                    variant="outlined"
                    density="compact"
                    inputmode="numeric"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.responsible_phone"
                    :label="t('شماره تماس مسؤول')"
                    variant="outlined"
                    density="compact"
                    inputmode="numeric"
                    :rules="[rules.phone10]"
                    :error="hasError('responsible_phone')"
                    :error-messages="getErrorMessages('responsible_phone')"
                  />
                </v-col>
              </v-row>

              <!-- Address -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس سوپرمارکیت') }}</span>
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
                  <FormatZoneAutocomplete
                    v-model="form.zone"
                    :label="t('آمریت')"
                    :disabled="saving"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.village"
                    :label="t('قریه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('village')"
                    :error-messages="getErrorMessages('village')"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.specefic_place"
                    :label="t('موقعیت مشخص')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('specefic_place')"
                    :error-messages="getErrorMessages('specefic_place')"
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
              </v-row>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.employee_quantity"
                type="number"
                :label="t('تعداد کارمندان')"
                variant="outlined"
                density="compact"
                :error="hasError('employee_quantity')"
                :error-messages="getErrorMessages('employee_quantity')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.guard_quantity"
                type="number"
                :label="t('تعداد محافظ')"
                variant="outlined"
                density="compact"
                :error="hasError('guard_quantity')"
                :error-messages="getErrorMessages('guard_quantity')"
              />
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Extra fields -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('معلومات منبع') }}</span>
          </div>

          <v-row>
            <!-- Resource mapping -->
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

          <!-- ✅ EXPORT BUTTONS (BOTTOM) -->
          <v-row class="mt-2">
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
                color="primary"
                variant="tonal"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-printer"
                @click="printForm"
              >
                {{ t('چاپ') }}
              </v-btn>

              <v-btn
                color="secondary"
                variant="tonal"
                class="text-none rounded-pill soft-btn"
                prepend-icon="mdi-file-pdf-box"
                @click="downloadPdf"
              >
                {{ t('PDF ساده') }}
              </v-btn>

              <v-btn
                color="secondary"
                variant="tonal"
                class="text-none rounded-pill soft-btn"
                prepend-icon="mdi-file-word-box"
                @click="downloadWord"
              >
                {{ t('Word ساده') }}
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
            </v-col>
          </v-row>
        </v-card>
      </v-form>

      <!-- IMAGE PREVIEW DIALOG -->
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
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

  // ✅ PICTURE (ONLY ADDITION)
  import PicturePicker from '@/components/form/PicturePicker.vue';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';
  import { printBlankForm } from '@/utils/printBlankForm';

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

    // media
    picture: null,
    previewUrl: null,

    // identity
    supermarket_name: '',
    market_activity_type: '',
    daily_clientele: '',
    shops_quantity: '',
    employee_quantity: '',
    guard_quantity: '',

    // responsible
    responsible_name: '',
    responsible_f_name: '',
    responsible_phone: '',
    responsible_job: '',

    // address
    province: '',
    zone: '',
    district: '',
    village: '',
    specefic_place: '',
    Province_addr: '',

    // extra
    coordinates: '',

    // resource
    resource_code: '',
    resource_phone: '',
    resource_id: null,

    // note
    note: '',
  });

  const form = ref(blankForm());

  /* RULES (✅ phone must be min/max 10 digits) */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    phone10: (v) => {
      if (v === null || v === undefined || String(v).trim() === '') return true;
      const digits = String(v).replace(/\D/g, '');
      return digits.length === 10 || t('شماره تماس باید دقیقاً ۱۰ رقم باشد');
    },
  };

  /* SIMPLE EXPORT (NO IMAGE / NO APP HEADERS) */
  function esc(v) {
    return String(v ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function buildSimpleHtml() {
    const rows = [
      [t('نام سوپرمارکیت'), form.value.supermarket_name],
      [t('نوع فعالیت'), form.value.market_activity_type],
      [t('مراجعین روزانه'), form.value.daily_clientele],
      [t('تعداد دکان‌ها'), form.value.shops_quantity],
      [t('تعداد کارمندان'), form.value.employee_quantity],
      [t('تعداد محافظ'), form.value.guard_quantity],

      [t('مسؤول'), form.value.responsible_name],
      [t('نام پدر مسؤول'), form.value.responsible_f_name],
      [t('شماره تماس مسؤول'), form.value.responsible_phone],
      [t(' موقف مسؤول'), form.value.responsible_job],

      [t('ولایت'), form.value.province],
      [t('آمریت'), form.value.zone],
      [t('ولسوالی'), form.value.district],
      [t('قریه'), form.value.village],
      [t('موقعیت مشخص'), form.value.specefic_place],

      [t('ولایت مربوطه'), form.value.Province_addr],
      [t('مختصات'), form.value.coordinates],

      [t('کود منبع'), form.value.resource_code],
      [t('شماره منبع'), form.value.resource_phone],

      [t('یادداشت'), form.value.note],
    ];

    const tableRows = rows
      .map(
        ([k, v]) => `
      <tr>
        <td class="k">${esc(k)}</td>
        <td class="v">${esc(v || '')}</td>
      </tr>`
      )
      .join('');

    return `
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>${esc(t('سوپرمارکیت'))}</title>
  <style>
    body{font-family: Arial, sans-serif; direction: rtl; padding: 18px; color:#111;}
    h2{margin:0 0 10px 0; font-size: 16px;}
    .meta{margin-bottom: 12px; font-size: 12px; color:#444;}
    table{width:100%; border-collapse:collapse; margin-top:10px;}
    td{border:1px solid #ddd; padding:8px; vertical-align:top; font-size: 12px;}
    td.k{width: 32%; background:#f7f7f9; font-weight:700;}
    .sig{margin-top:18px; display:flex; gap:24px; justify-content:space-between;}
    .sig .line{flex:1; border-top:1px solid #444; padding-top:6px; font-size:12px; text-align:center;}
    @media print{ body{padding:0} }
  </style>
</head>
<body>
  <h2>${esc(t('فورم معلومات سوپرمارکیت'))}</h2>
  <div class="meta">${esc(t('این سند به صورت ساده (بدون عکس و بدون هدر اپ) تولید شده است.'))}</div>
  <table>
    <tbody>
      ${tableRows}
    </tbody>
  </table>

  <div class="sig">
    <div class="line">${esc(t('امضا مسئول'))}</div>
    <div class="line">${esc(t('امضا ثبت کننده'))}</div>
    <div class="line">${esc(t('تاریخ'))}</div>
  </div>
</body>
</html>`;
  }

  /* PRINT BLANK FORM (empty fields, same design, one A4 page) */
  function printForm(e) {
    printBlankForm(e, { onError: showErrorMessage });
  }

  function downloadPdf() {
    const html = buildSimpleHtml();
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.open();
    w.document.write(html);
    w.document.close();
    w.focus();
    setTimeout(() => w.print(), 250);
  }

  function downloadWord() {
    const html = buildSimpleHtml();
    const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `supermarket_simple_${form.value?.id ?? 'new'}.doc`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  /* ✅ PICTURE STATE (kept as-is, now used by PicturePicker) */
  const pictureInput = ref(null);
  const pictureFile = ref(null);
  const pictureChanged = ref(false);

  const previewOpen = ref(false);
  const previewSrc = ref(null);

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
    const base = src || form.value.previewUrl || form.value.picture;
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

    const fullUrl = x.picture ?? null;
    b.picture = fullUrl;
    b.previewUrl = fullUrl;

    b.supermarket_name = x.supermarket_name ?? '';
    b.market_activity_type = x.market_activity_type ?? '';
    b.daily_clientele = x.daily_clientele ?? '';
    b.shops_quantity = x.shops_quantity ?? '';
    b.employee_quantity = x.employee_quantity ?? '';
    b.guard_quantity = x.guard_quantity ?? '';

    b.responsible_name = x.responsible_name ?? '';
    b.responsible_f_name = x.responsible_f_name ?? '';
    b.responsible_phone = x.responsible_phone ?? '';
    b.responsible_job = x.responsible_job ?? '';

    b.province = x.province ?? '';
    b.zone = x.zone ?? '';
    b.district = x.district ?? '';
    b.village = x.village ?? '';
    b.specefic_place = x.specefic_place ?? '';
    b.Province_addr = x.Province_addr ?? '';

    b.coordinates = x.coordinates ?? '';

    b.resource_code = x.resource_code ?? x.resource?.code_number ?? '';
    b.resource_phone = x.resource_phone ?? x.resource?.phone_number ?? '';
    b.resource_id = x.resource_id ?? x.resource?.id ?? null;

    b.note = x.note ?? '';
    return b;
  }

  async function loadRecord() {
    const id = route.params.id;
    if (!id) {
      form.value = blankForm();
      pictureFile.value = null;
      pictureChanged.value = false;
      return;
    }

    try {
      await repo.FetchSuperMarketData?.(id);
      const single = Array.isArray(repo.SuperMarket) ? repo.SuperMarket[0] : repo.SuperMarket || {};
      const mapped = mapFromApi(single);
      form.value = { ...mapped };
      pictureFile.value = null;
      pictureChanged.value = false;

      if (form.value.resource_code) onResourceCodeChange(form.value.resource_code);
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات سوپرمارکیت'));
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
    delete payload.previewUrl;

    const fd = toFormData(payload);
    if (pictureFile.value) fd.append('picture', pictureFile.value);
    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    delete payload.previewUrl;
    delete payload.picture;

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

    if (!form.value.supermarket_name?.trim()) {
      showErrorMessage(t('نام سوپرمارکیت الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateSuperMarket?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/supermarkets').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateSuperMarket?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/supermarkets').catch(() => {});
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
    if (form.value.previewUrl && form.value.previewUrl.startsWith('blob:')) {
      URL.revokeObjectURL(form.value.previewUrl);
    }

    if (isEdit.value) {
      loadRecord();
    } else {
      form.value = blankForm();
      pictureFile.value = null;
      pictureChanged.value = false;
    }

    clearBackendErrors();
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/supermarkets');
  }

  /* INIT */
  onMounted(async () => {
    if (isEdit.value) await loadRecord();
  });
</script>

<style scoped>
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }

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
  .soft-btn {
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
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
</style>
