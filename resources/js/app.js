// main.js
import { createApp } from 'vue';
import App from './App.vue';
import vuetify from './plugins/vuetify';
import router from './router/router';
import { createPinia } from 'pinia';
import i18n from './localization/Local.js';
import { useSystemProfileRepository } from './stores/SystemProfileRepository.js';
import Toast from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import VueApexCharts from 'vue3-apexcharts';
import '@fontsource/amiri/400.css';
import '@fontsource/amiri/700.css';
import '@fontsource/scheherazade-new/400.css';
import '@fontsource/scheherazade-new/700.css';
import { ensureRandomUUID } from './polyfills/cryptoRandomUUID';
ensureRandomUUID();
// ✅ Date libs
import dayjs from 'dayjs';
import jalaliday from 'jalaliday';
dayjs.extend(jalaliday);

// ✅ pickers
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import HijriGregorianDatepicker from 'vue3-hijri-gregorian-datepicker';
import 'vue3-hijri-gregorian-datepicker/dist/style.css';

// ✅ Chat store
import { useChatRepository } from './stores/ChatRepository.js';

// ✅ Axios for Bearer token mode
import axios from 'axios';
import { installUiDigitLocalizer } from './plugins/uiDigits.js';

axios.defaults.withCredentials = false;
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ✅ API base
// const API_BASE = import.meta.env.VITE_API_URL || 'https://200.100.100.1';
const API_BASE = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';
// axios.defaults.baseURL = `${API_BASE}/api`;
// axios.defaults.baseURL = '/api';
// axios.defaults.baseURL = 'http://public.test/api';
// ✅ attach token for all API requests
axios.interceptors.request.use((config) => {
  const token = sessionStorage.getItem('token'); // ✅ same place
  if (token) config.headers.Authorization = `Bearer ${token}`;

  // Wide tables send every column label. A GET query string is cut off and
  // the print request fails, so every red print button posts the payload.
  const path = String(config.url || '')
    .split('?')[0]
    .replace(/\/+$/, '');
  const method = String(config.method || 'get').toLowerCase();
  if (method === 'get' && /\/print$/.test(path) && config.params) {
    config.method = 'post';
    config.data = config.params;
    config.params = undefined;
    config.headers = config.headers || {};
    config.headers['Content-Type'] = 'application/json';
    config.headers.Accept = 'text/html';
  }

  return config;
});

document.addEventListener('DOMContentLoaded', function () {
  document.body.style.zoom = '100%';
});

/* =========================================================
   ✅ Reverb (from your component)
========================================================= */
import { initReverb } from './plugins/reverb.js';
/* ========================================================= */
import NoAutofill from '@/plugins/noAutofill';

const app = createApp(App);
const pinia = createPinia();

app.use(NoAutofill);
app.use(pinia);
app.use(router);

const systemProfile = useSystemProfileRepository(pinia);
systemProfile.ensureLoaded().then(() => {
  if (!localStorage.getItem('apartment-lang') && systemProfile.defaultLanguage) {
    i18n.global.locale.value = systemProfile.defaultLanguage;
    const rtl = systemProfile.defaultLanguage === 'fa' || systemProfile.defaultLanguage === 'ps';
    document.documentElement.setAttribute('dir', rtl ? 'rtl' : 'ltr');
  }
});
app.use(vuetify);
app.use(i18n);

// installUiDigitLocalizer(() => i18n.global.locale.value)

app.use(Toast);
app.use(VueApexCharts);

// ✅ register pickers
app.component('PersianDatePicker', Vue3PersianDatetimePicker);
app.component('HijriGregorianDatepicker', HijriGregorianDatepicker);

// ✅ start badge polling + presence
useChatRepository(pinia).initBadge();

/* =========================================================
   ✅ GLOBAL Persian/Arabic digits → English (IME-safe)
   - Fixes: Dari/Persian typing not showing (composition/IME)
========================================================= */
const FA = '۰۱۲۳۴۵۶۷۸۹';
const AR = '٠١٢٣٤٥٦٧٨٩';

// mark composing state per element
function setComposing(el, v) {
  try {
    el.__digit_norm_composing__ = v;
  } catch {}
}

function isComposing(el, evt) {
  return !!(evt?.isComposing || el?.__digit_norm_composing__);
}

function toEnglishDigits(val) {
  if (val === null || val === undefined) return val;
  const s = String(val);

  // ✅ quick check: only touch if it contains fa/ar digits
  let has = false;
  for (const ch of s) {
    if (FA.includes(ch) || AR.includes(ch)) {
      has = true;
      break;
    }
  }
  if (!has) return s;

  let out = '';
  for (const ch of s) {
    const fa = FA.indexOf(ch);
    if (fa !== -1) {
      out += String(fa);
      continue;
    }
    const ar = AR.indexOf(ch);
    if (ar !== -1) {
      out += String(ar);
      continue;
    }
    out += ch;
  }
  return out;
}

// ✅ track IME composition so Dari typing works
document.addEventListener(
  'compositionstart',
  (e) => {
    const el = e.target;
    if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA')) setComposing(el, true);
  },
  true
);

document.addEventListener(
  'compositionend',
  (e) => {
    const el = e.target;
    if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA')) setComposing(el, false);

    // after composition ends, normalize once
    if (!el) return;
    const v = el.value;
    if (!v) return;
    const nv = toEnglishDigits(v);
    if (nv !== v) {
      el.value = nv;
      el.dispatchEvent(new Event('input', { bubbles: true }));
    }
  },
  true
);

// ✅ normalize digits during normal typing (but NEVER during IME composition)
document.addEventListener(
  'input',
  (e) => {
    const el = e.target;
    if (!el) return;

    const tag = (el.tagName || '').toLowerCase();
    if (tag !== 'input' && tag !== 'textarea') return;

    const type = (el.getAttribute('type') || '').toLowerCase();
    if (type === 'password' || type === 'file') return;

    // ✅ critical: do nothing while composing
    if (isComposing(el, e)) return;

    const v = el.value;
    if (!v) return;

    const nv = toEnglishDigits(v);
    if (nv !== v) {
      const start = el.selectionStart;
      const end = el.selectionEnd;

      el.value = nv;
      el.dispatchEvent(new Event('input', { bubbles: true }));

      if (typeof start === 'number' && typeof end === 'number') {
        try {
          el.setSelectionRange(start, end);
        } catch {}
      }
    }
  },
  true
);
/* ========================================================= */

app.mount('#app');
initReverb();
