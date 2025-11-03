<template>
  <v-card
    class="rounded-2xl"
    :elevation="hover ? 8 : 2"
    variant="tonal"
    :color="color"
  >
    <v-card-title class="text-subtitle-1 font-weight-bold d-flex align-center justify-space-between">
      <span>{{ title }}</span>
      <slot name="actions" />
    </v-card-title>
    <v-card-text class="pt-0">
      <div ref="el" :style="{height}" />
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import * as echarts from 'echarts';

const props = defineProps({
  title: { type: String, required: true },
  option: { type: Object, required: true },
  height: { type: String, default: '320px' },
  color: { type: String, default: 'primary' },
  hover: { type: Boolean, default: true },
});

const el = ref(null);
let chart;

const render = () => {
  if (!el.value) return;
  if (!chart) chart = echarts.init(el.value, null, { renderer: 'canvas' });
  chart.setOption(props.option, true);
};

const resizeHandler = () => chart && chart.resize();

onMounted(() => {
  render();
  window.addEventListener('resize', resizeHandler);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', resizeHandler);
  if (chart) {
    chart.dispose();
    chart = null;
  }
});

watch(() => props.option, () => render(), { deep: true });
</script>
