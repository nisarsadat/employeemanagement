<template>
  <v-container fluid class="py-6 mt-12">
    <AppBar
      :title="t('واقعات')"
      :subtitle="t('اضافه کردن واقعات')"
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
          {{ t('اظافه کردن واقعه') }}
          <v-icon>mdi-plus</v-icon>
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
        :items-length="repo.totalItems || 0"
        item-key="idOrTmp"
        class="elevation-2 custom-table uniform-input"
        :dir="isRtl ? 'rtl' : 'ltr'"
        @update:page="(p) => { repo.page = p; fetch(); }"
        @update:items-per-page="(n) => { repo.itemsPerPage = n; fetch(); }"
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

        <!-- Documents (Upload + chips OUTSIDE the input) -->
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
                dinsity="compact"
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
            <v-btn class="bg-primary" variant="outlined" color="surface" @click="save(item)">
              {{ t('ذخیره دیتا') }}
            </v-btn>
            <v-btn class="bg-error" variant="outlined" @click="del(item)">
              {{ t('حذف دیتا') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">
            {{ t('واقعه دریافت نشد') }}
          </div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>

  <!-- ✅ Filterable / printable table for events -->
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
import { ref, onMounted, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import AppBar from "../../components/AppBar.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import { useDatabaseRepository } from "../../stores/DatabaseRepository";

const repo = useDatabaseRepository();

/* ---------- helpers to guarantee arrays ---------- */
const toArray = (v) => {
  if (v && typeof v.then === "function") return [];
  if (Array.isArray(v)) return v;
  if (Array.isArray(v?.data)) return v.data;
  if (Array.isArray(v?.items)) return v.items;
  if (v && typeof v === "object") return Object.values(v);
  return [];
};

const resourceCodeItems = computed(() => toArray(repo.resourcecode));
const officeEmployeesCodeItems = computed(() => toArray(repo.officeemployeescode));
const zoneEmployeesCodeItems = computed(() => toArray(repo.ZoneEmployeesCode));

const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
const zoneEmployeesItems = computed(() => toArray(repo.ZoneEmployees));
const resourceItems = computed(() => toArray(repo.Resource));

const eventTypeItems = computed(() => toArray(repo.eventTypeOptions));
const janiTalafatItems = computed(() => toArray(repo.janiTalafatOptions));
const maliTalafatItems = computed(() => toArray(repo.maliTalafatOptions));
/* -------------------------------------------------- */

const rows = ref([]);
let tmpId = 0;
const newTmpId = () => `tmp-${++tmpId}`;

const { t, locale } = useI18n();
const isRtl = ref(locale.value === "fa");
watch(locale, (newLocale) => (isRtl.value = newLocale === "fa"));

/* Editable Vuetify headers (can be grouped visually in slots) */
const headers = [
  { title: "کود منبع", key: "resource_code" },
  { title: "کود کارمند ", key: "office_employee_code", width: 300 },
  { title: "نوعیت واقعه", key: "event_type" },
  { title: "نوعیت مشخص واقعه", key: "event_specific_type" },
  { title: "هدف کی بود؟/جای بود", key: "target_person" },
  { title: "جرم را کی انجام داده", key: "who_did" },
  { title: "تاریخ واقعه", key: "event_date" },
  { title: "ساعت واقعه", key: "event_hour" },
  { title: "محل واقعه", key: "event_place" },
  { title: "تلفات جانی", key: "jani_talafat" },
  { title: "تلفات مالی", key: "mali_talafat" },
  { title: "کود کارمند واقعه گیرنده", key: "event_employee_code" },
  { title: "تفصیل", key: "tafseel" },
  { title: "شواهد مربوط به واقعه", key: "documents" },
  { title: "وضاحت", key: "talks" },
  { title: "ملاحظات", key: "notes" },
  { title: "عمل", key: "actions", sortable: false },
];

/* ✅ Headers for FilterableTable — ATOMIC KEYS ONLY */
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

  { title: t("تلفات جانی"), key: "jani_talafat" },
  { title: t("تلفات مالی"), key: "mali_talafat" },

  /* These two are IDs; if you also want the visible code text, map it server-side as strings */
  { title: t("کود کارمند واقعه گیرنده (ID)"), key: "event_office_employee_code" },
  { title: t("کود رزیدانت واقعه گیرنده (ID)"), key: "event_zone_employee_code" },
  { title: t("تفصیل"), key: "tafseel" },
  { title: t("وضاحت"), key: "talks" },
  { title: t("ملاحظات"), key: "notes" },

]);

/* ✅ Grouped header row for FilterableTable */
const topGroups = computed(() => [
  { title: t("کودهای نمایشی"), keys: ["resource_code", "office_employee_code"] },
  { title: t("نوع واقعه"), keys: ["event_type", "event_specific_type"] },
  { title: t("هدف و عامل"), keys: ["target_person", "who_did"] },
  { title: t("زمان"), keys: ["event_date", "event_hour"] },
  { title: t("مکان"), keys: ["zone", "hawza", "specific_place"] },
  { title: t("تلفات"), keys: ["jani_talafat", "mali_talafat"] },
  { title: t("متن‌ها"), keys: ["tafseel", "talks", "notes"] },
  { title: t("کودهای کارمند (IDs)"), keys: ["event_office_employee_code", "event_zone_employee_code"] },
]);

/* filters for FilterableTable */
const filters = ref({});

/* map API -> row */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? newTmpId(),

    resource_id: x.resource?.id ?? null,
    office_employee_id: x.office_employee?.id ?? null,

    /* display codes coming from API if present */
    resource_code: x.resource_code ?? "",
    office_employee_code: x.office_employee_code ?? "",

    event_type: x.event_type ?? "",
    event_specific_type: x.event_specific_type ?? "",
    who_did: x.who_did ?? "",
    target_person: x.target_person ?? "",

    event_date: x.event_date ?? "",
    event_hour: x.event_hour ?? "",

    zone: x.zone ?? "",
    hawza: x.hawza ?? "",
    specific_place: x.specific_place ?? "",

    jani_talafat: x.jani_talafat ?? "",
    mali_talafat: x.mali_talafat ?? "",

    /* these two are IDs per your current code */
    event_office_employee_code: x.event_office_employee?.id ?? null,
    event_zone_employee_code: x.event_zone_employee?.id ?? null,

    existingDocs: x.documents || [],

    tafseel: x.tafseel ?? "",
    talks: x.talks ?? "",
    notes: x.notes ?? "",
  };
  return { ...base, draft: { ...base, _newDocs: [] } };
}

function hydrate() {
  rows.value = toArray(repo.Events).map(toRowModel);
}

async function fetch() {
  await repo
    .FetchEventsData({ page: repo.page || 1, itemsPerPage: repo.itemsPerPage || 50 })
    .catch(() => {});
  hydrate();
}

/* add blank row */
function addBlankRow() {
  rows.value = [toRowModel({}), ...rows.value];
}

/* documents */
function triggerDocs(item) {
  const el = document.getElementById(`docs-${item.idOrTmp}`);
  if (el) el.click();
}
function onPickDocs(item, files) {
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

/* save / delete */
async function save(item) {
  const changed = diffDraft(item);
  const fd = toFormDataSelective(changed);
  (item.draft._newDocs || []).forEach((file) => fd.append("documents[]", file));

  if (!item.id) {
    const created = await repo.CreateEvent(fd).catch(() => null);
    if (created?.id) {
      const model = toRowModel(created);
      model.idOrTmp = item.idOrTmp;
      Object.assign(item, model);
      item.draft = { ...model, _newDocs: [] };
    } else {
      await fetch();
    }
    return;
  }

  const updated = await repo.UpdateEvent(item.id, fd).catch(() => null);
  if (updated?.id) {
    const model = toRowModel(updated);
    model.idOrTmp = item.idOrTmp;
    Object.assign(item, model);
    item.draft = { ...model, _newDocs: [] };
  }
}

async function del(item) {
  if (!item.id) {
    rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp);
    return;
  }
  if (!confirm(t("آیا از حذف این واقعه مطمئن هستید؟"))) return;
  await repo.DeleteEvent(item.id);
  await fetch();
}

/* Download All (ZIP) */
async function downloadAll(item) {
  if (!item.id) return;
  const base = (item.target_person || "documents").toLowerCase().replace(/\s+/g, "_");
  const filename = `${base}_documents.zip`;
  await repo.DownloadEventZip(item.id, filename);
}

/* init */
onMounted(async () => {
  await fetch();
  await Promise.all([
    repo.GetOfficeEmployee(),
    repo.GetZoneEmployees(),
    repo.GetResource(),
    repo.GetOfficeEmployeeCode(),
    repo.GetZoneEmployeesCode(),
    repo.GetResourceCode(),
  ]).catch(() => {});
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

/* ---------- Documents layout (chips OUTSIDE input) ---------- */
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

/* Chips horizontal row (center, grows, scrolls if many) */
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

/* Minor chip styles */
.chip-outline { background: #c8dae1; }

/* Download button */
.download-all-btn { white-space: nowrap; }

/* Hidden input */
.hidden-file {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

/* Reduce padding between table cells globally */
.custom-table :deep(td) { padding-left: 8px !important; padding-right: 1px !important; }

/* Tighten grouped input layouts */
.text-group,
.text-group-note,
.addr-group,
.drop-group,
.drop-group2 { gap: 4px !important; }

/* Reduce horizontal margin around inputs */
:deep(.v-text-field),
:deep(.v-autocomplete) { margin-right: -10px !important; margin-left: 2px; }
</style>
