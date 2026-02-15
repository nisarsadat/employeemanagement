// stores/FoulQuarterlyReportsRepository.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '@/axios';
import { toast } from 'vue3-toastify';

export const useFoulQuarterlyReportsRepository = defineStore(
  'FoulQuarterlyReportsRepository',
  () => {
    const loading = ref(false);

    // ✅ IMPORTANT: keep error as STRING (never object)
    const error = ref('');

    // Quarterly report payload from backend
    const quarterly = ref(null);

    // ✅ NEW FILTERS (From/To)
    const fromDate = ref(''); // YYYY-MM-DD
    const toDate = ref(''); // YYYY-MM-DD

    // =========================
    // Helpers
    // =========================
    function todayIso() {
      const d = new Date();
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${y}-${m}-${day}`;
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

      let msg = 'د ربعه راپور ترلاسه کولو کې ستونزه رامنځته شوه.';

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
    // ✅ NOTES fetch (from/to)
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

    function buildNotesSectionHtml(notes = []) {
      if (!hasUsefulNotes(notes)) return '';

      const clean = notes
        .map((n) => ({
          text: String(n?.report_note ?? n?.note ?? '').trim(),
        }))
        .filter((x) => x.text);

      if (clean.length === 0) return '';

      const notesTr = clean
        .map((n, idx) => {
          const text = escapeHtml(n.text);
          return `
            <tr>
              <td style="width:8%;">${idx + 1}</td>
              <td style="width:92%;">${text || '-'}</td>
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
              <span class="chip chip-primary">${clean.length}</span>
            </div>

            <div style="padding:0;">
              <table>
                <thead>
                  <tr>
                    <th style="width:8%;">#</th>
                    <th style="width:92%;">یادداشت</th>
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
    // Export CSS (unchanged)
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

      .hero-top > div:first-child { text-align: start; }

      .hero-title{
        color:#fff;
        font-weight:900;
        font-size:18px;
        margin:0 0 4px;
        letter-spacing:.2px;
        line-height:1.25;
        text-align:start;
      }

      .hero-sub{
        color: rgba(255,255,255,.92);
        margin:0;
        font-size:12px;
        line-height:1.55;
        font-weight:700;
        text-align:start;
      }

      .pills{
        margin-top:10px;
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        position:relative;
        z-index:1;
        justify-content:flex-start;
      }

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

      .stats-grid{ display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; margin-top:12px; }

      .card{
        background:#fff;
        border:1px solid #e9eef7;
        border-radius:14px;
        overflow:hidden;
        box-shadow: 0 10px 22px rgba(17,24,39,.06);
      }

      .stat-card{ padding:12px 12px; min-height:90px; display:flex; align-items:center; justify-content:space-between; gap:10px; }
      .stat-label{ font-size:11px; color: rgba(17,24,39,.65); font-weight:900; margin-bottom:4px; text-align:start; }
      .stat-number{ font-size:22px; font-weight:900; line-height:1.1; text-align:start; }

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

      .strip-left { display:flex; align-items:flex-start; gap:10px; }
      .strip-left > div { text-align:start; }

      .strip-title{ font-weight:900; font-size:12px; margin:0; text-align:start; }
      .strip-sub{ margin:2px 0 0; color: rgba(17,24,39,.55); font-size:10px; font-weight:800; text-align:start; }

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
        text-align:start;
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

      .hero-logo{
        display:flex;
        align-items:center;
        justify-content:flex-end;
        padding:10px 12px;
        border-radius:12px;
        min-width: 150px;
      }
      .hero-logo img{ height:82px; width:auto; object-fit:contain; display:block; }

      .no-break { break-inside: avoid; page-break-inside: avoid; }
      .card { break-inside: avoid; page-break-inside: avoid; }

      @media print{ .stats-grid{ grid-template-columns: repeat(2, 1fr); } }
    `;
    }

    // =========================
    // Quarterly HTML (only uses from/to label)
    // =========================
    function buildQuarterlyDashboardHtml(report, from, to, notes = []) {
      const fromText = String(from || fromDate.value || '').trim();
      const toText = String(to || toDate.value || '').trim();
      const rangeLabel = fromText && toText ? `${fromText} تا ${toText}` : '';

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

      const pills = rangeLabel
        ? `<span class="pill">د راپور موده: ${escapeHtml(rangeLabel)}</span>`
        : '';

      const infoSection =
        hasUsefulRows(infoTypes, 'count')
          ? `
            <div class="section">
              <div class="card no-break">
                <div class="header-strip bg-primary">
                  <div class="strip-left">
                    <div>
                      <div class="strip-title">د اطلاعاتو ډولونه</div>
                      <div class="strip-sub">د ډول له مخې وېش</div>
                    </div>
                  </div>
                  <span class="chip chip-primary">${informationsTotal}</span>
                </div>

                <div style="padding:0;">
                  <table>
                    <thead>
                      <tr>
                        <th style="width:70%;">ډول</th>
                        <th class="td-end" style="width:30%;">شمېر</th>
                      </tr>
                    </thead>
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
                  <div class="strip-left">
                    <div>
                      <div class="strip-title">لیټري دوسیه</div>
                      <div class="strip-sub">د لیټري دوسیه فارمت فورمونو لنډیز</div>
                    </div>
                  </div>
                  <span class="chip chip-success">${formatsActive}</span>
                </div>

                <div style="padding:0;">
                  <table>
                    <thead>
                      <tr>
                        <th style="width:70%;">فورم</th>
                        <th class="td-end" style="width:30%;">شمېر</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${formats
                        .filter((f) => safeNum(f?.count) > 0)
                        .map((f) => {
                          const name = escapeHtml(String(f?.name ?? ''));
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
                  <div class="strip-left">
                    <div>
                      <div class="strip-title">پېښې (د ډول له مخې)</div>
                      <div class="strip-sub">د پېښو وېش</div>
                    </div>
                  </div>
                  <span class="chip chip-warning">${eventsTotal}</span>
                </div>

                <div style="padding:0;">
                  <table>
                    <thead>
                      <tr>
                        <th style="width:70%;">د پېښې ډول</th>
                        <th class="td-end" style="width:30%;">شمېر</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${eventTypes
                        .filter((t) => safeNum(t?.count) > 0)
                        .map((t) => {
                          const label = escapeHtml(t?.label ?? '');
                          const count = safeNum(t?.count);
                          return `
                            <tr>
                              <td>${label || '-'}</td>
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
          <title>quarterly_dashboard_${escapeHtml(rangeLabel || 'range')}</title>
          <style>${baseExportCss()}</style>
        </head>
        <body>
          <div class="page">
            <div class="hero no-break">
              <div class="hero-top">
                <div>
                  <h1 class="hero-title">د 333 ربعه راپور</h1>
                  <p class="hero-sub">د سرچینو، معلوماتو، پېښو او فورمونو عمومي کتنه</p>
                </div>
                <div class="hero-logo">
                  <img src="${escapeHtml(getLogoUrl())}" alt="Logo" />
                </div>
              </div>

              <div class="pills">${pills}</div>
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
    // API (✅ from/to)
    // =========================
    const FetchQuarterlyFoulReport = async ({ from, to } = {}) => {
      try {
        loading.value = true;
        error.value = '';

        const f = String(from ?? fromDate.value ?? '').trim();
        const t = String(to ?? toDate.value ?? '').trim();

        fromDate.value = f;
        toDate.value = t;

        const { data } = await axios.get('/reports/foul/quarterly', {
          params: { from: fromDate.value, to: toDate.value },
        });

        quarterly.value = data;
        return data;
      } catch (e) {
        quarterly.value = null;
        error.value = normalizeError(e);
        toast.error(error.value);
        return null;
      } finally {
        loading.value = false;
      }
    };

    const ResetQuarterly = () => {
      quarterly.value = null;
      error.value = '';
      fromDate.value = todayIso();
      toDate.value = todayIso();
    };

    // =========================
    // EXPORT ACTIONS (✅ with notes using from/to)
    // =========================
    const DownloadQuarterlyFoulPdf = async () => {
      const report = quarterly.value;
      if (!report) return;

      const f = String(fromDate.value || '').trim();
      const t = String(toDate.value || '').trim();

      const notes = await fetchNotesForExport({
        type: 'quarterly',
        from: f,
        to: t,
      });

      const html = buildQuarterlyDashboardHtml(report, f, t, notes);
      openPrintWindow(html);
    };

    const DownloadQuarterlyFoulWord = async () => {
      const report = quarterly.value;
      if (!report) return;

      const f = String(fromDate.value || '').trim();
      const t = String(toDate.value || '').trim();

      const notes = await fetchNotesForExport({
        type: 'quarterly',
        from: f,
        to: t,
      });

      const html = buildQuarterlyDashboardHtml(report, f, t, notes);
      const safeName = `${f || 'from'}_${t || 'to'}`.replaceAll(':', '-');
      downloadWordFile(html, `quarterly_dashboard_${safeName}.doc`);
    };

    return {
      // state
      loading,
      error,

      // payload
      quarterly,

      // filters
      fromDate,
      toDate,

      // actions
      FetchQuarterlyFoulReport,
      ResetQuarterly,

      // export
      DownloadQuarterlyFoulPdf,
      DownloadQuarterlyFoulWord,
    };
  }
);
