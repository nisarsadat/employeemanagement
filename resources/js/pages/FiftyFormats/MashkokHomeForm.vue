<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="isEdit ? t('ویرایش خانه مشکوک') : t('ایجاد خانه مشکوک جدید')"
      :subtitle="
        isEdit
          ? t('ویرایش جزئیات یک ریکارډ خانه مشکوک موجود')
          : t('ثبت یک ریکارډ خانه مشکوک جدید در سیستم')
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
          <!-- =========================
               ✅ PICTURE SECTION (Saeen Fasad style)
               ✅ Mashkok logic kept
          ========================== -->
          <v-row>
            <!-- Picture -->
            <v-col cols="12" md="3">
              <div class="d-flex flex-column align-center gap-2">
                <div class="text-subtitle-2 font-weight-medium mb-2">
                  {{ t('تصویر') }}
                </div>

                <div class="pic-wrap-simple">
                  <input
                    ref="pictureInputRef"
                    class="hidden-file"
                    type="file"
                    accept="image/*"
                    @change="onPickPicture"
                  />

                  <v-avatar
                    size="88"
                    class="mb-2 cursor-pointer primary-avatar"
                    :key="picturePreview || pictureUrlFromApi || form.picture || 'no-picture'"
                    @click="triggerPicture"
                  >
                    <v-img
                      v-if="picturePreview || pictureUrlFromApi || form.picture"
                      :src="normalizePicture(picturePreview || pictureUrlFromApi || form.picture)"
                      :alt="t('عکس')"
                      cover
                    />
                    <v-icon v-else size="40">mdi-home-city</v-icon>
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
                      v-if="picturePreview || pictureUrlFromApi || form.picture"
                      size="small"
                      color="secondary"
                      variant="tonal"
                      class="text-none rounded-pill soft-btn"
                      @click.stop="openPreview(picturePreview || pictureUrlFromApi || form.picture)"
                    >
                      <v-icon start size="22">mdi-eye</v-icon>
                      {{ t('نمایش') }}
                    </v-btn>

                    <v-btn
                      v-if="picturePreview || pictureUrlFromApi || form.picture"
                      size="small"
                      color="red"
                      variant="tonal"
                      class="text-none rounded-pill soft-btn"
                      @click.stop="clearPicture"
                    >
                      <v-icon start size="22">mdi-delete</v-icon>
                      {{ t('حذف عکس') }}
                    </v-btn>
                  </div>
                </div>
              </div>
            </v-col>

            <!-- Right side content -->
            <v-col cols="12" md="9">
              <!-- Location -->
              <v-row class="mt-1">
                <v-col cols="12" md="12">
                  <ProvinceDistrictNameSelect
                    v-model:province="form.province"
                    v-model:district="form.district"
                    :province-label="t('ولایت ')"
                    :district-label="t('ولسوالی ')"
                    :mdProvince="6"
                    :mdDistrict="6"
                  />
                </v-col>

                <v-col cols="12" md="9">
                  <FormatZoneAutocomplete
                    v-model="form.zone"
                    :label="t('زون')"
                    :disabled="saving"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.village"
                    :label="t('قریه/ناحیه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('village')"
                    :error-messages="getErrorMessages('village')"
                  />
                </v-col>
              </v-row>

              <!-- Main address details -->
              <div class="section-header mt-4">
                <span class="section-title">{{ t('آدرس خانه مشکوک') }}</span>
              </div>

              <v-row>
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
                    :label="t('شماره سرک')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('street_number')"
                    :error-messages="getErrorMessages('street_number')"
                  />
                </v-col>

                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.home_number"
                    :label="t('شماره خانه')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('home_number')"
                    :error-messages="getErrorMessages('home_number')"
                  />
                </v-col>

                <v-col cols="12" md="12">
                  <v-text-field
                    v-model="form.coordinates"
                    :label="t('مختصات')"
                    variant="outlined"
                    density="compact"
                    :error="hasError('coordinates')"
                    :error-messages="getErrorMessages('coordinates')"
                  />
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <!-- Extra fields & source -->
          <v-divider class="my-4" />

          <v-row>
            <v-col cols="12" md="4">
              <v-autocomplete
                v-model="form.home_type"
                :items="['خپل', 'کرایی', 'ګرویي']"
                :label="t('نوع خانه')"
                variant="outlined"
                density="compact"
                clearable
                hide-details="auto"
                :error="hasError('home_type')"
                :error-messages="getErrorMessages('home_type')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.shak_symbols"
                :label="t('علایم شک')"
                variant="outlined"
                density="compact"
                :error="hasError('shak_symbols')"
                :error-messages="getErrorMessages('shak_symbols')"
              />
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.mashkok_type"
                :label="t('نوعیت مشکوکیت')"
                variant="outlined"
                density="compact"
              />
            </v-col>

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
                v-model="form.resource_code"
                :label="t('کود منبع')"
                variant="outlined"
                density="compact"
                readonly
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

            <!-- Note -->
            <v-col cols="12" md="12">
              <v-text-field
                v-model="form.note"
                :label="t('یادداشت')"
                variant="outlined"
                density="compact"
                :error="hasError('note')"
                :error-messages="getErrorMessages('note')"
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-textarea
                v-model="form.tafseel"
                :label="t('تفصیل')"
                variant="outlined"
                density="compact"
                rows="4"
                auto-grow
              />
            </v-col>
          </v-row>

          <!-- =========================
               ✅ DOCUMENTS SECTION
          ========================== -->
          <v-divider class="my-4" />
          <div class="docs-head">
            <div class="d-flex align-center ga-2">
              <v-tooltip :text="t('دانلود تمام اسناد (ZIP)')" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-btn
                    v-bind="tooltipProps"
                    color="green"
                    variant="outlined"
                    class="text-none rounded-pill secondary-action-btn"
                    prepend-icon="mdi-zip-box"
                    :disabled="!isEdit || !hasAnyDocs || downloadingZip"
                    :loading="downloadingZip"
                    @click="downloadAllDocsZip"
                  >
                    {{ t('دانلود ZIP') }}
                  </v-btn>
                </template>
              </v-tooltip>
            </div>
          </div>

          <div class="docs-card">
            <!-- Uploader -->
            <v-row class="mt-1">
              <v-col cols="12" md="12">
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
              </v-col>
            </v-row>

            <!-- Existing docs -->
            <div v-if="existingDocs.length" class="docs-block2">
              <div class="docs-title2">
                <v-icon size="18">mdi-folder</v-icon>
                <span>{{ t('اسناد موجود') }}</span>
                <v-spacer />
                <v-chip size="small" color="primary" variant="tonal">
                  {{ existingDocs.length }}
                </v-chip>
              </div>

              <div class="docs-grid">
                <div v-for="doc in existingDocs" :key="doc.id" class="doc-item">
                  <div class="doc-left">
                    <v-icon size="22" color="primary">mdi-file</v-icon>
                  </div>

                  <div class="doc-mid">
                    <div class="doc-name">
                      {{ doc.file_name || doc.original_name || doc.name || 'doc_' + doc.id }}
                    </div>
                    <div class="doc-meta">
                      <span v-if="doc.mime_type">{{ doc.mime_type }}</span>
                      <span v-if="doc.size"> • {{ formatBytes(doc.size) }}</span>
                    </div>
                  </div>

                  <div class="doc-right">
                    <v-tooltip :text="t('حذف از ریکارډ (بعد از ذخیره)')" location="top">
                      <template #activator="{ props: removeProps }">
                        <v-btn
                          v-bind="removeProps"
                          icon
                          size="small"
                          variant="text"
                          color="red"
                          @click.stop="removeExistingDoc(doc)"
                        >
                          <v-icon>mdi-delete</v-icon>
                        </v-btn>
                      </template>
                    </v-tooltip>
                  </div>
                </div>
              </div>
            </div>

            <!-- New docs -->
            <div v-if="newDocs.length" class="docs-block2 mt-3">
              <div class="docs-title2">
                <v-icon size="18" color="green">mdi-cloud-upload</v-icon>
                <span>{{ t('اسناد جدید (در انتظار ذخیره)') }}</span>
                <v-spacer />
                <v-chip size="small" color="green" variant="tonal">{{ newDocs.length }}</v-chip>
              </div>

              <div class="docs-grid">
                <div v-for="(f, idx) in newDocs" :key="idx" class="doc-item doc-item-new">
                  <div class="doc-left">
                    <v-icon size="22" color="green">mdi-file-plus</v-icon>
                  </div>

                  <div class="doc-mid">
                    <div class="doc-name">
                      {{ f?.name || 'file_' + idx }}
                    </div>
                    <div class="doc-meta" v-if="f?.size">
                      {{ formatBytes(f.size) }}
                    </div>
                  </div>

                  <div class="doc-right">
                    <v-tooltip :text="t('حذف از لیست')" location="top">
                      <template #activator="{ props: removeProps }">
                        <v-btn
                          v-bind="removeProps"
                          icon
                          size="small"
                          variant="text"
                          color="red"
                          @click.stop="removeNewDoc(idx)"
                        >
                          <v-icon>mdi-close</v-icon>
                        </v-btn>
                      </template>
                    </v-tooltip>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="!existingDocs.length && !newDocs.length" class="docs-empty">
              <v-icon size="26" color="grey-darken-1">mdi-file-document-outline</v-icon>
              <div class="text-body-2">{{ t('هیچ سندی انتخاب نشده است') }}</div>
            </div>
          </div>

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
          <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
        </template>
      </v-snackbar>

      <!-- Success Snackbar -->
      <v-snackbar v-model="showSuccess" color="success" timeout="3000">
        {{ successMessage }}
        <template #actions>
          <v-btn variant="text" @click="showSuccess = false">{{ t('بستن') }}</v-btn>
        </template>
      </v-snackbar>
    </v-container>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import ProvinceDistrictNameSelect from '@/components/ProvinceDistrictSelect.vue';

import AppBar from '@/components/AppBar.vue';
import { useMawadRepository } from '@/stores/MawadRepository';
import FormatZoneAutocomplete from '@/components/FormatZoneAutocomplete.vue';
import ResourceRemoteAutocomplete from '@/components/lookup/ResourceRemoteAutocomplete.vue';

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

/* RULES */
const rules = {
  required: (v) => {
    if (v === 0) return true;
    if (v === null || v === undefined) return t('این فیلد الزامی است');
    const s = String(v).trim();
    return s.length > 0 || t('این فیلد الزامی است');
  },
};

/* FORM MODEL */
const blankForm = () => ({
  id: null,

  province: '',
  zone: '',
  district: '',
  village: '',

  specific_place: '',
  street_number: '',
  home_number: '',
  coordinates: '',

  home_type: '',
  shak_symbols: '',
  mashkok_type: '',
  Province_addr: '',

  resource_id: null,
  resource_code: '',
  resource_phone: '',

  note: '',
  tafseel: '',

  picture: null,
});

const form = ref(blankForm());

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

/* =========================
✅ PICTURE STATE (Saeen-style UI, Mashkok logic kept)
========================= */
const pictureInputRef = ref(null);
const picturePreview = ref('');
const pictureUrlFromApi = ref('');
const previewOpen = ref(false);
const previewSrc = ref(null);

function revokePreview() {
  if (picturePreview.value && String(picturePreview.value).startsWith('blob:')) {
    URL.revokeObjectURL(picturePreview.value);
  }
  picturePreview.value = '';
}

function triggerPicture() {
  pictureInputRef.value?.click();
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

function onPickPicture(e) {
  const file = e?.target?.files?.[0] ?? null;

  revokePreview();

  if (!file) {
    form.value.picture = null;
    if (e?.target) e.target.value = '';
    return;
  }

  form.value.picture = file;
  picturePreview.value = URL.createObjectURL(file);

  if (e?.target) e.target.value = '';
}

function openPreview(src) {
  const base = src || picturePreview.value || pictureUrlFromApi.value || form.value.picture;
  const normalized = normalizePicture(base);
  previewSrc.value = normalized;
  if (previewSrc.value) previewOpen.value = true;
}

function clearPicture() {
  revokePreview();
  form.value.picture = null;
  pictureUrlFromApi.value = '';
  if (pictureInputRef.value) pictureInputRef.value.value = '';
}

onBeforeUnmount(() => revokePreview());

/* =========================
✅ DOCUMENTS STATE
========================= */
const existingDocs = ref([]);
const removed_document_ids = ref([]);
const newDocs = ref([]);
const newDocsInput = ref([]);

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

/* ZIP download */
const downloadingZip = ref(false);
async function downloadAllDocsZip() {
  if (!isEdit.value) return;
  const id = form.value.id;
  if (!id) return;

  const name = `mashkok_home_${id}`;
  try {
    downloadingZip.value = true;
    if (typeof repo.DownloadMashkokHomesZip === 'function') {
      await repo.DownloadMashkokHomesZip(id, name);
    } else if (typeof repo.DownloadMashkokHomeZip === 'function') {
      await repo.DownloadMashkokHomeZip(id, name);
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

/* MAP FROM API */
function mapFromApi(payload = {}) {
  const x = payload.data ?? payload;
  const b = blankForm();

  b.id = x.id ?? null;

  b.province = x.province ?? '';
  b.zone = x.zone ?? '';
  b.district = x.district ?? '';
  b.village = x.village ?? '';

  b.specific_place = x.specific_place ?? '';
  b.street_number = x.street_number ?? '';
  b.home_number = x.home_number ?? '';
  b.coordinates = x.coordinates ?? '';

  b.home_type = x.home_type ?? '';
  b.shak_symbols = x.shak_symbols ?? '';
  b.mashkok_type = x.mashkok_type ?? '';
  b.Province_addr = x.Province_addr ?? '';

  const r = x.resource || {};
  b.resource_id = x.resource_id ?? r.id ?? null;
  b.resource_code = x.resource_code ?? r.code_number ?? '';
  b.resource_phone = x.resource_phone ?? r.phone_number ?? '';

  b.note = x.note ?? '';
  b.tafseel = x.tafseel ?? '';

  pictureUrlFromApi.value = x.picture_url ?? x.picture ?? '';

  return { b, x };
}

async function loadRecord() {
  const id = route.params.id;
  if (!id) {
    form.value = blankForm();
    existingDocs.value = [];
    removed_document_ids.value = [];
    newDocs.value = [];
    newDocsInput.value = [];
    pictureUrlFromApi.value = '';
    clearPicture();
    return;
  }

  try {
    await repo.FetchMashkokHomeData?.(id);
    const single = Array.isArray(repo.MashkokHome) ? repo.MashkokHome[0] : repo.MashkokHome || {};
    if (!single) return;

    const { b, x } = mapFromApi(single);
    form.value = { ...b };

    existingDocs.value = normalizeDocsFromApi(x);
    removed_document_ids.value = [];
    newDocs.value = [];
    newDocsInput.value = [];

    revokePreview();
    if (pictureInputRef.value) pictureInputRef.value.value = '';
    form.value.picture = null;
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات خانه مشکوک'));
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

function buildCreateFormData() {
  const payload = { ...form.value };
  const fd = toFormData(payload);
  appendDocsToFormData(fd);
  return fd;
}

function buildUpdateFormData() {
  const payload = { ...form.value };
  const fd = toFormData(payload);
  appendDocsToFormData(fd);
  return fd;
}

/* helpers */
function formatBytes(bytes) {
  const n = Number(bytes);
  if (!n || n <= 0) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB'];
  let i = 0;
  let v = n;
  while (v >= 1024 && i < units.length - 1) {
    v /= 1024;
    i++;
  }
  return `${v.toFixed(v >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
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
EXPORT
========================= */
function esc(v) {
  return String(v ?? '').replace(/[&<>"']/g, (m) => {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return map[m];
  });
}
function safeFileName(s) {
  return String(s ?? '')
    .replace(/[\\/:*?"<>|]/g, '-')
    .replace(/\s+/g, ' ')
    .trim();
}
function v(x) {
  return x && String(x).trim() ? x : '—';
}
function exportRows() {
  return [
    { label: t('ولایت'), value: v(form.value.province) },
    { label: t('زون'), value: v(form.value.zone) },
    { label: t('ولسوالی'), value: v(form.value.district) },
    { label: t('قریه/ناحیه'), value: v(form.value.village) },
    { label: t('موقعیت مشخص'), value: v(form.value.specific_place) },
    { label: t('شماره سرک'), value: v(form.value.street_number) },
    { label: t('شماره خانه'), value: v(form.value.home_number) },
    { label: t('مختصات'), value: v(form.value.coordinates) },
    { label: t('نوع خانه'), value: v(form.value.home_type) },
    { label: t('نوعیت مشکوکیت'), value: v(form.value.mashkok_type) },
    { label: t('علایم شک'), value: v(form.value.shak_symbols) },
    { label: t('ولایت مربوطه'), value: v(form.value.Province_addr) },
    { label: t('کود منبع'), value: v(form.value.resource_code) },
    { label: t('شماره منبع'), value: v(form.value.resource_phone) },
    { label: t('یادداشت'), value: v(form.value.note) },
    { label: t('تفصیل'), value: v(form.value.tafseel) },
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
    <h3 class="title">د مشکوکو کورونو فورم</h3>
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
  const safeName = esc(form.value.province || '');

  return `<!DOCTYPE html>
<html dir="rtl" xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8" />
  <title>mashkokhome_${safeId}_${safeName}</title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }
    html, body { margin: 0; padding: 0; background: #fff; font-family: Tahoma, Arial, sans-serif; font-size: 12px; direction: rtl; }
    .title { text-align: center; margin: 22px 0 12px; font-size: 16px; font-weight: 700; }
    .simple-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 auto; }
    .simple-table th, .simple-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; font-size: 12px; }
    .simple-table th { width: 35%; background: #f5f5f5; text-align: right; font-weight: 700; }
    .simple-table td { width: 65%; text-align: right; word-break: break-word; }
    .sig { margin-top: 18px; display: flex; justify-content: space-between; gap: 12px; font-size: 12px; }
  </style>
</head>
<body>
  ${bodyHtml}
</body>
</html>`;
}
function downloadPdf() {
  const html = buildDocumentHtml();
  const w = window.open('', '_blank', 'width=1024,height=768');
  if (!w) return;

  w.document.open();
  w.document.write(html);
  w.document.close();

  setTimeout(() => {
    w.focus();
    w.print();
  }, 350);
}
function downloadWord() {
  const html = buildDocumentHtml();
  const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
  const url = URL.createObjectURL(blob);

  const link = document.createElement('a');
  link.href = url;
  link.download = `خانه_مشکوک_${safeFileName(form.value.province || 'جدید')}_${Date.now()}.doc`;
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

  if (!form.value.province?.trim() || !form.value.district?.trim()) {
    showErrorMessage(t('ولایت و ولسوالی الزامی است'));
    return;
  }

  saving.value = true;

  try {
    if (!isEdit.value) {
      const fd = buildCreateFormData();
      const created = await repo.CreateMashkokHome?.(fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (created?.id) {
        showSuccessMessage(t('موفقانه ثبت شد'));
        router.push('/app/mashkokhomes').catch(() => {});
      } else {
        showErrorMessage(t('ثبت معلومات ناکام شد'));
      }
    } else {
      const fd = buildUpdateFormData();
      const updated = await repo.UpdateMashkokHome?.(form.value.id, fd).catch((e) => {
        if (e?.response?.data?.errors) setBackendErrors(e.response.data.errors);
        return null;
      });

      if (updated?.id) {
        showSuccessMessage(t('تغییرات موفقانه ذخیره شد'));
        router.push('/app/mashkokhomes').catch(() => {});
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
  if (isEdit.value) {
    loadRecord();
  } else {
    form.value = blankForm();
    existingDocs.value = [];
    removed_document_ids.value = [];
    newDocs.value = [];
    newDocsInput.value = [];
    clearPicture();
  }

  clearBackendErrors();
  formRef.value?.resetValidation?.();
  showSuccessMessage(t('فرم پاک شد'));
}

function goBack() {
  router.push('/app/mashkokhomes');
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

/* picture like saeen fasad */
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
  background: #fff;
}

/* gap utils */
.gap-2 {
  gap: 8px;
}
.ga-2 {
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

/* docs UI */
.docs-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}

.docs-card {
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 14px;
  padding: 12px;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.72));
}

.docs-block2 {
  border: 1px dashed rgba(0, 0, 0, 0.18);
  border-radius: 14px;
  padding: 10px;
  background: rgba(255, 255, 255, 0.6);
}

.docs-title2 {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 800;
  opacity: 0.85;
  margin-bottom: 10px;
}

.docs-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}
@media (max-width: 960px) {
  .docs-grid {
    grid-template-columns: 1fr;
  }
}

.doc-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}
.doc-item-new {
  border-color: rgba(76, 175, 80, 0.28);
}

.doc-left {
  width: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.doc-mid {
  flex: 1;
  min-width: 0;
}
.doc-name {
  font-size: 12px;
  font-weight: 700;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.doc-meta {
  font-size: 11px;
  opacity: 0.65;
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.doc-right {
  display: flex;
  align-items: center;
}

.docs-empty {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px;
  margin-top: 10px;
  border-radius: 12px;
  background: rgba(0, 0, 0, 0.03);
}
</style>