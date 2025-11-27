<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Toolbar -->
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="6">
        <div class="d-flex ga-2">
          <v-btn
            :color="active === 'daily' ? 'primary' : undefined"
            variant="elevated"
            @click="setPeriod('daily')"
          >
            {{ t('ورځنی راپور') }}
          </v-btn>
          <v-btn
            :color="active === 'weekly' ? 'primary' : undefined"
            variant="elevated"
            @click="setPeriod('weekly')"
          >
            {{ t('اونیز راپور') }}
          </v-btn>
          <v-btn
            :color="active === 'yearly' ? 'primary' : undefined"
            variant="elevated"
            @click="setPeriod('yearly')"
          >
            {{ t('کلنی راپور') }}
          </v-btn>
        </div>
      </v-col>

      <!-- Formatted range -->
      <v-col cols="12" md="6" class="text-md-right text-caption">
        <span v-if="range.from && range.to">
          {{ t('له') }} <strong>{{ formatDate(range.from) }}</strong> {{ t('تر') }}
          <strong>{{ formatDate(range.to) }}</strong>
        </span>
      </v-col>
    </v-row>

    <!-- Chart -->
    <v-card elevation="2" class="mb-4">
      <v-card-text>
        <div ref="chartRef" style="height: 360px; width: 100%"></div>
      </v-card-text>
    </v-card>

    <!-- Summary -->
    <v-card elevation="2" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="text-subtitle-1">
        {{ t('لنډیز') }}
      </v-card-title>
      <v-divider />
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <div class="text-caption">{{ t('ټول داخل شوي تېل (L)') }}</div>
            <div class="text-h6">{{ qtyExact(totals.income) }} لیتر</div>
          </v-col>
          <v-col cols="12" md="4">
            <div class="text-caption">{{ t('ټول مصرف شوي تېل (L)') }}</div>
            <div class="text-h6">{{ qtyExact(totals.expense) }} لیتر</div>
          </v-col>
          <v-col cols="12" md="4">
            <div class="text-caption">{{ t('خالص (L)') }}</div>
            <div class="text-h6" :class="totals.net >= 0 ? 'text-success' : 'text-error'">
              {{ qtyExact(totals.net) }} لیتر
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import * as echarts from 'echarts';

/* i18n & RTL */
const { t, locale } = useI18n();
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

/* State */
const active = ref('daily');
const chartRef = ref(null);
let chart;

const displayLabels = ref([]);
const series = reactive({ income: [], expense: [], net: [] });
const totals = reactive({ income: 0, expense: 0, net: 0 });
const range = reactive({ from: null, to: null });

/* ------------------ DATE HELPERS (locale-aware) ------------------ */
const fmtDay = () =>
  new Intl.DateTimeFormat(locale.value || 'fa-AF', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  });

const fmtMonth = () =>
  new Intl.DateTimeFormat(locale.value || 'fa-AF', { year: 'numeric', month: 'long' });

const fmtShort = () =>
  new Intl.DateTimeFormat(locale.value || 'fa-AF', { month: 'short', day: '2-digit' });

function parseYmd(s) {
  if (!s) return null;
  const [y, m, d] = String(s).split('-').map(Number);
  if (!y || !m || !d) return null;
  return new Date(y, m - 1, d);
}

function formatDate(ymd) {
  const d = parseYmd(ymd);
  return d ? fmtDay().format(d) : ymd || '';
}

function startOfIsoWeek(d) {
  const date = new Date(d);
  const day = date.getDay();
  const diff = (day === 0 ? -6 : 1) - day;
  date.setDate(date.getDate() + diff);
  date.setHours(0, 0, 0, 0);
  return date;
}

function addDays(d, n) {
  const a = new Date(d);
  a.setDate(a.getDate() + n);
  return a;
}
function addWeeks(d, n) {
  return addDays(d, n * 7);
}
function addMonths(d, n) {
  const a = new Date(d);
  a.setMonth(a.getMonth() + n);
  return a;
}
function ymd(d) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}
function ym(d) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  return `${y}-${m}`;
}

function buildBuckets(period, fromISO, count) {
  const out = [];
  if (!fromISO || !count) return out;
  const fromDate = parseYmd(fromISO);
  if (!fromDate) return out;

  if (period === 'daily') {
    for (let i = 0; i < count; i++) out.push(ymd(addDays(fromDate, i)));
  } else if (period === 'weekly') {
    const start = startOfIsoWeek(fromDate);
    for (let i = 0; i < count; i++) {
      const weekStart = addWeeks(start, i);
      const weekEnd = addDays(weekStart, 6);
      out.push({ start: ymd(weekStart), end: ymd(weekEnd) });
    }
  } else {
    const start = new Date(fromDate.getFullYear(), fromDate.getMonth(), 1);
    for (let i = 0; i < count; i++) {
      const m = addMonths(start, i);
      out.push({ month: ym(m), dateObj: m });
    }
  }
  return out;
}

function formatBucketsToLabels(period, buckets) {
  if (period === 'daily') {
    return buckets.map((iso) => formatDate(iso));
  }
  if (period === 'weekly') {
    return buckets.map(({ start, end }) => {
      const s = parseYmd(start);
      const e = parseYmd(end);
      if (!s || !e) return `${start}–${end}`;
      const left = fmtShort().format(s);
      const right = fmtShort().format(e);
      return `${left} – ${right}`;
    });
  }
  return buckets.map(({ dateObj }) => fmtMonth().format(dateObj));
}

/* ------------------ NUMBER HELPERS (exact, no grouping) ------------------ */
const EASTERN = /^ps|^fa|^ar/i;
const easternDigits = { '0':'۰','1':'۱','2':'۲','3':'۳','4':'۴','5':'۵','6':'۶','7':'۷','8':'۸','9':'۹' };

function toEasternDigits(str) {
  return String(str).replace(/[0-9]/g, d => easternDigits[d] ?? d);
}

/** Show exact number (no commas/decimals), localized digits */
function qtyExact(n) {
  const s = (n ?? 0).toString();
  return EASTERN.test(locale.value || '') ? toEasternDigits(s) : s;
}

/* ------------------ Actions ------------------ */
function setPeriod(p) {
  if (active.value === p) return;
  active.value = p;
  load();
}

async function load() {
  const resp = await axios.get(`/oil-reports?period=${active.value}`);
  const data = resp?.data || {};

  series.income = data.series?.income || [];
  series.expense = data.series?.expense || [];
  series.net = data.series?.net || [];
  totals.income = data.totals?.income ?? 0;
  totals.expense = data.totals?.expense ?? 0;
  totals.net = data.totals?.net ?? 0;

  range.from = data.range?.from ?? null;
  range.to = data.range?.to ?? null;

  const count = (data.labels?.length ?? 0) || series.income.length;
  const buckets = buildBuckets(active.value, range.from, count);
  displayLabels.value = formatBucketsToLabels(active.value, buckets);

  renderChart();
}

function renderChart() {
  if (!chart) {
    chart = echarts.init(chartRef.value, null, { renderer: 'svg' });
    window.addEventListener('resize', () => chart?.resize());
  }
  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'line' },
      formatter: (params) => {
        const idx = params?.[0]?.dataIndex ?? 0;
        const title = displayLabels.value[idx] ?? '';
        const lines = params.map((p) => `${p.marker} ${p.seriesName}: ${qtyExact(p.value)}`);
        return `<div><strong>${title}</strong><br/>${lines.join('<br/>')}</div>`;
      },
    },
    legend: { data: [t('داخل'), t('مصرف'), t('خالص')] },
    grid: { left: 40, right: 20, top: 30, bottom: 90 },
    xAxis: { type: 'category', data: displayLabels.value },
    yAxis: {
      type: 'value',
      name: t('لیتر'),
      axisLabel: { formatter: (val) => qtyExact(val) },
    },
    series: [
      { name: t('داخل'), type: 'line', smooth: true, areaStyle: {}, data: series.income },
      { name: t('مصرف'), type: 'line', smooth: true, areaStyle: {}, data: series.expense },
      { name: t('خالص'), type: 'line', smooth: true, data: series.net },
    ],
  };
  chart.setOption(option, true);
}

/* Lifecycle */
onMounted(load);

watch(locale, () => {
  if (!range.from) return;
  const count = displayLabels.value.length;
  const buckets = buildBuckets(active.value, range.from, count);
  displayLabels.value = formatBucketsToLabels(active.value, buckets);
  renderChart();
});
</script>
