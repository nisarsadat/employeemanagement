<!-- src/components/charts/FormatsPieChart.vue -->
<template>
  <v-card rounded="xl" class="shadow-lg" color="surface" variant="elevated">
    <v-card-text class="pa-4">
      <!-- Header -->
      <div class="d-flex align-center justify-space-between mb-4">
        <div>
          <div class="text-h6 font-weight-bold text-primary mb-1">
            <v-icon size="20" color="primary" class="mr-2">mdi-chart-pie</v-icon>
            {{ title }}
          </div>
          <div class="text-caption text-medium-emphasis d-flex align-center gap-2">
            <v-icon size="14">mdi-information</v-icon>
            {{ subtitle }}
          </div>
        </div>

        <div class="d-flex align-center gap-2">
          <v-chip v-if="total != null" size="small" variant="tonal" color="primary" class="px-3">
            <v-icon start size="14">mdi-chart-box</v-icon>
            {{ totalLabel }}: {{ total.toLocaleString() }}
          </v-chip>
        </div>
      </div>

      <!-- Chart Container -->
      <div class="chart-container" :style="{ height: chartHeight }">
        <div v-if="hasData" ref="chartEl" class="chart" />
        
        <!-- Empty State -->
        <div v-else class="empty-state">
          <v-icon size="64" color="grey-lighten-2" class="mb-3">mdi-chart-donut</v-icon>
          <div class="text-body-1 text-medium-emphasis mb-2">{{ emptyText }}</div>
          <v-btn size="small" variant="tonal" color="primary" @click="$emit('refresh')">
            <v-icon start size="16">mdi-refresh</v-icon>
            {{ t('تازه‌سازی') }}
          </v-btn>
        </div>
      </div>

      <!-- Legend Stats -->
      <div v-if="hasData" class="legend-stats mt-4">
        <div class="d-flex align-center justify-space-between">
          <div class="text-caption text-medium-emphasis">
            {{ t('تعداد زون‌ها') }}: {{ data.length }}
          </div>
          <div class="text-caption text-medium-emphasis">
            {{ t('بیشترین') }}: {{ maxZoneValue }} • {{ t('کمترین') }}: {{ minZoneValue }}
          </div>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import * as echarts from 'echarts'
import 'echarts/theme/macarons'

const { t } = useI18n()
const props = defineProps({
  title: { type: String, default: 'توزیع فرم‌ها' },
  subtitle: { type: String, default: '' },
  total: { type: Number, default: null },
  totalLabel: { type: String, default: 'مجموع' },
  emptyText: { type: String, default: 'هیچ دیتا موجود نیست' },
  data: { type: Array, default: () => [] },
  height: { type: [Number, String], default: 340 },
  donut: { type: Boolean, default: false },
  showLegend: { type: Boolean, default: true },
})

const emit = defineEmits(['refresh'])

const chartEl = ref(null)
let chart = null

const hasData = computed(() => Array.isArray(props.data) && props.data.length > 0)
const chartHeight = computed(() => typeof props.height === 'number' ? `${props.height}px` : props.height)

const maxZoneValue = computed(() => {
  if (!hasData.value) return 0
  return Math.max(...props.data.map(d => d.value)).toLocaleString()
})

const minZoneValue = computed(() => {
  if (!hasData.value) return 0
  return Math.min(...props.data.map(d => d.value)).toLocaleString()
})

function buildOption() {
  const radius = props.donut ? ['45%', '70%'] : '65%'
  const center = props.showLegend ? ['60%', '50%'] : ['50%', '50%']

  return {
    backgroundColor: 'transparent',
    tooltip: {
      trigger: 'item',
      formatter: function(params) {
        const percentage = ((params.value / props.total) * 100).toFixed(1)
        return `
          <div style="padding: 8px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1)">
            <div style="font-weight: 600; color: #333; margin-bottom: 4px">${params.name}</div>
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px">
              <span style="color: #666">${params.value.toLocaleString()}</span>
              <span style="color: #2196F3; font-weight: 600">${percentage}%</span>
            </div>
          </div>
        `
      },
      backgroundColor: 'transparent',
      borderWidth: 0,
      extraCssText: 'box-shadow: none;'
    },
    legend: props.showLegend ? {
      orient: 'vertical',
      left: 'left',
      top: 'center',
      textStyle: {
        color: 'rgb(var(--v-theme-on-surface))',
        fontSize: 12,
        fontFamily: 'Vazirmatn, sans-serif'
      },
      itemWidth: 12,
      itemHeight: 12,
      itemGap: 8,
      formatter: function(name) {
        const item = props.data.find(d => d.name === name)
        if (!item) return name
        const percentage = ((item.value / props.total) * 100).toFixed(1)
        return `${name}  ${percentage}%`
      }
    } : undefined,
    series: [
      {
        name: props.title,
        type: 'pie',
        radius,
        center,
        avoidLabelOverlap: false,
        itemStyle: {
          borderRadius: 6,
          borderColor: 'rgb(var(--v-theme-surface))',
          borderWidth: 2
        },
        label: {
          show: true,
          position: 'outside',
          formatter: '{b}: {c}',
          color: 'rgb(var(--v-theme-on-surface))',
          fontFamily: 'Vazirmatn, sans-serif',
          fontSize: 11,
          fontWeight: 500
        },
        labelLine: {
          show: true,
          length: 10,
          length2: 20,
          smooth: true
        },
        emphasis: {
          scale: true,
          scaleSize: 10,
          itemStyle: {
            shadowBlur: 20,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0, 0, 0, 0.2)'
          },
          label: {
            show: true,
            fontSize: 14,
            fontWeight: 'bold'
          }
        },
        data: props.data,
        animationType: 'scale',
        animationEasing: 'elasticOut',
        animationDelay: function(idx) {
          return Math.random() * 200
        }
      }
    ],
    color: [
      '#2196F3', '#4CAF50', '#FF9800', '#9C27B0',
      '#00BCD4', '#8BC34A', '#FF5722', '#673AB7',
      '#009688', '#E91E63', '#3F51B5', '#CDDC39'
    ]
  }
}

function render() {
  if (!chartEl.value || !hasData.value) return
  
  if (!chart) {
    chart = echarts.init(chartEl.value, 'macarons', {
      renderer: 'canvas',
      locale: 'FA'
    })
  }
  
  chart.setOption(buildOption(), true)
  
  // Add resize observer for responsive chart
  if (!window.chartResizeHandlers) {
    window.chartResizeHandlers = new Set()
  }
  const resizeHandler = () => chart.resize()
  window.chartResizeHandlers.add(resizeHandler)
}

function handleResize() {
  if (chart) {
    chart.resize()
  }
}

onMounted(async () => {
  if (chartEl.value) {
    await nextTick()
    render()
  }
  window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
  if (chart) {
    chart.dispose()
    chart = null
  }
})

watch(
  () => [props.data, props.title, props.height, props.donut, props.showLegend],
  async () => {
    await nextTick()
    render()
  },
  { deep: true }
)
</script>

<style scoped>
.chart-container {
  width: 100%;
  position: relative;
}

.chart {
  width: 100%;
  height: 100%;
  min-height: 260px;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  padding: 40px 20px;
  text-align: center;
}

.legend-stats {
  border-top: 1px solid rgba(var(--v-border-color), 0.2);
  padding-top: 12px;
}

/* Smooth transitions */
.chart {
  transition: all 0.3s ease;
}

/* Responsive */
@media (max-width: 960px) {
  .chart {
    min-height: 220px;
  }
  
  :deep(.echarts-legend) {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    width: 100% !important;
    text-align: center;
  }
}
</style>