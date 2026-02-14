export function normalizeDigits(input) {
  if (input === null || input === undefined) return input;

  const s = String(input);

  const fa = '۰۱۲۳۴۵۶۷۸۹';
  const ar = '٠١٢٣٤٥٦٧٨٩';

  let out = '';
  for (const ch of s) {
    const iFa = fa.indexOf(ch);
    if (iFa !== -1) { out += String(iFa); continue; }

    const iAr = ar.indexOf(ch);
    if (iAr !== -1) { out += String(iAr); continue; }

    out += ch;
  }
  return out;
}

function walk(obj) {
  if (obj === null || obj === undefined) return obj;

  if (typeof obj === 'string') return normalizeDigits(obj);

  if (Array.isArray(obj)) return obj.map(walk);

  if (obj instanceof Date) return obj;

  if (obj instanceof File || obj instanceof Blob) return obj;

  if (obj && typeof obj === 'object') {
    const out = {};
    for (const k of Object.keys(obj)) out[k] = walk(obj[k]);
    return out;
  }

  return obj;
}

export function normalizeAnyPayload(data) {
  // JSON object
  if (data && typeof data === 'object' && !(data instanceof FormData)) {
    return walk(data);
  }

  // FormData
  if (data instanceof FormData) {
    const fd = new FormData();
    for (const [k, v] of data.entries()) {
      if (typeof v === 'string') fd.append(k, normalizeDigits(v));
      else fd.append(k, v);
    }
    return fd;
  }

  // primitives
  if (typeof data === 'string') return normalizeDigits(data);
  return data;
}
