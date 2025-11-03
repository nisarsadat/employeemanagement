<!-- src/views/dashboard/FifthyFormatOneDashboard.vue -->
<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('د پنځه-یو بڼو ډشبورډ (fifthyformatone)')"
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

      <!-- FROM calendar (keeps ISO internally, shows current calendar as hint) -->
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

      <!-- TO calendar (keeps ISO internally, shows current calendar as hint) -->
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

      <!-- Granularity -->
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
      <!-- LEFT: Smooth stacked AREA (ECharts; axis uses calendar formatter) -->
      <v-col cols="12" md="8">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="d-flex align-center justify-space-between mb-2">
            <div class="text-subtitle-1 font-weight-medium">{{ t('وختي لړۍ') }}</div>
            <v-chip density="compact" variant="flat">{{ granularityLabel }}</v-chip>
          </div>
          <v-chart :option="areaOption" autoresize style="height: 360px" />
        </v-card>
      </v-col>

      <!-- RIGHT: Donut with center total -->
      <v-col cols="12" md="4">
        <v-card class="pa-3 soft-card" elevation="1">
          <div class="text-subtitle-1 font-weight-medium mb-2">
            {{ t('ترکیب د بڼو له مخې') }}
          </div>
          <div class="relative">
            <v-chart :option="donutOption" autoresize style="height: 360px" />
            <div class="donut-center" v-if="donutTotal > 0">
              <div class="donut-total">{{ donutTotal }}</div>
              <div class="text-caption text-medium-emphasis">{{ t('ټول') }}</div>
            </div>
          </div>
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
          <v-chart :option="barOption" autoresize style="height: 360px" />
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
  /* ECharts (tree-shaken) */
  import { use } from 'echarts/core';
  import { CanvasRenderer } from 'echarts/renderers';
  import { BarChart, LineChart, PieChart } from 'echarts/charts';
  import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components';
  use([
    CanvasRenderer,
    BarChart,
    LineChart,
    PieChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
  ]);

  import VChart from 'vue-echarts';
  import { ref, computed, onMounted, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useFifthyFormatOneDashboardRepository } from '@/stores/FifthyFormatOneDashboardRepository';
  import { useCalendar } from '../../../../composables/useCalendar'; // ← your composable

  const repo = useFifthyFormatOneDashboardRepository();
  const { t, locale } = useI18n();
  const { calendar } = useCalendar(); // ← bind to AppBar switch

  /* RTL */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => {
    isRtl.value = l === 'fa';
  });

  /* Formats (match backend keys) */
  const formatOptions = [
    { key: 'damage_places', label: t('د تاوان ځایونه') },
    { key: 'damage_rasan_places', label: t('د تاوان رسېدو ځایونه') },
    { key: 'lost_mujremeen', label: t('ورک مجرمین') },
    { key: 'luchakano', label: t('لوچکان') },
    { key: 'mashkok_homes', label: t('مشکوک کورونه') },
    { key: 'mashkok_shops', label: t('مشکوک دوکانونه') },
    { key: 'mawad', label: t('مواد') },
    { key: 'non_responsable_people', label: t('غير مسؤل کسان') },
    { key: 'old_soldiers_homes', label: t('د پخوانیو عسکرو کورونه') },
  ];

  /* ===== Date helpers (ISO <-> Date; formatting by selected calendar) ===== */
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

  /* Calendar-aware formatter using Intl API */
  function fmtIntl(date, granularity = 'daily') {
    // pick calendar id
    let ca = 'gregory';
    if (calendar.value === 'jalali') ca = 'persian';
    else if (calendar.value === 'hijri') ca = 'islamic-umalqura';

    // locale fallback
    const loc = (locale.value === 'fa' ? 'fa-AF' : 'en-US') + `-u-ca-${ca}`;

    // options by granularity
    const optsDaily = { year: 'numeric', month: 'short', day: '2-digit' };
    const optsMonthly = { year: 'numeric', month: 'short' };
    const optsYearly = { year: 'numeric' };

    const fmt = new Intl.DateTimeFormat(
      loc,
      granularity === 'yearly' ? optsYearly : granularity === 'monthly' ? optsMonthly : optsDaily
    );
    return fmt.format(date);
  }

  /* Flexible string ("YYYY[-MM[-DD]]") to Date for display */
  function parseFlexibleToDate(s, gran = repo.granularity) {
    if (!s) return null;
    if (s.length === 4) return new Date(`${s}-01-01T00:00:00`);
    if (s.length === 7) return new Date(`${s}-01T00:00:00`);
    return new Date(`${s}T00:00:00`);
  }

  /* Exposed for template */
  function formatForCalendar(isoLike) {
    const d = parseFlexibleToDate(String(isoLike || ''));
    if (!d || Number.isNaN(d.getTime())) return '';
    return fmtIntl(d, repo.granularity);
  }

  /* v-date-input models (UI), synced to repo ISO */
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

  /* Keep UI in sync when quickRange/app bar changes things */
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

  /* Granularity label */
  const granularityLabel = computed(
    () =>
      ({
        daily: t('ورځنی'),
        monthly: t('میاشتنی'),
        yearly: t('کلنی'),
      }[repo.granularity] || t('ورځنی'))
  );

  /* Quick ranges (stick to ISO; UI syncs via watchers) */
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

  /* Data fetch */
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
    if (!repo.From && !repo.To) quickRange('daily');
    else fetchAll();
  });

  /* ===================== Chart data mapping ===================== */
  const categories = computed(() => {
    const set = new Set();
    (repo.series || []).forEach((s) => (s.points || []).forEach((p) => set.add(String(p.date))));
    return Array.from(set).sort();
  });
  const stackedSeries = computed(() => {
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

  /* ===== AREA option (axisLabel uses calendar formatter) ===== */
  const areaOption = computed(() => {
    const cats = categories.value;
    const series = stackedSeries.value.map((s) => ({
      name: s.name,
      type: 'line',
      stack: 'total',
      smooth: true,
      symbol: 'circle',
      symbolSize: 6,
      lineStyle: { width: 2.5 },
      areaStyle: { opacity: 0.22 },
      emphasis: { focus: 'series' },
      data: s.data,
    }));
    return {
      color: palette,
      tooltip: {
        trigger: 'axis',
        axisPointer: { type: 'line' },
        valueFormatter: (v) => `${v | 0}`,
        formatter: (params) => {
          if (!params?.length) return '';
          const x = params[0].axisValue;
          const title = formatForCalendar(x);
          const lines = params.map((p) => `${p.marker} ${p.seriesName}: <b>${p.value | 0}</b>`);
          return `<div style="margin-bottom:4px"><b>${title}</b></div>${lines.join('<br/>')}`;
        },
      },
      legend: { top: 0, right: isRtl.value ? 'auto' : 0, left: isRtl.value ? 0 : 'auto' },
      grid: { left: 12, right: 12, top: 36, bottom: 20, containLabel: true },
      xAxis: {
        type: 'category',
        data: cats,
        boundaryGap: false,
        axisTick: { show: false },
        axisLabel: {
          formatter: (val) => formatForCalendar(val),
        },
      },
      yAxis: { type: 'value', min: 0, axisTick: { show: false } },
      series,
    };
  });

  /* ===== Donut option ===== */
  const donutLabels = computed(() => (repo.cards || []).map((c) => c?.label || t('بې‌نوم')));
  const donutSeries = computed(() =>
    (repo.cards || []).map((c) => Number(c?.total ?? 0)).filter((v) => Number.isFinite(v) && v >= 0)
  );
  const donutTotal = computed(() => (donutSeries.value || []).reduce((a, b) => a + b, 0));

  const donutOption = computed(() => ({
    color: palette,
    tooltip: { trigger: 'item' },
    legend: { bottom: 0, type: 'scroll' },
    series: [
      {
        name: t('ترکیب'),
        type: 'pie',
        radius: ['60%', '85%'],
        avoidLabelOverlap: true,
        padAngle: 2,
        itemStyle: { borderRadius: 8, borderColor: '#fff', borderWidth: 2 },
        label: { show: true, formatter: '{b}\n{d}%' },
        data: donutLabels.value.map((name, i) => ({ name, value: donutSeries.value[i] || 0 })),
      },
    ],
  }));

  /* ===== Bar (Top resources) ===== */
  const barBucket = computed(() => {
    const b = repo.buckets || [];
    if (!b.length) return null;
    return repo.formatKey ? b.find((x) => x.key === repo.formatKey) || b[0] : b[0];
  });
  const barCats = computed(() =>
    (barBucket.value?.items || []).map((i) => `${i.code_number || '#'} — ${i.name || t('بې‌نوم')}`)
  );
  const barVals = computed(() => (barBucket.value?.items || []).map((i) => Number(i.count || 0)));

  const barOption = computed(() => ({
    color: ['#3F8CFF'],
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    grid: { left: 12, right: 12, top: 10, bottom: 10, containLabel: true },
    xAxis: {
      type: 'category',
      data: barCats.value,
      axisTick: { show: false },
      axisLabel: { interval: 0, overflow: 'truncate' },
    },
    yAxis: { type: 'value', min: 0, axisTick: { show: false } },
    series: [
      {
        type: 'bar',
        data: barVals.value,
        barWidth: '45%',
        itemStyle: { borderRadius: [8, 8, 0, 0] },
        label: { show: true, position: 'top' },
      },
    ],
  }));

  /* Table headers */
  const recentHeaders = [
    { key: 'type', title: t('ډول'), width: 140 },
    { key: 'name', title: t('נوم') },
    { key: 'zone', title: t('زون'), width: 140 },
    { key: 'created_at', title: t('نېټه'), width: 160 },
  ];

  /* Fetch on calendar change (so axis & hints rerender) */
  watch(calendar, () => {
    // No need to refetch data; formatting updates automatically,
    // but if you want to refetch per calendar, uncomment:
    // fetchAll()
  });
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

  /* Donut center */
  .relative {
    position: relative;
  }
  .donut-center {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    pointer-events: none;
  }
  .donut-total {
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
  }
</style>
