import { toEnDigits } from '@/plugins/inputDigits' // or wherever you exported it

export function isValidNumber(v) {
  if (v === null || v === undefined || v === '') return false
  const en = toEnDigits(v)
  return !isNaN(en) && en !== ''
}
