<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('محبوسین رها شده')" :subtitle="t('فهرست، جستجو و مدیریت اسیر/گرفتارها')">
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
            :search-label="t('جستجو در اسیر/گرفتارها...')"
            :column-label="t('ستون جستجو')"
            :all-columns-label="t('همه ستون‌ها')"
            :value-label="t('ارزش ستون')"
            :exclude-keys="['picture', 'map', 'resource_code', 'resource_phone_number']"
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
        <v-col cols="12" sm="12" md="6" lg="6" class="pl-sm-2">
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

        <!-- Excel Button -->
        <v-col cols="12" sm="4" md="2">
          <v-btn
            color="success"
            variant="flat"
            class="text-none"
            rounded="lg"
            size="large"
            block
            prepend-icon="mdi-microsoft-excel"
            :loading="excelDownloading"
            :disabled="excelDownloading"
            @click="downloadCaptiveExcel"
          >
            {{ t('اکسل') }}
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

      <!-- 📊 Filter Cards -->
      <v-row class="mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- All -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'all' }"
            elevation="cardFilter === 'all' ? 6 : 1"
            @click="cardFilter = 'all'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-group</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('همه اسیر/گرفتارها') }}</div>
                <div class="stat-card-subtitle">{{ t('مجموع') }}: {{ stats.total }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With documents -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withDocs' }"
            elevation="cardFilter === 'withDocs' ? 6 : 1"
            @click="cardFilter = 'withDocs'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-file-document-multiple</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای اسناد') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withDocs }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With coordinates -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withCoordinates' }"
            elevation="cardFilter === 'withCoordinates' ? 6 : 1"
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

        <!-- With resource code -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withResource' }"
            elevation="cardFilter === 'withResource' ? 6 : 1"
            @click="cardFilter = 'withResource'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-card-account-phone</v-icon>
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
        title-key="name"
        image-key="picture"
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
      <!-- RESOURCE CODE CLICKABLE WITH TOOLTIP -->
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

      <!-- تصویر -->
      <template #cell-picture="{ row }">
        <div v-if="row.picture" class="pic-wrap">
          <v-avatar size="32" class="cursor-pointer" @click.stop="openPreview(row.picture)">
            <v-img :src="normalizePicture(row.picture)" :alt="row.name" cover />
          </v-avatar>
        </div>
        <div v-else class="pic-wrap">
          <v-avatar size="32">
            <v-icon size="20">mdi-account</v-icon>
          </v-avatar>
        </div>
      </template>

      <!-- 🗺 Map cell -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.Province_addr"
          type="captives"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS (مشاهده / ویرایش / دانلود / حذف) -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده اسیر/گرفتار')" location="top">
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
                  ) && repo.DeleteCaptivePicture(item.id).then(() => fetchAll())
                "
              >
                mdi-image-remove
              </v-icon>
            </template>
          </v-tooltip>
          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش اسیر/گرفتار')" location="top">
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

          <!-- دانلود اسناد -->
          <v-tooltip :text="t('دانلود اسناد اسیر/گرفتار')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-bind="tooltipProps"
                size="small"
                color="green-darken-2"
                v-if="auth.can('attachmentDownload.create')"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadDocs(item)"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <!-- حذف -->
          <v-tooltip :text="t('حذف اسیر/گرفتار')" location="top">
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

    <!-- 🖼 IMAGE PREVIEW DIALOG -->
    <v-dialog v-model="previewOpen" max-width="520">
      <v-card>
        <v-toolbar
          density="compact"
          color="primary"
          :title="t('پیش نمایش تصویر')"
          class="text-white"
        />
        <v-card-text class="d-flex justify-center">
          <v-img
            v-if="previewSrc"
            :src="previewSrc"
            :alt="t('پیش نمایش')"
            max-width="480"
            max-height="420"
            cover
          />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn color="primary" variant="flat" @click="previewOpen = false">
            {{ t('بستن') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
            <div class="text-subtitle-1 font-weight-bold">
              {{ t('در حال دانلود اکسل...') }}
            </div>
            <div class="text-body-2 text-primary font-weight-bold">
              {{ excelDownloadProgress }}%
            </div>
          </div>
          <v-progress-linear
            :model-value="excelDownloadProgress"
            color="success"
            height="10"
            rounded
            striped
          />
          <div class="text-caption text-medium-emphasis mt-3">
            {{ t('لطفاً منتظر بمانید') }}
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
  import { useLoginRepository } from '../../stores/LoginRepository';
  const auth = useLoginRepository();
  import { useFasadRepository } from '@/stores/FasadRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import { useRelatedRouteFilters } from '@/composables/useRelatedRouteFilters';
  import { useTablePrintExport } from '@/composables/useTablePrintExport';
  const repo = useFasadRepository();
  const router = useRouter();
  const route = useRoute();
  const { relatedFilters, clearRelationQueries } = useRelatedRouteFilters();
  const selectedPrintColumns = ref([]);
  const excelDownloadDialog = ref(false);
  const excelDownloadProgress = ref(0);
  const excelDownloading = ref(false);
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
    const list = repo.Captives || [];
    if (Array.isArray(list)) return list;
    if (Array.isArray(list.data)) return list.data;
    return [];
  });

  /* 🔢 Filter cards state */
  const cardFilter = ref('all');

  const stats = computed(() => {
    const rows = rowsFT.value || [];
    const total = rows.length;
    const withDocs = rows.filter(
      (r) => Array.isArray(r.documents) && r.documents.length > 0
    ).length;
    const withCoordinates = rows.filter(
      (r) => r && r.coordinates && String(r.coordinates).trim() !== ''
    ).length;
    const withResource = rows.filter(
      (r) => r && r.resource_code && String(r.resource_code).trim() !== ''
    ).length;

    return { total, withDocs, withCoordinates, withResource };
  });

  const filteredRowsFT = computed(() => {
    const base = rowsFT.value || [];
    switch (cardFilter.value) {
      case 'withDocs':
        return base.filter((r) => Array.isArray(r.documents) && r.documents.length > 0);
      case 'withCoordinates':
        return base.filter((r) => r && r.coordinates && String(r.coordinates).trim() !== '');
      case 'withResource':
        return base.filter((r) => r && r.resource_code && String(r.resource_code).trim() !== '');
      default:
        return base;
    }
  });

  /* TABLE HEADERS – ALL INPUTS + map */
  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('coordinates'), key: 'coordinates' },

    { title: t('فرم مشخص'), key: 'specific_form' },
    { title: t('فاصله بانډ'), key: 'band_distince' },
    { title: t('فعالیت فعلی'), key: 'now_activity' },

    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('کود منبع'), key: 'resource_id' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('ملاحظات'), key: 'note' },
  ]);

  /* TOP GROUPS – همه فیلدها گروپ شده */
  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'nick_name', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    { title: t('منزل/موقعیت'), keys: ['specific_place', 'street_number', 'home_number'] },
    { title: t('فرم/بانډ/فعالیت'), keys: ['specific_form', 'band_distince', 'now_activity'] },
    { title: t('تماس'), keys: ['phone_number'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['note', 'Province_addr', 'coordinates'] },
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
      if (typeof repo.FetchCaptiveData === 'function') {
        await repo.FetchCaptiveData(id);
        const single = Array.isArray(repo.Captive) ? repo.Captive[0] : repo.Captive;
        detail.value = single || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات اسیر/گرفتار'));
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
    router.push('/app/captiveform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/captiveform/${row.id}`);
  }

  /* documents */
  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  async function downloadDocs(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    try {
      const base = (row.name || 'captive_documents').toLowerCase().replace(/\s+/g, '_');
      await repo.DownloadCaptiveDocuments(row.id, `${base}_documents.zip`);
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دانلود اسناد'));
    }
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
      await repo.DeleteCaptive?.(row.id);
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
      await repo.FetchCaptivesData?.({
        page: repo.page,
        perPage: repo.itemsPerPage,
        search: repo.Search || '',
        search_field: normalizedSearchField.value || route.query.search_field || null,
        search_value: normalizedSearchValue.value || route.query.search_value || null,
        fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
        source: relatedFilters.value.source,
        open: relatedFilters.value.open,
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست اسیر/گرفتارها'));
    }
  }

  async function fetchColumnValues(field, q = '') {
    try {
      const normalizedField =
        typeof field === 'string' ? field : field?.key ?? field?.value ?? null;

      if (!normalizedField) return [];

      const res = await repo.FetchCaptiveFilterOptions?.({
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
    cardFilter.value = 'all';
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

  /* IMAGE PREVIEW helpers */
  const previewOpen = ref(false);
  const previewSrc = ref(null);

  function normalizePicture(src) {
    if (!src) return null;
    let clean = String(src).trim();

    if (clean.startsWith('blob:') || clean.startsWith('data:')) return clean;

    clean = clean.replace(/\\\//g, '/');

    if (clean.startsWith('http://') || clean.startsWith('https://')) return clean;
    if (clean.startsWith('/')) return clean;

    return '/' + clean.replace(/^\/+/, '');
  }

  function openPreview(src) {
    const normalized = normalizePicture(src);
    if (!normalized) return;
    previewSrc.value = normalized;
    previewOpen.value = true;
  }

  usePageRefresh('/app/captives', fetchAll);

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
      OpenResourcesPrintView: repo.OpenCaptivesPrintView,
    },

    moduleKey: 'captives',

    getFilters: () => ({
      search: repo.Search || '',
      search_field: normalizedSearchField.value || route.query.search_field || null,
      search_value: normalizedSearchValue.value || route.query.search_value || null,

      resource_id: relatedFilters.value.resource_id || route.query.resource_id || null,
      source: relatedFilters.value.source,
      open: relatedFilters.value.open,
    }),

    getHeaders: () => headersFT.value,

    getSelectedColumnKeys: () => selectedPrintColumns.value,

    getTitle: () => t('چاپ محبوسین رها شده'),

    getPrintLabels: () => printLabels.value,

    getLocale: () => locale.value,

    defaultFileName: 'captives-print',
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
  async function downloadCaptiveExcel() {
    try {
      excelDownloading.value = true;
      excelDownloadDialog.value = true;
      excelDownloadProgress.value = 0;

      await repo.DownloadCaptiveExcel({
        search: repo.Search || '',
        search_field: normalizedSearchField.value || null,
        search_value: normalizedSearchValue.value || null,
        resource_id: relatedFilters.value.resource_id || route.query.resource_id || null,
        onDownloadProgress: (progressEvent) => {
          const total = progressEvent.total || 0;
          const loaded = progressEvent.loaded || 0;
          if (total > 0) {
            excelDownloadProgress.value = Math.min(100, Math.round((loaded * 100) / total));
          } else {
            if (excelDownloadProgress.value < 90) {
              excelDownloadProgress.value += 5;
            }
          }
        },
      });
      excelDownloadProgress.value = 100;
    } catch (e) {
      console.error('downloadCaptiveExcel error', e);
      showErrorMessage(t('خطا'));
    } finally {
      setTimeout(() => {
        excelDownloadDialog.value = false;
        excelDownloading.value = false;
        excelDownloadProgress.value = 0;
      }, 400);
    }
  }

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

  /* picture */
  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .resource-link {
    color: #1976d2;
    font-weight: 600;
    cursor: pointer;
  }
  .resource-link:hover {
    text-decoration: underline;
  }
</style>
