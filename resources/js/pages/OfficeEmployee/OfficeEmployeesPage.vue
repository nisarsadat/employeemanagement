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
            class="bg-white"
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
    <v-row class="px-2 mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- Total employees -->
      <v-col cols="12" md="3" lg="3">
        <v-card class="stat-card">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary">
                {{ totalCount }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ t('کل کارمندان') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-account-group</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- With documents -->
      <v-col cols="12" md="3" lg="3">
        <v-card class="stat-card">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green">
                {{ withDocumentsCount }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ t('دارای اسناد ضمیمه') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-paperclip</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- With picture -->
      <v-col cols="12" md="3" lg="3">
        <v-card class="stat-card">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-purple">
                {{ withPictureCount }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ t('دارای عکس') }}
              </div>
            </div>
            <v-avatar color="purple" variant="tonal" size="48">
              <v-icon color="purple">mdi-account-box</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Today added -->
      <v-col cols="12" md="3" lg="3">
        <v-card class="stat-card">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-amber">
                {{ todayAddedCount }}
              </div>
              <div class="text-caption text-medium-emphasis">
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
        :item="detail"
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
        <v-avatar v-if="row.picture" size="36" class="cursor-pointer" @click="openPreview(row)">
          <v-img :src="row.picture" />
        </v-avatar>
        <v-avatar v-else size="36" color="grey-lighten-2">
          <v-icon size="20">mdi-account</v-icon>
        </v-avatar>
      </template>

      <!-- 🗺 Map cell -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.code_number"
          type="office_employee"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <!-- 🔢 Informations count cell (routing style like Informations page) -->
      <template #cell-informations_count="{ row }">
        <v-btn
          v-if="row.informations_count && row.informations_count > 0"
          color="primary"
          variant="tonal"
          size="default"
          class="text-none"
          @click.stop="goEmployeeInformations(row)"
        >
          <v-icon size="18" class="mr-1">mdi-file-document-multiple</v-icon>
          {{ row.informations_count }}
        </v-btn>
        <span v-else class="text-grey">—</span>
      </template>

      <!-- 🔢 Events count cell -->
      <template #cell-events_count="{ row }">
        <v-btn
          v-if="row.events_count && row.events_count > 0"
          color="deep-purple"
          variant="tonal"
          size="default"
          class="text-none"
          @click.stop="goEmployeeEvents(row)"
        >
          <v-icon size="18" class="mr-1">mdi-calendar-alert</v-icon>
          {{ row.events_count }}
        </v-btn>
        <span v-else class="text-grey">—</span>
      </template>

      <!-- 🔢 Resources count cell -->
      <template #cell-resources_count="{ row }">
        <v-btn
          v-if="row.resources_count && row.resources_count > 0"
          color="teal"
          variant="tonal"
          size="default"
          class="text-none"
          @click.stop="goEmployeeResources(row)"
        >
          <v-icon size="18" class="mr-1">mdi-database</v-icon>
          {{ row.resources_count }}
        </v-btn>
        <span v-else class="text-grey">—</span>
      </template>
      <!-- ⚙️ ACTIONS: 4 buttons same style as Informations page -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip text="مشاهده کارمند" location="top">
            <template v-slot:activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" @click.stop="openPeek(item)" color="blue">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip text="تغییر جزیات کارمند" location="top">
            <template v-slot:activator="{ props: tooltipProps }">
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
            <template v-slot:activator="{ props: tooltipProps }">
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
            <template v-slot:activator="{ props: tooltipProps }">
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
    <v-dialog v-model="previewOpen" max-width="520">
      <v-card>
        <v-toolbar
          density="compact"
          color="primary"
          :title="t('پیش نمایش عکس کارمند')"
          class="text-white"
        />
        <v-card-text class="d-flex justify-center">
          <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="480" max-height="420" cover />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn color="primary" variant="flat" @click="previewOpen = false">
            {{ t('بستن') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- 🔁 Reusable Delete Confirm Dialog (same style as Informations) -->
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

  /* ===== Helpers ===== */
  const rowsFT = computed(() => repo.Employees || []);

  const totalCount = computed(() => rowsFT.value.length);

  const withDocumentsCount = computed(
    () => rowsFT.value.filter((r) => Array.isArray(r.documents) && r.documents.length > 0).length
  );

  const withPictureCount = computed(() => rowsFT.value.filter((r) => !!r.picture).length);

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

  const todayAddedCount = computed(() => {
    const today = new Date();
    return rowsFT.value.filter((r) => isSameDate(r.created_at, today)).length;
  });

  /* ===== Headers for FilterableTable ===== */
  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },

    // Identity
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('کود نمبر'), key: 'code_number' },

    // Rank/levels
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
    { title: t('ملاحظات'), key: 'note', width: 400 },

    // Counts
    { title: t('اطلاعات'), key: 'informations_count', sortable: false },
    { title: t('حوادث'), key: 'events_count', sortable: false },
    { title: t('منابع'), key: 'resources_count', sortable: false },

    // Map
    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

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
    const row = rowPayload?.raw ?? rowPayload;
    if (!row?.picture) return;
    previewSrc.value = row.picture;
    previewOpen.value = true;
  }

  function hasDocuments(payload) {
    const row = payload?.raw ?? payload;
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  /* ===== Peek / single fetch ===== */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    if (!id) return;
    loadingOne.value = true;
    try {
      if (typeof repo.FetchEmployeeData === 'function') {
        await repo.FetchEmployeeData(id);

        let raw = repo.Employee && Array.isArray(repo.Employee) ? repo.Employee[0] : repo.Employee;
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
    const row = payload?.raw ?? payload;
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
    const row = payload?.raw ?? payload;
    const id = row?.id;
    if (!id) return;
    router.push(`/app/officeemployeeform/${id}`);
  }

  // 🔗 Go to informations for this employee
  function goEmployeeInformations(rowPayload) {
    const row = rowPayload?.raw ?? rowPayload;
    router.push({
      path: '/app/informations',
      query: { office_employee_id: row.id },
    });
  }

  // 🔗 Go to events for this employee
  function goEmployeeEvents(rowPayload) {
    const row = rowPayload?.raw ?? rowPayload;
    router.push({
      path: '/app/events',
      query: { office_employee_id: row.id },
    });
  }

  // 🔗 Go to resources for this employee
  function goEmployeeResources(rowPayload) {
    const row = rowPayload?.raw ?? rowPayload;
    router.push({
      path: '/app/resources',
      query: { office_employee_id: row.id },
    });
  }

  /* ===== Delete using DeleteConfirmDialog (same pattern as Informations) ===== */
  const deleteDialog = ref(false);
  const deleteTarget = ref(null);

  function openDeleteDialog(payload) {
    const row = payload?.raw ?? payload;
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

  .cursor-pointer {
    cursor: pointer;
  }

  .actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 6px;
    min-width: 100px !important;
  }

  .actions-grid,
  .stat-card,
  .text-caption {
    font-family: var(--app-font) !important;
  }

  .action-btn {
    min-width: 58px !important;
    height: 22px !important;
    font-size: 11px;
    line-height: 1;
    padding-inline: 6px !important;
    justify-content: center;
    text-wrap: nowrap;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
    transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
  }

  .action-btn :deep(.v-btn__content) {
    gap: 4px;
  }

  .action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.2);
    filter: brightness(1.05);
  }

  .action-btn--view {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff !important;
  }

  .action-btn--edit {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff !important;
  }

  .action-btn--download {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff !important;
  }

  .action-btn--delete {
    background: linear-gradient(135deg, #f97373, #ef4444);
    color: #fff !important;
  }
</style>
