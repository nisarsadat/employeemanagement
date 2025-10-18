<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('کارمندان تدابیری')"
      :subtitle="t('اضافه کردن کارمند تدابیری')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <v-row class="d-flex">
      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetch()"
          clearable
          @click:clear="
            () => {
              repo.Search = '';
              fetch();
            }
          "
        />
      </v-col>

      <v-col cols="12" md="3" class="text-md-right">
        <v-btn color="primary" @click="addBlankRow">
          {{ t('اضافه کردن کارمند تدابیری') }}
        </v-btn>
      </v-col>
    </v-row>

    <v-card class="tablebody">
      <v-data-table
        :headers="headers"
        :items="rows"
        :loading="repo.loading"
        :items-per-page="repo.itemsPerPage"
        :page="repo.page"
        :items-length="repo.totalItems"
        item-key="idOrTmp"
        class="elevation-2 custom-table"
        :dir="isRtl ? 'rtl' : 'ltr'"
        density="comfortable"
        @update:page="
          (p) => {
            repo.page = p;
            fetch();
          }
        "
        @update:items-per-page="
          (n) => {
            repo.itemsPerPage = n;
            fetch();
          }
        "
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

        <!-- Editable fields -->
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

        <template #item.degree="{ item }">
          <v-text-field
            v-model="item.draft.degree"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('درجه موثقیت')"
            hide-details
            :error="hasError(item, 'degree')"
            :error-messages="getErrorMessages(item, 'degree')"
          />
        </template>

        <template #item.resedint_id="{ item }">
          <v-autocomplete
            v-model="item.draft.resedint_id"
            :items="repo.resedints || []"
            item-title="code_number"
            item-value="id"
            density="compact"
            variant="outlined"
            class="text-group"
            hide-details
            :placeholder="t('مود ریزیدانت')"
            persistent-placeholder
            clearable
            :error="hasError(item, 'resedint_id')"
            :error-messages="getErrorMessages(item, 'resedint_id')"
          />
        </template>

        <template #item.zone_entry_hour="{ item }">
          <v-text-field
            v-model="item.draft.zone_entry_hour"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('ساعت ورود به زون')"
            hide-details
            :error="hasError(item, 'zone_entry_hour')"
            :error-messages="getErrorMessages(item, 'zone_entry_hour')"
          />
        </template>

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
                <!-- new (unsaved) -->
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

                <!-- existing -->
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
            <v-btn variant="outlined" class="bg-info" @click="openPeek(item)">
              {{ t('جزییات') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">
            {{ t('کارمند تدابیری دریافت نشد') }}
          </div>
        </template>
      </v-data-table>

      <!-- Preview dialog -->
      <v-dialog v-model="previewOpen" max-width="520">
        <v-card>
          <v-toolbar density="compact" color="primary" :title="t('پیش نمایش')" class="text-white" />
          <v-card-text class="d-flex justify-center">
            <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="480" max-height="420" cover />
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn color="primary" variant="flat" @click="previewOpen = false">{{
              t('بستن')
            }}</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card>
  </v-container>

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

  <FilterableTable
    :headers="headersFT"
    :rows="rows"
    :filters="filters"
    @update:filters="(v) => (filters = { ...v })"
    :top-groups="topGroups"
    :showIndex="true"
    :rtl="true"
    class="mb-20 mx-2"
  />

  <!-- Error Snackbar -->
  <v-snackbar v-model="showError" color="error" timeout="5000">
    {{ errorMessage }}
    <template #actions>
      <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
    </template>
  </v-snackbar>
</template>

<script setup>
  import { ref, onMounted, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '../../components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useEmployeOfficeRepository } from '../../stores/EmployeeRepository';

  const { t, locale } = useI18n();
  const repo = useEmployeOfficeRepository();

  const drawer = ref(false);
  const rows = ref([]);
  const filters = ref({});
  const previewOpen = ref(false);
  const previewSrc = ref(null);
  const showError = ref(false);
  const errorMessage = ref('');

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => {
    isRtl.value = newLocale === 'fa';
  });

  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }
  function showErrorMessage(message) {
    errorMessage.value = message;
    showError.value = true;
  }

  /* ==== validation store per-row ==== */
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

  /* ==== headers ==== */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', width: 120, sortable: false },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام مستعار'), key: 'nickname' },
    { title: t('کود نمبر'), key: 'code_number' },
    { title: t('حوزه'), key: 'hawza' },
    { title: t('زون'), key: 'zone' },
    { title: t('درجه موثقیت'), key: 'degree' },
    { title: t('رزیدانت'), key: 'resedint_id' },
    { title: t('ساعت ورود به زون'), key: 'zone_entry_hour' },
    { title: t('ملاحضات'), key: 'notes' },
    { title: t('اسناد'), key: 'documents' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام مستعار'), key: 'nickname' },
    { title: t('کود نمبر'), key: 'code_number' },
    { title: t('حوزه'), key: 'hawza' },
    { title: t('زون'), key: 'zone' },
    { title: t('درجه موثقیت'), key: 'degree' },
    { title: t('رزیدانت'), key: 'resedint_id' },
    { title: t('ساعت ورود به زون'), key: 'zone_entry_hour' },
    { title: t('ملاحضات'), key: 'notes' },
  ]);

  const topGroups = computed(() => [
    {
      title: t('معرفت'),
      keys: ['name', 'father_name', 'nickname', 'code_number', 'degree'],
    },
  ]);

  /* ==== row model ==== */
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,
      loading: false,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      nickname: x.nickname ?? '',
      code_number: x.code_number ?? '',
      hawza: x.hawza ?? '',
      zone: x.zone ?? '',
      degree: x.degree ?? '',
      resedint_id: x.resedint_id ?? '',
      zone_entry_hour: x.zone_entry_hour ?? '',
      notes: x.notes ?? '',

      // docs from API resource
      existingDocs: Array.isArray(x.documents) ? x.documents : [],
    };
    // hold new files + deletions for update
    return { ...base, draft: { ...base, picture: null, _newDocs: [], removed_document_ids: [] } };
  }

  /* ==== fetch/hydrate ==== */
  function hydrate() {
    rows.value = (repo.ZoneEmployees || []).map(toRowModel);
  }

  async function fetch() {
    await repo.FetchZoneEmployeesData({
      page: repo.page || 1,
      itemsPerPage: repo.itemsPerPage || 50,
    });
    // ensure dropdown data is present
    if (!repo.resedints || !repo.resedints.length) {
      await repo.GetResedints().catch(() => {});
    }
    hydrate();
  }

  /* ==== add blank row ==== */
  function addBlankRow() {
    const newRow = toRowModel({});
    rows.value = [...rows.value, newRow];
    const per = repo.itemsPerPage || 50;
    const totalLocal = rows.value.length;
    repo.page = Math.max(1, Math.ceil(totalLocal / per));
  }

  /* ==== image picker ==== */
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

  /* ==== diff ==== */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'name',
      'father_name',
      'nickname',
      'code_number',
      'hawza',
      'zone',
      'degree',
      'resedint_id',
      'zone_entry_hour',
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

  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  /* ==== save/delete ==== */
  async function save(item) {
    if (!item.draft?.name?.trim()) {
      showErrorMessage(t('نام الزامی است'));
      return;
    }

    item.loading = true;
    clearItemErrors(item);

    try {
      // ===== CREATE =====
      if (!item.id) {
        // Build FormData (include picture if any, and new docs)
        const fd = new FormData();
        Object.entries(item.draft).forEach(([k, v]) => {
          if (k === '_newDocs' || k === 'removed_document_ids') return; // not for create
          if (v !== undefined && v !== null) fd.append(k, v);
        });
        // NEW documents
        (item.draft._newDocs || []).forEach((file) => fd.append('documents[]', file));

        const created = await repo.CreateZoneEmployee(fd).catch((e) => {
          throw e;
        });

        if (created && created.id) {
          showErrorMessage(t('کارمند تدابیری ایجاد شد'));
          // Refresh table data so list shows server state
          await fetch();
          // Clear this editor row after successful create
          clearEditor(item);
        } else {
          await fetch();
          clearEditor(item);
        }
        return;
      }

      // ===== UPDATE =====
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
      // Changed scalar fields (incl. picture replacement if any)
      Object.entries(changed).forEach(([k, v]) => {
        if (v !== undefined && v !== null) fd.append(k, v);
      });
      // NEW documents
      (item.draft._newDocs || []).forEach((file) => fd.append('documents[]', file));
      // REMOVED existing documents (delete on save)
      (item.draft.removed_document_ids || []).forEach((id) =>
        fd.append('removed_document_ids[]', id)
      );

      const updated = await repo.UpdateZoneEmployee(item.id, fd).catch((e) => {
        throw e;
      });

      if (updated && updated.id) {
        // Clean preview blob if needed
        if (!imageChanged && item._blobUrl) {
          URL.revokeObjectURL(item._blobUrl);
          item._blobUrl = null;
        }
        showErrorMessage(t('کارمند تدابیری بروزرسانی شد'));
        await fetch();
        // Clear this editor row after successful update
        clearEditor(item);
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

  /* ==== reset editor to a fresh blank row (clears inputs like OfficeEmployee) ==== */
  function clearEditor(item) {
    try {
      // Revoke preview blob (image)
      if (item && item._blobUrl) {
        URL.revokeObjectURL(item._blobUrl);
        item._blobUrl = null;
      }

      // Clear hidden inputs (picture + documents)
      const picEl = document.getElementById(`up-${item?.idOrTmp}`);
      if (picEl) picEl.value = '';
      const docsEl = document.getElementById(`docs-${item?.idOrTmp}`);
      if (docsEl) docsEl.value = '';

      // Clear per-field errors for this row
      if (item && item.idOrTmp && itemErrors.value[item.idOrTmp]) {
        delete itemErrors.value[item.idOrTmp];
      }

      // Replace the current row with a fresh blank editor row (keep same idOrTmp so table bindings don’t break)
      const blank = toRowModel({});
      blank.idOrTmp = item.idOrTmp;
      Object.assign(item, blank);

      // Ensure draft has docs buckets
      if (!item.draft) item.draft = {};
      item.draft._newDocs = [];
      item.draft.removed_document_ids = [];
    } catch (e) {
      console.error('clearEditor error:', e);
    }
  }

  async function del(item) {
    if (!item.id) {
      rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp);
      return;
    }
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name }))) return;

    try {
      await repo.DeleteZoneEmployees(item.id);
      await fetch();
      showErrorMessage(t('کارمند تدابیری حذف شد'));
    } catch (error) {
      console.error('Delete error:', error);
      showErrorMessage(t('خطا در حذف کارمند') + ': ' + (error?.message || t('خطای ناشناخته')));
    }
  }
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
    await repo.DownloadZoneEmployeeZip(item.id, `${base}_documents.zip`);
  }

  /* ==== peek ==== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      await repo.FetchZoneEmployeeData(id);
      detail.value = repo.ZoneEmployee || [];
    } catch (e) {
      console.error('Fetch one error:', e);
      showErrorMessage(t('خطا در دریافت  کارمند تدابیری'));
    } finally {
      loading.value = false;
    }
  }
  function openPeek(row) {
    if (!row?.id) return;
    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }

  /* ==== init ==== */
  onMounted(async () => {
    await repo.GetResedints().catch(() => {});
    await fetch();
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
  .addr-group,
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
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

  .pic-cell {
    display: inline-flex;
    align-items: center;
    gap: 6px;
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
