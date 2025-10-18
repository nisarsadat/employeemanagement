<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('رزیدانت ها')"
      :subtitle="t('اضافه کردن یک رزیدانت')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <!-- Search -->
    <v-row class="d-flex">
      <v-col >
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

    <!-- Single-row editor (create/edit) — NO pagination/footers -->
    <v-card class="tablebody" ref="editorCard">
      <v-data-table
        :headers="headers"
        :items="rows"
        :loading="repo.loading"
        item-key="idOrTmp"
        class="elevation-0 custom-table"
        :dir="isRtl ? 'rtl' : 'ltr'"
        density="comfortable"
        hide-default-footer
      >
        <!-- Picture with hover actions -->
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
              size="x-small"
              color="primary"
              variant="outlined"
              icon
              @click="triggerFile(item)"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
            <div v-else class="pic-thumb" :title="t('گزینه ها')">
              <v-avatar size="32">
                <v-img :src="item.previewUrl || item.picture" :alt="t('تصویر')" cover />
              </v-avatar>
              <div class="pic-overlay">
                <v-btn
                  size="x-small"
                  color="primary"
                  variant="flat"
                  @click.stop="triggerFile(item)"
                >
                  <v-icon start>mdi-pencil</v-icon>{{ t('ویرایش') }}
                </v-btn>
                <v-btn
                  size="x-small"
                  color="secondary"
                  variant="flat"
                  @click.stop="openPreview(item.previewUrl || item.picture)"
                >
                  <v-icon start>mdi-eye</v-icon>{{ t('نمایش') }}
                </v-btn>
              </div>
            </div>
          </div>
        </template>

        <!-- Basic fields -->
        <template #item.name="{ item }">
          <v-text-field
            v-model="item.draft.name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام')"
            hide-details
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
            hide-details
            :error="hasError(item, 'father_name')"
            :error-messages="getErrorMessages(item, 'father_name')"
          />
        </template>

        <template #item.nickname="{ item }">
          <v-text-field
            v-model="item.draft.nickname"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام مستعار')"
            hide-details
            :error="hasError(item, 'nickname')"
            :error-messages="getErrorMessages(item, 'nickname')"
          />
        </template>

        <!-- Main address -->
        <template #item.main_province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.main_province"
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
              hide-details
              :label="t('ولسوالی اصلی')"
              :error="hasError(item, 'main_district')"
              :error-messages="getErrorMessages(item, 'main_district')"
            />
            <v-text-field
              v-model="item.draft.main_village"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('قریه/ناحیه اصلی')"
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
              :error="hasError(item, 'now_province')"
              :error-messages="getErrorMessages(item, 'now_province')"
            />
            <v-text-field
              v-model="item.draft.now_district"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولسوالی فعلی')"
              :error="hasError(item, 'now_district')"
              :error-messages="getErrorMessages(item, 'now_district')"
            />
            <v-text-field
              v-model="item.draft.now_village"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('قریه/ناحیه فعلی')"
              :error="hasError(item, 'now_village')"
              :error-messages="getErrorMessages(item, 'now_village')"
            />
          </div>
        </template>

        <!-- Contact / links -->
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
        <template #item.whatsapp_number="{ item }">
          <v-text-field
            v-model="item.draft.whatsapp_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره واتساپ')"
            hide-details
            :error="hasError(item, 'whatsapp_number')"
            :error-messages="getErrorMessages(item, 'whatsapp_number')"
          />
        </template>

        <!-- Office employee link -->
        <template #item.officeemployee_id="{ item }">
          <v-autocomplete
            v-model="item.draft.officeemployee_id"
            :items="repo.officeemployees || []"
            item-title="code_number"
            item-value="id"
            density="compact"
            variant="outlined"
            class="drop-group"
            bg-color="grey-lighten-5"
            hide-details
            clearable
            :label="t('کود نمبر کارمند مدیریت مربوطه')"
            :placeholder="t('مربوطه مدیریت کود')"
            persistent-placeholder
            :error="hasError(item, 'officeemployee_id')"
            :error-messages="getErrorMessages(item, 'officeemployee_id')"
          />
        </template>

        <!-- Zone / hawza / code -->
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
        <template #item.hawza="{ item }">
          <v-text-field
            v-model="item.draft.hawza"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('حوزه')"
            hide-details
            :error="hasError(item, 'hawza')"
            :error-messages="getErrorMessages(item, 'hawza')"
          />
        </template>
        <template #item.zone="{ item }">
          <v-text-field
            v-model="item.draft.zone"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('زون')"
            hide-details
            :error="hasError(item, 'zone')"
            :error-messages="getErrorMessages(item, 'zone')"
          />
        </template>

        <!-- Coordinates -->
        <template #item.coordinates="{ item }">
          <v-text-field
            v-model="item.draft.coordinates"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('مختصات (lat,lng) یا آدرس مرجع')"
            hide-details
            :error="hasError(item, 'coordinates')"
            :error-messages="getErrorMessages(item, 'coordinates')"
          />
        </template>

        <!-- Notes -->
        <template #item.notes="{ item }">
          <v-text-field
            v-model="item.draft.notes"
            density="compact"
            class="text-group-note"
            variant="outlined"
            :label="t('ملاحضات')"
            hide-details
            :error="hasError(item, 'notes')"
            :error-messages="getErrorMessages(item, 'notes')"
          />
        </template>

        <!-- Documents (Upload + chips + delete-on-save + ZIP) -->
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

                <!-- existing files -->
                <v-chip
                  v-for="(d, idx) in item.existingDocs || []"
                  :key="`${item.idOrTmp}-df-${idx}`"
                  size="small"
                  color="primary"
                  variant="text"
                  class="chip-existing"
                  :title="d.original_name || d.file_name"
                  @click="openDoc(d.url)"
                >
                  {{ shortName(d.original_name || d.file_name) }}
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
            <v-btn
              class="bg-primary"
              variant="outlined"
              @click="save(item)"
              :loading="item.loading"
            >
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

      <!-- Image preview -->
      <v-dialog v-model="previewOpen" max-width="520">
        <v-card>
          <v-toolbar density="compact" color="primary" :title="t('پیش نمایش')" class="text-white" />
          <v-card-text class="d-flex justify-center">
            <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="480" max-height="420" cover />
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn color="primary" variant="flat" @click="previewOpen = false">
              {{ t('بستن') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card>

    <!-- Peek drawer -->
    <v-container fluid>
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

    <!-- Full data table with filtering + Edit & Peek -->
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
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    />

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, watch, computed, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '../../components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useEmployeOfficeRepository } from '../../stores/EmployeeRepository';

  const { t, locale } = useI18n();
  const repo = useEmployeOfficeRepository();

  /* UI */
  const drawer = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => (isRtl.value = newLocale === 'fa'));

  /* Snackbar + preview */
  const previewOpen = ref(false);
  const previewSrc = ref(null);
  const showError = ref(false);
  const errorMessage = ref('');
  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }
  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  /* Errors per row */
  const itemErrors = ref({});
  function setItemErrors(item, errors) {
    itemErrors.value[item.idOrTmp] = errors;
  }
  function clearItemErrors(item) {
    if (itemErrors.value[item.idOrTmp]) delete itemErrors.value[item.idOrTmp];
  }
  function hasError(item, field) {
    return !!itemErrors.value[item.idOrTmp]?.[field];
  }
  function getErrorMessages(item, field) {
    return itemErrors.value[item.idOrTmp]?.[field] ?? [];
  }

  /* Single-row editor data */
  const rows = ref([]); // always one editor row
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? 'tmp-1',
      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,
      loading: false,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      nickname: x.nickname ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',

      phone_number: x.phone_number ?? '',
      whatsapp_number: x.whatsapp_number ?? '',
      hawza: x.hawza ?? '',
      zone: x.zone ?? '',
      code_number: x.code_number ?? '',
      officeemployee_id: x.officeemployee_id ?? '',

      coordinates: x.coordinates ?? '',
      notes: x.notes ?? '',

      // docs from API
      existingDocs: Array.isArray(x.documents) ? x.documents : [],
    };
    return { ...base, draft: { ...base, picture: null, _newDocs: [], removed_document_ids: [] } };
  }
  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* Full dataset for FilterableTable */
  const rowsFT = computed(() => repo.Resedints || []);

  /* Headers (editor table) */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', width: 120, sortable: false },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name', width: 300 },
    { title: t('نام مستعار'), key: 'nickname' },
    { title: t('کود نمبر'), key: 'code_number' },
    { title: t('سکونت اصلی'), key: 'main_province' },
    { title: t('سکونت فعلی'), key: 'now_province' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('شماره واتساپ'), key: 'whatsapp_number' },
    { title: t('کود نمبر کارمند مدیریت مربوطه'), key: 'officeemployee_id' },
    { title: t('حوزه'), key: 'hawza' },
    { title: t('زون'), key: 'zone' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('ملاحضات'), key: 'notes' },
    { title: t('اسناد'), key: 'documents' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  /* Headers for filterable grid */
  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام مستعار'), key: 'nickname' },
    { title: t('کود نمبر'), key: 'code_number' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('شماره واتساپ'), key: 'whatsapp_number' },
    { title: t('حوزه'), key: 'hawza' },
    { title: t('زون'), key: 'zone' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('ملاحضات'), key: 'notes' },
  ]);

  const topGroups = computed(() => [
    { title: t('معرفت'), keys: ['name', 'father_name', 'nickname', 'code_number'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
  ]);

  const filters = ref({});

  /* Image picker */
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

  /* Diff */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'name',
      'father_name',
      'nickname',
      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_district',
      'now_village',
      'phone_number',
      'whatsapp_number',
      'hawza',
      'zone',
      'code_number',
      'officeemployee_id',
      'coordinates',
      'notes',
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

  /* Save / Delete */
  async function save(item) {
    if (!item?.draft?.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
      return;
    }
    item.loading = true;
    clearItemErrors(item);

    const clearRowInputs = () => {
      try {
        if (item._blobUrl) {
          URL.revokeObjectURL(item._blobUrl);
          item._blobUrl = null;
        }
        const picEl = document.getElementById(`up-${item.idOrTmp}`);
        const docsEl = document.getElementById(`docs-${item.idOrTmp}`);
        if (picEl) picEl.value = '';
        if (docsEl) docsEl.value = '';
      } catch {}
    };

    try {
      // CREATE
      if (!item.id) {
        const fd = new FormData();
        Object.entries(item.draft).forEach(([k, v]) => {
          if (k === '_newDocs' || k === 'removed_document_ids') return;
          if (v !== undefined && v !== null) fd.append(k, v);
        });
        (item.draft._newDocs || []).forEach((f) => fd.append('documents[]', f));

        const created = await repo.CreateResedint(fd);
        if (created?.id) {
          showErrorMessage(t('رزیدانت با موفقیت ایجاد شد'));
          await fetchAll();
          setBlankEditorRow();
          clearRowInputs();
        } else {
          await fetchAll();
          setBlankEditorRow();
        }
        return;
      }

      // UPDATE
      const { changed, imageChanged } = diffDraft(item);
      const hasRemovals =
        Array.isArray(item.draft.removed_document_ids) &&
        item.draft.removed_document_ids.length > 0;
      const hasNewDocs = Array.isArray(item.draft._newDocs) && item.draft._newDocs.length > 0;

      if (!hasRemovals && !hasNewDocs && Object.keys(changed).length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }

      const fd = new FormData();
      Object.entries(changed).forEach(([k, v]) => {
        if (v !== undefined && v !== null) fd.append(k, v);
      });
      (item.draft._newDocs || []).forEach((f) => fd.append('documents[]', f));
      (item.draft.removed_document_ids || []).forEach((id) =>
        fd.append('removed_document_ids[]', id)
      );

      const updated = await repo.UpdateResedint(item.id, fd);
      if (updated?.id) {
        if (!imageChanged && item._blobUrl) {
          URL.revokeObjectURL(item._blobUrl);
          item._blobUrl = null;
        }
        showErrorMessage(t('رزیدانت با موفقیت بروزرسانی شد'));
        await fetchAll();
        setBlankEditorRow();
        clearRowInputs();
      }
    } catch (error) {
      console.error('Save error:', error);
      const errors = error?.response?.data?.errors;
      if (errors) {
        setItemErrors(item, errors);
        showErrorMessage(t('لطفا خطاهای فرم را بررسی کنید'));
      } else {
        showErrorMessage(t('خطا در ذخیره اطلاعات') + ': ' + (error?.message || t('خطای ناشناخته')));
      }
    } finally {
      item.loading = false;
    }
  }

  async function del(item) {
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name }))) return;
    if (!confirm(t('با حذف این مورد اسناد را کاملا از دست می‌دهید. آیا واقعا مطمئن هستید؟')))
      return;

    try {
      await repo.DeleteResedintEmployee(item.id);
      await fetchAll();
      showErrorMessage(t('رزیدانت حذف شد'));
      setBlankEditorRow();
    } catch (error) {
      console.error('Delete error:', error);
      showErrorMessage(t('خطا در حذف رزیدانت') + ': ' + (error?.message || t('خطای ناشناخته')));
    }
  }

  /* Docs helpers */
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
    const base = (item.name || 'documents').toLowerCase().replace(/\s+/g, '_');
    const filename = `${base}_documents.zip`;
    await repo.DownloadResedintZip(item.id, filename);
  }

  /* Peek */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      await repo.FetchResedintData?.(id);
      detail.value = repo.Resedint || [];
    } catch (e) {
      console.error('Fetch one error:', e);
      showErrorMessage(t('خطا در دریافت رزیدانت'));
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

  /* Fetch ALL for FilterableTable */
  async function fetchAll() {
    try {
      if (typeof repo.FetchResedintsData === 'function') {
        await repo.FetchResedintsData({ page: 1, itemsPerPage: 100000, search: repo.Search });
        return;
      }
      // fallback big page
      repo.page = 1;
      repo.itemsPerPage = 100000;
      await repo.FetchResedintsData({ page: 1, itemsPerPage: 100000 });
    } catch (e) {
      console.error('fetchAll error', e);
    }
  }

  /* Start edit from FilterableTable */
  const editorCard = ref(null);
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;
      await repo.FetchResedintData?.(id);
      const single =
        repo.Resedint && Array.isArray(repo.Resedint) ? repo.Resedint[0] : repo.Resedint || row;
      rows.value = [toRowModel(single || row)];
      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      console.error('startEdit error', e);
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* Init */
  onMounted(async () => {
    await repo.GetOfficeEmployee().catch(() => {});
    await fetchAll();
    setBlankEditorRow();
  });
</script>

<style scoped>
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }

  /* inputs */
  .addr-group {
    display: grid;
    grid-template-columns: repeat(3, minmax(150px, 1fr));
    gap: 2px;
  }
  .text-group {
    display: grid;
    width: 200px;
    gap: 2px;
  }
  .text-group-note {
    display: grid;
    width: 400px;
    gap: 2px;
  }
  .drop-group {
    width: 250px;
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

  /* picture + overlay */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .pic-thumb {
    position: relative;
    display: inline-flex;
    cursor: pointer;
  }
  .pic-overlay {
    position: absolute;
    inset: -6px -6px auto auto;
    display: none;
    gap: 6px;
    background: rgba(0, 0, 0, 0.55);
    padding: 6px;
    border-radius: 8px;
    transform: translateY(-40%);
    white-space: nowrap;
  }
  .pic-thumb:hover .pic-overlay {
    display: inline-flex;
  }
  .pic-overlay :deep(.v-btn) {
    color: #fff;
  }

  /* compact fields */
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

  /* documents row */
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

  .tablebody {
    display: flex !important;
    gap: 2px !important;
  }
</style>
