<!-- src/views/fifthyFormats/Luchakano.vue -->
<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('لچکانو')" :subtitle="t('فهرست، جستجو و مدیریت لچکانو')">
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <!-- 🔍 Search + Filter + Reset + Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Column -->
        <v-col cols="12" sm="12" md="12" lg="12">
          <SearchColumnFilter
            v-model:modelSearch="repo.Search"
            v-model:modelColumn="searchField"
            v-model:modelSelectedValue="selectedColumnValue"
            :headers="headersFT"
            :search-label="t('جستجو در لچکانو...')"
            :column-label="t('ستون جستجو')"
            :all-columns-label="t('همه ستون‌ها')"
            :value-label="t('ارزش ستون')"
            :exclude-keys="['picture', 'map', 'resource_code', 'resource_phone']"
            :fetch-column-values="fetchColumnValues"
            @search="handleSearch"
            @clearSearch="clearSearch"
            @clearSelectedValue="clearSelectedValue"
          />
        </v-col>

        <!-- Apply filter -->
        <v-col cols="12" sm="6" md="2" lg="2">
          <v-btn
            color="primary"
            variant="flat"
            rounded="lg"
            block
            class="text-none"
            prepend-icon="mdi-filter"
            @click="handleSearch"
          >
            {{ t('فلتر') }}
          </v-btn>
        </v-col>

        <!-- Reset filter -->
        <v-col cols="12" sm="6" md="2" lg="2">
          <v-btn
            color="orange"
            rounded="lg"
            block
            class="text-none"
            prepend-icon="mdi-filter-remove"
            @click="clearAllFilters"
          >
            {{ t('ریست فلتر') }}
          </v-btn>
        </v-col>

        <!-- Create Button -->
        <v-col cols="12" sm="12" md="8" lg="8" class="pl-sm-2">
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
        <v-col cols="12" sm="10">
          <v-select
            v-model="selectedPrintColumns"
            :items="printableHeaderOptions"
            item-title="title"
            item-value="key"
            multiple
            density="comfortable"
            variant="outlined"
            rounded="lg"
            class="bg-white"
            hide-details
            :label="t('ستون‌های چاپ')"
            :menu-props="{ maxHeight: 400 }"
          >
            <template #selection="{ index }">
              <span v-if="index === 0" class="text-body-2">
                {{ selectedPrintColumns.length }}
                {{ t('ستون انتخاب شده') }}
              </span>
            </template>
          </v-select>
        </v-col>

        <v-col cols="12" sm="2">
          <v-btn
            color="error"
            variant="flat"
            rounded="lg"
            size="large"
            block
            class="text-none"
            prepend-icon="mdi-printer"
            @click="printAll"
          >
            {{ t('چاپ') }}
          </v-btn>
        </v-col>
      </v-row>

      <!-- ✅ Filter Cards -->
      <v-row class="mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'all' }"
            @click="cardFilter = 'all'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon"><v-icon>mdi-account-group</v-icon></div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('همه') }}</div>
                <div class="stat-card-subtitle">{{ t('مجموع') }}: {{ stats.total }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withCoordinates' }"
            @click="cardFilter = 'withCoordinates'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon"><v-icon>mdi-map-marker-radius</v-icon></div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای مختصات') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withCoordinates }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withPhone' }"
            @click="cardFilter = 'withPhone'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon"><v-icon>mdi-phone</v-icon></div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای شماره تماس') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withPhone }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withResource' }"
            @click="cardFilter = 'withResource'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon"><v-icon>mdi-card-account-phone</v-icon></div>
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
        :item="detailForPeek"
        :loading="loadingOne"
        image-key="picture"
        title-key="name"
        :fields="headersFT"
        :cols="3"
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
      class="mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >
      <!-- 🖼 PICTURE CELL -->
      <template #cell-picture="{ row }">
        <div class="pic-cell">
          <v-avatar
            v-if="row.picture_url"
            size="34"
            class="pic-thumb"
            @click.stop="openImage(row.picture_url, row.name)"
          >
            <v-img :src="row.picture_url" cover />
          </v-avatar>

          <v-avatar v-else size="34" class="pic-thumb pic-thumb--empty">
            <v-icon size="18">mdi-image-off</v-icon>
          </v-avatar>
        </div>
      </template>

      <!-- 🔗 RESOURCE CODE clickable -->
      <template #cell-resource_id="{ row }">
        <v-tooltip :text="t('رفتن به صفحه منابع')" location="top">
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              v-if="row.resource_id"
              variant="tonal"
              color="primary"
              append-icon="mdi-account"
              size="default"
              @click.stop="goToResource(row)"
            >
              {{ row.resource_id }}
            </v-btn>
            <span v-else>-</span>
          </template>
        </v-tooltip>
      </template>

      <!-- 🗺 Map cell -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.nickname"
          type="mawads"
          :photo-url="getPhotoUrl(row)"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده لچکانو')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش لچکانو')" location="top">
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
          <v-tooltip :text="t('حذف لچکانو')" location="top">
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

    <!-- 🖼 IMAGE PREVIEW DIALOG -->
    <v-dialog v-model="imgDialog" max-width="920">
      <v-card rounded="xl">
        <v-card-title class="d-flex align-center justify-space-between">
          <div class="d-flex align-center ga-2">
            <v-icon>mdi-image</v-icon>
            <span class="text-subtitle-1 font-weight-bold">
              {{ imgTitle || t('پیش‌نمایش عکس') }}
            </span>
          </div>

          <v-btn icon variant="text" @click="imgDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider />

        <v-card-text>
          <div class="img-preview-wrap">
            <v-img v-if="imgSrc" :src="imgSrc" class="img-preview" cover />
            <div v-else class="muted">{{ t('عکس موجود نیست') }}</div>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>

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
  import { useRouter, useRoute } from 'vue-router';

  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';
  import SearchColumnFilter from '@/components/SearchColumnFilter.vue';
  import { useTablePrintExport } from '@/composables/useTablePrintExport';
  import { useMawadRepository } from '@/stores/MawadRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  const repo = useMawadRepository();
  const router = useRouter();
  const route = useRoute();
  const selectedPrintColumns = ref([]);
  /* i18n / RTL */
  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const searchField = ref(null);
  const selectedColumnValue = ref(null);

  /* Snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (m) => {
    errorMessage.value = m;
    showError.value = true;
  };

  /* Small helper for row/raw */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  const normalizedSearchField = computed(() => {
    const v = searchField.value;
    if (!v) return null;
    if (typeof v === 'string') return v;
    return v.key ?? v.value ?? null;
  });

  const normalizedSearchValue = computed(() => {
    const v = selectedColumnValue.value;
    if (v === null || v === undefined || v === '') return null;
    if (typeof v === 'string' || typeof v === 'number') return v;
    return v.value ?? v.title ?? null;
  });

  /* rows from store */
  const rowsFT = computed(() => {
    const list = repo.Luchakanos || [];
    if (Array.isArray(list)) return list;
    if (Array.isArray(list.data)) return list.data;
    return [];
  });

  /* TABLE HEADERS (same as before, plus map) */
  const headersFT = computed(() => [
    { title: t('عکس'), key: 'picture', sortable: false },
    { title: t('تخلص'), key: 'nickname' },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک فعلی'), key: 'now_street_number' },
    { title: t('شماره خانه فعلی'), key: 'now_home_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'job' },
    { title: t('نوع اسلحه'), key: 'gun_type' },
    { title: t('تمویل کننده'), key: 'supplier' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('سابقه جرم'), key: 'history_jorm' },
    { title: t('نوع تخلف'), key: 'takhlof_type' },

    { title: t('کود منبع'), key: 'resource_id' },
    { title: t('شماره منبع'), key: 'resource_phone' },
    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('یادداشت'), key: 'note' },
  ]);

  /* TOP GROUPS (same idea as before) */
  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'nickname', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    {
      title: t('موقعیت فعلی'),
      keys: ['now_specific_place', 'now_street_number', 'now_home_number'],
    },
    { title: t('وظیفه/اسلحه/تماس'), keys: ['old_job', 'job', 'gun_type', 'phone_number'] },
    { title: t('سوابق/یادداشت'), keys: ['history_jorm', 'note'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
  ]);

  const imgDialog = ref(false);
  const imgSrc = ref('');
  const imgTitle = ref('');

  function openImage(src, title) {
    imgSrc.value = src || '';
    imgTitle.value = title || '';
    imgDialog.value = true;
  }

  const filters = ref({});

  /* Peek */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loadingOne.value = true;
    try {
      await repo.FetchLuchakanoData?.(id);
      detail.value =
        repo.Luchakano && Array.isArray(repo.Luchakano) ? repo.Luchakano[0] : repo.Luchakano || {};
    } catch (e) {
      console.error('fetchOne error', e);
      showErrorMessage(t('خطا در دریافت معلومات لچکانو'));
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

  /* Navigation */
  function goCreate() {
    router.push('/app/luchakanoform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/luchakanoform/${row.id}`);
  }

  /* Go to Resource page when clicking resource_code */
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

  /* Delete with dialog */
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
      await repo.DeleteLuchakano?.(row.id);
      await fetchAll();
      showErrorMessage(t('حذف شد'));
    } catch (e) {
      console.error('delete error', e);
      showErrorMessage(t('خطا در حذف'));
    } finally {
      deleteTarget.value = null;
    }
  }

  const cardFilter = ref('all');

  const stats = computed(() => {
    const rows = rowsFT.value || [];
    const total = rows.length;

    const withCoordinates = rows.filter(
      (r) => r?.coordinates && String(r.coordinates).trim() !== ''
    ).length;
    const withPhone = rows.filter(
      (r) => r?.phone_number && String(r.phone_number).trim() !== ''
    ).length;
    const withResource = rows.filter(
      (r) => r?.resource_code && String(r.resource_code).trim() !== ''
    ).length;

    return { total, withCoordinates, withPhone, withResource };
  });

  const filteredRowsFT = computed(() => {
    const base = rowsFT.value || [];

    if (cardFilter.value === 'withCoordinates')
      return base.filter((r) => r?.coordinates && String(r.coordinates).trim() !== '');
    if (cardFilter.value === 'withPhone')
      return base.filter((r) => r?.phone_number && String(r.phone_number).trim() !== '');
    if (cardFilter.value === 'withResource')
      return base.filter((r) => r?.resource_code && String(r.resource_code).trim() !== '');

    return base;
  });

  /* Fetch ALL */
  async function fetchAll() {
    try {
      await repo.FetchLuchakanosData?.({
        search: repo.Search,
        search_field: normalizedSearchField.value || route.query.search_field || null,
        search_value: normalizedSearchValue.value || route.query.search_value || null,
        ...route.query,
      });
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت فهرست لچکانو'));
    }
  }

  async function fetchColumnValues(field, q = '') {
    try {
      const normalizedField =
        typeof field === 'string' ? field : field?.key ?? field?.value ?? null;

      if (!normalizedField) return [];

      const res = await repo.FetchLuchakanoFilterOptions?.({
        field: normalizedField,
        q,
      });

      return res?.data?.options || [];
    } catch (e) {
      console.error('fetchColumnValues error', e);
      return [];
    }
  }

  async function clearSearch() {
    repo.Search = '';
    repo.page = 1;
    await fetchAll();
  }

  async function handleSearch() {
    repo.page = 1;
    await fetchAll();
  }

  async function clearSelectedValue() {
    selectedColumnValue.value = null;
    repo.page = 1;
    await fetchAll();
  }

  async function clearAllFilters() {
    repo.Search = '';
    searchField.value = null;
    selectedColumnValue.value = null;
    cardFilter.value = 'all';
    filters.value = {};
    repo.page = 1;

    expanded.value = false;
    selectedId.value = null;
    detail.value = null;

    if (Object.keys(route.query || {}).length) {
      await router.replace({
        path: route.path,
        query: {},
      });
    }

    await fetchAll();
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

  usePageRefresh('/app/luchakanos', fetchAll);

  watch(
    () => route.query,
    async () => {
      repo.page = 1;
      await fetchAll();
    },
    { deep: true }
  );
  const printLabels = computed(() => ({
    print: t('چاپ'),
    record_count: t('تعداد ریکارد'),
    index: t('شماره'),
    no_data: t('معلومات برای چاپ موجود نیست'),
    footer_note: '',
  }));

  const printableHeaderOptions = computed(() => {
    return headersFT.value.filter((h) => !['map'].includes(h.key));
  });

  function buildDefaultPrintColumns() {
    return printableHeaderOptions.value.map((h) => h.key);
  }

  const { printAll } = useTablePrintExport({
    repo: {
      OpenResourcesPrintView: repo.OpenLuchakanosPrintView,
    },

    moduleKey: 'luchakanos',

    getFilters: () => ({
      ...route.query,

      search: repo.Search || '',
      search_field: normalizedSearchField.value || route.query.search_field || null,
      search_value: normalizedSearchValue.value || route.query.search_value || null,

      resource_id: route.query.resource_id || null,
      source: route.query.source || null,
      open: route.query.open || null,

      card_filter: cardFilter.value || 'all',
    }),

    getHeaders: () => headersFT.value,

    getSelectedColumnKeys: () => selectedPrintColumns.value,

    getTitle: () => t('چاپ لچکانو'),

    getPrintLabels: () => printLabels.value,

    getLocale: () => locale.value,

    defaultFileName: 'luchakanos-print',
  });
  function normalizeUrl(url) {
    if (!url) return null;
    return String(url).replace('/storage/storage/', '/storage/');
  }

  function getPhotoUrl(payload) {
    const row = getRow(payload);
    if (!row) return null;

    return normalizeUrl(row.picture_url || row.picture);
  }

  const detailForPeek = computed(() => {
    if (!detail.value) return null;

    return {
      ...detail.value,
      picture: getPhotoUrl(detail.value),
    };
  });
  watch(
    printableHeaderOptions,
    (newColumns) => {
      if (!selectedPrintColumns.value.length) {
        selectedPrintColumns.value = newColumns.map((c) => c.key);
      }
    },
    { immediate: true }
  );
  /* INIT */
  onMounted(async () => {
    selectedPrintColumns.value = buildDefaultPrintColumns();
    await fetchAll();
  });
</script>

<style scoped>
  .page-bg {
    background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
    font-family: inherit;
  }

  .ga-2 {
    gap: 8px;
  }
  .stat-card {
    cursor: pointer;
    border-radius: 14px;
    border: 1px solid rgba(33, 150, 243, 0.08);
    background: #fff;
    transition: 0.2s;
  }
  .stat-card:hover {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    transform: translateY(-1px);
    border-color: rgba(33, 150, 243, 0.35);
  }
  .stat-card--active {
    border-color: rgba(25, 118, 210, 0.9);
    background: linear-gradient(135deg, #e3f2fd 0%, #fff 60%);
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
  .stat-card-title {
    font-size: 13px;
    font-weight: 700;
  }
  .stat-card-subtitle {
    font-size: 11px;
    color: #666;
  }
</style>
