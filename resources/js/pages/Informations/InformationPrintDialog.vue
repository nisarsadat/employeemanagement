<template>
  <AppBar :title="t('فورم اطلاع شخص')">
    <template #actions>
      <v-btn variant="text" prepend-icon="mdi-arrow-left" class="text-none" @click="goBack">
        {{ t('برگشت') }}
      </v-btn>
    </template>
  </AppBar>
  <div class="info-print-page" dir="rtl">
    <!-- TOP BAR (only for app view, hidden on print) -->
    <div class="top-bar no-print">
      <div>
        <button type="button" class="top-btn" @click="downloadPdf">PDF</button>
        <button type="button" class="top-btn" @click="downloadWord">Word</button>
      </div>
    </div>

    <!-- PAGE BODY (centered horizontally) -->
    <div ref="printArea" class="page-body">
      <!-- HEADER: titles + logo + Bismillah -->
      <table class="header-table">
        <tr>
          <!-- RIGHT side (stacked 4 items) -->
          <td class="stack-cell" style="text-align: right">
            فورم ثبت اطلاع <br />
            فورم ثبت اطلاع <br />
            فورم ثبت اطلاع <br />
            فورم ثبت اطلاع
          </td>

          <!-- CENTER LOGO -->
          <td class="logo-cell">
            <img :src="logoSrc" alt="logo" />
          </td>

          <!-- LEFT side (stacked 5 items) -->
          <td class="stack-cell" style="text-align: left">
            د اطلاع ثبت فورم <br />
            د اطلاع ثبت فورم <br />
            د اطلاع ثبت فورم <br />
            د اطلاع ثبت فورم <br />
            د اطلاع ثبت فورم
          </td>
        </tr>
      </table>

      <div class="bismillah-cell">بسم الله الرحمن الرحیم</div>

      <!-- MAIN TABLE (simple Word-like table) -->
      <table class="info-table">
        <thead>
          <tr>
            <th>شمېره</th>
            <th>کود منبع</th>
            <th>کود کارمند</th>
            <th>د اطلاع نیټه</th>
            <th>د اطلاع ساعت</th>
            <th>د معلومات ډول</th>
            <th>د جنایي معلومات ډول</th>
            <th>ولایت مربوطه</th>
            <th>نوم / موضوع</th>
          </tr>
        </thead>
        <tbody>
          <!-- 1 data row under header -->
          <tr>
            <td>{{ item.id || '' }}</td>
            <td>{{ item.resource_code_number || '' }}</td>
            <td>{{ item.office_employee_code_number || '' }}</td>
            <td>{{ formattedInformationDate }}</td>
            <td>{{ item.information_saved_hour || '' }}</td>
            <td>{{ item.syasi_info_type || '' }}</td>
            <td>{{ item.jenayee_info_type || '' }}</td>
            <td>{{ item.Province_addr || '' }}</td>
            <td>{{ item.name || '' }}</td>
          </tr>

          <!-- تفصیل (TALL ROW) -->
          <tr class="tafseel-row">
            <td colspan="9" class="big-cell">
              <span class="cell-label">تفصیل:</span>
              <div class="cell-content">
                {{ item.tafseel || '' }}
              </div>
            </td>
          </tr>

          <!-- اسناد / شواهد (HALF HEIGHT) -->
          <tr class="docs-row">
            <td colspan="9" class="big-cell">
              <span class="cell-label">د اطلاع اړوند اسناد او شواهد:</span>
              <div class="cell-content">
                <template v-if="Array.isArray(item.documents) && item.documents.length">
                  <ul class="docs-list">
                    <li
                      v-for="doc in item.documents"
                      :key="doc.id || doc.file_name || doc.original_name"
                    >
                      {{ doc.original_name || doc.file_name || doc.name }}
                    </li>
                  </ul>
                </template>
              </div>
            </td>
          </tr>

          <!-- مدیریت نظر (HALF HEIGHT) -->
          <tr class="manager-row">
            <td colspan="9" class="big-cell">
              <span class="cell-label">د مدیریت نظر:</span>
              <div class="cell-content">
                {{ item.manager_comment || '' }}
              </div>
            </td>
          </tr>

          <!-- هدایت (HALF HEIGHT) -->
          <tr class="direction-row">
            <td colspan="9" class="big-cell">
              <span class="cell-label">هدایت:</span>
              <div class="cell-content">
                {{ item.lead_drirection || '' }}
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- FOOTER (signatures) -->
      <div class="footer-row">
        <span>امضاء / مهر مسئول</span>
        <span>تاریخ / امضاء ثبت کوونکی</span>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue';
  import { useRoute } from 'vue-router';
  import { useDatabaseRepository } from '@/stores/DatabaseRepository';
  import AppBar from '../../components/AppBar.vue';
  import { useI18n } from 'vue-i18n';
  import router from '../../router/router';
  import { useCalendar } from '../../../composables/useCalendar'; // 🆕 calendar composable

  const route = useRoute();
  const repo = useDatabaseRepository();

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (newLocale) => {
    isRtl.value = newLocale === 'fa';
  });

  /* go back */
  function goBack() {
    router.push('/app/informations');
  }

  const printArea = ref(null);
  const logoSrc = `${window.location.origin}/view.png`; // file must be in /public

  const id = computed(() => route.params.id);

  const item = computed(() => {
    const raw = repo.Information;
    if (Array.isArray(raw)) return raw[0] || {};
    return raw || {};
  });

  /* 🗓 calendar formatting (same source as AppBar) */
  const { formatDisplay, toIso } = useCalendar();

  const uiLocale = computed(() => locale.value || 'fa');

  /**
   * Use the global calendar mode (gregorian/jalali/hijri)
   * to render the saved information date.
   * Assumes backend date is stored as ISO (YYYY-MM-DD) or parseable by dayjs.
   */
  const formattedInformationDate = computed(() => {
    const raw = item.value?.information_saved_date;
    if (!raw) return '';
    // normalize to ISO and then format for UI using current calendar
    const iso = toIso(raw); // safe wrapper -> YYYY-MM-DD
    return formatDisplay(iso, uiLocale.value);
  });

  onMounted(async () => {
    if (id.value && typeof repo.FetchInformationData === 'function') {
      await repo.FetchInformationData(id.value);
    }
  });

  function buildInnerHtml() {
    return printArea.value ? printArea.value.innerHTML : '';
  }

  /**
   * Build full HTML document for both PDF/Word.
   * Uses only printArea → no AppBar, no top buttons.
   */
  function buildDocumentHtml() {
    const bodyHtml = buildInnerHtml();
    if (!bodyHtml) return '';

    return `
  <html dir="rtl">
    <head>
      <meta charset="utf-8" />
      <title>information_${item.value.id || ''}</title>
      <style>
        @page { size: A4 portrait; margin: 10mm; }

        html, body {
          margin: 0;
          padding: 0;
          background: #ffffff;
          font-family: "Amiri", "Scheherazade New", Arial, sans-serif;
          font-size: 12px;
        }

        .info-print-page {
          background: #ffffff;
          padding: 0;
          margin: 0;
        }

        .page-body {
          width: 100%;
          margin: 0 auto;
          padding: 10mm;
          box-sizing: border-box;
        }

        table {
          border-collapse: collapse;
          width: 100%;
        }

        th, td {
          border: 1px solid #000;
          padding: 2px 4px;
          font-size: 12px;
          text-align: center;
        }

        /* header table (no borders) */
        .header-table {
          width: 100%;
          border: none;
          margin-bottom: 4px;
        }

        .header-table td {
          border: none;
          font-size: 14px;
          padding: 0 4px;
          vertical-align: middle;
        }

        .stack-cell {
          font-weight: bold;
          line-height: 1.4;
        }

        .logo-cell {
          text-align: center;
        }

        .logo-cell img {
          width: 70px;
          height: 55px;
          object-fit: contain;
        }

        .bismillah-cell {
          text-align: center;
          font-weight: bold;
          margin: 4px 0 8px;
        }

        .info-table {
          table-layout: fixed;
        }

        .info-table th {
          background: #f5f5f5;
          font-weight: bold;
        }

        .big-cell {
          position: relative;
          vertical-align: top;
          text-align: right;
        }

        /* taller tafseel row */
        .tafseel-row .big-cell {
          height: 400px;
        }

        /* half height for others */
        .docs-row .big-cell,
        .manager-row .big-cell,
        .direction-row .big-cell {
          height: 100px;
        }

        .cell-label {
          position: absolute;
          right: 4px;
          top: 2px;
          font-weight: normal;
        }

        .cell-content {
          margin-top: 18px;
          white-space: pre-wrap;
          text-align: right;
        }

        .docs-list {
          margin: 0;
          padding-left: 16px;
        }

        .footer-row {
          margin-top: 16px;
          display: flex;
          justify-content: space-between;
          font-size: 12px;
        }
      </style>
    </head>
    <body>
      <div class="info-print-page">
        <div class="page-body">
          ${bodyHtml}
        </div>
      </div>
    </body>
  </html>
  `;
  }

  function downloadPdf() {
    const html = buildDocumentHtml();
    if (!html) return;

    const w = window.open('', '_blank', 'width=1024,height=768');
    if (!w) return;

    w.document.open();
    w.document.write(html);
    w.document.close();

    // ✅ Wait until all images (including logo) are loaded
    w.addEventListener('load', () => {
      w.focus();
      w.print();
      // optional: close after print
      // w.close();
    });
  }

  function downloadWord() {
    const html = buildDocumentHtml();
    if (!html) return;

    const blob = new Blob(['\ufeff', html], {
      type: 'application/msword',
    });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `information_${item.value.id || ''}.doc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  }
</script>

<style scoped>
  /* ===== PAGE BASE ===== */
  .info-print-page {
    background: #ffffff !important;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 12px;
  }

  /* top buttons (not printed) */
  .top-bar {
    display: flex;
    justify-content: flex-start;
    gap: 4px;
    margin-bottom: 4px;
  }

  .top-btn {
    font-size: 12px;
    padding: 2px 6px;
    background: #ffffff;
    border: 1px solid #000;
    cursor: pointer;
  }

  .page-body {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    padding: 20px; /* Visible padding on screen */
    box-sizing: border-box;
  }

  /* ===== HARD RESET FOR TABLES SO app.css DOESN'T LEAK IN ===== */
  .info-print-page table,
  .info-print-page th,
  .info-print-page td,
  .info-print-page tr {
    margin: 0 !important;
    padding: 0 !important;
    border-collapse: collapse !important;
    box-shadow: none !important;
    background: #ffffff !important;
  }

  /* kill zebra & hover from global css */
  .info-print-page table tr:nth-child(even),
  .info-print-page table tr:nth-child(odd) {
    background: #ffffff !important;
  }

  .info-print-page table tr:hover {
    background: #ffffff !important;
  }

  /* ===== HEADER ===== */
  .header-table {
    width: 100%;
    border: none;
  }

  .header-table td {
    border: none !important;
    font-size: 14px;
    padding: 0 4px !important;
  }

  .title-cell {
    text-align: right;
    font-weight: bold;
  }

  .logo-cell {
    text-align: center;
  }

  .logo-cell img {
    width: 80px;
    height: 60px;
    object-fit: cover;
  }

  .bismillah-cell {
    text-align: center;
    font-weight: bold;
    margin: 4px 0;
  }

  /* ===== MAIN TABLE ===== */
  .info-table {
    table-layout: fixed; /* equal widths */
    width: 100%;
  }

  .info-table th,
  .info-table td {
    border: 1px solid #000 !important;
    padding: 2px 4px !important;
    font-size: 12px;
    text-align: center;
    height: 24px; /* same height for first data row */
  }

  /* big rows */
  .big-cell {
    position: relative;
    vertical-align: top;
    text-align: right;
  }

  /* taller tafseel row */
  .tafseel-row .big-cell {
    height: 400px;
  }

  /* half-height rows */
  .docs-row .big-cell,
  .manager-row .big-cell,
  .direction-row .big-cell {
    height: 100px;
  }

  .cell-label {
    position: absolute;
    right: 4px;
    top: 2px;
    font-weight: normal;
  }

  .cell-content {
    margin-top: 18px;
    white-space: pre-wrap;
    text-align: right;
  }

  .docs-list {
    margin: 0;
    padding-left: 16px;
  }

  /* ===== FOOTER ===== */
  .footer-row {
    margin-top: 16px;
    display: flex;
    justify-content: space-between;
    font-size: 12px;
  }

  .header-table {
    width: 100%;
    border: none;
    table-layout: fixed;
  }

  .header-table td {
    border: none !important;
    font-size: 14px;
    padding: 0 4px !important;
    vertical-align: middle;
  }

  .stack-cell {
    text-align: right;
    width: 35%;
    line-height: 1.4;
    font-weight: bold;
  }

  .logo-cell {
    text-align: center;
    width: 30%;
  }

  .logo-cell img {
    width: 70px;
    height: 55px;
    object-fit: contain;
  }

  /* ===== PRINT ===== */
  @media print {
    .no-print {
      display: none !important;
    }
    .page-body {
      width: 100%;
      max-width: none;
      margin: 0;
    }
  }
</style>
