// main.js
import { createApp } from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import router from './router/router'
import { createPinia } from 'pinia'
import i18n from './localization/Local.js'
import Toast from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import './axios'

import '@fontsource/amiri/400.css'
import '@fontsource/amiri/700.css'
import '@fontsource/scheherazade-new/400.css'
import '@fontsource/scheherazade-new/700.css'

// ✅ Date libs
import dayjs from 'dayjs'
import jalaliday from 'jalaliday'
dayjs.extend(jalaliday) // enable Jalali support globally

// ✅ Jalali picker (Vue 3)
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker'
// register as <PersianDatePicker ... />

// ✅ Hijri/Gregorian picker (Vue 3)
import HijriGregorianDatepicker from 'vue3-hijri-gregorian-datepicker'
import 'vue3-hijri-gregorian-datepicker/dist/style.css'
// register as <HijriGregorianDatepicker ... />

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(Toast)
app.use(vuetify)
app.use(i18n)

// 🔗 Register pickers globally so you can use them anywhere
app.component('PersianDatePicker', Vue3PersianDatetimePicker)
app.component('HijriGregorianDatepicker', HijriGregorianDatepicker)

app.mount('#app')
