<!-- resources/js/components/DateInput.vue -->
<template>
  <v-menu
    v-model="menu"
    :close-on-content-click="false"
    transition="fade-transition"
    location="top start"
    :max-width="320"
  >
    <template #activator="{ props }">
      <v-text-field
        v-bind="props"
        :model-value="display"
        :label="label"
        :rules="rules"
        variant="outlined"
        density="compact"
        rounded="lg"
        class="date-cell dateinput"
        :class="rtl ? 'date-cell-rtl' : 'date-cell-ltr'"
        hide-details="auto"
        readonly
        clearable
        :dir="'ltr'"
        :prepend-inner-icon="rtl ? undefined : 'mdi-calendar-month-outline'"
        :append-inner-icon="rtl ? 'mdi-calendar-month-outline' : undefined"
        @click:clear="clearDate"
      />
    </template>

    <v-card elevation="2" class="overflow-hidden">
      <!-- 1) Gregorian: native Vuetify date-picker -->
      <template v-if="calendar === 'gregorian'">
        <v-date-picker
          v-model="gregInner"
          color="primary"
          hide-actions
          @update:model-value="onPickGregorian"
        />
        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="menu = false">{{ closeText }}</v-btn>
        </v-card-actions>
      </template>

      <!-- 2) Jalali / 3) Hijri: custom picker styled like Vuetify -->
      <template v-else>
        <div class="picker-header">
          <div class="picker-title">{{ headerTitle }}</div>
          <div class="picker-nav">
            <v-btn icon variant="text" @click="prevMonth">
              <v-icon>mdi-chevron-left</v-icon>
            </v-btn>
            <div class="picker-month">{{ monthTitle }}</div>
            <v-btn icon variant="text" @click="nextMonth">
              <v-icon>mdi-chevron-right</v-icon>
            </v-btn>
          </div>
        </div>

        <div class="picker-grid picker-week">
          <div v-for="w in weekLetters" :key="w" class="picker-week-cell">
            {{ w }}
          </div>
        </div>

        <div class="picker-grid picker-days">
          <div v-for="n in padStart" :key="'pad-' + n" class="picker-day empty"></div>

          <button
            v-for="d in monthDays"
            :key="'d-' + d.d"
            type="button"
            class="picker-day"
            :class="{
              selected: isSelected(d.iso),
              today: isToday(d.iso),
              'today-selected': isSelected(d.iso) && isToday(d.iso),
            }"
            @click="pickIso(d.iso)"
            :title="d.iso"
          >
            {{ d.label }}
          </button>
        </div>

        <v-card-actions class="justify-between">
          <v-btn variant="text" @click="goToday">{{ todayText }}</v-btn>
          <v-btn variant="text" @click="menu = false">{{ closeText }}</v-btn>
        </v-card-actions>
      </template>
    </v-card>
  </v-menu>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

import dayjs from 'dayjs';
import jalaliday from 'jalaliday';
import { toGregorian, toHijri } from 'hijri-converter';

import { useCalendar } from '../../composables/useCalendar';

dayjs.extend(jalaliday);

const props = defineProps({
  modelValue: { type: String, default: null }, // ISO YYYY-MM-DD
  label: { type: String, default: '' },
  rules: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const { t, locale } = useI18n();
const { calendar, formatDisplay, toIso } = useCalendar();

const rtl = computed(() => locale.value === 'fa');
const closeText = computed(() => t('بستن'));
const todayText = computed(() => t('امروز'));

const AFGHAN_MONTHS = [
  'حمل',
  'ثور',
  'جوزا',
  'سرطان',
  'اسد',
  'سنبله',
  'میزان',
  'عقرب',
  'قوس',
  'جدی',
  'دلو',
  'حوت',
];

const DIGIT_MAPS = {
  arab: { 0: '٠', 1: '١', 2: '٢', 3: '٣', 4: '٤', 5: '٥', 6: '٦', 7: '٧', 8: '٨', 9: '٩' },
  arabext: { 0: '۰', 1: '۱', 2: '۲', 3: '۳', 4: '۴', 5: '۵', 6: '۶', 7: '۷', 8: '۸', 9: '۹' },
};

function applyDigits(s, set) {
  if (!set || set === 'latn') return String(s);
  const map = DIGIT_MAPS[set];
  if (!map) return String(s);
  return String(s).replace(/[0-9]/g, (d) => map[d] ?? d);
}

const menu = ref(false);

const gregInner = ref(props.modelValue || dayjs().format('YYYY-MM-DD'));

watch(
  () => props.modelValue,
  (v) => {
    if (calendar.value === 'gregorian') {
      gregInner.value = v || '';
    }
  }
);

function onPickGregorian(val) {
  const iso = toIso(val);
  emit('update:modelValue', iso);
  menu.value = false;
}

const panelYear = ref(0);
const panelMonth = ref(0);

function setPanelFromIso(iso) {
  const base = iso && dayjs(iso).isValid() ? dayjs(iso) : dayjs();

  if (calendar.value === 'jalali') {
    const j = base.calendar('jalali');
    panelYear.value = j.year();
    panelMonth.value = j.month() + 1;
  } else if (calendar.value === 'hijri') {
    const g = base;
    const { hy, hm } = toHijri(g.year(), g.month() + 1, g.date());
    panelYear.value = hy;
    panelMonth.value = hm;
  }
}

function prevMonth() {
  if (panelMonth.value === 1) {
    panelMonth.value = 12;
    panelYear.value -= 1;
  } else {
    panelMonth.value -= 1;
  }
}

function nextMonth() {
  if (panelMonth.value === 12) {
    panelMonth.value = 1;
    panelYear.value += 1;
  } else {
    panelMonth.value += 1;
  }
}

function goToday() {
  const todayIso = dayjs().format('YYYY-MM-DD');
  emit('update:modelValue', todayIso);
  setPanelFromIso(todayIso);
}

const firstDayIsoInPanel = computed(() =>
  calendar.value === 'jalali'
    ? jToIso(panelYear.value, panelMonth.value, 1)
    : hToIso(panelYear.value, panelMonth.value, 1)
);

const monthTitle = computed(() => {
  try {
    const d = new Date(firstDayIsoInPanel.value + 'T00:00:00');

    if (calendar.value === 'jalali') {
      const name = AFGHAN_MONTHS[(panelMonth.value - 1) % 12] || '';
      const y = panelYear.value;
      return `${name} ${applyDigits(String(y), rtl.value ? 'arabext' : 'latn')}`;
    }

    const f = new Intl.DateTimeFormat('ar', {
      calendar: 'islamic-umalqura',
      month: 'long',
      year: 'numeric',
    });

    return f.format(d);
  } catch {
    return `${panelYear.value}/${String(panelMonth.value).padStart(2, '0')}`;
  }
});

const currentIsoForHeader = computed(() => {
  if (calendar.value === 'jalali') return jToIso(panelYear.value, panelMonth.value, 15);
  if (calendar.value === 'hijri') return hToIso(panelYear.value, panelMonth.value, 15);
  return dayjs().format('YYYY-MM-DD');
});

const headerTitle = computed(() => formatDisplay(currentIsoForHeader.value, locale.value || 'fa'));

const weekLetters = computed(() => ['S', 'M', 'T', 'W', 'T', 'F', 'S']);

function daysInPanelMonth() {
  const out = [];

  for (let d = 1; d <= 31; d++) {
    const iso =
      calendar.value === 'jalali'
        ? jToIso(panelYear.value, panelMonth.value, d)
        : hToIso(panelYear.value, panelMonth.value, d);

    if (!iso) break;

    if (calendar.value === 'jalali') {
      const back = dayjs(iso).calendar('jalali');
      if (back.year() !== panelYear.value || back.month() + 1 !== panelMonth.value) break;
      out.push(iso);
    } else {
      const g = dayjs(iso);
      const { hy, hm } = toHijri(g.year(), g.month() + 1, g.date());
      if (hy !== panelYear.value || hm !== panelMonth.value) break;
      out.push(iso);
    }
  }

  return out;
}

const monthDays = computed(() => {
  return daysInPanelMonth().map((iso) => {
    const g = dayjs(iso);
    const dNum =
      calendar.value === 'jalali'
        ? g.calendar('jalali').date()
        : toHijri(g.year(), g.month() + 1, g.date()).hd;

    const label =
      calendar.value === 'jalali'
        ? applyDigits(String(dNum), rtl.value ? 'arabext' : 'latn')
        : applyDigits(String(dNum), 'arab');

    return { iso, d: dNum, label };
  });
});

const padStart = computed(() => {
  const wd = new Date(firstDayIsoInPanel.value + 'T00:00:00').getDay();
  return wd;
});

function isSelected(iso) {
  return (props.modelValue || '') === iso;
}

function isToday(iso) {
  return iso === dayjs().format('YYYY-MM-DD');
}

function pickIso(iso) {
  emit('update:modelValue', iso);
  menu.value = false;
}

function jToIso(jy, jm, jd) {
  try {
    const j = dayjs()
      .calendar('jalali')
      .year(jy)
      .month(jm - 1)
      .date(jd);

    const g = j.calendar('gregory');
    if (!g.isValid()) return '';
    return g.format('YYYY-MM-DD');
  } catch {
    return '';
  }
}

function hToIso(hy, hm, hd) {
  try {
    const { gy, gm, gd } = toGregorian(hy, hm, hd);
    return dayjs(new Date(gy, gm - 1, gd)).format('YYYY-MM-DD');
  } catch {
    return '';
  }
}

const display = computed(() => formatDisplay(props.modelValue, locale.value || 'fa'));

watch(
  [calendar, () => props.modelValue],
  () => {
    if (calendar.value !== 'gregorian') {
      setPanelFromIso(props.modelValue);
    }
  },
  { immediate: true }
);

onMounted(() => {
  if (!props.modelValue || String(props.modelValue).trim() === '') {
    emit('update:modelValue', dayjs().format('YYYY-MM-DD'));
  }
});

function clearDate() {
  emit('update:modelValue', null);
}
</script>

<style scoped>
.date-cell {
  width: 100%;
}
.date-cell-ltr :deep(.v-field__input),
.date-cell-rtl :deep(.v-field__input) {
  padding-left: 8px;
  padding-right: 8px;
}

:deep(.v-field__input) {
  line-height: 1.4;
}

.picker-header {
  background: rgb(var(--v-theme-primary));
  color: #fff;
  padding: 12px 14px;
}

.picker-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 6px;
}

.picker-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.picker-month {
  font-weight: 600;
  color: #fff;
}

.picker-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 6px;
  padding: 10px 12px;
}

.picker-week {
  padding-bottom: 0;
}

.picker-week-cell {
  text-align: center;
  font-size: 12px;
  color: #757575;
  font-weight: 600;
}

.picker-days {
  padding-top: 8px;
}

.picker-day {
  height: 36px;
  border-radius: 18px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}

.picker-day:hover {
  background: rgba(0, 0, 0, 0.06);
}

.picker-day.today {
  border: 1px solid rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.08);
  color: rgb(var(--v-theme-primary));
}

.picker-day.selected {
  background: rgb(var(--v-theme-primary));
  color: #fff;
}

.picker-day.today-selected {
  background: rgb(var(--v-theme-primary));
  color: #fff;
  border: 1px solid rgb(var(--v-theme-primary));
}

.picker-day.empty {
  visibility: hidden;
  pointer-events: none;
}
</style>