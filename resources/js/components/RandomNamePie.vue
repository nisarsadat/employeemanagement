<template>
  <div ref="root" :style="{ width: '100%', height }"></div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import * as echarts from 'echarts/core';
import { PieChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import { LabelLayout } from 'echarts/features';

echarts.use([
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  PieChart,
  CanvasRenderer,
  LabelLayout,
]);

const props = defineProps({
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  labels: { type: Array, default: () => [] },   // e.g. ['آمریت A','آمریت B', ...]
  values: { type: Array, default: () => [] },   // e.g. [10, 5, 2, ...] (same length)
  height: { type: String, default: '360px' },
  rtl: { type: Boolean, default: false },
});

const root = ref(null);
let chart = null;
let ro = null;

function buildOption() {
  const hasData = props.labels?.length && props.values?.length;
  const seriesData = (props.labels || []).map((name, i) => ({
    name,
    value: Number(props.values?.[i] ?? 0),
  }));

  return {
    title: {
      text: props.title,
      subtext: props.subtitle,
      left: 'center',
    },
    tooltip: {
      trigger: 'item',
      // Example: "آمریت A : 12 (34%)"
      formatter: ({ name, value, percent }) => `${name} : ${value} (${percent}%)`,
    },
    legend: {
      type: 'scroll',
      orient: 'vertical',
      right: 10,
      top: 20,
      bottom: 20,
      // If RTL, keep legend at left for nicer mirroring
      left: props.rtl ? 10 : 'auto',
    },
    series: [
      {
        name: props.title,
        type: 'pie',
        radius: '55%',
        center: props.rtl ? ['60%', '50%'] : ['40%', '50%'],
        data: hasData ? seriesData : [],
        emphasis: {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0,0,0,0.5)',
          },
        },
        // Show a placeholder if there is no data
        label: {
          show: !hasData,
          position: 'center',
          formatter: hasData ? '' : 'هیڅ معلومات نشته',
        },
      },
    ],
  };
}

function render() {
  if (!root.value) return;
  if (!chart) {
    chart = echarts.init(root.value);
  }
  chart.setOption(buildOption(), true);
}

onMounted(() => {
  render();
  // Resize observer keeps chart responsive
  ro = new ResizeObserver(() => {
    chart && chart.resize();
  });
  ro.observe(root.value);
});

onBeforeUnmount(() => {
  ro && ro.disconnect();
  if (chart) {
    chart.dispose();
    chart = null;
  }
});

// Re-render whenever inputs change
watch(() => [props.title, props.subtitle, props.labels, props.values, props.rtl], render, { deep: true });
</script>
