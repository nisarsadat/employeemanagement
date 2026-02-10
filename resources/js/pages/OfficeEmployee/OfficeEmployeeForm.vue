<template>
  <v-container fluid class="page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح معلومات کارمند') : t('اضافه کردن کارمند مدیریت')"
      :subtitle="isEdit ? t('ویرایش جزئیات یک کارمند موجود') : t('ثبت یک کارمند جدید در سیستم')"
    >
    </AppBar>

    <!-- STEPPER (sticky when scrolling) -->
    <section class="stepper-shell" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card class="stepper-card py-4 pb-12" elevation="0">
        <div class="d-flex justify-space-between align-center mb-1">
          <span class="text-caption text-medium-emphasis stepper-caption">
            {{ t('مراحل خانه پری فرم کارمند') }}
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
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step1Complete }"
              >
                {{ t('هویت') }}
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

            <!-- 3: وظیفه / تعلیمات -->
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
                {{ t('وظیفه و تعلیمات') }}
              </span>
            </div>

            <!-- 4: اسناد / سایر -->
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
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step4Complete }"
              >
                {{ t('اسناد و سایر') }}
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
                  {{ isEdit ? t('فرم اصلاح معلومات کارمند') : t('فرم ثبت کارمند جدید') }}
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
                  {{ t('معرفت و معلومات تماس') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('نام، نام پدر، کود نمبر، سن، شماره تماس و تصویر کارمند') }}
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

              <!-- Identity -->
              <v-col cols="12" md="9">
                <v-row>
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
                      v-model="form.code_number"
                      :label="t('کود نمبر')"
                      density="compact"
                      variant="outlined"
                      readonly
                      disabled
                      hint="Auto"
                      persistent-hint
                    />
                  </v-col>

                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.tazkira"
                      :label="t('شماره تذکره')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('tazkira')"
                      :error-messages="getErrorMessages('tazkira')"
                    />
                  </v-col>

                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.age"
                      :label="t('سن')"
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
                    <v-text-field
                      v-model="form.specific_skills"
                      :label="t('مهارت‌های خاص')"
                      density="compact"
                      variant="outlined"
                    />
                  </v-col>
                  <v-col cols="12" md="12">
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
                  {{ t('آدرس اصلی و فعلی') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('آدرس اصلی (زادگاه) و آدرس فعلی کارمند را وارد کنید.') }}
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

              <v-col cols="12" md="12">
                <v-text-field
                  v-model="form.coordinates"
                  :label="t('مختصات')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('coordinates')"
                  :error-messages="getErrorMessages('coordinates')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 3: وظیفه، زون/حوزه، تعلیمات، جهاد -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-briefcase</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('وظیفه، زون/حوزه و تعلیمات') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('وظیفه مشخص، بست، زون، حوزه و تحصیلات کارمند را درج کنید.') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.makhafat"
                  :label="t('مخافات')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.makafat"
                  :label="t('مکافات')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.bast"
                  :label="t('بست')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('bast')"
                  :error-messages="getErrorMessages('bast')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.level"
                  :label="t('رتبه/سطح')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('level')"
                  :error-messages="getErrorMessages('level')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.specific_job"
                  :label="t('وظیفه مشخص')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('specific_job')"
                  :error-messages="getErrorMessages('specific_job')"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.qisem"
                  :label="t('قسم')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('qisem')"
                  :error-messages="getErrorMessages('qisem')"
                />
              </v-col>
              <v-col cols="12">
                <ZoneHawzaNameSelect
                  v-model:zone="form.zone"
                  v-model:hawza="form.hawza"
                  :zone-label="t('زون')"
                  :hawza-label="t('حوزه')"
                  :mdZone="6"
                  :mdHawza="6"
                />
              </v-col>

              <v-col cols="12" md="12">
                <v-select
                  v-model="form.madani_halat"
                  :items="repo.getHalatMadaniOptions()"
                  :label="t('حالت مدنی')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>

              <!-- <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.activity"
                  :label="t('فعالیت')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col> -->
            </v-row>
            <v-col cols="12" md="12">
              <JobTypeSelect v-model="form.job_type" :label="t('نوعیت وظیفه')" />
            </v-col>
            <v-row class="px-3 pb-3 pt-0">
              <v-col cols="6" md="6">
                <v-text-field
                  v-model="form.relegy_study"
                  :label="t('تعلیمات دینی')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.new_study"
                  :label="t('تعلیمات عصری')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.faculty"
                  :label="t('پوهنحی/رشته')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.nizami"
                  :label="t('دروس نظامی')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.gun_knowledge"
                  :label="t('معلومات سلاح')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.history_jahad"
                  :label="t('سابقه جهاد')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.dilgay_mesher"
                  :label="t('دلګی مشر')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>

              <v-col cols="12" md="4">
                <MawqafJahadiSelect v-model="form.jahadi_mawaqaf" :label="t('موقف جهادی')" />
              </v-col>
           
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.jahad_time_responsibility"
                  :label="t('مسئولیت در زمان جهاد')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.jahad_place"
                  :label="t('محل جهاد')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 4: تاریخ‌ها و ملاحظات + اسناد -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header docs-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-calendar</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('تاریخ‌ها، ملاحظات و اسناد') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('تاریخ آمدن/رفتن، ملاحظات و اسناد ضمیمه را درج کنید.') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <DateInput v-model="form.join_date" :label="t('تاریخ آمدن')" />
              </v-col>

              <v-col cols="12" md="4">
                <DateInput v-model="form.left_date" :label="t('تاریخ رفتن')" />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.note"
                  :label="t('ملاحظات')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('note')"
                  :error-messages="getErrorMessages('note')"
                />
              </v-col>
            </v-row>

            <!-- DOCS -->
            <div class="pa-3 pt-0">
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
                    <v-icon size="16" class="ml-1" title="حذف" @click.stop="removeNewDocAt(idx)">
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
                    <v-icon size="16" class="ml-1" title="حذف" @click.stop="markDocForRemoval(d)">
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
              {{ isEdit ? t('ذخیرهٔ تغییرات') : t('ثبت کارمند') }}
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

  import AppBar from '../../components/AppBar.vue';
  import DateInput from '../../components/DateInput.vue';
  import { useEmployeOfficeRepository } from '../../stores/EmployeeRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';
  import ProvinceDistrictNameSelect from '../../components/ProvinceDistrictSelect.vue';
  import ZoneHawzaNameSelect from '../../components/ZoneHawzaSelect.vue';
  import JobTypeSelect from '../../components/JobTypeSelect.vue';
  import MawqafJahadiSelect from '../../components/MawqafJahadiSelect.vue';
  import { useLookupsRepository } from '../../stores/LookupsRepository';
  const lookups = useLookupsRepository();

  const repo = useEmployeOfficeRepository();
  const auth = useLoginRepository();
  const route = useRoute();
  const router = useRouter();
  const { t, locale } = useI18n();

  /* ===== Direction ===== */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => {
    isRtl.value = newLocale === 'fa';
  });
  const bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

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
    picture: null, // raw DB path
    picture_url: null, // full URL from backend
    previewUrl: null, // what we actually show (blob or URL)

    // identity
    name: '',
    father_name: '',
    grand_father_name: '',
    nick_name: '',
    code_number: '',
    tazkira: '',
    age: '',
    phone_number: '',
    language: '',
    nation: '',
    specific_skills: '',

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
    Province_addr: '',
    coordinates: '',
    blood_group: '',

    // job / org / zone / hawza
    makhafat: '',
    makafat: '',
    bast: '',
    level: '',
    specific_job: '',
    qisem: '',
    zone: '',
    hawza: '',
    madani_halat: '',
    activity: 'فعال',

    // education & jahad
    relegy_study: '',
    new_study: '',
    faculty: '',
    nizami: '',
    job_type: '',
    gun_knowledge: '',
    history_jahad: '',
    dilgay_mesher: '',
    jahadi_mawaqaf: '',
    jahad_time_responsibility: '',
    jahad_place: '',

    // dates & notes
    join_date: '',
    left_date: '',
    note: '',

    // docs
    existingDocs: [],
    _newDocs: [],
    removed_document_ids: [],
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

  /* ===== DOCS ===== */
  const docsInput = ref(null);

  function triggerDocs() {
    docsInput.value?.click();
  }

  function onPickDocs(e) {
    const files = Array.from(e.target.files || []);
    form.value._newDocs = files;
    if (docsInput.value) docsInput.value.value = '';
  }

  function removeNewDocAt(index) {
    if (!Array.isArray(form.value._newDocs)) return;
    form.value._newDocs.splice(index, 1);
    if (docsInput.value) docsInput.value.value = '';
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
    form.value.existingDocs = (form.value.existingDocs || []).filter((d) => d.id !== doc.id);
  }

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
      form.value.previewUrl = URL.createObjectURL(file); // blob URL
    } else {
      // fall back to backend URL
      form.value.previewUrl = form.value.picture_url || null;
    }
    // allow selecting the same file again
    if (e.target) e.target.value = '';
  }

  function normalizePicture(src) {
    if (!src) return null;

    let clean = String(src).trim();

    // keep blob/data URLs exactly
    if (clean.startsWith('blob:') || clean.startsWith('data:')) {
      return clean;
    }

    // Fix escaped slashes
    clean = clean.replace(/\\\//g, '/');

    // Absolute URLs
    if (clean.startsWith('http://') || clean.startsWith('https://')) {
      return clean;
    }

    // Root-relative
    if (clean.startsWith('/')) {
      return clean;
    }

    // Fallback: make it root-relative
    return '/' + clean.replace(/^\/+/, '');
  }

  function openPreview(src) {
    const base = src || form.value.previewUrl || form.value.picture_url || form.value.picture;
    const normalized = normalizePicture(base);

    previewSrc.value = normalized;
    if (previewSrc.value) {
      previewOpen.value = true;
    }
  }
  function mapFromApi(payload = {}) {
    // 👇 Unwrap Laravel-style { data: { ... } } responses
    const x = payload.data ?? payload;

    const b = blankForm();
    b.id = x.id ?? null;

    // Backend sends full URL in `picture`
    const fullUrl = x.picture ?? null;

    b.picture = x.picture ?? null; // raw from backend (full URL)
    b.picture_url = fullUrl; // keep same for consistency
    b.previewUrl = fullUrl; // show it in avatar immediately

    // identity
    b.name = x.name ?? '';
    b.father_name = x.father_name ?? '';
    b.grand_father_name = x.grand_father_name ?? '';
    b.nick_name = x.nick_name ?? '';
    b.code_number = x.code_number ?? '';
    b.tazkira = x.tazkira ?? '';
    b.age = x.age ?? '';
    b.phone_number = x.phone_number ?? '';
    b.language = x.language ?? '';
    b.nation = x.nation ?? '';
    b.specific_skills = x.specific_skills ?? '';
    b.blood_group = x.blood_group ?? '';

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
    b.Province_addr = x.Province_addr ?? '';
    b.coordinates = x.coordinates ?? '';

    // job / org / zone / hawza
    b.makafat = x.makafat ?? '';
    b.makhafat = x.makhafat ?? '';
    b.bast = x.bast ?? '';
    b.level = x.level ?? '';
    b.specific_job = x.specific_job ?? '';
    b.qisem = x.qisem ?? '';
    b.zone = x.zone ?? '';
    b.hawza = x.hawza ?? '';
    b.madani_halat = x.madani_halat ?? '';
    b.activity = x.activity ?? '';

    // education & jahad
    b.relegy_study = x.relegy_study ?? '';
    b.new_study = x.new_study ?? '';
    b.faculty = x.faculty ?? '';
    b.nizami = x.nizami ?? '';
    b.job_type = x.job_type ?? '';
    b.gun_knowledge = x.gun_knowledge ?? '';
    b.history_jahad = x.history_jahad ?? '';
    b.dilgay_mesher = x.dilgay_mesher ?? '';
    b.jahadi_mawaqaf = x.jahadi_mawaqaf ?? '';
    b.jahad_time_responsibility = x.jahad_time_responsibility ?? '';
    b.jahad_place = x.jahad_place ?? '';

    // dates & notes
    b.join_date = x.join_date ?? '';
    b.left_date = x.left_date ?? '';
    b.note = x.note ?? '';

    // docs
    b.existingDocs = Array.isArray(x.documents) ? x.documents : [];

    return b;
  }

  async function loadForEdit() {
    if (!isEdit.value) {
      form.value = blankForm();
      pictureFile.value = null;
      pictureChanged.value = false;
      return;
    }

    try {
      if (typeof repo.FetchEmployeeData === 'function') {
        await repo.FetchEmployeeData(idParam.value);
        const raw = Array.isArray(repo.Employee) ? repo.Employee[0] : repo.Employee || {};
        const mapped = mapFromApi(raw);
        form.value = { ...mapped, _newDocs: [], removed_document_ids: [] };
        pictureFile.value = null;
        pictureChanged.value = false;
      }
    } catch (e) {
      console.error('loadForEdit error', e);
      showErrorMessage(t('خطا در دریافت معلومات کارمند'));
    }
  }

  /* ===== FORM DATA ===== */
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
    delete payload.existingDocs;
    delete payload._newDocs;
    delete payload.removed_document_ids;

    // ✅ NEW: backend sets this automatically
    delete payload.code_number;

    const fd = toFormData(payload);

    if (pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

    (form.value._newDocs || []).forEach((file) => {
      fd.append('documents[]', file);
    });

    return fd;
  }
  function buildUpdateFormData() {
    const payload = { ...form.value };
    const removed = payload.removed_document_ids || [];

    delete payload.previewUrl;
    delete payload.picture_url;
    delete payload.existingDocs;
    delete payload._newDocs;
    delete payload.removed_document_ids;
    delete payload.picture;

    // ✅ NEW: backend does not allow updating this
    delete payload.code_number;

    const fd = toFormData(payload);

    if (pictureChanged.value && pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

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
        const created = await repo.CreateEmployee(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('کارمند موفقانه ثبت شد'));
          router.push({ path: '/app/officeemployee' }).catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateEmployee(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push({ path: '/app/officeemployee' }).catch(() => {});
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

  /* ===== INIT ===== */
  onMounted(async () => {
    await Promise.all([
      lookups.GetProvinces(),
      lookups.GetDistricts(),
      lookups.GetZones(),
      lookups.GetHawzas(),
      lookups.GetMawqafJahadis(),
      lookups.GetJobTypes(),
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
      !!form.value.bast?.trim() || !!form.value.specific_job?.trim() || !!form.value.faculty?.trim()
  );
  const step4Complete = computed(
    () =>
      (form.value._newDocs?.length || 0) > 0 ||
      (form.value.existingDocs?.length || 0) > 0 ||
      !!form.value.note?.trim()
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
    top: 80px; /* distance from top (AppBar) – adjust if needed */
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
    margin-top: 140px; /* was 8px – increase so header is fully below stepper */
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

  /* picture hover tools */
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

  /* Dropdown primary background */
  :deep(.primary-select .v-field) {
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
