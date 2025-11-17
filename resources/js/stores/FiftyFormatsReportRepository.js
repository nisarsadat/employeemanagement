// resources/js/stores/FiftyFormatsReportRepository.js
import { defineStore } from 'pinia'
import axios from 'axios'
import { useCalendar } from '../../composables/useCalendar'

axios.defaults.headers.common['Accept'] = 'application/json'

export const useFiftyFormatsReportRepository = defineStore(
  'fiftyFormatsReport',
  {
    state: () => ({
      // filters (store as ISO YYYY-MM-DD)
      Search: '',
      Zone: '',
      From: '',
      To: '',
      granularity: 'monthly', // daily | weekly | monthly | yearly
      ignoreDates: 0, // 1 = all time

      // grouping
      Group: 'province_zone', // 'province_zone' | 'resource'

      // ALL 56 format keys
      Formats: [
        'fasads',
        'saeen_fasads',
        'heists',
        'captives',
        'mulkanos',
        'entry_exits',
        'sarafyanos',
        'clubs',
        'mahajerats',
        'soldiers',

        'mawads',
        'lost_mujremeens',
        'old_soldiers_homes',
        'robbers',
        'non_responsable_people',
        'mashkok_homes',
        'damage_places',
        'damage_rasan_places',
        'mashkok_shops',
        'luchakanos',

        'schools',
        'hado_bus_stops',
        'transport_companies',
        'car_sales_companies',
        'parkings',
        'banks',
        'apartments',
        'rahnoma_mamlat',
        'hosppitals',
        'work_shops',

        'badmashanos',
        'emarat_offices',
        'hostels',
        'hotels',
        'madrasas',
        'mashkok_people',
        'mujahedinos',
        'old_jahadis',
        'sarayanos',
        'tujarans',

        'checkpoints',
        'masjids',
        'highways',
        'companies',
        'done_lost_places',
        'econemy_projects',
        'criminal_capture_places',
        'pumps',
        'shoras',
        'government_places',
        'super_markets',
        'parks',
        'complexes',
        'deplomatic_places',
        'universities',
        'wakilanos',
      ],

      // data from backend
      rows: [],          // = data.data
      chart: [],         // = data.meta.chart
      summary: {},       // = data.meta.summary

      // UI
      loading: false,
      error: null,
    }),

    actions: {
      setGranularity(g) {
        this.granularity = g || 'monthly'
      },

      setRange({ from, to }) {
        this.From = from || ''
        this.To = to || ''
      },

      setAllTime(on = true) {
        this.ignoreDates = on ? 1 : 0
        if (on) {
          this.From = ''
          this.To = ''
        }
      },

      setGroup(group) {
        this.Group = group || 'province_zone'
      },

      setFormats(list) {
        if (Array.isArray(list) && list.length) {
          this.Formats = list
        }
      },

      toggleFormat(fmt) {
        if (!fmt) return
        if (this.Formats.includes(fmt)) {
          this.Formats = this.Formats.filter((f) => f !== fmt)
        } else {
          this.Formats = [...this.Formats, fmt]
        }
      },

      _params(extra = {}) {
        const p = {
          search: this.Search || undefined,
          zone: this.Zone || undefined,
          period: this.granularity,
          granularity: this.granularity,
          group: this.Group,
          formats: this.Formats.join(','), // comma-separated
          ignoreDates: this.ignoreDates,
          ...extra,
        }

        if (!this.ignoreDates) {
          if (this.From) p.from = this.From
          if (this.To) p.to = this.To
        }

        return p
      },

      // -------- FETCH REPORT (matches JSON you sent) ----------
      async fetchReport() {
        this.loading = true
        this.error = null

        try {
          const { formatForBackend } = useCalendar()
          const params = this._params()

          // calendar-aware date formatting
          if (params.from) params.from = formatForBackend(params.from)
          if (params.to) params.to = formatForBackend(params.to)

          const { data } = await axios.get('/reports/fifty-formats', {
            params,
          })

          // backend structure like the JSON you pasted
          this.rows = data?.data || []
          this.chart = data?.meta?.chart || []
          this.summary = data?.meta?.summary || {}

          // if you ever want to sync meta back:
          // this.Group = data?.meta?.group || this.Group
          // this.granularity = data?.meta?.period || this.granularity
        } catch (e) {
          console.error(e)
          this.rows = []
          this.chart = []
          this.summary = {}
          this.error =
            e?.response?.data?.message ||
            e.message ||
            'ستونزه رامنځته شوه – راپور ترلاسه نه شو'
        } finally {
          this.loading = false
        }
      },

      // ---------- Excel (CSV, full matrix) ----------
      exportExcelCsv() {
        this.error = null

        if (!this.rows.length) {
          this.error = 'هیڅ دیتا نشته چې اکسپل ته صادر شي.'
          return
        }

        const selectedFormats = this.Formats.slice()

        let headers = []
        if (this.Group === 'resource') {
          headers = ['Resource ID', 'Resource Name']
        } else {
          headers = ['Province', 'Zone']
        }

        headers = headers.concat(selectedFormats)
        headers.push('Total')

        const csvRows = []
        csvRows.push(headers.join(','))

        for (const row of this.rows) {
          const line = []

          if (this.Group === 'resource') {
            line.push(row.resource_id ?? '')
            const rName = row.resource_name ?? ''
            line.push('"' + String(rName).replace(/"/g, '""') + '"')
          } else {
            const p = row.province ?? ''
            const z = row.zone ?? ''
            line.push('"' + String(p).replace(/"/g, '""') + '"')
            line.push('"' + String(z).replace(/"/g, '""') + '"')
          }

          selectedFormats.forEach((fmt) => {
            line.push(row[fmt] ?? 0)
          })

          line.push(row.total ?? 0)

          csvRows.push(line.join(','))
        }

        const csv = csvRows.join('\r\n')
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
        const url = URL.createObjectURL(blob)

        const link = document.createElement('a')
        link.href = url
        link.download = 'fifty_formats_report.csv'
        document.body.appendChild(link)
        link.click()
        link.remove()
        URL.revokeObjectURL(url)
      },

      // ---------- helper: Pashto label for formats ----------
      _formatLabelPs(key) {
        const map = {
          fasads: 'فاسد اشخاص',
          saeen_fasads: 'سعین فاسد',
          heists: 'هیست / غلا',
          captives: 'اسیران',
          mulkanos: 'ملکانو',
          entry_exits: 'د ورود/خروج ریکارډونه',
          sarafyanos: 'صرافانو',
          clubs: 'کلبونه',
          mahajerats: 'مهاجرتونه',
          soldiers: 'سربازان',

          mawads: 'مواد',
          lost_mujremeens: 'تښتېدلي مجرمین',
          old_soldiers_homes: 'د پخوانیو عساکرو کورونه',
          robbers: 'غله',
          non_responsable_people: 'غیر مسؤل اشخاص',
          mashkok_homes: 'مشکوک کورونه',
          damage_places: 'تخریب شوي ځایونه',
          damage_rasan_places: 'تخریب رَسان ځایونه',
          mashkok_shops: 'مشکوکې دوکانونه',
          luchakanos: 'لچکانو',

          schools: 'مکتبونه',
          hado_bus_stops: 'هادو بس تمځایونه',
          transport_companies: 'ترانسپورتي شرکتونه',
          car_sales_companies: 'د موټرو خرڅلاو شرکتونه',
          parkings: 'پارکینګونه',
          banks: 'بانکونه',
          apartments: 'اپارتمانونه',
          rahnoma_mamlat: 'راهنما معاملات',
          hosppitals: 'شفاخانه ها',
          work_shops: 'ورکشاپونه',

          badmashanos: 'بدماشانو',
          emarat_offices: 'د امارت ادارې',
          hostels: 'هاستلونه',
          hotels: 'هوتلونه',
          madrasas: 'مدرسې',
          mashkok_people: 'مشکوک اشخاص',
          mujahedinos: 'مجاهدین',
          old_jahadis: 'پخواني جهاديان',
          sarayanos: 'سرایانو',
          tujarans: 'تجاران',

          checkpoints: 'چک‌پواینټونه',
          masjids: 'مساجد',
          highways: 'عمومي شاهراهونه',
          companies: 'شرکتونه',
          done_lost_places: 'د ورک شوو کسانو ځایونه',
          econemy_projects: 'اقتصادي پروژې',
          criminal_capture_places: 'د مجرمانو د نیولو ځایونه',
          pumps: 'پمپونه',
          shoras: 'شوراګانې',
          government_places: 'دولتې ادارې',
          super_markets: 'سوپرمارکېټونه',
          parks: 'پارکونه',
          complexes: 'کمپلکسونه',
          deplomatic_places: 'دیپلوماتیک ځایونه',
          universities: 'پوهنتونونه',
          wakilanos: 'وکیلانو',
        }
        return map[key] || key
      },

      // ---------- Beautiful Pashto “PDF” via browser print ----------
      exportPdf() {
        this.error = null

        if (!this.rows.length) {
          this.error = 'هیڅ دیتا نشته چې PDF ته صادر شي.'
          return
        }

        const selectedFormats = this.Formats.slice()
        const rowsHtml = []
        let idx = 1
        const isResource = this.Group === 'resource'

        for (const row of this.rows) {
          const groupLabel = isResource
            ? row.resource_name ?? 'نامعلوم سرچینه'
            : `${row.province ?? 'نامعلوم ولایت'} / ${row.zone ?? 'نامعلوم زون'}`

          selectedFormats.forEach((fmt) => {
            const value = row[fmt] ?? 0
            if (!value) return

            const fmtLabel = this._formatLabelPs(fmt)
            let sentence

            if (isResource) {
              sentence =
                `سرچینه «${groupLabel}» د «${fmtLabel}» فارمت په چوکاټ کې ` +
                `${value} ثبت شوي ریکارډ(ونه) لري. دا ریکارډونه د نورو معلوماتو، څېړنو او د ساحې د ` +
                `امنیتي وضعیت د ښه درک لپاره په سیستم کې ساتل شوي دي.`
            } else {
              const [prov, zone] = groupLabel.split('/')
              sentence =
                `په ولایت «${(prov || '').trim()}» او زون «${(zone || '').trim()}» کې ` +
                `د «${fmtLabel}» فارمت لپاره ${value} ریکارډ(ونه) ثبت شوي دي. دغه ریکارډونه د دې سیمې د ` +
                `عمومي وضعیت او راټول شویو استخباراتي معلوماتو د تحلیل لپاره مهم رول لري.`
            }

            rowsHtml.push(`
              <tr>
                <td style="padding:4px 8px; border-bottom:1px solid #eee; text-align:center;">${idx}</td>
                <td style="padding:4px 8px; border-bottom:1px solid #eee; text-align:right;">${sentence}</td>
              </tr>
            `)
            idx += 1
          })
        }

        if (!rowsHtml.length) {
          this.error = 'د ټاکل شويو فارمتونو لپاره کوم ریکارډ نشته.'
          return
        }

        const periodLabel = {
          daily: 'ورځنی',
          weekly: 'اونیز',
          monthly: 'میاشتنی',
          yearly: 'کلنی',
        }[this.granularity] || this.granularity

        const metaParts = []
        metaParts.push(
          isResource
            ? 'ګروپ: د سرچینې پر بنسټ'
            : 'ګروپ: د ولایت / زون پر بنسټ',
        )
        metaParts.push(`موده: ${periodLabel}`)

        if (!this.ignoreDates) {
          if (this.From) metaParts.push(`له نېټې: ${this.From}`)
          if (this.To) metaParts.push(`تر نېټې: ${this.To}`)
        } else {
          metaParts.push('ټوله موده (All time)')
        }

        const html = `
<!DOCTYPE html>
<html lang="ps" dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>د ۵۶ فارمتونو راپور</title>
  <style>
    body {
      font-family: "Noto Naskh Arabic","Noto Sans Arabic","Segoe UI",sans-serif;
      margin: 24px;
      direction: rtl;
      text-align: right;
    }
    h1 {
      font-size: 18px;
      margin: 0 0 4px;
    }
    .meta {
      font-size: 11px;
      color: #555;
      margin-bottom: 12px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 11px;
    }
    thead {
      background: #2054a7;
      color: #fff;
    }
    th {
      padding: 6px 8px;
      text-align: center;
      font-weight: 600;
    }
    tbody td {
      line-height: 1.6;
    }
    .note {
      margin-top: 12px;
      font-size: 10px;
      color: #555;
    }
  </style>
</head>
<body>
  <h1>د ۵۶ فارمتونو جامع راپور (پښتو تشریح)</h1>
  <div class="meta">${metaParts.join(' | ')}</div>

  <table>
    <thead>
      <tr>
        <th style="width:40px;">شمېره</th>
        <th>تشریح</th>
      </tr>
    </thead>
    <tbody>
      ${rowsHtml.join('')}
    </tbody>
  </table>

  <div class="note">
    یادونه: که PDF ته اړتیا لرئ، د چاپ له کړکۍ څخه «Save as PDF» انتخاب کړئ.
  </div>

  <script>
    window.onload = function () {
      window.focus();
      window.print();
    };
  </script>
</body>
</html>
        `

        const win = window.open('', '_blank')
        if (!win) {
          this.error =
            'براوزر نوي کړکۍ ته اجازه نه ورکوي (پاپ‌اپ بند شوی). د براوزر تنظیمات وګوره.'
          return
        }
        win.document.open()
        win.document.write(html)
        win.document.close()
      },
    },
  },
)
