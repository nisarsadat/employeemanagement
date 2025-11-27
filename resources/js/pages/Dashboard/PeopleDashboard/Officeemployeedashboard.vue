<!-- src/views/dashboard/OfficeEmployeeDashboard.vue -->
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
          :label="t(' از تاریخ')"
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

      <v-col cols="12" md="3">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('پلټنه')"
          prepend-inner-icon="mdi-magnify"
          density="compact"
          variant="outlined"
          clearable
          @keyup.enter="loadAll"
          @click:clear="
            () => {
              repo.Search = '';
              loadAll();
            }
          "
        />
      </v-col>
    </v-row>

    <!-- Manual refresh -->
    <v-row>
      <v-col>
        <v-btn color="primary" class="rounded-xl" @click="loadAll">{{ t('دریافت مجدد') }}</v-btn>
      </v-col>
    </v-row>

    <!-- KPI Cards (soft glass) -->
    <v-row>
      <v-col cols="12" md="4">
        <v-card class="glass-soft rounded-2xl gradient-teal" :elevation="3">
          <v-card-text class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-high">{{ t(' کارمندان فعال') }}</div>
              <div class="text-h4 font-weight-bold mt-1">{{ cards.active_employees ?? '—' }}</div>
            </div>
            <v-icon size="48">mdi-account-group</v-icon>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="glass-soft rounded-2xl gradient-cyan" :elevation="3">
          <v-card-text class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-high">{{ t(' منابع جدید') }}</div>
              <div class="text-h4 font-weight-bold mt-1">{{ cards.new_resources ?? '—' }}</div>
            </div>
            <v-icon size="48">mdi-account-plus</v-icon>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="glass-soft rounded-2xl gradient-purple" :elevation="3">
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

    <!-- Charts -->
    <v-row class="mt-1">
      <v-col cols="12" md="6">
        <v-card class="glass-soft rounded-2xl" :elevation="2">
          <v-card-text>
            <ChartBox
              :title="t('کارمندان از روی زون')"
              color="indigo-lighten-4"
              :option="optEmployeesByZone"
              height="360px"
            />
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card class="glass-soft rounded-2xl" :elevation="2">
          <v-card-text>
            <RandomNamePie
              :title="t('کارمندان از روی زون به فیصدی)')"
              :labels="pieLabels"
              :values="pieValues"
              color="indigo-lighten-4"
              :rtl="direction === 'rtl'"
              height="416px"
            />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Employees with Events / Informations by Zone -->
    <v-row class="mt-2">
      <v-col cols="12">
        <v-card class="glass-soft rounded-2xl" :elevation="2">
          <v-card-text>
            <ChartBox
              :title="t('کارمندان که واقعات دارند از روی زون')"
              color="deep-purple-lighten-4"
              :option="optEmployeesWithEvents"
              height="360px"
            />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Activity Table -->
    <v-row class="mt-1">
      <v-col cols="12">
        <v-card class="glass-soft rounded-2xl" :elevation="2">
          <v-card-title class="text-subtitle-1 font-weight-bold">
            {{ t('فعالیت ها از لحاظ کارمندان') }}
          </v-card-title>

          <v-card-subtitle class="pb-6">
            <v-btn
              class="mr-2 rounded-xl"
              :color="activityGroup === 'events' ? 'primary' : 'grey'"
              size="small"
              @click="switchGroup('events')"
            >
              {{ t('واقعات') }}
            </v-btn>
            <v-btn
              class="rounded-xl"
              :color="activityGroup === 'informations' ? 'primary' : 'grey'"
              size="small"
              @click="switchGroup('informations')"
            >
              {{ t('اطلاعات') }}
            </v-btn>
          </v-card-subtitle>

          <v-data-table
            :items="activityRows"
            :headers="activityHeaders"
            :items-length="activityTotal"
            :items-per-page="perPage"
            :page="page"
            class="px-2"
            density="compact"
            hover
            @update:page="(p) => { page = p; loadActivity(); }"
            @update:items-per-page="(n) => { perPage = n; loadActivity(); }"
          >
            <template #item.employee_name="{ item }">
              <span class="font-weight-medium">
                {{ (item?.raw ?? item)?.employee_name ?? '—' }}
              </span>
            </template>

            <template #item.zone="{ item }">
              <v-chip size="small" color="primary" variant="tonal">
                {{ (item?.raw ?? item)?.zone || t('نامشخص') }}
              </v-chip>
            </template>

            <template #item.activity="{ item }">
              <v-chip
                size="small"
                :color="(item?.raw ?? item)?.activity === 'فعال' ? 'teal' : 'grey'"
                variant="elevated"
              >
                {{ (item?.raw ?? item)?.activity ?? '—' }}
              </v-chip>
            </template>

            <template #item.count="{ item }">
              <v-chip size="small" color="deep-purple" variant="tonal">
                {{ (item?.raw ?? item)?.count ?? 0 }}
              </v-chip>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import ChartBox from '@/components/ChartBox.vue'
import RandomNamePie from '@/components/RandomNamePie.vue'
import { usePeopleDashboardRepository } from '@/stores/PeopleDashboardRepository'
import { useCalendar } from '../../../../composables/useCalendar'

/* repos & i18n */
const drawer = ref(false)
const repo = usePeopleDashboardRepository()
const { t, locale } = useI18n()
const { calendar } = useCalendar()

/* direction */
const direction = computed(() =>
  ['fa', 'ps', 'ar', 'ur'].includes(locale.value) ? 'rtl' : 'ltr'
)

/* cards */
const cards = ref({})

/* activity table */
const activityGroup = ref('events')
const activityHeaders = [
  { title: t('نوم') || t('نوم'), key: 'employee_name' },
  { title: t('زون'), key: 'zone' },
  { title: t('حالت'), key: 'activity' },
  { title: t('شمېر'), key: 'count', align: 'end' },
]
const activityRows = ref([])
const activityTotal = ref(0)
const page = ref(1)
const perPage = ref(10)

/* echarts options (reactive) */
const optEmployeesByZone = ref({})
const optEmployeesWithEvents = ref({})
const optEmployeesWithInfos = ref({})

/* pie data */
const pieLabels = ref([])
const pieValues = ref([])

/* helpers */
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

/* data loaders */
async function loadCards() {
  const res = await repo.fetchCards()
  cards.value = res && typeof res === 'object' ? res : {}
}

async function loadCharts() {
  // employees by zone
  const zoneRowsRaw = await repo.employeesByZone(
    normalizeDate(repo.From),
    normalizeDate(repo.To),
    repo.Zone || '',
    repo.Search || ''
  )
  const zoneRows = Array.isArray(zoneRowsRaw) ? zoneRowsRaw : zoneRowsRaw?.data ?? []
  const zones = zoneRows.map((r) => r?.zone ?? t('نامعلوم'))
  const totals = zoneRows.map((r) => Number(r?.total ?? 0))
  const actives = zoneRows.map((r) => Number(r?.active ?? 0))

  optEmployeesByZone.value = {
    tooltip: { trigger: 'axis' },
    legend: {},
    xAxis: { type: 'category', data: zones },
    yAxis: { type: 'value' },
    series: [
      { name: t('ټول'), type: 'bar', data: totals },
      { name: t('فعال'), type: 'bar', data: actives },
    ],
  }

  // pie
  pieLabels.value = zones
  pieValues.value = totals

  // employees with events by zone
  const evRowsRaw = await repo.employeesWithEventsByZone(
    normalizeDate(repo.From),
    normalizeDate(repo.To),
    repo.Zone || '',
    repo.Search || ''
  )
  const evRows = Array.isArray(evRowsRaw) ? evRowsRaw : evRowsRaw?.data ?? []
  optEmployeesWithEvents.value = buildBarOption(
    evRows.map((r) => r?.zone ?? t('نامعلوم')),
    t('پېښې لرونکي'),
    evRows.map((r) => Number(r?.count ?? 0))
  )

  // employees with informations by zone (prepared; not shown in UI by default)
  const infoRowsRaw = await repo.employeesWithInformationsByZone(
    normalizeDate(repo.From),
    normalizeDate(repo.To),
    repo.Zone || '',
    repo.Search || ''
  )
  const infoRows = Array.isArray(infoRowsRaw) ? infoRowsRaw : infoRowsRaw?.data ?? []
  optEmployeesWithInfos.value = buildBarOption(
    infoRows.map((r) => r?.zone ?? t('نامعلوم')),
    t('مالومات لرونکي'),
    infoRows.map((r) => Number(r?.count ?? 0))
  )
}

function buildBarOption(x, sName, data) {
  return {
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    toolbox: { feature: { saveAsImage: {} } },
    grid: { left: 30, right: 20, bottom: 40, top: 30 },
    xAxis: { type: 'category', data: x, axisTick: { alignWithLabel: true } },
    yAxis: { type: 'value' },
    series: [{ name: sName, type: 'bar', data, emphasis: { focus: 'series' } }],
  }
}

async function loadActivity() {
  const res = await repo.employeeActivity(
    activityGroup.value,
    page.value,
    perPage.value,
    normalizeDate(repo.From),
    normalizeDate(repo.To),
    repo.Zone || '',
    repo.Search || ''
  )
  const rows = Array.isArray(res?.data) ? res.data : []

  // SAFELY provide employee_name for the table (no DB column required)
  activityRows.value = rows.map((r) => ({
    employee_name: r.employee_name ?? r.name ?? r.employee ?? '—',
    zone: r.zone ?? null,
    activity: r.activity ?? r.status ?? '—',
    count: Number(r.count ?? r.total ?? 0),
  }))

  activityTotal.value = Number(res?.meta?.total ?? rows.length)
}

function switchGroup(g) {
  activityGroup.value = g
  page.value = 1
  loadActivity()
}

/* debounced reloads when filters change */
const reloadAll = debounce(loadAll, 250)
watch(() => repo.Zone, reloadAll)
watch(() => repo.From, reloadAll)
watch(() => repo.To, reloadAll)
watch(() => repo.Search, reloadAll)
watch([locale, calendar], reloadAll)

/* quick range buttons */
function setRange(mode) {
  const now = new Date()
  const yyyy = now.getFullYear()
  const mm = String(now.getMonth() + 1).padStart(2, '0')
  const dd = String(now.getDate()).padStart(2, '0')

  if (mode === 'day') {
    repo.From = `${yyyy}-${mm}-${dd}`
    repo.To   = `${yyyy}-${mm}-${dd}`
  } else if (mode === 'month') {
    const first = `${yyyy}-${mm}-01`
    const last  = `${yyyy}-${mm}-${String(new Date(yyyy, now.getMonth() + 1, 0).getDate()).padStart(2, '0')}`
    repo.From = first
    repo.To   = last
  } else if (mode === 'year') {
    repo.From = `${yyyy}-01-01`
    repo.To   = `${yyyy}-12-31`
  }
  loadAll()
}

/* master loader */
async function loadAll() {
  await Promise.all([loadCards(), loadCharts(), loadActivity()])
}

onMounted(loadAll)
</script>

<style scoped>
/* Minimal soft glass */
.glass-soft {
  background: rgba(255, 255, 255, 0.35);
  border: 1px solid rgba(0, 0, 0, 0.06);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.text-high { color: rgba(0, 0, 0, 0.7); }
.rounded-2xl { border-radius: 16px; }

/* subtle gradients for KPIs */
.gradient-cyan    { background: linear-gradient(135deg, #e0f7ff, #e1f5fe); }
.gradient-teal    { background: linear-gradient(135deg, #e0f7f5, #e0f2f1); }
.gradient-purple  { background: linear-gradient(135deg, #f3e8ff, #ede7f6); }
</style>
