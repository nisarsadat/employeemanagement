<!-- src/views/wakilano/ComplexIndex.vue -->
<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('کمپلکس/کومپلکس')" :subtitle="t('فهرست، جستجو و مدیریت کمپلکس ها')">
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
            :search-label="t('جستجو در کمپلکس ها...')"
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

        <!-- Create + Excel Buttons -->
        <v-col cols="12" sm="12" md="8" lg="8" class="pl-sm-2">
          <div class="d-flex ga-2 align-center">
            <v-btn
              color="primary"
              variant="flat"
              class="text-none flex-grow-1"
              rounded="lg"
              prepend-icon="mdi-plus"
              @click="goCreate"
            >
              {{ t('جدید') }}
            </v-btn>
            <v-btn
              color="success"
              variant="flat"
              class="text-none flex-grow-1"
              rounded="lg"
              prepend-icon="mdi-microsoft-excel"
              :loading="excelDownloading"
              :disabled="excelDownloading"
              @click="downloadComplexExcel"
            >
              {{ t('اکسل') }}
            </v-btn>
          </div>
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

      <!-- 📊 Filter Cards -->
      <v-row class="mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- داخلي نارینه کارمندان -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'enterMale' }"
            @click="cardFilter = cardFilter === 'enterMale' ? null : 'enterMale'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-group</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('داخلي نارینه کارمندان') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.enterMale }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- داخلي ښځینه کارمندان -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'enterFemale' }"
@click="cardFilter = cardFilter === 'enterFemale' ? null : 'enterFemale'"          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-group-outline</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('داخلي ښځینه کارمندان') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.enterFemale }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- خارجی نارینه کارمندان -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'outerMale' }"
@click="cardFilter = cardFilter === 'outerMale' ? null : 'outerMale'"          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-arrow-right</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('خارجی نارینه کارمندان') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.outerMale }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- خارجی ښځینه کارمندان -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'outerFemale' }"
@click="cardFilter = cardFilter === 'outerFemale' ? null : 'outerFemale'"          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-arrow-right-outline</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('خارجی ښځینه کارمندان') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.outerFemale }}</div>
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
        title-key="complex_name"
        :fields="headersFT"
        :cols="3"
        :auto-detect-dates="false"
        image-key="picture"
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
      <template #cell-picture="{ row }">
        <PictureCell
          :picture="row.picture"
          :alt="row.name"
          :title="t('پیش نمایش تصویر')"
          :close-text="t('بستن')"
        />
      </template>

      <!-- 🔗 RESOURCE CODE CLICKABLE WITH TOOLTIP -->
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

      <!-- 🗺 MAP CELL -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.complex_name"
          :subtitle="row.Province_addr"
          type="complexes"
          :photo-url="null"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده کمپلکس')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>
          <v-tooltip :text="t('حذف عکس')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-bind="tooltipProps"
                size="small"
                color="deep-orange-darken-2"
                :disabled="!item.picture"
                @click.stop="
                  $event.view.confirm(
                    t('آیا مطمئن هستید که می‌خواهید عکس این ریکارډ را حذف کنید؟')
                  ) && repo.DeleteComplexPicture(item.id).then(() => fetchAll())
                "
              >
                mdi-image-remove
              </v-icon>
            </template>
          </v-tooltip>
          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش کمپلکس')" location="top">
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
          <v-tooltip :text="t('حذف کمپلکس')" location="top">
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

    <!-- Excel Download Dialog -->
    <v-dialog v-model="excelDownloadDialog" max-width="360" persistent>
      <v-card rounded="xl">
        <v-card-text class="pa-5">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-1 font-weight-bold">{{ t('در حال دانلود اکسل...') }}</div>
            <div class="text-body-2 text-primary font-weight-bold">{{ excelDownloadProgress }}%</div>
          </div>
          <v-progress-linear :model-value="excelDownloadProgress" color="success" height="10" rounded striped />
          <div class="text-caption text-medium-emphasis mt-3">{{ t('لطفاً منتظر بمانید') }}</div>
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
  import PictureCell from '@/components/table/PictureCell.vue';

  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';
  import SearchColumnFilter from '@/components/SearchColumnFilter.vue';

  import { useWakilanoRepository } from '@/stores/WakilanoRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import { useRelatedRouteFilters } from '@/composables/useRelatedRouteFilters';
  import { useTablePrintExport } from '@/composables/useTablePrintExport';
  const repo = useWakilanoRepository();
  const router = useRouter();
  const route = useRoute();
  const { relatedFilters, clearRelationQueries } = useRelatedRouteFilters();

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const searchField = ref(null);
  const selectedColumnValue = ref(null);
  const selectedPrintColumns = ref([]);
  const excelDownloadDialog = ref(false);
  const excelDownloadProgress = ref(0);
  const excelDownloading = ref(false);
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
    const list = repo.Complexes || [];
    if (Array.isArray(list)) return list;
    if (Array.isArray(list.data)) return list.data;
    return [];
  });

  /* 🔢 Filter cards state */
  const cardFilter = ref(null);
  const stats = computed(() => {
    const rows = rowsFT.value || [];
    const enterMale = rows
      .map((r) => Number(r?.enter_male_quantity || 0))
      .filter((n) => !Number.isNaN(n))
      .reduce((a, b) => a + b, 0);

    const enterFemale = rows
      .map((r) => Number(r?.enter_femal_quantity || 0))
      .filter((n) => !Number.isNaN(n))
      .reduce((a, b) => a + b, 0);

    const outerMale = rows
      .map((r) => Number(r?.outer_male_quantity || 0))
      .filter((n) => !Number.isNaN(n))
      .reduce((a, b) => a + b, 0);

    const outerFemale = rows
      .map((r) => Number(r?.outer_femal_quantity || 0))
      .filter((n) => !Number.isNaN(n))
      .reduce((a, b) => a + b, 0);

    return { enterMale, enterFemale, outerMale, outerFemale };
  });

  const filteredRowsFT = computed(() => {
    const base = rowsFT.value || [];
    switch (cardFilter.value) {
      case 'enterMale':
        return base.filter(
          (r) =>
            r &&
            r.enter_male_quantity &&
            String(r.enter_male_quantity).trim() !== '' &&
            Number(r.enter_male_quantity) > 0
        );
      case 'enterFemale':
        return base.filter(
          (r) =>
            r &&
            r.enter_femal_quantity &&
            String(r.enter_femal_quantity).trim() !== '' &&
            Number(r.enter_femal_quantity) > 0
        );
      case 'outerMale':
        return base.filter(
          (r) =>
            r &&
            r.outer_male_quantity &&
            String(r.outer_male_quantity).trim() !== '' &&
            Number(r.outer_male_quantity) > 0
        );
      case 'outerFemale':
        return base.filter(
          (r) =>
            r &&
            r.outer_femal_quantity &&
            String(r.outer_femal_quantity).trim() !== '' &&
            Number(r.outer_femal_quantity) > 0
        );
      default:
        return base;
    }
  });

  /* TABLE HEADERS – ALL INPUTS + map */
  const headersFT = computed(() => [
    { title: t('picture'), key: 'picture' },

    { title: t('نام کمپلکس'), key: 'complex_name' },
    { title: t('نوع فعالیت'), key: 'activity_type' },
    { title: t('محل فعالیت'), key: 'activity_place' },
    { title: t('تمویل‌کننده'), key: 'supplier' },

    { title: t('ورود ذکور'), key: 'enter_male_quantity' },
    { title: t('خروج ذکور'), key: 'outer_male_quantity' },
    { title: t('ورود اناث'), key: 'enter_femal_quantity' },
    { title: t('خروج اناث'), key: 'outer_femal_quantity' },

    { title: t('مسؤول'), key: 'responsible_name' },
    { title: t('نام پدر مسؤول'), key: 'responsible_f_name' },
    { title: t('مسول موقف'), key: 'responsible_job' },
    { title: t('کشور مسؤول'), key: 'responsible_country' },
    { title: t('شماره مسؤول'), key: 'responsible_phone' },

    { title: t('ولایت'), key: 'province' },
    { title: t('آمریت'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specefic_place' },
    { title: t('نمبر سرک'), key: 'street_number' },
    { title: t('نمبر خانه'), key: 'home_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('تعداد محافظ'), key: 'guard_quantity' },

    { title: t('کود منبع'), key: 'resource_id' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('یادداشت'), key: 'note' },
  ]);

  /* TOP GROUPS */
  const topGroups = computed(() => [
    {
      title: t('مشخصات کمپلکس'),
      keys: ['complex_name', 'activity_type', 'activity_place', 'supplier'],
    },
    {
      title: t('ورود/خروج'),
      keys: [
        'enter_male_quantity',
        'outer_male_quantity',
        'enter_femal_quantity',
        'outer_femal_quantity',
      ],
    },
    {
      title: t('مسؤول'),
      keys: ['responsible_name', 'responsible_f_name', 'responsible_phone', 'responsible_country'],
    },
    {
      title: t('آدرس'),
      keys: [
        'province',
        'zone',
        'district',
        'village',
        'specefic_place',
        'street_number',
        'home_number',
        'coordinates',
      ],
    },
    { title: t('امنیت'), keys: ['guard_quantity'] },
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
      if (typeof repo.FetchComplexData === 'function') {
        await repo.FetchComplexData(id);
        const single = Array.isArray(repo.Complex) ? repo.Complex[0] : repo.Complex;
        detail.value = single || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات کمپلکس'));
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
    router.push('/app/complexform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/complexform/${row.id}`);
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
      await repo.DeleteComplex?.(row.id);
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
      await repo.FetchComplexesData?.({
        search: repo.Search,
        search_field: normalizedSearchField.value || route.query.search_field || null,
        search_value: normalizedSearchValue.value || route.query.search_value || null,
        fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
        source: relatedFilters.value.source,
        open: relatedFilters.value.open,
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست کمپلکس ها'));
    }
  }

  async function fetchColumnValues(field, q = '') {
    try {
      const normalizedField =
        typeof field === 'string' ? field : field?.key ?? field?.value ?? null;

      if (!normalizedField) return [];

      const res = await repo.FetchComplexFilterOptions?.({
        field: normalizedField,
        q,
      });

      return res?.data?.options || [];
    } catch (e) {
      console.error('fetchColumnValues error', e);
      return [];
    }
  }

  async function handleSearch() {
    repo.page = 1;
    await fetchAll();
  }

  async function clearSearch() {
    repo.Search = '';
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
    cardFilter.value = null;
    filters.value = {};
    repo.page = 1;

    expanded.value = false;
    selectedId.value = null;
    detail.value = null;

    await clearRelationQueries();
    await fetchAll();
  }

  function handlePageChange(page) {
    repo.page = page;
    fetchAll();
  }

  function handlePerPageChange(perPage) {
    repo.itemsPerPage = perPage;
    repo.page = 1;
    fetchAll();
  }

  usePageRefresh('/app/complexes', fetchAll);

  watch(
    () => [relatedFilters.value.fifty_six_format_id, relatedFilters.value.open],
    async ([id, open]) => {
      if (!id) return;

      await fetchAll();

      if (open === 'peek') {
        openPeek({ id });
      }
    },
    { immediate: true }
  );

  watch(
    () => route.query,
    async () => {
      repo.page = 1;
      await fetchAll();
    },
    { deep: true }
  );

  /* =========== COMPLEX EXCEL DOWNLOAD =========== */
  async function downloadComplexExcel() {
    try {
      excelDownloading.value = true; excelDownloadDialog.value = true; excelDownloadProgress.value = 0;
      await repo.DownloadComplexExcel({
        search: repo.Search || '', search_field: normalizedSearchField.value || null,
        search_value: normalizedSearchValue.value || null, card_filter: cardFilter.value || 'all', resource_id: null,
        onDownloadProgress: (p) => { const t = p.total || 0; const l = p.loaded || 0; excelDownloadProgress.value = t > 0 ? Math.min(100, Math.round((l * 100) / t)) : (excelDownloadProgress.value < 90 ? excelDownloadProgress.value + 5 : excelDownloadProgress.value); },
      });
      excelDownloadProgress.value = 100;
    } catch (e) { console.error('downloadComplexExcel error', e); showErrorMessage(t('خطا در دانلود اکسل'));
    } finally { setTimeout(() => { excelDownloadDialog.value = false; excelDownloading.value = false; excelDownloadProgress.value = 0; }, 400); }
  }

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
      OpenResourcesPrintView: repo.OpenComplexesPrintView,
    },

    moduleKey: 'complexes',

    getFilters: () => ({
      search: repo.Search || '',
      search_field: normalizedSearchField.value || route.query.search_field || null,
      search_value: normalizedSearchValue.value || route.query.search_value || null,

      card_filter: cardFilter.value,

      fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
      resource_id: relatedFilters.value.resource_id,
      source: relatedFilters.value.source,
      open: relatedFilters.value.open,
    }),

    getHeaders: () => headersFT.value,

    getSelectedColumnKeys: () => selectedPrintColumns.value,

    getTitle: () => t('چاپ کمپلکس'),

    getPrintLabels: () => printLabels.value,

    getLocale: () => locale.value,

    defaultFileName: 'complexes-print',
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
  /* init */
  onMounted(async () => {
    selectedPrintColumns.value = buildDefaultPrintColumns();

    if (!relatedFilters.value.fifty_six_format_id) {
      await fetchAll();
    }
  });
</script>

<style scoped>
  .ga-2 {
    gap: 8px;
  }

  /* Background similar to other index pages */
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
