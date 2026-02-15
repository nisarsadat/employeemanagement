<!-- src/pages/Informations/InformationPrint.vue -->
<template>
  <AppBar :title="t('فورم اطلاع شخص')" @back="goBack">
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

      <!-- ✅ FOOTER REMOVED -->
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

  const systemName = ref('333');
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

    // ✅ normalize slashes before sending to calendar
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

  // ✅ UPDATED: force ONE PAGE by controlling heights + hiding overflow in print
  // (no layout changes for screen)
  function buildPrintCss() {
    return `
    @page { size: A4 portrait; margin: 12mm; }

    html, body {
      margin: 0;
      padding: 0;
      margin-left: 8px;
      margin-right: 8px;
      margin-top: 8px;
      font-family: 'Vazirmatn','Scheherazade New','Amiri',sans-serif;
      color: #111827;
      font-size: 10.5pt;          /* ✅ slightly smaller to help fit */
      line-height: 1.45;          /* ✅ slightly tighter to help fit */
      direction: rtl !important;
      text-align: right !important;
      unicode-bidi: plaintext !important;
      height: 95%;
      overflow: hidden !important; /* ✅ prevent extra page */
    }

    * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    .page-shell { padding: 0 !important; background: #fff !important; }

    /* ✅ SINGLE PAGE CANVAS (A4 - margins) */
    .paper {
      width: 100%;
      max-width: 820px;
      margin: 0 auto;
      background: #fff;
      border-radius: 14px;
      border: 1px solid rgba(0, 0, 0, 0.08);
      box-shadow: none !important;
      padding: 12px;              /* ✅ slightly smaller padding */
      direction: rtl !important;
      text-align: right !important;

      display: flex;
      flex-direction: column;

      height: calc(297mm - 24mm); /* ✅ force exactly one page height */
      max-height: calc(297mm - 24mm);
      overflow: hidden !important; /* ✅ never spill to page 2 */
    }

    /* ✅ Header */
    .hdr { display: grid; grid-template-columns: auto 1fr; gap: 10px; align-items: start; padding-right: 12mm; }
    .hdr-left { grid-column: 1; justify-self: end; }
    .hdr-right { justify-self: end; }

    .brand { display: flex; gap: 10px; align-items: center; min-width: 0; }
    .brand-logo { width: 44px; height: 44px; object-fit: contain; border-radius: 10px; }
    .brand-title { font-weight: 900; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .brand-sub { font-size: 12px; color: rgba(0,0,0,.55); margin-top: 2px; }

    .hdr-right { display: flex; gap: 8px; flex-wrap: wrap; flex-direction: row-reverse; justify-content: flex-start; }

    .meta-pill { border: 1px solid rgba(0,0,0,.08); border-radius: 10px; padding: 6px 9px; min-width: 120px; background: #fafafa; }
    .meta-k { font-size: 11px; color: rgba(0,0,0,.55); }
    .meta-v { font-size: 13px; font-weight: 900; margin-top: 2px; }

    .rule { height: 1px; background: rgba(0,0,0,.08); margin: 8px 0 10px; }

    /* ✅ 5 fields in one row */
    .grid.grid-5 { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
    .field { border: 1px solid rgba(0,0,0,.08); border-radius: 10px; padding: 8px 9px; background:#fff; min-width:0; }
    .k { font-size: 11px; color: rgba(0,0,0,.55); margin-bottom: 4px; }
    .v { font-size: 13px; font-weight: 650; }
    .strong { font-weight: 900; }

    /* ✅ content consumes remaining height */
    .content {
      margin-top: 10px;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-height: 0;
      overflow: hidden !important;  /* ✅ stay inside one page */
    }

    .section { margin-top: 0; min-height: 0; }
    .section-h { display:flex; align-items:center; justify-content: space-between; gap:10px; margin-bottom: 6px; }
    .section-title { font-weight: 900; font-size: 13px; }
    .chip { font-size: 11px; border: 1px solid rgba(0,0,0,.08); background:#f9fafb; padding: 3px 8px; border-radius: 999px; white-space: nowrap; }

    .box { border-radius: 10px; padding: 9px; overflow: hidden !important; }
    .box-text { background: #fcfcfd; }
    .pre { white-space: pre-wrap; word-break: break-word; line-height: 1.6; }

    .two.two-rows { display: grid; grid-template-columns: 1fr; gap: 8px; margin-top: 0; }
    .two.two-rows > .section { display:flex; flex-direction: column; min-height:0; }

    /* ✅ Growth rules */
    .grow-box { flex: 0 0 auto; }

    .tafseel-section .grow-box,
    .manager-section .grow-box,
    .direction-section .grow-box {
      flex: 1 1 auto;
      min-height: 0;
    }

    .tafseel-section { flex: 4; display: flex; flex-direction: column; min-height: 0; overflow: hidden !important; }
    .tafseel-section .box { flex: 1; min-height: 0; overflow: hidden !important; }
    .tafseel-pre { height: 100%; overflow: hidden !important; }

    /* ✅ Manager must stretch same as Direction (manager has extra inner .section wrapper) */
    .manager-section {
      flex: 2;
      display: flex;
      flex-direction: column;
      min-height: 0;
      overflow: hidden !important;
    }

    .manager-section > .section {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 0;
    }

    .manager-section > .section > .box {
      flex: 1;
      min-height: 0;
      overflow: hidden !important;
    }

    .direction-section { flex: 2; display: flex; flex-direction: column; min-height: 0; overflow: hidden !important; }
    .direction-section .box { flex: 1; min-height: 0; overflow: hidden !important; }

    .clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    /* ✅ tighter clamps so it never spills to page 2 */
    .clamp-10 { max-height: 120px; overflow: hidden; }
    .clamp-8 { max-height: 110px; overflow: hidden; }

    @media print {
      .paper {
        padding: 0 !important;
        border: none !important;
        border-radius: 0 !important;
        height: calc(297mm - 24mm) !important;
        max-height: calc(297mm - 24mm) !important;
        overflow: hidden !important;
      }
      .page-shell { padding: 0 !important; background: #fff !important; }
    }

    .no-print { display:none !important; }

    /* ===============================
       COLORS (PDF/WORD) — ONLY THESE
    ================================ */

    body { background: #F8F6F4 !important; }
    .page-shell { background: #F8F6F4 !important; }
    .paper { background: #F8F6F4 !important; }

    .hdr { background: #C4DFDF !important; border-radius: 12px; padding: 8px; }
    .meta-pill { background: #D2E9E9 !important; }
    .field { background: #E3F4F4 !important; }
    .box { background: #E3F4F4 !important; }
    `;
  }

  function buildDocumentHtml() {
    const paperHtml = buildInnerHtml();
    if (!paperHtml) return '';

    const css = buildPrintCss();

    return `
      <html dir="rtl">
        <head>
          <meta charset="utf-8" />
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
      if (w?.document?.fonts?.ready) await w.document.fonts.ready;
    } catch (e) {}
    await new Promise((r) => setTimeout(r, 250));
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
    max-width: 820px;
    margin: 0 auto;
    background: #f8f6f4; /* F8F6F4 */
    border-radius: 14px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.08);
    padding: 14px;

    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 64px - 28px);
  }

  .hdr {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 12px;
    align-items: start;

    background: #c4dfdf; /* C4DFDF */
    border-radius: 12px;
    padding: 8px;
  }

  .hdr-left {
    justify-self: end;
  }

  .hdr-right {
    justify-self: start;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    flex-direction: row-reverse;
    justify-content: flex-start;
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

  .meta-pill {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 7px 10px;
    min-width: 120px;
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
    .hdr-left,
    .hdr-right {
      justify-self: start;
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
      min-height: calc(297mm - 24mm) !important;
      background: #f8f6f4 !important;
    }
  }
</style>
