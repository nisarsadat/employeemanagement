<!-- src/pages/Oil/ExpenseOils/ExpenseOilsIndex.vue -->
<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- ✅ SUMMARY CARDS -->
    <v-row class="mb-4" dense :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" md="4">
        <v-card elevation="2" rounded="xl" class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-medium-emphasis">{{ t('ټول مصرف') }}</div>
              <div class="text-h6 font-weight-bold mt-1">
                {{ liters(summaryView.totalExpense) }}
              </div>
              <div class="text-caption mt-1">
                {{ t('ریکارډونه') }}: <strong>{{ repo.totalItems ?? 0 }}</strong>
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
              <div class="text-h6 font-weight-bold mt-1">
                {{ liters(summaryView.totalIncome) }}
              </div>
              <div class="text-caption mt-1">
                {{ t('ریکارډونه') }}: <strong>{{ summaryView.incomeCount }}</strong>
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
              <div class="text-h6 font-weight-bold mt-1">
                {{ liters(summaryView.totalOilQuantity) }}
              </div>
              <div class="text-caption mt-1">
                {{ t('حسابونه') }}: <strong>{{ summaryView.oilAccountsCount }}</strong>
              </div>
            </div>
            <v-avatar size="44" class="elevation-1">
              <v-icon size="24">mdi-fuel</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- ✅ Top Bar -->
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="4">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('د تلونکو تېلو لټون')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          @keyup.enter="applyFilters"
          @click:clear="() => { repo.Search=''; applyFilters(); }"
        />
      </v-col>

      <!-- ✅ Year (label changes by calendar, value stays Gregorian year) -->
      <v-col cols="12" md="3">
        <v-select
          :key="`year-${calKey}`"
          v-model="repo.year"
          :items="yearOptions"
          item-title="title"
          item-value="value"
          :label="t('کال')"
          variant="outlined"
          density="compact"
          clearable
          :loading="repo.yearsLoading"
          @update:modelValue="applyFilters"
        />
      </v-col>

      <!-- ✅ Month (label changes by calendar, value stays 1..12 Gregorian index) -->
      <v-col cols="12" md="3">
        <v-select
          :key="`month-${calKey}`"
          v-model="repo.month"
          :items="monthOptions"
          item-title="title"
          item-value="value"
          :label="t('میاشت')"
          variant="outlined"
          density="compact"
          clearable
          @update:modelValue="applyFilters"
        />
      </v-col>

      <v-col cols="12" md="2" class="text-md-right">
        <v-btn color="primary" prepend-icon="mdi-plus" block @click="openCreate">
          {{ t('نوی د تيلو مصرف') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- TABLE -->
    <v-card elevation="2" class="elevation-0">
      <v-card-text class="pa-0">
        <FilterableTable
          :loading="repo.loading"
          :headers="headersFT"
          :rows="rowsFT"
          :filters="filters"
          :topGroups="topGroups"
          :totalItems="repo.totalItems"
          :page="repo.page"
          :perPage="repo.itemsPerPage"
          @update:page="onPage"
          @update:perPage="onItemsPerPage"
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

    <ExpenseOilFormDialog
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
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useOilRepository } from '@/stores/OilRepository'
import ExpenseOilFormDialog from './ExpenseOilFormDialog.vue'
import FilterableTable from '@/components/FilterableTable.vue'
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue'
import { useCalendar } from '../../../../composables/useCalendar'

const repo = useOilRepository()
const { t, locale } = useI18n()

const cal = useCalendar()
const formatDisplay = cal.formatDisplay
const toIso = cal.toIso

// ✅ THIS is what AppBar changes: calendar.value = 'gregorian' | 'jalali' | 'hijri'
const calendar = cal.calendar

const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''))

function getRow(payload) {
  return payload?.raw ?? payload
}

// ✅ key to force v-select re-render when calendar changes
const calKey = computed(() => String(calendar?.value || 'gregorian'))

// =========================
// ✅ Month options (label changes by calendar)
// value ALWAYS 1..12 (Gregorian month index) -> sent to backend
// =========================
const monthOptions = computed(() => {
  const c = String(calendar?.value || 'gregorian')

  if (c === 'jalali') {
    return [
      { title: t('حمل'), value: 1 },
      { title: t('ثور'), value: 2 },
      { title: t('جوزا'), value: 3 },
      { title: t('سرطان'), value: 4 },
      { title: t('اسد'), value: 5 },
      { title: t('سنبله'), value: 6 },
      { title: t('میزان'), value: 7 },
      { title: t('عقرب'), value: 8 },
      { title: t('قوس'), value: 9 },
      { title: t('جدی'), value: 10 },
      { title: t('دلو'), value: 11 },
      { title: t('حوت'), value: 12 },
    ]
  }

  if (c === 'hijri') {
    return [
      { title: t('محرم'), value: 1 },
      { title: t('صفر'), value: 2 },
      { title: t('ربیع الاول'), value: 3 },
      { title: t('ربیع الثانی'), value: 4 },
      { title: t('جمادی الاول'), value: 5 },
      { title: t('جمادی الثانی'), value: 6 },
      { title: t('رجب'), value: 7 },
      { title: t('شعبان'), value: 8 },
      { title: t('رمضان'), value: 9 },
      { title: t('شوال'), value: 10 },
      { title: t('ذوالقعده'), value: 11 },
      { title: t('ذوالحجه'), value: 12 },
    ]
  }

  return [
    { title: t('جنوري'), value: 1 },
    { title: t('فبروري'), value: 2 },
    { title: t('مارچ'), value: 3 },
    { title: t('اپریل'), value: 4 },
    { title: t('می'), value: 5 },
    { title: t('جون'), value: 6 },
    { title: t('جولای'), value: 7 },
    { title: t('اګست'), value: 8 },
    { title: t('سپتمبر'), value: 9 },
    { title: t('اکتوبر'), value: 10 },
    { title: t('نومبر'), value: 11 },
    { title: t('ډسمبر'), value: 12 },
  ]
})

// =========================
// ✅ Year options
// value = Gregorian year (backend filter)
// title = calendar converted display (from your composable helper)
// =========================
const yearOptions = computed(() => {
  // uses your useCalendar.js helper (it reads calendar.value internally)
  return cal.yearItemsFromGregorianYears(repo.availableYears || [], locale.value)
})

// =========================
// ✅ Cards summary adapter (supports both camelCase and snake_case)
// =========================
const summaryView = computed(() => {
  const s = repo.summary || {}

  // backend (snake_case)
  const totalExpenseSnake = s.total_expense_quantity ?? s.filtered_expense_quantity
  const totalIncomeSnake = s.total_income_quantity
  const totalOilSnake = s.total_quantity_all_accounts

  // frontend (camelCase)
  const totalExpenseCamel = s.totalExpense ?? s.filteredExpense
  const totalIncomeCamel = s.totalIncome
  const totalOilCamel = s.totalOilQuantity

  return {
    totalExpense: Number(totalExpenseSnake ?? totalExpenseCamel ?? 0),
    totalIncome: Number(totalIncomeSnake ?? totalIncomeCamel ?? 0),
    totalOilQuantity: Number(totalOilSnake ?? totalOilCamel ?? 0),
    incomeCount: Number(s.incomeCount ?? 0),
    oilAccountsCount: Number(s.oilAccountsCount ?? 0),
  }
})

// =========================
// TABLE
// =========================
const headersFT = computed(() => [
  { title: t('کود کارمند'), key: 'officeemployee', sortable: false },
  { title: t('نام'), key: 'name', sortable: false },
  { title: t('نام پدر'), key: 'father_name', sortable: false },
  { title: t('نیټه'), key: 'date', sortable: false },
  { title: t('لیټر په عدد'), key: 'liter_in_number', sortable: false },
  { title: t('نوعیت'), key: 'account', sortable: false },
  { title: t('تفصیل'), key: 'tafseel', sortable: false },
  { title: t('پرداخت کننده'), key: 'payer', sortable: false },
  { title: t('ملاحظات'), key: 'note', sortable: false },
  { title: t('عملیات'), key: 'actions', sortable: false },
])

const litersRaw = (n) =>
  Number(n ?? 0).toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 })

const rowsFT = computed(() => {
  return (repo.ExpenseOils || []).map((it, i) => ({
    ...it,
    index: i + 1,
    account: it?.account?.name ?? it?.account ?? '—',
    name: it?.name ?? '—',
    father_name: it?.father_name ?? '—',
    officeemployee: it?.employee?.code_number ?? it?.office_employee?.code_number ?? '—',
    payer: it?.payer ?? '—',
    liter_in_number: litersRaw(it?.liter_in_number ?? it?.oil_quantity ?? 0),
    date: formatDisplay(it?.date, locale.value),
    note: it?.note ?? '',
    tafseel: it?.tafseel ?? it?.destination ?? '',
    raw: it,
  }))
})

const filters = []
const topGroups = []

// =========================
// Dialogs / CRUD
// =========================
const dialog = ref(false)
const editMode = ref(false)

const deleteDialog = ref(false)
const deleteTarget = ref(null)

const draft = reactive({
  id: null,
  oil_account_id: null,
  date: null,
  name: '',
  destination: '',
  liter_in_number: 0,
  note: '',
})

const fetchAll = async () => {
  // ✅ This call returns list + summary (backend additional).
  await repo.FetchExpenseOils({ page: repo.page, itemsPerPage: repo.itemsPerPage })
}

const applyFilters = async () => {
  repo.page = 1
  await fetchAll()
}

const onPage = async (p) => {
  repo.page = p || 1
  await fetchAll()
}

const onItemsPerPage = async (pp) => {
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
    name: '',
    destination: '',
    liter_in_number: 0,
    note: '',
  })
  dialog.value = true
}

const startEdit = async (payload) => {
  const row = getRow(payload)
  const id = row?.id
  if (!id) return

  editMode.value = true
  await repo.FetchExpenseOil(id)

  const d = repo.ExpenseOil || {}
  Object.assign(draft, {
    id: d.id ?? null,
    oil_account_id: d.oil_account_id ?? d.account?.id ?? null,
    date: d.date ?? null,
    name: d.name ?? '',
    destination: d.destination ?? '',
    liter_in_number: d.liter_in_number ?? 0,
    note: d.note ?? '',
  })

  dialog.value = true
}

const onSave = async (payload) => {
  if (payload?.date) payload.date = toIso(payload.date)

  if (editMode.value && payload.id) await repo.UpdateExpenseOil(payload.id, payload)
  else await repo.CreateExpenseOil(payload)

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
    await repo.DeleteExpenseOil(row.id)
    await fetchAll()
  } finally {
    deleteDialog.value = false
    deleteTarget.value = null
  }
}

const liters = (n) =>
  `${Number(n ?? 0).toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 })} L`

onMounted(async () => {
  await repo.FetchYears('expense-oils')
  await fetchAll()
})
</script>

<style scoped>
.icon-btn {
  border-radius: 10px;
}
</style>
