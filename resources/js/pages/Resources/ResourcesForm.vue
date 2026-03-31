<template>
  <v-container fluid class="page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح معلومات منبع') : t('اضافه کردن منبع')"
      :subtitle="isEdit ? t('ویرایش جزئیات یک منبع موجود') : t('ثبت یک منبع جدید در سیستم')"
    >
    </AppBar>

    <!-- STEPPER (sticky when scrolling) -->
    <section class="stepper-shell" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card class="stepper-card py-4 pb-12" elevation="0">
        <div class="d-flex justify-space-between align-center mb-1">
          <span class="text-caption text-medium-emphasis stepper-caption">
            {{ t('مراحل خانه پری فرم منبع') }}
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
            <!-- 1: هویت و تماس -->
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
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step1Complete }"
              >
                {{ t('هویت و تماس') }}
              </span>
            </div>

            <!-- 2: آدرس‌ها -->
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
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step2Complete }"
              >
                {{ t('آدرس‌ها') }}
              </span>
            </div>

            <!-- 3: وظیفه و نوع فعالیت -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step3Complete,
                  'is-active': currentStep === 3 && !step3Complete,
                }"
              >
                <v-icon size="16">
                  {{ step3Complete ? 'mdi-check' : 'mdi-briefcase' }}
                </v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step3Complete }"
              >
                {{ t('وظیفه و نوع فعالیت') }}
              </span>
            </div>

            <!-- 4: تاریخ و نوت -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step4Complete,
                  'is-active': currentStep === 4 && !step4Complete,
                }"
              >
                <v-icon size="16">
                  {{ step4Complete ? 'mdi-check' : 'mdi-calendar' }}
                </v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step4Complete }"
              >
                {{ t('تاریخ و نوت') }}
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
          <!-- HEADER -->
          <div class="d-flex justify-space-between align-center mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-avatar size="40" class="mr-2 header-avatar" color="primary">
                <v-icon size="22" color="white">mdi-account-badge</v-icon>
              </v-avatar>
              <div>
                <div class="text-subtitle-1 font-weight-medium header-title">
                  {{ isEdit ? t('فرم اصلاح معلومات منبع') : t('فرم ثبت منبع جدید') }}
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

          <!-- SECTION 1: هویت + تصویر + تماس -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-account-badge</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('معرفت و معلومات تماس منبع') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('نام، نام پدر، کود نمبر، سن، شماره تماس و تصویر منبع') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <!-- Picture (always show avatar) -->
              <v-col cols="12" md="3">
                <div class="d-flex flex-column align-center gap-2">
                  <div class="pic-wrap">
                    <input
                      ref="pictureInput"
                      class="hidden-file"
                      type="file"
                      accept="image/*"
                      @change="onPickPicture"
                    />
                    <div class="pic-thumb" :title="t('گزینه ها')" @click="triggerPicture">
                      <v-avatar
                        size="64"
                        :key="form.previewUrl || form.picture_url || form.picture || 'no-picture'"
                      >
                        <v-img
                          v-if="form.previewUrl || form.picture_url || form.picture"
                          :src="
                            normalizePicture(form.previewUrl || form.picture_url || form.picture)
                          "
                          cover
                        />
                        <v-icon v-else size="36" color="grey"> mdi-account </v-icon>
                      </v-avatar>
                      <div class="pic-overlay" @click.stop>
                        <v-btn
                          size="small"
                          color="secondary"
                          variant="flat"
                          @click.stop="triggerPicture"
                        >
                          <v-icon start>mdi-pencil</v-icon>{{ t('ویرایش') }}
                        </v-btn>
                        <v-btn
                          size="small"
                          color="secondary"
                          variant="flat"
                          v-if="form.previewUrl || form.picture_url || form.picture"
                          @click.stop="
                            openPreview(form.previewUrl || form.picture_url || form.picture)
                          "
                        >
                          <v-icon start>mdi-eye</v-icon>{{ t('نمایش') }}
                        </v-btn>
                      </div>
                    </div>
                  </div>
                </div>
              </v-col>

              <!-- Identity + contact -->
              <v-col cols="12" md="9">
                <v-row>
                  <v-col cols="12" md="4">
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

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.father_name"
                      :label="t('نام پدر')"
                      density="compact"
                      :rules="[rules.required]"
                      variant="outlined"
                      :error="hasError('father_name')"
                      :error-messages="getErrorMessages('father_name')"
                    />
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.grand_father_name"
                      :label="t('نام پدرکلان')"
                      :rules="[rules.required]"
                      density="compact"
                      variant="outlined"
                      :error="hasError('grand_father_name')"
                      :error-messages="getErrorMessages('grand_father_name')"
                    />
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.nick_name"
                      :label="t('نام مستعار')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('nick_name')"
                      :error-messages="getErrorMessages('nick_name')"
                    />
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.code_number"
                      :label="t('کود نمبر')"
                      density="compact"
                      variant="outlined"
                      readonly
                      :placeholder="!isEdit ? t('بعد از ثبت، سیستم خودکار میسازد') : ''"
                    />
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.tazkira"
                      :label="t('تذکره نمبر')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('tazkira')"
                      :error-messages="getErrorMessages('tazkira')"
                    />
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 2: آدرس‌ها -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-home-city</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('آدرس اصلی و فعلی منبع') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('آدرس اصلی (زادگاه) و آدرس فعلی منبع را وارد کنید.') }}
              </span>
            </div>
            <v-divider />

            <!-- Main address -->
            <v-row class="pa-3">
              <v-col cols="12" md="12">
                <ProvinceDistrictNameSelect
                  v-model:province="form.main_province"
                  v-model:district="form.main_district"
                  :province-label="t('ولایت اصلی *')"
                  :district-label="t('ولسوالی اصلی *')"
                  :rules="[rules.required]"
                  :mdProvince="6"
                  :mdDistrict="6"
                />
              </v-col>

              <v-col cols="12" md="12">
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

            <!-- Current address -->
            <v-row class="px-3 pb-3 pt-0">
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

              <v-col cols="12" md="12">
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

            <v-row class="px-3 pb-3 pt-0">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.now_specific_place"
                  :label="t('محل مشخص فعلی')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('now_specific_place')"
                  :error-messages="getErrorMessages('now_specific_place')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.now_street_number"
                  :label="t('نمبر سرک')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('now_street_number')"
                  :error-messages="getErrorMessages('now_street_number')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.now_home_number"
                  :label="t('نمبر خانه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('now_home_number')"
                  :error-messages="getErrorMessages('now_home_number')"
                />
              </v-col>
            </v-row>

            <v-row class="px-3 pb-3 pt-0">
              <!-- <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.Province_addr"
                  :label="t('ولایت مربوطه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('Province_addr')"
                  :error-messages="getErrorMessages('Province_addr')"
                />
              </v-col> -->
            </v-row>
          </v-sheet>

          <!-- SECTION 3: وظایف، وضعیت، نوع فعالیت -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-briefcase</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('وظایف، وضعیت و نوع فعالیت منبع') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('وظایف، فعالیت، حالت مدنی، نوع فعالیت منبع و کارمند مربوطه را درج کنید.') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.old_job"
                  :label="t('وظیفه سابقه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('old_job')"
                  :error-messages="getErrorMessages('old_job')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.now_job"
                  :label="t('وظیفه فعلی')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('now_job')"
                  :error-messages="getErrorMessages('now_job')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.job_place"
                  :label="t('محل وظیفه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('job_place')"
                  :error-messages="getErrorMessages('job_place')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <ZoneHawzaPicker
                  v-model:zone="form.zone"
                  v-model:hawza="form.hawza"
                  :disabled="false"
                  :colsZone="12"
                  :mdZone="6"
                  :colsHawza="12"
                  :mdHawza="6"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.specefic_place"
                  :label="t('محل مشخص تدارک')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.coordinates"
                  :label="t('کوردینات تدارک')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('coordinates')"
                  :error-messages="getErrorMessages('coordinates')"
                />
              </v-col>
              <!-- <v-col cols="12" md="3">
                <v-autocomplete
                  v-model="form.activity"
                  :items="repo.activityOptions || []"
                  :label="t('فعالیت منبع')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                  :error="hasError('activity')"
                  :error-messages="getErrorMessages('activity')"
                />
              </v-col> -->

              <v-col cols="12" md="3">
                <v-autocomplete
                  v-model="form.halat_madani"
                  :items="repo.halatMadaniOptions || []"
                  :label="t('حالت مدنی')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                  :error="hasError('halat_madani')"
                  :error-messages="getErrorMessages('halat_madani')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.age"
                  :label="t('عمر')"
                  type="number"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.ageRange]"
                  :error="hasError('age')"
                  :error-messages="getErrorMessages('age')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.elmi_level"
                  :label="t(' سطح علمی')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.faculty"
                  :label="t('دیپارتمنت/ فاکولته')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.specific_skills"
                  :label="t('مهارت‌های خاص')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="3">
                <DateInput
                  v-model="form.resource_saved_date"
                  :label="t('تاریخ ثبت منبع *')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <ResourceActivityTypeSelect
                  v-model="form.resource_activity_type"
                  :label="t('نوع فعالیت منبع')"
                  :error="hasError('resource_activity_type')"
                  :error-messages="getErrorMessages('resource_activity_type')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-autocomplete
                  v-model="form.office_employee_id"
                  :items="repo.officeemployees || []"
                  item-title="code_number"
                  item-value="id"
                  :label="t('کارمند مربوطه')"
                  :rules="[rules.required]"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                  :error="hasError('office_employee_id')"
                  :error-messages="getErrorMessages('office_employee_id')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.resedint_code"
                  :label="t('رزیدنت مربوطه')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('resedint_code')"
                  :error-messages="getErrorMessages('resedint_code')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.resource_cover"
                  :label="t('پوشش منبع')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('resource_cover')"
                  :error-messages="getErrorMessages('resource_cover')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.language"
                  :label="t('زبان')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.nation"
                  :label="t('قوم/ملیت')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="form.blood_group"
                  :items="bloodGroups"
                  :label="t('گروپ خون')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :error="hasError('blood_group')"
                  :error-messages="getErrorMessages('blood_group')"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.train"
                  :label="t('دوره آموزش ')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>
            </v-row>
          </v-sheet>
          <!-- SECTION 4: تاریخ و نوت -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.phone_number"
                  :label="t('شماره تماس')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.phone10]"
                  :error="hasError('phone_number')"
                  :error-messages="getErrorMessages('phone_number')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.phone_number_1"
                  :label="t('شماره تماس ۱')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.phone10]"
                  :error="hasError('phone_number_1')"
                  :error-messages="getErrorMessages('phone_number_1')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.phone_number_2"
                  :label="t('شماره تماس ۲')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.phone10]"
                  :error="hasError('phone_number_2')"
                  :error-messages="getErrorMessages('phone_number_2')"
                />
              </v-col>
              <v-col cols="12" md="3">
                <LetryFormatSelect v-model="form.letry_format_name" :label="t('۵۶ فارمټ')" />
              </v-col>

              <v-col cols="12" md="12">
                <v-text-field
                  v-model="form.project"
                  :label="t('پروجکت مربوطه')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>
            </v-row>
          </v-sheet>
          <!-- SECTION 4: تاریخ و نوت -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header docs-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-calendar</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('ملاحظات:') }}
                </span>
              </div>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="12">
                <v-text-field
                  v-model="form.note"
                  :label="t('نوت')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('note')"
                  :error-messages="getErrorMessages('note')"
                />
              </v-col>
            </v-row>
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
              {{ isEdit ? t('ذخیرهٔ تغییرات') : t('ثبت منبع') }}
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
  import LetryFormatSelect from '@/components/LetryFormatSelect.vue';
  import AppBar from '../../components/AppBar.vue';
  import DateInput from '../../components/DateInput.vue';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';
  import ProvinceDistrictNameSelect from '../../components/ProvinceDistrictSelect.vue';
  import ResourceActivityTypeSelect from '../../components/ResourceActivityTypeSelect.vue';
  import ZoneHawzaPicker from '@/components/ZoneHawzaSelect.vue';
  import { useLookupsRepository } from '../../stores/LookupsRepository';
  const lookups = useLookupsRepository();

  const repo = useDatabaseRepository();
  const auth = useLoginRepository();
  const route = useRoute();
  const router = useRouter();
  const { t, locale } = useI18n();
  const bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

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

  const previewOpen = ref(false);
  const previewSrc = ref(null);
  const pictureInput = ref(null);
  const pictureFile = ref(null); // new image file
  const pictureChanged = ref(false); // track if user changed picture

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
    // media
    picture: null,
    picture_url: null,
    previewUrl: null,

    // identity
    name: '',
    father_name: '',
    grand_father_name: '',
    nick_name: '',
    code_number: '',
    tazkira: '',
    age: '',
    phone_number: '',
    blood_group: '',

    phone_number_1: '',
    phone_number_2: '',
    language: '',
    nation: '',
    specific_skills: '',
    letry_format_name: '',

    // addresses
    main_province: '',
    main_district: '',
    main_village: '',
    now_province: '',
    now_district: '',
    now_village: '',
    now_specific_place: '',
    now_street_number: '',
    now_home_number: '',
    // Province_addr: '',
    zone: '',
    hawza: '',
    specefic_place: '',
    coordinates: '',
    tadarok_place: '',
    train: '',
    elmi_level: '',
    faculty: '',

    // jobs & status
    old_job: '',
    now_job: '',
    job_place: '',
    activity: 'فعال',
    halat_madani: '',
    resource_activity_type: '',
    resource_cover: '',
    office_employee_id: null,
    resedint_code: '',

    // date & note
    resource_saved_date: '',
    note: '',
  });

  const form = ref(blankForm());

  /* ===== RULES ===== */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
    phone10: (v) => !v || /^[0-9]{10}$/.test(String(v)) || t('شماره تماس باید دقیقاً ۱۰ رقم باشد'),
    ageRange: (v) => {
      if (v === '' || v === null || v === undefined) return true;
      const n = Number(v);
      if (Number.isNaN(n)) return t('عمر باید عدد باشد');
      if (n < 0 || n > 200) return t('عمر باید بین ۰ تا ۲۰۰ باشد');
      return true;
    },
  };

  /* ===== PICTURE ===== */
  function triggerPicture() {
    pictureInput.value?.click();
  }
  function onPickPicture(e) {
    const file = e.target.files?.[0] ?? null;
    pictureFile.value = file;
    pictureChanged.value = !!file;

    if (file) {
      if (form.value.previewUrl && form.value.previewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(form.value.previewUrl);
      }
      form.value.previewUrl = URL.createObjectURL(file);
    } else {
      form.value.previewUrl = form.value.picture_url || form.value.picture || null;
    }
    if (e.target) e.target.value = '';
  }
  function normalizePicture(src) {
    if (!src) return null;
    let clean = String(src).trim();

    if (clean.startsWith('blob:') || clean.startsWith('data:')) {
      return clean;
    }

    clean = clean.replace(/\\\//g, '/');

    if (clean.startsWith('http://') || clean.startsWith('https://')) {
      return clean;
    }

    if (clean.startsWith('/')) {
      return clean;
    }

    return '/' + clean.replace(/^\/+/, '');
  }
  function openPreview(src) {
    const base = src || form.value.previewUrl || form.value.picture_url || form.value.picture;
    const normalized = normalizePicture(base);
    previewSrc.value = normalized;
    if (previewSrc.value) previewOpen.value = true;
  }

  /* ===== MAP FROM API ===== */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

    const fullUrl = x.picture ?? null;
    b.picture = x.picture ?? null;
    b.picture_url = fullUrl;
    b.previewUrl = fullUrl;

    // identity
    b.name = x.name ?? '';
    b.father_name = x.father_name ?? '';
    b.grand_father_name = x.grand_father_name ?? '';
    b.nick_name = x.nick_name ?? '';
    b.code_number = x.code_number ?? '';
    b.tazkira = x.tazkira ?? '';
    b.age = x.age ?? '';
    b.phone_number = x.phone_number ?? '';
    b.phone_number_1 = x.phone_number_1 ?? '';
    b.phone_number_2 = x.phone_number_2 ?? '';

    b.language = x.language ?? '';
    b.nation = x.nation ?? '';
    b.specific_skills = x.specific_skills ?? '';
    b.letry_format_name = x.letry_format_name ?? '';

    // addresses
    b.main_province = x.main_province ?? '';
    b.main_district = x.main_district ?? '';
    b.main_village = x.main_village ?? '';
    b.now_province = x.now_province ?? '';
    b.now_district = x.now_district ?? '';
    b.now_village = x.now_village ?? '';
    b.now_specific_place = x.now_specific_place ?? '';
    b.now_street_number = x.now_street_number ?? '';
    b.now_home_number = x.now_home_number ?? '';
    // b.Province_addr = x.Province_addr ?? '';
    b.zone = x.zone ?? '';
    b.hawza = x.hawza ?? '';
    b.specefic_place = x.specefic_place ?? '';
    b.coordinates = x.coordinates ?? '';
    b.tadarok_place = x.tadarok_place ?? '';
    b.elmi_level = x.elmi_level ?? '';
    b.faculty = x.faculty ?? '';
    b.train = x.train ?? '';
    b.blood_group = x.blood_group ?? '';

    // jobs & status
    b.old_job = x.old_job ?? '';
    b.now_job = x.now_job ?? '';
    b.job_place = x.job_place ?? '';
    b.activity = x.activity ?? '';
    b.halat_madani = x.halat_madani ?? '';
    b.resource_activity_type = x.resource_activity_type ?? '';
    b.resource_cover = x.resource_cover ?? '';
    b.office_employee_id = x.office_employee_id ?? null;
    b.resedint_code = x.resedint_code ?? '';

    // date & note
    b.resource_saved_date = x.resource_saved_date ?? '';
    b.note = x.note ?? '';

    return b;
  }

  /* ===== FORM DATA HELPERS ===== */
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
    delete payload.picture_url;

    // ✅ code_number is backend-controlled (id)
    delete payload.code_number;

    const fd = toFormData(payload);

    if (pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };

    delete payload.previewUrl;
    delete payload.picture_url;

    // ✅ code_number is frozen (id)
    delete payload.code_number;

    // we handle picture separately
    const picturePath = payload.picture;
    delete payload.picture;

    const fd = toFormData(payload);

    if (pictureChanged.value && pictureFile.value) {
      fd.append('picture', pictureFile.value);
      if (picturePath && typeof picturePath === 'string') {
        fd.append('old_picture', picturePath);
      }
    }

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
        const created = await repo.CreateResource(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('منبع موفقانه ثبت شد'));
          router.push({ path: '/app/resources' }).catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateResource(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push({ path: '/app/resources' }).catch(() => {});
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
    if (form.value.previewUrl && form.value.previewUrl.startsWith('blob:')) {
      URL.revokeObjectURL(form.value.previewUrl);
    }
    form.value = blankForm();
    pictureFile.value = null;
    pictureChanged.value = false;
    formRef.value?.resetValidation?.();
    clearBackendErrors();
    showSuccessMessage(t('ورودی‌ها پاک شد'));
  }
  function goBack() {
    router.back();
  }

  /* ===== LOAD FOR EDIT ===== */
  async function loadForEdit() {
    if (!isEdit.value) {
      form.value = blankForm();
      pictureFile.value = null;
      pictureChanged.value = false;
      return;
    }

    try {
      if (typeof repo.FetchResourceData === 'function') {
        await repo.FetchResourceData(idParam.value);
        const raw = Array.isArray(repo.Resource) ? repo.Resource[0] : repo.Resource || {};
        const mapped = mapFromApi(raw);
        form.value = mapped;
        pictureFile.value = null;
        pictureChanged.value = false;
      }
    } catch (e) {
      console.error('loadForEdit error', e);
      showErrorMessage(t('خطا در دریافت معلومات منبع'));
    }
  }

  /* ===== INIT ===== */
  onMounted(async () => {
    await Promise.allSettled([
      lookups.GetProvinces(),
      lookups.GetDistricts(),
      lookups.GetResourceActivityTypes(),
      repo.GetOfficeEmployee?.(),
    ]);

    await loadForEdit();
  });

  /* ===== STEPPER LOGIC ===== */
  const step1Complete = computed(() => !!form.value.name?.trim());
  const step2Complete = computed(
    () =>
      !!form.value.main_province?.trim() &&
      !!form.value.main_district?.trim() &&
      !!form.value.main_village?.trim()
  );
  const step3Complete = computed(
    () =>
      !!form.value.activity?.trim() ||
      !!form.value.resource_activity_type?.trim() ||
      !!form.value.old_job?.trim() ||
      !!form.value.now_job?.trim()
  );
  const step4Complete = computed(
    () => !!form.value.resource_saved_date?.trim() || !!form.value.note?.trim()
  );

  const completedSteps = computed(
    () =>
      [step1Complete.value, step2Complete.value, step3Complete.value, step4Complete.value].filter(
        Boolean
      ).length
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

  .stepper-shell {
    position: absolute;
    top: 80px;
    left: 50%;
    transform: translateX(-50%);
    max-width: 90%;
    width: 100%;
    z-index: 20;
    margin: 0;
    padding-bottom: 12px;
  }

  /* push the form card down so it starts under the stepper */
  .form-shell {
    max-width: 1150px;
    margin-top: 140px;
    margin-bottom: 40px;
  }

  @media (max-width: 960px) {
    .stepper-shell {
      top: 64px;
    }
  }

  .stepper-card {
    border-radius: 14px;
    padding: 10px 14px 12px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(33, 150, 243, 0.12);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
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

  /* Card styling */
  .form-card {
    border-radius: 18px;
    border: 1px solid rgba(33, 150, 243, 0.08);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    font-family: inherit;
  }

  /* Top avatar */
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

  /* picture hover tools */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }

  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }

  .pic-thumb {
    display: inline-flex;
    cursor: pointer;
    position: relative;
  }

  .pic-overlay {
    position: absolute;
    inset: -6px -16px auto auto;
    display: none;
    gap: 2px;
    background: rgba(0, 0, 0, 0.55);
    padding: 6px;
    border-radius: 8px;
    transform: translateY(-20%);
    white-space: nowrap;
  }

  .pic-thumb:hover .pic-overlay {
    display: flex;
    flex-direction: column-reverse;
    gap: 8px;
  }

  .pic-overlay :deep(.v-btn) {
    color: #fff;
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
