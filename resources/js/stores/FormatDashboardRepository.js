// src/stores/FormatDashboardRepository.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useFormatDashboardRepository = defineStore('formatDash', {
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
  }),
  actions: {
    setGranularity(g) { this.granularity = g },
    setRange({ from, to }) { this.From = from; this.To = to },

    async fetchAll() {
      const params = {
        search: this.Search || undefined,
        zone:   this.Zone   || undefined,
        from:   this.From   || undefined,
        to:     this.To     || undefined,
        granularity: this.granularity
      }
      const { data } = await axios.get('/dashboard/56-formats/combined', { params })

      this.cards = (data.cards || []).map(c => ({
        key: c.key,
        label: c.label ?? c.key,
        total: Number(c.total ?? 0) || 0
      }))

      this.series = (data.series || []).map(s => ({
        key: s.key,
        label: s.label,
        points: (s.points || []).map(p => ({
          date: String(p.date),
          count: Number(p.count || 0)
        }))
      }))

      this.buckets = (data.buckets || []).map(b => ({
        key: b.key,
        label: b.label,
        items: (b.items || []).map(i => ({
          resource_id: i.resource_id,
          name: i.name,
          code_number: i.code_number,
          phone_number: i.phone_number,
          count: Number(i.count || 0)
        }))
      }))

      this.recent = data.recent || data.items || []
    },

    async fetchTopResources(limit = 10) {
      const params = {
        search: this.Search || undefined,
        zone:   this.Zone   || undefined,
        from:   this.From   || undefined,
        to:     this.To     || undefined,
        format: this.formatKey || undefined,
        limit
      }
      const { data } = await axios.get('/dashboard/56-formats/top', { params })
      this.buckets = data.buckets || []
    }
  }
})
