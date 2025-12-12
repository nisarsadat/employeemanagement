// main.js
import { createApp } from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import router from './router/router'
import { createPinia } from 'pinia'
import i18n from './localization/Local.js'
import Toast from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import VueApexCharts from 'vue3-apexcharts'
import '@fontsource/amiri/400.css'
import '@fontsource/amiri/700.css'
import '@fontsource/scheherazade-new/400.css'
import '@fontsource/scheherazade-new/700.css'

// ✅ Date libs
import dayjs from 'dayjs'
import jalaliday from 'jalaliday'
dayjs.extend(jalaliday)

// ✅ pickers
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker'
import HijriGregorianDatepicker from 'vue3-hijri-gregorian-datepicker'
import 'vue3-hijri-gregorian-datepicker/dist/style.css'

// ✅ Chat store
import { useChatRepository } from './stores/ChatRepository.js'

// ✅ Axios for Bearer token mode
import axios from 'axios'
axios.defaults.withCredentials = false
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// ✅ API base
const API_BASE = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'
axios.defaults.baseURL = `${API_BASE}/api`

// ✅ attach token for all API requests
axios.interceptors.request.use((config) => {
  const token = sessionStorage.getItem('token') // ✅ same place
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

document.addEventListener('DOMContentLoaded', function () {
  document.body.style.zoom = '100%'
})

/* =========================================================
   ✅ Reverb (from your component)
========================================================= */
import { initReverb } from './plugins/reverb.js'
initReverb()
/* ========================================================= */

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(vuetify)
app.use(i18n)
app.use(Toast)
app.use(VueApexCharts)

// ✅ register pickers
app.component('PersianDatePicker', Vue3PersianDatetimePicker)
app.component('HijriGregorianDatepicker', HijriGregorianDatepicker)

// ✅ start badge polling + presence
useChatRepository(pinia).initBadge()

app.mount('#app')
