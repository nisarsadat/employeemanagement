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
              <div class="brand-sub">{{ t('سند رسمی / پرنټ') }}</div>
            </div>
          </div>
        </div>

        <!-- ✅ Meta pills must be on LEFT -->
        <div class="hdr-right">
          <div class="meta-pill">
            <div class="meta-k">{{ t('شمېره') }}</div>
            <div class="meta-v">{{ item.id || '—' }}</div>
          </div>
          <div class="meta-pill">
            <div class="meta-k">{{ t('تاریخ') }}</div>
            <div class="meta-v">{{ formattedInformationDate || '—' }}</div>
          </div>
          <div class="meta-pill">
            <div class="meta-k">{{ t('ساعت') }}</div>
            <div class="meta-v">{{ item.information_saved_hour || '—' }}</div>
          </div>
        </div>
      </header>

      <div class="rule" />

      <section class="grid">
        <div class="field">
          <div class="k">{{ t('موضوع / نوم') }}</div>
          <div class="v strong clamp-2">{{ item.name || '—' }}</div>
        </div>

        <div class="field">
          <div class="k">{{ t('ولایت') }}</div>
          <div class="v">{{ item.Province_addr || '—' }}</div>
        </div>

        <div class="field">
          <div class="k">{{ t('د معلومات ډول') }}</div>
          <div class="v">{{ item.syasi_info_type || '—' }}</div>
        </div>

        <div class="field">
          <div class="k">{{ t('د جنایي معلومات ډول') }}</div>
          <div class="v">{{ item.jenayee_info_type || '—' }}</div>
        </div>
      </section>

      <main class="content">
        <section class="section">
          <div class="section-h">
            <div class="section-title">{{ t('تفصیل معلومات') }}</div>
            <div class="chip">{{ t('کلمات') }}: {{ wordCount(item.tafseel) }}</div>
          </div>
          <div class="box box-text">
            <div class="pre clamp-lines">
              {{ item.tafseel || '—' }}
            </div>
          </div>
        </section>

        <!-- ✅ Two columns with fixed positions (like image) -->
        <section class="two">
          <!-- Right side (first in RTL) -->
          <div class="section">
            <div class="section-h">
              <div class="section-title">{{ t('اسناد پیوست') }}</div>
              <div class="chip">{{ docsCount }}</div>
            </div>

            <div class="box box-text">
              <div class="pre clamp-10">{{ itemDocsNote }}</div>
            </div>
          </div>

          <!-- Left side -->
          <div class="section">
            <div class="section-h">
              <div class="section-title">{{ t('نظر مدیریت') }}</div>
            </div>
            <div class="box box-text">
              <div class="pre clamp-10">{{ item.manager_comment || '—' }}</div>
            </div>
          </div>
        </section>

        <section class="section">
          <div class="section-h">
            <div class="section-title">{{ t('هدایت و راهنمایی') }}</div>
          </div>
          <div class="box box-text">
            <div class="pre clamp-8">{{ item.lead_drirection || '—' }}</div>
          </div>
        </section>
      </main>

      <footer class="ftr">
        <div class="signs">
          <div class="sign">
            <div class="sign-k">{{ t('مسئول ثبت کننده') }}</div>
            <div class="line" />
            <div class="sign-meta">
              <span>{{ t('تاریخ') }}: ____</span>
              <span>{{ t('مهر') }}: ____</span>
            </div>
          </div>

          <div class="sign">
            <div class="sign-k">{{ t('مسئول تایید کننده') }}</div>
            <div class="line" />
            <div class="sign-meta">
              <span>{{ t('تاریخ') }}: ____</span>
              <span>{{ t('مهر') }}: ____</span>
            </div>
          </div>
        </div>

        <div class="stamp">
          <div class="stamp-row">
            <span class="s-k">{{ t('شناسه سیستم') }}</span>
            <span class="s-v">{{ systemId }}</span>
          </div>
          <div class="stamp-row">
            <span class="s-k">{{ t('تاریخ چاپ') }}</span>
            <span class="s-v">{{ printedAt }}</span>
          </div>
        </div>
      </footer>
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

  const route = useRoute();
  const repo = useDatabaseRepository();
  const { t, locale } = useI18n();

  function goBack() {
    router.push('/app/informations');
  }

  const printArea = ref(null);
  const logoSrc = `${window.location.origin}/logo.jpeg`;

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

  const systemName = ref('سیستم مدیریت اطلاعات ');
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

  const { formatDisplay, toIso } = useCalendar();

  const formattedInformationDate = computed(() => {
    const raw = item.value?.information_saved_date;
    if (!raw) return '';
    const iso = toIso(raw);
    // ✅ still use your locale for date formatting, but layout is always RTL
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

  // ✅ ALWAYS RTL print CSS (fixed positions like your image)
  function buildPrintCss() {
    return `
    @page { size: A4 portrait; margin: 12mm; }

    html, body {
      margin: 0;
      padding: 0;
      background: #fff;
      font-family: 'Vazirmatn','Scheherazade New','Amiri',sans-serif;
      color: #111827;
      font-size: 11pt;
      line-height: 1.55;
      direction: rtl !important;
      text-align: right !important;
      unicode-bidi: plaintext !important;
    }

    * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    .page-shell { padding: 0 !important; background: white !important; }

    .paper {
      width: 100%;
      max-width: 820px;
      margin: 0 auto;
      background: white;
      border-radius: 14px;
      border: 1px solid rgba(0, 0, 0, 0.08);
      box-shadow: none !important;
      padding: 14px;
      direction: rtl !important;
      text-align: right !important;
    }

    /* ✅ Header: brand RIGHT, meta LEFT */
    .hdr { display: grid; grid-template-columns: auto 1fr; gap: 12px; align-items: start; }
    .hdr-left { grid-column: 1; justify-self: end; }   /* brand on right */
    .hdr-right { grid-column: 4; justify-self: start; }/* meta on left */

    .brand { display: flex; gap: 10px; align-items: center; min-width: 0; }
    .brand-logo { width: 44px; height: 44px; object-fit: contain; border-radius: 10px; border: 1px solid rgba(0,0,0,.08); background:#fff; }
    .brand-title { font-weight: 900; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .brand-sub { font-size: 12px; color: rgba(0,0,0,.55); margin-top: 2px; }

    /* ✅ pills order like screenshot */
    .hdr-right { display: flex; gap: 8px; flex-wrap: wrap; flex-direction: row-reverse; justify-content: flex-start; }

    .meta-pill { border: 1px solid rgba(0,0,0,.08); border-radius: 10px; padding: 7px 10px; min-width: 120px; background: #fafafa; }
    .meta-k { font-size: 11px; color: rgba(0,0,0,.55); }
    .meta-v { font-size: 13px; font-weight: 900; margin-top: 2px; }

    .rule { height: 1px; background: rgba(0,0,0,.08); margin: 10px 0 12px; }

    .grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 10px; }
    .field { border: 1px solid rgba(0,0,0,.08); border-radius: 10px; padding: 9px 10px; background:#fff; min-width:0; }
    .k { font-size: 11px; color: rgba(0,0,0,.55); margin-bottom: 4px; }
    .v { font-size: 13px; font-weight: 650; }
    .strong { font-weight: 900; }

    .content { margin-top: 12px; }
    .section { margin-top: 10px; }
    .section-h { display:flex; align-items:center; justify-content: space-between; gap:10px; margin-bottom: 6px; }
    .section-title { font-weight: 900; font-size: 13px; }
    .chip { font-size: 11px; border: 1px solid rgba(0,0,0,.08); background:#f9fafb; padding: 3px 8px; border-radius: 999px; white-space: nowrap; }

    .box { border: 1px solid rgba(0,0,0,.08); border-radius: 10px; padding: 10px; background:#fff; }
    .box-text { background: #fcfcfd; }
    .pre { white-space: pre-wrap; word-break: break-word; line-height: 1.7; }

    /* ✅ Two columns fixed like page */
    .two { display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; align-items: stretch; }
    .two > .section { display:flex; flex-direction: column; min-height:0; }
    .two > .section .box { flex:1; }

    .ftr { margin-top: 12px; padding-top: 10px; border-top: 1px solid rgba(0,0,0,.08); display:grid; grid-template-columns: 1fr auto; gap:10px; align-items: end; }
    .signs { display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
    .sign { border: 1px solid rgba(0,0,0,.08); border-radius: 10px; padding: 10px; background:#fff; }
    .sign-k { font-size: 12px; color: rgba(0,0,0,.55); font-weight: 900; }
    .line { height: 1px; background: rgba(0,0,0,.65); margin: 16px 0 8px; opacity: .35; }
    .sign-meta { display:flex; justify-content: space-between; gap: 10px; font-size: 11px; color: rgba(0,0,0,.55); }

    .stamp { border: 1px solid rgba(0,0,0,.08); border-radius: 10px; padding:10px; background:#fafafa; min-width: 210px; }
    .stamp-row { display:flex; justify-content: space-between; gap:10px; padding: 3px 0; }
    .s-k { font-size: 11px; color: rgba(0,0,0,.55); }
    .s-v { font-size: 11.5px; font-weight: 900; }

    .clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .clamp-lines { max-height: 240px; overflow: hidden; }
    .clamp-10 { max-height: 130px; overflow: hidden; }
    .clamp-8 { max-height: 110px; overflow: hidden; }

    @media (max-width: 720px) {
      .hdr { grid-template-columns: 1fr; }
      .hdr-left, .hdr-right { grid-column: auto; justify-self: start; }
      .grid { grid-template-columns: 1fr; }
      .two { grid-template-columns: 1fr; }
      .ftr { grid-template-columns: 1fr; }
      .stamp { min-width: 0; }
      .signs { grid-template-columns: 1fr; }
    }

    .no-print { display:none !important; }
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
  .page-shell {
    min-height: calc(100vh - 64px);
    background: #f6f7fb;
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
  .dot-pdf { background: #e11d48; }
  .dot-word { background: #2563eb; }

  .paper {
    max-width: 820px;
    margin: 0 auto;
    background: white;
    border-radius: 14px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.08);
    padding: 14px;
  }

  /* ✅ Header fixed like image: brand right, pills left */
  .hdr {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 12px;
    align-items: start;
  }

  .hdr-left {
    justify-self: end; /* right side */
  }

  .hdr-right {
    justify-self: start; /* left side */
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    flex-direction: row-reverse; /* شمېره rightmost in pills group */
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
    background: #fafafa;
  }

  .meta-k { font-size: 11px; color: rgba(0, 0, 0, 0.55); }
  .meta-v { font-size: 13px; font-weight: 900; margin-top: 2px; }

  .rule {
    height: 1px;
    background: rgba(0, 0, 0, 0.08);
    margin: 10px 0 12px;
  }

  .grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 10px;
  }

  .field {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 9px 10px;
    background: #fff;
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

  .strong { font-weight: 900; }

  .content { margin-top: 12px; }
  .section { margin-top: 10px; }

  .section-h {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 6px;
  }

  .section-title { font-weight: 900; font-size: 13px; }

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
    background: #fff;
  }

  .box-text { background: #fcfcfd; }

  .pre {
    white-space: pre-wrap;
    word-break: break-word;
    line-height: 1.7;
  }

  .clamp-lines { max-height: 240px; overflow: hidden; }
  .clamp-10 { max-height: 130px; overflow: hidden; }
  .clamp-8 { max-height: 110px; overflow: hidden; }

  .two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 10px;
    align-items: stretch;
  }

  .two > .section {
    display: flex;
    flex-direction: column;
    min-height: 0;
  }

  .two > .section .box { flex: 1; }

  .ftr {
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
    align-items: end;
  }

  .signs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }

  .sign {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 10px;
    background: #fff;
  }

  .sign-k {
    font-size: 12px;
    color: rgba(0, 0, 0, 0.55);
    font-weight: 900;
  }

  .line {
    height: 1px;
    background: rgba(0, 0, 0, 0.65);
    margin: 16px 0 8px;
    opacity: 0.35;
  }

  .sign-meta {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 11px;
    color: rgba(0, 0, 0, 0.55);
  }

  .stamp {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 10px;
    background: #fafafa;
    min-width: 210px;
  }

  .stamp-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 3px 0;
  }

  .s-k { font-size: 11px; color: rgba(0, 0, 0, 0.55); }
  .s-v { font-size: 11.5px; font-weight: 900; }

  .clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  @media (max-width: 900px) {
    .paper { max-width: 100%; }
  }

  @media (max-width: 720px) {
    .hdr { grid-template-columns: 1fr; }
    .hdr-left, .hdr-right { justify-self: start; }
    .grid { grid-template-columns: 1fr; }
    .two { grid-template-columns: 1fr; }
    .ftr { grid-template-columns: 1fr; }
    .stamp { min-width: 0; }
    .signs { grid-template-columns: 1fr; }
  }

  @media print {
    .no-print { display: none !important; }
    .page-shell { padding: 0 !important; background: white !important; }
    .paper {
      max-width: none !important;
      margin: 0 !important;
      border: none !important;
      box-shadow: none !important;
      border-radius: 0 !important;
      padding: 0 !important;
    }
  }
</style>
