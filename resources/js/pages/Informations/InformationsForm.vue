<template>
  <v-container fluid class="page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('اصلاح اطلاع') : t('اضافه کردن اطلاع')"
      :subtitle="isEdit ? t('ویرایش جزئیات یک اطلاع موجود') : t('ثبت یک اطلاع جدید در سیستم')"
    >
    </AppBar>
    <v-dialog v-model="duplicateDialog" max-width="760">
      <v-card rounded="xl">
        <v-card-title class="d-flex align-center ga-2">
          <v-icon color="warning">mdi-alert-circle-outline</v-icon>
          <span>{{ t('اطلاع مشابه پیدا شد') }}</span>
        </v-card-title>

        <v-card-text>
          <div class="mb-3">
            {{ t('در سیستم معلومات مشابه با همین فیلدها پیدا شد:') }}
          </div>

          <v-alert type="info" variant="tonal" class="mb-3">
            <div>
              <strong>{{ t('نام') }}:</strong> {{ duplicatePayload.name || '—' }}
            </div>
            <div>
              <strong>{{ t('نام پدر') }}:</strong> {{ duplicatePayload.father_name || '—' }}
            </div>
            <div>
              <strong>{{ t('ولایت اصلی') }}:</strong> {{ duplicatePayload.main_province || '—' }}
            </div>
            <div>
              <strong>{{ t('ولسوالی اصلی') }}:</strong> {{ duplicatePayload.main_districts || '—' }}
            </div>
            <div>
              <strong>{{ t('قریه اصلی') }}:</strong> {{ duplicatePayload.main_village || '—' }}
            </div>
          </v-alert>

          <div class="mb-2 font-weight-medium">
            {{ t('ریکاردهای مشابه:') }}
          </div>

          <div v-if="duplicateMatches.length" class="d-flex flex-wrap ga-2">
            <v-chip
              v-for="row in duplicateMatches"
              :key="row.id"
              color="warning"
              variant="tonal"
              class="cursor-pointer"
              @click="goToDuplicateRow(row)"
            >
              #{{ row.id }} - {{ row.name || '—' }}
            </v-chip>
          </div>

          <div v-else class="text-medium-emphasis">—</div>

          <div class="mt-4">
            {{ t('آیا با وجود این، می‌خواهید ثبت ادامه یابد؟') }}
          </div>
        </v-card-text>

        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="closeDuplicateDialog">
            {{ t('بندول') }}
          </v-btn>
          <v-btn color="primary" :loading="saving" @click="confirmDuplicateAndSave">
            {{ t('ثبت') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
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
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step1Complete }"
              >
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
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step2Complete }"
              >
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
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step3Complete }"
              >
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
              <span
                class="stepper-label text-caption"
                :class="{ 'is-complete-text': step4Complete }"
              >
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
                <!-- <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('کودها') }}
                </span> -->
              </div>
              <!-- <span class="section-subtitle text-caption">
                {{ t('انتخاب کود منبع و کارمند اداری در صورت موجود بودن') }}
              </span> -->
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="6">
                <ResourceRemoteAutocomplete
                  class="primary-select"
                  v-model="form.resource_id"
                  :label="t('کود منبع')"
                  :item-title="formatResource"
                  :error="hasError('resource_id')"
                  :error-messages="getErrorMessages('resource_id')"
                  @selected="onInformationResourceSelected"
                  @cleared="onInformationResourceCleared"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-autocomplete
                  class="primary-select"
                  v-model="form.office_employee_id"
                  :items="officeEmployeesItems"
                  item-title="code_number"
                  item-value="id"
                  :label="t('کارمند مربوط')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :disabled="officeEmployeeLocked"
                  :error="hasError('office_employee_id')"
                  :error-messages="getErrorMessages('office_employee_id')"
                />
                <div v-if="officeEmployeeLocked" class="text-caption text-medium-emphasis mt-1">
                  {{ t('کارمند مربوط از روی منبع به صورت خودکار انتخاب شد') }}
                </div>
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
              <!-- <span class="section-subtitle text-caption">
                {{ t('نام، نام پدر و سایر مشخصات هویتی را وارد کنید.') }}
              </span> -->
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.name"
                  :label="t('نام')"
                  density="compact"
                  variant="outlined"
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

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.job"
                  :label="t('وظیفه')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.age"
                  :label="t('سن')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="form.madani_halat"
                  :items="madaniHalatItems"
                  variant="outlined"
                  :label="t('حالت مدنی')"
                  density="compact"
                  clearable
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
              <!-- <span class="section-subtitle text-caption">
                {{ t('آدرس اصلی شخص (زادگاه / محل اصلی)') }}
              </span> -->
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col cols="12" md="12">
                <ProvinceDistrictNameSelect
                  v-model:province="form.main_province"
                  v-model:district="form.main_districts"
                  :province-label="t('ولایت اصلی')"
                  :district-label="t('ولسوالی اصلی')"
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
              <!-- <span class="section-subtitle text-caption">
                {{ t('محل سکونت فعلی شخص در حال حاضر') }}
              </span> -->
            </div>
            <v-divider />
            <v-row class="pa-3">
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
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.specefic_place"
                  :label="t('موقعیت مشخص')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.street_number"
                  :label="t('شماره سرک')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.home_number"
                  :label="t('شماره خانه')"
                  density="compact"
                  variant="outlined"
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
                  {{ t('کود کارمند اطلاع گیرنده') }}
                </span>
              </div>
              <!-- <span class="section-subtitle text-caption">
                {{ t('در صورتیکه اطلاع مربوط ولایت مشخصی باشد، آن را درج کنید.') }}
              </span> -->
            </div>
            <v-divider />
            <v-row class="pa-3">
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
                <v-autocomplete
                  class="primary-select"
                  v-model="form.office_employee_info_id"
                  :items="officeEmployeesItems"
                  item-title="code_number"
                  item-value="id"
                  :label="t('کود کارمند اطلاع گیرنده')"
                  density="compact"
                  :rules="[rules.required]"
                  variant="outlined"
                  clearable
                  :error="hasError('office_employee_info_id')"
                  :error-messages="getErrorMessages('office_employee_info_id')"
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
              <!-- <span class="section-subtitle text-caption">
                {{ t('نوع اطلاع، سیاسی/جنایی بودن و زمان ثبت را مشخص کنید.') }}
              </span> -->
            </div>
            <v-divider />
            <v-row class="pa-3">
              <v-col>
                <SeyaseeTypeSelect
                  v-model="form.syasi_info_type"
                  :label="t('سیاسی - نوع')"
                  :cols="12"
                  :md="12"
                  :disabled="!!form.jenayee_info_type"
                />
              </v-col>

              <v-col>
                <JenayeeTypeSelect
                  v-model="form.jenayee_info_type"
                  :label="t('جنایی')"
                  :cols="12"
                  :md="12"
                  :disabled="!!form.syasi_info_type"
                />
              </v-col>
              <v-col>
                <LetryFormatSelect v-model="form.letry_format_name" :label="t('۵۶ فارمټ')" />
              </v-col>
            </v-row>

            <v-row class="pa-3 pt-0">
              <v-col cols="12" md="12">
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
              <v-col cols="12" md="4">
                <InformationCategorySelect
                  v-model="form.information_category"
                  :label="t('کتگوری اطلاع')"
                  :disabled="loadingEdit"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="form.resource_lasrasy"
                  :items="['مستقیم', 'غیر مستقیم']"
                  :label="t(' د منبع لاسرسی')"
                  variant="outlined"
                  density="compact"
                  clearable
                />
              </v-col>
              <v-col cols="6" md="12">
                <v-text-field
                  v-model="form.coordinates"
                  :label="t('مختصات')"
                  variant="outlined"
                  density="compact"
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
              <!-- <span class="section-subtitle text-caption">
                {{ t('تفصیل اطلاع، نظر مدیر، رهنمود و سایر توضیحات را درج نمایید.') }}
              </span> -->
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
                  v-model="form.tahlil"
                  :label="t('نظر آمریت تعقیب اطلاع')"
                  rows="3"
                  auto-grow
                  density="compact"
                  variant="outlined"
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

              <!-- <v-col cols="12" md="6">
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
              </v-col> -->
            </v-row>

            <v-row class="px-3 pb-3 pt-0">
              <v-col cols="12" md="12">
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
          <!-- FINANCE SECTION -->
          <v-sheet class="section-card mb-6" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-cash-multiple</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('معلومات مالی') }}
                </span>
              </div>
              <!-- <span class="section-subtitle text-caption">
                {{ t('حساب و جزئیات مالی مربوط به اطلاع') }}
              </span> -->
            </div>

            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="6">
                <v-autocomplete
                  v-model="form.account_id"
                  :items="accountItems"
                  item-title="display"
                  item-value="id"
                  :label="t('حساب')"
                  density="compact"
                  variant="outlined"
                  clearable
                  :error="hasError('account_id')"
                  :error-messages="getErrorMessages('account_id')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.message_to_finance"
                  :label="t('مسج مالی بخش ته')"
                  density="compact"
                  type="number"
                  variant="outlined"
                  :error="hasError('message_to_finance')"
                  :error-messages="getErrorMessages('message_to_finance')"
                />
              </v-col>

              <v-col cols="12" md="3">
                <v-text-field
                  v-model="form.money_amount_words"
                  :label="t('مقدار په حروف')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('money_amount_words')"
                  :error-messages="getErrorMessages('money_amount_words')"
                />
              </v-col>
            </v-row>
          </v-sheet>

          <!-- HAMAHANGI SECTION -->
          <v-sheet class="section-card mb-6" rounded="lg" elevation="1">
            <div class="section-header">
              <div class="d-flex align-center">
                <v-icon class="ml-2 section-icon">mdi-clipboard-text</v-icon>
                <span class="text-subtitle-2 font-weight-medium section-title">
                  {{ t('هم‌آهنگی') }}
                </span>
              </div>
              <!-- <span class="section-subtitle text-caption">
                {{ t('تاریخ هم‌آهنگی، شماره مکتوب و ارگان را درج کنید.') }}
              </span> -->
            </div>

            <v-divider />

            <v-row class="pa-3">
              <v-col cols="12" md="4">
                <DateInput v-model="form.hamahangi_date" :label="t('تاریخ هم‌آهنگی')" />
              </v-col>

              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.maktop_number"
                  :label="t('شماره مکتوب')"
                  density="compact"
                  variant="outlined"
                  :error="hasError('maktop_number')"
                  :error-messages="getErrorMessages('maktop_number')"
                />
              </v-col>

              <v-col cols="12" md="4">
                <OrganSelect v-model="form.organ" :label="t('ارگان اطلاع')" :disabled="saving" />
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
              <!-- <span class="section-subtitle text-caption">
                {{ t('می‌توانید چندین فایل را اضافه کنید. برای حذف روی ضربدر کلیک کنید.') }}
              </span> -->
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
            <v-row class="pa-3">
              <v-col cols="12" md="12">
                <v-text-field
                  v-model="form.docs_note"
                  :label="t('نوت راجع به اسناد')"
                  density="compact"
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="12">
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
            </v-row>
          </v-sheet>

          <!-- ACTIONS -->
          <div class="form-actions d-flex justify-end gap-2 flex-wrap">
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
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';
  import SeyaseeTypeSelect from '@/components/SeyaseeTypeSelect.vue';
  import JenayeeTypeSelect from '@/components/JenayeeTypeSelect.vue';
  import LetryFormatSelect from '@/components/LetryFormatSelect.vue';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';
  import OrganSelect from '@/components/OrganSelect.vue';
  import AppBar from '../../components/AppBar.vue';
  import DateInput from '../../components/DateInput.vue';
  import { useFinanceRepository } from '@/stores/FinanceRepository';
  import InformationCategorySelect from '@/components/InformationCategorySelect.vue';

  const financeRepo = useFinanceRepository();

  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';
  import { printBlankForm } from '@/utils/printBlankForm';

  const repo = useDatabaseRepository();
  const auth = useLoginRepository();
  const duplicateDialog = ref(false);
  const duplicateMatches = ref([]);
  const duplicatePayload = ref({});
  const allowDuplicateSave = ref(false);
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

  /* PRINT BLANK FORM (empty fields, same design, one A4 page) */
  function printForm(e) {
    printBlankForm(e, { keepValues: true, onError: showErrorMessage });
  }

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
    office_employee_tadabiri_id: null,
    office_employee_info_id: null,
    name: '',
    nick_name: '',
    father_name: '',
    grand_father_name: '',
    hamahangi_date: repo.getCurrentDate ? repo.getCurrentDate() : '',
    maktop_number: '',
    organ: '',
    age: '',
    job: '',
    madani_halat: '',
    specefic_place: '',
    home_number: '',
    street_number: '',
    account_id: null,

    main_province: '',
    main_districts: '',
    main_village: '',
    now_province: '',
    now_district: '',
    now_village: '',
    Province_addr: '',

    syasi_info_type: '',
    jenayee_info_type: '',
    letry_format_name: '',

    information_saved_date: repo.getCurrentDate ? repo.getCurrentDate() : '',
    information_saved_hour: '',
    information_degree: '',
    information_category: '',
    resource_lasrasy: '',
    coordinates: '',

    tafseel: '',
    manager_comment: '',
    tahlil: '',
    lead_drirection: '',
    // talks: '',
    note: '',
    docs_note: '',
    result: '',
    message_to_finance: '',
    money_amount_words: '',

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

  const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
  const seyasiItems = computed(() => toArray(repo.seyasiOptions));
  const jenayeeItems = computed(() => toArray(repo.jenayeeOptions));
  const madaniHalatItems = computed(() => [t('مجرد'), t('متاحل')]);
  const accountItems = computed(() =>
    (financeRepo.accounts || []).map((a) => ({
      ...a,
      display: a.name ? `${a.name} (${a.price ?? '0'})` : `${a.id}`,
    }))
  );

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
    form.value.existingDocs = (form.value.existingDocs || []).filter((d) => d.id !== doc.id);
  }

  /* ===== LOAD EDIT DATA ===== */
  function mapFromApi(x = {}) {
    const base = blankForm();
    base.id = x.id ?? null;

    base.resource_id = x.resource_id ?? null;
    base.office_employee_id = x.office_employee_id ?? null;
    base.office_employee_tadabiri_id = x.office_employee_tadabiri_id ?? null;
    base.office_employee_info_id = x.office_employee_info_id ?? null;
    base.name = x.name ?? '';
    base.nick_name = x.nick_name ?? '';
    base.father_name = x.father_name ?? '';
    base.grand_father_name = x.grand_father_name ?? '';
    base.hamahangi_date = x.hamahangi_date ?? '';
    base.maktop_number = x.maktop_number ?? '';
    base.organ = x.organ ?? '';
    base.age = x.age ?? '';
    base.job = x.job ?? '';
    base.madani_halat = x.madani_halat ?? '';
    base.specefic_place = x.specefic_place ?? '';
    base.street_number = x.street_number ?? '';
    base.home_number = x.home_number ?? '';
    base.account_id = x.account_id ?? x.account?.id ?? null;

    base.main_province = x.main_province ?? '';
    base.main_districts = x.main_districts ?? '';
    base.main_village = x.main_village ?? '';
    base.now_province = x.now_province ?? '';
    base.now_district = x.now_district ?? '';
    base.now_village = x.now_village ?? '';
    base.Province_addr = x.Province_addr ?? '';

    base.syasi_info_type = x.syasi_info_type ?? '';
    base.jenayee_info_type = x.jenayee_info_type ?? '';
    base.letry_format_name = x.letry_format_name ?? '';

    base.information_saved_date = x.information_saved_date ?? '';
    base.information_saved_hour = x.information_saved_hour ?? '';
    base.information_degree = x.information_degree ?? '';
    base.information_category = x.information_category ?? '';
    base.resource_lasrasy = x.resource_lasrasy ?? '';
    base.coordinates = x.coordinates ?? '';

    base.tafseel = x.tafseel ?? '';
    base.manager_comment = x.manager_comment ?? '';
    base.tahlil = x.tahlil ?? '';
    base.lead_drirection = x.lead_drirection ?? '';
    base.talks = x.talks ?? '';
    base.note = x.note ?? '';
    base.docs_note = x.docs_note ?? '';
    base.result = x.result ?? '';
    base.message_to_finance = x.message_to_finance ?? '';
    base.money_amount_words = x.money_amount_words ?? '';

    base.existingDocs = Array.isArray(x.documents) ? x.documents : [];

    return base;
  }
  // the popup of the ids for the info
  function buildDuplicateCheckPayload() {
    return {
      id: form.value.id ?? null,
      name: form.value.name?.trim() || '',
      father_name: form.value.father_name?.trim() || '',
      main_province: form.value.main_province?.trim() || '',
      main_districts: form.value.main_districts?.trim() || '',
      main_village: form.value.main_village?.trim() || '',
    };
  }

  async function checkDuplicateBeforeSave() {
    const payload = buildDuplicateCheckPayload();

    duplicatePayload.value = payload;
    duplicateMatches.value = [];

    if (
      !payload.name ||
      !payload.father_name ||
      !payload.main_province ||
      !payload.main_districts ||
      !payload.main_village
    ) {
      return false;
    }

    try {
      const res = await repo.CheckInformationDuplicate?.(payload);
      const matches = res?.data?.matches || res?.matches || [];

      if (Array.isArray(matches) && matches.length > 0) {
        duplicateMatches.value = matches;
        duplicateDialog.value = true;
        return true;
      }

      return false;
    } catch (e) {
      console.error('checkDuplicateBeforeSave error', e);
      return false;
    }
  }

  function closeDuplicateDialog() {
    duplicateDialog.value = false;
  }

  async function confirmDuplicateAndSave() {
    allowDuplicateSave.value = true;
    duplicateDialog.value = false;
    await onSubmit();
  }

  function goToDuplicateRow(row) {
    if (!row?.id) return;

    repo.page = 1;

    router.push({
      path: '/app/informations',
      query: {
        duplicate_of: String(row.id),
      },
    });
  }

  async function loadForEdit() {
    if (!isEdit.value) {
      form.value = blankForm();
      return;
    }

    try {
      if (typeof repo.FetchInformationData === 'function') {
        await repo.FetchInformationData(idParam.value);
        const raw = Array.isArray(repo.Information) ? repo.Information[0] : repo.Information || {};
        const mapped = mapFromApi(raw);
        form.value = { ...mapped, _newDocs: [], removed_document_ids: [] };
      } else {
        await repo.FetchInformationsData?.({
          page: 1,
          perPage: 10,
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

    const nullableFields = [
      'resource_id',
      'office_employee_id',
      'office_employee_info_id',
      'office_employee_akhistonky_id',
      'account_id',
    ];

    Object.entries(obj).forEach(([k, v]) => {
      if (v === undefined) return;

      // Important: send cleared nullable foreign keys explicitly
      if (nullableFields.includes(k)) {
        fd.append(k, v == null || v === '' ? '' : v);
        return;
      }

      if (v === null) return;

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

    if (!allowDuplicateSave.value) {
      const hasDuplicate = await checkDuplicateBeforeSave();
      if (hasDuplicate) {
        return;
      }
    }

    saving.value = true;

    try {
      // ✅ CREATE
      if (!isEdit.value) {
        const fd = buildCreateFormData();

        const created = await repo.CreateInformation(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          // ✅ show snackbar here (this page)
          showSuccessMessage(t('اطلاع موفقانه ثبت شد'));

          // ✅ reset form
          resetForm();

          // ✅ navigate safely
          await router.push({ path: '/app/informations' });
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }

        return;
      }

      // ✅ UPDATE
      const fd = buildUpdateFormData();

      const updated = await repo.UpdateInformation(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        // ✅ show snackbar here (this page)
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));

        // ✅ navigate safely
        await router.push({ path: '/app/informations' });
      } else {
        showErrorMessage(t('ذخیرهٔ تغییرات ناکام شد'));
      }
    } catch (e) {
      console.error('onSubmit error', e);
      showErrorMessage(t('خطا در ذخیره اطلاعات'));
    } finally {
      saving.value = false;
      allowDuplicateSave.value = false;
    }
  }
  function onInformationResourceSelected(r) {
    if (!r) return;

    form.value.resource_id = r.id ?? null;
    form.value.office_employee_id = r.office_employee?.id ?? r.office_employee_id ?? null;
  }

  function onInformationResourceCleared() {
    form.value.resource_id = null;
    form.value.office_employee_id = null;
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
      repo.FetchInformationsData?.({
        page: 1,
        perPage: 10,
      }),
      financeRepo.FetchAccountsData(),
    ]);

    await loadForEdit();
  });
  /* ===== DISPLAY HELPERS ===== */
  const formatResource = (r) => {
    if (!r) return '';
    const code = r.code_number ? String(r.code_number) : '';
    const id = r.id != null ? `#${r.id}` : '';
    return [code, id].filter(Boolean).join(' — ');
  };

  /* ===== STEPPER LOGIC ===== */
  const step1Complete = computed(() => !!form.value.name?.trim());
  const step2Complete = computed(
    () =>
      !!form.value.main_province?.trim() &&
      !!form.value.main_districts?.trim() &&
      !!form.value.main_village?.trim()
  );
  const step3Complete = computed(() => !!form.value.information_saved_date);
  const step4Complete = computed(
    () =>
      (form.value._newDocs?.length || 0) > 0 ||
      (form.value.existingDocs?.length || 0) > 0 ||
      !!form.value.tafseel?.trim()
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

  /* If Seyasee selected → clear & disable Jenayee */
  watch(
    () => form.value.syasi_info_type,
    (val) => {
      if (val) {
        form.value.jenayee_info_type = '';
      }
    }
  );

  /* If Jenayee selected → clear & disable Seyasee */
  watch(
    () => form.value.jenayee_info_type,
    (val) => {
      if (val) {
        form.value.syasi_info_type = '';
      }
    }
  );

  // ✅ Freeze office employee field when resource selected
  const officeEmployeeLocked = computed(() => !!form.value.resource_id);
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

  /* Card styling */
  .form-card {
    border-radius: 18px;
    border: 1px solid rgba(33, 150, 243, 0.08);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04) !important;
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
