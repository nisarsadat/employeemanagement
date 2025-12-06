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
        <!-- Search Field -->
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو در منابع...')"
            density="comfortable"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            color="primary"
            rounded="lg"
            hide-details
            @keyup.enter="fetchAll"
            clearable
            @click:clear="clearSearch"
            class="bg-white search-field"
          />
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
      <!-- Total resources (click = clear quick filter) -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'all' }"
          @click="applyQuickFilter('all')"
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

      <!-- This week (employees added this week / last 7 days) -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'week' }"
          @click="applyQuickFilter('week')"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ thisWeekCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('این هفته ثبت شده') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-calendar-week</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- This month (employees added this month) -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'month' }"
          @click="applyQuickFilter('month')"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-purple fontfamily">
                {{ thisMonthCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('این ماه ثبت شده') }}
              </div>
            </div>
            <v-avatar color="purple" variant="tonal" size="48">
              <v-icon color="purple">mdi-calendar-month</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Today added -->
      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          :class="{ 'stat-card-active': quickFilter === 'today' }"
          @click="applyQuickFilter('today')"
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

    <!-- 👁 Peek (side/details) -->
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

    <!-- 📋 MAIN TABLE -->
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
      class="mb-20 mx-2"
    >
      <!-- 🖼 Picture cell -->
      <template #cell-picture="{ row }">
        <v-avatar size="36" class="cursor-pointer" @click="openPreview(row)">
          <v-img v-if="getPhotoUrl(row)" :src="getPhotoUrl(row)" cover />
          <v-icon v-else size="20">mdi-account</v-icon>
        </v-avatar>
      </template>

      <!-- 👤 Office employee cell with route + tooltip -->
      <template #cell-office_employee_id="{ row }">
        <div v-if="getOfficeEmployee(row)" class="d-flex flex-column align-start">
          <v-tooltip :text="t('برای رفتن به صفحه کارمندان لطفا کلیک کنید')" location="top">
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

      <!-- 📌 Events count -->
      <template #cell-events_count="{ row }">
        <div v-if="getRow(row)?.events_count > 0" class="d-flex flex-column align-start">
          <v-tooltip :text="t('برای دیدن واقعات این منبع کلیک کنید')" location="top">
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
        <span v-else class="text-xs text-grey">-</span>
      </template>

      <!-- 🧩 Informations count -->
      <template #cell-informations_count="{ row }">
        <div v-if="getRow(row)?.informations_count > 0" class="d-flex flex-column align-start">
          <v-tooltip :text="t('برای دیدن اطلاعات این منبع کلیک کنید')" location="top">
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
        <span v-else class="text-xs text-grey">-</span>
      </template>

      <!-- 📑 Fifty-six formats count -->
      <template #cell-fifty_six_formats_count="{ row }">
        <div v-if="getRow(row)?.fifty_six_formats_count > 0" class="d-flex flex-column align-start">
          <v-tooltip :text="t('برای دیدن ۵۶ فارمت های این منبع کلیک کنید')" location="top">
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
        <span v-else class="text-xs text-grey">-</span>
      </template>

      <!-- 🗺 Map cell -->
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

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip text="مشاهده منبع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" @click.stop="openPeek(item)" color="blue">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip text="تغییر جزیات منبع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                @click.stop="startEdit(item)"
                color="amber-darken-2"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <!-- دانلود اسناد منبع -->
          <v-tooltip text="دانلود اسناد مربوط به منبع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadResourceDocs(item)"
                color="green-darken-2"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <!-- حذف -->
          <v-tooltip text="حذف منبع" location="top">
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

    <!-- 🖼 BIG PREVIEW DIALOG -->
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

    <!-- 🔁 Reusable Delete Confirm Dialog -->
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
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';

  import AppBar from '@/components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';

  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';

  const router = useRouter();
  const { t, locale } = useI18n();
  const repo = useDatabaseRepository();
  const auth = useLoginRepository();

  /* ===== RTL ===== */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  /* ===== Helpers: unwrap + URL ===== */
  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  function normalizeUrl(url) {
    if (!url) return null;
    // fix duplicate /storage/storage/
    return url.replace('/storage/storage/', '/storage/');
  }

  function getPhotoUrl(payload) {
    const row = getRow(payload);
    if (!row) return null;
    return normalizeUrl(row.picture || row.picture_url);
  }

  function getOfficeEmployee(payload) {
    const row = getRow(payload);
    if (!row) return null;
    // assuming nested relation: office_employee: { id, name, code_number }
    return row.office_employee || null;
  }
  // 🔗 Go to events of this resource
  function goResourceEvents(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    // adjust path/query to your real events route
    router.push({ path: '/app/events', query: { resource_id: row.id } });
  }

  // 🔗 Go to informations of this resource
  function goResourceInformations(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    // adjust path/query to your real informations route
    router.push({ path: '/app/informations', query: { resource_id: row.id } });
  }

  // 🔗 Go to 56 formats of this resource
  function goResourceFiftySixFormats(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    // adjust path/query to your real 56-formats route
    router.push({ path: '/app/fifthyformatsroute', query: { resource_id: row.id } });
  }
  /* ===== Data for table ===== */
  const rowsFT = computed(() => repo.Resources || []);

  /* ===== Date helpers ===== */
  function isSameDate(dateValue, today) {
    if (!dateValue) return false;
    const d = new Date(dateValue);
    if (Number.isNaN(d.getTime())) return false;
    return (
      d.getFullYear() === today.getFullYear() &&
      d.getMonth() === today.getMonth() &&
      d.getDate() === today.getDate()
    );
  }

  function isWithinLastDays(dateValue, days) {
    if (!dateValue) return false;
    const d = new Date(dateValue);
    if (Number.isNaN(d.getTime())) return false;
    const today = new Date();
    const diffMs = today.getTime() - d.getTime();
    if (diffMs < 0) return false;
    const diffDays = diffMs / (1000 * 60 * 60 * 24);
    return diffDays <= days;
  }

  function isSameMonth(dateValue, today) {
    if (!dateValue) return false;
    const d = new Date(dateValue);
    if (Number.isNaN(d.getTime())) return false;
    return d.getFullYear() === today.getFullYear() && d.getMonth() === today.getMonth();
  }

  /* ===== Stats ===== */
  const totalCount = computed(() => rowsFT.value.length);

  const todayAddedCount = computed(() => {
    const today = new Date();
    return rowsFT.value.filter((r) => isSameDate(r.resource_saved_date || r.created_at, today))
      .length;
  });

  // "This week" = last 7 days
  const thisWeekCount = computed(
    () =>
      rowsFT.value.filter((r) => isWithinLastDays(r.resource_saved_date || r.created_at, 7)).length
  );

  // "This month" = same calendar month
  const thisMonthCount = computed(() => {
    const today = new Date();
    return rowsFT.value.filter((r) => isSameMonth(r.resource_saved_date || r.created_at, today))
      .length;
  });

  /* ===== Quick filter for stats cards ===== */
  const quickFilter = ref('all'); // 'all' | 'today' | 'week' | 'month'

  function applyQuickFilter(mode) {
    quickFilter.value = mode;
  }

  const displayedRowsFT = computed(() => {
    let data = rowsFT.value || [];
    const today = new Date();

    if (quickFilter.value === 'today') {
      data = data.filter((r) => isSameDate(r.resource_saved_date || r.created_at, today));
    } else if (quickFilter.value === 'week') {
      data = data.filter((r) => isWithinLastDays(r.resource_saved_date || r.created_at, 7));
    } else if (quickFilter.value === 'month') {
      data = data.filter((r) => isSameMonth(r.resource_saved_date || r.created_at, today));
    } else {
      // 'all' – no extra filter
    }

    return data;
  });

  /* ===== Headers for FilterableTable ===== */
  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },

    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('کود نمبر'), key: 'code_number' },
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

    { title: t('فعالیت'), key: 'activity' },
    { title: t('حالت مدنی'), key: 'halat_madani' },
    { title: t('عمر'), key: 'age' },
    { title: t('رتبه علمی'), key: 'elmi_level' },
    { title: t('فاکولته/دیپارتمنت'), key: 'faculty' },
    { title: t('مهارت‌های خاص'), key: 'specific_skills' },
    { title: t('دوره/آموزش'), key: 'train' },

    { title: t('تاریخ ثبت منبع'), key: 'resource_saved_date' },
    { title: t('نوع فعالیت منبع'), key: 'resource_activity_type' },

    // FK: office employee
    { title: t('کارمند مربوطه'), key: 'office_employee_id' },

    { title: t('رزیدنت مربوطه'), key: 'resedint_code' },
    { title: t('پوشش منبع'), key: 'resource_cover' },
    { title: t('زبان'), key: 'language' },
    { title: t('قوم'), key: 'nation' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('کوردینات'), key: 'coordinates' },
    { title: t('نوت'), key: 'note' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('تعداد واقعات'), key: 'events_count' },
    { title: t(' تعداد اطلاعات'), key: 'informations_count' },
    { title: t(' تعداد ۵۶ فارمت'), key: 'fifty_six_formats_count' },

    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

  /* ===== Top filter groups ===== */
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

  /* ===== Error Snackbar ===== */
  const showError = ref(false);
  const errorMessage = ref('');

  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  /* ===== Preview dialog ===== */
  const previewOpen = ref(false);
  const previewSrc = ref(null);

  function openPreview(rowPayload) {
    const url = getPhotoUrl(rowPayload);
    if (!url) return;
    previewSrc.value = url;
    previewOpen.value = true;
  }

  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  /* ===== Peek / single fetch ===== */
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

  /* ===== Fetch ALL ===== */
  async function fetchAll() {
    try {
      if (typeof repo.FetchResourcesData === 'function') {
        await repo.FetchResourcesData({
          page: 1,
          itemsPerPage: 100000,
          search: repo.Search,
        });
      }
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت فهرست منابع'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* ===== Navigation ===== */
  function goCreate() {
    router.push('/app/resourcesform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    const id = row?.id;
    if (!id) return;
    router.push(`/app/resourcesform/${id}`);
  }

  // 🔗 Go to office employee page from resource
  function goOfficeEmployee(payload) {
    const row = getRow(payload);
    if (!row) return;
    const empId = row.office_employee_id || row.office_employee?.id;
    if (!empId) return;

    router.push(`/app/officeemployeeform/${empId}`);
  }

  /* ===== Download documents ZIP ===== */
  async function downloadResourceDocs(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    try {
      if (typeof repo.DownloadResourceZip === 'function') {
        await repo.DownloadResourceZip(row.id);
      } else {
        console.warn('DownloadResourceZip not implemented on repo');
        showErrorMessage(t('دانلود اسناد برای این منبع هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error('downloadResourceDocs error', e);
      showErrorMessage(t('خطا در دانلود اسناد منبع'));
    }
  }

  /* ===== Delete using DeleteConfirmDialog ===== */
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

  /* ===== Init ===== */
  onMounted(async () => {
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

  /* 🔹 make search field height similar to large button */
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
</style>
