<template>
  <v-card rounded="xl" class="data-card" elevation="0" :dir="rtl ? 'rtl' : 'ltr'">
    <div class="card-header" :class="headerClass">
      <div class="d-flex align-center">
        <v-avatar size="34" :color="chipColor" variant="tonal" class="mr-2">
          <v-icon icon="mdi-note-text-outline" size="18" />
        </v-avatar>
        <div>
          <div class="card-title fontfamily">{{ title }}</div>
          <div class="card-subtitle fontfamily">
            {{ subtitle }}
          </div>
        </div>
      </div>

      <v-chip :color="chipColor" variant="tonal" size="small" class="fontfamily">
        {{ total }}
      </v-chip>
    </div>

    <v-divider />

    <v-card-text class="pa-3">
      <div class="d-flex flex-wrap ga-2 align-center justify-space-between">
        <v-text-field
          v-model="search"
          :label="t('Search in notes')"
          variant="outlined"
          density="compact"
          clearable
          class="flex-1"
          style="min-width: 240px"
          @keyup.enter="fetchData(1)"
        />

        <div class="d-flex ga-2">
          <v-btn color="primary" variant="tonal" class="text-none" :loading="loading" @click="fetchData(1)">
            <v-icon class="mr-2">mdi-refresh</v-icon>
            {{ t('Refresh') }}
          </v-btn>
        </div>
      </div>
    </v-card-text>

    <v-card-text class="pa-0">
      <v-data-table
        :headers="headers"
        :items="rows"
        :loading="loading"
        item-key="id"
        class="elevation-0"
        hide-default-footer
        density="comfortable"
      >
        <template #item.created_at="{ item }">
          <span class="fontfamily">{{ formatDateTime(item.created_at) }}</span>
        </template>

        <template #item.report_note="{ item }">
          <div class="text-body-2 fontfamily" style="white-space: pre-wrap">
            {{ item.report_note }}
          </div>
        </template>

        <template #no-data>
          <div class="py-10 text-center text-medium-emphasis fontfamily">
            {{ t('No notes found') }}
          </div>
        </template>
      </v-data-table>
    </v-card-text>

    <v-divider />

    <v-card-actions class="py-3 d-flex justify-space-between">
      <div class="text-caption text-medium-emphasis fontfamily">
        {{ pageText }}
      </div>

      <div class="d-flex ga-2">
        <v-btn variant="tonal" :disabled="page <= 1" @click="fetchData(page - 1)">
          {{ t('Prev') }}
        </v-btn>

        <v-btn variant="tonal" :disabled="page >= lastPage" @click="fetchData(page + 1)">
          {{ t('Next') }}
        </v-btn>
      </div>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useReportNoteRepository } from '@/stores/ReportNoteRepository'

const { t } = useI18n()

const props = defineProps({
  type: { type: String, required: true }, // daily|weekly|monthly|quarter|yearly
  anchorDate: { type: String, default: '' }, // YYYY-MM-DD (daily/weekly/quarter/year)
  anchorMonth: { type: String, default: '' }, // YYYY-MM (monthly)
  perPage: { type: Number, default: 50 },
  rtl: { type: Boolean, default: false },
  locale: { type: String, default: 'fa' },
})

const repo = useReportNoteRepository()

const loading = ref(false)
const rows = ref([])
const total = ref(0)

const page = ref(1)
const lastPage = ref(1)

const search = ref('')

const headers = computed(() => ([
  { title: t('ID'), key: 'id', width: 80 },
  { title: t('Note'), key: 'report_note' },
  { title: t('Created'), key: 'created_at', width: 190 },
]))

const title = computed(() => {
  const map = {
    daily: t('Daily Notes'),
    weekly: t('Weekly Notes'),
    monthly: t('Monthly Notes'),
    quarter: t('Quarter Notes'),
    yearly: t('Yearly Notes'),
  }
  return map[props.type] || t('Report Notes')
})

const subtitle = computed(() => {
  const r = rangeText.value
  return `${t('Range')}: ${r}`
})

const chipColor = computed(() => {
  const map = {
    daily: 'error',
    weekly: 'warning',
    monthly: 'primary',
    quarter: 'info',
    yearly: 'success',
  }
  return map[props.type] || 'primary'
})

const headerClass = computed(() => {
  const map = {
    daily: 'danger-header',
    weekly: 'warning-header',
    monthly: 'primary-header',
    quarter: 'info-header',
    yearly: 'success-header',
  }
  return map[props.type] || 'primary-header'
})

const pageText = computed(() =>
  `${t('Page')} ${page.value} ${t('of')} ${lastPage.value} — ${t('Total')}: ${total.value}`
)

function pad2(n) {
  return String(n).padStart(2, '0')
}

function isoDate(d) {
  const y = d.getFullYear()
  const m = pad2(d.getMonth() + 1)
  const day = pad2(d.getDate())
  return `${y}-${m}-${day}`
}

function startOfWeek(d) {
  const x = new Date(d)
  const day = x.getDay()
  const diff = (day === 0 ? -6 : 1) - day
  x.setDate(x.getDate() + diff)
  return x
}

function endOfWeek(d) {
  const s = startOfWeek(d)
  const e = new Date(s)
  e.setDate(e.getDate() + 6)
  return e
}

function quarterOfMonth(mIndex0) {
  return Math.floor(mIndex0 / 3) + 1
}

function rangeForPeriod() {
  const type = String(props.type || '').trim()
  const anchor = props.anchorDate || new Date().toISOString().slice(0, 10)
  const ad = new Date(`${anchor}T00:00:00`)

  if (type === 'daily') {
    const from = isoDate(ad)
    const to = isoDate(ad)
    return { from, to }
  }

  if (type === 'weekly') {
    const s = startOfWeek(ad)
    const e = endOfWeek(ad)
    return { from: isoDate(s), to: isoDate(e) }
  }

  if (type === 'monthly') {
    const month = props.anchorMonth || `${ad.getFullYear()}-${pad2(ad.getMonth() + 1)}`
    const [y, m] = month.split('-').map(Number)
    const s = new Date(y, m - 1, 1)
    const e = new Date(y, m, 0)
    return { from: isoDate(s), to: isoDate(e) }
  }

  if (type === 'quarter') {
    const y = ad.getFullYear()
    const q = quarterOfMonth(ad.getMonth())
    const startMonth = (q - 1) * 3
    const s = new Date(y, startMonth, 1)
    const e = new Date(y, startMonth + 3, 0)
    return { from: isoDate(s), to: isoDate(e) }
  }

  if (type === 'yearly') {
    const y = ad.getFullYear()
    const s = new Date(y, 0, 1)
    const e = new Date(y, 11, 31)
    return { from: isoDate(s), to: isoDate(e) }
  }

  return { from: isoDate(ad), to: isoDate(ad) }
}

const range = computed(() => rangeForPeriod())

const rangeText = computed(() => {
  const r = range.value
  return `${r.from} - ${r.to}`
})

function formatDateTime(val) {
  if (!val) return ''
  try {
    const d = new Date(val)
    return d.toLocaleString(props.locale === 'fa' ? 'fa-IR' : 'en-US')
  } catch {
    return String(val)
  }
}

async function fetchData(p = 1) {
  loading.value = true
  try {
    const r = range.value
    const res = await repo.FetchReportNotesData({
      page: p,
      perPage: props.perPage,
      type: props.type,
      search: search.value || undefined,
      from: r.from,
      to: r.to,
    })

    rows.value = res.data || []
    page.value = res.current_page || 1
    lastPage.value = res.last_page || 1
    total.value = res.total || 0
  } finally {
    loading.value = false
  }
}

watch(
  () => [props.type, props.anchorDate, props.anchorMonth],
  () => fetchData(1),
  { deep: true }
)

onMounted(() => fetchData(1))
</script>

<style scoped>
.data-card {
  border-radius: 12px;
  border: 1px solid #e9eef7;
  overflow: hidden;
  background: #fff;
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

.primary-header { background: linear-gradient(135deg, #eef2ff 0%, #dde5ff 100%); }
.warning-header { background: linear-gradient(135deg, #fff6df 0%, #ffeab6 100%); }
.success-header { background: linear-gradient(135deg, #e8fbf2 0%, #d2f7e5 100%); }
.info-header    { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); }
.danger-header  { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); }

.fontfamily,
.fontfamily * {
  font-family: var(--app-font) !important;
}
</style>
