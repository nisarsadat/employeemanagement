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
  return applyDigits(out, digits === 'arabext' ? 'arabext' : 'latn');
}

function formatHijriFallback(iso, digits = 'latn') {
  const g = dayjs(iso);
  const { hy, hm, hd } = toHijri(g.year(), g.month() + 1, g.date());
  const out = `${hy}/${String(hm).padStart(2, '0')}/${String(hd).padStart(2, '0')}`;
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
        return safeFormat(iso, {
          calendar: 'persian',
          locale: uiLocale,
          digits: uiLocale === 'fa' ? 'arabext' : 'latn',
          type: 'jalali',
        });

      case 'hijri':
        return safeFormat(iso, {
          calendar: 'islamic-umalqura',
          locale: 'ar',
          digits: 'arab',
          type: 'hijri',
        });

      default:
        return dayjs(iso).format('YYYY/MM/DD');
    }
  }

  /**
   * Backend format (always ASCII digits, YYYY/MM/DD)
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

  // ============================================================
  // ✅ YEAR helpers (ONLY THIS PART MODIFIED)
  // ============================================================

  /**
   * Convert a *Gregorian year* to display year in current calendar.
   * We intentionally use a safe date in JANUARY to represent that Gregorian year,
   * so 2026 (Jan) shows Jalali 1404 (not 1405).
   */
  function formatYearDisplay(gYear, uiLocale = 'fa') {
    if (!gYear) return '';

    // ✅ use mid-JAN to avoid "edge" issues AND match the Gregorian year start
    const iso = `${gYear}-01-15`;

    if (calendar.value === 'gregorian') {
      return String(gYear);
    }

    if (calendar.value === 'jalali') {
      const out = safeFormat(iso, {
        calendar: 'persian',
        locale: uiLocale,
        digits: uiLocale === 'fa' ? 'arabext' : 'latn',
        type: 'jalali',
      });
      return String(out).split('/')[0] || '';
    }

    if (calendar.value === 'hijri') {
      const out = safeFormat(iso, {
        calendar: 'islamic-umalqura',
        locale: 'ar',
        digits: 'arab',
        type: 'hijri',
      });
      return String(out).split('/')[0] || '';
    }

    return String(gYear);
  }

  /**
   * Optional: show "year/month" in current calendar
   * (useful if you want Month dropdown titles like 1404/01)
   */
  function formatMonthYearDisplay(gYear, month, uiLocale = 'fa') {
    const mm = String(month ?? '').padStart(2, '0');
    if (!gYear || !mm) return '';

    // Build a safe ISO in that month
    const iso = `${gYear}-${mm}-15`;

    if (calendar.value === 'gregorian') {
      return `${gYear}/${mm}`;
    }

    if (calendar.value === 'jalali') {
      const out = safeFormat(iso, {
        calendar: 'persian',
        locale: uiLocale,
        digits: uiLocale === 'fa' ? 'arabext' : 'latn',
        type: 'jalali',
      });
      // out like "۱۴۰۴/۰۵/۲۴" => take year/month
      const parts = String(out).split('/');
      return parts.length >= 2 ? `${parts[0]}/${parts[1]}` : '';
    }

    if (calendar.value === 'hijri') {
      const out = safeFormat(iso, {
        calendar: 'islamic-umalqura',
        locale: 'ar',
        digits: 'arab',
        type: 'hijri',
      });
      const parts = String(out).split('/');
      return parts.length >= 2 ? `${parts[0]}/${parts[1]}` : '';
    }

    return `${gYear}/${mm}`;
  }

  /**
   * Build dropdown items from backend years (gregorian years).
   * Keeps value = gregorian year (backend filter)
   * Shows title = converted year (UI)
   */
  function yearItemsFromGregorianYears(years, uiLocale = 'fa') {
    return (years || [])
      .filter((y) => y)
      .map((y) => ({
        value: Number(y),
        title: formatYearDisplay(Number(y), uiLocale),
      }));
  }

  return {
    calendar,

    // dates
    formatDisplay,
    formatForBackend,
    toIso,

    // year helpers
    formatYearDisplay,
    formatMonthYearDisplay,
    yearItemsFromGregorianYears,

    // store actions
    setCalendar: calStore.setCalendar,
    cycleCalendar: calStore.cycle,
  };
}
