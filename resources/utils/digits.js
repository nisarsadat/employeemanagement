// src/utils/digits.js
const FA = ["۰","۱","۲","۳","۴","۵","۶","۷","۸","۹"];

export function toFaDigits(input) {
  if (input === null || input === undefined) return "";
  return String(input).replace(/\d/g, (d) => FA[Number(d)]);
}
