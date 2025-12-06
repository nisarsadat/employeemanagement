<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح کلب') : t('اضافه کردن یک ریکارډ جدید کلب')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ موجود کلب')
          : t('ثبت یک ریکارډ جدید کلب در سیستم')
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
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
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
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.am_students"
                :label="t('تعداد شاگردان صبح')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                type="number"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.pm_students"
                :label="t('تعداد شاگردان عصر')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                type="number"
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
            <v-col cols="12" class="d-flex justify-end ga-2">
              <v-btn
                color="primary"
                variant="flat"
                class="text-none"
                :loading="saving"
                type="submit"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت کلب جدید') }}
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
    const payload = { ...form.value };
    const fd = toFormData(payload);

    if (isEdit.value && form.value.id) {
      const updated = await repo.UpdateClub?.(form.value.id, fd);
      if (updated && updated.id) {
        showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
        goBack();
        return;
      }
    } else {
      const created = await repo.CreateClub?.(fd);
      if (created && created.id) {
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
