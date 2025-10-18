// resources/js/composables/useCalendar.js
import dayjs from 'dayjs'
import { storeToRefs } from 'pinia'
import { useCalendarStore } from '@/stores/calendar'

// map ASCII -> arab / arabext when Intl doesn't apply numberingSystem
const DIGIT_MAPS = {
  arab:    { '0':'٠','1':'١','2':'٢','3':'٣','4':'٤','5':'٥','6':'٦','7':'٧','8':'٨','9':'٩' },
  arabext: { '0':'۰','1':'۱','2':'۲','3':'۳','4':'۴','5':'۵','6':'۶','7':'۷','8':'۸','9':'۹' },
}
function applyDigits(s, set) {
  if (!set || set === 'latn') return s
  const map = DIGIT_MAPS[set]; if (!map) return s
  return String(s).replace(/[0-9]/g, d => map[d])
}

// Intl-based formatter to get Y/M/D and then enforce digits set
function formatWithIntl(iso, calendar, locale, digits = 'latn') {
  if (!iso) return ''
  const date = new Date(iso + 'T00:00:00')
  const fmt = new Intl.DateTimeFormat(locale, {
    calendar,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    numberingSystem: digits, // ask Intl nicely first
  })
  const parts = fmt.formatToParts(date)
  const y = parts.find(p => p.type === 'year')?.value ?? ''
  const m = parts.find(p => p.type === 'month')?.value ?? ''
  const d = parts.find(p => p.type === 'day')?.value ?? ''
  // Build YYYY/MM/DD, then force digits if needed (fallback)
  return applyDigits(`${y}/${m}/${d}`, digits)
}

export function useCalendar() {
  const calStore = useCalendarStore()
  const { calendar } = storeToRefs(calStore)

  function formatDisplay(iso, uiLocale = 'fa') {
    if (!iso || !dayjs(iso, 'YYYY-MM-DD', true).isValid()) return ''
    switch (calendar.value) {
      case 'gregorian':
        return dayjs(iso).format('YYYY/MM/DD')
      case 'jalali':
        // شمسی → Persian digits (arabext) when UI is fa, else ASCII
        return formatWithIntl(iso, 'persian', uiLocale, uiLocale === 'fa' ? 'arabext' : 'latn')
      case 'hijri':
        // ⬅️ changed: قمری → Arabic-Indic digits (arab), not English
        return formatWithIntl(iso, 'islamic-umalqura', 'ar', 'arab')
      default:
        return dayjs(iso).format('YYYY/MM/DD')
    }
  }

  // For backend (keep ASCII unless you decided otherwise earlier)
  function formatForBackend(iso) {
    if (!iso || !dayjs(iso, 'YYYY-MM-DD', true).isValid()) return ''
    switch (calendar.value) {
      case 'gregorian':
        return dayjs(iso).format('YYYY/MM/DD')
      case 'jalali':
        return formatWithIntl(iso, 'persian', 'en', 'latn')          // ASCII digits
      case 'hijri':
        return formatWithIntl(iso, 'islamic-umalqura', 'en', 'latn') // ASCII digits
      default:
        return dayjs(iso).format('YYYY/MM/DD')
    }
  }

  function toIso(value) {
    return dayjs(value).format('YYYY-MM-DD')
  }

  return {
    calendar,
    formatDisplay,
    formatForBackend,
    toIso,
    setCalendar: calStore.setCalendar,
    cycleCalendar: calStore.cycle,
  }
}
