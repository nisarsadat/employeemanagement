<!-- src/views/wakilano/DoneLostPlacesIndex.vue -->
<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="t('محلات گمشده/یافته شده')"
      :subtitle="t('فهرست، جستجو و مدیریت DoneLostPlaces')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
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

      <!-- 📊 Stats cards -->
      <v-row class="mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- All -->
        <v-col cols="12" sm="8" md="4">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'all' }"
            @click="cardFilter = 'all'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-map-marker-radius</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('محلات گمشده/یافته شده') }}</div>
                <div class="stat-card-subtitle">{{ t('مجموع') }}: {{ stats.total }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With coordinates -->
        <v-col cols="12" sm="8" md="4">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withCoordinates' }"
            @click="cardFilter = 'withCoordinates'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-crosshairs-gps</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای مختصات') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withCoordinates }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With person -->
        <!-- <v-col cols="12" sm="8" md="4">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withPerson' }"
            @click="cardFilter = 'withPerson'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-search</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('با شخص گمشده/یافته') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withPerson }}</div>
              </div>
            </div>
          </v-card>
        </v-col> -->

        <!-- With resource -->
        <v-col cols="12" sm="8" md="4">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withResource' }"
            @click="cardFilter = 'withResource'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-tie</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای منبع') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withResource }}</div>
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
        title-key="place_name"
        :fields="headersFT"
        :cols="3"
        image-key="picture"
        dir="rtl"
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
      :dateKeys="[]"
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
      <!-- MAP CELL -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.place_name"
          :subtitle="row.lost_person_name"
          type="done-lost-places"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <!-- ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- View -->
          <v-tooltip :text="t('مشاهده جزییات')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- Edit -->
          <v-tooltip :text="t('ویرایش محل')" location="top">
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

          <!-- Delete -->
          <v-tooltip :text="t('حذف ریکارډ')" location="top">
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
    <v-snackbar v-model="showError" color="error" timeout="4500">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
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
  import GlobalPagination from '@/components/GlobalPagination.vue';
  import { useWakilanoRepository } from '@/stores/WakilanoRepository';
  import PictureCell from '@/components/table/PictureCell.vue';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  usePageRefresh('/app/donelostplaces', fetchAll);
  const repo = useWakilanoRepository();
  const router = useRouter();

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const drawer = ref(false);

  /* Snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (m) => {
    errorMessage.value = m || t('خطا رخ داد');
    showError.value = true;
  };

  /* Helpers */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* Data from store */
  const rowsFT = computed(() => repo.DoneLostPlaces || []);

  /* Card filters */
  const cardFilter = ref('all');

  const stats = computed(() => {
    const rows = rowsFT.value || [];
    const total = rows.length;
    const withCoordinates = rows.filter(
      (r) => r && r.coordinates && String(r.coordinates).trim() !== ''
    ).length;
    const withPerson = rows.filter(
      (r) => r && r.lost_person_name && String(r.lost_person_name).trim() !== ''
    ).length;
    const withResource = rows.filter(
      (r) => r && r.resource_code && String(r.resource_code).trim() !== ''
    ).length;
    return { total, withCoordinates, withPerson, withResource };
  });

  const filteredRowsFT = computed(() => {
    const base = rowsFT.value || [];
    switch (cardFilter.value) {
      case 'withCoordinates':
        return base.filter((r) => r && r.coordinates && String(r.coordinates).trim() !== '');
      case 'withPerson':
        return base.filter((r) => r && r.lost_person_name && String(r.lost_person_name).trim());
      case 'withResource':
        return base.filter((r) => r && r.resource_code && String(r.resource_code).trim());
      default:
        return base;
    }
  });

  /* Headers (list view) */
  const headersFT = computed(() => [
    { title: t('picture'), key: 'picture' },

    { title: t('ساحه که اختطاف صورت ګرفته'), key: 'place_name' },

    { title: t('شخص گمشده/یافته'), key: 'lost_person_name' },
    { title: t('نام پدر شخص'), key: 'lost_person_f_name' },
    { title: t('وظیفه شخص'), key: 'lost_person_job' },
    { title: t('شماره تماس شخص'), key: 'lost_person_phone' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },

    { title: t('ولایت محل'), key: 'province' },
    { title: t('زون محل'), key: 'zone' },
    { title: t('ولسوالی محل'), key: 'district' },
    { title: t('قریه محل'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('تعداد موارد'), key: 'lost_quantity' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('یادداشت'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات محل'), keys: ['place_name', 'coordinates', 'lost_quantity'] },
    {
      title: t('شخص گمشده/یافته'),
      keys: ['lost_person_name', 'lost_person_f_name', 'lost_person_phone', 'lost_person_job'],
    },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
    { title: t('آدرس محل'), keys: ['province', 'zone', 'district', 'village', 'specific_place'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone', 'resource_id'] },
    { title: t('سایر'), keys: ['note', 'Province_addr'] },
  ]);

  const filters = ref({});

  /* Peek */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    if (!id) return;
    loadingOne.value = true;
    try {
      await repo.FetchDoneLostPlaceData?.(id);
      detail.value = repo.DoneLostPlace || {};
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات جزییات'));
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
    router.push('/app/done-lost-places/form');
  }
  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/done-lost-places/form/${row.id}`);
  }

  /* delete */
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
      await repo.DeleteDoneLostPlace?.(row.id);
      await fetchAll();
      showErrorMessage(t('ریکارد حذف شد'));
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
      await repo.FetchDoneLostPlacesData?.();
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست محلات'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    repo.page = 1;
    fetchAll();
  }

  function handlePageChange(page) {
    repo.page = page;
    fetchAll();
  }

  function handlePerPageChange(itemsPerPage) {
    repo.itemsPerPage = itemsPerPage;
    repo.page = 1;
    fetchAll();
  }

function goToResource(row) {
  const resourceId = row?.resource_id ?? row?.resource?.id ?? null;
  if (!resourceId) return;

  router.push({
    path: '/app/resources',
    query: {
      resource_id: resourceId,
      source: 'related',
    },
  });
}

  onMounted(async () => {
    await Promise.allSettled([repo.GetResourceCode?.(), fetchAll()]);
  });
</script>

<style scoped>
  .page-bg {
    background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
  }

  .ga-2 {
    gap: 8px;
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