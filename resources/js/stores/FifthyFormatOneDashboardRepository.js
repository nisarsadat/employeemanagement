// src/stores/FifthyFormatOneDashboardRepository.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useFifthyFormatOneDashboardRepository = defineStore('ffoDash', {
  state: () => ({
    Search: '',
    Zone: '',
    From: '',
    To: '',
    granularity: 'daily', // 'daily' | 'monthly' | 'yearly'
    formatKey: null,

    cards: [],
    series: [],
    buckets: [],
    recent: [],
    loading: false,
    error: null,
  }),

  actions: {
    setGranularity(g) { this.granularity = g },
    setRange({ from, to }) { this.From = from; this.To = to },

    _params(extra = {}) {
      return {
        search: this.Search || undefined,
        zone:   this.Zone   || undefined,
        from:   this.From   || undefined,
        to:     this.To     || undefined,
        ...extra,
      }
    },

    async fetchAll() {
      this.loading = true
      this.error = null
      try {
        const params = this._params({ granularity: this.granularity })
        const { data } = await axios.get('/dashboard/fifthyformatone/combined', { params })

        this.cards = (data.cards || []).map(c => ({
          key: c.key,
          label: c.label ?? c.key,
          total: Number(c.total ?? 0) || 0,
        }))

        this.series = (data.series || []).map(s => ({
          key: s.key,
          label: s.label,
          points: (s.points || []).map(p => ({
            date: String(p.date),
            count: Number(p.count || 0),
          })),
        }))

        this.buckets = (data.buckets || []).map(b => ({
          key: b.key,
          label: b.label,
          items: (b.items || []).map(i => ({
            resource_id: i.resource_id,
            name: i.name,
            code_number: i.code_number,
            phone_number: i.phone_number,
            count: Number(i.count || 0),
          })),
        }))

        // controller returns { recent: [...] }
        this.recent = data.recent || data.items || []
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Failed to load dashboard'
      } finally {
        this.loading = false
      }
    },

    async fetchTopResources(limit = 10) {
      this.loading = true
      this.error = null
      try {
        const params = this._params({
          format: this.formatKey || undefined,
          limit,
        })
        const { data } = await axios.get('/dashboard/fifthyformatone/top-resources', { params })
        this.buckets = (data.buckets || []).map(b => ({
          key: b.key,
          label: b.label,
          items: (b.items || []).map(i => ({
            resource_id: i.resource_id,
            name: i.name,
            code_number: i.code_number,
            phone_number: i.phone_number,
            count: Number(i.count || 0),
          })),
        }))
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Failed to load top resources'
      } finally {
        this.loading = false
      }
    },

    async fetchSummary() {
      const params = this._params()
      const { data } = await axios.get('/dashboard/fifthyformatone/summary', { params })
      this.cards = (data.cards || []).map(c => ({
        key: c.key,
        label: c.label ?? c.key,
        total: Number(c.total ?? 0) || 0,
      }))
    },

    async fetchSeries() {
      const params = this._params({ granularity: this.granularity })
      const { data } = await axios.get('/dashboard/fifthyformatone/timeseries', { params })
      this.series = (data.series || []).map(s => ({
        key: s.key,
        label: s.label,
        points: (s.points || []).map(p => ({
          date: String(p.date),
          count: Number(p.count || 0),
        })),
      }))
    },

    async fetchRecent(limit = 10) {
      const params = this._params({ limit })
      const { data } = await axios.get('/dashboard/fifthyformatone/recent', { params })
      this.recent = data.recent || data.items || []
    },
  },
})
