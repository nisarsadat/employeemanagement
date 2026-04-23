<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <ManagerFinanceDialog
      v-model="managerFinanceDialogOpen"
      :id="selectedRow?.id"
      :account_id="selectedRow?.account_id ?? null"
      :message_to_finance="selectedRow?.message_to_finance ?? ''"
      :money_amount_words="selectedRow?.money_amount_words ?? ''"
      :manager_comment="selectedRow?.manager_comment ?? ''"
      :account-items="accountItems"
      :is-rtl="isRtl"
      :loading="dialogSaving"
      @save="handleSaveManagerFinance"
    />

    <AnalysisLeadershipDialog
      v-model="analysisLeadershipDialogOpen"
      :id="selectedRow?.id"
      :tahlil="selectedRow?.tahlil ?? ''"
      :lead_drirection="selectedRow?.lead_drirection ?? ''"
      :is-rtl="isRtl"
      :loading="dialogSaving"
      @save="handleSaveAnalysisLeadership"
    />
    <AppBar :title="t('اطلاعات')" :subtitle="t('فهرست و جستجو')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- Search + Create + Year/Month Filter -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search -->
        <v-col cols="12" sm="12" md="12" lg="12">
          <!-- Search -->
          <SearchColumnFilter
            v-model:modelSearch="repo.Search"
            v-model:modelColumn="searchField"
            v-model:modelSelectedValue="searchValue"
            :headers="headersFT"
            :search-label="t('جستجو در اطلاعات...')"
            :column-label="t('ستون جستجو')"
            :all-columns-label="t('همه ستون‌ها')"
            :exclude-keys="['matched_links', 'map']"
            :fetch-column-values="fetchFilterValues"
            @search="fetchAll"
            @clear="clearSearch"
          />
        </v-col>

        <!-- ✅ NEW: Year -->
        <v-col cols="12" sm="6" md="6" lg="5">
          <v-select
            v-model="filterYear"
            :items="yearItems"
            item-title="title"
            item-value="value"
            density="comfortable"
            variant="outlined"
            rounded="lg"
            hide-details
            clearable
            :label="t('سال')"
            class="bg-white"
          />
        </v-col>

        <!-- ✅ NEW: Month -->
        <v-col cols="12" sm="4" md="2" lg="2">
          <v-select
            v-model="filterMonth"
            :items="monthItems"
            item-title="title"
            item-value="value"
            density="comfortable"
            variant="outlined"
            rounded="lg"
            size="large"
            hide-details
            clearable
            :label="t('ماه')"
            class="bg-white"
          />
        </v-col>

        <!-- ✅ NEW: Filter button -->
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
            @click="resetAllFilters"
          >
            {{ t('فلتر') }}
          </v-btn>
        </v-col>
        <v-col cols="12" sm="4" md="1" lg="1">
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
        <!-- Create -->
        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            v-if="auth.can('informations.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            @click="goCreate"
            prepend-icon="mdi-plus"
          >
            {{ t('اطلاع جدید') }}
          </v-btn>
        </v-col>
        <v-col cols="12" sm="10" >
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
        <v-col cols="12" sm="2" >
          <v-btn
            color="red"
            variant="flat"
            class="text-none"
            rounded="lg"
            size="large"
            block
            prepend-icon="mdi-printer"
            @click="printAll"
          >
            {{ t('چاپ') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- STATS CARDS -->
    <v-row class="px-2 d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" sm="6" md>
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('resources')"
          :class="{ 'active-stat': activeStat === 'resources' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ totalResourcesCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کل اطلاعات') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-account-multiple</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md>
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('saeen')"
          :class="{ 'active-stat': activeStat === 'saeen' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-amber fontfamily">
                {{ saeenFasadCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('ساعین فساد') }}
              </div>
            </div>
            <v-avatar color="amber" variant="tonal" size="48">
              <v-icon color="amber">mdi-alert-circle</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md>
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('shar')"
          :class="{ 'active-stat': activeStat === 'shar' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-purple fontfamily">
                {{ sharOFasadCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('شر او فساد') }}
              </div>
            </div>
            <v-avatar color="purple" variant="tonal" size="48">
              <v-icon color="purple">mdi-fire</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md>
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('spy')"
          :class="{ 'active-stat': activeStat === 'spy' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ jasosiCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('جاسوسی') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-shield-search</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md>
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('today')"
          :class="{ 'active-stat': activeStat === 'today' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ todayCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('امروز ثبت شده') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-calendar-today</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
      <!-- ✅ NEW: Category Alef -->
      <v-col cols="12" sm="6" md>
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('cat_alef')"
          :class="{ 'active-stat': activeStat === 'cat_alef' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-indigo fontfamily">
                {{ categoryAlefCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('اطلاعات کتگوری الف') }}
              </div>
            </div>
            <v-avatar color="indigo" variant="tonal" size="48">
              <v-icon color="indigo">mdi-format-list-bulleted</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
      <!-- ✅ NEW: Unpaid records -->
      <v-col cols="12" sm="6" md>
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('unpaid')"
          :class="{ 'active-stat': activeStat === 'unpaid' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-red-darken-2 fontfamily">
                {{ unpaidCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('ناپرداخت') }}
              </div>
            </div>
            <v-avatar color="red-darken-2" variant="tonal" size="48">
              <v-icon color="red-darken-2">mdi-cash-remove</v-icon>
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
        :item="detail"
        :loading="loading"
        :name-key="'name'"
        :fields="headersFT"
        dir="rtl"
        @close="expanded = false"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- List -->
    <FilterableTable
      :headers="headersFT"
      :rows="filteredRows"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :autoDetectDates="false"
      :dateKeys="['information_saved_date', 'hamahangi_date']"
      class="mx-2"
    >
      <template #cell-matched_links="{ row }">
        <div class="d-flex flex-wrap ga-1 align-center">
          <v-chip
            v-if="getRow(row).matched_information_id"
            size="small"
            color="primary"
            variant="tonal"
            class="cursor-pointer"
            @click.stop="filterByRelatedId(getRow(row).matched_information_id)"
          >
            {{ t('اصل') }} #{{ getRow(row).matched_information_id }}
          </v-chip>

          <v-chip
            v-for="cid in getRow(row).child_matched_information_ids || []"
            :key="cid"
            size="small"
            color="deep-purple"
            variant="tonal"
            class="cursor-pointer"
            @click.stop="filterByRelatedId(cid)"
          >
            #{{ cid }}
          </v-chip>

          <span
            v-if="
              !getRow(row).matched_information_id &&
              !(getRow(row).child_matched_information_ids || []).length
            "
            class="text-caption text-medium-emphasis"
          >
            —
          </span>
        </div>
      </template>
      <!-- RESOURCE -->
      <template #cell-resource_display="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip v-if="row.resource_id" text="اړوند منبع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="primary"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToResource(row)"
              >
                <v-icon start size="16">mdi-account-search</v-icon>
                <span class="text-caption">{{ row.resource_id }}</span>
              </v-btn>
            </template>
          </v-tooltip>
          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div>
      </template>

      <!-- OFFICE EMPLOYEE codes -->
      <template #cell-office_employee_display="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip v-if="row.office_employee_code_number" text="اړوند کارمند" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="deep-purple"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToOfficeEmployee(row)"
              >
                <v-icon start size="16">mdi-account-badge</v-icon>
                <span class="text-caption">{{ row.office_employee_code_number }}</span>
              </v-btn>
            </template>
          </v-tooltip>
          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div>
      </template>

      <template #cell-office_employee_info_display="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip v-if="row.office_employee_info_code_number" text="اړوند کارمند" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="deep-orange"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToOfficeEmployeeInfo(row)"
              >
                <v-icon start size="16">mdi-account-badge</v-icon>
                <span class="text-caption">{{ row.office_employee_info_code_number }}</span>
              </v-btn>
            </template>
          </v-tooltip>
          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div>
      </template>

      <!-- Finance status -->
      <template #cell-finance_status="{ row }">
        <v-chip
          size="small"
          label
          class="fontfamily"
          :color="financeStatusColor(getRow(row).finance_status)"
          variant="tonal"
        >
          <v-icon start size="14">mdi-cash-check</v-icon>
          {{ financeStatusLabel(getRow(row).finance_status) }}
        </v-chip>
      </template>

      <!-- Actions -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip text="د اطلاع مشاهده" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="د اطلاع تغیروول" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('informations.update')"
                size="small"
                v-bind="tooltipProps"
                color="amber-darken-2"
                @click.stop="startEdit(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="مربوطه اسناد" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                v-if="auth.can('attachmentDownload.create')"
                color="green-darken-2"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadDocuments(item)"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="د اطلاع حذفول" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('informations.delete')"
                size="small"
                v-bind="tooltipProps"
                color="red-darken-2"
                @click.stop="openDeleteDialog(item)"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
          <!-- ✅ Manager / Finance Dialog -->
          <v-tooltip text="مالی/مدیر" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('managercomment.view')"
                size="small"
                v-bind="tooltipProps"
                color="indigo"
                @click.stop="openManagerFinanceDialog(item)"
              >
                mdi-cash-edit
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ✅ Analysis / Leadership Dialog -->
          <v-tooltip text="تحلیل او تجزیه" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('direction.view')"
                size="small"
                v-bind="tooltipProps"
                color="deep-purple"
                @click.stop="openAnalysisLeadershipDialog(item)"
              >
                mdi-text-box-edit
              </v-icon>
            </template>
          </v-tooltip>
          <v-tooltip text="د اطلاع فورم" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="primary"
                @click.stop="goPrint(item)"
              >
                mdi-file-document-outline
              </v-icon>
            </template>
          </v-tooltip>
        </div>
      </template>
      <template #cell-result_pending_message="{ row }">
        <div class="d-flex align-center">
          <v-chip
            v-if="getRow(row).result_pending_bucket"
            size="small"
            label
            class="fontfamily"
            :color="resultPendingColor(getRow(row).result_pending_bucket)"
            variant="tonal"
          >
            <v-icon start size="14">mdi-clock-alert-outline</v-icon>
            {{ resultPendingLabel(getRow(row)) }}
          </v-chip>

          <span v-else class="text-caption text-medium-emphasis">—</span>
        </div>
      </template>
      <!-- 🗺 Map cell -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="getRow(row).coordinates"
          :title="getRow(row).name"
          :subtitle="getRow(row).syasi_info_type || getRow(row).jenayee_info_type"
          type="information"
          :zoom="16"
        />
      </template>
    </FilterableTable>

    <GlobalPagination
      :page="repo.page"
      :items-per-page="repo.itemsPerPage"
      :total="repo.totalItems"
      @update:page="handlePageChange"
      @update:itemsPerPage="handlePerPageChange"
    />

    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />
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
  import { ref, onMounted, computed, watch } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import axios from 'axios';

  import AppBar from '../../components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';
  import { useFinanceRepository } from '@/stores/FinanceRepository';
  import ManagerFinanceDialog from './ManagerFinanceDialog.vue';
  import AnalysisLeadershipDialog from './AnalysisLeadershipDialog.vue';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import { useRelatedRouteFilters } from '@/composables/useRelatedRouteFilters';
  import SearchColumnFilter from '@/components/SearchColumnFilter.vue';

  const auth = useLoginRepository();
  const { t, locale } = useI18n();
  const { relatedFilters } = useRelatedRouteFilters();
  const repo = useDatabaseRepository();
  const financeRepo = useFinanceRepository();
  const router = useRouter();
  const route = useRoute();
  const searchField = ref(null);
  const searchValue = ref(null);
  const excelDownloadDialog = ref(false);
  const excelDownloadProgress = ref(0);
  const excelDownloading = ref(false);
  const selectedPrintColumns = ref([]);
  async function fetchAll() {
    try {
      await repo.FetchInformationsData?.({
        search: repo.Search,
        search_field: searchField.value,
        search_value: searchValue.value,
        year: filterYear.value,
        month: filterMonth.value,
        date_field: 'information_saved_date',

        // route-driven stat/type first, local fallback after
        stat: relatedFilters.value.stat || activeStat.value,
        type: relatedFilters.value.type,

        // route date filters
        date: relatedFilters.value.date,
        date_from: relatedFilters.value.date_from,
        date_to: relatedFilters.value.date_to,
        from_date: relatedFilters.value.from_date,
        to_date: relatedFilters.value.to_date,

        // relation filters
        information_id: relatedFilters.value.information_id,
        resource_id: relatedFilters.value.resource_id,
        office_employee_id: relatedFilters.value.office_employee_id,
        event_id: relatedFilters.value.event_id,
        fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
      });

      await fetchStats();
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت معلومات'));
    }
  }
  usePageRefresh('/app/informations', fetchAll);

  const accountItems = computed(() =>
    (financeRepo.accounts || []).map((a) => ({
      ...a,
      display: a.name ? `${a.name} (${a.price ?? '0'})` : `${a.id}`,
    }))
  );
  async function fetchFilterValues(field, q = '') {
    if (!field) return [];

    try {
      const res = await repo.FetchInformationFilterOptions({
        field,
        q,
      });

      return res?.data?.options || [];
    } catch (e) {
      console.error('fetchFilterValues error', e);
      return [];
    }
  }
  /* ===== RTL ===== */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => (isRtl.value = newLocale === 'fa'));

  /* ===== unwrap helper ===== */
  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  /* ==========================
   * ✅ Month/Year Filters
   * ========================== */
  const filterYear = ref(null);
  const filterMonth = ref(null);

  const yearItems = ref([]);
  const monthItems = ref([]);

  async function fetchYearItemsFromDb() {
    try {
      const res = await repo.FetchInformationDateOptions?.({
        date_field: 'information_saved_date',
      });

      const years = res?.data?.years || [];

      yearItems.value = years.map((y) => ({
        value: y.value,
        title: y.label,
      }));
    } catch (e) {
      console.error('fetchYearItemsFromDb error', e);
    }
  }
  async function fetchMonthItemsFromDb(year = null) {
    try {
      const res = await repo.FetchInformationDateOptions?.({
        date_field: 'information_saved_date',
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

  const resultPendingLabels = computed(() => ({
    done: t('done'),
    pending: t('pending'),
    week: t('week'),
    two_weeks: t('two_weeks'),
    month: t('month'),
  }));

  function resultPendingLabel(row) {
    const item = getRow(row);
    const bucket = item?.result_pending_bucket;

    return resultPendingLabels.value[bucket] || item?.result_pending_message || '—';
  }
  function resultPendingColor(bucket) {
    const v = String(bucket ?? '')
      .trim()
      .toLowerCase();

    if (v === 'done') return 'green';
    if (v === 'pending') return 'amber';
    if (v === 'week') return 'orange';
    if (v === 'two_weeks') return 'deep-orange';
    if (v === 'month') return 'red-darken-2';

    return 'grey';
  }

  /* ===== Active stat cards ===== */
  const activeStat = ref(null);

  async function filterByStat(stat) {
    activeStat.value = activeStat.value === stat ? null : stat;
    repo.page = 1;
    await fetchAll();
  }

  /* ===== Data sources ===== */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  const allRows = computed(() => toArray(repo.Informations));

  /* ===== ✅ BACKEND STATS ===== */
  const totalResourcesCount = ref(0);
  const saeenFasadCount = ref(0);
  const sharOFasadCount = ref(0);
  const jasosiCount = ref(0);
  const todayCount = ref(0);
  const categoryAlefCount = ref(0);
  const unpaidCount = ref(0);

  async function fetchStats() {
    try {
      const { data } = await axios.get('/informations', {
        params: {
          search: repo.Search,
          search_field: searchField.value,
          search_value: searchValue.value,
          year: filterYear.value,
          month: filterMonth.value,
          date_field: 'information_saved_date',
          stats_only: 1,

          stat: relatedFilters.value.stat || activeStat.value,
          type: relatedFilters.value.type,

          date: relatedFilters.value.date,
          date_from: relatedFilters.value.date_from,
          date_to: relatedFilters.value.date_to,
          from_date: relatedFilters.value.from_date,
          to_date: relatedFilters.value.to_date,

          information_id: relatedFilters.value.information_id,
          resource_id: relatedFilters.value.resource_id,
          office_employee_id: relatedFilters.value.office_employee_id,
          event_id: relatedFilters.value.event_id,
          fifty_six_format_id: relatedFilters.value.fifty_six_format_id,
        },
      });

      const stats = data?.stats || {};

      totalResourcesCount.value = Number(stats.total ?? 0);
      saeenFasadCount.value = Number(stats.saeen ?? 0);
      sharOFasadCount.value = Number(stats.shar ?? 0);
      jasosiCount.value = Number(stats.spy ?? 0);
      todayCount.value = Number(stats.today ?? 0);
      categoryAlefCount.value = Number(stats.cat_alef ?? 0);
      unpaidCount.value = Number(stats.unpaid ?? 0);
    } catch (e) {
      console.error('fetchStats error', e);
    }
  }
  const filteredRows = computed(() => allRows.value);

  async function filterByRelatedId(id) {
    if (!id) return;

    repo.page = 1;

    await router.push({
      path: '/app/informations',
      query: {
        ...route.query,
        information_id: String(id),
        source: 'informations',
      },
    });
  }

  /* ===== Headers / Groups (unchanged) ===== */
  const headersFT = computed(() => [
    { title: t('آی ډی'), key: 'id' },
    { title: t('کود منبع'), key: 'resource_display' },
    { title: t('کارمند مربوط'), key: 'office_employee_display' },
    { title: t('پول پرداخت شده'), key: 'finance_paid_amount' },
    { title: t('د پیسو د پرداخت وضعیت'), key: 'finance_status' },
    { title: t('تعقیبی'), key: 'matched_links' },
    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('د نتیجې ځنډ'), key: 'result_pending_message' },
    { title: t('نام'), key: 'name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('حالت مدنی'), key: 'madani_halat' },

    { title: t('سن'), key: 'age' },
    { title: t('وظیفه'), key: 'job' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_districts' },
    { title: t('قریه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص'), key: 'specefic_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('شماره خانه'), key: 'home_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('سیاسی'), key: 'syasi_info_type' },
    { title: t('جنایی'), key: 'jenayee_info_type' },
    { title: t('۵۶ فارمت'), key: 'letry_format_name' },

    { title: t('تاریخ اطلاع'), key: 'information_saved_date' },
    { title: t('ساعت اطلاع'), key: 'information_saved_hour' },
    { title: t('درجه موثقیت اطلاع'), key: 'information_degree' },
    { title: t('د اطلاع کتګوري'), key: 'information_category' },
    { title: t('کارمند اطلاع گیرنده'), key: 'office_employee_info_display' },
    { title: t('د منبع لاسرسی'), key: 'resource_lasrasy' },

    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('نوت راجع به اسناد'), key: 'docs_note' },

    { title: t('نظر آمریت تعقیب اطلاع'), key: 'tahlil' },
    { title: t('نظر مدیر'), key: 'manager_comment' },
    { title: t('رهنمود رهبریت'), key: 'lead_drirection' },
    { title: t('تاریخ هم‌آهنگی'), key: 'hamahangi_date' },
    { title: t('شماره مکتوب'), key: 'maktop_number' },
    { title: t('ارگان'), key: 'organ' },
    { title: t('نتیجه'), key: 'result' },
    { title: t('ملاحظات'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('کودها'), keys: ['resource_id', 'office_employee_id'] },
    { title: t('معرفت'), keys: ['name', 'nick_name', 'father_name', 'grand_father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_districts', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
    {
      title: t('نوع و زمان اطلاع'),
      keys: [
        'syasi_info_type',
        'jenayee_info_type',
        'information_saved_date',
        'information_saved_hour',
        'information_degree',
      ],
    },
    { title: t('هم‌آهنگی'), keys: ['hamahangi_date', 'maktop_number', 'organ'] },
    {
      title: t('متن‌ها'),
      keys: ['tafseel', 'manager_comment', 'lead_drirection', 'note', 'result'],
    },
  ]);

  /* ===== Finance chip helpers ===== */
  function financeStatusLabel(v) {
    const s = String(v ?? '')
      .trim()
      .toLowerCase();
    if (s === 'paid' || s === 'پرداخت' || s === 'paid_finance') return 'پرداخت شوی';
    if (s === 'unpaid' || s === 'pending' || s === 'ناپرداخت' || s === 'not_paid')
      return 'ناپرداخت';
    if (!s) return '—';
    return v;
  }

  function financeStatusColor(v) {
    const s = String(v ?? '')
      .trim()
      .toLowerCase();
    if (s === 'paid' || s === 'پرداخت' || s === 'paid_finance') return 'green';
    if (s === 'unpaid' || s === 'pending' || s === 'ناپرداخت' || s === 'not_paid') return 'amber';
    return 'grey';
  }

  /* ===== Documents download ===== */
  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  async function downloadDocuments(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    const name = row.name || row.code_number || `information_${row.id}`;

    try {
      if (typeof repo.DownloadInformationZip === 'function') {
        await repo.DownloadInformationZip(row.id, name);
      } else {
        showErrorMessage(t('دانلود اسناد برای این اطلاع هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error('downloadDocuments error', e);
      showErrorMessage(t('خطا در دانلود اسناد'));
    }
  }

  const managerFinanceDialogOpen = ref(false);
  const analysisLeadershipDialogOpen = ref(false);
  const dialogSaving = ref(false);
  const selectedRow = ref(null);

  function openManagerFinanceDialog(payload) {
    selectedRow.value = getRow(payload);
    managerFinanceDialogOpen.value = true;
  }

  function openAnalysisLeadershipDialog(payload) {
    selectedRow.value = getRow(payload);
    analysisLeadershipDialogOpen.value = true;
  }

  async function handleSaveManagerFinance(patch) {
    dialogSaving.value = true;
    try {
      await repo.UpdateInformation(patch.id, patch);
      await fetchAll();
      managerFinanceDialogOpen.value = false;
    } catch (e) {
      console.error('handleSaveManagerFinance error', e);
      showErrorMessage(t('خطا در ذخیره اطلاعات'));
    } finally {
      dialogSaving.value = false;
    }
  }

  async function handleSaveAnalysisLeadership(patch) {
    dialogSaving.value = true;
    try {
      const cleanPatch = {
        id: patch?.id,
        tahlil: patch?.tahlil ?? '',
        lead_drirection: patch?.lead_drirection ?? '',
      };

      await repo.UpdateInformation(cleanPatch.id, cleanPatch);
      await fetchAll();
      analysisLeadershipDialogOpen.value = false;
    } catch (e) {
      console.error('handleSaveAnalysisLeadership error', e);
      showErrorMessage(t('خطا در ذخیره اطلاعات'));
    } finally {
      dialogSaving.value = false;
    }
  }
  async function resetAllFilters() {
    repo.Search = '';
    searchField.value = null;
    searchValue.value = null;
    filterYear.value = null;
    filterMonth.value = null;
    activeStat.value = null;
    repo.page = 1;

    await router.replace({ path: '/app/informations', query: {} });
    await fetchAll();
  }
  /* ===== Delete ===== */
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
      if (typeof repo.DeleteInformation === 'function') {
        await repo.DeleteInformation(row.id);
      } else {
        showErrorMessage(t('حذف برای این اطلاع هنوز تنظیم نشده است'));
        return;
      }
      await fetchAll();
    } catch (e) {
      console.error('handleDeleteConfirmed error', e);
      showErrorMessage(t('خطا در حذف اطلاع'));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  /* ===== Peek ===== */
  const filters = ref({});
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      if (typeof repo.FetchInformationData === 'function') {
        await repo.FetchInformationData(id);
        detail.value =
          (repo.Information && Array.isArray(repo.Information)
            ? repo.Information[0]
            : repo.Information) || {};
      } else {
        await fetchAll();
        detail.value = allRows.value.find((r) => r.id === id) || {};
      }
    } finally {
      loading.value = false;
    }
  }

  function openPeek(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }
const printableHeaderOptions = computed(() =>
  (headersFT.value || headersFT || [])
    .filter((h) => h.key && h.key !== 'actions' && h.key !== 'map')
    .map((h) => ({
      title: h.title,
      key: h.key,
    }))
);

onMounted(() => {
  if (!selectedPrintColumns.value.length) {
    selectedPrintColumns.value = printableHeaderOptions.value.map((x) => x.key);
  }
});
async function printAll() {
  try {
    await repo.OpenInformationsPrintView({
      search: repo.Search,
      search_field: searchField.value,
      search_value: searchValue.value,
      year: filterYear.value,
      month: filterMonth.value,
      date_field: 'information_saved_date',
      stat: activeStat.value || 'resources',

      resource_id: relatedFilters.value?.resource_id,
      office_employee_id: relatedFilters.value?.office_employee_id,
      information_id: relatedFilters.value?.information_id,
      event_id: relatedFilters.value?.event_id,
      fifty_six_format_id: relatedFilters.value?.fifty_six_format_id,

      selected_columns: selectedPrintColumns.value,

      column_labels: Object.fromEntries(
        printableHeaderOptions.value.map((h) => [h.key, h.title])
      ),

      print_labels: {
        index: '#',
        record_count: t('تعداد ریکاردها'),
        no_data: t('د چاپ لپاره معلومات نشته.'),
      },

      locale: locale.value,
      title: t('چاپ اطلاعات'),
    });
  } catch (e) {
    console.error(e);
    showErrorMessage(t('چاپ ناموفق بود'));
  }
}
  /* ===== Navigation ===== */
  function goCreate() {
    router.push('/app/informationform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/informationform/${row.id}`);
  }

  function goPrint(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/informations/print/${row.id}`);
  }

  function goToResource(payload) {
    const row = getRow(payload);
    const id = row?.resource_id;
    if (!id) return;

    router.push({
      path: '/app/resources',
      query: {
        resource_id: String(id),
        open: 'peek',
        source: 'informations',
      },
    });
  }

  function goToOfficeEmployee(payload) {
    const row = getRow(payload);
    const id = row?.office_employee_id;
    if (!id) return;

    router.push({
      path: '/app/officeemployee',
      query: {
        office_employee_id: String(id),
        open: 'peek',
        source: 'informations',
      },
    });
  }
  function goToOfficeEmployeeInfo(payload) {
    const row = getRow(payload);
    const id = row?.office_employee_info_id;
    if (!id) return;

    router.push({
      path: '/app/officeemployee',
      query: {
        office_employee_info_id: String(id),
        open: 'peek',
        source: 'informations',
      },
    });
  }

  async function clearRelatedFilter() {
    repo.page = 1;
    await router.replace({
      path: '/app/informations',
      query: {
        ...route.query,
        information_id: undefined,
      },
    });
    await fetchAll();
  }
  async function clearSearch() {
    repo.Search = '';
    searchField.value = null;
    searchValue.value = null;
    repo.page = 1;

    await fetchAll();
  }
  async function downloadExcel() {
    try {
      excelDownloading.value = true;
      excelDownloadDialog.value = true;
      excelDownloadProgress.value = 0;

      await repo.DownloadInformationsExcel({
        search: repo.Search || '',
        search_field: searchField.value,
        search_value: searchValue.value,
        year: filterYear.value,
        month: filterMonth.value,
        date_field: 'information_saved_date',

        stat: relatedFilters.value.stat || activeStat.value,
        type: relatedFilters.value.type,

        date: relatedFilters.value.date,
        date_from: relatedFilters.value.date_from,
        date_to: relatedFilters.value.date_to,
        from_date: relatedFilters.value.from_date,
        to_date: relatedFilters.value.to_date,

        information_id: relatedFilters.value.information_id,
        resource_id: relatedFilters.value.resource_id,
        office_employee_id: relatedFilters.value.office_employee_id,
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
      showErrorMessage(t('خطا در دانلود اکسل'));
    } finally {
      setTimeout(() => {
        excelDownloadDialog.value = false;
        excelDownloading.value = false;
        excelDownloadProgress.value = 0;
      }, 400);
    }
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
  watch(
    () => route.query,
    async () => {
      repo.page = 1;
      await fetchAll();
    },
    { deep: true }
  );

  /* ===== Init ===== */
  onMounted(async () => {
    await fetchYearItemsFromDb();
    await fetchMonthItemsFromDb(null);
    await fetchAll();
    await financeRepo.FetchAccountsData();
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

  .stat-card.active-stat {
    border-color: currentColor;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(240, 240, 240, 0.9) 100%);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
  }

  .stat-card.active-stat .v-avatar {
    background: rgba(255, 255, 255, 0.9) !important;
  }

  .cursor-pointer {
    cursor: pointer;
  }

  :deep(.v-field-label),
  :deep(.v-label),
  :deep(input::placeholder) {
    font-size: 12px !important;
    line-height: 1.2 !important;
    color: #555 !important;
  }

  :deep(.v-field__input) {
    padding: 2px 6px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
  }

  :deep(.v-data-table) {
    border-radius: 8px;
  }

  :deep(.v-data-table__wrapper) {
    border-radius: 8px;
  }

  :deep(.v-data-table__tr:hover) {
    background-color: rgba(33, 150, 243, 0.04) !important;
  }

  :deep(.v-btn--icon) {
    transition: all 0.2s ease;
  }

  :deep(.v-btn--icon:hover) {
    transform: scale(1.1);
  }

  .fontfamily {
    font-family: var(--app-font) !important;
  }
</style>
