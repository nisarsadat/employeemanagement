<template>
  <v-container fluid class="page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح معلومات زندانی تسلیم شده') : t('اضافه کردن زندانی تسلیم شده')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک زندانی تسلیم شده موجود')
          : t('ثبت یک زندانی تسلیم شده جدید در سیستم')
      "
    >
      <template #actions>
        <v-btn variant="text" prepend-icon="mdi-arrow-left" class="text-none" @click="goBack">
          {{ t('برگشت') }}
        </v-btn>
      </template>
    </AppBar>

    <!-- STEPPER (sticky when scrolling) -->
    <section class="stepper-shell" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card class="stepper-card py-4 pb-12" elevation="0">
        <div class="d-flex justify-space-between align-center mb-1">
          <span class="text-caption text-medium-emphasis stepper-caption">
            {{ t('مراحل خانه پری فرم زندانی تسلیم شده') }}
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

            <!-- 3: جزئیات دستگیری -->
            <div class="stepper-dot-group">
              <div
                class="stepper-dot"
                :class="{
                  'is-complete': step3Complete,
                  'is-active': currentStep === 3 && !step3Complete,
                }"
              >
                <v-icon size="16">
                  {{ step3Complete ? 'mdi-check' : 'mdi-handcuffs' }}
                </v-icon>
              </div>
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step3Complete }"
              >
                {{ t('جزئیات دستگیری') }}
              </span>
            </div>

            <!-- 4: مظنونیت / اسناد -->
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
                {{ t('مظنونیت و اسناد') }}
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
                  {{
                    isEdit
                      ? t('فرم اصلاح معلومات زندانی تسلیم شده')
                      : t('فرم ثبت زندانی تسلیم شده جدید')
                  }}
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

          <!-- SECTION 1: هویت + تصویر + تماس / وظیفه -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-account-badge</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('معرفت و معلومات تماس') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{
                  t(
                    'نام، نام پدر، نام پدر کلان، نام مستعار، تذکره، عمر، شماره تماس، زبان، سطح علمی و حالت مدنی'
                  )
                }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <!-- Picture -->
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
                          :src="normalizePicture(form.previewUrl || form.picture_url || form.picture)"
                          cover
                        />
                        <v-icon v-else size="36" color="grey">mdi-account</v-icon>
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

              <!-- Identity + basic info -->
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
                      :label="t('نام پدر کلان')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('grand_father_name')"
                      :error-messages="getErrorMessages('grand_father_name')"
                    />
                  </v-col>

                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.nickname"
                      :label="t('نام مستعار')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('nickname')"
                      :error-messages="getErrorMessages('nickname')"
                    />
                  </v-col>

                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.tazkira"
                      :label="t('نمبر تذکره')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('tazkira')"
                      :error-messages="getErrorMessages('tazkira')"
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
                      v-model="form.phone_number"
                      :label="t('شماره تماس')"
                      density="compact"
                      variant="outlined"
                      :rules="[rules.phone]"
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
                      :error="hasError('language')"
                      :error-messages="getErrorMessages('language')"
                    />
                  </v-col>

                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.scientific_level"
                      :label="t('سطح علمی')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('scientific_level')"
                      :error-messages="getErrorMessages('scientific_level')"
                    />
                  </v-col>

                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.halat_madani"
                      :label="t('حالت مدنی')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('halat_madani')"
                      :error-messages="getErrorMessages('halat_madani')"
                    />
                  </v-col>

                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.criminal_history"
                      :label="t('سابقه جرمی')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('criminal_history')"
                      :error-messages="getErrorMessages('criminal_history')"
                    />
                  </v-col>

                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.old_job"
                      :label="t('وظیفه سابقه')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('old_job')"
                      :error-messages="getErrorMessages('old_job')"
                    />
                  </v-col>

                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.new_job"
                      :label="t('وظیفه فعلی')"
                      density="compact"
                      variant="outlined"
                      :error="hasError('new_job')"
                      :error-messages="getErrorMessages('new_job')"
                    />
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 2: آدرس‌های اصلی و فعلی -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-home-city</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('آدرس اصلی و فعلی') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('آدرس اصلی (زادگاه) و آدرس فعلی زندانی را وارد کنید.') }}
              </span>
            </div>
            <v-divider />

            <!-- Main address -->
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
                  v-model="form.main_district"
                  :label="t('ولسوالی اصلی *')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.required]"
                  :error="hasError('main_district')"
                  :error-messages="getErrorMessages('main_district')"
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

            <!-- Current address -->
            <v-row class="px-3 pb-3 pt-0">
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

            <v-row class="px-3 pb-3 pt-0">
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

              <v-col cols="12" md="8">
                <v-text-field
                  v-model="form.coordinates"
                  :label="t('کوردینات محل دستگیری / آدرس')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('coordinates')"
                  :error-messages="getErrorMessages('coordinates')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 3: جزئیات دستگیری -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-handcuffs</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('جزئیات دستگیری') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('محل، مرجع، تاریخ و ساعت دستگیری را درج کنید.') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.captive_place"
                  :label="t('محل دستگیری *')"
                  density="compact"
                  variant="outlined"
                  :rules="[rules.required]"
                  :error="hasError('captive_place')"
                  :error-messages="getErrorMessages('captive_place')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.captive_marja"
                  :label="t('مرجع دستگیری')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('captive_marja')"
                  :error-messages="getErrorMessages('captive_marja')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <DateInput
                  v-model="form.captive_date"
                  :label="t('تاریخ دستگیری *')"
                  :rules="[rules.required]"
                  :error="hasError('captive_date')"
                  :error-messages="getErrorMessages('captive_date')"
                />
              </v-col>
            </v-row>

            <v-row class="px-3 pb-3 pt-0">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.captive_hour"
                  :label="t('ساعت دستگیری')"
                  type="time"
                  step="60"
                  density="compact"
                  variant="outlined"
                  :error="hasError('captive_hour')"
                  :error-messages="getErrorMessages('captive_hour')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- SECTION 4: مظنونیت، شواهد، ملاحظات و اسناد -->
          <v-sheet class="section-card mb-4" rounded="lg" elevation="1">
            <div class="section-header docs-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-alert-decagram</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('مظنونیت، شواهد، ملاحظات و اسناد') }}
                </span>
              </div>
              <span class="section-subtitle text-caption">
                {{ t('نوعیت مظنونیت، حالت دستگیری، وسایل گرفته شده، ملاحظات و اسناد را درج کنید.') }}
              </span>
            </div>
            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.suspect_type"
                  :label="t('نوعیت مظنونیت')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('suspect_type')"
                  :error-messages="getErrorMessages('suspect_type')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.captive_halat"
                  :label="t('حالت دستگیری')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('captive_halat')"
                  :error-messages="getErrorMessages('captive_halat')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.suspect_tools"
                  :label="t('وسایل و شواهد گرفته شده')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('suspect_tools')"
                  :error-messages="getErrorMessages('suspect_tools')"
                />
              </v-col>

              <v-col cols="12" md="12">
                <v-text-field
                  v-model="form.notes"
                  :label="t('ملاحظات')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('notes')"
                  :error-messages="getErrorMessages('notes')"
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
              {{ isEdit ? t('ذخیرهٔ تغییرات') : t('ثبت زندانی تسلیم شده') }}
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

  // identity & personal
  name: '',
  father_name: '',
  grand_father_name: '',
  nickname: '',
  tazkira: '',
  age: '',
  phone_number: '',
  language: '',
  scientific_level: '',
  halat_madani: '',
  criminal_history: '',
  old_job: '',
  new_job: '',

  // addresses
  main_province: '',
  main_district: '',
  main_village: '',
  now_province: '',
  now_district: '',
  now_village: '',
  Province_addr: '',
  coordinates: '',

  // captive details
  captive_place: '',
  captive_marja: '',
  captive_date: '',
  captive_hour: '',

  // suspect / evidence / notes
  suspect_type: '',
  captive_halat: '',
  suspect_tools: '',
  notes: '',

  // docs
  existingDocs: [],
  _newDocs: [],
  removed_document_ids: [],
});

const form = ref(blankForm());

/* ===== RULES ===== */
const rules = {
  required: (v) => !!v || t('این فیلد الزامی است'),
  phone: (v) =>
    !v || /^(\+?\d{7,15})$/.test(String(v)) || t('شماره تماس نامعتبر است'),
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
  form.value.existingDocs = (form.value.existingDocs || []).filter(
    (d) => d.id !== doc.id
  );
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
  const base =
    src || form.value.previewUrl || form.value.picture_url || form.value.picture;
  const normalized = normalizePicture(base);

  previewSrc.value = normalized;
  if (previewSrc.value) {
    previewOpen.value = true;
  }
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
  b.nickname = x.nickname ?? '';
  b.tazkira = x.tazkira ?? '';
  b.age = x.age ?? '';
  b.phone_number = x.phone_number ?? '';
  b.language = x.language ?? '';
  b.scientific_level = x.scientific_level ?? '';
  b.halat_madani = x.halat_madani ?? '';
  b.criminal_history = x.criminal_history ?? '';
  b.old_job = x.old_job ?? '';
  b.new_job = x.new_job ?? '';

  // addresses
  b.main_province = x.main_province ?? '';
  b.main_district = x.main_district ?? '';
  b.main_village = x.main_village ?? '';
  b.now_province = x.now_province ?? '';
  b.now_district = x.now_district ?? '';
  b.now_village = x.now_village ?? '';
  b.Province_addr = x.Province_addr ?? '';
  b.coordinates = x.coordinates ?? '';

  // captive details
  b.captive_place = x.captive_place ?? '';
  b.captive_marja = x.captive_marja ?? '';
  b.captive_date = x.captive_date ?? '';
  b.captive_hour = x.captive_hour ?? '';

  // suspect / evidence / notes
  b.suspect_type = x.suspect_type ?? '';
  b.captive_halat = x.captive_halat ?? '';
  b.suspect_tools = x.suspect_tools ?? '';
  b.notes = x.notes ?? '';

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
    if (typeof repo.FetchCaptiveDeliveryData === 'function') {
      await repo.FetchCaptiveDeliveryData(idParam.value);
      const raw = Array.isArray(repo.CaptiveDelivery)
        ? repo.CaptiveDelivery[0]
        : repo.CaptiveDelivery || {};
      const mapped = mapFromApi(raw);
      form.value = { ...mapped, _newDocs: [], removed_document_ids: [] };
      pictureFile.value = null;
      pictureChanged.value = false;
    }
  } catch (e) {
    console.error('loadForEdit error', e);
    showErrorMessage(t('خطا در دریافت معلومات زندانی تسلیم شده'));
  }
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
  delete payload.existingDocs;
  delete payload._newDocs;
  delete payload.removed_document_ids;

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

  const fd = toFormData(payload);

  if (pictureChanged.value && pictureFile.value) {
    fd.append('picture', pictureFile.value);
    if (form.value.picture && typeof form.value.picture === 'string') {
      fd.append('old_picture', form.value.picture);
    }
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
      const created = await repo.CreateCaptiveDelivery(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('زندانی تسلیم شده موفقانه ثبت شد'));
        router.push({ path: '/app/delevirycaptive' }).catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo
        .UpdateCaptiveDelivery(form.value.id, fd)
        .catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push({ path: '/app/delevirycaptive' }).catch(() => {});
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
  () => !!form.value.captive_place?.trim() && !!form.value.captive_date?.trim()
);
const step4Complete = computed(
  () =>
    (form.value._newDocs?.length || 0) > 0 ||
    (form.value.existingDocs?.length || 0) > 0 ||
    !!form.value.suspect_type?.trim() ||
    !!form.value.notes?.trim()
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
  max-width: 85%;
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
