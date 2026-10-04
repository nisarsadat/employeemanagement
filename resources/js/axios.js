// resources/js/axios.js
import axios from 'axios';

/**
 * ✅ GLOBAL DIGIT NORMALIZER
 * Converts Persian/Arabic digits to English digits in any string.
 * Works everywhere automatically via axios interceptor.
 */
function normalizeDigits(input) {
  if (input === null || input === undefined) return input;

  const s = String(input);
  const fa = '۰۱۲۳۴۵۶۷۸۹';
  const ar = '٠١٢٣٤٥٦٧٨٩';

  let out = '';
  for (const ch of s) {
    const iFa = fa.indexOf(ch);
    if (iFa !== -1) {
      out += String(iFa);
      continue;
    }
    const iAr = ar.indexOf(ch);
    if (iAr !== -1) {
      out += String(iAr);
      continue;
    }
    out += ch;
  }
  return out;
}

function normalizeDeep(value) {
  if (value === null || value === undefined) return value;

  if (typeof value === 'string') return normalizeDigits(value);

  if (Array.isArray(value)) return value.map(normalizeDeep);

  // Keep File/Blob untouched (uploads)
  if (value instanceof File || value instanceof Blob) return value;

  // Normalize plain objects
  if (value && typeof value === 'object') {
    // FormData
    if (value instanceof FormData) {
      const fd = new FormData();
      for (const [k, v] of value.entries()) {
        if (typeof v === 'string') fd.append(k, normalizeDigits(v));
        else fd.append(k, v);
      }
      return fd;
    }

    const out = {};
    for (const k of Object.keys(value)) out[k] = normalizeDeep(value[k]);
    return out;
  }

  return value;
}

// ✅ Base URL (token mode doesn't need Sanctum cookies/CSRF)
// axios.defaults.baseURL = 'https://200.100.100.1/api';
// axios.defaults.baseURL = 'http://127.0.0.1:8000/api';
axios.defaults.baseURL = '/api';
// axios.defaults.baseURL = 'http://public.test/api';

// ❌ Do NOT use cookies for token mode
axios.defaults.withCredentials = false;

// ✅ JSON defaults
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ✅ Load token from sessionStorage on boot
const saved = sessionStorage.getItem('token');
if (saved) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${saved}`;
}

// ✅ Attach token automatically on every request (if present)
axios.interceptors.request.use((config) => {
  const t = sessionStorage.getItem('token');
  if (t) config.headers.Authorization = `Bearer ${t}`;

  // ✅ IMPORTANT: normalize digits globally for ANY payload
  if (config.data) config.data = normalizeDeep(config.data);

  return config;
});

// ✅ Handle 401/419: drop token and bounce to login
axios.interceptors.response.use(
  (r) => r,
  (err) => {
    const status = err?.response?.status;
    if (status === 401 || status === 419) {
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      if (window.location.pathname !== '/') {
        window.location.href = '/';
      }
    }
    return Promise.reject(err);
  }
);

export default axios;
