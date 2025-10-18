<template>
  <v-container fluid class="py-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('واقعات')"
      :subtitle="t('اضافه کردن واقعات')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search -->
    <v-row class="d-flex">
      <v-col cols="12" md="9">
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
      <!-- Optional: add/reset editor row -->
      <!--
      <v-col cols="12" md="3" class="text-md-right">
        <v-btn color="primary" @click="setBlankEditorRow">
          {{ t('اظافه کردن واقعه') }} <v-icon>mdi-plus</v-icon>
        </v-btn>
      </v-col>
      -->
    </v-row>

    <!-- Single-row editor (create/edit) -->
    <v-card class="tablebody" ref="editorCard">
      <v-data-table
        :headers="headers"
        :items="rows"
        item-key="idOrTmp"
        :dir="isRtl ? 'rtl' : 'ltr'"
        class="elevation-2 custom-table uniform-input"
        hide-default-footer
      >
        <!-- resource_id -->
        <template #item.resource_code="{ item }">
          <v-autocomplete
            v-model="item.draft.resource_id"
            :items="resourceCodeItems"
            item-title="code_number"
            item-value="id"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کود منبع')"
            hide-details
            clearable
          />
        </template>

        <!-- office_employee_id -->
        <template #item.office_employee_code="{ item }">
          <v-autocomplete
            v-model="item.draft.office_employee_id"
            :items="officeEmployeesCodeItems"
            item-title="code_number"
            item-value="id"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کود کارمند دفتر')"
            hide-details
            clearable
          />
        </template>

        <!-- event_type -->
        <template #item.event_type="{ item }">
          <v-autocomplete
            v-model="item.draft.event_type"
            :items="eventTypeItems"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نوعیت واقعه')"
            hide-details
            clearable
          />
        </template>

        <!-- free text -->
        <template #item.event_specific_type="{ item }">
          <v-text-field
            v-model="item.draft.event_specific_type"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نوعیت مشخص واقعه')"
            hide-details
            clearable
          />
        </template>

        <template #item.target_person="{ item }">
          <v-text-field
            v-model="item.draft.target_person"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('هدف کی بود/مکان بود')"
            hide-details
            clearable
          />
        </template>

        <template #item.who_did="{ item }">
          <v-text-field
            v-model="item.draft.who_did"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کی جرم را انجام داده')"
            hide-details
            clearable
          />
        </template>

        <!-- text (not datepicker) -->
        <template #item.event_date="{ item }">
          <v-text-field
            v-model="item.draft.event_date"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تاریخ واقعه')"
            hide-details
            clearable
          />
        </template>

        <template #item.event_hour="{ item }">
          <v-text-field
            v-model="item.draft.event_hour"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('ساعت واقعه')"
            hide-details
            clearable
          />
        </template>

        <!-- place -->
        <template #item.event_place="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.zone"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('زون')"
              clearable
            />
            <v-text-field
              v-model="item.draft.hawza"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('حوزه')"
              clearable
            />
            <v-text-field
              v-model="item.draft.specific_place"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('محل مشخص')"
              clearable
            />
          </div>
        </template>

        <!-- NEW: coordenates -->
        <template #item.coordenates="{ item }">
          <v-text-field
            v-model="item.draft.coordenates"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کوردینات')"
            hide-details
            clearable
          />
        </template>

        <!-- jani_talafat -->
        <template #item.jani_talafat="{ item }">
          <v-autocomplete
            v-model="item.draft.jani_talafat"
            :items="janiTalafatItems"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تلفات جانی')"
            hide-details
            clearable
          />
        </template>

        <!-- mali_talafat -->
        <template #item.mali_talafat="{ item }">
          <v-autocomplete
            v-model="item.draft.mali_talafat"
            :items="maliTalafatItems"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تلفات مالی')"
            hide-details
            clearable
          />
        </template>

        <!-- event employee codes -->
        <template #item.event_employee_code="{ item }">
          <div class="drop-group2">
            <v-autocomplete
              v-model="item.draft.event_office_employee_code"
              :items="officeEmployeesItems"
              item-title="code_number"
              item-value="id"
              :label="t('کود کارمند واقعه گیرنده')"
              density="compact"
              variant="outlined"
              clearable
              hide-details
            />
            <v-autocomplete
              v-model="item.draft.event_zone_employee_code"
              :items="zoneEmployeesItems"
              item-title="code_number"
              item-value="id"
              :label="t('کود رزیدانت واقعه گیرنده')"
              density="compact"
              variant="outlined"
              clearable
              hide-details
            />
          </div>
        </template>

        <!-- text areas -->
        <template #item.tafseel="{ item }">
          <v-text-field
            v-model="item.draft.tafseel"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('تفصیل')"
            hide-details
            clearable
          />
        </template>

        <template #item.talks="{ item }">
          <v-text-field
            v-model="item.draft.talks"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('وضاحت')"
            hide-details
            clearable
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
                @click="openDocsPicker(item)"
              >
                {{ t('آپلود فایل ها') }}
              </v-btn>

              <div class="chips-row">
                <!-- new files -->
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
                >
                  {{ shortName(d.original_name || d.file_name) }}
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

            <!-- progress -->
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
                ...{{ t('در حال پلود') }} {{ uploadingPct[item.idOrTmp] }}%
              </div>
            </div>
            <div
              v-else-if="uploadingPct[item.idOrTmp] === 100"
              class="text-caption mt-1 text-success"
            >
              100% — {{ t('آپلود شد') }}
            </div>
          </div>
        </template>

        <template #item.notes="{ item }">
          <v-text-field
            v-model="item.draft.notes"
            density="compact"
            class="text-group-note"
            variant="outlined"
            :label="t('ملاحظات')"
            hide-details
            clearable
          />
        </template>

        <template #item.actions="{ item }">
          <div class="flex items-center gap-1">
            <v-btn class="bg-primary" variant="outlined" color="surface" @click="save(item)" :loading="item.loading">
              {{ t('ذخیره دیتا') }}
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
    </v-card>

    <!-- Peek drawer -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title-key="event_type"
        :fields="headers"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- Filterable / printable table with Edit + Peek -->
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

    <!-- Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, onMounted, watch, computed, nextTick, reactive } from "vue";
import { useI18n } from "vue-i18n";
import AppBar from "../../components/AppBar.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import PeekButton from "@/components/PeekButton.vue";
import { useDatabaseRepository } from "../../stores/DatabaseRepository";

const repo = useDatabaseRepository();

/* i18n / RTL */
const { t, locale } = useI18n();
const drawer = ref(false);
const isRtl = ref(locale.value === "fa");
watch(locale, (newLocale) => (isRtl.value = newLocale === "fa"));

/* Snackbar */
const showError = ref(false);
const errorMessage = ref("");
const toast = (msg) => {
  errorMessage.value = msg;
  showError.value = true;
};

/* helpers to guarantee arrays */
const toArray = (v) => {
  if (v && typeof v.then === "function") return [];
  if (Array.isArray(v)) return v;
  if (Array.isArray(v?.data)) return v.data;
  if (Array.isArray(v?.items)) return v.items;
  if (v && typeof v === "object") return Object.values(v);
  return [];
};

/* option sources */
const resourceCodeItems = computed(() => toArray(repo.resourcecode));
const officeEmployeesCodeItems = computed(() => toArray(repo.officeemployeescode));
const zoneEmployeesCodeItems = computed(() => toArray(repo.ZoneEmployeesCode));

const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
const zoneEmployeesItems = computed(() => toArray(repo.ZoneEmployees));
const eventTypeItems = computed(() => toArray(repo.eventTypeOptions));
const janiTalafatItems = computed(() => toArray(repo.janiTalafatOptions));
const maliTalafatItems = computed(() => toArray(repo.maliTalafatOptions));

/* rows - single-row editor */
const rows = ref([]);
let tmpId = 0;
const newTmpId = () => `tmp-${++tmpId}`;

/* Upload progress per row */
const uploadingPct = reactive({});
const startProgress = (key) => (uploadingPct[key] = 1);
const setProgress = (key, pct) => (uploadingPct[key] = pct);
const endProgress = (key) => (uploadingPct[key] = 0);

/* unwrap for slot proxies */
const getRow = (s) => (s && s.raw ? s.raw : s);

/* map API -> row */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? newTmpId(),
    loading: false,

    /* foreign keys (IDs) */
    resource_id: x.resource_id ?? x.resource?.id ?? null,
    office_employee_id: x.office_employee_id ?? x.office_employee?.id ?? null,

    /* visible codes if server returns them */
    resource_code: x.resource_code ?? "",
    office_employee_code: x.office_employee_code ?? "",

    /* event main fields */
    event_type: x.event_type ?? "",
    event_specific_type: x.event_specific_type ?? "",
    who_did: x.who_did ?? "",
    target_person: x.target_person ?? "",

    /* time */
    event_date: x.event_date ?? "",
    event_hour: x.event_hour ?? "",

    /* place */
    zone: x.zone ?? "",
    hawza: x.hawza ?? "",
    specific_place: x.specific_place ?? "",
    coordenates: x.coordenates ?? "",       // NEW

    /* losses */
    jani_talafat: x.jani_talafat ?? "",
    mali_talafat: x.mali_talafat ?? "",

    /* receiver codes (IDs) */
    event_office_employee_code: x.event_office_employee_code ?? x.event_office_employee?.id ?? null,
    event_zone_employee_code: x.event_zone_employee_code ?? x.event_zone_employee?.id ?? null,

    /* docs */
    existingDocs: Array.isArray(x.documents) ? x.documents : [],

    /* texts */
    tafseel: x.tafseel ?? "",
    talks: x.talks ?? "",
    notes: x.notes ?? "",
  };
  return { ...base, draft: { ...base, _newDocs: [] } };
}

/* create a blank row */
function setBlankEditorRow() {
  rows.value = [toRowModel({})];
}

/* headers (editor) */
const headers = computed(() => [
  { title: t("کود منبع"), key: "resource_code" },
  { title: t("کود کارمند "), key: "office_employee_code", width: 300 },
  { title: t("نوعیت واقعه"), key: "event_type" },
  { title: t("نوعیت مشخص واقعه"), key: "event_specific_type" },
  { title: t("هدف کی بود؟/جای بود"), key: "target_person" },
  { title: t("جرم را کی انجام داده"), key: "who_did" },
  { title: t("تاریخ واقعه"), key: "event_date" },
  { title: t("ساعت واقعه"), key: "event_hour" },
  { title: t("محل واقعه"), key: "event_place" },
  { title: t("کوردینات"), key: "coordenates" },        // NEW
  { title: t("تلفات جانی"), key: "jani_talafat" },
  { title: t("تلفات مالی"), key: "mali_talafat" },
  { title: t("کود کارمند واقعه گیرنده"), key: "event_employee_code" },
  { title: t("تفصیل"), key: "tafseel" },
  { title: t("شواهد مربوط به واقعه"), key: "documents" },
  { title: t("وضاحت"), key: "talks" },
  { title: t("ملاحظات"), key: "notes" },
  { title: t("عمل"), key: "actions", sortable: false },
]);

/* Filterable table (bottom) */
const rowsFT = computed(() => toArray(repo.Events));
const headersFT = computed(() => [
  { title: t("کود منبع (نمایشی)"), key: "resource_code" },
  { title: t("کود کارمند (نمایشی)"), key: "office_employee_code" },

  { title: t("نوعیت واقعه"), key: "event_type" },
  { title: t("نوعیت مشخص واقعه"), key: "event_specific_type" },
  { title: t("هدف/مکان"), key: "target_person" },
  { title: t("عامل واقعه"), key: "who_did" },

  { title: t("تاریخ واقعه"), key: "event_date" },
  { title: t("ساعت واقعه"), key: "event_hour" },

  { title: t("زون"), key: "zone" },
  { title: t("حوزه"), key: "hawza" },
  { title: t("محل مشخص"), key: "specific_place" },
  { title: t("کوردینات"), key: "coordenates" },        // NEW

  { title: t("تلفات جانی"), key: "jani_talafat" },
  { title: t("تلفات مالی"), key: "mali_talafat" },

  { title: t("کود کارمند واقعه گیرنده (ID)"), key: "event_office_employee_code" },
  { title: t("کود رزیدانت واقعه گیرنده (ID)"), key: "event_zone_employee_code" },
  { title: t("تفصیل"), key: "tafseel" },
  { title: t("وضاحت"), key: "talks" },
  { title: t("ملاحظات"), key: "notes" },
]);

const topGroups = computed(() => [
  { title: t("کودهای نمایشی"), keys: ["resource_code", "office_employee_code"] },
  { title: t("نوع واقعه"), keys: ["event_type", "event_specific_type"] },
  { title: t("هدف و عامل"), keys: ["target_person", "who_did"] },
  { title: t("زمان"), keys: ["event_date", "event_hour"] },
  { title: t("مکان"), keys: ["zone", "hawza", "specific_place", "coordenates"] }, // includes coordenates
  { title: t("تلفات"), keys: ["jani_talafat", "mali_talafat"] },
  { title: t("کودهای کارمند (IDs)"), keys: ["event_office_employee_code", "event_zone_employee_code"] },
  { title: t("متن‌ها"), keys: ["tafseel", "talks", "notes"] },
]);

/* filters for FilterableTable */
const filters = ref({});

/* docs */
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

/* diff + selective FormData */
function diffDraft(item) {
  const d = item.draft || {};
  const changed = {};
  const keys = [
    "resource_id",
    "office_employee_id",
    "event_type",
    "event_specific_type",
    "target_person",
    "who_did",
    "event_date",
    "event_hour",
    "zone",
    "hawza",
    "specific_place",
    "coordenates",                // NEW
    "jani_talafat",
    "mali_talafat",
    "event_office_employee_code",
    "event_zone_employee_code",
    "tafseel",
    "talks",
    "notes",
  ];
  for (const k of keys) {
    if ((d[k] ?? "") !== (item[k] ?? "")) changed[k] = d[k];
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

/* save / delete / download */
async function save(slotItem) {
  const item = getRow(slotItem);
  if (!item) return;

  const changed = item.id ? diffDraft(item) : { ...item.draft };
  const fd = toFormDataSelective(changed);
  (item.draft._newDocs || []).forEach((file) => fd.append("documents[]", file));

  const key = item.idOrTmp ?? item.id ?? `tmp-${Date.now()}`;
  item.loading = true;
  startProgress(key);

  try {
    if (!item.id) {
      const created = await repo.CreateEvent(fd, (pct) => setProgress(key, pct)).catch(() => null);
      if (created?.id) {
        await fetchAll();
        setBlankEditorRow();
        toast(t("واقعه ایجاد شد"));
      } else {
        await fetchAll();
        setBlankEditorRow();
      }
      return;
    }

    if ([...fd.keys()].length === 0) {
      toast(t("هیچ تغییری برای ذخیره وجود ندارد"));
      return;
    }

    const updated = await repo.UpdateEvent(item.id, fd, (pct) => setProgress(key, pct)).catch(() => null);
    if (updated?.id) {
      await fetchAll();
      setBlankEditorRow();
      toast(t("واقعه بروزرسانی شد"));
    }
  } finally {
    endProgress(key);
    item.loading = false;
  }
}

async function del(slotItem) {
  const item = getRow(slotItem);
  if (!item) return;
  if (!item.id) {
    setBlankEditorRow();
    return;
  }
  if (!confirm(t("آیا از حذف این واقعه مطمئن هستید؟"))) return;

  try {
    await repo.DeleteEvent(item.id);
    await fetchAll();
    setBlankEditorRow();
    toast(t("واقعه حذف شد"));
  } catch {
    toast(t("خطا در حذف واقعه"));
  }
}

async function downloadAll(slotItem) {
  const item = getRow(slotItem);
  if (!item?.id) return;
  const base = (item.event_type || "documents").toLowerCase().replace(/\s+/g, "_");
  await repo.DownloadEventZip(item.id, `${base}_documents.zip`);
}

function clearEditor(slotItem) {
  const item = getRow(slotItem);
  if (!item) return;
  item.draft = { ...toRowModel({}).draft };
  item.draft._newDocs = [];
  toast(t("ورودی‌ها پاک شد"));
}

/* fetch list for FilterableTable */
async function fetchAll() {
  try {
    await repo.FetchEventsData({
      page: 1,
      itemsPerPage: 100000,
      search: repo.Search,
    });
  } catch {}
}

/* Edit from FilterableTable */
const editorCard = ref(null);
async function startEdit(row) {
  try {
    const id = row?.id;
    if (!id) return;

    if (typeof repo.FetchEventData === "function") {
      await repo.FetchEventData(id);
      const single =
        (repo.Event && Array.isArray(repo.Event) ? repo.Event[0] : repo.Event) || row;
      rows.value = [toRowModel(single)];
    } else {
      await fetchAll();
      const list = rowsFT.value || [];
      const single = list.find((r) => r.id === id) || row;
      rows.value = [toRowModel(single)];
    }

    await nextTick();
    const el = editorCard.value?.$el || editorCard.value;
    el?.scrollIntoView?.({ behavior: "smooth", block: "start" });
  } catch {
    toast(t("خطا در باز کردن مورد برای ویرایش"));
  }
}

/* ===== Peek state & methods ===== */
const expanded = ref(false);
const selectedId = ref(null);
const loading = ref(false);
const detail = ref(null);

async function fetchOne(id) {
  loading.value = true;
  try {
    if (typeof repo.FetchEventData === "function") {
      await repo.FetchEventData(id);
      detail.value =
        (repo.Event && Array.isArray(repo.Event) ? repo.Event[0] : repo.Event) || {};
    } else {
      await fetchAll();
      const list = rowsFT.value || [];
      detail.value = list.find((r) => r.id === id) || {};
    }
  } finally {
    loading.value = false;
  }
}
function openPeek(payload) {
  const row = payload?.raw ?? payload;
  if (!row?.id) return;
  selectedId.value = row.id;
  expanded.value = true;
  fetchOne(row.id);
}

/* init */
onMounted(async () => {
  await Promise.allSettled([
    repo.GetOfficeEmployee?.(),
    repo.GetZoneEmployees?.(),
    repo.GetResource?.(),
    repo.GetOfficeEmployeeCode?.(),
    repo.GetZoneEmployeesCode?.(),
    repo.GetResourceCode?.(),
  ]);
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
.custom-table :deep(td) {
  vertical-align: middle;
  padding-top: 8px;
  padding-bottom: 8px;
}

/* compact inputs */
.text-group { display: grid; width: 200px; gap: 2px; }
.text-group-note { display: grid; width: 400px; gap: 2px; }
.addr-group { display: grid; grid-template-columns: repeat(3, minmax(200px, 1fr)); gap: 2px; }
.drop-group2 { display: grid; grid-template-columns: repeat(2, minmax(250px, 1fr)); gap: 2px; }

.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }

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
  gap: 8px;
  border: 1px dashed #9e9e9e;
  border-radius: 8px;
  padding: 6px 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}
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
