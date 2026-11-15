// resources/js/plugins/inputDigits.js

const FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
const AR = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩']

function toEnDigits(val) {
  if (val === null || val === undefined) return ''
  let s = String(val)
  s = s.replace(/[۰-۹]/g, (ch) => String(FA.indexOf(ch)))
  s = s.replace(/[٠-٩]/g, (ch) => String(AR.indexOf(ch)))
  return s
}

function enToFaDigits(val) {
  if (val === null || val === undefined) return ''
  return String(val).replace(/\d/g, (d) => FA[Number(d)])
}

function keepLatinDigits(el) {
  return !!(el && typeof el.closest === 'function' && el.closest('[data-latin-digits]'))
}

function isTextInput(el) {
  if (!el) return false
  const tag = (el.tagName || '').toLowerCase()
  if (tag !== 'input' && tag !== 'textarea') return false

  const type = (el.getAttribute('type') || '').toLowerCase()
  if (['password', 'file', 'email', 'url'].includes(type)) return false

  if (el.readOnly || el.disabled) return false
  return true
}

/**
 * Browser rejects Persian digits in <input type="number">.
 * In fa/ps: convert number -> text, add inputmode numeric.
 * In en: restore it back to number if it was originally number.
 */
function patchNumericType(el, localeIsFa) {
  if (!el || !isTextInput(el)) return

  const type = (el.getAttribute('type') || '').toLowerCase()

  // remember if it started as number
  if (type === 'number' && !el.dataset.digitWasNumber) {
    el.dataset.digitWasNumber = '1'
  }

  // fa/ps
  if (localeIsFa && el.dataset.digitWasNumber === '1') {
    if (type === 'number') el.setAttribute('type', 'text')
    el.setAttribute('inputmode', 'numeric')
    el.setAttribute('pattern', '[0-9۰-۹٠-٩]*')
  }

  // en
  if (!localeIsFa && el.dataset.digitWasNumber === '1') {
    if (type !== 'number') el.setAttribute('type', 'number')
    el.removeAttribute('inputmode')
    el.removeAttribute('pattern')
  }
}

/* =========================================================
   ✅ Normalize grouping separators (for 10-digit validations)
========================================================= */
const SEP_RE = /[ \t\r\n\u00A0\u2009\u200A\u200B\u202F\u2060._\-–—\/\\|،٬٫]+/g

function looksNumericish(s) {
  if (!/[0-9۰-۹٠-٩]/.test(s)) return false
  if (/[a-zA-Zآ-یء-ي]/.test(s)) return false
  return true
}

function stripGroupingIfNeeded(raw) {
  if (raw === null || raw === undefined) return ''
  let s = String(raw)
  if (!looksNumericish(s)) return s

  // convert to EN
  s = toEnDigits(s).trim()

  const dotCount = (s.match(/\./g) || []).length
  const hasOtherSep = /[ \-–—_،٬\/\\|٫]/.test(s)

  // if other separators exist => strip all separators
  if (hasOtherSep) return s.replace(SEP_RE, '')

  // multiple dots => grouping => strip
  if (dotCount > 1) return s.replace(SEP_RE, '')

  // exactly one dot: keep only if looks like a real decimal
  if (dotCount === 1) {
    const [a, b] = s.split('.')
    const aOk = a && /^\d+$/.test(a)
    const bOk = b && /^\d+$/.test(b)

    if (aOk && bOk) {
      // treat as decimal only when fraction small (<=2) and int not huge
      if (b.length <= 2 && a.length <= 6) return s
    }

    // otherwise dot is grouping separator
    return s.replace(/\./g, '')
  }

  return s
}
/* ========================================================= */

export function installInputDigitLocalizer(getLocale) {
  const isFaMode = () => {
    const loc = String(getLocale?.() || '').toLowerCase()
    return loc.startsWith('fa') || loc.startsWith('ps')
  }

  const applyDisplay = (el) => {
    if (!isTextInput(el)) return

    if (keepLatinDigits(el)) {
      const en = toEnDigits(el.value)
      if (el.value !== en) el.value = en
      return
    }

    const faMode = isFaMode()
    patchNumericType(el, faMode)

    const en = stripGroupingIfNeeded(el.value)

    if (faMode) {
      const fa = enToFaDigits(en)
      if (el.value !== fa) el.value = fa
    } else {
      if (el.value !== en) el.value = en
    }
  }

  // ✅ Main input handler: keep model EN, show FA in UI
  const onInputCapture = (e) => {
    const el = e.target
    if (!isTextInput(el)) return

    if (keepLatinDigits(el)) {
      const en = toEnDigits(el.value)
      if (el.value !== en) el.value = en
      return
    }

    const faMode = isFaMode()
    patchNumericType(el, faMode)

    const en = stripGroupingIfNeeded(el.value)

    if (!faMode) {
      if (el.value !== en) el.value = en
      return
    }

    // A) push EN to Vue model
    if (el.value !== en) el.value = en

    // B) show FA after Vue reads it
    queueMicrotask(() => {
      if (document.activeElement === el) {
        const fa = enToFaDigits(el.value)
        if (el.value !== fa) el.value = fa
      }
    })
  }

  const onFocusCapture = (e) => applyDisplay(e.target)

  // ✅ VERY IMPORTANT:
  // on blur/change, force EN into v-model (so rules validate), then keep FA display
  const syncModelEnKeepFa = (el) => {
    if (!isTextInput(el)) return
    if (keepLatinDigits(el)) {
      const en = toEnDigits(el.value)
      if (el.value !== en) el.value = en
      return
    }
    if (!isFaMode()) return

    const en = stripGroupingIfNeeded(el.value)

    if (el.value !== en) el.value = en
    el.dispatchEvent(new Event('input', { bubbles: true }))

    queueMicrotask(() => {
      const fa = enToFaDigits(en)
      if (el.value !== fa) el.value = fa
    })
  }

  const onBlurCapture = (e) => syncModelEnKeepFa(e.target)
  const onChangeCapture = (e) => syncModelEnKeepFa(e.target)

  const refresh = () => {
    const el = document.activeElement
    if (isTextInput(el)) applyDisplay(el)
  }

  document.addEventListener('input', onInputCapture, true)
  document.addEventListener('focus', onFocusCapture, true)
  document.addEventListener('blur', onBlurCapture, true)
  document.addEventListener('change', onChangeCapture, true)

  return {
    refresh,
    uninstall() {
      document.removeEventListener('input', onInputCapture, true)
      document.removeEventListener('focus', onFocusCapture, true)
      document.removeEventListener('blur', onBlurCapture, true)
      document.removeEventListener('change', onChangeCapture, true)
    },
  }
}
