/* =============================================================================
   reportFonts
   -----------------------------------------------------------------------------
   The font family (and @font-face set) the report PDF exports use, kept in one
   place so a blank printed form looks exactly like a printed report:

     'Amiri' -> 'Scheherazade New' -> 'Noto Naskh Arabic' -> 'Noto Sans Arabic'
     -> 'Tahoma' -> system UI / Segoe UI / Roboto / Helvetica / Arial

   The Arabic-script TTFs are served straight from public/fonts, matching the
   report exporters' inline @font-face declarations. A print document opened with
   window.open('', '_blank') has an about:blank base URL, so the URLs here are
   made absolute from window.location.origin.
============================================================================= */

/* the report PDF font stack, verbatim (same as app.css --app-font / the report repos) */
export const REPORT_FONT_STACK =
  `'Amiri','Scheherazade New','Noto Naskh Arabic','Noto Sans Arabic','Tahoma',` +
  `ui-sans-serif,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif`;

/* Latin-only fallback: the tail of the report stack (Amiri's Latin glyphs are a
   poor match for an all-English form, so English keeps the report's sans tail) */
export const REPORT_LATIN_FONT_STACK =
  `ui-sans-serif,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif`;

/* the same faces the report exports declare inline */
const FONT_FACES = [
  { family: 'Amiri', file: 'Amiri-Regular.ttf', weight: 400 },
  { family: 'Amiri', file: 'Amiri-Bold.ttf', weight: 700 },
  { family: 'Noto Naskh Arabic', file: 'NotoNaskhArabic-Regular.ttf', weight: 400 },
  { family: 'Noto Naskh Arabic', file: 'NotoNaskhArabic-Bold.ttf', weight: 700 },
];

function fontFilesBase() {
  try {
    if (typeof window !== 'undefined' && window.location?.origin) return window.location.origin;
  } catch (e) {
    /* ignore */
  }
  return '';
}

/* the report font family for the active language */
export function reportFontForLang(lang) {
  return lang === 'en' ? REPORT_LATIN_FONT_STACK : REPORT_FONT_STACK;
}

/* @font-face rules for a standalone (about:blank) print document */
export function reportFontFaceCss() {
  const base = fontFilesBase();
  return FONT_FACES.map(
    ({ family, file, weight }) =>
      `@font-face {\n` +
      `        font-family: '${family}';\n` +
      `        src: url('${base}/fonts/${file}') format('truetype');\n` +
      `        font-weight: ${weight};\n` +
      `        font-style: normal;\n` +
      `        font-display: swap;\n` +
      `      }`,
  ).join('\n\n      ');
}

/* the font families a print document must actually load before printing */
export const REPORT_FONT_LOAD_SPECS = [
  '400 10px Amiri',
  '700 10px Amiri',
  '400 10px "Noto Naskh Arabic"',
  '700 10px "Noto Naskh Arabic"',
];

/* =============================================================================
   Wait for a print window's fonts (the report faces are fetched from /fonts),
   then run `cb` exactly once. Falls back to printing if loading stalls.
============================================================================= */
export function whenPrintFontsReady(win, cb) {
  let done = false;
  const fire = () => {
    if (done) return;
    done = true;
    cb();
  };

  const cap = (promise, ms) => Promise.race([promise, new Promise((resolve) => setTimeout(resolve, ms))]);

  let loads = Promise.resolve([]);
  try {
    const faces = win?.document?.fonts;
    if (faces?.load) loads = Promise.allSettled(REPORT_FONT_LOAD_SPECS.map((spec) => faces.load(spec)));
  } catch (e) {
    /* ignore */
  }

  Promise.resolve(loads)
    .then(() => cap(win?.document?.fonts?.ready || Promise.resolve(), 2500))
    .then(fire)
    .catch(fire);

  /* hard safety net: never leave the user without a print dialog */
  setTimeout(fire, 4000);
}
