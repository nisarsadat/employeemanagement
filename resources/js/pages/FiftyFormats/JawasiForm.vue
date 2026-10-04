<template>
  <!-- APP BAR -->
  <AppBar
    :title="isEdit ? t('اصلاح جواسیس') : t('اضافه کردن ریکارډ جدید جواسیس')"
    :subtitle="
      isEdit ? t('ویرایش جزئیات یک ریکارډ موجود جواسیس') : t('ثبت یک ریکارډ جدید جواسیس در سیستم')
    "
  >
  </AppBar>

  <div class="fasad-page" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- TOP BAR (only for app view, hidden on print) -->

    <!-- FORM -->
    <v-container class="mt-4 form-container no-print" style="max-width: 1200px">
      <v-form
        ref="formRef"
        v-model="formValid"
        lazy-validation
        @submit.prevent="onSubmit"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <v-card class="pa-4" elevation="2">
          <!-- ✅ PICTURE + DOCUMENTS -->
          <v-row class="mt-1">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-camera" size="small" class="ml-1"></v-icon>
                {{ t('عکس و اسناد') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense class="mb-2">
            <!-- ✅ Picture -->
            <v-col cols="12" md="6">
              <div class="d-flex align-center ga-3 flex-wrap">
                <v-avatar size="72" rounded="lg" class="elevation-1">
                  <v-img :src="picturePreviewUrl || form.picture_url || ''" cover />
                </v-avatar>

                <input
                  ref="pictureInput"
                  class="hidden-file"
                  type="file"
                  accept="image/*"
                  @change="onPickPicture"
                />

                <div class="d-flex align-center ga-2 flex-wrap">
                  <v-btn
                    size="small"
                    color="primary"
                    variant="elevated"
                    prepend-icon="mdi-image-plus"
                    class="text-none"
                    @click="triggerPicture"
                  >
                    {{ t('انتخاب عکس') }}
                  </v-btn>

                  <v-btn
                    v-if="picturePreviewUrl || form.picture_url"
                    size="small"
                    color="warning"
                    variant="outlined"
                    prepend-icon="mdi-close"
                    class="text-none"
                    @click="clearPicture"
                  >
                    {{ t('حذف عکس') }}
                  </v-btn>
                </div>
              </div>

              <div class="text-caption mt-2" style="color: #666">
                <!-- {{ t('عکس در سیستم ذخیره می‌شود. (picture)') }} -->
              </div>
            </v-col>

            <!-- ✅ Documents -->
            <v-col cols="12" md="6">
              <input
                ref="docsInput"
                class="hidden-file"
                type="file"
                multiple
                @change="onPickDocs"
              />

              <div class="d-flex align-center ga-2 flex-wrap">
                <v-btn
                  size="small"
                  color="primary"
                  variant="elevated"
                  prepend-icon="mdi-tray-arrow-up"
                  class="text-none"
                  @click="triggerDocs"
                >
                  {{ t('انتخاب فایل‌ها') }}
                </v-btn>

                <v-chip size="small" variant="outlined" class="text-caption">
                  {{ t('فایل جدید') }}: {{ form._newDocs.length }} | {{ t('موجود') }}:
                  {{ form.existingDocs.length }}
                </v-chip>
              </div>

              <!-- chips -->
              <div class="chips-row mt-2">
                <!-- new docs -->
                <v-chip
                  v-for="(f, idx) in form._newDocs"
                  :key="`new-${idx}`"
                  size="small"
                  variant="outlined"
                  class="chip-outline"
                  pill
                  :title="f.name"
                >
                  <v-icon size="16" class="ml-1">mdi-plus</v-icon>
                  {{ shortName(f.name) }}
                  <v-icon size="16" class="ml-1" title="حذف" @click.stop="removeNewDocAt(idx)">
                    mdi-close
                  </v-icon>
                </v-chip>

                <!-- existing docs -->
                <v-chip
                  v-for="(d, idx) in form.existingDocs"
                  :key="`ex-${idx}`"
                  size="small"
                  color="primary"
                  variant="flat"
                  class="chip-existing"
                  pill
                  :title="d.original_name || d.file_name"
                  @click="openDoc(d.url)"
                >
                  <v-icon size="16" class="ml-1">mdi-file-eye</v-icon>
                  {{ shortName(d.original_name || d.file_name) }}
                  <v-icon size="16" class="ml-1" title="حذف" @click.stop="markDocForRemoval(d)">
                    mdi-close
                  </v-icon>
                </v-chip>
              </div>

              <div class="text-caption mt-2" style="color: #666">
                <!-- {{ t('اسناد به جدول جدا ذخیره می‌شوند. (documents[])') }} -->
              </div>
            </v-col>
          </v-row>

          <v-divider class="mb-4"></v-divider>

          <!-- ✅ BASIC INFO -->
          <v-row class="mt-1">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-account-details" size="small" class="ml-1"></v-icon>
                {{ t('مشخصات اساسی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.name"
                :label="t('نام') + ' *'"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.required]"
                required
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.nick_name"
                :label="t('نام مستعار')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.father_name"
                :label="t('نام پدر')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ✅ MAIN ADDRESS -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-home-map-marker" size="small" class="ml-1"></v-icon>
                {{ t('آدرس اصلی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
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
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ✅ CURRENT ADDRESS -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-map-marker-radius" size="small" class="ml-1"></v-icon>
                {{ t('آدرس فعلی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
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

            <v-col cols="12" md="6">
              <FormatZoneAutocomplete
                v-model="form.now_zone"
                :label="t('آمریت فعلی')"
                :disabled="saving"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.now_village"
                :label="t('قریه/ناحیه فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_specific_place"
                :label="t('موقعیت مشخص فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_street_number"
                :label="t('شماره سرک')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.now_home_number"
                :label="t('شماره خانه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ✅ LOCATION -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-map-marker-path" size="small" class="ml-1"></v-icon>
                {{ t('مختصات و کشور') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                persistent-hint
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.country"
                :label="t('کشور')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ✅ ACTIVITIES -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-clipboard-text" size="small" class="ml-1"></v-icon>
                {{ t('مسئولیت و فعالیت‌ها') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.responsiability"
                :label="t('مسئولیت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.done_activities"
                :label="t('فعالیت‌های انجام شده')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.activity_place"
                :label="t('محل فعالیت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ✅ COMMUNICATION -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-phone" size="small" class="ml-1"></v-icon>
                {{ t('ارتباطات') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.count_of_communication"
                :label="t('تعداد ارتباطات')"
                type="number"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                density="compact"
                variant="outlined"
                maxlength="10"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ✅ JOBS -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-briefcase" size="small" class="ml-1"></v-icon>
                {{ t('سوابق شغلی') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.old_job"
                :label="t('وظیفه قبلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.now_job"
                :label="t('وظیفه فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ✅ RESOURCE -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-source-branch" size="small" class="ml-1"></v-icon>
                {{ t('معلومات منبع') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="4">
              <ResourceRemoteAutocomplete
                v-model="form.resource_id"
                :label="t('کود منبع')"
                density="compact"
                variant="outlined"
                :item-title="resourceTitle"
                @selected="onResourceSelected"
                @cleared="onResourceCleared"
              />
            </v-col>

            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.resource_phone"
                :label="t('شماره منبع')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                readonly
              />
            </v-col>
          </v-row>

          <!-- ✅ NOTES -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-note-text" size="small" class="ml-1"></v-icon>
                {{ t('یادداشت‌ها') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12">
              <v-textarea
                v-model="form.tafseel"
                :label="t('تفصیل')"
                rows="3"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="form.notes"
                :label="t('یادداشت')"
                rows="3"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- ACTIONS -->
          <v-row class="mt-6">
            <v-col cols="12">
              <v-divider class="mb-4"></v-divider>

              <div class="d-flex justify-space-between align-center">
                <div class="d-flex ga-2">
                  <v-btn
                    color="primary"
                    variant="flat"
                    class="text-none"
                    :loading="saving"
                    type="submit"
                  >
                    {{ isEdit ? t('ذخیره') : t('ثبت') }}
                  </v-btn>

                  <v-btn color="secondary" variant="outlined" class="text-none" @click="resetForm">
                    {{ t('پاک کردن فرم') }}
                  </v-btn>
                </div>

                <div class="d-flex ga-2">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    class="text-none rounded-pill secondary-action-btn"
                    prepend-icon="mdi-printer"
                    @click="printForm"
                  >
                    {{ t('چاپ') }}
                  </v-btn>

                  <v-btn color="red" variant="outlined" class="text-none" @click="downloadPdf">
                    {{ t('PDF') }}
                  </v-btn>

                  <v-btn color="blue" variant="outlined" class="text-none" @click="downloadWord">
                    {{ t('Word') }}
                  </v-btn>
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card>
      </v-form>
    </v-container>
  </div>

  <!-- ❗ Error Snackbar -->
  <v-snackbar v-model="showError" color="error" timeout="5000">
    <div class="d-flex align-center">
      <v-icon icon="mdi-alert-circle" class="mr-2"></v-icon>
      <span>{{ errorMessage }}</span>
    </div>
    <template #actions>
      <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
    </template>
  </v-snackbar>

  <!-- ✅ Success Snackbar -->
  <v-snackbar v-model="showSuccess" color="success" timeout="4000">
    <div class="d-flex align-center">
      <v-icon icon="mdi-check-circle" class="mr-2"></v-icon>
      <span>{{ successMessage }}</span>
    </div>
    <template #actions>
      <v-btn variant="text" @click="showSuccess = false">{{ t('بستن') }}</v-btn>
    </template>
  </v-snackbar>
</template>

<script setup>
  import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';

  import AppBar from '@/components/AppBar.vue';
  import { useJawasiRepository } from '@/stores/JawasiRepository';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';
  import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';
  import { printBlankForm } from '@/utils/printBlankForm';
  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const route = useRoute();
  const router = useRouter();
  const repo = useJawasiRepository();

  const isEdit = computed(() => !!route.params.id);

  /* Form state */
  const formRef = ref(null);
  const formValid = ref(false);
  const saving = ref(false);

  /* ✅ picture/docs inputs */
  const pictureInput = ref(null);
  const docsInput = ref(null);
  const picturePreviewUrl = ref('');

  const form = ref({
    id: null,

    name: '',
    nick_name: '',
    father_name: '',

    main_province: '',
    main_district: '',
    main_village: '',

    now_province: '',
    now_zone: '',
    now_district: '',
    now_village: '',
    now_specific_place: '',
    now_street_number: '',
    now_home_number: '',

    coordinates: '',
    country: '',

    responsiability: '',
    done_activities: '',
    activity_place: '',

    count_of_communication: '',
    phone_number: '',

    old_job: '',
    now_job: '',

    resource_code: '',
    resource_phone: '',
    resource_id: null,

    notes: '',
    tafseel: '',

    // ✅ uploads
    picture_url: '',
    _newPicture: null,
    existingDocs: [],
    _newDocs: [],
    removed_document_ids: [],
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
    form.value.resource_phone = '';
  }

  /* ✅ picture handlers */
  function triggerPicture() {
    pictureInput.value?.click();
  }
  function onPickPicture(e) {
    const file = (e.target.files && e.target.files[0]) || null;
    form.value._newPicture = file;

    if (picturePreviewUrl.value) URL.revokeObjectURL(picturePreviewUrl.value);
    picturePreviewUrl.value = file ? URL.createObjectURL(file) : '';

    if (pictureInput.value) pictureInput.value.value = '';
  }
  function clearPicture() {
    form.value._newPicture = null;
    form.value.picture_url = '';
    if (picturePreviewUrl.value) URL.revokeObjectURL(picturePreviewUrl.value);
    picturePreviewUrl.value = '';
    if (pictureInput.value) pictureInput.value.value = '';
  }

  /* ✅ docs handlers */
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
    const n = String(name || '');
    if (n.length <= 18) return n;
    const dot = n.lastIndexOf('.');
    if (dot === -1) return n.slice(0, 15) + '…';
    const ext = n.slice(dot);
    return `${n.slice(0, 15)}…${ext}`;
  }
  function openDoc(url) {
    if (url) window.open(url, '_blank');
  }
  function markDocForRemoval(doc) {
    if (!doc?.id) return;
    if (!form.value.removed_document_ids.includes(doc.id)) {
      form.value.removed_document_ids.push(doc.id);
    }
    form.value.existingDocs = (form.value.existingDocs || []).filter((d) => d.id !== doc.id);
  }

  /* Helpers */
  function resetForm() {
    const keepId = isEdit.value ? form.value.id : null;

    form.value = {
      id: keepId,

      name: '',
      nick_name: '',
      father_name: '',

      main_province: '',
      main_district: '',
      main_village: '',

      now_province: '',
      now_zone: '',
      now_district: '',
      now_village: '',
      now_specific_place: '',
      now_street_number: '',
      now_home_number: '',

      coordinates: '',
      country: '',

      responsiability: '',
      done_activities: '',
      activity_place: '',

      count_of_communication: '',
      phone_number: '',

      old_job: '',
      now_job: '',

      resource_code: '',
      resource_phone: '',
      resource_id: null,

      notes: '',
      tafseel: '',

      picture_url: '',
      _newPicture: null,
      existingDocs: [],
      _newDocs: [],
      removed_document_ids: [],
    };

    clearPicture();
    formRef.value?.resetValidation?.();
  }

  function fillFormFromRecord(x = {}) {
    form.value = {
      id: x.id ?? null,

      name: x.name ?? '',
      nick_name: x.nick_name ?? '',
      father_name: x.father_name ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',
      now_specific_place: x.now_specific_place ?? '',
      now_street_number: x.now_street_number ?? '',
      now_home_number: x.now_home_number ?? '',

      coordinates: x.coordinates ?? '',
      country: x.country ?? '',

      responsiability: x.responsiability ?? '',
      done_activities: x.done_activities ?? '',
      activity_place: x.activity_place ?? '',

      count_of_communication: x.count_of_communication ?? '',
      phone_number: x.phone_number ?? '',

      old_job: x.old_job ?? '',
      now_job: x.now_job ?? '',
      tafseel: x.tafseel ?? '',

      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,

      notes: x.notes ?? '',

      // ✅ API fields
      picture_url: x.picture ?? x.picture_url ?? '',
      _newPicture: null,
      _newDocs: [],
      removed_document_ids: [],

      existingDocs: Array.isArray(x.documents)
        ? x.documents.map((d) => ({
            id: d.id,
            url: d.url || d.full_url || d.path || '',
            original_name: d.original_name || d.name || '',
            file_name: d.file_name || '',
          }))
        : [],
    };

    if (picturePreviewUrl.value) URL.revokeObjectURL(picturePreviewUrl.value);
    picturePreviewUrl.value = '';
  }

  /* ✅ Build FormData */
  function buildFormData() {
    const fd = new FormData();

    const payload = { ...form.value };
    delete payload.picture_url;
    delete payload._newPicture;
    delete payload.existingDocs;
    delete payload._newDocs;
    delete payload.removed_document_ids;

    Object.entries(payload).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });

    if (form.value._newPicture) {
      fd.append('picture', form.value._newPicture);
    }

    (form.value._newDocs || []).forEach((file) => {
      fd.append('documents[]', file);
    });

    (form.value.removed_document_ids || []).forEach((id) => {
      fd.append('removed_document_ids[]', String(id));
    });

    return fd;
  }

  /* =========================
   SIMPLE EXPORT (NO IMAGE, NO HEADERS)
========================= */
  function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, (m) => {
      const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
      return map[m];
    });
  }

  function exportRows() {
    return [
      { label: 'نام', value: form.value.name },
      { label: 'نام مستعار', value: form.value.nick_name },
      { label: 'نام پدر', value: form.value.father_name },

      { label: 'ولایت اصلی', value: form.value.main_province },
      { label: 'ولسوالی اصلی', value: form.value.main_district },
      { label: 'قریه/ناحیه اصلی', value: form.value.main_village },

      { label: 'ولایت فعلی', value: form.value.now_province },
      { label: 'آمریت فعلی', value: form.value.now_zone },
      { label: 'ولسوالی فعلی', value: form.value.now_district },
      { label: 'قریه/ناحیه فعلی', value: form.value.now_village },
      { label: 'موقعیت مشخص فعلی', value: form.value.now_specific_place },
      { label: 'شماره سرک', value: form.value.now_street_number },
      { label: 'شماره خانه', value: form.value.now_home_number },

      { label: 'مختصات', value: form.value.coordinates },
      { label: 'کشور', value: form.value.country },

      { label: 'مسئولیت', value: form.value.responsiability },
      { label: 'فعالیت‌های انجام شده', value: form.value.done_activities },
      { label: 'محل فعالیت', value: form.value.activity_place },

      { label: 'تعداد ارتباطات', value: form.value.count_of_communication },
      { label: 'شماره تماس', value: form.value.phone_number },

      { label: 'وظیفه قبلی', value: form.value.old_job },
      { label: 'وظیفه فعلی', value: form.value.now_job },
      { label: 'تفصیل', value: form.value.tafseel },

      { label: 'کود منبع', value: form.value.resource_code },
      { label: 'شماره منبع', value: form.value.resource_phone },

      { label: 'یادداشت', value: form.value.notes },
    ];
  }

  function buildSimpleTableHtml() {
    const rows = exportRows()
      .map(
        (r) => `
      <tr>
        <th>${esc(r.label)}</th>
        <td>${esc(r.value)}</td>
      </tr>
    `
      )
      .join('');

    return `
    <h3 class="title">فورم جواسیس</h3>
    <table class="simple-table">
      <tbody>${rows}</tbody>
    </table>
    <div class="sig">
      <div>${t('امضا مسئول')}: ____________________</div>
      <div>${t('امضا ثبت کننده')}: ____________________</div>
      <div>${t('تاریخ')}: ____________________</div>
    </div>
  `;
  }

  function buildDocumentHtml() {
    const bodyHtml = buildSimpleTableHtml();

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>jawasi_${esc(form.value.id || '')}_${esc(form.value.name || '')}</title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }
    html, body {
      margin: 0; padding: 0; background: #ffffff;
      font-family: Tahoma, Arial, sans-serif; font-size: 12px; direction: rtl;
    }
    .title { text-align:center; margin:0 0 12px; font-size:16px; font-weight:700; margin-top:22px; }
    .simple-table { width:95%; border-collapse:collapse; table-layout:fixed; margin-left:12px; margin-right:12px; }
    .simple-table th, .simple-table td { border:1px solid #000; padding:6px 8px; vertical-align:top; font-size:12px; }
    .simple-table th { width:35%; background:#f5f5f5; text-align:right; font-weight:700; }
    .simple-table td { width:65%; text-align:right; word-break:break-word; }
    .sig { margin-top:18px; margin-right:16px; display:flex; justify-content:space-between; gap:12px; font-size:12px; }
  </style>
</head>
<body>
  ${bodyHtml}
</body>
</html>
`;
  }

  /* PRINT BLANK FORM (empty fields, same design, one A4 page) */
  function printForm(e) {
    printBlankForm(e, { onError: showErrorMessage });
  }

  function downloadPdf() {
    const html = buildDocumentHtml();
    const w = window.open('', '_blank', 'width=1024,height=768');
    if (!w) return;
    w.document.open();
    w.document.write(html);
    w.document.close();
    w.focus();
    w.print();
  }

  function downloadWord() {
    const html = buildDocumentHtml();
    const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = `jawasi_${form.value.name || 'new'}_${Date.now()}.doc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    URL.revokeObjectURL(url);
    showSuccessMsg(t('فایل Word با موفقیت ایجاد شد'));
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
      const fd = buildFormData();

      if (isEdit.value && form.value.id) {
        const updated = await repo.UpdateJawasi?.(form.value.id, fd);
        if (updated && updated.id) {
          showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
          goBack();
          return;
        }
      } else {
        const created = await repo.CreateJawasi?.(fd);
        if (created && created.id) {
          showSuccessMsg(t('ریکارډ جدید موفقانه ثبت شد'));
          goBack();
          return;
        }
      }

      showErrorMessage(t('خطا در ذخیره معلومات'));
    } catch (e) {
      console.error('Jawasi save error', e);
      showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
    } finally {
      saving.value = false;
    }
  }

  function goBack() {
    router.push('/app/jawasis');
  }

  async function loadExisting() {
    const id = route.params.id;
    if (!id) return;
    try {
      await repo.FetchJawasiData?.(id);
      const rec = repo.Jawasi && Array.isArray(repo.Jawasi) ? repo.Jawasi[0] : repo.Jawasi || {};
      fillFormFromRecord(rec);
    } catch (e) {
      console.error('loadExisting error', e);
      showErrorMessage(t('خطا در دریافت معلومات جواسیس'));
    }
  }

  onMounted(async () => {
    if (isEdit.value) await loadExisting();
  });

  onBeforeUnmount(() => {
    if (picturePreviewUrl.value) URL.revokeObjectURL(picturePreviewUrl.value);
  });
</script>

<style scoped>
  .fasad-page {
    background: #ffffff !important;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 12px;
  }
  .no-print {
    display: block;
  }
  .top-bar {
    display: flex;
    justify-content: flex-start;
    gap: 4px;
    margin: 8px 16px;
  }
  .top-btn {
    font-size: 12px;
    padding: 4px 12px;
    background: #ffffff;
    border: 1px solid #000;
    cursor: pointer;
    border-radius: 4px;
  }
  .top-btn:hover {
    background: #f5f5f5;
  }
  .form-container {
    margin: 0 auto;
  }
  .section-title {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #37474f;
    background-color: #f5f5f5;
    padding: 8px 12px;
    border-radius: 4px;
    border-right: 4px solid #2196f3;
  }
  .hidden-file {
    display: none;
  }
  .chips-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }
  .chip-outline {
    border-style: dashed;
  }
  .chip-existing {
    cursor: pointer;
  }
  .ga-2 {
    gap: 8px;
  }
  .ga-3 {
    gap: 12px;
  }
  @media (max-width: 960px) {
    .form-container {
      padding: 8px !important;
    }
    .v-card {
      padding: 16px !important;
    }
    .d-flex.justify-space-between {
      flex-direction: column;
      gap: 16px;
    }
    .d-flex.justify-space-between > div {
      width: 100%;
      justify-content: center;
    }
  }
</style>
