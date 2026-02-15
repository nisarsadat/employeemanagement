<template>
  <v-container fluid class="page-bg pt-6 fontfamily" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="t('گزارش ماهانه سیستم ')"
      :subtitle="t('نمای کلی ماهانه منابع، اطلاعات، رویدادها و فورم‌ها')"
    />

    <!-- HEADER / FILTER -->
    <div class="header-container mb-6 fontfamily" :dir="isRtl ? 'rtl' : 'ltr'">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h1 class="title-text mb-1 fontfamily">
            {{ t('گزارش ماهانه سیستم ') }}
          </h1>
          <div class="subtitle-text fontfamily">
            {{ t('نمای کلی ماهانه منابع، اطلاعات، رویدادها و فورم‌ها') }}
          </div>
        </div>

        <div class="date-display d-flex align-center fontfamily">
          <v-icon icon="mdi-calendar-month-outline" class="mr-2" />
          <span class="font-weight-medium">{{ chipMonth }}</span>
        </div>
      </div>

      <v-card class="filter-card" elevation="0">
        <v-card-text class="pa-4">
          <div class="d-flex flex-wrap align-center justify-space-between ga-3">
            <div class="d-flex flex-wrap align-center ga-3">
              <!-- ✅ From Day -->
              <div class="date-input-container">
                <DateInput
                  v-model="repo.selectedMonthFrom"
                  :label="t('از تاریخ')"
                  calendar="gregorian"
                  :rtl="isRtl"
                  density="comfortable"
                />
              </div>

              <!-- ✅ To Day -->
              <div class="date-input-container">
                <DateInput
                  v-model="repo.selectedMonthTo"
                  :label="t('تا تاریخ')"
                  calendar="gregorian"
                  :rtl="isRtl"
                  density="comfortable"
                />
              </div>

              <v-btn
                color="primary"
                variant="flat"
                class="text-none action-btn"
                :loading="repo.loading"
                @click="fetch"
                rounded="lg"
                size="large"
              >
                <template #prepend>
                  <v-icon icon="mdi-chart-bar" />
                </template>
                {{ t('نمایش گزارش') }}
              </v-btn>

              <v-btn
                color="surface-variant"
                variant="outlined"
                class="text-none"
                @click="resetToThisMonth"
                :disabled="repo.loading"
                rounded="lg"
                size="large"
              >
                <template #prepend>
                  <v-icon icon="mdi-calendar-today" />
                </template>
                {{ t('این ماه') }}
              </v-btn>

              <!-- ✅ PDF -->
              <v-btn
                color="error"
                variant="tonal"
                class="text-none"
                :disabled="repo.loading || !report"
                rounded="lg"
                size="large"
                @click="repo.DownloadMonthlyFoulPdf()"
              >
                <template #prepend>
                  <v-icon icon="mdi-file-pdf-box" />
                </template>
                PDF
              </v-btn>

              <!-- ✅ Word -->
              <v-btn
                color="primary"
                variant="tonal"
                class="text-none"
                :disabled="repo.loading || !report"
                rounded="lg"
                size="large"
                @click="repo.DownloadMonthlyFoulWord()"
              >
                <template #prepend>
                  <v-icon icon="mdi-file-word-box" />
                </template>
                Word
              </v-btn>
            </div>
          </div>
        </v-card-text>
      </v-card>
    </div>

    <!-- ERROR / EMPTY -->
    <v-alert
      v-if="repo.error"
      type="error"
      variant="tonal"
      class="mb-4 rounded-xl fontfamily"
      border="start"
      :dir="isRtl ? 'rtl' : 'ltr'"
      border-color="error"
      density="comfortable"
    >
      <div class="d-flex align-center">
        <v-icon icon="mdi-alert-circle-outline" class="mr-3" />
        <span>{{ repo.error }}</span>
      </div>
    </v-alert>

    <v-alert
      v-else-if="!repo.loading && report && isAllZero"
      type="info"
      variant="tonal"
      class="mb-4 rounded-xl fontfamily"
      :dir="isRtl ? 'rtl' : 'ltr'"
      border="start"
      border-color="info"
      density="comfortable"
    >
      <div class="d-flex align-center">
        <v-icon icon="mdi-information-outline" class="mr-3" />
        <span>{{ t('برای ماه انتخاب‌شده هیچ دیتا موجود نیست.') }}</span>
      </div>
    </v-alert>

    <!-- STATS -->
    <v-row class="mb-5">
      <v-col cols="12" sm="6" lg="3">
        <v-card class="stat-card" elevation="0" :dir="isRtl ? 'rtl' : 'ltr'" @click="goResources">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="stat-label fontfamily">{{ t('منابع') }}</div>
              <div class="stat-number fontfamily">{{ report?.resources?.total ?? 0 }}</div>
              <v-chip size="x-small" color="primary" variant="tonal" class="mt-2 fontfamily">
                <v-icon icon="mdi-account-group" size="x-small" class="mr-1" />
                {{ t('کل') }}
              </v-chip>
            </div>

            <v-avatar size="44" color="primary" variant="tonal">
              <v-icon icon="mdi-account-group" />
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <v-card
          class="stat-card"
          elevation="0"
          :dir="isRtl ? 'rtl' : 'ltr'"
          @click="goInformations"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="stat-label fontfamily">{{ t('اطلاعات') }}</div>
              <div class="stat-number fontfamily">{{ report?.informations?.total ?? 0 }}</div>
              <v-chip size="x-small" color="info" variant="tonal" class="mt-2 fontfamily">
                <v-icon icon="mdi-file-document-outline" size="x-small" class="mr-1" />
                {{ t('موجود') }}
              </v-chip>
            </div>

            <v-avatar size="44" color="info" variant="tonal">
              <v-icon icon="mdi-file-document-multiple-outline" />
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <v-card class="stat-card" elevation="0" :dir="isRtl ? 'rtl' : 'ltr'" @click="goEvents">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="stat-label fontfamily">{{ t('رویدادها') }}</div>
              <div class="stat-number fontfamily">{{ report?.events?.total ?? 0 }}</div>
              <v-chip size="x-small" color="warning" variant="tonal" class="mt-2 fontfamily">
                <v-icon icon="mdi-alert-outline" size="x-small" class="mr-1" />
                {{ t('ثبت شده') }}
              </v-chip>
            </div>

            <v-avatar size="44" color="warning" variant="tonal">
              <v-icon icon="mdi-alert-decagram-outline" />
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <v-card
          class="stat-card"
          elevation="0"
          :dir="isRtl ? 'rtl' : 'ltr'"
          @click="goFiftyFormatsHub"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="stat-label fontfamily">{{ t('فورم‌های ۵۰ فارمت') }}</div>
              <div class="stat-number fontfamily">{{ report?.formats?.length ?? 0 }}</div>
              <v-chip size="x-small" color="success" variant="tonal" class="mt-2 fontfamily">
                <v-icon icon="mdi-format-list-bulleted" size="x-small" class="mr-1" />
                {{ t('فعال') }}
              </v-chip>
            </div>

            <v-avatar size="44" color="success" variant="tonal">
              <v-icon icon="mdi-clipboard-list-outline" />
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- TABLES -->
    <v-row>
      <!-- LEFT -->
      <v-col cols="12" lg="5">
        <!-- Informations types -->
        <v-card class="data-card mb-4" elevation="0" :dir="isRtl ? 'rtl' : 'ltr'">
          <div class="card-header primary-header">
            <div class="d-flex align-center">
              <v-avatar size="34" color="primary" variant="tonal" class="mr-2">
                <v-icon icon="mdi-shape-outline" size="18" />
              </v-avatar>
              <div>
                <div class="card-title fontfamily">{{ t('نوعیت‌های اطلاعات') }}</div>
                <div class="card-subtitle fontfamily">{{ t('توزیع بر اساس نوع') }}</div>
              </div>
            </div>
            <v-chip color="primary" variant="tonal" size="small" class="fontfamily">
              {{ report?.informations?.total ?? 0 }}
            </v-chip>
          </div>

          <v-divider />

          <v-card-text class="pa-0" :dir="isRtl ? 'rtl' : 'ltr'">
            <v-data-table
              :headers="infoHeaders"
              :items="report?.informations?.types ?? []"
              item-key="label"
              density="comfortable"
              class="elevation-0"
              :loading="repo.loading"
              @click:row="(_, row) => goInfoType(row?.item?.label)"
              hide-default-footer
              :no-data-text="t('هیچ دیتا وجود ندارد')"
            >
              <template #item.count="{ item }">
                <v-chip color="primary" variant="outlined" size="small" class="font-weight-bold">
                  {{ item.count }}
                </v-chip>
              </template>

              <template #item.label="{ item }">
                <div class="d-flex align-center">
                  <div class="type-indicator mr-2" :class="getTypeColor(item.label)"></div>
                  <span class="fontfamily">{{ item.label }}</span>
                </div>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>

        <!-- Formats -->
        <v-card class="data-card" elevation="0" :dir="isRtl ? 'rtl' : 'ltr'">
          <div class="card-header success-header">
            <div class="d-flex align-center">
              <v-avatar size="34" color="success" variant="tonal" class="mr-2">
                <v-icon icon="mdi-format-list-bulleted" size="18" />
              </v-avatar>
              <div>
                <div class="card-title fontfamily">{{ t('فورم‌ها') }}</div>
                <div class="card-subtitle fontfamily">{{ t('فورم‌های  ۵۰ فارمت') }}</div>
              </div>
            </div>
            <v-chip color="success" variant="tonal" size="small" class="fontfamily">
              {{ report?.formats?.length ?? 0 }}
            </v-chip>
          </div>

          <v-divider />

          <v-card-text class="pa-0" :dir="isRtl ? 'rtl' : 'ltr'">
            <v-data-table
              :headers="formatHeaders"
              :items="report?.formats ?? []"
              item-key="key"
              density="comfortable"
              class="elevation-0"
              @click:row="(_, row) => goFormatByKey(row?.item?.key)"
              :loading="repo.loading"
              :dir="isRtl ? 'rtl' : 'ltr'"
              hide-default-footer
              :no-data-text="t('هیچ فورم دارای دیتا نیست')"
            >
              <template #item.count="{ item }">
                <v-chip color="success" variant="outlined" size="small" class="font-weight-bold">
                  {{ item.count }}
                </v-chip>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- RIGHT -->
      <v-col cols="12" lg="7" :dir="isRtl ? 'rtl' : 'ltr'">
        <v-card class="data-card events-table-card" elevation="0">
          <div class="card-header warning-header">
            <div class="d-flex align-center">
              <v-avatar size="34" color="warning" variant="tonal" class="mr-2">
                <v-icon icon="mdi-alert-outline" size="18" />
              </v-avatar>
              <div>
                <div class="card-title fontfamily">{{ t('رویدادهای ثبت‌شده') }}</div>
                <div class="card-subtitle fontfamily">
                  {{ t('جزئیات رویدادها بر اساس نوع رویداد') }}
                </div>
              </div>
            </div>
            <v-chip color="warning" variant="tonal" size="small" class="fontfamily">
              {{ report?.events?.total ?? 0 }}
            </v-chip>
          </div>

          <v-divider />

          <v-card-text class="pa-0">
            <v-data-table
              :headers="eventHeaders"
              :items="eventRows"
              item-key="__idx"
              density="comfortable"
              @click:row="() => goEvents()"
              class="elevation-0"
              :loading="repo.loading"
              hide-default-footer
              :no-data-text="t('هیچ رویدادی ثبت نشده')"
            >
              <template #item.count="{ item }">
                <v-chip
                  color="warning"
                  variant="outlined"
                  size="small"
                  class="font-weight-bold fontfamily"
                >
                  {{ item.count }}
                </v-chip>
              </template>

              <template #item.label="{ item }">
                <v-chip
                  :color="getEventTypeColor(item.label)"
                  variant="tonal"
                  size="small"
                  class="font-weight-medium fontfamily"
                >
                  {{ item.label }}
                </v-chip>
              </template>
            </v-data-table>
          </v-card-text>

          <v-divider />

          <div class="summary-footer pa-3 fontfamily">
            <div class="d-flex align-center justify-space-between">
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('تعداد کل رویدادها') }}:
                <span class="font-weight-bold text-primary">
                  {{ report?.events?.total ?? 0 }}
                </span>
              </div>

              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('آخرین بروزرسانی') }}:
                {{ new Date().toLocaleTimeString(locale.value === 'fa' ? 'fa-IR' : 'en-US') }}
              </div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col>
        <ReportNotesPeriodTable
          type="monthly"
          :anchor-month="repo.selectedMonthFrom"
          :rtl="isRtl"
          :locale="locale"
          :per-page="50"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRouter } from 'vue-router';
  import ReportNotesPeriodTable from './ReportNotesPeriodTable.vue';

  import AppBar from '@/components/AppBar.vue';
  import DateInput from '@/components/DateInput.vue';
  import { useFoulReportsRepository } from '@/stores/FoulReportsRepository';

  const { t, locale } = useI18n();

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  const repo = useFoulReportsRepository();
  const report = computed(() => repo.monthly);

  const router = useRouter();

  const goResources = () => router.push('/app/resources');
  const goEvents = () => router.push('/app/events');
  const goInformations = () => router.push('/app/informations');
  const goFiftyFormatsHub = () => router.push('/app/fifthyformatsroute');

  function goFormatByKey(key) {
    if (!key) return;
    router.push(`/app/${key}`);
  }

  function goInfoType() {
    router.push('/app/informations');
  }

  function pad2(n) {
    return String(n).padStart(2, '0');
  }

  function currentMonthIso() {
    const d = new Date();
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}`;
  }

  function normalizeDayOrder() {
    const a = String(repo.selectedMonthFrom || '').trim(); // now YYYY-MM-DD
    const b = String(repo.selectedMonthTo || '').trim();

    if (!/^\d{4}-\d{2}-\d{2}$/.test(a) || !/^\d{4}-\d{2}-\d{2}$/.test(b)) return;

    if (a > b) {
      const tmp = a;
      repo.selectedMonthFrom = b;
      repo.selectedMonthTo = tmp;
    }
  }

  watch(
    () => [repo.selectedMonthFrom, repo.selectedMonthTo],
    () => normalizeDayOrder(),
    { deep: false }
  );

  const chipMonth = computed(() => {
    const from = repo.selectedMonthFrom || report.value?.meta?.month || '';
    const to = repo.selectedMonthTo || from;
    if (!from) return '';

    const [y1, m1] = from.split('-').map(Number);
    const [y2, m2] = to.split('-').map(Number);
    if (!y1 || !m1) return from;

    const dt1 = new Date(y1, m1 - 1, 1);
    const t1 = dt1.toLocaleDateString(locale.value === 'fa' ? 'fa-IR' : 'en-US', {
      year: 'numeric',
      month: 'long',
    });

    if (!y2 || !m2 || from === to) return t1;

    const dt2 = new Date(y2, m2 - 1, 1);
    const t2 = dt2.toLocaleDateString(locale.value === 'fa' ? 'fa-IR' : 'en-US', {
      year: 'numeric',
      month: 'long',
    });

    return `${t1} - ${t2}`;
  });

  const eventRows = computed(() => {
    const types = report.value?.events?.types ?? [];
    return types.map((r, idx) => ({ __idx: idx, ...r }));
  });

  const isAllZero = computed(() => {
    const r = report.value;
    if (!r) return false;
    return (
      (r.resources?.total ?? 0) === 0 &&
      (r.informations?.total ?? 0) === 0 &&
      (r.events?.total ?? 0) === 0 &&
      (r.formats?.length ?? 0) === 0
    );
  });

  const infoHeaders = computed(() => [
    { title: t('نوعیت اطلاعات'), key: 'label', width: '70%' },
    { title: t('تعداد'), key: 'count', align: 'end', width: '30%' },
  ]);

  const formatHeaders = computed(() => [
    { title: t('نام فورم'), key: 'name', width: '70%' },
    { title: t('ورودی‌ها'), key: 'count', align: 'end', width: '30%' },
  ]);

  const eventHeaders = computed(() => [
    { title: t('نوع رویداد'), key: 'label', width: '70%' },
    { title: t('تعداد'), key: 'count', align: 'end', width: '30%' },
  ]);

  function getTypeColor(type) {
    const colors = {
      عمومی: 'type-general',
      خصوصی: 'type-private',
      محرمانه: 'type-confidential',
      فوری: 'type-urgent',
    };
    return colors[type] || 'type-general';
  }

  function getEventTypeColor(type) {
    const colors = {
      حادثه: 'error',
      تخریب: 'warning',
      درگیری: 'deep-orange',
      طبیعی: 'info',
      سیاسی: 'purple',
    };
    return colors[type] || 'grey';
  }

  async function fetch() {
    normalizeDayOrder();
    await repo.FetchMonthlyFoulReport({
      fromMonth: repo.selectedMonthFrom, // now YYYY-MM-DD
      toMonth: repo.selectedMonthTo, // now YYYY-MM-DD
    });
  }

  function todayIso() {
    const d = new Date();
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
  }

  async function resetToThisMonth() {
    const cur = todayIso();
    repo.selectedMonthFrom = cur;
    repo.selectedMonthTo = cur;
    await fetch();
  }

  onMounted(() => {
    const cur = todayIso();
    if (!repo.selectedMonthFrom) repo.selectedMonthFrom = cur;
    if (!repo.selectedMonthTo) repo.selectedMonthTo = cur;
    fetch();
  });
</script>

<style scoped>
  .fontfamily,
  .fontfamily * {
    font-family: var(--app-font) !important;
  }

  .page-bg {
    background: #f6f7fb;
    min-height: 100vh;
  }

  .header-container {
    background: linear-gradient(135deg, #5b6eea 0%, #6d42a6 100%);
    border-radius: 14px;
    padding: 18px;
    position: relative;
    overflow: hidden;
  }

  .header-container::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='120' height='120' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3z' fill='%23ffffff' fill-opacity='0.10' fill-rule='evenodd'/%3E%3C/svg%3E");
    opacity: 0.35;
    pointer-events: none;
  }

  .title-text {
    color: #fff;
    font-size: 20px;
    font-weight: 800;
    letter-spacing: 0.2px;
    position: relative;
    z-index: 1;
  }

  .subtitle-text {
    color: rgba(255, 255, 255, 0.85);
    font-size: 13px;
    position: relative;
    z-index: 1;
  }

  .date-display {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 10px;
    padding: 8px 12px;
    color: #fff;
    font-size: 13px;
    position: relative;
    z-index: 1;
    white-space: nowrap;
  }

  .filter-card {
    margin-top: 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.95) !important;
    border: 1px solid rgba(255, 255, 255, 0.35);
    position: relative;
    z-index: 1;
  }

  .action-btn {
    height: 44px !important;
    padding: 0 18px !important;
    font-weight: 700;
  }

  .date-input-container :deep(.v-field) {
    min-height: 44px;
  }

  .date-input-container :deep(.v-input) {
    background: #fff;
    border-radius: 10px;
  }

  .stat-card {
    border-radius: 12px;
    border: 1px solid #e9eef7;
    background: #ffffff;
    transition: all 0.2s ease;
    cursor: pointer;
    min-height: 92px;
  }

  :deep(.v-data-table tbody tr) {
    cursor: pointer;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(16, 24, 40, 0.08) !important;
  }

  .stat-label {
    font-size: 12px;
    color: rgba(17, 24, 39, 0.65);
    margin-bottom: 4px;
    font-weight: 600;
  }

  .stat-number {
    font-size: 26px;
    font-weight: 900;
    line-height: 1.1;
  }

  .data-card {
    border-radius: 12px;
    border: 1px solid #e9eef7;
    overflow: hidden;
    background: #fff;
  }

  .events-table-card {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .card-header {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .card-title {
    font-size: 14px;
    font-weight: 800;
    color: rgba(17, 24, 39, 0.9);
  }

  .card-subtitle {
    font-size: 12px;
    color: rgba(17, 24, 39, 0.55);
    margin-top: 2px;
  }

  .primary-header {
    background: linear-gradient(135deg, #eef2ff 0%, #dde5ff 100%);
  }

  .success-header {
    background: linear-gradient(135deg, #e8fbf2 0%, #d2f7e5 100%);
  }

  .warning-header {
    background: linear-gradient(135deg, #fff6df 0%, #ffeab6 100%);
  }

  .summary-footer {
    background: #f8fafc;
  }

  .type-indicator {
    width: 7px;
    height: 7px;
    border-radius: 50%;
  }

  .type-general {
    background-color: #4f46e5;
  }

  .type-private {
    background-color: #0ea5e9;
  }

  .type-confidential {
    background-color: #8b5cf6;
  }

  .type-urgent {
    background-color: #ef4444;
  }

  :deep(.v-data-table) {
    font-size: 13px;
  }

  :deep(.v-data-table-header) {
    background: #f8fafc;
  }

  :deep(.v-data-table-header th) {
    font-weight: 800 !important;
    color: rgba(17, 24, 39, 0.7) !important;
    font-size: 12px !important;
  }

  :deep(.v-data-table-row:hover) {
    background: #f6f8ff;
  }
</style>
