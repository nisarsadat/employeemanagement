// stores/FoulReportsRepository.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '@/axios';
import { toast } from 'vue3-toastify';

export const useFoulReportsRepository = defineStore('FoulReportsRepository', () => {
  const loading = ref(false);

  // ✅ keep error as STRING (never object)
  const error = ref('');

  // payloads
  const daily = ref(null);
  const monthly = ref(null);

  // filters
  const selectedDate = ref(''); // YYYY-MM-DD

  // ✅ monthly range (two inputs)
  const selectedMonthFrom = ref(''); // YYYY-MM
  const selectedMonthTo = ref(''); // YYYY-MM

  // (keep old for compatibility if any other page uses it)
  const selectedMonth = ref(''); // YYYY-MM

  // =========================
  // Helpers (Dates, Safe, HTML)
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

  function currentMonthIso() {
    const d = new Date();
    const y = d.getFullYear();
    const m = pad2(d.getMonth() + 1);
    return `${y}-${m}`;
  }

  function monthToIsoDate(monthText) {
    // YYYY-MM -> YYYY-MM-01
    const m = String(monthText || '').trim();
    if (/^\d{4}-\d{2}$/.test(m)) return `${m}-01`;
    return todayIso();
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

    let msg = 'د راپور ترلاسه کولو کې ستونزه رامنځته شوه.';

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

  function buildDatePillsSameDay(dateIso) {
    const miladi = formatMiladi(dateIso);
    const shamsi = formatHijriShamsi(dateIso);
    const qamari = formatHijriQamari(dateIso);

    const pills = [];
    if (qamari)
      pills.push(`<span class="pill pill-qamari">هجري قمري: ${escapeHtml(qamari)}</span>`);
    if (miladi) pills.push(`<span class="pill pill-miladi">میلادي: ${escapeHtml(miladi)}</span>`);
    if (shamsi)
      pills.push(`<span class="pill pill-shamsi">هجري شمسي: ${escapeHtml(shamsi)}</span>`);

    return pills.join('');
  }

  // =========================
  // ✅ Notes date-range logic
  // =========================
  function startOfWeek(d) {
    const x = new Date(d);
    const day = x.getDay(); // 0 Sun .. 6 Sat
    const diff = (day === 0 ? -6 : 1) - day; // Monday start
    x.setDate(x.getDate() + diff);
    return x;
  }
  function endOfWeek(d) {
    const s = startOfWeek(d);
    const e = new Date(s);
    e.setDate(e.getDate() + 6);
    return e;
  }
  function quarterOfMonth(m0) {
    return Math.floor(m0 / 3) + 1;
  }

  function rangeForType(type, dateIso, monthText) {
    const base = new Date(`${dateIso}T00:00:00`);

    if (type === 'daily') {
      const x = isoDate(base);
      return { from: x, to: x };
    }

    if (type === 'weekly') {
      const s = startOfWeek(base);
      const e = endOfWeek(base);
      return { from: isoDate(s), to: isoDate(e) };
    }

    if (type === 'monthly') {
      const m = monthText || `${base.getFullYear()}-${pad2(base.getMonth() + 1)}`;
      const [y, mm] = m.split('-').map(Number);
      const s = new Date(y, mm - 1, 1);
      const e = new Date(y, mm, 0);
      return { from: isoDate(s), to: isoDate(e) };
    }

    if (type === 'quarter') {
      const y = base.getFullYear();
      const q = quarterOfMonth(base.getMonth());
      const startMonth = (q - 1) * 3;
      const s = new Date(y, startMonth, 1);
      const e = new Date(y, startMonth + 3, 0);
      return { from: isoDate(s), to: isoDate(e) };
    }

    if (type === 'yearly') {
      const y = base.getFullYear();
      return { from: `${y}-01-01`, to: `${y}-12-31` };
    }

    const x = isoDate(base);
    return { from: x, to: x };
  }

  // ✅ NEW: monthly range => fromMonth..toMonth to exact dates
  function rangeForMonthlyBetween(fromVal, toVal) {
    const a = String(fromVal || '').trim();
    const b = String(toVal || '').trim();

    const isDay = (x) => /^\d{4}-\d{2}-\d{2}$/.test(x);
    const isMonth = (x) => /^\d{4}-\d{2}$/.test(x);

    // ✅ NEW: if user gives day->day, use directly
    if (isDay(a) || isDay(b)) {
      const from = isDay(a) ? a : todayIso();
      const to = isDay(b) ? b : from;
      return { from, to };
    }

    // ✅ OLD: month->month fallback
    const fromFinal = isMonth(a) ? a : currentMonthIso();
    const toFinal = isMonth(b) ? b : fromFinal;

    const [y1, m1] = fromFinal.split('-').map(Number);
    const [y2, m2] = toFinal.split('-').map(Number);

    const start = new Date(y1, m1 - 1, 1);
    const end = new Date(y2, m2, 0);

    return { from: isoDate(start), to: isoDate(end) };
  }

  // =========================
  // ✅ Notes fetch (robust)
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
          // try next
        }
      }
    }

    return [];
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
      Report: 'راپور',
      Form: 'فورم',
      Forms: 'فورمونه',
    };

    return map[n] || n;
  }

  // =========================
  // Export CSS
  // =========================
  function baseExportCss() {
    const fontStack =
      `'Amiri', 'Scheherazade New', ui-sans-serif, -apple-system, BlinkMacSystemFont,` +
      ` 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji',` +
      ` 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif`;

    return `
      @page { size: A4 portrait; margin: 14mm; }

      :root{ --app-font: ${fontStack}; }
      * { box-sizing: border-box; }

      html, body {
        margin: 0;
        padding: 0;
        background: #f6f7fb;
        color: #111827;
        font-family: var(--app-font) !important;
        font-size: 12px;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      .page { width: 100%; padding: 8mm; background: #f6f7fb; }

      .hero {
        border-radius: 16px;
        padding: 18px 18px 14px;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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
        gap:12px;
        position:relative;
        z-index:1;
      }
      .hero-title{ color:#fff; font-weight:900; font-size:18px; margin:0 0 4px; line-height:1.25; }
      .hero-sub{ color: rgba(255,255,255,.92); margin:0; font-size:12px; line-height:1.55; font-weight:700; }

      .pills{ margin-top:10px; display:flex; gap:8px; flex-wrap:wrap; position:relative; z-index:1; }
      .pill{
        display:inline-block;
        padding:6px 10px;
        border-radius:999px;
        font-weight:900;
        font-size:11px;
        border:1px solid rgba(255,255,255,.28);
        background: rgba(255,255,255,.14);
        color:#fff;
      }

      .section { margin-top: 12px; }

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
        padding:12px 12px;
        min-height:90px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
      }
      .stat-label{ font-size:11px; color: rgba(17,24,39,.65); font-weight:900; margin-bottom:4px; }
      .stat-number{ font-size:22px; font-weight:900; line-height:1.1; }

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
      }
      .b-primary{ background:#eef2ff; color:#1e40af; border-color:#c7d2fe; }
      .b-info{ background:#e0f2fe; color:#075985; border-color:#bae6fd; }
      .b-warning{ background:#fff7ed; color:#9a3412; border-color:#fed7aa; }
      .b-success{ background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }

      .header-strip{
        padding:10px 12px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
      }
      .strip-title{ font-weight:900; font-size:12px; margin:0; }
      .strip-sub{ margin:2px 0 0; color: rgba(17,24,39,.55); font-size:10px; font-weight:800; }

      .chip{
        padding:5px 10px;
        border-radius:999px;
        font-weight:900;
        font-size:10px;
        border:1px solid;
        white-space:nowrap;
      }

      .bg-primary{ background: linear-gradient(135deg,#eef2ff 0%, #dde5ff 100%); }
      .bg-success{ background: linear-gradient(135deg,#e8fbf2 0%, #d2f7e5 100%); }
      .bg-warning{ background: linear-gradient(135deg,#fff6df 0%, #ffeab6 100%); }

      .chip-primary{ background:#eef2ff; border-color:#c7d2fe; color:#1e40af; }
      .chip-success{ background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
      .chip-warning{ background:#fff7ed; border-color:#fed7aa; color:#9a3412; }

      table{ width:100%; border-collapse:separate; border-spacing: 0; table-layout: fixed; }

      thead th{
        background:#f8fafc;
        color: rgba(17,24,39,.72);
        font-weight:900;
        font-size:11px;
        padding:9px 8px;
        border-bottom:1px solid #eef2f7;
        text-align: start;
      }
      tbody td{
        padding:9px 8px;
        border-bottom:1px solid #f0f4fa;
        font-size:11px;
        vertical-align:middle;
        color: rgba(17,24,39,.90);
        text-align:start;
      }
      tbody tr:nth-child(even) td{ background: #fcfdff; }

      .td-end{ text-align:end; }
      .td-center{ text-align:center; }

      .count-pill{
        display:inline-block;
        padding:4px 10px;
        border-radius:999px;
        font-weight:900;
        font-size:10px;
        border:1px solid;
      }
      .count-primary{ border-color:#c7d2fe; color:#1e40af; background:#eef2ff; }
      .count-success{ border-color:#a7f3d0; color:#065f46; background:#ecfdf5; }

      .hero-logo{ display:flex; align-items:center; justify-content:flex-end; padding:10px 12px; border-radius:12px; min-width: 150px; }
      .hero-logo img{ height:82px; width:auto; object-fit:contain; display:block; }

      .no-break { break-inside: avoid; page-break-inside: avoid; }
      .card { break-inside: avoid; page-break-inside: avoid; }

      @media print{
        .stats-grid{ grid-template-columns: repeat(2, 1fr); }
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
  // ✅ Notes table HTML block
  // =========================
  function buildNotesSectionHtml(notes = []) {
    if (!Array.isArray(notes) || notes.length === 0) return '';

    const notesTr = notes
      .map((n) => {
        const text = escapeHtml(n?.report_note ?? n?.note ?? '');
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
                <div class="strip-title">یادداشتونه</div>
                <div class="strip-sub">د راپور یادداشتونه</div>
              </div>
            </div>
            <span class="chip chip-primary">${notes.length}</span>
          </div>

          <div style="padding:0;">
            <table>
              <thead>
                <tr>
                  <th style="width:72%;">یادداشت</th>
                </tr>
              </thead>
              <tbody>${notesTr}</tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  // =========================
  // Daily HTML (with notes)
  // =========================
  function buildDailyDashboardHtml(report, dateIso, notes = []) {
    const dateText = dateIso || todayIso();

    const resourcesTotal = safeNum(report?.resources?.total);
    const informationsTotal = safeNum(report?.informations?.total);
    const eventsTotal = safeNum(report?.events?.total);

    const formats = Array.isArray(report?.formats) ? report.formats : [];
    const formatsActive = formats.length;

    const infoTypes = Array.isArray(report?.informations?.types) ? report.informations.types : [];
    const eventRows = Array.isArray(report?.events?.rows) ? report.events.rows : [];

    function getLogoUrl() {
      return `${window.location.origin}/logo.jpeg`;
    }

    const heroDatePills = buildDatePillsSameDay(dateText);

    const infoSection =
      infoTypes.length > 0
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

    const formatsSection =
      formats.length > 0
        ? `
          <div class="section">
            <div class="card no-break">
              <div class="header-strip bg-success">
                <div><div class="strip-title">لیټري دوسیه</div><div class="strip-sub">د ښار دلیټري دوسیه فارمت فورمونو لنډیز</div></div>
                <span class="chip chip-success">${formatsActive}</span>
              </div>
              <div style="padding:0;">
                <table>
                  <thead><tr><th style="width:70%;">فورم</th><th class="td-end" style="width:30%;">شمېر</th></tr></thead>
                  <tbody>
                    ${formats
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

    const eventsSection =
      eventRows.length > 0
        ? `
          <div class="section">
            <div class="card">
              <div class="header-strip bg-warning">
                <div><div class="strip-title">ثبت شوې پېښې</div><div class="strip-sub">د سیمې له مخې د پېښو جزیات</div></div>
                <span class="chip chip-warning">${eventsTotal}</span>
              </div>
              <div style="padding:0;">
                <table>
                  <thead>
                    <tr>
                      <th style="width:18%;">ولایت</th>
                      <th style="width:18%;">حوزه</th>
                      <th style="width:20%;">د پېښې ډول</th>
                      <th class="td-center" style="width:11%;">وژلي</th>
                      <th class="td-center" style="width:11%;">ټپیان</th>
                      <th class="td-end" style="width:22%;">مالي زیان</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${eventRows
                      .map((r) => {
                        const province = escapeHtml(r?.province ?? '');
                        const hawza = escapeHtml(r?.hawza ?? '');
                        const etype = escapeHtml(r?.event_specific_type ?? r?.event_type ?? '');
                        const killed = safeNum(r?.killed);
                        const injured = safeNum(r?.injured);
                        const mali = formatMoney(r?.talafat_mali);
                        const cls = eventTypeClass(etype);

                        return `
                          <tr>
                            <td>${province}</td>
                            <td>${hawza}</td>
                            <td class="td-center"><span class="etype ${cls}">${
                          etype || '-'
                        }</span></td>
                            <td class="td-center">${killed}</td>
                            <td class="td-center">${injured}</td>
                            <td class="td-end">${mali}</td>
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
          <title>daily_dashboard_${dateText}</title>
          <style>${baseExportCss()}</style>
        </head>
        <body>
          <div class="page">
            <div class="hero no-break">
              <div class="hero-top">
                <div>
                  <h1 class="hero-title">د 333 ورځنی راپور</h1>
                  <p class="hero-sub">د سرچینو، معلوماتو، پېښو او فورمونو ورځنۍ عمومي کتنه</p>
                </div>
                <div class="hero-logo">
                  <img src="${escapeHtml(getLogoUrl())}" alt="Logo" />
                </div>
              </div>
              <div class="pills">${heroDatePills || ''}</div>
            </div>

            <div class="stats-grid section no-break">
              <div class="card"><div class="stat-card"><div>
                <div class="stat-label">منابع</div>
                <div class="stat-number">${resourcesTotal}</div>
                <span class="badge b-primary">استخدام چک او کنټرول روزنه ترسره شوه</span>
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
                <div class="stat-label">لیټري دوسیه فارمت فورمونه</div>
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
  // Monthly HTML (with notes)
  // =========================
  function buildMonthlyDashboardHtml(report, monthText, notes = []) {
    const monthVal = monthText || currentMonthIso();
    const headerIso = monthToIsoDate(monthVal);

    const resourcesTotal = safeNum(report?.resources?.total);
    const informationsTotal = safeNum(report?.informations?.total);
    const eventsTotal = safeNum(report?.events?.total);

    const formats = Array.isArray(report?.formats) ? report.formats : [];
    const formatsActive = formats.length;

    const infoTypes = Array.isArray(report?.informations?.types) ? report.informations.types : [];
    const eventTypes = Array.isArray(report?.events?.types) ? report.events.types : [];

    function getLogoUrl() {
      return `${window.location.origin}/logo.png`;
    }

    const heroDatePills = buildDatePillsSameDay(headerIso);

    const infoSection =
      infoTypes.length > 0
        ? `
          <div class="section">
            <div class="card no-break">
              <div class="header-strip bg-primary">
                <div><div class="strip-title">د اطلاعات ډولونه</div><div class="strip-sub">د ډول له مخې وېش</div></div>
                <span class="chip chip-primary">${informationsTotal}</span>
              </div>
              <div style="padding:0;">
                <table>
                  <thead><tr><th style="width:70%;">ډول</th><th class="td-end" style="width:30%;">شمېر</th></tr></thead>
                  <tbody>
                    ${infoTypes
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

    const formatsSection =
      formats.length > 0
        ? `
          <div class="section">
            <div class="card no-break">
              <div class="header-strip bg-success">
                <div><div class="strip-title">لیټري دوسیه</div><div class="strip-sub">د لیټري دوسیه فارمت فورمونو لنډیز</div></div>
                <span class="chip chip-success">${formatsActive}</span>
              </div>
              <div style="padding:0;">
                <table>
                  <thead><tr><th style="width:70%;">فورم</th><th class="td-end" style="width:30%;">شمېر</th></tr></thead>
                  <tbody>
                    ${formats
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

    const eventsSection =
      eventTypes.length > 0
        ? `
          <div class="section">
            <div class="card">
              <div class="header-strip bg-warning">
                <div><div class="strip-title">پېښې (د ډول له مخې)</div><div class="strip-sub">د پېښو وېش</div></div>
                <span class="chip chip-warning">${eventsTotal}</span>
              </div>
              <div style="padding:0;">
                <table>
                  <thead><tr><th style="width:70%;">د پېښې ډول</th><th class="td-end" style="width:30%;">شمېر</th></tr></thead>
                  <tbody>
                    ${eventTypes
                      .map((t) => {
                        const label = escapeHtml(t?.label ?? '');
                        const count = safeNum(t?.count);
                        const cls = eventTypeClass(label);
                        return `
                          <tr>
                            <td><span class="etype ${cls}">${label}</span></td>
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
          <title>monthly_dashboard_${monthVal}</title>
          <style>${baseExportCss()}</style>
        </head>
        <body>
          <div class="page">
            <div class="hero no-break">
              <div class="hero-top">
                <div>
                  <h1 class="hero-title">د 333 میاشتنی راپور</h1>
                  <p class="hero-sub">د سرچینو، معلوماتو، پېښو او فورمونو میاشتنی عمومي کتنه</p>
                </div>
                <div class="hero-logo">
                  <img src="${escapeHtml(getLogoUrl())}" alt="Logo" />
                </div>
              </div>
              <div class="pills">${heroDatePills || ''}</div>
            </div>

            <div class="stats-grid section no-break">
              <div class="card"><div class="stat-card"><div>
                <div class="stat-label">منابع</div>
                <div class="stat-number">${resourcesTotal}</div>
                <span class="badge b-primary">استخدام چک او کنټرول روزنه ترسره شوه</span>
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
                <div class="stat-label">لیټري دوسیه فارمت فورمونه</div>
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

  function openPrintWindow(html) {
    const w = window.open('', '_blank', 'width=1100,height=800');
    if (!w) return false;

    w.document.open();
    w.document.write(html);
    w.document.close();

    w.addEventListener('load', () => {
      w.focus();
      w.print();
    });

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
  // API
  // =========================
  const FetchDailyFoulReport = async (date) => {
    try {
      loading.value = true;
      error.value = '';

      const finalDate = date || selectedDate.value || todayIso();
      selectedDate.value = finalDate;

      const { data } = await axios.get('/reports/foul/daily', {
        params: { date: finalDate },
      });

      daily.value = data;
      return data;
    } catch (e) {
      daily.value = null;
      error.value = normalizeError(e);
      toast.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  const FetchMonthlyFoulReport = async ({ fromMonth, toMonth } = {}) => {
    try {
      loading.value = true;
      error.value = '';

      const fromFinal =
        fromMonth || selectedMonthFrom.value || selectedMonth.value || currentMonthIso();
      const toFinal = toMonth || selectedMonthTo.value || fromFinal;

      // persist (kept for compatibility)
      selectedMonthFrom.value = fromFinal;
      selectedMonthTo.value = toFinal;
      selectedMonth.value = fromFinal;

      // ✅ NOW supports YYYY-MM-DD or YYYY-MM
      const rr = rangeForMonthlyBetween(fromFinal, toFinal);

      const { data } = await axios.get('/reports/foul/monthly', {
        params: { from: rr.from, to: rr.to }, // ✅ day-to-day filtering
      });

      monthly.value = data;
      return data;
    } catch (e) {
      monthly.value = null;
      error.value = normalizeError(e);
      toast.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  const ResetDaily = () => {
    daily.value = null;
    error.value = '';
    selectedDate.value = todayIso();
  };

  const ResetMonthly = () => {
    monthly.value = null;
    error.value = '';
    const cur = currentMonthIso();
    selectedMonthFrom.value = cur;
    selectedMonthTo.value = cur;
    selectedMonth.value = cur;
  };

  // Init defaults
  if (!selectedDate.value) selectedDate.value = todayIso();
  if (!selectedMonthFrom.value) selectedMonthFrom.value = currentMonthIso();
  if (!selectedMonthTo.value) selectedMonthTo.value = selectedMonthFrom.value;
  if (!selectedMonth.value) selectedMonth.value = selectedMonthFrom.value;

  // =========================
  // EXPORT ACTIONS (with notes)
  // =========================
  const DownloadDailyFoulPdf = async () => {
    const report = daily.value;
    if (!report) return;

    const dateText = selectedDate.value || todayIso();
    const r = rangeForType('daily', dateText);

    const notes = await fetchNotesForExport({
      type: 'daily',
      from: r.from,
      to: r.to,
    });

    const html = buildDailyDashboardHtml(report, dateText, notes);
    openPrintWindow(html);
  };

  const DownloadDailyFoulWord = async () => {
    const report = daily.value;
    if (!report) return;

    const dateText = selectedDate.value || todayIso();
    const r = rangeForType('daily', dateText);

    const notes = await fetchNotesForExport({
      type: 'daily',
      from: r.from,
      to: r.to,
    });

    const html = buildDailyDashboardHtml(report, dateText, notes);
    downloadWordFile(html, `daily_dashboard_${dateText}.doc`);
  };

  const DownloadMonthlyFoulPdf = async () => {
    const report = monthly.value;
    if (!report) return;

    const fromMonth = selectedMonthFrom.value || currentMonthIso();
    const toMonth = selectedMonthTo.value || fromMonth;

    // ✅ notes are fetched for the selected monthly range
    const rr = rangeForMonthlyBetween(fromMonth, toMonth);

    const notes = await fetchNotesForExport({
      type: 'monthly',
      from: rr.from,
      to: rr.to,
    });

    // report HTML still based on FROM month (same as backend monthly report)
    const html = buildMonthlyDashboardHtml(report, fromMonth, notes);
    openPrintWindow(html);
  };

  const DownloadMonthlyFoulWord = async () => {
    const report = monthly.value;
    if (!report) return;

    const fromMonth = selectedMonthFrom.value || currentMonthIso();
    const toMonth = selectedMonthTo.value || fromMonth;

    const rr = rangeForMonthlyBetween(fromMonth, toMonth);

    const notes = await fetchNotesForExport({
      type: 'monthly',
      from: rr.from,
      to: rr.to,
    });

    const html = buildMonthlyDashboardHtml(report, fromMonth, notes);
    downloadWordFile(html, `monthly_dashboard_${fromMonth}_to_${toMonth}.doc`);
  };

  return {
    // state
    loading,
    error,

    // payloads
    daily,
    monthly,

    // filters
    selectedDate,
    selectedMonthFrom,
    selectedMonthTo,
    selectedMonth,

    // actions
    FetchDailyFoulReport,
    FetchMonthlyFoulReport,
    ResetDaily,
    ResetMonthly,

    // export
    DownloadDailyFoulPdf,
    DownloadDailyFoulWord,
    DownloadMonthlyFoulPdf,
    DownloadMonthlyFoulWord,

    // expose if needed
    rangeForType,
  };
});
