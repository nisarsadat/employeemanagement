<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <MeetingFinanceDialog
      v-model="financeDialogOpen"
      :id="selectedRow?.id"
      :account_id="selectedRow?.account_id ?? null"
      :message_to_finance="selectedRow?.message_to_finance ?? ''"
      :money_amount_words="selectedRow?.money_amount_words ?? ''"
      :account-items="accountItems"
      :is-rtl="isRtl"
      :loading="dialogSaving"
      @save="handleSaveMeetingFinance"
    />
    <AppBar
      :title="t('جلسات')"
      :subtitle="t('فهرست، جستجو و مدیریت جلسات')"
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
            :search-label="t('جستجو در جلسات...')"
            :column-label="t('ستون جستجو')"
            :all-columns-label="t('همه ستون‌ها')"
            :value-label="t('ارزش ستون')"
            :exclude-keys="['map', 'office_employees_display']"
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
            v-if="auth.can('meetings.create')"
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

    </v-container>

    <v-row class="px-2 mt-3">
      <v-col
        v-for="card in statCards"
        :key="card.key"
        cols="12"
        sm="6"
        md="3"
      >
        <v-card
          class="pa-3"
          rounded="lg"
          :color="activeStat === card.key ? 'primary' : undefined"
          :variant="activeStat === card.key ? 'flat' : 'outlined'"
          @click="setStat(card.key)"
        >
          <div class="text-caption">{{ card.title }}</div>
          <div class="text-h6 font-weight-bold">{{ card.count }}</div>
        </v-card>
      </v-col>
    </v-row>

    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loadingOne"
        title-key="meeting_title"
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
          :title="row.meeting_title"
          :subtitle="row.specific_place"
          type="meeting"
          :zoom="16"
        />
      </template>

      <template #cell-office_employees_display="{ row }">
        <div
          v-if="officeEmployeesOf(row).length"
          class="d-flex flex-column align-center ga-1"
        >
          <v-tooltip
            v-for="employee in officeEmployeesOf(row)"
            :key="employee.id"
            :text="t('برای رفتن به صفحه کارمند کلیک کنید')"
            location="top"
          >
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="deep-purple"
                variant="tonal"
                size="small"
                class="text-none"
                @click.stop="goOfficeEmployee(employee)"
              >
                <v-icon size="16" class="mr-1">mdi-account-badge</v-icon>
                {{ employeeLabel(employee) }}
              </v-btn>
            </template>
          </v-tooltip>
        </div>
        <span v-else>—</span>
      </template>

      <template #cell-finance_status="{ row }">
        <v-chip
          size="small"
          variant="tonal"
          :color="financeStatusColor(getRow(row).finance_status)"
        >
          {{ financeStatusLabel(getRow(row).finance_status) }}
        </v-chip>
      </template>

      <template #actions="{ item }">

        <div class="d-flex align-center ga-2">

          <v-tooltip
            :text="t('مشاهده جلسه')"
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
            :text="t('چاپ فورم')"
            location="top"
          >
            <template #activator="{ props }">
              <v-icon
                v-bind="props"
                size="small"
                color="red-darken-2"
                @click.stop="goFinalForm(item)"
              >
                mdi-file-document-outline
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip
            :text="t('ویرایش جلسه')"
            location="top"
          >
            <template #activator="{ props }">
              <v-icon
                v-if="
                  auth.can('meetings.update')
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
            :text="t('دانلود اسناد جلسه')"
            location="top"
          >
            <template #activator="{ props }">
              <v-icon
                v-bind="props"
                size="small"
                color="green-darken-2"
                :disabled="
                  !hasDocuments(item)
                "
                @click.stop="
                  downloadDocs(item)
                "
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip
            :text="t('مالی جلسه')"
            location="top"
          >
            <template #activator="{ props }">
              <v-icon
                v-if="auth.can('meetings.update')"
                v-bind="props"
                size="small"
                color="indigo"
                @click.stop="openMeetingFinanceDialog(item)"
              >
                mdi-cash-edit
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip
            :text="t('حذف جلسه')"
            location="top"
          >
            <template #activator="{ props }">
              <v-icon
                v-if="
                  auth.can('meetings.delete')
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
  useFinanceRepository,
} from '@/stores/FinanceRepository';

import MeetingFinanceDialog
  from './MeetingFinanceDialog.vue';

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

const financeRepo =
  useFinanceRepository();

const router =
  useRouter();

const {
  t,
  locale,
} = useI18n();

const isRtl =
  ref(
    locale.value === 'fa'
  );

watch(
  locale,
  (value) => {
    isRtl.value =
      value === 'fa';
  }
);

const searchField =
  ref(null);

const activeStat =
  ref('all');

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

const financeDialogOpen =
  ref(false);

const dialogSaving =
  ref(false);

const selectedRow =
  ref(null);

const accountItems =
  computed(() =>
    (financeRepo.accounts || []).map(
      (account) => ({
        ...account,
        display: account.name
          ? `${account.name} (${account.price ?? '0'})`
          : `${account.id}`,
      })
    )
  );

function financeStatusLabel(
  value
) {
  const status =
    String(value ?? '')
      .trim()
      .toLowerCase();

  if (
    status === 'paid' ||
    status === 'پرداخت'
  ) {
    return t('پرداخت شوی');
  }

  if (
    status === 'unpaid' ||
    status === 'pending' ||
    status === 'ناپرداخت' ||
    status === 'not_paid' ||
    status === ''
  ) {
    return t('د انتظار حالت');
  }

  return value || '—';
}

function financeStatusColor(
  value
) {
  const status =
    String(value ?? '')
      .trim()
      .toLowerCase();

  if (
    status === 'paid' ||
    status === 'پرداخت'
  ) {
    return 'green';
  }

  if (
    status === 'unpaid' ||
    status === 'pending' ||
    status === 'ناپرداخت' ||
    status === 'not_paid'
  ) {
    return 'amber';
  }

  return 'grey';
}

function openMeetingFinanceDialog(
  payload
) {
  selectedRow.value =
    getRow(payload);

  financeDialogOpen.value =
    true;
}

async function handleSaveMeetingFinance(
  patch
) {
  dialogSaving.value =
    true;

  try {
    await repo.UpdateMeetingFinance(
      patch.id,
      {
        account_id:
          patch.account_id,
        message_to_finance:
          patch.message_to_finance,
        money_amount_words:
          patch.money_amount_words,
      }
    );

    await fetchAll();

    financeDialogOpen.value =
      false;
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t('خطا در ذخیره اطلاعات')
    );
  } finally {
    dialogSaving.value =
      false;
  }
}

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
      repo.Meetings || [];

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

const headersFT =
  computed(() => [
    {
      title:
        t('عنوان جلسه'),
      key:
        'meeting_title',
    },

    {
      title:
        t('ارگان'),
      key:
        'organ',
    },

    {
      title:
        t('ولایت'),
      key:
        'province',
    },

    {
      title:
        t('ولسوالی'),
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
        t('تاریخ شروع'),
      key:
        'start_date',
    },

    {
      title:
        t('تاریخ ختم'),
      key:
        'end_date',
    },

    {
      title:
        t('مجموع زمان'),
      key:
        'total_time',
    },

    {
      title:
        t(
          'اشتراک کننده گان'
        ),
      key:
        'participant',
    },

    {
      title:
        t(
          'اشتراک کننده مسئول'
        ),
      key:
        'boss_participant',
    },

    {
      title:
        t(
          'تعداد کارمندان دفتر'
        ),
      key:
        'office_employee_count',
    },

    {
      title:
        t('ترسره کوونکي'),
      key:
        'office_employees_display',
      sortable:
        false,
    },

    {
      title:
        t('نام مسئول'),
      key:
        'leader_name',
    },

    {
      title:
        t(
          'فعالیت های انجام شده'
        ),
      key:
        'done_actions',
    },

    {
      title:
        t('حالات مشکوک'),
      key:
        'mashkok_situations',
    },

    {
      title:
        t('مشکلات'),
      key:
        'problems',
    },

    {
      title:
        t('تهیه کننده جلسه'),
      key:
        'meeting_supplier',
    },

    {
      title:
        t(
          'شماره تماس تهیه کننده'
        ),
      key:
        'meeting_supplier_phone',
    },

    {
      title:
        t(
          'مسئول امنیت جلسه'
        ),
      key:
        'meeting_amniyat_responsable',
    },

    {
      title:
        t(
          'شماره تماس مسئول امنیت'
        ),
      key:
        'meeting_amniyat_responsable_phone',
    },

    {
      title:
        t(
          'تعداد منسوبین امنیتی'
        ),
      key:
        'amniyat_mansobine_count',
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
        t('ملاحظات'),
      key:
        'note',
    },

    {
      title:
        t('د پیسو د پرداخت وضعیت'),
      key:
        'finance_status',
    },
  ]);

const topGroups =
  computed(() => [
    {
      title:
        t('مشخصات جلسه'),

      keys: [
        'meeting_title',
        'organ',
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
      ],
    },

    {
      title:
        t('زمان جلسه'),

      keys: [
        'start_date',
        'end_date',
        'total_time',
      ],
    },

    {
      title:
        t(
          'اشتراک کننده گان'
        ),

      keys: [
        'participant',
        'boss_participant',
        'office_employee_count',
        'office_employees_display',
        'leader_name',
      ],
    },

    {
      title:
        t('نتایج جلسه'),

      keys: [
        'done_actions',
        'mashkok_situations',
        'problems',
      ],
    },

    {
      title:
        t('تماس و امنیت'),

      keys: [
        'meeting_supplier',
        'meeting_supplier_phone',
        'meeting_amniyat_responsable',
        'meeting_amniyat_responsable_phone',
        'amniyat_mansobine_count',
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
      .FetchMeetingData(id);

    detail.value =
      repo.Meeting || {};
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t(
        'خطا در دریافت معلومات جلسه'
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
    '/app/meetings/form'
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
    `/app/meetings/form/${row.id}`
  );
}

function officeEmployeesOf(row) {
  const source = getRow(row) || {};
  const list = source.office_employees;

  return Array.isArray(list)
    ? list.filter((employee) => employee?.id)
    : [];
}

function employeeLabel(employee) {
  const name = String(employee?.name || '').trim();
  const code = String(employee?.code_number || '').trim();

  if (name && code) return `${name} (${code})`;

  return name || code || t('کارمند');
}

function goOfficeEmployee(employee) {
  const empId = employee?.id;
  if (!empId) return;

  router.push({
    path: '/app/officeemployee',
    query: {
      office_employee_id: empId,
      source: 'meetings',
    },
  });
}

function hasDocuments(
  payload
) {
  const row =
    getRow(payload);

  return (
    Array.isArray(
      row?.documents
    ) &&
    row.documents.length >
      0
  );
}

async function downloadDocs(
  payload
) {
  const row =
    getRow(payload);

  if (!row?.id)
    return;

  try {
    const base =
      String(
        row.meeting_title ||
          `meeting_${row.id}`
      )
        .toLowerCase()
        .replace(
          /\s+/g,
          '_'
        );

    await repo
      .DownloadMeetingZip(
        row.id,
        `${base}_documents.zip`
      );
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t(
        'خطا در دانلود اسناد'
      )
    );
  }
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
      .DeleteMeeting(
        row.id
      );

    await fetchAll();
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t('خطا در حذف')
    );
  } finally {
    deleteTarget.value =
      null;
  }
}

async function fetchAll() {
  try {
    await repo
      .FetchMeetingsData({
        search:
          repo.Search,

        search_field:
          normalizedSearchField
            .value,

        search_value:
          normalizedSearchValue
            .value,

        date_field:
          'start_date',

        stat:
          activeStat.value ||
          'all',
      });
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t(
        'خطا در دریافت فهرست جلسات'
      )
    );
  }
}

const statCards = computed(() => {
  const counts = repo.meetingCardCounts || {};
  return [
    { key: 'all', title: t('همه جلسات'), count: counts.total || 0 },
    { key: 'today', title: t('جلسات امروز'), count: counts.today || 0 },
    { key: 'suspicious', title: t('د مشکوکو حرکاتو درلودونکې غونډی'), count: counts.suspicious || 0 },
    { key: 'problems', title: t('د ستونزو درلودونکې غونډي'), count: counts.problems || 0 },
  ];
});

function setStat(key) {
  activeStat.value = key;
  fetchAll();
}

function goFinalForm(payload) {
  const row = getRow(payload);
  if (!row?.id) return;
  router.push(`/app/meetings/print/${row.id}`);
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
        .FetchMeetingFilterOptions({
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

function excelColumnLabels() {
  const labels = {
    id: t('ID'),
  };

  headersFT.value.forEach((header) => {
    if (header?.key) {
      labels[header.key] =
        header.title ?? header.key;
    }
  });

  return labels;
}

async function downloadExcel() {
  try {
    await repo
      .DownloadMeetingsExcel({
        search:
          repo.Search,

        search_field:
          normalizedSearchField
            .value,

        search_value:
          normalizedSearchValue
            .value,

        date_field:
          'start_date',

        locale:
          locale.value,

        column_labels:
          excelColumnLabels(),
      });
  } catch (error) {
    console.error(error);

    showErrorMessage(
      t(
        'خطا در دانلود اکسل'
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
        .OpenMeetingsPrintView(
          payload
        ),
};

const {
  printAll,
} = useTablePrintExport({
  repo:
    printRepo,

  moduleKey:
    'meetings',

  getFilters:
    () => ({
      search:
        repo.Search,

      search_field:
        normalizedSearchField
          .value,

      search_value:
        normalizedSearchValue
          .value,

      date_field:
        'start_date',
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
      t('چاپ جلسات'),

  getPrintLabels:
    () =>
      printLabels.value,

  getLocale:
    () =>
      locale.value,

  defaultFileName:
    'meetings-print',
});

usePageRefresh(
  '/app/meetings',
  fetchAll
);

onMounted(
  async () => {
    await financeRepo.FetchAccountsData({
      page: 1,
      perPage: 2000,
      search: '',
    });

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
</style>