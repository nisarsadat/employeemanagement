<!-- src/components/charts/FormatsPieChart.vue -->
<template>
  <v-card rounded="xl" class="shadow-lg glass-card" :class="{ 'loading-state': localLoading }">
    <!-- Loading Overlay -->
    <div v-if="localLoading" class="loading-overlay">
      <v-progress-circular indeterminate color="primary" size="48" />
      <div class="text-body-2 mt-4 text-medium-emphasis">{{ t('در حال بارگذاری...') }}</div>
    </div>

    <v-card-text class="pa-4">
      <!-- Header with Custom Controls -->
      <div class="d-flex align-center justify-space-between mb-4">
        <div class="flex-grow-1">
          <div class="text-h6 font-weight-bold text-primary mb-1 d-flex align-center gap-2">
            <v-icon size="24" color="primary">mdi-chart-pie</v-icon>
            {{ title }}
          </div>
          <div class="text-caption text-medium-emphasis d-flex align-center gap-2 flex-wrap">
            <v-icon size="14">mdi-information</v-icon>
            <span>{{ subtitle }}</span>
            <v-chip v-if="showCount" size="x-small" variant="tonal" color="primary">
              {{ t('تعداد') }}: {{ displayData.length }}
            </v-chip>
          </div>
        </div>

        <!-- Custom Controls -->
        <div class="d-flex align-center gap-2 flex-shrink-0">
          <!-- View Mode Toggle -->
          <v-btn-toggle
            v-model="viewMode"
            color="primary"
            density="compact"
            variant="outlined"
            rounded="xl"
            divided
          >
            <v-btn size="small" value="pie">
              <v-icon size="18">mdi-chart-pie</v-icon>
            </v-btn>
            <v-btn size="small" value="bar">
              <v-icon size="18">mdi-chart-bar</v-icon>
            </v-btn>
            <v-btn size="small" value="list">
              <v-icon size="18">mdi-format-list-bulleted</v-icon>
            </v-btn>
          </v-btn-toggle>

          <!-- Filter Controls -->
          <v-menu v-model="showFilterMenu" :close-on-content-click="false">
            <template v-slot:activator="{ props }">
              <v-btn icon size="small" variant="tonal" color="primary" rounded="lg" v-bind="props">
                <v-icon>mdi-filter</v-icon>
              </v-btn>
            </template>

            <v-card min-width="280" rounded="xl">
              <v-card-text>
                <div class="filter-options">
                  <div class="text-caption text-medium-emphasis mb-3">{{ t('فیلترها') }}</div>

                  <!-- Min Value Filter -->
                  <div class="mb-4">
                    <div class="text-caption mb-2">{{ t('حداقل تعداد فرم') }}</div>
                    <v-text-field
                      v-model="minValueFilter"
                      type="number"
                      density="compact"
                      variant="outlined"
                      hide-details
                      :placeholder="t('حداقل')"
                      rounded="lg"
                      min="0"
                    >
                      <template #append>
                        <v-icon size="16" @click="minValueFilter = null">mdi-close</v-icon>
                      </template>
                    </v-text-field>
                  </div>

                  <!-- Sort Options -->
                  <div class="mb-4">
                    <div class="text-caption mb-2">{{ t('مرتب‌سازی') }}</div>
                    <v-radio-group v-model="sortBy" hide-details density="compact">
                      <v-radio :label="t('بیشترین به کمترین')" value="desc" density="compact" />
                      <v-radio :label="t('کمترین به بیشترین')" value="asc" density="compact" />
                      <v-radio :label="t('حروف الفبا')" value="name" density="compact" />
                    </v-radio-group>
                  </div>

                  <!-- Show All Toggle -->
                  <v-switch
                    v-model="showAllZones"
                    :label="t('نمایش تمام ناحیه ها')"
                    color="primary"
                    density="compact"
                    hide-details
                  />
                </div>
              </v-card-text>

              <v-card-actions class="px-4 pb-4">
                <v-spacer />
                <v-btn variant="text" @click="resetFilters">
                  {{ t('بازنشانی') }}
                </v-btn>
                <v-btn color="primary" variant="flat" @click="applyFilters">
                  {{ t('اعمال') }}
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-menu>

          <!-- Refresh Button -->
          <v-btn
            icon
            size="small"
            variant="flat"
            color="primary"
            rounded="lg"
            @click="handleRefresh"
            :loading="localLoading"
            class="refresh-btn"
          >
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </div>
      </div>

      <!-- Stats Summary -->
      <div v-if="hasData" class="stats-summary mb-4">
        <div class="d-flex align-center justify-space-between flex-wrap gap-2">
          <div class="d-flex align-center gap-4">
            <div class="stat-item">
              <v-icon size="16" color="primary">mdi-sum</v-icon>
              <span class="text-caption">{{ t('مجموع') }}:</span>
              <span class="text-caption font-weight-medium">{{
                totalFormats.toLocaleString()
              }}</span>
            </div>
            <div class="stat-item">
              <v-icon size="16" color="success">mdi-chart-line</v-icon>
              <span class="text-caption">{{ t('میانگین') }}:</span>
              <span class="text-caption font-weight-medium">{{ averageFormats.toFixed(1) }}</span>
            </div>
            <div class="stat-item">
              <v-icon size="16" color="amber">mdi-trophy</v-icon>
              <span class="text-caption">{{ t('بیشترین') }}:</span>
              <span class="text-caption font-weight-medium">{{ maxZoneValue }}</span>
            </div>
          </div>

          <v-chip size="small" variant="tonal" color="primary">
            <v-icon start size="14">mdi-chart-pie</v-icon>
            {{ t('تعداد ناحیه ها') }}: {{ displayData.length }}
          </v-chip>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="chart-container" :style="{ height: chartHeight }">
        <!-- ✅ IMPORTANT: keep chart DOM alive (v-show instead of v-if) -->
        <div ref="chartEl" class="chart" v-show="viewMode !== 'list' && hasData" />

        <!-- List View -->
        <div v-show="viewMode === 'list' && hasData" class="list-view">
          <div class="list-header">
            <div class="text-subtitle-2 font-weight-medium">{{ t('ناحیه') }}</div>
            <div class="text-subtitle-2 font-weight-medium">{{ t('تعداد فرم') }}</div>
          </div>

          <div class="list-items">
            <div
              v-for="(item, index) in paginatedDataEnhanced"
              :key="`${item.name}-${index}`"
              class="list-item"
              :class="{ highlighted: index + (currentPage - 1) * itemsPerPage < 3 }"
              @click="selectZone(item)"
            >
              <div class="zone-info">
                <div class="rank-circle" :class="`rank-${index + 1}`">
                  {{ index + 1 + (currentPage - 1) * itemsPerPage }}
                </div>

                <div class="zone-name">
                  <div class="name">{{ item.name || t('نامعلوم') }}</div>
                  <div class="text-caption text-medium-emphasis">
                    {{ t('رتبه') }} {{ index + 1 + (currentPage - 1) * itemsPerPage }}
                  </div>
                </div>
              </div>

              <div class="zone-count">
                <span class="count">{{ Number(item.value || 0).toLocaleString() }}</span>
                <v-progress-linear
                  :model-value="maxValue ? (Number(item.value) / maxValue) * 100 : 0"
                  height="6"
                  rounded
                  :color="item.color"
                  class="mt-1"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-show="!hasData" class="empty-state">
          <v-icon size="80" color="grey-lighten-2" class="mb-3">mdi-chart-donut-variant</v-icon>
          <div class="text-body-1 text-medium-emphasis mb-2">{{ emptyText }}</div>
          <div class="text-caption text-medium-emphasis mb-4">
            {{ t('برای مشاهده دیتا دکمه تازه‌سازی را بزنید') }}
          </div>
          <v-btn variant="flat" color="primary" rounded="xl" @click="handleRefresh">
            <v-icon start>mdi-refresh</v-icon>
            {{ t('تازه‌سازی دیتا') }}
          </v-btn>
        </div>
      </div>

      <!-- List Pagination Controls -->
      <div v-if="hasData && viewMode === 'list'" class="chart-controls mt-4">
        <div class="d-flex align-center justify-space-between">
          <div class="text-caption text-medium-emphasis">
            {{ t('نمایش') }} {{ showingFrom }}-{{ showingTo }} {{ t('از') }}
            {{ displayData.length }} {{ t('ناحیه') }}
          </div>

          <div class="d-flex align-center gap-2">
            <v-btn
              icon
              size="small"
              variant="tonal"
              color="grey"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              <v-icon>{{ isRtl ? 'mdi-chevron-right' : 'mdi-chevron-left' }}</v-icon>
            </v-btn>

            <div class="text-caption font-weight-medium">
              {{ t('صفحه') }} {{ currentPage }} {{ t('از') }} {{ totalPages }}
            </div>

            <v-btn
              icon
              size="small"
              variant="tonal"
              color="grey"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              <v-icon>{{ isRtl ? 'mdi-chevron-left' : 'mdi-chevron-right' }}</v-icon>
            </v-btn>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div v-if="hasData && viewMode !== 'list' && showLegend" class="legend-container mt-4">
        <div class="d-flex align-center justify-space-between flex-wrap gap-2">
          <div class="text-caption text-medium-emphasis">
            {{ t('رنگ‌ها نشان‌دهنده زون‌های مختلف هستند') }}
          </div>
          <div class="d-flex align-center gap-2">
            <v-btn
              size="x-small"
              variant="text"
              color="primary"
              @click="downloadChart"
              class="px-2"
            >
              <v-icon start size="14">mdi-download</v-icon>
              {{ t('ذخیره') }}
            </v-btn>
            <v-btn
              size="x-small"
              variant="text"
              color="primary"
              @click="fullscreenChart"
              class="px-2"
            >
              <v-icon start size="14">mdi-fullscreen</v-icon>
              {{ t('تمام صفحه') }}
            </v-btn>
          </div>
        </div>
      </div>
    </v-card-text>

    <!-- Toast Notification -->
    <v-snackbar v-model="showToast" :timeout="2000" color="success" rounded="xl" location="top">
      <div class="d-flex align-center gap-2">
        <v-icon>mdi-check-circle</v-icon>
        <span>{{ toastMessage }}</span>
      </div>
    </v-snackbar>
  </v-card>
</template>

<script setup>
  import { ref, shallowRef, onMounted, onBeforeUnmount, watch, computed, nextTick } from 'vue';
  import { useI18n } from 'vue-i18n';
  import * as echarts from 'echarts';
  import 'echarts/theme/macarons';

  const { t, locale } = useI18n();
  const isRtl = computed(() => String(locale.value).toLowerCase().startsWith('fa'));
  const isFaMode = computed(() => {
    const l = String(locale.value || '').toLowerCase();
    return l.startsWith('fa') || l.startsWith('ps');
  });

  const FA_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

  function toFaDigits(input) {
    if (input === null || input === undefined) return '';
    return String(input).replace(/\d/g, (d) => FA_DIGITS[Number(d)]);
  }

  function numUi(input) {
    return isFaMode.value ? toFaDigits(String(input)) : String(input);
  }

  function fmtNum(input) {
    return numUi(Number(input || 0).toLocaleString());
  }

  const props = defineProps({
    title: { type: String, default: 'توزیع فرم‌ها بر اساس ناحیه' },
    subtitle: { type: String, default: 'تمام ناحیه ها بر اساس تعداد فرم' },
    emptyText: { type: String, default: 'هیچ دیتا موجود نیست' },
    data: { type: Array, default: () => [] },
    height: { type: [Number, String], default: 400 },
    showLegend: { type: Boolean, default: true },
    showCount: { type: Boolean, default: true },
    loading: { type: Boolean, default: false },
  });

  const emit = defineEmits(['refresh', 'zone-click', 'data-updated']);

  // Reactive State
  const chartEl = ref(null);
  const chart = shallowRef(null);
  const localLoading = ref(false);
  const showFilterMenu = ref(false);
  const showToast = ref(false);
  const toastMessage = ref('');
  const viewMode = ref('pie'); // 'pie', 'bar', 'list'
  const minValueFilter = ref(null);
  const sortBy = ref('desc'); // 'desc', 'asc', 'name'
  const showAllZones = ref(true);
  const currentPage = ref(1);
  const itemsPerPage = ref(10);
  let resizeObserver = null;

  // Data Processing
  const safeData = computed(() => {
    const arr = Array.isArray(props.data) ? props.data : [];
    return arr.map((d, idx) => ({
      name: d?.name || t('نامعلوم'),
      value: Number(d?.value) || 0,
      color: d?.itemStyle?.color || getZoneColor(idx),
      raw: d,
    }));
  });

  // Apply filters
  const filteredData = computed(() => {
    let data = [...safeData.value];

    if (minValueFilter.value !== null && minValueFilter.value !== '') {
      const minValue = Number(minValueFilter.value);
      if (!isNaN(minValue)) {
        data = data.filter((item) => item.value >= minValue);
      }
    }

    return data;
  });

  // Sort data
  const sortedData = computed(() => {
    const data = [...filteredData.value];

    switch (sortBy.value) {
      case 'asc':
        return data.sort((a, b) => a.value - b.value);
      case 'name':
        return data.sort((a, b) => a.name.localeCompare(b.name, 'fa'));
      case 'desc':
      default:
        return data.sort((a, b) => b.value - a.value);
    }
  });

  // Display data (chart modes can be limited)
  const displayData = computed(() => {
    if (!showAllZones.value && viewMode.value !== 'list') {
      return sortedData.value.slice(0, 15);
    }
    return sortedData.value;
  });

  // Statistics
  const totalFormats = computed(() => displayData.value.reduce((sum, item) => sum + item.value, 0));

  const averageFormats = computed(() => {
    if (displayData.value.length === 0) return 0;
    return totalFormats.value / displayData.value.length;
  });

  const maxValue = computed(() => {
    if (displayData.value.length === 0) return 1;
    return Math.max(...displayData.value.map((item) => item.value), 1);
  });

  const maxZoneValue = computed(() => {
    if (displayData.value.length === 0) return '0';
    const maxItem = displayData.value.reduce((max, item) => (item.value > max.value ? item : max));
    return maxItem.value.toLocaleString();
  });

  // Enhanced data with percentages (for charts)
  const enhancedData = computed(() => {
    const total = totalFormats.value || 1;
    return displayData.value.map((item) => ({
      ...item,
      percentage: ((item.value / total) * 100).toFixed(1),
    }));
  });

  const hasData = computed(() => displayData.value.length > 0);

  // Pagination (list view)
  const totalPages = computed(() =>
    Math.max(1, Math.ceil(displayData.value.length / itemsPerPage.value))
  );

  const showingFrom = computed(() => {
    if (!displayData.value.length) return 0;
    return (currentPage.value - 1) * itemsPerPage.value + 1;
  });

  const showingTo = computed(() => {
    return Math.min(currentPage.value * itemsPerPage.value, displayData.value.length);
  });

  const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return displayData.value.slice(start, end);
  });

  // make paginated data already contains percentage (for list chips)
  const paginatedDataEnhanced = computed(() => {
    const total = totalFormats.value || 1;
    return paginatedData.value.map((item) => ({
      ...item,
      percentage: ((item.value / total) * 100).toFixed(1),
    }));
  });

  const chartHeight = computed(() => {
    if (viewMode.value === 'list') return '420px';
    return typeof props.height === 'number' ? `${props.height}px` : props.height;
  });

  // Helper Functions
  function getZoneColor(index) {
    const colors = [
      '#2196F3',
      '#4CAF50',
      '#FF9800',
      '#9C27B0',
      '#00BCD4',
      '#8BC34A',
      '#FF5722',
      '#673AB7',
      '#009688',
      '#E91E63',
      '#3F51B5',
      '#CDDC39',
      '#FF5252',
      '#448AFF',
      '#FF4081',
      '#7C4DFF',
      '#00E676',
      '#FF9100',
      '#2962FF',
      '#D500F9',
    ];
    return colors[index % colors.length];
  }

  function getPercentageColor(percentage) {
    const perc = Number(percentage);
    if (perc >= 20) return 'success';
    if (perc >= 10) return 'primary';
    if (perc >= 5) return 'warning';
    return 'grey';
  }

  // Chart Rendering
  async function renderChart() {
    if (!chartEl.value) return;
    if (!hasData.value) return;
    if (viewMode.value === 'list') return;

    await nextTick();

    // init once (DOM is kept alive via v-show)
    if (!chart.value) {
      chart.value = echarts.init(chartEl.value, 'macarons', {
        renderer: 'canvas',
        locale: 'FA',
      });
    }

    chart.value.setOption(buildChartOption(), { notMerge: true, lazyUpdate: true });

    // ✅ ensure correct size after layout settles
    requestAnimationFrame(() => {
      if (chart.value) chart.value.resize();
    });
  }

  function buildChartOption() {
    const isBarChart = viewMode.value === 'bar';

    const baseOptions = {
      backgroundColor: 'transparent',
      tooltip: {
        trigger: isBarChart ? 'axis' : 'item',
        backgroundColor: 'rgba(255, 255, 255, 0.95)',
        borderColor: 'rgba(0, 0, 0, 0.1)',
        borderWidth: 1,
        textStyle: {
          color: 'rgb(var(--v-theme-on-surface))',
          fontFamily: 'Vazirmatn, sans-serif',
          fontSize: 12,
        },
        formatter: isBarChart ? barTooltipFormatter : pieTooltipFormatter,
      },
      grid: isBarChart
        ? {
            left: '3%',
            right: '4%',
            bottom: '10%',
            top: '10%',
            containLabel: true,
          }
        : undefined,
    };

    if (isBarChart) {
      return {
        ...baseOptions,
        xAxis: {
          type: 'category',
          data: enhancedData.value.map((item) => item.name),
          axisLabel: {
            interval: 0,
            rotate: 45,
            textStyle: {
              fontFamily: 'Vazirmatn, sans-serif',
              fontSize: 11,
              color: 'rgb(var(--v-theme-on-surface-variant))',
            },
          },
        },
        yAxis: {
          type: 'value',
          axisLabel: {
            textStyle: {
              fontFamily: 'Vazirmatn, sans-serif',
              color: 'rgb(var(--v-theme-on-surface-variant))',
            },
          },
        },
        series: [
          {
            name: t('تعداد فرم'),
            type: 'bar',
            data: enhancedData.value.map((item, index) => ({
              value: item.value,
              itemStyle: {
                color: item.color || getZoneColor(index),
                borderRadius: [4, 4, 0, 0],
              },
              name: item.name,
              percentage: item.percentage,
            })),
            label: {
              show: true,
              position: 'top',
              formatter: (p) => fmtNum(p?.value),
              fontFamily: 'Vazirmatn, sans-serif',
              fontSize: 11,
              color: 'rgb(var(--v-theme-on-surface))',
            },
            barWidth: '60%',
            itemStyle: {
              borderColor: 'rgba(255, 255, 255, 0.3)',
              borderWidth: 1,
            },
            emphasis: {
              itemStyle: {
                shadowBlur: 20,
                shadowColor: 'rgba(0, 0, 0, 0.3)',
              },
            },
          },
        ],
        dataZoom:
          enhancedData.value.length > 10
            ? [
                { type: 'inside', start: 0, end: 100 },
                { show: true, type: 'slider', top: '90%', start: 0, end: 100 },
              ]
            : undefined,
      };
    }

    // Pie Chart
    return {
      ...baseOptions,
      legend: props.showLegend
        ? {
            orient: 'vertical',
            left: 'left',
            top: 'middle',
            textStyle: {
              color: 'rgb(var(--v-theme-on-surface))',
              fontSize: 11,
              fontFamily: 'Vazirmatn, sans-serif',
            },
            itemWidth: 12,
            itemHeight: 12,
            itemGap: 6,
            formatter: function (name) {
              const item = enhancedData.value.find((d) => d.name === name);
              if (!item) return name;
              const s = `${name} - ${Number(item.value).toLocaleString()} (${item.percentage}%)`;
              return numUi(s);
            },

            type: 'scroll',
            pageIconColor: 'rgb(var(--v-theme-primary))',
            pageIconInactiveColor: 'rgba(var(--v-theme-on-surface), 0.3)',
            pageTextStyle: {
              color: 'rgb(var(--v-theme-on-surface))',
            },
          }
        : undefined,
      series: [
        {
          name: props.title,
          type: 'pie',
          radius: ['40%', '70%'],
          center: props.showLegend ? ['60%', '50%'] : ['50%', '50%'],
          avoidLabelOverlap: true,
          itemStyle: {
            borderRadius: 6,
            borderColor: 'rgb(var(--v-theme-surface))',
            borderWidth: 2,
          },
          label: {
            show: true,
            formatter: (p) => `${p.name}: ${fmtNum(p.value)}`,
            color: 'rgb(var(--v-theme-on-surface))',
            fontFamily: 'Vazirmatn, sans-serif',
            fontSize: 11,
            fontWeight: 500,
          },
          labelLine: {
            show: true,
            length: 15,
            length2: 20,
            smooth: true,
          },
          emphasis: {
            scale: true,
            scaleSize: 10,
            itemStyle: {
              shadowBlur: 20,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.2)',
            },
          },
          data: enhancedData.value.map((item, index) => ({
            name: item.name,
            value: item.value,
            itemStyle: {
              color: item.color || getZoneColor(index),
            },
            percentage: item.percentage,
          })),
          animationType: 'scale',
          animationEasing: 'elasticOut',
        },
      ],
    };
  }

  function pieTooltipFormatter(params) {
    const item = enhancedData.value.find((d) => d.name === params.name);
    return `
    <div style="padding: 12px; min-width: 200px; background: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15)">
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px">
        <div style="width: 12px; height: 12px; border-radius: 2px; background: ${
          params.color
        }"></div>
        <div style="font-weight: 600; font-size: 13px; color: #333">${params.name}</div>
      </div>
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="color: #666">${t('تعداد فرم')}:</div>
        <div style="font-weight: 600; color: #2196F3">${fmtNum(params.value || 0)}</div>
      </div>
      <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px">
        <div style="font-weight: 600; color: #4CAF50">${item?.percentage || '0'}%</div>
      </div>
    </div>
  `;
  }

  function barTooltipFormatter(params) {
    const data = params?.[0];
    const perc = data?.data?.percentage || '0';
    return `
    <div style="padding: 12px; min-width: 180px; background: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15)">
      <div style="font-weight: 600; font-size: 13px; color: #333; margin-bottom: 8px">${
        data?.name || ''
      }</div>
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="color: #666">${t('تعداد')}:</div>
        <div style="font-weight: 600; color: #2196F3">${fmtNum(data?.value || 0)}</div>
      </div>
      <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px">
        <div style="font-weight: 600; color: #4CAF50">${numUi(perc + '%')}</div>
      </div>
    </div>
  `;
  }

  // Event Handlers
  async function handleRefresh() {
    localLoading.value = true;
    try {
      emit('refresh'); // ✅ emit is not awaitable
      showToastMessage(t('دیتا با موفقیت بروزرسانی شد'));
    } catch (error) {
      console.error('Refresh error:', error);
      showToastMessage(t('خطا در بروزرسانی دیتا'), 'error');
    } finally {
      localLoading.value = false;
    }
  }

  function selectZone(zone) {
    emit('zone-click', zone);
  }

  function applyFilters() {
    showFilterMenu.value = false;
    currentPage.value = 1;
    showToastMessage(t('فیلترها اعمال شدند'));
    renderChart();
  }

  function resetFilters() {
    minValueFilter.value = null;
    sortBy.value = 'desc';
    showAllZones.value = true;
    currentPage.value = 1;
    renderChart();
  }

  function downloadChart() {
    if (!chart.value) return;

    const url = chart.value.getDataURL({
      type: 'png',
      pixelRatio: 2,
      backgroundColor: '#fff',
    });

    const link = document.createElement('a');
    link.href = url;
    link.download = `formats-chart-${new Date().toISOString().split('T')[0]}.png`;
    link.click();

    showToastMessage(t('چارت با موفقیت ذخیره شد'));
  }

  function fullscreenChart() {
    if (!chart.value) return;
    chart.value.resize();
  }

  function showToastMessage(message) {
    toastMessage.value = message;
    showToast.value = true;
  }

  // Lifecycle
  onMounted(async () => {
    await nextTick();

    // render if already has data
    if (hasData.value && viewMode.value !== 'list') {
      await renderChart();
    }

    // resize observer for container changes
    if (chartEl.value && 'ResizeObserver' in window) {
      resizeObserver = new ResizeObserver(() => {
        if (chart.value) chart.value.resize();
      });
      resizeObserver.observe(chartEl.value);
    }
  });

  onBeforeUnmount(() => {
    if (resizeObserver) resizeObserver.disconnect();
    if (chart.value) chart.value.dispose();
  });
  // Watchers
  watch(
    () => [viewMode.value, showAllZones.value, minValueFilter.value, sortBy.value],
    async () => {
      currentPage.value = 1;
      await renderChart();
    }
  );

  watch(
    () => props.data,
    async () => {
      // ✅ render even if it was empty before and now has data
      await renderChart();
    },
    { deep: true }
  );

  watch(
    () => props.loading,
    (newVal) => {
      if (!newVal) localLoading.value = false;
    }
  );

  // ✅ if data becomes available after mount
  watch(hasData, async (v) => {
    if (v) await renderChart();
  });

  // list page changes scroll
  watch(currentPage, () => {
    const container = document.querySelector('.list-view .list-items');
    if (container) container.scrollTop = 0;
  });
  watch(
    () => locale.value,
    async () => {
      await renderChart();
    }
  );
</script>

<style scoped>
  .glass-card {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    position: relative;
  }

  .loading-state {
    opacity: 0.8;
  }

  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: inherit;
  }

  .chart-container {
    width: 100%;
    position: relative;
    transition: all 0.3s ease;
  }

  .chart {
    width: 100%;
    height: 90%;
    min-height: 300px;
  }

  .stats-summary {
    padding: 12px;
    background: rgba(var(--v-theme-surface-variant), 0.05);
    border-radius: 12px;
    border: 1px solid rgba(var(--v-border-color), 0.1);
  }

  .stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  /* List View Styles */
  .list-view {
    height: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border-radius: 12px;
    border: 1px solid rgba(var(--v-border-color), 0.1);
    background: rgba(var(--v-theme-surface), 0.5);
  }

  .list-header {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 16px;
    padding: 12px 16px;
    background: rgba(var(--v-theme-surface-variant), 0.1);
    border-bottom: 1px solid rgba(var(--v-border-color), 0.1);
  }

  .list-items {
    flex: 1;
    overflow-y: auto;
    max-height: 350px;
  }

  .list-item {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 16px;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(var(--v-border-color), 0.05);
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .list-item:hover {
    background: rgba(var(--v-theme-primary), 0.03);
    transform: translateX(4px);
  }

  .list-item.highlighted {
    background: linear-gradient(90deg, rgba(var(--v-theme-primary), 0.05), transparent);
  }

  .zone-info {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .rank-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    font-size: 0.875rem;
    flex-shrink: 0;
  }

  .rank-1 {
    background: linear-gradient(135deg, #ffd700, #ffa500);
    box-shadow: 0 0 15px rgba(255, 215, 0, 0.4);
  }

  .rank-2 {
    background: linear-gradient(135deg, #c0c0c0, #808080);
    box-shadow: 0 0 15px rgba(192, 192, 192, 0.4);
  }

  .rank-3 {
    background: linear-gradient(135deg, #cd7f32, #a0522d);
    box-shadow: 0 0 15px rgba(205, 127, 50, 0.4);
  }

  .rank-4,
  .rank-5,
  .rank-6,
  .rank-7,
  .rank-8,
  .rank-9,
  .rank-10 {
    background: linear-gradient(135deg, #2196f3, #1976d2);
    box-shadow: 0 0 10px rgba(33, 150, 243, 0.2);
  }

  .zone-name .name {
    font-weight: 500;
    margin-bottom: 2px;
  }

  .zone-count {
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .zone-count .count {
    font-weight: 600;
    font-size: 1rem;
    text-align: center;
  }

  .zone-percentage {
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }

  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 300px;
    padding: 40px 20px;
    text-align: center;
  }

  .refresh-btn {
    transition: all 0.3s ease;
  }

  .refresh-btn:hover {
    transform: rotate(180deg);
  }

  .hover-lift {
    transition: all 0.3s ease;
  }

  .hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1) !important;
  }

  /* Scrollbar Styling */
  .list-items::-webkit-scrollbar {
    width: 6px;
  }

  .list-items::-webkit-scrollbar-track {
    background: rgba(var(--v-border-color), 0.1);
    border-radius: 3px;
  }

  .list-items::-webkit-scrollbar-thumb {
    background: rgba(var(--v-theme-primary), 0.3);
    border-radius: 3px;
  }

  .list-items::-webkit-scrollbar-thumb:hover {
    background: rgba(var(--v-theme-primary), 0.5);
  }

  /* Chart Controls */
  .chart-controls {
    padding: 12px;
    background: rgba(var(--v-theme-surface-variant), 0.05);
    border-radius: 12px;
    border: 1px solid rgba(var(--v-border-color), 0.1);
  }

  /* Responsive */
  @media (max-width: 960px) {
    .list-header {
      grid-template-columns: 1fr 1fr 1fr;
      font-size: 0.75rem;
    }

    .list-item {
      grid-template-columns: 1fr 1fr 1fr;
      font-size: 0.875rem;
    }

    .zone-info {
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
    }

    .rank-circle {
      width: 24px;
      height: 24px;
      font-size: 0.75rem;
    }

    .chart {
      min-height: 250px;
    }
  }

  @media (max-width: 600px) {
    .stats-summary .stat-item {
      flex: 1;
      min-width: 100px;
    }

    .view-mode-toggle {
      flex-wrap: wrap;
    }

    .list-view {
      max-height: 300px;
    }
  }
</style>
