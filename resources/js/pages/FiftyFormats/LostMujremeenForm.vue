<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش مجرم گم شده') : t('ایجاد مجرم گم شده جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ مجرم گم شده موجود')
          : t('ثبت یک ریکارډ مجرم گم شده جدید در سیستم')
      "
    >
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
        <v-card class="simple-form-card" elevation="1">
          <v-row>
            <!-- Picture -->
            <v-col cols="12" md="3">
              <div class="d-flex flex-column align-center gap-2">
                <div class="text-subtitle-2 font-weight-medium mb-2">
                  {{ t('تصویر') }}
                </div>

                <div class="pic-wrap-simple">
                  <input
                    ref="pictureInput"
                    class="hidden-file"
                    type="file"
                    accept="image/*"
                    @change="onPickPicture"
                  />
                  <v-avatar
                    size="88"
                    class="mb-2 cursor-pointer primary-avatar"
                    :key="form.previewUrl || form.picture || 'no-picture'"
                    @click="triggerPicture"
                  >
                    <v-img
                      v-if="form.previewUrl || form.picture"
                      :src="normalizePicture(form.previewUrl || form.picture)"
                      :alt="t('عکس')"
                      cover
                    />
                    <v-icon v-else size="40">mdi-account</v-icon>
                  </v-avatar>

                  <div class="d-flex flex-column align-center gap-1">
                    <v-btn
                      size="small"
                      color="primary"
                      variant="tonal"
                      class="text-none rounded-pill soft-btn my-2"
                      @click.stop="triggerPicture"
                    >
                      <v-icon start size="22">mdi-pencil</v-icon>
                      {{ t('تغییر عکس') }}
                    </v-btn>

                    <v-btn
                      v-if="form.previewUrl || form.picture"
                      size="small"
                      color="secondary"
                      variant="tonal"
                      class="text-none rounded-pill soft-btn"
                      @click.stop="openPreview(form.previewUrl || form.picture)"
                    >
                      <v-icon start size="22">mdi-eye</v-icon>
                      {{ t('نمایش') }}
                    </v-btn>
                  </div>
                </div>
              </div>
            </v-col>

            <!-- Identity + addresses -->
            <v-col cols="12" md="9">
              <!-- Identity -->
              <div class="section-header">
                <span class="section-title">
                  {{ isEdit ? t('ویرایش ریکارډ مجرم گم شده') : t('ایجاد ریکارډ مجرم گم شده') }}
                </span>
              </div>

              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.nickname"
                    :label="t('نام مستعار')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('nickname')"
                    :error-messages="getErrorMessages('nickname')"
                  />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.name"
                    :label="t('نام *')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.required]"
                    :error="hasError('name')"
                    :error-messages="getErrorMessages('name')"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.father_name"
                    :label="t('نام پدر')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('father_name')"
                    :error-messages="getErrorMessages('father_name')"
                  />
                </v-col>
              </v-row>

              <!-- Main address -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس اصلی') }}</span>
              </div>
              <v-row>
                <v-col cols="12" md="12">
                  <ProvinceDistrictNameSelect
                    v-model:province="form.main_province"
                    v-model:district="form.main_district"
                    :province-label="t('ولایت اصلی')"
                    :district-label="t('ولسوالی اصلی')"
                    :mdProvince="6"
                    :mdDistrict="6"
                  />
                </v-col>
                <v-col cols="12" md="12">
                  <v-text-field
                    v-model="form.main_village"
                    :label="t('قریه/ناحیه اصلی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('main_village')"
                    :error-messages="getErrorMessages('main_village')"
                  />
                </v-col>
              </v-row>

              <!-- Now address -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس فعلی') }}</span>
              </div>
              <v-row>
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
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.now_village"
                    :label="t('قریه/ناحیه فعلی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('now_village')"
                    :error-messages="getErrorMessages('now_village')"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.now_specific_place"
                    :label="t('موقعیت فعلی مشخص')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('now_specific_place')"
                    :error-messages="getErrorMessages('now_specific_place')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.now_street_number"
                    :label="t('سرک نمبر فعلی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('now_street_number')"
                    :error-messages="getErrorMessages('now_street_number')"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.now_home_number"
                    :label="t('نمبر خانه فعلی')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('now_home_number')"
                    :error-messages="getErrorMessages('now_home_number')"
                  />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.coordinates"
                    :label="t('مختصات')"
                    variant="outlined"
                    density="compact"
                    :rules="[rules.coordinatesOptional]"
                    :error="hasError('coordinates')"
                    :error-messages="getErrorMessages('coordinates')"
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <v-row>
            <!-- General location -->
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.province"
                :label="t('ولایت احتمالی')"
                variant="outlined"
                density="compact"
                :error="hasError('province')"
                :error-messages="getErrorMessages('province')"
              />
            </v-col>
            <v-col cols="12" md="4">
              <FormatZoneAutocomplete
                v-model="form.zone"
                :label="t('آمریت احتمالی')"
                :disabled="saving"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.district"
                :label="t('ولسوالی احتمالی')"
                variant="outlined"
                density="compact"
                :error="hasError('district')"
                :error-messages="getErrorMessages('district')"
              />
            </v-col>

            <!-- Jobs / crime / lost reason -->
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.old_job"
                :label="t('وظیفه قبلی')"
                variant="outlined"
                density="compact"
                :error="hasError('old_job')"
                :error-messages="getErrorMessages('old_job')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.jorm"
                :label="t('جرم')"
                variant="outlined"
                density="compact"
                :error="hasError('jorm')"
                :error-messages="getErrorMessages('jorm')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.history_jorm"
                :label="t('تاریخچه جرم')"
                variant="outlined"
                density="compact"
                :error="hasError('history_jorm')"
                :error-messages="getErrorMessages('history_jorm')"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.lost_reason"
                :label="t('دلیل مفقودی')"
                variant="outlined"
                density="compact"
                :error="hasError('lost_reason')"
                :error-messages="getErrorMessages('lost_reason')"
              />
            </v-col>
            <!-- Phone + Note -->
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                variant="outlined"
                density="compact"
                :rules="[rules.phone]"
                :error="hasError('phone_number')"
                :error-messages="getErrorMessages('phone_number')"
              />
            </v-col>
            <!-- Province addr + coordinates -->
            <!-- <v-col cols="12" md="4">
              <v-text-field
                v-model="form.Province_addr"
                :label="t('ولایت مربوطه')"
                variant="outlined"
                density="compact"
                :error="hasError('Province_addr')"
                :error-messages="getErrorMessages('Province_addr')"
              />
            </v-col> -->

            <!-- Resource mapping -->
            <v-col cols="12" md="4">
              <ResourceRemoteAutocomplete
                v-model="form.resource_id"
                :label="t('کود منبع')"
                density="compact"
                variant="outlined"
                :rules="[rules.required]"
                :item-title="resourceTitle"
                @selected="onResourceSelected"
                @cleared="onResourceCleared"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.resource_phone"
                :label="t('شماره منبع')"
                variant="outlined"
                density="compact"
                readonly
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.note"
                :label="t('ملاحظات')"
                variant="outlined"
                density="compact"
                :error="hasError('note')"
                :error-messages="getErrorMessages('note')"
              />
            </v-col>
          </v-row>
          <v-divider class="my-4" />

          <div class="docs-header d-flex align-center justify-space-between">
            <div class="section-header mb-0">
              <span class="section-title">{{ t('اسناد') }}</span>
              <v-chip v-if="hasAnyDocs" class="ml-2" size="small" color="primary" variant="tonal">
                {{ (existingDocs.length || 0) + (newDocs.length || 0) }}
              </v-chip>
            </div>

            <v-tooltip :text="t('دانلود تمام اسناد (ZIP)')" location="top">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  color="green"
                  variant="outlined"
                  class="text-none rounded-pill secondary-action-btn"
                  prepend-icon="mdi-zip-box"
                  :disabled="!isEdit || !existingDocs.length || downloadingZip"
                  :loading="downloadingZip"
                  @click="downloadAllDocsZip"
                >
                  {{ t('دانلود ZIP') }}
                </v-btn>
              </template>
            </v-tooltip>
          </div>

          <v-row class="mt-2">
            <v-col cols="12">
              <v-file-input
                v-model="newDocsInput"
                multiple
                show-size
                variant="outlined"
                density="compact"
                prepend-icon="mdi-paperclip"
                :label="t('اسناد جدید را انتخاب کنید')"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp"
                @update:model-value="onNewDocsPicked"
              />
              <div class="text-caption text-medium-emphasis mt-2">
                <!-- {{ t('نکته') }}: {{ t('اسناد جدید بعد از ذخیره ثبت می‌شوند') }} -->
              </div>
            </v-col>

            <!-- existing docs -->
            <v-col cols="12" v-if="existingDocs.length">
              <div class="docs-block">
                <div class="docs-title">{{ t('اسناد موجود') }}</div>

                <div class="docs-chips">
                  <v-chip
                    v-for="doc in existingDocs"
                    :key="doc.id"
                    class="ma-1"
                    color="primary"
                    variant="tonal"
                    label
                  >
                    <v-icon start size="16">mdi-file</v-icon>
                    <span class="chip-text">{{
                      doc.file_name || doc.name || 'doc_' + doc.id
                    }}</span>

                    <template #append>
                      <v-tooltip :text="t('حذف از ریکارډ (بعد از ذخیره)')" location="top">
                        <template #activator="{ props: removeProps }">
                          <v-icon
                            v-bind="removeProps"
                            size="16"
                            class="ml-2 cursor-pointer"
                            color="red"
                            @click.stop="removeExistingDoc(doc)"
                          >
                            mdi-close-circle
                          </v-icon>
                        </template>
                      </v-tooltip>
                    </template>
                  </v-chip>
                </div>
              </div>
            </v-col>

            <!-- new docs -->
            <v-col cols="12" v-if="newDocs.length">
              <div class="docs-block docs-block--new">
                <div class="docs-title">{{ t('اسناد جدید (در انتظار ذخیره)') }}</div>

                <div class="docs-chips">
                  <v-chip
                    v-for="(f, idx) in newDocs"
                    :key="idx"
                    class="ma-1"
                    color="green"
                    variant="tonal"
                    label
                  >
                    <v-icon start size="16">mdi-file-plus</v-icon>
                    <span class="chip-text">{{ f?.name || 'file_' + idx }}</span>

                    <template #append>
                      <v-tooltip :text="t('حذف از لیست')" location="top">
                        <template #activator="{ props: removeProps }">
                          <v-icon
                            v-bind="removeProps"
                            size="16"
                            class="ml-2 cursor-pointer"
                            color="red"
                            @click.stop="removeNewDoc(idx)"
                          >
                            mdi-close-circle
                          </v-icon>
                        </template>
                      </v-tooltip>
                    </template>
                  </v-chip>
                </div>
              </div>
            </v-col>
          </v-row>

          <!-- Actions -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2 flex-wrap">
              <!-- ✅ Simple Export (NO image / NO app headers) -->
              <v-btn
                color="primary"
                variant="tonal"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-printer"
                @click="printForm"
              >
                {{ t('چاپ') }}
              </v-btn>

              <v-tooltip :text="t('PDF ساده (بدون عکس)')" location="top">
                <template #activator="{ props }">
                  <v-btn
                    v-bind="props"
                    variant="tonal"
                    color="primary"
                    class="text-none rounded-pill"
                    prepend-icon="mdi-file-pdf-box"
                    @click="downloadPdf"
                  >
                    {{ t('PDF') }}
                  </v-btn>
                </template>
              </v-tooltip>

              <v-tooltip :text="t('Word ساده (بدون عکس)')" location="top">
                <template #activator="{ props }">
                  <v-btn
                    v-bind="props"
                    variant="tonal"
                    color="primary"
                    class="text-none rounded-pill"
                    prepend-icon="mdi-file-word-box"
                    @click="downloadWord"
                  >
                    {{ t('Word') }}
                  </v-btn>
                </template>
              </v-tooltip>

              <v-btn
                color="primary"
                variant="elevated"
                class="text-none rounded-pill primary-action-btn"
                :loading="saving"
                type="submit"
                :prepend-icon="isEdit ? 'mdi-content-save-edit-outline' : 'mdi-plus-circle-outline'"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ثبت') }}
              </v-btn>

              <v-btn
                color="warning"
                variant="outlined"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-broom"
                @click="resetForm"
              >
                {{ t('پاک کردن فرم') }}
              </v-btn>
            </v-col>
          </v-row>
        </v-card>
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

      <!-- Error Snackbar -->
      <v-snackbar v-model="showError" color="error" timeout="5000">
        {{ errorMessage }}
        <template #actions>
          <v-btn variant="text" @click="showError = false">
            {{ t('بستن') }}
          </v-btn>
        </template>
      </v-snackbar>

      <!-- Success Snackbar -->
      <v-snackbar v-model="showSuccess" color="success" timeout="3000">
        {{ successMessage }}
        <template #actions>
          <v-btn variant="text" @click="showSuccess = false">
            {{ t('بستن') }}
          </v-btn>
        </template>
      </v-snackbar>
    </v-container>
  </v-container>
</template>

<script setup>
  import { ref, computed, onMounted, watch, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

  import AppBar from '@/components/AppBar.vue';
  import { useMawadRepository } from '@/stores/MawadRepository';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';
  import { printBlankForm } from '@/utils/printBlankForm';

  const repo = useMawadRepository();
  const route = useRoute();
  const router = useRouter();

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* MODE */
  const isEdit = computed(() => !!route.params.id);

  /* FORM / STATE */
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

  /* BACKEND ERRORS */
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

  /* FORM MODEL */
  const blankForm = () => ({
    id: null,

    // media
    picture: null,
    previewUrl: null,

    // identity
    name: '',
    nickname: '',
    father_name: '',

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

    // general location
    province: '',
    zone: '',
    district: '',

    // extra
    old_job: '',
    jorm: '',
    history_jorm: '',
    lost_reason: '',
    Province_addr: '',
    coordinates: '',

    // resource
    resource_code: '',
    resource_phone: '',
    resource_id: null,

    // contact & note
    phone_number: '',
    note: '',
    //
  });

  const form = ref(blankForm());

  // docs
  /* =========================
   ✅ DOCUMENTS STATE
========================= */
  const existingDocs = ref([]); // from API: [{id,file_name,file_path,url?...}]
  const removed_document_ids = ref([]); // int[]
  const newDocs = ref([]); // File[]
  const newDocsInput = ref([]); // v-file-input model
  const downloadingZip = ref(false);

  const hasAnyDocs = computed(() => {
    return (existingDocs.value?.length || 0) > 0 || (newDocs.value?.length || 0) > 0;
  });

  function normalizeDocsFromApi(x) {
    const docs = x?.documents;
    if (Array.isArray(docs)) return docs;
    if (Array.isArray(docs?.data)) return docs.data;
    return [];
  }

  function onNewDocsPicked(files) {
    const list = Array.isArray(files) ? files : [];
    const current = newDocs.value || [];
    const merged = [...current];

    for (const f of list) {
      if (!f) continue;
      const exists = merged.some((x) => x?.name === f.name && x?.size === f.size);
      if (!exists) merged.push(f);
    }

    newDocs.value = merged;
    newDocsInput.value = [];
  }

  function removeNewDoc(index) {
    const arr = [...(newDocs.value || [])];
    arr.splice(index, 1);
    newDocs.value = arr;
  }

  function removeExistingDoc(doc) {
    if (!doc?.id) return;
    if (!removed_document_ids.value.includes(doc.id)) {
      removed_document_ids.value.push(doc.id);
    }
    existingDocs.value = (existingDocs.value || []).filter((d) => d.id !== doc.id);
  }

  function appendDocsToFormData(fd) {
    for (const f of newDocs.value || []) {
      if (!f) continue;
      fd.append('_newDocs[]', f);
    }
    for (const id of removed_document_ids.value || []) {
      if (!id) continue;
      fd.append('removed_document_ids[]', String(id));
    }
  }

  async function downloadAllDocsZip() {
    if (!isEdit.value) return;
    const id = form.value.id;
    if (!id) return;

    const name = `lost_mujremeen_${id}`;
    try {
      downloadingZip.value = true;

      // ✅ repo method names - choose one
      if (typeof repo.DownloadLostMujremeenZip === 'function') {
        await repo.DownloadLostMujremeenZip(id, name);
      } else if (typeof repo.DownloadLostMujremeenZip === 'function') {
        await repo.DownloadLostMujremeenZip(id, name);
      } else {
        showErrorMessage(t('دانلود ZIP برای این ماډل هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دانلود اسناد'));
    } finally {
      downloadingZip.value = false;
    }
  }

  /* RULES */
  const rules = {
    required: (v) => !!String(v ?? '').trim() || t('این فیلد الزامی است'),
    phone: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      if (!/^[0-9]+$/.test(s)) return t('شماره تماس فقط عدد باشد');
      if (s.length !== 10) return t('شماره تماس باید ۱۰ رقم باشد');
      return true;
    },
    coordinatesOptional: (v) => {
      const s = String(v ?? '').trim();
      if (!s) return true;
      if (!/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/.test(s)) return t('مختصات درست نیست');
      return true;
    },
  };

  /* PICTURE */
  const pictureInput = ref(null);
  const pictureFile = ref(null);
  const pictureChanged = ref(false);

  const previewOpen = ref(false);
  const previewSrc = ref(null);

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
      form.value.previewUrl = form.value.picture || null;
    }
    if (e.target) e.target.value = '';
  }

  function normalizePicture(src) {
    if (!src) return null;
    let clean = String(src).trim();

    if (clean.startsWith('blob:') || clean.startsWith('data:')) return clean;

    clean = clean.replace(/\\\//g, '/');
    if (clean.startsWith('http://') || clean.startsWith('https://')) return clean;
    if (clean.startsWith('/')) return clean;

    return '/' + clean.replace(/^\/+/, '');
  }

  function openPreview(src) {
    const base = src || form.value.previewUrl || form.value.picture;
    const normalized = normalizePicture(base);
    previewSrc.value = normalized;
    if (previewSrc.value) previewOpen.value = true;
  }

  function resourceTitle(r) {
    if (!r) return '';
    const code = r.code_number ?? '';
    const name = r.name ?? '';
    return [code, name].filter(Boolean).join(' - ');
  }

  function onResourceSelected(r) {
    if (!r) return;
    form.value.resource_id = r.id ?? null;
    form.value.resource_code = r.code_number ?? '';
    form.value.resource_phone = r.phone_number ?? '';
  }

  function onResourceCleared() {
    form.value.resource_id = null;
    form.value.resource_code = '';
    form.value.resource_phone_number = '';
  }

  /* MAP FROM API */
  function mapFromApi(payload = {}) {
    const x = payload.data ?? payload;
    const b = blankForm();

    b.id = x.id ?? null;

    const fullUrl = x.picture ?? null;
    b.picture = fullUrl;
    b.previewUrl = fullUrl;

    b.name = x.name ?? '';
    b.nickname = x.nickname ?? '';
    b.father_name = x.father_name ?? '';

    b.main_province = x.main_province ?? '';
    b.main_district = x.main_district ?? '';
    b.main_village = x.main_village ?? '';
    b.now_province = x.now_province ?? '';
    b.now_district = x.now_district ?? '';
    b.now_village = x.now_village ?? '';
    b.now_specific_place = x.now_specific_place ?? '';
    b.now_street_number = x.now_street_number ?? '';
    b.now_home_number = x.now_home_number ?? '';

    b.province = x.province ?? '';
    b.zone = x.zone ?? '';
    b.district = x.district ?? '';

    b.old_job = x.old_job ?? '';
    b.jorm = x.jorm ?? '';
    b.history_jorm = x.history_jorm ?? '';
    b.lost_reason = x.lost_reason ?? '';
    b.Province_addr = x.Province_addr ?? '';
    b.coordinates = x.coordinates ?? '';

    const r = x.resource || {};
    b.resource_code = x.resource_code ?? r.code_number ?? '';
    b.resource_phone = x.resource_phone ?? r.phone_number ?? '';
    b.resource_id = x.resource_id ?? r.id ?? null;

    b.phone_number = x.phone_number ?? '';
    b.note = x.note ?? '';

    // docs
    existingDocs.value = normalizeDocsFromApi(x);
    removed_document_ids.value = [];
    newDocs.value = [];
    newDocsInput.value = [];

    return b;
  }

  async function loadRecord() {
    const id = route.params.id;
    if (!id) {
      form.value = blankForm();
      pictureFile.value = null;
      pictureChanged.value = false;
      return;
    }
    try {
      await repo.FetchLostMujremeenData?.(id);
      const single = Array.isArray(repo.LostMujremeen)
        ? repo.LostMujremeen[0]
        : repo.LostMujremeen || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };
      pictureFile.value = null;
      pictureChanged.value = false;
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات مجرم گم شده'));
    }
  }

  /* FORM DATA HELPERS */
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

    const fd = toFormData(payload);

    if (pictureFile.value) fd.append('picture', pictureFile.value);

    appendDocsToFormData(fd);
    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    delete payload.previewUrl;
    delete payload.picture;

    const fd = toFormData(payload);

    if (pictureChanged.value && pictureFile.value) fd.append('picture', pictureFile.value);

    appendDocsToFormData(fd);
    return fd;
  }

  /* SCROLL TO FIRST INVALID */
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

  /* =========================
   ✅ SIMPLE EXPORT (PDF/Word)
   - NO images
   - NO app headers/top bar
   - Clean RTL table + signature lines
========================= */
  function escapeHtml(s) {
    return String(s ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function buildExportHtml() {
    const rid = form.value.id ? `#${form.value.id}` : t('بدون شماره');

    const rows = [
      [t('نام'), form.value.name],
      [t('نام مستعار'), form.value.nickname],
      [t('نام پدر'), form.value.father_name],

      [t('ولایت اصلی'), form.value.main_province],
      [t('ولسوالی اصلی'), form.value.main_district],
      [t('قریه/ناحیه اصلی'), form.value.main_village],

      [t('ولایت فعلی'), form.value.now_province],
      [t('ولسوالی فعلی'), form.value.now_district],
      [t('قریه/ناحیه فعلی'), form.value.now_village],
      [t('موقعیت فعلی مشخص'), form.value.now_specific_place],
      [t('سرک نمبر فعلی'), form.value.now_street_number],
      [t('نمبر خانه فعلی'), form.value.now_home_number],

      [t('ولایت'), form.value.province],
      [t('آمریت'), form.value.zone],
      [t('ولسوالی'), form.value.district],

      [t('وظیفه قبلی'), form.value.old_job],
      [t('جرم'), form.value.jorm],
      [t('تاریخچه جرم'), form.value.history_jorm],
      [t('دلیل مفقودی'), form.value.lost_reason],

      [t('ولایت مربوطه'), form.value.Province_addr],
      [t('مختصات'), form.value.coordinates],

      [t('کود منبع'), form.value.resource_code],
      [t('شماره منبع'), form.value.resource_phone],

      [t('شماره تماس'), form.value.phone_number],
      [t('ملاحظات'), form.value.note],
    ];

    return `
<!doctype html>
<html dir="rtl" lang="fa">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${escapeHtml(t('فورم مجرم گم شده'))}</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 18px; color: #111; }
    h2 { margin: 0 0 12px; font-size: 18px; }
    .meta { font-size: 12px; color: #444; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    td { border: 1px solid #ddd; padding: 8px; vertical-align: top; font-size: 12px; }
    td.label { width: 32%; font-weight: 700; background: #fafafa; }
    .sig { margin-top: 28px; display: flex; gap: 16px; }
    .sig .box { flex: 1; border-top: 1px solid #333; padding-top: 6px; font-size: 12px; }
    @media print { body { padding: 0; } }
  </style>
</head>
<body>
  <h2>${escapeHtml(t('فورم مجرم گم شده'))}</h2>
  <div class="meta">${escapeHtml(t('شماره ریکارډ'))}: ${escapeHtml(rid)}</div>

  <table>
    ${rows
      .map(([k, v]) => {
        const val = String(v ?? '').trim();
        return `
          <tr>
            <td class="label">${escapeHtml(k)}</td>
            <td>${escapeHtml(val || '-')}</td>
          </tr>
        `;
      })
      .join('')}
  </table>

  <div class="sig">
    <div class="box">${escapeHtml(t('امضا مسئول'))}</div>
    <div class="box">${escapeHtml(t('امضا ثبت کننده'))}</div>
    <div class="box">${escapeHtml(t('تاریخ'))}: ____________</div>
  </div>
</body>
</html>
  `.trim();
  }

  /* PRINT BLANK FORM (empty fields, same design, one A4 page) */
  function printForm(e) {
    printBlankForm(e, { onError: showErrorMessage });
  }

  function downloadPdf() {
    const html = buildExportHtml();
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.open();
    w.document.write(html);
    w.document.close();
    w.focus();
    setTimeout(() => w.print(), 250);
  }

  function downloadWord() {
    const html = buildExportHtml();
    const blob = new Blob([html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const namePart = form.value.id ? `lost_mujremeen_${form.value.id}` : 'lost_mujremeen_blank';
    const a = document.createElement('a');
    a.href = url;
    a.download = `${namePart}.doc`;
    document.body.appendChild(a);
    a.click();
    a.remove();

    setTimeout(() => URL.revokeObjectURL(url), 2000);
  }

  /* SUBMIT */
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

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateLostMujremeen?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/lostmujremeens').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateLostMujremeen?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/lostmujremeens').catch(() => {});
        } else {
          showErrorMessage(t('ذخیرهٔ تغییرات ناکام شد'));
        }
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در ذخیره اطلاعات'));
    } finally {
      saving.value = false;
    }
  }

  /* RESET & NAVIGATION */
  function resetForm() {
    if (form.value.previewUrl && form.value.previewUrl.startsWith('blob:')) {
      URL.revokeObjectURL(form.value.previewUrl);
    }

    if (isEdit.value) {
      loadRecord();
    } else {
      form.value = blankForm();
      pictureFile.value = null;
      pictureChanged.value = false;
    }

    clearBackendErrors();
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/lostmujremeens');
  }

  /* INIT */
  onMounted(async () => {
    if (isEdit.value) {
      await loadRecord();
    }
  });
</script>

<style scoped>
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }

  /* main card */
  .simple-form-card {
    padding: 16px 16px 20px;
    border-radius: 12px;
  }

  /* sections */
  .section-header {
    display: flex;
    align-items: center;
    padding-block: 2px;
  }
  .section-title {
    font-size: 0.9rem;
    font-weight: 600;
    opacity: 0.8;
  }

  /* picture */
  .pic-wrap-simple {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .cursor-pointer {
    cursor: pointer;
  }
  .primary-avatar {
    border: 2px solid rgba(33, 150, 243, 0.25);
  }

  /* small gap utility */
  .gap-2 {
    gap: 8px;
  }

  /* slightly tighter fields */
  :deep(.v-field--variant-outlined .v-field__input) {
    padding-top: 4px;
    padding-bottom: 4px;
    min-height: 36px;
  }

  /* prettier buttons */
  .soft-btn {
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
  }

  .primary-action-btn {
    padding-inline: 18px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.25);
  }

  .secondary-action-btn {
    padding-inline: 14px;
    font-weight: 500;
  }

  .ga-2 {
    gap: 8px;
  }
  .docs-header {
    padding: 6px 0;
  }

  .docs-block {
    border: 1px dashed rgba(0, 0, 0, 0.18);
    border-radius: 14px;
    padding: 12px 12px 8px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.55));
  }
  .docs-block--new {
    border-style: dashed;
    border-color: rgba(46, 125, 50, 0.35);
  }

  .docs-title {
    font-size: 12px;
    font-weight: 800;
    opacity: 0.85;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .docs-chips {
    display: flex;
    flex-wrap: wrap;
  }

  .chip-text {
    max-width: 340px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
</style>
