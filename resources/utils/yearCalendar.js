    // src/utils/yearCalendar.js
import dayjs from 'dayjs';
import jalaliday from 'jalaliday';
import hijri from 'dayjs/plugin/islamic';

dayjs.extend(jalaliday);
dayjs.extend(hijri);

export function convertYearToCalendar(gYear, calendar) {
  if (!gYear) return '';

  // Use mid-year date to avoid edge shifting
  const base = dayjs(`${gYear}-07-01`);

  if (calendar === 'jalali') {
    return base.calendar('jalali').year(); // 1404
  }

  if (calendar === 'hijri') {
    return base.calendar('islamic').year(); // 1447
  }

  return gYear; // gregorian
}
