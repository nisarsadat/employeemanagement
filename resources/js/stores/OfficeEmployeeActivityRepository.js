// Pinia store: one repo for both Active & Inactive pages
import { defineStore } from 'pinia'
import axios from 'axios'

axios.defaults.headers.common['Accept'] = 'application/json'

export const useOfficeEmployeeActivityRepository = defineStore('officeEmployeeActivityRepo', {
  state: () => ({
    // shared controls
    search: '',
    perPage: 'all', // number | 'all'
    page: 1,

    // data buckets
    active:   { items: [], meta: null, links: null },
    inactive: { items: [], meta: null, links: null },

    counts: { active: 0, inactive: 0, total: 0 },

    loading: false,
    error: null,
  }),

  actions: {
    setSearch(v = '') { this.search = v?.trim?.() ?? '' },
    setPerPage(v = 'all') { this.perPage = v === 0 ? 'all' : v },
    setPage(p = 1) { this.page = Math.max(1, Number(p || 1)) },

    async fetchCounts() {
      try {
        const { data } = await axios.get('/office-employees/activity-counts')
        this.counts = {
          active: Number(data?.active ?? 0),
          inactive: Number(data?.inactive ?? 0),
          total: Number(data?.total ?? 0),
        }
      } catch (e) {
        // non-fatal
      }
    },

    /**
     * status: 'active' | 'inactive'
     */
    async fetchList(status = 'active') {
      const url =
        status === 'inactive'
          ? '/office-employees/inactive'
          : '/office-employees/active'

      const params = {
        search: this.search || undefined,
        perPage: this.perPage ?? undefined,
        page: this.perPage === 'all' ? undefined : this.page,
      }

      this.loading = true
      this.error = null
      try {
        const { data } = await axios.get(url, { params })
        const parsed = this._normalizeListResponse(data)

        if (status === 'inactive') {
          this.inactive.items = parsed.items
          this.inactive.meta = parsed.meta
          this.inactive.links = parsed.links
        } else {
          this.active.items = parsed.items
          this.active.meta = parsed.meta
          this.active.links = parsed.links
        }
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Failed to load employees'
      } finally {
        this.loading = false
        // best-effort refresh of counts
        this.fetchCounts()
      }
    },

    /**
     * Accepts both plain arrays and Laravel Resource paginated payloads.
     * - Array              -> { items, meta: null, links: null }
     * - { data, meta,... } -> picks those
     */
    _normalizeListResponse(payload) {
      if (Array.isArray(payload)) {
        return { items: payload, meta: null, links: null }
      }
      const items = Array.isArray(payload?.data) ? payload.data : []
      const meta = payload?.meta ?? null
      const links = payload?.links ?? null
      return { items, meta, links }
    },
  },
})
