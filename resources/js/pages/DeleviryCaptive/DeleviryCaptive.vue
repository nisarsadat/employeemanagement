<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('زندانی های تسلیم شده')"
      :subtitle="t('فهرست، جستجو و مدیریت زندانی های تسلیم شده')"
    >
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- ✅ SearchColumnFilter -->
        <v-col cols="12" sm="12" md="12" lg="12">
          <SearchColumnFilter
            v-model:modelSearch="repo.Search"
            v-model:modelColumn="searchField"
            v-model:modelSelectedValue="selectedColumnValue"
            :headers="headersFT"
            :search-label="t('جستجو در زندانی ها...')"
            :column-label="t('ستون جستجو')"
            :all-columns-label="t('همه ستون‌ها')"
            :value-label="t('ارزش ستون')"
            :exclude-keys="['picture', 'map']"
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

        <!-- Create -->
        <v-col cols="12" sm="6" md="4" lg="4" class="pl-sm-2">
          <v-btn
            v-if="auth.can('delevirycaptive.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('حدید') }}
          </v-btn>
        </v-col>

        <!-- Excel -->
        <v-col cols="12" sm="6" md="4" lg="4" class="pl-sm-2">
          <v-btn
            color="success"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-microsoft-excel"
            @click="downloadExcel"
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

      <!-- ✅ Cards now filter from backend -->
      <v-row class="mt-2 d-flex justify-between" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-col cols="12" sm="2" md="4">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'all' }"
            :elevation="cardFilter === 'all' ? 6 : 1"
            @click="applyCardFilter('all')"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-group</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('همه') }}</div>
                <div class="stat-card-subtitle">{{ t('مجموع') }}: {{ stats.total }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="2">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'saeen' }"
            :elevation="cardFilter === 'saeen' ? 6 : 1"
            @click="applyCardFilter('saeen')"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-alert</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('ساعین بالفساد') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.saeen }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="2">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'sharFasad' }"
            :elevation="cardFilter === 'sharFasad' ? 6 : 1"
            @click="applyCardFilter('sharFasad')"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-fire-alert</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('شر او فساد') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.sharFasad }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="2">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'spy' }"
            :elevation="cardFilter === 'spy' ? 6 : 1"
            @click="applyCardFilter('spy')"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-shield-search</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('جاسوسی') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.spy }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="2">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'today' }"
            :elevation="cardFilter === 'today' ? 6 : 1"
            @click="applyCardFilter('today')"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-calendar-today</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('امروز اضافه شده') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.today }}</div>
              </div>
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

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
        :dir="isRtl ? 'rtl' : 'ltr'"
        @fetch="fetchOne"
      />
    </v-container>

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
      class="mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >
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

      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.captive_place"
          type="deleviry_captive"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip :text="t('مشاهده زندانی تسلیم شده')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('ویرایش زندانی تسلیم شده')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('delevirycaptive.update')"
                v-bind="tooltipProps"
                size="small"
                color="amber-darken-2"
                @click.stop="goEdit(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('دانلود اسناد زندانی تسلیم شده')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('attachmentDownload.create')"
                v-bind="tooltipProps"
                size="small"
                color="green-darken-2"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadDocs(item)"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('حذف زندانی تسلیم شده')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('delevirycaptive.delete')"
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

    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />

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
  import { useRelatedRouteFilters } from '@/composables/useRelatedRouteFilters';

  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import SearchColumnFilter from '@/components/SearchColumnFilter.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';

  import { useDatabaseRepository } from '@/stores/DatabaseRepository';
  import { useLoginRepository } from '@/stores/LoginRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import { useTablePrintExport } from '@/composables/useTablePrintExport';
  const repo = useDatabaseRepository();
  const auth = useLoginRepository();
  const router = useRouter();
  const route = useRoute();
  const { relatedFilters, clearRelationQueries } = useRelatedRouteFilters();

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const searchField = ref(null);
  const selectedColumnValue = ref(null);

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

  const rowsFT = computed(() => {
    const list = repo.CaptiveDeliveries || repo.DeleviryCaptives || [];
    if (Array.isArray(list)) return list;
    if (Array.isArray(list.data)) return list.data;
    return [];
  });

  const cardFilter = ref('all');

  const stats = computed(() => ({
    total: Number(repo.captiveCardCounts?.total ?? 0),
    spy: Number(repo.captiveCardCounts?.spy ?? 0),
    saeen: Number(repo.captiveCardCounts?.saeen ?? 0),
    sharFasad: Number(repo.captiveCardCounts?.sharFasad ?? 0),
    today: Number(repo.captiveCardCounts?.today ?? 0),
  }));

  function applyCardFilter(value) {
    cardFilter.value = value;
    repo.page = 1;
    fetchAll();
  }

  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدر کلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nickname' },
    { title: t('نمبر تذکره'), key: 'tazkira' },
    { title: t('عمر'), key: 'age' },
    { title: t('حالت مدنی'), key: 'halat_madani' },
    { title: t('سابقه جرمی'), key: 'criminal_history' },
    { title: t('وظیفه سابقه'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'new_job' },
    { title: t('زبان'), key: 'language' },
    { title: t('سطح علمی'), key: 'scientific_level' },
    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/گذر اصلی'), key: 'main_village' },
    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/گذر فعلی'), key: 'now_village' },
    { title: t('علت اسارت'), key: 'captive_reason' },
    { title: t('نوع مظنونیت'), key: 'maznonyat_type' },
    { title: t('تسلیمی ورکونکی شخص'), key: 'taslimi_warkawonky_person' },
    { title: t('تسلیمی اخیستونکی شخص'), key: 'taslimi_akhistonky_person' },
    { title: t('تسلیمی اخیستونکی ارگان'), key: 'taslimi_akhistonky_organ' },
    { title: t('محل دستگیری'), key: 'captive_place' },
    { title: t('کوردینات محل دستگیری'), key: 'coordinates' },
    { title: t('مرجع دستگیری'), key: 'captive_marja' },
    { title: t('تاریخ دستگیری'), key: 'captive_date' },
    { title: t('ساعت دستگیری'), key: 'captive_hour' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('حالت دستگیری'), key: 'captive_halat' },
    { title: t('وسایل و شواهد گرفته شده'), key: 'suspect_tools' },
    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('ملاحظات'), key: 'notes' },
  ]);

  const topGroups = computed(() => [
    { title: t('معرفت'), keys: ['name', 'father_name', 'grand_father_name', 'nickname'] },
    { title: t('اطلاعات شخصی'), keys: ['tazkira', 'age', 'halat_madani', 'criminal_history'] },
    { title: t('وظایف'), keys: ['old_job', 'new_job'] },
    { title: t('مهارت‌ها/زبان'), keys: ['language', 'scientific_level'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
    {
      title: t('جزئیات دستگیری'),
      keys: ['captive_place', 'coordinates', 'captive_marja', 'captive_date', 'captive_hour'],
    },
    {
      title: t('مظنون/تماس'),
      keys: [
        'phone_number',
        'captive_halat',
        'suspect_tools',
        'maznonyat_type',
        'captive_reason',
        'taslimi_warkawonky_person',
        'taslimi_akhistonky_person',
        'taslimi_akhistonky_organ',
      ],
    },
  ]);

  async function downloadExcel() {
    try {
      await repo.DownloadCaptiveDeliveriesExcel({
        stat: cardFilter.value,
        search: repo.Search,
        search_field: normalizedSearchField.value,
        search_value: normalizedSearchValue.value,
        date_field: 'captive_date',
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دانلود اکسل'));
    }
  }

  const filters = ref({});

  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loadingOne.value = true;
    try {
      if (typeof repo.FetchCaptiveDeliveryData === 'function') {
        await repo.FetchCaptiveDeliveryData(id);
        detail.value =
          (Array.isArray(repo.CaptiveDelivery) ? repo.CaptiveDelivery[0] : repo.CaptiveDelivery) ||
          {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات زندانی'));
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

  function goCreate() {
    router.push('/app/delevirycaptiveform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/delevirycaptiveform/${row.id}`);
  }

  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  async function downloadDocs(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    try {
      const base = (row.name || 'documents').toLowerCase().replace(/\s+/g, '_');
      await repo.DownloadCaptiveDeliveryZip(row.id, `${base}_documents.zip`);
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دانلود اسناد'));
    }
  }

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
      await repo.DeleteCaptiveDelivery(row.id);
      await fetchAll();
      showErrorMessage(t('حذف شد'));
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در حذف'));
    } finally {
      deleteTarget.value = null;
    }
  }

  async function fetchAll() {
    try {
      await repo.FetchCaptiveDeliveriesData({
        stat: cardFilter.value,
        search: repo.Search,
        search_field: normalizedSearchField.value,
        search_value: normalizedSearchValue.value,
        fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
        source: relatedFilters.value.source,
        open: relatedFilters.value.open,
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست زندانی ها'));
    }
  }

  async function fetchColumnValues(field, q = '') {
    try {
      const normalizedField =
        typeof field === 'string' ? field : field?.key ?? field?.value ?? null;

      if (!normalizedField) return [];

      const res = await repo.FetchCaptiveDeliveryFilterOptions({
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

  function handlePageChange(value) {
    repo.page = value;
    fetchAll();
  }

  function handlePerPageChange(value) {
    repo.itemsPerPage = value;
    repo.page = 1;
    fetchAll();
  }

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

  usePageRefresh('/app/delevirycaptive', fetchAll);

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
  const selectedPrintColumns = ref([]);

  const printLabels = computed(() => ({
    print: t('چاپ'),
    record_count: t('تعداد ریکارد'),
  }));

  function buildDefaultPrintColumns() {
    return headersFT.value.map((h) => h.key).filter((key) => !['map'].includes(key));
  }

  const printableHeaderOptions = computed(() => {
    return headersFT.value.filter((h) => !['map'].includes(h.key));
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

  const printRepo = {
    OpenResourcesPrintView: (payload) => repo.OpenCaptiveDeliveriesPrintView(payload),
  };

  const { printAll } = useTablePrintExport({
    repo: printRepo,
    moduleKey: 'captive-deliveries',
    getFilters: () => ({
      stat: cardFilter.value,
      search: repo.Search,
      search_field: normalizedSearchField.value,
      search_value: normalizedSearchValue.value,
      fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
    }),
    getHeaders: () => headersFT.value,
    getSelectedColumnKeys: () => selectedPrintColumns.value,
    getTitle: () => t('چاپ زندانی های تسلیم شده'),
    getPrintLabels: () => printLabels.value,
    getLocale: () => locale.value,
    defaultFileName: 'captive-deliveries-print',
  });
  watch(
    () => route.query,
    async () => {
      repo.page = 1;
      await fetchAll();
    },
    { deep: true }
  );

  onMounted(async () => {
    if (!relatedFilters.value.fifty_six_format_id) {
      await fetchAll();
    }
    selectedPrintColumns.value = buildDefaultPrintColumns();
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

  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }

  .cursor-pointer {
    cursor: pointer;
  }
</style>
