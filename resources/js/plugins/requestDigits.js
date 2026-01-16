// resources/js/plugins/requestDigits.js

const FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
const AR = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩']

function toEnDigits(s) {
  if (s === null || s === undefined) return s
  if (typeof s !== 'string') return s
  let out = s
  out = out.replace(/[۰-۹]/g, (ch) => String(FA.indexOf(ch)))
  out = out.replace(/[٠-٩]/g, (ch) => String(AR.indexOf(ch)))
  return out
}

// remove separators so "۱۲۳-۴۵۶" -> "123456"
const SEP_RE = /[ \t\r\n\u00A0\u2009\u200A\u200B\u202F\u2060._\-–—\/\\|،٬٫]+/g

function looksNumericish(str) {
  const s = String(str)
  if (!/[0-9۰-۹٠-٩]/.test(s)) return false
  if (/[a-zA-Zآ-یء-ي]/.test(s)) return false
  return true
}

function normalizeString(val) {
  let s = toEnDigits(val)
  if (typeof s !== 'string') return s
  s = s.trim()
  if (looksNumericish(s)) s = s.replace(SEP_RE, '')
  return s
}

function normalizeAny(x) {
  if (x === null || x === undefined) return x
  if (typeof x === 'string') return normalizeString(x)
  if (Array.isArray(x)) return x.map(normalizeAny)

  if (typeof FormData !== 'undefined' && x instanceof FormData) {
    const fd = new FormData()
    for (const [k, v] of x.entries()) {
      fd.append(k, typeof v === 'string' ? normalizeString(v) : v)
    }
    return fd
  }

  if (typeof x === 'object') {
    const out = {}
    for (const k of Object.keys(x)) out[k] = normalizeAny(x[k])
    return out
  }

  return x
}

export function installRequestDigitNormalizer(axiosInstance) {
  if (!axiosInstance?.interceptors) return
  if (axiosInstance.__digits_normalizer_installed__) return
  axiosInstance.__digits_normalizer_installed__ = true

  axiosInstance.interceptors.request.use((config) => {
    if (config?.params) config.params = normalizeAny(config.params)
    if (config?.data) config.data = normalizeAny(config.data)
    return config
  })
}
