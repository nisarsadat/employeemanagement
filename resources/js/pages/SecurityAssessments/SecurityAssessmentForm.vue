<template>
  <v-container
    fluid
    class="page-bg"
    :dir="isRtl ? 'rtl' : 'ltr'"
  >
    <AppBar
      :title="
        isEdit
          ? t('اصلاح معلومات ارزیابی امنیتی')
          : t('اضافه کردن ارزیابی امنیتی')
      "
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ارزیابی امنیتی موجود')
          : t('ثبت یک ارزیابی امنیتی جدید در سیستم')
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
            {{ t('مراحل خانه پری فرم ارزیابی امنیتی') }}
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
                  mdi-shield-check-outline
                </v-icon>
              </v-avatar>

              <div>
                <div
                  class="text-subtitle-1 font-weight-medium"
                >
                  {{
                    isEdit
                      ? t(
                          'فرم اصلاح معلومات ارزیابی امنیتی'
                        )
                      : t(
                          'فرم ثبت ارزیابی امنیتی جدید'
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
          <!-- SECTION 1 : مشخصات و موقعیت -->
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
                  {{ t('مشخصات و موقعیت') }}
                </span>
              </div>
            </div>

            <v-divider />

            <v-row class="pa-3">
              <!-- Place name -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.place_name
                  "
                  :label="
                    t('د ځای نوم *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'place_name'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'place_name'
                    )
                  "
                />
              </v-col>

              <!-- Responsible / owner name -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.responsible_owner_name
                  "
                  :label="
                    t('نام مسؤول / مالک *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'responsible_owner_name'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'responsible_owner_name'
                    )
                  "
                />
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
                    t('ناحیه / ولسوالی *')
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
                    t('محل مشخص *')
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

              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="form.north"
                  :label="t('شمال')"
                  rows="2"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :error="hasError('north')"
                  :error-messages="getErrorMessages('north')"
                />
              </v-col>

              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="form.south"
                  :label="t('جنوب')"
                  rows="2"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :error="hasError('south')"
                  :error-messages="getErrorMessages('south')"
                />
              </v-col>

              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="form.east"
                  :label="t('شرق')"
                  rows="2"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :error="hasError('east')"
                  :error-messages="getErrorMessages('east')"
                />
              </v-col>

              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="form.west"
                  :label="t('غرب')"
                  rows="2"
                  auto-grow
                  density="compact"
                  variant="outlined"
                  :error="hasError('west')"
                  :error-messages="getErrorMessages('west')"
                />
              </v-col>

              <!-- ✅ ترسره کوونکي (one or more office employees) -->
              <v-col
                cols="12"
                md="6"
              >
                <v-select
                  v-model="
                    form.office_employee_ids
                  "
                  :items="
                    employeeOptions
                  "
                  item-title="title"
                  item-value="value"
                  :label="
                    t('ترسره کوونکي')
                  "
                  multiple
                  chips
                  closable-chips
                  clearable
                  density="compact"
                  variant="outlined"
                  :error="
                    hasError(
                      'office_employee_ids'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'office_employee_ids'
                    )
                  "
                />
              </v-col>

            </v-row>
          </v-sheet>

          <!-- ================================================= -->
          <!-- SECTION 2 : ارزونه -->
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
                  mdi-clipboard-text-search-outline
                </v-icon>

                <span
                  class="text-subtitle-2 font-weight-medium"
                >
                  {{ t('ارزونه') }}
                </span>
              </div>
            </div>

            <v-divider />

            <v-row class="pa-3">
              <!-- Daily visitors count -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model.number="
                    form.daily_visitors_count
                  "
                  :label="
                    t('تعداد روزانه مراجعین *')
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
                      'daily_visitors_count'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'daily_visitors_count'
                    )
                  "
                />
              </v-col>

              <!-- Risk places -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.risk_places
                  "
                  :label="
                    t('محلات آسیب‌رسان *')
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
                      'risk_places'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'risk_places'
                    )
                  "
                />
              </v-col>

              <!-- Vulnerable places -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.vulnerable_places
                  "
                  :label="
                    t('محلات آسیب‌پذیر *')
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
                      'vulnerable_places'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'vulnerable_places'
                    )
                  "
                />
              </v-col>

              <!-- Security gaps -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.security_gaps
                  "
                  :label="
                    t('خلأهای امنیتی *')
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
                      'security_gaps'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'security_gaps'
                    )
                  "
                />
              </v-col>

              <!-- Weak points -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.weak_points
                  "
                  :label="
                    t('نقاط ضعف *')
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
                      'weak_points'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'weak_points'
                    )
                  "
                />
              </v-col>

              <!-- Security incidents -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.security_incidents
                  "
                  :label="
                    t('حوادث امنیتی رخ‌داده *')
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
                      'security_incidents'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'security_incidents'
                    )
                  "
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- ================================================= -->
          <!-- SECTION 3 : دروازې او امنیتي چک -->
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
                  mdi-door-open
                </v-icon>

                <span
                  class="text-subtitle-2 font-weight-medium"
                >
                  {{ t('دروازې او امنیتي چک') }}
                </span>
              </div>
            </div>

            <v-divider />

            <v-row class="pa-3">
              <!-- Entrance gate -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.entrance_gate
                  "
                  :label="
                    t('دروازه ورودی *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'entrance_gate'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'entrance_gate'
                    )
                  "
                />
              </v-col>

              <!-- Exit gate -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.exit_gate
                  "
                  :label="
                    t('دروازه خروجی *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'exit_gate'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'exit_gate'
                    )
                  "
                />
              </v-col>

              <!-- Emergency gate -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.emergency_gate
                  "
                  :label="
                    t('دروازه اضطراری *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'emergency_gate'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'emergency_gate'
                    )
                  "
                />
              </v-col>

              <!-- Secondary gate -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.secondary_gate
                  "
                  :label="
                    t('دروازه فرعی *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'secondary_gate'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'secondary_gate'
                    )
                  "
                />
              </v-col>

              <!-- Physical check status -->
              <v-col
                cols="12"
                md="4"
              >
                <v-text-field
                  v-model="
                    form.physical_check_status
                  "
                  :label="
                    t('وضعیت بررسی فزیکی *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'physical_check_status'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'physical_check_status'
                    )
                  "
                />
              </v-col>

              <!-- Technical check status -->
              <v-col
                cols="12"
                md="4"
              >
                <v-text-field
                  v-model="
                    form.technical_check_status
                  "
                  :label="
                    t('وضعیت بررسی تخنیکی *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'technical_check_status'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'technical_check_status'
                    )
                  "
                />
              </v-col>

              <!-- Security cameras -->
              <v-col
                cols="12"
                md="4"
              >
                <v-text-field
                  v-model="
                    form.security_cameras
                  "
                  :label="
                    t('کمره‌های امنیتی *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'security_cameras'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'security_cameras'
                    )
                  "
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- ================================================= -->
          <!-- SECTION 4 : امنیتي مسؤولین او نور معلومات -->
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
                  {{ t('امنیتي مسؤولین او نور معلومات') }}
                </span>
              </div>
            </div>

            <v-divider />

            <v-row class="pa-3">
              <!-- Guards count -->
              <v-col
                cols="12"
                md="4"
              >
                <v-text-field
                  v-model.number="
                    form.guards_count
                  "
                  :label="
                    t('تعداد محافظین *')
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
                      'guards_count'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'guards_count'
                    )
                  "
                />
              </v-col>

              <!-- Active towers count -->
              <v-col
                cols="12"
                md="4"
              >
                <v-text-field
                  v-model.number="
                    form.active_towers_count
                  "
                  :label="
                    t('تعداد برج‌های فعال *')
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
                      'active_towers_count'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'active_towers_count'
                    )
                  "
                />
              </v-col>

              <!-- Active guard posts count -->
              <v-col
                cols="12"
                md="4"
              >
                <v-text-field
                  v-model.number="
                    form.active_guard_posts_count
                  "
                  :label="
                    t('تعداد قرارگاه‌های فعال *')
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
                      'active_guard_posts_count'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'active_guard_posts_count'
                    )
                  "
                />
              </v-col>

              <!-- Security responsible name -->
              <v-col
                cols="12"
                md="6"
              >
                <v-text-field
                  v-model="
                    form.security_responsible_name
                  "
                  :label="
                    t('نام مسؤول امنیتی *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                  ]"
                  :error="
                    hasError(
                      'security_responsible_name'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'security_responsible_name'
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
                    form.security_responsible_phone
                  "
                  :label="
                    t('شماره مسؤول امنیتی *')
                  "
                  density="compact"
                  variant="outlined"
                  :rules="[
                    rules.required,
                    rules.phone,
                  ]"
                  :error="
                    hasError(
                      'security_responsible_phone'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'security_responsible_phone'
                    )
                  "
                />
              </v-col>

              <!-- Other problems -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.other_problems
                  "
                  :label="
                    t('سایر مشکلات *')
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
                      'other_problems'
                    )
                  "
                  :error-messages="
                    getErrorMessages(
                      'other_problems'
                    )
                  "
                />
              </v-col>

              <!-- Note -->
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="
                    form.note
                  "
                  :label="
                    t('ملاحظات *')
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
          </v-sheet>

          <!-- ================= ACTIONS ================= -->

          <div
            class="form-actions d-flex justify-end ga-2 flex-wrap"
          >
            <v-btn
              color="primary"
              variant="outlined"
              class="text-none"
              prepend-icon="mdi-printer"
              @click="printForm"
            >
              {{ t('چاپ') }}
            </v-btn>

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
                      'ثبت ارزیابی'
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

import ProvinceDistrictNameSelect
  from '@/components/ProvinceDistrictSelect.vue';

import {
  useDatabaseRepository,
} from '@/stores/DatabaseRepository';
import { printBlankForm } from '@/utils/printBlankForm';

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
    locale.value === 'fa' ||
      locale.value === 'ps'
  );

watch(
  locale,
  (value) => {
    isRtl.value =
      value === 'fa' ||
      value === 'ps';
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

/* ✅ ترسره کوونکي dropdown options */
const employeeOptions =
  ref([]);

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

/* PRINT BLANK FORM (empty fields, same design, one A4 page) */
function printForm(e) {
  printBlankForm(e, { onError: showErrorMessage });
}

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

    place_name: '',

    responsible_owner_name: '',

    province: '',

    district: '',

    specific_place: '',

    coordinates: '',

    north: '',

    south: '',

    east: '',

    west: '',

    daily_visitors_count:
      '',

    risk_places: '',

    vulnerable_places: '',

    security_gaps: '',

    weak_points: '',

    security_incidents: '',

    entrance_gate: '',

    exit_gate: '',

    emergency_gate: '',

    secondary_gate: '',

    physical_check_status: '',

    technical_check_status: '',

    security_cameras: '',

    guards_count:
      '',

    active_towers_count:
      '',

    active_guard_posts_count:
      '',

    security_responsible_name:
      '',

    security_responsible_phone:
      '',

    other_problems: '',

    note: '',

    // ✅ ترسره کوونکي
    office_employee_ids: [],
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

  mapped.place_name =
    item.place_name ??
    '';

  mapped.responsible_owner_name =
    item.responsible_owner_name ??
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

  mapped.north =
    item.north ??
    '';

  mapped.south =
    item.south ??
    '';

  mapped.east =
    item.east ??
    '';

  mapped.west =
    item.west ??
    '';

  mapped.daily_visitors_count =
    item.daily_visitors_count ??
    '';

  mapped.risk_places =
    item.risk_places ??
    '';

  mapped.vulnerable_places =
    item.vulnerable_places ??
    '';

  mapped.security_gaps =
    item.security_gaps ??
    '';

  mapped.weak_points =
    item.weak_points ??
    '';

  mapped.security_incidents =
    item.security_incidents ??
    '';

  mapped.entrance_gate =
    item.entrance_gate ??
    '';

  mapped.exit_gate =
    item.exit_gate ??
    '';

  mapped.emergency_gate =
    item.emergency_gate ??
    '';

  mapped.secondary_gate =
    item.secondary_gate ??
    '';

  mapped.physical_check_status =
    item.physical_check_status ??
    '';

  mapped.technical_check_status =
    item.technical_check_status ??
    '';

  mapped.security_cameras =
    item.security_cameras ??
    '';

  mapped.guards_count =
    item.guards_count ??
    '';

  mapped.active_towers_count =
    item.active_towers_count ??
    '';

  mapped.active_guard_posts_count =
    item.active_guard_posts_count ??
    '';

  mapped.security_responsible_name =
    item.security_responsible_name ??
    '';

  mapped.security_responsible_phone =
    item.security_responsible_phone ??
    '';

  mapped.other_problems =
    item.other_problems ??
    '';

  mapped.note =
    item.note ??
    '';

  mapped.office_employee_ids =
    Array.isArray(
      item.office_employee_ids
    )
      ? item.office_employee_ids.map(
          Number
        )
      : [];

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
      .FetchSecurityAssessmentData(
        idParam.value
      );

    form.value =
      mapFromApi(
        repo.SecurityAssessment ||
          {}
      );
  } catch (error) {
    console.error(
      error
    );

    showErrorMessage(
      t(
        'د امنیتي ارزونې د معلوماتو په ترلاسه کولو کې ستونزه رامنځته شوه'
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

      if (Array.isArray(value)) {
        value.forEach((entry) => {
          if (entry === undefined || entry === null) {
            return;
          }

          formData.append(
            `${key}[]`,
            String(entry)
          );
        });

        return;
      }

      appendValue(
        formData,
        key,
        value
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
    'place_name',
    'responsible_owner_name',
    'province',
    'district',
    'specific_place',
    'coordinates',
    'daily_visitors_count',
    'risk_places',
    'vulnerable_places',
    'security_gaps',
    'weak_points',
    'security_incidents',
    'entrance_gate',
    'exit_gate',
    'emergency_gate',
    'secondary_gate',
    'physical_check_status',
    'technical_check_status',
    'security_cameras',
    'guards_count',
    'active_towers_count',
    'active_guard_posts_count',
    'security_responsible_name',
    'security_responsible_phone',
    'other_problems',
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

  saving.value =
    true;

  try {
    /* CREATE */
    if (
      !isEdit.value
    ) {
      const created =
        await repo
          .CreateSecurityAssessment(
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
            'امنیتي ارزونه په بریالیتوب سره ثبت شوه'
          )
        );

        router
          .push(
            '/app/security-assessments'
          )
          .catch(
            () => {}
          );
      } else {
        showErrorMessage(
          t(
            'د معلوماتو په ثبتولو کې ستونزه رامنځته شوه'
          )
        );
      }

      return;
    }

    /* UPDATE */

    const updated =
      await repo
        .UpdateSecurityAssessment(
          form.value.id,
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
      updated?.id
    ) {
      showSuccessMessage(
        t(
          'بدلونونه په بریالیتوب سره خوندي شول'
        )
      );

      router
        .push(
          '/app/security-assessments'
        )
        .catch(
          () => {}
        );
    } else {
      showErrorMessage(
        t(
          'د معلوماتو په ثبتولو کې ستونزه رامنځته شوه'
        )
      );
    }
  } catch (error) {
    console.error(
      'Security assessment save error:',
      error
    );

    showErrorMessage(
      t(
        'د معلوماتو په ثبتولو کې ستونزه رامنځته شوه'
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
          .place_name ||
          ''
      ).trim() &&
      !!String(
        form.value
          .responsible_owner_name ||
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
      ).trim() &&
      !!String(
        form.value
          .coordinates ||
          ''
      ).trim()
  );

const step2Complete =
  computed(
    () =>
      String(
        form.value
          .daily_visitors_count ??
          ''
      ).trim() !==
        '' &&
      !!String(
        form.value
          .risk_places ||
          ''
      ).trim() &&
      !!String(
        form.value
          .vulnerable_places ||
          ''
      ).trim() &&
      !!String(
        form.value
          .security_gaps ||
          ''
      ).trim() &&
      !!String(
        form.value
          .weak_points ||
          ''
      ).trim() &&
      !!String(
        form.value
          .security_incidents ||
          ''
      ).trim()
  );

const step3Complete =
  computed(
    () =>
      !!String(
        form.value
          .entrance_gate ||
          ''
      ).trim() &&
      !!String(
        form.value
          .exit_gate ||
          ''
      ).trim() &&
      !!String(
        form.value
          .emergency_gate ||
          ''
      ).trim() &&
      !!String(
        form.value
          .secondary_gate ||
          ''
      ).trim() &&
      !!String(
        form.value
          .physical_check_status ||
          ''
      ).trim() &&
      !!String(
        form.value
          .technical_check_status ||
          ''
      ).trim() &&
      !!String(
        form.value
          .security_cameras ||
          ''
      ).trim()
  );

const step4Complete =
  computed(
    () =>
      String(
        form.value
          .guards_count ??
          ''
      ).trim() !==
        '' &&
      String(
        form.value
          .active_towers_count ??
          ''
      ).trim() !==
        '' &&
      String(
        form.value
          .active_guard_posts_count ??
          ''
      ).trim() !==
        '' &&
      !!String(
        form.value
          .security_responsible_name ||
          ''
      ).trim() &&
      !!String(
        form.value
          .security_responsible_phone ||
          ''
      ).trim() &&
      !!String(
        form.value
          .other_problems ||
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
            'مشخصات و موقعیت'
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
            'ارزونه'
          ),
        complete:
          step2Complete
            .value,
        icon:
          'mdi-clipboard-text-search-outline',
      },

      {
        number: 3,
        title:
          t(
            'دروازې او امنیتي چک'
          ),
        complete:
          step3Complete
            .value,
        icon:
          'mdi-door-open',
      },

      {
        number: 4,
        title:
          t(
            'امنیتي مسؤولین'
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
    employeeOptions.value =
      await repo.FetchOfficeEmployeeOptions();

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

.stepper-dots {
  position:
    absolute;

  top:
    50%;

  left:
    0;

  right:
    0;

  transform:
    translateY(-50%);

  display:
    flex;

  justify-content:
    space-between;

  align-items:
    center;
}

.stepper-dot-group {
  display:
    flex;

  align-items:
    center;

  gap:
    6px;
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
      33,
      150,
      243,
      0.25
    );

  color:
    rgba(
      33,
      150,
      243,
      0.6
    );

  transition:
    all 0.2s ease;
}

.stepper-dot.is-active {
  border-color:
    rgb(
      var(
        --v-theme-primary
      )
    );

  color:
    rgb(
      var(
        --v-theme-primary
      )
    );

  box-shadow:
    0 0 0 4px
    rgba(
      33,
      150,
      243,
      0.12
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
  color:
    rgba(
      0,
      0,
      0,
      0.55
    );

  white-space:
    nowrap;
}

.stepper-label.is-complete-text {
  color:
    rgb(
      var(
        --v-theme-primary
      )
    );

  font-weight:
    500;
}

/* =========================================================
   SECTIONS
========================================================= */

.section-card {
  border:
    1px solid
    rgba(
      33,
      150,
      243,
      0.12
    );

  background:
    #fff;

  overflow:
    hidden;
}

.section-header {
  padding:
    12px 16px;

  background:
    rgba(
      33,
      150,
      243,
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

.form-card {
  border-radius:
    16px;

  margin-top:
    90px;
}

.form-shell {
  max-width:
    1000px;
}

.field-error {
  color:
    #b3261e;

  font-size:
    0.8rem;

  margin-top:
    4px;
}

.form-actions {
  margin-top:
    8px;
}
</style>
