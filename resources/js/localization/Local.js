import { createI18n } from "vue-i18n";
import EN from "./EN.json";
import DARI from "./DARI.json";
const savedLang = localStorage.getItem('apartment-lang') || 'fa';
const messages = {
    en: EN,
    fa: DARI,

};

const i18n = createI18n({
  legacy: false,
  locale: savedLang,
  fallbackLocale: 'fa',
  messages,
});

export default i18n;
