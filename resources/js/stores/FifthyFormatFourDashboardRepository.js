import { defineStore } from 'pinia'
import axios from 'axios'
axios.defaults.headers.common['Accept'] = 'application/json'

export const useFifthyFormatFourDashboardRepository = defineStore('ff4Dash', {
  state: () => ({
    Search: '',
    Zone: '',
    From: '',
    To: '',
    granularity: 'daily',   // 'daily' | 'monthly' | 'yearly'
    ignoreDates: 0,

    // data
    cards: [],
    series: [],
    buckets: [],    // top resources
    zones: [],      // top zones
    provinces: [],  // top provinces
    recent: [],

    // UI helpers
    loading: false,
    error: null,

    // breakdown panel
    selectedFormat: null,
    selectedField: null,
    breakdownItems: [],
  }),

  actions: {
    setGranularity(g) { this.granularity = g || 'daily' },
    setRange({ from, to }) { this.From = from || ''; this.To = to || '' },
    setAllTime(on = true) {
      this.ignoreDates = on ? 1 : 0
      if (on) { this.From = ''; this.To = '' }
    },

    _params(extra = {}) {
      const p = {
        search: this.Search || undefined,
        zone: this.Zone || undefined,
        granularity: this.granularity,
        ...extra,
      }
      if (!this.ignoreDates) {
        if (this.From) p.from = this.From
        if (this.To)   p.to   = this.To
      }
      return p
    },

    async fetchCombined() {
      this.loading = true; this.error = null
      try {
        const { data } = await axios.get('/dashboard/ff4/combined', { params: this._params() })
        this.cards     = data?.cards     || []
        this.series    = data?.series    || []
        this.buckets   = data?.buckets   || []
        this.zones     = data?.zones     || []
        this.provinces = data?.provinces || []
        this.recent    = data?.recent    || []
      } catch (e) {
        this.error = e?.response?.data?.message || e.message || 'ستونزه رامنځته شوه'
      } finally {
        this.loading = false
      }
    },

    async fetchBreakdown() {
      if (!this.selectedFormat || !this.selectedField) { this.breakdownItems = []; return }
      this.error = null
      try {
        const { data } = await axios.get('/dashboard/ff4/breakdown', {
          params: this._params({ format: this.selectedFormat, field: this.selectedField }),
        })
        this.breakdownItems = data?.items || []
      } catch (e) {
        this.breakdownItems = []
        this.error = e?.response?.data?.message || e.message || 'ستونزه رامنځته شوه'
      }
    },
  },
})
