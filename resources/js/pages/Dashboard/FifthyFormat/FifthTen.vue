<!-- src/views/dashboard/FifthyFormatFourDashboard.vue -->
<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- ============================ Filters ============================ -->
    <v-row class="mb-4" align="end">
      <v-col cols="12" md="3">
        <v-text-field
          v-model.trim="repo.Zone"
          :label="t('زون')"
          density="compact"
          variant="outlined"
          clearable
        />
      </v-col>

      <!-- FROM -->
      <v-col cols="12" md="3">
        <v-date-input
          v-model="fromModel"
          :label="t('له نېټې')"
          color="primary"
          density="compact"
          variant="outlined"
          clearable
          :first-day-of-week="6"
          @update:model-value="onFromChange"
        />
        <div class="text-caption text-medium-emphasis mt-1" v-if="repo.From">
          {{ t('نمایش') }}: {{ formatForCalendar(repo.From) }}
        </div>
      </v-col>

      <!-- TO -->
      <v-col cols="12" md="3">
        <v-date-input
          v-model="toModel"
          :label="t('تر نېټې')"
          color="primary"
          density="compact"
          variant="outlined"
          clearable
          :first-day-of-week="6"
          @update:model-value="onToChange"
        />
        <div class="text-caption text-medium-emphasis mt-1" v-if="repo.To">
          {{ t('نمایش') }}: {{ formatForCalendar(repo.To) }}
        </div>
      </v-col>

      <v-col cols="12" md="3">
        <v-select
          v-model="repo.granularity"
          :items="granOptions"
          :label="t('مهال وېش')"
          density="compact"
          variant="outlined"
        />
      </v-col>

      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('لټون')"
          prepend-inner-icon="mdi-magnify"
          density="compact"
          variant="outlined"
          clearable
          @keyup.enter="fetchAll"
        />
      </v-col>

      <v-col cols="12" md="3" class="text-md-right granularity">
        <v-btn
          variant="flat"
          color="primary"
          class="mr-2"
          :class="btnClass('daily')"
          @click="quickRange('daily')"
        >
          {{ t('ورځنی') }}
        </v-btn>
        <v-btn
          variant="flat"
          color="primary"
          class="mr-2"
          :class="btnClass('monthly')"
          @click="quickRange('monthly')"
        >
          {{ t('میاشتنی') }}
        </v-btn>
        <v-btn
          variant="flat"
          color="primary"
          :class="btnClass('yearly')"
          @click="quickRange('yearly')"
        >
          {{ t('کلنی') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- ============================ Cards ============================ -->
    <v-row>
      <v-col v-for="c in repo.cards" :key="c.key" cols="12" sm="6" md="3">
        <v-card class="pa-4 h-100 soft-card card-spotlight" elevation="1">
          <div class="text-overline text-primary">{{ c.label }}</div>
          <div class="text-h4 font-weight-bold mt-1">{{ c.total }}</div>
          <div class="text-caption text-medium-emphasis">{{ t('ټول ریکارډونه') }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- ============================ Main Charts (CHANGED/BEAUTIFIED) ============================ -->
    <v-row class="mt-2">
      <!-- STACKED AREA: pick up to 4 formats to compare trends -->
      <v-col cols="12" md="8">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-3">
            <div>
              <div class="text-subtitle-1 font-weight-medium">
                {{ t('د بڼو د رجحان ملټي-سریز (Stacked Area)') }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ t('مهال وېش') }}: {{ granularityLabel }}
              </div>
            </div>
            <v-select
              v-model="selectedAreaFormats"
              :items="formatOptions"
              item-title="label"
              item-value="key"
              :label="t('فورمټونه (تر ۴)')"
              multiple
              chips
              closable-chips
              density="compact"
              variant="outlined"
              style="max-width: 420px"
            />
          </div>
          <v-chart :option="stackedAreaOption" autoresize style="height: 380px" />
        </v-card>
      </v-col>

      <!-- ROSE DONUT (漂亮): composition by format -->
      <v-col cols="12" md="4">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="text-subtitle-1 font-weight-medium mb-2">
            {{ t('ترکیب د بڼو له مخې (Rose)') }}
          </div>
          <div class="relative">
            <v-chart :option="roseDonutOption" autoresize style="height: 380px" />
            <div class="donut-center" v-if="donutTotal > 0">
              <div class="donut-total">{{ donutTotal }}</div>
              <div class="text-caption text-medium-emphasis">{{ t('ټول') }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- ============================ Secondary Charts ============================ -->
    <v-row class="mt-2">
      <!-- Top Zones (gradient bar) -->
      <v-col cols="12" md="6">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-1">
            <div class="text-subtitle-1 font-weight-medium">{{ t('ټاپ زونونه') }}</div>
            <v-select
              v-model="selectedZonesFormat"
              :items="formatOptions"
              item-title="label"
              item-value="key"
              :label="t('فورمټ')"
              density="compact"
              variant="outlined"
              clearable="false"
              style="max-width: 260px"
            />
          </div>
          <v-chart :option="zonesBarOption" autoresize style="height: 320px" />
        </v-card>
      </v-col>

      <!-- Top Provinces (gradient bar) -->
      <v-col cols="12" md="6">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-1">
            <div class="text-subtitle-1 font-weight-medium">{{ t('ټاپ ولایتونه') }}</div>
            <v-select
              v-model="selectedProvFormat"
              :items="formatOptions"
              item-title="label"
              item-value="key"
              :label="t('فورمټ')"
              density="compact"
              variant="outlined"
              clearable="false"
              style="max-width: 260px"
            />
          </div>
          <v-chart :option="provincesBarOption" autoresize style="height: 320px" />
        </v-card>
      </v-col>
    </v-row>

    <!-- ============================ Breakdown & Top Resources ============================ -->
    <v-row class="mt-2">
      <!-- Treemap Breakdown -->
      <v-col cols="12" md="6">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-1">
            <div class="text-subtitle-1 font-weight-medium">{{ t('کټګوري وېش (Treemap)') }}</div>
            <div class="d-flex align-center" style="gap: 8px">
              <v-select
                v-model="repo.selectedFormat"
                :items="formatOptions"
                item-title="label"
                item-value="key"
                :label="t('فورمټ')"
                density="compact"
                variant="outlined"
                clearable="false"
                style="max-width: 220px"
                @update:model-value="onBreakdownFormatChange"
              />
              <v-select
                v-model="repo.selectedField"
                :items="fieldOptions"
                :label="t('ډول/فیلډ')"
                density="compact"
                variant="outlined"
                :disabled="!repo.selectedFormat"
                clearable="false"
                style="max-width: 220px"
                @update:model-value="repo.fetchBreakdown"
              />
            </div>
          </div>
          <v-chart :option="breakdownTreeOption" autoresize style="height: 320px" />
        </v-card>
      </v-col>

      <!-- Top resources (lollipop bar) -->
      <v-col cols="12" md="6">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-1">
            <div class="text-subtitle-1 font-weight-medium">
              {{ t('لومړي سرچينې (Top resources)') }}
            </div>
            <v-select
              v-model="selectedBucketFormat"
              :items="formatOptions"
              item-title="label"
              item-value="key"
              :label="t('فورمټ فلټر')"
              density="compact"
              variant="outlined"
              clearable="false"
              style="max-width: 260px"
            />
          </div>
          <v-chart :option="resourcesLollipopOption" autoresize style="height: 320px" />
        </v-card>
      </v-col>
    </v-row>

    <!-- ============================ Recent ============================ -->
    <v-row class="mt-2">
      <v-col cols="12">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="text-subtitle-1 font-weight-medium mb-2">{{ t('وروستي ریکارډونه') }}</div>
          <v-data-table
            :headers="recentHeaders"
            :items="repo.recent"
            density="compact"
            class="elevation-0"
            :items-per-page="8"
            hide-default-footer
          >
            <template #item.created_at="{ item }">
              <span>{{ formatForCalendar(item.created_at) }}</span>
            </template>
            <template #item.extra="{ item }">
              <span v-if="item.extra?.organ">{{ t('Organ') }}: {{ item.extra.organ }}</span>
              <span v-if="item.extra?.district"> | {{ t('ولسوالي') }}: {{ item.extra.district }}</span>
              <span v-if="item.extra?.village"> | {{ t('کلی') }}: {{ item.extra.village }}</span>
              <span v-if="item.extra?.type"> | {{ t('ډول') }}: {{ item.extra.type }}</span>
            </template>
            <template #bottom></template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <v-alert v-if="repo.error" type="error" variant="tonal" class="mt-3">
      {{ repo.error }}
    </v-alert>
  </v-container>
</template>

<script setup>
  /* ECharts modules */
  import { use } from 'echarts/core';
  import { CanvasRenderer } from 'echarts/renderers';
  import { BarChart, LineChart, PieChart, ScatterChart, TreemapChart } from 'echarts/charts';
  import {
    GridComponent,
    TooltipComponent,
    LegendComponent,
    DatasetComponent,
    DataZoomComponent,
    VisualMapComponent,
  } from 'echarts/components';
  use([
    CanvasRenderer,
    BarChart,
    LineChart,
    PieChart,
    ScatterChart,
    TreemapChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    DatasetComponent,
    DataZoomComponent,
    VisualMapComponent,
  ]);

  import VChart from 'vue-echarts';
  import { ref, computed, onMounted, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useFifthyFormatFourDashboardRepository } from '@/stores/FifthyFormatFourDashboardRepository';
  import { useCalendar } from '../../../../composables/useCalendar';

  const repo = useFifthyFormatFourDashboardRepository();
  const { t, locale } = useI18n();
  const { calendar } = useCalendar?.() || { calendar: ref('gregory') };

  /* RTL */
  const isRtl = ref(locale.value === 'fa' || locale.value === 'ps');
  watch(locale, (l) => (isRtl.value = l === 'fa' || l === 'ps'));

  /* Granularity */
  const granOptions = [
    { title: t('ورځنی'), value: 'daily' },
    { title: t('میاشتنی'), value: 'monthly' },
    { title: t('کلنی'), value: 'yearly' },
  ];
  const granularityLabel = computed(
    () =>
      ({ daily: t('ورځنی'), monthly: t('میاشتنی'), yearly: t('کلنی') }[repo.granularity] ||
        t('ورځنی'))
  );

  /* Formats list (FF4 keys) */
  const formatOptions = [
    { key: 'checkpoint', label: t('چیک پوینټ') },
    { key: 'company', label: t('کمپنۍ') },
    { key: 'complex', label: t('کمپلکس') },
    { key: 'criminal_capture_place', label: t('د جرم نیولو ځای') },
    { key: 'deplomatic_place', label: t('دیپلوماتیک ځای') },
    { key: 'donelostplace', label: t('موندل/ورک شوي ځایونه') },
    { key: 'econemy_project', label: t('اقتصادي پروژې') },
    { key: 'government_place', label: t('حکومتي ځای') },
    { key: 'highway', label: t('سرک/شاهراه') },
    { key: 'masjid', label: t('مسجد') },
    { key: 'park', label: t('پارک') },
    { key: 'pump', label: t('پمپ') },
    { key: 'shora', label: t('شورا') },
    { key: 'super_market', label: t('سوپرمارکېټ') },
    { key: 'university', label: t('پوهنځی/پوهنتون') },
    { key: 'wakilano', label: t('وکيلانو') },
  ];

  /* Defaults */
  const defaultFormatKey = formatOptions[0].key;

  // Multi-select for stacked area
  const selectedAreaFormats = ref([defaultFormatKey, formatOptions[1]?.key].filter(Boolean));

  const selectedZonesFormat = ref(defaultFormatKey);
  const selectedProvFormat = ref(defaultFormatKey);
  const selectedBucketFormat = ref(defaultFormatKey);

  /* Breakdown fields per FF4 */
  const formatFieldMap = {
    checkpoint: ['organ'],
    company: ['product_type'],
    complex: ['district'],
    criminal_capture_place: ['office'],
    deplomatic_place: ['district'],
    donelostplace: [],
    econemy_project: ['district'],
    government_place: [],
    highway: ['district'],
    masjid: [],
    park: [],
    pump: [],
    shora: [],
    super_market: [],
    university: [],
    wakilano: [],
  };
  const fieldOptions = computed(() => {
    const k = repo.selectedFormat;
    const arr = k ? formatFieldMap[k] || [] : [];
    return arr.map((v) => ({ title: v, value: v }));
  });
  function ensureBreakdownDefaults() {
    if (!repo.selectedFormat) repo.selectedFormat = defaultFormatKey;
    if (!repo.selectedField) {
      const first = fieldOptions.value?.[0]?.value;
      if (first) repo.selectedField = first;
    }
  }

  /* Date helpers (ISO <-> Date) */
  const toISO = (d) => {
    if (!d) return '';
    const dd = d instanceof Date ? d : new Date(d);
    if (Number.isNaN(dd.getTime())) return '';
    const y = dd.getFullYear(),
      m = String(dd.getMonth() + 1).padStart(2, '0'),
      day = String(dd.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  };
  const fromISO = (s) => {
    if (!s) return null;
    const dd = new Date(`${s}T00:00:00`);
    return Number.isNaN(dd.getTime()) ? null : dd;
  };

  /* Calendar-aware label */
  function fmtIntl(date, granularity = 'daily') {
    let ca = 'gregory';
    if (calendar?.value === 'jalali') ca = 'persian';
    else if (calendar?.value === 'hijri') ca = 'islamic-umalqura';
    const loc =
      (locale.value === 'fa' || locale.value === 'ps' ? 'fa-AF' : 'en-US') + `-u-ca-${ca}`;
    const optsDaily = { year: 'numeric', month: 'short', day: '2-digit' };
    const optsMonthly = { year: 'numeric', month: 'short' };
    const optsYearly = { year: 'numeric' };
    const fmt = new Intl.DateTimeFormat(
      loc,
      granularity === 'yearly' ? optsYearly : granularity === 'monthly' ? optsMonthly : optsDaily
    );
    return fmt.format(date);
  }
  function parseFlexibleToDate(s) {
    if (!s) return null;
    if (s.length === 4) return new Date(`${s}-01-01T00:00:00`);
    if (s.length === 7) return new Date(`${s}-01T00:00:00`);
    return new Date(`${s}T00:00:00`);
  }
  function formatForCalendar(isoLike) {
    const d = parseFlexibleToDate(String(isoLike || ''));
    if (!d || Number.isNaN(d.getTime())) return '';
    return fmtIntl(d, repo.granularity);
  }

  /* v-date-input models */
  const fromModel = ref(fromISO(repo.From));
  const toModel = ref(fromISO(repo.To));
  function onFromChange(val) {
    repo.From = toISO(val);
    if (repo.To && repo.From && repo.To < repo.From) {
      toModel.value = fromISO(repo.From);
      repo.To = repo.From;
    }
    fetchAll();
  }
  function onToChange(val) {
    repo.To = toISO(val);
    if (repo.To && repo.From && repo.To < repo.From) {
      fromModel.value = fromISO(repo.To);
      repo.From = repo.To;
    }
    fetchAll();
  }
  watch(
    () => repo.From,
    (v) => {
      const cur = toISO(fromModel.value);
      if ((v || '') !== (cur || '')) fromModel.value = fromISO(v);
    }
  );
  watch(
    () => repo.To,
    (v) => {
      const cur = toISO(toModel.value);
      if ((v || '') !== (cur || '')) toModel.value = fromISO(v);
    }
  );

  /* Quick ranges */
  function endOfTodayISO() {
    const d = new Date();
    d.setHours(23, 59, 59, 999);
    return toISO(d);
  }
  function startNDaysAgoISO(n) {
    const d = new Date();
    d.setDate(d.getDate() - n);
    d.setHours(0, 0, 0, 0);
    return toISO(d);
  }
  function startNMonthsAgoISO(n) {
    const d = new Date();
    d.setMonth(d.getMonth() - n, 1);
    d.setHours(0, 0, 0, 0);
    return toISO(d);
  }
  function startNYearsAgoISO(n) {
    const d = new Date();
    d.setFullYear(d.getFullYear() - n, 0, 1);
    d.setHours(0, 0, 0, 0);
    return toISO(d);
  }
  function quickRange(g) {
    repo.setGranularity(g);
    if (g === 'daily') repo.setRange({ from: startNDaysAgoISO(30), to: endOfTodayISO() });
    else if (g === 'monthly') repo.setRange({ from: startNMonthsAgoISO(6), to: endOfTodayISO() });
    else repo.setRange({ from: startNYearsAgoISO(5), to: endOfTodayISO() });
    fetchAll();
  }
  function btnClass(g) {
    return repo.granularity === g ? 'elevated' : '';
  }

  /* Fetch */
  function fetchAll() {
    repo.fetchCombined().then(() => {
      // defaults after data arrives
      if (!selectedAreaFormats.value?.length) {
        selectedAreaFormats.value = [defaultFormatKey, formatOptions[1]?.key].filter(Boolean);
      }
      if (!selectedZonesFormat.value) selectedZonesFormat.value = defaultFormatKey;
      if (!selectedProvFormat.value) selectedProvFormat.value = defaultFormatKey;
      if (!selectedBucketFormat.value) selectedBucketFormat.value = defaultFormatKey;
      ensureBreakdownDefaults();
      repo.fetchBreakdown();
    });
  }
  function onBreakdownFormatChange() {
    repo.selectedField = null;
    ensureBreakdownDefaults();
    repo.fetchBreakdown();
  }
  onMounted(() => {
    // initial defaults
    repo.selectedFormat = defaultFormatKey;
    ensureBreakdownDefaults();
    if (!repo.From && !repo.To) quickRange('daily');
    else fetchAll();
  });

  /* ============================ Chart Mappers ============================ */
  const palette = [
    '#2563eb',
    '#16a34a',
    '#9333ea',
    '#ef4444',
    '#f59e0b',
    '#0ea5e9',
    '#14b8a6',
    '#f43f5e',
    '#8b5cf6',
    '#22c55e',
  ];

  // series helpers
  function seriesForKey(key) {
    const s = (repo.series || []).find((x) => x.key === key);
    return s ? s.points || [] : [];
  }
  const allCategories = computed(() => {
    const set = new Set();
    (repo.series || []).forEach((s) => (s.points || []).forEach((p) => set.add(String(p.date))));
    return Array.from(set).sort();
  });

  /* STACKED AREA (multi-select) */
  const stackedAreaOption = computed(() => {
    const cats = allCategories.value;
    const sel = (selectedAreaFormats.value || []).slice(0, 4);
    const series = sel.map((key, idx) => {
      const pts = seriesForKey(key);
      const map = Object.create(null);
      pts.forEach((p) => (map[String(p.date)] = Number(p.count || 0)));
      return {
        name: formatOptions.find((f) => f.key === key)?.label || key,
        type: 'line',
        stack: 'total',
        smooth: true,
        symbol: 'circle',
        symbolSize: 4,
        lineStyle: { width: 2 },
        areaStyle: { opacity: 0.18 },
        emphasis: { focus: 'series' },
        data: cats.map((d) => map[d] ?? 0),
        color: palette[idx % palette.length],
      };
    });

    return {
      tooltip: {
        trigger: 'axis',
        axisPointer: { type: 'line' },
        formatter: (params) => {
          if (!params?.length) return '';
          const x = params[0].axisValue;
          const title = formatForCalendar(x);
          const lines = params
            .map((p) => `${p.marker} ${p.seriesName}: <b>${(p.value ?? 0) | 0}</b>`)
            .join('<br/>');
          return `<div style="margin-bottom:4px"><b>${title}</b></div>${lines}`;
        },
      },
      legend: { top: 0, type: 'scroll' },
      grid: { left: 12, right: 12, top: 36, bottom: 50, containLabel: true },
      dataZoom: [
        { type: 'slider', height: 18, bottom: 10, brushSelect: false },
        { type: 'inside' },
      ],
      xAxis: {
        type: 'category',
        data: cats,
        boundaryGap: false,
        axisTick: { show: false },
        axisLabel: { formatter: (val) => formatForCalendar(val) },
      },
      yAxis: { type: 'value', min: 0, axisTick: { show: false } },
      series,
    };
  });

  /* Rose Donut */
  const donutLabels = computed(() => (repo.cards || []).map((c) => c?.label || t('بې‌نوم')));
  const donutSeries = computed(() =>
    (repo.cards || []).map((c) => Number(c?.total ?? 0)).filter((v) => Number.isFinite(v) && v >= 0)
  );
  const donutTotal = computed(() => (donutSeries.value || []).reduce((a, b) => a + b, 0));
  const roseDonutOption = computed(() => ({
    color: palette,
    tooltip: { trigger: 'item', valueFormatter: (v) => `${v}` },
    legend: { bottom: 0, type: 'scroll' },
    series: [
      {
        name: t('ترکیب'),
        type: 'pie',
        roseType: 'area',
        radius: ['50%', '80%'],
        itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
        label: { show: true, formatter: '{b}\n{d}%' },
        data: donutLabels.value.map((name, i) => ({ name, value: donutSeries.value[i] || 0 })),
      },
    ],
  }));

  /* Zones & Provinces (gradient bars) */
  const zonesItemsFlat = computed(() => {
    const list = repo.zones || [];
    if (!list.length) return [];
    const pick = list.find((z) => z.key === selectedZonesFormat.value) || list[0];
    return pick?.items || [];
  });
  const provincesItemsFlat = computed(() => {
    const list = repo.provinces || [];
    if (!list.length) return [];
    const pick = list.find((p) => p.key === selectedProvFormat.value) || list[0];
    return pick?.items || [];
  });
  const zonesBarOption = computed(() => ({
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    grid: { left: 12, right: 12, top: 10, bottom: 10, containLabel: true },
    xAxis: { type: 'value', min: 0, axisTick: { show: false } },
    yAxis: {
      type: 'category',
      axisTick: { show: false },
      data: zonesItemsFlat.value.map((i) => i.zone || t('بې‌زون')),
    },
    series: [
      {
        type: 'bar',
        data: zonesItemsFlat.value.map((i) => i.count || 0),
        barWidth: '45%',
        itemStyle: {
          borderRadius: [0, 10, 10, 0],
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 1,
            y2: 0,
            colorStops: [
              { offset: 0, color: '#34d399' },
              { offset: 1, color: '#10b981' },
            ],
          },
        },
        label: { show: true, position: 'right' },
      },
    ],
  }));
  const provincesBarOption = computed(() => ({
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    grid: { left: 12, right: 12, top: 10, bottom: 10, containLabel: true },
    xAxis: { type: 'value', min: 0, axisTick: { show: false } },
    yAxis: {
      type: 'category',
      axisTick: { show: false },
      data: provincesItemsFlat.value.map((i) => i.province || t('بې‌ولایت')),
    },
    series: [
      {
        type: 'bar',
        data: provincesItemsFlat.value.map((i) => i.count || 0),
        barWidth: '45%',
        itemStyle: {
          borderRadius: [0, 10, 10, 0],
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 1,
            y2: 0,
            colorStops: [
              { offset: 0, color: '#fb923c' },
              { offset: 1, color: '#f59e0b' },
            ],
          },
        },
        label: { show: true, position: 'right' },
      },
    ],
  }));

  /* Treemap Breakdown */
  const breakdownTreeOption = computed(() => {
    const items = repo.breakdownItems || [];
    const nodes = items.map((it) => ({
      name: String(it.value ?? t('نامعلوم')),
      value: Number(it.count || 0),
    }));
    return {
      tooltip: { formatter: (p) => `${p.name}: <b>${p.value | 0}</b>` },
      series: [
        {
          type: 'treemap',
          roam: false,
          breadcrumb: { show: false },
          nodeClick: false,
          label: { show: true, formatter: '{b}\n{c}' },
          upperLabel: { show: true },
          itemStyle: { borderColor: '#fff', borderWidth: 2, gapWidth: 2 },
          levels: [
            {
              itemStyle: {
                borderColor: '#fff',
                borderWidth: 2,
                gapWidth: 2,
              },
            },
          ],
          data: nodes,
        },
      ],
    };
  });

  /* Top resources — Lollipop (bar + scatter) */
  const resourcesBucket = computed(() => {
    const b = repo.buckets || [];
    if (!b.length) return null;
    return b.find((x) => x.key === selectedBucketFormat.value) || b[0];
  });
  const resCats = computed(() =>
    (resourcesBucket.value?.items || []).map(
      (i) => `${i.code_number || '#'} — ${i.name || t('بې‌نوم')}`
    )
  );
  const resVals = computed(() =>
    (resourcesBucket.value?.items || []).map((i) => Number(i.count || 0))
  );
  const resourcesLollipopOption = computed(() => {
    const cats = resCats.value;
    const vals = resVals.value;
    return {
      grid: { left: 12, right: 12, top: 10, bottom: 10, containLabel: true },
      tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
      xAxis: {
        type: 'category',
        data: cats,
        axisTick: { show: false },
        axisLabel: { interval: 0, overflow: 'truncate' },
      },
      yAxis: { type: 'value', min: 0, axisTick: { show: false } },
      series: [
        {
          type: 'bar',
          data: vals,
          barWidth: '40%',
          itemStyle: { borderRadius: [10, 10, 0, 0], color: '#2563eb' },
          z: 1,
        },
        {
          type: 'scatter',
          data: vals.map((v, i) => [cats[i], v]),
          symbolSize: 10,
          itemStyle: { color: '#60a5fa', borderColor: '#fff', borderWidth: 2 },
          z: 2,
        },
      ],
    };
  });

  /* Recent headers */
  const recentHeaders = [
    { key: 'label', title: t('فورمټ'), width: 140 },
    { key: 'title', title: t('عنوان/نوم') },
    { key: 'zone', title: t('زون'), width: 120 },
    { key: 'province', title: t('ولایت'), width: 120 },
    { key: 'extra', title: t('نور'), width: 300 },
    { key: 'created_at', title: t('نېټه'), width: 160 },
  ];
</script>

<script>
  /* Lazy-register ECharts component */
  export default { components: { 'v-chart': (await import('vue-echarts')).default } };
</script>

<style scoped>
  .h-100 { height: 100%; }

  .soft-card {
    border-radius: 18px;
    background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.98));
    box-shadow: 0 6px 24px rgba(16,24,40,.06);
    backdrop-filter: saturate(1.05) blur(2px);
  }

  .card-spotlight {
    position: relative;
    overflow: hidden;
  }
  .card-spotlight::after {
    content: '';
    position: absolute;
    inset: -40%;
    background: conic-gradient(from 180deg at 50% 50%, rgba(37,99,235,.08), rgba(16,185,129,.08), rgba(245,158,11,.08), rgba(37,99,235,.08));
    filter: blur(22px);
    z-index: 0;
  }
  .card-spotlight > * { position: relative; z-index: 1; }

  .granularity .v-btn.elevated {
    box-shadow: 0 6px 20px rgba(0,0,0,.12);
  }

  .relative { position: relative; }
  .donut-center {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    pointer-events: none;
  }
  .donut-total {
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
  }
</style>
