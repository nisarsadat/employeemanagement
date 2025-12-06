<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح فساد') : t('اضافه کردن یک ریکارډ جدید فساد')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ موجود فساد')
          : t('ثبت یک ریکارډ جدید فساد در سیستم')
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
          <!-- هدر بخش‌ها -->
          <v-row>
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('مشخصات اساسی') }}
              </h3>
            </v-col>
          </v-row>

          <!-- Identity -->
          <v-row>
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
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('موقعیت و خانه') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
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
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('فعالیت، شبکه و تماس') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
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

          <v-row class="mt-1">
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.friends_number"
                :label="t('تعداد دوستان')"
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
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('سوابق بند / ارتباط') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
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
                :label="t('coordinates')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                hint="مثال: 34.1234, 69.1234"
                persistent-hint
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
            <v-col cols="12" class="d-flex justify-end ga-2">
              <v-btn
                color="primary"
                variant="flat"
                class="text-none"
                :loading="saving"
                type="submit"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت فساد جدید') }}
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

/* Build FormData like your editor */
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
    const rec = repo.Fasad && Array.isArray(repo.Fasad)
      ? repo.Fasad[0]
      : repo.Fasad || {};
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
.section-title {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 4px;
  color: #37474f;
}
</style>
