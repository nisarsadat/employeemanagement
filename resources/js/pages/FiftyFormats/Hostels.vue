<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('خواب گاه')" :subtitle="t('فهرست، جستجو و مدیریت هاستل‌ها')">
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <!-- 🔍 Search + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو در خواب گاه...')"
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
            {{ t('اضافه کردن خواب گاه جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- 👁 Peek -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loadingOne"
        title-key="hostel_name"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
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
      class="mb-20 mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >
      <!-- 🔗 Resource code clickable -->
      <template #cell-resource_code="{ row }">
        <v-tooltip :text="t('رفتن به منبع')" location="top">
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
          :coordinates="row.coordenates"
          :title="row.hostel_name"
          type="hostel"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTION ICONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip :text="t('مشاهده')" location="top">
            <template #activator="{ props: tip }">
              <v-icon v-bind="tip" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('ویرایش')" location="top">
            <template #activator="{ props: tip }">
              <v-icon v-bind="tip" size="small" color="amber-darken-2" @click.stop="goEdit(item)">
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('حذف خواب گاه')" location="top">
            <template #activator="{ props: tip }">
              <v-icon
                v-bind="tip"
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

    <!-- DELETE DIALOG -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />
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

  import { useBadmashanoRepository } from '@/stores/BadmashanoRepository';

  const repo = useBadmashanoRepository();
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

  /* 🔗 go to Resources page for this resource_code */
  function goToResource(row) {
    if (!row?.resource_code) return;
    router.push(`/app/resources/${row.resource_code}`);
  }

  /* Helpers */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* rows from store */
  const rowsFT = computed(() => {
    const list = repo.Hostels || [];
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
      (r) => r && r.coordenates && String(r.coordenates).trim() !== ''
    ).length;
    const withPhone = rows.filter(
      (r) => r && r.responsible_phone && String(r.responsible_phone).trim() !== ''
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
        return base.filter((r) => r && r.coordenates && String(r.coordenates).trim() !== '');
      case 'withPhone':
        return base.filter(
          (r) => r && r.responsible_phone && String(r.responsible_phone).trim() !== ''
        );
      case 'withResource':
        return base.filter((r) => r && r.resource_code && String(r.resource_code).trim() !== '');
      default:
        return base;
    }
  });

  /* TABLE HEADERS – WITH PICTURE + MAP */
  const headersFT = computed(() => [

    { title: t('نام خواب گاه'), key: 'hostel_name' },
    { title: t('نوع خواب گاه'), key: 'hostel_type' },

    { title: t('تعداد افراد'), key: 'people_quantity' },
    { title: t('نوع افراد'), key: 'people_type' },

    { title: t('مسؤول: نام'), key: 'responsible_name' },
    { title: t('مسؤول: نام پدر'), key: 'responsible_f_name' },
    { title: t('مسؤول: موقف'), key: 'responsible_position' },
    { title: t('مسؤول: شماره تماس'), key: 'responsible_phone' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه/ناحیه'), key: 'village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('مختصات'), key: 'coordenates' },

    { title: t('ولایت مربوطه'), key: 'Province_addr' },
    { title: t('یادداشت'), key: 'note', width: 300 },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

  /* TOP GROUPS */
  const topGroups = computed(() => [
    { title: t('خواب گاه'), keys: ['hostel_name', 'hostel_type'] },
    { title: t('افراد'), keys: ['people_quantity', 'people_type'] },
    {
      title: t('مسؤول'),
      keys: ['responsible_name', 'responsible_f_name', 'responsible_position', 'responsible_phone'],
    },
    { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village'] },
    {
      title: t('موقعیت'),
      keys: ['specific_place', 'coordenates', 'Province_addr'],
    },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
    { title: t('دیگر'), keys: ['note'] },
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
      if (typeof repo.FetchHostelData === 'function') {
        await repo.FetchHostelData(id);
        const single = Array.isArray(repo.Hostel) ? repo.Hostel[0] : repo.Hostel;
        detail.value = single || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات خواب گاه'));
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
    router.push('/app/hostelform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/hostelform/${row.id}`);
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
      await repo.DeleteHostel?.(row.id);
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
      await repo.FetchHostelsData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search || '',
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست خواب گاه ها'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* IMAGE PREVIEW helpers */
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

  /* init */
  onMounted(async () => {
    await fetchAll();
  });
</script>

<style scoped>
  .ga-2 {
    gap: 8px;
  }

  /* Background similar to other index pages */
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

  /* picture */
  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
</style>
