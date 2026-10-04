/* =============================================================================
   printBlankForm
   -----------------------------------------------------------------------------
   Prints a BLANK (empty) version of a form, keeping the on-screen design:
   sections, field order and field width all come from the live form DOM, so the
   same helper works for every form without any per-form field lists.

   - all values are empty (no data is copied from the form, not even dates)
   - everything stays inside one A4 page (the sections stretch to fill it)
   - labels come from the rendered form, so they are already localized
   - direction follows the active language (fa/ps -> rtl, en -> ltr) and the
     font family is the REPORT font family (the same one the report PDF exports
     use), with the same @font-face files loaded in the print window
   - the picture file picker is left out

   Usage (inside a form component):

     import { printBlankForm } from '@/utils/printBlankForm';

     <v-btn prepend-icon="mdi-printer" @click="printForm">{{ t('چاپ') }}</v-btn>

     function printForm(e) {
       printBlankForm(e, {
         onError: (msg) => {
           errorMessage.value = msg;
           showError.value = true;
         },
       });
     }
============================================================================= */
import i18n from '@/localization/Local.js';
import { reportFontFaceCss, reportFontForLang, whenPrintFontsReady } from '@/utils/reportFonts.js';
import { signatureLabels } from '@/utils/printSignature.js';

/* one full A4 page (297mm) minus the 8mm page margins, with a 5mm safety buffer */
const A4_PAGE_MIN_HEIGHT = '276mm';
const GRID_COLUMNS = 4;

/* the A4 content box in CSS px at 96dpi: 210mm - 2x9mm wide, 297mm - 2x8mm tall */
const A4_CONTENT_WIDTH_PX = 726;
const A4_CONTENT_HEIGHT_PX = 1043;

const CONTROL_SELECTOR = '.v-input, input, textarea, select, [contenteditable="true"]';
const SECTION_SELECTOR =
  '.section-header, .section-title, .v-card-title, h2, h3, h4, legend, [data-section-title]';
const PHOTO_LABEL = /^(تصویر|عکس|انځور|photo|image|avatar|picture)/i;

/* ============================== helpers ============================== */
function esc(v) {
  return String(v ?? '').replace(/[&<>"']/g, (m) => {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return map[m];
  });
}

function normalize(text) {
  return String(text ?? '')
    .replace(/\s+/g, ' ')
    .trim();
}

function isVisible(el) {
  if (!el || typeof el.getClientRects !== 'function') return false;
  if (el.getClientRects().length === 0) return false;
  let node = el;
  while (node && node.nodeType === 1) {
    const style = window.getComputedStyle(node);
    if (style.display === 'none' || style.visibility === 'hidden') return false;
    node = node.parentElement;
  }
  return true;
}

function domOrder(a, b) {
  if (a === b) return 0;
  const pos = a.compareDocumentPosition(b);
  if (pos & Node.DOCUMENT_POSITION_FOLLOWING) return -1;
  if (pos & Node.DOCUMENT_POSITION_PRECEDING) return 1;
  return 0;
}

function t(key) {
  try {
    return i18n.global.t(key);
  } catch (e) {
    return key;
  }
}

function currentLang() {
  const locale = i18n?.global?.locale?.value;
  if (locale === 'en') return 'en';
  if (locale === 'ps') return 'ps';
  return 'fa';
}

function classOf(el) {
  return typeof el?.className === 'string' ? el.className : '';
}

function resolveContainer(target, root) {
  if (root instanceof Element) return root;

  const el =
    target instanceof Element
      ? target
      : target?.currentTarget instanceof Element
        ? target.currentTarget
        : null;

  const fromEvent = el?.closest('form') || el?.closest('.v-form') || el?.closest('.v-card');
  if (fromEvent) return fromEvent;

  return (
    document.querySelector('.v-form') ||
    document.querySelector('.simple-form-card') ||
    document.querySelector('.v-card') ||
    null
  );
}

function pageTitle() {
  const el = document.querySelector('.v-app-bar .section-title, .v-toolbar .section-title');
  return normalize(el?.textContent);
}

function pageSubtitle() {
  const el = document.querySelector('.v-app-bar .section-subtitle, .v-toolbar .section-subtitle');
  return normalize(el?.textContent);
}

/* ============================ field reading ============================ */
function fieldLabel(control, col) {
  let text = normalize(control.querySelector('.v-label, label, legend')?.textContent);

  if (!text) text = normalize(control.getAttribute('aria-label'));

  if (!text && col) {
    const siblings = col.querySelectorAll(CONTROL_SELECTOR);
    if (siblings.length === 1) text = normalize(col.querySelector('.v-label, label, legend')?.textContent);
  }

  if (!text) {
    const input = control.querySelector('input, textarea, select');
    text = normalize(input?.getAttribute('placeholder') || control.getAttribute('placeholder'));
  }

  if (!text && col && col.querySelectorAll(CONTROL_SELECTOR).length === 1) {
    text = normalize(col.textContent);
  }

  return text.replace(/[:：]\s*$/, '');
}

function fieldKind(control) {
  const cls = classOf(control);
  if (/v-radio-group/.test(cls)) return 'options';
  if (/v-checkbox|v-switch|v-selection-control|v-radio/.test(cls)) return 'check';
  if (control.matches?.('input[type="checkbox"], input[type="radio"]')) return 'check';
  if (control.matches?.('textarea') || /v-textarea/.test(cls)) return 'text';
  return 'box';
}

function optionTexts(control) {
  return [...control.querySelectorAll('.v-radio, .v-selection-control')]
    .map((el) => normalize(el.querySelector('.v-label, label')?.textContent))
    .filter(Boolean);
}

function isPhotoField(control, col, label) {
  const scope = col || control;
  if (/\bpicture-picker\b/.test(classOf(scope))) return true;
  if (scope.querySelector?.('.picture-picker, .v-file-input, input[type="file"]')) return true;
  if (scope.querySelector?.('img, video, canvas')) return true;
  return PHOTO_LABEL.test(label);
}

/* field width in the printed 4-column grid, taken from the on-screen v-col */
function spanFromCol(col) {
  const cls = classOf(col);
  const match = /v-col-(?:xs|sm|md|lg|xl)-(\d+)/.exec(cls) || /v-col-(\d+)/.exec(cls);
  const size = match ? Number(match[1]) : 12;
  return Math.min(GRID_COLUMNS, Math.max(1, Math.round(size / (12 / GRID_COLUMNS))));
}

/* ============================== collector ============================== */
export function collectBlankFormSections(root) {
  if (!root) return [];

  /* --- section headings in DOM order (skip a heading nested in another one) --- */
  const markers = [];
  root.querySelectorAll(SECTION_SELECTOR).forEach((el) => {
    if (!isVisible(el)) return;
    if (markers.some((m) => m.el !== el && m.el.contains(el))) return;

    const titleEl = el.querySelector('.section-title, .v-card-title') || el;
    const title = normalize(titleEl.textContent).replace(/\s*[:：]\s*$/, '');
    if (!title) return;

    markers.push({
      type: 'section',
      el,
      title,
      subtitle: normalize(el.querySelector('.section-subtitle')?.textContent),
    });
  });

  /* --- visible, labelled controls (innermost control of each field) --- */
  const fields = [];
  const collected = [];
  root.querySelectorAll(CONTROL_SELECTOR).forEach((control) => {
    if (!isVisible(control)) return;
    if (collected.some((c) => c.contains(control))) return;

    const col = control.closest('.v-col') || control.parentElement;
    const label = fieldLabel(control, col);
    if (!label) return;
    if (isPhotoField(control, col, label)) return;

    const kind = fieldKind(control);
    collected.push(control);
    fields.push({
      type: 'field',
      el: control,
      label,
      span: spanFromCol(col),
      kind,
      options: kind === 'options' ? optionTexts(control) : [],
    });
  });

  /* --- walk everything in document order and group into sections --- */
  const sections = [];
  let current = null;

  [...markers, ...fields].sort((a, b) => domOrder(a.el, b.el)).forEach((item) => {
    if (item.type === 'section') {
      current = { title: item.title, subtitle: item.subtitle, fields: [] };
      sections.push(current);
      return;
    }
    if (!current) {
      current = { title: '', subtitle: '', fields: [] };
      sections.push(current);
    }
    current.fields.push(item);
  });

  return sections.filter((section) => section.fields.length);
}

/* ============================ document builder ============================ */
function fieldHtml(field) {
  const span = field.span > 1 ? ` style="grid-column: span ${field.span};"` : '';

  if (field.kind === 'check') {
    return `
      <div class="pf-field pf-field-check"${span}>
        <div class="pf-check-line">
          <span class="pf-check-box"></span>
          <span class="pf-label pf-label-check">${esc(field.label)}</span>
        </div>
      </div>`;
  }

  const options = field.options?.length
    ? `<div class="pf-options">${field.options.map(esc).join(' · ')}</div>`
    : '';

  return `
      <div class="pf-field"${span}>
        <div class="pf-label">${esc(field.label)}</div>
        ${options}
        <div class="pf-box"></div>
      </div>`;
}

/* ============================ signature footer ============================ */
/*
  Every printed form ends with the same signature block:
  امضا مسئول — امضا ثبت کننده — تاریخ
  Labels come from the shared printSignature util, in the active language.
*/
export { signatureLabels };

export function buildSignatureFooterHtml() {
  return `
      <div class="pf-signatures">
        ${signatureLabels()
          .map(
            (label) => `
          <div class="pf-sign">
            <div class="pf-sign-label">${esc(label)}</div>
            <div class="pf-sign-area"></div>
          </div>`,
          )
          .join('')}
      </div>`;
}

export function buildBlankFormHtml(options = {}) {
  const lang = options.lang || currentLang();
  const dir = lang === 'en' ? 'ltr' : 'rtl';
  const fontFamily = reportFontForLang(lang);

  const title = normalize(options.title) || normalize(pageTitle());
  const subtitle = normalize(options.subtitle) || normalize(pageSubtitle());
  const sections = options.sections?.length ? options.sections : [];

  const sectionsHtml = sections
    .map((section) => {
      /* grow each section in proportion to its rows so the blank form fills the A4 */
      const totalSpan = section.fields.reduce((sum, f) => sum + (f.span || 1), 0);
      const grow = Math.max(1, Math.ceil(totalSpan / GRID_COLUMNS));

      const head =
        section.title || section.subtitle
          ? `<div class="pf-head">
            <span class="pf-head-title">${esc(section.title)}</span>
            ${section.subtitle ? `<span class="pf-head-sub">${esc(section.subtitle)}</span>` : ''}
          </div>`
          : '';

      return `
      <section class="pf-section" style="flex-grow: ${grow};">
        ${head}
        <div class="pf-grid">${section.fields.map(fieldHtml).join('')}</div>
      </section>`;
    })
    .join('');

  return `<!DOCTYPE html>
<html lang="${lang}" dir="${dir}">
  <head>
    <meta charset="UTF-8" />
    <title>${esc(title || t('چاپ'))}</title>
    <style>
      ${reportFontFaceCss()}

      :root {
        --app-font: ${fontFamily};
        --v-font-family: var(--app-font);
      }

      @page {
        size: A4 portrait;
        margin: 8mm 9mm;
      }

      * {
        box-sizing: border-box;
        font-family: var(--app-font) !important;
      }

      html,
      body {
        margin: 0;
        padding: 0;
      }

      body {
        font-family: var(--app-font) !important;
        direction: ${dir};
        color: #111;
        background: #fff;
        font-size: 10px;
        line-height: 1.3;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .pf-page {
        display: flex;
        flex-direction: column;
        width: 100%;
        min-height: ${A4_PAGE_MIN_HEIGHT};
      }

      .pf-title-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #1976d2;
        border-radius: 10px;
        background: #f5f9ff;
        padding: 6px 10px;
        margin-bottom: 6px;
      }

      .pf-title-badge {
        flex: 0 0 auto;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #1976d2;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
      }

      .pf-title {
        font-size: 13.5px;
        font-weight: 700;
      }

      .pf-subtitle {
        font-size: 9px;
        color: #6b6b6b;
        margin-top: 1px;
      }

      .pf-section {
        display: flex;
        flex-direction: column;
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 4px;
        page-break-inside: avoid;
        break-inside: avoid;
      }

      .pf-section:last-child {
        margin-bottom: 0;
      }

      .pf-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 2px 8px;
        background: #f4f6fa;
        border-bottom: 1px solid #dcdcdc;
        padding: 3px 8px;
      }

      .pf-head-title {
        font-size: 11px;
        font-weight: 700;
      }

      .pf-head-sub {
        font-size: 9px;
        color: #777;
      }

      .pf-grid {
        display: grid;
        grid-template-columns: repeat(${GRID_COLUMNS}, 1fr);
        grid-auto-rows: 1fr;
        flex: 1 1 auto;
        gap: 3px 6px;
        padding: 4px 8px 5px;
      }

      .pf-field {
        display: flex;
        flex-direction: column;
        min-width: 0;
      }

      .pf-field-check {
        justify-content: center;
      }

      .pf-check-line {
        display: flex;
        align-items: center;
        gap: 5px;
        min-width: 0;
      }

      .pf-check-box {
        flex: 0 0 auto;
        width: 11px;
        height: 11px;
        border: 1px solid #9e9e9e;
        border-radius: 2px;
        background: #fff;
      }

      .pf-label {
        font-size: 9.5px;
        font-weight: 700;
        color: #444;
        margin-bottom: 1px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .pf-label-check {
        margin-bottom: 0;
      }

      .pf-options {
        font-size: 8.5px;
        color: #777;
        margin-bottom: 1px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .pf-box {
        flex: 1 1 auto;
        min-height: 16px;
        border: 1px solid #9e9e9e;
        border-radius: 3px;
        background: #fff;
      }

      .pf-signatures {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        padding: 6px 8px 7px;
        margin-top: 6px;
        flex: 0 0 auto;
        page-break-inside: avoid;
        break-inside: avoid;
      }

      .pf-sign {
        display: flex;
        flex-direction: column;
        min-width: 0;
      }

      .pf-sign-label {
        font-size: 9.5px;
        font-weight: 700;
        color: #444;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .pf-sign-area {
        flex: 1 1 auto;
        min-height: 30px;
        border: 1px solid #9e9e9e;
        border-radius: 3px;
        background: #fff;
      }
    </style>
  </head>
  <body>
    <div class="pf-page">
      <div class="pf-title-bar">
        <div class="pf-title-badge">۳۳۳</div>
        <div>
          <div class="pf-title">${esc(title)}</div>
          ${subtitle ? `<div class="pf-subtitle">${esc(subtitle)}</div>` : ''}
        </div>
      </div>
      ${sectionsHtml}
      ${buildSignatureFooterHtml()}
    </div>
  </body>
</html>
`;
}

/* ============================== printing ============================== */
/*
  A form with few fields stretches to fill the sheet; a very large one could
  spill onto a second page, so shrink it just enough to keep a single page.
*/
export function fitToSinglePage(page, zoomTarget) {
  if (!page) return 1;

  page.style.width = `${A4_CONTENT_WIDTH_PX}px`;
  page.style.minHeight = '0px';
  const naturalHeight = page.getBoundingClientRect().height;

  let zoom = 1;
  if (naturalHeight > A4_CONTENT_HEIGHT_PX + 1) {
    zoom = Math.max(0.55, Math.floor((A4_CONTENT_HEIGHT_PX / naturalHeight) * 100) / 100 - 0.01);
    if (zoomTarget) zoomTarget.style.zoom = String(zoom);
  }

  page.style.width = `${Math.round(A4_CONTENT_WIDTH_PX / zoom)}px`;
  page.style.minHeight = `${Math.floor(A4_CONTENT_HEIGHT_PX / zoom)}px`;

  return zoom;
}

export function printBlankForm(target, options = {}) {
  const { onError, root, ...rest } = options;

  const container = resolveContainer(target, root);
  const sections = collectBlankFormSections(container);

  if (!container || !sections.length) {
    onError?.(t('در این فرم چیزی برای چاپ پیدا نشد'));
    return false;
  }

  const html = buildBlankFormHtml({ ...rest, sections });
  const win = window.open('', '_blank');

  if (!win) {
    onError?.(t('باز کردن صفحه چاپ ممکن نشد'));
    return false;
  }

  win.document.open();
  win.document.write(html);
  win.document.close();
  win.focus();

  whenPrintFontsReady(win, () => {
    try {
      fitToSinglePage(win.document.querySelector('.pf-page'), win.document.documentElement);
      win.print();
    } catch (e) {
      console.error(e);
    }
  });

  return true;
}

export default printBlankForm;
