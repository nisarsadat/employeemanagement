<template>
  <v-container fluid class="page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح اطلاع') : t('اضافه کردن اطلاع')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک اطلاع موجود')
          : t('ثبت یک اطلاع جدید در سیستم')
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

    <!-- STEPPER (DYNAMIC) -->
    <section class="stepper-shell mx-auto" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card class="stepper-card py-4 pb-12" elevation="0">
        <div class="d-flex justify-space-between align-center mb-1">
          <span class="text-caption text-medium-emphasis stepper-caption">
            {{ t('مراحل خانه پری فرم') }}
          </span>
          <span class="text-caption text-primary font-weight-medium stepper-caption">
            {{ t('۴ بخش اصلی') }}
          </span>
        </div>

        <div class="stepper-track-wrapper">
          <v-progress-linear
            class="stepper-track"
            :model-value="progressValue"
            height="3"
            rounded
            color="primary"
          />
          <div class="stepper-dots">
            <!-- 1: هویت -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step1Complete,
                  'is-active': currentStep === 1 && !step1Complete,
                }"
              >
                <v-icon size="16">
                  {{ step1Complete ? 'mdi-check' : 'mdi-account-badge' }}
                </v-icon>
              </div>
              <span class="stepper-label text-caption" :class="{ 'is-complete-text': step1Complete }">
                {{ t('هویت') }}
              </span>
            </div>

            <!-- 2: آدرس -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step2Complete,
                  'is-active': currentStep === 2 && !step2Complete,
                }"
              >
                <v-icon size="16">
                  {{ step2Complete ? 'mdi-check' : 'mdi-home-city' }}
                </v-icon>
              </div>
              <span class="stepper-label text-caption" :class="{ 'is-complete-text': step2Complete }">
                {{ t('آدرس‌ها') }}
              </span>
            </div>

            <!-- 3: نوع اطلاع -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step3Complete,
                  'is-active': currentStep === 3 && !step3Complete,
                }"
              >
                <v-icon size="16">
                  {{ step3Complete ? 'mdi-check' : 'mdi-timer-sand' }}
                </v-icon>
              </div>
              <span class="stepper-label text-caption" :class="{ 'is-complete-text': step3Complete }">
                {{ t('نوع اطلاع') }}
              </span>
            </div>

            <!-- 4: اسناد -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step4Complete,
                  'is-active': currentStep === 4 && !step4Complete,
                }"
              >
                <v-icon size="16">
                  {{ step4Complete ? 'mdi-check' : 'mdi-paperclip' }}
                </v-icon>
              </div>
              <span class="stepper-label text-caption" :class="{ 'is-complete-text': step4Complete }">
                {{ t('اسناد') }}
              </span>
            </div>
          </div>
        </div>
      </v-card>
    </section>

    <!-- FORM WRAPPER -->
    <v-form
      ref="formRef"
      v-model="formValid"
      lazy-validation
      @submit.prevent="onSubmit"
      :dir="isRtl ? 'rtl' : 'ltr'"
    >
      <div class="form-shell mx-auto">
        <v-card class="pa-4 pa-md-6 form-card" elevation="4">
          <!-- HEADER BADGE -->
          <div class="d-flex justify-space-between align-center mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-avatar size="40" class="mr-2 header-avatar" color="primary">
                <v-icon size="22" color="white">mdi-file-document-edit</v-icon>
              </v-avatar>
              <div>
                <div class="text-subtitle-1 font-weight-medium header-title">
                  {{ isEdit ? t('فرم اصلاح اطلاع') : t('فرم ثبت اطلاع') }}
                </div>
                <div class="text-caption text-medium-emphasis header-subtitle">
                  {{
                    t(
                      'لطفاً تمام خانه‌های ضروری را با دقت خانه پری کنید. خانه‌های ستاره‌دار اجباری است.'
                    )
                  }}
                </div>
              </div>
            </div>
          </div>

          <!-- CODES SECTION -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-barcode-scan</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('کودها') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('انتخاب کود منبع و کارمند اداری در صورت موجود بودن') }}
              </span>
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-autocomplete
                  class="primary-select"
                  v-model="form.resource_id"
                  :items="resourceItems"
                  :item-title="formatResource"
                  item-value="id"
                  :label="t('کود منبع *')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :rules="[rules.selectRequired(t('کود منبع'))]"
                  :error="hasError('resource_id')"
                  :error-messages="getErrorMessages('resource_id')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-autocomplete
                  class="primary-select"
                  v-model="form.office_employee_id"
                  :items="officeEmployeesItems"
                  item-title="code_number"
                  item-value="id"
                  :label="t('کود کارمند اداری')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :error="hasError('office_employee_id')"
                  :error-messages="getErrorMessages('office_employee_id')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- IDENTITY SECTION -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-account-badge</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('معرفت (هویت شخص)') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('نام، نام پدر و سایر مشخصات هویتی را وارد کنید.') }}
              </span>
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.name"
                  :label="t('نام *')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.required]"
                  :error="hasError('name')"
                  :error-messages="getErrorMessages('name')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.nick_name"
                  :label="t('نام مستعار')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('nick_name')"
                  :error-messages="getErrorMessages('nick_name')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.father_name"
                  :label="t('نام پدر')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('father_name')"
                  :error-messages="getErrorMessages('father_name')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.grand_father_name"
                  :label="t('نام پدرکلان')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('grand_father_name')"
                  :error-messages="getErrorMessages('grand_father_name')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- MAIN ADDRESS SECTION -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-home-city</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('آدرس اصلی') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('آدرس اصلی شخص (زادگاه / محل اصلی)') }}
              </span>
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.main_province"
                  :label="t('ولایت اصلی *')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.required]"
                  :error="hasError('main_province')"
                  :error-messages="getErrorMessages('main_province')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.main_districts"
                  :label="t('ولسوالی اصلی *')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.required]"
                  :error="hasError('main_districts')"
                  :error-messages="getErrorMessages('main_districts')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.main_village"
                  :label="t('قریه/گذر اصلی *')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.required]"
                  :error="hasError('main_village')"
                  :error-messages="getErrorMessages('main_village')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- CURRENT ADDRESS SECTION -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-map-marker-radius</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('آدرس فعلی') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('محل سکونت فعلی شخص در حال حاضر') }}
              </span>
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.now_province"
                  :label="t('ولایت فعلی')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('now_province')"
                  :error-messages="getErrorMessages('now_province')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.now_district"
                  :label="t('ولسوالی فعلی')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('now_district')"
                  :error-messages="getErrorMessages('now_district')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.now_village"
                  :label="t('قریه/گذر فعلی')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('now_village')"
                  :error-messages="getErrorMessages('now_village')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- RELATED PROVINCE -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-map-legend</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('ولایت مربوطه') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('در صورتیکه اطلاع مربوط ولایت مشخصی باشد، آن را درج کنید.') }}
              </span>
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.Province_addr"
                  :label="t('ولایت مربوطه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('Province_addr')"
                  :error-messages="getErrorMessages('Province_addr')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- TYPES & TIME SECTION -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-timer-sand</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('نوع و زمان اطلاع') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('نوع اطلاع، سیاسی/جنایی بودن و زمان ثبت را مشخص کنید.') }}
              </span>
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-autocomplete
                  class="primary-select"
                  v-model="form.syasi_info_type"
                  :items="seyasiItems"
                  item-title="name"
                  item-value="id"
                  :label="t('سیاسی - نوع')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :error="hasError('syasi_info_type')"
                  :error-messages="getErrorMessages('syasi_info_type')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-autocomplete
                  class="primary-select"
                  v-model="form.jenayee_info_type"
                  :items="jenayeeItems"
                  item-title="name"
                  item-value="id"
                  :label="t('جنایی - هو/نه')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :error="hasError('jenayee_info_type')"
                  :error-messages="getErrorMessages('jenayee_info_type')"
                />
              </v-col>
              <v-col cols="12" md="4">
                <DateInput
                  v-model="form.information_saved_date"
                  :label="t('تاریخ اطلاع *')"
                  :rules="[rules.required]"
                />
              </v-col>
            </v-row>

            <v-row class="pa-3 pt-0">
              

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.information_saved_hour"
                  type="time"
                  step="60"
                  :label="t('ساعت اطلاع')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('information_saved_hour')"
                  :error-messages="getErrorMessages('information_saved_hour')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.information_degree"
                  :label="t('درجه موثقیت اطلاع')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('information_degree')"
                  :error-messages="getErrorMessages('information_degree')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- TEXTS SECTION -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-text</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('متن‌ها و توضیحات') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('تفصیل اطلاع، نظر مدیر، رهنمود و سایر توضیحات را درج نمایید.') }}
              </span>
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="6">
                <v-textarea
                  v-model="form.tafseel"
                  :label="t('تفصیل')"
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :error="hasError('tafseel')"
                  :error-messages="getErrorMessages('tafseel')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-textarea
                  v-model="form.manager_comment"
                  :label="t('نظر مدیر')"
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :error="hasError('manager_comment')"
                  :error-messages="getErrorMessages('manager_comment')"
                />
              </v-col>
            </v-row>

            <v-row class="px-3 pb-3 pt-0">
              <v-col cols="12" md="6">
                <v-textarea
                  v-model="form.lead_drirection"
                  :label="t('رهنمود رهبریت')"
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :error="hasError('lead_drirection')"
                  :error-messages="getErrorMessages('lead_drirection')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-textarea
                  v-model="form.talks"
                  :label="t('گفتگوهای انجام شده')"
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :error="hasError('talks')"
                  :error-messages="getErrorMessages('talks')"
                />
              </v-col>
            </v-row>

            <v-row class="px-3 pb-3 pt-0">
              <v-col cols="12" md="6">
                <v-textarea
                  v-model="form.note"
                  :label="t('ملاحظات')"
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :error="hasError('note')"
                  :error-messages="getErrorMessages('note')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-textarea
                  v-model="form.result"
                  :label="t('نتیجه گیری')"
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- DOCUMENTS SECTION -->
          <v-sheet class="section-card mb-6" rounded="lg" elevation="1">
            <div class="section-header docs-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-paperclip</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('اسناد ضمیمه') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{
                  t(
                    'می‌توانید چندین فایل را اضافه کنید. برای حذف روی ضربدر کلیک کنید.'
                  )
                }}
              </span>
            </div>
            <v-divider />

            <div class="pa-3">
              <input
                ref="docsInput"
                class="hidden-file"
                type="file"
                multiple
                @change="onPickDocs"
              />

              <div class="docs-field-row">
                <div class="d-flex align-center gap-2 flex-wrap">
                  <v-btn
                    class="uploader-btn2"
                    size="small"
                    variant="elevated"
                    color="primary"
                    density="comfortable"
                    prepend-icon="mdi-tray-arrow-up"
                    @click="triggerDocs"
                  >
                    {{ t('انتخاب و آپلود فایل‌ها') }}
                  </v-btn>

                  <v-chip size="small" variant="outlined" class="text-caption docs-counter-chip">
                    <v-icon size="16" class="ml-1">mdi-file-multiple</v-icon>
                    {{ t('فایل جدید') }}: {{ form._newDocs.length }}
                    &nbsp;|&nbsp;
                    {{ t('موجود') }}: {{ form.existingDocs.length }}
                  </v-chip>
                </div>

                <div
                  class="chips-row"
                  :title="
                    form._newDocs.length || form.existingDocs.length
                      ? ''
                      : t('فایلی انتخاب نشده است')
                  "
                >
                  <!-- NEW FILES -->
                  <v-chip
                    v-for="(f, idx) in form._newDocs"
                    :key="`nf-${idx}`"
                    size="small"
                    variant="outlined"
                    class="chip-outline"
                    :title="f.name"
                    pill
                  >
                    <v-icon size="16" class="ml-1">mdi-plus</v-icon>
                    {{ shortName(f.name) }}
                    <v-icon
                      size="16"
                      class="ml-1"
                      title="حذف"
                      @click.stop="removeNewDocAt(idx)"
                    >
                      mdi-close
                    </v-icon>
                  </v-chip>

                  <!-- EXISTING FILES -->
                  <v-chip
                    v-for="(d, idx) in form.existingDocs"
                    :key="`df-${idx}`"
                    size="small"
                    color="primary"
                    variant="flat"
                    class="chip-existing"
                    :title="d.original_name || d.file_name"
                    @click="openDoc(d.url)"
                    pill
                  >
                    <v-icon size="16" class="ml-1">mdi-file-eye</v-icon>
                    {{ shortName(d.original_name || d.file_name) }}
                    <v-icon
                      size="16"
                      class="ml-1"
                      title="حذف"
                      @click.stop="markDocForRemoval(d)"
                    >
                      mdi-close
                    </v-icon>
                  </v-chip>
                </div>
              </div>
            </div>
          </v-sheet>

          <!-- ACTIONS -->
          <div class="form-actions d-flex justify-end gap-2 flex-wrap">
            <v-btn
              type="submit"
              color="primary"
              class="text-none"
              :loading="saving"
              prepend-icon="mdi-content-save"
            >
              {{ isEdit ? t('ذخیرهٔ تغییرات') : t('ثبت اطلاع') }}
            </v-btn>

            <v-btn
              color="warning"
              variant="outlined"
              class="text-none"
              prepend-icon="mdi-broom"
              @click="resetForm"
            >
              {{ t('پاک کردن ورودی‌ها') }}
            </v-btn>
          </div>
        </v-card>
      </div>
    </v-form>

    <!-- ERROR SNACKBAR -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>

    <!-- SUCCESS SNACKBAR -->
    <v-snackbar v-model="showSuccess" color="success" timeout="3000">
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
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

import AppBar from '../../components/AppBar.vue';
import DateInput from '../../components/DateInput.vue';
import { useDatabaseRepository } from '../../stores/DatabaseRepository';
import { useLoginRepository } from '../../stores/LoginRepository';

const repo = useDatabaseRepository();
const auth = useLoginRepository();

const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n();

/* ===== Direction ===== */
const isRtl = ref(locale.value === 'fa');
watch(locale, (newLocale) => {
  isRtl.value = newLocale === 'fa';
});

/* ===== ROUTE / MODE ===== */
const idParam = computed(() => route.params.id);
const isEdit = computed(() => !!idParam.value);

/* ===== FORM / STATE ===== */
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

/* ===== BACKEND ERRORS ===== */
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

/* ===== FORM MODEL ===== */
const blankForm = () => ({
  id: null,
  resource_id: null,
  office_employee_id: null,

  name: '',
  nick_name: '',
  father_name: '',
  grand_father_name: '',

  main_province: '',
  main_districts: '',
  main_village: '',
  now_province: '',
  now_district: '',
  now_village: '',
  Province_addr: '',

  syasi_info_type: '',
  jenayee_info_type: '',

  information_saved_date: repo.getCurrentDate ? repo.getCurrentDate() : '',
  information_saved_hour: '',
  information_degree: '',

  tafseel: '',
  manager_comment: '',
  lead_drirection: '',
  talks: '',
  note: '',
  result: '',

  existingDocs: [],
  _newDocs: [],
  removed_document_ids: [],
});

const form = ref(blankForm());

/* ===== OPTIONS HELPER ===== */
const toArray = (v) => {
  if (v && typeof v.then === 'function') return [];
  if (Array.isArray(v)) return v;
  if (Array.isArray(v?.data)) return v.data;
  if (Array.isArray(v?.items)) return v.items;
  if (v && typeof v === 'object') return Object.values(v);
  return [];
};

const resourceItems = computed(() => toArray(repo.resource));
const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
const seyasiItems = computed(() => toArray(repo.seyasiOptions));
const jenayeeItems = computed(() => toArray(repo.jenayeeOptions));

/* ===== RULES ===== */
const rules = {
  required: (v) => !!v || t('این فیلد الزامی است'),
  selectRequired: (label) => (v) => !!v || t('{label} الزامی است', { label }),
};

/* ===== DOCS ===== */
const docsInput = ref(null);

function triggerDocs() {
  docsInput.value?.click();
}

function onPickDocs(e) {
  const files = Array.from(e.target.files || []);
  form.value._newDocs = files;
  if (docsInput.value) {
    docsInput.value.value = '';
  }
}

function removeNewDocAt(index) {
  if (!Array.isArray(form.value._newDocs)) return;
  form.value._newDocs.splice(index, 1);
  if (docsInput.value) {
    docsInput.value.value = '';
  }
}

function shortName(name = '') {
  if (name.length <= 18) return name;
  const dot = name.lastIndexOf('.');
  if (dot === -1) return name.slice(0, 15) + '…';
  const ext = name.slice(dot);
  const base = name.slice(0, 15);
  return `${base}…${ext}`;
}

function openDoc(url) {
  if (!url) return;
  window.open(url, '_blank');
}

function markDocForRemoval(doc) {
  if (!doc?.id) return;
  if (!Array.isArray(form.value.removed_document_ids)) {
    form.value.removed_document_ids = [];
  }
  if (!form.value.removed_document_ids.includes(doc.id)) {
    form.value.removed_document_ids.push(doc.id);
  }
  form.value.existingDocs = (form.value.existingDocs || []).filter(
    (d) => d.id !== doc.id,
  );
}

/* ===== LOAD EDIT DATA ===== */
function mapFromApi(x = {}) {
  const base = blankForm();
  base.id = x.id ?? null;

  base.resource_id = x.resource_id ?? null;
  base.office_employee_id = x.office_employee_id ?? null;

  base.name = x.name ?? '';
  base.nick_name = x.nick_name ?? '';
  base.father_name = x.father_name ?? '';
  base.grand_father_name = x.grand_father_name ?? '';

  base.main_province = x.main_province ?? '';
  base.main_districts = x.main_districts ?? '';
  base.main_village = x.main_village ?? '';
  base.now_province = x.now_province ?? '';
  base.now_district = x.now_district ?? '';
  base.now_village = x.now_village ?? '';
  base.Province_addr = x.Province_addr ?? '';

  base.syasi_info_type = x.syasi_info_type ?? '';
  base.jenayee_info_type = x.jenayee_info_type ?? '';

  base.information_saved_date = x.information_saved_date ?? '';
  base.information_saved_hour = x.information_saved_hour ?? '';
  base.information_degree = x.information_degree ?? '';

  base.tafseel = x.tafseel ?? '';
  base.manager_comment = x.manager_comment ?? '';
  base.lead_drirection = x.lead_drirection ?? '';
  base.talks = x.talks ?? '';
  base.note = x.note ?? '';
  base.result = x.result ?? '';

  base.existingDocs = Array.isArray(x.documents) ? x.documents : [];

  return base;
}

async function loadForEdit() {
  if (!isEdit.value) {
    form.value = blankForm();
    return;
  }

  try {
    if (typeof repo.FetchInformationData === 'function') {
      await repo.FetchInformationData(idParam.value);
      const raw = Array.isArray(repo.Information)
        ? repo.Information[0]
        : repo.Information || {};
      const mapped = mapFromApi(raw);
      form.value = { ...mapped, _newDocs: [], removed_document_ids: [] };
    } else {
      await repo.FetchInformationsData?.({
        page: 1,
        itemsPerPage: 100000,
      });
      const list = toArray(repo.Informations);
      const found = list.find((r) => r.id == idParam.value) || {};
      const mapped = mapFromApi(found);
      form.value = { ...mapped, _newDocs: [], removed_document_ids: [] };
    }
  } catch (e) {
    console.error('loadForEdit error', e);
    showErrorMessage(t('خطا در دریافت معلومات'));
  }
}

/* ===== FORM DATA BUILDERS ===== */
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
  delete payload.existingDocs;
  delete payload._newDocs;
  delete payload.removed_document_ids;

  const fd = toFormData(payload);
  (form.value._newDocs || []).forEach((file) => {
    fd.append('documents[]', file);
  });
  return fd;
}

function buildUpdateFormData() {
  const payload = { ...form.value };
  delete payload.existingDocs;
  const removed = payload.removed_document_ids || [];
  delete payload._newDocs;
  delete payload.removed_document_ids;

  const fd = toFormData(payload);

  (form.value._newDocs || []).forEach((file) => {
    fd.append('documents[]', file);
  });

  removed.forEach((id) => {
    fd.append('removed_document_ids[]', id);
  });

  return fd;
}

/* ===== SUBMIT ===== */
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

  if (!form.value.name?.trim()) {
    showErrorMessage(t('نام الزامی است'));
    return;
  }

  saving.value = true;

  try {
    if (!isEdit.value) {
      const fd = buildCreateFormData();
      const created = await repo.CreateInformation(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('اطلاع موفقانه ثبت شد'));
        resetForm();
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo.UpdateInformation(form.value.id, fd).catch(
        (e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        },
      );

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        await loadForEdit();
      } else {
        showErrorMessage(t('ذخیرهٔ تغییرات ناکام شد'));
      }
    }
  } catch (e) {
    console.error('onSubmit error', e);
    showErrorMessage(t('خطا در ذخیره اطلاعات'));
  } finally {
    saving.value = false;
  }
}

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

/* ===== RESET & NAVIGATION ===== */
function resetForm() {
  form.value = blankForm();
  formRef.value?.resetValidation?.();
  clearBackendErrors();
  showSuccessMessage(t('ورودی‌ها پاک شد'));
}

function goBack() {
  router.back();
}

/* ===== INIT ===== */
onMounted(async () => {
  await Promise.allSettled([
    repo.GetOfficeEmployee?.(),
    repo.GetResource?.(),
    repo.FetchInformationsData?.({
      page: 1,
      itemsPerPage: 1,
    }),
  ]);

  await loadForEdit();
});

/* ===== DISPLAY HELPERS ===== */
const formatResource = (r) => {
  if (!r) return '';
  const code = r.code_number ? String(r.code_number) : '';
  const nm = r.name ? String(r.name) : '';
  const dad = r.father_name ? String(r.father_name) : '';
  const id = r.id != null ? `#${r.id}` : '';
  return [code, nm, dad, id].filter(Boolean).join(' — ');
};

/* ===== STEPPER LOGIC ===== */
const step1Complete = computed(() => !!form.value.name?.trim());
const step2Complete = computed(
  () =>
    !!form.value.main_province?.trim() &&
    !!form.value.main_districts?.trim() &&
    !!form.value.main_village?.trim(),
);
const step3Complete = computed(() => !!form.value.information_saved_date);
const step4Complete = computed(
  () =>
    (form.value._newDocs?.length || 0) > 0 ||
    (form.value.existingDocs?.length || 0) > 0 ||
    !!form.value.tafseel?.trim(),
);

const completedSteps = computed(
  () =>
    [step1Complete.value, step2Complete.value, step3Complete.value, step4Complete.value].filter(
      Boolean,
    ).length,
);

const currentStep = computed(() => {
  if (!step1Complete.value) return 1;
  if (!step2Complete.value) return 2;
  if (!step3Complete.value) return 3;
  return 4;
});

const progressValue = computed(() => (completedSteps.value / 4) * 100);
</script>

<style scoped>
.page-bg {
  background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
  font-family: inherit;
}

/* Stepper */
.stepper-shell {
  max-width: 1150px;
  margin-top: 10px;
  margin-bottom: 6px;
}

.stepper-card {
  border-radius: 14px;
  padding: 10px 14px 12px;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(33, 150, 243, 0.12);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
  font-family: inherit;
}

.stepper-track-wrapper {
  position: relative;
  margin-top: 4px;
}

.stepper-track {
  opacity: 0.35;
}

.stepper-dots {
  position: absolute;
  inset-inline: 0;
  inset-block-start: -7px;
  display: flex;
  justify-content: space-between;
  pointer-events: none;
}

.stepper-dot-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.stepper-dot {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: #e0e7f1;
  border: 2px solid #b0bec5;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: auto;
  transition: all 0.2s ease;
}

.stepper-dot.is-active:not(.is-complete) {
  background: #1976d2;
  border-color: #1976d2;
  box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.18);
}

.stepper-dot.is-complete {
  background: #4caf50;
  border-color: #4caf50;
  box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.18);
}

.stepper-dot :deep(.v-icon) {
  color: #fff !important;
}

.stepper-label {
  color: #555;
  font-family: inherit;
  font-weight: 600;
}

.is-complete-text {
  color: #2e7d32;
}

.stepper-caption {
  font-family: inherit;
  font-weight: 600;
}

/* Main form wrapper */
.form-shell {
  max-width: 1150px;
  margin-top: 8px;
  margin-bottom: 40px;
}

/* Card styling */
.form-card {
  border-radius: 18px;
  border: 1px solid rgba(33, 150, 243, 0.08);
  background: #ffffff;
  box-shadow:
    0 12px 30px rgba(0, 0, 0, 0.06),
    0 1px 2px rgba(0, 0, 0, 0.04) !important;
  font-family: inherit;
}

/* Top avatar + chip */
.header-avatar {
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.35);
}

.header-title {
  font-family: inherit;
  font-weight: 700;
}

.header-subtitle {
  font-family: inherit;
  font-weight: 600;
}

/* Sections */
.section-card {
  background: #fafafa;
  border-radius: 16px;
  border: 1px solid #e0e0e0;
  transition: box-shadow 0.2s ease, transform 0.15s ease, border-color 0.2s ease;
  font-family: inherit;
}

.section-card:hover {
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
  border-color: rgba(33, 150, 243, 0.35);
  transform: translateY(-1px);
}

.section-header {
  padding: 8px 14px;
  background: #f7f7f9;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-family: inherit;
}

.docs-header {
  background: #f3f6ff;
}

.section-subtitle {
  color: #777;
  font-family: inherit;
  font-weight: 600;
}

.section-icon {
  color: #1976d2;
}

.section-title {
  font-family: inherit;
  font-weight: 700;
}

/* DOCS */
.hidden-file {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

.docs-field-row {
  display: flex;
  flex-direction: column;
  gap: 8px;
  border-radius: 12px;
  border: 1px dashed #b0bec5;
  padding: 10px 12px;
  background: linear-gradient(135deg, #f9fbff 0%, #f3f6ff 100%);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
  font-family: inherit;
}

.chips-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  padding-top: 4px;
  max-height: 92px;
  overflow-y: auto;
  scrollbar-width: thin;
}

.chip-existing {
  background: #c8dae1 !important;
  color: #000 !important;
}

.chip-outline {
  background: #e3f2fd;
}

.docs-counter-chip {
  font-family: inherit;
  font-weight: 600;
}

/* Form actions */
.form-actions {
  margin-top: 4px;
}

/* Compact inputs */
:deep(.v-field-label),
:deep(.v-label),
:deep(input::placeholder) {
  font-size: 12px !important;
  line-height: 1.2 !important;
  color: #555 !important;
  font-family: inherit !important;
  font-weight: 600;
}

:deep(.v-field__input) {
  min-height: 30px !important;
  padding: 3px 8px !important;
  font-size: 12px !important;
  line-height: 1.2 !important;
  font-family: inherit !important;
}

/* Dropdown primary background */
.primary-select :deep(.v-field) {
  background-color: #e3f2fd !important;
}

/* Buttons & chips font */
:deep(.v-btn),
:deep(.v-chip),
:deep(.v-snackbar),
:deep(.v-card-title),
:deep(.v-card-subtitle),
:deep(.v-list-item-title),
:deep(.v-list-item-subtitle) {
  font-family: inherit !important;
}

/* Small gap helper */
.gap-2 {
  gap: 8px;
}
</style>
