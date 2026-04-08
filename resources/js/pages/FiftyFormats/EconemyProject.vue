<!-- src/views/econemy/EconemyProjects.vue -->
<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="t('پروژه اقتصادی')"
      :subtitle="t('فهرست، جستجو و مدیریت پروژه‌های اقتصادی')"
      @toggle-drawer="drawer = !drawer"
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

      <!-- 📊 Filter Cards (like Heist) -->
      <v-row class="mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- All -->
        <v-col cols="12" sm="6" md="4">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'all' }"
            @click="cardFilter = 'all'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-clipboard-text</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('تمام پروژه‌ها') }}</div>
                <div class="stat-card-subtitle">{{ t('مجموع') }}: {{ stats.total }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With coordinates -->
        <v-col cols="12" sm="6" md="2">
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
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withCoordinates }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With responsible phone -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withRespPhone' }"
            @click="cardFilter = 'withRespPhone'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-phone</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای شماره مسؤول') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withRespPhone }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With guards -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withGuards' }"
            @click="cardFilter = 'withGuards'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-shield-account</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای محافظ') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withGuards }}</div>
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
        title-key="name"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        image-key="picture"
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
    > <template #cell-picture="{ row }">
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
          :title="row.name"
          :subtitle="row.responsiable_name || row.resource_code"
          type="econemy-projects"
          :photo-url="null"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده پروژه')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش پروژه')" location="top">
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
          <v-tooltip :text="t('حذف پروژه')" location="top">
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
        <v-btn variant="text" @click="showError = false"> {{ t('بستن') }} </v-btn>
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

  const repo = useWakilanoRepository();
  const router = useRouter();

  /* i18n / RTL */
  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));
  const drawer = ref(false);

  /* Snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (m) => {
    errorMessage.value = m;
    showError.value = true;
  };

  /* 🔗 go to Resources page for this resource_code */
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

  /* Helpers */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* rows from store */
  const rowsFT = computed(() => {
    const list = repo.EconemyProjects || [];
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

    const withRespPhone = rows.filter(
      (r) => r && r.responsible_phone && String(r.responsible_phone).trim() !== ''
    ).length;

    const withGuards = rows.filter(
      (r) =>
        r &&
        r.gaurd_quantity !== null &&
        r.gaurd_quantity !== undefined &&
        String(r.gaurd_quantity).trim() !== '' &&
        Number(r.gaurd_quantity) > 0
    ).length;

    return { total, withCoordinates, withRespPhone, withGuards };
  });

  const filteredRowsFT = computed(() => {
    const base = rowsFT.value || [];
    switch (cardFilter.value) {
      case 'withCoordinates':
        return base.filter((r) => r && r.coordinates && String(r.coordinates).trim() !== '');
      case 'withRespPhone':
        return base.filter(
          (r) => r && r.responsible_phone && String(r.responsible_phone).trim() !== ''
        );
      case 'withGuards':
        return base.filter(
          (r) =>
            r &&
            r.gaurd_quantity !== null &&
            r.gaurd_quantity !== undefined &&
            String(r.gaurd_quantity).trim() !== '' &&
            Number(r.gaurd_quantity) > 0
        );
      default:
        return base;
    }
  });

  /* TABLE HEADERS */
  const headersFT = computed(() => [
  { title: t('picture'), key: 'picture' },

    { title: t('نام پروژه'), key: 'name' },
    { title: t('نوع'), key: 'type' },

    { title: t('مسؤول'), key: 'responsiable_name' },
    { title: t('نام پدر مسؤول'), key: 'responsiable_f_name' },
    { title: t('وظیفه/موقف مسؤول'), key: 'responsible_possition' },
    { title: t('شماره تماس مسؤول'), key: 'responsible_phone' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specefic_place' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('حالت امنیتی/خلا'), key: 'amnyatee_khala' },
    { title: t('تعداد کارمندان'), key: 'employee_quantity' },
    { title: t('تعداد محافظ'), key: 'gaurd_quantity' },


    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('یادداشت'), key: 'note' },
  ]);

  /* TOP GROUPS */
  const topGroups = computed(() => [
    { title: t('مشخصات پروژه'), keys: ['name', 'type'] },
    {
      title: t('مسؤول پروژه'),
      keys: [
        'responsiable_name',
        'responsiable_f_name',
        'responsible_possition',
        'responsible_phone',
      ],
    },
    { title: t('آدرس پروژه'), keys: ['province', 'zone', 'district', 'village', 'specefic_place'] },
    { title: t('وضعیت/آمار'), keys: ['amnyatee_khala', 'employee_quantity', 'gaurd_quantity'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['Province_addr', 'coordinates', 'note'] },
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
      if (typeof repo.FetchEconemyProjectData === 'function') {
        await repo.FetchEconemyProjectData(id);
        const single = Array.isArray(repo.EconemyProject)
          ? repo.EconemyProject[0]
          : repo.EconemyProject;
        detail.value = single || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات پروژه'));
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
    router.push('/app/econemyprojectform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/econemyprojectform/${row.id}`);
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
      await repo.DeleteEconemyProject?.(row.id);
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
      await repo.FetchEconemyProjectsData?.();
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست پروژه‌ها'));
    }
  }

  function handlePageChange(p) {
    repo.page = p;
    fetchAll();
  }

  function handlePerPageChange(n) {
    repo.itemsPerPage = n;
    repo.page = 1;
    fetchAll();
  }

  function clearSearch() {
    repo.Search = '';
    repo.page = 1;
    fetchAll();
  }
  usePageRefresh('/app/econemyprojects', fetchAll);

  /* init */
  onMounted(async () => {
    await fetchAll();
  });
</script>

<style scoped>
  .ga-2 {
    gap: 8px;
  }

  /* Background (same vibe as Heist) */
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
    justify-content-center: center;
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