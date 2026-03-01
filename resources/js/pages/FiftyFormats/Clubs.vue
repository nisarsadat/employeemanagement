<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('کلب‌ها')" :subtitle="t('فهرست، جستجو و مدیریت کلب‌ها')">
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
            {{ t('کلب') }}
          </v-btn>
        </v-col>
      </v-row>

      <!-- 📊 Filter Cards (exactly like Fasad, adapted for clubs) -->
      <v-row class="mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- All -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'all' }"
            @click="cardFilter = 'all'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-group</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('کلب‌ها') }}</div>
                <div class="stat-card-subtitle">{{ t('مجموع') }}: {{ stats.total }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With coordinates (cordinate) -->
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

        <!-- With phone -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withPhone' }"
            @click="cardFilter = 'withPhone'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-phone</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای شماره تماس') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withPhone }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With resource -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withResource' }"
            @click="cardFilter = 'withResource'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-card-account-phone</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای منبع') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withResource }}</div>
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
        title-key="club_name"
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
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      class="mb-20 mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >
      <!-- 👤 picture cell with preview -->
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
      <!-- 🔗 RESOURCE CODE CLICKABLE (same hover style) -->
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

      <!-- 🗺 MAP CELL -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.cordinate"
          :title="row.name"
          :subtitle="row.club_name"
          type="clubs"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS (same idea as Fasad) -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده کلب')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش کلب')" location="top">
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
          <v-tooltip :text="t('حذف کلب')" location="top">
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
    <!-- 🖼 IMAGE PREVIEW DIALOG -->
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
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';

  import { useFasadRepository } from '@/stores/FasadRepository';

  const repo = useFasadRepository();
  const router = useRouter();

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
  /* IMAGE PREVIEW */
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
  /* Helpers */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* rows from store */
  const rowsFT = computed(() => {
    const list = repo.Clubs || [];
    if (Array.isArray(list)) return list;
    if (Array.isArray(list.data)) return list.data;
    return [];
  });

  /* 🔢 Filter cards state */
  const cardFilter = ref('all');

  const stats = computed(() => {
    const rows = rowsFT.value || [];
    const total = rows.length;

    const withCoordinates = rows.filter(
      (r) => r && r.cordinate && String(r.cordinate).trim() !== ''
    ).length;

    const withPhone = rows.filter(
      (r) => r && r.phone_number && String(r.phone_number).trim() !== ''
    ).length;

    const withResource = rows.filter(
      (r) => r && r.resource_code && String(r.resource_code).trim() !== ''
    ).length;

    return { total, withCoordinates, withPhone, withResource };
  });

  const filteredRowsFT = computed(() => {
    const base = rowsFT.value || [];
    switch (cardFilter.value) {
      case 'withCoordinates':
        return base.filter((r) => r && r.cordinate && String(r.cordinate).trim() !== '');
      case 'withPhone':
        return base.filter((r) => r && r.phone_number && String(r.phone_number).trim() !== '');
      case 'withResource':
        return base.filter((r) => r && r.resource_code && String(r.resource_code).trim() !== '');
      default:
        return base;
    }
  });

  /* TABLE HEADERS – list view (clubs fields) */
  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },
    { title: t('نام کلب'), key: 'club_name' },
    { title: t('نوع کلب'), key: 'club_type' },
    { title: t('مسوولص'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('وظیفه/موقعیت'), key: 'position' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('مختصات'), key: 'cordinate' },

    { title: t('شاگردان صبح'), key: 'am_students' },
    { title: t('شاگردان عصر'), key: 'pm_students' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('ملاحظات'), key: 'notes' },
  ]);

  /* TOP GROUPS (for FilterableTable) */
  const topGroups = computed(() => [
    {
      title: t('مشخصات'),
      keys: ['name', 'club_name', 'club_type', 'father_name', 'position', 'phone_number'],
    },
    {
      title: t('موقعیت'),
      keys: ['province', 'zone', 'district', 'specific_place', 'cordinate'],
    },
    { title: t('شاگردان'), keys: ['am_students', 'pm_students'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['Province_addr'] },
  ]);

  const filters = ref({});

  /* Peek */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loadingOne.value = true;
    try {
      await repo.FetchClubData?.(id);
      const single = repo.Club && Array.isArray(repo.Club) ? repo.Club[0] : repo.Club || {};
      detail.value = single || {};
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات کلب'));
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
    router.push('/app/clubform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/clubform/${row.id}`);
  }

  /* go to resource page by resource_code */
  function goToResource(row) {
    if (!row?.resource_code) return;
    router.push(`/app/resources/${row.resource_code}`);
  }

  /* delete with dialog */
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
      await repo.DeleteClub?.(row.id);
      await fetchAll();
      showErrorMessage(t('حذف شد'));
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
      await repo.FetchClubsData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search || '',
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست کلب‌ها'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* init */
  onMounted(async () => {
    await fetchAll();
  });
</script>

<style scoped>
  .ga-2 {
    gap: 8px;
  }

  /* Background same style */
  .page-bg {
    background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
    font-family: inherit;
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
</style>
