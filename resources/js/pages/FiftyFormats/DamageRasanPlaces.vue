<!-- src/views/fifthyFormats/DamageRasanPlaces.vue -->
<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="t('نقاط آسیب رسان')"
      :subtitle="t('فهرست، جستجو و مدیریت نقاط آسیب رسان')"
    >
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <!-- 🔍 Search + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Field -->
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
            class="bg-white"
          />
        </v-col>

        <!-- Create Button -->
        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            color="primary"
            variant="flat"
            size="large"
            class="text-none mx-4"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('جدید') }}
          </v-btn>
        </v-col>
      </v-row>

      <!-- 📊 Filter Cards -->
      <v-row class="mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- All -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'all' }"
            @click="cardFilter = 'all'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-format-list-bulleted</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('تمام نقاط') }}</div>
                <div class="stat-card-subtitle">
                  {{ t('مجموع') }}: {{ stats.total }}
                </div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With coordinates -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withCoordinates' }"
            @click="cardFilter = 'withCoordinates'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-map-marker-radius</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای مختصات') }}</div>
                <div class="stat-card-subtitle">
                  {{ t('تعداد') }}: {{ stats.withCoordinates }}
                </div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With threat type -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withThreat' }"
            @click="cardFilter = 'withThreat'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-alert-circle-outline</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای نوع تهدید') }}</div>
                <div class="stat-card-subtitle">
                  {{ t('تعداد') }}: {{ stats.withThreat }}
                </div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With resource -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withResource' }"
            @click="cardFilter = 'withResource'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-search</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای منبع') }}</div>
                <div class="stat-card-subtitle">
                  {{ t('تعداد') }}: {{ stats.withResource }}
                </div>
              </div>
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- 👁 Peek drawer -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loadingOne"
        title-key="specific_place"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        :auto-detect-dates="false"
        :date-keys="[]"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- 📋 MAIN TABLE -->
    <FilterableTable
      :headers="headersFT"
      :rows="filteredRowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      class=" mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >
      <template #cell-picture="{ row }">
        <PictureCell
          :picture="row.picture"
          :alt="row.name"
          :title="t('پیش نمایش تصویر')"
          :close-text="t('بستن')"
        />
      </template>

      <!-- 🔗 RESOURCE CODE CLICKABLE WITH TOOLTIP -->
      <template #cell-resource_code="{ row }">
        <v-tooltip :text="t('رفتن به صفحه منابع')" location="top">
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              v-if="row.resource_code"
              variant="tonal"
              color="primary"
              append-icon="mdi-account"
              size="default"
              @click.stop="goToResource(row)"
            >
              {{ row.resource_code }}
            </v-btn>
            <span v-else>-</span>
          </template>
        </v-tooltip>
      </template>

      <!-- 🗺 MAP CELL -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.specific_place || row.damage_place || row.province"
          :subtitle="row.Province_addr"
          type="damagerasan"
          :photo-url="null"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده نقطه آسیب رسان')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-bind="tooltipProps"
                size="small"
                color="blue"
                @click.stop="openPeek(item)"
              >
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش نقطه آسیب رسان')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-bind="tooltipProps"
                size="small"
                color="amber-darken-2"
                @click.stop="goEdit(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <!-- حذف -->
          <v-tooltip :text="t('حذف نقطه آسیب رسان')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-bind="tooltipProps"
                size="small"
                color="red-darken-2"
                @click.stop="openDeleteDialog(item)"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
        </div>
      </template>
    </FilterableTable>

    <GlobalPagination
      :page="repo.page"
      :items-per-page="repo.itemsPerPage"
      :total-items="repo.totalItems"
      @update:page="handlePageChange"
      @update:items-per-page="handlePerPageChange"
    />

    <!-- 🗑 Delete Confirm Dialog -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />

    <!-- ❗ Error Snackbar -->
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
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

import AppBar from '@/components/AppBar.vue';
import FilterableTable from '@/components/FilterableTable.vue';
import PeekButton from '@/components/PeekButton.vue';
import FlyToMapButton from '@/components/FlyToMapButton.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import PictureCell from '@/components/table/PictureCell.vue';
import GlobalPagination from '@/components/GlobalPagination.vue';

import { useMawadRepository } from '@/stores/MawadRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  usePageRefresh('/app/damagerasanplaces', fetchAll);
const repo = useMawadRepository();
const router = useRouter();

/* i18n / RTL */
const { t, locale } = useI18n();
const isRtl = ref(locale.value === 'fa');
watch(locale, (l) => (isRtl.value = l === 'fa'));

/* Snackbar */
const showError = ref(false);
const errorMessage = ref('');
const showErrorMessage = (m) => {
  errorMessage.value = m;
  showError.value = true;
};

/* 🔗 go to Resources page for this resource_code */
function goToResource(row) {
  if (!row?.resource_code) return;
  router.push(`/app/resources/${row.resource_code}`);
}

/* Helpers */
const getRow = (s) => (s && s.raw ? s.raw : s);

/* rows from store */
const rowsFT = computed(() => {
  const list = repo.DamageRasanPlaces || [];
  if (Array.isArray(list)) return list;
  if (Array.isArray(list.data)) return list.data;
  return [];
});

/* 🔢 Filter cards state */
const cardFilter = ref('all');

const stats = computed(() => {
  const rows = rowsFT.value || [];
  const total = rows.length;
  const withCoordinates = rows.filter(
    (r) => r && r.coordinates && String(r.coordinates).trim() !== ''
  ).length;
  const withThreat = rows.filter(
    (r) => r && r.tahdid_type && String(r.tahdid_type).trim() !== ''
  ).length;
  const withResource = rows.filter(
    (r) => r && (r.resource_code || r.resource_id)
  ).length;

  return { total, withCoordinates, withThreat, withResource };
});

const filteredRowsFT = computed(() => {
  const base = rowsFT.value || [];
  switch (cardFilter.value) {
    case 'withCoordinates':
      return base.filter((r) => r && r.coordinates && String(r.coordinates).trim() !== '');
    case 'withThreat':
      return base.filter((r) => r && r.tahdid_type && String(r.tahdid_type).trim() !== '');
    case 'withResource':
      return base.filter((r) => r && (r.resource_code || r.resource_id));
    default:
      return base;
  }
});

/* TABLE HEADERS – fields + map */
const headersFT = computed(() => [
  { title: t('picture'), key: 'picture' },

  { title: t('ولایت'), key: 'province' },
  { title: t('زون'), key: 'zone' },
  { title: t('ولسوالی'), key: 'district' },
  { title: t('قریه/ناحیه'), key: 'village' },

  { title: t('موقعیت مشخص'), key: 'specific_place' },
  { title: t('نقطه مشخص'), key: 'specific_dot' },
  { title: t('مختصات'), key: 'coordinates' },

  { title: t('نوع تهدید'), key: 'tahdid_type' },
  { title: t('درصدی'), key: 'percentage' },
  { title: t('محل آسیب'), key: 'damage_place' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },

  { title: t('نقشه'), key: 'map', sortable: false },
  { title: t('یادداشت'), key: 'note' },
]);

/* TOP GROUPS */
const topGroups = computed(() => [
  { title: t('موقعیت'), keys: ['province', 'zone', 'district', 'village'] },
  { title: t('جزییات نقطه'), keys: ['specific_place', 'specific_dot', 'coordinates'] },
  { title: t('تهدید/آسیب'), keys: ['tahdid_type', 'percentage', 'damage_place'] },
  { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
  { title: t('دیگر'), keys: ['note', 'Province_addr'] },
]);

const filters = ref({});

/* Peek */
const expanded = ref(false);
const selectedId = ref(null);
const loadingOne = ref(false);
const detail = ref(null);

async function fetchOne(id) {
  loadingOne.value = true;
  try {
    if (typeof repo.FetchDamageRasanPlaceData === 'function') {
      await repo.FetchDamageRasanPlaceData(id);
      const single = Array.isArray(repo.DamageRasanPlace)
        ? repo.DamageRasanPlace[0]
        : repo.DamageRasanPlace;
      detail.value = single || {};
    } else {
      await fetchAll();
      const list = rowsFT.value || [];
      detail.value = list.find((r) => r.id === id) || {};
    }
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت معلومات نقطه آسیب رسان'));
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

/* navigation */
function goCreate() {
  router.push('/app/damage-rasan-placeform');
}

function goEdit(payload) {
  const row = getRow(payload);
  if (!row?.id) return;
  router.push(`/app/damage-rasan-placeform/${row.id}`);
}

/* delete with dialog */
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
  deleteDialog.value = false;
  if (!row?.id) return;
  try {
    await repo.DeleteDamageRasanPlaces?.(row.id);
    await fetchAll();
    showErrorMessage(t('حذف شد'));
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در حذف'));
  } finally {
    deleteTarget.value = null;
  }
}

/* fetch all */
async function fetchAll() {
  try {
    await repo.FetchDamageRasanPlacesData?.();
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت فهرست نقاط آسیب رسان'));
  }
}

function clearSearch() {
  repo.Search = '';
  repo.page = 1;
  fetchAll();
}

function handlePageChange(p) {
  repo.page = p;
  fetchAll();
}

function handlePerPageChange(v) {
  repo.itemsPerPage = v;
  repo.page = 1;
  fetchAll();
}

/* init */
onMounted(async () => {
  await fetchAll();
});
</script>

<style scoped>
.ga-2 {
  gap: 8px;
}

/* Background like other index pages */
.page-bg {
  background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
  font-family: inherit;
}

/* Stats cards */
.stat-card {
  cursor: pointer;
  border-radius: 14px;
  border: 1px solid rgba(33, 150, 243, 0.08);
  background: #ffffff;
  transition: box-shadow 0.2s ease, transform 0.15s ease, border-color 0.2s ease,
    background-color 0.2s ease;
}

.stat-card:hover {
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
  transform: translateY(-1px);
  border-color: rgba(33, 150, 243, 0.35);
}

.stat-card--active {
  border-color: rgba(25, 118, 210, 0.9);
  background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 60%);
}

.stat-card-body {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
}

.stat-card-icon {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e3f2fd;
}

.stat-card-text {
  display: flex;
  flex-direction: column;
}

.stat-card-title {
  font-size: 13px;
  font-weight: 700;
}

.stat-card-subtitle {
  font-size: 11px;
  color: #666;
}
</style>