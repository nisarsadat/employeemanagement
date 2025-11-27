<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- App Bar -->
    <AppBar
      :title="t('د پیښو فعالیت راپور')"
      :subtitle="t('د ولایتونو، زونونو او حوزو له مخې د ثبت شویو پیښو څارنه')"
      @toggle-drawer="drawer = !drawer"
    >
    </AppBar>

    <!-- Summary Cards -->
    <v-row v-if="summaryExists" class="mb-6" dense :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" sm="6" md="3">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="primary" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-calendar-multiple</v-icon>
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

      <v-col cols="12" sm="6" md="3">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="error" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-account-alert</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-grey">
                {{ t('د مجرم لرونکې پیښې') }}
              </div>
              <div class="text-h6 font-weight-bold text-error">
                {{ repo.summary.with_criminal || 0 }}
              </div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="warning" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-hospital-box</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-grey">{{ t('انساني تلفات لرونکې') }}</div>
              <div class="text-h6 font-weight-bold">
                {{ repo.summary.with_talafat_jani || 0 }}
              </div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card elevation="2" class="pa-3 summary-card" rounded="lg">
          <div class="d-flex align-center">
            <v-avatar color="secondary" variant="tonal" size="40" class="me-3">
              <v-icon>mdi-cash-multiple</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-grey">{{ t('مالي تلفات لرونکې') }}</div>
              <div class="text-h6 font-weight-bold">
                {{ repo.summary.with_talafat_mali || 0 }}
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
              :label="t('لټون د تفصیل، هدف او ځای له مخې')"
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

          <v-col cols="12" md="2">
            <v-text-field
              v-model.trim="repo.ProvinceAddr"
              :label="t('اساس ولایت ')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="2">
            <v-text-field
              v-model.trim="repo.Zone"
              :label="t('زون')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="2">
            <v-text-field
              v-model.trim="repo.Hawza"
              :label="t('حوزه')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="2">
            <v-text-field
              v-model.trim="repo.EventType"
              :label="t('د پیښې عمومي ډول')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="2">
            <v-text-field
              v-model.trim="repo.EventSpecificType"
              :label="t('د پیښې مشخص ډول')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model.trim="repo.TargetPerson"
              :label="t('هدف شخص / اداره')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-select
              v-model="repo.Criminal"
              :items="criminalItems"
              item-title="title"
              item-value="value"
              density="compact"
              variant="outlined"
              :label="t('د مجرم شتون')"
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model.trim="repo.OfficeEmployeeId"
              :label="t('د کارمند ID (اختیاري)')"
              type="number"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model.trim="repo.ResourceId"
              :label="t('د منبع ID (اختیاري)')"
              type="number"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <!-- Period & dates -->
          <v-col cols="12" md="4">
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

          <v-col cols="6" md="2" class="d-flex align-center">
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

        <!-- Export Buttons -->
        <v-row class="mb-4" justify="end" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-col cols="12" class="d-flex justify-end flex-wrap ga-2">
            <v-btn
              variant="outlined"
              @click="repo.exportCsv"
              :disabled="!repo.rows.length"
              class="bg-success"
            >
              <v-icon start size="small">mdi-file-delimited-outline</v-icon>
              {{ t('CSV / Excel') }}
            </v-btn>

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
        <v-icon start>mdi-map-marker-radius</v-icon>
        {{ t('د ولایتونو له مخې پیښې') }}
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
                      <v-icon start>mdi-calendar-multiple</v-icon>
                      {{ t('ټولې پیښې') }}:
                      {{ group.totals?.events ?? group.events?.length ?? 0 }}
                    </v-chip>
                    <v-chip color="error" variant="elevated">
                      <v-icon start>mdi-account-alert</v-icon>
                      {{ t('مجرم لرونکې') }}:
                      {{ group.totals?.with_criminal ?? 0 }}
                    </v-chip>
                    <v-chip variant="outlined" color="warning">
                      <v-icon start>mdi-hospital-box</v-icon>
                      {{ t('انساني تلفات') }}:
                      {{ group.totals?.with_talafat_jani ?? 0 }}
                    </v-chip>
                    <v-chip variant="outlined" color="secondary">
                      <v-icon start>mdi-cash-multiple</v-icon>
                      {{ t('مالي تلفات') }}:
                      {{ group.totals?.with_talafat_mali ?? 0 }}
                    </v-chip>
                    <v-icon :icon="expanded ? 'mdi-chevron-up' : 'mdi-chevron-down'" />
                  </div>
                </div>
              </div>
            </template>
          </v-expansion-panel-title>

          <v-expansion-panel-content>
            <v-data-table
              :items="group.events || []"
              :headers="headers"
              density="comfortable"
              class="elevation-1 mt-2 rounded-lg"
              :items-per-page="-1"
              hide-default-footer
            >
              <template #item.event_date="{ item }">
                <span v-if="item.event_date">
                  {{ formatDate(item.event_date) }}
                </span>
                <span v-else>-</span>
              </template>

              <template #item.event_type="{ item }">
                <v-chip size="small" variant="tonal" color="info">
                  {{ item.event_type || '---' }}
                </v-chip>
              </template>

              <template #item.event_specific_type="{ item }">
                <v-chip size="small" variant="outlined" color="secondary">
                  {{ item.event_specific_type || '---' }}
                </v-chip>
              </template>

              <template #item.event_zone="{ item }">
                <v-chip size="small" variant="outlined" color="primary">
                  {{ item.event_zone || '---' }}
                </v-chip>
              </template>

              <template #item.event_hawza="{ item }">
                <v-chip size="small" variant="outlined" color="success">
                  {{ item.event_hawza || '---' }}
                </v-chip>
              </template>

              <template #item.target_person="{ item }">
                <div class="text-body-2">
                  {{ item.target_person || '---' }}
                </div>
              </template>

              <template #item.criminal="{ item }">
                <v-chip
                  v-if="item.criminal"
                  size="small"
                  variant="flat"
                  color="error"
                  prepend-icon="mdi-account-alert"
                >
                  {{ item.criminal }}
                </v-chip>
                <span v-else>-</span>
              </template>

              <template #item.talafat_jani="{ item }">
                <v-chip v-if="item.talafat_jani" size="small" variant="tonal" color="warning">
                  {{ item.talafat_jani }}
                </v-chip>
                <span v-else>-</span>
              </template>

              <template #item.talafat_mali="{ item }">
                <v-chip v-if="item.talafat_mali" size="small" variant="tonal" color="secondary">
                  {{ item.talafat_mali }}
                </v-chip>
                <span v-else>-</span>
              </template>

              <template #item.employee="{ item }">
                <div v-if="item.office_employee" class="text-body-2">
                  {{ item.office_employee.name || '---' }}
                  <span class="text-caption text-grey ms-1">
                    ({{ item.office_employee.code_number || '---' }})
                  </span>
                </div>
                <span v-else>---</span>
              </template>

              <template #item.resource="{ item }">
                <div v-if="item.resource" class="text-body-2">
                  {{ item.resource.name || '---' }}
                  <span class="text-caption text-grey ms-1">
                    ({{ item.resource.code_number || '---' }})
                  </span>
                </div>
                <span v-else>---</span>
              </template>

              <template #item.days_since_event="{ item }">
                <v-chip
                  v-if="item.days_since_event != null"
                  size="small"
                  :color="daysColor(Math.round(item.days_since_event))"
                  variant="flat"
                  :prepend-icon="getDaysIcon(Math.round(item.days_since_event))"
                >
                  {{ Math.round(item.days_since_event) }}
                </v-chip>
                <span v-else>-</span>
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
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import DateInput from '@/components/DateInput.vue';
  import { useCalendar } from '../../../composables/useCalendar';
  import { useEventsReportRepository } from '@/stores/EventsReportRepository';

  const { t, locale } = useI18n();
  const { calendar, formatForDisplay } = useCalendar();

  const isRtl = computed(() => ['fa', 'ps', 'prs', 'fa-AF'].includes(locale.value));

  const drawer = ref(false);
  const repo = useEventsReportRepository();
  const openPanels = ref([]);

  const criminalItems = [
    { title: t('ټول'), value: 'all' },
    { title: t('یوازې د مجرم لرونکې'), value: 'yes' },
    { title: t('یوازې بې مجرمه'), value: 'no' },
  ];

  const headers = computed(() => [
    { title: t('نېټه'), value: 'event_date', width: '9%' },
    { title: t('ساعت'), value: 'event_hour', width: '6%' },
    { title: t('ډول'), value: 'event_type', width: '10%' },
    { title: t('مشخص ډول'), value: 'event_specific_type', width: '12%' },
    { title: t('زون'), value: 'event_zone', width: '8%' },
    { title: t('حوزه'), value: 'event_hawza', width: '8%' },
    { title: t('هدف شخص/اداره'), value: 'target_person', width: '14%' },
    { title: t('مجرم'), value: 'criminal', width: '8%' },
    { title: t('انساني تلفات'), value: 'talafat_jani', width: '10%' },
    { title: t('مالي تلفات'), value: 'talafat_mali', width: '10%' },
    { title: t('کارمند'), value: 'employee', width: '15%' },
    { title: t('منبع'), value: 'resource', width: '15%' },
    {
      title: t('ورځې له پیښې وروسته'),
      value: 'days_since_event',
      width: '8%',
      align: 'center',
    },
  ]);

  const summaryExists = computed(() => !!Object.keys(repo.summary || {}).length);

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

  function formatDate(dateString) {
    if (!dateString) return '';
    if (typeof formatForDisplay === 'function') {
      try {
        return formatForDisplay(dateString);
      } catch {
        // ignore
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
    repo.ProvinceAddr = '';
    repo.Zone = '';
    repo.Hawza = '';
    repo.EventType = '';
    repo.EventSpecificType = '';
    repo.TargetPerson = '';
    repo.Criminal = 'all';
    repo.OfficeEmployeeId = '';
    repo.ResourceId = '';
    repo.Period = 'all';
    repo.From = '';
    repo.To = '';
    repo.ignoreDates = 1;
    openPanels.value = [];
    repo.fetchReport();
  }

  onMounted(() => {
    repo.fetchReport();
  });

  watch(
    () => [repo.ProvinceAddr, repo.Zone, repo.Hawza, repo.EventType, repo.Criminal],
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
