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
                {{ t('ریکارډونه') }}: <strong>{{ repo.summary?.incomeCount ?? repo.totalItems ?? 0 }}</strong>
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
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="10">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('د راتلونکو تېلو لټون')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          @keyup.enter="applyFilters"
          @click:clear="() => { repo.Search=''; applyFilters(); }"
        />
      </v-col>

      <!-- <v-col cols="12" md="3">
        <v-select
          v-model="repo.year"
          :items="repo.availableYears"
          :label="t('کال')"
          variant="outlined"
          density="compact"
          clearable
          :loading="repo.yearsLoading"
          @update:modelValue="applyFilters"
        />
      </v-col>

      <v-col cols="12" md="3">
        <v-select
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
      </v-col> -->

      <v-col cols="12" md="2" class="text-md-right">
        <v-btn color="primary" prepend-icon="mdi-plus" block @click="openCreate">
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
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useOilRepository } from '@/stores/OilRepository'
import IncomeOilFormDialog from './IncomeOilFormDialog.vue'
import FilterableTable from '@/components/FilterableTable.vue'
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue'
import { useCalendar } from '../../../../composables/useCalendar'

const repo = useOilRepository()
const { t, locale } = useI18n()
const { formatDisplay, toIso } = useCalendar()
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''))

const monthOptions = computed(() => ([
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
]))

function getRow(payload) {
  return payload?.raw ?? payload
}

const headersFT = computed(() => [
  { title: t('حساب'), key: 'account', sortable: false },
  { title: t('مقدار (L)'), key: 'oil_quantity', sortable: false },
  { title: t('نیټه'), key: 'date', sortable: false },
  { title: t('یادونه'), key: 'note', sortable: false },
])

const rowsFT = computed(() => {
  return (repo.IncomeOils || []).map((it, i) => ({
    ...it,
    index: i + 1,
    oil_quantity: qty(it?.oil_quantity),
    account: it?.account?.name ?? '—',
    date: formatDisplay(it?.date, locale.value),
    note: it?.note ?? '',
  }))
})

const filters = []
const topGroups = []

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

const fetchAll = async () => {
  await repo.FetchIncomeOils({ page: repo.page, itemsPerPage: repo.itemsPerPage })
  if (repo.FetchOilSummary) await repo.FetchOilSummary()
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
  Object.assign(draft, { id: null, oil_account_id: null, date: null, oil_quantity: 0, source: '', note: '' })
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

const qty = (n) =>
  Number(n ?? 0).toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 })

const money = (n) => Number(n ?? 0).toLocaleString(undefined, { maximumFractionDigits: 0 })
const liters = (n) =>
  `${Number(n ?? 0).toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 })} L`

onMounted(async () => {
  if (repo.FetchYears) await repo.FetchYears('income-oils')
  await fetchAll()
})
</script>

<style scoped>
.icon-btn {
  border-radius: 10px;
}
</style>
