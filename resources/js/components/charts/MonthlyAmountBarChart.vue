<template>
  <v-card rounded="xl" class="chart-card" :class="{ 'is-loading': loading }">
    <!-- Header -->
    <div class="chart-header pa-4">
      <div class="d-flex align-center justify-space-between gap-2">
        <div class="min-w-0">
          <div class="text-h6 font-weight-black text-truncate">{{ computedTitle }}</div>
          <div v-if="computedSubtitle" class="text-caption text-medium-emphasis text-truncate">
            {{ computedSubtitle }}
          </div>
        </div>

        <div class="d-flex align-center gap-2">
          <v-btn
            v-if="showDownload"
            size="small"
            variant="plain"
            rounded="lg"
            color="primary"
            :disabled="!hasData || loading"
            @click="downloadPng"
          >
            <v-icon start>mdi-download</v-icon>
            {{ t('charts.download') }}
          </v-btn>

          <v-btn
            v-if="showRefresh"
            size="small"
            variant="plain"
            rounded="lg"
            color="primary"
            :loading="loading"
            @click="$emit('refresh')"
          >
            <v-icon start>mdi-refresh</v-icon>
            {{ t('charts.refresh') }}
          </v-btn>
        </div>
      </div>
    </div>

    <v-divider />

    <!-- Body -->
    <div class="chart-body pa-3">
      <div v-if="!hasData && !loading" class="empty-state py-10 text-center">
        <v-icon size="64" color="grey-lighten-2">mdi-chart-bar</v-icon>
        <div class="text-body-2 text-medium-emphasis mt-2">{{ t('charts.empty') }}</div>
      </div>

      <div v-show="hasData || loading" class="chart-wrap">
        <div ref="el" class="echart" :style="{ height: heightPx }"></div>

        <div v-if="loading" class="loading-overlay">
          <v-progress-circular indeterminate color="primary" size="44" />
        </div>
      </div>

      <!-- Footer stats -->
      <div v-if="hasData && showStats" class="stats mt-3">
        <div class="stat">
          <div class="label">{{ t('charts.total') }}</div>
          <div class="value">{{ totalValue }}</div>
        </div>
        <div class="stat">
          <div class="label">{{ t('charts.avg') }}</div>
          <div class="value">{{ avgValue }}</div>
        </div>
        <div class="stat">
          <div class="label">{{ t('charts.max') }}</div>
          <div class="value">{{ maxValue }}</div>
        </div>
      </div>
    </div>
  </v-card>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import * as echarts from 'echarts';

const { t } = useI18n();

const props = defineProps({
  height: { type: Number, default: 340 },
  loading: { type: Boolean, default: false },

  // backend-driven array: [{ key, month, label, value }]
  data: { type: [Array, Object], default: () => [] },

  calendar: { type: String, default: 'miladi' },
  year: { type: [Number, String], default: '' },

  showRefresh: { type: Boolean, default: true },
  showDownload: { type: Boolean, default: true },
  showAverage: { type: Boolean, default: true },
  showStats: { type: Boolean, default: true },

  // ✅ NEW features
  showValues: { type: Boolean, default: true },          // labels above bars
  barWidth: { type: Number, default: 44 },               // ✅ wider bars
  topN: { type: Number, default: 0 },                    // 0 = all
  sort: { type: String, default: 'none' },               // none | asc | desc
  enableZoom: { type: Boolean, default: true },          // scrollbar when too many
  showLine: { type: Boolean, default: false },           // overlay line
  showCumulative: { type: Boolean, default: false },     // cumulative line

  highlightIndex: { type: Number, default: -1 },
  highlightColor: { type: String, default: '#505372' },
});

const emit = defineEmits(['refresh', 'barClick']);

const el = ref(null);
let chart = null;
let ro = null;
let rafResize = 0;

const heightPx = computed(() => `${props.height}px`);

/** normalize input data */
const normalizedItems = computed(() => {
  if (Array.isArray(props.data)) {
    return props.data.map((x) => ({
      label: String(x?.label ?? '—'),
      value: Number(x?.value ?? 0),
      raw: x,
    }));
  }

  const labels = Array.isArray(props.data?.labels) ? props.data.labels : [];
  const values = Array.isArray(props.data?.values) ? props.data.values : [];
  return labels.map((l, i) => ({
    label: String(l ?? '—'),
    value: Number(values?.[i] ?? 0),
    raw: { label: l, value: values?.[i] },
  }));
});

/** apply sort + topN (for chart only) */
const viewItems = computed(() => {
  let arr = [...normalizedItems.value];

  if (props.sort === 'asc') arr.sort((a, b) => a.value - b.value);
  if (props.sort === 'desc') arr.sort((a, b) => b.value - a.value);

  if (props.topN && props.topN > 0) arr = arr.slice(0, props.topN);

  return arr;
});

const hasData = computed(() => viewItems.value.length > 0);

const computedTitle = computed(() => t('dashboard.resourcesMonthly.title'));
const computedSubtitle = computed(() => {
  const y = String(props.year ?? '').trim();
  if (!y) return t('dashboard.resourcesMonthly.subtitleDefault');
  return t('dashboard.resourcesMonthly.subtitleWithYear', { year: y });
});

/** stats are from the shown chart items */
const totalValue = computed(() => viewItems.value.reduce((a, b) => a + (Number(b.value) || 0), 0));
const avgValue = computed(() => {
  const n = viewItems.value.length || 1;
  return Math.round((totalValue.value / n) * 100) / 100;
});
const maxValue = computed(() => Math.max(...(viewItems.value.length ? viewItems.value.map((x) => x.value) : [0])));

/** ===== Vuetify CSS var -> real rgba (canvas-safe) ===== */
function cssVarRgb(name, fallback = '33, 150, 243') {
  const raw = getComputedStyle(document.documentElement).getPropertyValue(name)?.trim();
  return raw || fallback;
}
function rgbaFromCssVar(name, alpha = 1, fallbackRgb = '33, 150, 243') {
  const rgb = cssVarRgb(name, fallbackRgb);
  if (rgb.includes(',')) return `rgba(${rgb}, ${alpha})`;
  return `rgba(${fallbackRgb}, ${alpha})`;
}

/** ===== safe resize ===== */
function safeResize() {
  if (!chart) return;
  cancelAnimationFrame(rafResize);
  rafResize = requestAnimationFrame(() => chart?.resize());
}

function buildOption() {
  const items = viewItems.value;

  const labels = items.map((x) => x.label);
  const values = items.map((x) => x.value);

  const showValueLabels = props.showValues && labels.length <= 12;

  const primary95 = rgbaFromCssVar('--v-theme-primary', 0.95);
  const primary35 = rgbaFromCssVar('--v-theme-primary', 0.35);

  // series data with highlight
  const seriesData = values.map((v, i) => {
    const isHi = props.highlightIndex >= 0 && i === props.highlightIndex;
    if (!isHi) return v;

    return {
      value: v,
      itemStyle: {
        color: props.highlightColor,
        shadowBlur: 10,
        shadowColor: 'rgba(0,0,0,0.18)',
      },
    };
  });

  // cumulative values (optional)
  let cum = [];
  if (props.showCumulative) {
    let sum = 0;
    cum = values.map((v) => {
      sum += v;
      return sum;
    });
  }

  const zoomNeeded = props.enableZoom && labels.length > 10;

  return {
    animationDuration: 500,
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      backgroundColor: 'rgba(20,20,20,0.92)',
      borderWidth: 0,
      textStyle: { color: '#fff' },
      padding: [10, 12],
      formatter: (params) => {
        const arr = Array.isArray(params) ? params : [params];
        const name = arr?.[0]?.axisValue ?? '';
        const rows = arr
          .map((p) => {
            const val = p?.data?.value ?? p?.value ?? 0;
            return `
              <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
                <span style="width:10px;height:10px;border-radius:999px;background:${p?.color}"></span>
                <span>${p?.seriesName}:</span>
                <span style="font-weight:800">${val}</span>
              </div>
            `;
          })
          .join('');
        return `<div style="font-weight:800;margin-bottom:4px">${name}</div>${rows}`;
      },
    },
    grid: { left: 14, right: 14, top: 22, bottom: zoomNeeded ? 58 : 34, containLabel: true },
    xAxis: {
      type: 'category',
      data: labels,
      axisTick: { alignWithLabel: true },
      axisLabel: { interval: 0, rotate: labels.length > 8 ? 35 : 0, margin: 12 },
    },
    yAxis: { type: 'value', splitLine: { lineStyle: { opacity: 0.25 } } },
    dataZoom: zoomNeeded
      ? [
          { type: 'inside', start: 0, end: 100 },
          { type: 'slider', height: 18, bottom: 10, start: 0, end: 100 },
        ]
      : [],
    series: [
      {
        name: t('dashboard.resourcesMonthly.seriesName'),
        type: 'bar',
        data: seriesData,
        barWidth: props.barWidth,            // ✅ wider bars
        barCategoryGap: '20%',
        itemStyle: {
          borderRadius: [10, 10, 4, 4],
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: primary95 },
            { offset: 1, color: primary35 },
          ]),
        },
        emphasis: {
          focus: 'series',
          itemStyle: { shadowBlur: 14, shadowColor: 'rgba(0,0,0,0.18)' },
        },
        label: showValueLabels
          ? { show: true, position: 'top', fontWeight: 800, formatter: ({ value }) => value }
          : { show: false },
        markLine: props.showAverage
          ? {
              symbol: ['none', 'none'],
              lineStyle: { type: 'dashed', opacity: 0.6 },
              label: { formatter: `${t('charts.avg')}: ${avgValue.value}` },
              data: [{ type: 'average', name: 'avg' }],
            }
          : undefined,
      },

      ...(props.showLine
        ? [
            {
              name: t('charts.trend'),
              type: 'line',
              data: values,
              smooth: true,
              symbol: 'circle',
              symbolSize: 6,
              lineStyle: { width: 3, opacity: 0.8 },
              emphasis: { focus: 'series' },
            },
          ]
        : []),

      ...(props.showCumulative
        ? [
            {
              name: t('charts.cumulative'),
              type: 'line',
              data: cum,
              smooth: true,
              symbol: 'none',
              lineStyle: { type: 'dotted', width: 3, opacity: 0.8 },
              emphasis: { focus: 'series' },
            },
          ]
        : []),
    ],
  };
}

async function init() {
  await nextTick();
  if (!el.value) return;

  if (!chart) {
    chart = echarts.init(el.value);

    ro = new ResizeObserver(() => safeResize());
    ro.observe(el.value);
    window.addEventListener('resize', safeResize);

    chart.on('click', (params) => {
      const idx = params?.dataIndex ?? -1;
      const item = viewItems.value?.[idx];
      emit('barClick', {
        index: idx,
        label: item?.label ?? params?.name ?? '',
        value: item?.value ?? (params?.data?.value ?? params?.value ?? 0),
        raw: item?.raw ?? null,
      });
    });
  }

  chart.setOption(buildOption(), true);
  safeResize();
}

function downloadPng() {
  if (!chart) return;
  const url = chart.getDataURL({ type: 'png', pixelRatio: 2, backgroundColor: '#fff' });
  const a = document.createElement('a');
  a.href = url;
  a.download = `chart_${String(props.year || 'all')}.png`;
  a.click();
}

function destroy() {
  try {
    cancelAnimationFrame(rafResize);
    rafResize = 0;

    window.removeEventListener('resize', safeResize);
    ro?.disconnect();
    ro = null;

    chart?.dispose();
    chart = null;
  } catch (_) {}
}

onMounted(() => init());

watch(
  () => [
    props.data,
    props.height,
    props.highlightIndex,
    props.highlightColor,
    props.calendar,
    props.year,
    props.showAverage,
    props.showValues,
    props.barWidth,
    props.topN,
    props.sort,
    props.enableZoom,
    props.showLine,
    props.showCumulative,
  ],
  () => init(),
  { deep: true }
);

onBeforeUnmount(() => destroy());
</script>

<style scoped>
.chart-card {
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  background: white;
}
.chart-header {
  background: linear-gradient(90deg, rgba(var(--v-theme-primary), 0.08), rgba(var(--v-theme-teal), 0.05));
}
.chart-wrap {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  background: white;
}
.echart {
  width: 100%;
}
.loading-overlay {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  background: rgba(255, 255, 255, 0.55);
  backdrop-filter: blur(2px);
}
.empty-state {
  border: 1px dashed rgba(0, 0, 0, 0.14);
  border-radius: 16px;
  background: rgba(0, 0, 0, 0.02);
}

.stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}
.stat {
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 14px;
  padding: 10px 12px;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.02), rgba(0, 0, 0, 0.01));
}
.stat .label {
  font-size: 12px;
  opacity: 0.75;
}
.stat .value {
  font-size: 18px;
  font-weight: 900;
  margin-top: 2px;
}

:deep(.v-theme--dark) .chart-card {
  background: #1e1e1e;
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.35);
}
:deep(.v-theme--dark) .stat {
  border-color: rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
}
</style>
