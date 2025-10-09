<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('زندانی های تسلیم شده')"
      :subtitle="t('اضافه کردن زندانی های تسلیم شده')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <v-row class="d-flex">
      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          :dir="isRtl ? 'rtl' : 'ltr'"
          @keyup.enter="fetch()"
          clearable
          @click:clear="() => { repo.Search = ''; fetch(); }"
        />
      </v-col>
      <v-col cols="12" md="3" class="text-md-right">
        <v-btn color="primary" @click="addBlankRow">
          {{ t('اضافه کردن زندانی تسلیم شده') }}
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
        @update:page="(p) => { repo.page = p; fetch(); }"
        @update:items-per-page="(n) => { repo.itemsPerPage = n; fetch(); }"
      >
        <!-- Picture -->
        <template #item.picture="{ item }">
          <div class="pic-cell">
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
            <div v-else class="pic-thumb" @click="triggerFile(item)" :title="t('ویرایش تصویر')">
              <v-avatar size="32">
                <v-img :src="item.previewUrl || item.picture" :alt="t('تصویر')" cover />
              </v-avatar>
            </div>
          </div>
        </template>

        <!-- Introduction -->
        <template #item.intro="{ item }">
          <div class="intro-group">
            <v-text-field v-model="item.draft.name" density="compact" variant="outlined" :label="t('نام')" hide-details />
            <v-text-field v-model="item.draft.father_name" density="compact" variant="outlined" :label="t('نام پدر')" hide-details />
            <v-text-field v-model="item.draft.nickname" density="compact" variant="outlined" :label="t('نام مستعار')" hide-details />
            <v-text-field v-model="item.draft.grand_father_name" density="compact" variant="outlined" :label="t('نام پدر کلان')" hide-details />
          </div>
        </template>

        <template #item.tazkira="{ item }">
          <v-text-field v-model="item.draft.tazkira" class="text-group" density="compact" variant="outlined" :label="t('نمبر تذکره')" hide-details />
        </template>

        <template #item.age="{ item }">
          <v-text-field v-model="item.draft.age" class="text-group" density="compact" variant="outlined" :label="t('عمر')" hide-details />
        </template>

        <template #item.halat_madani="{ item }">
          <v-text-field v-model="item.draft.halat_madani" class="text-group" density="compact" variant="outlined" :label="t('حالت مدنی')" hide-details />
        </template>

        <template #item.criminal_history="{ item }">
          <v-text-field v-model="item.draft.criminal_history" class="text-group" density="compact" variant="outlined" :label="t('سابقه جرمی')" hide-details />
        </template>

        <!-- Jobs -->
        <template #item.jobs="{ item }">
          <div class="drop-group2">
            <v-text-field v-model="item.draft.old_job" density="compact" variant="outlined" :label="t('وظیفه سابقه')" hide-details />
            <v-text-field v-model="item.draft.new_job" density="compact" variant="outlined" :label="t('وظیفه جدید')" hide-details />
          </div>
        </template>

        <template #item.language="{ item }">
          <v-text-field v-model="item.draft.language" class="text-group" density="compact" variant="outlined" :label="t('زبان')" hide-details />
        </template>

        <template #item.scientific_level="{ item }">
          <v-text-field v-model="item.draft.scientific_level" class="text-group" density="compact" variant="outlined" :label="t('سطح علمی')" hide-details />
        </template>

        <!-- Main address -->
        <template #item.main_add="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.main_province" density="compact" variant="outlined" hide-details :label="t('ولایت اصلی')" />
            <v-text-field v-model="item.draft.main_district" density="compact" variant="outlined" hide-details :label="t('ولسوالی اصلی')" />
            <v-text-field v-model="item.draft.main_village" density="compact" variant="outlined" hide-details :label="t('قریه/ناحیه اصلی')" />
          </div>
        </template>

        <!-- Now address -->
        <template #item.now_add="{ item }">
          <div class="addr-group">
            <v-text-field v-model="item.draft.now_province" density="compact" variant="outlined" hide-details :label="t('ولایت فعلی')" />
            <v-text-field v-model="item.draft.now_district" density="compact" variant="outlined" hide-details :label="t('ولسوالی فعلی')" />
            <v-text-field v-model="item.draft.now_village" density="compact" variant="outlined" hide-details :label="t('قریه/ناحیه فعلی')" />
          </div>
        </template>

        <template #item.captive_place="{ item }">
          <v-text-field v-model="item.draft.captive_place" class="text-group" density="compact" variant="outlined" :label="t('محل دستگیری')" hide-details />
        </template>

        <template #item.captive_marja="{ item }">
          <v-text-field v-model="item.draft.captive_marja" class="text-group" density="compact" variant="outlined" :label="t('مرجع دستگیری')" hide-details />
        </template>

        <template #item.captive_date="{ item }">
          <v-text-field v-model="item.draft.captive_date" class="text-group" density="compact" variant="outlined" :label="t('تاریخ دستگیری')" hide-details />
        </template>

        <template #item.captive_hour="{ item }">
          <v-text-field v-model="item.draft.captive_hour" class="text-group" density="compact" variant="outlined" :label="t('ساعت دستگیری')" hide-details />
        </template>

        <template #item.suspect_type="{ item }">
          <v-text-field v-model="item.draft.suspect_type" class="text-group" density="compact" variant="outlined" :label="t('نوعیت مظنونیت')" hide-details />
        </template>

        <template #item.phone_number="{ item }">
          <v-text-field v-model="item.draft.phone_number" class="text-group" density="compact" variant="outlined" :label="t('شماره تماس')" hide-details />
        </template>

        <template #item.captive_halat="{ item }">
          <v-text-field v-model="item.draft.captive_halat" class="text-group" density="compact" variant="outlined" :label="t('حالت دستگیری')" hide-details />
        </template>

        <template #item.suspect_tools="{ item }">
          <v-text-field v-model="item.draft.suspect_tools" class="text-group" density="compact" variant="outlined" :label="t('وسایل و شواهد گرفته شده')" hide-details />
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
                @click="openDocsPicker(item)"
              >
                {{ t('آپلود فایل ها') }}
              </v-btn>

              <div class="chips-row">
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

                <v-chip
                  v-for="(d, idx) in item.existingDocs || []"
                  :key="`${item.idOrTmp}-df-${idx}`"
                  size="small"
                  color="primary"
                  variant="text"
                  class="chip-existing"
                  :title="d.original_name || d.file_name"
                >
                  {{ shortName(d.original_name || d.file_name) }}
                </v-chip>
              </div>

              <v-btn
                class="download-all-btn"
                size="small"
                prepend-icon="mdi-download"
                :disabled="!item.id"
                color="error"
                @click="downloadAll(item)"
              >
                {{ t('دانلود فایل ها') }}
              </v-btn>
            </div>

            <div
              v-if="uploadingPct[item.idOrTmp] && uploadingPct[item.idOrTmp] < 100"
              class="mt-2"
            >
              <v-progress-linear
                :model-value="uploadingPct[item.idOrTmp]"
                height="6"
                color="primary"
                rounded
              />
              <div class="text-caption mt-1">
                {{ t('در حال آپلود') }} {{ uploadingPct[item.idOrTmp] }}%
              </div>
            </div>
            <div v-else-if="uploadingPct[item.idOrTmp] === 100" class="text-caption mt-1 text-success">
              100% — {{ t('آپلود شد') }}
            </div>
          </div>
        </template>

        <template #item.notes="{ item }">
          <v-text-field
            v-model="item.draft.notes"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('ملاحظات')"
            hide-details
          />
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="flex items-center gap-1">
            <v-btn color="primary" class="bg-navi" variant="outlined" @click="save(item)">
              {{ t('ثبت دیتا') }}
            </v-btn>
            <v-btn class="bg-error" variant="outlined" @click="remove(item)">
              {{ t('حذف دیتا') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">
            {{ t('زندانی تسلیم شده ای دریافت نشد') }}
          </div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>

  <!-- ✅ Filterable / printable table -->
  <FilterableTable
    :headers="headersFT"
    :rows="rows"
    :filters="filters"
    @update:filters="(v) => (filters = { ...v })"
    :top-groups="topGroups"
    :showIndex="true"
    :rtl="true"
    :index-label="t('شماره')"
    :all-label="t('همه')"
    class="mb-20 mx-2"
  />
</template>

<script setup>
import { ref, onMounted, watch, computed, reactive } from "vue";
import { useI18n } from "vue-i18n";
import AppBar from "../../components/AppBar.vue";
import { useDatabaseRepository } from "../../stores/DatabaseRepository";
import FilterableTable from "@/components/FilterableTable.vue";

const rows = ref([]);
const repo = useDatabaseRepository();
const drawer = ref(false);

/* progress map per row */
const uploadingPct = reactive({});
const startProgress = (key) => (uploadingPct[key] = 1);
const setProgress = (key, pct) => (uploadingPct[key] = pct);
const endProgress = (key) => (uploadingPct[key] = 0);

/* unwrap helper for possible slot proxy */
const getRow = (s) => (s && s.raw ? s.raw : s);

let tmpId = 0;
const newTmpId = () => `tmp-${++tmpId}`;

const { t, locale } = useI18n();
const isRtl = ref(locale.value === "fa");
watch(locale, (newLocale) => (isRtl.value = newLocale === "fa"));

/* Localized headers for the editable table */
const headers = computed(() => [
  { title: t("تصویر"), key: "picture", width: 120, sortable: false },
  { title: t("معرفت"), key: "intro" },
  { title: t("شماره تذکره"), key: "tazkira" },
  { title: t("عمر"), key: "age" },
  { title: t("حالت مدنی"), key: "halat_madani" },
  { title: t("سابقه جرمی"), key: "criminal_history" },
  { title: t("وظایف"), key: "jobs" },
  { title: t("زبان"), key: "language" },
  { title: t("سطح علمی"), key: "scientific_level" },
  { title: t("آدرس اصلی"), key: "main_add" },
  { title: t("آدرس فعلی"), key: "now_add" },
  { title: t("محل دستگیری"), key: "captive_place" },
  { title: t("مرجع دستگیری"), key: "captive_marja" },
  { title: t("تاریخ دستگیری"), key: "captive_date" },
  { title: t("ساعت دستگیری"), key: "captive_hour" },
  { title: t("نوعیت مظنونیت"), key: "suspect_type" },
  { title: t("شماره تماس"), key: "phone_number" },
  { title: t("حالت دستگیری"), key: "captive_halat" },
  { title: t("وسایل و شواهد گرفته شده با مظنون"), key: "suspect_tools" },
  { title: t("شواهد دستگیری زندانی"), key: "documents" },
  { title: t("ملاحظات"), key: "notes" },
  { title: t("عمل"), key: "actions", sortable: false },
]);

/* Atomic headers for FilterableTable (no slots, direct field access) */
const headersFT = computed(() => [
  { title: t("نام"), key: "name" },
  { title: t("نام پدر"), key: "father_name" },
  { title: t("نام پدر کلان"), key: "grand_father_name" },
  { title: t("نام مستعار"), key: "nickname" },

  { title: t("نمبر تذکره"), key: "tazkira" },
  { title: t("عمر"), key: "age" },
  { title: t("حالت مدنی"), key: "halat_madani" },
  { title: t("سابقه جرمی"), key: "criminal_history" },

  { title: t("وظیفه سابقه"), key: "old_job" },
  { title: t("وظیفه جدید"), key: "new_job" },

  { title: t("زبان"), key: "language" },
  { title: t("سطح علمی"), key: "scientific_level" },

  { title: t("ولایت اصلی"), key: "main_province" },
  { title: t("ولسوالی اصلی"), key: "main_district" },
  { title: t("قریه/ناحیه اصلی"), key: "main_village" },

  { title: t("ولایت فعلی"), key: "now_province" },
  { title: t("ولسوالی فعلی"), key: "now_district" },
  { title: t("قریه/ناحیه فعلی"), key: "now_village" },

  { title: t("محل دستگیری"), key: "captive_place" },
  { title: t("مرجع دستگیری"), key: "captive_marja" },
  { title: t("تاریخ دستگیری"), key: "captive_date" },
  { title: t("ساعت دستگیری"), key: "captive_hour" },

  { title: t("نوعیت مظنونیت"), key: "suspect_type" },
  { title: t("شماره تماس"), key: "phone_number" },
  { title: t("حالت دستگیری"), key: "captive_halat" },
  { title: t("وسایل و شواهد گرفته شده"), key: "suspect_tools" },

  { title: t("ملاحظات"), key: "notes" },
]);

/* Group bar for FilterableTable */
const topGroups = computed(() => [
  { title: t("معرفت"), keys: ["name", "father_name", "grand_father_name", "nickname"] },
  { title: t("اطلاعات شخصی"), keys: ["tazkira", "age", "halat_madani", "criminal_history"] },
  { title: t("وظایف"), keys: ["old_job", "new_job"] },
  { title: t("مهارت‌ها/زبان"), keys: ["language", "scientific_level"] },
  { title: t("آدرس اصلی"), keys: ["main_province", "main_district", "main_village"] },
  { title: t("آدرس فعلی"), keys: ["now_province", "now_district", "now_village"] },
  { title: t("جزئیات دستگیری"), keys: ["captive_place", "captive_marja", "captive_date", "captive_hour"] },
  { title: t("مظنون/تماس"), keys: ["suspect_type", "phone_number", "captive_halat", "suspect_tools"] },
]);

const filters = ref({});

/* row model */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? newTmpId(),

    picture: x.picture ?? null,
    previewUrl: x.picture ?? null,

    name: x.name ?? "",
    father_name: x.father_name ?? "",
    nickname: x.nickname ?? "",
    grand_father_name: x.grand_father_name ?? "",

    main_province: x.main_province ?? "",
    main_district: x.main_district ?? "",
    main_village: x.main_village ?? "",
    now_province: x.now_province ?? "",
    now_district: x.now_district ?? "",
    now_village: x.now_village ?? "",

    halat_madani: x.halat_madani ?? "",
    age: x.age ?? "",
    tazkira: x.tazkira ?? "",
    criminal_history: x.criminal_history ?? "",
    old_job: x.old_job ?? "",
    new_job: x.new_job ?? "",
    language: x.language ?? "",
    scientific_level: x.scientific_level ?? "",
    captive_place: x.captive_place ?? "",
    captive_marja: x.captive_marja ?? "",
    captive_date: x.captive_date ?? "",
    captive_hour: x.captive_hour ?? "",
    suspect_type: x.suspect_type ?? "",
    phone_number: x.phone_number ?? "",
    captive_halat: x.captive_halat ?? "",
    suspect_tools: x.suspect_tools ?? "",

    existingDocs: Array.isArray(x.documents) ? x.documents : [],

    notes: x.notes ?? "",
  };
  return { ...base, draft: { ...base, picture: null, _newDocs: [] } };
}

const toArray = (v) => (Array.isArray(v) ? v : Array.isArray(v?.data) ? v.data : []);

function hydrate() {
  const list = toArray(repo.CaptiveDeliveries || repo.DeleviryCaptives);
  rows.value = list.map(toRowModel);
}

async function fetch() {
  await repo
    .FetchCaptiveDeliveriesData({
      page: repo.page || 1,
      itemsPerPage: repo.itemsPerPage || 50,
    })
    .catch(() => {});
  hydrate();
}

function addBlankRow() {
  rows.value = [toRowModel({}), ...rows.value];
}

/* image picker */
function triggerFile(slotItem) {
  const item = getRow(slotItem);
  if (!item) return;
  document.getElementById(`up-${item.idOrTmp}`)?.click();
}
function onPickFile(slotItem, file) {
  const item = getRow(slotItem);
  if (!item) return;
  item.draft.picture = file || null;
  if (file) {
    if (item._blobUrl) URL.revokeObjectURL(item._blobUrl);
    item._blobUrl = URL.createObjectURL(file);
    item.previewUrl = item._blobUrl;
  } else {
    item.previewUrl = item.picture || null;
  }
}

/* documents */
function openDocsPicker(slotItem) {
  const item = getRow(slotItem);
  if (!item) return;
  document.getElementById(`docs-${item.idOrTmp}`)?.click();
}
function onPickDocs(slotItem, files) {
  const item = getRow(slotItem);
  if (!item?.draft) return;
  item.draft._newDocs = Array.isArray(files) ? files : [];
}
function shortName(name = "") {
  if (name.length <= 18) return name;
  const dot = name.lastIndexOf(".");
  if (dot === -1) return name.slice(0, 15) + "…";
  const ext = name.slice(dot);
  const base = name.slice(0, 15);
  return `${base}…${ext}`;
}

/* diff + formdata */
function diffDraft(item) {
  const d = item.draft || {};
  const changed = {};
  const keys = [
    "name",
    "father_name",
    "nickname",
    "grand_father_name",
    "main_province",
    "main_district",
    "main_village",
    "now_province",
    "now_district",
    "now_village",
    "halat_madani",
    "age",
    "tazkira",
    "criminal_history",
    "old_job",
    "new_job",
    "language",
    "scientific_level",
    "captive_place",
    "captive_marja",
    "captive_date",
    "captive_hour",
    "suspect_type",
    "phone_number",
    "captive_halat",
    "suspect_tools",
    "notes",
  ];
  for (const k of keys) {
    if ((d[k] ?? "") !== (item[k] ?? "")) changed[k] = d[k];
  }
  if (d.picture && typeof d.picture === "object") {
    changed.picture = d.picture;
  }
  return changed;
}
function toFormDataSelective(obj) {
  const fd = new FormData();
  for (const [k, v] of Object.entries(obj)) {
    if (v == null) continue;
    fd.append(k, v);
  }
  return fd;
}

/* save / delete / zip */
async function save(slotItem) {
  const item = getRow(slotItem);
  if (!item) return;

  const changed = item.id ? diffDraft(item) : { ...item.draft };
  const fd = toFormDataSelective(changed);
  (item.draft._newDocs || []).forEach((file) => fd.append("documents[]", file));

  const key = item.idOrTmp ?? item.id ?? `tmp-${Date.now()}`;
  startProgress(key);

  try {
    if (!item.id) {
      const created = await repo
        .CreateCaptiveDelivery(fd, (pct) => setProgress(key, pct))
        .catch(() => null);
      if (created?.id) {
        const model = toRowModel(created);
        model.idOrTmp = item.idOrTmp;
        Object.assign(item, model);
        item.draft = { ...model, picture: null, _newDocs: [] };
      } else {
        await fetch();
      }
      return;
    }

    if ([...fd.keys()].length === 0) return;

    const updated = await repo
      .UpdateCaptiveDelivery(item.id, fd, (pct) => setProgress(key, pct))
      .catch(() => null);
    if (updated?.id) {
      const model = toRowModel(updated);
      model.idOrTmp = item.idOrTmp;
      Object.assign(item, model);
      item.draft = { ...model, picture: null, _newDocs: [] };
    }
  } finally {
    endProgress(key);
  }
}

async function remove(slotItem) {
  const item = getRow(slotItem);
  if (!item) return;
  if (!item.id) {
    rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp);
    return;
  }
  if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name || t('رکورد') }))) return;
  await repo.DeleteCaptiveDelivery(item.id);
  await fetch();
}

async function downloadAll(slotItem) {
  const item = getRow(slotItem);
  if (!item?.id) return;
  const base = (item.name || "documents").toLowerCase().replace(/\s+/g, "_");
  await repo.DownloadCaptiveDeliveryZip(item.id, `${base}_documents.zip`);
}

onMounted(fetch);
</script>

<style scoped>
.custom-table :deep(.v-data-table__thead th) {
  background: rgb(var(--v-theme-primary));
  color: #fff;
  font-weight: 400;
}
.custom-table :deep(td) {
  vertical-align: middle;
  padding-top: 8px;
  padding-bottom: 8px;
}

/* compact inputs */
.text-group { display: grid; width: 100px; gap: 2px; }
.text-group-note { display: grid; width: 400px; gap: 2px; }
.addr-group { display: grid; grid-template-columns: repeat(3, minmax(100px, 1fr)); gap: 10px !important; }
.intro-group { display: grid; grid-template-columns: repeat(4, minmax(100px, 1fr)); gap: 10px; }
.drop-group2 { display: grid; grid-template-columns: repeat(2, minmax(100px, 1fr)); gap: 10px; }

.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }

.pic-cell { display: inline-flex; align-items: center; gap: 6px; margin-right: 12px; margin-left: 12px; }

/* labels/fields */
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

/* Documents layout */
.documents-inline { min-width: 520px; }
.docs-field-row {
  display: flex;
  align-items: center;
  gap: 4px;
  border: 1px dashed #9e9e9e;
  border-radius: 8px;
  padding: 6px 8px;
  box-shadow: 0 1px 3px rgba(96, 1, 1, 0.08);
}
.uploader-btn2 {
  white-space: nowrap;
  text-transform: none;
  padding: 0 10px;
  box-shadow: 0 1px 2px rgba(141, 2, 2, 0.14), 0 2px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(252, 250, 250, 0.06);
  border-radius: 8px;
}
.uploader-btn2:hover { box-shadow: 0 0 0 3px rgba(1, 63, 125, 0.25) !important; }
.uploader-btn2:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(1, 63, 125, 0.25); }
.chip-existing { background: #c8dae1; color: black !important; }
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
.chip-outline { background: #c8dae1; }
.download-all-btn { white-space: nowrap; }

/* Hidden input */
.hidden-file {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

/* tighter table cell padding */
.custom-table :deep(td) { padding-left: 8px !important; padding-right: 1px !important; }
:deep(.v-text-field), :deep(.v-autocomplete) { margin-right: -10px !important; margin-left: 2px; }
</style>
