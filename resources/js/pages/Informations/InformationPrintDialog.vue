<!-- src/pages/Informations/InformationPrint.vue -->
<template>
  <AppBar :title="t('اطلاع فورم')" @back="goBack">
    <template #actions>
      <div class="top-actions no-print">
        <button class="btn btn-pdf" type="button" @click="downloadPdf">
          <span class="dot dot-pdf" /> PDF
        </button>
        <button class="btn btn-word" type="button" @click="downloadWord">
          <span class="dot dot-word" /> Word
        </button>
      </div>
    </template>
  </AppBar>

  <!-- ✅ ALWAYS RTL -->
  <div class="page-shell" dir="rtl">
    <div ref="printArea" class="paper" dir="rtl">
      <!-- Header -->
      <header class="hdr">
        <!-- ✅ Meta pills must be on LEFT -->
        <div class="hdr-right">
          <div class="meta-pill">
            <div class="meta-k">{{ t('د اطلاع د ثبت نیټه') }}</div>
            <div class="meta-v">{{ formattedInformationDate || '—' }}</div>
          </div>
          <div class="meta-pill">
            <div class="meta-k">{{ t('د اطلاع نوعیت') }}</div>
            <div class="meta-v">{{ item.syasi_info_type || item.jenayee_info_type || '—' }}</div>
          </div>
          <div class="meta-pill">
            <div class="meta-k">{{ t('مسلسل شمیره') }}</div>
            <div class="meta-v">{{ item.id || '—' }}</div>
          </div>
        </div>

        <!-- ✅ بسم الله in exact center -->
        <div class="hdr-center">
          <div class="bismillah">بسم الله الرحمن الرحیم</div>
        </div>

        <!-- ✅ Brand must be on RIGHT -->
        <div class="hdr-left ml-36 mr-12">
          <div class="brand">
            <img :src="logoSrc" class="brand-logo" alt="Logo" />
            <div class="brand-text">
              <div class="brand-title">{{ systemName }}</div>
              <div class="brand-sub">{{ t('اطلاع') }}</div>
            </div>
          </div>
        </div>
      </header>

      <div class="rule" />

      <!-- ✅ FIVE FIELDS IN ONE ROW -->
      <section class="grid grid-5">
        <div class="field">
          <div class="k">{{ t('کود منبع') }}</div>
          <div class="v strong clamp-2">{{ item.resource_code_number || '—' }}</div>
        </div>

        <div class="field">
          <div class="k">{{ t('کود کارمند') }}</div>
          <div class="v strong clamp-2">{{ item.office_employee_code_number || '—' }}</div>
        </div>

        <div class="field">
          <div class="k">{{ t('د موثقیت درجه') }}</div>
          <div class="v strong">{{ item.information_degree ?? '—' }}</div>
        </div>

        <div class="field">
          <div class="k">{{ t('د اعتماد میزان') }}</div>
          <div class="v strong">{{ item.trust_percentage || '—' }}</div>
        </div>

        <div class="field">
          <div class="k">{{ t('د منبع لاسرسی') }}</div>
          <div class="v strong clamp-2">{{ item.resource_lasrasy || '—' }}</div>
        </div>
      </section>

      <main class="content">
        <!-- ✅ Tafseel should take MORE height (main filler) -->
        <section class="section tafseel-section">
          <div class="section-h">
            <div class="section-title">{{ t('تفصیل') }}</div>
            <div class="chip">{{ t('کلمات') }}: {{ wordCount(item.tafseel) }}</div>
          </div>
          <div class="box box-text grow-box">
            <div class="pre tafseel-pre">
              {{ item.tafseel || '—' }}
            </div>
          </div>
        </section>

        <!-- ✅ TWO ROWS (STACKED), NOT TWO COLUMNS -->
        <section class="two two-rows">
          <!-- Row 1 -->
          <div class="section">
            <div class="section-h">
              <div class="section-title">{{ t('اسناد او شواهد') }}</div>
              <div class="chip">{{ docsCount }}</div>
            </div>

            <div class="box box-text grow-box">
              <div class="pre clamp-10">{{ itemDocsNote }}</div>
            </div>
          </div>
        </section>

        <!-- Row 2 -->
        <section class="section manager-section">
          <div class="section">
            <div class="section-h">
              <div class="section-title">{{ t('د مدیر نظر') }}</div>
            </div>
            <div class="box box-text grow-box">
              <div class="pre">{{ item.manager_comment || '—' }}</div>
            </div>
          </div>
        </section>

        <section class="section direction-section">
          <div class="section-h">
            <div class="section-title">{{ t('هدایت') }}</div>
          </div>
          <div class="box box-text grow-box">
            <div class="pre clamp-8">{{ item.lead_drirection || '—' }}</div>
          </div>
        </section>
      </main>

      <!-- ✅ Bottom-right notes -->
      <div class="print-notes">
        <div class="print-note">{{ t('اشد محرم') }}</div>
        <div class="print-note">{{ t('با احترام میریت ریاست ۳۳۳') }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted } from 'vue';
  import { useRoute } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import router from '../../router/router';
  import AppBar from '../../components/AppBar.vue';
  import { useDatabaseRepository } from '@/stores/DatabaseRepository';
  import { useCalendar } from '../../../composables/useCalendar';
  import dayjs from 'dayjs';

  const route = useRoute();
  const repo = useDatabaseRepository();
  const { t, locale } = useI18n();

  function goBack() {
    router.push('/app/informations');
  }

  const printArea = ref(null);
  const logoSrc = '/logo.jpeg';

  const id = computed(() => route.params.id);

  const item = computed(() => {
    const raw = repo.Information;
    if (Array.isArray(raw)) return raw[0] || {};
    return raw || {};
  });

  const docsCount = computed(() =>
    Array.isArray(item.value?.documents) ? item.value.documents.length : 0
  );

  const itemDocsNote = computed(() => {
    const v = item.value?.docs_note;
    return v && String(v).trim() ? String(v) : '—';
  });

  const systemName = ref('۳۳۳ مدیریت');
  const systemId = ref('');
  const printedAt = ref('');

  function nowStamp() {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');
    return `${y}/${m}/${day} ${hh}:${mm}`;
  }

  function randomHex(len = 4) {
    const bytes = new Uint8Array(Math.ceil(len / 2));
    crypto.getRandomValues(bytes);
    return Array.from(bytes)
      .map((b) => b.toString(16).padStart(2, '0'))
      .join('')
      .slice(0, len)
      .toUpperCase();
  }

  function generateSystemId() {
    return `NDS-${randomHex(4)}-${randomHex(4)}-${randomHex(4)}`;
  }

  function refreshPrintMeta() {
    printedAt.value = nowStamp();
    systemId.value = generateSystemId();
  }

  refreshPrintMeta();

  const { formatDisplay } = useCalendar();

  const formattedInformationDate = computed(() => {
    const raw = item.value?.information_saved_date;
    if (!raw) return '';

    const normalized = String(raw).trim().replaceAll('/', '-');
    const iso = dayjs(normalized).format('YYYY-MM-DD');

    return formatDisplay(iso, locale.value || 'fa');
  });

  function wordCount(text) {
    if (!text) return 0;
    return String(text).trim().split(/\s+/).filter(Boolean).length;
  }

  onMounted(async () => {
    if (id.value && typeof repo.FetchInformationData === 'function') {
      await repo.FetchInformationData(id.value);
    }
  });

  function buildInnerHtml() {
    return printArea.value ? printArea.value.outerHTML : '';
  }

  function getProjectFontFamily() {
    try {
      const pageShell = document.querySelector('.page-shell');
      const paper = printArea.value;

      const pageShellFont = pageShell ? window.getComputedStyle(pageShell).fontFamily : '';
      const paperFont = paper ? window.getComputedStyle(paper).fontFamily : '';
      const bodyFont = window.getComputedStyle(document.body).fontFamily;
      const rootFont = window.getComputedStyle(document.documentElement).fontFamily;

      return paperFont || pageShellFont || bodyFont || rootFont || 'inherit';
    } catch (e) {
      return 'inherit';
    }
  }

  function getProjectHeadAssets() {
    try {
      const nodes = Array.from(
        document.head.querySelectorAll('style, link[rel="stylesheet"], link[as="style"]')
      );

      return nodes
        .map((node) => {
          if (node.tagName.toLowerCase() === 'style') {
            return node.outerHTML;
          }

          if (node.tagName.toLowerCase() === 'link') {
            const href = node.getAttribute('href');
            if (!href) return '';

            const rel = node.getAttribute('rel') || 'stylesheet';
            return `<link rel="${rel}" href="${href}" />`;
          }

          return '';
        })
        .join('\n');
    } catch (e) {
      return '';
    }
  }

  function buildPrintCss() {
    const projectFontFamily = getProjectFontFamily();

    return `
    @page {
      size: A4 portrait;
      margin: 9mm;
    }

    html, body {
      width: 100%;
      min-width: 0;
      max-width: 100%;
      margin-top: 8 !important;
      margin-right: 8 !important;
      margin-left:  !important;
      margin-bottom: 8 !important;
      padding: 0 !important;
      overflow: hidden !important;
      background: #f8f6f4 !important;
      color: #111827;
      direction: rtl !important;
      text-align: right !important;
      unicode-bidi: plaintext !important;
      font-family: ${projectFontFamily} !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    *, *::before, *::after {
      box-sizing: border-box;
      font-family: ${projectFontFamily} !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .no-print {
      display: none !important;
    }

    .page-shell {
      min-height: auto !important;
      height: auto !important;
      padding: 0 !important;
      margin-top: 8 !important;
      margin-right: 8 !important;
      margin-left: 8 !important;
      margin-bottom: 8 !important;
      background: #f8f6f4 !important;
      overflow: hidden !important;
      font-family: ${projectFontFamily} !important;
    }

    .paper {
      position: relative !important;
      width: 100% !important;
      max-width: none !important;
      margin: 0 !important;
      background: #f8f6f4 !important;
      border-radius: 14px;
      border: 1px solid rgba(0, 0, 0, 0.08);
      box-shadow: none !important;
      padding: 10px !important;
      font-family: ${projectFontFamily} !important;

      display: flex !important;
      flex-direction: column !important;

      height: calc(297mm - 18mm) !important;
      min-height: calc(297mm - 18mm) !important;
      max-height: calc(297mm - 18mm) !important;

      overflow: hidden !important;
      break-inside: avoid !important;
      page-break-inside: avoid !important;
    }

    .hdr {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
      gap: 10px;
      align-items: center;
      background: #C4DFDF !important;
      border-radius: 12px;
      padding: 8px;
      flex: 0 0 auto;
      min-height: 0;
      overflow: hidden;
    }

    .hdr-right {
      grid-column: 1;
      justify-self: start;
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      flex-direction: row-reverse;
      justify-content: flex-start;
      align-content: flex-start;
      min-width: 0;
    }

    .hdr-center {
      grid-column: 2;
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 0;
      padding-inline: 8px;
    }

    .hdr-left {
      grid-column: 3;
      justify-self: end;
      min-width: 0;
    }

    .brand {
      display: flex;
      gap: 10px;
      align-items: center;
      min-width: 0;
    }

    .brand-logo {
      width: 44px;
      height: 44px;
      object-fit: contain;
      border-radius: 10px;
      border: 1px solid rgba(0, 0, 0, 0.08);
      background: #fff;
      flex: 0 0 auto;
    }

    .brand-text {
      min-width: 0;
      flex: 1 1 auto;
      text-align: right;
    }

    .brand-title {
      font-weight: 900;
      font-size: 14px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .brand-sub {
      font-size: 12px;
      color: rgba(0, 0, 0, 0.55);
      margin-top: 2px;
    }

    .bismillah {
      font-weight: 900;
      font-size: 13px;
      text-align: center;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
      line-height: 1.3;
    }

    .meta-pill {
      border: 1px solid rgba(0,0,0,.08);
      border-radius: 10px;
      padding: 6px 9px;
      min-width: 105px;
      background: #D2E9E9 !important;
      flex: 0 1 auto;
    }

    .meta-k {
      font-size: 11px;
      color: rgba(0,0,0,.55);
    }

    .meta-v {
      font-size: 13px;
      font-weight: 900;
      margin-top: 2px;
      line-height: 1.35;
    }

    .rule {
      height: 1px;
      background: rgba(0,0,0,.08);
      margin: 8px 0 10px;
      flex: 0 0 auto;
    }

    .grid.grid-5 {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 8px;
      flex: 0 0 auto;
      min-height: 0;
    }

    .field {
      border: 1px solid rgba(0,0,0,.08);
      border-radius: 10px;
      padding: 8px 9px;
      background: #E3F4F4 !important;
      min-width: 0;
      overflow: hidden;
    }

    .k {
      font-size: 11px;
      color: rgba(0,0,0,.55);
      margin-bottom: 4px;
      line-height: 1.25;
    }

    .v {
      font-size: 13px;
      font-weight: 650;
      line-height: 1.35;
    }

    .strong {
      font-weight: 900;
    }

    .content {
      margin-top: 10px;
      flex: 1 1 auto;
      min-height: 0;
      overflow: hidden !important;

      display: grid !important;
      grid-template-rows: minmax(0, 1.7fr) auto auto auto;
      gap: 8px;
    }

    .section,
    .two.two-rows,
    .two.two-rows > .section,
    .tafseel-section,
    .manager-section,
    .direction-section {
      min-height: 0;
      overflow: hidden !important;
      break-inside: avoid !important;
      page-break-inside: avoid !important;
    }

    .section-h {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 6px;
      flex: 0 0 auto;
    }

    .section-title {
      font-weight: 900;
      font-size: 13px;
      line-height: 1.3;
    }

    .chip {
      font-size: 11px;
      border: 1px solid rgba(0,0,0,.08);
      background: #f9fafb;
      padding: 3px 8px;
      border-radius: 999px;
      white-space: nowrap;
      flex: 0 0 auto;
    }

    .box {
      border: 1px solid rgba(0,0,0,.08);
      border-radius: 10px;
      padding: 9px;
      background: #E3F4F4 !important;
      overflow: hidden !important;
      min-height: 0;
    }

    .box-text {
      background: #E3F4F4 !important;
    }

    .pre {
      white-space: pre-wrap;
      word-break: break-word;
      overflow-wrap: anywhere;
      line-height: 1.55;
    }

    .grow-box {
      min-height: 0;
    }

    .tafseel-section {
      display: flex;
      flex-direction: column;
      min-height: 0;
    }

    .tafseel-section .box {
      flex: 1 1 auto;
      min-height: 0;
      overflow: hidden !important;
    }

    .tafseel-pre {
      height: 100%;
      max-height: 100%;
      overflow: hidden !important;
    }

    .two.two-rows {
      display: grid;
      grid-template-columns: 1fr;
      gap: 8px;
      margin-top: 0;
      align-items: stretch;
    }

    .manager-section {
      display: block !important;
      min-height: 24mm;
    }

    .manager-section > .section {
      display: flex;
      flex-direction: column;
      min-height: 0;
      height: 100%;
    }

    .manager-section > .section > .box {
      min-height: 17mm;
      max-height: 24mm;
      overflow: hidden !important;
    }

    .direction-section {
      display: flex;
      flex-direction: column;
      min-height: 0;
    }

    .direction-section .box {
      min-height: 16mm;
      max-height: 25mm;
      overflow: hidden !important;
    }

    .clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden !important;
    }

    .clamp-10 {
      max-height: 85px;
      overflow: hidden !important;
    }

    .clamp-8 {
      max-height: 78px;
      overflow: hidden !important;
    }

    .print-notes {
      flex: 0 0 auto;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: flex-start;
      gap: 4px;
      margin-top: 8px;
      margin-right: 0;
      margin-left: auto;
      padding-top: 4px;
      min-height: 0;
      overflow: hidden !important;
      width: fit-content;
      max-width: 70%;
      text-align: right;
      direction: rtl;
    }

    .print-note {
      display: inline-flex;
      align-items: center;
      justify-content: flex-start;
      align-self: flex-start;
      max-width: 100%;
      width: fit-content;
      padding: 4px 10px;
      border-radius: 10px;
      border: 1px dashed rgba(0,0,0,.18);
      background: rgba(255,255,255,.55);
      color: rgba(0,0,0,.78);
      font-size: 11px;
      font-weight: 700;
      line-height: 1.35;
      text-align: right;
      white-space: normal;
      word-break: break-word;
      overflow-wrap: anywhere;
      direction: rtl;
    }

    @media print {
      html, body {
        overflow: hidden !important;
        height: auto !important;
        font-family: ${projectFontFamily} !important;
      }

      .page-shell {
        padding: 0 !important;
        margin: 0 !important;
        background: #f8f6f4 !important;
        font-family: ${projectFontFamily} !important;
      }

      .paper {
        margin: 0 !important;
        box-shadow: none !important;
        overflow: hidden !important;
        font-family: ${projectFontFamily} !important;
      }
    }
    `;
  }

  function buildDocumentHtml() {
    const paperHtml = buildInnerHtml();
    if (!paperHtml) return '';

    const css = buildPrintCss();
    const headAssets = getProjectHeadAssets();

    return `
      <html dir="rtl">
        <head>
          <meta charset="utf-8" />
          <meta name="viewport" content="width=device-width, initial-scale=1" />
          ${headAssets}
          <title>اطلاع_${item.value?.id || ''}_${Date.now()}</title>
          <style>${css}</style>
        </head>
        <body dir="rtl">
          ${paperHtml}
        </body>
      </html>
    `;
  }

  async function waitForPrintReady(w) {
    try {
      if (w?.document?.fonts?.ready) {
        await w.document.fonts.ready;
      }
    } catch (e) {}

    await new Promise((r) => setTimeout(r, 500));
  }

  function openPrintWindowAndPrint(html) {
    const w = window.open('', '_blank', 'width=1100,height=800');
    if (!w) return;

    w.document.open();
    w.document.write(html);
    w.document.close();

    w.addEventListener('load', async () => {
      await waitForPrintReady(w);
      w.focus();
      w.print();
    });
  }

  function downloadPdf() {
    refreshPrintMeta();
    const html = buildDocumentHtml();
    if (!html) return;
    openPrintWindowAndPrint(html);
  }

  function downloadWord() {
    refreshPrintMeta();
    const html = buildDocumentHtml();
    if (!html) return;

    const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `اطلاع_${item.value?.id || 'بدون_شماره'}_${Date.now()}.doc`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    URL.revokeObjectURL(url);
  }
</script>

<style scoped>
  /* ===============================
     COLORS (SCREEN) — ONLY THESE
  ================================ */
  .page-shell {
    min-height: calc(100vh - 64px);
    background: #f8f6f4; /* F8F6F4 */
    padding: 14px;
    font-family: inherit;
  }

  .top-actions {
    display: inline-flex;
    gap: 10px;
    align-items: center;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 34px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    background: white;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    transition: transform 0.12s ease, box-shadow 0.12s ease;
    font-family: inherit;
  }

  .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08);
  }

  .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
  }
  .dot-pdf {
    background: #e11d48;
  }
  .dot-word {
    background: #2563eb;
  }

  .paper {
    position: relative;
    max-width: 820px;
    margin: 0 auto;
    background: #f8f6f4; /* F8F6F4 */
    border-radius: 14px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.08);
    padding: 14px;
    font-family: inherit;

    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 64px - 28px);
    overflow: hidden;
  }

  .hdr {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
    gap: 12px;
    align-items: center;
    background: #c4dfdf; /* C4DFDF */
    border-radius: 12px;
    padding: 8px;
  }

  .hdr-right {
    grid-column: 1;
    justify-self: start;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    flex-direction: row-reverse;
    justify-content: flex-start;
    min-width: 0;
  }

  .hdr-center {
    grid-column: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 0;
    padding-inline: 8px;
  }

  .hdr-left {
    grid-column: 3;
    justify-self: end;
    min-width: 0;
  }

  .brand {
    display: flex;
    gap: 10px;
    align-items: center;
    min-width: 0;
  }

  .brand-logo {
    width: 44px;
    height: 44px;
    object-fit: contain;
    border-radius: 10px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    background: #fff;
  }

  .brand-text {
    min-width: 0;
    flex: 1 1 auto;
    text-align: right;
  }

  .brand-title {
    font-weight: 900;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .brand-sub {
    font-size: 12px;
    color: rgba(0, 0, 0, 0.55);
    margin-top: 2px;
  }

  .bismillah {
    font-weight: 900;
    font-size: 13px;
    text-align: center;
    white-space: nowrap;
    width: auto;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    pointer-events: none;
  }

  .meta-pill {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 7px 10px;
    min-width: 90px;
    background: #d2e9e9; /* D2E9E9 */
  }

  .meta-k {
    font-size: 11px;
    color: rgba(0, 0, 0, 0.55);
  }
  .meta-v {
    font-size: 13px;
    font-weight: 900;
    margin-top: 2px;
  }

  .rule {
    height: 1px;
    background: rgba(0, 0, 0, 0.08);
    margin: 10px 0 12px;
  }

  .grid.grid-5 {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
  }

  .field {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 9px 10px;
    background: #e3f4f4; /* E3F4F4 */
    min-width: 0;
  }

  .k {
    font-size: 11px;
    color: rgba(0, 0, 0, 0.55);
    margin-bottom: 4px;
  }

  .v {
    font-size: 13px;
    font-weight: 650;
  }

  .strong {
    font-weight: 900;
  }

  .content {
    margin-top: 12px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 0;
  }

  .section-h {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 6px;
  }

  .section-title {
    font-weight: 900;
    font-size: 13px;
  }

  .chip {
    font-size: 11px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    background: #f9fafb;
    padding: 3px 8px;
    border-radius: 999px;
    white-space: nowrap;
  }

  .box {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 10px;
    background: #e3f4f4; /* E3F4F4 */
  }

  .box-text {
    background: #e3f4f4; /* E3F4F4 */
  }

  .pre {
    white-space: pre-wrap;
    word-break: break-word;
    line-height: 1.7;
  }

  .tafseel-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
  }
  .tafseel-section .box {
    flex: 1;
    min-height: 0;
  }
  .tafseel-pre {
    height: 100%;
    overflow: hidden;
  }

  .two.two-rows {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    margin-top: 0;
    align-items: stretch;
  }

  .print-notes {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-start;
    gap: 6px;
    margin-top: 10px;
    margin-left: auto;
    width: fit-content;
    max-width: 70%;
    text-align: right;
    direction: rtl;
  }

  .print-note {
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    align-self: flex-start;
    max-width: 100%;
    width: fit-content;
    padding: 6px 10px;
    border-radius: 10px;
    border: 1px dashed rgba(0, 0, 0, 0.18);
    background: rgba(255, 255, 255, 0.55);
    color: rgba(0, 0, 0, 0.78);
    font-size: 11px;
    font-weight: 700;
    line-height: 1.4;
    text-align: right;
    white-space: normal;
    word-break: break-word;
    direction: rtl;
  }

  .clamp-10 {
    max-height: 170px;
    overflow: hidden;
  }
  .clamp-8 {
    max-height: 160px;
    overflow: hidden;
  }

  .clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  @media (max-width: 900px) {
    .paper {
      max-width: 100%;
    }
    .grid.grid-5 {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 720px) {
    .hdr {
      grid-template-columns: 1fr;
    }
    .hdr-right,
    .hdr-center,
    .hdr-left {
      grid-column: auto;
      justify-self: start;
    }
    .hdr-center {
      justify-content: center;
    }
    .grid.grid-5 {
      grid-template-columns: 1fr;
    }
    .paper {
      min-height: auto;
    }
  }

  @media print {
    .no-print {
      display: none !important;
    }
    .page-shell {
      padding: 0 !important;
      background: #f8f6f4 !important;
      min-height: auto !important;
    }
    .paper {
      max-width: none !important;
      margin: 0 !important;
      border: none !important;
      box-shadow: none !important;
      border-radius: 0 !important;
      padding: 0 !important;
      min-height: auto !important;
      background: #f8f6f4 !important;
    }
  }
</style>
