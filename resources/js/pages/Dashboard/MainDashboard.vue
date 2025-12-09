<template>
  <!-- APP BAR (same pattern as other pages) -->
  <AppBar :title="t('د کارمند مدیریت عمومي داشبورد')" @toggle-drawer="drawer = !drawer">
    <template #actions>
      <v-btn icon />
    </template>
  </AppBar>

  <v-container fluid class="page-bg" :dir="isRtl ? 'rtl' : 'ltr'">
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card elevation="2" class="pa-3 rounded-xl">
          <!-- HEADER + REFRESH -->
          <div class="d-flex justify-space-between align-center mb-3">
            <div>
              <div class="text-subtitle-1 font-weight-bold">
                {{ t('د کارمند مدیریت عمومي انځور') }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{
                  t(
                    'ټول دفتر کارمندان، منابع، اطلاعات، واقعات، تسلیم شوي بندیان او ۵۶ فارمتونه په یوه صفحه کې.'
                  )
                }}
              </div>
            </div>

            <v-btn
              variant="text"
              class="text-none"
              prepend-icon="mdi-refresh"
              :loading="isAnyLoading"
              @click="refreshAll"
            >
              {{ t('ټول تازه کول') }}
            </v-btn>
          </div>

          <!-- ========== STAT CARDS ========== -->
          <v-row dense class="mb-4">
            <!-- Employees -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 elevation-2 rounded-xl">
                <div class="d-flex justify-space-between align-center">
                  <div>
                    <div class="text-caption text-medium-emphasis">
                      {{ t('ټول دفتر کارمندان') }}
                    </div>
                    <div class="text-h5 font-weight-bold">
                      {{ repo.cards.total_employees }}
                    </div>
                  </div>
                  <v-avatar size="32">
                    <v-icon>mdi-account-group</v-icon>
                  </v-avatar>
                </div>
              </v-card>
            </v-col>

            <!-- Resources total + active/inactive -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 elevation-2 rounded-xl">
                <div class="text-caption text-medium-emphasis">
                  {{ t('ټول منابع') }}
                </div>
                <div class="text-h5 font-weight-bold">
                  {{ repo.cards.total_resources }}
                </div>
                <div class="text-caption text-medium-emphasis d-flex justify-space-between mt-1">
                  <span>{{ t('فعال') }}: {{ repo.cards.total_resources_active }}</span>
                  <span>{{ t('غیر فعال') }}: {{ repo.cards.total_resources_inactive }}</span>
                </div>
              </v-card>
            </v-col>

            <!-- Informations -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 elevation-2 rounded-xl">
                <div class="text-caption text-medium-emphasis">
                  {{ t('ټول اطلاعات') }}
                </div>
                <div class="text-h5 font-weight-bold">
                  {{ repo.cards.total_informations }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1">
                  {{ t('پر منابع او افرادو') }}
                </div>
              </v-card>
            </v-col>

            <!-- Events -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 elevation-2 rounded-xl">
                <div class="text-caption text-medium-emphasis">
                  {{ t('ټولې پیښې') }}
                </div>
                <div class="text-h5 font-weight-bold">
                  {{ repo.cards.total_events }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1">
                  {{ t('په سیستم کې ثبت شوي') }}
                </div>
              </v-card>
            </v-col>

            <!-- Captive deliveries -->
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-3 elevation-2 rounded-xl mt-2">
                <div class="text-caption text-medium-emphasis">
                  {{ t('ټول تسلیم شوي بندیان') }}
                </div>
                <div class="text-h5 font-weight-bold">
                  {{ repo.cards.total_captive_deliveries }}
                </div>
              </v-card>
            </v-col>

            <!-- Money summary -->
            <v-col cols="12" sm="6" md="4">
              <v-card class="pa-3 elevation-2 rounded-xl mt-2">
                <div class="text-caption text-medium-emphasis">
                  {{ t('مالي وضعیت (پیسې)') }}
                </div>
                <div class="text-subtitle-1 font-weight-bold">
                  {{ t('ټول موجوده پیسې') }}:
                  {{ repo.cards.total_money_balance }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1">
                  {{ t('عاید') }}: {{ repo.cards.total_money_income }}
                  &nbsp; | &nbsp;
                  {{ t('مصرف') }}: {{ repo.cards.total_money_expense }}
                </div>
              </v-card>
            </v-col>

            <!-- Oil summary -->
            <v-col cols="12" sm="6" md="4">
              <v-card class="pa-3 elevation-2 rounded-xl mt-2">
                <div class="text-caption text-medium-emphasis">
                  {{ t('د تېلو حساب') }}
                </div>
                <div class="text-subtitle-1 font-weight-bold">
                  {{ t('ټول موجود تېل') }}:
                  {{ repo.cards.total_oil_balance }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1">
                  {{ t('عاید') }}: {{ repo.cards.total_oil_income }}
                  &nbsp; | &nbsp;
                  {{ t('مصرف') }}: {{ repo.cards.total_oil_expense }}
                </div>
              </v-card>
            </v-col>
          </v-row>

          <!-- ========== TIME FILTER + GRAPHS ========== -->
          <v-row dense>
            <!-- Filter (MODE buttons instead of date range) -->
            <v-col cols="12" md="4">
              <v-card class="pa-3 rounded-xl elevation-1">
                <div class="d-flex justify-space-between align-center mb-2">
                  <div class="text-subtitle-2 font-weight-medium">
                    {{ t('مهالوېش فلټر') }}
                  </div>
                  <v-btn
                    size="small"
                    variant="text"
                    prepend-icon="mdi-refresh"
                    :loading="repo.isLoadingTimeseries"
                    @click="refreshTimeseries"
                  >
                    {{ t('ګراف تازه کول') }}
                  </v-btn>
                </div>

                <!-- MODE BUTTONS -->
                <div class="mode-buttons" :class="isRtl ? 'mode-buttons-rtl' : 'mode-buttons-ltr'">
                  <v-btn
                    v-for="mode in timeModes"
                    :key="mode.value"
                    size="small"
                    variant="outlined"
                    class="text-none"
                    :color="repo.timeseriesMode === mode.value ? 'primary' : undefined"
                    :class="{
                      'mode-active': repo.timeseriesMode === mode.value,
                    }"
                    @click="setMode(mode.value)"
                  >
                    {{ mode.label }}
                  </v-btn>
                </div>

                <div class="text-caption text-medium-emphasis mt-2">
                  {{
                    t(
                      'یو حالت وټاکه: ورځنی، اونیز، میاشتنی، کلنی یا ټول وخت. ټول وخت د پروژې له پیل نه تر اوسه پورې معلومات ښيي.'
                    )
                  }}
                </div>
              </v-card>
            </v-col>

            <!-- Resources over time -->
            <v-col cols="12" md="8">
              <v-card class="pa-3 rounded-xl elevation-1 h-100">
                <div class="d-flex justify-space-between align-center mb-2">
                  <div class="text-subtitle-2 font-weight-medium">
                    {{ t('منابع د وخت په تېرېدو') }}
                  </div>
                </div>

                <!-- ✅ SHOW DATE LABELS -->
                <v-sparkline
                  :value="resourceSeries.data"
                  :labels="resourceSeries.labels"
                  smooth
                  auto-draw
                  auto-draw-duration="800"
                  height="80"
                  show-labels
                  label-size="9"
                />

                <div class="text-caption text-medium-emphasis mt-1">
                  {{ t('د ټاکل شوي مودې په جریان کې د منابعو شمېر ښيي.') }}
                </div>
              </v-card>
            </v-col>

            <!-- Informations over time -->
            <v-col cols="12" md="6" class="mt-4">
              <v-card class="pa-3 rounded-xl elevation-1 h-100">
                <div class="text-subtitle-2 font-weight-medium mb-2">
                  {{ t('اطلاعات د وخت په تېرېدو') }}
                </div>

                <!-- ✅ SHOW DATE LABELS -->
                <v-sparkline
                  :value="informationSeries.data"
                  :labels="informationSeries.labels"
                  smooth
                  auto-draw
                  auto-draw-duration="800"
                  height="80"
                  show-labels
                  label-size="9"
                />

                <div class="text-caption text-medium-emphasis mt-1">
                  {{ t('د ټاکل شوي مودې په جریان کې ثبت شوي اطلاعات.') }}
                </div>
              </v-card>
            </v-col>

            <!-- Events over time -->
            <v-col cols="12" md="6" class="mt-4">
              <v-card class="pa-3 rounded-xl elevation-1 h-100">
                <div class="text-subtitle-2 font-weight-medium mb-2">
                  {{ t('پیښې د وخت په تېرېدو') }}
                </div>

                <!-- ✅ SHOW DATE LABELS -->
                <v-sparkline
                  :value="eventSeries.data"
                  :labels="eventSeries.labels"
                  smooth
                  auto-draw
                  auto-draw-duration="800"
                  height="80"
                  show-labels
                  label-size="9"
                />

                <div class="text-caption text-medium-emphasis mt-1">
                  {{ t('د ټاکل شوي مودې په جریان کې ثبت شوي پیښې.') }}
                </div>
              </v-card>
            </v-col>
          </v-row>

          <!-- ========== 56 FORMATS BY HAWZA - CHART INSTEAD OF TABLE ========== -->
          <v-row class="mt-6">
            <v-col cols="12">
              <v-card class="pa-3 rounded-xl elevation-2">
                <div class="d-flex justify-space-between align-center mb-3">
                  <div>
                    <div class="text-subtitle-1 font-weight-medium">
                      {{ t('۵۶ فارمتونه د حوزې له مخې') }}
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      {{
                        t(
                          'هر نقطه د یوې حوزې د ټولو فارمتونو مجموع ښيي. د افقي محور په اوږدو کې حوزې ښکاري.'
                        )
                      }}
                    </div>
                  </div>

                  <v-btn
                    size="small"
                    variant="text"
                    prepend-icon="mdi-refresh"
                    :loading="repo.isLoadingFormats"
                    @click="repo.FetchFormatsByHawza"
                  >
                    {{ t('فارمتونه تازه کول') }}
                  </v-btn>
                </div>

                <!-- 📈 HAWZA TOTALS CHART -->
                <v-sparkline
                  :value="hawzaSeries.data"
                  :labels="hawzaSeries.labels"
                  smooth
                  auto-draw
                  auto-draw-duration="800"
                  height="100"
                  show-labels
                  label-size="9"
                />

                <div class="text-caption text-medium-emphasis mt-1">
                  {{
                    t(
                      'هر لیبل یوه حوزه ده او ارزښت یې د هغې حوزې د ټولو فارمتونو مجموع ده.'
                    )
                  }}
                </div>
              </v-card>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

import AppBar from '@/components/AppBar.vue';
import { useEmployeeManagementDashboardRepository } from '@/stores/EmployeeManagementDashboardRepository';

const { t, locale } = useI18n();
const repo = useEmployeeManagementDashboardRepository();

const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));
const drawer = ref(false);

// chart series
const resourceSeries = computed(() => repo.getResourceSeriesForChart());
const informationSeries = computed(() => repo.getInformationSeriesForChart());
const eventSeries = computed(() => repo.getEventSeriesForChart());

// ✅ NEW: hawza totals series for the last chart
const hawzaSeries = computed(() => {
  const rows = repo.hawzaRows || [];
  const formatKeys = repo.formatKeys || [];

  const labels = rows.map((r) => r.hawza);
  const data = rows.map((r) => {
    if (typeof r.total === 'number') {
      return r.total;
    }
    // fallback: sum all format columns if total not present
    return formatKeys.reduce((sum, key) => {
      const val = Number(r[key] || 0);
      return sum + (Number.isFinite(val) ? val : 0);
    }, 0);
  });

  return { labels, data };
});

const isAnyLoading = computed(
  () => repo.isLoadingCards || repo.isLoadingTimeseries || repo.isLoadingFormats
);

// time modes for the buttons
const timeModes = [
  { value: 'daily', label: 'ورځنی' },
  { value: 'weekly', label: 'اونیز' },
  { value: 'monthly', label: 'میاشتنی' },
  { value: 'yearly', label: 'کلنی' },
  { value: 'all', label: 'ټول وخت' },
];

const setMode = async (mode) => {
  if (repo.timeseriesMode === mode) return;
  repo.timeseriesMode = mode;
  await refreshTimeseries();
};

const refreshTimeseries = async () => {
  await repo.FetchDashboardTimeseries();
};

const refreshAll = async () => {
  await Promise.all([
    repo.FetchDashboardCards(),
    repo.FetchDashboardTimeseries(),
    repo.FetchFormatsByHawza(),
  ]);
};

onMounted(refreshAll);
</script>

<style scoped>
.text-no-wrap {
  white-space: nowrap;
}

.v-data-table {
  font-size: 11px;
}

.mode-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 4px;
}

.mode-buttons-ltr {
  justify-content: flex-start;
}

.mode-buttons-rtl {
  justify-content: flex-end;
  direction: rtl;
}

.mode-active {
  font-weight: 600;
}
</style>
