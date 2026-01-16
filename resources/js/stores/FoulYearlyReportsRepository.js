// stores/FoulYearlyReportsRepository.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '@/axios';
import { toast } from 'vue3-toastify';

export const useFoulYearlyReportsRepository = defineStore('FoulYearlyReportsRepository', () => {
  const loading = ref(false);

  // ✅ IMPORTANT: keep error as STRING (never object)
  const error = ref('');

  // Yearly report payload from backend
  const yearly = ref(null);

  // Filters
  const selectedYear = ref(''); // YYYY

  // ===== helpers =====
  // ===== helpers =====
  function currentYearIso() {
    return String(new Date().getFullYear());
  }

  function todayIso() {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }

  function yearToIsoDate(yearText) {
    // YYYY -> YYYY-01-01 (only for header date formatting)
    const y = String(yearText || '').trim();
    if (/^\d{4}$/.test(y)) return `${y}-01-01`;
    return todayIso();
  }

  /* ================================
   * YEAR FORMATTERS (NO MILADI)
   * ================================ */
  function formatPersianHijriShamsiYear(dateIso) {
    try {
      const d = new Date(`${dateIso}T00:00:00`);
      return new Intl.DateTimeFormat('fa-AF-u-ca-persian', {
        year: 'numeric',
      }).format(d);
    } catch {
      return '';
    }
  }

  function formatHijriQamariYear(dateIso) {
    try {
      const d = new Date(`${dateIso}T00:00:00`);
      return new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura', {
        year: 'numeric',
      }).format(d);
    } catch {
      try {
        const d = new Date(`${dateIso}T00:00:00`);
        return new Intl.DateTimeFormat('ar-SA-u-ca-islamic', {
          year: 'numeric',
        }).format(d);
      } catch {
        return '';
      }
    }
  }

  function buildYearTitleLines(dateIso) {
    const shamsiYear = formatPersianHijriShamsiYear(dateIso);
    const qamariYear = formatHijriQamariYear(dateIso);

    const parts = [];
    if (shamsiYear) parts.push(`هجری شمسی: ${escapeHtml(shamsiYear)}`);
    if (qamariYear) parts.push(`هجری قمری: ${escapeHtml(qamariYear)}`);

    return parts.join(' / ');
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

    let msg = 'خطا در دریافت گزارش سالانه.';

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

  // ===== DATE FORMATTERS (ONLY Hijri Shamsi + Hijri Qamari) =====
  function formatPersianHijriShamsi(dateIso) {
    // Persian calendar (هجری شمسی)
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
    // Islamic calendar (هجری قمری) - try umalqura if available
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

  function buildDateLines(dateIso) {
    const shamsi = formatPersianHijriShamsi(dateIso);
    const qamari = formatHijriQamari(dateIso);

    // ✅ no میلادی output at all
    const parts = [];
    if (shamsi) {
      parts.push(
        `<div class="date-line"><span class="date-label">هجری شمسی:</span> <span>${escapeHtml(
          shamsi
        )}</span></div>`
      );
    }
    if (qamari) {
      parts.push(
        `<div class="date-line"><span class="date-label">هجری قمری:</span> <span>${escapeHtml(
          qamari
        )}</span></div>`
      );
    }
    return parts.join('');
  }

  // ===== EXPORT HTML BUILDERS (DESIGN MATCH DAILY/MONTHLY/WEEKLY) =====
  function buildHeaderHtml(dateIsoForHeader) {
    const logoSrc = `${window.location.origin}/view.png`;
    const dateLines = buildDateLines(dateIsoForHeader);

    return `
      <!-- HEADER: titles + logo -->
      <table class="header-table">
        <tr>
          <!-- RIGHT (Dari) -->
          <td class="stack-cell stack-right">
            <div class="stack-inner">
              فورم ثبت اطلاع <br />
              فورم ثبت اطلاع <br />
              فورم ثبت اطلاع <br />
              فورم ثبت اطلاع
            </div>
          </td>

          <!-- CENTER LOGO -->
          <td class="logo-cell">
            <img src="${logoSrc}" alt="logo" />
          </td>

          <!-- LEFT (Pashto) -->
          <td class="stack-cell stack-left">
            <div class="stack-inner">
              د اطلاع ثبت فورم <br />
              د اطلاع ثبت فورم <br />
              د اطلاع ثبت فورم <br />
              د اطلاع ثبت فورم <br />
              د اطلاع ثبت فورم
            </div>
          </td>
        </tr>
      </table>

      <table class="top-head-table">
        <tr>
          <!-- RIGHT: meta (کتنه / نېټه) -->
          <td class="top-right">
            <div class="meta-row">
            </div>

            <div class="meta-row">
              <span class="meta-label">نېټه:</span>
              <span class="meta-date">
                ${dateLines || ''}
              </span>
            </div>
          </td>

          <!-- CENTER: bismillah -->
          <td class="top-center">
            <div class="bismillah-top">بسم الله الرحمن الرحیم</div>
            <div class="bismillah-orn"></div>
          </td>

          <!-- LEFT: checkboxes -->
          <td class="top-left">
            <div class="check-row">
            </div>

            <div class="check-row">
            </div>
          </td>
        </tr>
      </table>
    `;
  }

  function buildInformationsTypesLines(report) {
    const types = Array.isArray(report?.informations?.types) ? report.informations.types : [];

    const valid = types
      .map((t) => ({
        label: String(t?.label ?? '').trim(),
        count: safeNum(t?.count),
      }))
      .filter((x) => x.label && x.count > 0);

    if (!valid.length) return '';

    return valid.map((x) => `<li>${escapeHtml(x.label)} ( ${x.count} ).</li>`).join('');
  }

  function buildEventTypesLines(report) {
    const types = Array.isArray(report?.events?.types) ? report.events.types : [];

    const valid = types
      .map((t) => ({
        label: String(t?.label ?? '').trim(),
        count: safeNum(t?.count),
      }))
      .filter((x) => x.label && x.count > 0);

    if (!valid.length) return '';

    return valid.map((x) => `<li>${escapeHtml(x.label)} ( ${x.count} ).</li>`).join('');
  }

  function buildYearlyBodyHtml(report, yearText, headerDateIso) {
    const resourcesTotal = safeNum(report?.resources?.total);

    const infoLines = buildInformationsTypesLines(report);
    const eventLines = buildEventTypesLines(report);

    const formats = Array.isArray(report?.formats) ? report.formats : [];
    const formatsCount = formats.reduce((sum, f) => sum + safeNum(f?.count), 0);

    // ✅ Build ONLY parts that have data (same behavior as daily/monthly/weekly)
    const parts = [];

    // Header + divider always
    parts.push(buildHeaderHtml(headerDateIso));
    parts.push(`<div class="bismillah-divider"></div>`);
    parts.push(`
  <div class="line title">
    د کلني راپور
    <div style="margin-top:4px; font-size:13px; font-weight:700;">
      ${buildYearTitleLines(headerDateIso)}
    </div>
  </div>
`);

    parts.push(`<div class="line right bold">السلام علیکم ورحمة الله وبرکاته</div>`);
    parts.push(
      `<div class="line right">الله تعالی مو په ټولو شرعی امورو کې موفق او بریالی لره.</div>`
    );
    parts.push(`<div class="line right bold">وروســته:</div>`);

    const bulletBlocks = [];

    if (resourcesTotal > 0) {
      bulletBlocks.push(`
        <li>
          د کابل ښار په م ختلفو حوزو کې ( ${resourcesTotal} ) تنه منابع استخدام شوی، وروزل شوی او ارزونه یې تررسه شوه.
        </li>
      `);
    }

    if (infoLines) {
      bulletBlocks.push(`
        <li class="bold">ثبت شوی اطلاعات:</li>
        <ul class="sub">
          ${infoLines}
        </ul>
      `);
    }

    if (eventLines) {
      bulletBlocks.push(`
        <li class="bold">ثبت شوی پېښې (د نوعیت پر اساس):</li>
        <ul class="sub">
          ${eventLines}
        </ul>
      `);
    }

    if (formatsCount > 0) {
      bulletBlocks.push(`
        <li class="bold">د کابل ښار لیټری دوسیه (۵۰ فارمت) کې ثبت شوی معلومات:</li>
        <ul class="sub">
          <li>د کابل ښار په مختلفو حوزو کې د ( ${formatsCount} ) مواردو اړوند معلومات ثبت شول.</li>
        </ul>
      `);
    }

    if (bulletBlocks.length) {
      parts.push(`
        <ul class="bullets">
          ${bulletBlocks.join('')}
        </ul>
      `);
    }

    // Closing always
    parts.push(`
      <div class="closing">
        <div class="bold">په درنښت</div>
        <div class="bold">د ۳۳۳ مدیریت مدیر</div>
      </div>
    `);

    return `<div class="letter-body">${parts.join('')}</div>`;
  }

  function buildYearlyDocumentHtml(report, yearText) {
    const yearVal = String(yearText || selectedYear.value || currentYearIso());
    const headerDateIso = yearToIsoDate(yearVal);
    const body = buildYearlyBodyHtml(report, yearVal, headerDateIso);

    return `
      <html dir="rtl">
        <head>
          <meta charset="utf-8" />
          <title>yearly_report_${yearVal || ''}</title>
          <style>
            /* ✅ consistent margins for print + Word (same as daily/monthly/weekly) */
            @page { size: A4 portrait; margin: 0; }

            html, body {
              margin: 0;
              padding: 0;
              background: #ffffff;
              color: #000;
              font-family: system-ui, -apple-system, "Segoe UI", Tahoma, Arial, sans-serif;
              font-size: 13px;
              line-height: 1.85;
            }

            /* ✅ this creates space from ALL sides */
            .page {
              width: 100%;
              box-sizing: border-box;
              padding: 12mm;
            }

            table { border-collapse: collapse; width: 100%; }

            .top-head-table { table-layout: fixed; width: 100%; margin-bottom: 4px; }
            .top-head-table td { vertical-align: top; padding: 0; }

            .top-right { width: 33%; text-align: right; font-size: 12px; line-height: 1.6; padding-right: 2px; white-space: nowrap; }
            .meta-row { display: flex; justify-content: flex-end; align-items: flex-start; gap: 6px; margin: 2px 0; }
            .meta-label { font-weight: 700; }
            .meta-box { width: 26px; height: 14px; border: 1px solid #000; display: inline-block; }
            .meta-date { display: inline-block; min-width: 60px; }
            .date-line { line-height: 1.4; }
            .date-label { font-weight: 700; }

            .top-center { width: 34%; text-align: center; padding-top: 2px; }
            .bismillah-top { font-weight: 700; font-size: 14px; margin-bottom: 2px; }
            .bismillah-orn { height: 10px; position: relative; margin: 0 auto; width: 90%; }
            .bismillah-orn::before { content: ""; position: absolute; left: 50%; top: 3px; transform: translateX(-50%); width: 160px; height: 2px; background: #000; border-radius: 2px; opacity: 0.6; }

            .top-left { width: 33%; text-align: left; font-size: 12px; line-height: 1.6; padding-left: 2px; white-space: nowrap; }
            .check-row { display: flex; align-items: center; justify-content: flex-start; gap: 6px; margin: 2px 0; }
            .check-box { width: 16px; height: 12px; border: 1px solid #000; display: inline-block; }
            .check-label { font-weight: 700; }

            /* ===== HEADER (print + word safe) ===== */
            .header-table{
              width: 100%;
              table-layout: fixed;
              border: none;
              margin: 0 auto;
            }
            .header-table td{
              border: none !important;
              padding: 0 !important;
              vertical-align: middle;
            }

            .logo-cell{ width: 46%; text-align: center; }
            .stack-cell{ width: 27%; }

            /* ✅ move texts toward the logo */
            .stack-right{ text-align: left; padding-left: 6px !important; }
            .stack-left{  text-align: right; padding-right: 6px !important; }

            .stack-inner{
              display: inline-block;
              font-size: 14px;
              font-weight: 700;
              line-height: 1.35;
              white-space: nowrap;
            }

            .logo-cell img{
              width: 70px;
              height: 55px;
              object-fit: contain;
            }

            .bismillah-divider {
              width: 100%;
              height: 16px;
              background: #cfcfcf !important;
              border-top: 1px solid #000;
              border-bottom: 1px solid #000;
              margin: 0 0 10px;
            }

            .line { margin: 3px 0; text-align: right; }
            .bold { font-weight: 700; }
            .right { text-align: right; }

            .title {
              font-size: 16px;
              font-weight: 800;
              text-align: center;
              margin: 8px 0 6px;
            }

            ul.bullets {
              margin: 8px 0 0;
              padding-right: 18px;
              list-style-type: disc;
              text-align: right;
            }
            ul.bullets > li {
              margin: 10px 0;
              text-align: right;
            }

            ul.sub {
              margin: 6px 0 0;
              padding-right: 0;
              list-style: none;
              text-align: right;
            }
            ul.sub > li {
              margin: 4px 0;
              padding-right: 18px;
              position: relative;
              text-align: right;
            }
            ul.sub > li::before {
              content: "➢";
              position: absolute;
              right: 0;
              top: 0;
              font-weight: 700;
            }

            .closing {
              margin-top: 16px;
              text-align: center;
              font-weight: 700;
            }
          </style>
        </head>

        <body>
          <div class="page">
            ${body}
          </div>
        </body>
      </html>
    `;
  }

  function openPrintWindow(html) {
    const w = window.open('', '_blank', 'width=1024,height=768');
    if (!w) return false;

    w.document.open();
    w.document.write(html);
    w.document.close();

    // ✅ ensure images (logo) load before print
    w.addEventListener('load', () => {
      const imgs = Array.from(w.document.images || []);
      if (!imgs.length) {
        w.focus();
        w.print();
        return;
      }

      let done = 0;
      const finish = () => {
        done += 1;
        if (done >= imgs.length) {
          w.focus();
          w.print();
        }
      };

      imgs.forEach((img) => {
        if (img.complete) return finish();
        img.addEventListener('load', finish);
        img.addEventListener('error', finish);
      });
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

  // ===== API =====
  const FetchYearlyFoulReport = async (year) => {
    try {
      loading.value = true;
      error.value = '';

      const finalYear = year || selectedYear.value || currentYearIso();
      selectedYear.value = String(finalYear);

      const { data } = await axios.get('/reports/foul/yearly', {
        params: { year: selectedYear.value },
      });

      yearly.value = data;
      return data;
    } catch (e) {
      console.error(e);
      yearly.value = null;

      error.value = normalizeError(e);
      toast.error(error.value);

      return null;
    } finally {
      loading.value = false;
    }
  };

  const ResetYearly = () => {
    yearly.value = null;
    error.value = '';
    selectedYear.value = currentYearIso();
  };

  // Init defaults
  if (!selectedYear.value) selectedYear.value = currentYearIso();

  // ===== EXPORT ACTIONS =====
  const DownloadYearlyFoulPdf = () => {
    const report = yearly.value;
    if (!report) return;

    const yearText = selectedYear.value || currentYearIso();
    const html = buildYearlyDocumentHtml(report, yearText);
    openPrintWindow(html);
  };

  const DownloadYearlyFoulWord = () => {
    const report = yearly.value;
    if (!report) return;

    const yearText = selectedYear.value || currentYearIso();
    const html = buildYearlyDocumentHtml(report, yearText);
    downloadWordFile(html, `yearly_report_${yearText}.doc`);
  };

  return {
    // state
    loading,
    error,

    // payload
    yearly,

    // filter
    selectedYear,

    // actions
    FetchYearlyFoulReport,
    ResetYearly,

    // export
    DownloadYearlyFoulPdf,
    DownloadYearlyFoulWord,
  };
});
