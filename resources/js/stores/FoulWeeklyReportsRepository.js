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

  // Filters
  const selectedWeek = ref(''); // YYYY-Www

  // ===== helpers =====
  function currentWeekIso() {
    // ISO week label like "2025-W52"
    const d = new Date();
    const date = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    const dayNum = date.getUTCDay() || 7; // 1..7 (Mon..Sun)
    date.setUTCDate(date.getUTCDate() + 4 - dayNum); // nearest Thu
    const yearStart = new Date(Date.UTC(date.getUTCFullYear(), 0, 1));
    const weekNo = Math.ceil((((date - yearStart) / 86400000) + 1) / 7);
    const yyyy = date.getUTCFullYear();
    const ww = String(weekNo).padStart(2, '0');
    return `${yyyy}-W${ww}`;
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

    let msg = 'خطا در دریافت گزارش هفته‌وار.';

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

  // ===== EXPORT HTML BUILDERS =====

  function buildHeaderHtml() {
    // same logo path rule as your existing print components
    const logoSrc = `${window.location.origin}/view.png`;

    return `
      <!-- HEADER: titles + logo (your existing structure) -->
      <table class="header-table">
        <tr>
          <!-- RIGHT side (stacked 4 items) -->
          <td class="stack-cell" style="text-align:right;">
            فورم ثبت اطلاع <br />
            فورم ثبت اطلاع <br />
            فورم ثبت اطلاع <br />
            فورم ثبت اطلاع
          </td>

          <!-- CENTER LOGO -->
          <td class="logo-cell">
            <img src="${logoSrc}" alt="logo" />
          </td>

          <!-- LEFT side (stacked 5 items) -->
          <td class="stack-cell" style="text-align:left;">
            د اطلاع ثبت فورم <br />
            د اطلاع ثبت فورم <br />
            د اطلاع ثبت فورم <br />
            د اطلاع ثبت فورم <br />
            د اطلاع ثبت فورم
          </td>
        </tr>
      </table>

      <table class="top-head-table">
        <tr>
          <!-- RIGHT: meta (کتنه / نېټه) -->
          <td class="top-right">
            <div class="meta-row">
              <span class="meta-label">کتنه:</span>
              <span class="meta-box"></span>
            </div>

            <div class="meta-row">
              <span class="meta-label">نېټه:</span>
              <span class="meta-date">
              </span>
            </div>
          </td>

          <!-- CENTER: bismillah on same row -->
          <td class="top-center">
            <div class="bismillah-top">بسم الله الرحمن الرحیم</div>
            <div class="bismillah-orn"></div>
          </td>

          <!-- LEFT: checkboxes -->
          <td class="top-left">
            <div class="check-row">
              <span class="check-label">محرمیت درجه:</span>
              <span class="check-box"></span>
              <span class="check-box"></span>
            </div>

            <div class="check-row">
              <span class="check-label">استعمالات درجه:</span>
              <span class="check-box"></span>
              <span class="check-box"></span>
            </div>
          </td>
        </tr>
      </table>
    `;
  }

  function buildInformationsTypesLines(report) {
    const types = Array.isArray(report?.informations?.types) ? report.informations.types : [];

    // only show existing types with count > 0
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
    // weekly endpoint returns events.types (grouped by event_type)
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

  function buildWeeklyBodyHtml(report, weekText) {
    const resourcesTotal = safeNum(report?.resources?.total);

    const infoLines = buildInformationsTypesLines(report);
    const eventLines = buildEventTypesLines(report);

    // 56 formats total count ONLY (sum of counts)
    const formats = Array.isArray(report?.formats) ? report.formats : [];
    const formatsCount = formats.reduce((sum, f) => sum + safeNum(f?.count), 0);

    return `
      <div class="letter-body">
        ${buildHeaderHtml()}

        <!-- ✅ gray divider under bismillah like image -->
        <div class="bismillah-divider"></div>

        <div class="line title">د هفته‌وار راپور ( ${escapeHtml(weekText || '')} )</div>

        <div class="line bold center">السلام علیکم ورحمة الله وبرکاته</div>

        <div class="line center">
          الله تعالی مو په ټولو شرعی امورو کې موفق او بریالی لره.
        </div>

        <div class="line right bold">وروســته:</div>

        <ul class="bullets">
          <li>
            د کابل ښار په م ختلفو حوزو کې ( ${resourcesTotal} ) تنه منابع استخدام شوی، وروزل شوی او ارزونه یې تررسه شوه.
          </li>

          <li class="bold">ثبت شوی اطلاعات:</li>
          <ul class="sub">
            ${infoLines || ''}
          </ul>

          <li class="bold">ثبت شوی پېښې (د نوعیت پر اساس):</li>
          <ul class="sub">
            ${eventLines || ''}
          </ul>

          <li class="bold">د کابل ښار لېبری دوسیه (بنجوسو فارمت) کې ثبت شوی معلومات:</li>
          <ul class="sub">
            <li>د کابل ښار په مختلفو حوزو کې د ( ${formatsCount} ) مواردو اړوند معلومات ثبت شول.</li>
          </ul>
        </ul>

        <div class="closing">
          <div class="bold">په درنښت</div>
          <div class="bold">د ۳۳۳ مدیریت مدیر</div>
        </div>
      </div>
    `;
  }

  function buildWeeklyDocumentHtml(report, weekText) {
    const body = buildWeeklyBodyHtml(report, weekText);

    return `
      <html dir="rtl">
        <head>
          <meta charset="utf-8" />
          <title>weekly_report_${weekText || ''}</title>
          <style>
            @page { size: A4 portrait; margin: 10mm; }

            html, body {
              margin: 0;
              padding: 0;
              background: #ffffff;
              color: #000;
              font-family: system-ui, -apple-system, "Segoe UI", Tahoma, Arial, sans-serif;
              font-size: 13px;
              line-height: 1.85;
            }

            .page { width: 100%; box-sizing: border-box; }
            table { border-collapse: collapse; width: 100%; }

            .top-head-table { table-layout: fixed; width: 100%; margin-bottom: 4px; }
            .top-head-table td { vertical-align: top; padding: 0; }

            .top-right { width: 33%; text-align: right; font-size: 12px; line-height: 1.6; padding-right: 2px; white-space: nowrap; }
            .meta-row { display: flex; justify-content: flex-end; align-items: center; gap: 6px; margin: 2px 0; }
            .meta-label { font-weight: 700; }
            .meta-box { width: 26px; height: 14px; border: 1px solid #000; display: inline-block; }
            .meta-date { display: inline-flex; align-items: center; gap: 14px; min-width: 60px; justify-content: center; }

            .top-center { width: 34%; text-align: center; padding-top: 2px; }
            .bismillah-top { font-weight: 700; font-size: 14px; margin-bottom: 2px; }
            .bismillah-orn { height: 10px; position: relative; margin: 0 auto; width: 90%; }
            .bismillah-orn::before { content: ""; position: absolute; left: 50%; top: 3px; transform: translateX(-50%); width: 160px; height: 2px; background: #000; border-radius: 2px; opacity: 0.6; }

            .top-left { width: 33%; text-align: left; font-size: 12px; line-height: 1.6; padding-left: 2px; white-space: nowrap; }
            .check-row { display: flex; align-items: center; justify-content: flex-start; gap: 6px; margin: 2px 0; }
            .check-box { width: 16px; height: 12px; border: 1px solid #000; display: inline-block; }
            .check-label { font-weight: 700; }

            .header-table { width: 90%; border: none; table-layout: fixed; margin: 0; margin-right: 20px; }
            .header-table td { border: none !important; font-size: 14px; padding: 0 4px !important; vertical-align: middle; }
            .stack-cell { width: 35%; line-height: 1.35; font-weight: 700; }
            .logo-cell { width: 30%; text-align: center; }
            .logo-cell img { width: 70px; height: 55px; object-fit: contain; }

            .bismillah-divider {
              width: 100%;
              height: 16px;
              background: #cfcfcf !important;
              border-top: 1px solid #000;
              border-bottom: 1px solid #000;
              margin: 0 0 10px;
            }

            .line { margin: 3px 0; }
            .bold { font-weight: 700; }
            .center { text-align: center; }
            .right { text-align: right; }

            .title {
              font-size: 16px;
              font-weight: 800;
              text-align: center;
              margin: 8px 0 6px;
            }

            ul.bullets { margin: 8px 0 0; padding-right: 18px; list-style-type: disc; }
            ul.bullets > li { margin: 10px 0; }

            ul.sub { margin: 6px 0 0; padding-right: 0; list-style: none; }
            ul.sub > li { margin: 4px 0; padding-right: 18px; position: relative; }
            ul.sub > li::before { content: "➢"; position: absolute; right: 0; top: 0; font-weight: 700; }

            .closing { margin-top: 16px; text-align: center; font-weight: 700; }
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
  const FetchWeeklyFoulReport = async (week) => {
    try {
      loading.value = true;
      error.value = '';

      const finalWeek = week || selectedWeek.value || currentWeekIso();
      selectedWeek.value = String(finalWeek);

      const { data } = await axios.get('/reports/foul/weekly', {
        params: { week: selectedWeek.value },
      });

      weekly.value = data;
      return data;
    } catch (e) {
      console.error(e);
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
    selectedWeek.value = currentWeekIso();
  };

  // Init defaults
  if (!selectedWeek.value) selectedWeek.value = currentWeekIso();

  // ===== EXPORT ACTIONS =====
  const DownloadWeeklyFoulPdf = () => {
    const report = weekly.value;
    if (!report) return;

    const weekText = selectedWeek.value || currentWeekIso();
    const html = buildWeeklyDocumentHtml(report, weekText);
    openPrintWindow(html);
  };

  const DownloadWeeklyFoulWord = () => {
    const report = weekly.value;
    if (!report) return;

    const weekText = selectedWeek.value || currentWeekIso();
    const html = buildWeeklyDocumentHtml(report, weekText);
    downloadWordFile(html, `weekly_report_${weekText}.doc`);
  };

  return {
    // state
    loading,
    error,

    // payload
    weekly,

    // filter
    selectedWeek,

    // actions
    FetchWeeklyFoulReport,
    ResetWeekly,

    // export
    DownloadWeeklyFoulPdf,
    DownloadWeeklyFoulWord,
  };
});
