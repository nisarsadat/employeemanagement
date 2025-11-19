// resources/js/stores/InformationReportRepository.js
import { defineStore } from 'pinia'
import axios from 'axios'
import { useCalendar } from '../../composables/useCalendar'

axios.defaults.headers.common.Accept = 'application/json'

export const useInformationReportRepository = defineStore('informationReport', {
  state: () => ({
    // Filters
    Search: '',
    ProvinceAddr: '',
    MainProvince: '',
    NowProvince: '',
    SyasiType: '',
    JenayeeType: '',
    InformationDegree: '',
    OfficeEmployeeId: '',
    ResourceId: '',

    // Time
    Period: 'monthly', // daily|weekly|monthly|yearly|custom|all
    From: '',
    To: '',
    ignoreDates: 0,

    // Data
    rows: [], // [{ province_addr, totals, informations: [] }]
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
      return state.meta?.totals || {}
    },
  },

  actions: {
    setPeriod(p) {
      this.Period = p || 'monthly'
      if (p === 'all') {
        this.setAllTime(true)
      }
    },

    setRange({ from, to }) {
      this.From = from || ''
      this.To = to || ''
      if (from || to) {
        this.ignoreDates = 0
        if (this.Period === 'all') this.Period = 'custom'
      }
    },

    setAllTime(on = true) {
      this.ignoreDates = on ? 1 : 0
      if (on) {
        this.From = ''
        this.To = ''
        this.Period = 'all'
      }
    },

    _params(extra = {}) {
      const p = {
        search: this.Search || undefined,
        province_addr: this.ProvinceAddr || undefined,
        main_province: this.MainProvince || undefined,
        now_province: this.NowProvince || undefined,
        syasi_info_type: this.SyasiType || undefined,
        jenayee_info_type: this.JenayeeType || undefined,
        information_degree: this.InformationDegree || undefined,
        office_employee_id: this.OfficeEmployeeId || undefined,
        resource_id: this.ResourceId || undefined,
        period: this.Period || 'all',
        ignore_dates: this.ignoreDates || 0,
        ...extra,
      }

      if (!this.ignoreDates) {
        if (this.From) p.from_date = this.From
        if (this.To) p.to_date = this.To
      }

      return p
    },

    async fetchReport() {
      this.loading = true
      this.error = null

      try {
        const { formatForBackend } = useCalendar()
        const params = this._params()

        if (params.from_date) {
          params.from_date = formatForBackend(params.from_date)
        }
        if (params.to_date) {
          params.to_date = formatForBackend(params.to_date)
        }

        const { data } = await axios.get('/information-report/activity-report', {
          params,
        })

        this.rows = data?.data || []
        this.meta = data?.meta || {
          period: this.Period,
          date_from: params.from_date || null,
          date_to: params.to_date || null,
          totals: {},
          filters: {},
        }
      } catch (e) {
        console.error(e)
        this.rows = []
        this.meta = {
          period: this.Period,
          date_from: null,
          date_to: null,
          totals: {},
          filters: {},
        }
        this.error =
          e?.response?.data?.message ||
          e.message ||
          'ستونزه رامنځته شوه – د معلوماتو راپور ترلاسه نه شو.'
      } finally {
        this.loading = false
      }
    },

    // ---------------- CSV Export ----------------
    exportCsv() {
      this.error = null
      if (!this.rows.length) {
        this.error = 'هیڅ دیتا نشته چې CSV ته صادر شي.'
        return
      }

      const headers = [
        'ID',
        'Name',
        'NickName',
        'FatherName',
        'GrandFather',
        'Province_addr',
        'MainProvince',
        'MainDistricts',
        'MainVillage',
        'NowProvince',
        'NowDistrict',
        'NowVillage',
        'SyasiInfoType',
        'JenayeeInfoType',
        'InformationSavedDate',
        'InformationSavedHour',
        'InformationDegree',
        'EmployeeName',
        'EmployeeCode',
        'ResourceName',
        'ResourceCode',
        'DaysSinceSaved',
        'Tafseel',
        'ManagerComment',
        'LeadDirection',
        'Talks',
        'Note',
      ]

      const csvRows = []
      csvRows.push(headers.join(','))

      for (const group of this.rows) {
        for (const info of group.informations || []) {
          const emp = info.office_employee || {}
          const res = info.resource || {}

          const line = []

          const esc = (val) =>
            '"' + String(val ?? '').replace(/"/g, '""') + '"'

          line.push(info.id ?? '')
          line.push(esc(info.name))
          line.push(esc(info.nick_name))
          line.push(esc(info.father_name))
          line.push(esc(info.grand_father_name))
          line.push(esc(info.Province_addr))
          line.push(esc(info.main_province))
          line.push(esc(info.main_districts))
          line.push(esc(info.main_village))
          line.push(esc(info.now_province))
          line.push(esc(info.now_district))
          line.push(esc(info.now_village))
          line.push(esc(info.syasi_info_type))
          line.push(esc(info.jenayee_info_type))
          line.push(info.information_saved_date ?? '')
          line.push(info.information_saved_hour ?? '')
          line.push(esc(info.information_degree))
          line.push(esc(emp.name))
          line.push(esc(emp.code_number))
          line.push(esc(res.name))
          line.push(esc(res.code_number))
          line.push(info.days_since_saved ?? '')
          line.push(esc(info.tafseel))
          line.push(esc(info.manager_comment))
          line.push(esc(info.lead_drirection))
          line.push(esc(info.talks))
          line.push(esc(info.note))

          csvRows.push(line.join(','))
        }
      }

      const csv = csvRows.join('\r\n')
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = 'information_activity_report.csv'
      document.body.appendChild(link)
      link.click()
      link.remove()
      URL.revokeObjectURL(url)
    },

    // ---------------- PDF Export (Pashto narrative) ----------------
    exportPdf() {
      this.error = null
      if (!this.rows.length) {
        this.error = 'هیڅ دیتا نشته چې PDF ته صادر شي.'
        return
      }

      const periodLabelMap = {
        daily: 'ورځنی راپور',
        weekly: 'اونیز راپور',
        monthly: 'میاشتنی راپور',
        yearly: 'کلنی راپور',
        custom: 'مخصوص موده',
        all: 'ټوله موده',
      }

      const meta = this.meta || {}
      const periodKey = meta.period || this.Period || 'all'
      const periodLabel = periodLabelMap[periodKey] || periodKey
      const filters = meta.filters || {}
      const summary = meta.totals || {}

      const metaParts = []

      metaParts.push(`موده: ${periodLabel}`)
      if (meta.date_from) metaParts.push(`له نېټې: ${meta.date_from}`)
      if (meta.date_to) metaParts.push(`تر نېټې: ${meta.date_to}`)

      if (filters.province_addr) {
        metaParts.push(`اساس ولایت: ${filters.province_addr}`)
      }
      if (filters.main_province) {
        metaParts.push(`اصلي ولایت: ${filters.main_province}`)
      }
      if (filters.now_province) {
        metaParts.push(`اوسنی ولایت: ${filters.now_province}`)
      }
      if (filters.syasi_info_type) {
        metaParts.push(`سیاسي معلومات ډول: ${filters.syasi_info_type}`)
      }
      if (filters.jenayee_info_type) {
        metaParts.push(`جنایي معلومات ډول: ${filters.jenayee_info_type}`)
      }

      const metaText = metaParts.join(' | ')

      const totalInfos = summary.informations ?? 0
      const syasiCount = summary.with_syasi ?? 0
      const jenayeeCount = summary.with_jenayee ?? 0
      const withManager = summary.with_manager_comment ?? 0
      const withLead = summary.with_lead_direction ?? 0

      const sectionsHtml = []

      sectionsHtml.push(`
        <p>
          په ټاکل شوې موده کې ټول ټال <strong>${totalInfos}</strong> معلوماتي ریکارډونه ثبت شوي دي.
          له دې ډلې، په <strong>${syasiCount}</strong> ریکارډونو کې سیاسي معلومات،
          په <strong>${jenayeeCount}</strong> ریکارډونو کې جنایي معلومات،
          په <strong>${withManager}</strong> ریکارډونو کې د مدیر تبصره،
          او په <strong>${withLead}</strong> ریکارډونو کې د مشرتابه هدایت ثبت شوی دی.
        </p>
      `)

      let index = 1

      for (const group of this.rows) {
        const prov = group.province_addr || 'نامعلوم ولایت'
        const gTotals = group.totals || {}
        const infos = group.informations || []

        const gCount = gTotals.informations ?? infos.length
        const gSyasi = gTotals.with_syasi ?? 0
        const gJenayee = gTotals.with_jenayee ?? 0
        const gManager = gTotals.with_manager_comment ?? 0
        const gLead = gTotals.with_lead_direction ?? 0

        sectionsHtml.push(`
          <h2 style="font-size:15px; margin:16px 0 6px;">
            ولایت: ${prov}
          </h2>
        `)

        sectionsHtml.push(`
          <p>
            په ولایت <strong>${prov}</strong> کې د همدې مودې په ترڅ کې
            ټول ټال <strong>${gCount}</strong> معلوماتي ریکارډونه ثبت شوي دي.
            له دې ډلې، په <strong>${gSyasi}</strong> ریکارډونو کې سیاسي معلومات،
            په <strong>${gJenayee}</strong> ریکارډونو کې جنایي معلومات،
            په <strong>${gManager}</strong> ریکارډونو کې د مدیر نظر،
            او په <strong>${gLead}</strong> ریکارډونو کې د مشرتابه هدایت شتون لري.
          </p>
        `)

        if (!infos.length) {
          sectionsHtml.push(`
            <p style="font-size:11px; color:#777;">
              د ټاکل شويو فلټرونو له مخې په دې ولایت کې کوم مفصل ریکارډ نشته.
            </p>
          `)
          continue
        }

        for (const info of infos) {
          const date = info.information_saved_date || 'نامعلومه نېټه'
          const hour = info.information_saved_hour || ''
          const degree = info.information_degree || ''
          const syasi = info.syasi_info_type || ''
          const jenayee = info.jenayee_info_type || ''
          const mainProv = info.main_province || ''
          const nowProv = info.now_province || ''
          const tafseel = info.tafseel || ''
          const managerComment = info.manager_comment || ''
          const lead = info.lead_drirection || ''
          const talks = info.talks || ''
          const days = info.days_since_saved

          const emp = info.office_employee || {}
          const res = info.resource || {}

          let daysSentence = ''
          if (days === 0) {
            daysSentence = 'دغه معلومات همدا نن ثبت شوي دي.'
          } else if (typeof days === 'number') {
            daysSentence = `دا معلومات شاوخوا ${days} ورځې مخکې ثبت شوي دي.`
          }

          const empSentence = emp.name
            ? ` دغه ریکارډ د کارمند «${emp.name}» (کد: ${emp.code_number || '---'}) پورې تړاو لري.`
            : ''

          const resSentence = res.name
            ? ` همدارنګه له سرچینې «${res.name}» سره تړاو لري.`
            : ''

          const typeSentenceParts = []
          if (syasi) typeSentenceParts.push(`سیاسي معلومات: ${syasi}`)
          if (jenayee) typeSentenceParts.push(`جنایي معلومات: ${jenayee}`)
          if (degree) typeSentenceParts.push(`د معلوماتو درجه: ${degree}`)
          const typeSentence = typeSentenceParts.length
            ? typeSentenceParts.join('، ')
            : ''

          const addrSentenceParts = []
          if (mainProv) addrSentenceParts.push(`اصلي ولایت: ${mainProv}`)
          if (nowProv) addrSentenceParts.push(`اوسنی ولایت: ${nowProv}`)
          const addrSentence = addrSentenceParts.length
            ? addrSentenceParts.join('، ')
            : ''

          sectionsHtml.push(`
            <p>
              <strong>${index}.</strong>
              معلومات د نېټې په <strong>${date}</strong> ${hour ? `او ساعت ${hour}` : ''} کې ثبت شوي دي.
              ${addrSentence ? addrSentence + ' .' : ''}
              ${typeSentence ? typeSentence + ' .' : ''}
              ${daysSentence}
              ${empSentence}
              ${resSentence}
              ${managerComment ? ` د مدیر نظر: ${managerComment}` : ''}
              ${lead ? ` د مشرتابه هدایت: ${lead}` : ''}
              ${talks ? ` نورې خبرې: ${talks}` : ''}
              ${tafseel ? ` تفصیل: ${tafseel}` : ''}
            </p>
          `)

          index += 1
        }
      }

      const html = `
<!DOCTYPE html>
<html lang="ps" dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>د معلوماتو تفصیلي راپور</title>
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
  <h1>د معلوماتو تفصیلي راپور</h1>
  <div class="meta">${metaText}</div>

  ${sectionsHtml.join('')}

  <div class="note">
    یادونه: د دې راپور محتوا د سیستم د موجودو ریکارډونو پر بنسټ جوړه شوې ده.
    د PDF ترلاسه کولو لپاره د چاپ له کړکۍ څخه «Save as PDF» انتخاب کړه.
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
})
