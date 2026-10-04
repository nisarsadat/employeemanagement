// resources/js/stores/EventsReportRepository.js
import { defineStore } from 'pinia'
import axios from 'axios'
import { useCalendar } from '../../composables/useCalendar'

axios.defaults.headers.common.Accept = 'application/json'

export const useEventsReportRepository = defineStore('eventsReport', {
  state: () => ({
    // Filters
    Search: '',
    ProvinceAddr: '',
    Zone: '',
    Hawza: '',
    EventType: '',
    EventSpecificType: '',
    TargetPerson: '',
    Criminal: 'all', // all | yes | no
    OfficeEmployeeId: '',
    ResourceId: '',

    // Time
    Period: 'monthly', // daily | weekly | monthly | yearly | custom | all
    From: '',
    To: '',
    ignoreDates: 0, // 1 = all time (period='all')

    // Data from backend
    rows: [], // = data (array of provinces with events[])
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
        zone: this.Zone || undefined,
        hawza: this.Hawza || undefined,
        event_type: this.EventType || undefined,
        event_specific_type: this.EventSpecificType || undefined,
        target_person: this.TargetPerson || undefined,
        office_employee_id: this.OfficeEmployeeId || undefined,
        resource_id: this.resourceId || this.ResourceId || undefined,
        period: this.Period || 'all',
        ...extra,
      }

      // Criminal: map "all/yes/no" to backend expected values
      if (this.Criminal && this.Criminal !== 'all') {
        p.criminal = this.Criminal === 'yes' ? '1' : '0'
      }

      if (!this.ignoreDates) {
        if (this.From) p.from_date = this.From
        if (this.To) p.to_date = this.To
      }

      return p
    },

    // ---------- FETCH REPORT ----------
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

        const { data } = await axios.get('/events-report/activity-report', { params })

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
          'ستونزه رامنځته شوه – د پیښو راپور ترلاسه نه شو.'
      } finally {
        this.loading = false
      }
    },

    // ---------- CSV export ----------
    exportCsv() {
      this.error = null

      if (!this.rows.length) {
        this.error = 'هیڅ دیتا نشته چې CSV ته صادر شي.'
        return
      }

      const headers = [
        'Province',
        'Event ID',
        'EventDate',
        'EventHour',
        'EventType',
        'EventSpecificType',
        'Zone',
        'Hawza',
        'TargetPerson',
        'Criminal',
        'TalafatJani',
        'TalafatMali',
        'EmployeeName',
        'EmployeeCode',
        'ResourceName',
        'ResourceCode',
        'DaysSinceEvent',
      ]

      const csvRows = []
      csvRows.push(headers.join(','))

      for (const group of this.rows) {
        const province = group.province_addr ?? ''
        for (const ev of group.events || []) {
          const emp = ev.office_employee || {}
          const res = ev.resource || {}

          const line = []
          line.push('"' + String(province).replace(/"/g, '""') + '"')
          line.push(ev.id ?? '')
          line.push(ev.event_date ?? '')
          line.push(ev.event_hour ?? '')
          line.push('"' + String(ev.event_type || '').replace(/"/g, '""') + '"')
          line.push(
            '"' + String(ev.event_specific_type || '').replace(/"/g, '""') + '"',
          )
          line.push('"' + String(ev.event_zone || '').replace(/"/g, '""') + '"')
          line.push('"' + String(ev.event_hawza || '').replace(/"/g, '""') + '"')
          line.push('"' + String(ev.target_person || '').replace(/"/g, '""') + '"')
          line.push('"' + String(ev.criminal || '').replace(/"/g, '""') + '"')
          line.push('"' + String(ev.talafat_jani || '').replace(/"/g, '""') + '"')
          line.push('"' + String(ev.talafat_mali || '').replace(/"/g, '""') + '"')
          line.push('"' + String(emp.name || '').replace(/"/g, '""') + '"')
          line.push('"' + String(emp.code_number || '').replace(/"/g, '""') + '"')
          line.push('"' + String(res.name || '').replace(/"/g, '""') + '"')
          line.push('"' + String(res.code_number || '').replace(/"/g, '""') + '"')
          line.push(ev.days_since_event ?? '')

          csvRows.push(line.join(','))
        }
      }

      const csv = csvRows.join('\r\n')
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)

      const link = document.createElement('a')
      link.href = url
      link.download = 'events_activity_report.csv'
      document.body.appendChild(link)
      link.click()
      link.remove()
      URL.revokeObjectURL(url)
    },

    // ---------- Pashto PDF report (نثري راپور) ----------
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

      const filtros = meta.filters || {}
      const summary = meta.totals || {}

      const metaParts = []
      metaParts.push(`موده: ${periodLabel}`)

      if (meta.date_from) metaParts.push(`له نېټې: ${meta.date_from}`)
      if (meta.date_to) metaParts.push(`تر نېټې: ${meta.date_to}`)

      if (filtros.province_addr) {
        metaParts.push(`اساس ولایت: ${filtros.province_addr}`)
      }
      if (filtros.zone) {
        metaParts.push(`آمریت: ${filtros.zone}`)
      }
      if (filtros.hawza) {
        metaParts.push(`حوزه: ${filtros.hawza}`)
      }
      if (filtros.event_type) {
        metaParts.push(`د پیښې ډول: ${filtros.event_type}`)
      }

      const metaText = metaParts.join(' | ')

      const totalEvents = summary.events ?? 0
      const totalCriminal = summary.with_criminal ?? 0
      const totalJani = summary.with_talafat_jani ?? 0
      const totalMali = summary.with_talafat_mali ?? 0

      const sectionsHtml = []

      const globalParagraph = `
        <p>
          په ټولیز ډول په ټاکل شوې موده کې <strong>${totalEvents}</strong> پیښې ثبت شوې دي.
          له دې ډلي څخه په <strong>${totalCriminal}</strong> پیښو کې مجرم یا تورن کس ذکر شوی،
          په <strong>${totalJani}</strong> پیښو کې د انساني تلفاتو او
          په <strong>${totalMali}</strong> پیښو کې د مالي تلفاتو راپور ورکړل شوی دی.
          دا راپور د ولایتونو او آمریتونو په کچه د ثبت شویو پیښو عمومي انځور وړاندې کوي.
        </p>
      `
      sectionsHtml.push(globalParagraph)

      let evIndex = 1

      for (const group of this.rows) {
        const prov = group.province_addr || 'نامعلوم ولایت'
        const gTotals = group.totals || {}
        const gEvents = group.events || []

        const gCount = gEvents.length
        const gCriminal = gTotals.with_criminal ?? 0
        const gJani = gTotals.with_talafat_jani ?? 0
        const gMali = gTotals.with_talafat_mali ?? 0

        sectionsHtml.push(`
          <h2 style="font-size:15px; margin:16px 0 6px;">
            ولایت: ${prov}
          </h2>
        `)

        sectionsHtml.push(`
          <p>
            په ولایت <strong>${prov}</strong> کې د دې راپور د مودې په ترڅ کې
            ټول ټال <strong>${gCount}</strong> پیښې ثبت شوي دي.
            له دې جملې څخه په <strong>${gCriminal}</strong> پیښو کې د مجرم یا
            تورن شخص نوم ذکر شوی او په <strong>${gJani}</strong> پیښو کې د
            انساني تلفاتو او په <strong>${gMali}</strong> پیښو کې د مالي
            تلفاتو راپور ورکړل شوی دی.
          </p>
        `)

        if (!gEvents.length) {
          sectionsHtml.push(`
            <p style="font-size:11px; color:#777;">
              په دې ولایت کې د ټاکل شويو فلټرونو له مخې کوم مفصل ریکارډ نشته.
            </p>
          `)
          continue
        }

        for (const ev of gEvents) {
          const date = ev.event_date || 'نامعلومه نېټه'
          const hour = ev.event_hour || ''
          const type = ev.event_type || 'نامعلوم ډول'
          const stype = ev.event_specific_type || ''
          const zone = ev.event_zone || 'نامعلوم آمریت'
          const hawza = ev.event_hawza || 'نامعلوم حوزه'
          const target = ev.target_person || ''
          const criminal = ev.criminal || ''
          const jani = ev.talafat_jani || ''
          const mali = ev.talafat_mali || ''
          const place = ev.event_specific_place || ''
          const tafseel = ev.tafseel || ''
          const daysLast =
            ev.days_since_event != null ? ev.days_since_event : null

          const emp = ev.office_employee || {}
          const res = ev.resource || {}

          let daysSentence = ''
          if (daysLast === 0) {
            daysSentence = 'دا پیښه همدا نن ثبت شوې ده.'
          } else if (daysLast != null) {
            daysSentence = `تقریباً ${daysLast} ورځې مخکې ثبت شوې ده.`
          }

          const empSentence = emp.name
            ? `دغه پیښه د کارمند «${emp.name}» (کد: ${emp.code_number || '---'}) پورې تړاو لري.`
            : ''

          const resSentence = res.name
            ? `دا ریکارډ له سرچینې «${res.name}» سره هم تړاو لري.`
            : ''

          const typePart = stype ? `${type} – ${stype}` : type
          const targetPart = target ? `، هدف: ${target}` : ''
          const crimPart = criminal ? `، مجرم/تورن: ${criminal}` : ''
          const janiPart = jani ? `، د انساني تلفاتو معلومات: ${jani}` : ''
          const maliPart = mali ? `، د مالي تلفاتو معلومات: ${mali}` : ''
          const placePart = place ? `، ځای: ${place}` : ''

          sectionsHtml.push(`
            <p>
              <strong>${evIndex}.</strong>
              پیښه د نېټې په <strong>${date}</strong> ${hour ? `او ساعت ${hour}` : ''} کې
              په ولایت <strong>${prov}</strong>، آمریت «${zone}» او حوزه «${hawza}» کې ثبت شوې ده.
              د پیښې ډول: <strong>${typePart}</strong>${targetPart}${crimPart}${janiPart}${maliPart}${placePart}.
              ${daysSentence}
              ${empSentence}
              ${resSentence}
              ${tafseel ? ` د پیښې تفصیل: ${tafseel}` : ''}
            </p>
          `)

          evIndex += 1
        }
      }

      const html = `
<!DOCTYPE html>
<html lang="ps" dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>د پیښو تفصیلي راپور</title>
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
  <h1>د پیښو تفصیلي راپور</h1>
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
