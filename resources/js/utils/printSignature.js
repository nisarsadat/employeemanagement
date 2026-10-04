/* =============================================================================
   printSignature
   -----------------------------------------------------------------------------
   One place for the signature block every printed form / PDF / Word export ends
   with:

     امضا مسئول — امضا ثبت کننده — تاریخ

   The keys are the same ones the filled-form prints already used, so the labels
   come out in the active language (fa / ps / en) without any new translations.
============================================================================= */
import i18n from '@/localization/Local.js';

function t(key) {
  try {
    return i18n.global.t(key);
  } catch (e) {
    return key;
  }
}

/* the three labels, in the active language */
export function signatureLabels() {
  return [t('امضا مسئول'), t('امضا ثبت کننده'), t('تاریخ')];
}

/*
  Ready-to-insert footer with inline styles, so it can be dropped into any
  document builder without touching that file's stylesheets.

  `escape` is optional: pass the file's own escaper when it has one.
*/
export function signatureFooterHtml({ escape, marginTop = 18 } = {}) {
  const safe = typeof escape === 'function' ? escape : (v) => String(v ?? '');

  const cells = signatureLabels()
    .map(
      (label) =>
        `<div style="flex:1 1 0;border-top:1px solid #444;padding-top:6px;text-align:center;">${safe(
          label,
        )}</div>`,
    )
    .join('');

  return `<div style="display:flex;gap:24px;justify-content:space-between;margin-top:${marginTop}px;font-size:12px;">${cells}</div>`;
}

export default signatureFooterHtml;
