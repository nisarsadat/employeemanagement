<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('فسادها')" :subtitle="t('فهرست، جستجو و مدیریت فسادها')">
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <!-- Search + Filter + Reset + Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <v-col cols="12" sm="12" md="12" lg="12">
          <SearchColumnFilter
            v-model:modelSearch="repo.Search"
            v-model:modelColumn="searchField"
            v-model:modelSelectedValue="selectedColumnValue"
            :headers="headersFT"
            :search-label="t('جستجو در فسادها...')"
            :column-label="t('ستون جستجو')"
            :all-columns-label="t('همه ستون‌ها')"
            :value-label="t('ارزش ستون')"
            :exclude-keys="['picture', 'map', 'resource_phone']"
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
            {{ t('اضافه ریکارډ جدید فساد') }}
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

      <!-- Filter Cards -->
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
                <div class="stat-card-title">{{ t('شر او فساد') }}</div>
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

    <!-- Peek -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loadingOne"
        title-key="name"
        :fields="headersFT"
        :cols="3"
        image-key="picture"
        :auto-detect-dates="false"
        :date-keys="[]"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- TABLE -->
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
      class="mb-6 mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >
      <template #cell-picture="{ row }">
        <v-avatar
          size="34"
          rounded="lg"
          class="elevation-1 cursor-pointer"
          @click.stop="openPicture(row)"
        >
          <v-img :src="row.picture" cover />
        </v-avatar>
      </template>

      <!-- 🔗 RESOURCE CODE CLICKABLE -->
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

      <!-- 🗺 map -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.Province_addr"
          type="fasads"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip :text="t('مشاهده فساد')" location="top">
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
                  ) && repo.DeleteFasadPicture(item.id).then(() => fetchAll())
                "
              >
                mdi-image-remove
              </v-icon>
            </template>
          </v-tooltip>
          <v-tooltip :text="t('ویرایش فساد')" location="top">
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

          <v-tooltip :text="t('دانلود اسناد مربوط به فساد')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-bind="tooltipProps"
                size="small"
                color="green-darken-2"
                v-if="auth.can('attachmentDownload.create')"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadDocuments(item)"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('حذف فساد')" location="top">
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
      class=""
      :page="repo.page"
      :items-per-page="repo.itemsPerPage"
      :total-items="repo.totalItems"
      @update:page="handlePageChange"
      @update:items-per-page="handlePerPageChange"
    />

    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />

    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>

  <!-- ✅ Picture Preview Dialog -->
  <v-dialog v-model="pictureDialog" max-width="900">
    <v-card rounded="lg">
      <v-card-title class="d-flex align-center justify-space-between">
        <span>{{ pictureTitle }}</span>
        <v-btn icon variant="text" @click="pictureDialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-divider />

      <v-card-text class="pa-2">
        <v-img :src="pictureSrc" max-height="700" cover class="rounded-lg" />
      </v-card-text>

      <v-divider />

      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="pictureDialog = false">
          {{ t('بستن') }}
        </v-btn>

        <v-btn
          v-if="pictureSrc"
          color="primary"
          variant="flat"
          prepend-icon="mdi-open-in-new"
          :href="pictureSrc"
          target="_blank"
        >
          {{ t('باز کردن در تب جدید') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRouter, useRoute } from 'vue-router';
  import { useRelatedRouteFilters } from '@/composables/useRelatedRouteFilters';

  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';
  import SearchColumnFilter from '@/components/SearchColumnFilter.vue';
  import { useFasadRepository } from '@/stores/FasadRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import { useTablePrintExport } from '@/composables/useTablePrintExport';
  const auth = useLoginRepository();
  const repo = useFasadRepository();
  const router = useRouter();
  const route = useRoute();
  const { relatedFilters, clearRelationQueries } = useRelatedRouteFilters();
  const selectedPrintColumns = ref([]);
  const searchField = ref(null);
  const selectedColumnValue = ref(null);

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (m) => {
    errorMessage.value = m;
    showError.value = true;
  };

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

  /* rows */
  const rowsFT = computed(() => {
    const list = repo.Fasads || [];
    if (Array.isArray(list)) return list;
    if (Array.isArray(list.data)) return list.data;
    return [];
  });

  /* filter cards */
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
      (r) => r?.resource_id && String(r.resource_id).trim() !== ''
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
      return base.filter((r) => r?.resource_id && String(r.resource_id).trim() !== '');
    return base;
  });

  const pictureDialog = ref(false);
  const pictureSrc = ref('');
  const pictureTitle = ref('');

  function openPicture(row) {
    if (!row?.picture) return;
    pictureSrc.value = row.picture;
    pictureTitle.value = row.name || t('عکس');
    pictureDialog.value = true;
  }

  /* headers */
  const headersFT = computed(() => [
    { title: t('عکس'), key: 'picture', sortable: false },
    { title: t('تخلص'), key: 'nick_name' },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

    { title: t('موقعیت مشخص فعلی'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('شماره خانه'), key: 'home_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('جبهه'), key: 'jabha' },
    { title: t('نام تمویل‌کننده'), key: 'supplier_name' },
    { title: t('مسئولیت'), key: 'responsibility' },
    { title: t('ساحه فعالیت'), key: 'activity_zone' },
    { title: t('تعداد دوستان'), key: 'friends_number' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('فاصله ارتباط'), key: 'bond_distance' },
    { title: t('دلیل آخرین بند'), key: 'last_band_reason' },
    { title: t('محل بند'), key: 'band_place' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('تفصیل'), key: 'tafseel' },

    { title: t('کود منبع'), key: 'resource_id' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('ملاحظات'), key: 'notes', sortable: false },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['picture', 'name', 'nick_name', 'father_name', 'phone_number'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    {
      title: t('آدرس فعلی'),
      keys: [
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'coordinates',
        'specific_place',
        'street_number',
        'home_number',
      ],
    },
    {
      title: t('فعالیت/جبهه'),
      keys: ['jabha', 'supplier_name', 'responsibility', 'activity_zone', 'now_job'],
    },
    {
      title: t('سوابق'),
      keys: ['last_band_reason', 'band_place', 'friends_number', 'bond_distance'],
    },
    { title: t('منبع'), keys: ['resource_id', 'resource_phone'] },
  ]);

  const filters = ref({});

  /* peek */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loadingOne.value = true;
    try {
      await repo.FetchFasadData?.(id);
      const single = repo.Fasad && Array.isArray(repo.Fasad) ? repo.Fasad[0] : repo.Fasad || {};
      detail.value = single || {};
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات فساد'));
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
    router.push('/app/fasadform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/fasadform/${row.id}`);
  }

  function goPrint(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/fasads/print/${row.id}`);
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

  /* ✅ docs download */
  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  async function downloadDocuments(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    const name = row.name || row.code_number || `fasad_${row.id}`;
    try {
      if (typeof repo.DownloadFasadZip === 'function') {
        await repo.DownloadFasadZip(row.id, name);
      } else {
        showErrorMessage(t('دانلود اسناد برای فساد هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دانلود اسناد'));
    }
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
      await repo.DeleteFasad?.(row.id);
      await fetchAll();
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
      await repo.FetchFasadsData?.({
        search: repo.Search,
        search_field: normalizedSearchField.value || route.query.search_field || null,
        search_value: normalizedSearchValue.value || route.query.search_value || null,

        fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
        source: relatedFilters.value.source,
        open: relatedFilters.value.open,
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست فسادها'));
    }
  }

  async function fetchColumnValues(field, q = '') {
    try {
      const normalizedField =
        typeof field === 'string' ? field : field?.key ?? field?.value ?? null;

      if (!normalizedField) return [];

      const res = await repo.FetchFasadFilterOptions?.({
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

  function handlePageChange(val) {
    repo.page = val;
    fetchAll();
  }

  function handlePerPageChange(val) {
    repo.itemsPerPage = val;
    repo.page = 1;
    fetchAll();
  }

  usePageRefresh('/app/fasads', fetchAll);

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
      OpenResourcesPrintView: repo.OpenFasadsPrintView,
    },

    moduleKey: 'fasads',

    getFilters: () => ({
      search: repo.Search || '',
      search_field: normalizedSearchField.value || route.query.search_field || null,
      search_value: normalizedSearchValue.value || route.query.search_value || null,

      fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
      source: relatedFilters.value.source,
      open: relatedFilters.value.open,
    }),

    getHeaders: () => headersFT.value,

    getSelectedColumnKeys: () => selectedPrintColumns.value,

    getTitle: () => t('چاپ فسادها'),

    getPrintLabels: () => printLabels.value,

    getLocale: () => locale.value,

    defaultFileName: 'fasads-print',
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
  watch(
    () => route.query,
    async () => {
      repo.page = 1;
      await fetchAll();
    },
    { deep: true }
  );

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
  .page-bg {
    background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
    font-family: inherit;
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
  .cursor-pointer {
    cursor: pointer;
  }
</style>
