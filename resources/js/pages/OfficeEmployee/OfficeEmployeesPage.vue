<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('کارمندان مدیریت')" :subtitle="t('اضافه کردن یک کارمند مدیریت')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- Search -->
    <v-row class="d-flex">
      <v-col>
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetchAll"
          clearable
          @click:clear="
            () => {
              repo.Search = '';
              fetchAll();
            }
          "
        />
      </v-col>
    </v-row>

    <v-form
      ref="editorForm"
      v-model="editorValid"
      lazy-validation
      :key="formKey"
      @submit.prevent="onSubmit(rows[0])"
    >
      <!-- Single-row editor (create/edit) — NO pagination/footers -->
      <v-card class="tablebody" ref="editorCard">
        <v-data-table
          :headers="headers"
          :items="rows"
          :loading="repo.loading"
          item-key="idOrTmp"
          class="elevation-2 custom-table"
          :dir="isRtl ? 'rtl' : 'ltr'"
          density="comfortable"
          hide-default-footer
        >
          <!-- Picture -->
          <template #item.picture="{ item }">
            <div class="pic-wrap">
              <input
                class="hidden-file"
                type="file"
                accept="image/*"
                :id="`up-${item.idOrTmp}`"
                @change="(e) => onPickFile(item, e.target.files?.[0] ?? null)"
              />
              <v-btn
                v-if="!item.previewUrl && !item.picture"
                size="small"
                color="primary"
                variant="outlined"
                icon
                @click="triggerFile(item)"
              >
                <v-icon>mdi-plus</v-icon>
              </v-btn>
              <div v-else class="pic-thumb" :title="t('گزینه ها')">
                <v-avatar size="32">
                  <v-img :src="item.previewUrl || item.picture" :alt="t('عکس')" cover />
                </v-avatar>
                <div class="pic-overlay">
                  <v-btn
                    size="small"
                    color="secondary"
                    variant="flat"
                    @click.stop="triggerFile(item)"
                  >
                    <v-icon start>mdi-pencil</v-icon>{{ t('ویرایش') }}
                  </v-btn>
                  <v-btn
                    size="small"
                    color="secondary"
                    @click.stop="openPreview(item.previewUrl || item.picture)"
                  >
                    <v-icon start>mdi-eye</v-icon>{{ t('نمایش') }}
                  </v-btn>
                </div>
              </div>
            </div>
          </template>

          <!-- Identity -->
          <template #item.name="{ item }">
            <v-text-field
              v-model="item.draft.name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام')"
              :rules="[rules.required]"
              hide-details
              required
              :error="hasError(item, 'name')"
              :error-messages="getErrorMessages(item, 'name')"
            />
          </template>

          <template #item.father_name="{ item }">
            <v-text-field
              v-model="item.draft.father_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدر')"
              :rules="[rules.required]"
              hide-details
              :error="hasError(item, 'father_name')"
              :error-messages="getErrorMessages(item, 'father_name')"
            />
          </template>

          <template #item.grand_father_name="{ item }">
            <v-text-field
              v-model="item.draft.grand_father_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام پدرکلان')"
              hide-details
              :error="hasError(item, 'grand_father_name')"
              :error-messages="getErrorMessages(item, 'grand_father_name')"
            />
          </template>

          <template #item.nick_name="{ item }">
            <v-text-field
              v-model="item.draft.nick_name"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نام مستعار')"
              hide-details
              :error="hasError(item, 'nick_name')"
              :error-messages="getErrorMessages(item, 'nick_name')"
            />
          </template>

          <template #item.code_number="{ item }">
            <v-text-field
              v-model="item.draft.code_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('کود نمبر')"
              hide-details
              :error="hasError(item, 'code_number')"
              :error-messages="getErrorMessages(item, 'code_number')"
            />
          </template>

          <!-- Rank / Levels -->
          <template #item.bast="{ item }">
            <v-text-field
              v-model="item.draft.bast"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('بست')"
              hide-details
              :error="hasError(item, 'bast')"
              :error-messages="getErrorMessages(item, 'bast')"
            />
          </template>

          <template #item.level="{ item }">
            <v-text-field
              v-model="item.draft.level"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('رتبه/سطح')"
              hide-details
              :error="hasError(item, 'level')"
              :error-messages="getErrorMessages(item, 'level')"
            />
          </template>

          <!-- Background -->
          <template #item.history_jahad="{ item }">
            <v-text-field
              v-model="item.draft.history_jahad"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('سابقه جهاد')"
              hide-details
            />
          </template>

          <template #item.dilgay_mesher="{ item }">
            <v-text-field
              v-model="item.draft.dilgay_mesher"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('دلګی/مشری')"
              hide-details
            />
          </template>
          <template #item.jahadi_mawaqaf="{ item }">
            <v-select
              v-model="item.draft.jahadi_mawaqaf"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مواقف جهادی')"
              :items="repo.getJahadiMawqafOptions()"
              hide-details
            />
          </template>

          <template #item.jahad_time_responsibility="{ item }">
            <v-text-field
              v-model="item.draft.jahad_time_responsibility"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مسئولیت در زمان جهاد')"
              hide-details
            />
          </template>

          <template #item.tazkira="{ item }">
            <v-text-field
              v-model="item.draft.tazkira"
              density="compact"
              class="text-group"
              variant="outlined"
              :label="t('شماره تذکره')"
              hide-details
              :error="hasError(item, 'tazkira')"
              :error-messages="getErrorMessages(item, 'tazkira')"
            />
          </template>

          <!-- Jahad Place -->
          <template #item.jahad_place="{ item }">
            <v-text-field
              v-model="item.draft.jahad_place"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('محل جهاد')"
              hide-details
            />
          </template>

          <!-- Main address -->
          <template #item.main_province="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.main_province"
                :rules="[rules.required]"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولایت اصلی')"
                :error="hasError(item, 'main_province')"
                :error-messages="getErrorMessages(item, 'main_province')"
              />
              <v-text-field
                v-model="item.draft.main_district"
                density="compact"
                variant="outlined"
                :rules="[rules.required]"
                hide-details
                :label="t('ولسوالی اصلی')"
                :error="hasError(item, 'main_district')"
                :error-messages="getErrorMessages(item, 'main_district')"
              />
              <v-text-field
                v-model="item.draft.main_village"
                density="compact"
                :rules="[rules.required]"
                variant="outlined"
                :label="t('قریه/ناحیه اصلی')"
                hide-details
                :error="hasError(item, 'main_village')"
                :error-messages="getErrorMessages(item, 'main_village')"
              />
            </div>
          </template>

          <!-- Now address -->
          <template #item.now_province="{ item }">
            <div class="addr-group">
              <v-text-field
                v-model="item.draft.now_province"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولایت فعلی')"
              />
              <v-text-field
                v-model="item.draft.now_district"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('ولسوالی فعلی')"
              />
              <v-text-field
                v-model="item.draft.now_village"
                density="compact"
                variant="outlined"
                hide-details
                :label="t('قریه/ناحیه فعلی')"
              />
            </div>
          </template>

          <template #item.now_specific_place="{ item }">
            <v-text-field
              v-model="item.draft.now_specific_place"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('محل مشخص فعلی')"
              hide-details
            />
          </template>

          <template #item.now_street_number="{ item }">
            <v-text-field
              v-model="item.draft.now_street_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نمبر سرک')"
              hide-details
            />
          </template>

          <template #item.now_home_number="{ item }">
            <v-text-field
              v-model="item.draft.now_home_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نمبر خانه')"
              hide-details
            />
          </template>

          <!-- Job / Org -->
          <template #item.specific_job="{ item }">
            <v-text-field
              v-model="item.draft.specific_job"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('وظیفه مشخص')"
              hide-details
            />
          </template>

          <template #item.qisem="{ item }">
            <v-text-field
              v-model="item.draft.qisem"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('قسم')"
              hide-details
            />
          </template>

          <!-- Zone -->
          <template #item.zone="{ item }">
            <v-select
              v-model="item.draft.zone"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('زون')"
              :items="repo.getZoneOptions()"
              hide-details
              @update:modelValue="onZoneChange(item)"
            />
          </template>

          <!-- Hawza -->
          <template #item.hawza="{ item }">
            <v-select
              v-model="item.draft.hawza"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('حوزه')"
              :items="hawzaItems(item)"
              :rules="[(v) => hawzaValidForZone(item, v)]"
              hide-details
              @update:modelValue="onHawzaChange(item)"
            />
          </template>

          <!-- Status & education -->
          <template #item.madani_halat="{ item }">
            <v-select
              v-model="item.draft.madani_halat"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('حالت مدنی')"
              :items="repo.getHalatMadaniOptions()"
              hide-details
            />
          </template>

          <template #item.activity="{ item }">
            <v-text-field
              v-model="item.draft.activity"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('فعالیت')"
              hide-details
            />
          </template>

          <template #item.relegy_study="{ item }">
            <v-text-field
              v-model="item.draft.relegy_study"
              class="text-group"
              density="compact"
              :label="t('تعلیمات دینی')"
              variant="outlined"
              hide-details
            />
          </template>

          <template #item.new_study="{ item }">
            <v-text-field
              v-model="item.draft.new_study"
              class="text-group"
              density="compact"
              :label="t('تعلیمات عصری')"
              variant="outlined"
              hide-details
            />
          </template>

          <template #item.faculty="{ item }">
            <v-text-field
              v-model="item.draft.faculty"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('پوهنحی/رشته')"
              hide-details
            />
          </template>

          <template #item.nizami="{ item }">
            <v-text-field
              v-model="item.draft.nizami"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('نظامی')"
              hide-details
            />
          </template>

          <template #item.gun_knowledge="{ item }">
            <v-text-field
              v-model="item.draft.gun_knowledge"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('معلومات سلاح')"
              hide-details
            />
          </template>

          <!-- Demographic -->
          <template #item.age="{ item }">
            <v-text-field
              v-model="item.draft.age"
              class="text-group"
              density="compact"
              variant="outlined"
              type="number"
              :label="t('سن')"
              hide-details
            />
          </template>

          <template #item.specific_skills="{ item }">
            <v-text-field
              v-model="item.draft.specific_skills"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مهارت‌های مشخص')"
              hide-details
            />
          </template>

          <template #item.language="{ item }">
            <v-text-field
              v-model="item.draft.language"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('زبان')"
              hide-details
            />
          </template>

          <template #item.nation="{ item }">
            <v-text-field
              v-model="item.draft.nation"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('قوم/ملیت')"
              hide-details
            />
          </template>

          <!-- Contact -->
          <template #item.phone_number="{ item }">
            <v-text-field
              v-model="item.draft.phone_number"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس')"
              hide-details
              :error="hasError(item, 'phone_number')"
              :error-messages="getErrorMessages(item, 'phone_number')"
            />
          </template>

          <template #item.coordinates="{ item }">
            <v-text-field
              v-model="item.draft.coordinates"
              class="text-group"
              density="compact"
              variant="outlined"
              :label="t('مختصات')"
              hide-details
            />
          </template>

          <!-- Dates -->
          <template #item.join_date="{ item }">
            <DateInput v-model="item.draft.join_date" class="text-group" :label="t('تاریخ آمدن')" />
          </template>

          <template #item.left_date="{ item }">
            <DateInput v-model="item.draft.left_date" class="text-group" :label="t('تاریخ رفتن')" />
          </template>

          <!-- Note -->
          <template #item.note="{ item }">
            <v-text-field
              v-model="item.draft.note"
              density="compact"
              class="text-group-note"
              variant="outlined"
              :label="t('ملاحظات')"
              hide-details
              :error="hasError(item, 'note')"
              :error-messages="getErrorMessages(item, 'note')"
            />
          </template>

          <!-- Documents -->
          <template #item.documents="{ item }">
            <div class="documents-inline">
              <input
                class="hidden-file"
                :id="`docs-${item.idOrTmp}`"
                type="file"
                multiple
                @change="(e) => onPickDocs(item, Array.from(e.target.files || []))"
              />

              <div class="docs-field-row">
                <v-btn
                  class="uploader-btn2"
                  size="small"
                  variant="text"
                  density="compact"
                  prepend-icon="mdi-tray-arrow-up"
                  @click="triggerDocs(item)"
                >
                  {{ t('آپلود فایل ها') }}
                </v-btn>

                <div
                  class="chips-row"
                  :title="
                    item.draft._newDocs?.length || item.existingDocs?.length
                      ? ''
                      : t('فایلی انتخاب نشده است')
                  "
                >
                  <!-- new files (unsaved) -->
                  <v-chip
                    v-for="(f, idx) in item.draft._newDocs || []"
                    :key="`${item.idOrTmp}-nf-${idx}`"
                    size="small"
                    variant="text"
                    class="chip-outline"
                    :title="f.name"
                  >
                    {{ shortName(f.name) }}
                  </v-chip>

                  <!-- existing files (from API) -->
                  <v-chip
                    v-for="(d, idx) in item.existingDocs || []"
                    :key="`${item.idOrTmp}-df-${idx}`"
                    size="small"
                    color="primary"
                    variant="text"
                    class="chip-existing"
                    :title="d.file_name"
                    @click="openDoc(d.url)"
                  >
                    {{ shortName(d.file_name) }}
                    <v-icon
                      size="16"
                      class="ml-1"
                      title="حذف"
                      @click.stop="markDocForRemoval(item, d)"
                      >mdi-close</v-icon
                    >
                  </v-chip>
                </div>

                <v-btn
                  class="download-all-btn"
                  prepend-icon="mdi-download"
                  :disabled="!item.id"
                  color="error"
                  size="small"
                  @click="downloadAll(item)"
                >
                  {{ t('دانلود فایل ها') }}
                </v-btn>
              </div>
            </div>
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="flex items-center gap-1">
              <v-btn class="bg-primary" variant="outlined" type="submit" :loading="item.loading">
                {{ t('ثبت دیتا') }}
              </v-btn>

              <v-btn class="bg-error" variant="outlined" @click="del(item)">
                {{ t('حذف دیتا') }}
              </v-btn>

              <v-btn color="warning" variant="outlined" @click="clearEditor(item)">
                {{ t('پاک کردن ورودی‌ها') }}
              </v-btn>
            </div>
          </template>

          <template #no-data>
            <div class="py-8 text-medium-emphasis">
              {{ t('یک سطر ویرایش/ایجاد آماده است') }}
            </div>
          </template>
        </v-data-table>

        <!-- Image preview dialog -->
        <v-dialog v-model="previewOpen" max-width="520">
          <v-card>
            <v-toolbar
              density="compact"
              color="primary"
              :title="t('پیش نمایش')"
              class="text-white"
            />
            <v-card-text class="d-flex justify-center">
              <v-img
                :src="previewSrc"
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
      </v-card>
    </v-form>

    <!-- Peek + FilterableTable -->
    <v-container fluid class="">
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title-key="name"
        image-key="picture"
        :fields="headers"
        :cols="3"
        dir="rtl"
        @fetch="fetchOne"
      />
    </v-container>

    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      :dateKeys="['join_date', 'left_date']"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    />

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, computed, watch, nextTick } from 'vue';
  import { useEmployeOfficeRepository } from '../../stores/EmployeeRepository';
  import AppBar from '../../components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useI18n } from 'vue-i18n';
  import DateInput from '../../components/DateInput.vue';

  const { t, locale } = useI18n();
  const repo = useEmployeOfficeRepository();
  const editorForm = ref(null);
  const editorValid = ref(false);
  const formKey = ref(0);

  const previewOpen = ref(false);
  const previewSrc = ref(null);
  const showError = ref(false);
  const errorMessage = ref('');
  const editorCard = ref(null);

  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }
  function showErrorMessage(message) {
    errorMessage.value = message;
    showError.value = true;
  }

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => {
    isRtl.value = newLocale === 'fa';
  });

  /* ===== Errors per row ===== */
  const itemErrors = ref({});
  function setItemErrors(item, errors) {
    itemErrors.value[item.idOrTmp] = errors;
  }
  function clearItemErrors(item) {
    if (itemErrors.value[item.idOrTmp]) delete itemErrors.value[item.idOrTmp];
  }
  function hasError(item, field) {
    return !!(itemErrors.value[item.idOrTmp] && itemErrors.value[item.idOrTmp][field]);
  }
  function getErrorMessages(item, field) {
    return itemErrors.value[item.idOrTmp]?.[field] ?? [];
  }

  /* ===== Single editor row ===== */
  const rows = ref([]);

  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? 'tmp-1',
      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,
      loading: false,

      // Identity
      name: x.name ?? '',
      father_name: x.father_name ?? '',
      grand_father_name: x.grand_father_name ?? '',
      nick_name: x.nick_name ?? '',
      code_number: x.code_number ?? '',

      // Rank/levels & background
      bast: x.bast ?? '',
      level: x.level ?? '',
      history_jahad: x.history_jahad ?? '',
      dilgay_mesher: x.dilgay_mesher ?? '',
      jahadi_mawaqaf: x.jahadi_mawaqaf ?? '',
      jahad_time_responsibility: x.jahad_time_responsibility ?? '',
      tazkira: x.tazkira ?? '',

      // Places
      jahad_place: x.jahad_place ?? '',
      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',
      now_specific_place: x.now_specific_place ?? '',
      now_street_number: x.now_street_number ?? '',
      now_home_number: x.now_home_number ?? '',

      // Job & org
      specific_job: x.specific_job ?? '',
      qisem: x.qisem ?? '',
      zone: x.zone ?? '',
      hawza: x.hawza ?? '',

      // Status & education
      madani_halat: x.madani_halat ?? '',
      activity: x.activity ?? '',
      relegy_study: x.relegy_study ?? '',
      new_study: x.new_study ?? '',
      faculty: x.faculty ?? '',
      nizami: x.nizami ?? '',
      gun_knowledge: x.gun_knowledge ?? '',

      // Demographic
      age: x.age ?? '',
      specific_skills: x.specific_skills ?? '',
      language: x.language ?? '',
      nation: x.nation ?? '',

      // Contact & misc
      phone_number: x.phone_number ?? '',
      coordinates: x.coordinates ?? '',
      join_date: x.join_date ?? '',
      left_date: x.left_date ?? '',
      note: x.note ?? '',

      // documents from API
      existingDocs: Array.isArray(x.documents) ? x.documents : [],
    };
    return { ...base, draft: { ...base, picture: null, _newDocs: [], removed_document_ids: [] } };
  }

  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* ===== ALL data for FilterableTable ===== */
  const rowsFT = computed(() => repo.Employees || []);

  /* ===== Headers (editor table) ===== */
  const headers = computed(() => [
    { title: t('تضویر'), key: 'picture', width: 120, sortable: false },

    // Identity
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('کود نمبر'), key: 'code_number' },

    // Rank/levels
    { title: t('بست'), key: 'bast' },
    { title: t('رتبه/سطح'), key: 'level' },

    // Background
    { title: t('سابقه جهاد'), key: 'history_jahad' },
    { title: t('دلګی/مشری'), key: 'dilgay_mesher' },
    { title: t('مواقف جهادی'), key: 'jahadi_mawaqaf' },
    { title: t('مسئولیت جهاد'), key: 'jahad_time_responsibility' },
    { title: t('شماره تذکره'), key: 'tazkira' },
    { title: t('محل جهاد'), key: 'jahad_place' },

    // Addresses
    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },
    { title: t('محل مشخص فعلی'), key: 'now_specific_place' },
    { title: t('نمبر سرک'), key: 'now_street_number' },
    { title: t('نمبر خانه'), key: 'now_home_number' },

    // Job/org
    { title: t('وظیفه مشخص'), key: 'specific_job' },
    { title: t('قسم'), key: 'qisem' },
    { title: t('زون'), key: 'zone' },
    { title: t('حوزه'), key: 'hawza' },

    // Status & education
    { title: t('حالت مدنی'), key: 'madani_halat' },
    { title: t('فعالیت'), key: 'activity' },
    { title: t('تعلیمات دینی'), key: 'relegy_study' },
    { title: t('تعلیمات عصری'), key: 'new_study' },
    { title: t('پوهنحی/رشته'), key: 'faculty' },
    { title: t('نظامی'), key: 'nizami' },
    { title: t('معلومات سلاح'), key: 'gun_knowledge' },

    // Demographic
    { title: t('سن'), key: 'age' },
    { title: t('مهارت‌های مشخص'), key: 'specific_skills' },
    { title: t('زبان'), key: 'language' },
    { title: t('قوم/ملیت'), key: 'nation' },

    // Contact & misc
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('تاریخ آمدن'), key: 'join_date' },
    { title: t('تاریخ رفتن'), key: 'left_date' },
    { title: t('ملاحظات'), key: 'note' },

    { title: t('اسناد'), key: 'documents' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* ===== Headers for FilterableTable ===== */
  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('کود نمبر'), key: 'code_number' },

    // Rank/levels
    { title: t('بست'), key: 'bast' },
    { title: t('رتبه/سطح'), key: 'level' },

    // Background
    { title: t('سابقه جهاد'), key: 'history_jahad' },
    { title: t('دلګی/مشری'), key: 'dilgay_mesher' },
    { title: t('مواقف جهادی'), key: 'jahadi_mawaqaf' },
    { title: t('مسئولیت جهاد'), key: 'jahad_time_responsibility' },
    { title: t('شماره تذکره'), key: 'tazkira' },
    { title: t('محل جهاد'), key: 'jahad_place' },

    // Addresses
    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },
    { title: t('محل مشخص فعلی'), key: 'now_specific_place' },
    { title: t('نمبر سرک'), key: 'now_street_number' },
    { title: t('نمبر خانه'), key: 'now_home_number' },

    // Job/org
    { title: t('وظیفه مشخص'), key: 'specific_job' },
    { title: t('قسم'), key: 'qisem' },
    { title: t('زون'), key: 'zone' },
    { title: t('حوزه'), key: 'hawza' },

    // Status & education
    { title: t('حالت مدنی'), key: 'madani_halat' },
    { title: t('فعالیت'), key: 'activity' },
    { title: t('تعلیمات دینی'), key: 'relegy_study' },
    { title: t('تعلیمات عصری'), key: 'new_study' },
    { title: t('پوهنحی/رشته'), key: 'faculty' },
    { title: t('نظامی'), key: 'nizami' },
    { title: t('معلومات سلاح'), key: 'gun_knowledge' },

    // Demographic
    { title: t('سن'), key: 'age' },
    { title: t('مهارت‌های مشخص'), key: 'specific_skills' },
    { title: t('زبان'), key: 'language' },
    { title: t('قوم/ملیت'), key: 'nation' },

    // Contact & misc
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('تاریخ آمدن'), key: 'join_date' },
    { title: t('تاریخ رفتن'), key: 'left_date' },
    { title: t('ملاحظات'), key: 'note', width: 400 },
  ]);

  const topGroups = computed(() => [
    {
      title: t('معرفت'),
      keys: [
        'name',
        'father_name',
        'grand_father_name',
        'nick_name',
        'code_number',
        'bast',
        'level',
        'history_jahad',
        'dilgay_mehser',
        'jahadi_mawaqaf',
        'jahad_time_responsibility',
        'tazkira',
      ],
    },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    {
      title: t('آدرس فعلی'),
      keys: [
        'now_province',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
      ],
    },
    {
      title: t(' تحصیلات'),
      keys: ['relegy_study', 'new_study', 'faculty', 'nizami', 'gun_knowledge'],
    },
  ]);

  /* ===== Rules ===== */
  const rules = {
    required: (v) => !!v || 'This field is required',
    selectRequired: (label) => (v) => !!v || `${label} is required`,
    min: (n) => (v) => (v && v.length >= n) || `Minimum ${n} characters`,
    max: (n) => (v) => (v && v.length <= n) || `Maximum ${n} characters`,
    number: (v) => !v || /^[0-9]+$/.test(v) || 'Only numbers allowed',
    email: (v) =>
      !v || /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(v) || 'Invalid email address',
  };

  const filters = ref({});

  /* ===== Image picker ===== */
  function triggerFile(item) {
    const el = document.getElementById(`up-${item.idOrTmp}`);
    if (el) el.click();
  }
  function onPickFile(item, file) {
    item.draft.picture = file || null;
    if (file) {
      if (item._blobUrl) URL.revokeObjectURL(item._blobUrl);
      item._blobUrl = URL.createObjectURL(file);
      item.previewUrl = item._blobUrl;
    } else {
      item.previewUrl = item.picture || null;
    }
  }

  /* ===== diff + merge ===== */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};

    const keys = [
      // identity
      'name',
      'father_name',
      'grand_father_name',
      'nick_name',
      'code_number',
      // rank/background
      'bast',
      'level',
      'history_jahad',
      'dilgay_mesher',
      'jahadi_mawaqaf',
      'jahad_time_responsibility',
      'tazkira',
      // places
      'jahad_place',
      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_district',
      'now_village',
      'now_specific_place',
      'now_street_number',
      'now_home_number',
      // job/org
      'specific_job',
      'qisem',
      'zone',
      'hawza',
      // status & education
      'madani_halat',
      'activity',
      'relegy_study',
      'new_study',
      'faculty',
      'nizami',
      'gun_knowledge',
      // demographic
      'age',
      'specific_skills',
      'language',
      'nation',
      // contact & misc
      'phone_number',
      'coordinates',
      'join_date',
      'left_date',
      'note',
    ];

    for (const k of keys) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    const imageChanged = !!(d.picture && typeof d.picture === 'object');
    if (imageChanged) {
      changed.picture = d.picture;
      changed.old_picture = item.picture;
    }
    return { changed, imageChanged };
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj).forEach(([k, v]) => {
      if (v !== undefined && v !== null) fd.append(k, v);
    });
    return fd;
  }
  async function hardResetFormValidation() {
    editorValid.value = false;
    itemErrors.value = {};
    await nextTick();
    editorForm.value?.resetValidation?.();
    formKey.value++;
    await nextTick();
  }

  /* ===== Save / Delete ===== */
  async function save(item) {
    if (!item.draft?.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
      return;
    }
    item.loading = true;
    clearItemErrors(item);

    try {
      // CREATE
      if (!item.id) {
        const fd = new FormData();
        Object.entries(item.draft).forEach(([k, v]) => {
          if (k === '_newDocs' || k === 'removed_document_ids') return;
          if (v !== undefined && v !== null) fd.append(k, v);
        });
        (item.draft._newDocs || []).forEach((file) => fd.append('documents[]', file));
        const created = await repo.CreateEmployee(fd);
        if (created && created.id) {
          await fetchAll();
          setBlankEditorRow();
          editorForm.value?.resetValidation?.();
        } else {
          await fetchAll();
          setBlankEditorRow();
          editorForm.value?.resetValidation?.();
        }
        return;
      }

      // UPDATE
      const { changed, imageChanged } = diffDraft(item);
      const hasRemovals = (item.draft.removed_document_ids || []).length > 0;
      const hasNewDocs = (item.draft._newDocs || []).length > 0;

      if (!hasRemovals && !hasNewDocs && Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }

      const fd = new FormData();
      Object.entries(changed).forEach(([k, v]) => {
        if (v !== undefined && v !== null) fd.append(k, v);
      });
      (item.draft._newDocs || []).forEach((file) => fd.append('documents[]', file));
      (item.draft.removed_document_ids || []).forEach((id) =>
        fd.append('removed_document_ids[]', id)
      );

      const updated = await repo.UpdateEmployee(item.id, fd);
      if (updated && updated.id) {
        if (!imageChanged && item._blobUrl) {
          URL.revokeObjectURL(item._blobUrl);
          item._blobUrl = null;
        }
        await fetchAll();
        setBlankEditorRow();
        editorForm.value?.resetValidation?.();
      }
    } catch (error) {
      console.error('Save error:', error);
      if (error.response?.data?.errors) {
        setItemErrors(item, error.response.data.errors);
        showErrorMessage(t('لطفا خطاهای فرم را بررسی کنید'));
      } else {
        showErrorMessage(t('خطا در ذخیره اطلاعات') + ': ' + (error.message || t('خطای ناشناخته')));
      }
    } finally {
      item.loading = false;
    }
  }

  // VALIDATED SUBMIT
  async function onSubmit(item) {
    const form = editorForm.value;
    if (!form) return;
    const { valid } = await form.validate();
    if (!valid) {
      showErrorMessage(t('لطفاً فیلدهای قرمز را درست کنید'));
      await nextTick();
      scrollToFirstInvalid();
      return;
    }
    await save(item);
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

  async function del(item) {
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا از حذف  مطمئن هستید؟', { name: item.name }))) return;
    if (!confirm(t('با حذف این مورد مواد را کاملا از دست میتین .آیا  واقعااز حذف  مطمئن هستید؟')))
      return;

    try {
      await repo.DeleteEmployee(item.id);
      await fetchAll();
      setBlankEditorRow();
    } catch (error) {
      console.error('Delete error:', error);
      showErrorMessage(t('خطا در حذف کارمند') + ': ' + (error.message || t('خطای ناشناخته')));
    }
  }

  function clearEditor(item) {
    try {
      if (item && item._blobUrl) {
        URL.revokeObjectURL(item._blobUrl);
        item._blobUrl = null;
      }
      const inputEl = document.getElementById(`up-${item?.idOrTmp}`);
      if (inputEl) inputEl.value = '';
      if (item && item.idOrTmp && itemErrors.value[item.idOrTmp]) {
        delete itemErrors.value[item.idOrTmp];
      }
      setBlankEditorRow();
      showErrorMessage(t('ورودی‌ها پاک شد'));
    } catch (e) {
      console.error('clearEditor error:', e);
    }
  }

  /* ===== Docs helpers ===== */
  function triggerDocs(item) {
    const el = document.getElementById(`docs-${item.idOrTmp}`);
    if (el) el.click();
  }
  function onPickDocs(item, files) {
    item.draft._newDocs = Array.isArray(files) ? files : [];
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
  function markDocForRemoval(item, doc) {
    if (!doc?.id) return;
    if (!Array.isArray(item.draft.removed_document_ids)) item.draft.removed_document_ids = [];
    if (!item.draft.removed_document_ids.includes(doc.id)) {
      item.draft.removed_document_ids.push(doc.id);
    }
    item.existingDocs = (item.existingDocs || []).filter((d) => d.id !== doc.id);
  }
  async function downloadAll(item) {
    if (!item.id) return;
    const base = (item.code_number || item.name || 'documents').toLowerCase().replace(/\s+/g, '_');
    const filename = `${base}_documents.zip`;
    await repo.DownloadOfficeEmployeeZip(item.id, filename);
  }

  /* ===== Peek / Fetch ===== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      await repo.FetchEmployeeData(id);
      detail.value = repo.Employee || [];
    } catch (error) {
      console.error('Fetch one error:', error);
      showErrorMessage(t('خطا در دریافت اطلاعات کارمند'));
    } finally {
      loading.value = false;
    }
  }
  function openPeek(payload) {
    const row = payload?.raw ?? payload;
    if (!row?.id) return;
    expanded.value = true;
    fetchOne(row.id);
  }

  /* ===== Fetch ALL ===== */
  async function fetchAll() {
    try {
      if (typeof repo.FetchEmployeesData === 'function') {
        await repo.FetchEmployeesData({
          page: repo.page,
          perPage: repo.perPage,
          search: repo.Search,
        });
        return;
      }
      repo.page = 1;
      repo.itemsPerPage = 100000;
      await repo.FetchEmployeesData({ page: 1, itemsPerPage: 100000 });
    } catch (e) {
      console.error('fetchAll error', e);
    }
  }

  /* ===== Start edit from FilterableTable ===== */
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      await repo.FetchEmployeeData(id);
      const single =
        repo.Employee && Array.isArray(repo.Employee) ? repo.Employee[0] : repo.Employee || row;
      rows.value = [toRowModel(single || row)];
      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      console.error('startEdit error', e);
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }
  function hawzaItems(item) {
    const z = item?.draft?.zone || null;
    // If zone is chosen → show only that zone’s hawzas
    // If zone empty → show all hawzas (so hawza can be chosen first)
    return repo.getHawzaOptionsForZone(z);
  }

  function onZoneChange(item) {
    const { zone, hawza } = repo.normalizeAfterZoneChange(
      item?.draft?.zone ?? '',
      item?.draft?.hawza ?? ''
    );
    item.draft.zone = zone;
    item.draft.hawza = hawza;
  }

  function onHawzaChange(item) {
    const { zone, hawza } = repo.normalizeAfterHawzaChange(
      item?.draft?.hawza ?? '',
      item?.draft?.zone ?? ''
    );
    item.draft.zone = zone;
    item.draft.hawza = hawza;
  }

  function hawzaValidForZone(item, value) {
    return (
      repo.hawzaIsValidForZone(item?.draft?.zone ?? '', value ?? '') ||
      t('حوزه انتخابی مربوط به این زون نیست')
    );
  }

  /* ===== Init ===== */
  onMounted(async () => {
    await fetchAll();
    setBlankEditorRow();

  });
</script>

<style scoped>
  table th {
    white-space: nowrap;
  }
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }
  .addr-group {
    display: grid;
    grid-template-columns: repeat(3, minmax(120px, 1fr));
    gap: 2px;
  }
  .text-group {
    display: grid;
    width: 120px;
    gap: 2px;
  }
  .text-group-note {
    display: grid;
    width: 400px;
    gap: 2px;
  }
  .flex {
    display: flex;
  }
  .items-center {
    align-items: center;
  }
  .gap-1 {
    gap: 4px;
  }
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .pic-thumb {
    display: inline-flex;
    cursor: pointer;
    position: relative;
  }
  .tablebody {
    display: flex !important;
    gap: 2px !important;
  }

  :deep(.v-field-label),
  :deep(.v-label),
  :deep(input::placeholder) {
    font-size: 12px !important;
    line-height: 1.2 !important;
    color: #555 !important;
  }
  :deep(.v-field__input) {
    min-height: 26px !important;
    padding: 2px 6px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
  }

  /* compact spacing in groups */
  .addr-group,
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
  }

  /* picture hover tools */
  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
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

  .documents-inline {
    min-width: 520px;
  }
  .docs-field-row {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px dashed #9e9e9e;
    border-radius: 8px;
    padding: 6px 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  }
  .chips-row {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 2px 0;
    scrollbar-width: thin;
  }
  .chip-existing {
    background: #c8dae1;
    color: black !important;
  }
  .chip-outline {
    background: #c8dae1;
  }
  .download-all-btn {
    white-space: nowrap;
  }
</style>
