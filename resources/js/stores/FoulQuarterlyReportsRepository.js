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

    // Filters
    const selectedQuarter = ref(''); // YYYY-Q1..Q3

    // =========================
    // Helpers
    // =========================
    function currentQuarterLabel() {
      const now = new Date();
      const y = now.getFullYear();
      const m = now.getMonth() + 1; // 1..12
      const q = m <= 4 ? 1 : m <= 8 ? 2 : 3;
      return `${y}-Q${q}`;
    }

    function todayIso() {
      const d = new Date();
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${y}-${m}-${day}`;
    }

    function quarterToIsoDate(quarterLabel) {
      // "YYYY-Qn" -> representative date within quarter (YYYY-01-01 / YYYY-05-01 / YYYY-09-01)
      const s = String(quarterLabel ?? '').trim();
      const m = s.match(/^(\d{4})-Q([1-3])$/i);
      if (!m) return todayIso();

      const year = m[1];
      const q = Number(m[2]);
      const month = q === 1 ? '01' : q === 2 ? '05' : '09'; // Q1..Q3 (4-month quarters)
      return `${year}-${month}-01`;
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
    // Dates (Miladi + Hijri Shamsi + Hijri Qamari) -> SAME as Daily/Monthly/Weekly design
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

      if (miladi) pills.push(`<span class="pill pill-miladi">میلادي: ${escapeHtml(miladi)}</span>`);
      if (shamsi)
        pills.push(`<span class="pill pill-shamsi">هجري شمسي: ${escapeHtml(shamsi)}</span>`);
      if (qamari)
        pills.push(`<span class="pill pill-qamari">هجري قمري: ${escapeHtml(qamari)}</span>`);

      return pills.join('');
    }

    // =========================
    // Export CSS (same as Yearly dashboard)
    // =========================
    function baseExportCss() {
      const fontStack =
        `'Amiri', 'Scheherazade New', ui-sans-serif, -apple-system, BlinkMacSystemFont,` +
        ` 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji',` +
        ` 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif`;

      return `
      @page { size: A4 portrait; margin: 14mm; }

      :root{
        --app-font: ${fontStack};
      }

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

      .page {
        width: 100%;
        padding: 8mm;
        background: #f6f7fb;
      }

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

      .hero-title{
        color:#fff;
        font-weight:900;
        font-size:18px;
        margin:0 0 4px;
        letter-spacing:.2px;
        line-height:1.25;
      }
      .hero-sub{
        color: rgba(255,255,255,.92);
        margin:0;
        font-size:12px;
        line-height:1.55;
        font-weight:700;
      }

      .hero-date{
        display:flex;
        flex-direction:column;
        align-items:flex-end;
        gap:6px;
        padding:10px 12px;
        border-radius:12px;
        border:1px solid rgba(255,255,255,.24);
        background: rgba(255,255,255,.16);
        color:#fff;
        white-space:nowrap;
        min-width: 150px;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.06);
      }
      .hero-date .date-big{
        font-weight:900;
        font-size:12px;
      }
      .hero-date .date-small{
        font-weight:800;
        font-size:10px;
        opacity:.92;
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
        padding:6px 10px;
        border-radius:999px;
        font-weight:900;
        font-size:11px;
        border:1px solid rgba(255,255,255,.28);
        background: rgba(255,255,255,.14);
        color:#fff;
      }
      .pill-miladi{ }
      .pill-shamsi{ }
      .pill-qamari{ }

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
      .stat-label{
        font-size:11px;
        color: rgba(17,24,39,.65);
        font-weight:900;
        margin-bottom:4px;
      }
      .stat-number{
        font-size:22px;
        font-weight:900;
        line-height:1.1;
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
      }
      .b-primary{ background:#eef2ff; color:#1e40af; border-color:#c7d2fe; }
      .b-info{ background:#e0f2fe; color:#075985; border-color:#bae6fd; }
      .b-warning{ background:#fff7ed; color:#9a3412; border-color:#fed7aa; }
      .b-success{ background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }

      .grid-2{
        display:grid;
        grid-template-columns: 5fr 7fr;
        gap:10px;
        margin-top:10px;
      }

      .header-strip{
        padding:10px 12px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
      }
      .strip-title{
        font-weight:900;
        font-size:12px;
        margin:0;
      }
      .strip-sub{
        margin:2px 0 0;
        color: rgba(17,24,39,.55);
        font-size:10px;
        font-weight:800;
      }
      .strip-left{ display:flex; align-items:center; gap:10px; }

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

      table{
        width:100%;
        border-collapse:separate;
        border-spacing: 0;
        table-layout: fixed;
      }
      thead th{
        background:#f8fafc;
        color: rgba(17,24,39,.72);
        font-weight:900;
        font-size:11px;
        padding:9px 8px;
        border-bottom:1px solid #eef2f7;
      }
      tbody td{
        padding:9px 8px;
        border-bottom:1px solid #f0f4fa;
        font-size:11px;
        vertical-align:middle;
        color: rgba(17,24,39,.90);
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

      .etype{
        display:inline-block;
        padding:4px 10px;
        border-radius:999px;
        font-weight:900;
        font-size:10px;
        border:1px solid #e5e7eb;
        background:#f3f4f6;
      }
      .etype-red{ background:#fee2e2; border-color:#fecaca; color:#991b1b; }
      .etype-amber{ background:#ffedd5; border-color:#fed7aa; color:#9a3412; }
      .etype-purple{ background:#ede9fe; border-color:#ddd6fe; color:#5b21b6; }
      .etype-blue{ background:#dbeafe; border-color:#bfdbfe; color:#1e40af; }
      .etype-gray{ background:#f3f4f6; border-color:#e5e7eb; color:#374151; }

      .footer{
        margin-top:10px;
        padding:10px 12px;
        background:#f8fafc;
        border-top:1px solid #eef2f7;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        font-size:10px;
        color: rgba(17,24,39,.65);
        font-weight:900;
      }
      .hero-logo{
        display:flex;
        align-items:center;
        justify-content:flex-end;
        padding:10px 12px;
        border-radius:12px;
        min-width: 150px;
      }
      .hero-logo img{
        height:42px;
        width:auto;
        object-fit:contain;
        display:block;
      }
      .no-break { break-inside: avoid; page-break-inside: avoid; }
      .card { break-inside: avoid; page-break-inside: avoid; }

      @media print{
        .stats-grid{ grid-template-columns: repeat(2, 1fr); }
        .grid-2{ grid-template-columns: 1fr; }
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

    function quarterTitleFromLabel(label) {
      // "YYYY-Qn" -> readable range
      const s = String(label ?? '').trim();
      const m = s.match(/^(\d{4})-Q([1-3])$/i);
      if (!m) return s;

      const q = Number(m[2]);
      const range = q === 1 ? 'جنوري - اپرېل' : q === 2 ? 'می - اګست' : 'سېپتمبر - ډسمبر';
      return `ربعه ${q} (${range})`;
    }

    // =========================
    // Quarterly HTML (same dashboard style)
    // =========================
    function buildQuarterlyDashboardHtml(report, quarterLabel) {
      const qLabel = String(quarterLabel || selectedQuarter.value || currentQuarterLabel());
      const headerIso = quarterToIsoDate(qLabel);
      const pills = buildDatePillsSameDay(headerIso);

      const resourcesTotal = safeNum(report?.resources?.total);
      const informationsTotal = safeNum(report?.informations?.total);
      const eventsTotal = safeNum(report?.events?.total);

      const formats = Array.isArray(report?.formats) ? report.formats : [];
      const formatsActive = formats.length;
      const formatsSum = formats.reduce((s, f) => s + safeNum(f?.count), 0);

      const infoTypes = Array.isArray(report?.informations?.types) ? report.informations.types : [];
      const eventTypes = Array.isArray(report?.events?.types) ? report.events.types : [];
      function getLogoUrl() {
        // ✅ logo must be inside /public so it works in print window
        return `${window.location.origin}/logo.png`;
      }
      const infoTr = infoTypes.length
        ? infoTypes
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
            .join('')
        : `<tr><td colspan="2" class="td-center">هیڅ معلومات نشته</td></tr>`;

      const formatsTr = formats.length
        ? formats
            .map((f) => {
              const name = escapeHtml(f?.name ?? '');
              const count = safeNum(f?.count);
              return `
              <tr>
                <td>${name}</td>
                <td class="td-end"><span class="count-pill count-success">${count}</span></td>
              </tr>
            `;
            })
            .join('')
        : `<tr><td colspan="2" class="td-center">هیڅ فورم کې معلومات نشته</td></tr>`;

      const eventsTr = eventTypes.length
        ? eventTypes
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
            .join('')
        : `<tr><td colspan="2" class="td-center">هیڅ پېښه نه ده ثبت شوې</td></tr>`;

      const quarterTitle = quarterTitleFromLabel(qLabel);

      return `
      <html dir="rtl">
        <head>
          <meta charset="utf-8" />
          <title>quarterly_dashboard_${qLabel}</title>
          <style>${baseExportCss()}</style>
        </head>
        <body>
          <div class="page">
            <div class="hero no-break">
              <div class="hero-top">
                <div>
                  <h1 class="hero-title">د 333 ربعه راپور</h1>
                  <p class="hero-sub">د سرچینو، معلوماتو، پېښو او فورمونو د ربعه عمومي کتنه</p>
                </div>
                 <div class="hero-logo">
                  <img src="${escapeHtml(getLogoUrl())}" alt="Logo" />
                </div>
              </div>

              <div class="pills">${pills || ''}</div>
            </div>

            <div class="stats-grid section no-break">
              <div class="card">
                <div class="stat-card">
                  <div>
                    <div class="stat-label">منابع</div>
                    <div class="stat-number">${resourcesTotal}</div>
                    <span class="badge b-primary">استخدام چک او کنټرول روزنه ترسره شوه</span>
                  </div>
                </div>
              </div>

              <div class="card">
                <div class="stat-card">
                  <div>
                    <div class="stat-label">اطلاعات</div>
                    <div class="stat-number">${informationsTotal}</div>
                    <span class="badge b-info">شتون لري</span>
                  </div>
                </div>
              </div>

              <div class="card">
                <div class="stat-card">
                  <div>
                    <div class="stat-label">پېښې</div>
                    <div class="stat-number">${eventsTotal}</div>
                    <span class="badge b-warning">ثبت شوې</span>
                  </div>
                </div>
              </div>

              <div class="card">
                <div class="stat-card">
                  <div>
                    <div class="stat-label">لیټري دوسیه فارمت فورمونه</div>
                    <div class="stat-number">${formatsActive}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="grid-2 section">
              <div>
                <div class="card no-break">
                  <div class="header-strip bg-primary">
                    <div class="strip-left">
                      <div>
                        <div class="strip-title">د معلوماتو ډولونه</div>
                        <div class="strip-sub">د ډول له مخې وېش (ربعه)</div>
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
                      <tbody>${infoTr}</tbody>
                    </table>
                  </div>
                </div>

                <div class="card no-break" style="margin-top:10px;">
                  <div class="header-strip bg-success">
                    <div class="strip-left">
                      <div>
                        <div class="strip-title">لیټري دوسیه</div>
                        <div class="strip-sub">د لیټري دوسیه فارمت فورمونو لنډیز</div>
                      </div>
                    </div>
                    <span class="chip chip-success">${formatsActive}</span>
                  </div>
                </div>
              </div>

              <div class="card">
                <div class="header-strip bg-warning">
                  <div class="strip-left">
                    <div>
                      <div class="strip-title">پېښې (د ډول له مخې)</div>
                      <div class="strip-sub">د ربعه د پېښو وېش</div>
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
                    <tbody>${eventsTr}</tbody>
                  </table>
                </div>
              </div>
            </div>
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
    const FetchQuarterlyFoulReport = async (quarter) => {
      try {
        loading.value = true;
        error.value = '';

        const finalQuarter = quarter || selectedQuarter.value || currentQuarterLabel();
        selectedQuarter.value = String(finalQuarter);

        const { data } = await axios.get('/reports/foul/quarterly', {
          params: { quarter: selectedQuarter.value }, // backend expects "YYYY-Qn"
        });

        quarterly.value = data;
        return data;
      } catch (e) {
        console.error(e);
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
      selectedQuarter.value = currentQuarterLabel();
    };

    // Init defaults
    if (!selectedQuarter.value) selectedQuarter.value = currentQuarterLabel();

    // =========================
    // EXPORT ACTIONS (new dashboard design)
    // =========================
    const DownloadQuarterlyFoulPdf = () => {
      const report = quarterly.value;
      if (!report) return;

      const qText = selectedQuarter.value || currentQuarterLabel();
      const html = buildQuarterlyDashboardHtml(report, qText);
      openPrintWindow(html);
    };

    const DownloadQuarterlyFoulWord = () => {
      const report = quarterly.value;
      if (!report) return;

      const qText = selectedQuarter.value || currentQuarterLabel();
      const html = buildQuarterlyDashboardHtml(report, qText);
      downloadWordFile(html, `quarterly_dashboard_${qText}.doc`);
    };

    return {
      // state
      loading,
      error,

      // payload
      quarterly,

      // filter
      selectedQuarter,

      // actions
      FetchQuarterlyFoulReport,
      ResetQuarterly,

      // export
      DownloadQuarterlyFoulPdf,
      DownloadQuarterlyFoulWord,
    };
  }
);
