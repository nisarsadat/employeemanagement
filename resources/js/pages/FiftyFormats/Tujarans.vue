<!-- src/views/tujaran/TujaransIndex.vue -->
<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="t('تجاران')"
      :subtitle="t('فهرست، جستجو و مدیریت تجاران')"
      @toggle-drawer="drawer = !drawer"
    >
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

      <!-- 📊 Filter Cards -->
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
                <div class="stat-card-title">{{ t('تمام تجاران') }}</div>
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

        <!-- With guards -->
        <v-col cols="12" sm="6" md="3">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withGuards' }"
            @click="cardFilter = 'withGuards'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-shield-account</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای محافظین') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withGuards }}</div>
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
        title-key="name"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        image-key="picture"
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
      class=" mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >
      <!-- 🖼 PICTURE CELL -->
      <template #cell-picture="{ row }">
        <div class="pic-cell">
          <v-avatar
            v-if="row.picture"
            size="34"
            class="pic-thumb"
            @click.stop="openImage(row.picture_url, row.name)"
          >
            <v-img :src="row.picture_url" cover />
          </v-avatar>

          <v-avatar v-else size="34" class="pic-thumb pic-thumb--empty">
            <v-icon size="18">mdi-image-off</v-icon>
          </v-avatar>
        </div>
      </template>
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
      <!-- 🗺 custom 'map' column -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.Province_addr"
          type="tujaran"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده تاجر')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش تاجر')" location="top">
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
          <v-tooltip :text="t('حذف تاجر')" location="top">
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

    <GlobalPagination
      :page="repo.page"
      :items-per-page="repo.itemsPerPage"
      :total-items="repo.totalItems"
      @update:page="handlePageChange"
      @update:items-per-page="handlePerPageChange"
    />

    <!-- 🗑 Delete Confirm Dialog -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />
    <!-- 🖼 IMAGE PREVIEW DIALOG -->
    <v-dialog v-model="imgDialog" max-width="920">
      <v-card rounded="xl">
        <v-card-title class="d-flex align-center justify-space-between">
          <div class="d-flex align-center ga-2">
            <v-icon>mdi-image</v-icon>
            <span class="text-subtitle-1 font-weight-bold">
              {{ imgTitle || t('پیش‌نمایش عکس') }}
            </span>
          </div>

          <v-btn icon variant="text" @click="imgDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider />

        <v-card-text>
          <div class="img-preview-wrap">
            <v-img v-if="imgSrc" :src="imgSrc" class="img-preview" cover />
            <div v-else class="muted">{{ t('عکس موجود نیست') }}</div>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- ❗ Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false"> {{ t('بستن') }} </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRouter } from 'vue-router';

  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';

  import { useBadmashanoRepository } from '@/stores/BadmashanoRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  const repo = useBadmashanoRepository();
  const router = useRouter();

  const drawer = ref(false);

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

  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* rows from store */
  const rowsFT = computed(() => {
    const list = repo.Tujarans || [];
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
      (r) => r && r.coordinates && String(r.coordinates).trim() !== ''
    ).length;
    const withPhone = rows.filter(
      (r) => r && r.phone_number && String(r.phone_number).trim() !== ''
    ).length;
    const withGuards = rows.filter(
      (r) =>
        r &&
        r.guard_guntity !== null &&
        r.guard_guntity !== undefined &&
        String(r.guard_guntity).trim() !== ''
    ).length;

    return { total, withCoordinates, withPhone, withGuards };
  });

  const filteredRowsFT = computed(() => {
    const base = rowsFT.value || [];
    switch (cardFilter.value) {
      case 'withCoordinates':
        return base.filter((r) => r && r.coordinates && String(r.coordinates).trim() !== '');
      case 'withPhone':
        return base.filter((r) => r && r.phone_number && String(r.phone_number).trim() !== '');
      case 'withGuards':
        return base.filter(
          (r) =>
            r &&
            r.guard_guntity !== null &&
            r.guard_guntity !== undefined &&
            String(r.guard_guntity).trim() !== ''
        );
      default:
        return base;
    }
  });
  /* 🔗 go to Resources page for this resource_code */
function goToResource(row) {
  const resourceId = row?.resource_id ?? row?.resource?.id ?? null;
  if (!resourceId) return;

  router.push({
    path: '/app/resources',
    query: {
      resource_id: resourceId,
      source: 'related',
    },
  });
}
  /* HEADERS */
  const headersFT = computed(() => [
    { title: t('عکس'), key: 'picture', sortable: false },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک فعلی'), key: 'now_street_number' },
    { title: t('شماره خانه فعلی'), key: 'now_home_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('اداره/ارگان'), key: 'organ' },

    { title: t('تعداد محافظین'), key: 'guard_guntity' },
    { title: t('متوجه تهدید'), key: 'tahdid_mutawaji' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('قسمت افراد'), key: 'people_type' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
    { title: t('نقشه'), key: 'map', sortable: false },
    { title: t('یادداشت'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    {
      title: t('موقعیت/خانه'),
      keys: ['now_specific_place', 'now_street_number', 'now_home_number', 'coordinates'],
    },
    { title: t('وظیفه/ارگان'), keys: ['old_job', 'now_job', 'organ'] },
    { title: t('تماس/افراد/محافظین'), keys: ['phone_number', 'people_type', 'guard_guntity'] },
    { title: t('منبع/یادداشت'), keys: ['resource_code', 'resource_phone', 'note'] },
  ]);
  const imgDialog = ref(false);
  const imgSrc = ref('');
  const imgTitle = ref('');

  function openImage(src, title) {
    imgSrc.value = src || '';
    imgTitle.value = title || '';
    imgDialog.value = true;
  }

  const filters = ref({});

  /* Peek */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loadingOne.value = true;
    try {
      if (typeof repo.FetchTujaranData === 'function') {
        await repo.FetchTujaranData(id);
        const single = Array.isArray(repo.Tujaran) ? repo.Tujaran[0] : repo.Tujaran;
        detail.value = single || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات تاجر'));
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
    router.push('/app/tujaransform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/tujaransform/${row.id}`);
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
      await repo.DeleteTujaran?.(row.id);
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
      await repo.FetchTujaransData?.();
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست تجاران'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    repo.page = 1;
    fetchAll();
  }

  function handlePageChange(page) {
    repo.page = page;
    fetchAll();
  }

  function handlePerPageChange(perPage) {
    repo.itemsPerPage = perPage;
    repo.page = 1;
    fetchAll();
  }
  usePageRefresh('/app/tujarans', fetchAll);

  /* init */
  onMounted(async () => {
    await fetchAll();
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
</style>