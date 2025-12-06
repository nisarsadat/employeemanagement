<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح مهاجرت') : t('اضافه کردن یک ریکارډ جدید مهاجرت')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ موجود مهاجرت')
          : t('ثبت یک ریکارډ جدید مهاجرت در سیستم')
      "
    >
      <template #actions>
        <v-btn
          variant="text"
          prepend-icon="mdi-arrow-left"
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
                v-model="form.scientific_level"
                :label="t('سطح علمی')"
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
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('آدرس اصلی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
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
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('آدرس فعلی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
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
              <!-- backend key: now_vinage -->
              <v-text-field
                v-model="form.now_vinage"
                :label="t('قریه/ناحیه فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- موقعیت و مختصات -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('موقعیت و خانه/مختصات') }}
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
                :label="t('سرک نمبر')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.home_number"
                :label="t('نمبر خانه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.cordinates"
                :label="t('مختصات')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                hint="مثال: 34.1234, 69.1234"
                persistent-hint
              />
            </v-col>
          </v-row>

          <!-- جزئیات مهاجرت / تعداد / تماس -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('مقصد مهاجرت، تعداد و تماس') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.country"
                :label="t('کشور مقصد')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.male_amount"
                type="number"
                :label="t('تعداد مرد')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.female_amount"
                type="number"
                :label="t('تعداد زن')"
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
                v-model="form.note"
                :label="t('ملاحظات')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ACTIONS -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2">
              <v-btn
                color="primary"
                variant="flat"
                class="text-none"
                :loading="saving"
                type="submit"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت مهاجرت جدید') }}
              </v-btn>
              <v-btn
                color="secondary"
                variant="text"
                class="text-none"
                @click="resetForm"
              >
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
  name: '',
  father_name: '',
  scientific_level: '',
  main_province: '',
  main_district: '',
  main_village: '',
  now_province: '',
  now_zone: '',
  now_district: '',
  now_vinage: '',
  specific_place: '',
  street_number: '',
  home_number: '',
  cordinates: '',
  country: '',
  male_amount: '',
  female_amount: '',
  phone_number: '',
  Province_addr: '',
  resource_code: '',
  resource_phone_number: '',
  resource_id: null,
  note: '',
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
  form.value.resource_phone_number = found?.phone_number ?? '';
  form.value.resource_id = found?.id ?? null;
}

/* Helpers */
function resetForm() {
  form.value = {
    id: isEdit.value ? form.value.id : null,
    name: '',
    father_name: '',
    scientific_level: '',
    main_province: '',
    main_district: '',
    main_village: '',
    now_province: '',
    now_zone: '',
    now_district: '',
    now_vinage: '',
    specific_place: '',
    street_number: '',
    home_number: '',
    cordinates: '',
    country: '',
    male_amount: '',
    female_amount: '',
    phone_number: '',
    Province_addr: '',
    resource_code: '',
    resource_phone_number: '',
    resource_id: null,
    note: '',
  };
  formRef.value?.resetValidation?.();
}

function fillFormFromRecord(x = {}) {
  form.value = {
    id: x.id ?? null,
    name: x.name ?? '',
    father_name: x.father_name ?? '',
    scientific_level: x.scientific_level ?? '',
    main_province: x.main_province ?? '',
    main_district: x.main_district ?? '',
    main_village: x.main_village ?? '',
    now_province: x.now_province ?? '',
    now_zone: x.now_zone ?? '',
    now_district: x.now_district ?? '',
    now_vinage: x.now_vinage ?? '',
    specific_place: x.specific_place ?? '',
    street_number: x.street_number ?? '',
    home_number: x.home_number ?? '',
    cordinates: x.cordinates ?? '',
    country: x.country ?? '',
    male_amount: x.male_amount ?? '',
    female_amount: x.female_amount ?? '',
    phone_number: x.phone_number ?? '',
    Province_addr: x.Province_addr ?? '',
    resource_code: x.resource_code ?? '',
    resource_phone_number: x.resource_phone_number ?? '',
    resource_id: x.resource_id ?? null,
    note: x.note ?? '',
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
    const payload = { ...form.value };
    const fd = toFormData(payload);

    if (isEdit.value && form.value.id) {
      const updated = await repo.UpdateMahajerat?.(form.value.id, fd);
      if (updated && updated.id) {
        showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
        goBack();
        return;
      }
    } else {
      const created = await repo.CreateMahajerat?.(fd);
      if (created && created.id) {
        showSuccessMsg(t('ریکارډ مهاجرت موفقانه ثبت شد'));
        goBack();
        return;
      }
    }

    showErrorMessage(t('خطا در ذخیره معلومات'));
  } catch (e) {
    console.error('Mahajerat save error', e);
    showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
  } finally {
    saving.value = false;
  }
}

/* Navigation */
function goBack() {
  router.push('/app/mahajerats');
}

/* Load on edit */
async function loadExisting() {
  const id = route.params.id;
  if (!id) return;
  try {
    await repo.FetchMahajeratData?.(id);
    const rec =
      repo.Mahajerat && Array.isArray(repo.Mahajerat)
        ? repo.Mahajerat[0]
        : repo.Mahajerat || {};
    fillFormFromRecord(rec);
  } catch (e) {
    console.error('loadExisting error', e);
    showErrorMessage(t('خطا در دریافت معلومات مهاجرت'));
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
.section-title {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 4px;
  color: #37474f;
}
</style>
