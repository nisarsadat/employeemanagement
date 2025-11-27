<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Filters -->
    <v-row class="mb-4" align="end">
      <v-col cols="12" md="3">
        <v-select
          v-model="repo.formatKey"
          :items="formatOptions"
          item-title="label"
          item-value="key"
          :label="t('فارمت')"
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
          v-model.trim="repo.From"
          :label="t('از تاریخ')"
          placeholder="2025-10-01"
          density="compact"
          variant="outlined"
          clearable
        />
      </v-col>
      <v-col cols="12" md="3">
        <v-text-field
          v-model.trim="repo.To"
          :label="t('تا تاریخ')"
          placeholder="2025-10-31"
          density="compact"
          variant="outlined"
          clearable
        />
      </v-col>

      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          prepend-inner-icon="mdi-magnify"
          density="compact"
          variant="outlined"
          clearable
          @keyup.enter="fetchAll"
        />
      </v-col>

      <!-- Granularity -->
      <v-col cols="12" md="3" class="text-md-right granularity">
        <v-btn variant="flat" color="primary" class="mr-2" :class="btnClass('daily')" @click="quickRange('daily')">
          {{ t('روزانه') }}
        </v-btn>
        <v-btn variant="flat" color="primary" class="mr-2" :class="btnClass('monthly')" @click="quickRange('monthly')">
          {{ t('ماهانه') }}
        </v-btn>
        <v-btn variant="flat" color="primary" :class="btnClass('yearly')" @click="quickRange('yearly')">
          {{ t('سالانه') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Cards -->
    <v-row>
      <v-col v-for="c in repo.cards" :key="c.key" cols="12" sm="6" md="3">
        <v-card class="pa-4 h-100 soft-card" elevation="1">
          <div class="text-overline text-primary">{{ c.label }}</div>
          <div class="text-h5 font-weight-bold mt-1">{{ c.total }}</div>
          <div class="text-caption text-medium-emphasis">{{ t('تمام ریکارد ها') }}</div>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mt-2">
      <!-- LEFT: Stacked Columns over time -->
      <v-col cols="12" md="8">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-2">
            <div class="text-subtitle-1 font-weight-medium">{{ t('پروسه زمانی') }}</div>
            <v-chip density="compact" variant="flat">{{ granularityLabel }}</v-chip>
          </div>
          <apexchart type="bar" height="340" :options="stackedOptions" :series="stackedSeries" />
        </v-card>
      </v-col>

      <!-- RIGHT: Polar Area composition -->
      <v-col cols="12" md="4">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="text-subtitle-1 font-weight-medium mb-2">
            {{ t('ترکیب از لحاظ صفحات') }}
          </div>
          <apexchart type="polarArea" height="340" :options="polarOptions" :series="polarSeries" />
        </v-card>
      </v-col>
    </v-row>

    <!-- Bottom row: Top resources + recent table -->
    <v-row class="mt-2">
      <v-col cols="12" md="7">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-2">
            <div class="text-subtitle-1 font-weight-medium">{{ t('منابع برتر') }}</div>
            <v-select
              v-model="repo.formatKey"
              :items="formatOptions"
              item-title="label"
              item-value="key"
              :label="t('فورمټ فلټر')"
              class="mt-1"
              density="compact"
              variant="outlined"
              clearable
              style="max-width: 260px"
              @update:model-value="fetchTop"
            />
          </div>
          <apexchart type="bar" height="340" :options="barOptions" :series="barSeries" />
        </v-card>
      </v-col>

      <v-col cols="12" md="5">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="text-subtitle-1 font-weight-medium mb-2">{{ t('ریکارد های اخیر') }}</div>
          <v-data-table
            :headers="recentHeaders"
            :items="repo.recent"
            density="compact"
            class="elevation-0"
            :items-per-page="6"
            hide-default-footer
          >
            <template #item.created_at="{ item }">
              <span>{{ formatDate(item.created_at) }}</span>
            </template>
            <template #bottom></template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useFormatDashboardRepository } from '@/stores/FormatDashboardRepository'
import { useCalendar } from '../../../../composables/useCalendar'

const repo = useFormatDashboardRepository()
const { t, locale } = useI18n()
useCalendar()

const isRtl = ref(locale.value === 'fa')
watch(locale, l => { isRtl.value = (l === 'fa') })

/* Formats (match backend keys) */
const formatOptions = [
  { key: 'captive', label: t('زندانی های تسلیم شده') },
  { key: 'clubs', label: t('جا های ورشی') },
  { key: 'entry_exit', label: t('داخل/خروج') },
  { key: 'fasad', label: t('فساد') },
  { key: 'heist', label: t('دزدی') },
  { key: 'mahajerat', label: t('مهاجرت ها') },
  { key: 'mulkano', label: t('ملک ها') },
  { key: 'saeen_fasad', label: t('ساعین فساد') },
  { key: 'sarafyano', label: t('صرافی ها') },
  { key: 'soldier', label: t('سرباز ها') },
]

const granularityLabel = computed(() => ({
  daily: t('روزانه'), monthly: t('ماهانه'), yearly: t('سالانه')
}[repo.granularity] || t('روزانه')))

function endOfTodayISO() {
  const d = new Date(); d.setHours(23,59,59,999); return d.toISOString().slice(0,10)
}
function startNDaysAgoISO(n) {
  const d = new Date(); d.setDate(d.getDate()-n); d.setHours(0,0,0,0); return d.toISOString().slice(0,10)
}
function startNMonthsAgoISO(n) {
  const d = new Date(); d.setMonth(d.getMonth()-n); d.setDate(1); d.setHours(0,0,0,0); return d.toISOString().slice(0,10)
}
function startNYearsAgoISO(n) {
  const d = new Date(); d.setFullYear(d.getFullYear()-n, 0, 1); d.setHours(0,0,0,0); return d.toISOString().slice(0,10)
}

function quickRange(g) {
  repo.setGranularity(g)
  if (g === 'daily') {
    repo.setRange({ from: startNDaysAgoISO(30), to: endOfTodayISO() })
  } else if (g === 'monthly') {
    repo.setRange({ from: startNMonthsAgoISO(6), to: endOfTodayISO() })
  } else {
    repo.setRange({ from: startNYearsAgoISO(5), to: endOfTodayISO() })
  }
  fetchAll()
}

function fetchAll() { repo.fetchAll() }
function fetchTop() { repo.fetchTopResources(10) }
function btnClass(g) { return repo.granularity === g ? 'elevated' : '' }

onMounted(() => {
  if (!repo.From && !repo.To) quickRange('daily')
  else fetchAll()
})

/* ===================== COLORS ===================== */
const palette = ['#00A76F','#1E88E5','#8E24AA','#F4511E','#FFB300','#5E35B1','#43A047','#00897B','#3949AB','#F06292']

/* ===================== LEFT: STACKED COLUMNS ===================== */
const categories = computed(() => {
  const set = new Set()
  ;(repo.series || []).forEach(s => (s.points || []).forEach(p => set.add(String(p.date))))
  return Array.from(set).sort()
})

const stackedSeries = computed(() => {
  const cats = categories.value
  if (!cats.length) return []
  return (repo.series || []).map(s => {
    const map = Object.create(null)
    ;(s.points || []).forEach(p => { map[String(p.date)] = Number(p.count || 0) })
    return { name: s.label, data: cats.map(d => map[d] ?? 0) }
  })
})

const stackedOptions = computed(() => ({
  chart: {
    type: 'bar',
    stacked: true,
    toolbar: { show: false },
    foreColor: 'inherit',
    fontFamily: 'Inter, Vazirmatn, system-ui'
  },
  colors: palette,
  plotOptions: {
    bar: { horizontal: false, columnWidth: '55%', borderRadius: 6, borderRadiusApplication: 'end' }
  },
  dataLabels: { enabled: false },
  stroke: { show: true, width: 1, colors: ['#fff'] },
  xaxis: {
    categories: categories.value,
    axisTicks: { show: false },
    axisBorder: { show: false },
    labels: { rotate: isRtl.value ? -10 : 0 }
  },
  yaxis: {
    min: 0,
    forceNiceScale: true,
    labels: { formatter: v => (v|0).toString() }
  },
  legend: { position: isRtl.value ? 'left' : 'right' },
  grid: { borderColor: 'rgba(0,0,0,0.08)', strokeDashArray: 4 },
  tooltip: { shared: true, intersect: false },
  noData: { text: t('دیتا موجود نیست') }
}))

/* ===================== RIGHT: POLAR AREA (with NaN% guard) ===================== */
const safeNums = (arr) => arr
  .map(v => Number(v))
  .filter(v => Number.isFinite(v) && v >= 0)

const polarRaw = computed(() => safeNums((repo.cards || []).map(c => c?.total ?? 0)))
const polarLabels = computed(() => (repo.cards || []).map(c => c?.label || t('بې‌نوم')))

const polarSeries = computed(() => {
  const s = polarRaw.value
  const sum = s.reduce((a,b) => a + b, 0)
  return (s.length === 0 || sum === 0) ? [] : s
})

const polarOptions = computed(() => ({
  labels: polarLabels.value,
  colors: palette,
  stroke: { width: 1 },
  yaxis: { show: false },
  fill: { opacity: 0.9 },
  legend: { position: 'bottom' },
  dataLabels: {
    enabled: true,
    formatter: (val) => {
      const v = Number(val)
      return Number.isFinite(v) ? `${Math.round(v)}%` : '0%'
    },
    style: { fontSize: '12px' }
  },
  plotOptions: { polarArea: { rings: { strokeWidth: 0 }, spokes: { strokeWidth: 1 } } },
  noData: { text: t('دیتا موجود نیست'), align: 'center' }
}))

/* ===================== BOTTOM: TOP RESOURCES BAR ===================== */
const barCats = computed(() => {
  const b = repo.buckets || []
  if (!b.length) return []
  const first = repo.formatKey ? b.find(x => x.key === repo.formatKey) || b[0] : b[0]
  return (first.items || []).map(i => `${i.code_number || '#'} — ${i.name || t('بې‌نوم')}`)
})
const barSeries = computed(() => {
  const b = repo.buckets || []
  if (!b.length) return [{ name: t('ریکارد ها'), data: [] }]
  const first = repo.formatKey ? b.find(x => x.key === repo.formatKey) || b[0] : b[0]
  return [{ name: t('ریکارد ها'), data: (first.items || []).map(i => Number(i.count || 0)) }]
})
const barOptions = computed(() => ({
  chart: { toolbar: { show: false }, foreColor: 'inherit', fontFamily: 'Inter, Vazirmatn, system-ui' },
  colors: ['#1E88E5'],
  plotOptions: {
    bar: { horizontal: true, barHeight: '60%', borderRadius: 6, dataLabels: { position: 'right' } }
  },
  dataLabels: { enabled: true, formatter: (v) => `${v}`, style: { fontSize: '12px' } },
  grid: { borderColor: 'rgba(0,0,0,0.08)', strokeDashArray: 4 },
  xaxis: { categories: barCats.value, axisTicks: { show: false }, axisBorder: { show: false } },
  yaxis: { labels: { maxWidth: 220 } },
  tooltip: { y: { formatter: (v) => `${v}` } },
  noData: { text: t('دیتا موجود نیست') }
}))

/* ===================== TABLE ===================== */
const recentHeaders = [
  { key: 'type', title: t('نوع'), width: 110 },
  { key: 'name', title: t('نام') },
  { key: 'zone', title: t('زون'), width: 120 },
  { key: 'created_at', title: t('تاریخ'), width: 140 },
]
function formatDate(iso) { return iso ? String(iso).slice(0, 10) : '' }
</script>

<style scoped>
.h-100 { height: 100% }
.soft-card { border-radius: 16px; backdrop-filter: saturate(1.1); }
.granularity .v-btn.elevated { box-shadow: 0 4px 14px rgba(0, 0, 0, .12); }
</style>
