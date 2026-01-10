<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('اطلاعات')" :subtitle="t('فهرست و جستجو')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- Search + Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Field -->
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو در اطلاعات...')"
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
            class="text-none"
            rounded="lg"
            block
            @click="goCreate"
            prepend-icon="mdi-plus"
          >
            {{ t('جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
    <!-- STATS CARDS -->
    <v-row class="px-2 d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- 1) Total Resources -->
      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('resources')"
          :class="{ 'active-stat': activeStat === 'resources' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ totalResourcesCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('کل اطلاعات') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-account-multiple</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 2) ساعین فساد -->
      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('saeen')"
          :class="{ 'active-stat': activeStat === 'saeen' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-amber fontfamily">
                {{ saeenFasadCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('ساعین فساد') }}
              </div>
            </div>
            <v-avatar color="amber" variant="tonal" size="48">
              <v-icon color="amber">mdi-alert-circle</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 3) شر او فساد -->
      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('shar')"
          :class="{ 'active-stat': activeStat === 'shar' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-purple fontfamily">
                {{ sharOFasadCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('شر او فساد') }}
              </div>
            </div>
            <v-avatar color="purple" variant="tonal" size="48">
              <v-icon color="purple">mdi-fire</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 4) جاسوسی -->
      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('spy')"
          :class="{ 'active-stat': activeStat === 'spy' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ jasosiCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('جاسوسی') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-shield-search</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- 5) Today -->
      <v-col cols="12" sm="2">
        <v-card
          class="stat-card cursor-pointer"
          @click="filterByStat('today')"
          :class="{ 'active-stat': activeStat === 'today' }"
          rounded="xl"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">
                {{ todayCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('امروز ثبت شده') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-calendar-today</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Peek -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title="جزییات اطلاعات"
        :name-key="'name'"
        :fields="headersFT"
        dir="rtl"
        @close="expanded = false"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- List -->
    <FilterableTable
      :headers="headersFT"
      :rows="filteredRows"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :autoDetectDates="false"
      :dateKeys="['information_saved_date']"
      class="mb-20 mx-2"
    >
      <!-- RESOURCE: فقط کود + tooltip -->
      <template #cell-resource_code_number="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip
            v-if="row.resource_code_number"
            text="برای رفتن به صفحه منابع کلیک کنید"
            location="top"
          >
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="primary"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToResource(row)"
              >
                <v-icon start size="16">mdi-account-search</v-icon>
                <span class="text-caption">
                  {{ row.resource_code_number }}
                </span>
              </v-btn>
            </template>
          </v-tooltip>

          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div>
      </template>

      <!-- OFFICE EMPLOYEE: فقط کود + tooltip -->
      <template #cell-office_employee_code_number="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip
            v-if="row.office_employee_code_number"
            text="برای رفتن به صفحه کارمندان مدیریت کلیک کنید"
            location="top"
          >
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="deep-purple"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToOfficeEmployee(row)"
              >
                <v-icon start size="16">mdi-account-badge</v-icon>
                <span class="text-caption">
                  {{ row.office_employee_code_number }}
                </span>
              </v-btn>
            </template>
          </v-tooltip>

          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div> </template
      ><!-- OFFICE EMPLOYEE: فقط کود + tooltip -->
      <template #cell-office_employee_info_code_number="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip
            v-if="row.office_employee_info_code_number"
            text="برای رفتن به صفحه کارمندان مدیریت کلیک کنید"
            location="top"
          >
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="deep-orange"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToOfficeEmployee(row)"
              >
                <v-icon start size="16">mdi-account-badge</v-icon>
                <span class="text-caption">
                  {{ row.office_employee_info_code_number }}
                </span>
              </v-btn>
            </template>
          </v-tooltip>

          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div>
      </template>
      <template #cell-office_employee_info_id="{ row }">
        <div class="d-flex flex-column align-start">
          <v-tooltip
            v-if="row.office_employee_info_id"
            text="برای رفتن به صفحه کارمندان مدیریت کلیک کنید"
            location="top"
          >
            <template #activator="{ props: tooltipProps }">
              <v-btn
                v-bind="tooltipProps"
                color="deep-purple"
                variant="tonal"
                class="text-none px-2 py-1"
                @click.stop="goToOfficeEmployee(row)"
              >
                <v-icon start size="16">mdi-account-badge</v-icon>
                <span class="text-caption">
                  {{ row.office_employee_info_id }}
                </span>
              </v-btn>
            </template>
          </v-tooltip>

          <span v-else class="text-caption text-medium-emphasis"> — </span>
        </div>
      </template>

      <!-- Actions column: 4 icons like OfficeEmployee (show/edit/download/delete) -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip text="مشاهده اطلاع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip text="تغییر جزیات اطلاع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="amber-darken-2"
                @click.stop="startEdit(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <!-- دانلود اسناد -->
          <v-tooltip text="دانلود اسناد مربوط به اطلاع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="green-darken-2"
                :disabled="!hasDocuments(item)"
                @click.stop="downloadDocuments(item)"
              >
                mdi-paperclip
              </v-icon>
            </template>
          </v-tooltip>

          <!-- حذف -->
          <v-tooltip text="حذف اطلاع" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="red-darken-2"
                @click.stop="openDeleteDialog(item)"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
          <v-tooltip text="فرم معلومات (PDF / Word)" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="primary"
                @click.stop="goPrint(item)"
              >
                mdi-file-document-outline
              </v-icon>
            </template>
          </v-tooltip>
        </div>
      </template>
    </FilterableTable>

    <!-- 🔁 Reusable Delete Confirm Dialog -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />

    <!-- Error Snackbar -->
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
  import { ref, onMounted, computed, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import AppBar from '../../components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import { useI18n } from 'vue-i18n';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';
  import { useRoute } from 'vue-router';
  const route = useRoute();

  const auth = useLoginRepository();
  const { t, locale } = useI18n();
  const repo = useDatabaseRepository();
  const router = useRouter();

  const showError = ref(false);
  const errorMessage = ref('');
  const activeStat = ref(null);

  function showErrorMessage(message) {
    errorMessage.value = message;
    showError.value = true;
  }

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => {
    isRtl.value = newLocale === 'fa';
  });

  /* ===== helper to unwrap row/raw ===== */
  function getRow(payload) {
    return payload?.raw ?? payload;
  }
  function goPrint(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/informations/print/${row.id}`);
  }
  const filterOfficeEmployeeId = computed(() => {
    const v = route.query?.office_employee_id;
    return v ? Number(v) : null;
  });

  const filteredRows = computed(() => {
    let rows = allRows.value;

    // ✅ URL filter (from OfficeEmployee page)
    if (filterOfficeEmployeeId.value) {
      rows = rows.filter((r) => Number(r.office_employee_id) === filterOfficeEmployeeId.value);
    }

    // ✅ Stat filters
    if (activeStat.value === 'today') {
      const today = new Date().toISOString().split('T')[0];
      rows = rows.filter((item) => item.information_saved_date === today);
    } else if (activeStat.value === 'saeen') {
      rows = rows.filter((item) => matchType(item, 'ساعین بالفساد'));
    } else if (activeStat.value === 'shar') {
      rows = rows.filter((item) => matchType(item, 'شر و فساد'));
    } else if (activeStat.value === 'spy') {
      rows = rows.filter((item) => matchType(item, ' جاسوسی') || matchType(item, 'جاسوسی'));
    } else if (activeStat.value === 'resources') {
      rows = allRows.value; // keep list unchanged
    }

    return rows;
  });

  /* ===== Data sources to arrays ===== */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  /* ===== Stats Calculations ===== */
  const allRows = computed(() => toArray(repo.Informations));

  const resourcesRows = computed(() => {
    const src = repo.Informations;

    return toArray(src);
  });

  const totalResourcesCount = computed(() => resourcesRows.value.length);

  const todayCount = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    return allRows.value.filter((item) => item.information_saved_date === today).length;
  });

  // matches from BOTH types (seyasi + jenayee) as you requested
  function matchType(item, wanted) {
    const a = (item?.syasi_info_type ?? '').toString().trim();
    const b = (item?.jenayee_info_type ?? '').toString().trim();
    const w = wanted.toString().trim();
    return a === w || b === w;
  }

  // Your 3 cards (from your options list)
  const saeenFasadCount = computed(
    () => allRows.value.filter((item) => matchType(item, 'ساعین بالفساد')).length
  );

  const sharOFasadCount = computed(
    () => allRows.value.filter((item) => matchType(item, 'شر و فساد')).length
  );

  const jasosiCount = computed(
    () =>
      allRows.value.filter((item) => matchType(item, ' جاسوسی') || matchType(item, 'جاسوسی')).length
  );

  /* ===== Filtered Rows based on active stat ===== */
  // const filteredRows = computed(() => {
  //   let rows = allRows.value;

  //   if (activeStat.value === 'today') {
  //     const today = new Date().toISOString().split('T')[0];
  //     rows = rows.filter((item) => item.information_saved_date === today);
  //   } else if (activeStat.value === 'saeen') {
  //     rows = rows.filter((item) => matchType(item, 'ساعین بالفساد'));
  //   } else if (activeStat.value === 'shar') {
  //     rows = rows.filter((item) => matchType(item, 'شر و فساد'));
  //   } else if (activeStat.value === 'spy') {
  //     rows = rows.filter((item) => matchType(item, ' جاسوسی') || matchType(item, 'جاسوسی'));
  //   } else if (activeStat.value === 'resources') {
  //     // this card is just for total resources count (not a list filter),
  //     // so we keep informations list unchanged.
  //     rows = allRows.value;
  //   }

  //   return rows;
  // });

  function filterByStat(stat) {
    activeStat.value = activeStat.value === stat ? null : stat;
  }

  /* ===== Headers for FilterableTable ===== */
  const headersFT = computed(() => [
    { title: t('کود منبع'), key: 'resource_code_number' },
    { title: t('کود کارمند مربوطه'), key: 'office_employee_code_number' },
    { title: t('کود کارمند  اطلاع گیرنده'), key: 'office_employee_info_code_number' },

    { title: t('نام'), key: 'name' },
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_districts' },
    { title: t('قریه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },

    { title: t('سیاسی'), key: 'syasi_info_type' },
    { title: t('جنایی'), key: 'jenayee_info_type' },

    { title: t('تاریخ اطلاع'), key: 'information_saved_date' },
    { title: t('ساعت اطلاع'), key: 'information_saved_hour' },
    { title: t('درجه موثقیت اطلاع'), key: 'information_degree' },

    // ✅ NEW: Hamahangi fields
    { title: t('تاریخ هم‌آهنگی'), key: 'hamahangi_date' },
    { title: t('شماره مکتوب'), key: 'maktop_number' },
    { title: t('ارگان'), key: 'organ' },

    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('نظر مدیر'), key: 'manager_comment' },
    { title: t('رهنمود رهبریت'), key: 'lead_drirection' },
    { title: t('گفت‌وگوها'), key: 'talks' },
    { title: t('نتیجه'), key: 'result' },
    { title: t('ملاحظات'), key: 'note' },
    { title: t('نوت راجع به اسناد'), key: 'docs_note', width: 140 },
  ]);

  const topGroups = computed(() => [
    {
      title: t('کودها'),
      keys: ['resource_id', 'office_employee_id'],
    },
    {
      title: t('معرفت'),
      keys: ['name', 'nick_name', 'father_name', 'grand_father_name'],
    },
    {
      title: t('آدرس اصلی'),
      keys: ['main_province', 'main_districts', 'main_village'],
    },
    {
      title: t('آدرس فعلی'),
      keys: ['now_province', 'now_district', 'now_village'],
    },
    {
      title: t('نوع و زمان اطلاع'),
      keys: [
        'syasi_info_type',
        'jenayee_info_type',
        'information_saved_date',
        'information_saved_hour',
        'information_degree',
      ],
    },

    // ✅ NEW group for the 3 fields
    {
      title: t('هم‌آهنگی'),
      keys: ['hamahangi_date', 'maktop_number', 'organ'],
    },

    {
      title: t('متن‌ها'),
      keys: ['tafseel', 'manager_comment', 'lead_drirection', 'talks', 'note', 'result'],
    },
  ]);

  /* ===== Download helpers ===== */
  function hasDocuments(payload) {
    const row = getRow(payload);
    const docs = row?.documents;
    return Array.isArray(docs) && docs.length > 0;
  }

  async function downloadDocuments(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    const name = row.name || row.code_number || `information_${row.id}`;

    try {
      if (typeof repo.DownloadInformationZip === 'function') {
        await repo.DownloadInformationZip(row.id, name);
      } else {
        console.warn('Download method for Informations not implemented on repo');
        showErrorMessage(t('دانلود اسناد برای این اطلاع هنوز تنظیم نشده است'));
      }
    } catch (e) {
      console.error('downloadDocuments error', e);
      showErrorMessage(t('خطا در دانلود اسناد'));
    }
  }

  /* ===== DELETE with reusable 3-step dialog ===== */
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
      if (typeof repo.DeleteInformation === 'function') {
        await repo.DeleteInformation(row.id);
      } else {
        console.warn('DeleteInformation not implemented on repo');
        showErrorMessage(t('حذف برای این اطلاع هنوز تنظیم نشده است'));
        return;
      }

      await fetchAll();
    } catch (e) {
      console.error('handleDeleteConfirmed error', e);
      showErrorMessage(t('خطا در حذف اطلاع'));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  /* ===== Peek / Fetch one ===== */
  const filters = ref({});
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      if (typeof repo.FetchInformationData === 'function') {
        await repo.FetchInformationData(id);
        detail.value =
          (repo.Information && Array.isArray(repo.Information)
            ? repo.Information[0]
            : repo.Information) || {};
      } else {
        await fetchAll();
        const list = allRows.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } finally {
      loading.value = false;
    }
  }

  function openPeek(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }

  watch(
    detail,
    (newDetail) => {
      console.log('Detail updated:', newDetail);
    },
    { deep: true }
  );

  /* ===== List Fetch ===== */
  async function fetchAll() {
    try {
      await repo.FetchInformationsData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search,
      });
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت معلومات'));
    }
  }

  /* ===== Navigation ===== */
  function goCreate() {
    router.push('/app/informationform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/informationform/${row.id}`);
  }

  // 🔹 Go to RESOURCE page (listing / main page)
  function goToResource(payload) {
    const row = getRow(payload);
    const id = row?.resource_id;
    if (!id) return;

    router.push({
      path: '/app/resources',
      query: { id: String(id), open: 'peek' },
    });
  }

  // 🔹 Go to OFFICE EMPLOYEE page (listing / main page)
  function goToOfficeEmployee(payload) {
    const row = getRow(payload);

    // choose the correct key you have in Informations rows
    const id =
      row?.office_employee_id || row?.office_employee_info_id || row?.office_employee_info_id; // keep only what exists in your API

    if (!id) return;

    router.push({
      path: '/app/officeemployee',
      query: { id: String(id), open: 'peek' }, // open=peek is optional
    });
  }

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* ===== Init ===== */
  onMounted(async () => {
    await Promise.allSettled([repo.GetOfficeEmployee?.(), repo.GetResource?.()]);
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

  .stat-card.active-stat {
    border-color: currentColor;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(240, 240, 240, 0.9) 100%);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
  }

  .stat-card.active-stat .v-avatar {
    background: rgba(255, 255, 255, 0.9) !important;
  }

  .cursor-pointer {
    cursor: pointer;
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

  table th {
    white-space: nowrap;
  }

  :deep(.v-field-label),
  :deep(.v-label),
  :deep(input::placeholder) {
    font-size: 12px !important;
    line-height: 1.2 !important;
    color: #555 !important;
  }

  :deep(.v-field__input) {
    min-height: 26px !important;
    padding: 2px 6px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
  }

  :deep(.v-data-table) {
    border-radius: 8px;
  }

  :deep(.v-data-table__wrapper) {
    border-radius: 8px;
  }

  :deep(.v-data-table__tr:hover) {
    background-color: rgba(33, 150, 243, 0.04) !important;
  }

  :deep(.v-btn--icon) {
    transition: all 0.2s ease;
  }

  :deep(.v-btn--icon:hover) {
    transform: scale(1.1);
  }
  .fontfamily {
    font-family: var(--app-font) !important;
  }
</style>
