<template>
  <v-container fluid class="pa-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Modern App Bar -->
    <AppBar
      :title="t('راپور ۵۶ فارمت‌ها')"
      :subtitle="t('گزارش مجموعی براساس ولایت/زون یا منبع')"
      @toggle-drawer="$emit('toggle-drawer')"
    >
      <template #actions>
        <v-btn icon variant="flat" color="primary" size="default">
          <v-icon>mdi-file-table-box-multiple</v-icon>
        </v-btn>
      </template>
    </AppBar>

    <!-- Elegant Filter Card -->
    <v-card elevation="2" class="mb-6 filter-card" rounded="lg" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-text class="pa-5 px-6">
        <!-- Elegant Header -->
        <div class="filter-header mb-5">
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center">
              <div class="icon-container mr-3 ml-12">
                <v-icon size="22" color="primary">mdi-filter-cog</v-icon>
              </div>
              <div>
                <div class="text-h6 font-weight-medium text-grey-darken-4">
                  {{ t('فلترونه د راپور لپاره') }}
                </div>
                <div class="text-caption text-grey-darken-1 mt-1">
                  {{ t('د وخت، ځای او فارمت پر بنسټ راپور جوړ کړئ') }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Rows -->
        <v-row dense class="mb-4" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-col cols="12" md="3">
            <v-select
              v-model="repo.Group"
              :items="groupOptions"
              item-title="label"
              item-value="value"
              :label="t('نوع گروپ‌بندی')"
              density="comfortable"
              variant="outlined"
              color="primary"
              hide-details
              bg-color="grey-lighten-4"
              prepend-inner-icon="mdi-chart-tree"
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-select
              v-model="repo.granularity"
              :items="periodOptions"
              item-title="label"
              item-value="value"
              :label="t('دورهٔ زمانی')"
              density="comfortable"
              variant="outlined"
              color="primary"
              hide-details
              bg-color="grey-lighten-4"
              prepend-inner-icon="mdi-chart-timeline"
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
                  density="comfortable"
                  variant="outlined"
                  readonly
                  :disabled="!!repo.ignoreDates"
                  prepend-inner-icon="mdi-calendar-blank"
                  color="primary"
                  hide-details
                  bg-color="grey-lighten-4"
                />
              </template>

              <v-card class="elevation-4" rounded="lg">
                <v-date-picker v-model="fromDateIso" hide-header color="primary" />
                <v-card-actions class="justify-end pa-3 bg-grey-lighten-4">
                  <v-btn variant="text" color="primary" @click="fromMenu = false">
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
                  density="comfortable"
                  variant="outlined"
                  readonly
                  :disabled="!!repo.ignoreDates"
                  prepend-inner-icon="mdi-calendar-blank"
                  color="primary"
                  hide-details
                  bg-color="grey-lighten-4"
                />
              </template>

              <v-card class="elevation-4" rounded="lg">
                <v-date-picker v-model="toDateIso" hide-header color="primary" />
                <v-card-actions class="justify-end pa-3 bg-grey-lighten-4">
                  <v-btn variant="text" color="primary" @click="toMenu = false">
                    {{ t('بستن') }}
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-menu>
          </v-col>
        </v-row>

        <!-- Row 2: all-time / zone / search / actions -->
        <v-row dense class="mb-4">
          <v-col cols="12" md="3" class="d-flex align-center">
            <v-checkbox
              v-model="allTime"
              :label="t('تمام وقت (بدون تاریخ)')"
              density="comfortable"
              hide-details
              color="primary"
              @change="onAllTimeToggle"
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model="repo.Zone"
              :label="t('زون (اختیاری)')"
              density="comfortable"
              variant="outlined"
              clearable
              color="primary"
              hide-details
              bg-color="grey-lighten-4"
              prepend-inner-icon="mdi-map-marker-outline"
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model="repo.Search"
              :label="t('جستجو (ولایت / منبع)')"
              density="comfortable"
              variant="outlined"
              clearable
              color="primary"
              hide-details
              bg-color="grey-lighten-4 "
              prepend-inner-icon="mdi-magnify"
            />
          </v-col>

          <v-col cols="12" md="3" class="d-flex align-end justify-end flex-wrap gap-3">
            <v-btn
              color="primary"
              prepend-icon="mdi-chart-box"
              :loading="repo.loading"
              @click="fetch"
              variant="flat"
              class="action-btn"
              size="large"
            >
              {{ t('جستجوی راپور') }}
            </v-btn>

            <v-btn
              color="grey-darken-1"
              variant="outlined"
              prepend-icon="mdi-refresh"
              @click="resetFilters"
              class="action-btn"
              size="large"
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
              density="comfortable"
              variant="outlined"
              :label="t('فارمت‌ها (جداول شامل در راپور)')"
              color="primary"
              bg-color="grey-lighten-4"
              hide-details
            >
              <template #selection="{ index }">
                <span v-if="index === 0" class="text-caption text-grey-darken-2">
                  {{ t('انتخاب شده') }}: {{ repo.Formats.length }}
                </span>
              </template>
            </v-select>
          </v-col>

          <v-col cols="12" md="3" class="d-flex align-end justify-end flex-wrap gap-3">
            <v-btn
              color="success"
              prepend-icon="mdi-file-excel"
              :disabled="!repo.rows.length"
              @click="repo.exportExcelCsv"
              variant="flat"
              class="export-btn"
              size="large"
            >
              {{ t('اکسل (CSV)') }}
            </v-btn>

            <v-btn
              color="error"
              prepend-icon="mdi-file-pdf-box"
              :disabled="!repo.rows.length"
              @click="repo.exportPdf"
              variant="flat"
              class="export-btn"
              size="large"
            >
              {{ t('PDF ') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Elegant Horizontal Summary Card - Simple -->
    <v-row dense class="mb-4" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12">
        <v-card class="summary-card" elevation="2" rounded="lg">
          <v-card-text class="py-5 px-6">
            <div class="d-flex align-center w-100">
              <div class="icon-container primary-icon mr-4 ml-12">
                <v-icon  color="white">mdi-database</v-icon>
              </div>
              <div class="flex-grow-1">
                <div class="text-body-1 text-grey-darken-2 font-weight-medium">
                  {{ t('مجموع ریکارډونه (ټولې ټاکلې جدولونه)') }}
                </div>
                <div class="text-h5 font-weight-bold text-grey-darken-4 mt-2">
                  {{ repo.summary.overall ?? 0 }}
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Elegant Table Card -->
    <v-card elevation="2" class="table-card" rounded="lg">
      <v-card-title class="table-header pa-4">
        <div class="d-flex justify-space-between align-center w-100">
          <div class="d-flex align-center">
            <div class="icon-container table-icon mr-3">
              <v-icon size="20" color="white">mdi-table</v-icon>
            </div>
            <span class="text-subtitle-1 font-weight-medium text-white">
              {{ tableTitle }}
            </span>
          </div>
          <div class="record-count">
            <span class="text-caption text-white text-medium-emphasis">
              {{ t('ریکارډها:') }}
              <span class="font-weight-bold">{{ repo.rows.length }}</span>
            </span>
          </div>
        </div>
      </v-card-title>

      <v-divider />

      <v-data-table
        :headers="headers"
        :items="repo.rows"
        :loading="repo.loading"
        density="comfortable"
        class="text-body-2 custom-table"
        :items-per-page="-1"
        hide-default-footer
        fixed-header
        height="480"
      >
        <template #loading>
          <div class="py-8 text-center">
            <v-progress-circular indeterminate size="24" color="primary" class="mr-3" />
            <span class="text-body-1 text-grey-darken-2">{{ t('در حال بارگذاری راپور ...') }}</span>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-center">
            <v-icon size="48" color="grey-lighten-1" class="mb-3">mdi-database-off</v-icon>
            <div class="text-body-1 text-grey-darken-2">
              {{ t('هیچ دیتایی با این فیلترها پیدا نشد.') }}
            </div>
          </div>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            color="primary"
            prepend-icon="mdi-eye-outline"
            @click="openDetails(item)"
          >
            {{ t('جزئیات فارمت‌ها') }}
          </v-btn>
        </template>
      </v-data-table>
    </v-card>

    <!-- Elegant Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="600" persistent>
      <v-card class="elevation-8" rounded="lg">
        <v-card-title class="details-header pa-4">
          <div class="d-flex align-center w-100">
            <div class="icon-container dialog-icon mr-3">
              <v-icon color="white">mdi-chart-box-outline</v-icon>
            </div>
            <span class="text-subtitle-1 font-weight-medium text-white">
              {{ detailsTitle }}
            </span>
            <v-spacer />
            <v-btn icon variant="text" color="white" @click="detailsDialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>

        <v-divider />

        <v-card-text class="pa-5">
          <v-alert
            v-if="!activeFormats.length"
            type="info"
            density="comfortable"
            variant="tonal"
            class="mb-4"
            border="start"
            color="primary"
          >
            {{ t('د دې ګروپ لپاره هیڅ فارمت ریکارډ نلري.') }}
          </v-alert>

          <v-table v-else density="comfortable" class="details-table elevation-1" rounded>
            <thead>
              <tr class="bg-grey-lighten-4">
                <th class="text-left text-subtitle-2 font-weight-medium pa-3">{{ t('فارمت') }}</th>
                <th class="text-right text-subtitle-2 font-weight-medium pa-3">{{ t('تعداد') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(fmt, index) in activeFormats"
                :key="fmt.key"
                :class="{ 'bg-grey-lighten-5': index % 2 === 0 }"
                class="table-row"
              >
                <td class="text-body-2 pa-3">{{ fmt.label }}</td>
                <td class="text-right pa-3">
                  <v-chip size="default" color="primary" variant="flat" class="font-weight-bold">
                    {{ fmt.value }}
                  </v-chip>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>

        <v-card-actions class="justify-end pa-4 bg-grey-lighten-4">
          <v-btn color="primary" variant="flat" @click="detailsDialog = false" size="large">
            <v-icon start>mdi-check</v-icon>
            {{ t('بستن') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Elegant Error Snackbar -->
    <v-snackbar v-model="snackbar" color="error" timeout="4000" location="top" elevation="8">
      <div class="d-flex align-center">
        <v-icon class="mr-3">mdi-alert-circle-outline</v-icon>
        <span class="text-body-2">{{ repo.error }}</span>
      </div>
      <template #actions>
        <v-btn icon variant="text" @click="snackbar = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import { useFiftyFormatsReportRepository } from '@/stores/FiftyFormatsReportRepository';
  import { useCalendar } from '../../../composables/useCalendar';

  const { t, locale } = useI18n();
  const repo = useFiftyFormatsReportRepository();
  const { formatDisplay, toIso } = useCalendar();

  const isRtl = computed(() => ['fa', 'ps', 'ar'].includes(locale.value));

  const allTime = ref(!!repo.ignoreDates);
  const snackbar = ref(false);

  // date pickers – renamed so they don't conflict with toIso()
  const fromMenu = ref(false);
  const toMenu = ref(false);
  const fromDateIso = ref(repo.From || '');
  const toDateIso = ref(repo.To || '');

  // keep repo.From / repo.To in sync
  watch(
    () => repo.From,
    (val) => {
      if (val) fromDateIso.value = toIsoDate(val);
    },
    { immediate: true }
  );
  watch(
    () => repo.To,
    (val) => {
      if (val) toDateIso.value = toIsoDate(val);
    },
    { immediate: true }
  );

  watch(fromDateIso, (val) => {
    repo.From = val || '';
  });
  watch(toDateIso, (val) => {
    repo.To = val || '';
  });

  const fromLabel = computed(() =>
    fromDateIso.value ? formatDisplay(fromDateIso.value, locale.value) : ''
  );
  const toLabel = computed(() =>
    toDateIso.value ? formatDisplay(toDateIso.value, locale.value) : ''
  );

  // options
  const groupOptions = [
    { value: 'province_zone', label: t('براساس ولایت/زون') },
    { value: 'resource', label: t('براساس منبع') },
  ];

  const periodOptions = [
    { value: 'daily', label: t('روزانه') },
    { value: 'weekly', label: t('هفتگی') },
    { value: 'monthly', label: t('ماهه') },
    { value: 'yearly', label: t('سالیانه') },
  ];

  // formats (same as store)
  const formatOptions = [
    { value: 'fasads', label: t('فاسد اشخاص') },
    { value: 'saeen_fasads', label: t('سعین فاسد') },
    { value: 'heists', label: t('هیست/غلا') },
    { value: 'captives', label: t('اسیران') },
    { value: 'mulkanos', label: t('ملکانو') },
    { value: 'entry_exits', label: t('ورود/خروج') },
    { value: 'sarafyanos', label: t('صرافان') },
    { value: 'clubs', label: t('کلب‌ها') },
    { value: 'mahajerats', label: t('مهاجرت') },
    { value: 'soldiers', label: t('سربازان') },

    { value: 'mawads', label: t('مواد') },
    { value: 'lost_mujremeens', label: t('تښتېدلي مجرمین') },
    { value: 'old_soldiers_homes', label: t('کورونه د عساکرو پخواني') },
    { value: 'robbers', label: t('غله') },
    { value: 'non_responsable_people', label: t('اشخاص غیرمسئول') },
    { value: 'mashkok_homes', label: t('کورونه مشکوک') },
    { value: 'damage_places', label: t('ځایونه تخریب شوي') },
    { value: 'damage_rasan_places', label: t('ځایونه تخریب‌رسان') },
    { value: 'mashkok_shops', label: t('دوکانونه مشکوک') },
    { value: 'luchakanos', label: t('لچکانو') },

    { value: 'schools', label: t('مکتبونه') },
    { value: 'hado_bus_stops', label: t('هادو بس تمځایونه') },
    { value: 'transport_companies', label: t('شرکت‌های ترانسپورتی') },
    { value: 'car_sales_companies', label: t('نمایشگاه‌های موتر') },
    { value: 'parkings', label: t('پارکینګ‌ها') },
    { value: 'banks', label: t('بانک‌ها') },
    { value: 'apartments', label: t('اپارتمان‌ها') },
    { value: 'rahnoma_mamlat', label: t('راهنما معاملات') },
    { value: 'hosppitals', label: t('شفاخانه‌ها') },
    { value: 'work_shops', label: t('ورکشاپ‌ها') },

    { value: 'badmashanos', label: t('بدماشانو') },
    { value: 'emarat_offices', label: t('دفاتر امارت') },
    { value: 'hostels', label: t('هاستل‌ها') },
    { value: 'hotels', label: t('هوتل‌ها') },
    { value: 'madrasas', label: t('مدرسه‌ها') },
    { value: 'mashkok_people', label: t('اشخاص مشکوک') },
    { value: 'mujahedinos', label: t('مجاهدین') },
    { value: 'old_jahadis', label: t('جهادی‌های قدیم') },
    { value: 'sarayanos', label: t('سرایانو') },
    { value: 'tujarans', label: t('تجاران') },

    { value: 'checkpoints', label: t('چک‌پواینټ‌ها') },
    { value: 'masjids', label: t('مساجد') },
    { value: 'highways', label: t('شاهراه‌ها') },
    { value: 'companies', label: t('شرکت‌ها') },
    { value: 'done_lost_places', label: t('محلات مفقود شده') },
    { value: 'econemy_projects', label: t('پروژه‌های اقتصادی') },
    { value: 'criminal_capture_places', label: t('محلات دستگیری مجرمین') },
    { value: 'pumps', label: t('پمپ‌ها') },
    { value: 'shoras', label: t('شوراها') },
    { value: 'government_places', label: t('ادارات دولتی') },
    { value: 'super_markets', label: t('سوپرمارکیت‌ها') },
    { value: 'parks', label: t('پارک‌ها') },
    { value: 'complexes', label: t('کمپلکس‌ها') },
    { value: 'deplomatic_places', label: t('محلات دیپلوماتیک') },
    { value: 'universities', label: t('دانشگاه‌ها') },
    { value: 'wakilanos', label: t('وکیلانو') },
  ];

  const headers = computed(() => {
    const base = [];

    if (repo.Group === 'resource') {
      base.push(
        { key: 'resource_id', title: t('ID منبع'), align: 'start', width: '120px' },
        { key: 'resource_name', title: t('اسم منبع'), align: 'start' }
      );
    } else {
      base.push(
        { key: 'province', title: t('ولایت'), align: 'start' },
        { key: 'zone', title: t('زون'), align: 'start' }
      );
    }

    return [
      ...base,
      { key: 'total', title: t('مجموع'), align: 'end', width: '100px' },
      { key: 'actions', title: t('جزئیات'), align: 'center', sortable: false, width: '150px' },
    ];
  });

  function findFormatLabel(key) {
    const found = formatOptions.find((o) => o.value === key);
    return found ? found.label : key;
  }

  const tableTitle = computed(() => {
    return repo.Group === 'resource' ? t('راپور براساس منبع') : t('راپور براساس ولایت/زون');
  });

  const currentPeriodLabel = computed(() => {
    const found = periodOptions.find((p) => p.value === repo.granularity);
    return found ? found.label : repo.granularity;
  });

  function fetch() {
    repo.fetchReport();
  }

  function resetFilters() {
    repo.Search = '';
    repo.Zone = '';
    allTime.value = false;
    repo.setAllTime(false);
    repo.granularity = 'monthly';
    fromDateIso.value = '';
    toDateIso.value = '';
    repo.From = '';
    repo.To = '';
    fetch();
  }

  function onAllTimeToggle(val) {
    repo.setAllTime(!!val);
  }

  function toIsoDate(value) {
    if (!value) return '';
    return toIso(value);
  }

  // ----- details dialog -----
  const detailsDialog = ref(false);
  const selectedRow = ref(null);

  function openDetails(item) {
    selectedRow.value = item;
    detailsDialog.value = true;
  }

  const detailsTitle = computed(() => {
    if (!selectedRow.value) return t('جزئیات فارمت‌ها');
    if (repo.Group === 'resource') {
      return `${t('منبع')}: ${selectedRow.value.resource_name ?? ''}`;
    }
    return `${t('ولایت')}: ${selectedRow.value.province ?? ''} / ${t('زون')}: ${
      selectedRow.value.zone ?? ''
    }`;
  });

  const activeFormats = computed(() => {
    if (!selectedRow.value) return [];
    return repo.Formats.map((key) => ({
      key,
      label: findFormatLabel(key),
      value: selectedRow.value[key] ?? 0,
    })).filter((f) => f.value && f.value > 0);
  });

  watch(
    () => repo.error,
    (val) => {
      if (val) snackbar.value = true;
    }
  );

  onMounted(() => {
    if (!repo.rows.length) {
      repo.fetchReport();
    }
  });
</script>

<style scoped>
  /* Filter Card */
  .filter-card {
    border: 1px solid #e0e0e0;
    background: #ffffff;
  }

  .filter-header {
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 16px;
  }

  .icon-container {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  /* Summary Cards */
  .summary-card {
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
  }

  .summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.15) !important;
  }

  .primary-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .secondary-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  }

  .accent-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }

  /* Table Styling */
  .table-card {
    border: 1px solid #e0e0e0;
    overflow: hidden;
  }

  .table-header {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
  }

  .table-icon {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.2);
  }

  .record-count {
    background: rgba(255, 255, 255, 0.1);
    padding: 6px 12px;
    border-radius: 16px;
  }

  .custom-table :deep(.v-data-table-header) {
    background-color: #f8f9fa;
  }

  .custom-table :deep(.v-data-table-header th) {
    font-weight: 600;
    color: #2c3e50;
    background-color: #f8f9fa !important;
    font-size: 0.875rem;
  }

  .custom-table :deep(.v-data-table__tr) {
    transition: background-color 0.2s;
    border-bottom: 1px solid #e0e0e0;
  }

  .custom-table :deep(.v-data-table__tr:hover) {
    background-color: #f8f9fa !important;
  }

  .custom-table :deep(.v-data-table__td) {
    border-bottom: 1px solid #e0e0e0;
    font-size: 0.875rem;
  }

  /* Buttons */
  .action-btn {
    border-radius: 8px;
    font-weight: 500;
    text-transform: none;
    letter-spacing: 0.5px;
    min-width: 140px;
    height: 44px;
  }

  .export-btn {
    border-radius: 8px;
    font-weight: 500;
    text-transform: none;
    min-width: 140px;
    height: 44px;
  }

  .detail-btn {
    border-radius: 6px;
    font-weight: 500;
    text-transform: none;
    min-width: 160px;
    height: 36px;
  }

  /* Details Dialog */
  .details-header {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
  }

  .dialog-icon {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
  }

  .details-table {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e0e0e0;
  }

  .details-table :deep(.table-row:hover) {
    background-color: #f8f9fa !important;
  }

  /* Responsive adjustments */
  @media (max-width: 960px) {
    .filter-card .v-card-text {
      padding: 16px;
    }

    .summary-card .v-card-text {
      padding: 16px;
    }

    .action-btn,
    .export-btn {
      min-width: 120px;
      height: 40px;
    }
  }
</style>
