<!-- src/pages/Dashboard.vue -->
<template>
  <div class="dashboard-container" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('داشبورد')" :subtitle="t('خلاصه سیستم و درجه‌بندی کارمندان')" />

    <!-- TOP: LOAD / ERROR -->
    <transition name="slide-down">
      <v-alert
        v-if="repo.error"
        type="error"
        variant="tonal"
        rounded="lg"
        class="mb-4 mx-4"
        :text="readableError"
        icon="mdi-alert-circle"
      />
    </transition>

    <!-- STATS CARDS WITH ANIMATION -->
    <div class="stats-grid px-4 mb-6">
      <transition-group name="stagger-fade" tag="div" class="row" appear>
        <v-col cols="12" sm="6" md="3" key="employees">
          <v-card
            rounded="xl"
            class="stat-card elevation-1 hover-elevation-3 transition-swing"
            :color="statsColors.employees.bg"
            :variant="statsColors.employees.variant"
          >
            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between">
                <div>
                  <div class="text-caption text-medium-emphasis mb-1 d-flex align-center gap-1">
                    <v-icon size="16" class="mr-1">mdi-account-multiple</v-icon>
                    {{ t('کارمندان') }}
                  </div>
                  <div class="text-h4 font-weight-black text-primary">{{ employeesTotal }}</div>
                  <v-progress-linear
                    :model-value="100"
                    color="primary"
                    height="4"
                    class="mt-3 rounded-pill"
                    rounded
                  />
                </div>
                <v-avatar size="56" :color="statsColors.employees.iconBg" class="elevation-2">
                  <v-icon size="28" :color="statsColors.employees.iconColor">mdi-account-multiple</v-icon>
                </v-avatar>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3" key="resources">
          <v-card
            rounded="xl"
            class="stat-card elevation-1 hover-elevation-3 transition-swing"
            :color="statsColors.resources.bg"
            :variant="statsColors.resources.variant"
          >
            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between">
                <div>
                  <div class="text-caption text-medium-emphasis mb-1 d-flex align-center gap-1">
                    <v-icon size="16" class="mr-1">mdi-folder-account</v-icon>
                    {{ t('منابع') }}
                  </div>
                  <div class="text-h4 font-weight-black text-teal">{{ resourcesTotal }}</div>
                  <div class="d-flex align-center mt-2 gap-2">
                    <v-chip size="small" variant="tonal" color="success" class="px-2">
                      <v-icon start size="14">mdi-check-circle</v-icon>
                      {{ resourcesActive }}
                    </v-chip>
                    <v-chip size="small" variant="tonal" color="grey" class="px-2">
                      <v-icon start size="14">mdi-close-circle</v-icon>
                      {{ resourcesInactive }}
                    </v-chip>
                  </div>
                </div>
                <v-avatar size="56" :color="statsColors.resources.iconBg" class="elevation-2">
                  <v-icon size="28" :color="statsColors.resources.iconColor">mdi-folder-account</v-icon>
                </v-avatar>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3" key="informations">
          <v-card
            rounded="xl"
            class="stat-card elevation-1 hover-elevation-3 transition-swing"
            :color="statsColors.informations.bg"
            :variant="statsColors.informations.variant"
          >
            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between">
                <div>
                  <div class="text-caption text-medium-emphasis mb-1 d-flex align-center gap-1">
                    <v-icon size="16" class="mr-1">mdi-file-document-multiple</v-icon>
                    {{ t('اطلاعات') }}
                  </div>
                  <div class="text-h4 font-weight-black text-indigo">{{ informationsTotal }}</div>
                  <div class="mt-3">
                    <v-progress-linear
                      :model-value="100"
                      color="indigo"
                      height="4"
                      class="rounded-pill"
                      rounded
                    />
                  </div>
                </div>
                <v-avatar size="56" :color="statsColors.informations.iconBg" class="elevation-2">
                  <v-icon size="28" :color="statsColors.informations.iconColor">mdi-file-document-multiple</v-icon>
                </v-avatar>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3" key="events">
          <v-card
            rounded="xl"
            class="stat-card elevation-1 hover-elevation-3 transition-swing"
            :color="statsColors.events.bg"
            :variant="statsColors.events.variant"
          >
            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between">
                <div>
                  <div class="text-caption text-medium-emphasis mb-1 d-flex align-center gap-1">
                    <v-icon size="16" class="mr-1">mdi-calendar-check</v-icon>
                    {{ t('رویدادها') }}
                  </div>
                  <div class="text-h4 font-weight-black text-teal-darken-1">{{ eventsTotal }}</div>
                  <div class="mt-3">
                    <v-progress-linear
                      :model-value="100"
                      color="teal"
                      height="4"
                      class="rounded-pill"
                      rounded
                    />
                  </div>
                </div>
                <v-avatar size="56" :color="statsColors.events.iconBg" class="elevation-2">
                  <v-icon size="28" :color="statsColors.events.iconColor">mdi-calendar-check</v-icon>
                </v-avatar>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </transition-group>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content px-4">
      <v-row dense>
        <!-- EMPLOYEES RANKING -->
        <v-col cols="12" lg="7">
          <v-card rounded="xl" class="elevation-1 h-100">
            <v-card-text class="pa-4">
              <!-- Header -->
              <div class="d-flex align-center justify-space-between mb-4">
                <div>
                  <div class="text-h6 font-weight-bold text-primary mb-1">
                    {{ t('درجه‌بندی کارمندان') }}
                  </div>
                  <div class="text-caption text-medium-emphasis d-flex align-center gap-2">
                    <v-icon size="14">mdi-chart-bar</v-icon>
                    {{ t('به اساس تعداد رویداد، منبع و معلومات') }}
                  </div>
                </div>

                <div class="d-flex gap-2">
                  <v-text-field
                    v-model="search"
                    density="compact"
                    variant="outlined"
                    rounded="xl"
                    class="mr-2"
                    :style="{ width: '240px' }"
                    hide-details
                    :placeholder="t('جستجو: نام یا کود نمبر')"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                  />
                  <v-btn
                    :loading="repo.loading"
                    variant="tonal"
                    color="primary"
                    rounded="xl"
                    @click="refresh"
                    class="px-4"
                  >
                    <template v-slot:prepend>
                      <v-icon>mdi-refresh</v-icon>
                    </template>
                    {{ t('تازه‌سازی') }}
                  </v-btn>
                </div>
              </div>

              <!-- Employees Table -->
              <div class="table-container rounded-xl">
                <v-data-table
                  :items="filteredEmployees"
                  :headers="employeeHeaders"
                  :loading="repo.loading"
                  density="comfortable"
                  class="employees-table"
                  item-key="id"
                  fixed-header
                  height="520"
                  :items-per-page="10"
                >
                  <!-- Rank Column -->
                  <template #item.rank="{ item }">
                    <div class="d-flex align-center gap-2">
                      <v-avatar size="32" :color="getRankColor(item.rank)" class="elevation-1">
                        <span class="text-body-2 font-weight-bold text-white">
                          {{ item.rank }}
                        </span>
                      </v-avatar>
                    </div>
                  </template>

                  <!-- Employee Info -->
                  <template #item.name="{ item }">
                    <div class="d-flex align-center gap-3">
                      <v-avatar size="36" color="surface-variant">
                        <span class="text-body-1 font-weight-medium">
                          {{ getInitials(item.name) }}
                        </span>
                      </v-avatar>
                      <div>
                        <div class="text-body-1 font-weight-bold">{{ item.name || '—' }}</div>
                        <div class="text-caption text-medium-emphasis d-flex align-center gap-1">
                          <v-icon size="12">mdi-identifier</v-icon>
                          {{ t('کود') }}: {{ item.code_number || '—' }}
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Resources Count -->
                  <template #item.resources_count="{ item }">
                    <div class="d-flex align-center justify-center gap-2">
                      <v-icon size="18" color="primary">mdi-folder</v-icon>
                      <span class="font-weight-bold">{{ num(item.resources_count) }}</span>
                    </div>
                  </template>

                  <!-- Informations Count -->
                  <template #item.informations_count="{ item }">
                    <div class="d-flex align-center justify-center gap-2">
                      <v-icon size="18" color="indigo">mdi-file-document</v-icon>
                      <span class="font-weight-bold">{{ num(item.informations_count) }}</span>
                    </div>
                  </template>

                  <!-- Events Count -->
                  <template #item.events_count="{ item }">
                    <div class="d-flex align-center justify-center gap-2">
                      <v-icon size="18" color="teal">mdi-calendar</v-icon>
                      <span class="font-weight-bold">{{ num(item.events_count) }}</span>
                    </div>
                  </template>

                  <!-- Score -->
                  <template #item.score="{ item }">
                    <div class="d-flex flex-column align-center">
                      <v-progress-circular
                        :model-value="getScorePercentage(item.score)"
                        :color="getScoreColor(item.score)"
                        size="48"
                        width="4"
                      >
                        <span class="text-body-2 font-weight-bold">{{ item.score }}</span>
                      </v-progress-circular>
                    </div>
                  </template>

                  <!-- Empty State -->
                  <template #no-data>
                    <div class="py-12 text-center">
                      <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-search</v-icon>
                      <div class="text-h6 text-medium-emphasis mb-2">{{ t('هیچ کارمند موجود نیست') }}</div>
                      <div class="text-caption text-medium-emphasis">{{ t('کارمند جدید اضافه کنید') }}</div>
                    </div>
                  </template>

                  <!-- Loading State -->
                  <template #loading>
                    <div class="py-12 text-center">
                      <v-progress-circular indeterminate color="primary" size="48" />
                      <div class="text-body-1 mt-4 text-medium-emphasis">{{ t('در حال بارگذاری...') }}</div>
                    </div>
                  </template>
                </v-data-table>
              </div>

              <!-- Table Footer -->
              <div class="d-flex align-center justify-space-between mt-4 px-2">
                <div class="text-caption text-medium-emphasis">
                  {{ t('نمایش') }} {{ Math.min(filteredEmployees.length, 10) }} {{ t('از') }} {{ filteredEmployees.length }} {{ t('کارمند') }}
                </div>
                <div class="d-flex align-center gap-2">
                  <v-chip size="small" variant="tonal" color="primary" class="px-3">
                    <v-icon start size="14">mdi-trophy</v-icon>
                    {{ t('بهترین') }}: {{ getTopEmployeeName }}
                  </v-chip>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- RIGHT SIDE: CHARTS AND ZONES -->
        <v-col cols="12" lg="5">
          <!-- Formats Pie Chart -->
          <FormatsPieChart
            class="mb-4"
            :title="t('توزیع فرم‌ها بر اساس زون')"
            :subtitle="t('۱۰ زون برتر بر اساس تعداد فرم')"
            :total="formatsTotal"
            :total-label="t('مجموع فرم‌ها')"
            :empty-text="t('هیچ دیتا موجود نیست')"
            :data="formatsPieData"
            :height="340"
            donut
            :show-legend="true"
          />

          <!-- Zones List -->
          <v-card rounded="xl" class="elevation-1">
            <v-card-text class="pa-4">
              <!-- Header -->
              <div class="d-flex align-center justify-space-between mb-4">
                <div>
                  <div class="text-h6 font-weight-bold text-primary mb-1">{{ t('آمار زون‌ها') }}</div>
                  <div class="text-caption text-medium-emphasis d-flex align-center gap-2">
                    <v-icon size="14">mdi-map-marker</v-icon>
                    {{ t('تفکیک بر اساس تعداد فرم') }}
                  </div>
                </div>

                <div class="d-flex align-center gap-2">
                  <v-chip size="small" variant="tonal" color="primary" class="px-3">
                    <v-icon start size="14">mdi-map</v-icon>
                    {{ t('زون') }}: {{ zones.length }}
                  </v-chip>
                </div>
              </div>

              <!-- Zones Table -->
              <div class="table-container rounded-xl">
                <v-data-table
                  :items="zonesTop"
                  :headers="zoneHeaders"
                  density="comfortable"
                  class="zones-table"
                  item-key="zone"
                  fixed-header
                  height="280"
                  hide-default-footer
                >
                  <!-- Zone Column with Icon -->
                  <template #item.zone="{ item }">
                    <div class="d-flex align-center gap-3">
                      <v-avatar size="32" color="surface-variant" variant="tonal">
                        <v-icon size="18" color="primary">mdi-map-marker-radius</v-icon>
                      </v-avatar>
                      <div>
                        <div class="text-body-1 font-weight-medium">{{ item.zone || t('نامعلوم') }}</div>
                        <div class="text-caption text-medium-emphasis">
                          {{ getZonePercentage(item.total) }}% {{ t('از کل') }}
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Total Column with Progress Bar -->
                  <template #item.total="{ item }">
                    <div class="d-flex flex-column align-end">
                      <v-chip size="small" variant="flat" color="primary" class="mb-1 px-3">
                        {{ num(item.total) }}
                      </v-chip>
                      <v-progress-linear
                        :model-value="getZonePercentage(item.total)"
                        color="primary"
                        height="6"
                        rounded
                        class="rounded-pill"
                      />
                    </div>
                  </template>

                  <!-- Empty State -->
                  <template #no-data>
                    <div class="py-8 text-center">
                      <v-icon size="48" color="grey-lighten-1" class="mb-3">mdi-map</v-icon>
                      <div class="text-body-1 text-medium-emphasis">{{ t('هیچ دیتا موجود نیست') }}</div>
                    </div>
                  </template>
                </v-data-table>
              </div>

              <!-- Footer -->
              <div class="d-flex align-center justify-space-between mt-4 px-2">
                <div class="text-caption text-medium-emphasis">
                  {{ t('نمایش') }} {{ zonesTop.length }} {{ t('از') }} {{ zones.length }} {{ t('زون') }}
                </div>
                <v-btn
                  size="small"
                  variant="text"
                  color="primary"
                  :to="{ name: 'reports' }"
                  class="px-2"
                >
                  {{ t('مشاهده جزئیات') }}
                  <v-icon end size="16">mdi-chevron-left</v-icon>
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Quick Stats Footer -->
    <v-card rounded="lg" class="mx-4 mt-6" color="surface" variant="flat">
      <v-card-text class="pa-3">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center gap-4">
            <div class="d-flex align-center gap-2">
              <v-icon size="16" color="success">mdi-check-circle</v-icon>
              <span class="text-caption">{{ t('آخرین بروزرسانی') }}:</span>
              <span class="text-caption font-weight-medium">{{ lastUpdateTime }}</span>
            </div>
            <div class="d-flex align-center gap-2">
              <v-icon size="16" color="primary">mdi-database</v-icon>
              <span class="text-caption">{{ t('کل رکوردها') }}:</span>
              <span class="text-caption font-weight-medium">{{ totalRecords }}</span>
            </div>
          </div>
          <div class="d-flex align-center gap-2">
            <v-chip size="small" variant="tonal" color="primary" class="px-2">
              <v-icon start size="14">mdi-update</v-icon>
              {{ t('آپ‌تایم') }}: 99.9%
            </v-chip>
          </div>
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useDisplay } from 'vuetify'
import AppBar from '@/components/AppBar.vue'
import FormatsPieChart from '@/components/charts/FormatZonePieChart.vue'
import { useDashboardRepository } from '@/stores/DashboardRepository'

const { t, locale } = useI18n()
const { mobile } = useDisplay()
const isRtl = computed(() => String(locale.value).toLowerCase().startsWith('fa'))

const repo = useDashboardRepository()
const search = ref('')
const lastUpdateTime = ref(new Date().toLocaleTimeString())

onMounted(async () => {
  if (!repo.dashboard) {
    await repo.fetchDashboard()
  }
})

async function refresh() {
  await repo.fetchDashboard()
  lastUpdateTime.value = new Date().toLocaleTimeString()
}

const readableError = computed(() => {
  const e = repo.error
  if (!e) return ''
  return e?.response?.data?.message || e?.message || String(e)
})

/* ===========================
  Stats Configuration
=========================== */
const statsColors = {
  employees: { bg: 'surface', variant: 'elevated', iconBg: 'primary', iconColor: 'white' },
  resources: { bg: 'surface', variant: 'elevated', iconBg: 'teal', iconColor: 'white' },
  informations: { bg: 'surface', variant: 'elevated', iconBg: 'indigo', iconColor: 'white' },
  events: { bg: 'surface', variant: 'elevated', iconBg: 'teal-darken-1', iconColor: 'white' }
}

/* ===========================
  Stats Computed
=========================== */
const employeesTotal = computed(() => repo.employees?.total ?? repo.employeesFlat.length ?? 0)
const resourcesTotal = computed(() => repo.resources?.total ?? 0)
const resourcesActive = computed(() => repo.resources?.active ?? 0)
const resourcesInactive = computed(() => repo.resources?.inactive ?? 0)
const informationsTotal = computed(() => repo.informations?.total ?? 0)
const eventsTotal = computed(() => repo.events?.total ?? 0)
const totalRecords = computed(() => 
  employeesTotal.value + resourcesTotal.value + informationsTotal.value + eventsTotal.value
)

/* ===========================
  Helper Functions
=========================== */
function num(v) {
  const n = Number(v)
  return Number.isFinite(n) ? n.toLocaleString() : '0'
}

function getInitials(name) {
  if (!name) return '—'
  return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2)
}

function getRankColor(rank) {
  if (rank === 1) return 'amber'
  if (rank === 2) return 'blue-grey'
  if (rank === 3) return 'deep-orange'
  return 'primary'
}

function employeeScore(item) {
  if (item?.score != null) return num(item.score)
  const events = Number(item?.events_count) || 0
  const resources = Number(item?.resources_count) || 0
  const informations = Number(item?.informations_count) || 0
  return events + resources + informations
}

function getScorePercentage(score) {
  const maxScore = Math.max(...employees.value.map(e => employeeScore(e)), 100)
  return Math.min((score / maxScore) * 100, 100)
}

function getScoreColor(score) {
  const percentage = getScorePercentage(score)
  if (percentage >= 80) return 'success'
  if (percentage >= 60) return 'primary'
  if (percentage >= 40) return 'warning'
  return 'error'
}

/* ===========================
  Employees
=========================== */
const employees = computed(() => {
  const list = Array.isArray(repo.employees?.flat_sorted)
    ? repo.employees.flat_sorted
    : Array.isArray(repo.dashboard?.employees_flat)
      ? repo.dashboard.employees_flat
      : []

  const sorted = [...list].sort((a, b) => {
    const sB = Number(b?.score) || employeeScore(b)
    const sA = Number(a?.score) || employeeScore(a)
    return sB - sA
  })

  return sorted.map((x, i) => ({
    ...x,
    rank: i + 1,
    score: Number(x?.score) || employeeScore(x),
  }))
})

const filteredEmployees = computed(() => {
  const q = search.value?.trim()?.toLowerCase()
  if (!q) return employees.value

  return employees.value.filter((e) => {
    const name = String(e?.name || '').toLowerCase()
    const code = String(e?.code_number || '').toLowerCase()
    return name.includes(q) || code.includes(q)
  })
})

const getTopEmployeeName = computed(() => {
  return filteredEmployees.value[0]?.name || '—'
})

const employeeHeaders = computed(() => [
  { title: t('رتبه'), key: 'rank', width: 80, sortable: false, align: 'center' },
  { title: t('کارمند'), key: 'name', sortable: false },
  { title: t('منابع'), key: 'resources_count', width: 100, sortable: false, align: 'center' },
  { title: t('معلومات'), key: 'informations_count', width: 100, sortable: false, align: 'center' },
  { title: t('رویداد'), key: 'events_count', width: 100, sortable: false, align: 'center' },
  { title: t('امتیاز'), key: 'score', width: 100, sortable: false, align: 'center' },
])

/* ===========================
  Formats By Zone
=========================== */
const zones = computed(() => {
  const z = repo.formats_by_zone?.zones
  return Array.isArray(z) ? z : []
})

const formatsTotal = computed(() => zones.value.reduce((sum, z) => sum + Number(z?.total || 0), 0))

const zonesTop = computed(() => 
  zones.value
    .slice(0, mobile.value ? 5 : 8)
    .map(zone => ({
      ...zone,
      percentage: (Number(zone.total) / formatsTotal.value) * 100
    }))
)

function getZonePercentage(total) {
  return formatsTotal.value > 0 ? ((Number(total) / formatsTotal.value) * 100).toFixed(1) : 0
}

const zoneHeaders = computed(() => [
  { title: t('زون'), key: 'zone', sortable: false },
  { title: t('تعداد'), key: 'total', width: 120, sortable: false, align: 'end' },
])

const formatsPieData = computed(() => {
  const list = zones.value
    .map((z) => ({ 
      value: Number(z?.total) || 0, 
      name: z?.zone || t('نامعلوم'),
      itemStyle: {
        color: getZoneColor(z?.zone)
      }
    }))
    .filter((x) => x.value > 0)
    .sort((a, b) => b.value - a.value)

  if (list.length <= 10) return list

  const top = list.slice(0, 9)
  const restSum = list.slice(9).reduce((s, x) => s + x.value, 0)
  if (restSum > 0) {
    top.push({ 
      name: t('سایر'), 
      value: restSum,
      itemStyle: { color: '#9E9E9E' }
    })
  }
  return top
})

function getZoneColor(zoneName) {
  const colors = [
    '#2196F3', '#4CAF50', '#FF9800', '#9C27B0',
    '#00BCD4', '#8BC34A', '#FF5722', '#673AB7',
    '#009688', '#E91E63', '#3F51B5', '#CDDC39'
  ]
  const hash = zoneName?.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) || 0
  return colors[hash % colors.length]
}
</script>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #f0f4f8 100%);
}

.stats-grid {
  animation: fadeInUp 0.6s ease-out;
}

.stat-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(var(--v-border-color), 0.1);
  backdrop-filter: blur(10px);
}

.stat-card:hover {
  transform: translateY(-4px);
  border-color: rgba(var(--v-primary), 0.3);
}

.main-content {
  animation: fadeIn 0.8s ease-out;
}

.table-container {
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-border-color), 0.1);
  overflow: hidden;
}

.employees-table :deep(.v-data-table__th) {
  background: rgba(var(--v-theme-primary), 0.05);
  font-weight: 600;
  color: rgb(var(--v-theme-primary));
}

.employees-table :deep(.v-data-table__tr:hover) {
  background: rgba(var(--v-theme-primary), 0.02);
}

.zones-table :deep(.v-data-table__th) {
  background: rgba(var(--v-theme-surface-variant), 0.1);
  font-weight: 600;
}

.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.gap-4 { gap: 16px; }

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.stagger-fade-move {
  transition: transform 0.5s ease;
}

.stagger-fade-enter-active {
  transition: all 0.5s ease;
  transition-delay: calc(var(--v-index) * 0.1s);
}

.stagger-fade-leave-active {
  transition: all 0.3s ease;
}

.stagger-fade-enter-from {
  opacity: 0;
  transform: scale(0.9) translateY(10px);
}

.stagger-fade-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* Scrollbar Styling */
:deep(.v-data-table__wrapper)::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

:deep(.v-data-table__wrapper)::-webkit-scrollbar-track {
  background: rgba(var(--v-border-color), 0.1);
  border-radius: 3px;
}

:deep(.v-data-table__wrapper)::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-primary), 0.3);
  border-radius: 3px;
}

:deep(.v-data-table__wrapper)::-webkit-scrollbar-thumb:hover {
  background: rgba(var(--v-theme-primary), 0.5);
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .main-content .row {
    flex-direction: column;
  }
  
  .main-content .v-col {
    width: 100%;
    max-width: 100%;
  }
}

@media (max-width: 600px) {
  .stats-grid .v-col {
    width: 100%;
    max-width: 100%;
  }
  
  .stat-card .text-h4 {
    font-size: 2rem;
  }
}
</style>