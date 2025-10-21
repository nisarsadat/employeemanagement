// resources/js/composables/useCalendar.js
import dayjs from 'dayjs';
import jalaliday from 'jalaliday';
import { toHijri } from 'hijri-converter';
import { storeToRefs } from 'pinia';
import { useCalendarStore } from '@/stores/calendar';

// enable jalali fallback via dayjs
dayjs.extend(jalaliday);

// ---------- digit helpers ----------
const DIGIT_MAPS = {
  arab: { 0: '٠', 1: '١', 2: '٢', 3: '٣', 4: '٤', 5: '٥', 6: '٦', 7: '٧', 8: '٨', 9: '٩' }, // Arabic-Indic
  arabext: { 0: '۰', 1: '۱', 2: '۲', 3: '۳', 4: '۴', 5: '۵', '۶': '۶', 7: '۷', 8: '۸', 9: '۹' }, // Persian
};
function applyDigits(s, set) {
  if (!set || set === 'latn') return s;
  const map = DIGIT_MAPS[set];
  if (!map) return s;
  return String(s).replace(/[0-9]/g, (d) => map[d]);
}

// Try Intl; if unsupported, throw so the caller can fallback
function formatWithIntlOrThrow(iso, { calendar, locale, digits = 'latn' }) {
  if (!iso) return '';
  const date = new Date(iso + 'T00:00:00');
  // Some engines throw on unknown calendar / numberingSystem
  const fmt = new Intl.DateTimeFormat(locale, {
    calendar,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    numberingSystem: digits,
  });
  const parts = fmt.formatToParts(date);
  const y = parts.find((p) => p.type === 'year')?.value ?? '';
  const m = parts.find((p) => p.type === 'month')?.value ?? '';
  const d = parts.find((p) => p.type === 'day')?.value ?? '';
  return applyDigits(`${y}/${m}/${d}`, digits);
}

// ---------- fallbacks ----------
function formatJalaliFallback(iso, digits = 'latn') {
  const j = dayjs(iso).calendar('jalali');
  const out = j.format('YYYY/MM/DD');
  // Persian digits for UI; ASCII for backend
  return applyDigits(out, digits === 'arabext' ? 'arabext' : 'latn');
}

function formatHijriFallback(iso, digits = 'latn') {
  const g = dayjs(iso);
  const { hy, hm, hd } = toHijri(g.year(), g.month() + 1, g.date());
  const out = `${hy}/${String(hm).padStart(2, '0')}/${String(hd).padStart(2, '0')}`;
  // Arabic-Indic digits for UI; ASCII for backend
  return applyDigits(out, digits === 'arab' ? 'arab' : 'latn');
}

// Safe wrapper that tries Intl first, then fallbacks
function safeFormat(iso, { calendar, locale, digits, type }) {
  try {
    return formatWithIntlOrThrow(iso, { calendar, locale, digits });
  } catch {
    if (type === 'jalali') return formatJalaliFallback(iso, digits);
    if (type === 'hijri') return formatHijriFallback(iso, digits);
    return dayjs(iso).format('YYYY/MM/DD');
  }
}

export function useCalendar() {
  const calStore = useCalendarStore();
  const { calendar } = storeToRefs(calStore);

  /**
   * UI display (pretty, localized digits)
   * - gregorian  → YYYY/MM/DD (ASCII)
   * - jalali     → Persian calendar, Persian digits when UI is fa
   * - hijri      → Umm al-Qura calendar, Arabic-Indic digits
   */
  function formatDisplay(iso, uiLocale = 'fa') {
    if (!iso || !dayjs(iso, 'YYYY-MM-DD', true).isValid()) return '';
    switch (calendar.value) {
      case 'gregorian':
        return dayjs(iso).format('YYYY/MM/DD');

      case 'jalali':
        // Prefer Intl Persian; fallback to dayjs+jalaliday
        return safeFormat(iso, {
          calendar: 'persian',
          locale: uiLocale, // 'fa' shows Persian months if supported
          digits: uiLocale === 'fa' ? 'arabext' : 'latn',
          type: 'jalali',
        });

      case 'hijri':
        // Prefer Intl Umm al-Qura; fallback to hijri-converter
        return safeFormat(iso, {
          calendar: 'islamic-umalqura',
          locale: 'ar', // Arabic for month/day names (header in pickers)
          digits: 'arab', // Arabic-Indic digits
          type: 'hijri',
        });

      default:
        return dayjs(iso).format('YYYY/MM/DD');
    }
  }

  /**
   * Backend format (always ASCII digits, YYYY/MM/DD)
   * Keeps the same day/month/year the user sees, but digits are ASCII.
   */
  function formatForBackend(iso) {
    if (!iso || !dayjs(iso, 'YYYY-MM-DD', true).isValid()) return '';
    switch (calendar.value) {
      case 'gregorian':
        return dayjs(iso).format('YYYY/MM/DD');

      case 'jalali':
        return safeFormat(iso, {
          calendar: 'persian',
          locale: 'en',
          digits: 'latn',
          type: 'jalali',
        });

      case 'hijri':
        return safeFormat(iso, {
          calendar: 'islamic-umalqura',
          locale: 'en',
          digits: 'latn',
          type: 'hijri',
        });

      default:
        return dayjs(iso).format('YYYY/MM/DD');
    }
  }

  // Ensure ISO (YYYY-MM-DD)
  function toIso(value) {
    return dayjs(value).format('YYYY-MM-DD');
  }

  return {
    calendar,
    formatDisplay,
    formatForBackend,
    toIso,
    setCalendar: calStore.setCalendar,
    cycleCalendar: calStore.cycle,
  };
}
