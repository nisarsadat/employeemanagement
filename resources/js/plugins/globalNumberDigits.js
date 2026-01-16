// resources/js/plugins/globalNumberDigits.js

const FA = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹']
const AR = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩']

function toEnDigits(val) {
  if (val === null || val === undefined) return val
  if (typeof val !== 'string') return val
  let s = val
  s = s.replace(/[۰-۹]/g, (ch) => String(FA.indexOf(ch)))
  s = s.replace(/[٠-٩]/g, (ch) => String(AR.indexOf(ch)))
  return s
}

function looksDigitRegex(re) {
  // Detect common patterns used in validations
  // like /^\d+$/, /^\d{10}$/, /^\d{1,10}$/ etc.
  const src = re?.source || ''
  return src.includes('\\d')
}

export function installGlobalNumberDigits() {
  if (window.__digits_number_patch_installed__) return
  window.__digits_number_patch_installed__ = true

  // keep originals
  const OriginalNumber = window.Number
  const OriginalIsNaN = window.isNaN
  const OriginalParseInt = window.parseInt
  const OriginalParseFloat = window.parseFloat

  // --- patched Number(...)
  function PatchedNumber(x) {
    return OriginalNumber(toEnDigits(x))
  }
  PatchedNumber.prototype = OriginalNumber.prototype

  // copy static props
  for (const key of Object.getOwnPropertyNames(OriginalNumber)) {
    if (key === 'length' || key === 'name' || key === 'prototype') continue
    const desc = Object.getOwnPropertyDescriptor(OriginalNumber, key)
    try { Object.defineProperty(PatchedNumber, key, desc) } catch (_) {}
  }

  if (typeof PatchedNumber.isFinite === 'function' && typeof PatchedNumber.isfinite !== 'function') {
    PatchedNumber.isfinite = PatchedNumber.isFinite
  }

  window.Number = PatchedNumber

  window.isNaN = function patchedIsNaN(x) {
    return OriginalIsNaN(toEnDigits(x))
  }

  window.parseInt = function patchedParseInt(x, radix) {
    return OriginalParseInt(toEnDigits(x), radix)
  }

  window.parseFloat = function patchedParseFloat(x) {
    return OriginalParseFloat(toEnDigits(x))
  }

  /* =========================================================
     ✅ NEW: patch RegExp.test and String.match for digit regex
     So rules like /^\d{10}$/ pass even if value is "۱۲۳..."
  ========================================================= */

  const OriginalRegExpTest = RegExp.prototype.test
  RegExp.prototype.test = function patchedTest(str) {
    if (typeof str === 'string' && looksDigitRegex(this)) {
      return OriginalRegExpTest.call(this, toEnDigits(str))
    }
    return OriginalRegExpTest.call(this, str)
  }

  const OriginalStringMatch = String.prototype.match
  String.prototype.match = function patchedMatch(re) {
    if (re instanceof RegExp && looksDigitRegex(re)) {
      return OriginalStringMatch.call(toEnDigits(String(this)), re)
    }
    return OriginalStringMatch.call(this, re)
  }
}
