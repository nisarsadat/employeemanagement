import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '@/axios';
import { toast } from 'vue3-toastify';

export const useFoulYearlyReportsRepository = defineStore('FoulYearlyReportsRepository', () => {
  const loading = ref(false);
  const error = ref('');
  const yearly = ref(null);

  // filter
  const selectedYear = ref('');
  const selectedDate = ref('');

  // backend years
  const availableYears = ref([]);
  const currentYear = ref('');

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

  function safeNum(val) {
    const n = Number(val ?? 0);
    return Number.isFinite(n) ? n : 0;
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
    let msg = 'د کلني راپور ترلاسه کولو کې ستونزه رامنځته شوه.';

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
    if (qamari) pills.push(`<span class="pill pill-qamari">هجري قمري: ${escapeHtml(qamari)}</span>`);
    if (shamsi) pills.push(`<span class="pill pill-shamsi">هجري شمسي: ${escapeHtml(shamsi)}</span>`);
    if (miladi) pills.push(`<span class="pill pill-miladi">میلادي: ${escapeHtml(miladi)}</span>`);

    return pills.join('');
  }

  function hasUsefulRows(arr, countKey = 'count') {
    if (!Array.isArray(arr) || arr.length === 0) return false;
    return arr.some((x) => safeNum(x?.[countKey] ?? 0) > 0);
  }

  function hasUsefulNotes(arr) {
    if (!Array.isArray(arr) || arr.length === 0) return false;
    return arr.some((n) => String(n?.report_note ?? n?.note ?? '').trim());
  }

  function rangeForYearlyNotes(yearText) {
    const y = String(yearText || selectedYear.value || currentYear.value || '').trim();
    if (!/^\d{4}$/.test(y)) {
      return { from: todayIso(), to: todayIso() };
    }

    if (yearly.value?.meta?.range?.from && yearly.value?.meta?.range?.to_exclusive) {
      const from = yearly.value.meta.range.from;
      const toExclusive = yearly.value.meta.range.to_exclusive;

      const toDate = new Date(`${toExclusive}T00:00:00`);
      toDate.setDate(toDate.getDate() - 1);
      const to = isoDate(toDate);

      return { from, to };
    }

    return { from: todayIso(), to: todayIso() };
  }

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
          // next
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
            <td style="width:100%;">${text || '-'}</td>
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
                <div class="strip-title">یادونه</div>
              </div>
            </div>
            <span class="chip chip-primary">${clean.length}</span>
          </div>

          <div style="padding:0;">
            <table>
              <thead>
                <tr>
                  <th style="width:100%;">یادونه</th>
                </tr>
              </thead>
              <tbody>${notesTr}</tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  function formatNameLocal(name) {
    const n = String(name ?? '').trim();
    if (!n) return '';

    const map = {
      Resources: 'منابع',
      Resource: 'سرچینه',
      Informations: 'اطلاعات',
      Information: 'اطلاعات',
      Events: 'پېښې',
      Event: 'پېښه',
      Finance: 'مالي',
      Finances: 'مالي',
      Daily: 'ورځنی',
      Monthly: 'میاشتنی',
      Weekly: 'اوونیز',
      Quarterly: 'ربعه',
      Yearly: 'کلنی',
      Report: 'راپور',
      Form: 'فورم',
      Forms: 'فورمونه',
    };

    return map[n] || n;
  }

  function appFontStack() {
    return (
      `'Amiri','Scheherazade New','Noto Naskh Arabic','Noto Sans Arabic','Tahoma',` +
      `ui-sans-serif,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif`
    );
  }

  function getSystemName() {
    try {
      const a = window?.systemName;
      if (typeof a === 'string' && a.trim()) return a.trim();
    } catch {}
    try {
      const b = import.meta?.env?.VITE_SYSTEM_NAME;
      if (typeof b === 'string' && b.trim()) return b.trim();
    } catch {}
    return 'د ۳۳۳ مدیریت';
  }

  const BISMILLAH = 'بسم الله الرحمن الرحیم';

  function baseExportCss() {
    const fontStack = appFontStack();

    return `
      @import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade+New:wght@400;700&display=swap');
      @page { size: A4 portrait; margin: 14mm; }

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
        background: #fff !important;
        color: #000 !important;
        font-family: var(--app-font) !important;
        font-size: 12px;
        direction: rtl !important;
        text-align: right !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      .page { width: 100%; padding: 8mm; background: #fff !important; }

      .hero {
        border-radius: 16px;
        padding: 18px 18px 14px;
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
        gap:12px;
        position:relative;
        z-index:1;
        direction: rtl !important;
      }

      .hero, .hero * { color: #000 !important; }

      .hero-system{
        font-weight: 900;
        font-size: 12px;
        margin: 0 0 6px;
        line-height: 1.4;
      }

      .hero-bismillah{
        font-weight: 900;
        font-size: 18px;
        margin: 0 0 6px;
        line-height: 1.4;
        margin-top: 24px;
        text-align: center;
      }

      .hero-title{ font-weight:900; font-size:18px; margin:0 0 4px; line-height:1.25; }
      .hero-sub{ margin:0; font-size:12px; line-height:1.55; font-weight:700; }

      .pills{ margin-top:10px; display:flex; gap:8px; flex-wrap:wrap; position:relative; z-index:1; }
      .pill{
        display:inline-block;
        padding:6px 10px;
        border-radius:999px;
        font-weight:900;
        font-size:11px;
        border:1px solid rgba(255,255,255,.28);
        background: rgba(255,255,255,.14);
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
      .stat-label{ font-size:11px; font-weight:900; margin-bottom:4px; color:#000 !important; }
      .stat-number{ font-size:22px; font-weight:900; line-height:1.1; color:#000 !important; }

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
      .strip-title{ font-weight:900; font-size:12px; margin:0; color:#000 !important; }
      .strip-sub{ margin:2px 0 0; font-size:10px; font-weight:800; color:#000 !important; }

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

      table{ width:100%; border-collapse:separate; border-spacing: 0; table-layout: fixed; direction: rtl !important; }

      thead th{
        background:#f8fafc;
        font-weight:900;
        font-size:11px;
        padding:9px 8px;
        border-bottom:1px solid #eef2f7;
        text-align: start;
        color:#000 !important;
      }
      tbody td{
        padding:9px 8px;
        border-bottom:1px solid #f0f4fa;
        font-size:11px;
        vertical-align:middle;
        text-align:start;
        color:#000 !important;
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
        color: inherit !important;
      }
      .count-primary{ border-color:#c7d2fe; background:#eef2ff; }
      .count-success{ border-color:#a7f3d0; background:#ecfdf5; }

      .hero-logo{
        display:flex;
        align-items:center;
        justify-content:flex-start;
        padding:10px 12px;
        border-radius:12px;
        min-width: 150px;
      }
      .hero-logo img{ height:82px; width:auto; object-fit:contain; display:block; }

      .no-break { break-inside: avoid; page-break-inside: avoid; }
      .card { break-inside: avoid; page-break-inside: avoid; }

      .etype{
        display:inline-block;
        padding:3px 10px;
        border-radius:999px;
        font-weight:900;
        font-size:10px;
        border:1px solid rgba(17,24,39,.12);
        color:#000 !important;
      }
      .margintowline { margin-top:12px; }
      .fontsizeb { font-size: 18px; color: #0000; }
      .etype-red{ background:#fee2e2; border-color:#fecaca; }
      .etype-amber{ background:#ffedd5; border-color:#fed7aa; }
      .etype-blue{ background:#dbeafe; border-color:#bfdbfe; }
      .etype-purple{ background:#ede9fe; border-color:#ddd6fe; }
      .etype-gray{ background:#f3f4f6; border-color:#e5e7eb; }

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

  function buildYearlyDashboardHtml(report, yearText, notes = []) {
    const yearVal = String(yearText || selectedYear.value || currentYear.value || '').trim();

    const headerIso = yearly.value?.meta?.range?.from || todayIso();
    const heroDatePills = buildDatePillsSameDay(headerIso);

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

    const infoSection =
      hasUsefulRows(infoTypes, 'count')
        ? `
          <div class="section">
            <div class="card no-break">
              <div class="header-strip bg-primary">
                <div><div class="strip-title">اطلاعات</div><div class="strip-sub">د ډول له مخې وېش (کال)</div></div>
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

    const formatsSection =
      hasUsefulRows(formats, 'count')
        ? `
          <div class="section">
            <div class="card no-break">
              <div class="header-strip bg-success">
                <div><div class="strip-title">پنځوس فارمټ</div><div class="strip-sub"></div></div>
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

    const eventsSection =
      hasUsefulRows(eventTypes, 'count')
        ? `
          <div class="section">
            <div class="card">
              <div class="header-strip bg-warning">
                <div><div class="strip-title">ثبت شوې پېښې</div><div class="strip-sub">د ډول له مخې د پېښو جزیات (کال)</div></div>
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
          <title>yearly_dashboard_${escapeHtml(yearVal)}</title>
          <style>${baseExportCss()}</style>
        </head>
        <body>
          <div class="page">
            <div class="hero no-break fontsizeb">
              <div class="hero-top">
                <div class="hero-info margintowline">
                  <div class="hero-system fontsizeb">${escapeHtml(systemName)} کلنی راپور</div>
                  <p class="hero-sub fontsizeb">د منابعو، اطلاعاتو، پېښو او پنځوس فارمټ کلنی عمومي کتنه</p>
                </div>

                <div class="hero-mid">
                  <div class="hero-bismillah fontsizeb">${escapeHtml(BISMILLAH)}</div>
                </div>

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
                <span class="badge b-primary">استخدام ، چک او کنټرول</span>
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

          setTimeout(resolve, 2000);
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
          } else resolve();
        } catch {
          resolve();
        }
      });

    const doPrint = async () => {
      try {
        await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));
        await waitFonts();
        await waitImages();

        w.focus();
        w.print();
      } finally {
        setTimeout(() => {
          try {
            document.body.removeChild(iframe);
          } catch {}
        }, 1200);
      }
    };

    iframe.onload = () => setTimeout(doPrint, 150);
    setTimeout(doPrint, 600);

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

  const FetchYearlyFoulYears = async () => {
    try {
      const { data } = await axios.get('/reports/foul/yearly/date-options');

      availableYears.value = Array.isArray(data?.years) ? data.years.map((x) => String(x)) : [];
      currentYear.value = String(data?.current_year ?? '');

      if (!selectedYear.value) {
        selectedYear.value = availableYears.value[0] || currentYear.value || '';
      }

      return data;
    } catch (e) {
      error.value = normalizeError(e);
      toast.error(error.value);
      return null;
    }
  };

  const FetchYearlyFoulReport = async (year) => {
    try {
      loading.value = true;
      error.value = '';

      const finalYear = String(year || selectedYear.value || currentYear.value || '').trim();
      selectedYear.value = finalYear;

      const { data } = await axios.get('/reports/foul/yearly', {
        params: { year: selectedYear.value },
      });

      yearly.value = data;
      selectedDate.value = data?.meta?.range?.from || '';

      return data;
    } catch (e) {
      yearly.value = null;
      error.value = normalizeError(e);
      toast.error(error.value);
      return null;
    } finally {
      loading.value = false;
    }
  };

  const ResetYearly = async () => {
    yearly.value = null;
    error.value = '';

    if (availableYears.value.length > 0) {
      selectedYear.value = availableYears.value[0];
    } else {
      selectedYear.value = currentYear.value || '';
    }

    if (selectedYear.value) {
      await FetchYearlyFoulReport(selectedYear.value);
    }
  };

  const DownloadYearlyFoulPdf = async () => {
    const report = yearly.value;
    if (!report) return;

    const yearText = selectedYear.value || currentYear.value || '';
    const r = rangeForYearlyNotes(yearText);

    const notes = await fetchNotesForExport({
      type: 'yearly',
      from: r.from,
      to: r.to,
    });

    const html = buildYearlyDashboardHtml(report, yearText, notes);
    printHtmlViaIframe(html);
  };

  const DownloadYearlyFoulWord = async () => {
    const report = yearly.value;
    if (!report) return;

    const yearText = selectedYear.value || currentYear.value || '';
    const r = rangeForYearlyNotes(yearText);

    const notes = await fetchNotesForExport({
      type: 'yearly',
      from: r.from,
      to: r.to,
    });

    const html = buildYearlyDashboardHtml(report, yearText, notes);
    downloadWordFile(html, `yearly_dashboard_${yearText}.doc`);
  };

  return {
    loading,
    error,
    yearly,
    selectedYear,
    selectedDate,
    availableYears,
    currentYear,
    FetchYearlyFoulYears,
    FetchYearlyFoulReport,
    ResetYearly,
    DownloadYearlyFoulPdf,
    DownloadYearlyFoulWord,
  };
});