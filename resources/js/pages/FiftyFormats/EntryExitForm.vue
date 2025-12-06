<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح ریکارډ ورود/خروج') : t('اضافه کردن یک ریکارډ جدید ورود/خروج')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ موجود ورود/خروج')
          : t('ثبت یک ریکارډ جدید ورود/خروج در سیستم')
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
            <v-col cols="12" md="4">
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

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.entry_way"
                :label="t('طریق ورود/خروج')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.way_type"
                :label="t('نوعه طریق')"
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
                {{ t('آدرس و موقعیت') }}
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

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- امنیتی -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('معلومات امنیتی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.amnayaty_poste"
                :label="t('پُسته امنیتی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.amnayaty_ishala"
                :label="t('اشاره امنیتی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- رفت و آمد -->
          <v-row class="mt-2">
            <v-col cols="12">
              <h3 class="section-title">
                {{ t('رفت و آمد') }}
              </h3>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.where_went"
                :label="t('به کجا رفت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.where_come"
                :label="t('از کجا آمد')"
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
                v-model="form.cordinate"
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
            <v-col cols="12" class="d-flex justify-end ga-2">
              <v-btn
                color="primary"
                variant="flat"
                class="text-none"
                :loading="saving"
                type="submit"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت ریکارډ جدید ورود/خروج') }}
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
  entry_way: '',
  way_type: '',
  province: '',
  zone: '',
  district: '',
  village: '',
  specific_place: '',
  Province_addr: '',
  amnayaty_poste: '',
  amnayaty_ishala: '',
  where_went: '',
  where_come: '',
  cordinate: '',
  resource_code: '',
  resource_phone_number: '',
  resource_id: null,
  notes: '',
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
    entry_way: '',
    way_type: '',
    province: '',
    zone: '',
    district: '',
    village: '',
    specific_place: '',
    Province_addr: '',
    amnayaty_poste: '',
    amnayaty_ishala: '',
    where_went: '',
    where_come: '',
    cordinate: '',
    resource_code: '',
    resource_phone_number: '',
    resource_id: null,
    notes: '',
  };
  formRef.value?.resetValidation?.();
}

function fillFormFromRecord(x = {}) {
  form.value = {
    id: x.id ?? null,
    name: x.name ?? '',
    entry_way: x.entry_way ?? '',
    way_type: x.way_type ?? '',
    province: x.province ?? '',
    zone: x.zone ?? '',
    district: x.district ?? '',
    village: x.village ?? '',
    specific_place: x.specific_place ?? '',
    Province_addr: x.Province_addr ?? '',
    amnayaty_poste: x.amnayaty_poste ?? '',
    amnayaty_ishala: x.amnayaty_ishala ?? '',
    where_went: x.where_went ?? '',
    where_come: x.where_come ?? '',
    cordinate: x.cordinate ?? '',
    resource_code: x.resource_code ?? '',
    resource_phone_number: x.resource_phone_number ?? '',
    resource_id: x.resource_id ?? null,
    notes: x.notes ?? '',
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
      const updated = await repo.UpdateEntryExit?.(form.value.id, fd);
      if (updated && updated.id) {
        showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
        goBack();
        return;
      }
    } else {
      const created = await repo.CreateEntryExit?.(fd);
      if (created && created.id) {
        showSuccessMsg(t('ریکارډ جدید موفقانه ثبت شد'));
        goBack();
        return;
      }
    }

    showErrorMessage(t('خطا در ذخیره معلومات'));
  } catch (e) {
    console.error('EntryExit save error', e);
    showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
  } finally {
    saving.value = false;
  }
}

/* Navigation */
function goBack() {
  router.push('/app/entry-exits');
}

/* Load on edit */
async function loadExisting() {
  const id = route.params.id;
  if (!id) return;
  try {
    await repo.FetchEntryExitData?.(id);
    const rec =
      repo.EntryExit && Array.isArray(repo.EntryExit)
        ? repo.EntryExit[0]
        : repo.EntryExit || {};
    fillFormFromRecord(rec);
  } catch (e) {
    console.error('loadExisting error', e);
    showErrorMessage(t('خطا در دریافت معلومات ورود/خروج'));
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
