<script setup>
import { onMounted, onUnmounted, ref, watch, nextTick, computed } from 'vue'
import * as echarts from 'echarts'
import { useI18n } from 'vue-i18n'
import { useInfoEventDashboardRepository } from '@/stores/InfoEventDashboardRepository'
import { useCalendar } from '../../../../composables/useCalendar'

// Emits (drawer toggle from AppBar)
const emit = defineEmits(['toggle-drawer'])

/* ---------- Stores & i18n ---------- */
const repo = useInfoEventDashboardRepository()
const { locale, t } = useI18n()
const { calendar } = useCalendar()

/* ---------- RTL / LTR ---------- */
const direction = computed(() => (locale.value === 'fa' ? 'rtl' : 'ltr'))

/* ---------- ECharts refs ---------- */
const lineRef = ref(null)
const pieRef  = ref(null)
let lineChart, pieChart

/* ---------- Date formatting ---------- */
function formatDate(dateStr, granularity = 'day') {
  if (!dateStr) return ''
  // Calendar map: same keys as useCalendar()
  const calKey = calendar.value || 'gregorian'
  // Unicode calendar ids
  const uca = calKey === 'jalali' ? 'persian' : calKey === 'hijri' ? 'islamic' : 'gregory'
  const loc = locale.value === 'fa' ? 'fa' : 'en'
  const options =
    granularity === 'month'
      ? { year: 'numeric', month: '2-digit', calendar: uca }
      : { year: 'numeric', month: '2-digit', day: '2-digit', calendar: uca }
  try {
    // Allow plain 'YYYY-MM' buckets too
    const d = dateStr.length === 7 ? new Date(dateStr + '-01') : new Date(dateStr)
    return new Intl.DateTimeFormat(`${loc}-u-ca-${uca}`, options).format(d)
  } catch {
    return dateStr
  }
}

/* ---------- Charts ---------- */
function initCharts() {
  if (lineRef.value && !lineChart) lineChart = echarts.init(lineRef.value)
  if (pieRef.value  && !pieChart)  pieChart  = echarts.init(pieRef.value)
  renderCharts()
}
function disposeCharts() {
  if (lineChart) { lineChart.dispose(); lineChart = null }
  if (pieChart)  { pieChart.dispose();  pieChart = null }
}

function renderCharts() {
  // Line: events over time
  const seriesEv = repo.state.EventsOverTime || []
  const isMonthBucket = (repo.state.GroupBy || 'month') === 'month'
  const xLabels = seriesEv.map(s => formatDate(s.label || s.bucket, isMonthBucket ? 'month' : 'day'))
  const lineOption = {
    tooltip: { trigger: 'axis' },
    grid: { left: 48, right: 20, bottom: 36, top: 28 },
    xAxis: { type: 'category', data: xLabels, axisLabel: { interval: 0, rotate: xLabels.length > 8 ? 30 : 0 } },
    yAxis: { type: 'value' },
    series: [{ name: t('پېښې'), type: 'line', smooth: true, areaStyle: {}, data: seriesEv.map(s => s.total || 0) }]
  }
  lineChart?.setOption(lineOption, true)

  // Pie: employees by zone
  const zoneRows = repo.state.EmployeesByZone || []
  const pieOption = {
    tooltip: { trigger: 'item' },
    legend: { type: 'scroll', top: 8 },
    series: [
      {
        type: 'pie',
        radius: ['42%', '70%'],
        center: ['50%', '60%'],
        label: { show: true, formatter: '{b}: {c}' },
        data: zoneRows.map(r => ({ name: r.zone || t('نامعلوم'), value: r.employees || 0 }))
      }
    ]
  }
  pieChart?.setOption(pieOption, true)
}

function handleResize() { lineChart?.resize(); pieChart?.resize() }

/* ---------- Quick range buttons ---------- */
function setRange(mode) {
  const now = new Date()
  const yyyy = now.getFullYear()
  const mm = String(now.getMonth() + 1).padStart(2, '0')
  const dd = String(now.getDate()).padStart(2, '0')

  if (mode === 'day') {
    repo.state.From = `${yyyy}-${mm}-${dd}`
    repo.state.To   = `${yyyy}-${mm}-${dd}`
    repo.state.GroupBy = 'day'
  } else if (mode === 'month') {
    const first = `${yyyy}-${mm}-01`
    const last  = `${yyyy}-${mm}-${String(new Date(yyyy, now.getMonth() + 1, 0).getDate()).padStart(2,'0')}`
    repo.state.From = first
    repo.state.To   = last
    repo.state.GroupBy = 'day'
  } else if (mode === 'year') {
    repo.state.From = `${yyyy}-01-01`
    repo.state.To   = `${yyyy}-12-31`
    repo.state.GroupBy = 'month'
  }
  fetchAll()
}

/* ---------- Fetch & map ---------- */
async function fetchAll() {
  await repo.fetchCards()
  await Promise.all([
    repo.fetchEventsOverTime(),
    repo.fetchEmployeesByZone(),
    repo.fetchRecentEvents(12),
    repo.fetchTopEmployeesByResources()
  ])
  await nextTick()
  renderCharts()
}

/* ---------- Table (date formatting reacts to calendar) ---------- */
const recentRows = computed(() =>
  (repo.state.RecentEvents || []).map(r => ({
    ...r,
    event_date_fmt: r.event_date ? formatDate(r.event_date, 'day') : ''
  }))
)

/* ---------- Re-render when language/calendar or data changes ---------- */
watch([() => repo.state.EventsOverTime, () => repo.state.EmployeesByZone], () => nextTick().then(renderCharts))
watch([locale, calendar], () => nextTick().then(renderCharts))

onMounted(async () => {
  setRange('month')
  window.addEventListener('resize', handleResize)
  await nextTick()
  initCharts()
})
onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  disposeCharts()
})
</script>

<template>
  <v-container fluid class="pt-6 mt-12" :dir="direction">
    

    <!-- Range buttons -->
    <v-row class="mb-3">
      <v-col cols="12" class="d-flex flex-wrap ga-2">
        <v-btn class="rounded-xl" variant="flat" color="primary" @click="setRange('day')">{{ t('ورځ') }}</v-btn>
        <v-btn class="rounded-xl" variant="flat" color="primary" @click="setRange('month')">{{ t('میاشت') }}</v-btn>
        <v-btn class="rounded-xl" variant="flat" color="primary" @click="setRange('year')">{{ t('کال') }}</v-btn>
      </v-col>
    </v-row>

    <!-- Cards -->
    <v-row>
      <v-col cols="12" md="3" v-for="c in repo.state.Cards" :key="c.key">
        <v-card elevation="3" class="pa-4 rounded-2xl">
          <div class="text-medium-emphasis text-caption">{{ c.title }}</div>
          <div class="text-h5 font-weight-bold mt-1">{{ c.value }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts -->
    <v-row class="mt-2">
      <v-col cols="12" md="7">
        <v-card elevation="3" class="pa-2 rounded-2xl">
          <div class="text-subtitle-2 px-2 pt-2">{{ t('پېښې د وخت په اوږدو کې') }}</div>
          <div ref="lineRef" style="height:380px;"></div>
        </v-card>
      </v-col>
      <v-col cols="12" md="5">
        <v-card elevation="3" class="pa-2 rounded-2xl">
          <div class="text-subtitle-2 px-2 pt-2">{{ t('کارکوونکي د زون له مخې') }}</div>
          <div ref="pieRef" style="height:380px;"></div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent table -->
    <v-row class="mt-3">
      <v-col cols="12">
        <v-card elevation="3" class="rounded-2xl">
          <v-card-title class="text-subtitle-2">{{ t('وروستي پېښې') }}</v-card-title>
          <v-data-table
            :items="recentRows"
            :headers="[
              { title: t('کارمند'), key: 'employee' },
              { title: t('سرچينه'), key: 'resource' },
              { title: t('ډول'), key: 'event_type' },
              { title: t('ځانګړی ډول'), key: 'event_specific_type' },
              { title: t('زون'), key: 'event_zone' },
              { title: t('حوزہ'), key: 'event_hawza' },
              { title: t('نېټه'), key: 'event_date_fmt' },
              { title: t('ساعت'), key: 'event_hour' }
            ]"
            density="compact"
            hide-default-footer
            class="px-2 pb-2"
          />
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.rounded-2xl { border-radius: 16px; }
</style>
