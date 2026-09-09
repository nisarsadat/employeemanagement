<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('ارزیابی امنیتی')"
      :subtitle="t('فهرست، جستجو و مدیریت ارزیابی های امنیتی')"
    >
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <v-container class="pa-0">
      <v-row class="align-center">

        <v-col cols="12">
          <SearchColumnFilter
            v-model:modelSearch="repo.Search"
            v-model:modelColumn="searchField"
            v-model:modelSelectedValue="selectedColumnValue"
            :headers="headersFT"
            :search-label="t('جستجو در ارزیابی های امنیتی...')"
            :column-label="t('ستون جستجو')"
            :all-columns-label="t('همه ستون‌ها')"
            :value-label="t('ارزش ستون')"
            :exclude-keys="['map']"
            :fetch-column-values="fetchColumnValues"
            @search="handleSearch"
            @clearSearch="clearSearch"
            @clearSelectedValue="clearSelectedValue"
          />
        </v-col>

        <v-col cols="12" sm="6" md="2">
          <v-btn
            color="primary"
            variant="flat"
            rounded="lg"
            block
            prepend-icon="mdi-filter"
            @click="handleSearch"
          >
            {{ t('فلتر') }}
          </v-btn>
        </v-col>

        <v-col cols="12" sm="6" md="2">
          <v-btn
            color="orange"
            rounded="lg"
            block
            prepend-icon="mdi-filter-remove"
            @click="clearAllFilters"
          >
            {{ t('ریست فلتر') }}
          </v-btn>
        </v-col>

        <v-col cols="12" sm="6" md="4">
          <v-btn
            v-if="auth.can('securityassessments.create')"
            color="primary"
            variant="flat"
            size="large"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('جدید') }}
          </v-btn>
        </v-col>

        <v-col cols="12" sm="6" md="4">
          <v-btn
            color="success"
            variant="flat"
            size="large"
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
              <span
                v-if="index === 0"
                class="text-body-2"
              >
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
            prepend-icon="mdi-printer"
            @click="printAll"
          >
            {{ t('چاپ') }}
          </v-btn>
        </v-col>

      </v-row>

      <v-row
        class="mt-2 d-flex justify-between"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <v-col
          v-for="card in statCards"
          :key="card.value"
          cols="12"
          sm="6"
          md="2"
        >
          <v-card
            class="stat-card"
            :class="{
              'stat-card--active':
                cardFilter === card.value
            }"
            :elevation="
              cardFilter === card.value ? 6 : 1
            "
            @click="applyCardFilter(card.value)"
          >
            <div class="stat-card-body">

              <div class="stat-card-icon">
                <v-icon>
                  {{ card.icon }}
                </v-icon>
              </div>

              <div class="stat-card-text">

                <div class="stat-card-title">
                  {{ card.title }}
                </div>

                <div class="stat-card-subtitle">
                  {{ t('تعداد') }}:
                  {{ card.count }}
                </div>

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
        title-key="place_name"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        :formatter="formatDetailValue"
        @fetch="fetchOne"
      />
    </v-container>

    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="
        (value) => (filters = { ...value })
      "
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      class="mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >

      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.place_name"
          :subtitle="row.specific_place"
          type="meeting"
          :zoom="16"
        />
      </template>

      <template #cell-direction="{ row }">
        {{ directionLabel(row.direction) }}
      </template>

      <template #actions="{ item }">

        <div class="d-flex align-center ga-2">

          <v-tooltip
            :text="t('مشاهده ارزیابی امنیتی')"
            location="top"
          >
            <template #activator="{ props }">
              <v-icon
                v-bind="props"
                size="small"
                color="blue"
                @click.stop="openPeek(item)"
              >
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip
            :text="t('ویرایش ارزیابی امنیتی')"
            location="top"
          >
            <template #activator="{ props }">
              <v-icon
                v-if="
                  auth.can('securityassessments.update')
                "
                v-bind="props"
                size="small"
                color="amber-darken-2"
                @click.stop="goEdit(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip
            :text="t('حذف ارزیابی امنیتی')"
            location="top"
          >
            <template #activator="{ props }">
              <v-icon
                v-if="
                  auth.can('securityassessments.delete')
                "
                v-bind="props"
                size="small"
                color="red-darken-2"
                @click.stop="
                  openDeleteDialog(item)
                "
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
      :items-per-page="
        repo.itemsPerPage
      "
      :total-items="repo.totalItems"
      @update:page="
        handlePageChange
      "
      @update:items-per-page="
        handlePerPageChange
      "
    />

    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="
        handleDeleteConfirmed
      "
    />

    <v-snackbar
      v-model="showError"
      color="error"
      timeout="5000"
    >
      {{ errorMessage }}

      <template #actions>
        <v-btn
          variant="text"
          @click="showError = false"
        >
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>

  </v-container>
</template>

<script setup>
import {
  ref,
  computed,
  onMounted,
  watch,
} from 'vue';

import {
  useI18n,
} from 'vue-i18n';

import {
  useRouter,
} from 'vue-router';

import AppBar from '@/components/AppBar.vue';

import FilterableTable
  from '@/components/FilterableTable.vue';

import PeekButton
  from '@/components/PeekButton.vue';

import FlyToMapButton
  from '@/components/FlyToMapButton.vue';

import DeleteConfirmDialog
  from '@/components/DeleteConfirmDialog.vue';

import SearchColumnFilter
  from '@/components/SearchColumnFilter.vue';

import GlobalPagination
  from '@/components/GlobalPagination.vue';

import {
  useDatabaseRepository,
} from '@/stores/DatabaseRepository';

import {
  useLoginRepository,
} from '@/stores/LoginRepository';

import {
  usePageRefresh,
} from '@/composables/usePageRefresh';

import {
  useTablePrintExport,
} from '@/composables/useTablePrintExport';

const repo =
  useDatabaseRepository();

const auth =
  useLoginRepository();

const router =
  useRouter();

const {
  t,
  locale,
} = useI18n();

const isRtl = ref(
  locale.value === 'fa' || locale.value === 'ps'
);

watch(
  locale,
  (value) => {
    isRtl.value =
      value === 'fa' || value === 'ps';
  }
);

const searchField =
  ref(null);

const selectedColumnValue =
  ref(null);

const showError =
  ref(false);

const errorMessage =
  ref('');

function showErrorMessage(
  message
) {
  errorMessage.value =
    message;

  showError.value =
    true;
}

const getRow =
  (value) =>
    value && value.raw
      ? value.raw
      : value;

const normalizedSearchField =
  computed(() => {

    const value =
      searchField.value;

    if (!value)
      return null;

    if (
      typeof value ===
      'string'
    )
      return value;

    return (
      value.key ??
      value.value ??
      null
    );
  });

const normalizedSearchValue =
  computed(() => {

    const value =
      selectedColumnValue.value;

    if (
      value === null ||
      value === undefined ||
      value === ''
    )
      return null;

    if (
      typeof value ===
        'string' ||
      typeof value ===
        'number'
    )
      return value;

    return (
      value.value ??
      value.title ??
      null
    );
  });

const rowsFT =
  computed(() => {

    const list =
      repo.SecurityAssessments || [];

    if (
      Array.isArray(list)
    )
      return list;

    if (
      Array.isArray(
        list.data
      )
    )
      return list.data;

    return [];
  });

const cardFilter =
  ref('all');

const stats =
  computed(() => ({
    total:
      Number(
        repo
          .securityAssessmentCardCounts
          ?.total ?? 0
      ),

    north:
      Number(
        repo
          .securityAssessmentCardCounts
          ?.north ?? 0
      ),

    south:
      Number(
        repo
          .securityAssessmentCardCounts
          ?.south ?? 0
      ),

    east:
      Number(
        repo
          .securityAssessmentCardCounts
          ?.east ?? 0
      ),

    west:
      Number(
        repo
          .securityAssessmentCardCounts
          ?.west ?? 0
      ),
  }));

const statCards =
  computed(() => [
    {
      value: 'all',
      title:
        t('همه ارزیابی ها'),
      count:
        stats.value.total,
      icon:
        'mdi-shield-check-outline',
    },

    {
      value: 'north',
      title:
        t('شمال'),
      count:
        stats.value.north,
      icon:
        'mdi-compass-north',
    },

    {
      value: 'south',
      title:
        t('جنوب'),
      count:
        stats.value.south,
      icon:
        'mdi-compass-south',
    },

    {
      value: 'east',
      title:
        t('ختیځ'),
      count:
        stats.value.east,
      icon:
        'mdi-compass-east',
    },

    {
      value: 'west',
      title:
        t('لویدیځ'),
      count:
        stats.value.west,
      icon:
        'mdi-compass-west',
    },
  ]);

function applyCardFilter(
  value
) {
  cardFilter.value =
    value;

  repo.page = 1;

  fetchAll();
}

function directionLabel(
  value
) {
  const map = {
    north: t('شمال'),
    south: t('جنوب'),
    east: t('ختیځ'),
    west: t('لویدیځ'),
  };

  return map[value] ?? (value ?? '—');
}

function formatDetailValue(
  key,
  value
) {
  if (key === 'direction') {
    return directionLabel(value);
  }

  return value;
}

const headersFT =
  computed(() => [
    {
      title:
        t('نام محل'),
      key:
        'place_name',
    },

    {
      title:
        t('نام مسؤول / مالک'),
      key:
        'responsible_owner_name',
    },

    {
      title:
        t('ولایت'),
      key:
        'province',
    },

    {
      title:
        t('ناحیه / ولسوالی'),
      key:
        'district',
    },

    {
      title:
        t('محل مشخص'),
      key:
        'specific_place',
    },

    {
      title:
        t('کوردینات'),
      key:
        'coordinates',
    },

    {
      title:
        t('سمت'),
      key:
        'direction',
    },

    {
      title:
        t('تعداد روزانه مراجعین'),
      key:
        'daily_visitors_count',
    },

    {
      title:
        t('محلات آسیب‌رسان'),
      key:
        'risk_places',
    },

    {
      title:
        t('محلات آسیب‌پذیر'),
      key:
        'vulnerable_places',
    },

    {
      title:
        t('خلأهای امنیتی'),
      key:
        'security_gaps',
    },

    {
      title:
        t('نقاط ضعف'),
      key:
        'weak_points',
    },

    {
      title:
        t('حوادث امنیتی رخ‌داده'),
      key:
        'security_incidents',
    },

    {
      title:
        t('دروازه ورودی'),
      key:
        'entrance_gate',
    },

    {
      title:
        t('دروازه خروجی'),
      key:
        'exit_gate',
    },

    {
      title:
        t('دروازه اضطراری'),
      key:
        'emergency_gate',
    },

    {
      title:
        t('دروازه فرعی'),
      key:
        'secondary_gate',
    },

    {
      title:
        t('وضعیت بررسی فزیکی'),
      key:
        'physical_check_status',
    },

    {
      title:
        t('وضعیت بررسی تخنیکی'),
      key:
        'technical_check_status',
    },

    {
      title:
        t('کمره‌های امنیتی'),
      key:
        'security_cameras',
    },

    {
      title:
        t('تعداد محافظین'),
      key:
        'guards_count',
    },

    {
      title:
        t('تعداد برج‌های فعال'),
      key:
        'active_towers_count',
    },

    {
      title:
        t('تعداد قرارگاه‌های فعال'),
      key:
        'active_guard_posts_count',
    },

    {
      title:
        t('نام مسؤول امنیتی'),
      key:
        'security_responsible_name',
    },

    {
      title:
        t('شماره مسؤول امنیتی'),
      key:
        'security_responsible_phone',
    },

    {
      title:
        t('نقشه'),
      key:
        'map',
      sortable:
        false,
    },

    {
      title:
        t('سایر مشکلات'),
      key:
        'other_problems',
    },

    {
      title:
        t('ملاحظات'),
      key:
        'note',
    },
  ]);

const topGroups =
  computed(() => [
    {
      title:
        t('مشخصات و موقعیت'),

      keys: [
        'place_name',
        'responsible_owner_name',
      ],
    },

    {
      title:
        t('موقعیت'),

      keys: [
        'province',
        'district',
        'specific_place',
        'coordinates',
        'direction',
      ],
    },

    {
      title:
        t('ارزونه'),

      keys: [
        'daily_visitors_count',
        'risk_places',
        'vulnerable_places',
        'security_gaps',
        'weak_points',
        'security_incidents',
      ],
    },

    {
      title:
        t('دروازې او امنیتي چک'),

      keys: [
        'entrance_gate',
        'exit_gate',
        'emergency_gate',
        'secondary_gate',
        'physical_check_status',
        'technical_check_status',
        'security_cameras',
      ],
    },

    {
      title:
        t('امنیتي مسؤولین'),

      keys: [
        'guards_count',
        'active_towers_count',
        'active_guard_posts_count',
        'security_responsible_name',
        'security_responsible_phone',
      ],
    },
  ]);

const filters =
  ref({});

const expanded =
  ref(false);

const selectedId =
  ref(null);

const loadingOne =
  ref(false);

const detail =
  ref(null);

async function fetchOne(
  id
) {
  loadingOne.value =
    true;

  try {
    await repo
      .FetchSecurityAssessmentData(id);

    detail.value =
      repo.SecurityAssessment || {};
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t(
        'د امنیتي ارزونې د معلوماتو په ترلاسه کولو کې ستونزه رامنځته شوه'
      )
    );
  } finally {
    loadingOne.value =
      false;
  }
}

function openPeek(
  payload
) {
  const row =
    getRow(payload);

  if (!row?.id)
    return;

  selectedId.value =
    row.id;

  expanded.value =
    true;

  fetchOne(
    row.id
  );
}

function goCreate() {
  router.push(
    '/app/security-assessments/form'
  );
}

function goEdit(
  payload
) {
  const row =
    getRow(payload);

  if (!row?.id)
    return;

  router.push(
    `/app/security-assessments/form/${row.id}`
  );
}

const deleteDialog =
  ref(false);

const deleteTarget =
  ref(null);

function openDeleteDialog(
  payload
) {
  const row =
    getRow(payload);

  if (!row?.id)
    return;

  deleteTarget.value =
    row;

  deleteDialog.value =
    true;
}

async function handleDeleteConfirmed(
  target
) {
  const row =
    target ||
    deleteTarget.value;

  deleteDialog.value =
    false;

  if (!row?.id)
    return;

  try {
    await repo
      .DeleteSecurityAssessment(
        row.id
      );

    await fetchAll();
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t(
        'د امنیتي ارزونې په حذف کولو کې ستونزه رامنځته شوه'
      )
    );
  } finally {
    deleteTarget.value =
      null;
  }
}

async function fetchAll() {
  try {
    await repo
      .FetchSecurityAssessmentsData({
        stat:
          cardFilter.value,

        search:
          repo.Search,

        search_field:
          normalizedSearchField
            .value,

        search_value:
          normalizedSearchValue
            .value,

        date_field:
          'created_at',
      });
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t(
        'د امنیتي ارزونو د لېست په ترلاسه کولو کې ستونزه رامنځته شوه'
      )
    );
  }
}

async function fetchColumnValues(
  field,
  q = ''
) {
  try {
    const normalizedField =
      typeof field ===
      'string'
        ? field
        : field?.key ??
          field?.value ??
          null;

    if (
      !normalizedField
    )
      return [];

    const response =
      await repo
        .FetchSecurityAssessmentFilterOptions({
          field:
            normalizedField,

          q,
        });

    return (
      response
        ?.data
        ?.options ||
      []
    );
  } catch (error) {
    console.error(error);

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
  selectedColumnValue.value =
    null;

  repo.page = 1;

  await fetchAll();
}

async function clearAllFilters() {
  repo.Search = '';

  searchField.value =
    null;

  selectedColumnValue.value =
    null;

  cardFilter.value =
    'all';

  filters.value =
    {};

  repo.page = 1;

  expanded.value =
    false;

  selectedId.value =
    null;

  detail.value =
    null;

  await fetchAll();
}

function handlePageChange(
  value
) {
  repo.page =
    value;

  fetchAll();
}

function handlePerPageChange(
  value
) {
  repo.itemsPerPage =
    value;

  repo.page = 1;

  fetchAll();
}

async function downloadExcel() {
  try {
    await repo
      .DownloadSecurityAssessmentsExcel({
        stat:
          cardFilter.value,

        search:
          repo.Search,

        search_field:
          normalizedSearchField
            .value,

        search_value:
          normalizedSearchValue
            .value,

        date_field:
          'created_at',
      });
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t(
        'د اکسل فایل په ډاونلوډ کې ستونزه رامنځته شوه'
      )
    );
  }
}

const selectedPrintColumns =
  ref([]);

const printLabels =
  computed(() => ({
    print:
      t('چاپ'),

    record_count:
      t('تعداد ریکارد'),
  }));

const printableHeaderOptions =
  computed(() =>
    headersFT.value.filter(
      (header) =>
        header.key !==
        'map'
    )
  );

function buildDefaultPrintColumns() {
  return (
    printableHeaderOptions
      .value
      .map(
        (header) =>
          header.key
      )
  );
}

watch(
  printableHeaderOptions,

  (columns) => {
    if (
      !selectedPrintColumns
        .value.length
    ) {
      selectedPrintColumns
        .value =
        columns.map(
          (column) =>
            column.key
        );
    }
  },

  {
    immediate: true,
  }
);

const printRepo = {
  OpenResourcesPrintView:
    (payload) =>
      repo
        .OpenSecurityAssessmentsPrintView(
          payload
        ),
};

const {
  printAll,
} = useTablePrintExport({
  repo:
    printRepo,

  moduleKey:
    'security-assessments',

  getFilters:
    () => ({
      stat:
        cardFilter.value,

      search:
        repo.Search,

      search_field:
        normalizedSearchField
          .value,

      search_value:
        normalizedSearchValue
          .value,

      date_field:
        'created_at',
    }),

  getHeaders:
    () =>
      headersFT.value,

  getSelectedColumnKeys:
    () =>
      selectedPrintColumns
        .value,

  getTitle:
    () =>
      t('چاپ ارزیابی های امنیتی'),

  getPrintLabels:
    () =>
      printLabels.value,

  getLocale:
    () =>
      locale.value,

  defaultFileName:
    'security-assessments-print',
});

usePageRefresh(
  '/app/security-assessments',
  fetchAll
);

onMounted(
  async () => {
    await fetchAll();

    selectedPrintColumns
      .value =
      buildDefaultPrintColumns();
  }
);
</script>

<style scoped>
.ga-2 {
  gap: 8px;
}

.page-bg {
  background:
    radial-gradient(
      circle at top,
      #f2f5ff 0,
      #f5f5f5 40%,
      #fafafa 100%
    );

  font-family:
    inherit;

  min-height:
    100%;
}

.stat-card {
  cursor:
    pointer;

  border-radius:
    14px;

  border:
    1px solid
    rgba(
      33,
      150,
      243,
      0.08
    );

  background:
    #fff;

  transition:
    box-shadow 0.2s ease,
    transform 0.15s ease,
    border-color 0.2s ease,
    background-color 0.2s ease;
}

.stat-card:hover {
  box-shadow:
    0 6px 18px
    rgba(
      0,
      0,
      0,
      0.06
    );

  transform:
    translateY(-1px);

  border-color:
    rgba(
      33,
      150,
      243,
      0.35
    );
}

.stat-card--active {
  border-color:
    rgb(
      var(
        --v-theme-primary
      )
    );

  background:
    rgba(
      var(
        --v-theme-primary
      ),
      0.05
    );
}

.stat-card-body {
  min-height:
    88px;

  padding:
    14px;

  display:
    flex;

  align-items:
    center;

  gap:
    12px;
}

.stat-card-icon {
  width:
    42px;

  height:
    42px;

  border-radius:
    12px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  background:
    rgba(
      var(
        --v-theme-primary
      ),
      0.1
    );

  color:
    rgb(
      var(
        --v-theme-primary
      )
    );
}

.stat-card-title {
  font-weight:
    600;

  line-height:
    1.3;
}

.stat-card-subtitle {
  margin-top:
    4px;

  font-size:
    0.78rem;

  opacity:
    0.72;
}
</style>
