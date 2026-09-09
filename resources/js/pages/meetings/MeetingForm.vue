<template>
  <v-container
    fluid
    class="page-bg"
    :dir="isRtl ? 'rtl' : 'ltr'"
  >
    <AppBar
      :title="
        isEdit
          ? t('اصلاح معلومات جلسه')
          : t('اضافه کردن جلسه')
      "
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک جلسه موجود')
          : t('ثبت یک جلسه جدید در سیستم')
      "
    />

    <!-- ===================== STEPPER ===================== -->
    <section
      class="stepper-shell"
      :dir="isRtl ? 'rtl' : 'ltr'"
    >
      <v-card
        class="stepper-card py-4 pb-12"
        elevation="0"
      >
        <div
          class="d-flex justify-space-between align-center mb-1"
        >
          <span
            class="text-caption text-medium-emphasis"
          >
            {{ t('مراحل خانه پری فرم جلسه') }}
          </span>

          <span
            class="text-caption text-primary font-weight-medium"
          >
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
            <div
              v-for="step in steps"
              :key="step.number"
              class="stepper-dot-group"
            >
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step.complete,
                  'is-active':
                    currentStep === step.number &&
                    !step.complete,
                }"
              >
                <v-icon size="16">
                  {{
                    step.complete
                      ? 'mdi-check'
                      : step.icon
                  }}
                </v-icon>
              </div>

              <span
                class="stepper-label text-caption"
                :class="{
                  'is-complete-text':
                    step.complete,
                }"
              >
                {{ step.title }}
              </span>
            </div>
          </div>
        </div>
      </v-card>
    </section>

    <!-- ===================== FORM ===================== -->
    <v-form
      ref="formRef"
      v-model="formValid"
      lazy-validation
      @submit.prevent="onSubmit"
      :dir="isRtl ? 'rtl' : 'ltr'"
    >
      <div class="form-shell mx-auto">
        <v-card
          class="pa-4 pa-md-6 form-card"
          elevation="4"
        >
          <!-- HEADER -->
          <div
            class="d-flex justify-space-between align-center mb-4 flex-wrap ga-2"
          >
            <div class="d-flex align-center">
              <v-avatar
                size="40"
                class="ml-2"
                color="primary"
              >
                <v-icon
                  size="22"
                  color="white"
                >
                  mdi-account-group
                </v-icon>
              </v-avatar>

              <div>
                <div
                  class="text-subtitle-1 font-weight-medium"
                >
                  {{
                    isEdit
                      ? t(
                          'فرم اصلاح معلومات جلسه'
                        )
                      : t(
                          'فرم ثبت جلسه جدید'
                        )
                  }}
                </div>

                <div
                  class="text-caption text-medium-emphasis"
                >
                  {{
                    t(
                      'لطفاً تمام خانه‌های ضروری را با دقت خانه پری کنید. خانه‌های ستاره‌دار اجباری است.'
                    )
                  }}
                </div>
              </div>
            </div>
          </div>

          <!-- ================================================= -->
          <!-- SECTION 1 -->
          <!-- ================================================= -->

          <v-sheet
            class="section-card mb-4"
            rounded="lg"
            elevation="1"
          >
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon
                  class="ml-2 section-icon"
                >
                  mdi-map-marker-radius
                </v-icon>

                <span
                  class="text-subtitle-2 font-weight-medium"
                >
                  {{
                    t(
                      'مشخصات و موقعیت جلسه'
                    )
                  }}
                </span>
              </div>
            </div>

            <v-divider />

            <v-row class="pa-3">
              <!-- Meeting title -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.meeting_title
                  "
                  :label="
                    t('عنوان جلسه *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'meeting_title'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'meeting_title'
                    )
                  "
                />
              </v-col>

              <!-- Organ -->
              <v-col
                cols="12"
                md="6"
              >
                <OrganSelect
                  v-model="
                    form.organ
                  "
                  :label="
                    t('ارگان *')
                  "
                  :disabled="
                    saving
                  "
                />

                <div
                  v-if="
                    hasError(
                      'organ'
                    )
                  "
                  class="field-error"
                >
                  {{
                    getErrorMessages(
                      'organ'
                    ).join('، ')
                  }}
                </div>
              </v-col>

              <!-- Province + district -->
              <v-col cols="12">
                <ProvinceDistrictNameSelect
                  v-model:province="
                    form.province
                  "
                  v-model:district="
                    form.district
                  "
                  :province-label="
                    t('ولایت *')
                  "
                  :district-label="
                    t('ولسوالی *')
                  "
                  :mdProvince="6"
                  :mdDistrict="6"
                />

                <div
                  v-if="
                    hasError(
                      'province'
                    ) ||
                    hasError(
                      'district'
                    )
                  "
                  class="field-error"
                >
                  {{
                    [
                      ...getErrorMessages(
                        'province'
                      ),
                      ...getErrorMessages(
                        'district'
                      ),
                    ].join('، ')
                  }}
                </div>
              </v-col>

              <!-- Specific place -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.specific_place
                  "
                  :label="
                    t(
                      'محل مشخص *'
                    )
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'specific_place'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'specific_place'
                    )
                  "
                />
              </v-col>

              <!-- Coordinates -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.coordinates
                  "
                  :label="
                    t('کوردینات *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'coordinates'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'coordinates'
                    )
                  "
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- ================================================= -->
          <!-- SECTION 2 -->
          <!-- ================================================= -->

          <v-sheet
            class="section-card mb-4"
            rounded="lg"
            elevation="1"
          >
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon
                  class="ml-2 section-icon"
                >
                  mdi-calendar-clock
                </v-icon>

                <span
                  class="text-subtitle-2 font-weight-medium"
                >
                  {{
                    t(
                      'زمان و اشتراک کننده گان'
                    )
                  }}
                </span>
              </div>
            </div>

            <v-divider />

            <v-row class="pa-3">
              <!-- Start date -->
              <v-col
                cols="12"
                md="4"
              >
                <DateInput
                  v-model="
                    form.start_date
                  "
                  :label="
                    t(
                      'تاریخ شروع *'
                    )
                  "
                />

                <div
                  v-if="
                    hasError(
                      'start_date'
                    )
                  "
                  class="field-error"
                >
                  {{
                    getErrorMessages(
                      'start_date'
                    ).join('، ')
                  }}
                </div>
              </v-col>

              <!-- End date -->
              <v-col
                cols="12"
                md="4"
              >
                <DateInput
                  v-model="
                    form.end_date
                  "
                  :label="
                    t(
                      'تاریخ ختم *'
                    )
                  "
                />

                <div
                  v-if="
                    hasError(
                      'end_date'
                    )
                  "
                  class="field-error"
                >
                  {{
                    getErrorMessages(
                      'end_date'
                    ).join('، ')
                  }}
                </div>
              </v-col>

              <!-- Total time -->
              <v-col
                cols="12"
                md="4"
              >
                <v-text-field
                  v-model="
                    form.total_time
                  "
                  :label="
                    t(
                      'مجموع زمان *'
                    )
                  "
                  :placeholder="
                    t('مثلاً ۲ ساعت')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'total_time'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'total_time'
                    )
                  "
                />
              </v-col>

              <!-- Participants -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.participant
                  "
                  :label="
                    t(
                      'اشتراک کننده گان *'
                    )
                  "
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'participant'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'participant'
                    )
                  "
                />
              </v-col>

              <!-- Boss participant -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.boss_participant
                  "
                  :label="
                    t(
                      'اشتراک کننده مسئول *'
                    )
                  "
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'boss_participant'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'boss_participant'
                    )
                  "
                />
              </v-col>

              <!-- Employee count -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model.number="
                    form.office_employee_count
                  "
                  :label="
                    t(
                      'تعداد کارمندان دفتر *'
                    )
                  "
                  type="number"
                  min="0"
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                    rules.nonNegativeInteger,
                  ]"
                  :error="
                    hasError(
                      'office_employee_count'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'office_employee_count'
                    )
                  "
                />
              </v-col>

              <!-- Leader -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.leader_name
                  "
                  :label="
                    t(
                      'نام مسئول *'
                    )
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'leader_name'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'leader_name'
                    )
                  "
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- ================================================= -->
          <!-- SECTION 3 -->
          <!-- ================================================= -->

          <v-sheet
            class="section-card mb-4"
            rounded="lg"
            elevation="1"
          >
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon
                  class="ml-2 section-icon"
                >
                  mdi-clipboard-text
                </v-icon>

                <span
                  class="text-subtitle-2 font-weight-medium"
                >
                  {{
                    t(
                      'نتایج و مشکلات جلسه'
                    )
                  }}
                </span>
              </div>
            </div>

            <v-divider />

            <v-row class="pa-3">
              <!-- Done actions -->
              <v-col cols="12">
                <v-textarea
                  v-model="
                    form.done_actions
                  "
                  :label="
                    t(
                      'فعالیت های انجام شده *'
                    )
                  "
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'done_actions'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'done_actions'
                    )
                  "
                />
              </v-col>

              <!-- Suspicious situations -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.mashkok_situations
                  "
                  :label="
                    t(
                      'حالات مشکوک *'
                    )
                  "
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'mashkok_situations'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'mashkok_situations'
                    )
                  "
                />
              </v-col>

              <!-- Problems -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.problems
                  "
                  :label="
                    t(
                      'مشکلات *'
                    )
                  "
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'problems'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'problems'
                    )
                  "
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- ================================================= -->
          <!-- SECTION 4 -->
          <!-- ================================================= -->

          <v-sheet
            class="section-card mb-4"
            rounded="lg"
            elevation="1"
          >
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon
                  class="ml-2 section-icon"
                >
                  mdi-shield-account
                </v-icon>

                <span
                  class="text-subtitle-2 font-weight-medium"
                >
                  {{
                    t(
                      'تماس، امنیت، ملاحظات و اسناد'
                    )
                  }}
                </span>
              </div>
            </div>

            <v-divider />

            <v-row class="pa-3">
              <!-- Supplier -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.meeting_supplier
                  "
                  :label="
                    t(
                      'تهیه کننده جلسه *'
                    )
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'meeting_supplier'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'meeting_supplier'
                    )
                  "
                />
              </v-col>

              <!-- Supplier phone -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.meeting_supplier_phone
                  "
                  :label="
                    t(
                      'شماره تماس تهیه کننده *'
                    )
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                    rules.phone,
                  ]"
                  :error="
                    hasError(
                      'meeting_supplier_phone'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'meeting_supplier_phone'
                    )
                  "
                />
              </v-col>

              <!-- Security responsible -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.meeting_amniyat_responsable
                  "
                  :label="
                    t(
                      'مسئول امنیت جلسه *'
                    )
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'meeting_amniyat_responsable'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'meeting_amniyat_responsable'
                    )
                  "
                />
              </v-col>

              <!-- Security responsible phone -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.meeting_amniyat_responsable_phone
                  "
                  :label="
                    t(
                      'شماره تماس مسئول امنیت *'
                    )
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                    rules.phone,
                  ]"
                  :error="
                    hasError(
                      'meeting_amniyat_responsable_phone'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'meeting_amniyat_responsable_phone'
                    )
                  "
                />
              </v-col>

              <!-- Security count -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model.number="
                    form.amniyat_mansobine_count
                  "
                  :label="
                    t(
                      'تعداد منسوبین امنیتی *'
                    )
                  "
                  type="number"
                  min="0"
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                    rules.nonNegativeInteger,
                  ]"
                  :error="
                    hasError(
                      'amniyat_mansobine_count'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'amniyat_mansobine_count'
                    )
                  "
                />
              </v-col>

              <!-- Notes -->
              <v-col cols="12">
                <v-textarea
                  v-model="
                    form.note
                  "
                  :label="
                    t(
                      'ملاحظات *'
                    )
                  "
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'note'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'note'
                    )
                  "
                />
              </v-col>
            </v-row>

            <!-- ================= DOCUMENTS ================= -->

            <div class="pa-3 pt-0">
              <input
                ref="docsInput"
                class="hidden-file"
                type="file"
                multiple
                @change="onPickDocs"
              />

              <div class="docs-field-row">
                <div
                  class="d-flex align-center ga-2 flex-wrap"
                >
                  <v-btn
                    size="small"
                    variant="elevated"
                    color="primary"
                    density="comfortable"
                    prepend-icon="mdi-tray-arrow-up"
                    @click="
                      triggerDocs
                    "
                  >
                    {{
                      t(
                        'انتخاب و آپلود فایل‌ها'
                      )
                    }}
                  </v-btn>

                  <v-chip
                    size="small"
                    variant="outlined"
                    class="text-caption"
                  >
                    <v-icon
                      size="16"
                      class="ml-1"
                    >
                      mdi-file-multiple
                    </v-icon>

                    {{
                      t('فایل جدید')
                    }}:
                    {{
                      form._newDocs
                        .length
                    }}

                    &nbsp;|&nbsp;

                    {{
                      t('موجود')
                    }}:
                    {{
                      form.existingDocs
                        .length
                    }}
                  </v-chip>
                </div>

                <div class="chips-row">
                  <!-- New docs -->
                  <v-chip
                    v-for="(
                      file, index
                    ) in form._newDocs"
                    :key="
                      `new-${index}`
                    "
                    size="small"
                    variant="outlined"
                    :title="file.name"
                  >
                    <v-icon
                      size="16"
                      class="ml-1"
                    >
                      mdi-plus
                    </v-icon>

                    {{
                      shortName(
                        file.name
                      )
                    }}

                    <v-icon
                      size="16"
                      class="ml-1"
                      @click.stop="
                        removeNewDocAt(
                          index
                        )
                      "
                    >
                      mdi-close
                    </v-icon>
                  </v-chip>

                  <!-- Existing docs -->
                  <v-chip
                    v-for="
                      document in form.existingDocs
                    "
                    :key="
                      `existing-${document.id}`
                    "
                    size="small"
                    color="primary"
                    variant="flat"
                    :title="
                      document.original_name ||
                      document.file_name
                    "
                    @click="
                      openDoc(
                        document.url
                      )
                    "
                  >
                    <v-icon
                      size="16"
                      class="ml-1"
                    >
                      mdi-file-eye
                    </v-icon>

                    {{
                      shortName(
                        document.original_name ||
                          document.file_name
                      )
                    }}

                    <v-icon
                      size="16"
                      class="ml-1"
                      @click.stop="
                        markDocForRemoval(
                          document
                        )
                      "
                    >
                      mdi-close
                    </v-icon>
                  </v-chip>
                </div>
              </div>
            </div>
          </v-sheet>

          <!-- ================= ACTIONS ================= -->

          <div
            class="form-actions d-flex justify-end ga-2 flex-wrap"
          >
            <v-btn
              type="submit"
              color="primary"
              class="text-none"
              :loading="saving"
              prepend-icon="mdi-content-save"
            >
              {{
                isEdit
                  ? t(
                      'ذخیرهٔ تغییرات'
                    )
                  : t(
                      'ثبت جلسه'
                    )
              }}
            </v-btn>

            <v-btn
              color="warning"
              variant="outlined"
              class="text-none"
              prepend-icon="mdi-broom"
              @click="resetForm"
            >
              {{
                t(
                  'پاک کردن ورودی‌ها'
                )
              }}
            </v-btn>

            <v-btn
              color="secondary"
              variant="outlined"
              class="text-none"
              prepend-icon="mdi-arrow-left"
              @click="goBack"
            >
              {{ t('برگشت') }}
            </v-btn>
          </div>
        </v-card>
      </div>
    </v-form>

    <!-- ================= ERROR ================= -->

    <v-snackbar
      v-model="showError"
      color="error"
      timeout="5000"
    >
      {{ errorMessage }}

      <template #actions>
        <v-btn
          variant="text"
          @click="
            showError = false
          "
        >
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>

    <!-- ================= SUCCESS ================= -->

    <v-snackbar
      v-model="showSuccess"
      color="success"
      timeout="3000"
    >
      {{ successMessage }}

      <template #actions>
        <v-btn
          variant="text"
          @click="
            showSuccess = false
          "
        >
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import {
  ref,
  computed,
  watch,
  onMounted,
  nextTick,
} from 'vue';

import {
  useRoute,
  useRouter,
} from 'vue-router';

import {
  useI18n,
} from 'vue-i18n';

import AppBar
  from '@/components/AppBar.vue';

import DateInput
  from '@/components/DateInput.vue';

import ProvinceDistrictNameSelect
  from '@/components/ProvinceDistrictSelect.vue';

import OrganSelect
  from '@/components/OrganSelect.vue';

import {
  useDatabaseRepository,
} from '@/stores/DatabaseRepository';

const repo =
  useDatabaseRepository();

const route =
  useRoute();

const router =
  useRouter();

const {
  t,
  locale,
} = useI18n();

/* =========================================================
   RTL
========================================================= */

const isRtl =
  ref(
    locale.value === 'fa'
  );

watch(
  locale,
  (value) => {
    isRtl.value =
      value === 'fa';
  }
);

/* =========================================================
   ROUTE
========================================================= */

const idParam =
  computed(
    () => route.params.id
  );

const isEdit =
  computed(
    () => !!idParam.value
  );

/* =========================================================
   STATE
========================================================= */

const formRef =
  ref(null);

const formValid =
  ref(false);

const saving =
  ref(false);

const showError =
  ref(false);

const errorMessage =
  ref('');

const showSuccess =
  ref(false);

const successMessage =
  ref('');

/* =========================================================
   MESSAGES
========================================================= */

function showErrorMessage(
  message
) {
  errorMessage.value =
    message;

  showError.value =
    true;
}

function showSuccessMessage(
  message
) {
  successMessage.value =
    message;

  showSuccess.value =
    true;
}

/* =========================================================
   BACKEND VALIDATION
========================================================= */

const backendErrors =
  ref({});

function setBackendErrors(
  errors
) {
  backendErrors.value =
    errors || {};
}

function clearBackendErrors() {
  backendErrors.value =
    {};
}

function hasError(
  field
) {
  return !!backendErrors
    .value?.[field];
}

function getErrorMessages(
  field
) {
  return (
    backendErrors
      .value?.[field] ??
    []
  );
}

/* =========================================================
   BLANK FORM
========================================================= */

const blankForm =
  () => ({
    id: null,

    meeting_title: '',

    organ: '',

    province: '',

    district: '',

    specific_place: '',

    coordinates: '',

    start_date: '',

    end_date: '',

    total_time: '',

    participant: '',

    boss_participant: '',

    office_employee_count:
      '',

    leader_name: '',

    done_actions: '',

    mashkok_situations: '',

    problems: '',

    meeting_supplier: '',

    meeting_supplier_phone:
      '',

    meeting_amniyat_responsable:
      '',

    meeting_amniyat_responsable_phone:
      '',

    amniyat_mansobine_count:
      '',

    note: '',

    existingDocs: [],

    _newDocs: [],

    removed_document_ids:
      [],
  });

const form =
  ref(
    blankForm()
  );

/* =========================================================
   RULES
========================================================= */

const rules = {
  required: (
    value
  ) => {
    if (
      value === null ||
      value === undefined ||
      String(
        value
      ).trim() === ''
    ) {
      return t(
        'این فیلد الزامی است'
      );
    }

    return true;
  },

  phone: (
    value
  ) => {
    if (!value)
      return true;

    return (
      /^[0-9+\-\s()]{7,30}$/.test(
        String(
          value
        )
      ) ||
      t(
        'شماره تماس نامعتبر است'
      )
    );
  },

  nonNegativeInteger: (
    value
  ) => {
    if (
      value === '' ||
      value === null ||
      value === undefined
    ) {
      return true;
    }

    const number =
      Number(
        value
      );

    if (
      !Number.isInteger(
        number
      )
    ) {
      return t(
        'مقدار باید عدد صحیح باشد'
      );
    }

    if (
      number < 0
    ) {
      return t(
        'مقدار نمی‌تواند منفی باشد'
      );
    }

    return true;
  },
};

/* =========================================================
   DOCUMENTS
========================================================= */

const docsInput =
  ref(null);

function triggerDocs() {
  docsInput.value?.click();
}

function onPickDocs(
  event
) {
  const files =
    Array.from(
      event.target.files ||
        []
    );

  form.value._newDocs =
    [
      ...form.value
        ._newDocs,
      ...files,
    ];

  if (
    docsInput.value
  ) {
    docsInput.value.value =
      '';
  }
}

function removeNewDocAt(
  index
) {
  form.value._newDocs
    .splice(
      index,
      1
    );
}

function shortName(
  name = ''
) {
  if (
    name.length <= 22
  ) {
    return name;
  }

  const dot =
    name.lastIndexOf(
      '.'
    );

  if (
    dot === -1
  ) {
    return (
      name.slice(
        0,
        19
      ) + '…'
    );
  }

  return (
    name.slice(
      0,
      17
    ) +
    '…' +
    name.slice(dot)
  );
}

function openDoc(
  url
) {
  if (!url)
    return;

  window.open(
    url,
    '_blank'
  );
}

function markDocForRemoval(
  document
) {
  if (
    !document?.id
  ) {
    return;
  }

  if (
    !form.value
      .removed_document_ids
      .includes(
        document.id
      )
  ) {
    form.value
      .removed_document_ids
      .push(
        document.id
      );
  }

  form.value.existingDocs =
    form.value
      .existingDocs
      .filter(
        (item) =>
          item.id !==
          document.id
      );
}

/* =========================================================
   API -> FORM
========================================================= */

function mapFromApi(
  payload = {}
) {
  const item =
    payload?.data ??
    payload ??
    {};

  const mapped =
    blankForm();

  mapped.id =
    item.id ??
    null;

  mapped.meeting_title =
    item.meeting_title ??
    '';

  mapped.organ =
    item.organ ??
    '';

  mapped.province =
    item.province ??
    '';

  mapped.district =
    item.district ??
    '';

  mapped.specific_place =
    item.specific_place ??
    '';

  mapped.coordinates =
    item.coordinates ??
    '';

  mapped.start_date =
    item.start_date ??
    '';

  mapped.end_date =
    item.end_date ??
    '';

  mapped.total_time =
    item.total_time ??
    '';

  mapped.participant =
    item.participant ??
    '';

  mapped.boss_participant =
    item.boss_participant ??
    '';

  mapped.office_employee_count =
    item.office_employee_count ??
    '';

  mapped.leader_name =
    item.leader_name ??
    '';

  mapped.done_actions =
    item.done_actions ??
    '';

  mapped.mashkok_situations =
    item.mashkok_situations ??
    '';

  mapped.problems =
    item.problems ??
    '';

  mapped.meeting_supplier =
    item.meeting_supplier ??
    '';

  mapped.meeting_supplier_phone =
    item.meeting_supplier_phone ??
    '';

  mapped.meeting_amniyat_responsable =
    item.meeting_amniyat_responsable ??
    '';

  mapped.meeting_amniyat_responsable_phone =
    item.meeting_amniyat_responsable_phone ??
    '';

  mapped.amniyat_mansobine_count =
    item.amniyat_mansobine_count ??
    '';

  mapped.note =
    item.note ??
    '';

  mapped.existingDocs =
    Array.isArray(
      item.documents
    )
      ? item.documents
      : [];

  mapped._newDocs =
    [];

  mapped.removed_document_ids =
    [];

  return mapped;
}

/* =========================================================
   LOAD EDIT
========================================================= */

async function loadForEdit() {
  if (
    !isEdit.value
  ) {
    form.value =
      blankForm();

    return;
  }

  try {
    await repo
      .FetchMeetingData(
        idParam.value
      );

    form.value =
      mapFromApi(
        repo.Meeting ||
          {}
      );
  } catch (error) {
    console.error(
      error
    );

    showErrorMessage(
      t(
        'خطا در دریافت معلومات جلسه'
      )
    );
  }
}

/* =========================================================
   FORM DATA
========================================================= */

function appendValue(
  formData,
  key,
  value
) {
  if (
    value === undefined ||
    value === null
  ) {
    return;
  }

  formData.append(
    key,
    String(
      value
    )
  );
}

function buildCreateFormData() {
  const formData =
    new FormData();

  const excludedKeys =
    [
      'id',
      'existingDocs',
      '_newDocs',
      'removed_document_ids',
    ];

  Object.entries(
    form.value
  ).forEach(
    ([
      key,
      value,
    ]) => {
      if (
        excludedKeys
          .includes(
            key
          )
      ) {
        return;
      }

      appendValue(
        formData,
        key,
        value
      );
    }
  );

  (
    form.value
      ._newDocs ||
    []
  ).forEach(
    (file) => {
      formData.append(
        'documents[]',
        file
      );
    }
  );

  return formData;
}

function buildUpdateFormData() {
  const formData =
    buildCreateFormData();

  (
    form.value
      .removed_document_ids ||
    []
  ).forEach(
    (id) => {
      formData.append(
        'removed_document_ids[]',
        id
      );
    }
  );

  return formData;
}

/* =========================================================
   EXTRA REQUIRED CHECK
========================================================= */

const requiredFields =
  [
    'meeting_title',
    'organ',
    'province',
    'district',
    'specific_place',
    'coordinates',
    'start_date',
    'end_date',
    'total_time',
    'participant',
    'boss_participant',
    'office_employee_count',
    'leader_name',
    'done_actions',
    'mashkok_situations',
    'problems',
    'meeting_supplier',
    'meeting_supplier_phone',
    'meeting_amniyat_responsable',
    'meeting_amniyat_responsable_phone',
    'amniyat_mansobine_count',
    'note',
  ];

function allRequiredFieldsFilled() {
  return requiredFields
    .every(
      (field) => {
        const value =
          form.value[
            field
          ];

        return (
          value !== null &&
          value !== undefined &&
          String(
            value
          ).trim() !==
            ''
        );
      }
    );
}

/* =========================================================
   DATE CHECK
========================================================= */

function dateOrderValid() {
  if (
    !form.value
      .start_date ||
    !form.value
      .end_date
  ) {
    return true;
  }

  /*
    Backend also validates:
    end_date >= start_date

    This simple check works with the normal Y-m-d
    values used by DateInput in your forms.
  */

  return (
    String(
      form.value
        .end_date
    ) >=
    String(
      form.value
        .start_date
    )
  );
}

/* =========================================================
   SUBMIT
========================================================= */

async function onSubmit() {
  clearBackendErrors();

  const currentForm =
    formRef.value;

  if (!currentForm)
    return;

  const {
    valid,
  } =
    await currentForm
      .validate();

  if (
    !valid ||
    !allRequiredFieldsFilled()
  ) {
    showErrorMessage(
      t(
        'لطفاً فیلدهای قرمز را درست کنید'
      )
    );

    await nextTick();

    scrollToFirstInvalid();

    return;
  }

  if (
    !dateOrderValid()
  ) {
    showErrorMessage(
      t(
        'تاریخ ختم نمی‌تواند قبل از تاریخ شروع باشد.'
      )
    );

    return;
  }

  saving.value =
    true;

  try {
    /* CREATE */
    if (
      !isEdit.value
    ) {
      const created =
        await repo
          .CreateMeeting(
            buildCreateFormData()
          )
          .catch(
            (error) => {
              if (
                error
                  ?.response
                  ?.data
                  ?.errors
              ) {
                setBackendErrors(
                  error.response
                    .data
                    .errors
                );
              }

              return null;
            }
          );

      if (
        created?.id
      ) {
        showSuccessMessage(
          t(
            'جلسه موفقانه ثبت شد'
          )
        );

        router
          .push(
            '/app/meetings'
          )
          .catch(
            () => {}
          );
      } else {
        showErrorMessage(
          t(
            'ثبت معلومات ناکام شد'
          )
        );
      }

      return;
    }

    /* UPDATE */

    const updated =
      await repo
        .UpdateMeeting(
          form.value.id,
          buildUpdateFormData()
        )
        .catch(
          (error) => {
            if (
              error
                ?.response
                ?.data
                ?.errors
            ) {
              setBackendErrors(
                error.response
                  .data
                  .errors
              );
            }

            return null;
          }
        );

    if (
      updated?.id
    ) {
      showSuccessMessage(
        t(
          'تغییرات موفقانه ذخیره شد'
        )
      );

      router
        .push(
          '/app/meetings'
        )
        .catch(
          () => {}
        );
    } else {
      showErrorMessage(
        t(
          'ذخیرهٔ تغییرات ناکام شد'
        )
      );
    }
  } catch (error) {
    console.error(
      'Meeting save error:',
      error
    );

    showErrorMessage(
      t(
        'خطا در ذخیره اطلاعات'
      )
    );
  } finally {
    saving.value =
      false;
  }
}

/* =========================================================
   SCROLL VALIDATION
========================================================= */

function scrollToFirstInvalid() {
  const first =
    document
      .querySelector(
        '.v-input--error, [aria-invalid="true"]'
      ) ||
    document
      .querySelector(
        '.v-messages__message'
      );

  if (!first)
    return;

  first.scrollIntoView({
    behavior:
      'smooth',
    block:
      'center',
  });

  const input =
    first.querySelector?.(
      'input, textarea, .v-field__input'
    );

  if (
    input &&
    typeof input.focus ===
      'function'
  ) {
    input.focus();
  }
}

/* =========================================================
   RESET
========================================================= */

function resetForm() {
  form.value =
    blankForm();

  formRef.value
    ?.resetValidation?.();

  clearBackendErrors();

  showSuccessMessage(
    t(
      'ورودی‌ها پاک شد'
    )
  );
}

/* =========================================================
   BACK
========================================================= */

function goBack() {
  router.back();
}

/* =========================================================
   STEPPER
========================================================= */

const step1Complete =
  computed(
    () =>
      !!String(
        form.value
          .meeting_title ||
          ''
      ).trim() &&
      !!String(
        form.value.organ ||
          ''
      ).trim() &&
      !!String(
        form.value
          .province ||
          ''
      ).trim() &&
      !!String(
        form.value
          .district ||
          ''
      ).trim() &&
      !!String(
        form.value
          .specific_place ||
          ''
      ).trim()
  );

const step2Complete =
  computed(
    () =>
      !!String(
        form.value
          .start_date ||
          ''
      ).trim() &&
      !!String(
        form.value
          .end_date ||
          ''
      ).trim() &&
      !!String(
        form.value
          .participant ||
          ''
      ).trim() &&
      !!String(
        form.value
          .leader_name ||
          ''
      ).trim()
  );

const step3Complete =
  computed(
    () =>
      !!String(
        form.value
          .done_actions ||
          ''
      ).trim() &&
      !!String(
        form.value
          .mashkok_situations ||
          ''
      ).trim() &&
      !!String(
        form.value
          .problems ||
          ''
      ).trim()
  );

const step4Complete =
  computed(
    () =>
      !!String(
        form.value
          .meeting_supplier ||
          ''
      ).trim() &&
      !!String(
        form.value
          .meeting_amniyat_responsable ||
          ''
      ).trim() &&
      !!String(
        form.value.note ||
          ''
      ).trim()
  );

const completedSteps =
  computed(
    () =>
      [
        step1Complete
          .value,
        step2Complete
          .value,
        step3Complete
          .value,
        step4Complete
          .value,
      ].filter(
        Boolean
      ).length
  );

const currentStep =
  computed(
    () => {
      if (
        !step1Complete
          .value
      ) {
        return 1;
      }

      if (
        !step2Complete
          .value
      ) {
        return 2;
      }

      if (
        !step3Complete
          .value
      ) {
        return 3;
      }

      return 4;
    }
  );

const progressValue =
  computed(
    () =>
      (
        completedSteps
          .value /
        4
      ) *
      100
  );

const steps =
  computed(
    () => [
      {
        number: 1,
        title:
          t(
            'مشخصات'
          ),
        complete:
          step1Complete
            .value,
        icon:
          'mdi-map-marker-radius',
      },

      {
        number: 2,
        title:
          t(
            'زمان و اشتراک'
          ),
        complete:
          step2Complete
            .value,
        icon:
          'mdi-calendar-clock',
      },

      {
        number: 3,
        title:
          t(
            'نتایج'
          ),
        complete:
          step3Complete
            .value,
        icon:
          'mdi-clipboard-text',
      },

      {
        number: 4,
        title:
          t(
            'امنیت و اسناد'
          ),
        complete:
          step4Complete
            .value,
        icon:
          'mdi-shield-account',
      },
    ]
  );

/* =========================================================
   INIT
========================================================= */

onMounted(
  async () => {
    await loadForEdit();
  }
);
</script>

<style scoped>
.page-bg {
  background:
    radial-gradient(
      circle at top,
      #f2f5ff 0,
      #f5f5f5 40%,
      #fafafa 100%
    );

  font-family:
    inherit;

  min-height:
    100%;
}

.ga-2 {
  gap: 8px;
}

/* =========================================================
   STEPPER
========================================================= */

.stepper-shell {
  position:
    absolute;

  top:
    80px;

  left:
    50%;

  transform:
    translateX(-50%);

  max-width:
    85%;

  width:
    100%;

  z-index:
    20;

  margin:
    0;

  padding-bottom:
    12px;
}

.stepper-card {
  border-radius:
    14px;

  padding:
    10px 14px 12px;

  background:
    rgba(
      255,
      255,
      255,
      0.95
    );

  border:
    1px solid
    rgba(
      33,
      150,
      243,
      0.12
    );

  box-shadow:
    0 4px 12px
    rgba(
      0,
      0,
      0,
      0.06
    );
}

.stepper-track-wrapper {
  position:
    relative;

  margin-top:
    4px;
}

.stepper-track {
  opacity:
    0.35;
}

.stepper-dots {
  position:
    absolute;

  inset-inline:
    0;

  inset-block-start:
    -7px;

  display:
    flex;

  justify-content:
    space-between;

  pointer-events:
    none;
}

.stepper-dot-group {
  display:
    flex;

  flex-direction:
    column;

  align-items:
    center;

  min-width:
    90px;
}

.stepper-dot {
  width:
    28px;

  height:
    28px;

  border-radius:
    50%;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  background:
    #fff;

  border:
    2px solid
    rgba(
      var(
        --v-theme-primary
      ),
      0.35
    );

  color:
    rgb(
      var(
        --v-theme-primary
      )
    );
}

.stepper-dot.is-active {
  border-color:
    rgb(
      var(
        --v-theme-primary
      )
    );

  box-shadow:
    0 0 0 4px
    rgba(
      var(
        --v-theme-primary
      ),
      0.08
    );
}

.stepper-dot.is-complete {
  background:
    rgb(
      var(
        --v-theme-primary
      )
    );

  border-color:
    rgb(
      var(
        --v-theme-primary
      )
    );

  color:
    #fff;
}

.stepper-label {
  margin-top:
    7px;

  white-space:
    nowrap;
}

.is-complete-text {
  color:
    rgb(
      var(
        --v-theme-primary
      )
    );

  font-weight:
    600;
}

/* =========================================================
   FORM
========================================================= */

.form-shell {
  max-width:
    1150px;

  margin-top:
    140px;

  margin-bottom:
    40px;
}

.form-card {
  border-radius:
    16px;
}

.section-card {
  overflow:
    hidden;

  border:
    1px solid
    rgba(
      33,
      150,
      243,
      0.08
    );
}

.section-header {
  padding:
    12px 16px;

  background:
    rgba(
      var(
        --v-theme-primary
      ),
      0.04
    );
}

.section-icon {
  color:
    rgb(
      var(
        --v-theme-primary
      )
    );
}

/* =========================================================
   DOCUMENTS
========================================================= */

.hidden-file {
  display:
    none;
}

.docs-field-row {
  display:
    flex;

  flex-direction:
    column;

  gap:
    12px;
}

.chips-row {
  display:
    flex;

  gap:
    8px;

  flex-wrap:
    wrap;

  min-height:
    32px;
}

.field-error {
  color:
    rgb(
      var(
        --v-theme-error
      )
    );

  font-size:
    0.75rem;

  margin-top:
    -4px;

  padding-inline:
    12px;
}

/* =========================================================
   MOBILE
========================================================= */

@media (
  max-width: 960px
) {
  .stepper-shell {
    top:
      64px;

    max-width:
      94%;
  }

  .form-shell {
    margin-top:
      150px;
  }

  .stepper-label {
    font-size:
      0.68rem !important;
  }
}
</style>