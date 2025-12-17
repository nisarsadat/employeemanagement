// stores/FoulReportsRepository.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '@/axios';
import { toast } from 'vue3-toastify';

export const useFoulReportsRepository = defineStore('FoulReportsRepository', () => {
  const loading = ref(false);

  // ✅ IMPORTANT: keep error as STRING (never object)
  const error = ref('');

  // Daily report payload from backend
  const daily = ref(null);

  // Filters
  const selectedDate = ref(''); // YYYY-MM-DD

  // ===== helpers =====
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

    let msg = 'خطا در دریافت گزارش روزانه.';

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

    // ✅ Top row: right meta (کتنه/نېټه) + center bismillah + left checkboxes (exact positioning style)
    // ✅ Then your existing header-table (stacked texts + logo)
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

      <!-- (keep your bismillah here too if you want it in this place) -->
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
      </table>    `;
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

  function buildDailyBodyHtml(report) {
    const resourcesTotal = safeNum(report?.resources?.total);

    const eventsRows = Array.isArray(report?.events?.rows) ? report.events.rows : [];
    const eventsTr = eventsRows.length
      ? eventsRows
          .map(
            (r) => `
              <tr>
                <td>${escapeHtml(r?.province ?? '')}</td>
                <td>${escapeHtml(r?.hawza ?? '')}</td>
                <td>${escapeHtml(r?.event_type ?? '')}</td>
                <td>${safeNum(r?.killed)}</td>
                <td>${safeNum(r?.injured)}</td>
                <td>${formatMoney(r?.talafat_mali)}</td>
              </tr>
            `
          )
          .join('')
      : `
        <tr>
          <td colspan="6" style="text-align:center;">هیچ رویدادی ثبت نشده</td>
        </tr>
      `;

    // 56 formats total count ONLY (sum of counts)
    const formats = Array.isArray(report?.formats) ? report.formats : [];
    const formatsCount = formats.reduce((sum, f) => sum + safeNum(f?.count), 0);

    const infoLines = buildInformationsTypesLines(report);

    // ✅ EXACT TEXT (no additions, same as your current)
    return `
      <div class="letter-body">
        ${buildHeaderHtml()}

        <!-- ✅ gray divider under bismillah like image -->
        <div class="bismillah-divider"></div>

        <div class="line title">د استقراربرنامه ۶۹- قدرمن ریاست ته!</div>

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

          <li class="bold">ثبت شوی پېښې:</li>

          <table class="events-table">
            <thead>
              <tr>
                <th>ولایت</th>
                <th>حوزه</th>
                <th>نوعیت</th>
                <th>وژل شوی</th>
                <th>ټپیان شوی</th>
                <th>مالی تلفات</th>
              </tr>
            </thead>
            <tbody>
              ${eventsTr}
            </tbody>
          </table>

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

  function buildDailyDocumentHtml(report, dateText) {
    const body = buildDailyBodyHtml(report);

    // ✅ system font + layout like image
    return `
      <html dir="rtl">
        <head>
          <meta charset="utf-8" />
          <title>daily_report_${dateText || ''}</title>
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

            /* ===== TOP ROW (meta + bismillah + checkboxes) ===== */
            .top-head-table {
              table-layout: fixed;
              width: 100%;
              margin-bottom: 4px;
            }

            .top-head-table td {
              vertical-align: top;
              padding: 0;
            }

            .top-right {
              width: 33%;
              text-align: right;
              font-size: 12px;
              line-height: 1.6;
              padding-right: 2px;
              white-space: nowrap;
            }

            .meta-row {
              display: flex;
              justify-content: flex-end;
              align-items: center;
              gap: 6px;
              margin: 2px 0;
            }

            .meta-label { font-weight: 700; }
            .meta-box {
              width: 26px;
              height: 14px;
              border: 1px solid #000;
              display: inline-block;
            }

            .meta-date {
              display: inline-flex;
              align-items: center;
              gap: 14px;
              min-width: 60px;
              justify-content: center;
            }

            .meta-slash {
              display: inline-block;
              width: 18px;
              text-align: center;
              border-bottom: 1px dotted #000;
              padding-bottom: 1px;
            }

            .top-center {
              width: 34%;
              text-align: center;
              padding-top: 2px;
            }

            .bismillah-top {
              font-weight: 700;
              font-size: 14px;
              margin-bottom: 2px;
            }

            /* simple ornamental stroke like scan */
            .bismillah-orn {
              height: 10px;
              position: relative;
              margin: 0 auto;
              width: 90%;
            }
            .bismillah-orn::before {
              content: "";
              position: absolute;
              left: 50%;
              top: 3px;
              transform: translateX(-50%);
              width: 160px;
              height: 2px;
              background: #000;
              border-radius: 2px;
              opacity: 0.6;
            }

            .top-left {
              width: 33%;
              text-align: left;
              font-size: 12px;
              line-height: 1.6;
              padding-left: 2px;
              white-space: nowrap;
            }

            .check-row {
              display: flex;
              align-items: center;
              justify-content: flex-start;
              gap: 6px;
              margin: 2px 0;
            }

            .check-box {
              width: 16px;
              height: 12px;
              border: 1px solid #000;
              display: inline-block;
            }

            .check-label { font-weight: 700; }

            /* ===== HEADER TABLE (stacked texts + logo) ===== */
            .header-table {
              width: 90%;
              border: none;
              table-layout: fixed;
              margin: 0 ;
              margin-right: 20px;
            }

            .header-table td {
              border: none !important;
              font-size: 14px;
              padding: 0 4px !important;
              vertical-align: middle;
            }

            .stack-cell {
              width: 35%;
              line-height: 1.35;
              font-weight: 700;
            }

            .logo-cell {
              width: 30%;
              text-align: center;
            }

            .logo-cell img {
              width: 70px;
              height: 55px;
              object-fit: contain;
            }

            .bismillah-cell {
              text-align: center;
              font-weight: 700;
              margin: 2px 0 6px;
            }

            /* ===== GRAY BAR UNDER BISMILLAH (scan) ===== */
            .bismillah-divider {
              width: 100%;
              height: 16px;
              background: #cfcfcf !important;
              border-top: 1px solid #000;
              border-bottom: 1px solid #000;
              margin: 0 0 10px;
            }

            /* ===== Body text spacing like scan ===== */
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

            ul.bullets {
              margin: 8px 0 0;
              padding-right: 18px;
              list-style-type: disc;
            }

            ul.bullets > li { margin: 10px 0; }

            /* sub bullets use "➢" like scan */
            ul.sub {
              margin: 6px 0 0;
              padding-right: 0;
              list-style: none;
            }

            ul.sub > li {
              margin: 4px 0;
              padding-right: 18px;
              position: relative;
            }

            ul.sub > li::before {
              content: "➢";
              position: absolute;
              right: 0;
              top: 0;
              font-weight: 700;
            }

            /* ===== Events table ===== */
            table.events-table {
              width: 100%;
              border-collapse: collapse;
              margin: 8px 0 10px;
              table-layout: fixed;
            }

            table.events-table th,
            table.events-table td {
              border: 1px solid #000;
              padding: 5px 6px;
              text-align: center;
              font-size: 12px;
              vertical-align: middle;
              background: #fff;
            }

            table.events-table th {
              font-weight: 800;
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
      console.error(e);
      daily.value = null;

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

  // Init date default (optional)
  if (!selectedDate.value) selectedDate.value = todayIso();

  // ===== EXPORT ACTIONS =====
  const DownloadDailyFoulPdf = () => {
    const report = daily.value;
    if (!report) return;

    const dateText = selectedDate.value || todayIso();
    const html = buildDailyDocumentHtml(report, dateText);
    openPrintWindow(html);
  };

  const DownloadDailyFoulWord = () => {
    const report = daily.value;
    if (!report) return;

    const dateText = selectedDate.value || todayIso();
    const html = buildDailyDocumentHtml(report, dateText);
    downloadWordFile(html, `daily_report_${dateText}.doc`);
  };

  return {
    // state
    loading,
    error,
    daily,
    selectedDate,

    // actions
    FetchDailyFoulReport,
    ResetDaily,

    // export
    DownloadDailyFoulPdf,
    DownloadDailyFoulWord,
  };
});
