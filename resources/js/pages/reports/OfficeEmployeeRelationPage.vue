<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="pageTitle"
      :subtitle="pageSubtitle"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions>
        <v-btn
          size="small"
          variant="text"
          class="mx-1"
          @click="goBack"
          color="primary"
        >
          <v-icon start>mdi-arrow-left</v-icon>
          {{ t('بیرته راپور ته') }}
        </v-btn>
        <v-btn
          size="small"
          variant="tonal"
          class="mx-1"
          @click="fetchData"
          :loading="loading"
          color="primary"
        >
          <v-icon start>mdi-refresh</v-icon>
          {{ t('تازه کول') }}
        </v-btn>
      </template>
    </AppBar>

    <!-- Employee Profile Header -->
    <v-card class="mb-6" elevation="2" rounded="lg" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-text class="pa-6">
        <v-row align="center">
          <!-- Employee Avatar and Basic Info -->
          <v-col cols="12" md="8">
            <div class="d-flex align-start">
              <v-avatar color="primary" variant="tonal" size="64" class="me-4">
                <v-icon size="32">mdi-account-circle</v-icon>
              </v-avatar>
              <div class="flex-grow-1">
                <div class="d-flex align-center mb-2">
                  <h2 class="text-h5 font-weight-bold me-3">{{ employee.name }}</h2>
                  <v-chip
                    size="small"
                    :color="
                      employee.activity === 'فعال' || employee.activity === 'active'
                        ? 'success'
                        : 'error'
                    "
                    variant="flat"
                    class="font-weight-bold"
                  >
                    <v-icon start size="small">
                      {{
                        employee.activity === 'فعال' || employee.activity === 'active'
                          ? 'mdi-check-circle'
                          : 'mdi-close-circle'
                      }}
                    </v-icon>
                    {{ activityText(employee.activity) }}
                  </v-chip>
                </div>
                
                <v-row class="mt-3">
                  <v-col cols="12" sm="6" md="4">
                    <div class="d-flex align-center mb-2">
                      <v-icon size="18" color="primary" class="me-2">mdi-identifier</v-icon>
                      <span class="text-caption text-medium-emphasis">{{ t('کود نمبر') }}:</span>
                      <span class="text-body-2 font-weight-medium ms-2">{{ employee.code_number || '---' }}</span>
                    </div>
                  </v-col>
                  <v-col cols="12" sm="6" md="4">
                    <div class="d-flex align-center mb-2">
                      <v-icon size="18" color="primary" class="me-2">mdi-phone</v-icon>
                      <span class="text-caption text-medium-emphasis">{{ t('تلیفون') }}:</span>
                      <span class="text-body-2 font-weight-medium ms-2">{{ employee.phone_number || '---' }}</span>
                    </div>
                  </v-col>
                  <v-col cols="12" sm="6" md="4">
                    <div class="d-flex align-center mb-2">
                      <v-icon size="18" color="primary" class="me-2">mdi-account</v-icon>
                      <span class="text-caption text-medium-emphasis">{{ t('د پلار نوم') }}:</span>
                      <span class="text-body-2 font-weight-medium ms-2">{{ employee.father_name || '---' }}</span>
                    </div>
                  </v-col>
                </v-row>
              </div>
            </div>
          </v-col>

          <!-- Location Info -->
          <v-col cols="12" md="4">
            <v-card variant="outlined" class="pa-3" rounded="lg">
              <div class="text-caption text-medium-emphasis mb-2">{{ t('موقعیت') }}</div>
              <div class="d-flex align-center mb-2">
                <v-icon size="18" color="primary" class="me-2">mdi-map-marker</v-icon>
                <span class="text-body-2">{{ employee.province || '---' }}</span>
              </div>
              <div class="d-flex align-center mb-2">
                <v-icon size="18" color="info" class="me-2">mdi-map</v-icon>
                <span class="text-body-2">{{ t('زون') }}: {{ employee.zone || '---' }}</span>
              </div>
              <div class="d-flex align-center">
                <v-icon size="18" color="secondary" class="me-2">mdi-map-marker-radius</v-icon>
                <span class="text-body-2">{{ t('حوزه') }}: {{ employee.hawza || '---' }}</span>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Period Info -->
        <v-divider class="my-4" />
        <v-row align="center">
          <v-col cols="12" md="6">
            <div class="d-flex align-center">
              <v-icon size="20" color="primary" class="me-2">mdi-calendar</v-icon>
              <div>
                <span class="text-caption text-medium-emphasis">{{ t('موده') }}:</span>
                <span class="text-body-1 font-weight-medium ms-2">{{ periodLabel }}</span>
              </div>
            </div>
          </v-col>
          <v-col cols="12" md="6" class="text-md-end">
            <div class="d-flex align-center justify-end">
              <v-icon size="18" color="grey" class="me-2">mdi-clock-outline</v-icon>
              <span class="text-caption text-medium-emphasis">
                <template v-if="meta.date_from || meta.date_to">
                  {{ t('له نېټې') }}:
                  <strong class="text-primary">{{ meta.date_from || '---' }}</strong>
                  &nbsp; / &nbsp;
                  {{ t('تر نېټې') }}:
                  <strong class="text-primary">{{ meta.date_to || '---' }}</strong>
                </template>
                <template v-else>
                  {{ t('ټوله موده') }}
                </template>
              </span>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Stats Cards -->
    <v-row v-if="rows.length" class="mb-6" :dir="isRtl ? 'rtl' : 'ltr'" dense>
      <v-col cols="12" sm="6" md="6">
        <v-card elevation="2" class="pa-3 text-center stats-card" rounded="lg">
          <v-icon size="40" :color="relationColor" class="mb-2">{{ relationIcon }}</v-icon>
          <div class="text-h5 font-weight-bold">{{ rows.length }}</div>
          <div class="text-caption text-medium-emphasis">{{ relationLabel }}</div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="6">
        <v-card elevation="2" class="pa-3 text-center stats-card" rounded="lg">
          <v-icon size="40" color="success" class="mb-2">mdi-check-all</v-icon>
          <div class="text-h5 font-weight-bold">{{ activeRecordsCount }}</div>
          <div class="text-caption text-medium-emphasis">{{ t('فعال ریکارډونه') }}</div>
        </v-card>
      </v-col>
      
    </v-row>

    <!-- Error Alert -->
    <v-alert
      v-if="error"
      type="error"
      class="mb-4"
      variant="tonal"
      density="compact"
      border="start"
      icon="mdi-alert-circle-outline"
      rounded="lg"
    >
      <div class="d-flex align-center">
        <v-icon size="20" class="me-2">mdi-alert</v-icon>
        <div>{{ error }}</div>
      </div>
    </v-alert>

    <!-- Loading State -->
    <v-skeleton-loader
      v-if="loading"
      class="mt-4"
      type="card, table, table-row-divider"
    />

    <!-- Data Table -->
    <v-card
      v-else-if="rows.length"
      elevation="2"
      rounded="lg"
      :dir="isRtl ? 'rtl' : 'ltr'"
    >
      <v-card-title class="d-flex align-center py-4 px-6">
        <v-avatar :color="relationColor" variant="tonal" size="40" class="me-3">
          <v-icon>{{ relationIcon }}</v-icon>
        </v-avatar>
        <div>
          <div class="text-h6 font-weight-bold">{{ tableTitle }}</div>
          <div class="text-caption text-medium-emphasis">
            {{ t('د کارمند اړوند ریکارډونه') }} - {{ rows.length }} {{ t('ریکارډونه') }}
          </div>
        </div>
        <v-spacer />
        <v-chip variant="outlined" color="primary">
          <v-icon start size="small">mdi-counter</v-icon>
          {{ t('ټول') }}: {{ rows.length }}
        </v-chip>
      </v-card-title>
      
      <v-divider />
      
      <v-data-table
        :items="rows"
        :headers="headers"
        density="comfortable"
        class="elevation-0"
        :items-per-page="-1"
        hide-default-footer
      >
        <template #item.date="{ item }">
          <v-chip size="small" variant="outlined" color="primary">
            <v-icon start size="small">mdi-calendar</v-icon>
            {{ formatDate(item.date) }}
          </v-chip>
        </template>

        <template #item.type="{ item }">
          <v-chip size="small" variant="tonal" color="info">
            {{ item.type || '---' }}
          </v-chip>
        </template>

        <template #item.title="{ item }">
          <span class="font-weight-medium text-body-1">
            {{ item.title || '---' }}
          </span>
        </template>

        <template #item.details="{ item }">
          <div class="text-body-2 line-clamp-2">
            {{ item.details || '---' }}
          </div>
        </template>

        <template #item.id="{ item }">
          <span class="text-caption text-medium-emphasis font-mono">
            #{{ item.id }}
          </span>
        </template>

        <!-- Custom columns for resources -->
        <template v-if="relation === 'resources'" #item.code_number="{ item }">
          <v-chip size="small" variant="outlined">
            {{ item.code_number || '---' }}
          </v-chip>
        </template>

        <template v-if="relation === 'resources'" #item.phone_number="{ item }">
          <div class="d-flex align-center">
            <v-icon size="16" class="me-1">mdi-phone</v-icon>
            {{ item.phone_number || '---' }}
          </div>
        </template>

        <!-- Custom columns for informations -->
        <template v-if="relation === 'informations'" #item.category="{ item }">
          <v-chip size="small" variant="tonal" color="secondary">
            {{ item.category || '---' }}
          </v-chip>
        </template>

        <template v-if="relation === 'informations'" #item.source="{ item }">
          <v-chip size="small" variant="flat" color="warning">
            {{ item.source || '---' }}
          </v-chip>
        </template>

        <!-- Custom columns for events -->
        <template v-if="relation === 'events'" #item.zone="{ item }">
          <v-chip size="small" variant="outlined" color="info">
            {{ item.zone || '---' }}
          </v-chip>
        </template>

        <template v-if="relation === 'events'" #item.hawza="{ item }">
          <v-chip size="small" variant="outlined" color="secondary">
            {{ item.hawza || '---' }}
          </v-chip>
        </template>
      </v-data-table>
    </v-card>

    <!-- Empty State -->
    <v-card
      v-else-if="!loading && !error"
      elevation="2"
      rounded="lg"
      class="text-center py-12"
    >
      <v-avatar color="grey-lighten-3" size="80" class="mb-4">
        <v-icon size="40" color="grey">mdi-folder-open-outline</v-icon>
      </v-avatar>
      <h3 class="text-h5 text-grey mb-2">
        {{ t('هیڅ ریکارډ نشته') }}
      </h3>
      <p class="text-body-1 text-grey mb-4 px-6">
        {{ t('د ټاکل شوې مودې لپاره د دې کارمند لپاره ریکارډ ونه موندل شو.') }}
      </p>
      <v-btn color="primary" @click="fetchData" variant="tonal">
        <v-icon start>mdi-refresh</v-icon>
        {{ t('بیا هڅه وکړئ') }}
      </v-btn>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import AppBar from '@/components/AppBar.vue'
import { useCalendar } from '../../../composables/useCalendar'
import { useOfficeEmployeesReportRepository } from '@/stores/OfficeEmployeesReportRepository'

const { t, locale } = useI18n()
const route = useRoute()
const router = useRouter()
const { formatForDisplay } = useCalendar()
const reportRepo = useOfficeEmployeesReportRepository()

const isRtl = computed(() =>
  ['fa', 'ps', 'prs', 'fa-AF'].includes(locale.value),
)

const drawer = ref(false)
const loading = ref(false)
const error = ref(null)

const rows = ref([])
const meta = ref({})
// ✅ always an object, not null
const employee = ref({})

const relation = computed(() =>
  String(route.params.relation || '').toLowerCase(),
)
const employeeId = computed(() => route.params.id)

// Relation configuration
const relationConfig = computed(() => {
  const configs = {
    events: {
      label: t('پیښې'),
      icon: 'mdi-calendar-check',
      color: 'success',
      description: t('د کارمند اړوند پیښې')
    },
    resources: {
      label: t('منابع'),
      icon: 'mdi-folder-multiple',
      color: 'warning',
      description: t('د کارمند اړوند منابع')
    },
    informations: {
      label: t('معلومات'),
      icon: 'mdi-information',
      color: 'info',
      description: t('د کارمند اړوند معلومات')
    }
  }
  return configs[relation.value] || { label: relation.value, icon: 'mdi-dots-horizontal', color: 'grey', description: '' }
})

const relationLabel = computed(() => relationConfig.value.label)
const relationIcon = computed(() => relationConfig.value.icon)
const relationColor = computed(() => relationConfig.value.color)

const pageTitle = computed(() => `${employee.value?.name || ''} - ${relationLabel.value}`)
const pageSubtitle = computed(() => relationConfig.value.description)

const tableTitle = computed(() => `${relationLabel.value} ${t('لیست')}`)

const periodLabel = computed(() => {
  const p = (meta.value.period || 'all').toLowerCase()
  const periods = {
    daily: t('ورځنی'),
    weekly: t('اونیز'),
    monthly: t('میاشتنی'),
    yearly: t('کلنی'),
    all: t('ټوله موده')
  }
  return periods[p] || p
})

const activeRecordsCount = computed(() => {
  return rows.value.filter(record => record.status !== 'inactive').length
})

const lastUpdateTime = computed(() => {
  const now = new Date()
  return now.toLocaleTimeString(locale.value, { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
})

const headers = computed(() => {
  const baseHeaders = [
    { title: t('شمېره'), value: 'id', width: '8%', sortable: true },
    { title: t('نېټه'), value: 'date', width: '12%', sortable: true },
  ]

  if (relation.value === 'events') {
    return [
      ...baseHeaders,
      { title: t('ډول'), value: 'type', width: '12%', sortable: true },
      { title: t('سرلیک'), value: 'title', width: '20%', sortable: true },
      { title: t('زون'), value: 'zone', width: '10%', sortable: true },
      { title: t('حوزه'), value: 'hawza', width: '10%', sortable: true },
      { title: t('جزئیات'), value: 'details', width: '28%', sortable: false },
    ]
  }

  if (relation.value === 'resources') {
    return [
      ...baseHeaders,
      { title: t('نوم'), value: 'name', width: '15%', sortable: true },
      { title: t('کود نمبر'), value: 'code_number', width: '12%', sortable: true },
      { title: t('تلیفون'), value: 'phone_number', width: '13%', sortable: true },
      { title: t('ډول'), value: 'type', width: '10%', sortable: true },
      { title: t('جزئیات'), value: 'details', width: '30%', sortable: false },
    ]
  }

  // informations
  return [
    ...baseHeaders,
    { title: t('سرلیک'), value: 'title', width: '20%', sortable: true },
    { title: t('کټګوري'), value: 'category', width: '15%', sortable: true },
    { title: t('منبع'), value: 'source', width: '15%', sortable: true },
    { title: t('جزئیات'), value: 'details', width: '30%', sortable: false },
  ]
})

function activityText(act) {
  if (act === 'active') return t('فعال')
  if (act === 'inactive') return t('غیر فعال')
  return act || '---'
}

function formatDate(dateString) {
  if (!dateString) return ''
  if (typeof formatForDisplay === 'function') {
    try {
      return formatForDisplay(dateString)
    } catch {
      // ignore
    }
  }
  const d = new Date(dateString)
  if (Number.isNaN(d.getTime())) return dateString
  return d.toLocaleDateString('en-US', {
    month: '2-digit',
    day: '2-digit',
    year: 'numeric',
  })
}

function goBack() {
  router.push({ name: 'office-employees-activity-report' })
}

async function fetchData() {
  loading.value = true
  error.value = null

  try {
    if (!employeeId.value || !relation.value) {
      throw new Error('Missing id or relation for fetchEmployeeRelation')
    }

    // we pass period/from/to only for meta display; backend ignores dates for relation
    const data = await reportRepo.fetchEmployeeRelation(
      employeeId.value,
      relation.value,
      {
        period: route.query.period || undefined,
        from_date: route.query.from_date || undefined,
        to_date: route.query.to_date || undefined,
      },
    )

    meta.value = data.meta || {}
    // ✅ always fallback to {}
    employee.value = meta.value.employee || {}

    rows.value = (data.data || []).map((row) => {
      const r = { ...row }
      if (relation.value === 'events') {
        r.date = row.event_date
      } else if (relation.value === 'resources') {
        r.date = row.resource_saved_date
      } else if (relation.value === 'informations') {
        r.date = row.information_saved_date
      }
      return r
    })
  } catch (e) {
    console.error(e)
    error.value =
      e?.response?.data?.message ||
      e.message ||
      t('ستونزه رامنځته شوه، معلومات لوډ نه شول.')
    rows.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.stats-card {
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.05);
}

.stats-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.font-mono {
  font-family: 'Courier New', monospace;
}

:deep(.v-data-table) {
  border-radius: 0 0 8px 8px;
}

:deep(.v-data-table-header) {
  background-color: rgba(0,0,0,0.02);
}

:deep(.v-data-table .v-chip) {
  height: 24px;
}
</style>
