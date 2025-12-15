<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="t('زندانی های تسلیم شده')"
      :subtitle="t('فهرست، جستجو و مدیریت زندانی های تسلیم شده')"
    >
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <!-- 🔍 Search + Cards + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Field -->
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو در زندانی ها...')"
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
            v-if="auth.can('delevirycaptive.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('اضافه کردن زندانی جدید') }}
          </v-btn>
        </v-col>
      </v-row>

      <!-- 📊 Filter Cards -->
      <v-row class="mt-2" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- All -->
        <v-col cols="12" sm="6" md="3" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'all' }"
            elevation="cardFilter === 'all' ? 6 : 1"
            @click="cardFilter = 'all'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-account-group</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('همه زندانی های تسلیم شده') }}</div>
                <div class="stat-card-subtitle">{{ t('مجموع') }}: {{ stats.total }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With documents -->
        <v-col cols="12" sm="6" md="3" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withDocs' }"
            elevation="cardFilter === 'withDocs' ? 6 : 1"
            @click="cardFilter = 'withDocs'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-file-document-multiple</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('دارای اسناد') }}</div>
                <div class="stat-card-subtitle">{{ t('تعداد') }}: {{ stats.withDocs }}</div>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- With coordinates -->
        <v-col cols="12" sm="6" md="3" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withCoordinates' }"
            elevation="cardFilter === 'withCoordinates' ? 6 : 1"
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

        <!-- With criminal history -->
        <v-col cols="12" sm="6" md="3" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-card
            class="stat-card"
            :class="{ 'stat-card--active': cardFilter === 'withCriminalHistory' }"
            elevation="cardFilter === 'withCriminalHistory' ? 6 : 1"
            @click="cardFilter = 'withCriminalHistory'"
          >
            <div class="stat-card-body">
              <div class="stat-card-icon">
                <v-icon>mdi-gavel</v-icon>
              </div>
              <div class="stat-card-text">
                <div class="stat-card-title">{{ t('سابقه جرمی') }}</div>
                <div class="stat-card-subtitle">
                  {{ t('تعداد') }}: {{ stats.withCriminalHistory }}
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
        title-key="name"
        image-key="picture"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
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
      class="mb-20 mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >
      <template #cell-picture="{ row }">
        <!-- Has picture -->
        <div v-if="row.picture" class="pic-wrap">
          <v-avatar size="32" class="cursor-pointer" @click.stop="openPreview(row.picture)">
            <v-img :src="normalizePicture(row.picture)" :alt="row.name" cover />
          </v-avatar>
        </div>

        <!-- No picture → mdi-account -->
        <div v-else class="pic-wrap">
          <v-avatar size="32">
            <v-icon size="20">mdi-account</v-icon>
          </v-avatar>
        </div>
      </template>

      <!-- 🗺 Map cell -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.captive_place"
          type="deleviry_captive"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده زندانی تسلیم شده')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش زندانی تسلیم شده')" location="top">
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

          <!-- دانلود اسناد -->
          <v-tooltip :text="t('دانلود اسناد زندانی تسلیم شده')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-bind="tooltipProps"
                size="small"
                color="green-darken-2"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadDocs(item)"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <!-- حذف -->
          <v-tooltip :text="t('حذف زندانی تسلیم شده')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('delevirycaptive.delete')"
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

  import { useDatabaseRepository } from '@/stores/DatabaseRepository';
  import { useLoginRepository } from '@/stores/LoginRepository';

  const repo = useDatabaseRepository();
  const auth = useLoginRepository();
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

  /* helpers */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* rows from store */
  const rowsFT = computed(() => {
    const list = repo.CaptiveDeliveries || repo.DeleviryCaptives || [];
    if (Array.isArray(list)) return list;
    if (Array.isArray(list.data)) return list.data;
    return [];
  });

  /* 🔢 Filter cards state */
  const cardFilter = ref('all');

  const stats = computed(() => {
    const rows = rowsFT.value || [];
    const total = rows.length;
    const withDocs = rows.filter(
      (r) => Array.isArray(r.documents) && r.documents.length > 0
    ).length;
    const withCoordinates = rows.filter(
      (r) => r && r.coordinates && String(r.coordinates).trim() !== ''
    ).length;
    const withCriminalHistory = rows.filter(
      (r) => r && r.criminal_history && String(r.criminal_history).trim() !== ''
    ).length;

    return { total, withDocs, withCoordinates, withCriminalHistory };
  });

  const filteredRowsFT = computed(() => {
    const base = rowsFT.value || [];
    switch (cardFilter.value) {
      case 'withDocs':
        return base.filter((r) => Array.isArray(r.documents) && r.documents.length > 0);
      case 'withCoordinates':
        return base.filter((r) => r && r.coordinates && String(r.coordinates).trim() !== '');
      case 'withCriminalHistory':
        return base.filter(
          (r) => r && r.criminal_history && String(r.criminal_history).trim() !== ''
        );
      default:
        return base;
    }
  });

  /* TABLE HEADERS (resource/employee codes removed) */
  const headersFT = computed(() => [
    { title: t('تصویر'), key: 'picture', sortable: false },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدر کلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nickname' },

    { title: t('نمبر تذکره'), key: 'tazkira' },
    { title: t('عمر'), key: 'age' },
    { title: t('حالت مدنی'), key: 'halat_madani' },
    { title: t('سابقه جرمی'), key: 'criminal_history' },

    { title: t('وظیفه سابقه'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'new_job' },

    { title: t('زبان'), key: 'language' },
    { title: t('سطح علمی'), key: 'scientific_level' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/گذر اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/گذر فعلی'), key: 'now_village' },
    { title: t('علت اسارت'), key: 'captive_reason' },
    { title: t('نوع مظنونیت'), key: 'maznonyat_type' },
    { title: t('تسلیمی ورکونکی شخص'), key: 'taslimi_warkawonky_person' },
    { title: t('تسلیمی اخیستونکی شخص'), key: 'taslimi_akhistonky_person' },
    { title: t('تسلیمی اخیستونکی ارگان'), key: 'taslimi_akhistonky_organ' },

    { title: t('محل دستگیری'), key: 'captive_place' },
    { title: t('کوردینات محل دستگیری'), key: 'coordinates' },
    { title: t('مرجع دستگیری'), key: 'captive_marja' },
    { title: t('تاریخ دستگیری'), key: 'captive_date' },
    { title: t('ساعت دستگیری'), key: 'captive_hour' },

    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('حالت دستگیری'), key: 'captive_halat' },
    { title: t('وسایل و شواهد گرفته شده'), key: 'suspect_tools' },

    { title: t('ملاحظات'), key: 'notes' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

  /* TOP GROUPS (ارتباطی group removed) */
  const topGroups = computed(() => [
    { title: t('معرفت'), keys: ['name', 'father_name', 'grand_father_name', 'nickname'] },
    { title: t('اطلاعات شخصی'), keys: ['tazkira', 'age', 'halat_madani', 'criminal_history'] },
    { title: t('وظایف'), keys: ['old_job', 'new_job'] },
    { title: t('مهارت‌ها/زبان'), keys: ['language', 'scientific_level'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
    {
      title: t('جزئیات دستگیری'),
      keys: ['captive_place', 'coordinates', 'captive_marja', 'captive_date', 'captive_hour'],
    },
    {
      title: t('مظنون/تماس'),
      keys: [
        ,
        'phone_number',
        'captive_halat',
        'suspect_tools',
        'maznonyat_type',
        'captive_reason',
        'taslimi_warkawonky_person',
        'taslimi_akhistonky_person',
        'taslimi_akhistonky_organ',
      ],
    },
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
      if (typeof repo.FetchCaptiveDeliveryData === 'function') {
        await repo.FetchCaptiveDeliveryData(id);
        detail.value =
          (Array.isArray(repo.CaptiveDelivery) ? repo.CaptiveDelivery[0] : repo.CaptiveDelivery) ||
          {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات زندانی'));
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
    router.push('/app/delevirycaptiveform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/delevirycaptiveform/${row.id}`);
  }

  /* documents */
  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  async function downloadDocs(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    try {
      const base = (row.name || 'documents').toLowerCase().replace(/\s+/g, '_');
      await repo.DownloadCaptiveDeliveryZip(row.id, `${base}_documents.zip`);
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دانلود اسناد'));
    }
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
      await repo.DeleteCaptiveDelivery(row.id);
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
      await repo.FetchCaptiveDeliveriesData({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search,
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست زندانی ها'));
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

    // keep blob/data
    if (clean.startsWith('blob:') || clean.startsWith('data:')) return clean;

    // fix escaped slashes
    clean = clean.replace(/\\\//g, '/');

    // absolute
    if (clean.startsWith('http://') || clean.startsWith('https://')) return clean;

    // root-relative
    if (clean.startsWith('/')) return clean;

    // fallback
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

  /* Background similar to index page */
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

  /* picture hover tools (same idea as index page) */
  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }

  .pic-thumb {
    display: inline-flex;
    cursor: pointer;
    position: relative;
  }

  .pic-overlay {
    position: absolute;
    inset: -6px -16px auto auto;
    display: none;
    background: rgba(0, 0, 0, 0.55);
    padding: 4px 6px;
    border-radius: 8px;
    transform: translateY(-20%);
    white-space: nowrap;
  }

  .pic-thumb:hover .pic-overlay {
    display: flex;
  }

  .pic-overlay :deep(.v-btn) {
    color: #fff;
    font-size: 11px;
    min-height: 0;
    padding-inline: 8px;
  }
</style>
