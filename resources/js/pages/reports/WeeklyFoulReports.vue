<template>
  <v-container fluid class="page-bg pt-6 fontfamily" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar
      :title="t('گزارش هفته‌وار سیستم ')"
      :subtitle="t('نمای کلی هفته‌وار منابع، اطلاعات، رویدادها و فورم‌ها')"
    />

    <!-- HEADER / FILTER -->
    <div class="header-container mb-6 fontfamily" :dir="isRtl ? 'rtl' : 'ltr'">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h1 class="title-text mb-1 fontfamily">
            {{ t('گزارش هفته‌وار سیستم ') }}
          </h1>
          <div class="subtitle-text fontfamily">
            {{ t('نمای کلی هفته‌وار منابع، اطلاعات، رویدادها و فورم‌ها') }}
          </div>
        </div>

        <div class="date-display d-flex align-center fontfamily">
          <v-icon icon="mdi-calendar-month-outline" class="mr-2" />
          <span class="font-weight-medium">{{ chipWeek }}</span>
        </div>
      </div>

      <v-card class="filter-card" elevation="0">
        <v-card-text class="pa-4">
          <div class="d-flex flex-wrap align-center justify-space-between ga-3">
            <div class="d-flex flex-wrap align-center ga-3">
              <!-- ✅ calendar selector (gregorian / hijri shamsi / hijri qamari) -->
              <v-select
                v-model="calendarType"
                :items="calendarOptions"
                item-title="title"
                item-value="value"
                :label="t('نوع تقویم')"
                variant="outlined"
                density="comfortable"
                rounded="lg"
                hide-details="auto"
                class="bg-white"
                style="min-width: 200px"
              />

              <!-- ✅ DateInput (we use DATE to compute ISO week) -->
              <div class="date-input-container">
                <DateInput
                  v-model="pickedDate"
                  :label="t('انتخاب تاریخ (برای هفته)')"
                  :rtl="isRtl"
                  density="comfortable"
                  :calendar="calendarType"
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
                @click="resetToCurrentWeek"
                :disabled="repo.loading"
                rounded="lg"
                size="large"
              >
                <template #prepend>
                  <v-icon icon="mdi-calendar-today" />
                </template>
                {{ t('هفته جاری') }}
              </v-btn>

              <!-- ✅ PDF -->
              <v-btn
                color="error"
                variant="tonal"
                class="text-none"
                :disabled="repo.loading || !report"
                rounded="lg"
                size="large"
                @click="repo.DownloadWeeklyFoulPdf()"
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
                @click="repo.DownloadWeeklyFoulWord()"
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
        <span>{{ t('برای هفته انتخاب‌شده هیچ دیتا موجود نیست.') }}</span>
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
              no-data-text="هیچ دیتا وجود ندارد"
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
              no-data-text="هیچ فورم دارای دیتا نیست"
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
                  {{ t('خلاصه رویدادها بر اساس نوعیت') }}
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
              :items="eventTypes"
              item-key="label"
              density="comfortable"
              @click:row="() => goEvents()"
              class="elevation-0"
              :loading="repo.loading"
              hide-default-footer
              no-data-text="هیچ رویدادی ثبت نشده"
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
          type="weekly"
          :anchor-date="repo.selectedDate"
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
  import ReportNotesPeriodTable from './ReportNotesPeriodTable.vue';

  import AppBar from '@/components/AppBar.vue';
  import DateInput from '@/components/DateInput.vue';
  import { useFoulWeeklyReportsRepository } from '@/stores/FoulWeeklyReportsRepository';
  import { useRouter } from 'vue-router';

  const { t, locale } = useI18n();

  // ✅ RTL/LTR exactly like your employee page
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  const repo = useFoulWeeklyReportsRepository();
  const report = computed(() => repo.weekly);

  const router = useRouter();

  // main routes
  const goResources = () => router.push('/app/resources');
  const goEvents = () => router.push('/app/events');
  const goInformations = () => router.push('/app/informations');
  const goFiftyFormatsHub = () => router.push('/app/fifthyformatsroute');

  // 56-format route from key (your API returns key like: "fasads", "robbers", "parks"...)
  function goFormatByKey(key) {
    if (!key) return;
    router.push(`/app/${key}`);
  }

  // information types (we only route to informations list)
  function goInfoType(label) {
    router.push('/app/informations');
  }

  // ✅ calendar type + pickedDate (we compute ISO week from picked date)
  const calendarType = ref('gregorian'); // 'gregorian' | 'jalali' | 'hijri'
  const pickedDate = ref(''); // YYYY-MM-DD (from DateInput)

  const calendarOptions = computed(() => [
    { title: t('میلادی'), value: 'gregorian' },
    { title: t('هجری شمسی'), value: 'jalali' },
    { title: t('هجری قمری'), value: 'hijri' },
  ]);

  function toLatinDigits(s) {
    const str = String(s ?? '');
    const fa = '۰۱۲۳۴۵۶۷۸۹';
    const ar = '٠١٢٣٤٥٦٧٨٩';
    return str
      .split('')
      .map((ch) => {
        const i1 = fa.indexOf(ch);
        if (i1 >= 0) return String(i1);
        const i2 = ar.indexOf(ch);
        if (i2 >= 0) return String(i2);
        return ch;
      })
      .join('');
  }

  // ISO week label from Date object: "YYYY-Www"
  function isoWeekLabelFromDate(dt) {
    if (!(dt instanceof Date) || Number.isNaN(dt.getTime())) return '';

    // Convert to UTC (safe)
    const date = new Date(Date.UTC(dt.getFullYear(), dt.getMonth(), dt.getDate()));
    const dayNum = date.getUTCDay() || 7; // Mon=1..Sun=7
    date.setUTCDate(date.getUTCDate() + 4 - dayNum); // nearest Thu => ISO year
    const isoYear = date.getUTCFullYear();

    const yearStart = new Date(Date.UTC(isoYear, 0, 1));
    const weekNo = Math.ceil(((date - yearStart) / 86400000 + 1) / 7);

    return `${isoYear}-W${String(weekNo).padStart(2, '0')}`;
  }

  // We show chipWeek in the same “date-display”
  const selectedWeekComputed = computed(() => {
    if (!pickedDate.value) return repo.selectedWeek || '';

    const [y, m, d] = String(pickedDate.value).split('-').map(Number);
    if (!y || !m || !d) return repo.selectedWeek || '';

    const dt = new Date(y, m - 1, d);
    const label = isoWeekLabelFromDate(dt);

    // normalize digits if DateInput produced non-latin (extra safety)
    return toLatinDigits(label);
  });

  const chipWeek = computed(() => {
    // prefer computed, else backend meta.week
    return String(selectedWeekComputed.value || report.value?.meta?.week || '').trim();
  });

  const eventTypes = computed(() => {
    const types = report.value?.events?.types ?? [];
    return Array.isArray(types) ? types : [];
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
    // keep repo.selectedWeek in sync for exports + API
    repo.selectedWeek = selectedWeekComputed.value || repo.selectedWeek;
    await repo.FetchWeeklyFoulReport(repo.selectedWeek, calendarType.value);
  }

  async function resetToCurrentWeek() {
    calendarType.value = 'gregorian';
    pickedDate.value = new Date().toISOString().slice(0, 10);
    await fetch();
  }

  onMounted(() => {
    // default to today
    pickedDate.value = new Date().toISOString().slice(0, 10);
    repo.selectedWeek = selectedWeekComputed.value || repo.selectedWeek;
    fetch();
  });
</script>

<style scoped>
  /* ✅ enforce your app.css font everywhere */
  .fontfamily,
  .fontfamily * {
    font-family: var(--app-font) !important;
  }

  .page-bg {
    background: #f6f7fb;
    min-height: 100vh;
  }

  /* ===== Header: smaller + professional ===== */
  .header-container {
    background: linear-gradient(135deg, #5b6eea 0%, #6d42a6 100%);
    border-radius: 14px;
    padding: 18px;
    position: relative;
    overflow: hidden;
  }

  /* offline-safe pattern (inline svg data URI) */
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

  /* ===== Filter card ===== */
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

  /* ===== Stats: smaller cards ===== */
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

  /* ===== Data cards ===== */
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

  /* ===== type indicator ===== */
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

  /* ===== table look ===== */
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
