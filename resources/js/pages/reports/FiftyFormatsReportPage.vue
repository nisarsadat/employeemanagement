<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('راپور ۵۶ فارمت‌ها')"
      :subtitle="t('گزارش مجموعی براساس ولایت/زون یا ریسورس')"
      @toggle-drawer="$emit('toggle-drawer')"
    >
      <template #actions>
        <v-btn icon>
          <v-icon>mdi-file-table-box-multiple</v-icon>
        </v-btn>
      </template>
    </AppBar>

    <!-- FILTERS CARD -->
    <v-card elevation="2" class="mb-4">
      <v-card-title class="text-subtitle-1 d-flex justify-space-between align-center">
        <span>
          <v-icon size="18" class="mr-1">mdi-filter-variant</v-icon>
          {{ t('فلترونه د راپور لپاره') }}
        </span>
        <span class="text-caption text-medium-emphasis">
          {{ t('د وخت، ځای او فارمت پر بنسټ راپور جوړ کړئ') }}
        </span>
      </v-card-title>

      <v-divider />

      <v-card-text>
        <!-- Row 1: group / period / from / to -->
        <v-row dense class="mb-2">
          <v-col cols="12" md="3">
            <v-select
              v-model="repo.Group"
              :items="groupOptions"
              item-title="label"
              item-value="value"
              :label="t('نوع گروپ‌بندی')"
              density="compact"
              variant="outlined"
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-select
              v-model="repo.granularity"
              :items="periodOptions"
              item-title="label"
              item-value="value"
              :label="t('دورهٔ زمانی')"
              density="compact"
              variant="outlined"
            />
          </v-col>

          <!-- From date -->
          <v-col cols="12" md="3">
            <v-menu
              v-model="fromMenu"
              :close-on-content-click="false"
              transition="scale-transition"
              offset-y
              max-width="290"
              min-width="290"
            >
              <template #activator="{ props }">
                <v-text-field
                  v-bind="props"
                  :label="t('از تاریخ')"
                  :model-value="fromLabel"
                  density="compact"
                  variant="outlined"
                  readonly
                  :disabled="!!repo.ignoreDates"
                  prepend-inner-icon="mdi-calendar-month-outline"
                />
              </template>

              <v-card>
                <v-date-picker v-model="fromDateIso" hide-header />
                <v-card-actions class="justify-end">
                  <v-btn variant="text" @click="fromMenu = false">
                    {{ t('بستن') }}
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-menu>
          </v-col>

          <!-- To date -->
          <v-col cols="12" md="3">
            <v-menu
              v-model="toMenu"
              :close-on-content-click="false"
              transition="scale-transition"
              offset-y
              max-width="290"
              min-width="290"
            >
              <template #activator="{ props }">
                <v-text-field
                  v-bind="props"
                  :label="t('الی تاریخ')"
                  :model-value="toLabel"
                  density="compact"
                  variant="outlined"
                  readonly
                  :disabled="!!repo.ignoreDates"
                  prepend-inner-icon="mdi-calendar-month-outline"
                />
              </template>

              <v-card>
                <v-date-picker v-model="toDateIso" hide-header />
                <v-card-actions class="justify-end">
                  <v-btn variant="text" @click="toMenu = false">
                    {{ t('بستن') }}
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-menu>
          </v-col>
        </v-row>

        <!-- Row 2: all-time / zone / search / actions -->
        <v-row dense class="mb-2">
          <v-col cols="12" md="3">
            <v-checkbox
              v-model="allTime"
              :label="t('تمام وقت (بدون تاریخ)')"
              density="compact"
              hide-details
              @change="onAllTimeToggle"
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model="repo.Zone"
              :label="t('زون (اختیاری)')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model="repo.Search"
              :label="t('جستجو (ولایت / ریسورس)')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <v-col cols="12" md="3" class="d-flex align-end justify-end flex-wrap gap-2">
            <v-btn
              color="primary"
              prepend-icon="mdi-magnify"
              :loading="repo.loading"
              @click="fetch"
            >
              {{ t('جستجوی راپور') }}
            </v-btn>

            <v-btn
              color="secondary"
              variant="text"
              prepend-icon="mdi-reload"
              @click="resetFilters"
            >
              {{ t('ریست فیلترها') }}
            </v-btn>
          </v-col>
        </v-row>

        <!-- Row 3: formats & exports -->
        <v-row dense>
          <v-col cols="12" md="9">
            <v-select
              v-model="repo.Formats"
              :items="formatOptions"
              item-title="label"
              item-value="value"
              multiple
              density="compact"
              variant="outlined"
              :label="t('فارمت‌ها (جداول شامل در راپور)')"
            >
              <template #selection="{ index }">
                <span v-if="index === 0" class="text-caption">
                  {{ t('فارمت‌های انتخاب شده') }}: {{ repo.Formats.length }}
                </span>
              </template>
            </v-select>
          </v-col>

          <v-col cols="12" md="3" class="d-flex align-end justify-end flex-wrap gap-2">
            <v-btn
              color="success"
              prepend-icon="mdi-file-excel"
              :disabled="!repo.rows.length"
              @click="repo.exportExcelCsv"
            >
              {{ t('اکسل (CSV)') }}
            </v-btn>

            <v-btn
              color="error"
              prepend-icon="mdi-file-pdf-box"
              :disabled="!repo.rows.length"
              @click="repo.exportPdf"
            >
              {{ t('PDF (پشتو تشریح)') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- SUMMARY -->
    <v-row dense class="mb-3">
      <v-col cols="12" md="4">
        <v-card variant="outlined" color="primary" class="summary-card">
          <v-card-text class="py-2 d-flex justify-space-between align-center">
            <span class="text-caption text-white">
              {{ t('مجموع ریکارډونه (ټولې ټاکلې جدولونه)') }}
            </span>
            <span class="text-h6 text-white">{{ repo.summary.overall ?? 0 }}</span>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card variant="outlined" class="summary-card">
          <v-card-text class="py-2 d-flex justify-space-between align-center">
            <span class="text-caption">
              {{ t('ګروپ‌بندی') }}
            </span>
            <span class="text-body-2">
              {{ repo.Group === 'resource' ? t('براساس ریسورس') : t('براساس ولایت/زون') }}
            </span>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card variant="outlined" class="summary-card">
          <v-card-text class="py-2 d-flex justify-space-between align-center">
            <span class="text-caption">
              {{ t('دورهٔ زمانی') }}
            </span>
            <span class="text-body-2">{{ currentPeriodLabel }}</span>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- MAIN TABLE -->
    <v-card elevation="2">
      <v-card-title class="text-subtitle-2 d-flex justify-space-between align-center">
        <span>
          <v-icon size="18" class="mr-1">mdi-table</v-icon>
          {{ tableTitle }}
        </span>
        <span class="text-caption text-medium-emphasis">
          {{ t('ریکارډها:') }} {{ repo.rows.length }}
        </span>
      </v-card-title>

      <v-divider />

      <v-data-table
        :headers="headers"
        :items="repo.rows"
        :loading="repo.loading"
        density="compact"
        class="text-body-2"
        :items-per-page="-1"
        hide-default-footer
        fixed-header
        height="480"
      >
        <template #loading>
          <div class="py-4 text-center">
            <v-progress-circular indeterminate size="22" class="mr-2" />
            {{ t('در حال بارگذاری راپور ...') }}
          </div>
        </template>

        <template #no-data>
          <div class="py-4 text-center">
            {{ t('هیچ دیتایی با این فیلترها پیدا نشد.') }}
          </div>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            size="x-small"
            variant="text"
            color="primary"
            prepend-icon="mdi-eye"
            @click="openDetails(item)"
          >
            {{ t('جزئیات فارمت‌ها') }}
          </v-btn>
        </template>
      </v-data-table>
    </v-card>

    <!-- DETAILS DIALOG -->
    <v-dialog v-model="detailsDialog" max-width="640">
      <v-card>
        <v-card-title class="text-subtitle-1 d-flex align-center">
          <v-icon class="mr-1">mdi-eye</v-icon>
          {{ detailsTitle }}
        </v-card-title>

        <v-divider />

        <v-card-text>
          <v-alert
            v-if="!activeFormats.length"
            type="info"
            density="comfortable"
            variant="tonal"
            class="mb-2"
          >
            {{ t('د دې ګروپ لپاره هیڅ فارمت ریکارډ نلري.') }}
          </v-alert>

          <v-table v-else density="compact">
            <thead>
              <tr>
                <th class="text-left">{{ t('فارمت') }}</th>
                <th class="text-right">{{ t('تعداد') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="fmt in activeFormats"
                :key="fmt.key"
              >
                <td>{{ fmt.label }}</td>
                <td class="text-right">
                  <span class="font-weight-medium">{{ fmt.value }}</span>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>

        <v-card-actions class="justify-end">
          <v-btn color="primary" variant="text" @click="detailsDialog = false">
            {{ t('بستن') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Error snackbar -->
    <v-snackbar
      v-model="snackbar"
      color="error"
      timeout="4000"
      location="top"
      multi-line
    >
      {{ repo.error }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppBar from '@/components/AppBar.vue'
import { useFiftyFormatsReportRepository } from '@/stores/FiftyFormatsReportRepository'
import { useCalendar } from '../../../composables/useCalendar'

const { t, locale } = useI18n()
const repo = useFiftyFormatsReportRepository()
const { formatDisplay, toIso } = useCalendar()

const isRtl = computed(() => ['fa', 'ps', 'ar'].includes(locale.value))

const allTime = ref(!!repo.ignoreDates)
const snackbar = ref(false)

// date pickers – renamed so they don’t conflict with toIso()
const fromMenu = ref(false)
const toMenu = ref(false)
const fromDateIso = ref(repo.From || '')
const toDateIso = ref(repo.To || '')

// keep repo.From / repo.To in sync
watch(
  () => repo.From,
  (val) => { if (val) fromDateIso.value = toIsoDate(val) },
  { immediate: true }
)
watch(
  () => repo.To,
  (val) => { if (val) toDateIso.value = toIsoDate(val) },
  { immediate: true }
)

watch(fromDateIso, (val) => {
  repo.From = val || ''
})
watch(toDateIso, (val) => {
  repo.To = val || ''
})

const fromLabel = computed(() =>
  fromDateIso.value ? formatDisplay(fromDateIso.value, locale.value) : ''
)
const toLabel = computed(() =>
  toDateIso.value ? formatDisplay(toDateIso.value, locale.value) : ''
)

// options
const groupOptions = [
  { value: 'province_zone', label: t('براساس ولایت/زون') },
  { value: 'resource',      label: t('براساس ریسورس') },
]

const periodOptions = [
  { value: 'daily',   label: t('روزانه') },
  { value: 'weekly',  label: t('هفتگی') },
  { value: 'monthly', label: t('ماهه') },
  { value: 'yearly',  label: t('سالیانه') },
]

// formats (same as store)
const formatOptions = [
  { value: 'fasads',                label: t('فاسد اشخاص') },
  { value: 'saeen_fasads',          label: t('سعین فاسد') },
  { value: 'heists',                label: t('هیست/غلا') },
  { value: 'captives',              label: t('اسیران') },
  { value: 'mulkanos',              label: t('ملکانو') },
  { value: 'entry_exits',           label: t('ورود/خروج') },
  { value: 'sarafyanos',            label: t('صرافان') },
  { value: 'clubs',                 label: t('کلب‌ها') },
  { value: 'mahajerats',            label: t('مهاجرت') },
  { value: 'soldiers',              label: t('سربازان') },

  { value: 'mawads',                label: t('مواد') },
  { value: 'lost_mujremeens',       label: t('تښتېدلي مجرمین') },
  { value: 'old_soldiers_homes',    label: t('کورونه د عساکرو پخواني') },
  { value: 'robbers',               label: t('غله') },
  { value: 'non_responsable_people', label: t('اشخاص غیرمسئول') },
  { value: 'mashkok_homes',         label: t('کورونه مشکوک') },
  { value: 'damage_places',         label: t('ځایونه تخریب شوي') },
  { value: 'damage_rasan_places',   label: t('ځایونه تخریب‌رسان') },
  { value: 'mashkok_shops',         label: t('دوکانونه مشکوک') },
  { value: 'luchakanos',            label: t('لچکانو') },

  { value: 'schools',               label: t('مکتبونه') },
  { value: 'hado_bus_stops',        label: t('هادو بس تمځایونه') },
  { value: 'transport_companies',   label: t('شرکت‌های ترانسپورتی') },
  { value: 'car_sales_companies',   label: t('نمایشگاه‌های موتر') },
  { value: 'parkings',              label: t('پارکینګ‌ها') },
  { value: 'banks',                 label: t('بانک‌ها') },
  { value: 'apartments',            label: t('اپارتمان‌ها') },
  { value: 'rahnoma_mamlat',        label: t('راهنما معاملات') },
  { value: 'hosppitals',            label: t('شفاخانه‌ها') },
  { value: 'work_shops',            label: t('ورکشاپ‌ها') },

  { value: 'badmashanos',           label: t('بدماشانو') },
  { value: 'emarat_offices',        label: t('دفاتر امارت') },
  { value: 'hostels',               label: t('هاستل‌ها') },
  { value: 'hotels',                label: t('هوتل‌ها') },
  { value: 'madrasas',              label: t('مدرسه‌ها') },
  { value: 'mashkok_people',        label: t('اشخاص مشکوک') },
  { value: 'mujahedinos',           label: t('مجاهدین') },
  { value: 'old_jahadis',           label: t('جهادی‌های قدیم') },
  { value: 'sarayanos',             label: t('سرایانو') },
  { value: 'tujarans',              label: t('تجاران') },

  { value: 'checkpoints',           label: t('چک‌پواینټ‌ها') },
  { value: 'masjids',               label: t('مساجد') },
  { value: 'highways',              label: t('شاهراه‌ها') },
  { value: 'companies',             label: t('شرکت‌ها') },
  { value: 'done_lost_places',      label: t('محلات مفقود شده') },
  { value: 'econemy_projects',      label: t('پروژه‌های اقتصادی') },
  { value: 'criminal_capture_places', label: t('محلات دستگیری مجرمین') },
  { value: 'pumps',                 label: t('پمپ‌ها') },
  { value: 'shoras',                label: t('شوراها') },
  { value: 'government_places',     label: t('ادارات دولتی') },
  { value: 'super_markets',         label: t('سوپرمارکیت‌ها') },
  { value: 'parks',                 label: t('پارک‌ها') },
  { value: 'complexes',             label: t('کمپلکس‌ها') },
  { value: 'deplomatic_places',     label: t('محلات دیپلوماتیک') },
  { value: 'universities',          label: t('دانشگاه‌ها') },
  { value: 'wakilanos',             label: t('وکیلانو') },
]

const headers = computed(() => {
  const base = []

  if (repo.Group === 'resource') {
    base.push(
      { key: 'resource_id',   title: t('ID ریسورس'),   align: 'start' },
      { key: 'resource_name', title: t('اسم ریسورس'), align: 'start' },
    )
  } else {
    base.push(
      { key: 'province', title: t('ولایت'), align: 'start' },
      { key: 'zone',     title: t('زون'),   align: 'start' },
    )
  }

  return [
    ...base,
    { key: 'total',   title: t('مجموع'),   align: 'end' },
    { key: 'actions', title: t('جزئیات'), align: 'center', sortable: false },
  ]
})

function findFormatLabel(key) {
  const found = formatOptions.find(o => o.value === key)
  return found ? found.label : key
}

const tableTitle = computed(() => {
  return repo.Group === 'resource'
    ? t('راپور براساس ریسورس')
    : t('راپور براساس ولایت/زون')
})

const currentPeriodLabel = computed(() => {
  const found = periodOptions.find(p => p.value === repo.granularity)
  return found ? found.label : repo.granularity
})

function fetch() {
  repo.fetchReport()
}

function resetFilters() {
  repo.Search = ''
  repo.Zone   = ''
  allTime.value = false
  repo.setAllTime(false)
  repo.granularity = 'monthly'
  fromDateIso.value = ''
  toDateIso.value = ''
  repo.From = ''
  repo.To = ''
  fetch()
}

function onAllTimeToggle(val) {
  repo.setAllTime(!!val)
}

function toIsoDate(value) {
  if (!value) return ''
  return toIso(value)
}

// ----- details dialog -----
const detailsDialog = ref(false)
const selectedRow = ref(null)

function openDetails(item) {
  selectedRow.value = item
  detailsDialog.value = true
}

const detailsTitle = computed(() => {
  if (!selectedRow.value) return t('جزئیات فارمت‌ها')
  if (repo.Group === 'resource') {
    return `${t('ریسورس')}: ${selectedRow.value.resource_name ?? ''}`
  }
  return `${t('ولایت')}: ${selectedRow.value.province ?? ''} / ${t('زون')}: ${selectedRow.value.zone ?? ''}`
})

const activeFormats = computed(() => {
  if (!selectedRow.value) return []
  return repo.Formats
    .map(key => ({
      key,
      label: findFormatLabel(key),
      value: selectedRow.value[key] ?? 0,
    }))
    .filter(f => f.value && f.value > 0)
})

watch(
  () => repo.error,
  (val) => { if (val) snackbar.value = true }
)

onMounted(() => {
  if (!repo.rows.length) {
    repo.fetchReport()
  }
})
</script>

<style scoped>
.summary-card {
  border-radius: 12px;
}
</style>
