import { createI18n } from "vue-i18n";
import EN from "./EN.json";
import DARI from "./DARI.json";
import PS from "./PS.json"; // ✅ add this

const savedLang = localStorage.getItem('apartment-lang') || 'fa';

const messages = {
  en: EN,
  fa: DARI,
  ps: PS, // ✅ add this
};

const i18n = createI18n({
  legacy: false,
  locale: savedLang,
  fallbackLocale: 'fa',
  messages,
});

export default i18n;
