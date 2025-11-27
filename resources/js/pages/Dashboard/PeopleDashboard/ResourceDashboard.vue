<!-- src/views/dashboard/ResourcesDashboard.vue -->
<template>
  <v-container fluid class="pt-6 mt-12" :dir="direction">
   

    <!-- Quick ranges -->
    <v-row class="mb-2">
      <v-col cols="12" class="d-flex flex-wrap ga-2">
        <v-btn class="rounded-xl" variant="flat" color="primary" @click="setRange('day')">
          {{ t('روز') }}
        </v-btn>
        <v-btn class="rounded-xl" variant="flat" color="primary" @click="setRange('month')">
          {{ t('ماه') }}
        </v-btn>
        <v-btn class="rounded-xl" variant="flat" color="primary" @click="setRange('year')">
          {{ t('سال') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-row class="mb-3">
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
          placeholder="2025-07-01"
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

      <v-col cols="12" md="3">
        <v-select
          v-model="repo.GroupBy"
          :items="groupByItems"
          :label="t('نوع بندی')"
          density="compact"
          variant="outlined"
        />
      </v-col>
    </v-row>

    <!-- Cards -->
    <v-row>
      <v-col cols="12" md="4">
        <v-card class="rounded-2xl gradient-cyan" elevation="3">
          <v-card-text class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-high">{{ t('نوع منبع') }}</div>
              <div class="text-h4 font-weight-bold mt-1">{{ cards.new_resources ?? '—' }}</div>
            </div>
            <v-icon size="48">mdi-account-plus</v-icon>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="rounded-2xl gradient-teal" elevation="3">
          <v-card-text class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-high">{{ t('کارمندان فعال') }}</div>
              <div class="text-h4 font-weight-bold mt-1">{{ cards.active_employees ?? '—' }}</div>
            </div>
            <v-icon size="48">mdi-account-group</v-icon>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="rounded-2xl gradient-purple" elevation="3">
          <v-card-text class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-high">{{ t('زندانی های تسلیم شده') }}</div>
              <div class="text-h4 font-weight-bold mt-1">{{ cards.captive_deliveries ?? '—' }}</div>
            </div>
            <v-icon size="48">mdi-handcuffs</v-icon>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Nightingale Pie (Rose) Chart -->
    <v-row class="mt-1">
      <v-col cols="12">
        <v-card elevation="3" class="rounded-2xl">
          <v-card-title class="text-subtitle-2 px-4 pt-3">
            {{ t('منابع از روی وقت') }}
          </v-card-title>
          <v-card-text class="pt-0">
            <div ref="roseRef" style="width: 100%; height: 520px"></div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import * as echarts from 'echarts'
import { usePeopleDashboardRepository } from '@/stores/PeopleDashboardRepository'
import AppBar from '@/components/AppBar.vue'
import { useCalendar } from '../../../../composables/useCalendar'

/* ---------- repos & i18n ---------- */
const drawer = ref(false)
const repo = usePeopleDashboardRepository()
const { t, locale } = useI18n()
const { calendar } = useCalendar()

/* ---------- direction ---------- */
const direction = computed(() =>
  ['fa', 'ps', 'ar', 'ur'].includes(locale.value) ? 'rtl' : 'ltr'
)

/* ---------- group by items (translated) ---------- */
const groupByItems = computed(() => [
  { title: t('ماه'), value: 'month' },
  { title: t('هفته'),  value: 'week'  },
  { title: t('روز'),   value: 'day'   },
])

/* ---------- cards ---------- */
const cards = ref({})

/* ---------- helpers ---------- */
function debounce(fn, ms = 300) {
  let t
  return (...args) => {
    clearTimeout(t)
    t = setTimeout(() => fn(...args), ms)
  }
}

/** Normalize to YYYY-MM-DD for API */
function normalizeDate(s) {
  if (!s) return ''
  const raw = String(s).trim()
  const ymd = raw.match(/^(\d{4})[-/.](\d{1,2})[-/.](\d{1,2})$/)
  if (ymd) {
    const y = Number(ymd[1])
    const m = String(Number(ymd[2])).padStart(2, '0')
    const d = String(Number(ymd[3])).padStart(2, '0')
    return `${y}-${m}-${d}`
  }
  const dmy = raw.match(/^(\d{1,2})[-/.](\d{1,2})[-/.](\d{4})$/)
  if (dmy) {
    const y = Number(dmy[3])
    const m = String(Number(dmy[2])).padStart(2, '0')
    const d = String(Number(dmy[1])).padStart(2, '0')
    return `${y}-${m}-${d}`
  }
  const dt = new Date(raw)
  if (!isNaN(dt.getTime())) {
    const y = dt.getFullYear()
    const m = String(dt.getMonth() + 1).padStart(2, '0')
    const d = String(dt.getDate()).padStart(2, '0')
    return `${y}-${m}-${d}`
  }
  return ''
}

/** Calendar-aware formatter (gregorian / persian / islamic) */
function formatDateReadable(dateStr, granularity = 'day') {
  if (!dateStr) return ''
  const calKey = calendar.value || 'gregorian'
  const uca = calKey === 'jalali' ? 'persian' : calKey === 'hijri' ? 'islamic' : 'gregory'
  const loc = ['fa', 'ps', 'ar', 'ur'].includes(locale.value) ? 'fa' : 'en'
  const options =
    granularity === 'month'
      ? { year: 'numeric', month: '2-digit', calendar: uca }
      : { year: 'numeric', month: '2-digit', day: '2-digit', calendar: uca }
  try {
    // buckets can be YYYY-MM or full date
    const d = dateStr.length === 7 ? new Date(dateStr + '-01') : new Date(dateStr)
    return new Intl.DateTimeFormat(`${loc}-u-ca-${uca}`, options).format(d)
  } catch {
    return dateStr
  }
}

/* ---------- cards data ---------- */
async function loadCards() {
  const res = await repo.fetchCards()
  cards.value = res && typeof res === 'object' ? res : {}
}
const reloadCards = debounce(loadCards, 250)

/* filters -> cards reload */
watch(() => repo.Zone,   reloadCards)
watch(() => repo.From,   reloadCards)
watch(() => repo.To,     reloadCards)
watch(() => repo.GroupBy,reloadCards)

/* ---------- Nightingale (Rose) chart ---------- */
const roseRef = ref(null)
let roseChart = null

function ensureRoseChart() {
  if (roseChart || !roseRef.value) return
  roseChart = echarts.init(roseRef.value)
}

function buildRoseOption(slices) {
  return {
    tooltip: {
      trigger: 'item',
      formatter: (p) => `${p.name}<br/>${t('ټول')}: ${p.value}`,
    },
    legend: { top: 'bottom' },
    toolbox: {
      show: true,
      feature: {
        mark: { show: true },
        dataView: { show: true, readOnly: true },
        restore: { show: true },
        saveAsImage: { show: true },
      },
    },
    series: [
      {
        name: 'Nightingale Chart',
        type: 'pie',
        radius: [50, 150],
        center: ['60%', '45%'],
        roseType: 'area',
        itemStyle: { borderRadius: 8 },
        label: { show: true, formatter: '{b}: {c}' },
        data: slices,
      },
    ],
  }
}

/** Load chart data from API and format labels with calendar */
async function setRoseFromApi() {
  const from = normalizeDate(repo.From)
  const to   = normalizeDate(repo.To)
  const zone = repo.Zone || ''
  const gb   = repo.GroupBy || 'month'

  // Expect rows: [{ period: 'YYYY-MM'|'YYYY-MM-DD', count: N }, ...]
  const rowsRaw = await repo.resourcesOverTime(from, to, zone, gb)
  const rows = Array.isArray(rowsRaw) ? rowsRaw : rowsRaw?.data ?? []

  const slices = rows
    .map((r) => {
      const period = String(r?.period ?? '').trim()
      const formatted =
        gb === 'month' ? formatDateReadable(period, 'month') : formatDateReadable(period, 'day')
      return { name: formatted || period || t('نامعلوم'), value: Number(r?.count ?? 0) }
    })
    .filter((s) => s.name)

  const safeSlices = slices.length ? slices : [{ name: t('دیتا موجود نیست'), value: 0 }]

  ensureRoseChart()
  if (roseChart) {
    roseChart.setOption(buildRoseOption(safeSlices), true)
  }
}

const reloadRose = debounce(setRoseFromApi, 250)

/* filters & locale/calendar -> chart reload */
watch(() => repo.Zone,    reloadRose)
watch(() => repo.From,    reloadRose)
watch(() => repo.To,      reloadRose)
watch(() => repo.GroupBy, reloadRose)
watch([locale, calendar], () => nextTick().then(setRoseFromApi))

function handleResize() {
  roseChart?.resize()
}

/* ---------- Quick range buttons ---------- */
function setRange(mode) {
  const now  = new Date()
  const yyyy = now.getFullYear()
  const mm   = String(now.getMonth() + 1).padStart(2, '0')
  const dd   = String(now.getDate()).padStart(2, '0')

  if (mode === 'day') {
    repo.From = `${yyyy}-${mm}-${dd}`
    repo.To   = `${yyyy}-${mm}-${dd}`
    repo.GroupBy = 'day'
  } else if (mode === 'month') {
    const first = `${yyyy}-${mm}-01`
    const last  = `${yyyy}-${mm}-${String(new Date(yyyy, now.getMonth() + 1, 0).getDate()).padStart(2,'0')}`
    repo.From = first
    repo.To   = last
    repo.GroupBy = 'day'
  } else if (mode === 'year') {
    repo.From = `${yyyy}-01-01`
    repo.To   = `${yyyy}-12-31`
    repo.GroupBy = 'month'
  }
  loadAll()
}

/* ---------- load all ---------- */
async function loadAll() {
  if (!repo.GroupBy) repo.GroupBy = 'month'
  await Promise.all([loadCards(), setRoseFromApi()])
}

onMounted(async () => {
  await loadAll()
  window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
  if (roseChart) {
    roseChart.dispose()
    roseChart = null
  }
})
</script>

<style scoped>
.rounded-2xl { border-radius: 16px; }
.text-high   { color: rgba(0,0,0,0.7); }
.gradient-cyan {
  background: linear-gradient(135deg, #e0f7ff, #e1f5fe);
}
.gradient-teal {
  background: linear-gradient(135deg, #e0f7f5, #e0f2f1);
}
.gradient-purple {
  background: linear-gradient(135deg, #f3e8ff, #ede7f6);
}
</style>
