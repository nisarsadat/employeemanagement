// src/plugins/axiosDigits.js
import axios from "axios";

const fa = ["۰","۱","۲","۳","۴","۵","۶","۷","۸","۹"];
const ar = ["٠","١","٢","٣","٤","٥","٦","٧","٨","٩"];

function toEnDigits(str) {
  if (str === null || str === undefined) return str;
  if (typeof str !== "string") return str;

  let s = str;
  s = s.replace(/[۰-۹]/g, (ch) => String(fa.indexOf(ch)));
  s = s.replace(/[٠-٩]/g, (ch) => String(ar.indexOf(ch)));
  return s;
}

function deepToEnDigits(val) {
  if (val === null || val === undefined) return val;

  if (typeof val === "string") return toEnDigits(val);

  if (Array.isArray(val)) return val.map(deepToEnDigits);

  if (typeof val === "object") {
    const out = {};
    for (const k in val) out[k] = deepToEnDigits(val[k]);
    return out;
  }

  return val;
}

export function installAxiosDigitNormalizer() {
  axios.interceptors.request.use((config) => {
    if (config.data) config.data = deepToEnDigits(config.data);
    if (config.params) config.params = deepToEnDigits(config.params);
    return config;
  });
}
