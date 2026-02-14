<!-- src/views/schools/Schools.vue -->
<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('مکتب ها')" :subtitle="t('فهرست، جستجو و مدیریت مکتب ها')">
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <!-- 🔍 Search + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Field -->
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو...')"
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
      </v-row>

      <!-- 📊 Stats cards -->
      <v-row class="mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- All schools -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'all' }"
            @click="cardFilter = 'all'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-school</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('مکتب ها') }}</div>
                <div class="stat-card-subtitle">{{ t('مجموع') }}: {{ stats.total }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With coordinates -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withCoordinates' }"
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

        <!-- With female students -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withFemaleStudents' }"
            @click="cardFilter = 'withFemaleStudents'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-human-female</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('شاگردان دختر') }}</div>
                <div class="stat-card-subtitle">
                  {{ t('مکتب های دارای شاگردان دختر') }}: {{ stats.withFemaleStudents }}
                </div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With female teachers -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withFemaleTeachers' }"
            @click="cardFilter = 'withFemaleTeachers'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-human-female-female</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('معلمان زن') }}</div>
                <div class="stat-card-subtitle">
                  {{ t('مکتب های دارای معلمان زن') }}: {{ stats.withFemaleTeachers }}
                </div>
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
        title-key="school_name"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
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
      class="mb-20 mx-2"
      :enableEdit="true"
      :enablePeek="true"
      @edit="goEdit"
      @peek="openPeek"
    >
      <!-- 🔗 RESOURCE CODE CLICKABLE WITH TOOLTIP -->
      <template #cell-resource_code="{ row }">
        <v-tooltip :text="t('رفتن به صفحه منابع')" location="top">
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              v-if="row.resource_code"
              variant="tonal"
              color="primary"
              append-icon="mdi-account"
              size="default"
              @click.stop="goToResource(row)"
            >
              {{ row.resource_code }}
            </v-btn>
            <span v-else>-</span>
          </template>
        </v-tooltip>
      </template>
      <!-- 🗺 MAP COLUMN -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.school_name"
          :subtitle="row.province"
          type="schools"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده مکتب')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش مکتب')" location="top">
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

          <!-- حذف -->
          <v-tooltip :text="t('حذف مکتب')" location="top">
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

    <!-- 🗑 Delete Confirm Dialog -->
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
  import { ref, computed, onMounted, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRouter } from 'vue-router';

  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';

  import { useSchoolRepository } from '@/stores/SchoolRepository';

  const router = useRouter();
  const repo = useSchoolRepository();

  /* i18n / RTL */
  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* Snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (m) => {
    errorMessage.value = m;
    showError.value = true;
  };

  /* Helpers */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* Data from store */
  const rowsFT = computed(() => {
    const list = repo.Schools || [];
    if (Array.isArray(list)) return list;
    if (Array.isArray(list.data)) return list.data;
    return [];
  });
  /* 🔗 go to Resources page for this resource_code */
  function goToResource(row) {
    if (!row?.resource_code) return;
    router.push(`/app/resources/${row.resource_code}`);
  }
  /* Filter card state + stats */
  const cardFilter = ref('all');

  const stats = computed(() => {
    const rows = rowsFT.value || [];
    const total = rows.length;
    const withCoordinates = rows.filter(
      (r) => r && r.coordinates && String(r.coordinates).trim() !== ''
    ).length;
    const withFemaleStudents = rows.filter(
      (r) => Number(r?.female_students_quantity || 0) > 0
    ).length;
    const withFemaleTeachers = rows.filter(
      (r) => Number(r?.female_teachers_quantity || 0) > 0
    ).length;

    return { total, withCoordinates, withFemaleStudents, withFemaleTeachers };
  });

  const filteredRowsFT = computed(() => {
    const base = rowsFT.value || [];
    switch (cardFilter.value) {
      case 'withCoordinates':
        return base.filter((r) => r && r.coordinates && String(r.coordinates).trim() !== '');
      case 'withFemaleStudents':
        return base.filter((r) => Number(r?.female_students_quantity || 0) > 0);
      case 'withFemaleTeachers':
        return base.filter((r) => Number(r?.female_teachers_quantity || 0) > 0);
      default:
        return base;
    }
  });

  /* Headers */
  const headersFT = computed(() => [
    { title: t('نام مکتب'), key: 'school_name' },
    { title: t('نوع مکتب'), key: 'school_type' },

    { title: t('نام مدیر'), key: 'principle_name' },
    { title: t('نام پدر مدیر'), key: 'principle_f_name' },
    { title: t('شماره مدیر'), key: 'principle_phone' },

    { title: t('معلمان مرد'), key: 'male_teachers_quantity' },
    { title: t('معلمان زن'), key: 'female_teachers_quantity' },
    { title: t('شاگردان پسر'), key: 'male_students_quantity' },
    { title: t('شاگردان دختر'), key: 'female_students_quantity' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی/ناحیه'), key: 'district' },
    { title: t('قریه/ناحیه کوچک'), key: 'village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('یادداشت'), key: 'note' },

  ]);

  /* Top filter groups */
  const topGroups = computed(() => [
    { title: t('مشخصات مکتب'), keys: ['school_name', 'school_type'] },
    { title: t('مدیر'), keys: ['principle_name', 'principle_f_name', 'principle_phone'] },
    {
      title: t('کارمندان/شاگردان'),
      keys: [
        'male_teachers_quantity',
        'female_teachers_quantity',
        'male_students_quantity',
        'female_students_quantity',
      ],
    },
    { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village'] },
    { title: t('موقعیت/سرک/مختصات'), keys: ['specific_place', 'street_number', 'coordinates'] },
    {
      title: t('منبع/یادداشت'),
      keys: ['resource_code', 'resource_phone', 'note', 'Province_addr'],
    },
  ]);

  const filters = ref({});

  /* Peek drawer */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loadingOne.value = true;
    try {
      if (typeof repo.FetchSchoolData === 'function') {
        await repo.FetchSchoolData(id);
        const single = Array.isArray(repo.School) ? repo.School[0] : repo.School;
        detail.value = single || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات مکتب'));
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

  /* Navigation */
  function goCreate() {
    router.push('/app/schools/form');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/schools/form/${row.id}`);
  }

  /* Delete */
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
      await repo.DeleteSchool?.(row.id);
      await fetchAll();
      showErrorMessage(t('حذف شد'));
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در حذف'));
    } finally {
      deleteTarget.value = null;
    }
  }

  /* Fetch all */
  async function fetchAll() {
    try {
      await repo.FetchSchoolsData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search || '',
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست مکتب ها'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* Init */
  onMounted(async () => {
    await fetchAll();
  });
</script>

<style scoped>
  .page-bg {
    background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
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

  .ga-2 {
    gap: 8px;
  }
</style>
