import { createApp } from 'vue';
import App from './App.vue';
import vuetify from './plugins/vuetify';
import router from './router/router';
import { createPinia } from 'pinia';
import i18n from './localization/Local.js';
import Toast from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import VueApexCharts from 'vue3-apexcharts';
import { ensureRandomUUID } from './polyfills/cryptoRandomUUID';
import dayjs from 'dayjs';
import jalaliday from 'jalaliday';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import HijriGregorianDatepicker from 'vue3-hijri-gregorian-datepicker';
import 'vue3-hijri-gregorian-datepicker/dist/style.css';
import { useChatRepository } from './stores/ChatRepository.js';
import axios from 'axios';
import { initReverb } from './plugins/reverb.js';
import NoAutofill from '@/plugins/noAutofill';

ensureRandomUUID();
dayjs.extend(jalaliday);

/* =========================================
   Axios
========================================= */
axios.defaults.withCredentials = false;
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const API_BASE = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';
axios.defaults.baseURL = `${API_BASE}/api`;

axios.interceptors.request.use(
  (config) => {
    const token = sessionStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

/* =========================================
   Optional startup tweaks
========================================= */
document.addEventListener('DOMContentLoaded', () => {
  document.body.style.zoom = '100%';
});

/* =========================================
   Vue app
========================================= */
const app = createApp(App);
const pinia = createPinia();

app.use(NoAutofill);
app.use(pinia);
app.use(router);
app.use(vuetify);
app.use(i18n);
app.use(Toast);
app.use(VueApexCharts);

app.component('PersianDatePicker', Vue3PersianDatetimePicker);
app.component('HijriGregorianDatepicker', HijriGregorianDatepicker);

/* =========================================
   Reverb + Chat badge
   Keep, but guarded
========================================= */
try {
  initReverb();
} catch (error) {
  console.warn('Reverb init skipped:', error);
}

try {
  useChatRepository(pinia).initBadge();
} catch (error) {
  console.warn('Chat badge init skipped:', error);
}

/* =========================================
   Global Persian/Arabic digits -> English
========================================= */
const FA = '۰۱۲۳۴۵۶۷۸۹';
const AR = '٠١٢٣٤٥٦٧٨٩';

function setComposing(el, value) {
  try {
    el.__digit_norm_composing__ = value;
  } catch {}
}

function isComposing(el, evt) {
  return !!(evt?.isComposing || el?.__digit_norm_composing__);
}

function toEnglishDigits(value) {
  if (value === null || value === undefined) return value;

  const str = String(value);
  let hasArabicDigits = false;

  for (const ch of str) {
    if (FA.includes(ch) || AR.includes(ch)) {
      hasArabicDigits = true;
      break;
    }
  }

  if (!hasArabicDigits) return str;

  let out = '';

  for (const ch of str) {
    const faIndex = FA.indexOf(ch);
    if (faIndex !== -1) {
      out += String(faIndex);
      continue;
    }

    const arIndex = AR.indexOf(ch);
    if (arIndex !== -1) {
      out += String(arIndex);
      continue;
    }

    out += ch;
  }

  return out;
}

document.addEventListener(
  'compositionstart',
  (e) => {
    const el = e.target;
    if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA')) {
      setComposing(el, true);
    }
  },
  true
);

document.addEventListener(
  'compositionend',
  (e) => {
    const el = e.target;

    if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA')) {
      setComposing(el, false);
    }

    if (!el) return;

    const currentValue = el.value;
    if (!currentValue) return;

    const normalizedValue = toEnglishDigits(currentValue);
    if (normalizedValue !== currentValue) {
      el.value = normalizedValue;
      el.dispatchEvent(new Event('input', { bubbles: true }));
    }
  },
  true
);

document.addEventListener(
  'input',
  (e) => {
    const el = e.target;
    if (!el) return;

    const tag = (el.tagName || '').toLowerCase();
    if (tag !== 'input' && tag !== 'textarea') return;

    const type = (el.getAttribute('type') || '').toLowerCase();
    if (type === 'password' || type === 'file') return;

    if (isComposing(el, e)) return;

    const currentValue = el.value;
    if (!currentValue) return;

    const normalizedValue = toEnglishDigits(currentValue);
    if (normalizedValue !== currentValue) {
      const start = el.selectionStart;
      const end = el.selectionEnd;

      el.value = normalizedValue;
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

app.mount('#app');