<template>
  <v-card
    rounded="xl"
    class="chart-card"
    :class="{
      'is-loading': loading,
      'has-data': hasData && !loading,
      'glass-effect': true,
    }"
    elevation="0"
  >
    <!-- Animated gradient border -->
    <div class="card-border-gradient" />
    <div class="card-shine" />

    <!-- Header with enhanced gradient -->
    <div class="chart-header pa-5">
      <div class="d-flex align-center justify-space-between gap-4">
        <div class="min-w-0">
          <div class="d-flex align-center gap-2 mb-1">
            <div class="title-indicator">
              <span class="pulse-dot" />
            </div>
            <div class="text-h6 font-weight-black text-truncate title-text">
              {{ computedTitle }}
            </div>
          </div>

          <div
            v-if="computedSubtitle"
            class="d-flex align-center text-caption text-medium-emphasis text-truncate subtitle-text"
          >
            <v-icon size="14" class="mr-1" color="primary" opacity="0.7"
              >mdi-information-outline</v-icon
            >
            {{ computedSubtitle }}
          </div>
        </div>

        <div class="d-flex align-center gap-2">
          <v-btn
            v-if="showDownload"
            size="small"
            variant="text"
            rounded="lg"
            class="action-btn"
            :disabled="!hasData || loading"
            @click="downloadPng"
          >
            <v-icon size="18">mdi-download</v-icon>
            <span class="action-btn-text">{{ t('charts.download') }}</span>
          </v-btn>

          <v-btn
            v-if="showRefresh"
            size="small"
            variant="text"
            rounded="lg"
            class="action-btn"
            :loading="loading"
            @click="$emit('refresh')"
          >
            <v-icon size="18" :class="{ 'spin-icon': loading }">mdi-refresh</v-icon>
            <span class="action-btn-text">{{ t('charts.refresh') }}</span>
          </v-btn>
        </div>
      </div>
    </div>

    <v-divider class="soft-divider" />

    <!-- Body with enhanced transitions -->
    <div class="chart-body pa-4">
      <!-- Empty state with animation -->
      <div v-if="!hasData && !loading" class="empty-state py-10 text-center">
        <div class="empty-icon-wrap">
          <v-icon size="54" class="empty-icon" color="primary" opacity="0.5">mdi-chart-bar</v-icon>
          <div class="empty-icon-ring" />
        </div>
        <div class="text-body-2 text-medium-emphasis mt-3">
          {{ t('charts.empty') }}
        </div>
        <v-btn
          v-if="showRefresh"
          variant="tonal"
          color="primary"
          size="small"
          class="mt-4 empty-btn"
          rounded="pill"
          @click="$emit('refresh')"
        >
          <v-icon start size="16">mdi-refresh</v-icon>
          {{ t('charts.loadData') }}
        </v-btn>
      </div>

      <!-- Chart with enhanced loading state -->
      <div v-show="hasData || loading" class="chart-wrap">
        <div ref="el" class="echart" :style="{ height: heightPx }"></div>

        <!-- Enhanced loading overlay -->
        <div v-if="loading" class="loading-overlay">
          <div class="loading-card">
            <div class="loading-spinner">
              <v-progress-circular indeterminate color="primary" size="48" width="4" />
            </div>
            <div class="loading-text text-caption font-weight-medium mt-3">
              {{ t('charts.loading') }}
            </div>
            <div class="loading-dots">
              <span class="dot" />
              <span class="dot" />
              <span class="dot" />
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced footer stats with animations -->
      <div v-if="hasData && showStats" class="stats mt-4">
        <div class="stat" v-for="(stat, index) in statsData" :key="index">
          <div class="stat-icon">
            <v-icon size="16" :color="stat.color" opacity="0.7">{{ stat.icon }}</v-icon>
          </div>
          <div class="stat-content">
            <div class="stat-label text-caption">{{ stat.label }}</div>
            <div class="stat-value text-h6 font-weight-black">{{ stat.value }}</div>
          </div>
          <div
            class="stat-glow"
            :style="{
              background: `radial-gradient(circle at 30% 30%, ${stat.color}15, transparent 70%)`,
            }"
          />
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
    data: { type: Array, default: () => [] },
    showRefresh: { type: Boolean, default: true },
    showDownload: { type: Boolean, default: true },
    showAverage: { type: Boolean, default: true },
    showStats: { type: Boolean, default: true },
    showValues: { type: Boolean, default: true },
    barWidth: { type: Number, default: 44 },
    topN: { type: Number, default: 0 },
    sort: { type: String, default: 'desc' },
    enableZoom: { type: Boolean, default: true },
    showLine: { type: Boolean, default: false },
    showCumulative: { type: Boolean, default: false },
    highlightIndex: { type: Number, default: -1 },
    highlightColor: { type: String, default: '#505372' },
  });

  const emit = defineEmits(['refresh', 'barClick']);

  const el = ref(null);
  let chart = null;
  let ro = null;
  let rafResize = 0;

  const heightPx = computed(() => `${props.height}px`);

  const normalizedItems = computed(() => {
    const arr = Array.isArray(props.data) ? props.data : [];
    return arr.map((x) => ({
      label: String(x?.hawza ?? '—'),
      value: Number(x?.count ?? 0),
      raw: x,
    }));
  });

  const viewItems = computed(() => {
    let arr = [...normalizedItems.value];
    if (props.sort === 'asc') arr.sort((a, b) => a.value - b.value);
    if (props.sort === 'desc') arr.sort((a, b) => b.value - a.value);
    if (props.topN && props.topN > 0) arr = arr.slice(0, props.topN);
    return arr;
  });

  const hasData = computed(() => viewItems.value.length > 0);

  const computedTitle = computed(() => t('dashboard.resourcesByHawza.title'));
  const computedSubtitle = computed(() => t('dashboard.resourcesByHawza.subtitle'));

  const totalValue = computed(() =>
    viewItems.value.reduce((a, b) => a + (Number(b.value) || 0), 0)
  );
  const avgValue = computed(() => {
    const n = viewItems.value.length || 1;
    return Math.round((totalValue.value / n) * 100) / 100;
  });
  const maxValue = computed(() =>
    Math.max(...(viewItems.value.length ? viewItems.value.map((x) => x.value) : [0]))
  );

  const statsData = computed(() => [
    {
      label: t('charts.total'),
      value: totalValue.value.toLocaleString(),
      icon: 'mdi-chart-box-outline',
      color: 'var(--v-theme-primary)',
    },
    {
      label: t('charts.avg'),
      value: avgValue.value.toLocaleString(),
      icon: 'mdi-chart-line',
      color: 'var(--v-theme-teal)',
    },
    {
      label: t('charts.max'),
      value: maxValue.value.toLocaleString(),
      icon: 'mdi-chart-bell-curve',
      color: 'var(--v-theme-success)',
    },
  ]);

  function cssVarRgb(name, fallback = '33, 150, 243') {
    const raw = getComputedStyle(document.documentElement).getPropertyValue(name)?.trim();
    return raw || fallback;
  }

  function rgbaFromCssVar(name, alpha = 1, fallbackRgb = '33, 150, 243') {
    const rgb = cssVarRgb(name, fallbackRgb);
    if (rgb.includes(',')) return `rgba(${rgb}, ${alpha})`;
    return `rgba(${fallbackRgb}, ${alpha})`;
  }

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
    const primary75 = rgbaFromCssVar('--v-theme-primary', 0.75);
    const primary35 = rgbaFromCssVar('--v-theme-primary', 0.35);

    const palette = [
      ['#6366F1', '#4F46E5'],
      ['#06B6D4', '#0891B2'],
      ['#10B981', '#059669'],
      ['#F59E0B', '#D97706'],
      ['#EF4444', '#DC2626'],
      ['#8B5CF6', '#7C3AED'],
      ['#EC4899', '#DB2777'],
      ['#14B8A6', '#0D9488'],
      ['#84CC16', '#65A30D'],
      ['#F97316', '#EA580C'],
    ];

    const seriesData = values.map((v, i) => {
      const colorPair = palette[i % palette.length];

      const gradient = new echarts.graphic.LinearGradient(0, 0, 0, 1, [
        { offset: 0, color: colorPair[0] },
        { offset: 1, color: colorPair[1] },
      ]);

      const isHi = props.highlightIndex >= 0 && i === props.highlightIndex;

      return {
        value: v,
        itemStyle: {
          color: isHi ? props.highlightColor : gradient,
          shadowBlur: 14,
          shadowColor: 'rgba(0,0,0,0.2)',
          borderRadius: [12, 12, 6, 6],
        },
      };
    });

    let cum = [];
    if (props.showCumulative) {
      let sum = 0;
      cum = values.map((v) => (sum += v));
    }

    const zoomNeeded = props.enableZoom && labels.length > 10;

    return {
      animation: true,
      animationDuration: 800,
      animationEasing: 'cubicOut',
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow',
          shadowStyle: {
            color: 'rgba(0,0,0,0.05)',
          },
        },
        backgroundColor: 'rgba(30,30,40,0.95)',
        borderWidth: 0,
        borderRadius: 16,
        textStyle: { color: '#fff', fontSize: 12 },
        padding: [12, 16],
        extraCssText: 'box-shadow: 0 12px 28px rgba(0,0,0,0.3); backdrop-filter: blur(8px);',
      },
      grid: {
        left: 16,
        right: 16,
        top: 28,
        bottom: zoomNeeded ? 58 : 36,
        containLabel: true,
      },
      xAxis: {
        type: 'category',
        data: labels,
        axisTick: {
          alignWithLabel: true,
          length: 8,
          lineStyle: { color: 'rgba(0,0,0,0.1)' },
        },
        axisLine: { lineStyle: { color: 'rgba(0,0,0,0.1)' } },
        axisLabel: {
          interval: 0,
          rotate: labels.length > 8 ? 35 : 0,
          margin: 12,
          fontWeight: 500,
          fontSize: 11,
          color: 'rgba(0,0,0,0.7)',
        },
      },
      yAxis: {
        type: 'value',
        splitLine: {
          lineStyle: {
            opacity: 0.15,
            type: 'dashed',
          },
        },
        axisLabel: {
          fontWeight: 500,
          fontSize: 11,
          color: 'rgba(0,0,0,0.6)',
        },
      },
      dataZoom: zoomNeeded
        ? [
            { type: 'inside', start: 0, end: 100 },
            {
              type: 'slider',
              height: 20,
              bottom: 8,
              start: 0,
              end: 100,
              borderColor: 'transparent',
              backgroundColor: 'rgba(0,0,0,0.05)',
              fillerColor: rgbaFromCssVar('--v-theme-primary', 0.3),
              handleStyle: {
                color: rgbaFromCssVar('--v-theme-primary', 0.8),
                borderColor: 'transparent',
                shadowBlur: 6,
                shadowColor: 'rgba(0,0,0,0.2)',
              },
            },
          ]
        : [],
      series: [
        {
          name: t('dashboard.resourcesByHawza.seriesName'),
          type: 'bar',
          data: seriesData,
          barWidth: props.barWidth,
          barCategoryGap: '25%',
          itemStyle: {
            borderRadius: [12, 12, 6, 6],
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
              { offset: 0, color: primary95 },
              { offset: 0.6, color: primary75 },
              { offset: 1, color: primary35 },
            ]),
          },
          emphasis: {
            focus: 'series',
            itemStyle: {
              shadowBlur: 20,
              shadowColor: 'rgba(0,0,0,0.25)',
              borderRadius: [14, 14, 8, 8],
            },
          },
          label: showValueLabels
            ? {
                show: true,
                position: 'top',
                fontWeight: 700,
                fontSize: 11,
                color: 'rgba(0,0,0,0.7)',
                formatter: ({ value }) => value,
              }
            : { show: false },
          markLine: props.showAverage
            ? {
                symbol: ['none', 'none'],
                lineStyle: {
                  type: 'dashed',
                  opacity: 0.5,
                  width: 2,
                  color: 'rgba(0,0,0,0.3)',
                },
                label: {
                  formatter: `${t('charts.avg')}: ${avgValue.value}`,
                  fontSize: 11,
                  fontWeight: 600,
                  backgroundColor: 'rgba(255,255,255,0.9)',
                  padding: [4, 8],
                  borderRadius: 12,
                },
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
                symbolSize: 8,
                lineStyle: {
                  width: 3,
                  opacity: 0.8,
                  color: rgbaFromCssVar('--v-theme-teal', 0.8),
                },
                areaStyle: {
                  color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                    { offset: 0, color: rgbaFromCssVar('--v-theme-teal', 0.3) },
                    { offset: 1, color: rgbaFromCssVar('--v-theme-teal', 0) },
                  ]),
                },
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
                lineStyle: {
                  type: 'dotted',
                  width: 3,
                  opacity: 0.7,
                  color: rgbaFromCssVar('--v-theme-success', 0.9),
                },
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
          value: item?.value ?? params?.data?.value ?? params?.value ?? 0,
          raw: item?.raw ?? null,
        });
      });
    }

    chart.setOption(buildOption(), true);
    safeResize();
  }

  function downloadPng() {
    if (!chart) return;
    const url = chart.getDataURL({
      type: 'png',
      pixelRatio: 2,
      backgroundColor: 'transparent',
      excludeComponents: ['dataZoom'],
    });
    const a = document.createElement('a');
    a.href = url;
    a.download = `resources_by_hawza.png`;
    a.click();
  }

  function destroy() {
    try {
      cancelAnimationFrame(rafResize);
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
    position: relative;
    overflow: hidden;
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.02) inset;
    backdrop-filter: blur(12px);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .chart-card:hover {
    box-shadow: 0 28px 48px -16px rgba(0, 0, 0, 0.2),
      0 0 0 1px rgba(var(--v-theme-primary), 0.1) inset;
    transform: translateY(-2px);
  }

  /* Animated gradient border */
  .card-border-gradient {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: radial-gradient(
      1200px 320px at 0% 0%,
      rgba(var(--v-theme-primary), 0.15) 0%,
      rgba(var(--v-theme-teal), 0.1) 40%,
      transparent 70%
    );
    opacity: 0.8;
    animation: gradientShift 10s ease infinite;
  }

  .card-shine {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    opacity: 0.5;
    animation: shine 8s ease-in-out infinite;
    pointer-events: none;
  }

  @keyframes gradientShift {
    0%,
    100% {
      opacity: 0.8;
      transform: scale(1);
    }
    50% {
      opacity: 1;
      transform: scale(1.1);
    }
  }

  @keyframes shine {
    0%,
    100% {
      transform: translate(-10%, -10%) rotate(0deg);
    }
    50% {
      transform: translate(5%, 5%) rotate(5deg);
    }
  }

  /* Header */
  .chart-header {
    position: relative;
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(4px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.5);
  }

  .title-indicator {
    position: relative;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .pulse-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: rgb(var(--v-theme-primary));
    box-shadow: 0 0 0 4px rgba(var(--v-theme-primary), 0.2);
    animation: pulse 2s ease infinite;
  }

  @keyframes pulse {
    0%,
    100% {
      box-shadow: 0 0 0 4px rgba(var(--v-theme-primary), 0.2);
    }
    50% {
      box-shadow: 0 0 0 8px rgba(var(--v-theme-primary), 0.1);
    }
  }

  .title-text {
    letter-spacing: -0.02em;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.7));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .subtitle-text {
    opacity: 0.7;
    transition: opacity 0.2s ease;
  }

  .subtitle-text:hover {
    opacity: 1;
  }

  .action-btn {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(4px);
    transition: all 0.2s ease;
    color: rgb(var(--v-theme-primary));
    font-weight: 500;
    padding: 0 16px;
    letter-spacing: -0.01em;
  }

  .action-btn:hover {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(var(--v-theme-primary), 0.3);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(var(--v-theme-primary), 0.15);
  }

  .action-btn-text {
    margin-left: 8px;
  }

  .spin-icon {
    animation: spin 1s ease infinite;
  }

  @keyframes spin {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }

  .soft-divider {
    opacity: 0.4;
    border-color: rgba(255, 255, 255, 0.5);
  }

  /* Chart box */
  .chart-wrap {
    position: relative;
    border-radius: 22px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
  }

  .echart {
    width: 100%;
    transition: opacity 0.3s ease;
  }

  /* Loading overlay */
  .loading-overlay {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(8px);
    animation: fadeIn 0.3s ease;
  }

  .loading-card {
    display: grid;
    place-items: center;
    padding: 28px 32px;
    border-radius: 32px;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    animation: slideUp 0.4s ease;
  }

  .loading-spinner {
    filter: drop-shadow(0 8px 16px rgba(var(--v-theme-primary), 0.2));
  }

  .loading-text {
    background: linear-gradient(90deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .loading-dots {
    display: flex;
    gap: 6px;
    margin-top: 8px;
  }

  .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgb(var(--v-theme-primary));
    opacity: 0.5;
    animation: dotPulse 1.4s ease infinite;
  }

  .dot:nth-child(2) {
    animation-delay: 0.2s;
  }
  .dot:nth-child(3) {
    animation-delay: 0.4s;
  }

  @keyframes dotPulse {
    0%,
    60%,
    100% {
      transform: scale(1);
      opacity: 0.5;
    }
    30% {
      transform: scale(1.3);
      opacity: 1;
    }
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Empty state */
  .empty-state {
    border: 2px dashed rgba(0, 0, 0, 0.1);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(4px);
    transition: all 0.3s ease;
  }

  .empty-state:hover {
    border-color: rgba(var(--v-theme-primary), 0.3);
    background: rgba(255, 255, 255, 0.6);
  }

  .empty-icon-wrap {
    position: relative;
    width: 90px;
    height: 90px;
    margin: 0 auto;
    border-radius: 30px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.05);
  }

  .empty-icon-ring {
    position: absolute;
    inset: -4px;
    border-radius: 34px;
    border: 2px solid rgba(var(--v-theme-primary), 0.2);
    animation: ringPulse 2s ease infinite;
  }

  @keyframes ringPulse {
    0%,
    100% {
      transform: scale(1);
      opacity: 0.3;
    }
    50% {
      transform: scale(1.1);
      opacity: 0.1;
    }
  }

  .empty-btn {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.9);
    transition: all 0.2s ease;
  }

  .empty-btn:hover {
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(var(--v-theme-primary), 0.2);
  }

  /* Stats */
  .stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
  }

  .stat {
    position: relative;
    border-radius: 22px;
    padding: 16px;
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 12px;
    overflow: hidden;
  }

  .stat:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.7);
    border-color: rgba(255, 255, 255, 0.9);
    box-shadow: 0 16px 28px rgba(0, 0, 0, 0.08);
  }

  .stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
  }

  .stat-content {
    flex: 1;
    position: relative;
    z-index: 1;
  }

  .stat-label {
    opacity: 0.6;
    font-weight: 500;
    margin-bottom: 2px;
  }

  .stat-value {
    line-height: 1.2;
    letter-spacing: -0.02em;
  }

  .stat-glow {
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: 0.5;
    transition: opacity 0.3s ease;
  }

  .stat:hover .stat-glow {
    opacity: 0.8;
  }

  /* Dark mode enhancements */
  :deep(.v-theme--dark) .chart-card {
    background: rgba(20, 20, 24, 0.75);
    border-color: rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05) inset;
  }

  :deep(.v-theme--dark) .chart-header {
    background: rgba(30, 30, 34, 0.4);
    border-bottom-color: rgba(255, 255, 255, 0.1);
  }

  :deep(.v-theme--dark) .title-text {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  :deep(.v-theme--dark) .action-btn {
    background: rgba(40, 40, 44, 0.7);
    border-color: rgba(255, 255, 255, 0.15);
    color: white;
  }

  :deep(.v-theme--dark) .action-btn:hover {
    background: rgba(50, 50, 54, 0.9);
    border-color: rgba(var(--v-theme-primary), 0.3);
  }

  :deep(.v-theme--dark) .chart-wrap {
    background: rgba(25, 25, 29, 0.5);
    border-color: rgba(255, 255, 255, 0.1);
  }

  :deep(.v-theme--dark) .loading-overlay {
    background: rgba(0, 0, 0, 0.5);
  }

  :deep(.v-theme--dark) .loading-card {
    background: rgba(30, 30, 34, 0.7);
    border-color: rgba(255, 255, 255, 0.1);
  }

  :deep(.v-theme--dark) .stat {
    background: rgba(30, 30, 34, 0.5);
    border-color: rgba(255, 255, 255, 0.1);
  }

  :deep(.v-theme--dark) .stat:hover {
    background: rgba(40, 40, 44, 0.7);
    border-color: rgba(255, 255, 255, 0.15);
  }

  :deep(.v-theme--dark) .stat-icon {
    background: rgba(40, 40, 44, 0.7);
    border-color: rgba(255, 255, 255, 0.15);
  }

  :deep(.v-theme--dark) .xAxis .axisLabel,
  :deep(.v-theme--dark) .yAxis .axisLabel {
    color: rgba(255, 255, 255, 0.7) !important;
  }
</style>
