<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('کارمندان مدیریت')" :subtitle="t('فهرست و جستجو')">
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
            :label="t('جستجو در کارمندان...')"
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
            v-if="auth.can('officeemployee.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('کارمند جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- 📊 STATS CARDS -->
    <v-row class="px-2 mt-2 d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- 1) Total employees -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="applyJobTypeFilter(null)">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ totalCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کل کارمندان') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-account-group</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 2) اداری -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="applyJobTypeFilter('اداری')">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-indigo fontfamily">
                {{ edariCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('اداری') }}
              </div>
            </div>
            <v-avatar color="indigo" variant="tonal" size="48">
              <v-icon color="indigo">mdi-briefcase-account</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 3) اوپراتیفی -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="applyJobTypeFilter('اوپراتیفی')">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-cyan fontfamily">
                {{ operatifiCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('اوپراتیفی') }}
              </div>
            </div>
            <v-avatar color="cyan" variant="tonal" size="48">
              <v-icon color="cyan">mdi-shield-account</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 4) Active employees (click -> /app/activeemployees) -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="goActiveEmployees">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ activeEmployeesCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کارمندان فعال') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-account-check</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 5) Inactive employees (click -> /app/inactiveemployees) -->
      <v-col cols="12" md="2" lg="2">
        <v-card class="stat-card" rounded="xl" @click="goInactiveEmployees">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-red fontfamily">
                {{ inactiveEmployeesCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کارمندان غیر فعال') }}
              </div>
            </div>
            <v-avatar color="red" variant="tonal" size="48">
              <v-icon color="red">mdi-account-off</v-icon>
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
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      :dateKeys="['join_date', 'left_date']"
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

      <!-- 🗺 Map cell -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="getRow(row).coordinates"
          :title="getRow(row).name"
          :subtitle="getRow(row).code_number"
          type="office_employee"
          :photo-url="getPhotoUrl(row)"
          :zoom="16"
        />
      </template>

      <!-- 🔢 Informations count -->
      <template #cell-informations_count="{ row }">
        <v-tooltip
          v-if="getRow(row).informations_count && getRow(row).informations_count > 0"
          text="تعداد معلومات این کارمند، کلیک کنید تا به صفحه معلومات بروید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="primary"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goEmployeeInformations(row)"
            >
              <v-icon size="18" class="mr-1">mdi-file-document-multiple</v-icon>
              {{ getRow(row).informations_count }}
            </v-btn>
          </template>
        </v-tooltip>
        <span v-else class="text-red">0</span>
      </template>

      <!-- 🔢 Events count -->
      <template #cell-events_count="{ row }">
        <v-tooltip
          v-if="getRow(row).events_count && getRow(row).events_count > 0"
          text="تعداد واقعات این کارمند، کلیک کنید تا به صفحه واقعات بروید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="deep-purple"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goEmployeeEvents(row)"
            >
              <v-icon size="18" class="mr-1">mdi-calendar-alert</v-icon>
              {{ getRow(row).events_count }}
            </v-btn>
          </template>
        </v-tooltip>
        <span v-else class="text-red">0</span>
      </template>
      <!-- ✅ Active resources count -->
      <template #cell-resources_active_count="{ row }">
        <v-tooltip
          v-if="getRow(row).resources_active_count && getRow(row).resources_active_count > 0"
          text="منابع فعال این کارمند — کلیک کنید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <div
              v-bind="tooltipProps"
              class="count-pill count-pill--active"
              role="button"
              tabindex="0"
              @click.stop="goActiveResources(row)"
              @keydown.enter.stop="goActiveResources(row)"
            >
              <v-icon size="18" class="mr-1">mdi-check-decagram</v-icon>
              <span class="count-pill__num">{{ getRow(row).resources_active_count }}</span>
            </div>
          </template>
        </v-tooltip>

        <span v-else class="text-red">0</span>
      </template>

      <!-- ✅ Inactive resources count -->
      <template #cell-resources_inactive_count="{ row }">
        <v-tooltip
          v-if="getRow(row).resources_inactive_count && getRow(row).resources_inactive_count > 0"
          text="منابع غیر فعال این کارمند — کلیک کنید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <div
              v-bind="tooltipProps"
              class="count-pill count-pill--inactive"
              role="button"
              tabindex="0"
              @click.stop="goInactiveResources(row)"
              @keydown.enter.stop="goInactiveResources(row)"
            >
              <v-icon size="18" class="mr-1">mdi-close-octagon</v-icon>
              <span class="count-pill__num">{{ getRow(row).resources_inactive_count }}</span>
            </div>
          </template>
        </v-tooltip>

        <span v-else class="text-red">0</span>
      </template>

      <!-- 🔢 Resources count -->
      <template #cell-resources_count="{ row }">
        <v-tooltip
          v-if="getRow(row).resources_count && getRow(row).resources_count > 0"
          text="تعداد منابع این کارمند، کلیک کنید تا به صفحه منابع بروید"
          location="top"
        >
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              color="teal"
              variant="tonal"
              size="default"
              class="text-none"
              @click.stop="goEmployeeResources(row)"
            >
              <v-icon size="18" class="mr-1">mdi-database</v-icon>
              {{ getRow(row).resources_count }}
            </v-btn>
          </template>
        </v-tooltip>
        <span v-else class="text-red">0</span>
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip text="مشاهده کارمند" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" @click.stop="openPeek(item)" color="blue">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip text="تغییر جزیات کارمند" location="top">
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

          <!-- دانلود اسناد -->
          <v-tooltip text="دانلود اسناد مربوط به کارمند" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadEmployeeDocs(item)"
                color="green-darken-2"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <!-- حذف -->
          <v-tooltip text="حذف کارمند" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('officeemployee.delete')"
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
          :title="t('پیش نمایش عکس کارمند')"
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

  import { useEmployeOfficeRepository } from '../../stores/EmployeeRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';

  const router = useRouter();
  const { t, locale } = useI18n();
  const repo = useEmployeOfficeRepository();
  const auth = useLoginRepository();

  /* ===== RTL ===== */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  /* ===== Helpers: unwrap + fix URL ===== */
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

  /* ===== Data & stats ===== */
  const rowsFT = computed(() => repo.Employees || []);
  const totalCount = computed(() => rowsFT.value.length);

  const edariCount = computed(
    () => rowsFT.value.filter((r) => String(r.job_type || '').trim() === 'اداری').length
  );

  const operatifiCount = computed(
    () => rowsFT.value.filter((r) => String(r.job_type || '').trim() === 'اوپراتیفی').length
  );

  const activeEmployeesCount = computed(
    () => rowsFT.value.filter((r) => String(r.activity || '').trim() === 'فعال').length
  );

  const inactiveEmployeesCount = computed(
    () => rowsFT.value.filter((r) => String(r.activity || '').trim() === 'غیر فعال').length
  );

  /* ===== Headers for FilterableTable ===== */
  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },
    // Counts
    { title: t('اطلاعات'), key: 'informations_count', sortable: false },
    { title: t('حوادث'), key: 'events_count', sortable: false },
    { title: t('منابع'), key: 'resources_count', sortable: false },
    { title: t('تعداد منابع فعال'), key: 'resources_active_count', sortable: false },
    { title: t('تعداد منابع غیر فعال'), key: 'resources_inactive_count', sortable: false },

    // Identity
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('کود نمبر'), key: 'code_number' },

    // Rank/levels
    { title: t('نوعیت وظیفه'), key: 'job_type' },
    { title: t('بست'), key: 'bast' },
    { title: t('رتبه/سطح'), key: 'level' },

    // Background
    { title: t('سابقه جهاد'), key: 'history_jahad' },
    { title: t('دلګی/مشری'), key: 'dilgay_mesher' },
    { title: t('مواقف جهادی'), key: 'jahadi_mawaqaf' },
    { title: t('مسئولیت جهاد'), key: 'jahad_time_responsibility' },
    { title: t('شماره تذکره'), key: 'tazkira' },
    { title: t('محل جهاد'), key: 'jahad_place' },

    // Addresses
    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },
    { title: t('محل مشخص فعلی'), key: 'now_specific_place' },
    { title: t('نمبر سرک'), key: 'now_street_number' },
    { title: t('نمبر خانه'), key: 'now_home_number' },

    // Job/org
    { title: t('وظیفه مشخص'), key: 'specific_job' },
    { title: t('قسم'), key: 'qisem' },
    { title: t('زون'), key: 'zone' },
    { title: t('حوزه'), key: 'hawza' },

    // Status & education
    { title: t('حالت مدنی'), key: 'madani_halat' },
    { title: t('فعالیت'), key: 'activity' },
    { title: t('تعلیمات دینی'), key: 'relegy_study' },
    { title: t('تعلیمات عصری'), key: 'new_study' },
    { title: t('پوهنحی/رشته'), key: 'faculty' },
    { title: t('نظامی'), key: 'nizami' },
    { title: t('معلومات سلاح'), key: 'gun_knowledge' },

    // Demographic
    { title: t('سن'), key: 'age' },
    { title: t('مهارت‌های مشخص'), key: 'specific_skills' },
    { title: t('زبان'), key: 'language' },
    { title: t('قوم/ملیت'), key: 'nation' },

    // Contact & misc
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('تاریخ آمدن'), key: 'join_date' },
    { title: t('تاریخ رفتن'), key: 'left_date' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('ملاحظات'), key: 'note', width: '450' },

    // Map
    { title: t('نقشه'), key: 'map', sortable: false },
  ]);
  function applyJobTypeFilter(val) {
    if (!val) {
      // Total: clear job_type filter
      const next = { ...(filters.value || {}) };
      delete next.job_type;
      filters.value = next;
      return;
    }

    filters.value = {
      ...(filters.value || {}),
      job_type: val, // must match headersFT key: 'job_type'
    };
  }

  /* ===== Top filter groups ===== */
  const topGroups = computed(() => [
    {
      title: t('معرفت'),
      keys: [
        'name',
        'father_name',
        'grand_father_name',
        'nick_name',
        'code_number',
        'bast',
        'level',
        'history_jahad',
        'dilgay_mehser',
        'jahadi_mawaqaf',
        'jahad_time_responsibility',
        'tazkira',
        'dilgay_mesher',
      ],
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
    {
      title: t('تحصیلات'),
      keys: ['relegy_study', 'new_study', 'faculty', 'nizami', 'gun_knowledge'],
    },
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
      if (typeof repo.FetchEmployeeData === 'function') {
        await repo.FetchEmployeeData(id);
        const raw =
          repo.Employee && Array.isArray(repo.Employee) ? repo.Employee[0] : repo.Employee;
        detail.value = raw || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error('FetchOne employee error:', e);
      showErrorMessage(t('خطا در دریافت معلومات کارمند'));
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
      if (typeof repo.FetchEmployeesData === 'function') {
        await repo.FetchEmployeesData({
          page: repo.page,
          perPage: repo.perPage,
          search: repo.Search,
        });
      } else {
        await repo.FetchEmployeesData({ page: 1, itemsPerPage: 100000 });
      }
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت فهرست کارمندان'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* ===== Navigation ===== */
  function goCreate() {
    router.push('/app/officeemployeeform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    const id = row?.id;
    if (!id) return;
    router.push(`/app/officeemployeeform/${id}`);
  }

  function goEmployeeInformations(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/informations',
      query: { office_employee_id: row.id },
    });
  }

  function goEmployeeEvents(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/events',
      query: { office_employee_id: row.id },
    });
  }

  function goEmployeeResources(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/resources',
      query: { office_employee_id: row.id },
    });
  }
  function goActiveResources(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/activeresource',
      query: { office_employee_id: row.id },
    });
  }

  function goInactiveResources(rowPayload) {
    const row = getRow(rowPayload);
    router.push({
      path: '/app/inactiveresource',
      query: { office_employee_id: row.id },
    });
  }

  async function downloadEmployeeDocs(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    try {
      if (typeof repo.DownloadOfficeEmployeeZip === 'function') {
        await repo.DownloadOfficeEmployeeZip(row.id);
      } else {
        console.warn('DownloadOfficeEmployeeZip not implemented on repo');
        showErrorMessage(t('دانلود اسناد برای این کارمند هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error('downloadEmployeeDocs error', e);
      showErrorMessage(t('خطا در دانلود اسناد کارمند'));
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
      if (typeof repo.DeleteEmployee === 'function') {
        await repo.DeleteEmployee(row.id);
      } else {
        console.warn('DeleteEmployee not implemented on repo');
        showErrorMessage(t('حذف برای این کارمند هنوز تنظیم نشده است'));
        return;
      }

      await fetchAll();
    } catch (e) {
      console.error('handleDeleteConfirmed error', e);
      showErrorMessage(t('خطا در حذف کارمند'));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  function goActiveEmployees() {
    router.push('/app/activeemployees');
  }

  function goInactiveEmployees() {
    router.push('/app/inactiveemployees');
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
</style>
