<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Toolbar -->
    <v-row class="align-center mb-4" dense :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" md="6">
        <div class="d-flex ga-2">
          <v-btn
            :color="active === 'daily' ? 'primary' : undefined"
            variant="elevated"
            @click="setPeriod('daily')"
          >
            {{ t('راپور روزانه') }}
          </v-btn>
          <v-btn
            :color="active === 'weekly' ? 'primary' : undefined"
            variant="elevated"
            @click="setPeriod('weekly')"
          >
            {{ t('راپور هفته وار') }}
          </v-btn>
          <v-btn
            :color="active === 'yearly' ? 'primary' : undefined"
            variant="elevated"
            @click="setPeriod('yearly')"
          >
            {{ t('راپور سالانه') }}
          </v-btn>
        </div>
      </v-col>

      <v-col cols="12" md="7" class="text-md-right text-caption" :dir="isRtl ? 'rtl' : 'ltr'">
        <span v-if="repo.income.range.from && repo.income.range.to">
          {{ t('از') }} <strong>{{ formatDateYMD(repo.income.range.from) }}</strong> {{ t('تا') }}
          <strong>{{ formatDateYMD(repo.income.range.to) }}</strong>
        </span>
      </v-col>
    </v-row>

    <!-- KPI Cards -->
    <v-row class="mb-4" dense :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- Total we have (accounts total) -->
      <v-col cols="12" md="4">
        <v-card elevation="2" class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-medium-emphasis">{{ t('تمام پول موجودی') }}</div>
              <div class="text-h5 mt-1">{{ moneyExact(repo.accounts.total) }}افغانی</div>
            </div>
            <v-avatar size="40" color="success" variant="tonal">
              <v-icon>mdi-bank</v-icon>
            </v-avatar>
          </div>
          <div class="text-caption text-medium-emphasis mt-2" v-if="repo.accounts.as_of">
            {{ t('تا تاریخ') }}: {{ formatDateYMD(repo.accounts.as_of) }}
          </div>
        </v-card>
      </v-col>

      <!-- Total expense in period -->
      <v-col cols="12" md="4">
        <v-card elevation="2" class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-medium-emphasis">{{ t('تمام مصرف ها') }}</div>
              <div class="text-h5 mt-1 text-error">
                {{ moneyExact(repo.expense.totals?.expense || 0) }}افغانی
              </div>
            </div>
            <v-avatar size="40" color="error" variant="tonal">
              <v-icon>mdi-cash-minus</v-icon>
            </v-avatar>
          </div>
          <div class="text-caption text-medium-emphasis mt-2">
            {{ periodLabel }}ا
          </div>
        </v-card>
      </v-col>

      <!-- Total income in period -->
      <v-col cols="12" md="4">
        <v-card elevation="2" class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption text-medium-emphasis">{{ t('تمام عایدات') }}</div>
              <div class="text-h5 mt-1 text-success">
                {{ moneyExact(repo.income.totals?.income || 0) }}افغانی
              </div>
            </div>
            <v-avatar size="40" color="primary" variant="tonal">
              <v-icon>mdi-cash-plus</v-icon>
            </v-avatar>
          </div>
          <div class="text-caption text-medium-emphasis mt-2">
            {{ periodLabel }}
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Income Chart -->
    <v-card elevation="2" class="mb-4" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="text-subtitle-1">{{ t('گراف عایدات') }}</v-card-title>
      <v-divider />
      <v-card-text :dir="isRtl ? 'rtl' : 'ltr'">
        <div ref="incomeChartRef" style="height: 320px; width: 100%"></div>
      </v-card-text>
    </v-card>

    <!-- Expense Chart -->
    <v-card elevation="2" class="mb-4" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="text-subtitle-1">{{ t('گراف های مصارف') }}</v-card-title>
      <v-divider />
      <v-card-text>
        <div ref="expenseChartRef" style="height: 320px; width: 100%"></div>
      </v-card-text>
    </v-card>

    <!-- Accounts Chart -->
    <v-card elevation="2" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="text-subtitle-1">
        {{ t('بیلانس فعلی حساب ها') }}
        <small class="text-medium-emphasis ms-2" v-if="repo.accounts.as_of">
          ({{ t('از تاریخ') }}: {{ formatDateYMD(repo.accounts.as_of) }})
        </small>
      </v-card-title>
      <v-divider />
      <v-card-text>
        <div ref="accountsChartRef" style="height: 360px; width: 100%"></div>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { onMounted, ref, computed, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import * as echarts from 'echarts';
import { useFinanceReportRepository } from '@/stores/useFinanceReportRepository';

const { t, locale } = useI18n();
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));
const active = ref('daily');

const repo = useFinanceReportRepository();

// Chart refs
const incomeChartRef = ref(null);
const expenseChartRef = ref(null);
const accountsChartRef = ref(null);

let incomeChart, expenseChart, accountsChart;

/* -------------------- Digit localization (no grouping, no forced decimals) -------------------- */
const EASTERN = /^ps|^fa|^ar/i;
const easternDigits = { '0':'۰','1':'۱','2':'۲','3':'۳','4':'۴','5':'۵','6':'۶','7':'۷','8':'۸','9':'۹' };
function toEasternDigits(s) { return String(s).replace(/[0-9]/g, d => easternDigits[d] ?? d); }

/** Show exact number as typed (e.g., 20, 1234.5) — no commas, no fixed decimals */
function numberExact(n) {
  const s = (n ?? 0).toString();
  return EASTERN.test(locale.value || '') ? toEasternDigits(s) : s;
}

/** Alias for money values (same exact formatting, just a semantic name) */
const moneyExact = numberExact;

/* -------------------- Date helpers (locale-aware) -------------------- */
const EASTERN_LOCALES = ['fa', 'fa-IR', 'ps', 'ps-AF', 'ar', 'ar-SA', 'ar-AE'];
function formatDateTS(ts) {
  const d = new Date(ts);
  const df = new Intl.DateTimeFormat(locale.value || 'en', { year: 'numeric', month: '2-digit', day: '2-digit' });
  const out = df.format(d);
  return EASTERN_LOCALES.includes((locale.value || '').toString()) ? toEasternDigits(out) : out;
}
function formatDateYMD(ymd) {
  if (!ymd) return '';
  const [y, m = 1, d = 1] = String(ymd).split('-').map(Number);
  return formatDateTS(new Date(y, m - 1, d).getTime());
}

/* -------------------- Period Label -------------------- */
const periodLabel = computed(() => {
  if (!repo.income.range?.from || !repo.income.range?.to) return '';
  return `${t('له')} ${formatDateYMD(repo.income.range.from)} ${t('تر')} ${formatDateYMD(repo.income.range.to)}`;
});

/* -------------------- Period Switching -------------------- */
function setPeriod(p) {
  if (active.value === p) return;
  active.value = p;
  loadCharts();
}

/* -------------------- Data helpers -------------------- */
// Convert {labels: ['2025-10-01', ...], series: [123, ...]} -> [[ts, value], ...]
function toTimeSeries(labels = [], values = []) {
  return labels.map((d, i) => {
    const [y, m = 1, d2 = 1] = String(d).split('-').map(Number);
    const ts = new Date(y, m - 1, d2).getTime();
    const v = Number(values?.[i] ?? 0);
    return [ts, v];
  });
}

/* -------------------- Chart Rendering -------------------- */
function renderIncome() {
  if (!incomeChart) {
    incomeChart = echarts.init(incomeChartRef.value, null, { renderer: 'svg' });
    window.addEventListener('resize', () => incomeChart?.resize());
  }
  const data = toTimeSeries(repo.income.labels, repo.income.series);

  incomeChart.setOption({
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'cross' },
      formatter: (params) => {
        const p = Array.isArray(params) ? params[0] : params;
        const ts = p?.value?.[0];
        const val = p?.value?.[1];
        return `${formatDateTS(ts)}<br/>${t('عاید')}: ${moneyExact(val)}`;
      },
    },
    grid: { left: 40, right: 20, top: 30, bottom: 60 },
    xAxis: {
      type: 'time',
      axisLabel: { formatter: (val) => formatDateTS(val) },
    },
    yAxis: {
      type: 'value',
      name: t('افغانی'),
      axisLabel: { formatter: (val) => numberExact(val) },
    },
    series: [
      { name: t('عاید'), type: 'line', smooth: true, areaStyle: {}, data },
    ],
  });
}

function renderExpense() {
  if (!expenseChart) {
    expenseChart = echarts.init(expenseChartRef.value, null, { renderer: 'svg' });
    window.addEventListener('resize', () => expenseChart?.resize());
  }
  const data = toTimeSeries(repo.expense.labels, repo.expense.series);

  expenseChart.setOption({
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'cross' },
      formatter: (params) => {
        const p = Array.isArray(params) ? params[0] : params;
        const ts = p?.value?.[0];
        const val = p?.value?.[1];
        return `${formatDateTS(ts)}<br/>${t('مصرف')}: ${moneyExact(val)}`;
      },
    },
    grid: { left: 40, right: 20, top: 30, bottom: 60 },
    xAxis: {
      type: 'time',
      axisLabel: { formatter: (val) => formatDateTS(val) },
    },
    yAxis: {
      type: 'value',
      name: t('افغانی'),
      axisLabel: { formatter: (val) => numberExact(val) },
    },
    series: [
      { name: t('مصرف'), type: 'line', smooth: true, areaStyle: {}, data },
    ],
  });
}

function renderAccounts() {
  if (!accountsChart) {
    accountsChart = echarts.init(accountsChartRef.value, null, { renderer: 'svg' });
    window.addEventListener('resize', () => accountsChart?.resize());
  }

  accountsChart.setOption({
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      formatter: (params) => {
        const list = Array.isArray(params) ? params : [params];
        return list.map((p) => `${p?.axisValueLabel}: ${moneyExact(p?.value)}`).join('<br/>');
      },
    },
    grid: { left: 60, right: 20, top: 60, bottom: 30 },
    xAxis: {
      type: 'category',
      data: repo.accounts.names,
      axisLabel: { rotate: 35, interval: 0, bottom: 30 },
    },
    yAxis: {
      type: 'value',
      name: t('افغانی'),
      axisLabel: { formatter: (val) => numberExact(val) },
    },
    series: [
      { name: t('بیلانس'), type: 'bar', data: repo.accounts.values }
    ],
  });
}

/* -------------------- Load Data -------------------- */
async function loadCharts() {
  await Promise.all([
    repo.fetchIncome(active.value),
    repo.fetchExpense(active.value),
    repo.fetchAccounts(),
  ]);
  await nextTick();
  renderIncome();
  renderExpense();
  renderAccounts();
}

/* -------------------- Mount & Locale Watch -------------------- */
onMounted(loadCharts);

watch(locale, async () => {
  await nextTick();
  renderIncome();
  renderExpense();
  renderAccounts();
});
</script>
