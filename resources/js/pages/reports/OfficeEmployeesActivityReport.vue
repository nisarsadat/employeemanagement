<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- App Bar -->
    <AppBar
      :title="t('د کارمندان د فعالیت راپور')"
      :subtitle="t('د ریاست کارمندان د فعالیت څارنه – په ولایتونو، زونونو او حوزو کې')"
      @toggle-drawer="drawer = !drawer"
    >
    </AppBar>

    <!-- Summary Cards -->
    <v-row v-if="summaryExists" class="mb-6" dense :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" sm="6" md="4" lg="2">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="primary" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-grey">{{ t('ټول کارمندان') }}</div>
              <div class="text-h6 font-weight-bold">
                {{ repo.summary.employees || 0 }}
              </div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="4" lg="2">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="success" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-account-check</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-grey">{{ t('فعال') }}</div>
              <div class="text-h6 font-weight-bold text-success">
                {{ repo.summary.active || 0 }}
              </div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="4" lg="2">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="error" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-account-off</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-grey">{{ t('غیر فعال') }}</div>
              <div class="text-h6 font-weight-bold text-error">
                {{ repo.summary.inactive || 0 }}
              </div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="4" lg="2">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="info" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-calendar-check</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-grey">{{ t('ټولې پیښې') }}</div>
              <div class="text-h6 font-weight-bold">
                {{ repo.summary.events || 0 }}
              </div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="4" lg="2">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="warning" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-folder-multiple</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-grey">{{ t('منابع') }}</div>
              <div class="text-h6 font-weight-bold">
                {{ repo.summary.resources || 0 }}
              </div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="4" lg="2">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="secondary" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-information-outline</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-grey">{{ t('معلومات') }}</div>
              <div class="text-h6 font-weight-bold">
                {{ repo.summary.informations || 0 }}
              </div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters Card -->
    <v-card class="mb-6" elevation="2" rounded="lg" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="d-flex align-center py-3">
        <v-icon start>mdi-filter-variant</v-icon>
        {{ t('فلټرونه') }}
        <v-spacer />
        <v-btn variant="outlined" class="bg-error" @click="resetFilters">
          {{ t('فلټرونه پاک کړه') }}
        </v-btn>
      </v-card-title>
      <v-divider />
      <v-card-text class="pt-4">
        <v-row dense>
          <v-col cols="12" md="4">
            <v-text-field
              v-model.trim="repo.Search"
              :label="t('لټون د نوم/کود/شمېره له مخې')"
              density="compact"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              clearable
              @keyup.enter="repo.fetchReport"
              @click:clear="
                () => {
                  repo.Search = '';
                  repo.fetchReport();
                }
              "
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-select
              v-model="repo.Status"
              :label="t('حالت')"
              :items="statusItems"
              item-title="title"
              item-value="value"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="2">
            <v-text-field
              v-model.trim="repo.ProvinceAddr"
              :label="t('اساس ولایت ')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model.trim="repo.NowProvince"
              :label="t('اوسنی ولایت ')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model.trim="repo.Zone"
              :label="t('زون')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model.trim="repo.Hawza"
              :label="t('حوزه')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model.trim="repo.MadaniHalat"
              :label="t('مدني حالت')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <!-- Period & dates -->
          <v-col cols="12" md="3">
            <v-btn-toggle
              v-model="repo.Period"
              density="compact"
              variant="outlined"
              color="red"
              class="w-100 bg-primary"
              divided
            >
              <v-btn value="daily" size="small">{{ t('ورځنی') }}</v-btn>
              <v-btn value="weekly" size="small">{{ t('اونیز') }}</v-btn>
              <v-btn value="monthly" size="small">{{ t('میاشتنی') }}</v-btn>
              <v-btn value="yearly" size="small">{{ t('کلنی') }}</v-btn>
              <v-btn value="all" size="small">{{ t('ټول وخت') }}</v-btn>
            </v-btn-toggle>
          </v-col>

          <v-col cols="6" md="3">
            <DateInput
              v-model="repo.From"
              :label="t('له نېټې')"
              density="compact"
              :calendar="calendar"
            />
          </v-col>

          <v-col cols="6" md="3">
            <DateInput
              v-model="repo.To"
              :label="t('تر نېټې')"
              density="compact"
              :calendar="calendar"
            />
          </v-col>

          <v-col cols="6" md="3" class="d-flex align-center">
            <v-switch
              v-model="allTimeModel"
              :label="t('ټوله موده')"
              color="primary"
              hide-details
              density="compact"
            />
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" class="d-flex justify-end">
            <v-btn
              color="primary"
              class="mt-2"
              :loading="repo.loading"
              @click="repo.fetchReport"
              prepend-icon="mdi-chart-bar"
            >
              {{ t('راپور وګورئ') }}
            </v-btn>
          </v-col>
        </v-row>
        <!-- ========================= -->
        <!-- Export Buttons (Inside)   -->
        <!-- ========================= -->
        <v-row class="mb-4" justify="end" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-col cols="12" class="d-flex justify-end flex-wrap ga-2">
            <!-- CSV / Excel -->
            <v-btn
              variant="outlined"
              @click="repo.exportCsv"
              :disabled="!repo.rows.length"
              class="bg-success"
            >
              <v-icon start size="small">mdi-file-delimited-outline</v-icon>
              {{ t('CSV / Excel') }}
            </v-btn>

            <!-- PDF -->
            <v-btn
              variant="outlined"
              @click="repo.exportPdf"
              :disabled="!repo.rows.length"
              class="bg-error"
            >
              <v-icon start size="small">mdi-file-pdf-box</v-icon>
              {{ t('PDF (پښتو راپور)') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Error Alert -->
    <v-alert
      v-if="repo.error"
      type="error"
      class="mb-4"
      variant="tonal"
      density="compact"
      border="start"
      icon="mdi-alert-circle-outline"
    >
      {{ repo.error }}
    </v-alert>

    <!-- Loading State -->
    <v-skeleton-loader v-if="repo.loading" class="mt-4" type="card, table" />

    <!-- Provinces / Groups -->
    <v-card
      v-else-if="repo.rows.length"
      class="mb-8"
      elevation="2"
      rounded="lg"
      :dir="isRtl ? 'rtl' : 'ltr'"
    >
      <v-card-title class="d-flex align-center py-3">
        <v-icon start>mdi-account-details</v-icon>
        {{ t('د ولایتونو له مخې کارمندان') }}
        <v-spacer />
        <span class="text-caption text-medium-emphasis">
          {{ t('ټول') }}: {{ repo.rows.length }} {{ t('ولایتونه') }}
        </span>
      </v-card-title>
      <v-divider />

      <v-expansion-panels v-model="openPanels" multiple class="pa-2">
        <v-expansion-panel v-for="(group, idx) in repo.rows" :key="idx" class="mb-2">
          <v-expansion-panel-title class="py-3">
            <template #default="{ expanded }">
              <div class="d-flex flex-column w-100">
                <div class="d-flex justify-space-between align-center">
                  <div class="d-flex align-center">
                    <v-avatar size="32" color="primary" variant="tonal" class="me-3">
                      <v-icon>mdi-map-marker</v-icon>
                    </v-avatar>
                    <span class="text-subtitle-1 font-weight-medium">
                      {{ group.province_addr || '---' }}
                    </span>
                  </div>
                  <div class="d-flex align-center flex-wrap ga-2">
                    <v-chip variant="elevated" color="primary">
                      <v-icon start>mdi-account</v-icon>
                      {{ t('کارمندان') }}:
                      {{ group.totals?.employees ?? group.employees?.length ?? 0 }}
                    </v-chip>
                    <v-chip color="success" variant="elevated">
                      <v-icon start>mdi-check</v-icon>
                      {{ t('فعال') }}:
                      {{ group.totals?.active ?? 0 }}
                    </v-chip>
                    <v-chip color="error" variant="elevated">
                      <v-icon start>mdi-close</v-icon>
                      {{ t('غیر فعال') }}:
                      {{ group.totals?.inactive ?? 0 }}
                    </v-chip>
                    <v-chip variant="outlined" color="info">
                      <v-icon start>mdi-calendar</v-icon>
                      {{ t('پیښې') }}:
                      {{ group.totals?.events ?? 0 }}
                    </v-chip>
                    <v-chip variant="outlined" color="warning">
                      <v-icon start>mdi-folder</v-icon>
                      {{ t('منابع') }}:
                      {{ group.totals?.resources ?? 0 }}
                    </v-chip>
                    <v-chip variant="outlined" color="secondary">
                      <v-icon start>mdi-information</v-icon>
                      {{ t('معلومات') }}:
                      {{ group.totals?.informations ?? 0 }}
                    </v-chip>
                    <v-icon :icon="expanded ? 'mdi-chevron-up' : 'mdi-chevron-down'" />
                  </div>
                </div>
              </div>
            </template>
          </v-expansion-panel-title>

          <v-expansion-panel-content>
            <v-data-table
              :items="group.employees || []"
              :headers="headers"
              density="comfortable"
              class="elevation-1 mt-2 rounded-lg"
              :items-per-page="-1"
              hide-default-footer
            >
              <template #item.activity="{ item }">
                <v-chip
                  size="small"
                  :color="
                    item.activity === 'فعال' || item.activity === 'active' ? 'success' : 'grey'
                  "
                  variant="flat"
                  :prepend-icon="
                    item.activity === 'فعال' || item.activity === 'active'
                      ? 'mdi-check'
                      : 'mdi-close'
                  "
                >
                  {{ getActivityText(item.activity) }}
                </v-chip>
              </template>

              <template #item.days_since_last_activity="{ item }">
                <v-chip
                  v-if="item.days_since_last_activity != null"
                  size="small"
                  :color="daysColor(Math.round(item.days_since_last_activity))"
                  variant="flat"
                  :prepend-icon="getDaysIcon(Math.round(item.days_since_last_activity))"
                >
                  {{ Math.round(item.days_since_last_activity) }}
                </v-chip>
                <span v-else>-</span>
              </template>

              <template #item.last_activity_date="{ item }">
                <span v-if="item.last_activity_date">
                  {{ formatDate(item.last_activity_date) }}
                </span>
                <span v-else>-</span>
              </template>

              <!-- CLICKABLE chips -->
              <template #item.events_count="{ item }">
                <v-chip
                  size="small"
                  variant="outlined"
                  color="info"
                  class="cursor-pointer"
                  @click="goToRelation(item, 'events')"
                >
                  <v-icon start size="small">mdi-calendar-check</v-icon>
                  {{ item.events_count || 0 }}
                </v-chip>
              </template>

              <template #item.resources_count="{ item }">
                <v-chip
                  size="small"
                  variant="outlined"
                  color="warning"
                  class="cursor-pointer"
                  @click="goToRelation(item, 'resources')"
                >
                  <v-icon start size="small">mdi-folder</v-icon>
                  {{ item.resources_count || 0 }}
                </v-chip>
              </template>

              <template #item.informations_count="{ item }">
                <v-chip
                  size="small"
                  variant="outlined"
                  color="secondary"
                  class="cursor-pointer"
                  @click="goToRelation(item, 'informations')"
                >
                  <v-icon start size="small">mdi-information</v-icon>
                  {{ item.informations_count || 0 }}
                </v-chip>
              </template>
            </v-data-table>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-expansion-panels>
    </v-card>

    <!-- Empty State -->
    <v-card
      v-else-if="!repo.loading && !repo.error"
      elevation="2"
      class="text-center py-8"
      rounded="lg"
    >
      <v-icon size="64" color="grey-lighten-1" class="mb-4"> mdi-database-remove-outline </v-icon>
      <div class="text-h6 text-grey mb-2">
        {{ t('هیڅ نتیجه ونه موندل شوه') }}
      </div>
      <div class="text-body-2 text-grey mb-4">
        {{ t('فلټرونه بدل کړه او بیا راپور ترلاسه کړه.') }}
      </div>
      <v-btn color="primary" @click="resetFilters">
        {{ t('فلټرونه پاک کړه') }}
      </v-btn>
    </v-card>
  </v-container>
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import DateInput from '../../components/DateInput.vue';
  import { useOfficeEmployeesReportRepository } from '@/stores/OfficeEmployeesReportRepository';
  import { useCalendar } from '../../../composables/useCalendar';

  const { t, locale } = useI18n();
  const router = useRouter();

  const isRtl = computed(() => ['fa', 'ps', 'prs', 'fa-AF'].includes(locale.value));

  // Calendar composable (assumes you have formatForDisplay there)
  const { calendar, formatForDisplay } = useCalendar();

  const drawer = ref(false);
  const repo = useOfficeEmployeesReportRepository();
  const openPanels = ref([]);

  const statusItems = [
    { title: t('ټول'), value: 'all' },
    { title: t('فعال (Dari)'), value: 'فعال' },
    { title: t('غیر فعال (Dari)'), value: 'غیر فعال' },
    { title: t('Active (EN)'), value: 'active' },
    { title: t('Inactive (EN)'), value: 'inactive' },
  ];

  const headers = computed(() => [
    { title: t('نوم'), value: 'name', width: '12%' },
    { title: t('د پلار نوم'), value: 'father_name', width: '10%' },
    { title: t('کود نمبر'), value: 'code_number', width: '8%' },
    { title: t('تلیفون'), value: 'phone_number', width: '10%' },
    { title: t('زون'), value: 'zone', width: '8%' },
    { title: t('حوزه'), value: 'hawza', width: '8%' },
    { title: t('مدني حالت'), value: 'madani_halat', width: '8%' },
    { title: t('حالت'), value: 'activity', width: '8%' },
    {
      title: t('پیښې'),
      value: 'events_count',
      width: '6%',
      align: 'center',
    },
    {
      title: t('منابع'),
      value: 'resources_count',
      width: '6%',
      align: 'center',
    },
    {
      title: t('معلومات'),
      value: 'informations_count',
      width: '6%',
      align: 'center',
    },
    { title: t('وروستی فعالیت'), value: 'last_activity_date', width: '10%' },
    {
      title: t('ورځې له وروستي فعالیته'),
      value: 'days_since_last_activity',
      width: '8%',
      align: 'center',
    },
  ]);

  const summaryExists = computed(() => !!Object.keys(repo.summary || {}).length);

  // sync switch with ignoreDates
  const allTimeModel = computed({
    get() {
      return !!repo.ignoreDates;
    },
    set(val) {
      repo.setAllTime(val);
    },
  });

  function daysColor(days) {
    if (days == null) return 'grey';
    if (days <= 7) return 'success';
    if (days <= 30) return 'warning';
    return 'error';
  }

  function getDaysIcon(days) {
    if (days <= 7) return 'mdi-timer-outline';
    if (days <= 30) return 'mdi-timer-sand-empty';
    return 'mdi-timer-off-outline';
  }

  function getActivityText(activity) {
    if (activity === 'active') return t('فعال');
    if (activity === 'inactive') return t('غیر فعال');
    return activity;
  }

  // Format as month/day/year (Gregorian) but respect your calendar formatter if present
  function formatDate(dateString) {
    if (!dateString) return '';
    if (typeof formatForDisplay === 'function') {
      try {
        return formatForDisplay(dateString);
      } catch {
        // fallback
      }
    }
    const d = new Date(dateString);
    if (Number.isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('en-US', {
      month: '2-digit',
      day: '2-digit',
      year: 'numeric',
    });
  }

  function resetFilters() {
    repo.Search = '';
    repo.Status = 'all';
    repo.ProvinceAddr = '';
    repo.NowProvince = '';
    repo.Zone = '';
    repo.Hawza = '';
    repo.MadaniHalat = '';
    repo.Period = 'all';
    repo.From = '';
    repo.To = '';
    repo.ignoreDates = 1;
    openPanels.value = [];
    repo.fetchReport();
  }

  /**
   * Navigate to detail page for a given employee + relation
   * relation: 'events' | 'resources' | 'informations'
   */
  function goToRelation(item, relation) {
    router.push({
      name: 'office-employees-activity-report',
      params: {
        id: item.id,
        relation,
      },
      query: {
        period: repo.Period || undefined,
        from_date: repo.From || undefined,
        to_date: repo.To || undefined,
      },
    });
  }

  onMounted(() => {
    repo.fetchReport();
  });

  // when filters that deeply affect grouping change, collapse panels
  watch(
    () => [repo.ProvinceAddr, repo.NowProvince, repo.Zone, repo.Status],
    () => {
      openPanels.value = [];
    }
  );
</script>

<style scoped>
  .summary-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
  }

  .summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
  }

  .v-expansion-panel {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 8px;
  }

  :deep(.v-data-table) {
    border-radius: 8px;
  }

  :deep(.v-data-table-header) {
    background-color: rgba(0, 0, 0, 0.02);
  }

  .cursor-pointer {
    cursor: pointer;
  }
</style>
