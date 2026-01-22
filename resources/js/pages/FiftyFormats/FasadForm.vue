<template>
  <!-- APP BAR -->
  <AppBar
    :title="isEdit ? t('اصلاح فساد') : t('اضافه کردن یک ریکارډ جدید فساد')"
    :subtitle="
      isEdit ? t('ویرایش جزئیات یک ریکارډ موجود فساد') : t('ثبت یک ریکارډ جدید فساد در سیستم')
    "
  >
  </AppBar>

  <div class="fasad-page" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- TOP BAR (only for app view, hidden on print) -->
    <div class="top-bar no-print">
      <div>
        <button type="button" class="top-btn" @click="downloadPdf">PDF</button>
        <button type="button" class="top-btn" @click="downloadWord">Word</button>
      </div>
    </div>

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
                {{ t('عکس در سیستم ذخیره می‌شود. (picture)') }}
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
                {{ t('اسناد به جدول جدا ذخیره می‌شوند. (documents[])') }}
              </div>
            </v-col>
          </v-row>

          <v-divider class="mb-4"></v-divider>

          <!-- هدر بخش‌ها -->
          <v-row class="mt-1">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-account-details" size="small" class="ml-1"></v-icon>
                {{ t('مشخصات اساسی') }}
              </h3>
            </v-col>
          </v-row>

          <!-- Identity -->
          <v-row dense>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.name"
                :label="t('نام') + ' *'"
                density="compact"
                variant="outlined"
                hide-details="auto"
                :rules="[rules.required]"
                required
                color="primary"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.father_name"
                :label="t('نام پدر')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.nick_name"
                :label="t('نام مستعار')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- آدرس اصلی -->
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

          <!-- آدرس فعلی -->
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
                :label="t('زون فعلی')"
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

          <!-- موقعیت / خانه -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-home-analytics" size="small" class="ml-1"></v-icon>
                {{ t('موقعیت و خانه') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.specific_place"
                :label="t('موقعیت مشخص فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.street_number"
                :label="t('شماره سرک')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.home_number"
                :label="t('شماره خانه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- شبکه/تماس/فعالیت -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-account-network" size="small" class="ml-1"></v-icon>
                {{ t('فعالیت، شبکه و تماس') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.jabha"
                :label="t('جبهه')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.supplier_name"
                :label="t('نام تمویل‌کننده')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.responsibility"
                :label="t('مسئولیت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.activity_zone"
                :label="t('ساحه فعالیت')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <v-row dense class="mt-1">
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.friends_number"
                :label="t('تعداد دوستان')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                type="number"
              />
            </v-col>

            <v-col cols="12" md="5">
              <v-text-field
                v-model="form.phone_number"
                :label="t('شماره تماس')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.bond_distance"
                :label="t('فاصله ارتباط')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.last_band_reason"
                :label="t('دلیل آخرین بند/ارتباط')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.band_place"
                :label="t('محل بند/ارتباط')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.now_job"
                :label="t('وظیفه فعلی')"
                density="compact"
                variant="outlined"
                hide-details="auto"
              />
            </v-col>
          </v-row>

          <!-- مختصات -->
          <v-row class="mt-4">
            <v-col cols="12">
              <h3 class="section-title">
                <v-icon icon="mdi-map-marker-path" size="small" class="ml-1"></v-icon>
                {{ t('مختصات و نقشه') }}
              </h3>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                hint="مثال: 34.1234, 69.1234"
                persistent-hint
              />
            </v-col>
          </v-row>

          <!-- منبع -->
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
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
                density="compact"
                variant="outlined"
                hide-details="auto"
                clearable
                @update:model-value="onResourceCodeChange"
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
                    :prepend-icon="isRtl ? 'mdi-content-save' : null"
                    :append-icon="!isRtl ? 'mdi-content-save' : null"
                    :loading="saving"
                    type="submit"
                  >
                    {{ isEdit ? t('ذخیره تغییرات') : t('ثبت فساد جدید') }}
                  </v-btn>

                  <v-btn
                    color="secondary"
                    variant="outlined"
                    class="text-none"
                    @click="resetForm"
                    :prepend-icon="isRtl ? 'mdi-delete-sweep' : null"
                    :append-icon="!isRtl ? 'mdi-delete-sweep' : null"
                  >
                    {{ t('پاک کردن فرم') }}
                  </v-btn>
                </div>

                <div class="d-flex ga-2">
                  <v-btn
                    color="red"
                    variant="outlined"
                    class="text-none"
                    @click="downloadPdf"
                    :prepend-icon="isRtl ? 'mdi-file-pdf-box' : null"
                    :append-icon="!isRtl ? 'mdi-file-pdf-box' : null"
                  >
                    {{ t('PDF') }}
                  </v-btn>

                  <v-btn
                    color="blue"
                    variant="outlined"
                    class="text-none"
                    @click="downloadWord"
                    :prepend-icon="isRtl ? 'mdi-file-word-box' : null"
                    :append-icon="!isRtl ? 'mdi-file-word-box' : null"
                  >
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
      <v-btn variant="text" @click="showError = false">
        {{ t('بستن') }}
      </v-btn>
    </template>
  </v-snackbar>

  <!-- ✅ Success Snackbar -->
  <v-snackbar v-model="showSuccess" color="success" timeout="4000">
    <div class="d-flex align-center">
      <v-icon icon="mdi-check-circle" class="mr-2"></v-icon>
      <span>{{ successMessage }}</span>
    </div>
    <template #actions>
      <v-btn variant="text" @click="showSuccess = false">
        {{ t('بستن') }}
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script setup>
  import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';
  import AppBar from '@/components/AppBar.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const route = useRoute();
  const router = useRouter();
  const repo = useFasadRepository();

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
    nick_name: '',
    name: '',
    father_name: '',
    main_province: '',
    main_district: '',
    main_village: '',
    now_province: '',
    now_zone: '',
    now_district: '',
    now_village: '',
    specific_place: '',
    street_number: '',
    home_number: '',
    jabha: '',
    supplier_name: '',
    responsibility: '',
    activity_zone: '',
    friends_number: '',
    phone_number: '',
    bond_distance: '',
    last_band_reason: '',
    band_place: '',
    now_job: '',
    Province_addr: '',
    coordinates: '',
    resource_code: '',
    resource_phone: '',
    resource_id: null,

    // ✅ uploads
    picture_url: '', // existing URL from API
    _newPicture: null, // File
    existingDocs: [], // [{id,url,original_name,file_name}]
    _newDocs: [], // File[]
    removed_document_ids: [], // number[]
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

  /* Resources dropdown */
  const resources = computed(() => repo.resourcecode || []);

  function onResourceCodeChange(code) {
    const found = resources.value.find((r) => r.code_number === code);
    form.value.resource_phone = found?.phone_number ?? '';
    form.value.resource_id = found?.id ?? null;
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
    form.value.picture_url = ''; // if you want to remove existing picture on update, backend must handle it (optional)
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
      nick_name: '',
      name: '',
      father_name: '',
      main_province: '',
      main_district: '',
      main_village: '',
      now_province: '',
      now_zone: '',
      now_district: '',
      now_village: '',
      specific_place: '',
      street_number: '',
      home_number: '',
      jabha: '',
      supplier_name: '',
      responsibility: '',
      activity_zone: '',
      friends_number: '',
      phone_number: '',
      bond_distance: '',
      last_band_reason: '',
      band_place: '',
      now_job: '',
      Province_addr: '',
      coordinates: '',
      resource_code: '',
      resource_phone: '',
      resource_id: null,

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
      nick_name: x.nick_name ?? '',
      name: x.name ?? '',
      father_name: x.father_name ?? '',
      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',
      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',
      specific_place: x.specific_place ?? '',
      street_number: x.street_number ?? '',
      home_number: x.home_number ?? '',
      jabha: x.jabha ?? '',
      supplier_name: x.supplier_name ?? '',
      responsibility: x.responsibility ?? '',
      activity_zone: x.activity_zone ?? '',
      friends_number: x.friends_number ?? '',
      phone_number: x.phone_number ?? '',
      bond_distance: x.bond_distance ?? '',
      last_band_reason: x.last_band_reason ?? '',
      band_place: x.band_place ?? '',
      now_job: x.now_job ?? '',
      Province_addr: x.Province_addr ?? '',
      coordinates: x.coordinates ?? '',
      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,

      // ✅ API fields
      picture_url: x.picture ?? x.picture_url ?? '',
      _newPicture: null,
      _newDocs: [],
      removed_document_ids: [],

      // ✅ documents array from API (make sure backend returns documents with url)
      existingDocs: Array.isArray(x.documents)
        ? x.documents.map((d) => ({
            id: d.id,
            url: d.url || d.full_url || d.path || '',
            original_name: d.original_name || d.name || '',
            file_name: d.file_name || '',
          }))
        : [],
    };

    // clear local preview
    if (picturePreviewUrl.value) URL.revokeObjectURL(picturePreviewUrl.value);
    picturePreviewUrl.value = '';
  }

  /* ✅ Build FormData (sends picture + documents) */
  function buildFormData() {
    const fd = new FormData();

    // normal fields (exclude client-only)
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

    // picture
    if (form.value._newPicture) {
      fd.append('picture', form.value._newPicture);
    }

    // documents[]
    (form.value._newDocs || []).forEach((file) => {
      fd.append('documents[]', file);
    });

    // removed_document_ids[]
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
      { label: 'تخلص', value: form.value.nick_name },
      { label: 'نام', value: form.value.name },
      { label: 'نام پدر', value: form.value.father_name },
      { label: 'ولایت مربوطه', value: form.value.Province_addr },
      { label: 'وظیفه فعلی', value: form.value.now_job },

      { label: 'ولایت اصلی', value: form.value.main_province },
      { label: 'ولسوالی اصلی', value: form.value.main_district },
      { label: 'قریه/ناحیه اصلی', value: form.value.main_village },

      { label: 'ولایت فعلی', value: form.value.now_province },
      { label: 'زون فعلی', value: form.value.now_zone },
      { label: 'ولسوالی فعلی', value: form.value.now_district },
      { label: 'قریه/ناحیه فعلی', value: form.value.now_village },

      { label: 'موقعیت مشخص فعلی', value: form.value.specific_place },
      { label: 'شماره سرک', value: form.value.street_number },
      { label: 'شماره خانه', value: form.value.home_number },

      { label: 'جبهه', value: form.value.jabha },
      { label: 'نام تمویل‌کننده', value: form.value.supplier_name },
      { label: 'مسئولیت', value: form.value.responsibility },
      { label: 'ساحه فعالیت', value: form.value.activity_zone },
      { label: 'تعداد دوستان', value: form.value.friends_number },
      { label: 'شماره تماس', value: form.value.phone_number },
      { label: 'فاصله ارتباط', value: form.value.bond_distance },

      { label: 'دلیل آخرین بند/ارتباط', value: form.value.last_band_reason },
      { label: 'محل بند/ارتباط', value: form.value.band_place },

      { label: 'مختصات', value: form.value.coordinates },

      { label: 'کود منبع', value: form.value.resource_code },
      { label: 'شماره منبع', value: form.value.resource_phone },
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
    <h3 class="title">فورم فساد</h3>
    <table class="simple-table">
      <tbody>
        ${rows}
      </tbody>
    </table>
    <div class="sig">
      <div>امضاء / مهر مسئول: ____________________</div>
      <div>تاریخ / امضاء ثبت کوونکی: ____________________</div>
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
  <title>فساد_${esc(form.value.id || '')}_${esc(form.value.name || '')}</title>
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
    link.download = `فساد_${form.value.name || 'جدید'}_${Date.now()}.doc`;
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
        const updated = await repo.UpdateFasad?.(form.value.id, fd);
        if (updated && updated.id) {
          showSuccessMsg(t('تغییرات موفقانه ذخیره شد'));
          goBack();
          return;
        }
      } else {
        const created = await repo.CreateFasad?.(fd);
        if (created && created.id) {
          showSuccessMsg(t('فساد جدید موفقانه ثبت شد'));
          goBack();
          return;
        }
      }

      showErrorMessage(t('خطا در ذخیره معلومات'));
    } catch (e) {
      console.error('Fasad save error', e);
      showErrorMessage(t('خطا در ذخیره معلومات') + ' ' + (e?.message || ''));
    } finally {
      saving.value = false;
    }
  }

  /* Navigation */
  function goBack() {
    router.push('/app/fasads');
  }

  /* Load on edit */
  async function loadExisting() {
    const id = route.params.id;
    if (!id) return;
    try {
      await repo.FetchFasadData?.(id);
      const rec = repo.Fasad && Array.isArray(repo.Fasad) ? repo.Fasad[0] : repo.Fasad || {};
      fillFormFromRecord(rec);
    } catch (e) {
      console.error('loadExisting error', e);
      showErrorMessage(t('خطا در دریافت معلومات فساد'));
    }
  }

  /* Init */
  onMounted(async () => {
    // ✅ you already have ProvinceDistrictNameSelect, no need lookups here
    await repo.GetResourceCode?.();
    if (isEdit.value) await loadExisting();
  });

  onBeforeUnmount(() => {
    if (picturePreviewUrl.value) URL.revokeObjectURL(picturePreviewUrl.value);
  });
</script>

<style scoped>
  /* ===== GENERAL ===== */
  .fasad-page {
    background: #ffffff !important;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 12px;
  }

  /* ===== SCREEN VIEW ===== */
  .no-print {
    display: block;
  }

  /* top buttons (not printed) */
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

  /* Form container */
  .form-container {
    margin: 0 auto;
  }

  /* Form styling */
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

  /* ===== RESPONSIVE ===== */
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
