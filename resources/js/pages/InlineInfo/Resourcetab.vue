<template>
  <v-container flat fluid :dir="isRtl ? 'rtl' : 'ltr'">
    <v-dialog v-model="reviewOpen" max-width="900" scrollable>
      <v-card rounded="xl" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-card-title class="d-flex align-center justify-space-between">
          <div class="d-flex align-center" style="gap: 10px">
            <v-icon>mdi-clipboard-text-outline</v-icon>
            <span>{{ t('بازبینی معلومات قبل از ثبت') }}</span>
          </div>
          <v-btn icon variant="text" @click="reviewOpen = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider />

        <v-card-text class="pretty-scroll" style="max-height: 70vh">
          <!-- Simple 2-column review table -->
          <v-table density="compact">
            <tbody>
              <tr v-for="row in reviewRows" :key="row.key">
                <td class="font-weight-bold" style="white-space: nowrap">
                  {{ row.label }}
                </td>
                <td>
                  {{ row.value || '—' }}
                </td>
              </tr>

              <!-- docs -->
              <tr>
                <td class="font-weight-bold">{{ t('اسناد / ضمایم') }}</td>
                <td>
                  <div v-if="(draft._newDocs || []).length">
                    <v-chip
                      v-for="f in draft._newDocs"
                      :key="fileKey(f)"
                      size="small"
                      class="ma-1"
                      variant="outlined"
                    >
                      {{ f.name }}
                    </v-chip>
                  </div>
                  <span v-else>—</span>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-btn variant="text" @click="reviewOpen = false" :disabled="submitting">
            {{ t('برگشت') }}
          </v-btn>
          <v-spacer />
          <v-btn
            color="primary"
            :loading="submitting"
            :disabled="submitting"
            @click="openSubmitConfirm"
          >
            <v-icon start>mdi-check</v-icon>
            {{ t('تایید و ثبت') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- ✅ SMALL CONFIRM: Submit -->
    <v-dialog v-model="confirmSubmitOpen" max-width="420">
      <v-card rounded="xl" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-card-title class="d-flex align-center" style="gap: 10px">
          <v-icon color="primary">mdi-check-decagram</v-icon>
          <span>{{ t('تایید نهایی') }}</span>
        </v-card-title>

        <v-card-text class="pt-2">
          {{ t('آیا مطمئن هستید که می‌خواهید این معلومات ثبت شود؟') }}
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-btn variant="text" @click="confirmSubmitOpen = false" :disabled="submitting">
            {{ t('لغو') }}
          </v-btn>
          <v-spacer />
          <v-btn
            color="primary"
            :loading="submitting"
            :disabled="submitting"
            @click="doConfirmedSubmit"
          >
            <v-icon start>mdi-check</v-icon>
            {{ t('بلی، ثبت شود') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- ✅ SMALL CONFIRM: Reset -->
    <v-dialog v-model="confirmResetOpen" max-width="420">
      <v-card rounded="xl" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-card-title class="d-flex align-center" style="gap: 10px">
          <v-icon color="error">mdi-alert</v-icon>
          <span>{{ t('هشدار') }}</span>
        </v-card-title>

        <v-card-text class="pt-2">
          {{ t('با پاک کردن فرم، تمام معلومات وارد شده حذف می‌شود. ادامه می‌دهید؟') }}
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-btn variant="text" @click="confirmResetOpen = false" :disabled="submitting">
            {{ t('لغو') }}
          </v-btn>
          <v-spacer />
          <v-btn color="error" :disabled="submitting" @click="doConfirmedReset">
            <v-icon start>mdi-delete</v-icon>
            {{ t('بلی، پاک شود') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-form ref="form" v-model="valid" @keydown.enter.prevent lazy-validation @submit.prevent>
      <v-card class="pa-4 form-card" elevation="2">
        <!-- Header -->
        <v-card-title class="form-title d-flex align-center mb-4" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-icon color="primary" class="mr-2">mdi-information-outline</v-icon>
          {{ t('افزودن اطلاعات جدید') }}
        </v-card-title>

        <!-- Body -->
        <v-card-text class="pa-0 pretty-scroll scroll-body max-h-form" :dir="isRtl ? 'rtl' : 'ltr'">
          <!-- Codes (+ Name moved here to make 3 columns) -->
          <v-row class="tight-grid">
            <!-- resource_id -->
            <v-col cols="12" md="3">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کود منبع') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <ResourceRemoteAutocomplete
                  v-model="draft.resource_id"
                  :label="t('کود منبع')"
                  density="compact"
                  variant="outlined"
                  rounded="lg"
                  class="cell"
                  :rules="[rules.selectRequired(t('کود منبع'))]"
                  :item-title="resourceTitle"
                  @selected="onResourceSelected"
                  @cleared="onResourceCleared"
                />
              </v-card-text>
            </v-col>

            <!-- office_employee_id -->
            <v-col cols="12" md="3">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('کود کارمند') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-autocomplete
                  v-model="draft.office_employee_id"
                  :items="officeEmployeesItems"
                  item-title="code_number"
                  item-value="id"
                  rounded="lg"
                  class="cell"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-inner-icon="mdi-account-badge"
                />
              </v-card-text>
            </v-col>

            <!-- ✅ trust_percentage (read-only) -->
            <v-col cols="12" md="3">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('فیصدی موثقیت') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-text-field
                  v-model="draft.trust_percentage"
                  variant="outlined"
                  rounded="lg"
                  class="cell"
                  density="compact"
                  readonly
                  prepend-inner-icon="mdi-percent"
                />
              </v-card-text>
            </v-col>
            <v-col cols="12" md="3">
              <v-card-item>
                <v-card-subtitle class="field-label">{{ t('د منبع لاسرسی') }}</v-card-subtitle>
              </v-card-item>
              <v-card-text>
                <v-select
                  v-model="draft.resource_lasrasy"
                  :items="['مستقیم', 'غیر مستقیم']"
                  :label="t(' د منبع لاسرسی')"
                  variant="outlined"
                  density="compact"
                  clearable
                />
              </v-card-text>
            </v-col>
          </v-row>

          <!-- ✅ SHEET: د متهم شهرت -->
          <v-card variant="outlined" class="sheet-card px-4 py-3 mt-2">
            <v-card-item class="sheet-title-row">
              <v-icon class="mr-2" size="small">mdi-account-details</v-icon>
              <v-card-subtitle class="sheet-title">{{ t('د متهم شهرت') }}</v-card-subtitle>
            </v-card-item>

            <v-row class="tight-grid">
              <!-- name -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('نام') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.name"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                    prepend-inner-icon="mdi-account"
                  />
                </v-card-text>
              </v-col>

              <!-- nick_name -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('نام مستعار') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.nick_name"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                    prepend-inner-icon="mdi-tag"
                  />
                </v-card-text>
              </v-col>

              <!-- father_name -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('نام پدر') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.father_name"
                    variant="outlined"
                    density="compact"
                    clearable
                    rounded="lg"
                    class="cell"
                    prepend-inner-icon="mdi-account-supervisor"
                  />
                </v-card-text>
              </v-col>

              <!-- grand_father_name -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('نام پدرکلان') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.grand_father_name"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                    prepend-inner-icon="mdi-account-supervisor-outline"
                  />
                </v-card-text>
              </v-col>

              <!-- ✅ NEW: age -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('عمر') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.age"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                    min="0"
                    prepend-inner-icon="mdi-numeric"
                  />
                </v-card-text>
              </v-col>
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('موقعیت مشخص') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.specefic_place"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                  />
                </v-card-text>
              </v-col>
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('شماره خانه') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.home_number"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                  />
                </v-card-text>
              </v-col>
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('شماره سرک') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.street_number"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                  />
                </v-card-text>
              </v-col>
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('مختصات') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.coordinates"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                  />
                </v-card-text>
              </v-col>
              <!-- ✅ NEW: madani_halat (مجرد/متاحل) -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('حالت مدنی') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-select
                    v-model="draft.madani_halat"
                    :items="madaniHalatItems"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                    prepend-inner-icon="mdi-account-heart"
                  />
                </v-card-text>
              </v-col>

              <!-- ✅ NEW: job -->
              <v-col cols="12" md="8">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('وظیفه') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.job"
                    variant="outlined"
                    density="compact"
                    rounded="lg"
                    class="cell"
                    clearable
                    prepend-inner-icon="mdi-briefcase"
                  />
                </v-card-text>
              </v-col>
            </v-row>
          </v-card>

          <!-- Addresses -->
          <v-card variant="outlined" class="sheet-card px-4 py-3 mt-3">
            <v-card-item class="sheet-title-row">
              <v-icon class="mr-2" size="small">mdi-map-marker</v-icon>
              <v-card-subtitle class="sheet-title">{{ t('آدرس‌ها') }}</v-card-subtitle>
            </v-card-item>

            <v-expansion-panels variant="accordion">
              <v-expansion-panel>
                <v-expansion-panel-title>
                  <v-icon class="mr-2">mdi-map-marker</v-icon>
                  {{ t('آدرس‌ها') }}
                </v-expansion-panel-title>
                <v-expansion-panel-text>
                  <!-- Main Address -->
                  <v-row class="tight-grid">
                    <v-col cols="12">
                      <v-card-subtitle class="section-subtitle mb-2">
                        <v-icon size="small" class="mr-1">mdi-home</v-icon>
                        {{ t('آدرس اصلی') }}
                      </v-card-subtitle>
                    </v-col>
                    <v-col cols="12">
                      <ProvinceDistrictNameSelect
                        v-model:province="draft.main_province"
                        v-model:district="draft.main_districts"
                        :province-label="t('ولایت اصلی')"
                        :district-label="t('ولسوالی اصلی')"
                        :mdProvince="6"
                        :mdDistrict="6"
                      />
                    </v-col>

                    <v-col cols="12" md="12">
                      <v-text-field
                        v-model="draft.main_village"
                        :label="t('قریه/ناحیه اصلی')"
                        variant="outlined"
                        density="compact"
                        rounded="lg"
                        clearable
                      />
                    </v-col>
                  </v-row>

                  <!-- Current Address -->
                  <v-row class="tight-grid">
                    <v-col cols="12">
                      <v-card-subtitle class="section-subtitle mb-2">
                        <v-icon size="small" class="mr-1">mdi-map-marker</v-icon>
                        {{ t('آدرس فعلی') }}
                      </v-card-subtitle>
                    </v-col>

                    <v-col cols="12">
                      <ProvinceDistrictNameSelect
                        v-model:province="draft.now_province"
                        v-model:district="draft.now_district"
                        :province-label="t('ولایت فعلی')"
                        :district-label="t('ولسوالی فعلی')"
                        :mdProvince="6"
                        :mdDistrict="6"
                      />
                    </v-col>

                    <v-col cols="12" md="12">
                      <v-text-field
                        v-model="draft.now_village"
                        :label="t('قریه/ناحیه فعلی')"
                        variant="outlined"
                        density="compact"
                        clearable
                        rounded="lg"
                      />
                    </v-col>
                  </v-row>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
          </v-card>

          <!-- Types + Time -->
          <v-card variant="outlined" class="sheet-card px-4 py-3 mt-3">
            <v-row class="tight-grid mt-2">
              <v-col cols="12" md="12">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('تفصیل') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-textarea
                    v-model="draft.tafseel"
                    variant="outlined"
                    density="compact"
                    clearable
                    rounded="lg"
                    rows="3"
                    auto-grow
                  />
                </v-card-text>
              </v-col>
              <v-col cols="12" md="12">
                <v-card-item class="sheet-title-row">
                  <v-card-subtitle class="sheet-title">{{
                    t('د اطلاع اړوند اسناد او شواهد')
                  }}</v-card-subtitle>
                </v-card-item>
                <!-- Documents -->
                <v-card variant="outlined" class="docs-field-row sheet-card px-4 py-3 mt-3">
                  <v-card-item class="sheet-title-row">
                    <v-icon class="mr-2" size="small">mdi-paperclip</v-icon>
                    <v-card-subtitle class="sheet-title">{{ t('اسناد / ضمایم') }}</v-card-subtitle>
                  </v-card-item>

                  <v-card-text>
                    <input
                      ref="fileInput"
                      type="file"
                      class="hidden-file"
                      rounded="lg"
                      multiple
                      @change="onFilesPicked($event)"
                    />

                    <v-row align="center" class="tight-grid">
                      <v-col cols="auto">
                        <v-btn
                          prepend-icon="mdi-tray-arrow-up"
                          color="primary"
                          @click="fileInput?.click()"
                        >
                          {{ t('آپلود فایل ها') }}
                        </v-btn>
                      </v-col>
                      <v-col>
                        <v-chip-group>
                          <v-chip
                            v-for="f in draft._newDocs"
                            :key="fileKey(f)"
                            size="small"
                            variant="outlined"
                            color="primary"
                            :title="f.name"
                            closable
                            @click:close="removeFile(f)"
                          >
                            {{ shortName(f.name) }}
                          </v-chip>
                        </v-chip-group>
                      </v-col>
                    </v-row>

                    <v-row v-if="progress > 0 && progress < 100" class="mt-2 tight-grid">
                      <v-col cols="12">
                        <v-progress-linear
                          :model-value="progress"
                          height="8"
                          color="primary"
                          rounded
                        />
                        <div class="text-caption mt-1 text-medium-emphasis">
                          {{ t('در حال آپلود') }}... {{ progress }}%
                        </div>
                      </v-col>
                    </v-row>
                    <v-row v-else-if="progress === 100" class="tight-grid">
                      <v-col cols="12">
                        <v-alert type="success" density="compact" class="mt-2">
                          {{ t('آپلود با موفقیت انجام شد') }}
                        </v-alert>
                      </v-col>
                    </v-row>

                    <v-row class="pa-3">
                      <v-col cols="12" md="12">
                        <v-text-field
                          v-model="draft.docs_note"
                          :label="t('نوت راجع به اسناد')"
                          density="compact"
                          variant="outlined"
                        />
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>
              <!-- information_degree -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{
                    t('درجه موثقیت اطلاع')
                  }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.information_degree"
                    variant="outlined"
                    density="compact"
                    clearable
                    suffix="%"
                    prepend-inner-icon="mdi-star"
                    rounded="lg"
                    class="cell"
                  />
                </v-card-text>
              </v-col>
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('سیاسی - نوع') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <SeyaseeTypeSelect
                    v-model="draft.syasi_info_type"
                    :label="t('سیاسی - نوع')"
                    :cols="12"
                    :md="12"
                    :disabled="!!draft.jenayee_info_type"
                  />
                </v-card-text>
              </v-col>

              <!-- jenayee_info_type -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('جنایی') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <JenayeeTypeSelect
                    v-model="draft.jenayee_info_type"
                    :label="t('جنایی')"
                    :cols="12"
                    :md="12"
                    :disabled="!!draft.syasi_info_type"
                  />
                </v-card-text>
              </v-col>

              <!-- <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('۵۶ فارمټ') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <LetryFormatSelect v-model="draft.letry_format_name" :label="t('۵۶ فارمټ')" />
                </v-card-text>
              </v-col> -->

              <!-- information_saved_date -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('تاریخ اطلاع') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <div class="dateinput">
                    <DateInput
                      v-model="draft.information_saved_date"
                      :rules="[rules.required(t('تاریخ اطلاع'))]"
                      :label="t('تاریخ اطلاع')"
                    />
                  </div>
                </v-card-text>
              </v-col>

              <!-- information_saved_hour -->
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('ساعت اطلاع') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-text-field
                    v-model="draft.information_saved_hour"
                    variant="outlined"
                    density="compact"
                    clearable
                    type="time"
                    step="60"
                    placeholder="HH:MM"
                    prepend-inner-icon="mdi-clock"
                    rounded="lg"
                    class="cell"
                  />
                </v-card-text>
              </v-col>

              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('کتگوری اطلاع') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <InformationCategorySelect
                    v-model="draft.information_category"
                    :disabled="loadingEdit"
                    class="cell"
                    rounded="lg"
                    variant="outlined"
                    density="compact"
                    clearable
                  />
                </v-card-text>
              </v-col>
              <v-col cols="12" md="4">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('درجه استعجالیت') }}</v-card-subtitle>
                </v-card-item>
                <v-select
                  v-model="draft.estijalyat_degree"
                  :items="[t('عاجل'), t('عادی')]"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  class="cell"
                  clearable
                  prepend-inner-icon="mdi-alert-circle-outline"
                />
              </v-col>
            </v-row>
          </v-card>
          <v-card variant="outlined" class="sheet-card px-4 py-3 mt-3">
            <v-card-item class="sheet-title-row">
              <v-icon class="mr-2" size="small">mdi-timer-outline</v-icon>
              <v-card-subtitle class="sheet-title">{{ t('هماهنګی') }}</v-card-subtitle>
              <!-- ✅ hamahangi_date -->
              <v-col cols="12" md="12">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('تاریخ هم‌آهنگی') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <div class="dateinput">
                    <DateInput
                      v-model="draft.hamahangi_date"
                      :label="t('تاریخ هم‌آهنگی')"
                      :disabled="submitting || isNotUrgent"
                    />
                  </div>
                </v-card-text>
              </v-col>

              <!-- ✅ organ -->
              <v-col cols="12" md="12">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('ترسره شوی همغږی') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <OrganSelect
                    v-model="draft.organ"
                    :label="t('ترسره شوی همغږی')"
                    :disabled="submitting || isNotUrgent"
                  />
                </v-card-text>
              </v-col>
            </v-card-item>
          </v-card>

          <!-- Long texts -->
          <v-card variant="outlined" class="sheet-card px-4 py-3 mt-3">
            <v-card-item class="sheet-title-row">
              <v-icon class="mr-2" size="small">mdi-text-long</v-icon>
              <v-card-subtitle class="sheet-title">{{ t('متن‌ها') }}</v-card-subtitle>
            </v-card-item>

            <v-row class="tight-grid">
              <v-col cols="12" md="12">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{
                    t('کود کارمند اطلاع گیرنده')
                  }}</v-card-subtitle>
                </v-card-item>
                <v-autocomplete
                  class="primary-select"
                  v-model="draft.office_employee_info_id"
                  :items="officeEmployeesItems"
                  item-title="code_number"
                  item-value="id"
                  :label="t('کود کارمند اطلاع گیرنده')"
                  density="compact"
                  variant="outlined"
                  clearable
                />
              </v-col>
              <v-col cols="12" md="12">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('نظر مدیر') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-textarea
                    v-model="draft.manager_comment"
                    variant="outlined"
                    density="compact"
                    clearable
                    rounded="lg"
                    rows="2"
                    auto-grow
                    :disabled="submitting || isNotUrgent"
                  />
                </v-card-text>
              </v-col>

              <v-col cols="12" md="12">
                <v-card-item>
                  <v-card-subtitle class="field-label">{{ t('ملاحظات') }}</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                  <v-textarea
                    v-model="draft.note"
                    variant="outlined"
                    density="compact"
                    clearable
                    rounded="lg"
                    rows="2"
                    auto-grow
                  />
                </v-card-text>
              </v-col>
            </v-row>
          </v-card>
        </v-card-text>

        <v-divider class="my-3" />

        <v-card-actions class="pa-0">
          <v-spacer />

          <!-- PDF + Word -->
          <v-btn variant="tonal" color="primary" class="mr-2" @click="onDownloadPdf">
            <v-icon start>mdi-file-pdf-box</v-icon>
            PDF
          </v-btn>

          <v-btn variant="tonal" color="primary" class="mr-2" @click="onDownloadWord">
            <v-icon start>mdi-file-word-box</v-icon>
            Word
          </v-btn>

          <v-btn
            variant="text"
            color="grey-darken-1"
            @click="openResetConfirm"
            :disabled="submitting"
          >
            <v-icon start>mdi-delete</v-icon>
            {{ t('پاک کردن فرم') }}
          </v-btn>
          <v-btn
            v-if="auth.can('inlineinfo.create')"
            color="primary"
            :loading="submitting"
            :disabled="submitting"
            size="large"
            @click="openReview"
          >
            <v-icon start>mdi-content-save</v-icon>
            {{ t('اضافه کردن اطلاعات') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-container>
</template>

<script setup>
  import { ref, reactive, computed, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import DateInput from '../../components/DateInput.vue';
  import OrganSelect from '@/components/OrganSelect.vue';
  import SeyaseeTypeSelect from '@/components/SeyaseeTypeSelect.vue';
  import JenayeeTypeSelect from '@/components/JenayeeTypeSelect.vue';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';
  import { useLoginRepository } from '../../stores/LoginRepository';
  import LetryFormatSelect from '@/components/LetryFormatSelect.vue';
  import InformationCategorySelect from '@/components/InformationCategorySelect.vue';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';
  import { useCalendar } from '../../../composables/useCalendar';
  const auth = useLoginRepository();
  const repo = useDatabaseRepository();
  const { t, locale } = useI18n();
  const { formatDisplay } = useCalendar();

  const valid = ref(false);
  const submitting = ref(false);
  const progress = ref(0);
  const form = ref(null);
  const fileInput = ref(null);
  const loadingEdit = ref(false);

  /* RTL handling */
  const RTL_LOCALES = ['fa', 'fa-AF', 'prs', 'ps', 'ar'];
  const isRtl = ref(RTL_LOCALES.includes(locale.value));
  watch(locale, (v) => {
    isRtl.value = RTL_LOCALES.includes(v);
  });

  /* toArray helper + dropdowns */
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

  /* ✅ NEW: madani halat items */
  const madaniHalatItems = computed(() => [t('مجرد'), t('متاحل')]);

  /* DRAFT — exactly the single-row editor fields */
  const baseDraft = () => ({
    // codes
    resource_id: null,
    office_employee_id: null,
    office_employee_tadabiri_id: null,
    office_employee_info_id: null,

    // identity / شهرت
    name: '',
    nick_name: '',
    father_name: '',
    grand_father_name: '',
    age: '',
    specefic_place: '',
    home_number: '',
    street_number: '',
    coordinates: '',
    madani_halat: '',
    job: '',

    // hamahangi
    hamahangi_date: '',
    maktop_number: '',
    organ: '',
    trust_percentage: '',
    resource_lasrasy: '',

    // addresses
    main_province: '',
    main_districts: '',
    main_village: '',
    now_province: '',
    now_district: '',
    now_village: '',

    // types & time
    syasi_info_type: '',
    letry_format_name: '',
    jenayee_info_type: '',
    information_saved_date: '',
    information_saved_hour: new Date().toTimeString().slice(0, 5),
    information_degree: '',
    estijalyat_degree: '',
    information_category: '',

    // texts
    tafseel: '',
    manager_comment: '',
    lead_drirection: '',
    note: '',
    docs_note: '',
    Province_addr: '',

    // files
    _newDocs: [],
  });
  const draft = reactive(baseDraft());
  const isNotUrgent = computed(() => String(draft.estijalyat_degree || '').trim() === t('عادی'));

  /* Rules (unchanged) */
  const rules = {
    required: (label) => (v) => (v && String(v).trim().length > 0) || `«${label}» ضروری است`,
    selectRequired: (label) => (v) =>
      (v !== null && v !== undefined && v !== '') || `لطفاً «${label}» را انتخاب کنید`,
    dateYMD: (label) => (v) =>
      !v || /^\d{4}-\d{2}-\d{2}$/.test(v) || `${label} باید به صورت YYYY-MM-DD باشد`,
    timeHM: (label) => (v) => !v || /^\d{2}:\d{2}$/.test(v) || `${label} باید به صورت HH:MM باشد`,
  };

  /* Helpers (unchanged) */
  function shortName(name = '') {
    if (name.length <= 22) return name;
    const dot = name.lastIndexOf('.');
    if (dot === -1) return name.slice(0, 18) + '…';
    const ext = name.slice(dot);
    const base = name.slice(0, 18);
    return `${base}…${ext}`;
  }
  function fileKey(f) {
    return `${f.name}__${f.size}__${f.lastModified}`;
  }

  function onFilesPicked(e) {
    const picked = Array.from(e.target?.files || []);
    draft._newDocs = picked;

    if (e?.target) e.target.value = '';
  }

  function removeFile(file) {
    const key = fileKey(file);
    draft._newDocs = draft._newDocs.filter((x) => fileKey(x) !== key);

    if (fileInput.value) fileInput.value.value = '';
  }

  function onDownloadPdf() {
    repo.DownloadInformationFormPdf(draft);
  }

  function onDownloadWord() {
    repo.DownloadInformationFormWord(draft, 'information_form.doc');
  }

  /* FormData (unchanged) */
  function appendFD(fd, k, v) {
    if (v == null) return;
    if (Array.isArray(v)) {
      v.forEach((val) => appendFD(fd, k + '[]', val));
      return;
    }
    if (typeof v === 'object') {
      fd.append(k, v.id ?? v.value ?? JSON.stringify(v));
      return;
    }
    fd.append(k, v);
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(obj)) appendFD(fd, k, v);
    return fd;
  }

  /* Actions (unchanged) */
  async function onCreate() {
    const ok = await form.value?.validate();
    if (!ok?.valid) {
      requestAnimationFrame(() => {
        const firstInvalid =
          document.querySelector('[aria-invalid="true"]') ||
          document.querySelector('.v-input--error');
        firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid?.focus?.();
      });
      return;
    }

    submitting.value = true;
    progress.value = 1;

    const fd = toFormDataSelective({ ...draft });
    (draft._newDocs || []).forEach((f) => fd.append('documents[]', f));

    try {
      const created = await repo
        .CreateInformation(fd, (pct) => (progress.value = pct))
        .catch(() => null);
      if (created?.id) onReset();
    } finally {
      submitting.value = false;
      setTimeout(() => (progress.value = 0), 800);
    }
  }
  function onReset() {
    Object.assign(draft, baseDraft());
    form.value?.resetValidation();
    if (fileInput.value) fileInput.value.value = '';
  }
  (async () => {
    await Promise.all([repo.GetOfficeEmployee?.()]).catch(() => {});
  })();

  /* pretty resource label */
  const formatResource = (r) => {
    if (!r) return '';
    const code = r.code_number ? String(r.code_number) : '';
    const id = r.id != null ? `#${r.id}` : '';
    return [code, id].filter(Boolean).join(' — ');
  };

  const reviewOpen = ref(false);

  function displayValue(v) {
    if (v == null) return '';
    if (typeof v === 'string') return v.trim();
    return String(v);
  }
  function formatReviewDate(v) {
    if (!v) return '';
    return formatDisplay(String(v).trim(), locale.value) || String(v);
  }
  function formatReviewDateTime(v) {
    if (!v) return '';

    const d = new Date(v);
    if (Number.isNaN(d.getTime())) return String(v);

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hour = String(d.getHours()).padStart(2, '0');
    const minute = String(d.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day} ${hour}:${minute}`;
  }
  const reviewRows = computed(() => {
    const emp = officeEmployeesItems.value.find((x) => x?.id === draft.office_employee_id);
    const infoEmp = officeEmployeesItems.value.find((x) => x?.id === draft.office_employee_info_id);

    return [
      {
        key: 'resource_id',
        label: t('کود منبع'),
        value: displayValue(draft.resource_id),
      },
      {
        key: 'office_employee_id',
        label: t('کود کارمند'),
        value: emp?.code_number || draft.office_employee_id,
      },
      {
        key: 'trust_percentage',
        label: t('فیصدی موثقیت'),
        value: displayValue(draft.trust_percentage),
      },
      {
        key: 'resource_lasrasy',
        label: t('د منبع لاسرسی'),
        value: displayValue(draft.resource_lasrasy),
      },

      { key: 'name', label: t('نام'), value: displayValue(draft.name) },
      { key: 'nick_name', label: t('نام مستعار'), value: displayValue(draft.nick_name) },
      { key: 'father_name', label: t('نام پدر'), value: displayValue(draft.father_name) },
      {
        key: 'grand_father_name',
        label: t('نام پدرکلان'),
        value: displayValue(draft.grand_father_name),
      },
      { key: 'age', label: t('عمر'), value: displayValue(draft.age) },
      { key: 'specefic_place', label: t('موقعیت مشخص'), value: displayValue(draft.specefic_place) },
      { key: 'home_number', label: t('شماره خانه'), value: displayValue(draft.home_number) },
      { key: 'street_number', label: t('شماره سرک'), value: displayValue(draft.street_number) },
      { key: 'coordinates', label: t('مختصات'), value: displayValue(draft.coordinates) },
      { key: 'madani_halat', label: t('حالت مدنی'), value: displayValue(draft.madani_halat) },
      { key: 'job', label: t('وظیفه'), value: displayValue(draft.job) },

      { key: 'main_province', label: t('ولایت اصلی'), value: displayValue(draft.main_province) },
      {
        key: 'main_districts',
        label: t('ولسوالی اصلی'),
        value: displayValue(draft.main_districts),
      },
      { key: 'main_village', label: t('قریه/ناحیه اصلی'), value: displayValue(draft.main_village) },

      { key: 'now_province', label: t('ولایت فعلی'), value: displayValue(draft.now_province) },
      { key: 'now_district', label: t('ولسوالی فعلی'), value: displayValue(draft.now_district) },
      { key: 'now_village', label: t('قریه/ناحیه فعلی'), value: displayValue(draft.now_village) },

      {
        key: 'syasi_info_type',
        label: t('سیاسی - نوع'),
        value: displayValue(draft.syasi_info_type),
      },
      { key: 'jenayee_info_type', label: t('جنایی'), value: displayValue(draft.jenayee_info_type) },
      {
        key: 'letry_format_name',
        label: t('۵۶ فارمټ'),
        value: displayValue(draft.letry_format_name),
      },

      {
        key: 'information_saved_date',
        label: t('تاریخ اطلاع'),
        value: formatReviewDate(draft.information_saved_date),
      },
      {
        key: 'information_saved_hour',
        label: t('ساعت اطلاع'),
        value: displayValue(draft.information_saved_hour),
      },
      {
        key: 'information_degree',
        label: t('درجه موثقیت اطلاع'),
        value: displayValue(draft.information_degree),
      },
      {
        key: 'information_category',
        label: t('کتگوری اطلاع'),
        value: displayValue(draft.information_category),
      },
      {
        key: 'estijalyat_degree',
        label: t('درجه استعجالیت'),
        value: displayValue(draft.estijalyat_degree),
      },

      { key: 'tafseel', label: t('تفصیل'), value: displayValue(draft.tafseel) },
      { key: 'manager_comment', label: t('نظر مدیر'), value: displayValue(draft.manager_comment) },
      { key: 'note', label: t('ملاحظات'), value: displayValue(draft.note) },
      { key: 'docs_note', label: t('نوت راجع به اسناد'), value: displayValue(draft.docs_note) },

      {
        key: 'hamahangi_date',
        label: t('تاریخ هم‌آهنگی'),
        value: formatReviewDate(draft.hamahangi_date),
      },
      { key: 'organ', label: t('ترسره شوی همغږی'), value: displayValue(draft.organ) },

      {
        key: 'office_employee_info_id',
        label: t('کود کارمند اطلاع گیرنده'),
        value: infoEmp?.code_number || draft.office_employee_info_id,
      },
    ];
  });

  async function openReview() {
    const ok = await form.value?.validate();
    if (!ok?.valid) {
      requestAnimationFrame(() => {
        const firstInvalid =
          document.querySelector('[aria-invalid="true"]') ||
          document.querySelector('.v-input--error');
        firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid?.focus?.();
      });
      return;
    }

    reviewOpen.value = true;
  }

  const confirmSubmitOpen = ref(false);
  const confirmResetOpen = ref(false);

  function openSubmitConfirm() {
    reviewOpen.value = false;
    confirmSubmitOpen.value = true;
  }

  async function doConfirmedSubmit() {
    confirmSubmitOpen.value = false;
    await onCreate();
  }

  function openResetConfirm() {
    confirmResetOpen.value = true;
  }

  function doConfirmedReset() {
    confirmResetOpen.value = false;
    onReset();
  }

  async function confirmSubmit() {
    reviewOpen.value = false;
    await onCreate();
  }

  watch(
    () => draft.syasi_info_type,
    (val) => {
      if (val) draft.jenayee_info_type = '';
    }
  );

  watch(
    () => draft.jenayee_info_type,
    (val) => {
      if (val) draft.syasi_info_type = '';
    }
  );

  watch(
    () => draft.estijalyat_degree,
    (val) => {
      const v = String(val || '').trim();
      if (v === t('عاجل')) {
        draft.hamahangi_date = '';
        draft.organ = '';
      }
    }
  );

  function resourceTitle(r) {
    if (!r) return '';
    const code = r.code_number ?? '';
    const name = r.name ?? '';
    return [code, name].filter(Boolean).join(' - ');
  }

  function onResourceSelected(r) {
    if (!r) return;

    draft.resource_id = r.id ?? null;

    draft.trust_percentage =
      r.trust_percentage ?? r.trustPercentage ?? r.trust_percent ?? r.trustPercent ?? '';

    const empId =
      r.office_employee?.id ??
      r.officeEmployee?.id ??
      r.office_employee_id ??
      r.employee_id ??
      null;

    if (empId) {
      draft.office_employee_id = empId;
    } else {
      draft.office_employee_id = null;
    }
  }

  function onResourceCleared() {
    draft.resource_id = null;
    draft.office_employee_id = null;
    draft.trust_percentage = '';
  }

  usePageRefresh('/app/inlineinfo', async () => {
    onReset();

    await Promise.all([repo.GetOfficeEmployee?.()]).catch(() => {});
  });
</script>

<style scoped>
  .form-card {
    border-radius: 16px;
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
  }
  .form-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
  }

  /* Let inputs expand within columns (no fixed min/max) */
  .cell {
    width: 100%;
  }

  /* Labels */
  .field-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #475569;
    opacity: 1;
  }
  .section-subtitle {
    font-size: 0.95rem;
    font-weight: 600;
    color: #334155;
    opacity: 1;
  }

  /* ✅ SHEETS */
  .sheet-card {
    border-radius: 14px;
  }
  .sheet-title-row {
    padding: 2px 0 6px 0;
  }
  .sheet-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    opacity: 1;
  }

  /* Docs row */
  .docs-field-row {
    display: block;
    gap: 4px;
    border: 1px dashed #9e9e9e;
    border-radius: 8px;
    padding: 6px 8px;
  }

  /* Hidden file input */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .dateinput {
    width: 100% !important;
  }
  /* Tighter grid gutters */
  .tight-grid {
    --v-gutter-x: 8px;
    --v-gutter-y: 8px;
  }

  /* Compact paddings */
  :deep(.v-card-item) {
    padding: 4px 0 2px 0;
  }
  :deep(.v-card-text) {
    padding: 4px 0 0 0;
  }

  /* RTL polishing */
  :deep([dir='rtl'] .v-field__input) {
    direction: rtl;
    text-align: right;
  }
  :deep([dir='rtl'] .v-field-label),
  :deep([dir='rtl'] .v-label),
  :deep([dir='rtl'] .v-input__details) {
    text-align: right;
  }
  :deep(.v-field__input) {
    padding-inline-start: 10px !important;
    padding-inline-end: 10px !important;
  }

  @media (max-width: 960px) {
    .form-card {
      padding: 16px;
    }
    .form-title {
      font-size: 1.3rem;
    }
  }

  /* ✅ only vertical scroll */
  .max-h-form {
    max-height: calc(87.5vh - 220px);
    overflow-y: auto;
    overflow-x: hidden;
    overscroll-behavior: contain;
    scroll-behavior: smooth;
    padding-inline-end: 6px;
  }

  /* ✅ extra safety: if something is wider, clip it */
  .pretty-scroll {
    overflow-x: hidden;
  }

  /* ✅ prevent long content from forcing width */
  :deep(.v-field__input),
  :deep(.v-textarea .v-field__input),
  :deep(.v-autocomplete),
  :deep(.v-select),
  :deep(.v-text-field) {
    max-width: 100%;
  }

  /* ✅ chips can cause horizontal overflow sometimes */
  :deep(.v-chip-group) {
    max-width: 100%;
    overflow-x: hidden;
    flex-wrap: wrap;
  }

  /* ✅ Pretty scrollbar (Chrome/Edge/Safari) */
  .pretty-scroll::-webkit-scrollbar {
    width: 10px;
    height: 10px;
  }
  :deep(.v-dialog .v-card-text) {
    font-size: 0.95rem;
    color: #334155;
  }
  .pretty-scroll::-webkit-scrollbar-track {
    background: rgba(148, 163, 184, 0.18);
    border-radius: 999px;
  }

  .pretty-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, rgba(59, 130, 246, 0.65), rgba(99, 102, 241, 0.65));
    border-radius: 999px;
    border: 2px solid rgba(248, 250, 252, 0.8);
  }

  .pretty-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, rgba(59, 130, 246, 0.85), rgba(99, 102, 241, 0.85));
  }

  /* ✅ Firefox */
  .pretty-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(12, 111, 112, 0.54) rgba(148, 163, 184, 0.18);
  }

  /* Optional: nicer feel on touchpads */
  .pretty-scroll {
    -webkit-overflow-scrolling: touch;
  }
</style>
