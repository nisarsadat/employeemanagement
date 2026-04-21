<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('منابع')" :subtitle="t('فهرست و جستجو')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- 🔍 Search + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Column -->
        <v-col cols="12" sm="12" md="12" lg="12">
          <SearchColumnFilter
            v-model:modelSearch="repo.Search"
            v-model:modelColumn="searchField"
            v-model:modelSelectedValue="selectedColumnValue"
            :headers="headersFT"
            :search-label="t('جستجو در منابع...')"
            :column-label="t('ستون جستجو')"
            :all-columns-label="t('همه ستون‌ها')"
            :value-label="t('ارزش ستون')"
            :exclude-keys="[
              'picture',
              'map',
              'events_count',
              'informations_count',
              'khayrat_reports_count',
              'fifty_six_formats_count',
              'payed_money',
            ]"
            :fetch-column-values="fetchColumnValues"
            @search="fetchAll"
            @clearSearch="clearSearch"
            @clearSelectedValue="clearSelectedValue"
          />
        </v-col>
        <!-- YEAR -->
        <v-col cols="12" sm="6" md="5">
          <v-select
            v-model="filterYear"
            :items="yearItems"
            item-title="title"
            item-value="value"
            density="comfortable"
            variant="outlined"
            rounded="lg"
            class="bg-white"
            hide-details
            clearable
            :label="t('سال')"
          />
        </v-col>

        <!-- MONTH -->
        <v-col cols="12" sm="4" md="2">
          <v-select
            v-model="filterMonth"
            :items="monthItems"
            item-title="title"
            item-value="value"
            density="comfortable"
            variant="outlined"
            class="bg-white"
            rounded="lg"
            hide-details
            clearable
            :label="t('ماه')"
          />
        </v-col>
        <!-- Create Button -->

        <v-col cols="12" sm="4" md="1" lg="1">
          <v-btn
            color="primary"
            variant="flat"
            rounded="lg"
            block
            class="text-none"
            prepend-icon="mdi-filter"
            @click="applyMonthYearFilter"
          >
            {{ t('فلتر') }}
          </v-btn>
        </v-col>
        <v-col cols="12" sm="4" md="1" lg="1">
          <v-btn
            color="orange"
            rounded="lg"
            block
            class="text-none"
            prepend-icon="mdi-filter-remove"
            @click="clearAllFilters"
          >
            {{ t('فلتر') }}
          </v-btn>
        </v-col>
        <v-col cols="12" sm="4" md="1">
          <v-btn
            color="success"
            variant="flat"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-microsoft-excel"
            :loading="excelDownloading"
            :disabled="excelDownloading"
            @click="downloadExcel"
          >
            {{ t('اکسل') }}
          </v-btn>
        </v-col>
        <!-- Create Button -->
        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            v-if="auth.can('resources.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('منبع جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- 📊 STATS CARDS -->
    <v-row class="px-2 mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'all' }"
          @click="applyQuickFilter('all')"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ totalCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کل منابع') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-database</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'active' }"
          @click="goActiveResources"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ activeCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('منابع فعال') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-check-circle</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'inactive' }"
          @click="goInactiveResources"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-purple fontfamily">
                {{ inactiveCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('منابع غیر فعال') }}
              </div>
            </div>
            <v-avatar color="purple" variant="tonal" size="48">
              <v-icon color="purple">mdi-cancel</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'today' }"
          @click="applyQuickFilter('today')"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-amber fontfamily">
                {{ todayAddedCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('امروز اضافه شده') }}
              </div>
            </div>
            <v-avatar color="amber" variant="tonal" size="48">
              <v-icon color="amber">mdi-calendar-today</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detailForPeek"
        :loading="loadingOne"
        title-key="name"
        image-key="picture"
        :fields="headersFT"
        :cols="3"
        dir="rtl"
        @fetch="fetchOne"
      />
    </v-container>

    <FilterableTable
      :headers="headersFT"
      :rows="displayedRowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      :dateKeys="['resource_saved_date']"
      @edit="startEdit"
      @peek="openPeek"
      class="mx-2"
    >
      <template #cell-picture="{ row }">
        <v-avatar size="36" class="cursor-pointer" @click="openPreview(row)">
          <v-img v-if="getPhotoUrl(row)" :src="getPhotoUrl(row)" cover />
          <v-icon v-else size="20">mdi-account</v-icon>
        </v-avatar>
      </template>

      <template #cell-activity="{ row }">
        <div class="d-flex align-center justify-center">
          <span
            class="status-dot"
            :class="{
              'status-dot--active': String(getRow(row).activity || '').trim() === 'فعال',
              'status-dot--inactive': String(getRow(row).activity || '').trim() === 'غیر فعال',
            }"
            :title="String(getRow(row).activity || '').trim()"
          />
        </div>
      </template>

      <template #cell-office_employee_id="{ row }">
        <div v-if="getOfficeEmployee(row)" class="d-flex flex-column align-start">
          <v-tooltip :text="t('اړوند کارمند')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                class="px-2 py-1 text-none"
                color="primary"
                size="default"
                variant="tonal"
                v-bind="tooltipProps"
                @click.stop="goOfficeEmployee(row)"
              >
                <div class="d-flex flex-column align-start">
                  <span class="text-body-2">
                    {{ getOfficeEmployee(row).code_number || t('بدون کود') }}
                  </span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-grey">-</span>
      </template>

      <template #cell-events_count="{ row }">
        <div v-if="getRow(row)?.events_count > 0" class="d-flex flex-column align-start">
          <v-tooltip :text="t('د پیښو تعداد')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                class="px-2 py-1 text-none"
                color="success"
                size="default"
                variant="tonal"
                v-bind="tooltipProps"
                @click.stop="goResourceEvents(row)"
              >
                <div class="d-flex align-center ga-1">
                  <v-icon size="18">mdi-alert-circle-check</v-icon>
                  <span class="text-body-2">
                    {{ getRow(row).events_count }}
                  </span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-red"> 0 </span>
      </template>

      <template #cell-informations_count="{ row }">
        <div v-if="getRow(row)?.informations_count > 0" class="d-flex flex-column align-start">
          <v-tooltip :text="t('د اطلاعاتو پیښه')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                class="px-2 py-1 text-none"
                color="info"
                size="default"
                variant="tonal"
                v-bind="tooltipProps"
                @click.stop="goResourceInformations(row)"
              >
                <div class="d-flex align-center ga-1">
                  <v-icon size="18">mdi-information-variant-circle</v-icon>
                  <span class="text-body-2">
                    {{ getRow(row).informations_count }}
                  </span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-red">0</span>
      </template>

      <template #cell-fifty_six_formats_count="{ row }">
        <div v-if="getRow(row)?.fifty_six_formats_count > 0" class="d-flex flex-column align-start">
          <v-tooltip :text="t('۵۰ فارمټ')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                class="px-2 py-1 text-none"
                color="pink"
                size="default"
                variant="tonal"
                v-bind="tooltipProps"
                @click.stop="goResourceFiftySixFormats(row)"
              >
                <div class="d-flex align-center ga-1">
                  <v-icon size="18">mdi-file-document-multiple-outline</v-icon>
                  <span class="text-body-2">
                    {{ getRow(row).fifty_six_formats_count }}
                  </span>
                </div>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else class="text-xs text-red">0</span>
      </template>

      <template #cell-code_number="{ row }">
        <div class="d-flex flex-column align-start">
          <v-btn class="px-2 py-1 text-none" color="warning" size="default" variant="tonal">
            <div class="d-flex align-center ga-1">
              <span class="text-body-2">
                {{ getRow(row).code_number }}
              </span>
            </div>
          </v-btn>
        </div>
      </template>

      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="getRow(row).coordinates"
          :title="getRow(row).name"
          :subtitle="getRow(row).code_number"
          type="resources"
          :photo-url="getPhotoUrl(row)"
          :zoom="16"
        />
      </template>

      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip text="د منبع لیدنه" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" @click.stop="openPeek(item)" color="blue">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="د منبع تغیروول" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('resources.update')"
                size="small"
                v-bind="tooltipProps"
                @click.stop="startEdit(item)"
                color="amber-darken-2"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="د منبع حذف" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('resources.delete')"
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

    <GlobalPagination
      :page="Number(repo.page) || 1"
      :items-per-page="Number(repo.itemsPerPage) || 10"
      :total="Number(repo.totalItems) || 0"
      @update:page="handlePageChange"
      @update:itemsPerPage="handlePerPageChange"
    />

    <v-dialog v-model="previewOpen" max-width="520" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card :dir="isRtl ? 'rtl' : 'ltr'">
        <v-toolbar
          density="compact"
          color="primary"
          :title="t('پیش نمایش عکس منبع')"
          class="text-white"
          :dir="isRtl ? 'rtl' : 'ltr'"
        />
        <v-card-text class="d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="480" max-height="420" cover />
        </v-card-text>
        <v-card-actions class="justify-end" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-btn color="primary" variant="flat" @click="previewOpen = false">
            {{ t('بستن') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
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
  import { ref, computed, watch, onMounted } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { useI18n } from 'vue-i18n';

  import AppBar from '@/components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';

  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import { useRelatedRouteFilters } from '@/composables/useRelatedRouteFilters';
  import SearchColumnFilter from '@/components/SearchColumnFilter.vue';
  const route = useRoute();
  const router = useRouter();
  const { t, locale } = useI18n();
  const { relatedFilters } = useRelatedRouteFilters();
  const repo = useDatabaseRepository();
  const auth = useLoginRepository();
  const selectedColumnValue = ref(null);
  const searchField = ref(null);
  const excelDownloadDialog = ref(false);
  const excelDownloadProgress = ref(0);
  const excelDownloading = ref(false);
  /* ===== RTL ===== */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  function normalizeUrl(url) {
    if (!url) return null;
    return url.replace('/storage/storage/', '/storage/');
  }

  function getPhotoUrl(payload) {
    const row = getRow(payload);
    if (!row) return null;
    return normalizeUrl(row.picture || row.picture_url);
  }

  const filterYear = ref(null);
  const filterMonth = ref(null);

  const yearItems = ref([]);
  const monthItems = ref([]);

  async function fetchYearItemsFromDb() {
    try {
      const res = await repo.FetchResourceDateOptions({ date_field: 'resource_saved_date' });
      const years = res?.data?.years || [];

      yearItems.value = years.map((y) => ({
        value: y.value,
        title: y.label,
      }));
    } catch (e) {
      console.error('fetchYearItemsFromDb error', e);
      showErrorMessage(t('خطا در دریافت سال‌ها'));
    }
  }
  async function fetchMonthItemsFromDb(year = null) {
    try {
      const res = await repo.FetchResourceDateOptions({
        date_field: 'resource_saved_date',
        year,
      });

      const months = res?.data?.months || [];

      monthItems.value = months.map((m) => ({
        value: m.value,
        title: m.label,
      }));
    } catch (e) {
      console.error('fetchMonthItemsFromDb error', e);
      monthItems.value = [];
      showErrorMessage(t('خطا در دریافت ماه‌ها'));
    }
  }

  watch(filterYear, async (newYear) => {
    filterMonth.value = null;
    await fetchMonthItemsFromDb(newYear);
  });
  async function applyMonthYearFilter() {
    repo.page = 1;
    await fetchAll();
  }

  const rowsFT = computed(() => repo.Resources || []);

  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },
    { title: t('پول داده شده'), key: 'payed_money' },
    { title: t('تعداد واقعات'), key: 'events_count' },
    { title: t('تعداد اطلاعات'), key: 'informations_count' },
    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('تعداد راپور خیریت'), key: 'khayrat_reports_count' },
    { title: t('تعداد ۵۶ فارمت'), key: 'fifty_six_formats_count' },
    { title: t('۵۶ فارمټ'), key: 'letry_format_name' },
    { title: t('کارمند مربوطه'), key: 'office_employee_id' },
    { title: t('کارمند مربوطه'), key: 'office_employee_code' },
    { title: t('رزیدنت مربوطه'), key: 'resedint_code' },
    { title: t('کود نمبر'), key: 'code_number' },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('تذکره نمبر'), key: 'tazkira' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی'), key: 'main_district' },
    { title: t('قریه'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک'), key: 'now_street_number' },
    { title: t('شماره خانه'), key: 'now_home_number' },

    { title: t('وظیفه سابقه'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('محل وظیفه'), key: 'job_place' },

    { title: t('زون تدارک'), key: 'zone' },
    { title: t('حوزه تدارک'), key: 'hawza' },
    { title: t('محل مشخص تدارک'), key: 'specefic_place' },
    { title: t('کوردینات تدارک'), key: 'coordinates' },

    { title: t('فعالیت'), key: 'activity' },
    { title: t('حالت مدنی'), key: 'halat_madani' },
    { title: t('عمر'), key: 'age' },
    { title: t('رتبه علمی'), key: 'elmi_level' },
    { title: t('فاکولته/دیپارتمنت'), key: 'faculty' },
    { title: t('مهارت‌های خاص'), key: 'specific_skills' },
    { title: t('تاریخ ثبت منبع'), key: 'resource_saved_date' },
    { title: t('نوع فعالیت منبع'), key: 'resource_activity_type' },

    { title: t('پوشش منبع'), key: 'resource_cover' },
    { title: t('زبان'), key: 'language' },
    { title: t('قوم'), key: 'nation' },
    { title: t('گروپ خون'), key: 'blood_group' },
    { title: t('دوره/آموزش'), key: 'train' },
    { title: t('مربوطه پروژه'), key: 'project' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('میزان اعتماد'), key: 'trust_percentage' },
    { title: t('نوت'), key: 'note' },
  ]);

  async function fetchAll() {
    try {
      await repo.FetchResourcesData({
        year: filterYear.value,
        month: filterMonth.value,
        date_field: 'resource_saved_date',
        stat: relatedFilters.value.stat || (quickFilter.value === 'all' ? null : quickFilter.value),
        search_field: searchField.value,
        search_value: selectedColumnValue.value,

        date: relatedFilters.value.date,
        date_from: relatedFilters.value.date_from,
        date_to: relatedFilters.value.date_to,
        from_date: relatedFilters.value.from_date,
        to_date: relatedFilters.value.to_date,

        resource_id: relatedFilters.value.resource_id,
        office_employee_id: relatedFilters.value.office_employee_id,
        information_id: relatedFilters.value.information_id,
        event_id: relatedFilters.value.event_id,
        fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
        stat: relatedFilters.value.stat || (quickFilter.value === 'all' ? null : quickFilter.value),
      });
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت فهرست منابع'));
    }
  }
  async function fetchColumnValues(field, q = '') {
    try {
      const res = await repo.FetchResourceFilterOptions({
        field,
        q,
      });

      return res?.data?.options || [];
    } catch (e) {
      console.error('fetchColumnValues error', e);
      return [];
    }
  }
  async function clearSelectedValue() {
    selectedColumnValue.value = null;
    repo.page = 1;
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

  function getOfficeEmployee(payload) {
    const row = getRow(payload);
    if (!row) return null;
    return row.office_employee || null;
  }

  function goResourceEvents(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push({
      path: '/app/events',
      query: {
        resource_id: row.id,
        source: 'resources',
      },
    });
  }

  function goResourceInformations(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push({
      path: '/app/informations',
      query: {
        resource_id: row.id,
        source: 'resources',
      },
    });
  }
  function goResourceFiftySixFormats(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push({ path: '/app/fifthyformatsroute', query: { resource_id: row.id } });
  }

  const totalCount = computed(() => Number(repo.resourceStats?.total_count) || 0);
  const activeCount = computed(() => Number(repo.resourceStats?.active_count) || 0);
  const inactiveCount = computed(() => Number(repo.resourceStats?.inactive_count) || 0);
  const todayAddedCount = computed(() => Number(repo.resourceStats?.today_added_count) || 0);

  function goActiveResources() {
    quickFilter.value = 'active';
    repo.page = 1;
    fetchAll();
  }

  function goInactiveResources() {
    quickFilter.value = 'inactive';
    repo.page = 1;
    fetchAll();
  }

  const quickFilter = ref('all');

  async function applyQuickFilter(mode) {
    quickFilter.value = mode;
    repo.page = 1;
    await fetchAll();
  }

  const routeResourceKey = computed(() => {
    return route.query?.id ?? route.query?.resource_id ?? route.params?.id ?? null;
  });

  const isNumericResourceKey = computed(() => {
    return Number.isFinite(Number(routeResourceKey.value));
  });

  const shouldOpenPeek = computed(() => {
    return String(route.query?.open || '').toLowerCase() === 'peek';
  });
  async function clearAllFilters() {
    repo.Search = '';
    searchField.value = null;
    selectedColumnValue.value = null;
    filterYear.value = null;
    filterMonth.value = null;
    quickFilter.value = 'all';
    filters.value = {};
    repo.page = 1;

    expanded.value = false;
    selectedId.value = null;
    detail.value = null;

    await router.replace({ path: '/app/resources', query: {} });
    await fetchAll();
  }

  const displayedRowsFT = computed(() => {
    let data = rowsFT.value || [];
    const key = routeResourceKey.value;

    if (!key) return data;

    if (isNumericResourceKey.value) {
      const id = Number(key);
      return data.filter((r) => Number(r.id) === id);
    }

    const code = String(key).trim();
    return data.filter((r) => String(r.code_number || '').trim() === code);
  });

  const topGroups = computed(() => [
    {
      title: t('معرفت'),
      keys: ['name', 'father_name', 'grand_father_name', 'nick_name', 'code_number', 'tazkira'],
    },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    {
      title: t('آدرس فعلی'),
      keys: [
        'now_province',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
      ],
    },
    { title: t('وظایف'), keys: ['old_job', 'now_job', 'job_place'] },
  ]);

  const filters = ref({});

  const showError = ref(false);
  const errorMessage = ref('');

  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  const previewOpen = ref(false);
  const previewSrc = ref(null);

  function openPreview(rowPayload) {
    const url = getPhotoUrl(rowPayload);
    if (!url) return;
    previewSrc.value = url;
    previewOpen.value = true;
  }

  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  const detailForPeek = computed(() => {
    if (!detail.value) return null;
    return {
      ...detail.value,
      picture: getPhotoUrl(detail.value),
    };
  });

  async function fetchOne(id) {
    if (!id) return;
    loadingOne.value = true;
    try {
      if (typeof repo.FetchResourceData === 'function') {
        await repo.FetchResourceData(id);
        const raw =
          repo.Resource && Array.isArray(repo.Resource) ? repo.Resource[0] : repo.Resource;
        detail.value = raw || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error('FetchOne resource error:', e);
      showErrorMessage(t('خطا در دریافت معلومات منبع'));
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

  async function clearSearch() {
    repo.Search = '';
    repo.page = 1;
    await fetchAll();
  }

  function goCreate() {
    router.push('/app/resourcesform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    const id = row?.id;
    if (!id) return;
    router.push(`/app/resourcesform/${id}`);
  }

  function goOfficeEmployee(payload) {
    const row = getRow(payload);
    if (!row) return;

    const empId = row.office_employee_id || row.office_employee?.id;
    if (!empId) return;

    router.push({
      path: '/app/officeemployee',
      query: {
        office_employee_id: empId,
        source: 'resources',
      },
    });
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
    if (!row?.id) {
      deleteDialog.value = false;
      deleteTarget.value = null;
      return;
    }

    try {
      if (typeof repo.DeleteResource === 'function') {
        await repo.DeleteResource(row.id);
      } else {
        console.warn('DeleteResource not implemented on repo');
        showErrorMessage(t('حذف برای این منبع هنوز تنظیم نشده است'));
        return;
      }

      await fetchAll();
    } catch (e) {
      console.error('handleDeleteConfirmed error', e);
      showErrorMessage(t('خطا در حذف منبع'));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }
  async function downloadExcel() {
    try {
      excelDownloading.value = true;
      excelDownloadDialog.value = true;
      excelDownloadProgress.value = 0;

      await repo.DownloadResourcesExcel({
        search: repo.Search || '',
        search_field: searchField.value,
        search_value: selectedColumnValue.value,
        year: filterYear.value,
        month: filterMonth.value,
        date_field: 'resource_saved_date',
        stat:
          relatedFilters.value.stat || (quickFilter.value === 'all' ? 'all' : quickFilter.value),

        date: relatedFilters.value.date,
        date_from: relatedFilters.value.date_from,
        date_to: relatedFilters.value.date_to,
        from_date: relatedFilters.value.from_date,
        to_date: relatedFilters.value.to_date,

        resource_id: relatedFilters.value.resource_id,
        office_employee_id: relatedFilters.value.office_employee_id,
        information_id: relatedFilters.value.information_id,
        event_id: relatedFilters.value.event_id,
        fifty_six_format_id: relatedFilters.value.fifty_six_format_id,

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
      console.error('downloadExcel error', e);
      showErrorMessage(t('خطا'));
    } finally {
      setTimeout(() => {
        excelDownloadDialog.value = false;
        excelDownloading.value = false;
        excelDownloadProgress.value = 0;
      }, 400);
    }
  }
  watch(
    [routeResourceKey, rowsFT, shouldOpenPeek],
    async ([key, rows, open]) => {
      if (!key || !open) return;

      let found = null;

      if (Number.isFinite(Number(key))) {
        const id = Number(key);
        found = (rows || []).find((r) => Number(r.id) === id);
      } else {
        const code = String(key).trim();
        found = (rows || []).find((r) => String(r.code_number || '').trim() === code);
      }

      if (!found) return;

      selectedId.value = found.id;
      expanded.value = true;
      await fetchOne(found.id);
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
  usePageRefresh('/app/resources', fetchAll);

  onMounted(async () => {
    await fetchYearItemsFromDb();
    await fetchMonthItemsFromDb(null);
    await fetchAll();
  });
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

  .stat-card-active {
    border-color: #1976d2 !important;
    box-shadow: 0 0 0 1px rgba(25, 118, 210, 0.3);
  }

  .text-green {
    color: #4caf50 !important;
  }

  .text-amber {
    color: #ff9800 !important;
  }

  .text-purple {
    color: #9c27b0 !important;
  }

  .search-field :deep(.v-field) {
    min-height: 48px;
  }

  .cursor-pointer {
    cursor: pointer;
  }

  .fontfamily {
    font-family: var(--app-font) !important;
  }

  .text-grey {
    color: #9e9e9e;
  }

  .status-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    display: inline-block;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.06);
  }

  .status-dot--active {
    background: #4caf50;
  }

  .status-dot--inactive {
    background: #f44336;
  }
</style>
