// resources/js/stores/OfficeEmployeesReportRepository.js
import { defineStore } from 'pinia';
import axios from 'axios';
import { useCalendar } from '../../composables/useCalendar';

axios.defaults.headers.common['Accept'] = 'application/json';

export const useOfficeEmployeesReportRepository = defineStore('officeEmployeesReport', {
  state: () => ({
    // Filters
    Search: '',
    Status: 'all', // 'all' | 'active' | 'inactive' | 'فعال' | 'غیر فعال'

    ProvinceAddr: '',
    MainProvince: '',
    MainDistrict: '',
    NowProvince: '',
    NowDistrict: '',
    Zone: '',
    Hawza: '',
    MadaniHalat: '',

    // Time
    Period: 'monthly', // daily | weekly | monthly | yearly | custom | all
    From: '',
    To: '',
    ignoreDates: 0, // 1 = all time (period='all')

    // Data from backend
    rows: [], // = data (array of provinces)
    meta: {
      period: 'all',
      date_from: null,
      date_to: null,
      totals: {},
      filters: {},
    },

    // UI
    loading: false,
    error: null,
  }),

  getters: {
    summary(state) {
      return state.meta?.totals || {};
    },
  },

  actions: {
    setPeriod(p) {
      this.Period = p || 'monthly';
      if (p === 'all') {
        this.setAllTime(true);
      }
    },

    setRange({ from, to }) {
      this.From = from || '';
      this.To = to || '';
      if (from || to) {
        this.ignoreDates = 0;
        if (this.Period === 'all') this.Period = 'custom';
      }
    },

    setAllTime(on = true) {
      this.ignoreDates = on ? 1 : 0;
      if (on) {
        this.From = '';
        this.To = '';
        this.Period = 'all';
      }
    },

    _params(extra = {}) {
      const p = {
        search: this.Search || undefined,
        status: this.Status || undefined,

        province_addr: this.ProvinceAddr || undefined,
        main_province: this.MainProvince || undefined,
        main_district: this.MainDistrict || undefined,
        now_province: this.NowProvince || undefined,
        now_district: this.NowDistrict || undefined,
        zone: this.Zone || undefined,
        hawza: this.Hawza || undefined,
        madani_halat: this.MadaniHalat || undefined,

        period: this.Period || 'all',
        ...extra,
      };

      if (!this.ignoreDates) {
        if (this.From) p.from_date = this.From;
        if (this.To) p.to_date = this.To;
      }

      return p;
    },

    // -------- FETCH REPORT ----------
    async fetchReport() {
      this.loading = true;
      this.error = null;

      try {
        const { formatForBackend } = useCalendar();
        const params = this._params();

        if (params.from_date) {
          params.from_date = formatForBackend(params.from_date);
        }
        if (params.to_date) {
          params.to_date = formatForBackend(params.to_date);
        }

        const { data } = await axios.get('/office-employees-report/activity-report', { params });

        this.rows = data?.data || [];
        this.meta = data?.meta || {
          period: this.Period,
          date_from: params.from_date || null,
          date_to: params.to_date || null,
          totals: {},
          filters: {},
        };
      } catch (e) {
        console.error(e);
        this.rows = [];
        this.meta = {
          period: this.Period,
          date_from: null,
          date_to: null,
          totals: {},
          filters: {},
        };
        this.error =
          e?.response?.data?.message ||
          e.message ||
          'ستونزه رامنځته شوه – د کارمندان راپور ترلاسه نه شو.';
      } finally {
        this.loading = false;
      }
    },

    // ---------- CSV export ----------
    exportCsv() {
      this.error = null;

      if (!this.rows.length) {
        this.error = 'هیڅ دیتا نشته چې CSV ته صادر شي.';
        return;
      }

      const headers = [
        'Province',
        'Employee ID',
        'Name',
        'Father',
        'Code',
        'Phone',
        'Zone',
        'Hawza',
        'Madani',
        'Activity',
        'Events',
        'Resources',
        'Informations',
        'LastActivity',
        'DaysSinceLast',
      ];

      const csvRows = [];
      csvRows.push(headers.join(','));

      for (const group of this.rows) {
        const province = group.province_addr ?? '';
        for (const emp of group.employees || []) {
          const line = [];
          line.push('"' + String(province).replace(/"/g, '""') + '"');
          line.push(emp.id ?? '');
          line.push('"' + String(emp.name || '').replace(/"/g, '""') + '"');
          line.push('"' + String(emp.father_name || '').replace(/"/g, '""') + '"');
          line.push('"' + String(emp.code_number || '').replace(/"/g, '""') + '"');
          line.push('"' + String(emp.phone_number || '').replace(/"/g, '""') + '"');
          line.push('"' + String(emp.zone || '').replace(/"/g, '""') + '"');
          line.push('"' + String(emp.hawza || '').replace(/"/g, '""') + '"');
          line.push('"' + String(emp.madani_halat || '').replace(/"/g, '""') + '"');
          line.push('"' + String(emp.activity || '').replace(/"/g, '""') + '"');

          line.push(emp.events_count ?? 0);
          line.push(emp.resources_count ?? 0);
          line.push(emp.informations_count ?? 0);
          line.push(emp.last_activity_date ?? '');
          line.push(emp.days_since_last_activity ?? '');

          csvRows.push(line.join(','));
        }
      }

      const csv = csvRows.join('\r\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);

      const link = document.createElement('a');
      link.href = url;
      link.download = 'office_employees_activity_report.csv';
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
    },

    // ---------- Pashto PDF report (نثري راپور، نه جدول) ----------
    exportPdf() {
      this.error = null;

      if (!this.rows.length) {
        this.error = 'هیڅ دیتا نشته چې PDF ته صادر شي.';
        return;
      }

      const periodLabelMap = {
        daily: 'ورځنی راپور',
        weekly: 'اونیز راپور',
        monthly: 'میاشتنی راپور',
        yearly: 'کلنی راپور',
        custom: 'مخصوص موده',
        all: 'ټوله موده',
      };

      const meta = this.meta || {};
      const periodKey = meta.period || this.Period || 'all';
      const periodLabel = periodLabelMap[periodKey] || periodKey;

      const filtros = meta.filters || {};
      const summary = meta.totals || {};

      const metaParts = [];
      metaParts.push(`موده: ${periodLabel}`);

      if (meta.date_from) metaParts.push(`له نېټې: ${meta.date_from}`);
      if (meta.date_to) metaParts.push(`تر نېټې: ${meta.date_to}`);

      if (filtros.status && filtros.status !== 'all') {
        metaParts.push(`فلټر د حالت له مخې: ${filtros.status}`);
      }
      if (filtros.province_addr) {
        metaParts.push(`اساس ولایت: ${filtros.province_addr}`);
      }
      if (filtros.now_province) {
        metaParts.push(`اوسنی ولایت: ${filtros.now_province}`);
      }
      if (filtros.zone) {
        metaParts.push(`زون: ${filtros.zone}`);
      }
      if (filtros.hawza) {
        metaParts.push(`حوزه: ${filtros.hawza}`);
      }

      const metaText = metaParts.join(' | ');

      // -------- Build narrative body ----------
      const sectionsHtml = [];

      // Global summary paragraph
      const totalEmployees = summary.employees ?? 0;
      const totalActive = summary.active ?? 0;
      const totalInactive = summary.inactive ?? 0;
      const totalEvents = summary.events ?? 0;
      const totalResources = summary.resources ?? 0;
      const totalInfos = summary.informations ?? 0;

      const globalParagraph = `
          <p>
            په ټولیز ډول په سیستم کې <strong>${totalEmployees}</strong> رسمي کارمندان ثبت دي
            چې له دې شمېرې څخه <strong>${totalActive}</strong> فعال او
            <strong>${totalInactive}</strong> غیر فعال په نښه شوي دي.
            د ورکړل شوې مودې په جریان کې د دغو کارمندان له لوري یا د هغوی په اړه
            ټول ټال <strong>${totalEvents}</strong> پیښې،
            <strong>${totalResources}</strong> د منابعو ریکارډونه او
            <strong>${totalInfos}</strong> معلوماتي ریکارډونه ثبت شوي دي.
            دا راپور هڅه کوي چې د ولایتونو او زونونو په کچه د کارمندان د فعالیت انځور په روښانه ډول وړاندې کړي.
          </p>
        `;
      sectionsHtml.push(globalParagraph);

      let empIndex = 1;

      for (const group of this.rows) {
        const prov = group.province_addr || 'نامعلوم ولایت';
        const gTotals = group.totals || {};
        const gEmployees = group.employees || [];

        const gEmpCount = gEmployees.length;
        const gActive = gEmployees.filter((e) => e.activity === 'فعال').length;
        const gInactive = gEmployees.filter((e) => e.activity === 'غیر فعال').length;

        const gEvents = gTotals.events ?? 0;
        const gResources = gTotals.resources ?? 0;
        const gInfos = gTotals.informations ?? 0;

        // Province heading
        sectionsHtml.push(`
            <h2 style="font-size:15px; margin:16px 0 6px;">
              ولایت: ${prov}
            </h2>
          `);

        // Province-level summary
        sectionsHtml.push(`
            <p>
              په ولایت <strong>${prov}</strong> کې ټول ټال
              <strong>${gEmpCount}</strong> کارمندان ثبت شوي دي.
              له دې جملې څخه <strong>${gActive}</strong> فعال او
              <strong>${gInactive}</strong> غیر فعال بلل شوي دي.
              د راپور په موده کې د دې ولایت د کارمندان له لوري
              <strong>${gEvents}</strong> پیښې،
              <strong>${gResources}</strong> د منابعو ریکارډونه او
              <strong>${gInfos}</strong> معلوماتي ریکارډونه ثبت شوي دي.
            </p>
          `);

        if (!gEmployees.length) {
          sectionsHtml.push(`
              <p style="font-size:11px; color:#777;">
                په دې ولایت کې د ټاکل شويو فلټرونو له مخې کوم مفصل ریکارډ نشته.
              </p>
            `);
          continue;
        }

        // Employees in this province
        for (const emp of gEmployees) {
          const name = emp.name || 'نامعلوم نوم';
          const father = emp.father_name || '';
          const code = emp.code_number || 'بدون کوډ';
          const phone = emp.phone_number || 'شمېره ثبت نه ده';
          const zone = emp.zone || 'نامعلوم زون';
          const hawza = emp.hawza || 'نامعلوم حوزه';
          const madani = emp.madani_halat || '';
          const activity = emp.activity || '';
          const joinDate = emp.join_date || '';
          const leftDate = emp.left_date || '';

          const evCount = emp.events_count ?? 0;
          const resCount = emp.resources_count ?? 0;
          const infoCount = emp.informations_count ?? 0;

          const lastAct = emp.last_activity_date || 'نامعلوم';
          const daysLast =
            emp.days_since_last_activity != null ? emp.days_since_last_activity : null;

          const mainRes = emp.main_resource || null;
          const resName = mainRes?.name || null;
          const resCode = mainRes?.code_number || null;

          let activitySentence = '';
          if (activity === 'فعال') {
            activitySentence = 'اوس مهال فعاله دنده ترسره کوي.';
          } else if (activity === 'غیر فعال') {
            activitySentence = 'دا مهال غیر فعال په نښه شوی دی.';
          } else {
            activitySentence = 'د فعالیت حالت یې په څرګند ډول نه دی ټاکل شوی.';
          }

          let daysSentence = '';
          if (daysLast === 0) {
            daysSentence = 'وروستی فعالیت یې د همدې ورځې په جریان کې ثبت شوی دی.';
          } else if (daysLast != null) {
            daysSentence = `وروستی ثبت شوی فعالیت یې نږدې ${daysLast} ورځې وړاندې ترسره شوی دی.`;
          } else {
            daysSentence = 'د وروستي فعالیت کره نېټه د موجودو ریکارډونو له مخې معلومه نه ده.';
          }

          let resSentence = '';
          if (resName && resCode) {
            resSentence = `په ډېری ریکارډونو کې له سرچینې «${resName}» (کوډ: ${resCode}) سره تړاو لري.`;
          } else if (resName) {
            resSentence = `په ډېری ریکارډونو کې د «${resName}» له سرچینې سره تړاو لري.`;
          }

          const madaniText = madani ? `، مدني حالت: ${madani}` : '';
          const joinText = joinDate ? `، د پیوستون نېټه: ${joinDate}` : '';
          const leftText = leftDate ? `، د پرې کېدو نېټه: ${leftDate}` : '';

          sectionsHtml.push(`
              <p>
                <strong>${empIndex}.</strong>
                کارمند «<strong>${name}</strong>» زوی د «${father}» (کد: ${code}، موبایل: ${phone})
                په زون «${zone}» او حوزه «${hawza}» کې ثبت شوی دی${madaniText}${joinText}${leftText}.
                ${activitySentence}
                د ورکړل شوې مودې په ترڅ کې د نوموړي په اړه
                <strong>${evCount}</strong> پیښې،
                <strong>${resCount}</strong> د منابعو ریکارډونه او
                <strong>${infoCount}</strong> معلوماتي ریکارډونه ثبت دي.
                وروستی ثبت شوی فعالیت یې په نېټه <strong>${lastAct}</strong> کې لیدل کېږي.
                ${daysSentence}
                ${resSentence}
              </p>
            `);

          empIndex += 1;
        }
      }

      const html = `
<!DOCTYPE html>
<html lang="ps" dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>د کارمندان د فعالیت تفصیلي راپور</title>
  <style>
    body {
      font-family: "Noto Naskh Arabic","Noto Sans Arabic","Segoe UI",sans-serif;
      margin: 24px;
      direction: rtl;
      text-align: right;
      font-size: 12px;
      line-height: 1.8;
    }
    h1 {
      font-size: 20px;
      margin: 0 0 6px;
    }
    h2 {
      font-size: 15px;
      margin: 18px 0 6px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 4px;
    }
    .meta {
      font-size: 11px;
      color: #555;
      margin-bottom: 12px;
    }
    p {
      margin: 6px 0;
    }
    .note {
      margin-top: 14px;
      font-size: 10px;
      color: #666;
    }
  </style>
</head>
<body>
  <h1>د کارمندان د فعالیت تفصیلي راپور</h1>
  <div class="meta">${metaText}</div>

  ${sectionsHtml.join('')}

  <div class="note">
    یادونه: د دې راپور محتوا د سیستم د موجودو ریکارډونو پر بنسټ جوړه شوې ده.
    که ته غواړې PDF فایل ترلاسه کړې، د چاپ له کړکۍ څخه «Save as PDF» انتخاب کړه.
  </div>

  <script>
    window.onload = function () {
      window.focus();
      window.print();
    };
  </script>
</body>
</html>
        `;

      const win = window.open('', '_blank');
      if (!win) {
        this.error = 'براوزر نوي کړکۍ ته اجازه نه ورکوي (پاپ‌اپ بند شوی). د براوزر تنظیمات وګوره.';
        return;
      }
      win.document.open();
      win.document.write(html);
      win.document.close();
    },

    // -------- FETCH SINGLE EMPLOYEE RELATION (events/resources/informations) ------
    // ❗ This does NOT apply any date filters – it always returns ALL related records.
    async fetchEmployeeRelation(id, relation, extraParams = {}) {
      if (!id || !relation) {
        throw new Error('Missing id or relation for fetchEmployeeRelation');
      }

      try {
        const { data } = await axios.get(
          `/office-employees-report/${id}/${relation}`,
          { params: extraParams },
        );
        return data;
      } catch (e) {
        console.error(e);
        // we don't touch this.loading here, just bubble up the error
        throw e;
      }
    },
  },
});
