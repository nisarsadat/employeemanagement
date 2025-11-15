// src/utils/assetUrl.js
export function toAbsoluteUrl(val) {
  if (!val) return null;

  // already absolute
  if (/^https?:\/\//i.test(val)) return val;

  const origin =
    typeof window !== 'undefined' && window.location ? window.location.origin : '';

  // starts with /storage/...
  if (val.startsWith('/')) return `${origin}${val}`;

  // plain "storage/..." etc.
  return `${origin}/${val}`;
}
