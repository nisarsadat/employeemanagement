<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('د پنځه-دو بڼو ډشبورډ (fifthyformatstow)')"
      :subtitle="t('یو مخ — کارتونه، ګرافونه، جدول')"
      @toggle-drawer="() => {}"
    />

    <!-- Filters -->
    <v-row class="mb-4" align="end">
      <v-col cols="12" md="3">
        <v-select
          v-model="repo.formatKey"
          :items="formatOptions"
          item-title="label"
          item-value="key"
          :label="t('بڼه/فورمټ')"
          density="compact"
          variant="outlined"
          clearable
          @update:model-value="fetchTop"
        />
      </v-col>

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

      <!-- Granularity + All-time -->
      <v-col cols="12" md="3" class="text-md-right granularity">
        <!-- <v-btn
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
        </v-btn> -->

        <v-btn variant="tonal" class="ml-2" @click="clearRange">
          {{ t('ټولې مودې') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Cards -->
    <v-row>
      <v-col v-for="c in repo.cards" :key="c.key" cols="12" sm="6" md="3">
        <v-card class="pa-4 h-100 soft-card" elevation="1">
          <div class="text-overline text-primary">{{ c.label }}</div>
          <div class="text-h5 font-weight-bold mt-1">{{ c.total }}</div>
          <div class="text-caption text-medium-emphasis">{{ t('ټول ریکارډونه') }}</div>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mt-2">
      <!-- LEFT: Stacked COLUMN + MA line -->
      <v-col cols="12" md="8">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-2">
            <div class="text-subtitle-1 font-weight-medium">{{ t('وختي لړۍ (ستونونه + MA)') }}</div>
            <v-chip density="compact" variant="flat">{{ granularityLabel }}</v-chip>
          </div>
          <v-chart :option="columnOption" autoresize style="height: 360px" />
        </v-card>
      </v-col>

      <!-- RIGHT: Treemap composition -->
      <v-col cols="12" md="4">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="text-subtitle-1 font-weight-medium mb-2">
            {{ t('ترکیب د بڼو له مخې (Treemap)') }}
          </div>
          <v-chart :option="treemapOption" autoresize style="height: 360px" />
        </v-card>
      </v-col>
    </v-row>

    <!-- Bottom row: Top resources + recent table -->
    <v-row class="mt-2">
      <v-col cols="12" md="7">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-2">
            <div class="text-subtitle-1 font-weight-medium">{{ t('لومړي سرچينې') }}</div>
            <v-select
              v-model="repo.formatKey"
              :items="formatOptions"
              item-title="label"
              item-value="key"
              :label="t('فورمټ فلټر')"
              class="mt-1"
              density="compact"
              variant="outlined"
              clearable
              style="max-width: 260px"
              @update:model-value="fetchTop"
            />
          </div>
        </v-card>
        <v-card class="pa-3 soft-card mt-2" elevation="1">
          <v-chart :option="hbarOption" autoresize style="height: 360px" />
        </v-card>
      </v-col>

      <v-col cols="12" md="5">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="text-subtitle-1 font-weight-medium mb-2">{{ t('وروستي ریکارډونه') }}</div>
          <v-data-table
            :headers="recentHeaders"
            :items="repo.recent"
            density="compact"
            class="elevation-0"
            :items-per-page="6"
            hide-default-footer
          >
            <template #item.created_at="{ item }">
              <span>{{ formatForCalendar(item.created_at) }}</span>
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
  /* ECharts */
  import { use } from 'echarts/core';
  import { CanvasRenderer } from 'echarts/renderers';
  import { BarChart, LineChart, TreemapChart } from 'echarts/charts';
  import {
    GridComponent,
    TooltipComponent,
    LegendComponent,
    DataZoomComponent,
  } from 'echarts/components';
  use([
    CanvasRenderer,
    BarChart,
    LineChart,
    TreemapChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    DataZoomComponent,
  ]);

  import VChart from 'vue-echarts';
  import { ref, computed, onMounted, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useFifthyFormatsTwoDashboardRepository } from '@/stores/FifthyFormatsTwoDashboardRepository';
  import { useCalendar } from '../../../../composables/useCalendar';

  const repo = useFifthyFormatsTwoDashboardRepository();
  const { t, locale } = useI18n();
  const { calendar } = useCalendar();

  /* RTL */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => {
    isRtl.value = l === 'fa';
  });

  /* Formats (match backend keys) */
  const formatOptions = [
    { key: 'apartment', label: t('اپارتمانونه') },
    { key: 'bank', label: t('بانکونه') },
    { key: 'car_sales_company', label: t('د موټرو د پلور شرکتونه') },
    { key: 'hado_bus_stop', label: t('هادو بس تمځایونه') },
    { key: 'hosppital', label: t('روغتونونه') },
    { key: 'parking', label: t('پارکينګونه') },
    { key: 'rahnoma_mamlat', label: t('رهنما معاملات') },
    { key: 'school', label: t('ښوونځي') },
    { key: 'transport_company', label: t('ترانسپورتي شرکتونه') },
    { key: 'workshop', label: t('ورکشاپونه') },
  ];

  /* ===== Date helpers (ISO <-> Date; calendar formatting) ===== */
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

  function fmtIntl(date, granularity = 'daily') {
    let ca = 'gregory';
    if (calendar.value === 'jalali') ca = 'persian';
    else if (calendar.value === 'hijri') ca = 'islamic-umalqura';
    const loc = (locale.value === 'fa' ? 'fa-AF' : 'en-US') + `-u-ca-${ca}`;
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
  function clearRange() {
    repo.setRange({ from: undefined, to: undefined });
    fromModel.value = null;
    toModel.value = null;
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

  const granularityLabel = computed(
    () =>
      ({ daily: t('ورځنی'), monthly: t('میاشتنی'), yearly: t('کلنی') }[repo.granularity] ||
      t('ورځنی'))
  );

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
    d.setMonth(d.getMonth() - n);
    d.setDate(1);
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

  function fetchAll() {
    repo.fetchAll();
  }
  function fetchTop() {
    repo.fetchTopResources(10);
  }
  function btnClass(g) {
    return repo.granularity === g ? 'elevated' : '';
  }

  onMounted(() => {
    // Keep your previous default: last 30 days on first load.
    if (!repo.From && !repo.To) quickRange('daily');
    else fetchAll();
  });

  /* ===================== Chart data mapping ===================== */
  const categories = computed(() => {
    const set = new Set();
    (repo.series || []).forEach((s) => (s.points || []).forEach((p) => set.add(String(p.date))));
    return Array.from(set).sort();
  });
  const stackedSeriesData = computed(() => {
    const cats = categories.value;
    if (!cats.length) return [];
    return (repo.series || []).map((s) => {
      const map = Object.create(null);
      (s.points || []).forEach((p) => {
        map[String(p.date)] = Number(p.count || 0);
      });
      return { name: s.label, data: cats.map((d) => map[d] ?? 0) };
    });
  });

  /* Moving average (7) across total */
  const movingAverage = computed(() => {
    const cats = categories.value;
    if (!cats.length) return [];
    const totals = cats.map((_, idx) =>
      stackedSeriesData.value.reduce((acc, s) => acc + (Number(s.data[idx]) || 0), 0)
    );
    const w = 7;
    const ma = [];
    for (let i = 0; i < totals.length; i++) {
      const start = Math.max(0, i - w + 1);
      let sum = 0,
        cnt = 0;
      for (let j = start; j <= i; j++) {
        sum += totals[j];
        cnt++;
      }
      ma.push(cnt ? +(sum / cnt).toFixed(2) : 0);
    }
    return ma;
  });

  /* Palette */
  const palette = [
    '#3F8CFF',
    '#00B894',
    '#A66DD4',
    '#FF7675',
    '#FDCB6E',
    '#6C5CE7',
    '#00CEC9',
    '#0984E3',
    '#E84393',
    '#55EFC4',
  ];

  /* ===== Stacked COLUMN + MA line ===== */
  const columnOption = computed(() => {
    const cats = categories.value;
    const stacks = stackedSeriesData.value.map((s) => ({
      name: s.name,
      type: 'bar',
      stack: 'total',
      emphasis: { focus: 'series' },
      data: s.data,
      barMaxWidth: 26,
      itemStyle: { borderRadius: [6, 6, 0, 0] },
    }));
    const maLine = {
      name: t('MA(7)'),
      type: 'line',
      symbol: 'circle',
      symbolSize: 4,
      lineStyle: { width: 2.5 },
      data: movingAverage.value,
      yAxisIndex: 0,
      smooth: true,
    };
    return {
      color: palette,
      tooltip: {
        trigger: 'axis',
        axisPointer: { type: 'shadow' },
        formatter: (params) => {
          if (!params?.length) return '';
          const x = params[0].axisValue;
          const title = formatForCalendar(x);
          const lines = params.map((p) => `${p.marker} ${p.seriesName}: <b>${p.value}</b>`);
          return `<div style="margin-bottom:4px"><b>${title}</b></div>${lines.join('<br/>')}`;
        },
      },
      legend: { top: 0, right: isRtl.value ? 'auto' : 0, left: isRtl.value ? 0 : 'auto' },
      grid: { left: 12, right: 12, top: 36, bottom: 24, containLabel: true },
      xAxis: {
        type: 'category',
        data: cats,
        axisTick: { show: false },
        axisLabel: { formatter: (val) => formatForCalendar(val) },
      },
      yAxis: { type: 'value', min: 0, axisTick: { show: false } },
      series: [...stacks, maLine],
    };
  });

  /* ===== Treemap (cards composition) ===== */
  const treemapData = computed(() =>
    (repo.cards || []).map((c) => ({
      name: c.label || t('بې‌نوم'),
      value: Number(c.total || 0),
    }))
  );
  const treemapOption = computed(() => ({
    color: palette,
    tooltip: { trigger: 'item', formatter: ({ name, value }) => `${name}: <b>${value}</b>` },
    series: [
      {
        type: 'treemap',
        roam: false,
        breadcrumb: { show: false },
        nodeClick: false,
        label: { show: true, formatter: '{b}\n{c}', fontWeight: 600 },
        upperLabel: { show: false },
        itemStyle: { borderColor: '#fff', borderWidth: 2, gapWidth: 2 },
        data: treemapData.value.length ? treemapData.value : [{ name: t('ډاټا نشته'), value: 1 }],
      },
    ],
  }));

  /* ===== Horizontal bar with zoom (Top Resources) ===== */
  const barBucket = computed(() => {
    const b = repo.buckets || [];
    if (!b.length) return null;
    return repo.formatKey ? b.find((x) => x.key === repo.formatKey) || b[0] : b[0];
  });
  const hbarCats = computed(() =>
    (barBucket.value?.items || []).map((i) => `${i.code_number || '#'} — ${i.name || t('بې‌نوم')}`)
  );
  const hbarVals = computed(() => (barBucket.value?.items || []).map((i) => Number(i.count || 0)));

  const hbarOption = computed(() => ({
    color: ['#3F8CFF'],
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    grid: { left: 12, right: 12, top: 10, bottom: 40, containLabel: true },
    dataZoom: [
      { type: 'slider', orient: 'horizontal', bottom: 6, height: 18, filterMode: 'weakFilter' },
      { type: 'inside' },
    ],
    xAxis: { type: 'value', min: 0, axisTick: { show: false } },
    yAxis: { type: 'category', data: hbarCats.value, axisTick: { show: false }, inverse: true },
    series: [
      {
        type: 'bar',
        data: hbarVals.value,
        barWidth: '45%',
        itemStyle: { borderRadius: [0, 8, 8, 0] },
        label: { show: true, position: 'right' },
      },
    ],
  }));

  /* Table headers */
  const recentHeaders = [
    { key: 'type', title: t('ډول'), width: 160 },
    { key: 'name', title: t('نوم') },
    { key: 'zone', title: t('زون'), width: 140 },
    { key: 'created_at', title: t('نېټه'), width: 160 },
  ];

  /* Calendar re-render (no refetch needed) */
  watch(calendar, () => {});
</script>

<script>
  export default { components: { 'v-chart': (await import('vue-echarts')).default } };
</script>

<style scoped>
  .h-100 {
    height: 100%;
  }
  .soft-card {
    border-radius: 16px;
    backdrop-filter: saturate(1.08);
  }
  .granularity .v-btn.elevated {
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
  }
</style>
