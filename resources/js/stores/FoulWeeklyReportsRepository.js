// stores/FoulWeeklyReportsRepository.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '@/axios';
import { toast } from 'vue3-toastify';

export const useFoulWeeklyReportsRepository = defineStore('FoulWeeklyReportsRepository', () => {
  const loading = ref(false);

  // ✅ IMPORTANT: keep error as STRING (never object)
  const error = ref('');

  // Weekly report payload from backend
  const weekly = ref(null);

  // Filters (✅ from/to)
  const selectedFrom = ref(''); // YYYY-MM-DD (inclusive)
  const selectedTo = ref(''); // YYYY-MM-DD (inclusive)
  const calendarType = ref('gregorian');

  // =========================
  // Helpers
  // =========================
  function pad2(n) {
    return String(n).padStart(2, '0');
  }

  function isoDate(d) {
    const y = d.getFullYear();
    const m = pad2(d.getMonth() + 1);
    const day = pad2(d.getDate());
    return `${y}-${m}-${day}`;
  }

  function todayIso() {
    return isoDate(new Date());
  }

  // current ISO week (Mon..Sun) -> {from,to}
  function currentIsoWeekRange() {
    const d = new Date();
    const day = d.getDay(); // 0..6 (Sun..Sat)
    const diffToMonday = (day === 0 ? -6 : 1) - day;

    const monday = new Date(d);
    monday.setDate(d.getDate() + diffToMonday);

    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);

    return { from: isoDate(monday), to: isoDate(sunday) };
  }

  function safeNum(val) {
    const n = Number(val ?? 0);
    return Number.isFinite(n) ? n : 0;
  }

  function formatMoney(val) {
    return safeNum(val).toLocaleString();
  }

  function escapeHtml(s) {
    const str = String(s ?? '');
    return str
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function normalizeError(e) {
    const data = e?.response?.data;
    let msg = 'د اوونیز راپور ترلاسه کولو کې ستونزه رامنځته شوه.';

    if (typeof data === 'string') return data;

    if (data && typeof data === 'object') {
      if (typeof data.message === 'string') return data.message;

      if (data.message && typeof data.message === 'object') {
        const merged = Object.values(data.message).flat().filter(Boolean).join(' ، ');
        return merged || msg;
      }

      if (data.errors && typeof data.errors === 'object') {
        const merged = Object.values(data.errors).flat().filter(Boolean).join(' ، ');
        return merged || msg;
      }

      if (typeof data.error === 'string') return data.error;

      try {
        return JSON.stringify(data);
      } catch {
        return msg;
      }
    }

    if (typeof e?.message === 'string' && e.message.trim()) return e.message;

    try {
      return String(e);
    } catch {
      return msg;
    }
  }

  // =========================
  // Dates (Miladi + Hijri Shamsi + Hijri Qamari)
  // =========================
  function formatMiladi(dateIso) {
    const d = String(dateIso || '').trim();
    return /^\d{4}-\d{2}-\d{2}$/.test(d) ? d : '';
  }

  function formatHijriShamsi(dateIso) {
    try {
      const d = new Date(`${dateIso}T00:00:00`);
      return new Intl.DateTimeFormat('fa-AF-u-ca-persian', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      }).format(d);
    } catch {
      return '';
    }
  }

  function formatHijriQamari(dateIso) {
    try {
      const d = new Date(`${dateIso}T00:00:00`);
      return new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      }).format(d);
    } catch {
      try {
        const d = new Date(`${dateIso}T00:00:00`);
        return new Intl.DateTimeFormat('ar-SA-u-ca-islamic', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
        }).format(d);
      } catch {
        return '';
      }
    }
  }

  function buildDatePillsRange(fromIso, toIso) {
    const fMil = formatMiladi(fromIso);
    const tMil = formatMiladi(toIso);

    const fSh = formatHijriShamsi(fromIso);
    const tSh = formatHijriShamsi(toIso);

    const fQ = formatHijriQamari(fromIso);
    const tQ = formatHijriQamari(toIso);

    const pills = [];

    if (fQ && tQ)
      pills.push(
        `<span class="pill pill-qamari">هجري قمري: ${escapeHtml(fQ)} — ${escapeHtml(tQ)}</span>`
      );
    if (fSh && tSh)
      pills.push(
        `<span class="pill pill-shamsi">هجري شمسي: ${escapeHtml(fSh)} — ${escapeHtml(tSh)}</span>`
      );
    if (fMil && tMil)
      pills.push(
        `<span class="pill pill-miladi">میلادي: ${escapeHtml(fMil)} — ${escapeHtml(tMil)}</span>`
      );

    return pills.join('');
  }

  // =========================
  // ✅ EMPTY / USEFUL ROW HELPERS
  // =========================
  function hasUsefulRows(arr, countKey = 'count') {
    if (!Array.isArray(arr) || arr.length === 0) return false;
    return arr.some((x) => safeNum(x?.[countKey] ?? 0) > 0);
  }

  function hasUsefulNotes(arr) {
    if (!Array.isArray(arr) || arr.length === 0) return false;
    return arr.some((n) => String(n?.report_note ?? n?.note ?? '').trim());
  }

  // =========================
  // ✅ NOTES: fetch + html section
  // =========================
  async function fetchNotesForExport({ type, from, to }) {
    const endpoints = ['/report-notes', '/report_notes'];

    const paramVariants = [
      { type, from, to, perPage: 1000, page: 1 },
      { type, from, to, per_page: 1000, page: 1 },
      { type, start: from, end: to, perPage: 1000, page: 1 },
      { type, start: from, end: to, per_page: 1000, page: 1 },
      { report_type: type, from, to, perPage: 1000, page: 1 },
      { report_type: type, start: from, end: to, perPage: 1000, page: 1 },
    ];

    for (const url of endpoints) {
      for (const params of paramVariants) {
        try {
          const res = await axios.get(url, { params });
          if (Array.isArray(res?.data?.data)) return res.data.data;
          if (Array.isArray(res?.data)) return res.data;
        } catch {
          // try next variant
        }
      }
    }

    return [];
  }

  function buildNotesSectionHtml(notes = []) {
    if (!hasUsefulNotes(notes)) return '';

    const clean = notes
      .map((n) => ({ text: String(n?.report_note ?? n?.note ?? '').trim() }))
      .filter((x) => x.text);

    if (clean.length === 0) return '';

    const notesTr = clean
      .map((n) => {
        const text = escapeHtml(n.text);
        return `
          <tr>
            <td style="width:72%;">${text || '-'}</td>
          </tr>
        `;
      })
      .join('');

    return `
      <div class="section">
        <div class="card">
          <div class="header-strip bg-primary">
            <div class="strip-left">
              <div>
                <div class="strip-title">یادؤنه</div>
              </div>
            </div>
            <span class="chip chip-primary">${clean.length}</span>
          </div>

          <div style="padding:0;">
            <table>
              <thead><tr></tr></thead>
              <tbody>${notesTr}</tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  // =========================
  // ✅ Format name localization
  // =========================
  function formatNameLocal(name) {
    const n = String(name ?? '').trim();
    if (!n) return '';

    const map = {
      Resources: 'منابع',
      Informations: 'اطلاعات',
      Information: 'اطلاعات',
      Events: 'پېښې',
      Event: 'پېښه',
      Finance: 'مالي',
      Finances: 'مالي',

      Daily: 'ورځنی',
      Monthly: 'میاشتنی',
      Weekly: 'اوونیز',

      Report: 'راپور',
      Form: 'فورم',
      Forms: 'فورمونه',
    };

    return map[n] || n;
  }

  // =========================
  // ✅ App font stack (SAME AS DAILY/MONTHLY REPO)
  // =========================
  function appFontStack() {
    return (
      `'Amiri','Noto Naskh Arabic','Noto Sans','Tahoma',` +
      `ui-sans-serif,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif`
    );
  }

  // =========================
  // ✅ System Name (same logic)
  // =========================
  function getSystemName() {
    try {
      const a = window?.systemName;
      if (typeof a === 'string' && a.trim()) return a.trim();
    } catch {}
    try {
      // eslint-disable-next-line no-undef
      const b = import.meta?.env?.VITE_SYSTEM_NAME;
      if (typeof b === 'string' && b.trim()) return b.trim();
    } catch {}
    return 'د پنځم مدیریت';
  }

  const BISMILLAH = 'بسم الله الرحمن الرحیم';

  // =========================
  // ✅ Export CSS (MATCH daily/monthly EXACT)
  // - RTL + same hero + same pill styles
  // - table align start
  // - HERO TEXT BLACK (same as your daily/monthly)
  // =========================
  function baseExportCss() {
    const fontStack = appFontStack();

    return `
    @font-face {
      font-family: 'Amiri';
      src: url('/fonts/Amiri-Regular.ttf') format('truetype');
      font-weight: 400;
      font-style: normal;
      font-display: swap;
    }

    @font-face {
      font-family: 'Amiri';
      src: url('/fonts/Amiri-Bold.ttf') format('truetype');
      font-weight: 700;
      font-style: normal;
      font-display: swap;
    }

    @font-face {
      font-family: 'Noto Naskh Arabic';
      src: url('/fonts/NotoNaskhArabic-Regular.ttf') format('truetype');
      font-weight: 400;
      font-style: normal;
      font-display: swap;
    }

    @font-face {
      font-family: 'Noto Naskh Arabic';
      src: url('/fonts/NotoNaskhArabic-Bold.ttf') format('truetype');
      font-weight: 700;
      font-style: normal;
      font-display: swap;
    }

    @font-face {
      font-family: 'Noto Sans';
      src: url('/fonts/NotoSans-Regular.ttf') format('truetype');
      font-weight: 400;
      font-style: normal;
      font-display: swap;
    }

    @font-face {
      font-family: 'Noto Sans';
      src: url('/fonts/NotoSans-Bold.ttf') format('truetype');
      font-weight: 700;
      font-style: normal;
      font-display: swap;
    }

    @page {
      size: A4 portrait;
      margin: 10mm;
    }

    :root{
      --app-font: ${fontStack};
      --v-font-family: var(--app-font);
    }

    * {
      box-sizing: border-box;
      font-family: var(--app-font) !important;
      color: #000 !important;
    }

    html, body {
      margin: 0;
      padding: 0;
      width: 210mm;
      min-height: 297mm;
      background: #fff !important;
      color: #000 !important;
      font-family: var(--app-font) !important;
      font-size: 11px;
      line-height: 1.45;
      direction: rtl !important;
      text-align: right !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      overflow: visible !important;
    }

    .page {
      width: 190mm;
      min-height: auto;
      margin: 0 auto;
      padding: 0;
      background: #fff !important;
    }

    .hero {
      border-radius: 16px;
      padding: 14px 14px 12px;
      background: #547ca3;
      position: relative;
      overflow: hidden;
      box-shadow: 0 18px 40px rgba(79,70,229,.18);
    }

    .hero::before{
      content:"";
      position:absolute;
      inset:0;
      opacity:.42;
      background:
        radial-gradient(circle at 16% 26%, rgba(255,255,255,.20) 0 2px, transparent 3px) 0 0/26px 26px,
        radial-gradient(circle at 70% 40%, rgba(255,255,255,.14) 0 2px, transparent 3px) 0 0/34px 34px;
      pointer-events:none;
    }

    .hero-top{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:10px;
      position:relative;
      z-index:1;
      direction: rtl !important;
    }

    .hero, .hero * {
      color: #000 !important;
    }

    .hero-bismillah{
      font-weight: 900;
      font-size: 16px;
      margin: 0 0 4px;
      line-height: 1.4;
      margin-top: 18px;
      text-align: center;
    }

    .hero-system{
      font-weight: 900;
      font-size: 13px;
      margin: 0 0 6px;
      line-height: 1.4;
    }

    .hero-title{
      font-weight:900;
      font-size:16px;
      margin:0 0 4px;
      line-height:1.25;
    }

    .hero-sub{
      margin:0;
      font-size:11px;
      line-height:1.55;
      font-weight:700;
    }

    .pills{
      margin-top:10px;
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      position:relative;
      z-index:1;
    }

    .pill{
      display:inline-block;
      padding:5px 9px;
      border-radius:999px;
      font-weight:900;
      font-size:10px;
      border:1px solid rgba(255,255,255,.28);
      background: rgba(255,255,255,.14);
    }

    .section {
      margin-top: 10px;
    }

    .stats-grid{
      display:grid;
      grid-template-columns: repeat(4, 1fr);
      gap:10px;
      margin-top:12px;
    }

    .card{
      background:#fff;
      border:1px solid #e9eef7;
      border-radius:14px;
      overflow:hidden;
      box-shadow: 0 10px 22px rgba(17,24,39,.06);
    }

    .stat-card{
      padding:10px 10px;
      min-height:84px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
    }

    .stat-label{
      font-size:11px;
      font-weight:900;
      margin-bottom:4px;
      color:#000 !important;
    }

    .stat-number{
      font-size:20px;
      font-weight:900;
      line-height:1.1;
      color:#000 !important;
    }

    .badge{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:4px 10px;
      border-radius:999px;
      font-size:10px;
      font-weight:900;
      margin-top:6px;
      border:1px solid;
      white-space:nowrap;
      color: inherit !important;
    }

    .b-primary{ background:#eef2ff; border-color:#c7d2fe; }
    .b-info{ background:#e0f2fe; border-color:#bae6fd; }
    .b-warning{ background:#fff7ed; border-color:#fed7aa; }
    .b-success{ background:#ecfdf5; border-color:#a7f3d0; }

    .header-strip{
      padding:10px 12px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      direction: rtl !important;
    }

    .strip-title{
      font-weight:900;
      font-size:12px;
      margin:0;
      color:#000 !important;
    }

    .strip-sub{
      margin:2px 0 0;
      font-size:10px;
      font-weight:800;
      color:#000 !important;
    }

    .chip{
      padding:5px 10px;
      border-radius:999px;
      font-weight:900;
      font-size:10px;
      border:1px solid;
      white-space:nowrap;
      color: inherit !important;
    }

    .bg-primary{ background: linear-gradient(135deg,#eef2ff 0%, #dde5ff 100%); }
    .bg-success{ background: linear-gradient(135deg,#e8fbf2 0%, #d2f7e5 100%); }
    .bg-warning{ background: linear-gradient(135deg,#fff6df 0%, #ffeab6 100%); }

    .chip-primary{ background:#eef2ff; border-color:#c7d2fe; }
    .chip-success{ background:#ecfdf5; border-color:#a7f3d0; }
    .chip-warning{ background:#fff7ed; border-color:#fed7aa; }

    table{
      width:100%;
      border-collapse:separate;
      border-spacing:0;
      table-layout:fixed;
      direction: rtl !important;
    }

    thead th{
      background:#f8fafc;
      font-weight:900;
      font-size:11px;
      padding:8px 7px;
      border-bottom:1px solid #eef2f7;
      text-align:start;
      color:#000 !important;
      word-break: break-word;
      overflow-wrap: anywhere;
    }

    tbody td{
      padding:8px 7px;
      border-bottom:1px solid #f0f4fa;
      font-size:11px;
      vertical-align:middle;
      text-align:start;
      color:#000 !important;
      word-break: break-word;
      overflow-wrap: anywhere;
    }

    tbody tr:nth-child(even) td{
      background:#fcfdff;
    }

    .td-end{ text-align:end; }
    .td-center{ text-align:center; }

    .count-pill{
      display:inline-block;
      padding:4px 10px;
      border-radius:999px;
      font-weight:900;
      font-size:10px;
      border:1px solid;
      color: inherit !important;
    }

    .count-primary{ border-color:#c7d2fe; background:#eef2ff; }
    .count-success{ border-color:#a7f3d0; background:#ecfdf5; }

    .hero-logo{
      display:flex;
      align-items:center;
      justify-content:flex-start;
      padding:8px 10px;
      border-radius:12px;
      min-width:120px;
    }

    .hero-logo img{
      height:70px;
      width:auto;
      object-fit:contain;
      display:block;
    }

    .no-break {
      break-inside: avoid;
      page-break-inside: avoid;
    }

    .etype{
      display:inline-block;
      padding:3px 10px;
      border-radius:999px;
      font-weight:900;
      font-size:10px;
      border:1px solid rgba(17,24,39,.12);
      color:#000 !important;
    }

    .etype-red{ background:#fee2e2; border-color:#fecaca; }
    .etype-amber{ background:#ffedd5; border-color:#fed7aa; }
    .etype-blue{ background:#dbeafe; border-color:#bfdbfe; }
    .etype-purple{ background:#ede9fe; border-color:#ddd6fe; }
    .etype-gray{ background:#f3f4f6; border-color:#e5e7eb; }

    .fontsizeb {
      font-size: inherit;
      color: #000 !important;
    }

    .margintowline {
      margin-top:12px;
    }

    @media print {
      html, body {
        width: 210mm !important;
        min-height: 297mm !important;
        overflow: visible !important;
      }

      .page {
        width: 190mm !important;
        min-height: auto !important;
      }

      .hero,
      .stats-grid,
      .stat-card,
      .header-strip {
        break-inside: avoid;
        page-break-inside: avoid;
      }

      .section,
      .card,
      table,
      tbody,
      tr,
      td {
        break-inside: auto;
        page-break-inside: auto;
      }

      thead {
        display: table-header-group;
      }

      tfoot {
        display: table-footer-group;
      }

      tr {
        page-break-inside: avoid;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
  `;
  }
  function eventTypeClass(type) {
    const t = String(type ?? '').trim();
    const map = {
      حادثه: 'etype-red',
      پېښه: 'etype-red',
      تخریب: 'etype-amber',
      ويجاړۍ: 'etype-amber',
      درگیری: 'etype-amber',
      نښته: 'etype-amber',
      طبیعی: 'etype-blue',
      طبيعي: 'etype-blue',
      سیاسی: 'etype-purple',
      سياسي: 'etype-purple',
    };
    return map[t] || 'etype-gray';
  }

  // =========================
  // ✅ Weekly HTML (EXACT HERO LAYOUT AS DAILY/MONTHLY)
  // - hero: left texts, middle bismillah, right logo
  // - omit sections with no useful rows
  // =========================
  function buildWeeklyDashboardHtml(report, fromIso, toIso, notes = []) {
    const systemName = getSystemName();

    const resourcesTotal = safeNum(report?.resources?.total);
    const informationsTotal = safeNum(report?.informations?.total);
    const eventsTotal = safeNum(report?.events?.total);

    const formats = Array.isArray(report?.formats) ? report.formats : [];
    const formatsActive = formats.length;

    const infoTypes = Array.isArray(report?.informations?.types) ? report.informations.types : [];
    const eventTypes = Array.isArray(report?.events?.types) ? report.events.types : [];

    function getLogoUrl() {
      return `${window.location.origin}/logo.jpeg`;
    }

    const heroDatePills = buildDatePillsRange(fromIso, toIso);

    const infoSection = hasUsefulRows(infoTypes, 'count')
      ? `
          <div class="section">
            <div class="card no-break">
              <div class="header-strip bg-primary">
                <div><div class="strip-title">اطلاعات</div><div class="strip-sub"></div></div>
                <span class="chip chip-primary">${informationsTotal}</span>
              </div>
              <div style="padding:0;">
                <table>
                  <thead><tr><th style="width:70%;">ډول</th><th class="td-end" style="width:30%;">شمېر</th></tr></thead>
                  <tbody>
                    ${infoTypes
                      .filter((x) => safeNum(x?.count) > 0)
                      .map((x) => {
                        const label = escapeHtml(x?.label ?? '');
                        const count = safeNum(x?.count);
                        return `
                          <tr>
                            <td>${label}</td>
                            <td class="td-end"><span class="count-pill count-primary">${count}</span></td>
                          </tr>
                        `;
                      })
                      .join('')}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        `
      : '';

    const formatsSection = hasUsefulRows(formats, 'count')
      ? `
          <div class="section">
            <div class="card no-break">
              <div class="header-strip bg-success">
                <div><div class="strip-title">پنځوس فارمټ</div><div class="strip-sub">د پنځوس فارمټ لنډیز</div></div>
                <span class="chip chip-success">${formatsActive}</span>
              </div>
              <div style="padding:0;">
                <table>
                  <thead><tr><th style="width:70%;">فورم</th><th class="td-end" style="width:30%;">شمېر</th></tr></thead>
                  <tbody>
                    ${formats
                      .filter((f) => safeNum(f?.count) > 0)
                      .map((f) => {
                        const name = escapeHtml(formatNameLocal(f?.name ?? ''));
                        const count = safeNum(f?.count);
                        return `
                          <tr>
                            <td>${name || '-'}</td>
                            <td class="td-end"><span class="count-pill count-success">${count}</span></td>
                          </tr>
                        `;
                      })
                      .join('')}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        `
      : '';

    const eventsSection = hasUsefulRows(eventTypes, 'count')
      ? `
          <div class="section">
            <div class="card">
              <div class="header-strip bg-warning">
                <div><div class="strip-title">پېښې (د ډول له مخې)</div><div class="strip-sub">د مودې د پېښو وېش</div></div>
                <span class="chip chip-warning">${eventsTotal}</span>
              </div>
              <div style="padding:0;">
                <table>
                  <thead><tr><th style="width:70%;">د پېښې ډول</th><th class="td-end" style="width:30%;">شمېر</th></tr></thead>
                  <tbody>
                    ${eventTypes
                      .filter((t) => safeNum(t?.count) > 0)
                      .map((t) => {
                        const label = escapeHtml(t?.label ?? '');
                        const count = safeNum(t?.count);
                        const cls = eventTypeClass(label);
                        return `
                          <tr>
                            <td><span class="etype ${cls}">${label || '-'}</span></td>
                            <td class="td-end"><span class="count-pill count-primary">${count}</span></td>
                          </tr>
                        `;
                      })
                      .join('')}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        `
      : '';

    return `
      <html dir="rtl">
        <head>
          <meta charset="utf-8" />
          <title>weekly_dashboard_${fromIso}_${toIso}</title>
          <style>${baseExportCss()}</style>
        </head>
        <body>
          <div class="page">
            <div class="hero no-break fontsizeb">
              <div class="hero-top">
                <!-- ✅ left texts (same structure as daily/monthly) -->
                <div class="hero-info margintowline">
                  <div class="hero-system fontsizeb">${escapeHtml(systemName)} اوونیز راپور</div>
                  <p class="hero-sub fontsizeb">د منابعو، اطلاعاتو، پېښو او پنځوس فارمټ اوونیزه عمومي کتنه</p>
                </div>

                <!-- ✅ middle bismillah (same as daily/monthly “hero-mid”) -->
                <div class="hero-mid">
                  <div class="hero-bismillah fontsizeb">${escapeHtml(BISMILLAH)}</div>
                </div>

                <!-- ✅ right logo -->
                <div class="hero-logo">
                  <img src="${escapeHtml(getLogoUrl())}" alt="Logo" />
                </div>
              </div>

              <div class="pills fontsizeb">${heroDatePills || ''}</div>
            </div>

            <div class="stats-grid section no-break">
              <div class="card"><div class="stat-card"><div>
                <div class="stat-label">منابع</div>
                <div class="stat-number">${resourcesTotal}</div>
                <span class="badge b-primary">استخدام ، روزنه او ارزونه ترسره شوه</span>
              </div></div></div>

              <div class="card"><div class="stat-card"><div>
                <div class="stat-label">اطلاعات</div>
                <div class="stat-number">${informationsTotal}</div>
                <span class="badge b-info">شتون لري</span>
              </div></div></div>

              <div class="card"><div class="stat-card"><div>
                <div class="stat-label">پېښې</div>
                <div class="stat-number">${eventsTotal}</div>
                <span class="badge b-warning">ثبت شوې</span>
              </div></div></div>

              <div class="card"><div class="stat-card"><div>
                <div class="stat-label">پنځوس فارمټ</div>
                <div class="stat-number">${formatsActive}</div>
              </div></div></div>
            </div>

            ${infoSection}
            ${formatsSection}
            ${eventsSection}
            ${buildNotesSectionHtml(notes)}
          </div>
        </body>
      </html>
    `;
  }

  // =========================
  // ✅ Print helper (FIX BLANK PDF) — same as daily/monthly
  // =========================
  function printHtmlViaIframe(html) {
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.style.opacity = '0';
    iframe.style.pointerEvents = 'none';
    iframe.setAttribute('aria-hidden', 'true');

    document.body.appendChild(iframe);

    const w = iframe.contentWindow;
    const doc = iframe.contentDocument || w.document;

    let hasPrinted = false;

    doc.open();
    doc.write(html);
    doc.close();

    const waitImages = () =>
      new Promise((resolve) => {
        try {
          const imgs = Array.from(doc.images || []);
          if (!imgs.length) return resolve();

          let done = 0;
          const tick = () => {
            done += 1;
            if (done >= imgs.length) resolve();
          };

          imgs.forEach((img) => {
            if (img.complete) tick();
            else {
              img.addEventListener('load', tick, { once: true });
              img.addEventListener('error', tick, { once: true });
            }
          });

          setTimeout(resolve, 2500);
        } catch {
          resolve();
        }
      });

    const waitFonts = () =>
      new Promise((resolve) => {
        try {
          const fonts = doc.fonts;
          if (fonts && typeof fonts.ready?.then === 'function') {
            fonts.ready.then(resolve).catch(resolve);
          } else {
            resolve();
          }
        } catch {
          resolve();
        }
      });

    const doPrint = async () => {
      if (hasPrinted) return;
      hasPrinted = true;

      try {
        await new Promise((r) => setTimeout(r, 400));
        await waitFonts();
        await waitImages();
        await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));

        w.focus();
        w.print();
      } finally {
        setTimeout(() => {
          try {
            document.body.removeChild(iframe);
          } catch {}
        }, 1500);
      }
    };

    iframe.onload = () => {
      setTimeout(doPrint, 300);
    };

    return true;
  }

  function downloadWordFile(html, filename) {
    const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    URL.revokeObjectURL(url);
  }

  // =========================
  // API (✅ from/to)
  // =========================
  const FetchWeeklyFoulReport = async (from, to) => {
    try {
      loading.value = true;
      error.value = '';

      const r = currentIsoWeekRange();
      const finalFrom = String(from || selectedFrom.value || r.from);
      const finalTo = String(to || selectedTo.value || r.to);

      selectedFrom.value = finalFrom;
      selectedTo.value = finalTo;

      const { data } = await axios.get('/reports/foul/weekly', {
        params: { from: selectedFrom.value, to: selectedTo.value },
      });

      weekly.value = data;
      return data;
    } catch (e) {
      weekly.value = null;
      error.value = normalizeError(e);
      toast.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  const ResetWeekly = () => {
    weekly.value = null;
    error.value = '';
    const r = currentIsoWeekRange();
    selectedFrom.value = r.from;
    selectedTo.value = r.to;
  };

  // Init defaults
  if (!selectedFrom.value || !selectedTo.value) {
    const r = currentIsoWeekRange();
    selectedFrom.value = r.from;
    selectedTo.value = r.to;
  }

  // =========================
  // EXPORT ACTIONS (✅ with notes)
  // =========================
  const DownloadWeeklyFoulPdf = async () => {
    const report = weekly.value;
    if (!report) return;

    const fromIso = selectedFrom.value || currentIsoWeekRange().from;
    const toIso = selectedTo.value || currentIsoWeekRange().to;

    const notes = await fetchNotesForExport({
      type: 'weekly',
      from: fromIso,
      to: toIso,
    });

    const html = buildWeeklyDashboardHtml(report, fromIso, toIso, notes);
    printHtmlViaIframe(html);
  };

  const DownloadWeeklyFoulWord = async () => {
    const report = weekly.value;
    if (!report) return;

    const fromIso = selectedFrom.value || currentIsoWeekRange().from;
    const toIso = selectedTo.value || currentIsoWeekRange().to;

    const notes = await fetchNotesForExport({
      type: 'weekly',
      from: fromIso,
      to: toIso,
    });

    const html = buildWeeklyDashboardHtml(report, fromIso, toIso, notes);
    downloadWordFile(html, `weekly_dashboard_${fromIso}_${toIso}.doc`);
  };

  return {
    // state
    loading,
    error,

    // payload
    weekly,

    // filters
    selectedFrom,
    selectedTo,
    calendarType,

    // actions
    FetchWeeklyFoulReport,
    ResetWeekly,

    // export
    DownloadWeeklyFoulPdf,
    DownloadWeeklyFoulWord,
  };
});
