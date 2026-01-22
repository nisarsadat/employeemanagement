<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش هېست') : t('ایجاد هېست جدید')"
      :subtitle="
        isEdit ? t('ویرایش جزئیات یک ریکارډ هېست موجود') : t('ثبت یک ریکارډ هېست جدید در سیستم')
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
                  {{ isEdit ? t('ویرایش ریکارډ هېست') : t('ایجاد ریکارډ هېست') }}
                </span>
              </div>

              <v-row>
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

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.phone_number"
                    :label="t('شماره تماس')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('phone_number')"
                    :error-messages="getErrorMessages('phone_number')"
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
                    variant="outlined"
                    density="compact"
                    :error="hasError('now_village')"
                    :error-messages="getErrorMessages('now_village')"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.specific_place"
                    :label="t('موقعیت مشخص')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('specific_place')"
                    :error-messages="getErrorMessages('specific_place')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.street_number"
                    :label="t('سرک نمبر')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('street_number')"
                    :error-messages="getErrorMessages('street_number')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.home_number"
                    :label="t('نمبر خانه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('home_number')"
                    :error-messages="getErrorMessages('home_number')"
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Extra fields -->
          <div class="section-header mb-2">
            <span class="section-title">{{ t('جزئیات اضافی و منبع') }}</span>
          </div>

          <v-row>
            <!-- Jobs / activity / gun / robberies -->
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
                v-model="form.now_job"
                :label="t('وظیفه فعلی')"
                variant="outlined"
                density="compact"
                :error="hasError('now_job')"
                :error-messages="getErrorMessages('now_job')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="form.done_roberies"
                type="number"
                :label="t('سرقت‌های انجام‌شده')"
                variant="outlined"
                density="compact"
                :error="hasError('done_roberies')"
                :error-messages="getErrorMessages('done_roberies')"
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.gun_type"
                :label="t('نوع سلاح')"
                variant="outlined"
                density="compact"
                :error="hasError('gun_type')"
                :error-messages="getErrorMessages('gun_type')"
              />
            </v-col>

            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.coordinates"
                :label="t('مختصات')"
                variant="outlined"
                density="compact"
                :error="hasError('coordinates')"
                :error-messages="getErrorMessages('coordinates')"
              />
            </v-col>

            <!-- Resource mapping -->
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.resource_code"
                :items="resources"
                item-title="code_number"
                item-value="code_number"
                :label="t('کود منبع')"
                variant="outlined"
                density="compact"
                clearable
                @update:model-value="onResourceCodeChange"
                :error="hasError('resource_code')"
                :error-messages="getErrorMessages('resource_code')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.resource_phone_number"
                :label="t('شماره منبع')"
                variant="outlined"
                density="compact"
                readonly
              />
            </v-col>

            <!-- Note -->
            <v-col cols="12" md="8">
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

          <!-- ✅ DOCUMENTS (NO OTHER CHANGES) -->
          <v-divider class="my-4" />

          <div class="section-header mb-2">
            <span class="section-title">{{ t('اسناد') }}</span>
          </div>

          <v-row>
            <v-col cols="12">
              <!-- picker -->
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
                  variant="tonal"
                  class="text-none rounded-pill soft-btn"
                  prepend-icon="mdi-paperclip"
                  @click="triggerDocs"
                >
                  {{ t('انتخاب اسناد') }}
                </v-btn>

                <v-btn
                  v-if="isEdit"
                  size="small"
                  color="primary"
                  variant="outlined"
                  class="text-none rounded-pill soft-btn"
                  prepend-icon="mdi-folder-zip"
                  @click="downloadHeistDocumentsZip"
                >
                  {{ t('دانلود همه اسناد') }}
                </v-btn>
              </div>

              <!-- existing docs chips -->
              <div v-if="existingDocs.length" class="mt-3 d-flex flex-wrap ga-2">
                <v-chip
                  v-for="d in existingDocs"
                  :key="`ex-${d.id}`"
                  color="secondary"
                  variant="tonal"
                  class="ma-1"
                  closable
                  @click="openDoc(d)"
                  @click:close="removeExistingDoc(d)"
                >
                  <v-icon start size="18">mdi-file</v-icon>
                  {{ d.original_name || d.file_name || `doc_${d.id}` }}
                </v-chip>
              </div>

              <!-- new docs chips -->
              <div v-if="newDocs.length" class="mt-2 d-flex flex-wrap ga-2">
                <v-chip
                  v-for="(f, idx) in newDocs"
                  :key="`new-${idx}-${f.name}`"
                  color="primary"
                  variant="tonal"
                  class="ma-1"
                  closable
                  @click:close="removeNewDoc(idx)"
                >
                  <v-icon start size="18">mdi-file-plus</v-icon>
                  {{ f.name }}
                </v-chip>
              </div>
            </v-col>
          </v-row>

          <!-- Actions -->
          <v-row class="mt-4">
            <v-col cols="12" class="d-flex justify-end ga-2 flex-wrap">
              <v-btn
                color="primary"
                variant="elevated"
                class="text-none rounded-pill primary-action-btn"
                :loading="saving"
                type="submit"
                :prepend-icon="isEdit ? 'mdi-content-save-edit-outline' : 'mdi-plus-circle-outline'"
              >
                {{ isEdit ? t('ذخیره تغییرات') : t('ایجاد ریکارډ هېست') }}
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

              <v-btn
                color="red"
                variant="outlined"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-file-pdf-box"
                @click="downloadPdf"
              >
                {{ t('PDF') }}
              </v-btn>

              <v-btn
                color="blue"
                variant="outlined"
                class="text-none rounded-pill secondary-action-btn"
                prepend-icon="mdi-file-word-box"
                @click="downloadWord"
              >
                {{ t('Word') }}
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

  import AppBar from '@/components/AppBar.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';
  import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';
  import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';

  const repo = useFasadRepository();
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
    father_name: '',
    phone_number: '',

    // addresses
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

    // extra
    old_job: '',
    now_job: '',
    done_roberies: '',
    gun_type: '',
    Province_addr: '',
    coordinates: '',

    // resource
    resource_code: '',
    resource_phone_number: '',
    resource_id: null,

    // note
    note: '',
  });

  const form = ref(blankForm());

  /* ✅ DOCUMENTS STATE (NO OTHER CHANGES) */
  const docsInput = ref(null);
  const newDocs = ref([]); // File[]
  const existingDocs = ref([]); // from API
  const removedDocIds = ref([]); // ids to delete on update

  function triggerDocs() {
    docsInput.value?.click();
  }

  function onPickDocs(e) {
    const files = Array.from(e.target.files || []);
    if (!files.length) return;

    // append (not replace)
    newDocs.value = [...newDocs.value, ...files];

    if (e.target) e.target.value = '';
  }

  function removeNewDoc(idx) {
    newDocs.value.splice(idx, 1);
  }

  function removeExistingDoc(doc) {
    const id = doc?.id;
    if (id != null && !removedDocIds.value.includes(id)) {
      removedDocIds.value.push(id);
    }
    existingDocs.value = existingDocs.value.filter((d) => d.id !== id);
  }

  function openDoc(doc) {
    const path = doc?.file_path || doc?.url;
    if (!path) return;

    const url = normalizePicture(path);
    window.open(url, '_blank');
  }

  async function downloadHeistDocumentsZip() {
    const id = form.value.id;
    if (!id) return;
    try {
      await repo.DownloadHeistZip?.(id, `heist_${id}_documents`);
    } catch (e) {
      console.error(e);
      showErrorMessage(t('دانلود اسناد ناکام شد'));
    }
  }

  /* RULES */
  const rules = {
    required: (v) => !!v || t('این فیلد الزامی است'),
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

  /* RESOURCES DROPDOWN */
  const resources = computed(() => repo.resourcecode || []);

  function onResourceCodeChange(code) {
    const found = resources.value.find((r) => r.code_number === code);
    form.value.resource_phone_number = found?.phone_number ?? '';
    form.value.resource_id = found?.id ?? null;
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
    b.father_name = x.father_name ?? '';
    b.phone_number = x.phone_number ?? '';

    b.main_province = x.main_province ?? '';
    b.main_district = x.main_district ?? '';
    b.main_village = x.main_village ?? '';
    b.now_province = x.now_province ?? '';
    b.now_zone = x.now_zone ?? '';
    b.now_district = x.now_district ?? '';
    b.now_village = x.now_village ?? '';
    b.specific_place = x.specific_place ?? '';
    b.street_number = x.street_number ?? '';
    b.home_number = x.home_number ?? '';

    b.old_job = x.old_job ?? '';
    b.now_job = x.now_job ?? '';
    b.done_roberies = x.done_roberies ?? '';
    b.gun_type = x.gun_type ?? '';
    b.Province_addr = x.Province_addr ?? '';
    b.coordinates = x.coordinates ?? '';

    const r = x.resource || {};
    b.resource_code = x.resource_code ?? r.code_number ?? '';
    b.resource_phone_number = x.resource_phone_number ?? r.phone_number ?? '';
    b.resource_id = x.resource_id ?? r.id ?? null;

    b.note = x.note ?? '';

    // ✅ docs from API
    existingDocs.value = Array.isArray(x.documents) ? x.documents : [];
    removedDocIds.value = [];
    newDocs.value = [];

    return b;
  }

  async function loadRecord() {
    const id = route.params.id;
    if (!id) {
      form.value = blankForm();
      pictureFile.value = null;
      pictureChanged.value = false;

      // docs reset
      existingDocs.value = [];
      removedDocIds.value = [];
      newDocs.value = [];

      return;
    }

    try {
      await repo.FetchHeistData?.(id);
      const single = Array.isArray(repo.Heist) ? repo.Heist[0] : repo.Heist || {};
      if (!single) return;

      const mapped = mapFromApi(single);
      form.value = { ...mapped };
      pictureFile.value = null;
      pictureChanged.value = false;
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات هېست'));
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
    delete payload.previewUrl; // UI-only

    const fd = toFormData(payload);

    if (pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

    // ✅ new documents: documents[]
    for (const f of newDocs.value) {
      fd.append('documents[]', f);
    }

    return fd;
  }

  function buildUpdateFormData() {
    const payload = { ...form.value };
    delete payload.previewUrl;
    delete payload.picture; // keep current unless uploading new

    const fd = toFormData(payload);

    if (pictureChanged.value && pictureFile.value) {
      fd.append('picture', pictureFile.value);
    }

    // ✅ removed_document_ids[]
    for (const id of removedDocIds.value) {
      fd.append('removed_document_ids[]', id);
    }

    // ✅ new documents: documents[]
    for (const f of newDocs.value) {
      fd.append('documents[]', f);
    }

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
   SIMPLE EXPORT (NO IMAGE, NO APP HEADERS)
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
      { label: 'نام پدر', value: form.value.father_name },
      { label: 'شماره تماس', value: form.value.phone_number },

      { label: 'ولایت اصلی', value: form.value.main_province },
      { label: 'ولسوالی اصلی', value: form.value.main_district },
      { label: 'قریه/ناحیه اصلی', value: form.value.main_village },

      { label: 'ولایت فعلی', value: form.value.now_province },
      { label: 'زون فعلی', value: form.value.now_zone },
      { label: 'ولسوالی فعلی', value: form.value.now_district },
      { label: 'قریه/ناحیه فعلی', value: form.value.now_village },

      { label: 'موقعیت مشخص', value: form.value.specific_place },
      { label: 'سرک نمبر', value: form.value.street_number },
      { label: 'نمبر خانه', value: form.value.home_number },

      { label: 'وظیفه قبلی', value: form.value.old_job },
      { label: 'وظیفه فعلی', value: form.value.now_job },

      { label: 'سرقت‌های انجام‌شده', value: form.value.done_roberies },
      { label: 'نوع سلاح', value: form.value.gun_type },

      { label: 'ولایت مربوطه', value: form.value.Province_addr },
      { label: 'مختصات', value: form.value.coordinates },

      { label: 'کود منبع', value: form.value.resource_code },
      { label: 'شماره منبع', value: form.value.resource_phone_number },

      { label: 'ملاحظات', value: form.value.note },
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
    <h3 class="title">فورم هېست</h3>
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
    const safeId = esc(form.value.id || '');
    const safeName = esc(form.value.name || '');

    return `
<!DOCTYPE html>
<html dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>heist_${safeId}_${safeName}</title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }

    html, body {
      margin: 0;
      padding: 0;
      background: #ffffff;
      font-family: Tahoma, Arial, sans-serif;
      font-size: 12px;
      direction: rtl;
    }

    .title {
      text-align: center;
      margin: 22px 0 12px;
      font-size: 16px;
      font-weight: 700;
    }

    .simple-table {
      width: 95%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-left: 12px;
      margin-right: 12px;
    }

    .simple-table th,
    .simple-table td {
      border: 1px solid #000;
      padding: 6px 8px;
      vertical-align: top;
      font-size: 12px;
    }

    .simple-table th {
      width: 35%;
      background: #f5f5f5;
      text-align: right;
      font-weight: 700;
    }

    .simple-table td {
      width: 65%;
      text-align: right;
      word-break: break-word;
    }

    .sig {
      margin-top: 18px;
      margin-right: 16px;
      display: flex;
      justify-content: space-between;
      gap: 12px;
      font-size: 12px;
    }
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
    link.download = `هېست_${form.value.name || 'جدید'}_${Date.now()}.doc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    URL.revokeObjectURL(url);
    showSuccessMessage(t('فایل Word با موفقیت ایجاد شد'));
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

    if (!form.value.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
      return;
    }

    saving.value = true;

    try {
      if (!isEdit.value) {
        const fd = buildCreateFormData();
        const created = await repo.CreateHeist?.(fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (created?.id) {
          showSuccessMessage(t('موفقانه ثبت شد'));
          router.push('/app/heist').catch(() => {});
        } else {
          showErrorMessage(t('ثبت معلومات ناکام شد'));
        }
      } else {
        const fd = buildUpdateFormData();
        const updated = await repo.UpdateHeist?.(form.value.id, fd).catch((e) => {
          if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
          return null;
        });

        if (updated?.id) {
          showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
          router.push('/app/heist').catch(() => {});
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

      // docs reset
      existingDocs.value = [];
      removedDocIds.value = [];
      newDocs.value = [];
    }

    clearBackendErrors();
    formRef.value?.resetValidation?.();
    showSuccessMessage(t('فرم پاک شد'));
  }

  function goBack() {
    router.push('/app/heist');
  }

  /* INIT */
  onMounted(async () => {
    await repo.GetResourceCode?.();
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
</style>
