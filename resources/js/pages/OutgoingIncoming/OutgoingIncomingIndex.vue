<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('مکتوب های صادره/وارده')" :subtitle="t('فهرست و جستجو')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- Search + Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو...')"
            density="comfortable"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            color="primary"
            rounded="lg"
            hide-details
            @keyup.enter="fetchAll"
            clearable
            @click:clear="clearSearch"
            class="bg-white search-field"
          />
        </v-col>

        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('ثبت جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- STATS -->
    <v-row class="px-2 mt-2 d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- Total -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="applyTypeFilter(null)">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">{{ totalCount }}</div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('تمام') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-file-document-multiple</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Incoming -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="applyTypeFilter('incomming')">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">{{ incomingCount }}</div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('وارده') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-download</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Outgoing to Reyasat -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="applyTypeFilter('outgoing_to_reyasat')">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-indigo fontfamily">
                {{ outToReyasatCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('صادره به ریاست') }}
              </div>
            </div>
            <v-avatar color="indigo" variant="tonal" size="48">
              <v-icon color="indigo">mdi-upload</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Outgoing to Others -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="applyTypeFilter('out_going_to_others')">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-cyan fontfamily">{{ outToOthersCount }}</div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('صادره به دیگران') }}
              </div>
            </div>
            <v-avatar color="cyan" variant="tonal" size="48">
              <v-icon color="cyan">mdi-share-variant</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Has Docs -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="applyHasDocsFilter()">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-purple fontfamily">{{ hasDocsCount }}</div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('دارای ضمیمه') }}
              </div>
            </div>
            <v-avatar color="deep-purple" variant="tonal" size="48">
              <v-icon color="deep-purple">mdi-paperclip</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Peek -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detailForPeek"
        :loading="loadingOne"
        title-key="number"
        :fields="headersFT"
        :cols="3"
        dir="rtl"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- MAIN TABLE -->
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
      :dateKeys="['sign_date', 'outgoing_incoming_date']"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    >
      <!-- Docs count -->
      <template #cell-documents_count="{ row }">
        <v-tooltip
          v-if="getDocs(getRow(row)).length > 0"
          text="کلیک کنید برای دانلود ضمیمه‌ها"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="primary"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="downloadDocs(row)"
            >
              <v-icon size="18" class="mr-1">mdi-paperclip</v-icon>
              {{ getDocs(getRow(row)).length }}
            </v-btn>
          </template>
        </v-tooltip>
        <span v-else class="text-red">0</span>
      </template>

      <!-- ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip text="مشاهده" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" @click.stop="openPeek(item)" color="blue">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="ویرایش" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                @click.stop="startEdit(item)"
                color="amber-darken-2"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="دانلود ضمیمه‌ها" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                :disabled="getDocs(getRow(item)).length === 0"
                @click.stop="downloadDocs(item)"
                color="green-darken-2"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="حذف" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                @click.stop="openDeleteDialog(item)"
                color="red-darken-2"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
        </div>
      </template>
    </FilterableTable>

    <!-- Delete Confirm -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
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
import { ref, computed, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";

import AppBar from "@/components/AppBar.vue";
import PeekButton from "@/components/PeekButton.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import DeleteConfirmDialog from "@/components/DeleteConfirmDialog.vue";

import { useOutgoingIncomingRepository } from "@/stores/OutgoingIncomingRepository";

const router = useRouter();
const { t, locale } = useI18n();
const repo = useOutgoingIncomingRepository();

/* RTL */
const isRtl = ref(locale.value === "fa");
watch(locale, (n) => (isRtl.value = n === "fa"));

/* Helpers */
function getRow(payload) {
  return payload?.raw ?? payload;
}
function getDocs(row) {
  if (!row) return [];
  return (
    row.documents ||
    row.sundryDocuments ||
    row.sundry_documents ||
    row.sundry_documents_list ||
    []
  );
}

/* Data */
const rowsFT = computed(() => repo.Items || []);
const totalCount = computed(() => rowsFT.value.length);

const incomingCount = computed(
  () => rowsFT.value.filter((r) => String(r.incomming || "").trim() !== "" && Number(r.incomming) !== 0).length
);

const outToReyasatCount = computed(
  () => rowsFT.value.filter((r) => String(r.outgoing_to_reyasat || "").trim() !== "" && Number(r.outgoing_to_reyasat) !== 0).length
);

const outToOthersCount = computed(
  () => rowsFT.value.filter((r) => String(r.out_going_to_others || "").trim() !== "" && Number(r.out_going_to_others) !== 0).length
);

const hasDocsCount = computed(
  () => rowsFT.value.filter((r) => getDocs(r).length > 0).length
);

/* Headers */
const headersFT = computed(() => [
  { title: t("ضمیمه‌ها"), key: "documents_count", sortable: false },

  { title: t("شماره"), key: "number" },
  { title: t("تاریخ امضاء"), key: "sign_date" },
  { title: t("تاریخ صادره/وارده"), key: "outgoing_incoming_date" },

  { title: t("وارده"), key: "incomming" },
  { title: t("صادره به ریاست"), key: "outgoing_to_reyasat" },
  { title: t("صادره به دیگران"), key: "out_going_to_others" },

  { title: t("احکام ریاست"), key: "ahkam_reyasat" },
  { title: t("احکام رد شده ریاست"), key: "ahkam_rad_shoda_reyasat" },
  { title: t("ریاست بدون هدایت"), key: "reyasat_na_direction" },

  { title: t("لوی ریاست احکام"), key: "loy_reyasat_ahkam" },
  { title: t("لوی ریاست احکام رد"), key: "loy_reyasat_ahakm_rad" },
  { title: t("لوی ریاست هدایت"), key: "loy_reyasat_direction" },

  { title: t("مرجع"), key: "marja" },
  { title: t("امضاء کننده"), key: "who_sign" },

  { title: t("شماره جلد"), key: "numer_of_jold" },
  { title: t("شماره لیست"), key: "list_number" },
  { title: t("مقدار نقل"), key: "naql_amount" },
  { title: t("صفحات"), key: "pages" },

  { title: t("ضمیمه (متن)"), key: "zamima" },
  { title: t("ملاحظات"), key: "notes" },
]);

/* Filters */
const filters = ref({});

function applyTypeFilter(typeKey) {
  const next = { ...(filters.value || {}) };
  // clear old filters first
  delete next.__type;
  delete next.incomming;
  delete next.outgoing_to_reyasat;
  delete next.out_going_to_others;

  if (!typeKey) {
    filters.value = next;
    return;
  }

  // lightweight filter by setting a key the table can match
  if (typeKey === "incomming") next.incomming = "1";
  if (typeKey === "outgoing_to_reyasat") next.outgoing_to_reyasat = "1";
  if (typeKey === "out_going_to_others") next.out_going_to_others = "1";

  filters.value = next;
}

function applyHasDocsFilter() {
  // If your FilterableTable supports custom filters, wire it there.
  // Here we just set a marker key; your FilterableTable can treat it specially if you want.
  filters.value = { ...(filters.value || {}), __hasDocs: true };
}

/* Top groups */
const topGroups = computed(() => [
  {
    title: t("اساسی"),
    keys: ["number", "sign_date", "outgoing_incoming_date", "marja", "who_sign"],
  },
  {
    title: t("صادره/وارده"),
    keys: ["incomming", "outgoing_to_reyasat", "out_going_to_others"],
  },
  {
    title: t("احکام"),
    keys: [
      "ahkam_reyasat",
      "ahkam_rad_shoda_reyasat",
      "reyasat_na_direction",
      "loy_reyasat_ahkam",
      "loy_reyasat_ahakm_rad",
      "loy_reyasat_direction",
    ],
  },
  { title: t("شماره‌ها"), keys: ["numer_of_jold", "list_number", "naql_amount", "pages"] },
]);

/* Error snackbar */
const showError = ref(false);
const errorMessage = ref("");
function showErrorMessage(msg) {
  errorMessage.value = msg;
  showError.value = true;
}

/* Peek */
const expanded = ref(false);
const selectedId = ref(null);
const loadingOne = ref(false);
const detail = ref(null);

const detailForPeek = computed(() => detail.value || null);

async function fetchOne(id) {
  if (!id) return;
  loadingOne.value = true;
  try {
    const d = await repo.FetchOutgoingIncomingItem(id);
    detail.value = d || {};
  } catch (e) {
    console.error(e);
    showErrorMessage(t("خطا در دریافت معلومات"));
  } finally {
    loadingOne.value = false;
  }
}

function openPeek(payload) {
  const row = getRow(payload);
  if (!row?.id) return;
  selectedId.value = row.id;
  expanded.value = true;
  fetchOne(row.id);
}

/* Fetch all */
async function fetchAll() {
  try {
    await repo.FetchOutgoingIncomingData({
      page: repo.page,
      perPage: repo.perPage,
      search: repo.Search,
    });
  } catch (e) {
    console.error(e);
    showErrorMessage(t("خطا در دریافت فهرست"));
  }
}

function clearSearch() {
  repo.Search = "";
  fetchAll();
}

/* Navigation */
function goCreate() {
  router.push("/app/outgoingincomingform");
}
function startEdit(payload) {
  const row = getRow(payload);
  if (!row?.id) return;
  router.push(`/app/outgoingincomingform/${row.id}`);
}

/* Download */
async function downloadDocs(payload) {
  const row = getRow(payload);
  if (!row?.id) return;

  try {
    if (typeof repo.DownloadOutgoingIncomingZip === "function") {
      await repo.DownloadOutgoingIncomingZip(row.id);
    } else {
      showErrorMessage(t("دانلود ضمیمه‌ها تنظیم نشده است"));
    }
  } catch (e) {
    console.error(e);
    showErrorMessage(t("خطا در دانلود ضمیمه‌ها"));
  }
}

/* Delete */
const deleteDialog = ref(false);
const deleteTarget = ref(null);

function openDeleteDialog(payload) {
  const row = getRow(payload);
  if (!row?.id) return;
  deleteTarget.value = row;
  deleteDialog.value = true;
}

async function handleDeleteConfirmed(target) {
  const row = target || deleteTarget.value;
  if (!row?.id) {
    deleteDialog.value = false;
    deleteTarget.value = null;
    return;
  }

  try {
    await repo.DeleteOutgoingIncoming(row.id);
    await fetchAll();
  } catch (e) {
    console.error(e);
    showErrorMessage(t("خطا در حذف"));
  } finally {
    deleteDialog.value = false;
    deleteTarget.value = null;
  }
}

onMounted(fetchAll);
</script>

<style scoped>
.stat-card {
  border-radius: 12px;
  transition: all 0.3s ease;
  cursor: pointer;
  border: 2px solid transparent;
  height: 100px;
  font-family: var(--app-font) !important;
}
.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}
.text-green {
  color: #4caf50 !important;
}
.text-purple {
  color: #9c27b0 !important;
}
.search-field :deep(.v-field) {
  min-height: 48px;
}
.fontfamily {
  font-family: var(--app-font) !important;
}
</style>
