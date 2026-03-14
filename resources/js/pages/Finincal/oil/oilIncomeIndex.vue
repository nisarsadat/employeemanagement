<!-- src/pages/Oil/IncomeOils/IncomeOilsIndex.vue -->
<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- ✅ TOP SUMMARY CARDS (Finance style - on every page) -->
    <v-row class="mb-4" dense :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" md="4">
        <v-card elevation="2" rounded="xl" class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-medium-emphasis">{{ t('ټول مصرف') }}</div>
              <div class="text-h6 font-weight-bold mt-1">{{ money(repo.summary?.totalExpense) }}</div>
              <div class="text-caption mt-1">
                {{ t('ریکارډونه') }}: <strong>{{ repo.summary?.expenseCount ?? 0 }}</strong>
              </div>
            </div>
            <v-avatar size="44" class="elevation-1">
              <v-icon size="24">mdi-cash-minus</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card elevation="2" rounded="xl" class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-medium-emphasis">{{ t('ټول عاید') }}</div>
              <div class="text-h6 font-weight-bold mt-1">{{ money(repo.summary?.totalIncome) }}</div>
              <div class="text-caption mt-1">
                {{ t('ریکارډونه') }}:
                <strong>{{ repo.summary?.incomeCount ?? repo.totalItems ?? 0 }}</strong>
              </div>
            </div>
            <v-avatar size="44" class="elevation-1">
              <v-icon size="24">mdi-cash-plus</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card elevation="2" rounded="xl" class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-medium-emphasis">{{ t('ټول تېل (موجود)') }}</div>
              <div class="text-h6 font-weight-bold mt-1">{{ liters(repo.summary?.totalOilQuantity) }}</div>
              <div class="text-caption mt-1">
                {{ t('حسابونه') }}: <strong>{{ repo.summary?.oilAccountsCount ?? 0 }}</strong>
              </div>
            </div>
            <v-avatar size="44" class="elevation-1">
              <v-icon size="24">mdi-fuel</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- ✅ Top Bar (Finance style: Search + Year + Month + Create) -->
    <v-row class=" mb-4" dense>
      <v-col cols="12" md="10 ">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('د راتلونکو تېلو لټون')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          @keyup.enter="applyFilters"
          @click:clear="
            () => {
              repo.Search = '';
              applyFilters();
            }
          "
        />
      </v-col>

      <!-- ✅ Year / Month (✅ same calendar-aware logic as Finances) -->
      <!-- <v-col cols="12" md="3">
        <v-select
          v-model="selectedYear"
          :items="yearItems"
          item-title="title"
          item-value="value"
          :label="t('کال')"
          variant="outlined"
          density="compact"
          clearable
          :loading="repo.yearsLoading"
          @update:modelValue="onYearMonthChanged"
        />
      </v-col>

      <v-col cols="12" md="3">
        <v-select
          v-model="selectedMonth"
          :items="monthOptions"
          item-title="title"
          item-value="value"
          :label="t('میاشت')"
          variant="outlined"
          density="compact"
          clearable
          :disabled="!selectedYear"
          @update:modelValue="onYearMonthChanged"
        />
      </v-col> -->

      <v-col cols="12" md="2" class="text-md-right align-center  ">
        <v-btn color="primary" class="mt-2" prepend-icon="mdi-plus" block @click="openCreate">
          {{ t('نوی د تيلو داخلول') }}
        </v-btn>
      </v-col>
    </v-row>

    <v-card elevation="2" class="elevation-0">
      <v-card-text class="pa-0">
        <FilterableTable
          :loading="repo.loading"
          :headers="headersFT"
          :rows="rowsFT"
          :filters="filters"
          :topGroups="topGroups"
        >
          <template #actions="{ item }">
            <div class="d-flex align-center ga-2">
              <v-tooltip :text="t('ویرایش')" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-btn
                    v-bind="tooltipProps"
                    icon
                    size="x-small"
                    variant="tonal"
                    color="amber-darken-2"
                    class="icon-btn"
                    @click.stop="startEdit(item)"
                  >
                    <v-icon size="18">mdi-pencil</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>

              <v-tooltip :text="t('حذف')" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-btn
                    v-bind="tooltipProps"
                    icon
                    size="x-small"
                    variant="tonal"
                    color="red-darken-2"
                    class="icon-btn"
                    @click.stop="openDeleteDialog(item)"
                  >
                    <v-icon size="18">mdi-delete</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
            </div>
          </template>
        </FilterableTable>
      </v-card-text>
    </v-card>

    <GlobalPagination
      :page="repo.page"
      :items-per-page="repo.itemsPerPage"
      :total-items="repo.totalItems"
      @update:page="handlePageChange"
      @update:items-per-page="handlePerPageChange"
    />

    <IncomeOilFormDialog
      v-model="dialog"
      :editMode="editMode"
      :value="draft"
      :busy="repo.loading"
      @save="onSave"
    />

    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />
  </v-container>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'

import { useOilRepository } from '@/stores/OilRepository'
import IncomeOilFormDialog from './IncomeOilFormDialog.vue'
import FilterableTable from '@/components/FilterableTable.vue'
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue'
import GlobalPagination from '@/components/GlobalPagination.vue'
import { useCalendar } from '../../../../composables/useCalendar'
  import { usePageRefresh } from '@/composables/usePageRefresh';

const repo = useOilRepository()
const { t, locale } = useI18n()

/* ===== RTL ===== */
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''))

function getRow(payload) {
  return payload?.raw ?? payload
}

/* ===== Calendar (same as Finances) ===== */
const { calendar, formatYearDisplay, toIso } = useCalendar()

/* ===== Year/Month filters (values stay Gregorian) ===== */
const selectedYear = ref(null)  // sent to backend (gregorian)
const selectedMonth = ref(null) // sent to backend (gregorian 1..12)

/* ===== ✅ Year items from backend, titles follow calendar mode ===== */
const yearItems = ref([])
const yearNumbers = ref([])

function rebuildYearItems() {
  if (!Array.isArray(yearNumbers.value) || yearNumbers.value.length === 0) return
  yearItems.value = yearNumbers.value.map((y) => ({
    value: y,
    title: typeof formatYearDisplay === 'function' ? formatYearDisplay(y) : String(y),
  }))
}

async function fetchYearItemsFromRepo() {
  const years = Array.isArray(repo.availableYears) ? repo.availableYears : []
  yearNumbers.value = years.map((y) => Number(y)).filter((n) => Number.isFinite(n))
  rebuildYearItems()
}

/* update year titles when calendar/locale changes (selected value stays same) */
watch([calendar, locale], () => rebuildYearItems())

/* ===== ✅ Month titles follow calendar mode, values stay gregorian 1..12 ===== */
const JALALI_MONTHS_FA = ['حمل', 'ثور', 'جوزا', 'سرطان', 'اسد', 'سنبله', 'میزان', 'عقرب', 'قوس', 'جدی', 'دلو', 'حوت']

const HIJRI_QAMARI_MONTHS_FA = [
  'محرم',
  'صفر',
  'ربیع‌الاول',
  'ربیع‌الثانی',
  'جمادی‌الاول',
  'جمادی‌الثانی',
  'رجب',
  'شعبان',
  'رمضان',
  'شوال',
  'ذوالقعده',
  'ذوالحجه',
]

function monthTitleFromGregorianMonth(gYear, gMonth) {
  const iso = `${gYear || dayjs().year()}-${String(gMonth).padStart(2, '0')}-15`

  if (calendar.value === 'gregorian') {
    try {
      const dt = new Date(iso + 'T00:00:00')
      return new Intl.DateTimeFormat(locale.value, { month: 'long' }).format(dt)
    } catch {
      return String(gMonth)
    }
  }

  if (calendar.value === 'jalali') {
    try {
      const dt = new Date(iso + 'T00:00:00')
      const fmt = new Intl.DateTimeFormat(locale.value, { calendar: 'persian', month: 'numeric' })
      const parts = fmt.formatToParts(dt)
      const m = Number(parts.find((p) => p.type === 'month')?.value)
      if (m >= 1 && m <= 12) return JALALI_MONTHS_FA[m - 1]
    } catch {}
    return JALALI_MONTHS_FA[(gMonth - 1) % 12]
  }

  if (calendar.value === 'hijri') {
    try {
      const dt = new Date(iso + 'T00:00:00')
      const fmt = new Intl.DateTimeFormat('ar', { calendar: 'islamic-umalqura', month: 'numeric' })
      const parts = fmt.formatToParts(dt)
      const m = Number(parts.find((p) => p.type === 'month')?.value)
      if (m >= 1 && m <= 12) return HIJRI_QAMARI_MONTHS_FA[m - 1]
    } catch {}
    return HIJRI_QAMARI_MONTHS_FA[(gMonth - 1) % 12]
  }

  return String(gMonth)
}

const monthOptions = computed(() => {
  const y = selectedYear.value || dayjs().year()
  return Array.from({ length: 12 }).map((_, i) => {
    const m = i + 1
    return { value: m, title: monthTitleFromGregorianMonth(y, m) }
  })
})

function onYearMonthChanged() {
  if (!selectedYear.value) selectedMonth.value = null
  applyFilters()
}

/* ===== Headers / rows ===== */
const headersFT = computed(() => [
  { title: t('حساب'), key: 'account', sortable: false },
  { title: t('مقدار (L)'), key: 'oil_quantity', sortable: false },
  { title: t('د تیلو د عاید نیټه'), key: 'date', sortable: false },
  { title: t('یادونه'), key: 'note', sortable: false },
])

const qty = (n) =>
  Number(n ?? 0).toLocaleString(undefined, { maximumFractionDigits: 3 })

// ✅ IMPORTANT: add idOrTmp + keep date RAW (let FilterableTable format it)
const rowsFT = computed(() => {
  return (repo.IncomeOils || []).map((it, i) => ({
    ...it,
    idOrTmp: it?.id ?? `tmp-${i}`,
    index: i + 1,
    oil_quantity: qty(it?.oil_quantity),
    account: it?.account?.name ?? '—',
    date: it?.date ?? null, // ✅ raw
    note: it?.note ?? '',
    raw: it,
  }))
})

const filters = []
const topGroups = []

/* ===== Dialog / CRUD ===== */
const dialog = ref(false)
const editMode = ref(false)

const deleteDialog = ref(false)
const deleteTarget = ref(null)

const draft = reactive({
  id: null,
  oil_account_id: null,
  date: null,
  oil_quantity: 0,
  source: '',
  note: '',
})

/* ===== Fetch / filters (✅ now includes search + year + month) ===== */
const fetchAll = async () => {
  await repo.FetchIncomeOils({
    page: repo.page,
    itemsPerPage: repo.itemsPerPage,
    search: repo.Search,
    year: selectedYear.value,
    month: selectedMonth.value,
  })
  if (repo.FetchOilSummary) await repo.FetchOilSummary()
}

const applyFilters = async () => {
  repo.page = 1
  await fetchAll()
}

const handlePageChange = async (p) => {
  repo.page = p || 1
  await fetchAll()
}

const handlePerPageChange = async (pp) => {
  repo.itemsPerPage = pp || 10
  repo.page = 1
  await fetchAll()
}

const openCreate = () => {
  editMode.value = false
  Object.assign(draft, {
    id: null,
    oil_account_id: null,
    date: null,
    oil_quantity: 0,
    source: '',
    note: '',
  })
  dialog.value = true
}

const startEdit = async (payload) => {
  const row = getRow(payload)
  const id = row?.id
  if (!id) return

  editMode.value = true
  await repo.FetchIncomeOil(id)

  const d = repo.IncomeOil || {}
  Object.assign(draft, {
    id: d.id ?? null,
    oil_account_id: d.oil_account_id ?? d.account?.id ?? null,
    date: d.date ?? null,
    oil_quantity: d.oil_quantity ?? 0,
    source: d.source ?? '',
    note: d.note ?? '',
  })

  dialog.value = true
}

const onSave = async (payload) => {
  if (payload?.date) payload.date = toIso(payload.date)

  if (editMode.value && payload.id) await repo.UpdateIncomeOil(payload.id, payload)
  else await repo.CreateIncomeOil(payload)

  dialog.value = false
  await fetchAll()
}

function openDeleteDialog(payload) {
  const row = getRow(payload)
  if (!row?.id) return
  deleteTarget.value = row
  deleteDialog.value = true
}

async function handleDeleteConfirmed(target) {
  const row = target || deleteTarget.value
  if (!row?.id) {
    deleteDialog.value = false
    deleteTarget.value = null
    return
  }

  try {
    await repo.DeleteIncomeOil(row.id)
    await fetchAll()
  } finally {
    deleteDialog.value = false
    deleteTarget.value = null
  }
}
  usePageRefresh('/app/finincalindex', fetchAll);

/* ===== Formatters ===== */
const money = (n) => Number(n ?? 0).toLocaleString(undefined, { maximumFractionDigits: 0 })
const liters = (n) =>
  `${Number(n ?? 0).toLocaleString(undefined, { maximumFractionDigits: 3 })} L`

/* ===== Init ===== */
onMounted(async () => {
  if (repo.FetchYears) await repo.FetchYears('income-oils') // fills repo.availableYears
  await fetchYearItemsFromRepo()
  await fetchAll()
})
</script>

<style scoped>
.icon-btn {
  border-radius: 10px;
}
</style>