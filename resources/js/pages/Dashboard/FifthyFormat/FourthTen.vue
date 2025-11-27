  <!-- src/views/dashboard/FifthyFormatThreeDashboard.vue -->
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
            :label="t('از تاریخ')"
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
            :label="t('تا تاریخ')"
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
            :label="t('تقسیم اوقات')"
            density="compact"
            variant="outlined"
          />
        </v-col>

        <v-col cols="12" md="9">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو')"
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
            {{ t('روزانه') }}
          </v-btn>
          <v-btn
            variant="flat"
            color="primary"
            class="mr-2"
            :class="btnClass('monthly')"
            @click="quickRange('monthly')"
          >
            {{ t('ماهانه') }}
          </v-btn>
          <v-btn
            variant="flat"
            color="primary"
            :class="btnClass('yearly')"
            @click="quickRange('yearly')"
          >
            {{ t('سالانه') }}
          </v-btn>
        </v-col>
      </v-row>

      <!-- ============================ Cards ============================ -->
      <v-row>
        <v-col v-for="c in repo.cards" :key="c.key" cols="12" sm="6" md="3">
          <v-card class="pa-4 h-100 soft-card" elevation="1">
            <div class="text-overline text-primary">{{ c.label }}</div>
            <div class="text-h4 font-weight-bold mt-1">{{ c.total }}</div>
            <div class="text-caption text-medium-emphasis">{{ t('تمامی ریکارد ها') }}</div>
          </v-card>
        </v-col>
      </v-row>

      <!-- ============================ Main Charts (REPLACED SECTION) ============================ -->
      <v-row class="mt-2">
        <!-- BEAUTIFUL: Dual-trend comparison + format selectors (with defaults) -->
        <v-col cols="12" md="8">
          <v-card class="pa-3 soft-card" elevation="1">
            <div class="d-flex align-center justify-space-between mb-3">
              <div>
                <div class="text-subtitle-1 font-weight-medium">
                  {{ t(' مقایسه') }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ t('تقسیم اوقات') }}: {{ granularityLabel }}
                </div>
              </div>
              <div class="d-flex align-center" style="gap: 8px; max-width: 520px; width: 100%">
                <v-select
                  v-model="trendA"
                  :items="formatOptions"
                  item-title="label"
                  item-value="key"
                  :label="t('فارمت اصلی')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                  class="flex-1-1"
                />
                <v-select
                  v-model="trendB"
                  :items="formatOptions"
                  item-title="label"
                  item-value="key"
                  :label="t('مقایسه')"
                  density="compact"
                  variant="outlined"
                  hide-details="auto"
                  class="flex-1-1"
                />
              </div>
            </div>

            <!-- Dual line (A vs B) with gradient & markers -->
            <v-chart :option="compareOption" autoresize style="height: 380px" />
          </v-card>
        </v-col>

        <!-- Donut composition (by format) -->
        <v-col cols="12" md="4">
          <v-card class="pa-3 soft-card" elevation="1">
            <div class="text-subtitle-1 font-weight-medium mb-2">
              {{ t('ترکیب') }}
            </div>
            <div class="relative">
              <v-chart :option="donutOption" autoresize style="height: 380px" />
              <div class="donut-center" v-if="donutTotal > 0">
                <div class="donut-total">{{ donutTotal }}</div>
                <div class="text-caption text-medium-emphasis">{{ t('همه') }}</div>
              </div>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <!-- ============================ Secondary Charts ============================ -->
      <v-row class="mt-2">
        <!-- Top Zones (default selected) -->
        <v-col cols="12" md="6">
          <v-card class="pa-3 soft-card" elevation="1">
            <div class="d-flex align-center justify-space-between mb-1">
              <div class="text-subtitle-1 font-weight-medium">{{ t('زون های برتر') }}</div>
              <v-select
                v-model="selectedZonesFormat"
                :items="formatOptions"
                item-title="label"
                item-value="key"
                :label="t('فارمت')"
                density="compact"
                variant="outlined"
                clearable="false"
                style="max-width: 260px"
              />
            </div>
            <v-chart :option="zonesBarOption" autoresize style="height: 320px" />
          </v-card>
        </v-col>

        <!-- Top Provinces (default selected) -->
        <v-col cols="12" md="6">
          <v-card class="pa-3 soft-card" elevation="1">
            <div class="d-flex align-center justify-space-between mb-1">
              <div class="text-subtitle-1 font-weight-medium">{{ t('ولایت های برتر') }}</div>
              <v-select
                v-model="selectedProvFormat"
                :items="formatOptions"
                item-title="label"
                item-value="key"
                :label="t('فارمت')"
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
        <!-- Breakdown (defaults applied) -->
        <v-col cols="12" md="6">
          <v-card class="pa-3 soft-card" elevation="1">
            <div class="d-flex align-center justify-space-between mb-1">
              <div class="text-subtitle-1 font-weight-medium">{{ t('تقسیم کتگوری ها') }}</div>
              <div class="d-flex align-center" style="gap: 8px">
                <v-select
                  v-model="repo.selectedFormat"
                  :items="formatOptions"
                  item-title="label"
                  item-value="key"
                  :label="t('فارمت')"
                  density="compact"
                  variant="outlined"
                  clearable="false"
                  style="max-width: 220px"
                  @update:model-value="onBreakdownFormatChange"
                />
                <v-select
                  v-model="repo.selectedField"
                  :items="fieldOptions"
                  :label="t('نوع')"
                  density="compact"
                  variant="outlined"
                  :disabled="!repo.selectedFormat"
                  clearable="false"
                  style="max-width: 220px"
                  @update:model-value="repo.fetchBreakdown"
                />
              </div>
            </div>
            <v-chart :option="breakdownPieOption" autoresize style="height: 320px" />
          </v-card>
        </v-col>

        <!-- Top resources (default selected) -->
        <v-col cols="12" md="6">
          <v-card class="pa-3 soft-card" elevation="1">
            <div class="d-flex align-center justify-space-between mb-1">
              <div class="text-subtitle-1 font-weight-medium">
                {{ t('منابع برتر') }}
              </div>
              <v-select
                v-model="selectedBucketFormat"
                :items="formatOptions"
                item-title="label"
                item-value="key"
                :label="t('فیلتر فارمت')"
                density="compact"
                variant="outlined"
                clearable="false"
                style="max-width: 260px"
              />
            </div>
            <v-chart :option="resourcesBarOption" autoresize style="height: 320px" />
          </v-card>
        </v-col>
      </v-row>

      <!-- ============================ Recent ============================ -->
      <v-row class="mt-2">
        <v-col cols="12">
          <v-card class="pa-3 soft-card" elevation="1">
            <div class="text-subtitle-1 font-weight-medium mb-2">{{ t('ریکارد های اخیر') }}</div>
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
                <span v-if="item.extra?.organ">{{ t('Organ') }}: {{ item.extra.organ }} </span>
                <span v-if="item.extra?.jorm_type">
                  | {{ t('جرم') }}: {{ item.extra.jorm_type }}
                </span>
                <span v-if="item.extra?.people_type">
                  | {{ t('شخص') }}: {{ item.extra.people_type }}
                </span>
                <span v-if="item.extra?.type"> | {{ t('نوع') }}: {{ item.extra.type }} </span>
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
    /* ECharts (tree-shaken, clean palette & smooth charts) */
    import { use } from 'echarts/core';
    import { CanvasRenderer } from 'echarts/renderers';
    import { BarChart, LineChart, PieChart } from 'echarts/charts';
    import {
      GridComponent,
      TooltipComponent,
      LegendComponent,
      DatasetComponent,
      DataZoomComponent,
    } from 'echarts/components';
    use([
      CanvasRenderer,
      BarChart,
      LineChart,
      PieChart,
      GridComponent,
      TooltipComponent,
      LegendComponent,
      DatasetComponent,
      DataZoomComponent,
    ]);

    import VChart from 'vue-echarts';
    import { ref, computed, onMounted, watch } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useFifthyFormatThreeDashboardRepository } from '@/stores/FifthyFormatThreeDashboardRepository';
    import { useCalendar } from '../../../../composables/useCalendar'

    const repo = useFifthyFormatThreeDashboardRepository();
    const { t, locale } = useI18n();
    const { calendar } = useCalendar();

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
        ({ daily: t('روزانه'), monthly: t('ماهانه'), yearly: t('سالانه') }[repo.granularity] ||
        t('روزانه'))
    );

    /* Formats list (sync with backend keys & labels) */
    const formatOptions = [
      { key: 'badmashano', label: t('بد ماش ها') },
      { key: 'emarat_office', label: t('دوایر دولتیو امارتی') },
      { key: 'hostel', label: t('خواب گاه ها') },
      { key: 'hotel', label: t('هوتل ها و رستورانت ها') },
      { key: 'madrasa', label: t('مدرسه ها') },
      { key: 'mashkok_people', label: t('افراد مشکوک') },
      { key: 'mujahedino', label: t('مجاهدین') },
      { key: 'old_jahadi', label: t('افراد جهادی سابقه') },
      { key: 'sarayano', label: t('سرای ها') },
      { key: 'tujaran', label: t('تجار ها') },
    ];

    /* Defaults for dropdowns (always have a selected value) */
    const defaultFormatKey = formatOptions[0].key;
    const trendA = ref(defaultFormatKey);
    const trendB = ref(formatOptions[1]?.key || defaultFormatKey);
    const selectedZonesFormat = ref(defaultFormatKey);
    const selectedProvFormat = ref(defaultFormatKey);
    const selectedBucketFormat = ref(defaultFormatKey);

    /* Breakdown selectable fields */
    const formatFieldMap = {
      badmashano: ['jorm_type', 'job', 'old_job', 'supplier'],
      emarat_office: ['home_type', 'responsible_office', 'tahdid_mutawajeh'],
      hostel: ['hostel_type', 'people_type'],
      hotel: ['type'],
      madrasa: ['madrasa_type', 'mazhab', 'tarafdari_type_responsible'],
      mashkok_people: ['now_job', 'old_job'],
      mujahedino: ['organ', 'duty_type', 'now_job'],
      old_jahadi: ['tanzim', 'now_job', 'old_job', 'supplier'],
      sarayano: ['saray_type'],
      tujaran: ['organ', 'now_job', 'people_type'],
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
      if (calendar.value === 'jalali') ca = 'persian';
      else if (calendar.value === 'hijri') ca = 'islamic-umalqura';
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
        // ensure defaults after data arrives
        if (!trendA.value) trendA.value = defaultFormatKey;
        if (!trendB.value) trendB.value = formatOptions[1]?.key || defaultFormatKey;
        if (!selectedZonesFormat.value) selectedZonesFormat.value = defaultFormatKey;
        if (!selectedProvFormat.value) selectedProvFormat.value = defaultFormatKey;
        if (!selectedBucketFormat.value) selectedBucketFormat.value = defaultFormatKey;
        ensureBreakdownDefaults();
        repo.fetchBreakdown();
      });
    }
    function onBreakdownFormatChange() {
      // set default field when format changes
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
    watch(calendar, () => {
      /* formatting only */
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

    /* Helpers to extract a single series by key */
    function seriesForKey(key) {
      const s = (repo.series || []).find((x) => x.key === key);
      return s ? s.points || [] : [];
    }
    const allCategories = computed(() => {
      const set = new Set();
      (repo.series || []).forEach((s) => (s.points || []).forEach((p) => set.add(String(p.date))));
      return Array.from(set).sort();
    });

    /* BEAUTIFUL dual-trend comparison option */
    const compareOption = computed(() => {
      const cats = allCategories.value;
      const aPts = seriesForKey(trendA.value);
      const bPts = seriesForKey(trendB.value);

      const mapA = Object.create(null);
      const mapB = Object.create(null);
      aPts.forEach((p) => (mapA[String(p.date)] = Number(p.count || 0)));
      bPts.forEach((p) => (mapB[String(p.date)] = Number(p.count || 0)));

      return {
        color: [palette[0], palette[3]],
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'line' },
          formatter: (params) => {
            if (!params?.length) return '';
            const x = params[0].axisValue;
            const title = formatForCalendar(x);
            const lines = params.map(
              (p) => `${p.marker} ${p.seriesName}: <b>${(p.value ?? 0) | 0}</b>`
            );
            return `<div style="margin-bottom:4px"><b>${title}</b></div>${lines.join('<br/>')}`;
          },
        },
        legend: { top: 0, right: isRtl.value ? 'auto' : 0, left: isRtl.value ? 0 : 'auto' },
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
        series: [
          {
            name: formatOptions.find((f) => f.key === trendA.value)?.label || 'A',
            type: 'line',
            smooth: true,
            symbol: 'circle',
            symbolSize: 5,
            lineStyle: { width: 2.4 },
            areaStyle: { opacity: 0.16 },
            emphasis: { focus: 'series' },
            data: cats.map((d) => mapA[d] ?? 0),
          },
          {
            name: formatOptions.find((f) => f.key === trendB.value)?.label || 'B',
            type: 'line',
            smooth: true,
            symbol: 'circle',
            symbolSize: 5,
            lineStyle: { width: 2.4 },
            areaStyle: { opacity: 0.08 },
            emphasis: { focus: 'series' },
            data: cats.map((d) => mapB[d] ?? 0),
          },
        ],
      };
    });

    /* Donut (cards composition) */
    const donutLabels = computed(() => (repo.cards || []).map((c) => c?.label || t('بې‌نوم')));
    const donutSeries = computed(() =>
      (repo.cards || []).map((c) => Number(c?.total ?? 0)).filter((v) => Number.isFinite(v) && v >= 0)
    );
    const donutTotal = computed(() => (donutSeries.value || []).reduce((a, b) => a + b, 0));
    const donutOption = computed(() => ({
      color: palette,
      tooltip: { trigger: 'item', valueFormatter: (v) => `${v}` },
      legend: { bottom: 0, type: 'scroll' },
      series: [
        {
          name: t('ترکیب'),
          type: 'pie',
          radius: ['58%', '82%'],
          padAngle: 2,
          itemStyle: { borderRadius: 8, borderColor: '#fff', borderWidth: 2 },
          label: { show: true, formatter: '{b}\n{d}%' },
          data: donutLabels.value.map((name, i) => ({ name, value: donutSeries.value[i] || 0 })),
        },
      ],
    }));

    /* Top resources (bar) */
    const resourcesBucket = computed(() => {
      const b = repo.buckets || [];
      if (!b.length) return null;
      return b.find((x) => x.key === selectedBucketFormat.value) || b[0];
    });
    const resCats = computed(() =>
      (resourcesBucket.value?.items || []).map(
        (i) => `${i.code_number || '#'} — ${i.name || t('بی نام')}`
      )
    );
    const resVals = computed(() =>
      (resourcesBucket.value?.items || []).map((i) => Number(i.count || 0))
    );
    const resourcesBarOption = computed(() => ({
      color: ['#2563eb'],
      tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
      grid: { left: 12, right: 12, top: 10, bottom: 10, containLabel: true },
      xAxis: {
        type: 'category',
        data: resCats.value,
        axisTick: { show: false },
        axisLabel: { interval: 0, overflow: 'truncate' },
      },
      yAxis: { type: 'value', min: 0, axisTick: { show: false } },
      series: [
        {
          type: 'bar',
          data: resVals.value,
          barWidth: '45%',
          itemStyle: { borderRadius: [8, 8, 0, 0] },
          label: { show: true, position: 'top' },
        },
      ],
    }));

    /* Zones bar & Provinces bar */
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
      color: ['#16a34a'],
      tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
      grid: { left: 12, right: 12, top: 10, bottom: 10, containLabel: true },
      xAxis: { type: 'value', min: 0, axisTick: { show: false } },
      yAxis: {
        type: 'category',
        axisTick: { show: false },
        data: zonesItemsFlat.value.map((i) => i.zone || t('واجد زون')),
      },
      series: [
        {
          type: 'bar',
          data: zonesItemsFlat.value.map((i) => i.count || 0),
          barWidth: '45%',
          itemStyle: { borderRadius: [0, 8, 8, 0] },
          label: { show: true, position: 'right' },
        },
      ],
    }));
    const provincesBarOption = computed(() => ({
      color: ['#f59e0b'],
      tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
      grid: { left: 12, right: 12, top: 10, bottom: 10, containLabel: true },
      xAxis: { type: 'value', min: 0, axisTick: { show: false } },
      yAxis: {
        type: 'category',
        axisTick: { show: false },
        data: provincesItemsFlat.value.map((i) => i.province || t('واجد ولایت')),
      },
      series: [
        {
          type: 'bar',
          data: provincesItemsFlat.value.map((i) => i.count || 0),
          barWidth: '45%',
          itemStyle: { borderRadius: [0, 8, 8, 0] },
          label: { show: true, position: 'right' },
        },
      ],
    }));

    /* Breakdown pie */
    const breakdownPieOption = computed(() => {
      const items = repo.breakdownItems || [];
      return {
        color: palette,
        tooltip: { trigger: 'item' },
        legend: { bottom: 0, type: 'scroll' },
        series: [
          {
            name: t('تقسیم'),
            type: 'pie',
            radius: ['48%', '78%'],
            padAngle: 2,
            itemStyle: { borderRadius: 8, borderColor: '#fff', borderWidth: 2 },
            label: { show: true, formatter: '{b}\n{d}%' },
            data: items.map((it) => ({
              name: String(it.value ?? t('نامعلوم')),
              value: Number(it.count || 0),
            })),
          },
        ],
      };
    });

    /* Recent table */
    const recentHeaders = [
      { key: 'label', title: t('فارمت ها'), width: 140 },
      { key: 'title', title: t('عنوان/نام') },
      { key: 'zone', title: t('زون'), width: 120 },
      { key: 'province', title: t('ولایت'), width: 120 },
      { key: 'extra', title: t('غیره'), width: 280 },
      { key: 'created_at', title: t('تاریخ'), width: 160 },
    ];
  </script>

  <script>
    /* Lazy-register ECharts component (keeps SFC clean) */
    export default { components: { 'v-chart': (await import('vue-echarts')).default } };
  </script>

  <style scoped>
    .h-100 {
      height: 100%;
    }
    .soft-card {
      border-radius: 16px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.92));
      box-shadow: 0 1px 0 rgba(0, 0, 0, 0.02);
      backdrop-filter: saturate(1.05) blur(2px);
    }
    .granularity .v-btn.elevated {
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
    }

    /* Donut center overlay */
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
    .flex-1-1 {
      flex: 1 1 0;
      min-width: 0;
    }
  </style>
